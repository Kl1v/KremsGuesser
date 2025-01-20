<?php
require 'connection.php';

$lobbyCode = $_GET['lobbyCode'];
$round = $_GET['round'];

$stmt = $conn->prepare("SELECT started_at FROM locations WHERE lobby_code = ? AND round = ?");
$stmt->bind_param("si", $lobbyCode, $round);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row && $row['started_at'] !== null) {
    echo json_encode(['started' => true]);
} else {
    echo json_encode(['started' => false]);
}
?>
