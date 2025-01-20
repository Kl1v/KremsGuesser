<?php
session_start();
require 'connection.php';
if (!isset($_SESSION['user_name'])) {
    // Benutzer ist nicht angemeldet, leitet auf die Login-Seite weiter
    header('Location: index.php');
    exit;
}
// Lobby-Code aus der URL abrufen
if (isset($_GET['lobbyCode'])) {
    $lobbyCode = $_GET['lobbyCode'];
} else {
    die("Kein Lobby-Code übergeben.");
}

// Aktuelle Runde (standardmäßig 1, falls nicht angegeben)
$currentRound = isset($_GET['runde']) ? (int)$_GET['runde'] : 1;

// Alle Guesses und Spielerinformationen für die aktuelle Runde abrufen
$stmt = $conn->prepare(
    "SELECT g.runde, g.spielername, g.lat, g.lng, g.score, p.is_host 
     FROM guesses g
     JOIN players p ON g.spielername = p.username
     WHERE g.lobby_id = ? AND g.runde = ?
     ORDER BY g.spielername ASC"
);
$stmt->bind_param("si", $lobbyCode, $currentRound);
$stmt->execute();
$result = $stmt->get_result();

$guesses = [];
$isHost = false;
while ($row = $result->fetch_assoc()) {
    $guesses[] = $row;
    if ($row['is_host'] && $row['spielername'] === $_SESSION['user_name']) {
        $isHost = true;
    }
}
$stmt->close();

// Location für die aktuelle Runde abrufen
$stmt = $conn->prepare("SELECT latitude, longitude FROM locations WHERE lobby_code = ? AND round = ?");
$stmt->bind_param("si", $lobbyCode, $currentRound);
$stmt->execute();
$result = $stmt->get_result();
$currentLocation = $result->fetch_assoc();
$stmt->close();

if (!$currentLocation) {
    die("Keine Location für die aktuelle Runde gefunden.");
}

// Funktion zur Berechnung der Entfernung zwischen zwei geografischen Punkten (in Kilometern)
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $R = 6371; // Radius der Erde in km
    $phi1 = deg2rad($lat1);
    $phi2 = deg2rad($lat2);
    $deltaPhi = deg2rad($lat2 - $lat1);
    $deltaLambda = deg2rad($lon2 - $lon1);

    $a = sin($deltaPhi / 2) * sin($deltaPhi / 2) + cos($phi1) * cos($phi2) * sin($deltaLambda / 2) * sin($deltaLambda / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $R * $c; // Entfernung in Kilometern
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ergebnisse für Lobby <?php echo htmlspecialchars($lobbyCode); ?> - Runde <?php echo $currentRound; ?></title>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCQnFQURsReLCE66o_kF2oNvgFMDkHyO6E&callback=initMap&libraries=places"
        async defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    #map {
        margin-top: 100px;
        height: 50vh;
        width: 100%;
        border: 1px solid #ccc;
    }

    .table-container {
        margin-top: 20px;
    }

    .table {
        background-color: #f8f9fa;
        border-radius: 10px;
        overflow: hidden;
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
</head>

<body>
    <?php require 'navbar.php'; ?>

    <div class="container">
        <div id="map"></div>

        <div class="table-container">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Spielername</th>
                        <th>Entfernung (in km)</th>
                        <th>Punkte</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($guesses as $guess): 
                        $distance = calculateDistance($currentLocation['latitude'], $currentLocation['longitude'], $guess['lat'], $guess['lng']);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($guess['spielername']); ?></td>
                        <td><?php echo number_format($distance, 2); ?> km</td>
                        <td><?php echo $guess['score']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($isHost): ?>
            <div class="text-center mt-4">
                <form action="start_next_round.php" method="POST">
                    <input type="hidden" name="lobbyCode" value="<?php echo htmlspecialchars($lobbyCode); ?>">
                    <input type="hidden" name="currentRound" value="<?php echo $currentRound; ?>">
                    <button type="submit" class="btn btn-primary btn-lg">Nächste Runde starten</button>
                </form>
            </div>
        <?php endif; ?>

        <script>
        let map;
        const currentLocation = <?php echo json_encode($currentLocation); ?>;
        const guesses = <?php echo json_encode($guesses); ?>;

        function initMap() {
            // Initialisiere die Karte
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 13,
                center: {
                    lat: parseFloat(currentLocation.latitude),
                    lng: parseFloat(currentLocation.longitude)
                }, // Zentrum: aktuelle Location
            });

            // Marker für die richtige Position
            new google.maps.Marker({
                position: {
                    lat: parseFloat(currentLocation.latitude),
                    lng: parseFloat(currentLocation.longitude)
                },
                map: map,
                title: `Richtige Position für Runde <?php echo $currentRound; ?>`,
                icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
            });

            // Marker für die Spieler-Guesses
            guesses.forEach(guess => {
                const position = {
                    lat: parseFloat(guess.lat),
                    lng: parseFloat(guess.lng)
                };
                new google.maps.Marker({
                    position: position,
                    map: map,
                    title: `${guess.spielername}`,
                    icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                });
            });
        }
        let currentRound = <?php echo $currentRound; ?>;
        let lobbyCode = '<?php echo htmlspecialchars($lobbyCode); ?>';
        let nextRoundUrl = `game_multiplayer.php?code=${lobbyCode}&runde=${currentRound + 1}`;

        setInterval(() => {
            fetch(`check_round_started.php?lobbyCode=${lobbyCode}&round=${currentRound + 1}`)
                .then(response => response.json())
                .then(data => {
                    if (data.started) {
                        window.location.href = nextRoundUrl;
                    }
                })
                .catch(err => console.error('Fehler beim Abrufen des Rundenstatus:', err));
        }, 1000); // Überprüft alle Sekunde
        </script>
    </div>
</body>

</html>