<?php
// ===================================================================
// 🔬 FORENSIC DEBUG TEST - ANALISI SCIENTIFICA ANOMALIA MYSQL
// ===================================================================

// Include SOLO la connessione DB, nessun'altra dipendenza
require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>FORENSIC DEBUG</title></head><body>";
echo "<h1>🔬 FORENSIC DEBUG TEST - ANOMALIA business_categories</h1>";
echo "<hr>";

// ====================
// TEST 1: CONNESSIONE
// ====================
echo "<h2>📊 TEST 1: VERIFICA CONNESSIONE DATABASE</h2>";
$db_check = $conn->query("SELECT DATABASE() as current_db, CURRENT_USER() as current_user, @@hostname as server, @@port as port, @@version as mysql_version");
if ($db_check) {
    $db_info = $db_check->fetch_assoc();
    echo "<table border='1' style='border-collapse:collapse'>";
    echo "<tr><th>Parametro</th><th>Valore</th></tr>";
    foreach ($db_info as $key => $value) {
        echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
    }
    echo "</table>";
} else {
    echo "<div style='color:red'>❌ ERRORE CONNESSIONE: " . $conn->error . "</div>";
}

// ====================
// TEST 2: ESISTENZA COLONNA
// ====================
echo "<h2>🔍 TEST 2: VERIFICA ESISTENZA COLONNA business_categories</h2>";

// Metodo 1: INFORMATION_SCHEMA
echo "<h3>Metodo 1: INFORMATION_SCHEMA</h3>";
$result = $conn->query("
    SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'social_gioco_tris' 
    AND TABLE_NAME = 'aziende' 
    AND COLUMN_NAME = 'business_categories'
");
if ($result && $result->num_rows > 0) {
    echo "<div style='color:green'>✅ Colonna TROVATA in INFORMATION_SCHEMA</div>";
    $row = $result->fetch_assoc();
    echo "<pre>" . print_r($row, true) . "</pre>";
} else {
    echo "<div style='color:red'>❌ Colonna NON TROVATA in INFORMATION_SCHEMA</div>";
}

// Metodo 2: DESCRIBE
echo "<h3>Metodo 2: DESCRIBE aziende</h3>";
$result = $conn->query("DESCRIBE aziende");
$found = false;
if ($result) {
    echo "<table border='1' style='border-collapse:collapse; width:100%'>";
    echo "<tr style='background:#f0f0f0'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] == 'business_categories') {
            $found = true;
            echo "<tr style='background:lightgreen'>";
        } else {
            echo "<tr>";
        }
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($found) {
        echo "<div style='color:green'>✅ Colonna business_categories PRESENTE nella struttura</div>";
    } else {
        echo "<div style='color:red'>❌ Colonna business_categories NON PRESENTE nella struttura</div>";
    }
}

// ====================
// TEST 3: QUERY DIRETTE
// ====================
echo "<h2>🧪 TEST 3: QUERY DIRETTE</h2>";

// Test SELECT semplice
echo "<h3>Test SELECT semplice:</h3>";
$test_query = "SELECT business_categories FROM aziende LIMIT 1";
echo "<code>$test_query</code><br>";
$result = $conn->query($test_query);
if ($result) {
    echo "<div style='color:green'>✅ SELECT FUNZIONA</div>";
    if ($row = $result->fetch_assoc()) {
        echo "<div>Valore: " . htmlspecialchars(var_export($row['business_categories'], true)) . "</div>";
    }
} else {
    echo "<div style='color:red'>❌ ERRORE: " . $conn->error . "</div>";
}

// Test UPDATE con valore statico
echo "<h3>Test UPDATE con valore statico (non eseguito):</h3>";
$test_update = "UPDATE aziende SET business_categories = '[]' WHERE id = 1";
echo "<code>$test_update</code><br>";
// Non eseguiamo realmente, solo testiamo se la query è valida
$stmt = $conn->prepare("UPDATE aziende SET business_categories = ? WHERE id = ?");
if ($stmt) {
    echo "<div style='color:green'>✅ PREPARE UPDATE FUNZIONA</div>";
    $stmt->close();
} else {
    echo "<div style='color:red'>❌ ERRORE PREPARE: " . $conn->error . "</div>";
}

