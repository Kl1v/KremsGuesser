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
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        .lobby-code-input {
    width: 200px;
    padding: 10px;
    font-size: 24px;
    font-weight: bold;
    letter-spacing: 10px;
    text-align: center;
    border: 2px solid #52386e;
    border-radius: 8px;
    outline: none;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    color: #52386e;
    background-color: #52386e;
}
    </style>





</head>
<body style="padding-top: 70px;">
    <?php require 'navbar.php'; ?>


<!-- hier noch Modal machen wenn code falsch/nicht eingegeben ist!!!!!!-->


<!-- Content Section -->
<div class="container" data-aos="fade-down" data-aos-duration="1000">
    <div class="play-container" >
        <h1>Lobby beitreten</h1>
        <h4>Gib den Code der Lobby ein</h4>
        <div class="code-container mb-4">
            <h1 class="mb-3">Code</h1>
            <div class="lobby-code-container">
                <input type="number" placeholder="X X X X" maxlength="4" class="lobby-code-input" oninput="this.value=this.value.slice(0,4)">
            </div>
        </div>
        <div class="d-flex flex-column align-items-center gap-3">
            <button type="submit" class="start-button">Lobby beitreten</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script></body>
</body>
</html>
