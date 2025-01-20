<?php
session_start();
require 'connection.php';

// Lobby-Code aus der URL abrufen
if (isset($_GET['runde'])) {
    $runde = intval($_GET['runde']);
} else {
    $runde = intval(1);
}

if (isset($_GET['code'])) {
    $lobbyCode = $_GET['code'];
} else {
    die("Kein Lobby-Code übergeben.");
}
$started = null;
$stmt = $conn->prepare("SELECT started_at FROM locations WHERE round = ? AND lobby_code = ?");
$stmt->bind_param("is", $runde, $lobbyCode);
$stmt->execute();
$result = $stmt->get_result(); // get_result() verwenden, um das Ergebnis abzurufen
$row = $result->fetch_assoc(); // fetch_assoc() holt das Ergebnis als assoziatives Array
$stmt->close();

if (!$row || is_null($row['started_at'])) {
    // Wenn noch kein Datum gesetzt ist, führe das Update durch
    $stmt = $conn->prepare("UPDATE locations SET started_at = NOW() WHERE round = ? AND lobby_code = ?");
    $stmt->bind_param("is", $runde, $lobbyCode);
    $stmt->execute();
    $stmt->close();
}

$stmt = $conn->prepare("SELECT time_limit, started_at FROM lobbies 
                        JOIN locations ON lobbies.code = locations.lobby_code 
                        WHERE locations.round = ? AND locations.lobby_code = ?");
$stmt->bind_param("is", $runde, $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Lobby nicht gefunden oder Zeitlimit nicht definiert.");
}

$row = $result->fetch_assoc();
$timeLimit = intval($row['time_limit']); // Zeitlimit in Sekunden
$startedAt = strtotime($row['started_at']); // Startzeit als Unix-Zeitstempel
$currentTime = time(); // Aktuelle Zeit
$elapsedTime = $currentTime - $startedAt;
$remainingTime = max($timeLimit - $elapsedTime, 0); // Mindestens 0 Sekunden

$stmt->close();

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

// Verarbeitung von POST-Daten für gespeicherte Guesses
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (
        isset($data['lobbyCode'], $data['runde'], $data['spielername'], $data['lat'], $data['lng'], $data['score']) &&
        $data['lobbyCode'] === $lobbyCode
    ) {
        // Überprüfen, ob der Spieler bereits für diese Runde und Lobby einen Eintrag hat
        $stmt = $conn->prepare("SELECT * FROM guesses WHERE lobby_id = ? AND runde = ? AND spielername = ?");
        $stmt->bind_param("sis", $data['lobbyCode'], $data['runde'], $data['spielername']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Spieler hat bereits einen Guess abgegeben, also nichts tun oder Fehlermeldung
            echo json_encode(['success' => false, 'error' => 'Bereits abgegeben']);
            $stmt->close();
            exit;
        }

        // Wenn noch kein Eintrag existiert, führe den Insert aus
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

$stmt = $conn->prepare("SELECT rounds FROM lobbies WHERE code = ?");
$stmt->bind_param("s", $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();
$lobbyData = $result->fetch_assoc();
$stmt->close();

$maxRounds = $lobbyData['rounds'];

// Prüfen, ob die maximale Anzahl an Runden erreicht wurde
if ($_GET['runde'] > $maxRounds) {
    header("Location: final_results.php?lobbyCode=$lobbyCode");
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiplayer-Spiel</title>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCQnFQURsReLCE66o_kF2oNvgFMDkHyO6E&callback=initMap&libraries=maps,marker&v=beta"
        async defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="stylemain.css">
    <style>
    #street-view {
        width: 100%;
        height: 100vh;
    }

    #submit-btn {
        position: absolute;
        width: 30vw;
        bottom: 10px;
        z-index: 101;
        right: 20px;
    }

    #map {
        height: 30vh;
        width: 30vw;
        position: absolute;
        bottom: 50px;
        right: 20px;
        border: 1px solid #ccc;
        z-index: 100;
        transition: all 0.3s ease-in-out;
    }

    #map:hover {
        height: 70vh;
        width: 70vw;
        cursor: crosshair;
    }

    #timer {
        position: absolute;
        top: 80px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #007bff, #00d2ff);
        /* Sanfter Farbverlauf */
        color: white;
        padding: 0px 10px;
        border-radius: 15px;
        font-size: 2.2rem;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        /* Modernes Schatten-Design */
        z-index: 999;
        animation: fade-pulse 1.5s infinite;
        /* Sanftere Animation */
        font-family: 'Roboto', sans-serif;
        /* Moderne Schriftart */
    }

    /* Countdown-Ziffern */
    #timer-countdown {
        display: inline-block;
        font-family: 'Courier New', monospace;
        /* Klare Ziffernschrift */
        font-weight: 700;
        font-size: 2.4rem;
        color: #ffffff;
        /* Heller für Ziffern */
        text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        /* Leichter Schatten für Ziffern */
    }

    /* Animation */
    @keyframes fade-pulse {

        0%,
        100% {
            transform: translateX(-50%) scale(1);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        }

        50% {
            transform: translateX(-50%) scale(1.05);
            box-shadow: 0 8px 30px rgba(0, 210, 255, 0.5);
        }
    }

    #red-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(255, 0, 0, 0) 60%, rgba(255, 0, 0, 0.5) 90%, rgba(255, 0, 0, 0.8) 100%);
        pointer-events: none;
        z-index: 1000;
        display: none;
        animation: pulse-effect 1.2s infinite;
    }

    @keyframes pulse-effect {

        0%,
        100% {
            opacity: 0.7;
        }

        50% {
            opacity: 1;
        }
    }
    </style>
