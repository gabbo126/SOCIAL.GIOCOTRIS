<?php
/**
 * 🧪 TEST MINIMO API - Verifica metodo jsonResponse()
 */

require_once 'config.php';
require_once 'includes/db.php';

// Test diretto della classe MediaManager
try {
    // Include la classe
    require_once 'api/media_manager.php';
    
    // Testa se la classe può essere istanziata
    $media_manager = new MediaManager($conn);
    echo "✅ MediaManager istanziata correttamente\n";
    
    // Test reflection per verificare metodi
    $reflection = new ReflectionClass('MediaManager');
    $methods = $reflection->getMethods();
    
    echo "\n📋 Metodi trovati nella classe:\n";
    foreach ($methods as $method) {
        if ($method->name === 'jsonResponse') {
            echo "✅ jsonResponse() - TROVATO (visibilità: " . ($method->isPrivate() ? 'private' : 'public') . ")\n";
        }
        if ($method->name === 'getMediaLimits') {
            echo "✅ getMediaLimits() - TROVATO (visibilità: " . ($method->isPrivate() ? 'private' : 'public') . ")\n";
        }
    }
    
    // Test diretto chiamata API
    $_GET['action'] = 'get_limits';
    $_GET['azienda_id'] = '0';
    
    echo "\n🚀 Test chiamata handleRequest()...\n";
    
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ ERRORE FATALE: " . $e->getMessage() . "\n";
}
?>
