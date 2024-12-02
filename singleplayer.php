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
        #map {
            height: auto;
            width: auto;
        }

        #street-view {
            height: 91.8vh;
            width: 100%;
        }
    </style>
</head>
<body style="padding-top: 70px;">
<?php require 'navbar.php'; ?>

<div id="street-view"></div>
<div style="position: fixed; bottom: 10px; right: 10px;">
    <div id="map"></div>
</div>

<script>
    let map;
    let marker;

    function getRandomLocationInKrems() {
        // Definiere einen Bereich für zufällige Koordinaten in Krems
        const latRange = {min: 48.392, max: 48.428}; // Breitengradbereich für Krems
        const lngRange = {min: 15.577, max: 15.625}; // Längengradbereich für Krems

        // Erzeuge zufällige Latitude und Longitude innerhalb des Bereichs
        const randomLat = Math.random() * (latRange.max - latRange.min) + latRange.min;
        const randomLng = Math.random() * (lngRange.max - lngRange.min) + lngRange.min;

        return {lat: randomLat, lng: randomLng};
    }

    function initMap() {
        // Generiere eine zufällige Position in Krems
        const randomLocation = getRandomLocationInKrems();
        console.log(randomLocation);

        // Erstelle die Karte und zentriere sie auf die zufällige Position
        map = new google.maps.Map(document.getElementById("map"), {
            center: randomLocation,
            zoom: 13,
        });

        // Street View initialisieren mit der zufälligen Position
        const panorama = new google.maps.StreetViewPanorama(
            document.getElementById('street-view'), {
                position: randomLocation,
                pov: {heading: 165, pitch: 0},
                zoom: 1
            }
        );

        // Street View mit der Karte verbinden
        //map.setStreetView(panorama);

        // Klick-Listener auf die Karte setzen
        map.addEventListener("click", (event) => {
            // Wenn bereits ein Marker existiert, diesen entfernen
            if (marker) {
                marker.setMap(null);
            }

            // Marker an der Position des Klicks platzieren
            marker = new google.maps.Marker({
                position: event.latLng,
                map: map,
            });

            // Koordinaten des Klicks in der Konsole ausgeben
            console.log("Koordinaten:", event.latLng.lat(), event.latLng.lng());
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
