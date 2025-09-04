<?php
// ===================================================================
// 🔍 DEBUG DASHBOARD LIVE - TRACE REAL EXECUTION
// ===================================================================
// ✅ Replica esatto flusso della dashboard per identificare root cause
// ===================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';
require_once 'config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>DEBUG DASHBOARD LIVE</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    .success { color: green; font-weight: bold; background: #e8f5e8; padding: 15px; border-left: 5px solid green; margin: 10px 0; }
    .error { color: red; font-weight: bold; background: #ffe6e6; padding: 15px; border-left: 5px solid red; margin: 10px 0; }
    .info { color: blue; background: #e6f3ff; padding: 15px; border-left: 5px solid blue; margin: 10px 0; }
    .warning { color: orange; font-weight: bold; background: #fff8e6; padding: 15px; border-left: 5px solid orange; margin: 10px 0; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #f2f2f2; font-weight: bold; }
    code { background: #f4f4f4; padding: 5px 8px; border-radius: 3px; font-family: monospace; }
    .timestamp { font-family: monospace; font-size: 0.9em; }
    .form-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #007bff; }
    .query { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";
echo "</head><body>";

echo "<h1>🔍 DEBUG DASHBOARD LIVE - TRACE REAL EXECUTION</h1>";
echo "<div class='warning'>🎯 Replica esatto flusso della dashboard per identificare la root cause</div>";
echo "<hr>";

$current_time = date('Y-m-d H:i:s');
echo "<div class='info'>⏰ <strong>Debug avviato:</strong> <span class='timestamp'>$current_time</span></div>";

// ==========================================
// FORM DI TEST IDENTICO ALLA DASHBOARD
// ==========================================
echo "<div class='form-section'>";
echo "<h2>🎯 FORM TEST IDENTICO ALLA DASHBOARD</h2>";
echo "<p><strong>Questo form replica esattamente il POST della dashboard:</strong></p>";

echo "<form method='POST' action=''>";
echo "<input type='hidden' name='action' value='generate_token'>";
echo "<input type='hidden' name='type' value='creation'>";
echo "<div style='margin: 10px 0;'>";
echo "<label>Validità (ore): <input type='number' name='validita' value='24' min='1' max='720' style='padding: 5px;'></label>";
echo "</div>";
echo "<button type='submit' style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;'>🧪 SIMULA CREAZIONE TOKEN</button>";
echo "</form>";
echo "</div>";

// ==========================================
// PROCESSING - REPLICA ESATTO CODICE token_manager.php
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    echo "<h2>🔄 PROCESSING LIVE - REPLICA token_manager.php</h2>";
    
    $action = $_POST['action'];
    echo "<div class='info'>📋 <strong>Action ricevuto:</strong> '$action'</div>";
    
    if ($action === 'generate_token') {
        $type = $_POST['type'] ?? '';
        $tipo_pacchetto = $_POST['tipo_pacchetto'] ?? 'foto';
        $validita = isset($_POST['validita']) ? (int)$_POST['validita'] : 24;
        $scadenza = date('Y-m-d H:i:s', strtotime("+{$validita} hours"));
        $token = bin2hex(random_bytes(32));
        
        echo "<div class='info'>📋 <strong>Parametri ricevuti:</strong></div>";
        echo "<table>";
        echo "<tr><td><strong>type</strong></td><td><code>'$type'</code></td></tr>";
        echo "<tr><td><strong>tipo_pacchetto</strong></td><td><code>'$tipo_pacchetto'</code></td></tr>";
        echo "<tr><td><strong>validita</strong></td><td><code>$validita ore</code></td></tr>";
        echo "<tr><td><strong>scadenza</strong></td><td><code>$scadenza</code></td></tr>";
        echo "<tr><td><strong>token</strong></td><td><code>" . substr($token, 0, 16) . "..." . substr($token, -8) . "</code></td></tr>";
        echo "</table>";
        
        echo "<div class='query'><strong>🔍 Controllo condizione:</strong> <code>if (\$type === 'creation')</code></div>";
        
        if ($type === 'creation') {
            echo "<div class='success'>✅ Condizione soddisfatta: type === 'creation'</div>";
            
            $db_type = 'creazione';
            echo "<div class='info'>🎯 <strong>db_type impostato:</strong> <code>'$db_type'</code></div>";
            
            echo "<div class='query'><strong>🔍 Query che verrà eseguita:</strong><br>";
            echo "<code>INSERT INTO tokens (token, type, data_scadenza, status, tipo_pacchetto) VALUES (?, ?, ?, 'attivo', ?)</code></div>";
            
            echo "<div class='info'><strong>🔗 Parametri binding:</strong> token='$token', type='$db_type', scadenza='$scadenza', pacchetto='$tipo_pacchetto'</div>";
            
            $stmt = $conn->prepare("INSERT INTO tokens (token, type, data_scadenza, status, tipo_pacchetto) VALUES (?, ?, ?, 'attivo', ?)");
            
            if (!$stmt) {
                echo "<div class='error'>❌ ERRORE PREPARE: " . $conn->error . "</div>";
            } else {
                echo "<div class='success'>✅ PREPARE OK</div>";
                
                $bind_result = $stmt->bind_param('ssss', $token, $db_type, $scadenza, $tipo_pacchetto);
                
                if (!$bind_result) {
                    echo "<div class='error'>❌ ERRORE BIND: " . $stmt->error . "</div>";
                } else {
                    echo "<div class='success'>✅ BIND OK</div>";
                    
                    $execute_result = $stmt->execute();
                    
                    if (!$execute_result) {
                        echo "<div class='error'>❌ ERRORE EXECUTE: " . $stmt->error . "</div>";
                    } else {
                        $insert_id = $conn->insert_id;
                        echo "<div class='success'>✅ EXECUTE OK - Insert ID: $insert_id</div>";
                        
                        // VERIFICA IMMEDIATA - REPLICA ESATTA del token_manager.php
                        echo "<h3>🧪 VERIFICA IMMEDIATA (replica esatta)</h3>";
                        
                        $verify = $conn->prepare("SELECT type FROM tokens WHERE token = ?");
                        $verify->bind_param('s', $token);
                        $verify->execute();
                        $result = $verify->get_result()->fetch_assoc();
                        $actual_type = $result['type'] ?? 'NULL';
                        
                        echo "<table>";
                        echo "<tr><td><strong>Type atteso</strong></td><td><code>'creazione'</code></td></tr>";
                        echo "<tr><td><strong>Type effettivo</strong></td><td><code>'$actual_type'</code></td></tr>";
                        echo "<tr><td><strong>Match</strong></td><td>" . ($actual_type === 'creazione' ? '✅ SÌ' : '❌ NO') . "</td></tr>";
                        echo "</table>";
                        
                        if ($actual_type !== 'creazione') {
                            echo "<div class='error'>❌ ERRORE CRITICO: Type non corretto! Atteso: 'creazione', Trovato: '$actual_type'</div>";
                            echo "<div class='warning'>⚠️ QUESTO È L'ERRORE CHE CAUSA IL PROBLEMA NELLA DASHBOARD!</div>";
                        } else {
                            echo "<div class='success'>✅ Type corretto! Il token è stato creato con successo!</div>";
                            echo "<div class='info'>🎯 Se questo test funziona ma la dashboard fallisce, il problema è nella session/redirect logic!</div>";
                        }
                        
                        // Token info per test
                        echo "<h3>🎫 INFO TOKEN PER TEST</h3>";
                        echo "<div class='info'>💎 <strong>URL di test:</strong><br>";
                        echo "<code>http://localhost/SOCIAL.GIOCOTRIS/register_company.php?token=$token</code></div>";
                        
                        // Cleanup opzionale
                        echo "<div style='margin-top: 20px;'>";
                        echo "<a href='?cleanup=$token' style='color: orange;'>[🧹 Elimina questo token test]</a>";
                        echo "</div>";
                    }
                }
            }
            
        } else {
            echo "<div class='error'>❌ Condizione NON soddisfatta: type '$type' !== 'creation'</div>";
            echo "<div class='warning'>⚠️ Questo potrebbe essere il problema! Verificare i dati POST.</div>";
        }
    }
}

// ==========================================
// CLEANUP TOKEN TEST
// ==========================================
if (isset($_GET['cleanup'])) {
    $cleanup_token = $_GET['cleanup'];
    $cleanup_stmt = $conn->prepare("DELETE FROM tokens WHERE token = ?");
    $cleanup_stmt->bind_param('s', $cleanup_token);
    if ($cleanup_stmt->execute()) {
        echo "<div class='success'>🧹 Token test eliminato con successo</div>";
    }
}

echo "<hr>";
echo "<p><strong>🕐 Debug completato:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='dashboard_new.php'>🚀 Dashboard Admin</a> | ";
echo "<a href='../test_specific_token.php'>🧪 Test Token</a></p>";
echo "</body></html>";
?>
