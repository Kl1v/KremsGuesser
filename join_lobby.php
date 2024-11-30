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
            <h1>Lobby beitreten</h1>
            <h4>Gib den Code der Lobby ein</h4>
            <div class="code-container mb-4">
                <h1 class="mb-3">Code</h1>
                <div class="lobby-code-container">
                    <input type="number" placeholder="X X X X" maxlength="4" class="lobby-code-input"
                        oninput="this.value=this.value.slice(0,4)">
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