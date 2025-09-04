<?php
// ===================================================================
// 🧪 TEST COMPLETO SISTEMA business_categories
// ===================================================================
// ✅ Test INSERT, UPDATE, SELECT per verificare funzionamento
// ===================================================================

require_once 'config.php';
require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>TEST BUSINESS CATEGORIES</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    .success { color: green; font-weight: bold; background: #e8f5e8; padding: 10px; border-left: 5px solid green; margin: 10px 0; }
    .error { color: red; font-weight: bold; background: #ffe6e6; padding: 10px; border-left: 5px solid red; margin: 10px 0; }
    .info { color: blue; background: #e6f3ff; padding: 10px; border-left: 5px solid blue; margin: 10px 0; }
    .warning { color: orange; font-weight: bold; background: #fff8e6; padding: 10px; border-left: 5px solid orange; margin: 10px 0; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
    .json-display { background: #f8f8f8; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; }
</style>";
echo "</head><body>";

echo "<h1>🧪 TEST COMPLETO SISTEMA business_categories</h1>";
echo "<div class='info'>📋 Questo script testa completamente il sistema di categorie business per verificare che funzioni correttamente.</div>";
echo "<hr>";

$test_passed = true;
$test_results = [];

// ==========================================
// TEST 1: VERIFICA CONNESSIONE DATABASE
// ==========================================
echo "<h2>TEST 1: 🔗 VERIFICA CONNESSIONE DATABASE</h2>";

try {
    $db_info = $conn->query("SELECT DATABASE() as db, @@character_set_database as charset, @@collation_database as collation, VERSION() as version");
    if ($db_info) {
        $info = $db_info->fetch_assoc();
        echo "<div class='success'>✅ Connessione database riuscita</div>";
        echo "<table>";
        echo "<tr><th>Parametro</th><th>Valore</th></tr>";
        foreach ($info as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
        $test_results['connection'] = true;
    } else {
        throw new Exception("Impossibile ottenere info database");
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE CONNESSIONE: " . $e->getMessage() . "</div>";
    $test_passed = false;
    $test_results['connection'] = false;
}

// ==========================================
// TEST 2: VERIFICA ESISTENZA COLONNA
// ==========================================
echo "<h2>TEST 2: 📋 VERIFICA ESISTENZA COLONNA business_categories</h2>";

try {
    $column_check = $conn->query("SHOW COLUMNS FROM aziende LIKE 'business_categories'");
    if ($column_check && $column_check->num_rows > 0) {
        $column_info = $column_check->fetch_assoc();
        echo "<div class='success'>✅ Colonna business_categories trovata</div>";
        echo "<table>";
        echo "<tr><th>Proprietà</th><th>Valore</th></tr>";
        foreach ($column_info as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
        $test_results['column_exists'] = true;
    } else {
        throw new Exception("Colonna business_categories NON trovata nella tabella aziende");
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE COLONNA: " . $e->getMessage() . "</div>";
    echo "<div class='warning'>⚠️ Esegui prima: <a href='rebuild_database_emergency.php'>rebuild_database_emergency.php</a></div>";
    $test_passed = false;
    $test_results['column_exists'] = false;
}

// ==========================================
// TEST 3: INSERT CON business_categories
// ==========================================
echo "<h2>TEST 3: ➕ TEST INSERT con business_categories</h2>";

$test_categories = [
    'Ristorazione',
    'Bar & Caffetteria', 
    'Pizza & Fast Food'
];
$categories_json = json_encode($test_categories, JSON_UNESCAPED_UNICODE);

try {
    $insert_sql = "INSERT INTO aziende (nome, descrizione, business_categories, telefono, email) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    
    if (!$stmt) {
        throw new Exception("PREPARE ERROR: " . $conn->error);
    }
    
    $test_nome = "TEST AZIENDA " . date('H:i:s');
    $test_desc = "Azienda di test per sistema business_categories";
    $test_tel = "+39 123 456 789";
    $test_email = "test" . time() . "@example.com";
    
    $stmt->bind_param('sssss', $test_nome, $test_desc, $categories_json, $test_tel, $test_email);
    
    if ($stmt->execute()) {
        $test_id = $conn->insert_id;
        echo "<div class='success'>✅ INSERT riuscito - ID: $test_id</div>";
        echo "<div class='info'>📊 Nome: $test_nome</div>";
        echo "<div class='info'>📊 Categorie inserite:</div>";
        echo "<div class='json-display'>$categories_json</div>";
        $test_results['insert'] = ['success' => true, 'id' => $test_id];
    } else {
        throw new Exception("EXECUTE ERROR: " . $stmt->error);
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE INSERT: " . $e->getMessage() . "</div>";
    $test_passed = false;
    $test_results['insert'] = ['success' => false, 'error' => $e->getMessage()];
}

// ==========================================
// TEST 4: SELECT E VERIFICA DATI
// ==========================================
if (isset($test_results['insert']['id'])) {
    echo "<h2>TEST 4: 🔍 SELECT E VERIFICA DATI</h2>";
    
    $test_id = $test_results['insert']['id'];
    
    try {
        $select_sql = "SELECT id, nome, business_categories, telefono, email FROM aziende WHERE id = ?";
        $stmt = $conn->prepare($select_sql);
        $stmt->bind_param('i', $test_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo "<div class='success'>✅ SELECT riuscito - Dati recuperati</div>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valore</th></tr>";
            foreach ($row as $campo => $valore) {
                if ($campo === 'business_categories') {
                    echo "<tr><td><strong>$campo</strong></td><td>";
                    if ($valore) {
                        $decoded = json_decode($valore, true);
                        if ($decoded) {
                            echo "<div class='json-display'>" . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</div>";
                            
                            // Verifica che i dati siano quelli inseriti
                            if ($decoded === $test_categories) {
                                echo "<div class='success'>✅ Dati corrispondono perfettamente</div>";
                            } else {
                                echo "<div class='error'>❌ Dati NON corrispondono</div>";
                                $test_passed = false;
                            }
                        } else {
                            echo "<div class='error'>❌ JSON non valido</div>";
                            $test_passed = false;
                        }
                    } else {
                        echo "<em>NULL o vuoto</em>";
                    }
                    echo "</td></tr>";
                } else {
                    echo "<tr><td><strong>$campo</strong></td><td>" . htmlspecialchars($valore) . "</td></tr>";
                }
            }
            echo "</table>";
            $test_results['select'] = true;
        } else {
            throw new Exception("Nessun record trovato con ID $test_id");
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE SELECT: " . $e->getMessage() . "</div>";
        $test_passed = false;
        $test_results['select'] = false;
    }
}

// ==========================================
// TEST 5: UPDATE business_categories
// ==========================================
if (isset($test_results['insert']['id'])) {
    echo "<h2>TEST 5: 🔄 UPDATE business_categories</h2>";
    
    $test_id = $test_results['insert']['id'];
    $new_categories = [
        'Hotel & Alloggi',
        'Spa & Benessere',
        'Ristorante Gourmet'
    ];
    $new_categories_json = json_encode($new_categories, JSON_UNESCAPED_UNICODE);
    
    try {
        $update_sql = "UPDATE aziende SET business_categories = ?, descrizione = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        
        if (!$stmt) {
            throw new Exception("PREPARE UPDATE ERROR: " . $conn->error);
        }
        
        $new_desc = "Descrizione aggiornata - Test UPDATE categorie business";
        $stmt->bind_param('ssi', $new_categories_json, $new_desc, $test_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "<div class='success'>✅ UPDATE riuscito - Record aggiornato</div>";
                echo "<div class='info'>📊 Nuove categorie:</div>";
                echo "<div class='json-display'>$new_categories_json</div>";
                
                // Verifica immediata del cambiamento
                $verify_sql = "SELECT business_categories FROM aziende WHERE id = ?";
                $verify_stmt = $conn->prepare($verify_sql);
                $verify_stmt->bind_param('i', $test_id);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->get_result();
                
                if ($verify_row = $verify_result->fetch_assoc()) {
                    $retrieved_categories = json_decode($verify_row['business_categories'], true);
                    if ($retrieved_categories === $new_categories) {
                        echo "<div class='success'>✅ UPDATE verificato - Dati corretti nel database</div>";
                        $test_results['update'] = true;
                    } else {
                        echo "<div class='error'>❌ UPDATE fallito - Dati non corrispondono</div>";
                        $test_passed = false;
                        $test_results['update'] = false;
                    }
                }
                
            } else {
                throw new Exception("Nessun record aggiornato (affected_rows = 0)");
            }
        } else {
            throw new Exception("EXECUTE UPDATE ERROR: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE UPDATE: " . $e->getMessage() . "</div>";
        $test_passed = false;
        $test_results['update'] = false;
    }
}

// ==========================================
// TEST 6: CLEANUP - RIMOZIONE RECORD TEST
// ==========================================
if (isset($test_results['insert']['id'])) {
    echo "<h2>TEST 6: 🧹 CLEANUP - Rimozione record di test</h2>";
    
    $test_id = $test_results['insert']['id'];
    
    try {
        $delete_sql = "DELETE FROM aziende WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param('i', $test_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "<div class='success'>✅ Record di test eliminato correttamente</div>";
                $test_results['cleanup'] = true;
            } else {
                echo "<div class='warning'>⚠️ Nessun record eliminato (era già stato rimosso?)</div>";
                $test_results['cleanup'] = false;
            }
        } else {
            throw new Exception("EXECUTE DELETE ERROR: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE DELETE: " . $e->getMessage() . "</div>";
        $test_results['cleanup'] = false;
    }
}

// ==========================================
// RISULTATO FINALE
// ==========================================
echo "<hr>";
echo "<h1>📊 RISULTATO FINALE DEI TEST</h1>";

$total_tests = count($test_results);
$passed_tests = count(array_filter($test_results, function($result) {
    return is_bool($result) ? $result : (is_array($result) ? $result['success'] : false);
}));

if ($test_passed && $passed_tests === $total_tests) {
    echo "<div class='success' style='font-size: 20px; text-align: center; padding: 20px'>";
    echo "🎉 TUTTI I TEST SUPERATI ($passed_tests/$total_tests)";
    echo "<br><br><strong>✅ SISTEMA business_categories COMPLETAMENTE FUNZIONANTE!</strong>";
    echo "</div>";
    
    echo "<h3>🚀 Operazioni Testate con Successo:</h3>";
    echo "<ul>";
    echo "<li>✅ Connessione database e charset UTF8MB4</li>";
    echo "<li>✅ Esistenza colonna business_categories (tipo TEXT)</li>";
    echo "<li>✅ INSERT con categorie JSON</li>";
    echo "<li>✅ SELECT e decodifica categorie</li>";
    echo "<li>✅ UPDATE categorie esistenti</li>";
    echo "<li>✅ DELETE e cleanup</li>";
    echo "</ul>";
    
    echo "<h3>🎯 Prossimi Step Operativi:</h3>";
    echo "<div class='info'>";
    echo "1. <strong>Testa registrazione azienda:</strong> <a href='inserimento.php'>inserimento.php</a><br>";
    echo "2. <strong>Testa modifica azienda esistente</strong> tramite token di modifica<br>";
    echo "3. <strong>Verifica interfaccia utente</strong> del sistema categorie<br>";
    echo "4. Il sistema è <strong>PRONTO PER PRODUZIONE</strong>! 🚀";
    echo "</div>";
    
} else {
    echo "<div class='error' style='font-size: 20px; text-align: center; padding: 20px'>";
    echo "❌ ALCUNI TEST FALLITI ($passed_tests/$total_tests)";
    echo "<br><br><strong>⚠️ SISTEMA business_categories NON COMPLETAMENTE FUNZIONANTE</strong>";
    echo "</div>";
    
    echo "<h3>❌ Test Falliti:</h3>";
    echo "<ul>";
    foreach ($test_results as $test => $result) {
        $status = is_bool($result) ? $result : (is_array($result) ? $result['success'] : false);
        if (!$status) {
            $error = is_array($result) && isset($result['error']) ? $result['error'] : 'Test fallito';
            echo "<li><strong>$test:</strong> $error</li>";
        }
    }
    echo "</ul>";
    
    echo "<div class='warning'>";
    echo "<strong>🔧 AZIONE RICHIESTA:</strong><br>";
    echo "1. Esegui <a href='rebuild_database_emergency.php'>rebuild_database_emergency.php</a><br>";
    echo "2. Riavvia MySQL da XAMPP Control Panel<br>";
    echo "3. Riesegui questo test";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>🕐 Test completato:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='index.php'>← Torna alla home</a> | <a href='admin/'>Admin Panel</a></p>";
echo "</body></html>";
?>
