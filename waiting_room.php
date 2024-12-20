<?php
session_start();
require 'connection.php';
if (!isset($_SESSION['user_name'])) {
    // Benutzer ist nicht angemeldet, leitet auf die Login-Seite weiter
    header('Location: index.php');
    exit;
}

if (!isset($_GET['lobbyCode'])) {
    die("Kein Lobby-Code übergeben.");
}

$lobbyCode = $_GET['lobbyCode'];
$round = intval($_GET['runde']);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warten auf andere Spieler...</title>
    <script>
    const lobbyCode = "<?php echo $lobbyCode; ?>";
    const round = "<?php echo $round; ?>";

    function checkStatus() {
        fetch(`check_status.php?lobbyCode=${lobbyCode}`)
            .then(response => response.json())
            .then(data => {
                if (data.allGuessesSubmitted || data.timeExpired) {
                    window.location.href = `show_score.php?lobbyCode=${lobbyCode}&runde=${round}`;
                } else {
                    console.log("Warten auf Spieler:", data.debug);
                }
            })
            .catch(error => console.error('Fehler beim Abrufen des Status:', error));
    }

    // Status jede Sekunde prüfen
    setInterval(checkStatus, 1000);
    </script>
</head>

<body>
    <div class="container text-center">
        <h1>Warten auf andere Spieler...</h1>
        <p>Bitte warten Sie, bis alle Spieler ihre Guesses abgegeben haben, oder bis die Zeit abgelaufen ist.</p>
    </div>
</body>

</html>