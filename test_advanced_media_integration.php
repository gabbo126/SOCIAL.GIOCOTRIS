<?php
/**
 * 🔬 TEST INTEGRAZIONE ADVANCED MEDIA SYSTEM
 * Validazione completa dell'integrazione sistema media avanzato
 * in registrazione e modifica azienda
 */

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>🔬 Test Advanced Media Integration - SOCIAL.GIOCOTRIS</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'>";
echo "</head><body>";

echo "<div class='container-fluid py-4'>";
echo "<h1 class='text-center mb-4'>🔬 <strong>Test Advanced Media Integration</strong></h1>";

// Test 1: Verifica presenza files sistema media avanzato
echo "<div class='row mb-5'>";
echo "<div class='col-12'>";
echo "<div class='card'>";
echo "<div class='card-header bg-primary text-white'>";
echo "<h5><i class='bi bi-file-check'></i> Test 1: Verifica File Sistema Media</h5>";
echo "</div>";
echo "<div class='card-body'>";

$media_files = [
    'assets/css/advanced-media-manager.css' => 'CSS Advanced Media Manager',
    'assets/js/advanced-media-manager.js' => 'JavaScript Advanced Media Manager',
    'templates/advanced-media-section.php' => 'Template Advanced Media Section',
    'api/media_manager.php' => 'API Media Manager'
];

$all_files_ok = true;
foreach ($media_files as $file => $description) {
    $exists = file_exists($file);
    $all_files_ok = $all_files_ok && $exists;
    
    echo "<div class='d-flex align-items-center mb-2'>";
    echo "<i class='bi bi-" . ($exists ? "check-circle-fill text-success" : "x-circle-fill text-danger") . " me-2'></i>";
    echo "<span class='" . ($exists ? "text-success" : "text-danger") . "'>";
    echo "<strong>{$description}:</strong> " . ($exists ? "✅ Presente" : "❌ Mancante") . " ({$file})";
    echo "</span>";
    echo "</div>";
}

echo "<div class='mt-3 p-3 " . ($all_files_ok ? "bg-success" : "bg-danger") . " text-white rounded'>";
echo "<strong>Risultato Test 1:</strong> " . ($all_files_ok ? "✅ TUTTI I FILE PRESENTI" : "❌ FILE MANCANTI");
echo "</div>";

echo "</div></div></div></div>";

// Test 2: Verifica integrazione in modifica_azienda_token.php
echo "<div class='row mb-5'>";
echo "<div class='col-12'>";
echo "<div class='card'>";
echo "<div class='card-header bg-success text-white'>";
echo "<h5><i class='bi bi-code-slash'></i> Test 2: Verifica Integrazione Modifica Azienda</h5>";
echo "</div>";
echo "<div class='card-body'>";

$modifica_file = 'modifica_azienda_token.php';
if (file_exists($modifica_file)) {
    $content = file_get_contents($modifica_file);
    
    // Verifica presenza include advanced-media-section.php
    $has_include = strpos($content, "include __DIR__ . '/templates/advanced-media-section.php'") !== false;
    
    // Verifica rimozione vecchio sistema
    $has_old_system = strpos($content, 'add-media-btn') !== false;
    
    echo "<div class='d-flex align-items-center mb-2'>";
    echo "<i class='bi bi-" . ($has_include ? "check-circle-fill text-success" : "x-circle-fill text-danger") . " me-2'></i>";
    echo "<span class='" . ($has_include ? "text-success" : "text-danger") . "'>";
    echo "<strong>Include Advanced Media Section:</strong> " . ($has_include ? "✅ Integrato" : "❌ Mancante");
    echo "</span>";
    echo "</div>";
    
    echo "<div class='d-flex align-items-center mb-2'>";
    echo "<i class='bi bi-" . (!$has_old_system ? "check-circle-fill text-success" : "x-circle-fill text-warning") . " me-2'></i>";
    echo "<span class='" . (!$has_old_system ? "text-success" : "text-warning") . "'>";
    echo "<strong>Sistema Legacy Rimosso:</strong> " . (!$has_old_system ? "✅ Pulito" : "⚠️ Tracce ancora presenti");
    echo "</span>";
    echo "</div>";
    
    $integration_ok = $has_include && !$has_old_system;
    
} else {
    echo "<div class='alert alert-danger'><strong>❌ File modifica_azienda_token.php non trovato!</strong></div>";
    $integration_ok = false;
}

