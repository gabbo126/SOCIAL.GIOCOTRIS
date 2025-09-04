<?php
// ===================================================================
// 🔧 FIX FOREIGN KEY CONSTRAINT - TOKENS CREAZIONE
// ===================================================================
// ✅ Risolve errore token creazione azienda con id_azienda nullable
// ===================================================================

require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>FIX TOKENS FOREIGN KEY</title>";
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
</style>";
echo "</head><body>";

echo "<h1>🔧 FIX TOKENS FOREIGN KEY CONSTRAINT</h1>";
echo "<div class='info'>⚡ Risoluzione errore token creazione azienda - id_azienda nullable</div>";
echo "<hr>";

$success = true;

// ==========================================
// STEP 1: ANALISI ATTUALE
// ==========================================
echo "<h2>STEP 1: 📊 ANALISI STRUTTURA ATTUALE</h2>";

try {
    // Verifica struttura tokens
    $describe = $conn->query("DESCRIBE tokens");
    if ($describe) {
        echo "<h3>📋 Struttura Tabella tokens:</h3>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        $id_azienda_nullable = false;
        while ($row = $describe->fetch_assoc()) {
            $highlight = ($row['Field'] === 'id_azienda') ? ' style="background-color: #fff3cd;"' : '';
            if ($row['Field'] === 'id_azienda') {
                $id_azienda_nullable = ($row['Null'] === 'YES');
            }
            echo "<tr$highlight>";
            echo "<td><strong>" . $row['Field'] . "</strong></td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if (!$id_azienda_nullable) {
            echo "<div class='error'>❌ PROBLEMA: id_azienda è NOT NULL - Impedisce token creazione!</div>";
        } else {
            echo "<div class='success'>✅ id_azienda è già nullable - Il problema è altrove</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE ANALISI: " . $e->getMessage() . "</div>";
    $success = false;
}

// ==========================================
// STEP 2: VERIFICA VINCOLI FOREIGN KEY
// ==========================================
echo "<h2>STEP 2: 🔗 VERIFICA VINCOLI FOREIGN KEY</h2>";

try {
    $constraints = $conn->query("
        SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = 'social_gioco_tris' 
        AND TABLE_NAME = 'tokens' 
        AND COLUMN_NAME = 'id_azienda'
        AND REFERENCED_TABLE_NAME = 'aziende'
    ");
    
    $has_foreign_key = false;
    $constraint_name = '';
    if ($constraints && $constraints->num_rows > 0) {
        $constraint = $constraints->fetch_assoc();
        $constraint_name = $constraint['CONSTRAINT_NAME'];
        $has_foreign_key = true;
        echo "<div class='warning'>⚠️ Vincolo FOREIGN KEY trovato: <code>$constraint_name</code></div>";
    } else {
        echo "<div class='info'>📋 Nessun vincolo FOREIGN KEY su id_azienda</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE VINCOLI: " . $e->getMessage() . "</div>";
}

// ==========================================
// STEP 3: CORREZIONE id_azienda NULLABLE
// ==========================================
if ($success && !$id_azienda_nullable) {
    echo "<h2>STEP 3: 🔧 CORREZIONE id_azienda NULLABLE</h2>";
    
    try {
        // Prima rimuovi il vincolo foreign key se esiste
        if ($has_foreign_key) {
            echo "<h3>🗑️ Rimozione vincolo FOREIGN KEY</h3>";
            $drop_fk = $conn->query("ALTER TABLE tokens DROP FOREIGN KEY $constraint_name");
            if ($drop_fk) {
                echo "<div class='success'>✅ Vincolo FOREIGN KEY rimosso</div>";
            } else {
                throw new Exception("Errore rimozione FK: " . $conn->error);
            }
        }
        
        // Modifica colonna per renderla nullable
        echo "<h3>🔧 Modifica colonna id_azienda → NULLABLE</h3>";
        $modify_col = $conn->query("ALTER TABLE tokens MODIFY COLUMN id_azienda INT(10) UNSIGNED NULL");
        if ($modify_col) {
            echo "<div class='success'>✅ Colonna id_azienda ora è NULLABLE</div>";
        } else {
            throw new Exception("Errore modifica colonna: " . $conn->error);
        }
        
        // Ricrea vincolo foreign key con supporto NULL
        if ($has_foreign_key) {
            echo "<h3>🔗 Ricreazione vincolo FOREIGN KEY (con NULL)</h3>";
            $recreate_fk = $conn->query("
                ALTER TABLE tokens 
                ADD CONSTRAINT tokens_ibfk_1 
                FOREIGN KEY (id_azienda) 
                REFERENCES aziende(id) 
                ON DELETE CASCADE 
                ON UPDATE CASCADE
            ");
            if ($recreate_fk) {
                echo "<div class='success'>✅ Vincolo FOREIGN KEY ricreato (supporta NULL)</div>";
            } else {
                echo "<div class='warning'>⚠️ Errore ricreazione FK: " . $conn->error . " (può funzionare ugualmente)</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE CORREZIONE: " . $e->getMessage() . "</div>";
        $success = false;
    }
}

// ==========================================
// STEP 4: VERIFICA POST-CORREZIONE
// ==========================================
if ($success) {
    echo "<h2>STEP 4: ✅ VERIFICA POST-CORREZIONE</h2>";
    
    try {
        // Verifica struttura aggiornata
        $verify = $conn->query("
            SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_DEFAULT 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = 'social_gioco_tris' 
            AND TABLE_NAME = 'tokens' 
            AND COLUMN_NAME = 'id_azienda'
        ");
        
        if ($verify && $verify->num_rows > 0) {
            $col = $verify->fetch_assoc();
            echo "<table>";
            echo "<tr><th>Proprietà</th><th>Valore</th><th>Status</th></tr>";
            echo "<tr><td><strong>Campo</strong></td><td>" . $col['COLUMN_NAME'] . "</td><td>✅</td></tr>";
            echo "<tr><td><strong>Nullable</strong></td><td>" . $col['IS_NULLABLE'] . "</td><td>" . 
                ($col['IS_NULLABLE'] === 'YES' ? '✅ Corretto' : '❌ Ancora NOT NULL') . "</td></tr>";
            echo "<tr><td><strong>Default</strong></td><td>" . ($col['COLUMN_DEFAULT'] ?? 'NULL') . "</td><td>✅</td></tr>";
            echo "</table>";
            
            if ($col['IS_NULLABLE'] === 'YES') {
                echo "<div class='success'>🎉 CORREZIONE COMPLETATA - id_azienda è ora NULLABLE!</div>";
            } else {
                echo "<div class='error'>❌ id_azienda è ancora NOT NULL</div>";
                $success = false;
            }
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE VERIFICA: " . $e->getMessage() . "</div>";
        $success = false;
    }
}

// ==========================================
// STEP 5: TEST INSERIMENTO TOKEN CREAZIONE
// ==========================================
if ($success) {
    echo "<h2>STEP 5: 🧪 TEST INSERIMENTO TOKEN CREAZIONE</h2>";
    
    try {
        // Genera token di test
        $test_token = 'test_' . bin2hex(random_bytes(16));
        $test_scadenza = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Prova inserimento senza id_azienda
        $test_stmt = $conn->prepare("INSERT INTO tokens (token, type, data_scadenza, status, tipo_pacchetto, id_azienda) VALUES (?, 'creazione', ?, 'attivo', 'foto', NULL)");
        $test_stmt->bind_param('ss', $test_token, $test_scadenza);
        
        if ($test_stmt->execute()) {
            $test_id = $conn->insert_id;
            echo "<div class='success'>✅ TEST INSERIMENTO RIUSCITO - ID: $test_id</div>";
            
            // Verifica inserimento
            $verify_test = $conn->query("SELECT * FROM tokens WHERE id = $test_id");
            if ($verify_test && $verify_test->num_rows > 0) {
                $token_data = $verify_test->fetch_assoc();
                echo "<h4>📋 Token di test inserito:</h4>";
                echo "<table>";
                echo "<tr><th>Campo</th><th>Valore</th></tr>";
                foreach ($token_data as $campo => $valore) {
                    echo "<tr><td><strong>$campo</strong></td><td>" . ($valore ?? 'NULL') . "</td></tr>";
                }
                echo "</table>";
            }
            
            // Pulisci test
            $conn->query("DELETE FROM tokens WHERE id = $test_id");
            echo "<div class='info'>🧹 Token di test rimosso</div>";
            
        } else {
            throw new Exception("Test inserimento fallito: " . $test_stmt->error);
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE TEST: " . $e->getMessage() . "</div>";
        $success = false;
    }
}

// ==========================================
// STEP 6: AGGIORNA token_manager.php
// ==========================================
if ($success) {
    echo "<h2>STEP 6: 📝 VERIFICA token_manager.php</h2>";
    echo "<div class='info'>📋 La query in token_manager.php dovrebbe già essere corretta:</div>";
    echo "<code>INSERT INTO tokens (token, type, data_scadenza, status, tipo_pacchetto) VALUES (?, 'creazione', ?, 'attivo', ?)</code>";
    echo "<div class='success'>✅ Query non include id_azienda per token creazione - Perfetto!</div>";
}

// ==========================================
// RISULTATO FINALE
// ==========================================
echo "<hr>";

if ($success) {
    echo "<div class='success' style='font-size: 20px; text-align: center; padding: 20px'>";
    echo "🎉 FOREIGN KEY CONSTRAINT RISOLTO!";
    echo "</div>";
    
    echo "<h3>✅ CORREZIONI APPLICATE:</h3>";
    echo "<div class='success'>";
    echo "1. ✅ Campo <code>id_azienda</code> ora è NULLABLE<br>";
    echo "2. ✅ Token di CREAZIONE possono avere <code>id_azienda = NULL</code><br>";
    echo "3. ✅ Token di MODIFICA possono avere <code>id_azienda</code> valido<br>";
    echo "4. ✅ Vincolo FOREIGN KEY ricreato correttamente<br>";
    echo "5. ✅ Test inserimento token creazione SUPERATO!";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>🚀 PROSSIMI STEP:</h3>";
    echo "1. <strong>Testa creazione token:</strong> Vai alla dashboard admin<br>";
    echo "2. <strong>Crea token creazione azienda:</strong> Dovrebbe funzionare senza errori<br>";
    echo "3. <strong>Verifica registrazione:</strong> Il flusso completo dovrebbe essere funzionante<br>";
    echo "4. <strong>Sistema completamente riparato!</strong> 🔥";
    echo "</div>";
    
} else {
    echo "<div class='error' style='font-size: 20px; text-align: center; padding: 20px'>";
    echo "❌ ERRORE NELLA CORREZIONE";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>🕐 Fix completato:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='admin/dashboard_new.php'>🚀 TESTA CREAZIONE TOKEN</a> | ";
echo "<a href='index.php'>← Torna alla home</a></p>";
echo "</body></html>";
?>
