<?php
require_once 'config.php';
require_once 'includes/db.php';

// Debug pulsante media - verificare rendering e token
$token = $_GET['token'] ?? 'test_media_fix_1756720953';

echo "<!DOCTYPE html><html><head><title>Debug Media Button</title>";
echo "<style>body{font-family:Arial;margin:20px;} .debug{background:#f8f9fa;border:1px solid #dee2e6;padding:15px;margin:10px 0;border-radius:5px;} .success{background:#d1edff;border-color:#b8daff;} .error{background:#f8d7da;border-color:#f5c6cb;} .warning{background:#fff3cd;border-color:#ffeaa7;}</style>";
echo "</head><body>";

echo "<h2>🔍 Debug Pulsante Media - Registrazione Azienda</h2>";

// Verifica token nel database
$query = "SELECT * FROM tokens WHERE token = ? AND type = 'creazione'";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$token_data = $result->fetch_assoc();

echo "<div class='debug " . ($token_data ? "success" : "error") . "'>";
echo "<h3>1. Verifica Token Database</h3>";
if ($token_data) {
    echo "<p><strong>✅ Token trovato:</strong> {$token}</p>";
    echo "<p><strong>Tipo pacchetto:</strong> {$token_data['tipo_pacchetto']}</p>";
    echo "<p><strong>Status:</strong> {$token_data['status']}</p>";
    echo "<p><strong>Scadenza:</strong> {$token_data['data_scadenza']}</p>";
    
    $is_expired = strtotime($token_data['data_scadenza']) < time();
    echo "<p><strong>Scaduto:</strong> " . ($is_expired ? "❌ SÌ" : "✅ NO") . "</p>";
} else {
    echo "<p><strong>❌ Token non trovato nel database</strong></p>";
}
echo "</div>";

// Simula rendering template
if ($token_data && $token_data['status'] === 'attivo' && strtotime($token_data['data_scadenza']) > time()) {
    $tipo_pacchetto = $token_data['tipo_pacchetto'];
    
    echo "<div class='debug success'>";
    echo "<h3>2. Simulazione Template Rendering</h3>";
    echo "<p><strong>✅ Token valido - Procedo con rendering</strong></p>";
    echo "<p><strong>Tipo pacchetto:</strong> {$tipo_pacchetto}</p>";
    
    echo "<h4>HTML Generato per Sezione Media:</h4>";
    echo "<div style='background:#263238;color:#fff;padding:10px;border-radius:3px;font-family:monospace;white-space:pre-wrap;'>";
    
    if ($tipo_pacchetto === 'foto_video') {
        echo htmlspecialchars('
<div class="card-body">
    <p class="text-success mb-3">
        <i class="bi bi-star-fill"></i> <strong>Piano Pro:</strong> 
        Puoi caricare foto, video, link YouTube e link a immagini.
    </p>
    
    <!-- Media Manager Pro -->
    <div id="media-upload-container" class="border rounded p-3 bg-light" data-package="foto_video">
        <div class="text-center mb-3">
            <button type="button" class="btn btn-outline-success" id="add-media-btn">
                <i class="bi bi-plus-lg"></i> Aggiungi Media (max 5)
            </button>
        </div>
        <div id="media-list" class="row g-3">
            <!-- Media items generati via JavaScript -->
        </div>
    </div>
</div>');
    } else {
        echo htmlspecialchars('
<div class="card-body">
    <p class="text-info mb-3">
        <i class="bi bi-image"></i> <strong>Piano Base:</strong> 
        Puoi caricare foto e link a immagini (max 3).
    </p>
    
    <!-- Media Manager Base -->
    <div id="media-upload-container" class="border rounded p-3 bg-light" data-package="foto">
        <div class="text-center mb-3">
            <button type="button" class="btn btn-outline-info" id="add-media-btn">
                <i class="bi bi-plus-lg"></i> Aggiungi Media (max 3)
            </button>
        </div>
        <div id="media-list" class="row g-3">
            <!-- Media items generati via JavaScript -->
        </div>
    </div>
</div>');
    }
    
    echo "</div>";
    echo "</div>";
    
    // Test link diretto
    echo "<div class='debug warning'>";
    echo "<h3>3. Test Accesso Diretto</h3>";
    $test_url = "register_company.php?token=" . urlencode($token);
    echo "<p><strong>Link per test:</strong></p>";
    echo "<a href='{$test_url}' target='_blank' class='btn btn-primary' style='display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:5px;'>";
    echo "🧪 APRI REGISTRAZIONE AZIENDA";
    echo "</a>";
    echo "</div>";
    
} else {
    echo "<div class='debug error'>";
    echo "<h3>2. Problema Token</h3>";
    if (!$token_data) {
        echo "<p><strong>❌ Token non esiste</strong></p>";
    } elseif ($token_data['status'] !== 'attivo') {
        echo "<p><strong>❌ Token non attivo</strong> (status: {$token_data['status']})</p>";
    } elseif (strtotime($token_data['data_scadenza']) <= time()) {
        echo "<p><strong>❌ Token scaduto</strong></p>";
        echo "<p>Scadenza: {$token_data['data_scadenza']}</p>";
        echo "<p>Ora attuale: " . date('Y-m-d H:i:s') . "</p>";
    }
    echo "</div>";
}

// Verifica script JavaScript
echo "<div class='debug'>";
echo "<h3>4. Verifica Script JavaScript</h3>";
$media_manager_path = 'assets/js/media-manager-unified.js';
if (file_exists($media_manager_path)) {
    $file_size = filesize($media_manager_path);
    $file_modified = date('Y-m-d H:i:s', filemtime($media_manager_path));
    echo "<p><strong>✅ File JavaScript trovato</strong></p>";
    echo "<p><strong>Path:</strong> {$media_manager_path}</p>";
    echo "<p><strong>Dimensione:</strong> {$file_size} bytes</p>";
    echo "<p><strong>Ultima modifica:</strong> {$file_modified}</p>";
} else {
    echo "<p><strong>❌ File JavaScript non trovato</strong> ({$media_manager_path})</p>";
}
echo "</div>";

echo "<div class='debug'>";
echo "<h3>5. Raccomandazioni Debug</h3>";
echo "<ul>";
echo "<li><strong>Hard Refresh:</strong> Premi Ctrl+Shift+R per forzare ricaricamento cache</li>";
echo "<li><strong>Console Browser:</strong> Apri DevTools (F12) → Console per errori JavaScript</li>";
echo "<li><strong>Inspect Element:</strong> Verifica se il pulsante esiste nel DOM</li>";
echo "<li><strong>Network Tab:</strong> Verifica se il file JavaScript si carica correttamente</li>";
echo "</ul>";
echo "</div>";

$stmt->close();
$conn->close();
?>
</body></html>