</head>

<body>
    <?php require 'navbar.php'; ?>

    <div id="street-view"></div>
    <div id="red-overlay"></div>
    <div id="timer"><span id="timer-countdown"></span></div>
    <div id="map"></div>
    <button id="submit-btn" class="btn btn-primary">Absenden</button>


    <script>
    let smallMap;
    let smallMapMarker;
    let markerPosition;
    const redOverlay = document.getElementById("red-overlay");
    let currentLocationIndex = <?php echo $runde - 1; ?>; // Runde 1 entspricht Index 0
    let locations = <?php echo json_encode($locations); ?>;
    const lobbyCode = "<?php echo $lobbyCode; ?>";
    const playerName = "<?php echo $_SESSION['user_name'] ?? 'Unbekannt'; ?>";
    const timeLimit = <?php echo intval($timeLimit); ?>; // Zeitlimit in Sekunden

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
        const currentLocation = {
            lat: parseFloat(locations[currentLocationIndex].latitude),
            lng: parseFloat(locations[currentLocationIndex].longitude)
        };

        const panorama = new google.maps.StreetViewPanorama(document.getElementById("street-view"), {
            position: currentLocation,
            disableDefaultUI: true
        });

        smallMap = new google.maps.Map(document.getElementById("map"), {
            center: {
                lat: 48.4100,
                lng: 15.6100
            },
            zoom: 12,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            disableDefaultUI: true
        });

        smallMapMarker = new google.maps.Marker({
            position: null, // Marker wird erst bei Klick gesetzt
            map: smallMap,
            visible: false
        });

        smallMap.addListener("click", (event) => {
            markerPosition = event.latLng;
            smallMapMarker.setPosition(markerPosition);
            smallMapMarker.setVisible(true);
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
    const timerDisplay = document.getElementById("timer-countdown");
    startTimer(timeLimit, timerDisplay);
});

let remainingTime = <?php echo $remainingTime; ?>;
const timerDisplay = document.getElementById("timer-countdown");

document.addEventListener("DOMContentLoaded", () => {
    startTimer(remainingTime, timerDisplay);
});

function startTimer(duration, display) {
    let timer = duration;
    const interval = setInterval(() => {
        const minutes = Math.floor(timer / 60);
        const seconds = timer % 60;

        display.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

        if (timer <= 10) {
            display.parentElement.style.background = 'linear-gradient(135deg, #ff4e50, #ff8a65)';
            display.parentElement.style.boxShadow = '0 6px 20px rgba(255, 78, 80, 0.5)';
        }
        if (timer <= 5) {
            display.parentElement.style.background = 'linear-gradient(135deg, rgb(255, 0, 4), rgb(255, 82, 29))';
            display.parentElement.style.boxShadow = '0 6px 20px rgba(112, 0, 2, 0.5)';
            redOverlay.style.display = "block";
        }

        if (timer-- <= 0) {
            clearInterval(interval);
            document.getElementById('submit-btn').disabled = true;
            setTimeout(() => {
                window.location.href = `show_score.php?lobbyCode=${lobbyCode}&runde=${currentLocationIndex + 1}`;
            }, 5000);
        }
    }, 1000);
}

    document.getElementById('submit-btn').addEventListener('click', () => {
        if (markerPosition) {
            const submitButton = document.getElementById('submit-btn');
            submitButton.disabled = true; // Deaktiviert den Button, um mehrfaches Absenden zu verhindern

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
                        runde: currentLocationIndex + 1, // Index + 1 für die Runde
                        spielername: playerName,
                        lat: markerPosition.lat(),
                        lng: markerPosition.lng(),
                        score: score
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {} else {
                        submitButton.disabled = false;
                    }
                });
        } else {

        }
    });

    function startTimer(duration, display) {
        let timer = duration,
            minutes, seconds;
        const interval = setInterval(() => {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            display.textContent = minutes + ":" + seconds;

            if (timer <= 10) { // Farbwechsel bei geringer Zeit
                display.parentElement.style.background = 'linear-gradient(135deg, #ff4e50, #ff8a65)';
                display.parentElement.style.boxShadow = '0 6px 20px rgba(255, 78, 80, 0.5)';
            }
            if (timer <= 5) { // Farbwechsel bei geringer Zeit
                display.parentElement.style.background =
                    'linear-gradient(135deg,rgb(255, 0, 4),rgb(255, 82, 29))';
                display.parentElement.style.boxShadow = '0 6px 20px rgba(112, 0, 2, 0.5)';
                redOverlay.style.display = "block";
            }

            if (--timer < 0) {
                clearInterval(interval);
                document.getElementById('submit-btn').disabled = true; // Button deaktivieren
                setTimeout(() => {
                    window.location.href =
                        `show_score.php?lobbyCode=${lobbyCode}&runde=${currentLocationIndex + 1}`;
                }, 5000); // 5 Sekunden Verzögerung vor der Weiterleitung
            }
        }, 1000);
    }


    window.initMap = initMap;
    </script>

</body>

</html>