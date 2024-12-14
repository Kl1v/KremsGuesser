<?php
// Verbindung zur Datenbank einbinden
require 'connection.php';

// Überprüfung, ob das Formular abgesendet wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eingabewerte erfassen und bereinigen
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validierung
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        die("Alle Felder müssen ausgefüllt werden.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Ungültige E-Mail-Adresse.");
    }

    if ($password !== $confirmPassword) {
        die("Die Passwörter stimmen nicht überein.");
    }

    // Prüfen, ob die E-Mail-Adresse bereits registriert ist
    $sql_check_email = "SELECT id FROM login WHERE Email = ?";
    $stmt = $conn->prepare($sql_check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        die("Diese E-Mail-Adresse ist bereits registriert.");
    }
    $sql_check_name = "SELECT id FROM login WHERE username = ?";
    $stmt = $conn->prepare($sql_check_name);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        die("Der Name ist bereits registriert.");
    }

    // Passwort hashen
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Benutzer registrieren
    $sql_insert = "INSERT INTO login (username, email, password, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("sss", $name, $email, $passwordHash);

    if ($stmt->execute()) {
        // Erfolgreich registriert
        header("Location: register.php?status=success");
        exit();
    } else {
        // Fehler bei der Registrierung
        header("Location: register.php?status=error");
        exit();
    }

    // Verbindung schließen
    $stmt->close();
    $conn->close();
}
?>