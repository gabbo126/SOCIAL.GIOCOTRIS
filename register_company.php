<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$token_valido = false;
$error_message = '';
$tipo_pacchetto = 'foto'; // Default

// Gestione messaggio di errore da URL (da processa_registrazione.php)
if (isset($_GET['error']) && !empty($_GET['error'])) {
    $error_message = urldecode($_GET['error']);
}

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT * FROM tokens WHERE token = ? AND type = 'creazione' AND status = 'attivo' AND data_scadenza > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $token_data = $result->fetch_assoc();
        $token_valido = true;
        $tipo_pacchetto = $token_data['tipo_pacchetto'] ?? 'foto';
    } else {
        $error_message = 'Il token fornito non è valido, è scaduto o è già stato utilizzato.';
    }
    $stmt->close();
} else {
    $error_message = 'Nessun token fornito. Impossibile procedere.';
}

$page_title = 'Registra la tua Azienda';
require_once 'templates/header.php';
?>

<div class="container">
    <?php if ($error_message): ?>
        <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php elseif ($token_valido): ?>
        <?php 
        // Configurazione per template unificato
        $form_mode = 'create';
        $form_action = 'processa_registrazione.php';
        $azienda = null; // Nessun dato precompilato per creazione
        
        // Include template unificato
        include 'templates/company-form.php';
        ?>
    <?php endif; ?>
</div>

<?php
$conn->close();
?>

<!-- 🎯 Sistema Media Nativo: advanced-media-section.php usa già AdvancedMediaManager integrato -->
<!-- Nessun script aggiuntivo necessario - il template ha la sua inizializzazione -->

<?php require_once 'templates/footer.php'; ?>
