<?php
// ===================================================================
// 🚨 DIAGNOSI CRITICA EMERGENZA - VERIFICA NOME COLONNA
// ===================================================================

require_once 'includes/db.php';

echo "<h1>🚨 DIAGNOSI EMERGENZA - DISCREPANZA NOME COLONNA</h1>";
echo "<hr>";

// 1. VERIFICA TUTTE LE COLONNE CON "business" NEL NOME
echo "<h2>🔍 RICERCA COLONNE CON 'business' NEL NOME</h2>";
$result = $conn->query("
    SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'aziende' 
    AND COLUMN_NAME LIKE '%business%'
");

if ($result && $result->num_rows > 0) {
    echo "<div style='color:orange; font-weight:bold'>🎯 COLONNE TROVATE:</div>";
    echo "<table border='1' style='border-collapse:collapse; width:100%'>";
    echo "<tr style='background:#f0f0f0'><th>NOME ESATTO</th><th>TIPO</th><th>NULL</th><th>DEFAULT</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $highlight = ($row['COLUMN_NAME'] === 'business_categories') ? 'background:lightgreen' : 'background:lightcoral';
        echo "<tr style='$highlight'>";
        echo "<td><strong>" . $row['COLUMN_NAME'] . "</strong></td>";
        echo "<td>" . $row['DATA_TYPE'] . "</td>";
        echo "<td>" . $row['IS_NULLABLE'] . "</td>";
        echo "<td>" . ($row['COLUMN_DEFAULT'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='color:red; font-size:18px'>❌ NESSUNA COLONNA CON 'business' TROVATA!</div>";
}

// 2. VERIFICA SPECIFICA business_categories VS business_class
echo "<h2>⚖️ CONFRONTO NOMI SPECIFICI</h2>";

$names_to_check = ['business_categories', 'business_class', 'categoria_business', 'business_category'];

foreach ($names_to_check as $name) {
    $result = $conn->query("
        SELECT COUNT(*) as exists_count
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'aziende' 
        AND COLUMN_NAME = '$name'
    ");
    
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row['exists_count'] > 0) {
            echo "<div style='color:green'>✅ COLONNA '$name' ESISTE</div>";
        } else {
            echo "<div style='color:red'>❌ COLONNA '$name' NON ESISTE</div>";
        }
    }
}

// 3. STRUTTURA COMPLETA TABELLA
echo "<h2>📋 STRUTTURA COMPLETA TABELLA AZIENDE</h2>";
$result = $conn->query("DESCRIBE aziende");
if ($result) {
    echo "<table border='1' style='border-collapse:collapse; width:100%'>";
    echo "<tr style='background:#f0f0f0'><th>#</th><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    $i = 1;
    while ($row = $result->fetch_assoc()) {
        $highlight = (strpos($row['Field'], 'business') !== false) ? 'background:yellow' : '';
        echo "<tr style='$highlight'>";
        echo "<td>$i</td>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
        $i++;
    }
    echo "</table>";
}

// 4. TEST QUERY DIRETTA
echo "<h2>🧪 TEST QUERY DIRETTA</h2>";

$test_queries = [
    "SELECT business_categories FROM aziende LIMIT 1",
    "SELECT business_class FROM aziende LIMIT 1"
];

foreach ($test_queries as $query) {
    echo "<div><strong>Test:</strong> <code>$query</code></div>";
    $result = $conn->query($query);
    if ($result) {
        echo "<div style='color:green'>✅ QUERY FUNZIONA</div>";
    } else {
        echo "<div style='color:red'>❌ ERRORE: " . $conn->error . "</div>";
    }
    echo "<br>";
}

// 5. DATABASE CORRENTE
echo "<h2>🗄️ VERIFICA DATABASE CORRENTE</h2>";
$result = $conn->query("SELECT DATABASE() as current_db");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<div><strong>Database attivo:</strong> " . $row['current_db'] . "</div>";
}

// 6. CACHE E PRIVILEGI
echo "<h2>🔄 COMANDI RISOLUZIONE</h2>";
echo "<div style='background:#f5f5f5; padding:10px; border:1px solid #ccc'>";
echo "<strong>COMANDI DA ESEGUIRE IN phpMyAdmin:</strong><br><br>";

echo "<h3>Se la colonna è business_class:</h3>";
echo "<code>ALTER TABLE aziende CHANGE business_class business_categories TEXT;</code><br><br>";

echo "<h3>Se serve flush cache:</h3>";
echo "<code>FLUSH TABLES;<br>FLUSH PRIVILEGES;</code><br><br>";

echo "<h3>Se serve ricreare completamente:</h3>";
echo "<code>ALTER TABLE aziende ADD COLUMN business_categories TEXT NULL AFTER servizi;</code>";
echo "</div>";

echo "<hr>";
echo "<p><strong>🕐 DIAGNOSI EMERGENZA COMPLETATA:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
