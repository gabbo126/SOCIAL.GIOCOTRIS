<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = 'Conferma Modifica Azienda';
require_once 'templates/header.php';

echo '<div class="container my-5">';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && isset($_POST['id_azienda'])) {
    $token = $_POST['token'];
    $id_azienda = filter_input(INPUT_POST, 'id_azienda', FILTER_VALIDATE_INT);

    // 1. Re-validazione del token
    $stmt = $conn->prepare("SELECT id, tipo_pacchetto FROM tokens WHERE token = ? AND id_azienda = ? AND type = 'modifica' AND status = 'attivo' AND data_scadenza > NOW()");
    $stmt->bind_param('si', $token, $id_azienda);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $token_data = $result->fetch_assoc();
        $tipo_pacchetto = $token_data['tipo_pacchetto'] ?? 'foto';
        
        // 2. Gestione Dati e Upload
        try {
            // Sanitizzazione dati
            $nome = sanitize_input($_POST['nome']);
            $descrizione = sanitize_input($_POST['descrizione']);
            $indirizzo = sanitize_input($_POST['indirizzo']);
            $telefono = sanitize_input($_POST['telefono']);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $sito_web = filter_input(INPUT_POST, 'sito_web', FILTER_SANITIZE_URL);
            
            // GESTIONE CATEGORIE AVANZATE
            $business_categories_json = sanitize_input($_POST['business_categories'] ?? '[]');
            $business_categories = json_decode($business_categories_json, true);
            
            if (!is_array($business_categories)) {
                $business_categories = [];
            }
            
            // GESTIONE SERVIZI OFFERTI MODERNI
            $services_offered_json = sanitize_input($_POST['services_offered'] ?? '[]');
            $services_offered = json_decode($services_offered_json, true);
            
            if (!is_array($services_offered)) {
                $services_offered = [];
            }
            
            // Conversione per compatibilità database esistente
            $tipo_struttura = !empty($business_categories) ? $business_categories[0] : '';
            // Usa i nuovi servizi moderni per il campo legacy 'servizi'
            $servizi_legacy = !empty($services_offered) ? implode(', ', $services_offered) : implode(', ', array_slice($business_categories, 1));

            // 🚀 FIX CRITICO: Recupera TUTTI i dati esistenti per merge intelligente
            $current_data_stmt = $conn->prepare("SELECT logo_url, foto1_url, foto2_url, foto3_url, media_json, business_categories FROM aziende WHERE id = ?");
            $current_data_stmt->bind_param('i', $id_azienda);
            $current_data_stmt->execute();
            $current_data = $current_data_stmt->get_result()->fetch_assoc();

            // 🔒 PRESERVAZIONE DATI ESISTENTI (principio: mai perdere dati se non esplicitamente modificati)
            $logo_url = $current_data['logo_url']; // Mantieni logo esistente
            $foto1_url = $current_data['foto1_url']; // Mantieni foto esistenti
            $foto2_url = $current_data['foto2_url'];
            $foto3_url = $current_data['foto3_url'];
            
            // 🔒 PRESERVAZIONE CATEGORIE: se non fornite, mantieni quelle esistenti
            if (empty($business_categories) && !empty($current_data['business_categories'])) {
                $existing_categories = json_decode($current_data['business_categories'], true);
                if (is_array($existing_categories)) {
                    $business_categories = $existing_categories;
                    $business_categories_json = $current_data['business_categories'];
                    error_log("🔒 Categorie preservate: " . json_encode($business_categories));
                }
            }

            // Gestione upload (sovrascrive solo se viene fornito un nuovo file)
            $upload_dir = 'assets/img/aziende/';
            
            // Upload del logo (corretto per usare upload_file_single)
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
                $logo_url = upload_file_single($_FILES['logo'], $upload_dir);
            }
            
            // 🚀 FIX CRITICO: SISTEMA MERGE INTELLIGENTE LOGO/GALLERIA
            $media_json = null; // Default: mantieni media esistenti
            
            // 🔍 Recupera i media esistenti dall'azienda (ESCLUDENDO il logo)
            $existing_gallery_media = [];
            if (!empty($current_data['media_json'])) {
                $all_existing_media = deserialize_media_from_db($current_data['media_json']);
                // 🚨 SEPARAZIONE CRITICA: filtra il logo dalla galleria
                foreach ($all_existing_media as $media) {
                    if ($media['url'] !== $current_data['logo_url']) {
                        $existing_gallery_media[] = $media;
                    }
                }
            } else {
                // Converte i vecchi campi foto (ESCLUDENDO logo) in formato nuovo
                $legacy_data = $current_data;
                unset($legacy_data['logo_url']); // Rimuovi logo dalla conversione
                $existing_gallery_media = convert_legacy_media_to_flexible($legacy_data);
            }
            
            // 🔄 MERGE INTELLIGENTE: processa nuovi media SOLO per la galleria
            if (isset($_POST['media_types']) && is_array($_POST['media_types'])) {
                $media_files = $_FILES['media_files'] ?? [];
                $media_urls = $_POST['media_urls'] ?? [];
                
                // Usa la nuova funzione di processing flessibile
                $processed_media = process_flexible_media(
                    $media_files,           // File caricati
                    $_POST['media_types'],  // Tipi media
                    $media_urls,           // URL esterni
                    $tipo_pacchetto,       // Tipo pacchetto (foto/foto_video)
                    $upload_dir            // Directory upload
                );
                
                if ($processed_media['success']) {
                    // 🔒 MERGE SICURO: aggiungi nuovi media SENZA toccare il logo
                    $final_gallery_media = array_merge($existing_gallery_media, $processed_media['media']);
                    
                    // 🚨 VALIDAZIONE ANTI-CONTAMINAZIONE: rimuovi eventuali duplicati del logo
                    $clean_gallery_media = [];
                    foreach ($final_gallery_media as $media) {
                        if ($media['url'] !== $current_data['logo_url']) {
                            $clean_gallery_media[] = $media;
                        }
                    }
                    
                    $media_json = serialize_media_for_db($clean_gallery_media);
                    
                    error_log("✅ Media galleria aggiornati: " . count($clean_gallery_media) . " elementi (logo escluso)");
                    error_log("📋 Gallery Media JSON: " . $media_json);
                    
                    // 🔄 Per backward compatibility: aggiorna campi foto SOLO dalla galleria
                    $legacy_photos = 0;
                    // NON resettare foto esistenti! Solo aggiornare se ci sono nuove nella galleria
                    
                    foreach ($clean_gallery_media as $media) {
                        if ($media['type'] === 'image' && $media['url'] && $legacy_photos < 3) {
                            if ($legacy_photos === 0) $foto1_url = $media['url'];
                            elseif ($legacy_photos === 1) $foto2_url = $media['url'];
                            elseif ($legacy_photos === 2) $foto3_url = $media['url'];
                            $legacy_photos++;
                        }
                    }
                } else {
                    error_log("⚠️ Errore processing media galleria: " . ($processed_media['error'] ?? 'Errore sconosciuto'));
                    // Mantieni i media galleria esistenti in caso di errore
                    $media_json = serialize_media_for_db($existing_gallery_media);
                }
            } else {
                // Nessun nuovo media fornito, mantieni galleria esistente (SENZA logo)
                $media_json = serialize_media_for_db($existing_gallery_media);
                error_log("🔒 Galleria media preservata: " . count($existing_gallery_media) . " elementi");
            }

            // Verifica esistenza colonna business_categories
            $column_check = $conn->query("SHOW COLUMNS FROM aziende LIKE 'business_categories'");
            if (!$column_check || $column_check->num_rows === 0) {
                throw new Exception('Colonna business_categories non trovata nella tabella aziende.');
            }
            // Verifica esistenza colonna services_offered
            $services_column_check = $conn->query("SHOW COLUMNS FROM aziende LIKE 'services_offered'");
            $services_column_exists = ($services_column_check && $services_column_check->num_rows > 0);
            
            // 3. Aggiornamento nel DB con la VERA struttura della tabella (include business_categories + services_offered + media_json!)
            if ($services_column_exists) {
                // Query con services_offered (nuova struttura)
                $update_stmt = $conn->prepare("UPDATE aziende SET nome = ?, tipo_struttura = ?, descrizione = ?, indirizzo = ?, telefono = ?, email = ?, sito_web = ?, servizi = ?, business_categories = ?, services_offered = ?, logo_url = ?, foto1_url = ?, foto2_url = ?, foto3_url = ?, media_json = ? WHERE id = ?");
                
                if (!$update_stmt) {
                    throw new Exception('Errore preparazione query UPDATE: ' . $conn->error);
                }
                
                $update_stmt->bind_param('sssssssssssssssi', $nome, $tipo_struttura, $descrizione, $indirizzo, $telefono, $email, $sito_web, $servizi_legacy, $business_categories_json, $services_offered_json, $logo_url, $foto1_url, $foto2_url, $foto3_url, $media_json, $id_azienda);
            } else {
                // Query senza services_offered (struttura legacy)
                $update_stmt = $conn->prepare("UPDATE aziende SET nome = ?, tipo_struttura = ?, descrizione = ?, indirizzo = ?, telefono = ?, email = ?, sito_web = ?, servizi = ?, business_categories = ?, logo_url = ?, foto1_url = ?, foto2_url = ?, foto3_url = ?, media_json = ? WHERE id = ?");
                
                if (!$update_stmt) {
                    throw new Exception('Errore preparazione query UPDATE: ' . $conn->error);
                }
                
                $update_stmt->bind_param('ssssssssssssssi', $nome, $tipo_struttura, $descrizione, $indirizzo, $telefono, $email, $sito_web, $servizi_legacy, $business_categories_json, $logo_url, $foto1_url, $foto2_url, $foto3_url, $media_json, $id_azienda);
            }
            
            // 🔍 DEBUG CRITICO: Log dei dati prima dell'UPDATE
            error_log("🔧 UPDATE azienda ID: $id_azienda");
            error_log("📝 Dati UPDATE: nome=$nome, tipo_struttura=$tipo_struttura");
            error_log("📦 Categorie: $business_categories_json");
            error_log("🎯 Servizi: $services_offered_json");
            
            // VERIFICA CRITICA: controlla se l'UPDATE ha realmente avuto successo
            if (!$update_stmt->execute()) {
                throw new Exception('Errore esecuzione UPDATE: ' . $update_stmt->error);
            }
            
            // VERIFICA CRITICA: controlla se sono state effettivamente modificate delle righe
            $rows_affected = $update_stmt->affected_rows;
            error_log("📊 Righe modificate: $rows_affected");
            
            $update_stmt->close();
            
            // 🚨 FIX CRITICO: Non fallire se dati identici, ma avvisa
            if ($rows_affected === 0) {
                // Verifica che l'azienda esista ancora
                $check_stmt = $conn->prepare("SELECT id FROM aziende WHERE id = ?");
                $check_stmt->bind_param('i', $id_azienda);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $check_stmt->close();
                
                if ($check_result->num_rows === 0) {
                    throw new Exception("Errore: Azienda con ID $id_azienda non trovata nel database.");
                } else {
                    // Azienda esiste ma nessuna modifica effettuata (dati identici)
                    error_log("⚠️ Nessuna modifica effettuata - dati probabilmente identici");
                    // Non lanciare Exception, continua con successo
                }
            }
            
            // SOLO ora mostra il messaggio di successo - DOPO aver verificato il salvataggio reale!
            ?>
            <!DOCTYPE html>
            <html lang="it">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Modifica Completata - Social Gioco Tris</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
                <style>
                .success-container {
                    min-height: 100vh;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .success-card {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    max-width: 500px;
                    width: 90%;
                    overflow: hidden;
                }
                .success-header {
                    background: linear-gradient(135deg, #28a745, #20c997);
                    color: white;
                    padding: 2rem 1.5rem 1rem;
                    text-align: center;
                    position: relative;
                }
                .success-icon {
                    font-size: 4rem;
                    margin-bottom: 0.5rem;
                    animation: successPulse 2s ease-in-out infinite;
                }
                .success-body {
                    padding: 2rem 1.5rem;
                    text-align: center;
                }
                .success-title {
                    font-size: 1.5rem;
                    font-weight: 600;
                    color: #2d3748;
                    margin-bottom: 1rem;
                }
                .success-message {
                    color: #718096;
                    font-size: 1.1rem;
                    line-height: 1.6;
                    margin-bottom: 2rem;
                }
                .success-actions {
                    display: flex;
                    gap: 1rem;
                    justify-content: center;
                    flex-wrap: wrap;
                }
                .btn-elegant {
                    padding: 0.75rem 2rem;
                    border-radius: 50px;
                    font-weight: 500;
                    text-decoration: none;
                    transition: all 0.3s ease;
                    border: none;
                    font-size: 1rem;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                }
                .btn-primary-elegant {
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
                }
                .btn-primary-elegant:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
                    color: white;
                }
                .btn-secondary-elegant {
                    background: rgba(108, 117, 125, 0.1);
                    color: #6c757d;
                    border: 2px solid rgba(108, 117, 125, 0.2);
                }
                .btn-secondary-elegant:hover {
                    background: rgba(108, 117, 125, 0.2);
                    transform: translateY(-1px);
                    color: #495057;
                }
                .stats-badge {
                    background: rgba(40, 167, 69, 0.1);
                    color: #28a745;
                    font-size: 0.9rem;
                    font-weight: 600;
                    padding: 0.5rem 1rem;
                    border-radius: 50px;
                    border: 1px solid rgba(40, 167, 69, 0.2);
                    display: inline-block;
                    margin-top: 1rem;
                }
                @keyframes successPulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                }
                @media (max-width: 576px) {
                    .success-card { margin: 1rem; }
                    .success-header { padding: 1.5rem 1rem 0.5rem; }
                    .success-body { padding: 1.5rem 1rem; }
                    .success-icon { font-size: 3rem; }
                    .success-actions { flex-direction: column; }
                    .btn-elegant { width: 100%; justify-content: center; }
                }
                </style>
            </head>
            <body>
                <div class="success-container">
                    <div class="success-card">
                        <div class="success-header">
                            <i class="bi bi-check-circle-fill success-icon"></i>
                        </div>
                        <div class="success-body">
                            <h2 class="success-title">Modifica completata con successo!</h2>
                            <p class="success-message">
                                I dati della tua attività sono stati <strong>aggiornati correttamente</strong> 
                                e sono ora visibili nel portale.
                            </p>
                            
                            <div class="stats-badge">
                                <i class="bi bi-database-fill-check me-2"></i>
                                Database aggiornato: <?php echo $rows_affected; ?> record modificato
                            </div>
                            
                            <div class="success-actions mt-4">
                                <a href="index.php" class="btn-elegant btn-primary-elegant">
                                    <i class="bi bi-house-fill"></i>
                                    Torna alla Home
                                </a>
                                <a href="javascript:history.back()" class="btn-elegant btn-secondary-elegant">
                                    <i class="bi bi-arrow-left"></i>
                                    Modifica altro
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                <script>
                // Auto-redirect dopo 10 secondi (opzionale)
                setTimeout(function() {
                    const badge = document.querySelector('.stats-badge');
                    if (badge) {
                        badge.innerHTML = '<i class="bi bi-clock me-2"></i>Reindirizzamento tra pochi secondi...';
                        badge.style.background = 'rgba(255, 193, 7, 0.1)';
                        badge.style.color = '#ffc107';
                        badge.style.borderColor = 'rgba(255, 193, 7, 0.2)';
                    }
                    
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 3000);
                }, 7000);
                </script>
            </body>
            </html>
            <?php

        } catch (Exception $e) {
            ?>
            <!DOCTYPE html>
            <html lang="it">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Errore Modifica - Social Gioco Tris</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
                <style>
                .error-container {
                    min-height: 100vh;
                    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .error-card {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
                    max-width: 600px;
                    width: 90%;
                    overflow: hidden;
                }
                .error-header {
                    background: linear-gradient(135deg, #dc3545, #c82333);
                    color: white;
                    padding: 2rem 1.5rem 1rem;
                    text-align: center;
                }
                .error-icon {
                    font-size: 4rem;
                    margin-bottom: 0.5rem;
                    animation: errorShake 1s ease-in-out;
                }
                .error-body {
                    padding: 2rem 1.5rem;
                }
                .error-title {
                    font-size: 1.5rem;
                    font-weight: 600;
                    color: #2d3748;
                    margin-bottom: 1rem;
                    text-align: center;
                }
                @keyframes errorShake {
                    0%, 100% { transform: translateX(0); }
                    25% { transform: translateX(-5px); }
                    75% { transform: translateX(5px); }
                }
                </style>
            </head>
            <body>
                <div class="error-container">
                    <div class="error-card">
                        <div class="error-header">
                            <i class="bi bi-exclamation-triangle-fill error-icon"></i>
                        </div>
                        <div class="error-body">
                            <h2 class="error-title">Si è verificato un errore</h2>
                            <div class="alert alert-danger">
                                <h5><i class="bi bi-bug-fill me-2"></i>Dettagli tecnici:</h5>
                                <p><strong>Errore:</strong><br><?php echo htmlspecialchars($e->getMessage()); ?></p>
                                <details class="mt-3">
                                    <summary class="text-muted" style="cursor: pointer;">Informazioni avanzate per sviluppatori</summary>
                                    <pre class="mt-2 text-muted" style="font-size: 0.8rem;"><?php echo htmlspecialchars($e->getTraceAsString()); ?></pre>
                                </details>
                            </div>
                            <div class="text-center">
                                <a href="javascript:history.back()" class="btn btn-outline-primary me-2">
                                    <i class="bi bi-arrow-left"></i> Torna indietro
                                </a>
                                <a href="index.php" class="btn btn-primary">
                                    <i class="bi bi-house-fill"></i> Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            <?php
            error_log('Errore modifica azienda: ' . $e->getMessage());
            echo '</div>';
        }

    } else {
        echo '<div class="alert alert-danger"><h4>Token non valido, scaduto o non corrispondente all\'azienda.</h4></div>';
    }

} else {
    echo '<div class="alert alert-warning"><h4>Accesso non consentito.</h4></div>';
}

echo '</div>';

require_once 'templates/footer.php';
$conn->close();
?>
