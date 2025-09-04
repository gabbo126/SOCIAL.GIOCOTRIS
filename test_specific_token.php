<?php
// ===================================================================
// 🧪 TEST TOKEN SPECIFICO - DEBUG ROOT CAUSE
// ===================================================================
// ✅ Testa esattamente la query di register_company.php
// ===================================================================

require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>TEST SPECIFIC TOKEN</title>";
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
    .form-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
</style>";
echo "</head><body>";

echo "<h1>🧪 TEST TOKEN SPECIFICO - DEBUG ROOT CAUSE</h1>";
echo "<div class='info'>🎯 Test esatto della query di register_company.php su token reale</div>";
echo "<hr>";

$current_time = date('Y-m-d H:i:s');
echo "<div class='info'>⏰ <strong>MySQL NOW():</strong> <span class='timestamp'>$current_time</span></div>";

// ==========================================
// TOKEN DA TESTARE
// ==========================================
$test_token = '';
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $test_token = trim($_GET['token']);
} elseif (isset($_POST['token']) && !empty($_POST['token'])) {
    $test_token = trim($_POST['token']);
}

echo "<div class='form-section'>";
echo "<h2>🔍 INSERISCI TOKEN DA TESTARE</h2>";
echo "<form method='POST' action=''>";
echo "<input type='text' name='token' placeholder='Inserisci token completo dal dashboard...' style='width: 600px; padding: 10px; font-family: monospace;' value='$test_token'>";
echo "<button type='submit' style='padding: 10px 20px; margin-left: 10px;'>🧪 TESTA TOKEN</button>";
echo "</form>";

if (!empty($test_token)) {
    echo "<div class='info'>🎫 <strong>Token in test:</strong> <code>" . substr($test_token, 0, 16) . "..." . substr($test_token, -8) . "</code></div>";
}
echo "</div>";

