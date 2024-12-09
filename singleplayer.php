<?php
session_start();
require 'connection.php';

// Punkteinitialisierung
if (!isset($_SESSION['total_points'])) {
    $_SESSION['total_points'] = 0;
}

// Punkte speichern, wenn die Runde endet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Überprüfen, ob der Benutzername in der Session verfügbar ist
    if (!isset($_SESSION['user_name'])) {
        echo json_encode(['success' => false, 'error' => 'Benutzername nicht gesetzt']);
        exit;
    }

    // Empfangen der Punkte aus der Anfrage (JSON-Body)
    $data = json_decode(file_get_contents('php://input'), true);

    // Überprüfen, ob 'points' vorhanden ist
    if (!isset($data['points'])) {
        echo json_encode(['success' => false, 'error' => 'Punkte fehlen']);
        exit;
    }

    $username = $_SESSION['user_name']; // Benutzername aus der Session
    $roundPoints = intval($data['points']);
    $_SESSION['total_points'] += $roundPoints;

    // Punkte in der Datenbank speichern
    try {
        // Update-Statement vorbereiten und ausführen
        $stmt = $conn->prepare("UPDATE login SET score = score + ? WHERE username = ?");
        $stmt->bind_param("is", $roundPoints, $username);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'total_points' => $_SESSION['total_points']]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Datenbankupdate fehlgeschlagen']);
        }
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Singleplayer</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="stylemain.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCQnFQURsReLCE66o_kF2oNvgFMDkHyO6E&callback=initMap&libraries=maps,marker&v=beta"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden; /* Kein Scrollen auf der Seite */
        }

        #map {
            height: 30vh;
            width: 30vw;
            position: absolute;
            bottom: 65px;
            right: 20px;
            border: 1px solid #ccc;
            z-index: 100;
        }

        #street-view {
            height: 100vh;
            width: 100%;
        }

        #submit-btn, #next-btn {
            position: absolute;
            bottom: 30px;
            right: 20px;
            height: 4vh;
            width: 30vw;
            z-index: 1000;
        }

        #next-btn {
            display: none;
        }
    </style>
</head>
<body>
<?php require 'navbar.php'; ?>

<div id="street-view"></div>
<div>
    <div id="map"></div>
    <button id="submit-btn" class="btn btn-primary">Absenden</button>
    <button id="next-btn" class="btn btn-success">Nächste Runde</button>
</div>

<script>
    let smallMap; // Kleine Karte
    let originalMarker; // Marker für die ursprüngliche Position
    let userMarker; // Marker für die gesetzte Position
    let markerPosition; // Position des gesetzten Markers
    let randomLocation; // Zufällige Position für Street View

    function getRandomLocationInKrems() {
        const latRange = {min: 48.392, max: 48.428};
        const lngRange = {min: 15.577, max: 15.625};

        const randomLat = Math.random() * (latRange.max - latRange.min) + latRange.min;
        const randomLng = Math.random() * (lngRange.max - lngRange.min) + lngRange.min;

        return {lat: randomLat, lng: randomLng};
    }

    function checkForStreetView(location, callback) {
        const streetViewService = new google.maps.StreetViewService();
        streetViewService.getPanorama({location: location, radius: 50}, (data, status) => {
            callback(status === google.maps.StreetViewStatus.OK);
        });
    }

    function getValidRandomLocation(callback) {
        let attempts = 0;

        function tryRandomLocation() {
            if (attempts > 5) {
                console.warn("Fallback-Position wird verwendet.");
                callback({lat: 48.4105, lng: 15.6106}); // Fallback-Position
                return;
            }

            const randomLocation = getRandomLocationInKrems();
            checkForStreetView(randomLocation, (isValid) => {
                if (isValid) {
                    callback(randomLocation);
                } else {
                    attempts++;
                    tryRandomLocation();
                }
            });
        }

        tryRandomLocation();
    }

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Erdradius in Kilometern
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
            Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c * 1000; // in Metern
    }

    function computeDistanceFromMarker() {
        const lat1 = randomLocation.lat;
        const lon1 = randomLocation.lng;
        const lat2 = markerPosition.lat();
        const lon2 = markerPosition.lng();

        return calculateDistance(lat1, lon1, lat2, lon2);
    }

    function calculatePoints(distance) {
        let points;
        if (distance <= 5) {
            points = 5000;
        } else if (distance >= 1000) {
            points = 0;
        } else {
            points = 5000 * (1 - (distance - 5) / (1000 - 5));
        }
        return Math.round(points);
    }

    function initMap() {
        getValidRandomLocation((location) => {
            randomLocation = location;
            console.log("Verwendete Position für Street View:", randomLocation);

            const panorama = new google.maps.StreetViewPanorama(document.getElementById("street-view"), {
                position: randomLocation,
                pov: {heading: 165, pitch: 0},
                zoom: 1,
                disableDefaultUI: true,
                linksControl: false,
                addressControl: false,
                panControl: false,
                fullscreenControl: false,
            });

            smallMap = new google.maps.Map(document.getElementById("map"), {
                center: { lat: 48.4095, lng: 15.6106 },
                zoom: 12,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                disableDefaultUI: true,
            });

            originalMarker = new google.maps.Marker({
                position: randomLocation,
                map: smallMap,
                visible: false, // Marker wird initial nicht angezeigt
                icon: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
            });

            userMarker = new google.maps.Marker({
                map: smallMap,
                visible: false, // Marker wird initial nicht angezeigt
                draggable: false,
                icon: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
            });

            smallMap.addListener("click", (event) => {
                const clickedLocation = event.latLng;

                userMarker.setPosition(clickedLocation);
                userMarker.setVisible(true);

                markerPosition = clickedLocation;
                console.log("Neue Marker-Position:", clickedLocation.toString());
            });
        });
    }

    document.getElementById('submit-btn').addEventListener('click', () => {
    if (markerPosition) {
        const distance = computeDistanceFromMarker();
        const points = calculatePoints(distance);

        alert(`Entfernung: ${distance.toFixed(2)} Meter\nPunkte: ${points}`);

        // Zeige beide Marker an
        originalMarker.setVisible(true);
        userMarker.setVisible(true);

        // Punkte an den Server senden
        fetch('singleplayer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ points: points })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`Punkte erfolgreich gespeichert: ${data.total_points}`);
                alert(`Gesamte Punkte: ${data.total_points}`);
            } else {
                console.error('Fehler beim Speichern der Punkte:', data.error);
                alert('Fehler beim Speichern der Punkte.');
            }
        })
        .catch(error => {
            console.error('Fehler:', error);
        });

        // Zeige nur den "Zum Hauptmenü"-Button
        document.getElementById('submit-btn').style.display = 'none';
        document.querySelector('#next-btn').style.display = 'block';
    } else {
        alert("Bitte setzen Sie zuerst einen Marker auf der Karte!");
    }
});

    document.getElementById('next-btn').addEventListener('click', () => {
        location.reload(); // Lädt die nächste Runde
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
