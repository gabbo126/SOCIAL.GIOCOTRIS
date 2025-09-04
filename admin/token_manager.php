<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Assicurati che i percorsi siano corretti risalendo dalla posizione di questo file
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';

// Aggiungi il campo tipo_pacchetto alla tabella token se non esiste
try {
    $conn->query("ALTER TABLE tokens ADD COLUMN IF NOT EXISTS tipo_pacchetto ENUM('foto', 'foto_video') DEFAULT 'foto'");
} catch (Exception $e) {
    // Il campo esiste già o errore
    error_log('Errore aggiunta campo tipo_pacchetto: ' . $e->getMessage());
}

// Sicurezza: controlla se l'utente è un admin loggato
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die('Accesso non autorizzato.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action = $_POST['action'];

    // --- Genera Token (Creazione o Modifica) ---
    if ($action === 'generate_token') {
        $type = $_POST['type'] ?? '';
        $tipo_pacchetto = $_POST['tipo_pacchetto'] ?? 'foto'; // Default: solo foto
        $validita = isset($_POST['validita']) ? (int)$_POST['validita'] : 24; // Validità in ore, default 24
        $scadenza = date('Y-m-d H:i:s', strtotime("+{$validita} hours"));
        $token = bin2hex(random_bytes(32));
        
        // 🔍 DEBUG: Log dei valori ricevuti
        error_log("[TOKEN_MANAGER] POST type: '" . $type . "'");
        error_log("[TOKEN_MANAGER] Action: '" . $action . "'");
        
        // Token di CREAZIONE - VALIDAZIONE ROBUSTA
        if ($type === 'creation') {
            // 🎯 GARANTISCI che il campo type sia SEMPRE 'creazione'
            $db_type = 'creazione';  // Hardcoded per sicurezza
            
            $stmt = $conn->prepare("INSERT INTO tokens (token, type, data_scadenza, status, tipo_pacchetto) VALUES (?, ?, ?, 'attivo', ?)");
            $stmt->bind_param('ssss', $token, $db_type, $scadenza, $tipo_pacchetto);
            
            if ($stmt->execute()) {
                error_log("[TOKEN_MANAGER] ✅ Token creazione inserito con successo: ID " . $conn->insert_id);
                
                // 🧪 VERIFICA IMMEDIATA: Controlla che il type sia corretto
                $verify = $conn->prepare("SELECT type FROM tokens WHERE token = ?");
                $verify->bind_param('s', $token);
                $verify->execute();
                $result = $verify->get_result()->fetch_assoc();
                $actual_type = $result['type'] ?? 'NULL';
                
                error_log("[TOKEN_MANAGER] 🔍 Verifica type inserito: '" . $actual_type . "'");
                
                if ($actual_type !== 'creazione') {
                    error_log("[TOKEN_MANAGER] ❌ ERRORE CRITICO: Type non corretto! Atteso: 'creazione', Trovato: '" . $actual_type . "'");
                    $_SESSION['error_message'] = 'ERRORE: Campo type non salvato correttamente. Contatta amministratore.';
                    header('Location: dashboard_new.php');
                    exit;
                }
            } else {
                error_log("[TOKEN_MANAGER] ❌ ERRORE inserimento token: " . $stmt->error);
                $_SESSION['error_message'] = 'Errore durante la creazione del token: ' . $stmt->error;
                header('Location: dashboard_new.php');
                exit;
            }
            
            $_SESSION['last_token'] = [
                'token' => $token,
                'type' => 'Creazione',
                'link' => BASE_URL . '/register_company.php?token=' . $token,
                'data_creazione' => date('Y-m-d H:i:s'),
                'data_scadenza' => $scadenza,
                'tipo_pacchetto' => $tipo_pacchetto
            ];
            $_SESSION['success_message'] = 'Token di CREAZIONE generato con successo!';
        }
        // Token di MODIFICA
        else if ($type === 'modification') {
            $id_azienda = filter_input(INPUT_POST, 'id_azienda', FILTER_VALIDATE_INT);
            
            if (!$id_azienda) {
                $_SESSION['error_message'] = 'ID azienda non valido o non fornito.';
            } else {
                $check_stmt = $conn->prepare("SELECT id FROM aziende WHERE id = ?");
                $check_stmt->bind_param('i', $id_azienda);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows === 0) {
                    $_SESSION['error_message'] = 'Nessuna azienda trovata con l\'ID fornito.';
                } else {
                    $stmt = $conn->prepare("INSERT INTO tokens (token, type, id_azienda, data_scadenza, status, tipo_pacchetto) VALUES (?, 'modifica', ?, ?, 'attivo', ?)");
                    $stmt->bind_param('siss', $token, $id_azienda, $scadenza, $tipo_pacchetto);
                    $stmt->execute();

                    $_SESSION['last_token'] = [
                        'token' => $token,
                        'type' => 'Modifica',
                        'link' => BASE_URL . '/modifica_azienda_token.php?token=' . $token,
                        'data_creazione' => date('Y-m-d H:i:s'),
                        'data_scadenza' => $scadenza,
                        'id_azienda' => $id_azienda,
                        'tipo_pacchetto' => $tipo_pacchetto
                    ];
                    $_SESSION['success_message'] = 'Token di MODIFICA generato per l\'azienda ID ' . $id_azienda;
                }
            }
        }
    }

    // --- Disattiva un Token ---
    if ($action === 'deactivate_token') {
        $token_id = filter_input(INPUT_POST, 'token_id', FILTER_VALIDATE_INT);
        if ($token_id) {
            $stmt = $conn->prepare("UPDATE tokens SET status = 'disattivato' WHERE id = ? AND status = 'attivo'");
            $stmt->bind_param('i', $token_id);
            $stmt->execute();
            $_SESSION['success_message'] = 'Token disattivato con successo.';
        }
    }

    $conn->close();
    header('Location: dashboard.php');
    exit();
}
