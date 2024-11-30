<?php
session_start();
require('connection.php'); // Verbindung zur Datenbank herstellen

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formulardaten abrufen
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Benutzer anhand der E-Mail suchen
    $stmt = $conn->prepare("SELECT id, username, password FROM login WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Passwort überprüfen
        if (password_verify($password, $user['password'])) {
            // Benutzer in die Session speichern
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];

            // Weiterleitung zur Startseite (oder Dashboard)
            header("Location: index.php");
            exit();
        } else {
            // Falsches Passwort
            header("Location: login.php?status=wrong_password");
            exit();
        }
    } else {
        // Benutzer nicht gefunden
        header("Location: login.php?status=user_not_found");
        exit();
    }
} else {
    // Wenn kein POST-Request, zurück zum Login
    header("Location: login.php");
    exit();
}
?>