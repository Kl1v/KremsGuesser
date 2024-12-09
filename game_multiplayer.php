<?php
session_start();
require 'connection.php';

// Lobby-Code aus der URL abrufen
if (isset($_GET['code'])) {
    $lobbyCode = $_GET['code'];
} else {
    die("Kein Lobby-Code übergeben.");
}

// Alle Locations für die angegebene Lobby in Reihenfolge der Runden abrufen
$stmt = $conn->prepare("SELECT latitude, longitude, round FROM locations WHERE lobby_code = ? ORDER BY round ASC");
$stmt->bind_param("s", $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();

// Überprüfen, ob es Locations gibt
if ($result->num_rows == 0) {
    die("Keine Positionen gefunden. Das Spiel wurde möglicherweise nicht korrekt gestartet.");
}

$locations = [];
while ($row = $result->fetch_assoc()) {
    $locations[] = $row;
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiplayer-Spiel</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap&libraries=maps,marker&v=beta" async defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCEtD-b25DbDtWDqwJGcVFpJhzKiYU9rjk&callback=initMap&libraries=maps,marker&v=beta"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="stylemain.css">
    <style>
        #street-view {
            width: 100%;
            height: 100vh;
        }
        #map {
            height: 30vh;
            width: 30vw;
            position: absolute;
            bottom: 50px;
            right: 20px;
            border: 1px solid #ccc;
            z-index: 100;
        }
        #submit-btn {
            position: absolute;
            width: 30vw;
            bottom: 10px;
            z-index: 101;
            right: 20px;
        }
    </style>
</head>
<body>
    <?php require 'navbar.php'; ?>

    <div id="street-view"></div>
    <div id="map"></div>
    <button id="submit-btn" class="btn btn-primary">Absenden</button>

    <script>
        let smallMap; // Kleine Karte
        let smallMapMarker; // Marker in der kleinen Karte
        let markerPosition; // Variable zum Speichern der Koordinaten des Markers
        let currentLocationIndex = 0; // Startindex der Locations
        let locations = <?php echo json_encode($locations); ?>; // Locations aus PHP

        // Berechnung der Entfernung zwischen zwei geographischen Punkten (Haversine-Formel)
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
            const lat1 = parseFloat(locations[currentLocationIndex].latitude);
            const lon1 = parseFloat(locations[currentLocationIndex].longitude);
            const lat2 = markerPosition.lat();
            const lon2 = markerPosition.lng();

            return calculateDistance(lat1, lon1, lat2, lon2);
        }

        function calculatePoints(distance) {
            if (distance <= 5) {
                return 5000;
            } else if (distance >= 1000) {
                return 0;
            } else {
                return Math.round(5000 * (1 - (distance - 5) / (1000 - 5)));
            }
        }

        function initMap() {
            const randomLocation = {
                lat: parseFloat(locations[currentLocationIndex].latitude),
                lng: parseFloat(locations[currentLocationIndex].longitude)
            };

            const panorama = new google.maps.StreetViewPanorama(document.getElementById("street-view"), {
                position: randomLocation,
                pov: {heading: 165, pitch: 0},
                zoom: 1,
                disableDefaultUI: true
            });

            smallMap = new google.maps.Map(document.getElementById("map"), {
                center: randomLocation,
                zoom: 12,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                disableDefaultUI: true
            });

            smallMapMarker = new google.maps.Marker({
                position: randomLocation,
                map: smallMap,
                visible: false
            });

            smallMap.addListener("click", (event) => {
                markerPosition = event.latLng;
                smallMapMarker.setPosition(markerPosition);
                smallMapMarker.setVisible(true);
            });
        }

        document.getElementById('submit-btn').addEventListener('click', () => {
            if (markerPosition) {
                const distance = computeDistanceFromMarker();
                const points = calculatePoints(distance);

                alert(`Entfernung: ${distance.toFixed(2)} Meter\nPunkte: ${points}`);

                if (currentLocationIndex < locations.length - 1) {
                    currentLocationIndex++;
                    initMap();
                } else {
                    alert("Das Spiel ist zu Ende!");
                }
            } else {
                alert("Bitte setzen Sie zuerst einen Marker auf der Karte!");
            }
        });
    </script>
</body>
</html>
