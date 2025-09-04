<?php
// File per le funzioni riutilizzabili del sito

/**
 * Processa gli upload multipli di media (immagini, video, URL) e restituisce un array con i percorsi salvati.
 * 
 * @param array $files Array di file $_FILES['media_files']
 * @param array $media_types Array con i tipi di media (image, video, youtube, vimeo)
 * @param array $media_urls Array con gli URL per i tipi youtube/vimeo
 * @param string $upload_dir Directory di upload relativa
 * @param int $max_files Numero massimo di file (default: 5)
 * @return array Array con i percorsi dei file salvati e gli URL, vuoto se errore
 */
function process_multiple_media($files, $media_types, $media_urls, $upload_dir, $max_files = 5) {
    $saved_paths = [];
    $count = 0;
    
    // Assicuriamoci che la directory esista
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Processa ogni file/URL
    for ($i = 0; $i < count($media_types) && $count < $max_files; $i++) {
        $type = $media_types[$i];
        
        // Gestione URL esterni (YouTube/Vimeo)
        if ($type === 'youtube' || $type === 'vimeo') {
            if (!empty($media_urls[$i])) {
                $saved_paths['media' . ($count + 1) . '_url'] = sanitize_url($media_urls[$i]);
                $count++;
            }
            continue;
        }
        
        // Gestione upload file (immagini/video)
        if (isset($files['name'][$i]) && $files['error'][$i] === UPLOAD_ERR_OK) {
            $tmp_name = $files['tmp_name'][$i];
            $name = $files['name'][$i];
            $file_ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            
            // Valida estensione file in base al tipo
            $allowed_exts = [];
            if ($type === 'image') {
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            } elseif ($type === 'video') {
                $allowed_exts = ['mp4', 'webm', 'ogg'];
            }
            
            if (!in_array($file_ext, $allowed_exts)) {
                continue; // Salta file non validi
            }
            
            // Genera nome file univoco
            $new_filename = uniqid() . '-' . bin2hex(random_bytes(8)) . '.' . $file_ext;
            $dest_path = $upload_dir . $new_filename;
            
            // Sposta il file
            if (move_uploaded_file($tmp_name, $dest_path)) {
                $saved_paths['media' . ($count + 1) . '_url'] = $dest_path;
                $count++;
            }
        }
        
        // Limita il numero di file
        if ($count >= $max_files) break;
    }
    
    return $saved_paths;
}

/**
 * Pulisce una stringa di input generica.
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Valida e pulisce un indirizzo email.
 */
function sanitize_email($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Valida e pulisce un URL.
 */
function sanitize_url($url) {
    return filter_var(trim($url), FILTER_SANITIZE_URL);
}

/**
 * Tronca una stringa a una lunghezza massima.
 */
function truncate_text($text, $length) {
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length);
        $text .= '...';
    }
    return $text;
}

/**
 * Gestisce l'upload di un file in modo sicuro.
 *
 * @param string $file_input_name Il nome del campo input type="file".
 * @param string $upload_dir La cartella di destinazione.
 * @return string|null Il percorso del file caricato o null in caso di fallimento/assenza.
 */
function upload_file($file_input_name, $upload_dir) {
    // Controlla se il file è stato caricato e non ci sono errori
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] === UPLOAD_ERR_OK) {
        return upload_file_single($_FILES[$file_input_name], $upload_dir);
    }
    return null;
}

/**
 * Gestisce l'upload di un singolo file (per upload multipli).
 *
 * @param array $file_data Array con i dati del file ($_FILES[field]).
 * @param string $upload_dir La cartella di destinazione.
 * @return string|array Il percorso del file caricato o array con errore.
 */
