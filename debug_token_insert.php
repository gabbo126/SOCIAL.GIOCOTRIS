<?php
// ===================================================================
// 🚨 DEBUG PROFONDO TOKEN INSERT - ROOT CAUSE ANALYSIS
// ===================================================================
// ✅ Analizza struttura tabella, query insert, binding parametri
// ===================================================================

require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>DEBUG TOKEN INSERT</title>";
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
    .query { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";
echo "</head><body>";

echo "<h1>🚨 DEBUG PROFONDO TOKEN INSERT - ROOT CAUSE ANALYSIS</h1>";
echo "<div class='warning'>🎯 Analisi completa: struttura tabella, query, binding, charset, collation</div>";
echo "<hr>";

$current_time = date('Y-m-d H:i:s');
echo "<div class='info'>⏰ <strong>Debug avviato:</strong> <span class='timestamp'>$current_time</span></div>";

// ==========================================
// STEP 1: STRUTTURA TABELLA TOKENS
// ==========================================
echo "<h2>STEP 1: 🗃️ STRUTTURA TABELLA TOKENS</h2>";

try {
    $describe = $conn->query("DESCRIBE tokens");
    if ($describe) {
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $describe->fetch_assoc()) {
            $highlight = '';
            if ($row['Field'] === 'type') {
                $highlight = ' style="background-color: #ffffcc; font-weight: bold;"';
            }
            echo "<tr$highlight>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Informazioni charset/collation
    echo "<h3>🔤 CHARSET E COLLATION</h3>";
    $charset_info = $conn->query("SELECT @@character_set_database, @@collation_database");
    if ($charset_info) {
        $charset = $charset_info->fetch_array();
        echo "<table>";
        echo "<tr><td><strong>character_set_database</strong></td><td>" . $charset[0] . "</td></tr>";
        echo "<tr><td><strong>collation_database</strong></td><td>" . $charset[1] . "</td></tr>";
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE STRUTTURA: " . $e->getMessage() . "</div>";
}

// ==========================================
// STEP 2: TEST INSERT SIMULATO
// ==========================================
echo "<h2>STEP 2: 🧪 TEST INSERT SIMULATO</h2>";

try {
    // Genera dati test
    $test_token = 'DEBUG_TOKEN_' . time();
    $test_type = 'creazione';
    $test_scadenza = date('Y-m-d H:i:s', strtotime('+24 hours'));
    $test_status = 'attivo';
    $test_pacchetto = 'foto';
    
    echo "<div class='info'>📋 <strong>Dati test:</strong></div>";
    echo "<table>";
    echo "<tr><td><strong>token</strong></td><td><code>$test_token</code></td></tr>";
    echo "<tr><td><strong>type</strong></td><td><code>$test_type</code></td></tr>";
    echo "<tr><td><strong>data_scadenza</strong></td><td><code>$test_scadenza</code></td></tr>";
    echo "<tr><td><strong>status</strong></td><td><code>$test_status</code></td></tr>";
    echo "<tr><td><strong>tipo_pacchetto</strong></td><td><code>$test_pacchetto</code></td></tr>";
    echo "</table>";
    
    // Query esatta del token_manager.php
    echo "<div class='query'><strong>🔍 Query esatta token_manager.php:</strong><br>";
    echo "<code>INSERT INTO tokens (token, type, data_scadenza, status, tipo_pacchetto) VALUES (?, ?, ?, 'attivo', ?)</code></div>";
    
    $stmt = $conn->prepare("INSERT INTO tokens (token, type, data_scadenza, status, tipo_pacchetto) VALUES (?, ?, ?, 'attivo', ?)");
    
    if (!$stmt) {
        echo "<div class='error'>❌ ERRORE PREPARE: " . $conn->error . "</div>";
    } else {
        echo "<div class='success'>✅ PREPARE OK</div>";
        
        // Binding parametri
        echo "<div class='info'>🔗 <strong>Binding parametri:</strong> ssss</div>";
        $bind_result = $stmt->bind_param('ssss', $test_token, $test_type, $test_scadenza, $test_pacchetto);
        
        if (!$bind_result) {
            echo "<div class='error'>❌ ERRORE BIND: " . $stmt->error . "</div>";
        } else {
            echo "<div class='success'>✅ BIND OK</div>";
            
            // Esecuzione
            $execute_result = $stmt->execute();
            
            if (!$execute_result) {
                echo "<div class='error'>❌ ERRORE EXECUTE: " . $stmt->error . "</div>";
            } else {
                $insert_id = $conn->insert_id;
                echo "<div class='success'>✅ INSERT OK - ID: $insert_id</div>";
                
                // Verifica immediata
                echo "<h3>🧪 VERIFICA IMMEDIATA</h3>";
                $verify = $conn->prepare("SELECT * FROM tokens WHERE token = ?");
                $verify->bind_param('s', $test_token);
                $verify->execute();
                $result = $verify->get_result()->fetch_assoc();
                
                if ($result) {
                    echo "<table>";
                    echo "<tr><th>Campo</th><th>Valore Atteso</th><th>Valore Effettivo</th><th>Status</th></tr>";
                    
                    $checks = [
                        'token' => $test_token,
                        'type' => $test_type,
                        'data_scadenza' => $test_scadenza,
                        'status' => $test_status,
                        'tipo_pacchetto' => $test_pacchetto
                    ];
                    
                    $all_ok = true;
                    foreach ($checks as $campo => $atteso) {
                        $effettivo = $result[$campo] ?? 'NULL';
                        $match = ($effettivo === $atteso);
                        $status_icon = $match ? '✅' : '❌';
                        $row_class = $match ? '' : ' style="background-color: #ffe6e6;"';
                        
                        if (!$match) $all_ok = false;
                        
                        echo "<tr$row_class>";
                        echo "<td><strong>$campo</strong></td>";
                        echo "<td><code>" . ($atteso ?? 'NULL') . "</code></td>";
                        echo "<td><code>" . ($effettivo ?? 'NULL') . "</code></td>";
                        echo "<td>$status_icon</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    
                    if ($all_ok) {
                        echo "<div class='success'>✅ TUTTI I CAMPI CORRETTI - IL BUG NON È NELLA QUERY!</div>";
                    } else {
                        echo "<div class='error'>❌ DISCREPANZE TROVATE - PROBLEMA NELLA QUERY O STRUTTURA DB!</div>";
                    }
                    
                } else {
                    echo "<div class='error'>❌ NESSUN RECORD TROVATO DOPO INSERT!</div>";
                }
                
                // Cleanup
                $cleanup = $conn->prepare("DELETE FROM tokens WHERE token = ?");
                $cleanup->bind_param('s', $test_token);
                $cleanup->execute();
                echo "<div class='info'>🧹 Token test eliminato</div>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE TEST: " . $e->getMessage() . "</div>";
}

// ==========================================
// STEP 3: ANALISI LOG ERRORI
// ==========================================
echo "<h2>STEP 3: 📄 LOG ERRORI PHP</h2>";

$php_error_log = ini_get('error_log');
if ($php_error_log && file_exists($php_error_log)) {
    echo "<div class='info'>📂 <strong>Log file:</strong> $php_error_log</div>";
    
    $log_content = file_get_contents($php_error_log);
    $lines = explode("\n", $log_content);
    $token_lines = array_filter($lines, function($line) {
        return strpos($line, '[TOKEN_MANAGER]') !== false;
    });
    
    if (!empty($token_lines)) {
        echo "<div class='query'><strong>🔍 Ultimi log TOKEN_MANAGER:</strong><br>";
        foreach (array_slice($token_lines, -10) as $line) {
            echo htmlspecialchars($line) . "<br>";
        }
        echo "</div>";
    } else {
        echo "<div class='warning'>⚠️ Nessun log TOKEN_MANAGER trovato</div>";
    }
} else {
    echo "<div class='warning'>⚠️ Log errori PHP non trovato o non configurato</div>";
}

echo "<hr>";
echo "<p><strong>🕐 Debug completato:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='admin/dashboard_new.php'>🚀 Dashboard Admin</a> | ";
echo "<a href='test_specific_token.php'>🧪 Test Token</a></p>";
echo "</body></html>";
?>
