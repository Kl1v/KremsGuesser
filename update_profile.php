<?php
session_start();
require 'connection.php';

global $conn;

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($username) || empty($email)) {
        $_SESSION['errorMessage'] = 'Name und E-Mail dürfen nicht leer sein.';
        header('Location: profile.php');
        exit();
    }

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE login SET username = ?, email = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $username, $email, $hashedPassword, $userId);
    } else {
        $query = "UPDATE login SET username = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $username, $email, $userId);
        $_SESSION['user_name'] = $username;
    }

    if ($stmt->execute()) {
        $_SESSION['successMessage'] = 'Deine Profilinformationen wurden erfolgreich aktualisiert.';
    } else {
        $_SESSION['errorMessage'] = 'Fehler beim Aktualisieren der Daten: ' . $stmt->error;
    }

    $stmt->close();
    header('Location: profile.php');
    exit();
} else {
    $_SESSION['errorMessage'] = 'Ungültige Anfrage.';
    header('Location: profile.php');
    exit();
}
?>