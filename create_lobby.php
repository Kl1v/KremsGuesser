<?php
require 'connection.php'; // Verbindung zur Datenbank
require 'functions.php'; // Gemeinsame Funktionen
session_start(); // Session starten

// Überprüfen, ob der Benutzername in der Session gesetzt ist
if (!isset($_SESSION['user_name']) || empty($_SESSION['user_name'])) {
    die('Benutzername ist nicht gesetzt. Bitte melde dich an.');
}

// Funktion zum Erstellen der Lobby
function createLobby($conn, $lobbyCode, $rounds, $timeLimit) {
    // Überprüfen, ob die Lobby bereits existiert
    $stmt = $conn->prepare("SELECT * FROM lobbies WHERE code = ?");
    $stmt->bind_param("s", $lobbyCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Lobby existiert nicht, neu erstellen
        $stmt = $conn->prepare("INSERT INTO lobbies (code, rounds, time_limit) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $lobbyCode, $rounds, $timeLimit);
        if (!$stmt->execute()) {
            $stmt->close();
            return false; // Fehler beim Erstellen der Lobby
        }
    }
    $stmt->close();

    // Spieler als Host hinzufügen
    $stmt = $conn->prepare("INSERT INTO players (username, lobby_code, is_host) VALUES (?, ?, 1)");
    $stmt->bind_param("ss", $_SESSION['user_name'], $lobbyCode);
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false; // Fehler beim Hinzufügen des Hosts
}

// POST-Request verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lobbyCode = $_POST['lobbyCode'] ?? null;
    $rounds = isset($_POST['rounds']) ? intval($_POST['rounds']) : null;
    $timeLimit = isset($_POST['timeLimit']) ? intval($_POST['timeLimit']) : null;

    // Validierung der Eingaben
    if (!$lobbyCode || !$rounds || !$timeLimit) {
        $errorMessage = 'Alle Felder müssen ausgefüllt sein!';
    } else {
        $result = createLobby($conn, $lobbyCode, $rounds, $timeLimit);
        if ($result) {
            // Weiterleitung zur Lobby-Startseite
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
    <link rel="stylesheet" href="style.css">
</head>

<body style="padding-top: 70px;">
    <!-- Navbar -->
    <?php require 'navbar.php'; ?>

    <div class="container">
        <div class="play-container">
            <h1 class="text-center">Lobby Erstellen</h1>
            <h4 class="text-center">Stelle hier alle Einstellungen ein für deine Lobby!</h4>

            <!-- Fehleranzeige -->
            <?php if (isset($errorMessage)) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>

            <!-- Lobby Erstellungsformular -->
            <form method="POST">
                <div class="d-flex flex-column align-items-center gap-3">
                    <!-- Lobby Code -->
                    <div class="code-container w-100">
                        <h1 class="mb-3 text-center">Code</h1>
                        <div class="lobby-code-container text-center">
                            <input type="text" placeholder="XXXX" class="lobby-code-input" maxlength="4"
                                   value="<?php echo $lobbyCode; ?>" readonly name="lobbyCode">
                        </div>
                    </div>

                    <!-- Runden Auswahl -->
                    <div class="code-container w-100">
                        <h1 class="text-center">RUNDEN:</h1>
                        <div class="option-container d-flex justify-content-center">
                            <div class="option">
                                <input type="radio" id="round3" name="rounds" value="3" checked>
                                <label for="round3">3</label>
                            </div>
                            <div class="option mx-3">
                                <input type="radio" id="round5" name="rounds" value="5">
                                <label for="round5">5</label>
                            </div>
                            <div class="option">
                                <input type="radio" id="round10" name="rounds" value="10">
                                <label for="round10">10</label>
                            </div>
                        </div>
                    </div>

                    <!-- Zeitlimit -->
                    <div class="code-container w-100">
                        <h1 class="text-center">ZEITLIMIT PRO RUNDE</h1>
                        <div class="time-input-container text-center">
                            <input type="number" id="timeInput" placeholder="max: 120sec" oninput="validateSeconds(this)"
                                   min="0" max="120" name="timeLimit" style="width:100px;">
                        </div>
                    </div>

                    <!-- Start Button -->
                    <button type="submit" class="start-button btn btn-warning w-50 mt-4">START</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
