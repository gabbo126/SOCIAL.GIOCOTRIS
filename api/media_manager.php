<?php
/**
 * 🎯 MEDIA MANAGER API - Sistema Gestione Media Aziendali v2.0
 * Gestisce upload, validazione, salvataggio e CRUD media con UX ottimale
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

class MediaManager {
    private $conn;
    private $upload_dir = '../uploads/aziende/';
    private $allowed_formats = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
    private $max_file_size = 5 * 1024 * 1024; // 5MB default
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        
        // Crea directory upload se non esiste
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }
    
    /**
     * 🎯 ENDPOINT PRINCIPALE - Router API
     */
    public function handleRequest() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type');
        
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        try {
            switch ($method . ':' . $action) {
                case 'POST:upload':
                    return $this->uploadMedia();
                    
                case 'POST:add_url':
                    return $this->addUrlMedia();
                    
                case 'GET:list':
                    return $this->listMedia();
                    
                case 'PUT:reorder':
                    return $this->reorderMedia();
                    
                case 'DELETE:remove':
                    return $this->removeMedia();
                    
                case 'GET:limits':
                    return $this->getMediaLimits();
                    
                default:
                    throw new Exception('Azione non supportata');
            }
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode() ?: 'GENERIC_ERROR'
            ], 400);
        }
    }
    
    /**
     * 📤 UPLOAD FILE - Gestione robusta upload
     */
    private function uploadMedia() {
        $azienda_id = (int)($_POST['azienda_id'] ?? 0);
        $tipo_media = $_POST['tipo_media'] ?? 'galleria';
        
        // Durante la registrazione (azienda_id=0), salviamo temporaneamente in sessione
        if (!$azienda_id) {
            session_start();
            if (!isset($_SESSION['temp_media'])) {
                $_SESSION['temp_media'] = [];
            }
            
            // Controllo limite temporaneo (max 5 media in sessione)
            if (count($_SESSION['temp_media']) >= 5) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Limite media temporaneo raggiunto (max 5)',
                    'error_code' => 'TEMP_LIMIT_EXCEEDED'
                ], 400);
            }
            
            // Gestione upload file temporaneo
            if (!isset($_FILES['media_file'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Nessun file ricevuto',
                    'error_code' => 'NO_FILE_UPLOADED'
                ], 400);
            }
            
            $file = $_FILES['media_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Errore upload file',
                    'error_code' => 'UPLOAD_ERROR'
                ], 400);
            }
            
            // Salva temporaneamente
            $temp_dir = '../uploads/temp/';
            if (!is_dir($temp_dir)) {
                mkdir($temp_dir, 0777, true);
            }
            
            $filename = uniqid() . '_' . basename($file['name']);
            $filepath = $temp_dir . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Impossibile salvare il file',
                    'error_code' => 'SAVE_ERROR'
                ], 500);
            }
            
            // Salva info in sessione
            $media_data = [
                'id' => uniqid('temp_'),
                'url' => '/uploads/temp/' . $filename,
                'tipo' => 'immagine',
                'tipo_media' => $tipo_media,
                'timestamp' => time()
            ];
            
            $_SESSION['temp_media'][] = $media_data;
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'File caricato temporaneamente per registrazione',
                'data' => [
                    'media_id' => $media_data['id'],
                    'preview_url' => $media_data['url'],
                    'temp_mode' => true
                ]
            ]);
        }
        
        // Verifica limiti piano per azienda esistente
        $limits_check = $this->checkPlanLimits($azienda_id, $tipo_media);
        if (!$limits_check['can_add']) {
            throw new Exception($limits_check['message'], 'PLAN_LIMIT_EXCEEDED');
        }
        
        if (!isset($_FILES['media_file'])) {
            throw new Exception('Nessun file ricevuto', 'NO_FILE_UPLOADED');
        }
        
        $file = $_FILES['media_file'];
        
        // Validazione file
        $validation = $this->validateUploadedFile($file);
        if (!$validation['valid']) {
            throw new Exception($validation['message'], 'FILE_VALIDATION_ERROR');
        }
        
        // Processo upload sicuro
        $upload_result = $this->processFileUpload($file, $azienda_id, $tipo_media);
        
        if (!$upload_result['success']) {
            throw new Exception($upload_result['error'], 'UPLOAD_PROCESSING_ERROR');
        }
        
        // Salvataggio in database
        $media_id = $this->saveMediaToDatabase([
            'azienda_id' => $azienda_id,
            'tipo_media' => $tipo_media,
            'nome_file' => $upload_result['original_name'],
            'percorso_file' => $upload_result['file_path'],
            'tipo_sorgente' => 'upload',
            'formato' => $upload_result['format'],
            'dimensione_kb' => $upload_result['size_kb'],
            'larghezza' => $upload_result['width'],
            'altezza' => $upload_result['height']
        ]);
        
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Media caricato con successo',
            'data' => [
                'media_id' => $media_id,
                'file_path' => $upload_result['file_path'],
                'preview_url' => $upload_result['preview_url'],
                'metadata' => [
                    'size_kb' => $upload_result['size_kb'],
                    'dimensions' => $upload_result['width'] . 'x' . $upload_result['height'],
                    'format' => $upload_result['format']
                ]
            ]
        ]);
    }
    
    /**
     * 🔗 ADD URL MEDIA - Validazione e salvataggio URL esterni
     */
    private function addUrlMedia() {
        $azienda_id = (int)($_POST['azienda_id'] ?? 0);
        $tipo_media = $_POST['tipo_media'] ?? 'galleria';
        $url = trim($_POST['media_url'] ?? '');
        
        // Durante la registrazione (azienda_id=0), salviamo temporaneamente in sessione
        if (!$azienda_id) {
            session_start();
            if (!isset($_SESSION['temp_media'])) {
                $_SESSION['temp_media'] = [];
            }
            
            // Controllo limite temporaneo (max 5 media in sessione)
            if (count($_SESSION['temp_media']) >= 5) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Limite media temporaneo raggiunto (max 5)',
                    'error_code' => 'TEMP_LIMIT_EXCEEDED'
                ], 400);
            }
            
            if (!$url) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'URL mancante',
                    'error_code' => 'MISSING_URL'
                ], 400);
            }
            
            // Determina tipo URL
            $media_type = 'link';
            if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
                $media_type = 'youtube';
            }
            
            // Salva info in sessione
            $media_data = [
                'id' => uniqid('temp_'),
                'url' => $url,
                'tipo' => $media_type,
                'tipo_media' => $tipo_media,
                'timestamp' => time()
            ];
            
            $_SESSION['temp_media'][] = $media_data;
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'URL aggiunto temporaneamente per registrazione',
                'data' => [
                    'media_id' => $media_data['id'],
                    'preview_url' => $url,
                    'media_type' => $media_type,
                    'temp_mode' => true
                ]
            ]);
        }
        
        if (!$url) {
            throw new Exception('URL mancante', 'MISSING_URL');
        }
        
        // Verifica limiti piano per azienda esistente
        $limits_check = $this->checkPlanLimits($azienda_id, $tipo_media);
        if (!$limits_check['can_add']) {
            throw new Exception($limits_check['message'], 'PLAN_LIMIT_EXCEEDED');
        }
        
        // Validazione URL
        $validation = $this->validateMediaUrl($url);
        if (!$validation['valid']) {
            throw new Exception($validation['message'], 'URL_VALIDATION_ERROR');
        }
        
        // Salvataggio in database
        $media_id = $this->saveMediaToDatabase([
            'azienda_id' => $azienda_id,
            'tipo_media' => $tipo_media,
            'url_esterno' => $url,
            'tipo_sorgente' => 'url',
            'formato' => $validation['format'],
            'larghezza' => $validation['width'],
            'altezza' => $validation['height']
        ]);
        
        return $this->jsonResponse([
            'success' => true,
            'message' => 'URL media aggiunto con successo',
            'data' => [
                'media_id' => $media_id,
                'url' => $url,
                'preview_url' => $url,
                'metadata' => [
                    'dimensions' => ($validation['width'] && $validation['height']) ? 
                                  $validation['width'] . 'x' . $validation['height'] : 'Auto',
                    'format' => $validation['format'] ?: 'Rilevato automaticamente'
                ]
            ]
        ]);
    }
    
    /**
     * 📋 LIST MEDIA - Recupera tutti i media di un'azienda
     */
    private function listMedia() {
        $azienda_id = (int)($_GET['azienda_id'] ?? 0);
        
        // Durante la registrazione, azienda_id è 0 - restituire lista vuota
        if (!$azienda_id) {
            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'media' => [],
                    'limits' => $this->getMediaLimitsForCompany(0),
                    'stats' => [
                        'total_media' => 0,
                        'logo_count' => 0,
                        'galleria_count' => 0
                    ]
                ]
            ]);
        }
        
        $stmt = $this->conn->prepare("
            SELECT id, tipo_media, nome_file, percorso_file, url_esterno, 
                   tipo_sorgente, formato, dimensione_kb, larghezza, altezza,
                   ordine_visualizzazione, data_caricamento
            FROM azienda_media 
            WHERE azienda_id = ? AND attivo = 1 
            ORDER BY tipo_media DESC, ordine_visualizzazione ASC
        ");
        
        $stmt->bind_param('i', $azienda_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $media_list = [];
        while ($row = $result->fetch_assoc()) {
            $media_list[] = [
                'id' => $row['id'],
                'tipo' => $row['tipo_media'],
                'nome' => $row['nome_file'] ?: 'URL Esterno',
                'url' => $row['percorso_file'] ? 
                        ('uploads/aziende/' . $row['percorso_file']) : 
                        $row['url_esterno'],
                'sorgente' => $row['tipo_sorgente'],
                'formato' => $row['formato'],
                'dimensioni' => ($row['larghezza'] && $row['altezza']) ? 
                              $row['larghezza'] . 'x' . $row['altezza'] : null,
                'size_kb' => $row['dimensione_kb'],
                'ordine' => $row['ordine_visualizzazione'],
                'data_upload' => $row['data_caricamento']
            ];
        }
        
        // Ottieni anche i limiti attuali
        $limits = $this->getMediaLimitsForCompany($azienda_id);
        
        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'media' => $media_list,
                'limits' => $limits,
                'stats' => [
                    'total_media' => count($media_list),
                    'logo_count' => count(array_filter($media_list, function($m) { return $m['tipo'] === 'logo'; })),
                    'galleria_count' => count(array_filter($media_list, function($m) { return $m['tipo'] === 'galleria'; }))
                ]
            ]
        ]);
    }
    
    /**
     * 🔄 REORDER MEDIA - Cambia ordine visualizzazione
     */
    private function reorderMedia() {
        $media_ids = json_decode(file_get_contents('php://input'), true)['media_ids'] ?? [];
        
        if (empty($media_ids)) {
            throw new Exception('Nessun media da riordinare');
        }
        
        $this->conn->begin_transaction();
        
        try {
            $stmt = $this->conn->prepare("UPDATE azienda_media SET ordine_visualizzazione = ? WHERE id = ?");
            
            foreach ($media_ids as $index => $media_id) {
                $new_order = $index + 1;
                $stmt->bind_param('ii', $new_order, $media_id);
                $stmt->execute();
            }
            
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception('Errore durante riordinamento: ' . $e->getMessage());
        }
        
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Ordine media aggiornato con successo'
        ]);
    }
    
    /**
     * 🗑️ REMOVE MEDIA - Rimozione sicura (soft delete)
     */
    private function removeMedia() {
        $media_id = (int)($_GET['media_id'] ?? 0);
        
        if (!$media_id) {
            throw new Exception('ID media mancante');
        }
        
        // Soft delete per sicurezza
        $stmt = $this->conn->prepare("UPDATE azienda_media SET attivo = 0 WHERE id = ?");
        $stmt->bind_param('i', $media_id);
        
        if (!$stmt->execute() || $stmt->affected_rows === 0) {
            throw new Exception('Media non trovato o già rimosso');
        }
        
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Media rimosso con successo'
        ]);
    }
    
    /**
     * 📊 VERIFICA LIMITI PIANO
     */
    private function checkPlanLimits($azienda_id, $tipo_media) {
        // Usa stored procedure creata nel database
        $stmt = $this->conn->prepare("CALL CheckMediaLimits(?, ?, @can_add, @message)");
        $stmt->bind_param('is', $azienda_id, $tipo_media);
        $stmt->execute();
        
        $result = $this->conn->query("SELECT @can_add as can_add, @message as message");
        $limits = $result->fetch_assoc();
        
        return [
            'can_add' => (bool)$limits['can_add'],
            'message' => $limits['message']
        ];
    }
    
    /**
     * ✅ VALIDAZIONE FILE UPLOAD
     */
    private function validateUploadedFile($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'Errore durante upload: ' . $this->getUploadErrorMessage($file['error'])];
        }
        
        if ($file['size'] > $this->max_file_size) {
            return ['valid' => false, 'message' => 'File troppo grande. Max: ' . ($this->max_file_size / 1024 / 1024) . 'MB'];
        }
        
        $file_info = pathinfo($file['name']);
        $extension = strtolower($file_info['extension'] ?? '');
        
        if (!in_array($extension, $this->allowed_formats)) {
            return ['valid' => false, 'message' => 'Formato non supportato. Formati consentiti: ' . implode(', ', $this->allowed_formats)];
        }
        
        // Verifica MIME type per sicurezza
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowed_mimes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 
            'image/webp', 'image/avif'
        ];
        
        if (!in_array($mime_type, $allowed_mimes)) {
            return ['valid' => false, 'message' => 'Tipo file non valido'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * ✅ VALIDAZIONE URL MEDIA
     */
    private function validateMediaUrl($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['valid' => false, 'message' => 'URL non valido'];
        }
        
        // Verifica accessibility URL (con timeout)
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'method' => 'HEAD',
                'user_agent' => 'MediaValidator/1.0'
            ]
        ]);
        
        $headers = @get_headers($url, 1, $context);
        
        if (!$headers || strpos($headers[0], '200') === false) {
            return ['valid' => false, 'message' => 'URL non accessibile o non trovato'];
        }
        
        // Estrai informazioni se possibile
        $format = null;
        $width = null;
        $height = null;
        
        if (isset($headers['Content-Type'])) {
            $content_type = is_array($headers['Content-Type']) ? 
                          $headers['Content-Type'][0] : $headers['Content-Type'];
            
            if (strpos($content_type, 'image/') === 0) {
                $format = str_replace('image/', '', $content_type);
                
                // Tenta di ottenere dimensioni immagine
                $image_info = @getimagesize($url);
                if ($image_info) {
                    $width = $image_info[0];
                    $height = $image_info[1];
                }
            }
        }
        
        return [
            'valid' => true,
            'format' => $format,
            'width' => $width,
            'height' => $height
        ];
    }
    
    /**
     * 🔄 PROCESSO UPLOAD FILE
     */
    private function processFileUpload($file, $azienda_id, $tipo_media) {
        $file_info = pathinfo($file['name']);
        $extension = strtolower($file_info['extension']);
        
        // Genera nome file unico
        $unique_name = $azienda_id . '_' . $tipo_media . '_' . uniqid() . '.' . $extension;
        $target_path = $this->upload_dir . $unique_name;
        
        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            return ['success' => false, 'error' => 'Impossibile salvare il file'];
        }
        
        // Ottieni dimensioni immagine
        $image_info = @getimagesize($target_path);
        $width = $image_info[0] ?? null;
        $height = $image_info[1] ?? null;
        
        // Ottieni dimensione file in KB
        $size_kb = round(filesize($target_path) / 1024, 2);
        
        return [
            'success' => true,
            'file_path' => $unique_name,
            'preview_url' => 'uploads/aziende/' . $unique_name,
            'original_name' => $file['name'],
            'format' => $extension,
            'size_kb' => $size_kb,
            'width' => $width,
            'height' => $height
        ];
    }
    
    /**
     * 💾 SALVATAGGIO DATABASE
     */
    private function saveMediaToDatabase($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO azienda_media 
            (azienda_id, tipo_media, nome_file, percorso_file, url_esterno, 
             tipo_sorgente, formato, dimensione_kb, larghezza, altezza) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param('issssssdii', 
            $data['azienda_id'],
            $data['tipo_media'],
            $data['nome_file'] ?? null,
            $data['percorso_file'] ?? null,
            $data['url_esterno'] ?? null,
            $data['tipo_sorgente'],
            $data['formato'] ?? null,
            $data['dimensione_kb'] ?? null,
            $data['larghezza'] ?? null,
            $data['altezza'] ?? null
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Errore salvataggio database: ' . $stmt->error);
        }
        
        return $this->conn->insert_id;
    }
    
    /**
     * 📊 LIMITI MEDIA PER AZIENDA
     */
    private function getMediaLimits() {
        $azienda_id = (int)($_GET['azienda_id'] ?? 0);
        return $this->jsonResponse(['success' => true, 'data' => $this->getMediaLimitsForCompany($azienda_id)]);
    }
    
    private function getMediaLimitsForCompany($azienda_id) {
        $stmt = $this->conn->prepare("
            SELECT a.piano, p.max_media_totali, p.max_media_galleria, p.max_file_size_mb,
                   COUNT(m.id) as current_total,
                   SUM(CASE WHEN m.tipo_media = 'galleria' THEN 1 ELSE 0 END) as current_galleria
            FROM aziende a
            LEFT JOIN piani_media_limits p ON a.piano = p.piano
            LEFT JOIN azienda_media m ON a.id = m.azienda_id AND m.attivo = 1
            WHERE a.id = ?
            GROUP BY a.id
        ");
        
        $stmt->bind_param('i', $azienda_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        // 🔧 FIX CRITICO: Controllo su $result per evitare warning PHP
        if (!$result) {
            // Se azienda non trovata, restituisci valori default per piano base
            return [
                'piano' => 'foto',
                'max_totali' => 5,
                'max_galleria' => 5,
                'max_file_mb' => 2,
                'current_total' => 0,
                'current_galleria' => 0,
                'can_add_logo' => true,
                'can_add_galleria' => true
            ];
        }
        
        // Se result esiste ma alcuni campi sono null, usa valori default
        $piano = $result['piano'] ?? 'foto';
        $max_totali = (int)($result['max_media_totali'] ?? 5);
        $max_galleria = (int)($result['max_media_galleria'] ?? 5);
        $max_file_mb = (int)($result['max_file_size_mb'] ?? 2);
        $current_total = (int)($result['current_total'] ?? 0);
        $current_galleria = (int)($result['current_galleria'] ?? 0);
        
        return [
            'piano' => $piano,
            'max_totali' => $max_totali,
            'max_galleria' => $max_galleria,
            'max_file_mb' => $max_file_mb,
            'current_total' => $current_total,
            'current_galleria' => $current_galleria,
            'can_add_logo' => true, // Logo sempre sostituibile
            'can_add_galleria' => $current_galleria < $max_galleria
        ];
    }
    
    /**
     * 🎨 UTILITY FUNCTIONS
     */
    private function jsonResponse($data, $http_code = 200) {
        http_response_code($http_code);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    private function getUploadErrorMessage($error_code) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File troppo grande (limite server)',
            UPLOAD_ERR_FORM_SIZE => 'File troppo grande (limite form)', 
            UPLOAD_ERR_PARTIAL => 'Upload parziale - riprova',
            UPLOAD_ERR_NO_FILE => 'Nessun file selezionato',
            UPLOAD_ERR_NO_TMP_DIR => 'Directory temporanea mancante',
            UPLOAD_ERR_CANT_WRITE => 'Impossibile scrivere su disco',
            UPLOAD_ERR_EXTENSION => 'Upload bloccato da estensione PHP'
        ];
        
        return $errors[$error_code] ?? 'Errore sconosciuto';
    }
}

// 🚀 INIZIALIZZAZIONE API
if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    $media_manager = new MediaManager($conn);
    $media_manager->handleRequest();
}
?>
