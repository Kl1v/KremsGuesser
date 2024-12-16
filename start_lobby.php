<?php
require 'connection.php'; // Verbindung zur Datenbank
session_start(); // Startet die Session

if (!isset($_SESSION['user_name'])) {
    // Benutzer ist nicht angemeldet, leitet auf die Login-Seite weiter
    header('Location: index.php');
    exit;
}

// Funktion, um die Spieler in der Lobby anzuzeigen
function getPlayersInLobby($conn, $lobbyCode) {
    $stmt = $conn->prepare("SELECT * FROM players WHERE lobby_code = ?");
    $stmt->bind_param("s", $lobbyCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $players = [];
    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
    }
    $stmt->close();
    return $players;
}

// Funktion, um den Host der Lobby zu finden
function getHostOfLobby($conn, $lobbyCode) {
    $stmt = $conn->prepare("SELECT * FROM players WHERE lobby_code = ? AND is_host = 1");
    $stmt->bind_param("s", $lobbyCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $host = $result->fetch_assoc();
    $stmt->close();
    return $host;
}

// Funktion, um die Lobby und die Spieler zu löschen
function deleteLobby($conn, $lobbyCode) {
    $conn->begin_transaction(); // Transaktion starten
    try {
        // Lösche alle Spieler der Lobby
        $stmt = $conn->prepare("DELETE FROM players WHERE lobby_code = ?");
        $stmt->bind_param("s", $lobbyCode);
        $stmt->execute();
        $stmt->close();

        // Lösche die Lobby
        $stmt = $conn->prepare("DELETE FROM lobbies WHERE code = ?");
        $stmt->bind_param("s", $lobbyCode);
        $stmt->execute();
        $stmt->close();

        $conn->commit(); // Transaktion bestätigen
    } catch (Exception $e) {
        $conn->rollback(); // Transaktion zurückrollen
        die("Fehler beim Löschen der Lobby: " . $e->getMessage());
    }
}

// Funktion, um einen Spieler zu entfernen
function removePlayer($conn, $username) {
    $stmt = $conn->prepare("DELETE FROM players WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
}

// Funktion zum Starten des Spiels und Weiterleiten aller Spieler
function startGame($conn, $lobbyCode) {
    // Holen der ersten Runde-Location
    $stmt = $conn->prepare("SELECT latitude, longitude FROM locations WHERE lobby_code = ? ORDER BY round ASC LIMIT 1");
    $stmt->bind_param("s", $lobbyCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $location = $result->fetch_assoc();
    $stmt->close();

    // Speichern des Spielstatus (Spiel ist gestartet)
    $stmt = $conn->prepare("UPDATE lobbies SET is_game_started = 1 WHERE code = ?");
    $stmt->bind_param("s", $lobbyCode);
    $stmt->execute();
    $stmt->close();

    // Weiterleitung der Spieler zur Spiel-Seite
    $players = getPlayersInLobby($conn, $lobbyCode);
    foreach ($players as $player) {
        // Weiterleitung für alle Spieler
        header("Location: game_multiplayer.php?code=$lobbyCode&runde=1");
        exit;
    }

    return $location;
}

// AJAX-Request: Spieler in der Lobby abrufen
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'get_players' && isset($_GET['code'])) {
        $lobbyCode = $_GET['code'];
        $players = getPlayersInLobby($conn, $lobbyCode);
        echo json_encode($players);
    }

    if ($action === 'kick_player' && isset($_POST['username'])) {
        $username = $_POST['username'];
        removePlayer($conn, $username);
        echo json_encode(["status" => "success"]);
    }

    if ($action === 'check_game_started' && isset($_GET['code'])) {
        $lobbyCode = $_GET['code'];
        $stmt = $conn->prepare("SELECT is_game_started FROM lobbies WHERE code = ?");
        $stmt->bind_param("s", $lobbyCode);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        echo json_encode($data);
    }

    exit;
}

// Lobby-Code aus der URL holen
if (isset($_GET['code'])) {
    $lobbyCode = $_GET['code'];
} else {
    die("Kein Lobby-Code übergeben.");
}

// Spieler und Host abrufen
$players = getPlayersInLobby($conn, $lobbyCode);
$host = getHostOfLobby($conn, $lobbyCode);

// Host-Funktion zum Schließen der Lobby
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['closeLobby'])) {
    deleteLobby($conn, $lobbyCode);
    header("Location: index.php?message=Lobby wurde erfolgreich geschlossen.");
    exit;
}

