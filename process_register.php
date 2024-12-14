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

    $errorMessage = '';

    // Validierung
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errorMessage = "Alle Felder müssen ausgefüllt werden.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Ungültige E-Mail-Adresse.";
    } elseif ($password !== $confirmPassword) {
        $errorMessage = "Die Passwörter stimmen nicht überein.";
    } else {
        // Prüfen, ob die E-Mail-Adresse oder der Benutzername bereits registriert ist
        $sql_check_email = "SELECT id FROM login WHERE Email = ?";
        $stmt = $conn->prepare($sql_check_email);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errorMessage = "Diese E-Mail-Adresse ist bereits registriert.";
        } else {
            $sql_check_name = "SELECT id FROM login WHERE username = ?";
            $stmt = $conn->prepare($sql_check_name);
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $errorMessage = "Der Name ist bereits registriert.";
            }
        }
    }

    // Wenn keine Fehler vorliegen, Benutzer registrieren
    if (empty($errorMessage)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $sql_insert = "INSERT INTO login (username, email, password, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("sss", $name, $email, $passwordHash);

        if ($stmt->execute()) {
            header("Location: register.php?status=success");
            exit();
        } else {
            $errorMessage = "Es gab einen Fehler bei der Registrierung. Bitte versuche es erneut.";
        }
    }

    // Verbindung schließen
    $stmt->close();
    $conn->close();
}
?>