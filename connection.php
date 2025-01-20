<?php
$servername = "kremsguesser.duckdns.org"; // Muss regelmäßig geupdatet werden
$username = "kremsguesser";
$password = "123mysql"; // Ersetze xxx mit deinem Passwort
$dbname = "kremsguesserdb";

// Pfad zum SSL-Zertifikat

// Initialisiere MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Verbindung überprüfen
if ($conn->connect_error) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
}

?>
