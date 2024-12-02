<?php
require 'connection.php';
session_start();

if (!isset($_GET['lobbyCode'])) {
    die('Lobby-Code nicht angegeben.');
}

$lobbyCode = $_GET['lobbyCode'];

// Überprüfen, ob der aktuelle Benutzer Host der Lobby ist
$stmt = $conn->prepare("SELECT is_host FROM players WHERE username = ? AND lobby_code = ?");
$stmt->bind_param("ss", $_SESSION['user_name'], $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0 || !$result->fetch_assoc()['is_host']) {
    die('Du bist nicht der Host dieser Lobby.');
}

// Lösche Lobby und alle zugehörigen Einträge
$stmt = $conn->prepare("DELETE FROM lobbies WHERE code = ?");
$stmt->bind_param("s", $lobbyCode);

if ($stmt->execute()) {
    header("Location: index.php");
    exit;
} else {
    echo 'Fehler beim Schließen der Lobby.';
}
?>
