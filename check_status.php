<?php
require 'connection.php';

if (!isset($_GET['lobbyCode'])) {
    echo json_encode(['error' => 'Kein Lobby-Code übergeben.']);
    exit;
}

$lobbyCode = $_GET['lobbyCode'];

// 1. Hole die Anzahl der Spieler in der Lobby
$playerCountQuery = $conn->prepare("
    SELECT COUNT(*) as player_count
    FROM players
    WHERE lobby_code = ?
");
$playerCountQuery->bind_param("s", $lobbyCode);
$playerCountQuery->execute();
$result = $playerCountQuery->get_result();
$playerCount = $result->fetch_assoc()['player_count'];

// 2. Hole die aktuelle Runde der Lobby
$currentRoundQuery = $conn->prepare("
    SELECT rounds
    FROM lobbies
    WHERE code = ?
");
$currentRoundQuery->bind_param("s", $lobbyCode);
$currentRoundQuery->execute();
$result = $currentRoundQuery->get_result();
$currentRound = $result->fetch_assoc()['rounds'];

// 3. Prüfe, ob alle Spieler ihre Guesses abgegeben haben
$guessesQuery = $conn->prepare("
    SELECT COUNT(*) as guess_count
    FROM guesses
    WHERE lobby_id = ? AND runde = ?
");
$guessesQuery->bind_param("si", $lobbyCode, $currentRound);
$guessesQuery->execute();
$result = $guessesQuery->get_result();
$guessCount = $result->fetch_assoc()['guess_count'];

// 4. Prüfe, ob die Zeit abgelaufen ist
$timeCheckQuery = $conn->prepare("
    SELECT is_game_started, time_limit, TIMESTAMPDIFF(SECOND, created_at, NOW()) as elapsed_time
    FROM lobbies
    WHERE code = ?
");
$timeCheckQuery->bind_param("s", $lobbyCode);
$timeCheckQuery->execute();
$result = $timeCheckQuery->get_result();
$timeData = $result->fetch_assoc();

$isGameStarted = $timeData['is_game_started'];
$timeLimit = $timeData['time_limit'];
$elapsedTime = $timeData['elapsed_time'];

$timeExpired = $elapsedTime >= $timeLimit;

// 5. Rückgabe der Statusinformationen
$response = [
    'allGuessesSubmitted' => $guessCount >= $playerCount,
    'timeExpired' => $timeExpired,
];
echo json_encode($response);
?>
