<?php
// Stelle sicher, dass die Session gestartet ist, um auf $_SESSION zuzugreifen
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg fixed-top" style="background-color: #1e0028;">
    <div class="container-fluid">
        <!-- Markenname -->
        <a class="navbar-brand" href="#" style="color: #FFD700; font-weight: bold; font-size: 1.5rem;">KREMSGUESSER</a>

        <!-- Mobile Navigation Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item ms-1 mt-2">
                    <a class="nav-link text-white" href="index.php">
                        <h5>Home</h5>
                    </a>
                </li>
                <li class="nav-item ms-1 mt-2">
                    <a class="nav-link text-white" aria-current="page" href="play.php">
                        <h5>Play</h5>
                    </a>
                </li>
                <li class="nav-item ms-1 mt-2">
                    <a class="nav-link text-white" href="scoreboard.php">
                        <h5>Scoreboard</h5>
                    </a>
                </li>
                <li class="nav-item ms-3">
                    <?php if (isset($_SESSION['user_name'])): ?>
                    <!-- Dropdown-Menü für eingeloggte Nutzer -->
                    <div class="dropdown">
                        <button class="btn btn-warning dropdown-toggle d-flex align-items-center" type="button"
                            id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                            style="border-radius: 20px; font-weight: bold;">
                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <form action="logout.php" method="POST" style="margin: 0;">
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <!-- Login-Button für Gäste -->
                    <a href="login.php" style="text-decoration: none;">
                        <button type="button" class="btn btn-warning d-flex align-items-center"
                            style="border-radius: 20px; font-weight: bold;">
                            Login
                            <img src="img/benutzerbild.png" alt="User Image" width="20" height="20" class="ms-2">
                        </button>
                    </a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>