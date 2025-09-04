<?php
require_once 'templates/header.php'; // Include l'header e avvia la connessione DB

// 🎯 FUNZIONE VALIDAZIONE LINK IMMAGINE ROBUSTA
function validate_image_link($url) {
    // Controlla formato URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ["valid" => false, "error" => "URL non valido"];
    }
    
    // Estensioni permesse per immagini
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowed_extensions)) {
        return ["valid" => false, "error" => "Estensione non valida. Permessi: " . implode(', ', $allowed_extensions)];
    }
    
    // Verifica headers HTTP (opzionale - può essere lento)
    $headers = @get_headers($url, 1);
    if ($headers && isset($headers['Content-Type'])) {
        $content_type = is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type'];
        if (!strpos($content_type, 'image/') === 0) {
            return ["valid" => false, "error" => "Il link non punta a un'immagine valida"];
        }
    }
    
    return ["valid" => true, "error" => null];
}

// Funzione per gestire l'upload di un file in modo sicuro
function upload_file_single($file_input_name, $target_dir = 'uploads/') {
    if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_OK) {
        return ["success" => false, "error" => "Nessun file caricato o errore nell'upload."];
    }

    $file = $_FILES[$file_input_name];
    $target_file = $target_dir . uniqid() . '_' . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Controlli di sicurezza
    if (getimagesize($file["tmp_name"]) === false) {
        return ["success" => false, "error" => "Il file non è un'immagine valida."];
    }
    if ($file["size"] > 10000000) { // Limite di 10MB
        return ["success" => false, "error" => "Il file è troppo grande (max 10MB)."];
    }
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        return ["success" => false, "error" => "Sono consentiti solo file JPG, JPEG, PNG, GIF e WEBP."];
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => true, "filepath" => $target_file];
    } else {
        return ["success" => false, "error" => "Errore durante lo spostamento del file."];
    }
}

$token = isset($_REQUEST['token']) ? sanitize_input($_REQUEST['token']) : '';
$error_message = '';
$success_message = '';
$token_is_valid = false;

// 1. Validazione del token
if (!empty($token)) {
    $stmt = $conn->prepare("SELECT * FROM token_inviti WHERE token = ? AND usato = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $token_data = $result->fetch_assoc();
        if ($token_data['usato']) {
            die('Questo token è già stato utilizzato.');
        }

        // Controlla se il token è scaduto (validità di 24 ore)
        $data_creazione = new DateTime($token_data['data_creazione']);
        $data_scadenza = $data_creazione->add(new DateInterval('P1D'));
        $ora_attuale = new DateTime();

        if ($ora_attuale > $data_scadenza) {
            die('Questo token è scaduto. Contatta l\'amministratore per riceverne uno nuovo.');
        }

        $token_is_valid = true;
    } else {
        die('Token non valido.');
    }
} else {
    die('Token mancante. Accesso non autorizzato.');
}

