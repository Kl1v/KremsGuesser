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
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KremsGuesser - Registrieren</title>
    <link rel="stylesheet" href="stylemain.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .password-container {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 18px;
        color: #6c757d;
    }

    .password-toggle:hover {
        color: #495057;
    }

    body {
        background: linear-gradient(135deg, #240046, #3C096C);
        color: white;
        font-family: Arial, sans-serif;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0;
    }

    .card {
        background: #f8f9fa;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        padding: 20px;
    }

    .btn-warning {
        background: #FFC107;
        border: none;
        border-radius: 20px;
        font-weight: bold;
        padding: 10px 20px;
    }

    .btn-warning:hover {
        background: #e0a800;
    }

    .link-login {
        color: white;
        text-decoration: underline;
        cursor: pointer;
    }

    h1,
    h4 {
        text-align: center;
    }

    .error-message {
        color: red;
        font-size: 16px;
        text-align: center;
        margin-bottom: 20px;
    }

    .success-message {
        color: green;
        font-size: 16px;
        text-align: center;
        margin-bottom: 20px;
    }
    </style>
</head>

<body>

    <!-- Navbar -->
    <?php require 'navbar.php'; ?>

    <!-- Registrierung Bereich -->
    <div class="container d-flex flex-column align-items-center justify-content-center">
        <div class="card text-center" style="width: 100%; max-width: 400px;">
            <div>
                <img src="img/benutzerbild.png" height="100px" width="100px" alt="Benutzer Bild" class="rounded-circle">
            </div>
            <h1>REGISTRIEREN</h1>
            <h4>ERSTELLE EINEN ACCOUNT</h4>

            <!-- Fehlermeldung anzeigen -->
            <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
            <?php endif; ?>

            <!-- Statusmeldungen -->
            <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <p class="success-message">Registrierung erfolgreich!</p>
            <?php endif; ?>

            <form action="register.php" method="POST" id="registerForm">
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Name" required>
                </div>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="E-Mail" required>
                </div>
                <div class="mb-3 password-container">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Passwort"
                        required>
                    <span class="password-toggle" onclick="togglePassword('password')">👁️</span>
                </div>
                <div class="mb-3 password-container">
                    <input type="password" name="confirmPassword" id="confirmPassword" class="form-control"
                        placeholder="Passwort wiederholen" required>
                    <span class="password-toggle" onclick="togglePassword('confirmPassword')">👁️</span>
                </div>
                <button type="submit" class="btn btn-warning">Registrieren</button>
            </form>
        </div>

        <!-- Link zur Anmeldung -->
        <div class="text-center mt-4">
            <p>Hast du schon einen Account? <a href="login.php" class="link-login">Melde dich hier an!</a></p>
        </div>
    </div>

    <script>
    function togglePassword(inputId) {
        const passwordField = document.getElementById(inputId);
        const toggleIcon = passwordField.nextElementSibling;
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.textContent = '🙈'; // Symbol für "Verstecken"
        } else {
            passwordField.type = 'password';
            toggleIcon.textContent = '👁️'; // Symbol für "Anzeigen"
        }
    }

    document.getElementById('registerForm').addEventListener('submit', function(event) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const errorMessage = document.getElementById('errorMessage');

        if (password !== confirmPassword) {
            errorMessage.style.display = 'block'; // Zeige Fehlermeldung
            event.preventDefault(); // Verhindere das Absenden des Formulars
        } else {
            errorMessage.style.display = 'none'; // Verstecke Fehlermeldung
        }
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>