<?php
require 'connection.php'; // Verbindung zur Datenbank

// Eingabedaten empfangen
$data = json_decode(file_get_contents('php://input'), true);
$lobbyCode = $data['lobbyCode'];

// Überprüfen, ob der Host die Anfrage gesendet hat
$stmt = $conn->prepare("SELECT * FROM players WHERE lobby_code = ? AND is_host = 1");
$stmt->bind_param("s", $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();
$host = $result->fetch_assoc();
$stmt->close();

if (!$host) {
    echo json_encode(['success' => false, 'message' => 'Host nicht gefunden.']);
    exit;
}

// Spiel starten: Hier könnte Logik zum Spielstart hinzugefügt werden
// Zum Beispiel, eine neue Spielinstanz zu erstellen oder den Status zu ändern

echo json_encode(['success' => true]);
?>