function upload_file_single($file_data, $upload_dir) {
    // Controlla se il file è valido
    if ($file_data['error'] === UPLOAD_ERR_OK) {
        
        // Assicurati che la directory di upload esista, altrimenti creala
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                error_log("Impossibile creare la cartella di upload: " . $upload_dir);
                return null; // Non è stato possibile creare la cartella
            }
        }

        $tmp_name = $file_data['tmp_name'];
        
        // Crea un nome univoco per il file per evitare sovrascritture e problemi di sicurezza
        $file_extension = strtolower(pathinfo($file_data['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            error_log("Tentativo di upload di un file non consentito: " . $file_extension);
            return array('error' => 'Formato file non supportato. Formati consentiti: ' . implode(', ', $allowed_extensions));
        }

        // Verifica dimensioni file (limite diverso per video e immagini)
        $max_size = in_array($file_extension, ['mp4', 'webm', 'ogg']) ? 100 * 1024 * 1024 : 5 * 1024 * 1024; // 100MB video, 5MB immagini
        if ($file_data['size'] > $max_size) {
            $max_mb = $max_size / (1024 * 1024);
            return array('error' => 'File troppo grande. Massimo consentito: ' . $max_mb . 'MB');
        }

        // Prefisso diverso per video e immagini
        $prefix = in_array($file_extension, ['mp4', 'webm', 'ogg']) ? 'video_' : 'img_';
        $file_name = uniqid($prefix, true) . '.' . $file_extension;
        $destination = $upload_dir . $file_name;

        // Sposta il file nella destinazione finale
        if (move_uploaded_file($tmp_name, $destination)) {
            return $destination; // Ritorna il percorso relativo del file
        } else {
            error_log("Impossibile spostare il file caricato a: " . $destination);
        }
    }
    
    // Ritorna null se non c'è file, c'è un errore, o lo spostamento fallisce
    return null;
}

/**
 * SISTEMA AVANZATO GESTIONE MEDIA - IDIOT-PROOF
 * =====================================================
 */

/**
 * Normalizza automaticamente i link YouTube in tutti i formati possibili
 * 
 * @param string $url URL YouTube in qualsiasi formato
 * @return array Array con ['success' => bool, 'video_id' => string, 'embed_url' => string, 'error' => string]
 */
function normalize_youtube_url($url) {
    if (empty($url)) {
        return ['success' => false, 'error' => 'URL vuoto'];
    }
    
    // Rimuovi spazi e caratteri speciali
    $url = trim($url);
    
    // Pattern per estrarre l'ID video da tutti i formati YouTube possibili
    $patterns = [
        // Standard: https://www.youtube.com/watch?v=VIDEO_ID
        '/(?:youtube\.com\/watch\?v=)([a-zA-Z0-9_-]{11})/',
        // Corto: https://youtu.be/VIDEO_ID
        '/(?:youtu\.be\/)([a-zA-Z0-9_-]{11})/',
        // Embed: https://www.youtube.com/embed/VIDEO_ID
        '/(?:youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/',
        // Con timestamp: https://www.youtube.com/watch?v=VIDEO_ID&t=123s
        '/(?:youtube\.com\/watch\?.*v=)([a-zA-Z0-9_-]{11})/',
        // Mobile: https://m.youtube.com/watch?v=VIDEO_ID
        '/(?:m\.youtube\.com\/watch\?v=)([a-zA-Z0-9_-]{11})/',
    ];
    
    $video_id = null;
    
    // Prova tutti i pattern
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            $video_id = $matches[1];
            break;
        }
    }
    
    if (!$video_id) {
        return ['success' => false, 'error' => 'URL YouTube non valido o ID video non trovato'];
    }
    
    // Genera URL embed pulito
    $embed_url = "https://www.youtube.com/embed/{$video_id}";
    
    return [
        'success' => true,
        'video_id' => $video_id,
        'embed_url' => $embed_url,
        'thumbnail_url' => "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg",
        'error' => null
    ];
}

/**
 * Valida un link immagine per verificare che punti a un file immagine valido
 * 
 * @param string $url URL del link da validare
 * @return array Array con ['success' => bool, 'error' => string|null]
 */