// ====================
// TEST 4: PREPARED STATEMENT COMPLETO
// ====================
echo "<h2>📝 TEST 4: PREPARED STATEMENT COMPLETO (come nel file originale)</h2>";

// Simula la query esatta del file processa_modifica_token.php
$query = "UPDATE aziende SET nome = ?, tipo_struttura = ?, descrizione = ?, indirizzo = ?, telefono = ?, email = ?, sito_web = ?, servizi = ?, business_categories = ?, logo_url = ?, foto1_url = ?, foto2_url = ?, foto3_url = ?, media_json = ? WHERE id = ?";
echo "<div style='background:#f0f0f0; padding:10px; overflow-x:auto'>";
echo "<code>" . htmlspecialchars($query) . "</code>";
echo "</div>";

$stmt = $conn->prepare($query);
if ($stmt) {
    echo "<div style='color:green'>✅ PREPARE STATEMENT COMPLETO FUNZIONA!</div>";
    $stmt->close();
} else {
    echo "<div style='color:red; font-size:18px'>❌ ERRORE PREPARE: <strong>" . $conn->error . "</strong></div>";
}

// ====================
// TEST 5: CACHE E PRIVILEGI
// ====================
echo "<h2>🔄 TEST 5: FLUSH CACHE E PRIVILEGI</h2>";

// Flush tables
$conn->query("FLUSH TABLES");
echo "<div>✅ FLUSH TABLES eseguito</div>";

// Verifica privilegi
$result = $conn->query("SHOW GRANTS FOR CURRENT_USER()");
if ($result) {
    echo "<h3>Privilegi utente corrente:</h3>";
    while ($row = $result->fetch_row()) {
        echo "<div><code>" . htmlspecialchars($row[0]) . "</code></div>";
    }
}

// ====================
// TEST 6: CHARSET E COLLATION
// ====================
echo "<h2>🌐 TEST 6: CHARSET E COLLATION</h2>";
$result = $conn->query("SHOW VARIABLES LIKE '%character%'");
if ($result) {
    echo "<table border='1' style='border-collapse:collapse'>";
    echo "<tr><th>Variable</th><th>Value</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['Variable_name'] . "</td><td>" . $row['Value'] . "</td></tr>";
    }
    echo "</table>";
}

// ====================
// TEST 7: PROCESSI ATTIVI
// ====================
echo "<h2>⚙️ TEST 7: PROCESSI E TRANSAZIONI</h2>";
$result = $conn->query("SHOW PROCESSLIST");
if ($result) {
    echo "<h3>Processi attivi:</h3>";
    echo "<table border='1' style='border-collapse:collapse; width:100%'>";
    echo "<tr><th>Id</th><th>User</th><th>Host</th><th>db</th><th>Command</th><th>Time</th><th>State</th><th>Info</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// ====================
// RIEPILOGO FINALE
// ====================
echo "<h2>📊 RIEPILOGO DIAGNOSTICO</h2>";
echo "<div style='background:#ffeecc; padding:20px; border:2px solid #ff9900'>";
echo "<h3>RISULTATI TEST:</h3>";
echo "<ul>";
echo "<li>Database attivo: <strong>social_gioco_tris</strong></li>";
echo "<li>Colonna business_categories in INFORMATION_SCHEMA: <strong id='info_schema'>?</strong></li>";
echo "<li>Colonna business_categories in DESCRIBE: <strong id='describe'>?</strong></li>";
echo "<li>SELECT business_categories: <strong id='select'>?</strong></li>";
echo "<li>PREPARE UPDATE: <strong id='prepare'>?</strong></li>";
echo "</ul>";

echo "<h3>POSSIBILI CAUSE SE ANOMALIA PERSISTE:</h3>";
echo "<ol>";
echo "<li><strong>Cache MySQL persistente:</strong> Riavvia completamente MySQL da XAMPP Control Panel</li>";
echo "<li><strong>Transazione sospesa:</strong> Esegui COMMIT; in phpMyAdmin</li>";
echo "<li><strong>Permessi insufficienti:</strong> Verifica GRANT per l'utente root</li>";
echo "<li><strong>Corruzione indice:</strong> REPAIR TABLE aziende; in phpMyAdmin</li>";
echo "<li><strong>Database multipli:</strong> Verifica non ci siano duplicati del database</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><strong>🕐 TEST COMPLETATO:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</body></html>";
?>
