<?php
require 'connection.php'; // Verbindung zur Datenbank
require 'functions.php'; // Gemeinsame Funktionen
session_start(); // Session starten

// Überprüfen, ob der Benutzername in der Session gesetzt ist
if (!isset($_SESSION['user_name']) || empty($_SESSION['user_name'])) {
    die('Benutzername ist nicht gesetzt. Bitte melde dich an.');
}

// Funktion zur Generierung zufälliger Koordinaten in Krems
function getRandomLocationInKrems()
{
    $latMin = 48.392; // Minimaler Breitengrad
    $latMax = 48.428; // Maximaler Breitengrad
    $lngMin = 15.577; // Minimaler Längengrad
    $lngMax = 15.625; // Maximaler Längengrad

    $latitude = rand($latMin * 1000000, $latMax * 1000000) / 1000000;
    $longitude = rand($lngMin * 1000000, $lngMax * 1000000) / 1000000;

    return ['lat' => $latitude, 'lng' => $longitude];
}

// Funktion zur Validierung, ob Street View für eine Position verfügbar ist
function isStreetViewAvailable($lat, $lng)
{
    $apiKey = "AIzaSyCQnFQURsReLCE66o_kF2oNvgFMDkHyO6E";
    $url = "https://maps.googleapis.com/maps/api/streetview/metadata?location=$lat,$lng&key=$apiKey";

    $context = stream_context_create([
        'https' => ['timeout' => 5] // Timeout auf 5 Sekunden setzen
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return false;
    }

    $data = json_decode($response, true);
    return isset($data['status']) && $data['status'] === "OK";
}

// Funktion zur Erstellung der Lobby
function createLobby($conn, $lobbyCode, $rounds, $timeLimit)
{
    // Überprüfen, ob die Lobby bereits existiert
    $stmt = $conn->prepare("SELECT * FROM lobbies WHERE code = ?");
    $stmt->bind_param("s", $lobbyCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO lobbies (code, rounds, time_limit) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $lobbyCode, $rounds, $timeLimit);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
    }
    $stmt->close();

    // Spieler als Host hinzufügen
    $stmt = $conn->prepare("INSERT INTO players (username, lobby_code, is_host) VALUES (?, ?, 1)");
    $stmt->bind_param("ss", $_SESSION['user_name'], $lobbyCode);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $stmt->close();

    // Zufällige Positionen für jede Runde generieren
    for ($i = 1; $i <= $rounds; $i++) {
        $location = null;

        // Wiederholen, bis eine valide Position gefunden wird
        do {
            $randomLocation = getRandomLocationInKrems();
            $isValid = isStreetViewAvailable($randomLocation['lat'], $randomLocation['lng']);
            if ($isValid) {
                $location = $randomLocation;
            }
        } while ($location === null);

        $stmt = $conn->prepare("INSERT INTO locations (lobby_code, round, latitude, longitude) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sidd", $lobbyCode, $i, $location['lat'], $location['lng']);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
    }

    return true;
}

// POST-Request verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lobbyCode = $_POST['lobbyCode'] ?? null;
    $rounds = isset($_POST['rounds']) ? intval($_POST['rounds']) : null;
    $timeLimit = isset($_POST['timeLimit']) ? intval($_POST['timeLimit']) : null;



    if (!$lobbyCode || !$rounds || !$timeLimit) {
        $errorMessage = 'Alle Felder müssen ausgefüllt sein!';
    } else {
        $result = createLobby($conn, $lobbyCode, $rounds, $timeLimit);
        if ($result) {
            header("Location: start_lobby.php?code=" . $lobbyCode);
            exit;
        } else {
            $errorMessage = 'Fehler beim Erstellen der Lobby.';
        }
    }
}

// Generiere einen eindeutigen Code für die Lobby
$lobbyCode = generateUniqueLobbyCode($conn);
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KremsGuesser - Lobby Erstellen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>


        .radio-group {
            display: flex;
            gap: 1rem; /* Abstand zwischen den Radio-Buttons */
            justify-content: center; /* Zentriert die Buttons */
        }

        .radio {
            display: flex;
            align-items: center;
        }

        .radio-label {
            cursor: pointer;
        }

        .radio input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .radio input[type="radio"] + .radio-label:before {
            content: '';
            background: rgb(244, 244, 244);
            border-radius: 100%;
            border: 1px solid rgba(0, 0, 0, 0.25);
            display: inline-block;
            width: 1.4em;
            height: 1.4em;
            position: relative;
            top: -0.2em;
            margin-right: 1em;
            vertical-align: top;
            cursor: pointer;
            text-align: center;
            transition: all 250ms ease;
        }

        .radio input[type="radio"]:checked + .radio-label:before {
            background-color: rgb(101, 73, 139);
            box-shadow: inset 0 0 0 4px rgb(244, 244, 244);
        }

        .radio input[type="radio"]:focus + .radio-label:before {
            outline: none;
            border-color: rgb(101, 73, 139);
        }

        .radio input[type="radio"]:disabled + .radio-label:before {
            box-shadow: inset 0 0 0 4px rgb(244, 244, 244);
            border-color: rgba(0, 0, 0, 0.25);
            background: rgba(0, 0, 0, 0.25);
        }

        .radio .radio-label:empty:before {
            margin-right: 0;
        }

        input[name="timeLimit"] {
            width: 100%;
            max-width: 185px;
            padding: 0.5rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        ::placeholder {
        color: lightgray;
        }
    </style>
</head>

<body style="padding-top: 70px;">
    <?php require 'navbar.php'; ?>

    <div class="container">
        <div class="play-container" data-aos="fade-down" data-aos-duration="1000">
            <h1 class="text-center">Lobby erstellen</h1>
            <?php if (isset($errorMessage)) : ?>
                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
            <?php endif; ?>

            <form method="POST" >
                <div class="d-flex flex-column align-items-center gap-3" >
                    <div class="code-container w-100">
                        <h2>Code</h2>
                        <div class="lobby-code-container">
                            <input type="number" placeholder="<?php echo $lobbyCode; ?>" maxlength="4" class="lobby-code-input" readonly name="lobbyCode" disabled>
                        </div>
                    </div>
                    <div class="code-container w-100">
                        <h2>Runden</h2>
                        <div class="radio-group">
                            <div class="radio">
                                <input id="round3" name="rounds" type="radio" value="3" checked>
                                <label for="round3" class="radio-label">3</label>
                            </div>
                            <div class="radio">
                                <input id="round5" name="rounds" type="radio" value="5">
                                <label for="round5" class="radio-label">5</label>
                            </div>
                            <div class="radio">
                                <input id="round10" name="rounds" type="radio" value="10">
                                <label for="round10" class="radio-label">10</label>
                            </div>
                        </div>
                    </div>
                    <div class="code-container w-100">
                        <h2>Zeitlimit pro Runde</h2>
                        <input type="number" min="10" max="120" name="timeLimit" placeholder="maximal 120 Sekunden">
                    </div>
                    <button type="submit" class="start-button">Lobby erstellen</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>

</html>
