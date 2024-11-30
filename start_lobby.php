<?php
require 'connection.php'; // Verbindung zur Datenbank

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

// Funktion, um den Host zu finden
function getHostOfLobby($conn, $lobbyCode) {
    $stmt = $conn->prepare("SELECT * FROM players WHERE lobby_code = ? AND is_host = 1");
    $stmt->bind_param("s", $lobbyCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $host = $result->fetch_assoc();
    $stmt->close();
    return $host;
}

// Lobby-Code aus der URL (oder POST) holen
if (isset($_GET['code'])) {
    $lobbyCode = $_GET['code'];
} else {
    // Fehlerbehandlung, wenn kein Lobby-Code übergeben wurde
    die("Kein Lobby-Code übergeben.");
}

// Hole die Spieler in dieser Lobby
$players = getPlayersInLobby($conn, $lobbyCode);
$host = getHostOfLobby($conn, $lobbyCode);

?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lobby - <?php echo htmlspecialchars($lobbyCode); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="padding-top: 70px;">
    <!-- Navbar -->
    <?php require 'navbar.php'; ?>

    <!-- Content Section -->
    <div class="container">
        <h1>Lobby: <?php echo htmlspecialchars($lobbyCode); ?></h1>
        <h3>Host: <?php echo htmlspecialchars($host['username']); ?></h3>

        <h4>Spieler in dieser Lobby:</h4>
        <ul class="list-group">
            <?php foreach ($players as $player): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php echo htmlspecialchars($player['username']); ?>
                <?php if ($player['username'] !== $host['username']): ?>
                <button class="btn btn-danger btn-sm"
                    onclick="kickPlayer('<?php echo $player['id']; ?>')">Kicken</button>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- Weitere Optionen für den Host -->
        <div class="mt-3">
            <button class="btn btn-success" id="startGameBtn" onclick="startGame()">Spiel starten</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Funktion zum Kicken eines Spielers
    function kickPlayer(playerId) {
        if (confirm('Bist du sicher, dass du diesen Spieler kicken möchtest?')) {
            fetch('kick_player.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        playerId: playerId,
                        lobbyCode: '<?php echo $lobbyCode; ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Spieler wurde gekickt!');
                        location.reload(); // Seite neu laden, um die Änderung zu sehen
                    } else {
                        alert('Fehler: ' + data.message);
                    }
                })
                .catch(error => console.error('Fehler beim Kicken des Spielers:', error));
        }
    }

    // Funktion zum Starten des Spiels
    function startGame() {
        fetch('start_game.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    lobbyCode: '<?php echo $lobbyCode; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'game.php?code=' + '<?php echo $lobbyCode; ?>'; // Weiterleiten zum Spiel
                } else {
                    alert('Fehler: ' + data.message);
                }
            })
            .catch(error => console.error('Fehler beim Starten des Spiels:', error));
    }
    </script>
</body>

</html>