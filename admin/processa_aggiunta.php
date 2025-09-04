<?php
require_once 'partials/header.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizzazione e validazione
    $nome = trim($_POST['nome']);
    $descrizione = trim($_POST['descrizione']);
    $tipo_struttura = trim($_POST['tipo_struttura']);
    $indirizzo = trim($_POST['indirizzo']);
    $telefono = trim($_POST['telefono']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $sito_web = sanitizeUrl(trim($_POST['sito_web']));
    $servizi = trim($_POST['servizi']);

    if (empty($nome) || empty($descrizione) || empty($tipo_struttura) || empty($indirizzo)) {
        $error = 'I campi Nome, Descrizione, Tipo Struttura e Indirizzo sono obbligatori.';
    } else {
        $upload_dir = __DIR__ . '/../' . UPLOAD_DIR;
        
        // Gestione upload logo
        $logo_url_result = uploadFile($_FILES['logo'], $upload_dir);
        
        // Gestione upload multiplo media (immagini e video)
        $media_results = [];
        if (isset($_FILES['media_files']) && is_array($_FILES['media_files']['name'])) {
            $file_count = count($_FILES['media_files']['name']);
            
            for ($i = 0; $i < min($file_count, 5); $i++) {
                if ($_FILES['media_files']['error'][$i] === UPLOAD_ERR_OK) {
                    // Riorganizza array per upload_file
                    $single_file = [
                        'name' => $_FILES['media_files']['name'][$i],
                        'type' => $_FILES['media_files']['type'][$i],
                        'tmp_name' => $_FILES['media_files']['tmp_name'][$i],
                        'error' => $_FILES['media_files']['error'][$i],
                        'size' => $_FILES['media_files']['size'][$i]
                    ];
                    
                    $result = upload_file_single($single_file, $upload_dir);
                    if ($result && !isset($result['error'])) {
                        $media_results[] = $result;
                    }
                }
            }
        }
        
        // Assegna i media caricati ai campi del database (foto1, foto2, foto3, video1, video2)
        $foto1_url_result = isset($media_results[0]) ? $media_results[0] : null;
        $foto2_url_result = isset($media_results[1]) ? $media_results[1] : null;
        $foto3_url_result = isset($media_results[2]) ? $media_results[2] : null;
        $video1_url_result = isset($media_results[3]) ? $media_results[3] : null;
        $video2_url_result = isset($media_results[4]) ? $media_results[4] : null;

        // Estrai i percorsi o gli errori
        $logo_url = is_array($logo_url_result) ? null : $logo_url_result;
        $foto1_url = is_array($foto1_url_result) ? null : $foto1_url_result;
        $foto2_url = is_array($foto2_url_result) ? null : $foto2_url_result;
        $foto3_url = is_array($foto3_url_result) ? null : $foto3_url_result;
        $video1_url = is_array($video1_url_result) ? null : $video1_url_result;
        $video2_url = is_array($video2_url_result) ? null : $video2_url_result;

        // Controlla se ci sono stati errori di upload
        if (is_array($logo_url_result) && $logo_url_result['error']) { $error = $logo_url_result['error']; }
        elseif (is_array($foto1_url_result) && $foto1_url_result['error']) { $error = $foto1_url_result['error']; }
        elseif (is_array($foto2_url_result) && $foto2_url_result['error']) { $error = $foto2_url_result['error']; }
        elseif (is_array($foto3_url_result) && $foto3_url_result['error']) { $error = $foto3_url_result['error']; }
        elseif (is_array($video1_url_result) && $video1_url_result['error']) { $error = $video1_url_result['error']; }
        elseif (is_array($video2_url_result) && $video2_url_result['error']) { $error = $video2_url_result['error']; }

        if (empty($error)) {
            $sql = "INSERT INTO aziende (nome, descrizione, tipo_struttura, indirizzo, telefono, email, sito_web, servizi, logo_url, foto1_url, foto2_url, foto3_url, video1_url, video2_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssssssssssss', $nome, $descrizione, $tipo_struttura, $indirizzo, $telefono, $email, $sito_web, $servizi, $logo_url, $foto1_url, $foto2_url, $foto3_url, $video1_url, $video2_url);
            
            if ($stmt->execute()) {
                $message = "Azienda '<strong>" . htmlspecialchars($nome) . "</strong>' aggiunta con successo!";
            } else {
                $error = "Errore durante l'inserimento nel database: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    $conn->close();
} else {
    // Se si accede direttamente al file, reindirizza alla dashboard
    header('Location: dashboard.php');
    exit;
}
?>

<div class="container mt-4">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
        <a href="dashboard.php" class="btn btn-primary">Torna alla Dashboard</a>
        <a href="aggiungi_azienda.php" class="btn btn-secondary">Aggiungi un'altra azienda</a>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <a href="aggiungi_azienda.php" class="btn btn-secondary">Torna al form</a>
    <?php endif; ?>
</div>

<?php require_once 'partials/footer.php'; ?>
