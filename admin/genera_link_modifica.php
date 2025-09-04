<?php
require_once 'partials/header.php';

$id_azienda = $_POST['id_azienda'] ?? null;

if (!$id_azienda || !filter_var($id_azienda, FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = 'ID azienda non valido.';
    header('Location: dashboard.php');
    exit();
}

// Controlla se l'azienda esiste
$stmt_check = $conn->prepare("SELECT id FROM aziende WHERE id = ?");
$stmt_check->bind_param('i', $id_azienda);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows === 0) {
    $_SESSION['error_message'] = "Nessuna azienda trovata con l'ID fornito.";
    header('Location: dashboard.php');
    exit();
}
$stmt_check->close();

// Genera un token sicuro
$token = bin2hex(random_bytes(32));

// Salva il token nella nuova tabella
$stmt = $conn->prepare("INSERT INTO token_modifica (id_azienda, token) VALUES (?, ?)");
$stmt->bind_param("is", $id_azienda, $token);
$stmt->execute();

$link = BASE_URL . '/modifica_azienda.php?token=' . $token;

$_SESSION['success_message'] = "Link di modifica generato per l'azienda ID {$id_azienda}.<br>Copia e invia questo link: <input type='text' value='{$link}' readonly onclick='this.select();' style='width:100%'>";

$stmt->close();
$conn->close();

header('Location: dashboard.php');
exit();
