<?php
/**
 * 🧪 TEST ADVANCED MEDIA SYSTEM - Testing Completo v2.0
 * Verifica funzionamento end-to-end del nuovo sistema media
 */

require_once 'config.php';
require_once 'includes/db.php';

echo "<!DOCTYPE html>
<html lang='it'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>🧪 Test Advanced Media System</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css'>
    <style>
        .test-section { margin: 2rem 0; padding: 1.5rem; border: 1px solid #dee2e6; border-radius: 0.375rem; }
        .test-success { background: #d4edda; border-color: #c3e6cb; }
        .test-error { background: #f8d7da; border-color: #f5c6cb; }
        .test-warning { background: #fff3cd; border-color: #ffeaa7; }
        .code-block { background: #f8f9fa; padding: 1rem; border-radius: 0.25rem; font-family: monospace; }
    </style>
</head>
<body class='bg-light'>
<div class='container py-4'>
    <h1 class='mb-4'><i class='bi bi-gear-fill text-primary'></i> Test Advanced Media System</h1>";

// 🔍 TEST 1: VERIFICA DATABASE SCHEMA
echo "<div class='test-section'>";
echo "<h3><i class='bi bi-database'></i> Test 1: Verifica Schema Database</h3>";

try {
    // Verifica tabella azienda_media
    $result = $conn->query("DESCRIBE azienda_media");
    if ($result && $result->num_rows > 0) {
        echo "<div class='alert alert-success'><i class='bi bi-check-circle'></i> ✅ Tabella azienda_media presente</div>";
        
        echo "<div class='code-block'>";
        echo "<strong>Struttura tabella:</strong><br>";
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['Field']} ({$row['Type']}) {$row['Null']} {$row['Key']}<br>";
        }
        echo "</div>";
    } else {
        throw new Exception("Tabella azienda_media non trovata");
    }
    
    // Verifica stored procedure
    $result = $conn->query("SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'CheckMediaLimits'");
    if ($result && $result->num_rows > 0) {
        echo "<div class='alert alert-success'><i class='bi bi-check-circle'></i> ✅ Stored Procedure CheckMediaLimits presente</div>";
    } else {
        echo "<div class='alert alert-warning'><i class='bi bi-exclamation-triangle'></i> ⚠️ Stored Procedure CheckMediaLimits mancante</div>";
    }
    
    // Verifica view legacy
    $result = $conn->query("SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_" . DB_NAME . " = 'legacy_azienda_media_view'");
    if ($result && $result->num_rows > 0) {
        echo "<div class='alert alert-success'><i class='bi bi-check-circle'></i> ✅ View legacy presente per compatibilità</div>";
    } else {
        echo "<div class='alert alert-info'><i class='bi bi-info-circle'></i> ℹ️ View legacy non trovata (opzionale)</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'><i class='bi bi-x-circle'></i> ❌ Errore database: " . $e->getMessage() . "</div>";
}
echo "</div>";

// 🔍 TEST 2: VERIFICA API BACKEND
echo "<div class='test-section'>";
echo "<h3><i class='bi bi-cloud-arrow-up'></i> Test 2: Verifica API Backend</h3>";

// Test API disponibilità
$api_file = 'api/media_manager.php';
if (file_exists($api_file)) {
    echo "<div class='alert alert-success'><i class='bi bi-check-circle'></i> ✅ API file presente: $api_file</div>";
    
    // Test API response
    $test_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/$api_file?action=limits&azienda_id=1";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($test_url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<div class='alert alert-success'><i class='bi bi-check-circle'></i> ✅ API risponde correttamente</div>";
            echo "<div class='code-block'><strong>Risposta test:</strong><br>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</div>";
        } else {
            echo "<div class='alert alert-warning'><i class='bi bi-exclamation-triangle'></i> ⚠️ API risponde ma JSON non valido</div>";
        }
    } else {
        echo "<div class='alert alert-danger'><i class='bi bi-x-circle'></i> ❌ API non risponde</div>";
    }
    
} else {
    echo "<div class='alert alert-danger'><i class='bi bi-x-circle'></i> ❌ API file non trovato: $api_file</div>";
}
echo "</div>";

// 🔍 TEST 3: VERIFICA FILES FRONTEND
echo "<div class='test-section'>";
echo "<h3><i class='bi bi-code-slash'></i> Test 3: Verifica Files Frontend</h3>";

$frontend_files = [
    'assets/js/advanced-media-manager.js' => 'JavaScript principale',
    'assets/css/advanced-media-manager.css' => 'CSS styling',
    'templates/advanced-media-section.php' => 'Template integrazione'
];

foreach ($frontend_files as $file => $desc) {
    if (file_exists($file)) {
        $size = round(filesize($file) / 1024, 2);
        echo "<div class='alert alert-success'><i class='bi bi-check-circle'></i> ✅ $desc: $file ({$size}KB)</div>";
    } else {
        echo "<div class='alert alert-danger'><i class='bi bi-x-circle'></i> ❌ File mancante: $file - $desc</div>";
    }
}
echo "</div>";

// 🔍 TEST 4: TEST FUNZIONALE MEDIA MANAGER
echo "<div class='test-section'>";
echo "<h3><i class='bi bi-images'></i> Test 4: Demo Media Manager</h3>";

// Test con azienda esempio
$test_azienda_id = 1;

echo "<div class='alert alert-info'>";
echo "<i class='bi bi-info-circle'></i> <strong>Demo interattiva:</strong> Test del sistema completo con azienda ID $test_azienda_id";
echo "</div>";

// Include il template
$azienda_id = $test_azienda_id;
$context = 'edit';
$readonly = false;

echo "<div class='border p-4 bg-white rounded'>";
include 'templates/advanced-media-section.php';
echo "</div>";

echo "</div>";

// 🔍 TEST 5: VERIFICA COMPATIBILITÀ BROWSER
echo "<div class='test-section'>";
echo "<h3><i class='bi bi-browser-chrome'></i> Test 5: Compatibilità Browser</h3>";

echo "<div class='row'>";

// Test JavaScript features
echo "<div class='col-md-6'>";
echo "<h5>JavaScript Features</h5>";
echo "<div id='js-features-test'>";
echo "<div class='alert alert-info'>Testing JavaScript features...</div>";
echo "</div>";
echo "</div>";

// Test CSS features
echo "<div class='col-md-6'>";
echo "<h5>CSS Features</h5>";
echo "<div id='css-features-test'>";
echo "<div class='alert alert-info'>Testing CSS features...</div>";
echo "</div>";
echo "</div>";

echo "</div>";
echo "</div>";

// 🔍 TEST 6: PERFORMANCE BENCHMARK
echo "<div class='test-section'>";
echo "<h3><i class='bi bi-speedometer2'></i> Test 6: Performance Benchmark</h3>";

$start_time = microtime(true);

// Test query performance
$test_queries = [
    "SELECT COUNT(*) FROM azienda_media WHERE attivo = 1",
    "SELECT * FROM aziende WHERE piano = 'base' LIMIT 5",
    "SELECT am.*, a.nome_azienda FROM azienda_media am JOIN aziende a ON am.azienda_id = a.id LIMIT 10"
];

echo "<div class='row'>";
foreach ($test_queries as $i => $query) {
    $query_start = microtime(true);
    $result = $conn->query($query);
    $query_time = round((microtime(true) - $query_start) * 1000, 2);
    
    $status = $query_time < 100 ? 'success' : ($query_time < 500 ? 'warning' : 'danger');
    $rows = $result ? $result->num_rows : 0;
    
    echo "<div class='col-md-4'>";
    echo "<div class='alert alert-$status'>";
    echo "<strong>Query " . ($i + 1) . ":</strong><br>";
    echo "Tempo: {$query_time}ms<br>";
    echo "Risultati: $rows righe";
    echo "</div>";
    echo "</div>";
}
echo "</div>";

$total_time = round((microtime(true) - $start_time) * 1000, 2);
echo "<div class='alert alert-info text-center'>";
echo "<strong>Tempo totale test:</strong> {$total_time}ms";
echo "</div>";

echo "</div>";

// JAVASCRIPT TESTS
echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
echo "<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test JavaScript features
    testJavaScriptFeatures();
    testCSSFeatures();
});

