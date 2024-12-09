<?php
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['lobbyCode'])) {
        $lobbyCode = $data['lobbyCode'];

        // Einträge löschen
        $stmt1 = $conn->prepare("DELETE FROM guesses WHERE lobby_id = ?");
        $stmt1->bind_param("s", $lobbyCode);
        $stmt1->execute();
        $stmt1->close();

        // Lobby löschen
        $stmt2 = $conn->prepare("DELETE FROM lobbies WHERE code = ?");
        $stmt2->bind_param("s", $lobbyCode);
        $stmt2->execute();
        $stmt2->close();

        echo json_encode(['success' => true]);
    }
}
?>
