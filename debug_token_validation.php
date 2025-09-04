<?php
// ===================================================================
// 🔍 DEBUG TOKEN VALIDATION - ANALISI COMPLETA
// ===================================================================
// ✅ Verifica token scaduti, status e flusso validazione
// ===================================================================

require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>DEBUG TOKEN VALIDATION</title>";
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
    .token-form { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
    .timestamp { font-family: monospace; font-size: 0.9em; }
</style>";
echo "</head><body>";

echo "<h1>🔍 DEBUG TOKEN VALIDATION</h1>";
echo "<div class='info'>🎯 Analisi completa validazione token e flusso registrazione azienda</div>";
echo "<hr>";

$current_time = date('Y-m-d H:i:s');
echo "<div class='info'>⏰ <strong>Timestamp attuale:</strong> <span class='timestamp'>$current_time</span></div>";

// ==========================================
// STEP 1: TOKENS ATTUALMENTE NEL DATABASE
// ==========================================
echo "<h2>STEP 1: 🎫 TOKENS NEL DATABASE</h2>";

try {
    $tokens = $conn->query("
        SELECT 
            id, 
            token, 
            type, 
            status, 
            data_creazione, 
            data_scadenza, 
            tipo_pacchetto,
            id_azienda,
            CASE 
                WHEN data_scadenza < NOW() THEN 'SCADUTO'
                WHEN status != 'attivo' THEN 'INATTIVO'
                ELSE 'VALIDO'
            END as validation_status,
            TIMESTAMPDIFF(HOUR, NOW(), data_scadenza) as ore_rimanenti
        FROM tokens 
        ORDER BY data_creazione DESC 
        LIMIT 10
    ");
    
    if ($tokens && $tokens->num_rows > 0) {
        echo "<div class='success'>📊 Token trovati nel database:</div>";
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Token (8 char)</th>
                <th>Tipo</th>
                <th>Status DB</th>
                <th>Validation</th>
                <th>Creazione</th>
                <th>Scadenza</th>
                <th>Ore Rim.</th>
                <th>Pacchetto</th>
                <th>ID Azienda</th>
              </tr>";
              
        while ($row = $tokens->fetch_assoc()) {
            $validation_class = '';
            if ($row['validation_status'] === 'SCADUTO') $validation_class = 'style="background-color: #ffe6e6;"';
            elseif ($row['validation_status'] === 'INATTIVO') $validation_class = 'style="background-color: #fff8e6;"';
            elseif ($row['validation_status'] === 'VALIDO') $validation_class = 'style="background-color: #e8f5e8;"';
            
            echo "<tr $validation_class>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td><code>" . substr($row['token'], 0, 8) . "...</code></td>";
            echo "<td><strong>" . $row['type'] . "</strong></td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "<td><strong>" . $row['validation_status'] . "</strong></td>";
            echo "<td class='timestamp'>" . $row['data_creazione'] . "</td>";
            echo "<td class='timestamp'>" . $row['data_scadenza'] . "</td>";
            echo "<td>" . ($row['ore_rimanenti'] ?? 'N/A') . "</td>";
            echo "<td>" . $row['tipo_pacchetto'] . "</td>";
            echo "<td>" . ($row['id_azienda'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>❌ Nessun token trovato nel database!</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE QUERY TOKENS: " . $e->getMessage() . "</div>";
}

// ==========================================
// STEP 2: TEST VALIDAZIONE TOKEN SPECIFICO
// ==========================================
echo "<h2>STEP 2: 🧪 TEST VALIDAZIONE TOKEN SPECIFICO</h2>";

// Form per testare un token specifico
echo "<div class='token-form'>";
echo "<h3>🔍 Testa Validazione Token:</h3>";
echo "<form method='GET' action=''>";
echo "<input type='text' name='test_token' placeholder='Inserisci token completo...' style='width: 400px; padding: 8px;' value='" . ($_GET['test_token'] ?? '') . "'>";
echo "<button type='submit' style='padding: 8px 16px; margin-left: 10px;'>🔍 Testa Token</button>";
echo "</form>";

if (isset($_GET['test_token']) && !empty($_GET['test_token'])) {
    $test_token = trim($_GET['test_token']);
    
    echo "<h4>🔬 Risultato Test per: <code>" . substr($test_token, 0, 16) . "...</code></h4>";
    
    try {
        // Query di validazione (simulando quella del sistema)
        $validate_stmt = $conn->prepare("
            SELECT 
                id, 
                token, 
                type, 
                status, 
                data_creazione, 
                data_scadenza, 
                tipo_pacchetto,
                id_azienda,
                CASE 
                    WHEN data_scadenza < NOW() THEN 'SCADUTO'
                    WHEN status != 'attivo' THEN 'INATTIVO'
                    ELSE 'VALIDO'
                END as validation_result
            FROM tokens 
            WHERE token = ?
        ");
        
        $validate_stmt->bind_param('s', $test_token);
        $validate_stmt->execute();
        $validate_result = $validate_stmt->get_result();
        
        if ($validate_result && $validate_result->num_rows > 0) {
            $token_data = $validate_result->fetch_assoc();
            
            echo "<div class='success'>✅ Token trovato nel database</div>";
            
            // Tabella dettagliata
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valore</th><th>Note</th></tr>";
            foreach ($token_data as $campo => $valore) {
                $note = '';
                if ($campo === 'validation_result') {
                    if ($valore === 'SCADUTO') $note = '❌ Token scaduto';
                    elseif ($valore === 'INATTIVO') $note = '⚠️ Token inattivo';
                    elseif ($valore === 'VALIDO') $note = '✅ Token valido';
                }
                echo "<tr><td><strong>$campo</strong></td><td>" . ($valore ?? 'NULL') . "</td><td>$note</td></tr>";
            }
            echo "</table>";
            
            // Test scadenza dettagliato
            $now = new DateTime();
            $scadenza = new DateTime($token_data['data_scadenza']);
            $diff = $now->diff($scadenza);
            
            echo "<h4>⏰ Analisi Temporale Dettagliata:</h4>";
            echo "<table>";
            echo "<tr><td><strong>Ora attuale</strong></td><td class='timestamp'>$current_time</td></tr>";
            echo "<tr><td><strong>Scadenza token</strong></td><td class='timestamp'>" . $token_data['data_scadenza'] . "</td></tr>";
            
            if ($now > $scadenza) {
                echo "<tr style='background-color: #ffe6e6;'><td><strong>Risultato</strong></td><td>❌ SCADUTO da " . $diff->format('%h ore %i minuti') . "</td></tr>";
            } else {
                echo "<tr style='background-color: #e8f5e8;'><td><strong>Risultato</strong></td><td>✅ VALIDO - Scade tra " . $diff->format('%h ore %i minuti') . "</td></tr>";
            }
            echo "</table>";
            
        } else {
            echo "<div class='error'>❌ Token NON trovato nel database</div>";
            echo "<div class='warning'>⚠️ Possibili cause:<br>";
            echo "• Token non esistente<br>";
            echo "• Errore di digitazione<br>";
            echo "• Token eliminato dal sistema</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE TEST TOKEN: " . $e->getMessage() . "</div>";
    }
}
echo "</div>";

// ==========================================
// STEP 3: ANALISI REGISTER_COMPANY.PHP
// ==========================================
echo "<h2>STEP 3: 📄 ANALISI REGISTER_COMPANY.PHP</h2>";

try {
    if (file_exists('register_company.php')) {
        echo "<div class='success'>✅ File register_company.php trovato</div>";
        
        // Cerca la logica di validazione token
        $content = file_get_contents('register_company.php');
        
        if (strpos($content, 'token') !== false) {
            echo "<div class='info'>📋 File contiene logica token</div>";
            
            // Cerca pattern specifici
            $patterns = [
                'SELECT.*FROM.*tokens.*WHERE' => 'Query di selezione token',
                'data_scadenza.*<.*NOW' => 'Check scadenza token',
                'status.*=.*attivo' => 'Check status attivo',
                'Il token.*non è valido' => 'Messaggio errore token'
            ];
            
            echo "<h4>🔍 Pattern trovati nel codice:</h4>";
            echo "<table>";
            echo "<tr><th>Pattern</th><th>Trovato</th><th>Descrizione</th></tr>";
            foreach ($patterns as $pattern => $desc) {
                $found = preg_match("/$pattern/i", $content) ? '✅' : '❌';
                echo "<tr><td><code>$pattern</code></td><td>$found</td><td>$desc</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='warning'>⚠️ Il file non sembra contenere logica token</div>";
        }
    } else {
        echo "<div class='error'>❌ File register_company.php NON trovato</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE ANALISI FILE: " . $e->getMessage() . "</div>";
}

// ==========================================
// STEP 4: CREAZIONE TOKEN TEST
// ==========================================
echo "<h2>STEP 4: 🧪 CREAZIONE TOKEN TEST</h2>";

if (isset($_GET['create_test_token'])) {
    try {
        // Crea un token test con validità 24 ore
        $test_token = bin2hex(random_bytes(32));
        $test_scadenza = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $create_stmt = $conn->prepare("INSERT INTO tokens (token, type, data_scadenza, status, tipo_pacchetto) VALUES (?, 'creazione', ?, 'attivo', 'foto')");
        $create_stmt->bind_param('ss', $test_token, $test_scadenza);
        
        if ($create_stmt->execute()) {
            $token_id = $conn->insert_id;
            echo "<div class='success'>✅ Token test creato con successo - ID: $token_id</div>";
            echo "<div class='info'>";
            echo "<strong>🎫 Token generato:</strong><br>";
            echo "<code>$test_token</code><br><br>";
            echo "<strong>🔗 Link di test:</strong><br>";
            echo "<a href='register_company.php?token=$test_token' target='_blank'>Testa registrazione con questo token</a><br><br>";
            echo "<strong>⏰ Scadenza:</strong> $test_scadenza";
            echo "</div>";
        } else {
            echo "<div class='error'>❌ Errore creazione token test: " . $create_stmt->error . "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE CREAZIONE TOKEN TEST: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='token-form'>";
    echo "<a href='?create_test_token=1' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Crea Token Test</a>";
    echo "<p><small>Crea un token test valido per 24 ore per testare la registrazione</small></p>";
    echo "</div>";
}

// ==========================================
// DIAGNOSI E RACCOMANDAZIONI
// ==========================================
echo "<hr>";
echo "<h2>🔧 DIAGNOSI E RACCOMANDAZIONI</h2>";

echo "<div class='warning'>";
echo "<h3>🎯 POSSIBILI CAUSE ERRORE:</h3>";
echo "1. <strong>Token scaduto:</strong> Verifica la scadenza del token usato<br>";
echo "2. <strong>Token già utilizzato:</strong> I token potrebbero essere single-use<br>";
echo "3. <strong>Status inattivo:</strong> Il token potrebbe essere disattivato<br>";
echo "4. <strong>Cache/sessioni:</strong> Problemi di cache o sessioni PHP<br>";
echo "5. <strong>Logica validazione:</strong> Bug nella logica di validazione";
echo "</div>";

echo "<div class='success'>";
echo "<h3>✅ PROSSIMI STEP DEBUG:</h3>";
echo "1. <strong>Usa il form sopra</strong> per testare un token specifico<br>";
echo "2. <strong>Crea un token test</strong> e prova la registrazione<br>";
echo "3. <strong>Verifica register_company.php</strong> per logica validazione<br>";
echo "4. <strong>Controlla i log</strong> di Apache/PHP per errori<br>";
echo "5. <strong>Test end-to-end</strong> completo del flusso";
echo "</div>";

echo "<hr>";
echo "<p><strong>🕐 Debug completato:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='admin/dashboard_new.php'>🚀 Dashboard Admin</a> | ";
echo "<a href='index.php'>← Home</a></p>";
echo "</body></html>";
?>
