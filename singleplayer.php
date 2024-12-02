<?php
session_start();
require 'connection.php';
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
    <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCEtD-b25DbDtWDqwJGcVFpJhzKiYU9rjk&callback=initMap&libraries=maps,marker&v=beta"></script>
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
            bottom: 20px;
            right: 20px;
            border: 1px solid #ccc;
            z-index: 100;
        }

        #map:hover {
            height: 35vh;
            width: 35vw;
            transition: 0.3s ease-in-out;
        }

        #street-view {
            height: 100vh;
            width: 100%;
        }

        #submit-btn {
            position: absolute;
            bottom: 20px;
            right: 20px;
            height: 4vh;
            width: 30vw;
            transform: translateX(-50%);
            z-index: 1000;
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
<?php require 'navbar.php'; ?>

<div id="street-view"></div>
<div>
    <div id="map"></div>
    <button id="submit-btn" class="btn btn-primary">Absenden</button>
</div>


<script>
    let smallMap; // Kleine Karte
    let smallMapMarker; // Marker in der kleinen Karte
    let markerPosition; // Variable zum Speichern der Koordinaten des Markers
    let randomLocation; // Zufällige Position für Street View

    // Generiere eine zufällige Position in Krems
    function getRandomLocationInKrems() {
        const latRange = {min: 48.392, max: 48.428};
        const lngRange = {min: 15.577, max: 15.625};

        const randomLat = Math.random() * (latRange.max - latRange.min) + latRange.min;
        const randomLng = Math.random() * (lngRange.max - lngRange.min) + lngRange.min;

        return {lat: randomLat, lng: randomLng};
    }

    // Prüfe, ob Street View verfügbar ist
    function checkForStreetView(location, callback) {
        const streetViewService = new google.maps.StreetViewService();
        streetViewService.getPanorama({location: location, radius: 50}, (data, status) => {
            callback(status === google.maps.StreetViewStatus.OK);
        });
    }

    // Suche eine gültige Street View Position
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
        const lat1 = randomLocation.lat;
        const lon1 = randomLocation.lng;
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
        getValidRandomLocation((location) => {
            randomLocation = location;
            console.log("Verwendete Position für Street View:", randomLocation);

            // Street View initialisieren (unabhängig von der kleinen Karte)
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

            // Kleine Karte (rechts unten) initialisieren
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
        });
    }

    // Absenden Button Event
    document.getElementById('submit-btn').addEventListener('click', () => {
        if (markerPosition) {
            const distance = computeDistanceFromMarker();
            const points = calculatePoints(distance);
            alert(`Entfernung: ${distance.toFixed(2)} Meter\nPunkte: ${points}`);
        } else {
            alert("Bitte setzen Sie zuerst einen Marker auf der Karte!");
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
