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
</head>
<body>
    <?php require 'navbar.php'?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <h2 class="text-center">Multiplayer-Spiel - Lobby: <?php echo htmlspecialchars($lobbyCode); ?></h2>
            </div>
            <div class="col-md-12">
                <!-- Street View Container -->
                <div id="street-view" style="width: 100%; height: 400px;"></div>
            </div>
            <div class="col-md-12 mt-4">
                <!-- Kleine Karte Container -->
                <div id="map" style="width: 100%; height: 400px;"></div>
            </div>
        </div>

        <!-- Button zum Absenden der Markierung -->
        <div class="row mt-4">
            <div class="col-md-12 text-center">
                <button id="submit-btn" class="btn btn-primary">Entfernung Berechnen und Punkte Erhalten</button>
            </div>
        </div>
    </div>

    <script>
        let smallMap; // Kleine Karte
        let smallMapMarker; // Marker in der kleinen Karte
        let markerPosition; // Variable zum Speichern der Koordinaten des Markers
        let currentLocationIndex = 0; // Startindex der Locations
        let locations = <?php echo json_encode($locations); ?>; // Locations aus PHP

        // Generiere eine zufällige Position in Krems
        function getRandomLocationInKrems() {
            const latRange = {min: 48.392, max: 48.428};
            const lngRange = {min: 15.577, max: 15.625};

            const randomLat = Math.random() * (latRange.max - latRange.min) + latRange.min;
            const randomLng = Math.random() * (lngRange.max - lngRange.min) + lngRange.min;

            return {lat: randomLat, lng: randomLng};
        }

        // Berechnung der Entfernung zwischen zwei geographischen Punkten (Haversine-Formel)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Erdradius in Kilometern

            // Umrechnung der Koordinaten von Grad in Bogenmaß
            const φ1 = lat1 * Math.PI / 180;
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lon2 - lon1) * Math.PI / 180;

            // Haversine-Formel
            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            // Entfernung berechnen
            const distance = R * c * 1000; // in Metern
            return distance;
        }

        // Berechnung der Entfernung zwischen der zufälligen Position und dem Marker
        function computeDistanceFromMarker() {
            const lat1 = locations[currentLocationIndex].latitude;
            const lon1 = locations[currentLocationIndex].longitude;
            const lat2 = markerPosition.lat();
            const lon2 = markerPosition.lng();

            const distance = calculateDistance(lat1, lon1, lat2, lon2);
            console.log(`Die Entfernung zwischen der zufälligen Position und dem Marker beträgt ${distance.toFixed(2)} Meter.`);

            return distance;
        }

        // Punkte basierend auf der Entfernung berechnen (0-5m => 5000 Punkte, 1000m => 0 Punkte)
        function calculatePoints(distance) {
            let points;

            if (distance <= 5) {
                points = 5000; // 0-5 Meter => 5000 Punkte
            } else if (distance >= 1000) {
                points = 0; // 1000 Meter oder mehr => 0 Punkte
            } else {
                // Lineare Interpolation zwischen 5000 und 0 Punkten
                points = 5000 * (1 - (distance - 5) / (1000 - 5));
            }

            points = Math.round(points);
            return points;
        }

        function initMap() {
            // Die erste Position aus der Locations-Array verwenden
            const randomLocation = { lat: locations[currentLocationIndex].latitude, lng: locations[currentLocationIndex].longitude };

            // Street View initialisieren
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

            // Kleine Karte initialisieren
            smallMap = new google.maps.Map(document.getElementById("map"), {
                center: randomLocation,
                zoom: 12,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                disableDefaultUI: true, // Entfernt UI-Elemente für die kleine Karte
            });

            // Marker wird initial nicht angezeigt
            smallMapMarker = new google.maps.Marker({
                position: randomLocation,
                map: smallMap,
                visible: false // Marker ist zunächst unsichtbar
            });

            // Klick-Listener für die kleine Karte (aktualisiert Marker und speichert Koordinaten)
            smallMap.addEventListener("click", (event) => {
                const clickedLocation = event.latLng;

                // Aktualisiere Marker-Position und mache ihn sichtbar
                smallMapMarker.setPosition(clickedLocation);
                smallMapMarker.setVisible(true); // Marker wird sichtbar

                // Speichere Koordinaten des Markers
                markerPosition = clickedLocation;
                console.log("Neue Marker-Position:", clickedLocation.toString());
            });
        }

        // Absenden Button Event
        document.getElementById('submit-btn').addEventListener('click', () => {
            if (markerPosition) {
                const distance = computeDistanceFromMarker();
                const points = calculatePoints(distance);
                alert(`Entfernung: ${distance.toFixed(2)} Meter\nPunkte: ${points}`);

                // Nächste Runde
                if (currentLocationIndex < locations.length - 1) {
                    currentLocationIndex++;
                    initMap(); // Karte und StreetView aktualisieren
                } else {
                    alert("Das Spiel ist zu Ende!");
                }
            } else {
                alert("Bitte setzen Sie zuerst einen Marker auf der Karte!");
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
