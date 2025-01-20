<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_name'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lobbyCode = $_POST['lobbyCode'];
    $currentRound = (int)$_POST['currentRound'];
    $nextRound = $currentRound + 1;

    // Überprüfen, ob der Benutzer Host ist
    $stmt = $conn->prepare("SELECT is_host FROM players WHERE username = ? AND lobby_code = ?");
    $stmt->bind_param("ss", $_SESSION['user_name'], $lobbyCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row || !$row['is_host']) {
        die("Unbefugter Zugriff: Nur der Host kann die nächste Runde starten.");
    }

    // Maximale Rundenanzahl abrufen
    $stmt = $conn->prepare("SELECT rounds FROM lobbies WHERE code = ?");
    $stmt->bind_param("s", $lobbyCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $lobbyData = $result->fetch_assoc();
    $stmt->close();

    $maxRounds = $lobbyData['rounds'];

    // Prüfen, ob die maximale Anzahl an Runden erreicht wurde
    if ($nextRound > $maxRounds) {
        header("Location: final_results.php?lobbyCode=$lobbyCode");
        exit;
    } else {
        // `started_at` für die nächste Runde setzen
        $stmt = $conn->prepare("UPDATE locations SET started_at = NOW() WHERE lobby_code = ? AND round = ?");
        $stmt->bind_param("si", $lobbyCode, $nextRound);
        $stmt->execute();
        $stmt->close();

        // Leite den Host zur nächsten Runde weiter
        header("Location: game_multiplayer.php?code=$lobbyCode&runde=$nextRound");
        exit;
    }
}
?>
