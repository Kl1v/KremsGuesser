<?php
session_start(); // Session starten
require 'connection.php'; // Datenbankverbindung einbinden
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KremsGuesser</title>
    <link rel="stylesheet" href="stylemain.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            padding-top: 70px;
            background-color: #22003D;
        }
        
        .gradient-left, .gradient-right {
            background: linear-gradient(to bottom, #d8bfd8, #4b0082);
        }



        .background-img {
            max-width: 100%;
            margin-bottom: 20px;
            margin-top: 25px;
        }
        .dynamic-center h1, .dynamic-center h4 {
            text-align: center;
            color: #FFD700;
        }
        .LoginRegisterCard ul {
            padding-left: 20px;
        }

        
        .card-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #ffffff !important;
        }

        .card-title2{
            font-size: 1.5rem;
            font-weight: bold;
            color: #ffffff !important;
        }

        .card-text {
            font-size: 1rem;
            line-height: 1.6;
            color: #ffffff;
        }


            .card {
            border: none !important;
            border-radius: 30px;
            box-shadow: none; /* Entfernt den Hintergrundschimmer komplett */
            background-color: transparent;
        }

        .card:hover {
            transform: none; 
            transition: none;
        }

        .card-body {
            color: #FFD700;
            background-color: #2e003e;
            border-radius: 10px;
        }

        .card-up{
            
            text-align:center;
        }


        .globe{
            margin-top: 50px;
            margin: 30px;
        }

        .card:hover .card-title {
            transform: scale(1.1);
            transition: transform 0.3s ease-in-out;
        }

        footer {
        text-align: center;
        padding: 10px;
        font-size: 0.8em;
        background-color: #1e0028;
        color: #fff;
        width: 100%;
    }

    </style>
</head>
<body>
    <?php require 'navbar.php'; ?>

    <!-- Grid System -->
    <div class="container-fluid">
        <div class="row">
            <!-- öffnen ombre -->
            <div class="col-12 col-md-3 gradient-left"></div>
    
            <!-- Mittlere Spalte -->
            <div class="col-12 col-md-12 col-lg-6 dynamic-center">
                <img src="img/Krems-Maps.png" alt="User Image" class="background-img">
                <h1>ENTDECKE KREMS!</h1>
                <h4>TESTE DEINE KREMS-KENNTNISSE</h4>
                <div class="row justify-content-center">
                    <div class="col-12 col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body card-up">
                                <h5 class="card-title">ERFORSCHEN</h5>
                                <p class="card-text">Entdecke den ungesehene Teile von Krems.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body card-up">
                                <h5 class="card-title">CHALLENGE</h5>
                                <p class="card-text">Spiele Herausforderungen mit Freunden.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body card-up">
                                <h5 class="card-title">NAVIGATION</h5>
                                <p class="card-text">Teste deinen Orientierungssinn in Krems.</p>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="globe">
                    <img src="GlobeKrems.png" alt="Krems Globe" width="200"/>
                </div>
                
                <div class="card LoginRegisterCard">
                    <div>
                        <h5 class="card-title2">Wie KremsGuesser funktioniert</h5>
                        <ul class="card-text">
                            <li>Du spawnst an einem zufälligen Ort in Krems.</li>
                            <li>Du musst den Standort anhand der Umgebung erkennen.</li>
                            <li>Die Herausforderung ist, die exakte Position auf der Karte zu finden.</li>
                            <li>Je genauer du schätzt, desto mehr Punkte erhältst du!</li>
                        </ul>
                    </div>
                </div>
            </div>
    
            
            <!-- schliessen obre -->
            <div class="col-12 col-md-3 gradient-left"></div>
        </div>
    </div>

    <footer>
        © 2024 KREMSGUESSER
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
