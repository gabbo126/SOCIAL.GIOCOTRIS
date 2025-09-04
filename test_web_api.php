<?php
/**
 * 🧪 TEST WEB API - Verifica funzionamento via HTTP
 */

echo "<h2>🧪 Test Web API - Media Manager</h2>";

// Simula chiamata GET per get_limits
$_GET['action'] = 'get_limits';
$_GET['azienda_id'] = '0';

echo "<p><strong>📋 Parametri Test:</strong></p>";
echo "<pre>";
echo "action = " . $_GET['action'] . "\n";
echo "azienda_id = " . $_GET['azienda_id'] . "\n";
echo "</pre>";

echo "<p><strong>🚀 Chiamata API:</strong></p>";
echo "<pre>";

// Cattura output
ob_start();

try {
    // Include config e database
    require_once 'config.php';
    require_once 'includes/db.php';
    
    // Include e testa MediaManager
    require_once 'api/media_manager.php';
    
    $media_manager = new MediaManager($conn);
    
    // Test reflection per verificare metodi
    $reflection = new ReflectionClass('MediaManager');
    $methods = $reflection->getMethods();
    
    $hasJsonResponse = false;
    $hasGetMediaLimits = false;
    
    foreach ($methods as $method) {
        if ($method->name === 'jsonResponse') {
            $hasJsonResponse = true;
        }
        if ($method->name === 'getMediaLimits') {
            $hasGetMediaLimits = true;
        }
    }
    
    echo "✅ MediaManager caricata correttamente\n";
    echo "📊 Metodo jsonResponse(): " . ($hasJsonResponse ? "✅ PRESENTE" : "❌ MANCANTE") . "\n";
    echo "📊 Metodo getMediaLimits(): " . ($hasGetMediaLimits ? "✅ PRESENTE" : "❌ MANCANTE") . "\n";
    echo "\n🚀 Esecuzione handleRequest()...\n\n";
    
    // Esegui la chiamata
    $media_manager->handleRequest();
    
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " (linea " . $e->getLine() . ")\n";
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " (linea " . $e->getLine() . ")\n";
}

$output = ob_get_clean();
echo htmlspecialchars($output);

echo "</pre>";
?>
