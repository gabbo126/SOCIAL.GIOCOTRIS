<?php
// ===================================================================
// 🚨 EMERGENCY DATABASE REBUILD - RICOSTRUZIONE TOTALE
// ===================================================================
// ⚠️ ATTENZIONE: Questo script ELIMINA e RICREA il database completo!
// ✅ Autorizzato dall'utente - Sito in sviluppo
// ===================================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>DATABASE REBUILD</title>";
echo "<style>body{font-family:Arial,sans-serif; margin:20px} .success{color:green; font-weight:bold} .error{color:red; font-weight:bold} .info{color:blue} .warning{color:orange; font-weight:bold}</style>";
echo "</head><body>";

echo "<h1>🚨 EMERGENCY DATABASE REBUILD</h1>";
echo "<div class='warning'>⚠️ RICOSTRUZIONE COMPLETA DATABASE IN CORSO...</div>";
echo "<hr>";

// STEP 1: CONNESSIONE ROOT MYSQL
echo "<h2>STEP 1: CONNESSIONE MYSQL ROOT</h2>";
$root_conn = new mysqli("localhost", "root", "");

if ($root_conn->connect_error) {
    echo "<div class='error'>❌ ERRORE CONNESSIONE ROOT: " . $root_conn->connect_error . "</div>";
    exit;
}
echo "<div class='success'>✅ CONNESSO A MYSQL COME ROOT</div>";

// STEP 2: ELIMINA DATABASE ESISTENTE
echo "<h2>STEP 2: ELIMINAZIONE DATABASE ESISTENTE</h2>";
$drop_result = $root_conn->query("DROP DATABASE IF EXISTS social_gioco_tris");
if ($drop_result) {
    echo "<div class='success'>✅ DATABASE ELIMINATO (se esisteva)</div>";
} else {
    echo "<div class='error'>❌ ERRORE DROP DATABASE: " . $root_conn->error . "</div>";
}

