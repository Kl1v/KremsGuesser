<?php
session_start();
require 'connection.php'; // Verbindung zur Datenbank

// Wenn das Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lobbyCode = $_POST['lobbyCode'];

    // Prüfe, ob die Lobby existiert
    $stmt = $conn->prepare("SELECT * FROM lobbies WHERE code = ?");
    $stmt->bind_param("s", $lobbyCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Lobby existiert, Benutzer zur Lobby hinzufügen
        $lobby = $result->fetch_assoc();
        $username = $_SESSION['user_name']; // Der Benutzername aus der Session

        // Prüfe, ob der Benutzer bereits in der Lobby ist
        $checkPlayerStmt = $conn->prepare("SELECT * FROM players WHERE username = ? AND lobby_code = ?");
        $checkPlayerStmt->bind_param("ss", $username, $lobbyCode);
        $checkPlayerStmt->execute();
        $checkPlayerResult = $checkPlayerStmt->get_result();

        if ($checkPlayerResult->num_rows == 0) {
            // Benutzer ist noch nicht in der Lobby -> hinzufügen
            $addPlayerStmt = $conn->prepare("INSERT INTO players (username, lobby_code, is_host) VALUES (?, ?, 0)");
            $addPlayerStmt->bind_param("ss", $username, $lobbyCode);
            $addPlayerStmt->execute();
            $addPlayerStmt->close();
        }

        $checkPlayerStmt->close();
        header("Location: start_lobby.php?code=$lobbyCode"); // Weiterleitung zur Lobby
        exit;
    } else {
        // Lobby existiert nicht
        $error = "Dieser Lobby-Code existiert nicht.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KremsGuesser - Lobby beitreten</title>
    <link rel="stylesheet" href="stylemain.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="padding-top: 70px;">
    <!-- Navbar -->
    <?php require 'navbar.php'; ?>

    <!-- Content Section -->
    <div class="container">
        <div class="play-container">
            <h1>Lobby beitreten</h1>
            <h4>Gib den Code der Lobby ein</h4>
            <form method="POST" action="join_lobby.php" class="mb-4">
                <div class="code-container mb-4">
                    <h1 class="mb-3">Code</h1>
                    <div class="lobby-code-container">
                        <input 
                            type="number" 
                            name="lobbyCode" 
                            placeholder="X X X X" 
                            maxlength="4" 
                            class="lobby-code-input" 
                            required 
                            oninput="this.value=this.value.slice(0,4)">
                    </div>
                </div>
                <div class="d-flex flex-column align-items-center gap-3">
                    <button type="submit" class="btn-custom-scnd">Lobby beitreten</button>
                </div>
            </form>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger text-center">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