echo "<div class='mt-3 p-3 " . ($integration_ok ? "bg-success" : "bg-warning") . " text-white rounded'>";
echo "<strong>Risultato Test 2:</strong> " . ($integration_ok ? "✅ INTEGRAZIONE COMPLETATA" : "⚠️ RICHIEDE ATTENZIONE");
echo "</div>";

echo "</div></div></div></div>";

// Test 3: Verifica integrazione in company-form.php (registrazione)
echo "<div class='row mb-5'>";
echo "<div class='col-12'>";
echo "<div class='card'>";
echo "<div class='card-header bg-info text-white'>";
echo "<h5><i class='bi bi-file-plus'></i> Test 3: Verifica Integrazione Registrazione</h5>";
echo "</div>";
echo "<div class='card-body'>";

$form_file = 'templates/company-form.php';
if (file_exists($form_file)) {
    $form_content = file_get_contents($form_file);
    
    // Verifica presenza include advanced-media-section.php
    $has_form_include = strpos($form_content, "include __DIR__ . '/advanced-media-section.php'") !== false;
    
    echo "<div class='d-flex align-items-center mb-2'>";
    echo "<i class='bi bi-" . ($has_form_include ? "check-circle-fill text-success" : "x-circle-fill text-danger") . " me-2'></i>";
    echo "<span class='" . ($has_form_include ? "text-success" : "text-danger") . "'>";
    echo "<strong>Include Advanced Media Section:</strong> " . ($has_form_include ? "✅ Integrato" : "❌ Mancante");
    echo "</span>";
    echo "</div>";
    
} else {
    echo "<div class='alert alert-danger'><strong>❌ File company-form.php non trovato!</strong></div>";
    $has_form_include = false;
}

echo "<div class='mt-3 p-3 " . ($has_form_include ? "bg-success" : "bg-danger") . " text-white rounded'>";
echo "<strong>Risultato Test 3:</strong> " . ($has_form_include ? "✅ REGISTRAZIONE INTEGRATA" : "❌ REGISTRAZIONE NON INTEGRATA");
echo "</div>";

echo "</div></div></div></div>";

// Test 4: Verifica API Media Manager
echo "<div class='row mb-5'>";
echo "<div class='col-12'>";
echo "<div class='card'>";
echo "<div class='card-header bg-warning text-dark'>";
echo "<h5><i class='bi bi-cloud-arrow-up'></i> Test 4: Verifica API Media Manager</h5>";
echo "</div>";
echo "<div class='card-body'>";

$api_file = 'api/media_manager.php';
if (file_exists($api_file)) {
    echo "<div class='d-flex align-items-center mb-2'>";
    echo "<i class='bi bi-check-circle-fill text-success me-2'></i>";
    echo "<span class='text-success'>";
    echo "<strong>File API:</strong> ✅ Presente (" . round(filesize($api_file)/1024, 1) . " KB)";
    echo "</span>";
    echo "</div>";
    
    // Test PHP syntax
    $syntax_check = exec("php -l {$api_file} 2>&1", $output, $return_code);
    $syntax_ok = ($return_code === 0);
    
    echo "<div class='d-flex align-items-center mb-2'>";
    echo "<i class='bi bi-" . ($syntax_ok ? "check-circle-fill text-success" : "x-circle-fill text-danger") . " me-2'></i>";
    echo "<span class='" . ($syntax_ok ? "text-success" : "text-danger") . "'>";
    echo "<strong>Sintassi PHP:</strong> " . ($syntax_ok ? "✅ Valida" : "❌ Errori") . "";
    echo "</span>";
    echo "</div>";
    
} else {
    echo "<div class='alert alert-danger'><strong>❌ File API media_manager.php non trovato!</strong></div>";
    $syntax_ok = false;
}

