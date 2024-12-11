<?php
require 'connection.php';

if (!isset($_GET['lobbyCode'])) {
    die(json_encode(['error' => 'Kein Lobby-Code übergeben.']));
}

$lobbyCode = $_GET['lobbyCode'];

// Überprüfen, ob alle Spieler ihre Guesses abgegeben haben
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS remaining 
     FROM players p
     LEFT JOIN guesses g ON p.username = g.spielername AND g.lobby_id = ?
     WHERE p.lobby_code = ? AND g.id IS NULL"
);
$stmt->bind_param("ss", $lobbyCode, $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$remainingGuesses = $row['remaining'];
$stmt->close();

// Überprüfen, ob die Zeit abgelaufen ist
$stmt = $conn->prepare(
    "SELECT start_time FROM lobbies WHERE lobby_code = ?"
);
$stmt->bind_param("s", $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$startTime = strtotime($row['start_time']);
$timeExpired = (time() - $startTime) > 20; // 20 Sekunden Timeout
$stmt->close();

// Rückgabe des Status
$response = [
    'allGuessesSubmitted' => $remainingGuesses === 0,
    'timeExpired' => $timeExpired
];

echo json_encode($response);
?>