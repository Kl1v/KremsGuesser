<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KremsGuesser</title>
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
                        <a href="login.php">
                        <button type="button" class="btn btn-warning d-flex align-items-center" style="border-radius: 20px; font-weight: bold;">
                            Login
                            <img src="img/benutzerbild.png" alt="User Image" width="20" height="20" class="ms-2">
                        </button>
                        </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Content Section -->
<div class="container">
    <div class="play-container">
        <h1>Lobby Erstellen</h1>
        <h4>Stelle hier alle EInstellungen ein für deine Lobby!</h4>
        <div class="d-flex flex-column align-items-center gap-3">
            <div class="code-container mb-4">
                <h1 class="mb-3">Code</h1>
                <div class="lobby-code-container">
                <input type="text" placeholder="XXXX" class="lobby-code-input" maxlength="4" oninput="validateLobbyCode(this)">
                </div>
            </div>
            <div class="code-container mb-4">
            <h1>RUNDEN:</h1>
                    <div class="option-container">
                    <div class="option" onclick="selectRound(this)">
                            <button class="rounds-button">3</button>
                        </div>
                        <div class="option" onclick="selectRound(this)">
                            <button class="rounds-button">5</button>
                        </div>
                        <div class="option" onclick="selectRound(this)">
                            <button class="rounds-button">10</button>
                        </div>
                    </div>
            </div>

            <div class="code-container mb-4">
                <h1>ZEITLIMIT PRO RUNDE</h1>
                <div class="time-input-container">
                    <input type="number" id="timeInput" placeholder="max.120" oninput="validateSeconds(this)" min="0" max="120" >
                </div>
            </div>



                <button class="start-button"> START </button>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
        function validateLobbyCode(input) {
            // Entferne alle Nicht-Zahlen aus der Eingabe
            input.value = input.value.replace(/[^0-9]/g, '');

            // Stelle sicher, dass der Wert nicht länger als 4 Zeichen ist
            if (input.value.length > 4) {
                input.value = input.value.slice(0, 4);
            }
        }


        // Funktion zum Aktivieren der ausgewählten Rundenzahl
        function selectRound(element) {
            const options = document.querySelectorAll('.option');
            options.forEach(option => option.classList.remove('active'));
            element.classList.add('active');
        }

        // Funktion zur Zeitformatierung (z. B. 00:00)
        function formatTime(input) {
            let value = input.value.replace(/[^0-9]/g, ''); // Entferne alle Nicht-Zahlen
            if (value.length > 2) {
                input.value = value.slice(0, 2) + ":" + value.slice(2, 4);
            } else {
                input.value = value;
            }
        }


        function selectRound(selectedOption) {
            // Entferne die 'active'-Klasse von allen Optionen
            const allOptions = document.querySelectorAll('.option');
            allOptions.forEach(option => option.classList.remove('active'));

            // Füge die 'active'-Klasse nur zur ausgewählten Option hinzu
            selectedOption.classList.add('active');
        }

        function validateSeconds(input) {
            let value = parseInt(input.value, 10); // Konvertiere die Eingabe in eine Zahl

            // Stelle sicher, dass der Wert zwischen 0 und 120 liegt
            if (isNaN(value) || value < 0) {
                input.value = ''; // Leere Eingabe, falls ungültig
            } else if (value > 120) {
                input.value = 120; // Begrenze auf 120 Sekunden
            }
        }


    </script>
</body>
</html>
