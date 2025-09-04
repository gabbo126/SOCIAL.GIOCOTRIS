<?php
require_once 'config.php';
require_once 'includes/db.php';

// Debug source code per verificare HTML effettivo generato
$token = $_GET['token'] ?? 'test_media_fix_1756720953';

// Verifica token
$query = "SELECT * FROM tokens WHERE token = ? AND type = 'creazione'";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$token_data = $result->fetch_assoc();

if (!$token_data || $token_data['status'] !== 'attivo' || strtotime($token_data['data_scadenza']) <= time()) {
    die("Token non valido o scaduto");
}

$tipo_pacchetto = $token_data['tipo_pacchetto'];

echo "<!DOCTYPE html><html><head>";
echo "<title>Source Code Debug</title>";
echo "<style>body{font-family:Arial;margin:20px;background:#f8f9fa;} .source{background:#263238;color:#fff;padding:20px;border-radius:8px;white-space:pre-wrap;font-family:monospace;font-size:12px;overflow-x:auto;} .info{background:#d1edff;border:1px solid #b8daff;padding:15px;margin:10px 0;border-radius:5px;}</style>";
echo "</head><body>";

echo "<h1>🔍 Source Code Debug - Media Section</h1>";

echo "<div class='info'>";
echo "<h3>Token Info</h3>";
echo "<p><strong>Token:</strong> $token</p>";
echo "<p><strong>Tipo Pacchetto:</strong> $tipo_pacchetto</p>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>HTML Generato per Sezione Media</h3>";
echo "<p>Questo è l'HTML che dovrebbe essere generato dal template:</p>";
echo "</div>";

echo "<div class='source'>";

// Genera l'HTML esatto che dovrebbe essere prodotto
if ($tipo_pacchetto === 'foto_video') {
    echo htmlspecialchars('
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="bi bi-images"></i> Media e Immagini
            <span class="badge bg-success ms-2">🌟 Piano Pro: Fino a 5 media</span>
        </h6>
    </div>
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
    </div>
</div>');
} else {
    echo htmlspecialchars('
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="bi bi-images"></i> Media e Immagini
            <span class="badge bg-info ms-2">🏷️ Piano Base: Fino a 3 media</span>
        </h6>
    </div>
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
    </div>
</div>');
}

echo "</div>";

echo "<div class='info'>";
echo "<h3>Verifica Template File</h3>";
$template_path = 'templates/company-form.php';
if (file_exists($template_path)) {
    $template_size = filesize($template_path);
    $template_modified = date('Y-m-d H:i:s', filemtime($template_path));
    echo "<p><strong>✅ Template trovato:</strong> $template_path</p>";
    echo "<p><strong>Dimensione:</strong> $template_size bytes</p>";
    echo "<p><strong>Ultima modifica:</strong> $template_modified</p>";
} else {
    echo "<p><strong>❌ Template non trovato:</strong> $template_path</p>";
}
echo "</div>";

echo "<div class='info'>";
echo "<h3>Test Caricamento Template</h3>";
echo "<p>Ora caricherò il template reale e verificherò se produce l'HTML corretto:</p>";
echo "</div>";

// Carica il template reale e verifica output
ob_start();
$form_mode = 'create'; // Simula modalità create
try {
    include 'templates/company-form.php';
    $template_output = ob_get_contents();
} catch (Exception $e) {
    $template_output = "ERRORE: " . $e->getMessage();
}
ob_end_clean();

// Cerca la sezione media nell'output
$media_section_start = strpos($template_output, 'id="media-upload-container"');
if ($media_section_start !== false) {
    echo "<div class='info' style='background:#d4edda;border-color:#c3e6cb;'>";
    echo "<h3>✅ Sezione Media Trovata nel Template</h3>";
    echo "<p>Il container media è presente nell'output del template alla posizione: $media_section_start</p>";
    
    // Estrai la sezione media
    $media_section_end = strpos($template_output, '</div>', $media_section_start + 200);
    if ($media_section_end !== false) {
        $media_section = substr($template_output, max(0, $media_section_start - 200), $media_section_end - $media_section_start + 300);
        echo "<div class='source'>";
        echo htmlspecialchars($media_section);
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<div class='info' style='background:#f8d7da;border-color:#f5c6cb;'>";
    echo "<h3>❌ Sezione Media NON Trovata</h3>";
    echo "<p>Il container media non è presente nell'output del template!</p>";
    echo "<p>Possibili cause:</p>";
    echo "<ul>";
    echo "<li>Errore PHP nel template</li>";
    echo "<li>Condizione IF non soddisfatta</li>";
    echo "<li>Include template non funziona</li>";
    echo "</ul>";
    echo "</div>";
}

$stmt->close();
$conn->close();
?>
</body></html>
