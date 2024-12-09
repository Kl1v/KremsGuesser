<?php
session_start();
require 'connection.php';

// Lobby-Code aus der URL abrufen
if (isset($_GET['lobbyCode'])) {
    $lobbyCode = $_GET['lobbyCode'];
} else {
    die("Kein Lobby-Code übergeben.");
}

// Alle gespeicherten Guesse für die angegebene Lobby abfragen
$stmt = $conn->prepare(
    "SELECT g.runde, g.spielername, g.lat, g.lng, g.score, p.is_host 
     FROM guesses g
     JOIN players p ON g.lobby_id = p.lobby_code
     WHERE g.lobby_id = ? 
     ORDER BY g.runde ASC"
);
$stmt->bind_param("s", $lobbyCode);
$stmt->execute();
$result = $stmt->get_result();

// Überprüfen, ob es Ergebnisse gibt
if ($result->num_rows == 0) {
    die("Keine Spielergebnisse gefunden.");
}

$guesses = [];
while ($row = $result->fetch_assoc()) {
    $guesses[] = $row;
}

$stmt->close();

// Überprüfen, wie viele Runden es gibt
$stmt = $conn->prepare("SELECT rounds FROM lobbies WHERE code = ?");
$stmt->bind_param("s", $lobbyCode);
$stmt->execute();
$stmt->bind_result($totalRounds);
$stmt->fetch();
$stmt->close();

// Überprüfen, wie viele Spieler ihre Guess bereits abgegeben haben
$stmt = $conn->prepare("SELECT COUNT(*) FROM guesses WHERE lobby_id = ? AND runde = ?");
$stmt->bind_param("si", $lobbyCode, $guesses[0]['runde']);
$stmt->execute();
$stmt->bind_result($guessesCount);
$stmt->fetch();
$stmt->close();

// Überprüfen, wie viele Spieler in der Lobby sind
$stmt = $conn->prepare("SELECT COUNT(*) FROM players WHERE lobby_code = ?");
$stmt->bind_param("s", $lobbyCode);
$stmt->execute();
$stmt->bind_result($totalPlayers);
$stmt->fetch();
$stmt->close();

$allGuessesSubmitted = ($guessesCount == $totalPlayers);

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
    'allGuessesSubmitted' => $allGuessesSubmitted
];

// Rückgabe der Daten als JSON
echo json_encode($response);

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
    <title>Ergebnisse für Lobby <?php echo htmlspecialchars($lobbyCode); ?></title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCQnFQURsReLCE66o_kF2oNvgFMDkHyO6E&callback=initMap&libraries=places" async defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #map {
            height: 60vh;
            width: 100%;
            margin-top: 20px;
        }
        table {
            margin-top: 20px;
            width: 100%;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
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
                    $roundLocation = $locations[$guess['runde']] ?? ['lat' => 51.1657, 'lng' => 10.4515]; // Standardkoordinaten wenn keine gefunden
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
        let correctMarker;
        let playerGuesses = [];
        const lobbyCode = "<?php echo $lobbyCode; ?>";
        const locations = <?php echo json_encode($locations); ?>;
        const nextRoundButton = document.getElementById('next-round-btn');

        // Funktion zur Aktualisierung der Spielergebnisse
        function updateResults() {
            fetch('show_score.php?lobbyCode=' + lobbyCode)
                .then(response => response.json())
                .then(data => {
                    playerGuesses = data.guesses;

                    // Aktualisieren der Karte mit den neuen Markern
                    updateMap();

                    // Wenn alle Spieler ihre Guess abgegeben haben, Button aktivieren
                    nextRoundButton.disabled = !data.allGuessesSubmitted;
                })
                .catch(error => console.error('Fehler beim Abrufen der Ergebnisse:', error));
        }

        // Karte aktualisieren
        function updateMap() {
            // Entferne bestehende Marker, bevor neue gesetzt werden
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            // Initialisiere die Karte
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 6,
                center: locations[1] || { lat: 51.1657, lng: 10.4515 } // Wenn keine Koordinaten, dann Standardkoordinaten
            });

            // Marker für jede Runde
            for (let round in locations) {
                const location = locations[round];
                correctMarker = new google.maps.Marker({
                    position: location,
                    map: map,
                    title: 'Richtige Position für Runde ' + round,
                    icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
                });
            }

            // Marker für die Spieler-Guesses
            playerGuesses.forEach(guess => {
                const position = { lat: parseFloat(guess.lat), lng: parseFloat(guess.lng) };
                const marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: guess.spielername,
                    icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png' // Blauer Marker für den Spieler
                });
                markers.push(marker);
            });
        }

        // Nächste Runde starten
        function startNextRound() {
            window.location.href = "game_multiplayer.php?code=" + lobbyCode;
        }

        // Initialisierung der Karte und die erste Aktualisierung
        function initMap() {
            updateResults(); // Erste Aktualisierung der Ergebnisse und Karte
            setInterval(updateResults, 2000); // Alle 2 Sekunden Ergebnisse aktualisieren
        }

        nextRoundButton.addEventListener('click', startNextRound);

        window.initMap = initMap;
    </script>

</body>
</html>
