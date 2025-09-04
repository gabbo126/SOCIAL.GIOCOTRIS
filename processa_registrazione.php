<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

try {
    // 1. VALIDAZIONE TOKEN
    if (!isset($_POST['token']) || empty($_POST['token'])) {
        throw new Exception('Token mancante');
    }
    
    $token = trim($_POST['token']);
    
    // Verifica token nel database
    $stmt = $conn->prepare("SELECT id, tipo_pacchetto FROM tokens WHERE token = ? AND type = 'creazione' AND status = 'attivo' AND data_scadenza > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        throw new Exception('Token non valido, scaduto o già utilizzato');
    }
    
    $token_data = $result->fetch_assoc();
    $token_id = $token_data['id'];
    $tipo_pacchetto = $token_data['tipo_pacchetto'] ?? 'foto';
    $stmt->close();
    
    // 2. VALIDAZIONE CAMPI OBBLIGATORI
    $required_fields = ['nome', 'descrizione', 'indirizzo'];
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $errors[] = "Il campo '$field' è obbligatorio";
        }
    }
    
    // VALIDAZIONE SPECIALE: business_categories (sostituisce tipo_struttura)
    $business_categories_json = trim($_POST['business_categories'] ?? '[]');
    $business_categories = json_decode($business_categories_json, true);
    
    if (!is_array($business_categories) || empty($business_categories)) {
        $errors[] = "Seleziona almeno una categoria per l'attività";
    }
    
    if (!empty($errors)) {
        throw new Exception('Campi obbligatori mancanti: ' . implode(', ', $errors));
    }
    
    // 3. SANITIZZAZIONE DATI
    $nome = trim($_POST['nome']);
    $descrizione = trim($_POST['descrizione']);
    $indirizzo = trim($_POST['indirizzo']);
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sito_web = trim($_POST['sito_web'] ?? '');
    
    // GESTIONE SERVIZI OFFERTI MODERNI
    $services_offered_json = trim($_POST['services_offered'] ?? '[]');
    $services_offered = json_decode($services_offered_json, true);
    
    if (!is_array($services_offered)) {
        $services_offered = [];
    }
    
    // Conversione per compatibilità database esistente (già validato sopra)
    $tipo_struttura = !empty($business_categories) ? $business_categories[0] : '';
    // Usa i nuovi servizi moderni per il campo legacy 'servizi'
    $servizi_legacy = !empty($services_offered) ? implode(', ', $services_offered) : implode(', ', array_slice($business_categories, 1));
    
    // Validazione email se fornita
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email non valida');
    }
    
    // Validazione URL se fornito
    if (!empty($sito_web) && !filter_var($sito_web, FILTER_VALIDATE_URL)) {
        throw new Exception('URL sito web non valido');
    }
    
    // GESTIONE SERVIZI OFFERTI (NUOVO SISTEMA)
    $services_offered_json = '[]'; // Default vuoto
    if (isset($_POST['services_offered']) && !empty($_POST['services_offered'])) {
        $services_data = $_POST['services_offered'];
        
        // Se è già JSON, validalo
        if (is_string($services_data)) {
            $decoded = json_decode($services_data, true);
            if (is_array($decoded)) {
                $services_offered_json = $services_data;
            }
        }
        // Se è un array, convertilo in JSON
        elseif (is_array($services_data)) {
            $services_offered_json = json_encode(array_values($services_data));
        }
    }
    
    // Gestione upload media
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Inizializza URLs media
    $logo_url = null;
    $foto1_url = null;
    $foto2_url = null;
    $foto3_url = null;
    
    // Upload logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $logo_path = upload_file_single($_FILES['logo'], $upload_dir);
        if ($logo_path) {
            $logo_url = $logo_path;
        }
    }
    
    // Gestione media flessibile
    $media_json = json_encode([]);
    
    // Processa media con il nuovo sistema flessibile
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
            $media_json = serialize_media_for_db($processed_media['media']);
            
            // Backward compatibility per vecchi campi foto
            $legacy_photos = 0;
            foreach ($processed_media['media'] as $media) {
                if ($media['type'] === 'image' && $media['url'] && $legacy_photos < 3) {
                    if ($legacy_photos === 0) $foto1_url = $media['url'];
                    elseif ($legacy_photos === 1) $foto2_url = $media['url'];
                    elseif ($legacy_photos === 2) $foto3_url = $media['url'];
                    $legacy_photos++;
                }
            }
        }
    }
    
    // Genera iniziale dal nome
    $iniziale = strtoupper(substr($nome, 0, 1));
    
    // Verifica esistenza colonna services_offered
    $services_column_check = $conn->query("SHOW COLUMNS FROM aziende LIKE 'services_offered'");
    $services_column_exists = ($services_column_check && $services_column_check->num_rows > 0);
    
    // Inserimento nel database con transazione
    $conn->begin_transaction();
    
    try {
        if ($services_column_exists) {
            // Query con services_offered (nuova struttura)
            $sql = "INSERT INTO aziende (nome, iniziale, descrizione, indirizzo, telefono, email, sito_web, tipo_struttura, servizi, business_categories, services_offered, logo_url, foto1_url, foto2_url, foto3_url, media_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insert_stmt = $conn->prepare($sql);
            
            if (!$insert_stmt) {
                throw new Exception('Errore preparazione query: ' . $conn->error);
            }
            
            $insert_stmt->bind_param(
                "ssssssssssssssss",
                $nome,
                $iniziale,
                $descrizione,
                $indirizzo,
                $telefono,
                $email,
                $sito_web,
                $tipo_struttura,
                $servizi_legacy,
                $business_categories_json,
                $services_offered_json,
                $logo_url,
                $foto1_url,
                $foto2_url,
                $foto3_url,
                $media_json
            );
        } else {
            // Query senza services_offered (struttura legacy)
            $sql = "INSERT INTO aziende (nome, iniziale, descrizione, indirizzo, telefono, email, sito_web, tipo_struttura, servizi, business_categories, logo_url, foto1_url, foto2_url, foto3_url, media_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insert_stmt = $conn->prepare($sql);
            
            if (!$insert_stmt) {
                throw new Exception('Errore preparazione query: ' . $conn->error);
            }
            
            $insert_stmt->bind_param(
                "sssssssssssssss",
                $nome,
                $iniziale,
                $descrizione,
                $indirizzo,
                $telefono,
                $email,
                $sito_web,
                $tipo_struttura,
                $servizi_legacy,
                $business_categories_json,
                $logo_url,
                $foto1_url,
                $foto2_url,
                $foto3_url,
                $media_json
            );
        }
        
        if (!$insert_stmt->execute()) {
            throw new Exception('Errore inserimento azienda: ' . $insert_stmt->error);
        }
        
        $azienda_id = $conn->insert_id;
        $insert_stmt->close();
        
        // Aggiorna il token come utilizzato
        $update_token_stmt = $conn->prepare("UPDATE tokens SET status = 'utilizzato' WHERE id = ?");
        $update_token_stmt->bind_param('i', $token_id);
        
        if (!$update_token_stmt->execute()) {
            throw new Exception('Errore aggiornamento token: ' . $update_token_stmt->error);
        }
        
        $update_token_stmt->close();
        $conn->commit();
        
        // Reindirizza alla pagina di successo
        header('Location: successo.php?type=registrazione');
        exit();
        
    } catch (Exception $e) {
        // Rollback in caso di errore
        $conn->rollback();
        error_log("Rollback eseguito per errore: " . $e->getMessage());
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("=== ERRORE REGISTRAZIONE AZIENDA: " . $e->getMessage() . " ===");
    
    // Reindirizza con messaggio di errore dettagliato
    $error_message = urlencode($e->getMessage());
    $redirect_url = "register_company.php?token=" . urlencode($_POST['token'] ?? '') . "&error=" . $error_message;
    
    error_log("Redirect verso: $redirect_url");
    header("Location: $redirect_url");
    exit();
}

finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
