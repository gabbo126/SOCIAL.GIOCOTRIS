<?php
// Avvia la sessione per poterla distruggere
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rimuovi tutte le variabili di sessione
session_unset();

// Distruggi la sessione
session_destroy();

// Reindirizza alla pagina di login
header('Location: login.php');
exit();
