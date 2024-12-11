<?php
session_start();
require 'connection.php';

// Lobby-Code aus der URL abrufen
if (isset($_GET['lobbyCode'])) {
    $lobbyCode = $_GET['lobbyCode'];
} else {
    die("Kein Lobby-Code übergeben.");
}

// Spieler-ID aus der Session abrufen
if (!isset($_SESSION['player_id'])) {
    die("Nicht authentifiziert.");
}
$playerId = $_SESSION['player_id'];

// Alle gespeicherten Guesse für die angegebene Lobby abfragen
$stmt = $conn->prepare(
    "SELECT g.runde, g.spielername, g.lat, g.lng, g.score, p.is_host 
     FROM guesses g
     JOIN players p ON g.lobby_id = p.lobby_code AND g.spielername = p.name
     WHERE g.lobby_id = ? 
     ORDER BY g.runde ASC"
);
$stmt->bind_param("s", $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Keine Spielergebnisse gefunden.");
}

$guesses = [];
while ($row = $result->fetch_assoc()) {
    $guesses[] = $row;
}
$stmt->close();

// Überprüfen, ob der aktuelle Spieler der Host ist
$stmt = $conn->prepare("SELECT is_host FROM players WHERE id = ?");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$stmt->bind_result($isHost);
$stmt->fetch();
$stmt->close();

// Abrufen der echten Koordinaten für jede Runde aus der locations-Tabelle
$locations = [];
foreach ($guesses as $guess) {
    $stmt = $conn->prepare("SELECT latitude, longitude FROM locations WHERE round = ? AND lobby_code = ?");
    $stmt->bind_param("is", $guess['runde'], $lobbyCode);
    $stmt->execute();
    $stmt->bind_result($lat, $lng);
    if ($stmt->fetch()) {
        $locations[$guess['runde']] = ['lat' => $lat, 'lng' => $lng];
    }
    $stmt->close();
}

$response = [
    'guesses' => $guesses,
    'locations' => $locations,
    'isHost' => $isHost
];

echo json_encode($response);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ergebnisse für Lobby <?php echo htmlspecialchars($lobbyCode); ?></title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCQnFQURsReLCE66o_kF2oNvgFMDkHyO6E&callback=initMap&libraries=places" async defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #map {
            height: 60vh;
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php require 'navbar.php'; ?>

    <div class="container">
        <div id="map"></div>
        <table class="table">
            <thead>
                <tr>
                    <th>Runde</th>
                    <th>Spielername</th>
                    <th>Entfernung (in km)</th>
                    <th>Punkte</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($guesses as $guess): 
                    $roundLocation = $locations[$guess['runde']] ?? ['lat' => 51.1657, 'lng' => 10.4515];
                    $distance = calculateDistance($roundLocation['lat'], $roundLocation['lng'], $guess['lat'], $guess['lng']);
                ?>
                    <tr>
                        <td><?php echo $guess['runde']; ?></td>
                        <td><?php echo htmlspecialchars($guess['spielername']); ?></td>
                        <td><?php echo number_format($distance, 2); ?> km</td>
                        <td><?php echo $guess['score']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="mt-3">
            <button id="next-round-btn" class="btn btn-primary" disabled>Zur nächsten Runde</button>
        </div>
    </div>

    <script>
        let map;
        let markers = [];
        let playerGuesses = [];
        const lobbyCode = "<?php echo $lobbyCode; ?>";
        const isHost = <?php echo json_encode($isHost); ?>;
        const nextRoundButton = document.getElementById('next-round-btn');

        // Funktion zur Aktualisierung der Spielergebnisse
        function updateResults() {
            fetch('show_score.php?lobbyCode=' + lobbyCode)
                .then(response => response.json())
                .then(data => {
                    playerGuesses = data.guesses;
                    updateMap(data.locations);

                    // Wenn der Host ist, den Button aktivieren
                    if (isHost) {
                        nextRoundButton.disabled = false;
                    }
                })
                .catch(error => console.error('Fehler beim Abrufen der Ergebnisse:', error));
        }

        // Karte aktualisieren
        function updateMap(locations) {
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 6,
                center: locations[1] || { lat: 51.1657, lng: 10.4515 }
            });

            for (let round in locations) {
                const location = locations[round];
                const correctMarker = new google.maps.Marker({
                    position: location,
                    map: map,
                    title: 'Richtige Position für Runde ' + round,
                    icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
                });
            }

            playerGuesses.forEach(guess => {
                const position = { lat: parseFloat(guess.lat), lng: parseFloat(guess.lng) };
                const marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: guess.spielername,
                    icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                });
                markers.push(marker);
            });
        }

        // Nächste Runde starten
        function startNextRound() {
            fetch('start_next_round.php?lobbyCode=' + lobbyCode)
                .then(() => {
                    window.location.href = "game_multiplayer.php?code=" + lobbyCode;
                })
                .catch(error => console.error('Fehler beim Start der nächsten Runde:', error));
        }

        // Automatische Weiterleitung für Spieler
        setInterval(() => {
            fetch('check_round_status.php?lobbyCode=' + lobbyCode)
                .then(response => response.json())
                .then(data => {
                    if (data.nextRoundStarted) {
                        window.location.href = "game_multiplayer.php?code=" + lobbyCode;
                    }
                });
        }, 2000);

        nextRoundButton.addEventListener('click', startNextRound);
        window.initMap = updateResults;
    </script>
</body>
</html>