// 2. Processamento del form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_is_valid) {
    try {
        // Raccolta dati base
        $nome = sanitize_input($_POST['nome']);
        $descrizione = sanitize_input($_POST['descrizione']);
        $indirizzo = sanitize_input($_POST['indirizzo']);
        $telefono = sanitize_input($_POST['telefono']);
        $email = sanitize_input($_POST['email']);
        $sito_web = sanitize_input($_POST['sito_web']);
        $orari = sanitize_input($_POST['orari']);
        $categoria = sanitize_input($_POST['categoria']);
        $tipo_pacchetto = isset($_POST['tipo_pacchetto']) ? sanitize_input($_POST['tipo_pacchetto']) : 'foto';

        // Validazione campi obbligatori
        if (empty($nome) || empty($descrizione) || empty($indirizzo) || empty($telefono) || empty($email)) {
            throw new Exception("Tutti i campi obbligatori devono essere compilati.");
        }

        // 🎯 PROCESSAMENTO MEDIA CON LIMITE 3 E VALIDAZIONE LINK
        $media_paths = [];
        $media_count = 0;
        $max_media = 3; // LIMITE FISSO: 3 media totali

        // Gestione upload file
        if (isset($_FILES['media_files'])) {
            foreach ($_FILES['media_files']['name'] as $key => $filename) {
                if ($media_count >= $max_media) break;
                
                if (!empty($filename) && $_FILES['media_files']['error'][$key] === UPLOAD_ERR_OK) {
                    // Simula upload file singolo
                    $temp_file = [
                        'name' => $_FILES['media_files']['name'][$key],
                        'tmp_name' => $_FILES['media_files']['tmp_name'][$key],
                        'error' => $_FILES['media_files']['error'][$key],
                        'size' => $_FILES['media_files']['size'][$key]
                    ];
                    $_FILES['temp_upload'] = $temp_file;
                    
                    $result = upload_file_single('temp_upload');
                    if ($result['success']) {
                        $media_paths[] = $result['filepath'];
                        $media_count++;
                    } else {
                        throw new Exception("Errore upload file: " . $result['error']);
                    }
                }
            }
        }

        // 🎯 GESTIONE LINK IMMAGINI CON VALIDAZIONE ROBUSTA
        if (isset($_POST['media_urls'])) {
            foreach ($_POST['media_urls'] as $url) {
                if ($media_count >= $max_media) break;
                
                if (!empty($url)) {
                    // 🔍 VALIDAZIONE LINK IMMAGINE
                    $validation = validate_image_link($url);
                    if (!$validation['valid']) {
                        throw new Exception("Link non valido: " . $validation['error']);
                    }
                    
                    $media_paths[] = $url;
                    $media_count++;
                }
            }
        }

        // Upload logo
        $logo_path = '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $result = upload_file_single('logo');
            if ($result['success']) {
                $logo_path = $result['filepath'];
            } else {
                throw new Exception("Errore upload logo: " . $result['error']);
            }
        }

        // Salvataggio nel database
        $stmt = $conn->prepare("INSERT INTO aziende (nome, descrizione, indirizzo, telefono, email, sito_web, orari, categoria, logo_url, media_paths, tipo_pacchetto, token_utilizzato) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $media_json = json_encode($media_paths);
        $stmt->bind_param("ssssssssssss", $nome, $descrizione, $indirizzo, $telefono, $email, $sito_web, $orari, $categoria, $logo_path, $media_json, $tipo_pacchetto, $token);

        if ($stmt->execute()) {
            // Marca token come usato
            $update_token = $conn->prepare("UPDATE token_inviti SET usato = 1 WHERE token = ?");
            $update_token->bind_param("s", $token);
            $update_token->execute();

            $success_message = "Azienda registrata con successo! Hai caricato $media_count media.";
            
            // Redirect a pagina successo
            header("Location: successo.php?message=" . urlencode($success_message));
            exit;
        } else {
            throw new Exception("Errore durante il salvataggio nel database.");
        }

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione Azienda - Nuovo Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <h2>🚨 NUOVO SISTEMA - Registrazione Azienda</h2>
    
    <!-- 🎯 BANNER IMMEDIATO VISIBILE -->
    <div class="alert alert-success text-center" style="background-color: #28a745; color: white; font-weight: bold; font-size: 18px;">
        ✅ NUOVO FILE - LIMITE 3 MEDIA - VALIDAZIONE LINK IMMAGINI ATTIVA!
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if ($token_is_valid): ?>
    <form action="inserimento_nuovo.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <!-- DATI BASE AZIENDA -->
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome Azienda *</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="categoria" class="form-label">Categoria *</label>
                    <select class="form-control" id="categoria" name="categoria" required>
                        <option value="">Seleziona categoria</option>
                        <option value="ristorante">Ristorante</option>
                        <option value="hotel">Hotel</option>
                        <option value="negozio">Negozio</option>
                        <option value="servizi">Servizi</option>
                        <option value="altro">Altro</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="descrizione" class="form-label">Descrizione *</label>
            <textarea class="form-control" id="descrizione" name="descrizione" rows="3" required></textarea>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="indirizzo" class="form-label">Indirizzo *</label>
                    <input type="text" class="form-control" id="indirizzo" name="indirizzo" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="telefono" class="form-label">Telefono *</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="sito_web" class="form-label">Sito Web</label>
                    <input type="url" class="form-control" id="sito_web" name="sito_web">
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="orari" class="form-label">Orari di Apertura</label>
            <textarea class="form-control" id="orari" name="orari" rows="2"></textarea>
        </div>

        <!-- LOGO -->
        <div class="mb-3">
            <label for="logo" class="form-label">Logo Azienda</label>
            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
        </div>

        <!-- SELEZIONE TIPO PACCHETTO -->
        <fieldset class="mb-4">
            <legend class="h5">Tipo Pacchetto *</legend>
            <div class="alert alert-info alert-modern">
                <div class="alert-content">
                    <strong>Seleziona il pacchetto più adatto alle tue esigenze:</strong>
                </div>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="tipo_pacchetto" id="pacchetto_base" value="foto" checked>
                <label class="form-check-label fw-medium" for="pacchetto_base">
                    <strong>Piano Base</strong> - Solo foto link (massimo 3)
                    <small class="d-block text-muted">Include: caricamento immagini e link a foto esterne</small>
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tipo_pacchetto" id="pacchetto_pro" value="foto_video">
                <label class="form-check-label fw-medium" for="pacchetto_pro">
                    <strong>Piano Pro</strong> - Foto link e video YouTube (massimo 5)
                    <small class="d-block text-muted">Include: immagini, foto link e video YouTube</small>
                </label>
            </div>
        </fieldset>

        <!-- 🎯 SISTEMA MEDIA NUOVO CON LIMITE 3 E VALIDAZIONE LINK -->
        <fieldset>
            <legend><i class="bi bi-collection-play"></i> Media Aziendali (LIMITE 3)</legend>
            <div class="alert alert-info">
                <strong><i class="bi bi-lightbulb"></i> Nuovo Sistema:</strong> Puoi caricare fino a <strong>3 media totali</strong> (immagini o link a immagini).
                <br><small>⚠️ I link devono puntare a file immagine validi (.jpg, .png, .webp, ecc.)</small>
            </div>
            
            <div id="media-container">
                <!-- I media vengono aggiunti dinamicamente qui -->
            </div>
            
            <div class="media-controls">
                <button type="button" id="add-media-btn" class="btn btn-success">
                    <i class="fas fa-plus"></i> Aggiungi Media
                </button>
                <small class="form-text text-muted ml-3">
                    <span id="media-count">0</span>/<span id="media-limit">3</span> media aggiunti
                </small>
            </div>
        </fieldset>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Registra Azienda
            </button>
        </div>
    </form>
    <?php endif; ?>
</div>

<!-- 🎯 JAVASCRIPT NUOVO CON LIMITE 3 E VALIDAZIONE LINK -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mediaContainer = document.getElementById('media-container');
    const addMediaBtn = document.getElementById('add-media-btn');
    const mediaCountSpan = document.getElementById('media-count');
    const mediaLimitSpan = document.getElementById('media-limit');
    
    let mediaCount = 0;
    let maxMedia = 3; // 🎯 LIMITE FISSO: 3 media totali

    // Aggiorna limite in base al tipo pacchetto
    function updateMediaLimit() {
        maxMedia = 3; // 🎯 LIMITE FISSO: 3 media totali sempre
        mediaLimitSpan.textContent = maxMedia;
        
        // Nascondi bottone se limite raggiunto
        if (mediaCount >= maxMedia) {
            addMediaBtn.style.display = 'none';
        } else {
            addMediaBtn.style.display = 'inline-block';
        }
    }

    // Gestione cambio pacchetto
    document.querySelectorAll('input[name="tipo_pacchetto"]').forEach(radio => {
        radio.addEventListener('change', updateMediaLimit);
    });

    // Aggiungi slot media
    function addMediaSlot() {
        if (mediaCount >= maxMedia) return;
        
        const mediaId = 'media_' + Date.now();
        const mediaItem = document.createElement('div');
        mediaItem.className = 'media-item card mb-3';
        
        const tipoPacchetto = document.querySelector('input[name="tipo_pacchetto"]:checked')?.value || 'foto';
        
        mediaItem.innerHTML = `
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Tipo Media:</label>
                        <select class="form-control media-type-select" name="media_types[]" data-media-id="${mediaId}">
                            <option value="image">📷 Immagine</option>
                            <option value="image_link">🔗 Link Immagine</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <div class="media-input-container">
                            <div class="file-input" id="file-input-${mediaId}">
                                <label class="form-label">Carica Immagine:</label>
                                <input type="file" class="form-control" name="media_files[]" accept="image/*">
                                <small class="form-text text-muted">Max 10MB. Formati: JPG, PNG, GIF, WEBP</small>
                            </div>
                            <div class="url-input" id="url-input-${mediaId}" style="display:none;">
                                <label class="form-label">🔗 Link a Immagine:</label>
                                <input type="url" class="form-control media-url-input" name="media_urls[]" placeholder="https://esempio.com/immagine.jpg">
                                <small class="form-text text-muted">Inserisci link diretto a file immagine (.jpg, .png, .webp, ecc.)</small>
                                <div class="url-preview mt-2" style="display:none;">
                                    <img class="img-thumbnail" style="max-height: 80px;" alt="Preview">
                                </div>
                                <div class="url-error mt-2" style="display:none;">
                                    <small class="text-danger"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-media" title="Rimuovi">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        mediaContainer.appendChild(mediaItem);
        mediaCount++;
        updateCounter();

        // Event listeners per nuovo media item
        const typeSelect = mediaItem.querySelector('.media-type-select');
        const fileInput = mediaItem.querySelector('.file-input');
        const urlInput = mediaItem.querySelector('.url-input');
        const removeBtn = mediaItem.querySelector('.remove-media');
        const urlField = mediaItem.querySelector('.media-url-input');

        // Cambio tipo media
        typeSelect.addEventListener('change', function() {
            if (this.value === 'image_link') {
                fileInput.style.display = 'none';
                urlInput.style.display = 'block';
                urlField.setAttribute('name', 'media_urls[]');
                mediaItem.querySelector('input[type="file"]').removeAttribute('name');
            } else {
                fileInput.style.display = 'block';
                urlInput.style.display = 'none';
                mediaItem.querySelector('input[type="file"]').setAttribute('name', 'media_files[]');
                urlField.removeAttribute('name');
            }
        });

        // 🎯 VALIDAZIONE LINK IMMAGINE REAL-TIME
        urlField.addEventListener('input', function() {
            const url = this.value.trim();
            const previewDiv = mediaItem.querySelector('.url-preview');
            const errorDiv = mediaItem.querySelector('.url-error');
            
            if (!url) {
                previewDiv.style.display = 'none';
                errorDiv.style.display = 'none';
                return;
            }

            // Validazione estensione
            const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
            const extension = url.split('.').pop().toLowerCase().split('?')[0];
            
            if (!allowedExtensions.includes(extension)) {
                errorDiv.style.display = 'block';
                errorDiv.querySelector('small').textContent = 'Estensione non valida. Usa: ' + allowedExtensions.join(', ');
                previewDiv.style.display = 'none';
                return;
            }

            // Preview immagine
            errorDiv.style.display = 'none';
            const img = previewDiv.querySelector('img');
            img.src = url;
            img.onload = function() {
                previewDiv.style.display = 'block';
            };
            img.onerror = function() {
                errorDiv.style.display = 'block';
                errorDiv.querySelector('small').textContent = 'Impossibile caricare l\'immagine dal link fornito';
                previewDiv.style.display = 'none';
            };
        });

        // Rimozione media
        removeBtn.addEventListener('click', function() {
            mediaItem.remove();
            mediaCount--;
            updateCounter();
        });
    }

    // Aggiorna contatore
    function updateCounter() {
        mediaCountSpan.textContent = mediaCount;
        addMediaBtn.style.display = (mediaCount >= maxMedia) ? 'none' : 'inline-block';
    }

    // Event listener aggiungi media
    addMediaBtn.addEventListener('click', addMediaSlot);

    // Inizializza
    updateMediaLimit();
});
</script>

<style>
.media-item {
    border-left: 4px solid #007bff;
}

.media-controls {
    text-align: center;
    margin-top: 20px;
}

.url-preview img {
    border: 2px solid #28a745;
    border-radius: 5px;
}

.url-error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    padding: 8px;
}

.alert-success {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { background-color: #28a745; }
    50% { background-color: #20c997; }
    100% { background-color: #28a745; }
}
</style>

</body>
</html>
