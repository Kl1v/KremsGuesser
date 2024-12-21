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
$stmt = $conn->prepare("\n    SELECT g.spielername, SUM(g.score) AS total_score \n    FROM guesses g\n    WHERE g.lobby_id = ?\n    GROUP BY g.spielername\n    ORDER BY total_score DESC\n");
$stmt->bind_param("s", $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();

$finalResults = [];
while ($row = $result->fetch_assoc()) {
    $finalResults[] = $row;
}
$stmt->close();

// Löschlogik für Lobby
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteLobby'])) {
    $stmt = $conn->prepare("DELETE FROM lobbies WHERE lobby_code = ?");
    $stmt->bind_param("s", $lobbyCode);
    $stmt->execute();
    $stmt->close();
    echo json_encode(["success" => true]);
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

        // Lobby löschen, wenn die Seite verlassen wird
        const deleteLobbyOnLeave = () => {
            fetch("?lobbyCode=<?php echo htmlspecialchars($lobbyCode); ?>", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: new URLSearchParams({
                        deleteLobby: true
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log("Lobby erfolgreich gelöscht beim Verlassen der Seite.");
                    }
                })
                .catch(err => console.error("Fehler beim Löschen der Lobby: ", err));
        };

        // Event-Listener für Seitenwechsel
        window.addEventListener("beforeunload", deleteLobbyOnLeave);
        document.addEventListener("visibilitychange", () => {
            if (document.visibilityState === "hidden") {
                deleteLobbyOnLeave();
            }
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

        <a href="index.php" class="btn btn-primary">Zurück zur Startseite</a>
    </div>
</body>

</html>