if (!empty($test_token)) {
    
    // ==========================================
    // STEP 1: QUERY ESATTA DI register_company.php
    // ==========================================
    echo "<h2>STEP 1: 🎯 QUERY ESATTA register_company.php</h2>";
    
    try {
        echo "<div class='info'>📋 <strong>Query utilizzata:</strong><br>";
        echo "<code>SELECT * FROM tokens WHERE token = ? AND type = 'creazione' AND status = 'attivo' AND data_scadenza > NOW()</code></div>";
        
        $stmt = $conn->prepare("SELECT * FROM tokens WHERE token = ? AND type = 'creazione' AND status = 'attivo' AND data_scadenza > NOW()");
        $stmt->bind_param('s', $test_token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            echo "<div class='success'>✅ TOKEN VALIDO - Trovato 1 record</div>";
            
            $token_data = $result->fetch_assoc();
            echo "<h3>📊 Dati Token Recuperati:</h3>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valore</th><th>Note</th></tr>";
            foreach ($token_data as $campo => $valore) {
                $note = '';
                if ($campo === 'data_scadenza') {
                    $scadenza_time = strtotime($valore);
                    $now_time = time();
                    if ($scadenza_time > $now_time) {
                        $diff_hours = round(($scadenza_time - $now_time) / 3600, 1);
                        $note = "✅ Valido ancora per $diff_hours ore";
                    } else {
                        $note = "❌ SCADUTO";
                    }
                }
                echo "<tr><td><strong>$campo</strong></td><td class='timestamp'>" . ($valore ?? 'NULL') . "</td><td>$note</td></tr>";
            }
            echo "</table>";
            
        } else {
            echo "<div class='error'>❌ TOKEN NON VALIDO - Nessun record trovato</div>";
            echo "<div class='warning'>⚠️ Il token fallisce una o più condizioni della query</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE QUERY: " . $e->getMessage() . "</div>";
    }
    
    // ==========================================
    // STEP 2: DEBUG CONDIZIONI SINGOLE
    // ==========================================
    echo "<h2>STEP 2: 🔍 DEBUG CONDIZIONI SINGOLE</h2>";
    
    try {
        // Test 1: Token esiste?
        echo "<h3>🔍 Test 1: Token esiste nel database?</h3>";
        $test1 = $conn->prepare("SELECT COUNT(*) as count FROM tokens WHERE token = ?");
        $test1->bind_param('s', $test_token);
        $test1->execute();
        $result1 = $test1->get_result()->fetch_assoc();
        
        if ($result1['count'] > 0) {
            echo "<div class='success'>✅ Token trovato nel database</div>";
        } else {
            echo "<div class='error'>❌ Token NON trovato nel database</div>";
            echo "<div class='info'>📋 Il problema è qui: il token non esiste o è stato cancellato</div>";
        }
        
        if ($result1['count'] > 0) {
            // Test 2: Type = 'creazione'?
            echo "<h3>🔍 Test 2: Type = 'creazione'?</h3>";
            $test2 = $conn->prepare("SELECT type FROM tokens WHERE token = ?");
            $test2->bind_param('s', $test_token);
            $test2->execute();
            $result2 = $test2->get_result()->fetch_assoc();
            
            if ($result2['type'] === 'creazione') {
                echo "<div class='success'>✅ Type corretto: " . $result2['type'] . "</div>";
            } else {
                echo "<div class='error'>❌ Type errato: " . $result2['type'] . " (dovrebbe essere 'creazione')</div>";
            }
            
            // Test 3: Status = 'attivo'?
            echo "<h3>🔍 Test 3: Status = 'attivo'?</h3>";
            $test3 = $conn->prepare("SELECT status FROM tokens WHERE token = ?");
            $test3->bind_param('s', $test_token);
            $test3->execute();
            $result3 = $test3->get_result()->fetch_assoc();
            
            if ($result3['status'] === 'attivo') {
                echo "<div class='success'>✅ Status corretto: " . $result3['status'] . "</div>";
            } else {
                echo "<div class='error'>❌ Status errato: " . $result3['status'] . " (dovrebbe essere 'attivo')</div>";
            }
            
            // Test 4: data_scadenza > NOW()?
            echo "<h3>🔍 Test 4: data_scadenza > NOW()?</h3>";
            $test4 = $conn->prepare("SELECT data_scadenza, NOW() as now_time FROM tokens WHERE token = ?");
            $test4->bind_param('s', $test_token);
            $test4->execute();
            $result4 = $test4->get_result()->fetch_assoc();
            
            $scadenza = $result4['data_scadenza'];
            $now = $result4['now_time'];
            
            echo "<table>";
            echo "<tr><td><strong>data_scadenza</strong></td><td class='timestamp'>$scadenza</td></tr>";
            echo "<tr><td><strong>NOW()</strong></td><td class='timestamp'>$now</td></tr>";
            echo "</table>";
            
            if ($scadenza > $now) {
                $diff = strtotime($scadenza) - strtotime($now);
                $hours = round($diff / 3600, 1);
                echo "<div class='success'>✅ Token NON scaduto - Valido ancora per $hours ore</div>";
            } else {
                $diff = strtotime($now) - strtotime($scadenza);
                $hours = round($diff / 3600, 1);
                echo "<div class='error'>❌ TOKEN SCADUTO da $hours ore</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE DEBUG: " . $e->getMessage() . "</div>";
    }
    
    // ==========================================
    // STEP 3: TUTTI I TOKENS RECENTI
    // ==========================================
    echo "<h2>STEP 3: 🗃️ TUTTI I TOKEN RECENTI</h2>";
    
    try {
        $recent = $conn->query("
            SELECT 
                id, 
                LEFT(token, 16) as token_prefix,
                RIGHT(token, 8) as token_suffix,
                type, 
                status, 
                data_creazione, 
                data_scadenza,
                CASE 
                    WHEN data_scadenza > NOW() AND status = 'attivo' AND type = 'creazione' THEN 'VALIDO'
                    WHEN data_scadenza <= NOW() THEN 'SCADUTO'
                    WHEN status != 'attivo' THEN 'INATTIVO'
                    WHEN type != 'creazione' THEN 'TIPO_ERRATO'
                    ELSE 'ALTRO'
                END as stato
            FROM tokens 
            ORDER BY data_creazione DESC 
            LIMIT 5
        ");
        
        if ($recent && $recent->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Token</th><th>Type</th><th>Status</th><th>Creazione</th><th>Scadenza</th><th>Stato</th></tr>";
            while ($row = $recent->fetch_assoc()) {
                $class = '';
                if ($row['stato'] === 'VALIDO') $class = ' style="background-color: #e8f5e8;"';
                elseif ($row['stato'] === 'SCADUTO') $class = ' style="background-color: #ffe6e6;"';
                
                echo "<tr$class>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td><code>" . $row['token_prefix'] . "..." . $row['token_suffix'] . "</code></td>";
                echo "<td>" . $row['type'] . "</td>";
                echo "<td>" . $row['status'] . "</td>";
                echo "<td class='timestamp'>" . $row['data_creazione'] . "</td>";
                echo "<td class='timestamp'>" . $row['data_scadenza'] . "</td>";
                echo "<td><strong>" . $row['stato'] . "</strong></td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE: " . $e->getMessage() . "</div>";
    }
}

echo "<hr>";
echo "<p><strong>🕐 Test completato:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='admin/dashboard_new.php'>🚀 Dashboard Admin</a> | ";
echo "<a href='debug_token_validation.php'>🔍 Debug Generale</a></p>";
echo "</body></html>";
?>
