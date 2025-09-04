<?php
require_once 'config.php';

// Genera un token sicuro e univoco
$token = bin2hex(random_bytes(32));

// Salva il token nel database
try {
    $stmt = $conn->prepare("INSERT INTO token_inviti (token) VALUES (?)");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    // Genera il link completo da inviare all'azienda
    $link_inserimento = BASE_URL . '/inserimento.php?token=' . $token;

    echo "<h1>Token Generato con Successo!</h1>";
    echo "<p>Invia questo link all'azienda:</p>";
    echo "<input type='text' value='{$link_inserimento}' style='width: 100%; padding: 10px;' readonly onclick='this.select();'>";
    echo "<p><a href='genera_token.php'>Genera un altro token</a></p>";

} catch (Exception $e) {
    die('Errore durante la generazione del token: ' . $e->getMessage());
}

$stmt->close();
$conn->close();
