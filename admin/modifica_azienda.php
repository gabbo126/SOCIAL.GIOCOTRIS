<?php
require_once 'partials/header.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$message = '';
$error = '';
$azienda_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($azienda_id === 0) {
    header('Location: dashboard.php');
    exit;
}

// Gestione dell'aggiornamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logica di aggiornamento (verrà implementata qui)
    $nome = trim($_POST['nome']);
    $descrizione = trim($_POST['descrizione']);
    $tipo_struttura = trim($_POST['tipo_struttura']);
    $indirizzo = trim($_POST['indirizzo']);
    $telefono = trim($_POST['telefono']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $sito_web = sanitize_url(trim($_POST['sito_web']));
    $servizi = trim($_POST['servizi']);

    if (empty($nome) || empty($descrizione) || empty($tipo_struttura) || empty($indirizzo)) {
        $error = 'I campi obbligatori non possono essere vuoti.';
    } else {
        $upload_dir = __DIR__ . '/../' . UPLOAD_DIR;
        $query_parts = [];
        $params = [];
        $types = '';

        // 1. Prepara i campi di testo per l'aggiornamento
        $text_fields = ['nome' => $nome, 'descrizione' => $descrizione, 'tipo_struttura' => $tipo_struttura, 'indirizzo' => $indirizzo, 'telefono' => $telefono, 'email' => $email, 'sito_web' => $sito_web, 'servizi' => $servizi];
        foreach ($text_fields as $key => $value) {
            $query_parts[] = "`{$key}` = ?";
            $params[] = $value;
            $types .= 's';
        }

        // 2. Gestisce la rimozione dei file esistenti
        $files_to_remove = [];
        if (isset($_POST['remove_media']) && !empty($_POST['remove_media'])) {
            $files_to_remove = explode(',', $_POST['remove_media']);
            
            // Rimuove i file dal database
            $media_db_fields = ['foto1_url', 'foto2_url', 'foto3_url', 'video1_url', 'video2_url'];
            foreach ($media_db_fields as $field) {
                if (in_array($azienda[$field], $files_to_remove)) {
                    $query_parts[] = "`{$field}` = NULL";
                    // Rimuove fisicamente il file
                    if (file_exists('../' . $azienda[$field])) {
                        unlink('../' . $azienda[$field]);
                    }
                }
            }
        }
        
        // 3. Gestisce l'upload del logo separatamente
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
            $file_path = upload_file('logo', $upload_dir);
            if (is_array($file_path) && isset($file_path['error'])) {
                $error = $file_path['error'];
            } else {
                $query_parts[] = "`logo_url` = ?";
                $params[] = $file_path;
                $types .= 's';
            }
        }
        
        // 4. Gestisce l'upload multiplo dei nuovi media
        if (isset($_FILES['media_files']) && is_array($_FILES['media_files']['name'])) {
            $file_count = count($_FILES['media_files']['name']);
            $media_results = [];
            
            for ($i = 0; $i < min($file_count, 5); $i++) {
                if ($_FILES['media_files']['error'][$i] === UPLOAD_ERR_OK) {
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
            
            // Assegna i nuovi media ai campi disponibili del database
            $current_media = [
                'foto1_url' => $azienda['foto1_url'],
                'foto2_url' => $azienda['foto2_url'],
                'foto3_url' => $azienda['foto3_url'],
                'video1_url' => $azienda['video1_url'],
                'video2_url' => $azienda['video2_url']
            ];
            
            // Trova i campi liberi (NULL o rimossi)
            $available_fields = [];
            foreach ($current_media as $field => $value) {
                if (is_null($value) || in_array($value, $files_to_remove)) {
                    $available_fields[] = $field;
                }
            }
            
            // Assegna i nuovi media ai campi disponibili
            foreach ($media_results as $index => $media_path) {
                if (isset($available_fields[$index])) {
                    $field = $available_fields[$index];
                    $query_parts[] = "`{$field}` = ?";
                    $params[] = $media_path;
                    $types .= 's';
                }
            }
        }

        // 3. Esegue l'aggiornamento solo se non ci sono stati errori
        if (empty($error)) {
            $sql = "UPDATE aziende SET " . implode(', ', $query_parts) . " WHERE id = ?";
            $params[] = $azienda_id;
            $types .= 'i';

            $stmt = $conn->prepare($sql);
            if ($stmt) {
                // Per compatibilità con versioni PHP < 5.6
                $bind_params = array_merge([$types], $params);
                $refs = [];
                foreach ($bind_params as $key => $value) {
                    $refs[$key] = &$bind_params[$key];
                }
                call_user_func_array([$stmt, 'bind_param'], $refs);
                if ($stmt->execute()) {
                    $message = 'Azienda aggiornata con successo!';
                } else {
                    $error = 'Errore durante aggiornamento: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = 'Errore nella preparazione della query: ' . $conn->error;
            }
        }
    }
} // Fine del blocco POST

// Recupera i dati attuali dell'azienda per mostrare nel form
$stmt = $conn->prepare("SELECT * FROM aziende WHERE id = ?");
$stmt->bind_param('i', $azienda_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Location: dashboard.php');
    exit;
}
$azienda = $result->fetch_assoc();
$stmt->close();

?>

<h1 class="h3 mb-4">Modifica Azienda: <?php echo htmlspecialchars($azienda['nome']); ?></h1>

<?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="modifica_azienda.php?id=<?php echo $azienda_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nome" class="form-label">Nome Azienda *</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($azienda['nome']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tipo_struttura" class="form-label">Tipo Struttura *</label>
                    <input type="text" class="form-control" id="tipo_struttura" name="tipo_struttura" value="<?php echo htmlspecialchars($azienda['tipo_struttura']); ?>" required>
                </div>
                <div class="col-12 mb-3">
                    <label for="descrizione" class="form-label">Descrizione *</label>
                    <textarea class="form-control" id="descrizione" name="descrizione" rows="4" required><?php echo htmlspecialchars($azienda['descrizione']); ?></textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="indirizzo" class="form-label">Indirizzo *</label>
                    <input type="text" class="form-control" id="indirizzo" name="indirizzo" value="<?php echo htmlspecialchars($azienda['indirizzo']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Telefono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($azienda['telefono']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($azienda['email']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="sito_web" class="form-label">Sito Web</label>
                    <input type="url" class="form-control" id="sito_web" name="sito_web" value="<?php echo htmlspecialchars($azienda['sito_web']); ?>">
                </div>

                <div class="col-12 mb-3">
                    <label for="servizi" class="form-label">Servizi (separati da virgola)</label>
                    <input type="text" class="form-control" id="servizi" name="servizi" value="<?php echo htmlspecialchars($azienda['servizi']); ?>">
                </div>

                <!-- Immagini -->
                <div class="col-12"><hr></div>
                <p class="text-muted small">Carica un nuovo file solo se vuoi sostituire l'immagine corrente.</p>
                <div class="col-md-6 mb-3">
                    <label for="logo" class="form-label">Logo</label>
                    <input class="form-control" type="file" id="logo" name="logo" accept="image/*">
                    <small class="form-text text-muted">Formato: JPG, PNG, WebP (max 5MB)</small>
                    <?php if ($azienda['logo_url']): ?><img src="../<?php echo htmlspecialchars($azienda['logo_url']); ?>" alt="Logo" class="img-thumbnail mt-2" width="100"><?php endif; ?>
                </div>
                <!-- Upload Multiplo Media -->
                <div class="col-12 mb-3">
                    <label class="form-label">Media (Foto e Video - Max 5 file)</label>
                    <input class="form-control" type="file" id="media_files" name="media_files[]" multiple accept=".jpg,.jpeg,.png,.webp,.mp4,.webm,.ogg">
                    <small class="form-text text-muted">Seleziona fino a 5 file: Immagini (JPG, PNG, WebP max 5MB) e Video (MP4, WebM, OGG max 100MB)</small>
                    
                    <!-- Media Esistenti -->
                    <?php
                    $existing_media = [];
                    if ($azienda['foto1_url']) $existing_media[] = $azienda['foto1_url'];
                    if ($azienda['foto2_url']) $existing_media[] = $azienda['foto2_url'];
                    if ($azienda['foto3_url']) $existing_media[] = $azienda['foto3_url'];
                    if ($azienda['video1_url']) $existing_media[] = $azienda['video1_url'];
                    if ($azienda['video2_url']) $existing_media[] = $azienda['video2_url'];
                    ?>
                    
                    <?php if (!empty($existing_media)): ?>
                    <div class="mt-3">
                        <h6>Media Esistenti:</h6>
                        <div class="row g-2" id="existing_media">
                            <?php foreach ($existing_media as $index => $media_url): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card h-100">
                                    <div class="card-body p-2 text-center">
                                        <div class="preview-content mb-2" style="height: 80px; display: flex; align-items: center; justify-content: center;">
                                            <?php 
                                            $file_ext = strtolower(pathinfo($media_url, PATHINFO_EXTENSION));
                                            $is_video = in_array($file_ext, ['mp4', 'webm', 'ogg']);
                                            ?>
                                            <?php if ($is_video): ?>
                                                <video src="../<?php echo htmlspecialchars($media_url); ?>" style="max-width: 100%; max-height: 100%; object-fit: cover;" controls></video>
                                            <?php else: ?>
                                                <img src="../<?php echo htmlspecialchars($media_url); ?>" alt="Media" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;"><?php echo basename($media_url); ?></small>
                                        <span class="badge <?php echo $is_video ? 'bg-success' : 'bg-primary'; ?> mt-1"><?php echo $is_video ? 'Video' : 'Immagine'; ?></span>
                                        <br>
                                        <button type="button" class="btn btn-sm btn-danger mt-1" onclick="removeExistingMedia(this, '<?php echo htmlspecialchars($media_url); ?>')">
                                            <i class="bi bi-trash"></i> Rimuovi
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Preview Nuovi File -->
                    <div id="media_preview" class="mt-3 row g-2" style="display: none;">
                        <h6>Nuovi File Selezionati:</h6>
                        <!-- I nuovi file selezionati appariranno qui -->
                    </div>
                    
                    <!-- Hidden input per tracciare i file da rimuovere -->
                    <input type="hidden" id="remove_media" name="remove_media" value="">
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                <a href="dashboard.php" class="btn btn-secondary">Annulla</a>
            </div>
        </form>
    </div>
</div>

<script>
// JavaScript per gestire upload multiplo con preview e rimozione
document.addEventListener('DOMContentLoaded', function() {
    const mediaInput = document.getElementById('media_files');
    const previewContainer = document.getElementById('media_preview');
    const removeMediaInput = document.getElementById('remove_media');
    const maxFiles = 5;
    let filesToRemove = [];
    
    // Gestione selezione nuovi file
    mediaInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        // Conta i media esistenti non rimossi
        const existingMediaCount = document.querySelectorAll('#existing_media .card:not(.removed)').length;
        
        // Controlla limite file totale
        if (files.length + existingMediaCount > maxFiles) {
            alert(`Massimo ${maxFiles} file totali consentiti (hai già ${existingMediaCount} file esistenti)`);
            e.target.value = '';
            previewContainer.style.display = 'none';
            return;
        }
        
        // Pulisce preview precedente
        previewContainer.innerHTML = '';
        
        if (files.length > 0) {
            const title = document.createElement('h6');
            title.textContent = 'Nuovi File Selezionati:';
            previewContainer.appendChild(title);
            previewContainer.style.display = 'block';
        } else {
            previewContainer.style.display = 'none';
        }
        
        // Crea preview per ogni file
        files.forEach((file, index) => {
            const isImage = file.type.startsWith('image/');
            const isVideo = file.type.startsWith('video/');
            
            // Controllo formato
            const validExtensions = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm', 'ogg'];
            const fileExt = file.name.split('.').pop().toLowerCase();
            if (!validExtensions.includes(fileExt)) {
                alert(`Formato non supportato: ${file.name}`);
                e.target.value = '';
                previewContainer.style.display = 'none';
                return;
            }
            
            // Controllo dimensioni
            const maxSize = isVideo ? 100 * 1024 * 1024 : 5 * 1024 * 1024; // 100MB video, 5MB immagini
            if (file.size > maxSize) {
                const maxMB = maxSize / (1024 * 1024);
                alert(`File troppo grande: ${file.name} (max ${maxMB}MB)`);
                e.target.value = '';
                previewContainer.style.display = 'none';
                return;
            }
            
            // Crea card preview
            const col = document.createElement('div');
            col.className = 'col-6 col-md-4 col-lg-3';
            
            const card = document.createElement('div');
            card.className = 'card h-100';
            card.innerHTML = `
                <div class="card-body p-2 text-center">
                    <div class="preview-content mb-2" style="height: 80px; display: flex; align-items: center; justify-content: center;">
                        ${isImage ? `<img src="${URL.createObjectURL(file)}" alt="Preview" style="max-width: 100%; max-height: 100%; object-fit: cover;">` : ''}
                        ${isVideo ? `<video src="${URL.createObjectURL(file)}" style="max-width: 100%; max-height: 100%; object-fit: cover;" controls></video>` : ''}
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.75rem;">${file.name}</small>
                    <small class="text-muted d-block" style="font-size: 0.7rem;">${(file.size / 1024 / 1024).toFixed(1)}MB</small>
                    <span class="badge ${isImage ? 'bg-primary' : 'bg-success'} mt-1">${isImage ? 'Immagine' : 'Video'}</span>
                </div>
            `;
            
            col.appendChild(card);
            previewContainer.appendChild(col);
        });
    });
    
    // Aggiorna campo hidden con i file da rimuovere
    function updateRemoveList() {
        removeMediaInput.value = filesToRemove.join(',');
    }
});

// Funzione per rimuovere media esistente
function removeExistingMedia(button, mediaUrl) {
    const card = button.closest('.card');
    const col = button.closest('.col-6, .col-md-4, .col-lg-3');
    
    // Aggiungi alla lista di rimozione
    const removeMediaInput = document.getElementById('remove_media');
    let filesToRemove = removeMediaInput.value ? removeMediaInput.value.split(',') : [];
    
    if (!filesToRemove.includes(mediaUrl)) {
        filesToRemove.push(mediaUrl);
        removeMediaInput.value = filesToRemove.join(',');
    }
    
    // Nasconde visivamente la card
    col.style.display = 'none';
    card.classList.add('removed');
    
    // Mostra messaggio di conferma
    const toast = document.createElement('div');
    toast.className = 'alert alert-warning alert-dismissible fade show';
    toast.innerHTML = `
        <strong>File rimosso:</strong> ${mediaUrl.split('/').pop()}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Inserisce il toast dopo il titolo
    const mediaSection = button.closest('.col-12');
    mediaSection.insertBefore(toast, mediaSection.children[2]);
    
    // Rimuove automaticamente dopo 3 secondi
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}
</script>

<?php 
$conn->close();
require_once 'partials/footer.php'; 
?>