function testJavaScriptFeatures() {
    const container = document.getElementById('js-features-test');
    const tests = [
        { name: 'ES6 Classes', test: () => typeof class {} === 'function' },
        { name: 'Fetch API', test: () => typeof fetch === 'function' },
        { name: 'Promise', test: () => typeof Promise === 'function' },
        { name: 'FormData', test: () => typeof FormData === 'function' },
        { name: 'File API', test: () => typeof File === 'function' },
        { name: 'Drag & Drop', test: () => 'draggable' in document.createElement('div') }
    ];
    
    let html = '';
    tests.forEach(test => {
        const passed = test.test();
        const icon = passed ? 'bi-check-circle text-success' : 'bi-x-circle text-danger';
        const status = passed ? '✅' : '❌';
        html += `<div><i class='bi ${icon}'></i> ${status} ${test.name}</div>`;
    });
    
    container.innerHTML = html;
}

function testCSSFeatures() {
    const container = document.getElementById('css-features-test');
    const testDiv = document.createElement('div');
    document.body.appendChild(testDiv);
    
    const tests = [
        { name: 'CSS Grid', test: () => CSS.supports('display', 'grid') },
        { name: 'Flexbox', test: () => CSS.supports('display', 'flex') },
        { name: 'CSS Variables', test: () => CSS.supports('color', 'var(--test)') },
        { name: 'Transform', test: () => CSS.supports('transform', 'translateX(10px)') },
        { name: 'Transition', test: () => CSS.supports('transition', 'all 0.3s') },
        { name: 'Border Radius', test: () => CSS.supports('border-radius', '10px') }
    ];
    
    let html = '';
    tests.forEach(test => {
        const passed = test.test();
        const icon = passed ? 'bi-check-circle text-success' : 'bi-x-circle text-danger';
        const status = passed ? '✅' : '❌';
        html += `<div><i class='bi ${icon}'></i> ${status} ${test.name}</div>`;
    });
    
    container.innerHTML = html;
    document.body.removeChild(testDiv);
}
</script>";

echo "</div></body></html>";
?>
