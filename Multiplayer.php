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
    .raise:hover,
    .raise:focus {
        box-shadow: 0 0.5em 0.5em -0.4em white;
        /* Weißer Schatten */
        transform: translateY(-0.25em);
        /* Button hebt sich */
    }

    button {
        background: none;
        border: 2px solid;
        font: inherit;
        line-height: 1;
        margin: 0.5em;
        padding: 3em 2em;
        color: var(--color);
        transition: 0.25s;
    }

    button:hover,
    button:focus {
        border-color: var(--hover);
        /* Optional: Rahmenfarbe ändern */
        box-shadow: 0 0.5em 0.5em -0.4em white;
        /* Weißer Schatten */
    }

    /* Beispiel für Farbvariablen */
    :root {
        --color: #ffa260;
        /* Standardfarbe */
        --hover: #ffc78e;
        /* Hover-Farbe */
    }
    </style>
</head>

<body style="padding-top: 70px;">
    <!-- Navbar -->
    <?php require 'navbar.php'; ?>

    <!-- Content Section -->
    <div class="container" data-aos="fade-down" data-aos-duration="1000">
        <div class="play-container">
            <h1>Mehrspieler</h1>
            <h4>Lobby beitreten oder eine erstellen?</h4>
            <div class="d-flex flex-column align-items-center gap-3">
                <form action="join_lobby.php" method="get">
                    <button type="submit" class="btn-custom raise">Lobby beitreten</button>
                </form>
                <form action="create_lobby.php" method="get">
                    <button type="submit" class="btn-custom raise">Lobby erstellen</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
    AOS.init();
    </script>
</body>

</html>