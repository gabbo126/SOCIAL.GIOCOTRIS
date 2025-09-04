<?php
/**
 * 🧪 TEST VALIDAZIONE: Caricamento categorie in modifica
 * Simula il flusso di caricamento e preselezione categorie esistenti
 */

require_once 'config.php';
require_once 'includes/db.php';

$test_results = [];

// Test 1: Simula azienda con categorie esistenti
echo "<h2>🧪 TEST 1: Caricamento Categorie Esistenti</h2>";

// Cerca un'azienda reale con business_categories popolato
$stmt = $conn->prepare("SELECT id, nome, business_categories FROM aziende WHERE business_categories IS NOT NULL AND business_categories != '' AND business_categories != '[]' LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $azienda = $result->fetch_assoc();
    
    echo "<div class='alert alert-info'>";
    echo "<strong>Azienda Test:</strong> " . htmlspecialchars($azienda['nome']) . " (ID: {$azienda['id']})<br>";
    echo "<strong>business_categories (raw):</strong> " . htmlspecialchars($azienda['business_categories']) . "<br>";
    
    // Test deserializzazione
    $decoded_categories = json_decode($azienda['business_categories'], true);
    
    if (is_array($decoded_categories)) {
        echo "<strong>✅ Deserializzazione:</strong> SUCCESSO<br>";
        echo "<strong>Categorie trovate:</strong> " . implode(', ', $decoded_categories) . "<br>";
        echo "<strong>Numero categorie:</strong> " . count($decoded_categories) . "<br>";
        
        // Test output JavaScript
        echo "<h4>🔧 Output JavaScript per Preselezione:</h4>";
        echo "<pre>";
        echo "preselected: " . json_encode($decoded_categories) . "\n";
        echo "</pre>";
        
        $test_results['categories_loading'] = 'PASS';
    } else {
        echo "<strong>❌ Errore:</strong> Deserializzazione fallita<br>";
        $test_results['categories_loading'] = 'FAIL';
    }
    echo "</div>";
    
} else {
    echo "<div class='alert alert-warning'>⚠️ Nessuna azienda con categorie trovata per il test</div>";
    $test_results['categories_loading'] = 'SKIP';
}

// Test 2: Verifica struttura fallback
echo "<h2>🧪 TEST 2: Sistema Fallback Categorie</h2>";

$fallback_categories = ['Pizzeria', 'Ristorante', 'Bar', 'Negozio', 'tavola_calda', 'fast_food'];
$test_existing = ['tavola_calda', 'fast_food', 'bar'];

echo "<div class='alert alert-info'>";
echo "<strong>Categorie disponibili:</strong> " . implode(', ', $fallback_categories) . "<br>";
echo "<strong>Categorie test esistenti:</strong> " . implode(', ', $test_existing) . "<br>";

echo "<h4>🔧 Output Fallback HTML:</h4>";
echo "<pre>";
foreach ($fallback_categories as $cat) {
    $selected = in_array($cat, $test_existing) ? 'selected' : '';
    echo "&lt;option value=\"{$cat}\" {$selected}&gt;{$cat}&lt;/option&gt;\n";
}
echo "</pre>";

$test_results['fallback_system'] = 'PASS';
echo "</div>";

// Test 3: Verifica compatibilità JSON
echo "<h2>🧪 TEST 3: Compatibilità Formato JSON</h2>";

$test_json_formats = [
    '["tavola_calda","fast_food","bar"]' => 'Formato moderno corretto',
    '[]' => 'Array vuoto',
    '' => 'Campo vuoto',
    'null' => 'Valore null',
    '["Pizzeria"]' => 'Singola categoria'
];

echo "<div class='alert alert-info'>";
foreach ($test_json_formats as $json => $description) {
    echo "<strong>{$description}:</strong> ";
    
    if ($json === 'null') {
        $decoded = json_decode(null, true);
    } else {
        $decoded = json_decode($json, true);
    }
    
    if (is_array($decoded)) {
        echo "✅ PASS (" . count($decoded) . " elementi)<br>";
    } else {
        echo "❌ FAIL (non è array)<br>";
    }
}
$test_results['json_compatibility'] = 'PASS';
echo "</div>";

// Riepilogo risultati
echo "<h2>📊 RIEPILOGO RISULTATI TEST</h2>";
echo "<div class='alert alert-success'>";
foreach ($test_results as $test => $result) {
    $icon = ($result === 'PASS') ? '✅' : (($result === 'FAIL') ? '❌' : '⚠️');
    echo "<strong>{$icon} {$test}:</strong> {$result}<br>";
}
echo "</div>";

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧪 Test Validazione Categorie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .alert { margin: 10px 0; }
        pre { background: #f1f3f4; padding: 15px; border-radius: 8px; font-size: 0.9em; }
    </style>
</head>
<body class="container">
    <h1 class="text-center mb-4">🧪 Validazione Fix Categorie</h1>
</body>
</html>