// Spieler-Funktion zum Verlassen der Lobby
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leaveLobby'])) {
    $stmt = $conn->prepare("DELETE FROM players WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['user_name']);
    $stmt->execute();
    $stmt->close();
    
    header("Location: index.php");
    exit;
}

// Spiel starten, wenn der Host den Button klickt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['startGame'])) {
    $location = startGame($conn, $lobbyCode);
    // Weiterleitung erfolgt nun in der startGame-Funktion für alle Spieler
    exit;  // Verhindert doppelte Ausführung der Weiterleitung
}

?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lobby - <?php echo htmlspecialchars($lobbyCode); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .card {
        border: none;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .lobby-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .player-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .btn-close-lobby {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-close-lobby:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }

    body {
        background-image: url('img/Big-Map.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    body::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: -1;
    }

    .card {
        color: white;
        border: none;
        background-color: #2e003e;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        min-height: 93vh;
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php require 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card p-4">
                    <div class="lobby-header">
                        <h1>Lobby: <strong><?php echo htmlspecialchars($lobbyCode); ?></strong></h1>
                        <p>Host: <strong><?php echo htmlspecialchars($host['username']); ?></strong></p>
                    </div>

                    <h4 class="mb-4">Spieler in dieser Lobby:</h4>
                    <ul class="list-group player-list" id="playerList">
                        <!-- Spieler werden hier dynamisch mit JavaScript geladen -->
                    </ul>

                    <div class="mt-4 text-center">
                        <!-- Nur der Host kann die Lobby schließen -->
                        <?php if ($_SESSION['user_name'] === $host['username']): ?>
                        <form method="POST" onsubmit="return confirm('Möchtest du die Lobby wirklich schließen?');">
                            <button type="submit" name="closeLobby" class="btn btn-close-lobby w-40 mb-2">
                                Lobby schließen
                            </button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Möchtest du das Spiel wirklich starten?');">
                            <button type="submit" name="startGame" class="btn btn-success w-40 mb-2">
                                Spiel starten
                            </button>
                        </form>
                        <?php endif; ?>

                        <form method="POST">
                            <button type="submit" name="leaveLobby" class="btn btn-warning w-40">
                                Lobby verlassen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Spieler laden
    const lobbyCode = "<?php echo htmlspecialchars($lobbyCode); ?>";

    function loadPlayers() {
        fetch(`start_lobby.php?action=get_players&code=${lobbyCode}`)
            .then(response => response.json())
            .then(players => {
                const playerList = document.getElementById('playerList');
                playerList.innerHTML = ''; // Leeren der Liste

                players.forEach(player => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center';
                    li.innerHTML = `${player.username} ${player.is_host == 1 ? '(Host)' : ''}`;
                    playerList.appendChild(li);
                });
            });
    }

    loadPlayers();
    setInterval(loadPlayers, 000); // Alle 5 Sekunden die Spieler aktualisieren

    // Überprüfen, ob das Spiel gestartet wurde
    setInterval(() => {
        fetch(`start_lobby.php?action=check_game_started&code=${lobbyCode}`)
            .then(response => response.json())
            .then(data => {
                if (data.is_game_started === 1) {
                    // Weiterleitung zur game_multiplayer.php mit dem Lobby-Code
                    location.href = `game_multiplayer.php?code=${lobbyCode}`;
                }
            });
    }, 2000); // Alle 2 Sekunden prüfen
    </script>
</body>

</html>