function validate_image_link($url) {
    // Validazione base URL
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        return ['success' => false, 'error' => 'URL non valido'];
    }
    
    // Validazione estensione
    $valid_extensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp', '.svg'];
    $url_lower = strtolower($url);
    $has_valid_extension = false;
    
    foreach ($valid_extensions as $ext) {
        if (strpos($url_lower, $ext) !== false) {
            $has_valid_extension = true;
            break;
        }
    }
    
    if (!$has_valid_extension) {
        return ['success' => false, 'error' => 'Estensione file non valida. Supportate: ' . implode(', ', $valid_extensions)];
    }
    
    // Validazione headers HTTP (verifica accessibilità e content-type)
    $context = stream_context_create([
        'http' => [
            'method' => 'HEAD',
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (compatible; ImageValidator/1.0)'
        ]
    ]);
    
    $headers = @get_headers($url, 1, $context);
    
    if (!$headers) {
        return ['success' => false, 'error' => 'Impossibile accedere al link fornito'];
    }
    
    // Verifica status code HTTP
    $status_line = $headers[0] ?? '';
    if (!preg_match('/HTTP\/\d\.\d\s+2\d\d/', $status_line)) {
        return ['success' => false, 'error' => 'Link non accessibile (errore HTTP)'];
    }
    
    // Verifica Content-Type
    $content_type = $headers['Content-Type'] ?? $headers['content-type'] ?? '';
    if (is_array($content_type)) {
        $content_type = end($content_type);
    }
    
    $valid_mime_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/svg+xml'];
    $is_valid_mime = false;
    
    foreach ($valid_mime_types as $mime) {
        if (stripos($content_type, $mime) !== false) {
            $is_valid_mime = true;
            break;
        }
    }
    
    if (!$is_valid_mime) {
        return ['success' => false, 'error' => 'Il link non punta a un\'immagine valida (Content-Type: ' . $content_type . ')'];
    }
    
    // Verifica dimensione file (opzionale)
    $content_length = $headers['Content-Length'] ?? $headers['content-length'] ?? null;
    if (is_array($content_length)) {
        $content_length = end($content_length);
    }
    
    if ($content_length && $content_length > 10 * 1024 * 1024) { // 10MB
        return ['success' => false, 'error' => 'File troppo grande (max 10MB)'];
    }
    
    return ['success' => true, 'error' => null];
}

/**
 * Processa e valida un array di media (foto, video, link) con gestione flessibile
 * 
 * @param array $files Array $_FILES per i file caricati
 * @param array $media_types Tipi di media per ogni slot ['image', 'video', 'youtube', 'vimeo']
 * @param array $media_urls URL per i link esterni (YouTube, Vimeo)
 * @param string $package_type Tipo pacchetto ('foto' o 'foto_video')
 * @param string $upload_dir Directory di upload
 * @return array Array con ['success' => bool, 'media' => array, 'error' => string]
 */
