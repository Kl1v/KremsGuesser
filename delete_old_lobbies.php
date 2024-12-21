<?php
require 'connection.php';

// Lobbys löschen, die älter als 30 Minuten sind
$stmt = $conn->prepare("DELETE FROM lobbies WHERE TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 30");
$stmt->execute();
$stmt->close();

// Verknüpfte Daten aus anderen Tabellen löschen, falls nötig (z. B. Spieler, Guesses)
$conn->query("DELETE FROM guesses WHERE lobby_id NOT IN (SELECT id FROM lobbies)");
$conn->query("DELETE FROM players WHERE lobby_code NOT IN (SELECT code FROM lobbies)");

?>