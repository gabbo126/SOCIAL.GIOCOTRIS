<?php
require_once 'config.php';

$error_message = '';
$success_message = '';
$token_is_valid = false;
$id_azienda = null;
$azienda = null;

// 1. Validazione del token
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die('Accesso negato. Token di modifica mancante.');
}

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT * FROM token_modifica WHERE token = ?");
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $token_data = $result->fetch_assoc();

    if ($token_data['usato']) {
        die('Questo link di modifica è già stato utilizzato.');
    }

    $data_creazione = new DateTime($token_data['data_creazione']);
    $data_scadenza = $data_creazione->add(new DateInterval('PT24H'));
    $ora_attuale = new DateTime();

    if ($ora_attuale > $data_scadenza) {
        die('Accesso negato. Questo link di modifica è scaduto.');
    }

    $token_is_valid = true;
    $id_azienda = $token_data['id_azienda'];

    // 2. Recupero dei dati dell'azienda
    $stmt_azienda = $conn->prepare("SELECT * FROM aziende WHERE id = ?");
    $stmt_azienda->bind_param('i', $id_azienda);
    $stmt_azienda->execute();
    $azienda_result = $stmt_azienda->get_result();
    if ($azienda_result->num_rows === 1) {
        $azienda = $azienda_result->fetch_assoc();
    } else {
        die('Errore: azienda non trovata.');
    }
    $stmt_azienda->close();

} else {
    die('Accesso negato. Link di modifica non valido.');
}
$stmt->close();

// 3. Gestione del form di aggiornamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_is_valid) {

    // Recupero e sanificazione dei dati dal form
    $nome = sanitize_input($_POST['nome']);
    $descrizione = sanitize_input($_POST['descrizione']);
    $indirizzo = sanitize_input($_POST['indirizzo']);
    $telefono = sanitize_input($_POST['telefono']);
    $email = sanitize_email($_POST['email']);
    $sito_web = sanitize_url($_POST['sito_web']);
    $tipo_struttura = sanitize_input($_POST['tipo_struttura']);
    $servizi = sanitize_input($_POST['servizi']);
    $iniziale = strtoupper(substr($nome, 0, 1));
    
    // Gestione upgrade pacchetto
    $nuovo_tipo_pacchetto = isset($_POST['upgrade_pacchetto']) ? 'foto_video' : $azienda['tipo_pacchetto'];

    // Gestione upload immagini: mantieni le vecchie se non ne vengono caricate di nuove
    $logo_url = upload_file('logo', 'uploads/') ?: $azienda['logo_url'];
    $foto1_url = upload_file('foto1', 'uploads/') ?: $azienda['foto1_url'];
    $foto2_url = upload_file('foto2', 'uploads/') ?: $azienda['foto2_url'];
    $foto3_url = upload_file('foto3', 'uploads/') ?: $azienda['foto3_url'];
    
    // Gestione upload video solo se pacchetto supporta i video
    $video1_url = $azienda['video1_url'];
    $video2_url = $azienda['video2_url'];
    if ($nuovo_tipo_pacchetto === 'foto_video') {
        $video1_url = upload_file('video1', 'uploads/') ?: $azienda['video1_url'];
        $video2_url = upload_file('video2', 'uploads/') ?: $azienda['video2_url'];
    }

    // Preparazione della query di UPDATE
    $sql_update = "UPDATE aziende SET 
                    nome = ?, iniziale = ?, descrizione = ?, indirizzo = ?, telefono = ?, email = ?, sito_web = ?, 
                    tipo_struttura = ?, servizi = ?, tipo_pacchetto = ?, logo_url = ?, foto1_url = ?, foto2_url = ?, foto3_url = ?, video1_url = ?, video2_url = ? 
                    WHERE id = ?";
    
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssssssssssssssssi", 
        $nome, $iniziale, $descrizione, $indirizzo, $telefono, $email, $sito_web, 
        $tipo_struttura, $servizi, $nuovo_tipo_pacchetto, $logo_url, $foto1_url, $foto2_url, $foto3_url, $video1_url, $video2_url, 
        $id_azienda
    );

    if ($stmt_update->execute()) {
        // Aggiornamento andato a buon fine, ora invalida il token di modifica
        $stmt_token = $conn->prepare("UPDATE token_modifica SET usato = 1, data_utilizzo = NOW() WHERE token = ?");
        $stmt_token->bind_param('s', $token);
        $stmt_token->execute();
        $stmt_token->close();

        $success_message = 'Dati aziendali aggiornati con successo!';
        // Ricarica i dati dell'azienda per mostrarli aggiornati
        $stmt_azienda = $conn->prepare("SELECT * FROM aziende WHERE id = ?");
        $stmt_azienda->bind_param('i', $id_azienda);
        $stmt_azienda->execute();
        $azienda = $stmt_azienda->get_result()->fetch_assoc();
        $stmt_azienda->close();

    } else {
        $error_message = "Errore durante l'aggiornamento dei dati: " . $conn->error;
    }
    $stmt_update->close();
}

require_once 'templates/header.php';
?>

<div class="container">
    <?php if ($success_message): ?>
        <div class="message success"><?php echo $success_message; ?></div>
    <?php else: ?>
        <?php 
        // Configurazione per template unificato
        $form_mode = 'edit';
        $form_action = 'modifica_azienda.php?token=' . urlencode($token);
        // $azienda già disponibile dal codice precedente
        
        // Include template unificato
        include 'templates/company-form.php';
        ?>
    <?php endif; ?>
</div>

<?php
$conn->close();
?>

<!-- JavaScript unificato (Vimeo rimosso) -->
<script src="/SOCIAL.GIOCOTRIS/assets/js/unified-media-uploader.js"></script>

<?php require_once 'templates/footer.php'; ?>
