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
    <!-- Navbar -->
    <?php require 'navbar.php'; ?>

    <!-- Content Section -->
    <div class="container">
        <div class="play-container">
            <h1>Multiplayer</h1>
            <h4>Jemanden einladen oder teilnehmen?</h4>
            <div class="d-flex flex-column align-items-center gap-3">
                <form action="join_lobby.php" method="get">
                    <button type="submit" class="btn-custom">Lobby beitreten</button>
                </form>
                <form action="create_lobby.php" method="get">
                    <button type="submit" class="btn-custom">Lobby erstellen</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>