<?php
session_start();
require 'connection.php';

// Lobby-Code aus der URL abrufen
if (isset($_GET['lobbyCode'])) {
    $lobbyCode = $_GET['lobbyCode'];
} else {
    die("Kein Lobby-Code übergeben.");
}

// Alle gespeicherten Guesse für die angegebene Lobby abfragen
$stmt = $conn->prepare(
    "SELECT g.runde, g.spielername, g.lat, g.lng, g.score 
     FROM guesses g
     JOIN players p ON g.lobby_id = p.lobby_code
     WHERE g.lobby_id = ? 
     ORDER BY g.runde ASC"
);
$stmt->bind_param("s", $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();

// Überprüfen, ob es Ergebnisse gibt
if ($result->num_rows == 0) {
    die("Keine Spielergebnisse gefunden.");
}

$guesses = [];
while ($row = $result->fetch_assoc()) {
    $guesses[] = $row;
}

$stmt->close();

// Überprüfen, wie viele Runden es gibt
$stmt = $conn->prepare("SELECT rounds FROM lobbies WHERE code = ?");
$stmt->bind_param("s", $lobbyCode);
$stmt->execute();
$stmt->bind_result($totalRounds);
$stmt->fetch();
$stmt->close();

// Wenn alle Runden gespielt sind, löschen wir die Einträge
if (count($guesses) / $totalRounds === 1) {
    // Löschen der Guesse-Einträge und der Locations
    $conn->begin_transaction();

    try {
        // Löschen der Guesse
        $stmt = $conn->prepare("DELETE FROM guesses WHERE lobby_id = ?");
        $stmt->bind_param("s", $lobbyCode);
        $stmt->execute();

        // Löschen der Locations
        $stmt = $conn->prepare("DELETE FROM locations WHERE lobby_code = ?");
        $stmt->bind_param("s", $lobbyCode);
        $stmt->execute();

        // Löschen der Lobby
        $stmt = $conn->prepare("DELETE FROM lobbies WHERE code = ?");
        $stmt->bind_param("s", $lobbyCode);
        $stmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Fehler beim Löschen der Lobby und Guesse: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ergebnisse für Lobby <?php echo htmlspecialchars($lobbyCode); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        table {
            margin-top: 20px;
            width: 100%;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
        <? require 'navbar.php'?>
<div class="container">
    <h1 class="my-4">Ergebnisse für Lobby: <?php echo htmlspecialchars($lobbyCode); ?></h1>

    <table class="table">
        <thead>
            <tr>
                <th>Runde</th>
                <th>Spielername</th>
                <th>Position (Lat/Lng)</th>
                <th>Punkte</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($guesses as $guess): ?>
                <tr>
                    <td><?php echo $guess['runde']; ?></td>
                    <td><?php echo htmlspecialchars($guess['spielername']); ?></td>
                    <td><?php echo $guess['lat'] . ', ' . $guess['lng']; ?></td>
                    <td><?php echo $guess['score']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mt-3">
        <a href="game.php?code=<?php echo $lobbyCode; ?>" class="btn btn-primary">Zurück zum Spiel</a>
    </div>
</div>

</body>
</html>
