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
            z-index: 1000;
        }

        #street-view {
            height: 100vh;
            width: 100%;
        }
    </style>
</head>
<body>
<?php require 'navbar.php'; ?>

<div id="street-view"></div>
<div id="map"></div>

<script>
    let smallMap; // Kleine Karte
    let smallMapMarker; // Marker in der kleinen Karte

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

    function initMap() {
        getValidRandomLocation((randomLocation) => {
            console.log("Verwendete Position:", randomLocation);

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

            // Kleine Karte (rechts unten) initialisieren
            smallMap = new google.maps.Map(document.getElementById("map"), {
                center: randomLocation,
                zoom: 12,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                disableDefaultUI: true, // Entfernt UI-Elemente für die kleine Karte
            });

            // Marker auf der kleinen Karte hinzufügen
            smallMapMarker = new google.maps.Marker({
                position: randomLocation,
                map: smallMap,
                visible: false
            });

            // Klick-Listener für die kleine Karte (aktualisiert Marker)
            smallMap.addEventListener("click", (event) => {
                const clickedLocation = event.latLng;

                // Aktualisiere Marker und Street View
                smallMapMarker.setPosition(clickedLocation);
                panorama.setPosition(clickedLocation);
                smallMap.setCenter(clickedLocation);

                console.log("Neue Position:", clickedLocation.toString());
            });
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
