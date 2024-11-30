<?php
require 'connection.php'; // Verbindung zur Datenbank

// Eingabedaten empfangen
$data = json_decode(file_get_contents('php://input'), true);
$playerId = $data['playerId'];
$lobbyCode = $data['lobbyCode'];

// Prüfen, ob der Spieler der Host ist
$stmt = $conn->prepare("SELECT * FROM players WHERE id = ? AND lobby_code = ?");
$stmt->bind_param("is", $playerId, $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();
$player = $result->fetch_assoc();
$stmt->close();

if (!$player) {
    echo json_encode(['success' => false, 'message' => 'Spieler nicht gefunden.']);
    exit;
}

// Prüfen, ob der Host den Spieler kicken möchte
$stmt = $conn->prepare("SELECT * FROM players WHERE lobby_code = ? AND is_host = 1");
$stmt->bind_param("s", $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();
$host = $result->fetch_assoc();
$stmt->close();

if ($player['id'] === $host['id']) {
    echo json_encode(['success' => false, 'message' => 'Der Host kann nicht gekickt werden.']);
    exit;
}

// Spieler aus der Lobby entfernen
$stmt = $conn->prepare("DELETE FROM players WHERE id = ? AND lobby_code = ?");
$stmt->bind_param("is", $playerId, $lobbyCode);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Fehler beim Kicken des Spielers.']);
}
?>