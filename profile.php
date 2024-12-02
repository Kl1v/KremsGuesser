<?php
session_start();
require 'connection.php';

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) { // Angenommen, user_id wird in der Session gespeichert
    header('Location: login.php'); // Umleitung, falls der Benutzer nicht eingeloggt ist
    exit();
}

// Benutzerdaten aus der Session holen
$userId = $_SESSION['user_id'];

?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - KremsGuesser</title>
    <link rel="stylesheet" href="stylemain.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
            width: 100%;
            max-width: 400px;
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

        h1,
        h4 {
            text-align: center;
        }

        .profile-img {
            border-radius: 50%;
            margin-bottom: 15px;
        }

        .link-edit {
            color: white;
            text-decoration: underline;
            cursor: pointer;
        }

        .link-edit:hover {
            color: #FFC107;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <?php require 'navbar.php'; ?>

    <!-- Profilbereich -->
    <div class="container d-flex flex-column align-items-center justify-content-center">
        <div class="card text-center">
            <div>
                <img src="img/benutzerbild.png" height="100px" width="100px" alt="Benutzer Bild" class="profile-img">
            </div>
            <h1>Willkommen!</h1>
            <h4>Deine Profilinformationen</h4>

            <!-- Erfolgs- oder Fehlermeldung -->
            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php elseif (isset($errorMessage)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <!-- Anzeige der Benutzerdaten -->
            <form method="POST" action="">
                <div class="mb-3">
                    <strong>Name:</strong>
                    <input 
                        type="text" 
                        name="username" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($login['username']); ?>" 
                        required>
                </div>
                <div class="mb-3">
                    <strong>Email:</strong>
                    <input 
                        type="email" 
                        name="email" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($login['email']); ?>" 
                        required>
                </div>
                <div class="mb-3">
                    <strong>Passwort:</strong>
                    <input 
                        type="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Neues Passwort (optional)">
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-warning">Ändern</button>
                    <a href="logout.php" class="btn btn-danger">Abmelden</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>


</html>
