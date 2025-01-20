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

    $stmt = $conn->prepare("DELETE FROM guesses WHERE lobby_id = ?");
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
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
    body {
        padding-top: 150px;
        background-color: #664a8d;
    }

    h1 {
        color: white;
    }

    .table-container {
        margin-top: 30px;
        background-color: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .table {
        border-collapse: collapse;
        width: 100%;
        border-radius: 0;
        overflow: hidden;
    }

    .table thead {
        background: linear-gradient(90deg, #007bff, #6610f2);
        color: white;
    }

    .table thead th {
        padding: 15px;
        text-transform: uppercase;
        font-weight: bold;
        letter-spacing: 0.05em;
        border: none;
    }

    .table tbody tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .table tbody tr:nth-child(odd) {
        background-color: #ffffff;
    }

    .table tbody tr:hover {
        background-color: #d6e9ff;
    }

    .table td {
        padding: 12px 15px;
        border: none;
    }

    #backToHome {
    display: inline-block;
    outline: 0;
    border: none;
    cursor: pointer;
    padding: 0 24px;
    border-radius: 50px;
    min-width: 200px;
    height: 50px;
    font-size: 18px;
    background-color: #fd0;
    font-weight: 500;
    color: #222;
    transition: transform 0.2s ease, background-color 0.3s ease;

    }
    #backToHome:hover {
    transform: scale(1.1);
    /* Vergrößert den Button um 10% */

    background-color: rgb(255, 229, 58);

    }
    .highlight {
        font-weight: bold;
        color: #007bff;
        background-color: #f8f9fa;
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

        // Event-Listener für das Verlassen der Seite
        window.addEventListener("beforeunload", () => {
            // Wenn die Seite verlassen wird, wird die Lobby gelöscht
            const form = document.getElementById('deleteLobbyForm');
            form.submit();
        });
    });
    </script>
</head>

<body>
    <?php require 'navbar.php'?>
    <div class="container" data-aos="fade-down" data-aos-duration="500">
        <h1 class="text-center">Endergebnisse für Lobby: <?php echo htmlspecialchars($lobbyCode); ?></h1>

        <div class="table-container">
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
                    <tr class="<?php echo $result['spielername'] === $_SESSION['user_name'] ? 'highlight' : ''; ?>">
                        <td><?php echo $rank++; ?></td>
                        <td><?php echo htmlspecialchars($result['spielername']); ?></td>
                        <td><?php echo $result['total_score']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Formular zum Löschen der Lobby -->
        <form id="deleteLobbyForm" method="POST" style="display: none;">
            <input type="hidden" name="deleteLobby" value="true">
        </form>

        <!-- Button zum Zurück zur Startseite -->
        <button id="backToHome" class="btn btn-primary d-block mx-auto mt-2">Zurück zur Startseite</button>
    </div>
</body>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</html>
