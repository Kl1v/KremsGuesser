<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KremsGuesser</title>
    <link rel="stylesheet" href="stylemain.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding-top: 70px;">
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
                    <?php if (isset($_SESSION['user_name'])): ?>
                        <!-- Eingeloggt: Logout-Button anzeigen -->
                        <form action="logout.php" method="POST" style="display: inline;">
                            <button type="submit" class="btn btn-danger d-flex align-items-center" style="border-radius: 20px; font-weight: bold;">
                                Logout
                            </button>
                        </form>
                    <?php else: ?>
                        <!-- Nicht eingeloggt: Login-Button anzeigen -->
                        <a href="login.php" style="text-decoration: none;">
                            <button type="button" class="btn btn-warning d-flex align-items-center" style="border-radius: 20px; font-weight: bold;">
                                Login
                                <img src="img/benutzerbild.png" alt="User Image" width="20" height="20" class="ms-2">
                            </button>
                        </a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Content Section -->
<div class="container">
    <div class="play-container">
        <h1>Lobby beitreten</h1>
        <h4>Gib den Code der Lobby ein</h4>
        <div class="code-container mb-4">
            <h1 class="mb-3">Code</h1>
            <div class="lobby-code-container">
                <input type="number" placeholder="X X X X" maxlength="4" class="lobby-code-input" oninput="this.value=this.value.slice(0,4)">
            </div>
        </div>
        <div class="d-flex flex-column align-items-center gap-3">
            <button type="submit" class="btn-custom-scnd">Lobby beitreten</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
