<?php
require_once 'templates/header.php'; // Include l'header e avvia la connessione DB

// 🚨🚨🚨 MARKER TEMPORANEO PER VERIFICA FILE - SE NON VEDI QUESTO TESTO IL FILE È SBAGLIATO 🚨🚨🚨

// Funzione per gestire l'upload di un file in modo sicuro
function upload_file($file_input_name, $target_dir = 'uploads/') {
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
    if ($file["size"] > 5000000) { // Limite di 5MB
        return ["success" => false, "error" => "Il file è troppo grande (max 5MB)."];
    }
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        return ["success" => false, "error" => "Sono consentiti solo file JPG, JPEG, PNG e GIF."];
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
        $data_scadenza = $data_creazione->add(new DateInterval('PT24H')); // PT24H = 24 ore
        $ora_attuale = new DateTime();

        if ($ora_attuale > $data_scadenza) {
            die('Accesso negato. Questo token è scaduto. Contatta l\'amministratore per riceverne uno nuovo.');
        }

        $token_is_valid = true;
    } else {
        $error_message = "Token non valido, scaduto o già utilizzato.";
    }
    $stmt->close();
} else {
    $error_message = "Token di accesso mancante.";
}

// 2. Gestione del submit del form
if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_is_valid) {
    // Validazione e sanitizzazione dati
    $nome = sanitize_input($_POST['nome']);
    $descrizione = sanitize_input($_POST['descrizione']);
    $indirizzo = sanitize_input($_POST['indirizzo']);
    $telefono = sanitize_input($_POST['telefono']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $sito_web = filter_var($_POST['sito_web'], FILTER_SANITIZE_URL);
    $tipo_pacchetto = sanitize_input($_POST['tipo_pacchetto']);
    
    // GESTIONE CATEGORIE AVANZATE
    $business_categories_json = sanitize_input($_POST['business_categories'] ?? '[]');
    $business_categories = json_decode($business_categories_json, true);
    
    if (!is_array($business_categories)) {
        $business_categories = [];
    }
    
    // Conversione per compatibilità database esistente
    $tipo_struttura = !empty($business_categories) ? $business_categories[0] : '';
    $servizi = implode(', ', array_slice($business_categories, 1));
    
    // Calcolo iniziale
    $iniziale = !empty($nome) ? strtoupper(substr($nome, 0, 1)) : '';

    // Gestione upload
    $logo_result = upload_file('logo');
    $foto1_result = upload_file('foto1');
    $foto2_result = upload_file('foto2');
    $foto3_result = upload_file('foto3');

    $logo_url = $logo_result['success'] ? $logo_result['filepath'] : null;
    $foto1_url = $foto1_result['success'] ? $foto1_result['filepath'] : null;
    $foto2_url = $foto2_result['success'] ? $foto2_result['filepath'] : null;
    $foto3_url = $foto3_result['success'] ? $foto3_result['filepath'] : null;

    if (empty($nome) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Nome azienda e email sono campi obbligatori e devono essere validi.";
    } else {
        // Prima aggiungi il campo tipo_pacchetto alla tabella se non esiste
        $conn->query("ALTER TABLE aziende ADD COLUMN IF NOT EXISTS tipo_pacchetto ENUM('foto', 'foto_video') DEFAULT 'foto'");
        
        // Inserimento nel database
        $conn->begin_transaction();
        try {
            $sql_azienda = "INSERT INTO aziende (nome, iniziale, descrizione, indirizzo, telefono, email, sito_web, tipo_struttura, servizi, tipo_pacchetto, logo_url, foto1_url, foto2_url, foto3_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_azienda = $conn->prepare($sql_azienda);
            
            // Associa i parametri come variabili, che è il modo corretto
            $stmt_azienda->bind_param("ssssssssssssss", 
                $nome, $iniziale, $descrizione, $indirizzo, $telefono, $email, $sito_web, 
                $tipo_struttura, $servizi, $tipo_pacchetto, $logo_url, 
                $foto1_url,
                $foto2_url,
                $foto3_url
            );
            $stmt_azienda->execute();
            $stmt_azienda->close();

            // Aggiorna il token come usato
            $sql_token = "UPDATE token_inviti SET usato = 1, data_utilizzo = NOW() WHERE token = ?";
            $stmt_token = $conn->prepare($sql_token);
            $stmt_token->bind_param("s", $token);
            $stmt_token->execute();
            $stmt_token->close();

            $conn->commit();
            $success_message = "Grazie! I dati della tua azienda sono stati inseriti con successo.";
            $token_is_valid = false; // Impedisce di mostrare di nuovo il form

        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Si è verificato un errore durante il salvataggio dei dati: " . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <h2>Inserimento Dati Azienda</h2>

    <?php if (!empty($success_message)): ?>
        <div class="message success"><?php echo $success_message; ?></div>
    <?php elseif (!empty($error_message)): ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if ($token_is_valid): ?>
    <p>Compila il seguente modulo per registrare la tua attività nel nostro portale.</p>
    <form action="inserimento.php" method="POST" enctype="multipart/form-data" class="inserimento-form">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <div class="form-group">
            <label for="nome">Nome Azienda *</label>
            <input type="text" id="nome" name="nome" required>
        </div>
        <div class="form-group">
            <label for="descrizione">Descrizione</label>
            <textarea id="descrizione" name="descrizione" rows="5"></textarea>
        </div>
        <div class="form-group">
            <label for="indirizzo">Indirizzo</label>
            <input type="text" id="indirizzo" name="indirizzo">
        </div>
        <div class="form-group">
            <label for="telefono">Telefono</label>
            <input type="tel" id="telefono" name="telefono">
        </div>
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="sito_web">Sito Web</label>
            <input type="url" id="sito_web" name="sito_web" placeholder="https://www.esempio.com">
        </div>
        <div class="form-group">
            <label for="tipo_struttura">Tipo di Struttura</label>
            <select id="tipo_struttura" name="tipo_struttura">
                <option value="">Seleziona un tipo...</option>
                <option value="hotel">Hotel</option>
                <option value="ristorante">Ristorante</option>
                <option value="campeggio">Campeggio</option>
                <option value="negozio">Negozio</option>
                <option value="servizi">Servizi</option>
                <option value="altro">Altro</option>
            </select>
        </div>
        <div class="form-group">
            <label for="servizi">Servizi Offerti (separati da virgola)</label>
            <input type="text" id="servizi" name="servizi">
        </div>
        
        <fieldset>
            <legend><i class="bi bi-box-seam"></i> Selezione Pacchetto</legend>
            <div class="form-group">
                <p><strong>Scegli il tipo di pacchetto per la tua attività:</strong></p>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="tipo_pacchetto" value="foto" checked>
                        <strong>Pacchetto Foto</strong> - Solo immagini (logo + 3 foto)
                        <small>Perfetto per mostrare la tua attività con immagini di qualità</small>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="tipo_pacchetto" value="foto_video">
                        <strong>Pacchetto Foto + Video</strong> - Immagini e video (logo + 3 foto + 2 video)
                        <small>Completo con video per un'esperienza più coinvolgente (fino a 5 media)</small>
                    </label>
                </div>
            </div>
        </fieldset>

        <!-- LOGO AZIENDALE -->
        <fieldset>
            <legend><i class="bi bi-camera"></i> Logo Aziendale</legend>
            <div class="form-group">
                <label for="logo">Logo Azienda (max 5MB)</label>
                <input type="file" id="logo" name="logo" accept="image/*" class="form-control">
                <small class="form-text text-muted">Formati supportati: JPG, PNG, GIF. Dimensione consigliata: 300x300px</small>
            </div>
        </fieldset>

        <!-- 🚨 DEBUG TEST: SE VEDI QUESTO BANNER LE MODIFICHE FUNZIONANO! -->
        <div class="alert alert-warning text-center" style="background-color: #ff0000; color: white; font-weight: bold; font-size: 18px;">
            🚨 DEBUG TEST: MODIFICHE APPLICATE CORRETTAMENTE! 🚨
        </div>
        <!-- SISTEMA MEDIA AVANZATO E FLESSIBILE -->
        <fieldset>
            <legend><i class="bi bi-collection-play"></i> Media Aziendali (Foto, Video, YouTube)</legend>
            <div class="alert alert-info">
                <strong><i class="bi bi-lightbulb"></i> Sistema Intelligente:</strong> Puoi caricare fino a <span id="max-media-count">3</span> media totali (foto, video o link).
                <br><small>YouTube viene normalizzato automaticamente per la migliore visualizzazione.</small>
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

        <!-- JAVASCRIPT PER GESTIONE MEDIA DINAMICA -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mediaContainer = document.getElementById('media-container');
            const addMediaBtn = document.getElementById('add-media-btn');
            const mediaCountSpan = document.getElementById('media-count');
            const mediaLimitSpan = document.getElementById('media-limit');
            const maxMediaCountSpan = document.getElementById('max-media-count');
            
            let mediaCount = 0;
            let maxMedia = 3; // LIMITE FISSO: 3 media totali per tutti i pacchetti
            
            // Aggiorna limite in base al tipo pacchetto selezionato
            function updateMediaLimit() {
                const tipoPacchetto = document.querySelector('input[name="tipo_pacchetto"]:checked')?.value;
                maxMedia = 3; // LIMITE FISSO: 3 media totali indipendentemente dal pacchetto
                mediaLimitSpan.textContent = maxMedia;
                maxMediaCountSpan.textContent = maxMedia;
                
                // Aggiorna visibilità pulsante aggiungi
                addMediaBtn.style.display = (mediaCount >= maxMedia) ? 'none' : 'inline-block';
                
                // CRITICO: Aggiorna opzioni disponibili per tutti i select esistenti
                document.querySelectorAll('.media-item .media-type-select').forEach(select => {
                    updateSelectOptions(select, tipoPacchetto);
                });
            }
            
            // Funzione per aggiornare opzioni select in base al pacchetto
            function updateSelectOptions(selectElement, tipoPacchetto) {
                console.log('updateSelectOptions chiamata:', tipoPacchetto, selectElement);
                
                const currentValue = selectElement.value;
                const videoOption = selectElement.querySelector('option[value="video"]');
                const youtubeOption = selectElement.querySelector('option[value="youtube"]');

                
                console.log('Opzioni trovate:', {video: !!videoOption, youtube: !!youtubeOption});
                
                if (tipoPacchetto === 'foto') {
                    // Modalità SOLO IMMAGINI: blocca video e link
                    console.log('MODALITÀ SOLO FOTO: nascondo video/youtube');
                    if (videoOption) {
                        videoOption.style.display = 'none';
                        videoOption.disabled = true;
                    }
                    if (youtubeOption) {
                        youtubeOption.style.display = 'none';
                        youtubeOption.disabled = true;
                    }

                    
                    // Se attualmente selezionato un tipo non consentito, forza su image
                    if (currentValue !== 'image') {
                        console.log('Forzando selezione su image, era:', currentValue);
                        selectElement.value = 'image';
                        const changeEvent = new Event('change');
                        selectElement.dispatchEvent(changeEvent);
                    }
                } else {
                    // Modalità FOTO + VIDEO: abilita tutto
                    console.log('MODALITÀ FOTO+VIDEO: abilito tutto');
                    if (videoOption) {
                        videoOption.style.display = 'block';
                        videoOption.disabled = false;
                    }
                    if (youtubeOption) {
                        youtubeOption.style.display = 'block';
                        youtubeOption.disabled = false;
                    }

                }
            }
            
            // Event listener per cambio tipo pacchetto
            document.querySelectorAll('input[name="tipo_pacchetto"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    console.log('Tipo pacchetto cambiato:', this.value);
                    updateMediaLimit();
                });
            });
            
            // INIZIALIZZAZIONE: Applica le regole all'avvio
            updateMediaLimit();
            
            // Funzione per aggiungere un nuovo slot media
            function addMediaSlot() {
                if (mediaCount >= maxMedia) return;
                
                const mediaId = 'media_' + Date.now();
                const mediaItem = document.createElement('div');
                mediaItem.className = 'media-item card mb-3';
                // Determina tipo pacchetto corrente per opzioni corrette
                const tipoPacchetto = document.querySelector('input[name="tipo_pacchetto"]:checked')?.value || 'foto';
                const isVideoAllowed = (tipoPacchetto === 'foto_video');
                
                mediaItem.innerHTML = `
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Tipo Media:</label>
                                <select class="form-control media-type-select" name="media_types[]" data-media-id="${mediaId}">
                                    <option value="image">📷 Immagine</option>
                                    <option value="video" style="display:${isVideoAllowed ? 'block' : 'none'}">🎥 Video</option>
                                    <option value="youtube" style="display:${isVideoAllowed ? 'block' : 'none'}">📺 Link YouTube</option>
                                    <option value="vimeo" style="display:${isVideoAllowed ? 'block' : 'none'}">🎬 Link Vimeo</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <div class="media-input-container">
                                    <!-- Input dinamico basato sul tipo -->
                                    <div class="file-input" id="file-input-${mediaId}">
                                        <label class="form-label">Carica File:</label>
                                        <input type="file" class="form-control" name="media_files[]" accept="image/*">
                                        <small class="form-text text-muted">Max 10MB. Formati: JPG, PNG, GIF</small>
                                    </div>
                                    <div class="url-input" id="url-input-${mediaId}" style="display:none;">
                                        <label class="form-label dynamic-link-label">🔗 Link a Contenuto Esterno:</label>
                                        <input type="url" class="form-control media-url-input" name="media_urls[]" placeholder="https://esempio.com/contenuto">
                                        <small class="form-text text-muted dynamic-link-help">Inserisci link al contenuto</small>
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
                
                console.log('Nuovo media aggiunto. Count:', mediaCount, 'Pacchetto:', tipoPacchetto);
                const typeSelect = mediaItem.querySelector('.media-type-select');
                updateSelectOptions(typeSelect, tipoPacchetto);
                console.log('Opzioni aggiornate per nuovo media, pacchetto:', tipoPacchetto);
                
                typeSelect.addEventListener('change', function() {
                    // VALIDAZIONE CRITICA: Verifica se il tipo selezionato è consentito
                    const tipoPacchetto = document.querySelector('input[name="tipo_pacchetto"]:checked')?.value || 'foto';
                    if (tipoPacchetto === 'foto' && this.value !== 'image') {
                        alert('ERRORE: In modalità "Solo Foto" puoi caricare solo immagini!');
                        this.value = 'image';
                        return;
                    }
                    toggleMediaInput(this.value, mediaId);
                });
                
                // CRITICO: Aggiungi il media al DOM PRIMA di applicare le opzioni
                mediaContainer.appendChild(mediaItem);
                
                // Applica opzioni corrette al nuovo select DOPO averlo aggiunto al DOM
                updateSelectOptions(typeSelect, tipoPacchetto);
                console.log('Opzioni aggiornate per nuovo media, pacchetto:', tipoPacchetto);
                
                // Inizializza input appropriato
                toggleMediaInput('image', mediaId);
                
                // Event listener per rimozione
                mediaItem.querySelector('.remove-media').addEventListener('click', function() {
                    mediaItem.remove();
                    mediaCount--;
                    updateCounter();
                });
            
            // Aggiorna contatore
            function updateCounter() {
                mediaCountSpan.textContent = mediaCount;
                addMediaBtn.style.display = (mediaCount >= maxMedia) ? 'none' : 'inline-block';
            }
            
            // Event listener per pulsante aggiungi
            addMediaBtn.addEventListener('click', addMediaSlot);
            
            // Inizializzazione
            updateMediaLimit();
            
            // Funzione per mostrare/nascondere input appropriati
            function toggleMediaInput(mediaType, mediaId) {
                const fileInput = document.getElementById(`file-input-${mediaId}`);
                const urlInput = document.getElementById(`url-input-${mediaId}`);
                
                if (mediaType === 'image' || mediaType === 'video') {
                    fileInput.style.display = 'block';
                    urlInput.style.display = 'none';
                    
                    // Aggiorna accept per il tipo di file
                    const input = fileInput.querySelector('input[type="file"]');
                    if (mediaType === 'image') {
                        input.accept = 'image/*';
                        fileInput.querySelector('small').textContent = 'Max 10MB. Formati: JPG, PNG, GIF';
                    } else {
                        input.accept = 'video/*';
                        fileInput.querySelector('small').textContent = 'Max 50MB. Formati: MP4, AVI, MOV';
                    }
                } else {
                    fileInput.style.display = 'none';
                    urlInput.style.display = 'block';
                    
                    // Aggiorna etichetta e placeholder in base al tipo
                    const label = urlInput.querySelector('label');
                    const input = urlInput.querySelector('input');
                    const helpText = urlInput.querySelector('small');
                    
                    if (mediaType === 'image') {
                        label.textContent = 'Link Immagine Diretta:';
                        input.placeholder = 'https://esempio.com/immagine.jpg';
                        helpText.textContent = 'Inserisci link diretto al file immagine (.jpg, .png, .webp, .gif)';
                        // Aggiungi validazione real-time per link immagine
                        setupImageLinkValidation(input, urlInput);
                    } else if (mediaType === 'youtube') {
                        label.textContent = 'Link YouTube:';
                        input.placeholder = 'https://www.youtube.com/watch?v=...';
                        helpText.textContent = 'Inserisci qualsiasi URL YouTube (verrà normalizzato automaticamente)';
                    } else if (mediaType === 'vimeo') {
                        label.textContent = 'Link Vimeo:';
                        input.placeholder = 'https://vimeo.com/...';
                        helpText.textContent = 'Inserisci qualsiasi URL Vimeo (verrà normalizzato automaticamente)';
                    }
                }
            }
            
            // Funzione per validazione link immagine in tempo reale
            function setupImageLinkValidation(inputElement, containerElement) {
                const previewDiv = containerElement.querySelector('.url-preview');
                const errorDiv = containerElement.querySelector('.url-error');
                
                if (!previewDiv || !errorDiv) return;
                
                const previewImg = previewDiv.querySelector('img');
                const errorText = errorDiv.querySelector('small');
                
                inputElement.addEventListener('input', function() {
                    const url = this.value.trim();
                    
                    // Reset stati precedenti
                    previewDiv.style.display = 'none';
                    errorDiv.style.display = 'none';
                    inputElement.classList.remove('is-valid', 'is-invalid');
                    
                    if (!url) return;
                    
                    // Validazione estensione
                    const validExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp', '.svg'];
                    const hasValidExtension = validExtensions.some(ext => url.toLowerCase().includes(ext));
                    
                    if (!hasValidExtension) {
                        showImageError('Link non valido: deve puntare a un file immagine (.jpg, .png, .webp, .gif)', errorDiv, errorText);
                        inputElement.classList.add('is-invalid');
                        return;
                    }
                    
                    // Test caricamento immagine
                    const testImg = new Image();
                    testImg.onload = function() {
                        // Immagine caricata con successo
                        previewImg.src = url;
                        previewDiv.style.display = 'block';
                        inputElement.classList.add('is-valid');
                        console.log('Immagine validata con successo:', url);
                    };
                    testImg.onerror = function() {
                        // Errore caricamento
                        showImageError('Errore: impossibile caricare l\'immagine dal link fornito', errorDiv, errorText);
                        inputElement.classList.add('is-invalid');
                        console.log('Errore validazione immagine:', url);
                    };
                    testImg.src = url;
                });
            }
            
            // Funzione helper per mostrare errori
            function showImageError(message, errorDiv, errorText) {
                if (errorText) errorText.textContent = message;
                if (errorDiv) errorDiv.style.display = 'block';
            }
        });
        </script>
        
        <!-- 🎨 STILI CSS PER INTERFACCIA MEDIA -->
        <style>
        .media-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .media-item:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .media-controls {
            display: flex;
            align-items: center;
            margin-top: 15px;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
        }
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-body {
            padding: 1rem;
        }
        </style>

        <!-- 🚀 PULSANTE INVIO CON VALIDAZIONE -->
        <div class="form-submit-section">
            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                <i class="fas fa-paper-plane"></i> Registra Azienda
            </button>
            <small class="form-text text-muted mt-2">
                ✅ I tuoi media verranno processati automaticamente con normalizzazione intelligente
            </small>
        </div>
        
        <style>
        .form-submit-section {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        </style>
    </form>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>
