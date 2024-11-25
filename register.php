<?php
session_start();
// Status aus der URL abfragen
$status = isset($_GET['status']) ? $_GET['status'] : '';
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
        h1, h4 {
            text-align: center;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: -10px;
            margin-bottom: 10px;
            display: none;
        }
        .success-message {
            color: green;
            font-size: 16px;
            text-align: center;
            margin-bottom: 20px;
        }
        .error-message-server {
            color: red;
            font-size: 16px;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top" style="background-color: #1e0028;">
    <div class="container-fluid">
        <a class="navbar-brand" href="#" style="color: #FFD700; font-weight: bold; font-size: 1.5rem;">KREMSGUESSER</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item ms-1 mt-2">
                    <a class="nav-link text-white" aria-current="page" href="play.php"><h5>Play</h5></a>
                </li>
                <li class="nav-item ms-1 mt-2">
                    <a class="nav-link text-white" href="index.php"><h5>Home</h5></a>
                </li>
                <li class="nav-item ms-1 mt-2">
                    <a class="nav-link text-white" href="scoreboard.php"><h5>Scoreboard</h5></a>
                </li>
                <li class="nav-item ms-3">
<<<<<<< HEAD
                        <?php if (isset($_SESSION['user_name'])): ?>
                            <!-- Eingeloggt: Benutzername anzeigen -->
                            <button type="button" class="btn btn-warning d-flex align-items-center" style="border-radius: 20px; font-weight: bold;">
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </button>
                        <?php else: ?>
                            <!-- Nicht eingeloggt: Login anzeigen -->
                            <a href="login.php">
                                <button type="button" class="btn btn-warning d-flex align-items-center" style="border-radius: 20px; font-weight: bold;">
                                    Login
                                    <img src="img/benutzerbild.png" alt="User Image" width="20" height="20" class="ms-2">
                                </button>
                            </a>
                        <?php endif; ?>
                    </li>
=======
                    <a class="link-button" href="login.php">
                        <button type="button" class="btn btn-warning d-flex align-items-center" style="border-radius: 20px; font-weight: bold;">
                            Login
                            <img src="img/benutzerbild.png" alt="User Image" width="20" height="20" class="ms-2">
                        </button>
                    </a>
                </li>
>>>>>>> 65f6cdf9d0ffe30601bea1f4c0f2cac9659579e3
            </ul>
        </div>
    </div>
</nav>

<!-- Registrierung Bereich -->
<div class="container d-flex flex-column align-items-center justify-content-center">
    <div class="card text-center" style="width: 100%; max-width: 400px;">
        <div>
            <img src="img/benutzerbild.png" height="100px" width="100px" alt="Benutzer Bild" class="rounded-circle">
        </div>
        <h1>REGISTRIEREN</h1>
        <h4>ERSTELLE EINEN ACCOUNT</h4>

        <!-- Statusmeldungen -->
        <?php if ($status === 'success'): ?>
            <p class="success-message">Registrierung erfolgreich!</p>
        <?php elseif ($status === 'error'): ?>
            <p class="error-message-server">Es gab einen Fehler bei der Registrierung. Bitte versuche es erneut.</p>
        <?php endif; ?>

        <form action="process_register.php" method="POST" id="registerForm">
            <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Name" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="E-Mail" required>
            </div>
            <div class="mb-3 password-container">
                <input type="password" name="password" id="password" class="form-control" placeholder="Passwort" required>
                <span class="password-toggle" onclick="togglePassword('password')">👁️</span>
            </div>
            <div class="mb-3 password-container">
                <input type="password" name="confirmPassword" id="confirmPassword" class="form-control" placeholder="Passwort wiederholen" required>
                <span class="password-toggle" onclick="togglePassword('confirmPassword')">👁️</span>
            </div>
            <p class="error-message" id="errorMessage">Die Passwörter stimmen nicht überein.</p>
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
