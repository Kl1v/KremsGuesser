<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KremsGuesser</title>
    <link rel="stylesheet" href="stylemain.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body style="padding-top: 70px;">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top" style="background-color: #1e0028;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" style="color: #FFD700; font-weight: bold; font-size: 1.5rem;">KREMSGUESSER</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item ms-1 mt-2">
                        <a class="nav-link text-white" aria-current="page" href="play.php"><h5>Play</h5></a>
                    </li>
                    <li class="nav-item ms-1 mt-2">
                        <a class="nav-link text-white" href="index.php"><h5>Home</h5></a>
                    </li>
                    <li class="nav-item ms-1 mt-2">
                        <a class="nav-link text-white" href="scoreboard.php"><h5>Scoreboard</h5></a>
                    </li>
                    <li class="nav-item ms-3">
                        <a href="login.php">
                        <button type="button" class="btn btn-warning d-flex align-items-center" style="border-radius: 20px; font-weight: bold;">
                            Login
                            <img src="img/benutzerbild.png" alt="User Image" width="20" height="20" class="ms-2">
                        </button>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Grid System -->
    <div class="container-fluid">
        <div class="row">
            <!-- Linke Spalte mit Farbverlauf -->
            <div class="col-12 col-md-3 gradient-left"></div>
    
            <!-- Mittlere Spalte (dynamisch) -->
            <div class="col-12 col-md-12 col-lg-6 dynamic-center">
                <img src="img/Krems-Maps.png" alt="User Image" class="background-img">
                <h1>ENTDECKE KREMS!</h1>
                <h4>TESTE DEINE KREMS-KENNTNISSE</h4>
                <div class="row justify-content-center">
                    <div class="col-12 col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Card title</h5>
                                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>

                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Card title</h5>
                                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>

                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Card title</h5>
                                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>

                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card LoginRegisterCard">
                    <div class="card-body">
                        <h5 class="card-title">Wie KremsGuesser funktioniert</h5>
                        <ul class="card-text">
                            <li>Du spawnst an einem zufälligen Ort in Krems</li>
                            <li>Du musst den Standort anhand der Umgebung erkennen.</li>
                            <li>Die Herausforderung ist, die genaue Position auf der Karte zu finden.</li>
                            <li>Je genauer du bist, desto mehr Punkte erhältst du.</li>
                        </ul>
                    </div>
                </div>

            </div>


            
    
            <!-- Rechte Spalte mit Farbverlauf -->
            <div class="col-12 col-md-3 gradient-right"></div>
        </div>
    </div>
    
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
