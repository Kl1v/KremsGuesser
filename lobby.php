<?php
require 'connection.php'; // Verbindung zur Datenbank
require 'functions.php'; // Gemeinsame Funktionen
session_start(); // Session starten
if (!isset($_SESSION['user_name'])) {
    // Benutzer ist nicht angemeldet, leitet auf die Login-Seite weiter
    header('Location: index.php');
    exit;
}
// Lobby-Code generieren
$lobbyCode = generateUniqueLobbyCode($conn);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KremsGuesser</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="stylemain.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="padding-top: 70px;">
    <!-- Navbar -->
    <?php require 'navbar.php'; ?>

    <div class="container">
        <div class="play-container">
            <h1>Lobby Erstellen</h1>
            <h4>Stelle hier alle Einstellungen ein für deine Lobby!</h4>
            <div class="d-flex flex-column align-items-center gap-3">
                <div class="code-container mb-4">
                    <h1 class="mb-3">Code</h1>
                    <div class="lobby-code-container">
                        <input type="text" class="lobby-code-input" maxlength="4" value="<?php echo $lobbyCode; ?>"
                            readonly>
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
                        <input type="number" id="timeInput" placeholder="max.120" oninput="validateSeconds(this)"
                            min="0" max="120">
                    </div>
                </div>

                <button class="start-button" onclick="startLobby()">START</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let selectedRounds = 3;
    let selectedTimeLimit = 60;

    function validateSeconds(input) {
        const value = parseInt(input.value, 10);
        if (isNaN(value) || value < 0) input.value = '';
        else if (value > 120) input.value = 120;
        else selectedTimeLimit = value;
    }

    function selectRound(element) {
        document.querySelectorAll('.option').forEach(opt => opt.classList.remove('active'));
        element.classList.add('active');
        selectedRounds = parseInt(element.querySelector('button').textContent);
    }

    function startLobby() {
        const lobbyCode = document.querySelector('.lobby-code-input').value;

        fetch('create_lobby.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    lobbyCode,
                    rounds: selectedRounds,
                    timeLimit: selectedTimeLimit
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'start_lobby.php?code=' + data.lobbyCode;
                } else {
                    alert('Fehler: ' + data.message);
                }
            })
            .catch(error => console.error('Fehler beim Erstellen der Lobby:', error));
    }
    </script>
</body>

</html>