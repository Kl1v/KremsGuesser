<?php
require 'connection.php';

// Lobbys löschen, die älter als 30 Minuten sind
$stmt = $conn->prepare("DELETE FROM lobbies WHERE TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 30");
$stmt->execute();
$stmt->close();

?>