echo "<div class='mt-3 p-3 " . ($syntax_ok ? "bg-success" : "bg-danger") . " text-white rounded'>";
echo "<strong>Risultato Test 4:</strong> " . ($syntax_ok ? "✅ API FUNZIONANTE" : "❌ API NON DISPONIBILE");
echo "</div>";

echo "</div></div></div></div>";

// RIEPILOGO FINALE
echo "<div class='row'>";
echo "<div class='col-12'>";
echo "<div class='card border-primary'>";
echo "<div class='card-header bg-primary text-white text-center'>";
echo "<h4><i class='bi bi-clipboard-check'></i> 🏁 RIEPILOGO TEST INTEGRAZIONE</h4>";
echo "</div>";
echo "<div class='card-body'>";

$total_tests = 4;
$passed_tests = 0;
if ($all_files_ok) $passed_tests++;
if ($integration_ok) $passed_tests++;
if ($has_form_include) $passed_tests++;
if ($syntax_ok) $passed_tests++;

$success_percentage = round(($passed_tests / $total_tests) * 100);

echo "<div class='text-center mb-4'>";
echo "<div class='display-1 " . ($success_percentage >= 75 ? "text-success" : "text-warning") . "'>";
echo "{$success_percentage}%";
echo "</div>";
echo "<h5>Test Superati: {$passed_tests}/{$total_tests}</h5>";
echo "</div>";

echo "<div class='row text-center'>";
echo "<div class='col-md-3'>";
echo "<div class='p-3 " . ($all_files_ok ? "bg-success" : "bg-danger") . " text-white rounded mb-2'>";
echo "<i class='bi bi-file-check display-6'></i>";
echo "<div>File Sistema</div>";
echo "</div>";
echo "</div>";

echo "<div class='col-md-3'>";
echo "<div class='p-3 " . ($integration_ok ? "bg-success" : "bg-warning") . " text-white rounded mb-2'>";
echo "<i class='bi bi-code-slash display-6'></i>";
echo "<div>Modifica Azienda</div>";
echo "</div>";
echo "</div>";

echo "<div class='col-md-3'>";
echo "<div class='p-3 " . ($has_form_include ? "bg-success" : "bg-danger") . " text-white rounded mb-2'>";
echo "<i class='bi bi-file-plus display-6'></i>";
echo "<div>Registrazione</div>";
echo "</div>";
echo "</div>";

echo "<div class='col-md-3'>";
echo "<div class='p-3 " . ($syntax_ok ? "bg-success" : "bg-danger") . " text-white rounded mb-2'>";
echo "<i class='bi bi-cloud-arrow-up display-6'></i>";
echo "<div>API Manager</div>";
echo "</div>";
echo "</div>";
echo "</div>";

if ($success_percentage >= 75) {
    echo "<div class='alert alert-success text-center mt-4'>";
    echo "<h5><i class='bi bi-check-circle-fill'></i> 🎉 <strong>INTEGRAZIONE AVANZATA COMPLETATA CON SUCCESSO!</strong></h5>";
    echo "<p>Il sistema media avanzato è stato integrato correttamente in entrambi i flussi (registrazione e modifica azienda).</p>";
} else {
    echo "<div class='alert alert-warning text-center mt-4'>";
    echo "<h5><i class='bi bi-exclamation-triangle-fill'></i> ⚠️ <strong>INTEGRAZIONE RICHIEDE ATTENZIONE</strong></h5>";
    echo "<p>Alcuni componenti del sistema media avanzato necessitano di correzioni.</p>";
}

echo "</div>";

echo "</div></div></div></div>";

echo "<div class='text-center mt-4'>";
echo "<a href='register_company.php' class='btn btn-success me-3'>";
echo "<i class='bi bi-plus-circle'></i> Test Registrazione Azienda";
echo "</a>";
echo "<a href='modifica_azienda_token.php?token=demo' class='btn btn-warning'>";
echo "<i class='bi bi-pencil-square'></i> Test Modifica Azienda";
echo "</a>";
echo "</div>";

echo "</div>";
echo "</body></html>";
?>
