<?php
require 'connection.php'; // Verbindung zur Datenbank
session_start(); // Startet die Session

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
        // Lösche die Lobby
        $stmt = $conn->prepare("DELETE FROM lobbies WHERE code = ?");
        $stmt->bind_param("s", $lobbyCode);
        $stmt->execute();
        $stmt->close();

        // Lösche die Spieler aus der Lobby
        $stmt = $conn->prepare("DELETE FROM players WHERE lobby_code = ?");
        $stmt->bind_param("s", $lobbyCode);
        $stmt->execute();
        $stmt->close();

        $conn->commit(); // Transaktion bestätigen
    } catch (Exception $e) {
        $conn->rollback(); // Transaktion zurückrollen
        die("Fehler beim Löschen der Lobby: " . $e->getMessage());
    }
}

// AJAX-Request: Spieler in der Lobby abrufen
if (isset($_GET['action']) && $_GET['action'] === 'get_players') {
    if (isset($_GET['code'])) {
        $lobbyCode = $_GET['code'];
        $players = getPlayersInLobby($conn, $lobbyCode);
        echo json_encode($players);
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
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lobby - <?php echo htmlspecialchars($lobbyCode); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
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
                                <button type="submit" name="closeLobby" class="btn btn-close-lobby w-100 mb-2">
                                    Lobby schließen
                                </button>
                            </form>
                        <?php endif; ?>
                        <button class="btn btn-success w-100" onclick="startGame()">Spiel starten</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const lobbyCode = "<?php echo htmlspecialchars($lobbyCode); ?>";

        // Funktion, um Spieler aus der Datenbank abzurufen und die Liste zu aktualisieren
        function loadPlayers() {
            fetch(`start_lobby.php?action=get_players&code=${lobbyCode}`)
                .then(response => response.json())
                .then(players => {
                    const playerList = document.getElementById("playerList");
                    playerList.innerHTML = ""; // Alte Liste leeren

                    players.forEach(player => {
                        const listItem = document.createElement("li");
                        listItem.className = "list-group-item d-flex justify-content-between align-items-center";
                        listItem.textContent = player.username;

                        if (player.is_host) {
                            const badge = document.createElement("span");
                            badge.className = "badge bg-primary";
                            badge.textContent = "Host";
                            listItem.appendChild(badge);
                        }

                        playerList.appendChild(listItem);
                    });
                })
                .catch(error => console.error("Fehler beim Laden der Spieler:", error));
        }

        // Funktion zum Starten des Spiels
        function startGame() {
            alert("Das Spiel startet bald!");
            // Hier könnte die Weiterleitung zur Spielseite ergänzt werden.
        }

        // Spieler-Liste jede Sekunde aktualisieren
        setInterval(loadPlayers, 1000);

        // Initiales Laden der Spieler-Liste
        loadPlayers();
    </script>
</body>

</html>
