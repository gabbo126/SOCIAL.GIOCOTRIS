<?php
// ===================================================================
// 🚨 DEBUG CRITICO: Verifica Struttura Database
// ===================================================================

require_once 'includes/db.php';

echo "<h1>🔍 DIAGNOSI CRITICA DATABASE - SOCIAL GIOCO TRIS</h1>";
echo "<hr>";

// 1. VERIFICA CONNESSIONE DATABASE
echo "<h2>📡 STATO CONNESSIONE</h2>";
if ($conn->connect_error) {
    echo "<div style='color:red'>❌ ERRORE CONNESSIONE: " . $conn->connect_error . "</div>";
    exit;
} else {
    echo "<div style='color:green'>✅ CONNESSO AL DATABASE: <strong>social_gioco_tris</strong></div>";
}

// 2. VERIFICA ESISTENZA TABELLA AZIENDE
echo "<h2>🗃️ VERIFICA TABELLA AZIENDE</h2>";
$result = $conn->query("SHOW TABLES LIKE 'aziende'");
if ($result && $result->num_rows > 0) {
    echo "<div style='color:green'>✅ TABELLA 'aziende' ESISTE</div>";
} else {
    echo "<div style='color:red'>❌ TABELLA 'aziende' NON TROVATA!</div>";
    exit;
}

// 3. STRUTTURA COMPLETA TABELLA AZIENDE
echo "<h2>📋 STRUTTURA TABELLA AZIENDE</h2>";
echo "<h3>🔧 DESCRIBE aziende:</h3>";
$result = $conn->query("DESCRIBE aziende");
if ($result) {
    echo "<table border='1' style='border-collapse:collapse; width:100%'>";
    echo "<tr style='background:#f0f0f0'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $style = ($row['Field'] == 'business_categories') ? 'background:lightgreen' : '';
        echo "<tr style='$style'>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='color:red'>❌ ERRORE LETTURA STRUTTURA: " . $conn->error . "</div>";
}

// 4. VERIFICA SPECIFICA COLONNA BUSINESS_CATEGORIES
echo "<h2>🎯 VERIFICA COLONNA 'business_categories'</h2>";
$result = $conn->query("
    SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'social_gioco_tris' 
    AND TABLE_NAME = 'aziende' 
    AND COLUMN_NAME = 'business_categories'
");

if ($result && $result->num_rows > 0) {
    echo "<div style='color:green'>✅ COLONNA 'business_categories' TROVATA</div>";
    while ($row = $result->fetch_assoc()) {
        echo "<ul>";
        echo "<li><strong>Tipo:</strong> " . $row['DATA_TYPE'] . "</li>";
        echo "<li><strong>Nullable:</strong> " . $row['IS_NULLABLE'] . "</li>";
        echo "<li><strong>Default:</strong> " . ($row['COLUMN_DEFAULT'] ?? 'NULL') . "</li>";
        echo "<li><strong>Commento:</strong> " . ($row['COLUMN_COMMENT'] ?? 'Nessuno') . "</li>";
        echo "</ul>";
    }
} else {
    echo "<div style='color:red; font-size:18px; font-weight:bold'>❌ COLONNA 'business_categories' NON ESISTE!</div>";
    echo "<div style='color:orange'>⚠️ QUESTA È LA CAUSA DELL'ERRORE!</div>";
}

// 5. CONTEGGIO RECORD ESISTENTI
echo "<h2>📊 STATISTICHE TABELLA</h2>";
$result = $conn->query("SELECT COUNT(*) as total FROM aziende");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<div>📈 <strong>Record esistenti:</strong> " . $row['total'] . " aziende</div>";
} else {
    echo "<div style='color:red'>❌ ERRORE CONTEGGIO: " . $conn->error . "</div>";
}

// 6. QUERY MIGRATION SUGGERITA
echo "<h2>🛠️ SOLUTION SQL IMMEDIATA</h2>";
echo "<div style='background:#f5f5f5; padding:10px; border:1px solid #ccc'>";
echo "<strong>ESEGUI QUESTA QUERY IN phpMyAdmin:</strong><br><br>";
echo "<code style='color:blue; font-size:14px'>";
echo "ALTER TABLE `aziende` <br>";
echo "ADD COLUMN `business_categories` TEXT NULL <br>";
echo "COMMENT 'Categorie business in formato JSON per sistema avanzato' <br>";
echo "AFTER `servizi`;";
echo "</code>";
echo "</div>";

echo "<hr>";
echo "<p><strong>🕐 DEBUG COMPLETATO:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
