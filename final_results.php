<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_name'])) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['lobbyCode'])) {
    $lobbyCode = $_GET['lobbyCode'];
} else {
    die("Kein Lobby-Code übergeben.");
}

// Gesamtergebnisse abrufen
$stmt = $conn->prepare("SELECT g.spielername, SUM(g.score) AS total_score FROM guesses g WHERE g.lobby_id = ? GROUP BY g.spielername ORDER BY total_score DESC");
$stmt->bind_param("s", $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();

$finalResults = [];
while ($row = $result->fetch_assoc()) {
    $finalResults[] = $row;
}
$stmt->close();

// Löschlogik für Lobby (geändert: direkt hier im gleichen Skript)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Löschen der Lobby
    $stmt = $conn->prepare("DELETE FROM lobbies WHERE code = ?");
    $stmt->bind_param("s", $lobbyCode);
    $stmt->execute();
    $stmt->close();
    // Umleiten zur Startseite nach erfolgreichem Löschen
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Endergebnisse für Lobby <?php echo htmlspecialchars($lobbyCode); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        padding-top: 150px;
    }

    .table {
        margin-top: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .table thead {
        background-color: #007bff;
        color: white;
    }

    .table thead th {
        text-transform: uppercase;
        font-weight: bold;
        letter-spacing: 0.05em;
    }

    .table tbody tr:hover {
        background-color: #e9ecef;
    }
    </style>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        // Event-Listener für den Button "Zurück zur Startseite"
        document.getElementById("backToHome").addEventListener("click", () => {
            // Beim Klick auf den Button das Formular absenden
            const form = document.getElementById('deleteLobbyForm');
            form.submit();
        });
    });
    </script>
</head>

<body>
    <?php require 'navbar.php'?>
    <div class="container">
        <h1>Endergebnisse für Lobby: <?php echo htmlspecialchars($lobbyCode); ?></h1>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Rang</th>
                    <th>Spielername</th>
                    <th>Gesamtpunkte</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; ?>
                <?php foreach ($finalResults as $result): ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo htmlspecialchars($result['spielername']); ?></td>
                    <td><?php echo $result['total_score']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Formular zum Löschen der Lobby -->
        <form id="deleteLobbyForm" method="POST" style="display: none;">
            <input type="hidden" name="deleteLobby" value="true">
        </form>

        <!-- Button zum Zurück zur Startseite -->
        <button id="backToHome" class="btn btn-primary">Zurück zur Startseite</button>

    </div>
</body>

</html>