<?php
session_start();
require 'connection.php';

// Lobby-Code aus der URL abrufen
if (isset($_GET['code'])) {
    $lobbyCode = $_GET['code'];
} else {
    die("Kein Lobby-Code übergeben.");
}

// Verarbeitung von POST-Daten für gespeicherte Guesses
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (
        isset($data['lobbyCode'], $data['runde'], $data['spielername'], $data['lat'], $data['lng'], $data['score']) &&
        $data['lobbyCode'] === $lobbyCode
    ) {
        $stmt = $conn->prepare(
            "INSERT INTO guesses (lobby_id, runde, spielername, lat, lng, score) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "sisddi",
            $data['lobbyCode'],
            $data['runde'],
            $data['spielername'],
            $data['lat'],
            $data['lng'],
            $data['score']
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }

        $stmt->close();
        exit;
    }
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
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCEtD-b25DbDtWDqwJGcVFpJhzKiYU9rjk&callback=initMap&libraries=maps,marker&v=beta" async defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        let smallMap;
        let smallMapMarker;
        let markerPosition;
        let currentLocationIndex = 0;
        let locations = <?php echo json_encode($locations); ?>;
        const lobbyCode = "<?php echo $lobbyCode; ?>";
        const playerName = "<?php echo $_SESSION['user_name'] ?? 'Unbekannt'; ?>";

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius der Erde in km
            const φ1 = lat1 * Math.PI / 180;
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lon2 - lon1) * Math.PI / 180;

            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                      Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            return R * c * 1000; // Entfernung in Metern
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
                const distance = calculateDistance(
                    parseFloat(locations[currentLocationIndex].latitude),
                    parseFloat(locations[currentLocationIndex].longitude),
                    markerPosition.lat(),
                    markerPosition.lng()
                );

                const score = calculatePoints(distance);

                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        lobbyCode: lobbyCode,
                        runde: currentLocationIndex + 1,
                        spielername: playerName,
                        lat: markerPosition.lat(),
                        lng: markerPosition.lng(),
                        score: score
                    })
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          if (currentLocationIndex < locations.length - 1) {
                              currentLocationIndex++;
                              initMap();
                          } else {
                              window.location.href = `show_score.php?lobbyCode=${lobbyCode}`;
                          }
                      } else {
                          alert('Fehler beim Absenden der Daten.');
                      }
                  });
            } else {
                alert("Bitte setzen Sie zuerst einen Marker auf der Karte!");
            }
        });

        window.initMap = initMap;
    </script>
</body>
</html>
