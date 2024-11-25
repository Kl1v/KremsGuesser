<?php
session_start();
?>
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
            font-family: Arial, sans-serif;
            background: linear-gradient(180deg, #3D0059, #B18CD9);
            color: #fff;
        }

        header {
            background-color: #3D0059; /* Gleiche Farbe wie der Hintergrund */
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 1.5em;
            color: #FFC800;
        }

        header nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
        }

        .scoreboard {
            margin: 40px auto;
            width: 80%;
            max-width: 800px;
            background-color: #fff;
            color: #333;
            border-radius: 10px;
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
            </ul>
        </div>
    </div>
</nav>

    <!-- SVG-Welle -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 150" style="display: block;">
        <path fill="#ffffff" fill-opacity="1" d="M0,96L40,112C80,128,160,160,240,165.3C320,171,400,149,480,144C560,139,640,149,720,149.3C800,149,880,139,960,133.3C1040,128,1120,128,1200,122.7C1280,117,1360,107,1400,101.3L1440,96L1440,0L1400,0C1360,0,1280,0,1200,0C1120,0,1040,0,960,0C880,0,800,0,720,0C640,0,560,0,480,0C400,0,320,0,240,0C160,0,80,0,40,0L0,0Z"></path>
    </svg>

    <main>
        <section class="scoreboard">
            <h2>SCOREBOARD</h2>
            <table class="scores-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Score</th>
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>XXXXXX</td>
                        <td>Max Muster</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>XXXXXX</td>
                        <td>Maxi M.</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>XXXXXX</td>
                        <td>Maxl Mus.</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>XXXXXX</td>
                        <td>Muster Max</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>XXXXXX</td>
                        <td>Maxer Muster</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>XXXXXX</td>
                        <td>Mad Max</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>XXXXXX</td>
                        <td>Mustermann</td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>XXXXXX</td>
                        <td>Muster M.</td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>XXXXXX</td>
                        <td>Maximilian</td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>XXXXXX</td>
                        <td>M Mann</td>
                    </tr>
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
