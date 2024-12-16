<?php
session_start();
 
$roundPoints = isset($_SESSION['points']) ? $_SESSION['points'] : 0;
$totalPoints = isset($_SESSION['total_points']) ? $_SESSION['total_points'] : 0;
$originalLocation = isset($_SESSION['original_location']) ? $_SESSION['original_location'] : ['lat' => 48.41, 'lng' => 15.61];
$userLocation = isset($_SESSION['user_location']) ? $_SESSION['user_location'] : ['lat' => 48.42, 'lng' => 15.62];
?>
 
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ergebnis</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="stylemain.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script async
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCQnFQURsReLCE66o_kF2oNvgFMDkHyO6E&callback=initMap&libraries=maps,marker&v=beta">
    </script>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; font-family: Arial, sans-serif; }
        #map { height: 100vh; width: 100%; }
        #points-display {
            position: absolute;
            bottom: 20px; /* Abstand vom unteren Rand */
            left: 50%; /* Horizontal zentrieren */
            transform: translateX(-50%); /* Zentrierung ausgleichen */
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 10px;
            font-size: 1.5rem;
            z-index: 1000;
            text-align: center;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.3);
        }
 
        #slider-container {
            margin-top: 10px;
        }
        #slider {
            width: 300px; height: 15px; background: #ddd; border-radius: 10px;
            position: relative; overflow: hidden;
        }
        #slider-fill {
            height: 100%; width: 0%; background-color: green; transition: width 1s ease-in-out;
        }
        #next-round-btn {
            margin-top: 20px; text-align: center;
        }
    </style>
</head>
<body>
    <div id="points-display">
        <p><strong>Punkte in dieser Runde:</strong> <?php echo $roundPoints; ?></p>
        <p><strong>Gesamtpunkte:</strong> <?php echo $totalPoints; ?></p>
 
        <!-- Slider -->
        <div id="slider-container">
            <div id="slider">
                <div id="slider-fill"></div>
            </div>
            <div id="slider-value"><?php echo $roundPoints; ?> Punkte</div>
        </div>
 
        <!-- Button zur nächsten Runde -->
        <div id="next-round-btn">
            <a href="singleplayer.php" class="btn btn-success">Nächste Runde</a>
        </div>
    </div>
    <div id="map"></div>
 
    <script>
        function initMap() {
            const originalPosition = { lat: <?php echo $originalLocation['lat']; ?>, lng: <?php echo $originalLocation['lng']; ?> };
            const userPosition = { lat: <?php echo $userLocation['lat']; ?>, lng: <?php echo $userLocation['lng']; ?> };
 
            const map = new google.maps.Map(document.getElementById("map"), {
                center: originalPosition,
                zoom: 15,
                disableDefaultUI: true, // Benutzeroberfläche deaktivieren
                draggable: false // Karte nicht beweglich
            });
 
            // Ursprünglicher Marker mit InfoWindow
            const originalMarker = new google.maps.Marker({
                position: originalPosition,
                map: map,
                title: "Ursprüngliche Position",
                icon: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
            });
 
            const originalInfoWindow = new google.maps.InfoWindow({
                content: "<div style='font-size: 11px;'><strong>Ursprüngliche Position</strong></div>",
                disableAutoPan: true, // Verhindert das automatische Verschieben der Karte
                pixelOffset: new google.maps.Size(0, -30) // Passt die Position des InfoWindows an
            });
 
 
            originalMarker.addListener("click", () => {
                originalInfoWindow.open(map, originalMarker);
            });
 
            // Benutzer-Marker mit InfoWindow
            const userMarker = new google.maps.Marker({
                position: userPosition,
                map: map,
                title: "Deine Position",
                icon: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
            });
 
            const userInfoWindow = new google.maps.InfoWindow({
                content: "<div style='font-size: 11px;'><strong>Ich</strong></div>"
            });
 
            userMarker.addListener("click", () => {
                userInfoWindow.open(map, userMarker);
            });
 
            // InfoWindows automatisch öffnen, um die Beschriftungen direkt zu sehen
            originalInfoWindow.open(map, originalMarker);
            userInfoWindow.open(map, userMarker);
        }
 
 
        window.onload = () => {
            const points = <?php echo $roundPoints; ?>;
 
            // Slider-Fill anpassen
            const sliderFill = document.getElementById('slider-fill');
            const sliderValue = document.getElementById('slider-value');
 
            const maxPoints = 5000;
            const widthPercentage = (points / maxPoints) * 100;
 
            sliderFill.style.width = `${widthPercentage}%`;
            sliderValue.innerText = `${points} Punkte`;
        };
    </script>
</body>
</html>