function process_flexible_media($files, $media_types, $media_urls, $package_type, $upload_dir = 'uploads/') {
    // Assicurati che la directory esista
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $processed_media = [];
    $max_items = 3; // LIMITE FISSO: 3 media totali per tutti i pacchetti
    $allowed_photo_only = ($package_type === 'foto');
    
    if (!$media_types || !is_array($media_types)) {
        return ['success' => true, 'media' => [], 'error' => null];
    }
    
    $media_count = 0;
    
    for ($i = 0; $i < count($media_types) && $media_count < $max_items; $i++) {
        $media_type = $media_types[$i] ?? null;
        $media_item = [
            'type' => $media_type,
            'url' => null,
            'embed_url' => null,
            'thumbnail_url' => null,
            'error' => null
        ];
        
        if ($media_type === 'image') {
            // Gestione immagine: upload file O link diretto
            $url = $media_urls[$i] ?? '';
            
            if (!empty($url)) {
                // VALIDAZIONE ROBUSTA LINK IMMAGINE BACKEND
                $validation_result = validate_image_link($url);
                if ($validation_result['success']) {
                    $media_item['url'] = $url;
                    $media_item['is_external'] = true;
                    $processed_media[] = $media_item;
                    $media_count++;
                } else {
                    $media_item['error'] = $validation_result['error'];
                    $processed_media[] = $media_item;
                    $media_count++;
                }
            } elseif (isset($files['name'][$i]) && $files['error'][$i] === UPLOAD_ERR_OK) {
                // Upload file immagine
                $uploaded_path = upload_file_single([
                    'name' => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ], $upload_dir);
                
                if ($uploaded_path) {
                    $media_item['url'] = $uploaded_path;
                    $media_item['is_external'] = false;
                    $processed_media[] = $media_item;
                    $media_count++;
                }
            }
        } elseif ($media_type === 'video' && !$allowed_photo_only) {
            // Upload video (solo se pacchetto lo supporta)
            if (isset($files['name'][$i]) && $files['error'][$i] === UPLOAD_ERR_OK) {
                $uploaded_path = upload_file_single([
                    'name' => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ], $upload_dir);
                
                if ($uploaded_path) {
                    $media_item['url'] = $uploaded_path;
                    $processed_media[] = $media_item;
                    $media_count++;
                }
            }
        } elseif ($media_type === 'youtube' && !$allowed_photo_only) {
            // Link YouTube
            $url = $media_urls[$i] ?? '';
            if (!empty($url)) {
                $normalized = normalize_youtube_url($url);
                if ($normalized['success']) {
                    $media_item['url'] = $normalized['embed_url'];
                    $media_item['embed_url'] = $normalized['embed_url'];
                    $media_item['thumbnail_url'] = $normalized['thumbnail_url'];
                } else {
                    $media_item['error'] = $normalized['error'];
                }
                $processed_media[] = $media_item;
                $media_count++;
            }
        } elseif ($media_type === 'vimeo' && !$allowed_photo_only) {
            // Link Vimeo
            $url = $media_urls[$i] ?? '';
            if (!empty($url)) {
                $normalized = normalize_vimeo_url($url);
                if ($normalized['success']) {
                    $media_item['url'] = $normalized['embed_url'];
                    $media_item['embed_url'] = $normalized['embed_url'];
                } else {
                    $media_item['error'] = $normalized['error'];
                }
                $processed_media[] = $media_item;
                $media_count++;
            }
        }
    }
    
    return [
        'success' => true,
        'media' => $processed_media,
        'count' => $media_count,
        'error' => null
    ];
}

/**
 * Salva i media in formato JSON nella tabella aziende
 * 
 * @param array $media_array Array dei media processati
 * @return string JSON string per il database
 */
function serialize_media_for_db($media_array) {
    if (empty($media_array)) {
        return json_encode([]);
    }
    
    return json_encode($media_array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/**
 * Recupera e deserializza i media dal database
 * 
 * @param string $media_json JSON string dal database
 * @return array Array dei media
 */
function deserialize_media_from_db($media_json) {
    if (empty($media_json)) {
        return [];
    }
    
    $decoded = json_decode($media_json, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * BACKWARD COMPATIBILITY: Converte i vecchi campi foto1_url, foto2_url, foto3_url in formato JSON
 * 
 * @param array $azienda_data Dati azienda dal database
 * @return array Array media nel nuovo formato
 */
function convert_legacy_media_to_flexible($azienda_data) {
    $media = [];
    
    // Converti le foto esistenti
    for ($i = 1; $i <= 3; $i++) {
        $foto_key = "foto{$i}_url";
        if (!empty($azienda_data[$foto_key])) {
            $media[] = [
                'type' => 'image',
                'url' => $azienda_data[$foto_key],
                'embed_url' => null,
                'thumbnail_url' => null,
                'error' => null
            ];
        }
    }
    
    return $media;
}

?>