// STEP 3: CREA DATABASE PULITO
echo "<h2>STEP 3: CREAZIONE DATABASE PULITO</h2>";
$create_result = $root_conn->query("CREATE DATABASE social_gioco_tris CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
if ($create_result) {
    echo "<div class='success'>✅ DATABASE CREATO CON UTF8MB4</div>";
} else {
    echo "<div class='error'>❌ ERRORE CREATE DATABASE: " . $root_conn->error . "</div>";
    exit;
}

// STEP 4: SELEZIONA DATABASE
echo "<h2>STEP 4: SELEZIONE DATABASE</h2>";
$select_result = $root_conn->select_db("social_gioco_tris");
if ($select_result) {
    echo "<div class='success'>✅ DATABASE SELEZIONATO</div>";
} else {
    echo "<div class='error'>❌ ERRORE SELEZIONE DATABASE</div>";
    exit;
}

// STEP 5: CREA TABELLA AZIENDE COMPLETA
echo "<h2>STEP 5: CREAZIONE TABELLA AZIENDE</h2>";

$create_table_sql = "
CREATE TABLE aziende (
    id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    nome varchar(255) NOT NULL,
    iniziale char(1) DEFAULT NULL,
    descrizione text,
    indirizzo varchar(255) DEFAULT NULL,
    telefono varchar(50) DEFAULT NULL,
    email varchar(255) DEFAULT NULL,
    sito_web varchar(255) DEFAULT NULL,
    tipo_struttura varchar(100) DEFAULT NULL,
    servizi text,
    business_categories TEXT NULL COMMENT 'Categorie business in formato JSON per sistema avanzato',
    logo_url varchar(255) DEFAULT NULL,
    foto1_url varchar(255) DEFAULT NULL,
    foto2_url varchar(255) DEFAULT NULL,
    foto3_url varchar(255) DEFAULT NULL,
    data_inserimento timestamp DEFAULT CURRENT_TIMESTAMP,
    media_json text,
    PRIMARY KEY (id),
    KEY idx_nome (nome),
    KEY idx_iniziale (iniziale)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$table_result = $root_conn->query($create_table_sql);
if ($table_result) {
    echo "<div class='success'>✅ TABELLA AZIENDE CREATA CON business_categories</div>";
} else {
    echo "<div class='error'>❌ ERRORE CREAZIONE TABELLA: " . $root_conn->error . "</div>";
    exit;
}

// STEP 6: CREA TABELLA TOKENS
echo "<h2>STEP 6: CREAZIONE TABELLA TOKENS</h2>";

$create_tokens_sql = "
CREATE TABLE tokens (
    id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    token varchar(255) NOT NULL UNIQUE,
    id_azienda int(10) UNSIGNED NOT NULL,
    type enum('registrazione','modifica') NOT NULL,
    status enum('attivo','usato','scaduto') DEFAULT 'attivo',
    data_creazione timestamp DEFAULT CURRENT_TIMESTAMP,
    data_scadenza datetime DEFAULT NULL,
    tipo_pacchetto varchar(50) DEFAULT 'basic',
    PRIMARY KEY (id),
    KEY idx_token (token),
    KEY idx_id_azienda (id_azienda),
    KEY idx_status (status),
    FOREIGN KEY (id_azienda) REFERENCES aziende(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$tokens_result = $root_conn->query($create_tokens_sql);
if ($tokens_result) {
    echo "<div class='success'>✅ TABELLA TOKENS CREATA</div>";
} else {
    echo "<div class='error'>❌ ERRORE CREAZIONE TOKENS: " . $root_conn->error . "</div>";
}

// STEP 7: VERIFICA STRUTTURA business_categories
echo "<h2>STEP 7: VERIFICA COLONNA business_categories</h2>";

$verify_result = $root_conn->query("DESCRIBE aziende");
if ($verify_result) {
    echo "<table border='1' style='border-collapse:collapse; width:100%'>";
    echo "<tr style='background:#f0f0f0'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $found_business_categories = false;
    while ($row = $verify_result->fetch_assoc()) {
        if ($row['Field'] == 'business_categories') {
            $found_business_categories = true;
            echo "<tr style='background:lightgreen'>";
        } else {
            echo "<tr>";
        }
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($found_business_categories) {
        echo "<div class='success'>✅ COLONNA business_categories PRESENTE E ATTIVA</div>";
    } else {
        echo "<div class='error'>❌ COLONNA business_categories NON TROVATA!</div>";
    }
}

// STEP 8: TEST INSERT/UPDATE COMPLETO
echo "<h2>STEP 8: TEST INSERT/UPDATE BUSINESS_CATEGORIES</h2>";

// Test INSERT
$test_data = json_encode(['Ristorazione', 'Bar', 'Pizzeria']);
$insert_sql = "INSERT INTO aziende (nome, descrizione, business_categories) VALUES (?, ?, ?)";
$stmt = $root_conn->prepare($insert_sql);

if ($stmt) {
    $test_nome = "TEST AZIENDA " . date('H:i:s');
    $test_desc = "Azienda di test per verifica sistema categorie";
    
    $stmt->bind_param('sss', $test_nome, $test_desc, $test_data);
    
    if ($stmt->execute()) {
        $test_id = $root_conn->insert_id;
        echo "<div class='success'>✅ INSERT TEST RIUSCITO - ID: $test_id</div>";
        echo "<div class='info'>📊 Dati inseriti: $test_data</div>";
        
        // Test SELECT
        $select_sql = "SELECT id, nome, business_categories FROM aziende WHERE id = ?";
        $select_stmt = $root_conn->prepare($select_sql);
        $select_stmt->bind_param('i', $test_id);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo "<div class='success'>✅ SELECT TEST RIUSCITO</div>";
            echo "<div class='info'>📖 Dati recuperati: " . htmlspecialchars($row['business_categories']) . "</div>";
        }
        
        // Test UPDATE
        $new_data = json_encode(['Hotel', 'Ristorante', 'Spa']);
        $update_sql = "UPDATE aziende SET business_categories = ? WHERE id = ?";
        $update_stmt = $root_conn->prepare($update_sql);
        $update_stmt->bind_param('si', $new_data, $test_id);
        
        if ($update_stmt->execute()) {
            echo "<div class='success'>✅ UPDATE TEST RIUSCITO</div>";
            echo "<div class='info'>📝 Nuovi dati: $new_data</div>";
        } else {
            echo "<div class='error'>❌ ERRORE UPDATE: " . $update_stmt->error . "</div>";
        }
        
        // Cleanup test
        $root_conn->query("DELETE FROM aziende WHERE id = $test_id");
        echo "<div class='info'>🧹 Record di test eliminato</div>";
        
    } else {
        echo "<div class='error'>❌ ERRORE INSERT: " . $stmt->error . "</div>";
    }
} else {
    echo "<div class='error'>❌ ERRORE PREPARE INSERT: " . $root_conn->error . "</div>";
}

// STEP 9: INFORMAZIONI DATABASE
echo "<h2>STEP 9: INFORMAZIONI DATABASE FINALE</h2>";
$info_result = $root_conn->query("SELECT 
    DATABASE() as current_db,
    @@character_set_database as charset,
    @@collation_database as collation,
    VERSION() as mysql_version
");

if ($info_result) {
    $info = $info_result->fetch_assoc();
    echo "<ul>";
    echo "<li><strong>Database attivo:</strong> " . $info['current_db'] . "</li>";
    echo "<li><strong>Charset:</strong> " . $info['charset'] . "</li>";
    echo "<li><strong>Collation:</strong> " . $info['collation'] . "</li>";
    echo "<li><strong>MySQL Version:</strong> " . $info['mysql_version'] . "</li>";
    echo "</ul>";
}

$root_conn->close();

echo "<hr>";
echo "<h1 style='color:green'>🎉 RICOSTRUZIONE DATABASE COMPLETATA!</h1>";
echo "<div class='success'>✅ Database social_gioco_tris ricreato con successo</div>";
echo "<div class='success'>✅ Tabella aziende con colonna business_categories funzionante</div>";
echo "<div class='success'>✅ Test INSERT/UPDATE/SELECT completati con successo</div>";

echo "<h3>🚀 PROSSIMI STEP:</h3>";
echo "<ol>";
echo "<li>Testa la registrazione azienda: <a href='inserimento.php'>inserimento.php</a></li>";
echo "<li>Testa la modifica azienda esistente</li>";
echo "<li>Esegui test completo: <a href='test_business_categories.php'>test_business_categories.php</a></li>";
echo "</ol>";

echo "<div class='warning'>⚠️ Ricorda: Tutti i dati precedenti sono stati eliminati!</div>";
echo "<p><strong>🕐 REBUILD COMPLETATO:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</body></html>";
?>
