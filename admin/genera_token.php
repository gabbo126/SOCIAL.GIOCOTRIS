<?php
require_once 'partials/header.php'; // Questo file include già session_start() e il controllo del login

// Genera un token sicuro e univoco
$token = bin2hex(random_bytes(32));

// Salva il token nel database
try {
    $stmt = $conn->prepare("INSERT INTO token_inviti (token) VALUES (?)");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    // Imposta un messaggio di successo nella sessione
    $_SESSION['success_message'] = 'Nuovo token generato con successo!';

} catch (Exception $e) {
    // In caso di errore, potresti voler gestire un messaggio di errore
    $_SESSION['error_message'] = 'Errore durante la generazione del token: ' . $e->getMessage();
}

$stmt->close();
$conn->close();

// Reindirizza l'utente alla dashboard
header('Location: dashboard.php');
exit();
