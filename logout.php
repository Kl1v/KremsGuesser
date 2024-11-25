<?php
session_start();
session_unset(); // Alle Session-Variablen löschen
session_destroy(); // Session beenden
header("Location: index.php"); // Zur Startseite weiterleiten
exit();
?>
