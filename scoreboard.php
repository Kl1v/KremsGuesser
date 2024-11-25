<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Krems Guesser - Scoreboard</title>
    <link rel="stylesheet" href="stylemain.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            margin: 0;
            background: linear-gradient(to bottom, #3D0059, #ffffff); /* Verlauf: von der Farbe der Welle zu Weiß */
            color: #fff;
        }

        /* Navbar */
        nav.navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        /* Welle */
        .wave-container {
            position: relative;
            height: 50vh; /* Mehr Platz für die Welle */
            background: #3D0059; /* Gleiche Farbe wie die Welle */
        }

        .wave {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 100%;
        }

        /* Inhalt */
        main {
            margin-top: 11px; /* Abstand zwischen Welle und Scoreboard */
        }

        .scoreboard {
            margin: auto;
            width: 80%;
            max-width: 800px;
            background-color: #fff;
            color: #333;
            border-radius: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            text-align: center;
        }

        .scoreboard h2 {
            background: linear-gradient(90deg, #A972D8, #C0A5E5);
            color: #333;
            margin: 0;
            padding: 15px 0;
            font-size: 1.8em;
            font-weight: bold;
        }

        .scores-table {
            width: 100%;
            border-collapse: collapse;
        }

        .scores-table th, .scores-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .scores-table th {
            background: #E6D7F1;
            color: #333;
        }

        .scores-table td {
            color: #333;
        }

        .start-button {
            margin: 20px 0;
            text-align: center;
        }

        .start-button a {
            text-decoration: none;
            font-size: 1.2em;
            color: #fff;
            background-color: #FFC800;
            padding: 10px 20px;
            border-radius: 25px;
            transition: background-color 0.3s ease;
        }

        .start-button a:hover {
            background-color: #E6A600;
        }

        footer {
            text-align: center;
            padding: 10px;
            font-size: 0.8em;
            background-color: #22003D;
            color: #fff;
        }

        .boardstyle {
        position: relative; /* Erlaubt das Überschreiben von Elementen */
        text-align: center; /* Zentriert die Überschrift */
    }

    .overlay-title {
        position: absolute; /* Positioniert die Überschrift über die SVG */
        top: 40%; /* Verschiebt die Überschrift nach unten (50% des Containers) */
        left: 50%; /* Zentriert die Überschrift horizontal */
        transform: translate(-50%, -50%); /* Exakte Zentrierung */
        color: white; /* Farbe des Textes */
        font-size: 3rem; /* Schriftgröße */
        font-weight: bold; /* Fettgedruckter Text */
        z-index: 1; /* Stellt sicher, dass die Überschrift vor der SVG liegt */
        pointer-events: none; /* Verhindert, dass die Überschrift Interaktionen blockiert */
    }

    svg {
        display: block;
        width: 100%; /* Passt sich an die Breite des Containers an */
        height: auto; /* Bewahrt das Seitenverhältnis */
    }



        .wave-container {
        position: relative;
        width: 100%;
        overflow: hidden;
        background: #3D0059; /* Optional: Farbe hinter dem Bild */
    }



    </style>
</head>
<body>
    <!-- Navbar -->
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
                    <button type="button" class="btn btn-warning d-flex align-items-center" 
                        style="border-radius: 20px; font-weight: bold;" 
                        onclick="window.location.href='login.php'">
                        Login
                        <img src="img/benutzerbild.png" alt="User Image" width="20" height="20" class="ms-2">
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>
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
                    <button type="button" class="btn btn-warning d-flex align-items-center" 
                        style="border-radius: 20px; font-weight: bold;" 
                        onclick="window.location.href='login.php'">
                        Login
                        <img src="img/benutzerbild.png" alt="User Image" width="20" height="20" class="ms-2">
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>


<div class="boardstyle">
    <h2 class="overlay-title">SCOREBOARD</h2>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
        <path fill="#1e0028" fill-opacity="1" d="M0,96L80,122.7C160,149,320,203,480,213.3C640,224,800,192,960,186.7C1120,181,1280,203,1360,213.3L1440,224L1440,0L1360,0C1280,0,1120,0,960,0C800,0,640,0,480,0C320,0,160,0,80,0L0,0Z"></path>
    </svg>
</div>



    <!-- Hauptinhalt -->
    <main>
        <section class="scoreboard">
            <table class="scores-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Score</th>
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>1</td><td>XXXXXX</td><td>Max Muster</td></tr>
                    <tr><td>2</td><td>XXXXXX</td><td>Maxi M.</td></tr>
                    <tr><td>3</td><td>XXXXXX</td><td>Maxl Mus.</td></tr>
                    <tr><td>4</td><td>XXXXXX</td><td>Muster Max</td></tr>
                    <tr><td>5</td><td>XXXXXX</td><td>Maxer Muster</td></tr>
                    <tr><td>6</td><td>XXXXXX</td><td>Max Muster</td></tr>
                    <tr><td>7</td><td>XXXXXX</td><td>Maxi M.</td></tr>
                    <tr><td>8</td><td>XXXXXX</td><td>Maxl Mus.</td></tr>
                    <tr><td>9</td><td>XXXXXX</td><td>Muster Max</td></tr>
                    <tr><td>10</td><td>XXXXXX</td><td>Maxer Muster</td></tr>
                </tbody>
            </table>
            <div class="start-button">
                <a href="play.php">START</a>
            </div>
        </section>
    </main>

    <footer>
        © 2024 KREMSGUESSER
    </footer>
</body>
</html>
