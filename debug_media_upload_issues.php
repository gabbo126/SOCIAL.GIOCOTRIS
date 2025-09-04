<?php
/**
 * 🔬 DEBUG IMMEDIATO PROBLEMI MEDIA UPLOAD
 * 1. Upload mancante in registrazione
 * 2. Errore "limiti piano non caricati" in modifica
 */

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>🔬 Debug Media Upload Issues - SOCIAL.GIOCOTRIS</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'>";
echo "</head><body>";

echo "<div class='container-fluid py-4'>";
echo "<h1 class='text-center mb-4'>🔬 <strong>Debug Media Upload Issues</strong></h1>";

// DEBUG 1: Verifica template advanced-media-section.php
echo "<div class='row mb-5'>";
echo "<div class='col-12'>";
echo "<div class='card border-danger'>";
echo "<div class='card-header bg-danger text-white'>";
echo "<h5><i class='bi bi-bug'></i> DEBUG 1: Template Advanced Media Section</h5>";
echo "</div>";
echo "<div class='card-body'>";

$template_file = 'templates/advanced-media-section.php';
if (file_exists($template_file)) {
    echo "<div class='alert alert-success'><strong>✅ Template File Exists</strong></div>";
    
    $content = file_get_contents($template_file);
    $content_size = round(strlen($content) / 1024, 1);
    
    echo "<p><strong>File Size:</strong> {$content_size} KB</p>";
    
    // Check key components
    $has_css_include = strpos($content, 'advanced-media-manager.css') !== false;
    $has_js_include = strpos($content, 'advanced-media-manager.js') !== false;
    $has_media_container = strpos($content, 'advanced-media-container') !== false;
    $has_initialization = strpos($content, 'AdvancedMediaManager') !== false;
    
    echo "<div class='row'>";
    echo "<div class='col-md-6'>";
    echo "<h6>Componenti Template:</h6>";
    echo "<ul class='list-group'>";
    echo "<li class='list-group-item d-flex justify-content-between'>";
    echo "CSS Include <span class='badge " . ($has_css_include ? "bg-success" : "bg-danger") . "'>" . ($has_css_include ? "✅" : "❌") . "</span>";
    echo "</li>";
    echo "<li class='list-group-item d-flex justify-content-between'>";
    echo "JS Include <span class='badge " . ($has_js_include ? "bg-success" : "bg-danger") . "'>" . ($has_js_include ? "✅" : "❌") . "</span>";
    echo "</li>";
    echo "<li class='list-group-item d-flex justify-content-between'>";
    echo "Media Container <span class='badge " . ($has_media_container ? "bg-success" : "bg-danger") . "'>" . ($has_media_container ? "✅" : "❌") . "</span>";
    echo "</li>";
    echo "<li class='list-group-item d-flex justify-content-between'>";
    echo "JS Initialization <span class='badge " . ($has_initialization ? "bg-success" : "bg-danger") . "'>" . ($has_initialization ? "✅" : "❌") . "</span>";
    echo "</li>";
    echo "</ul>";
    echo "</div>";
    echo "<div class='col-md-6'>";
    
    // Test include in isolation
    echo "<h6>Test Include Isolation:</h6>";
    
    try {
        // Simulate include
        $azienda_id = 0;
        $context = 'register';
        $readonly = false;
        
        ob_start();
        include $template_file;
        $output = ob_get_clean();
        
        $has_output = !empty(trim($output));
        $output_size = strlen($output);
        
        echo "<div class='alert " . ($has_output ? "alert-success" : "alert-danger") . "'>";
        echo "<strong>" . ($has_output ? "✅ Include Successful" : "❌ Include Failed") . "</strong><br>";
        echo "Output Size: {$output_size} bytes";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>";
        echo "<strong>❌ Include Error:</strong><br>";
        echo htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    
    echo "</div>";
    echo "</div>";
    
} else {
    echo "<div class='alert alert-danger'><strong>❌ Template File Missing!</strong></div>";
}

echo "</div></div></div></div>";

// DEBUG 2: Verifica file CSS/JS sistema media
echo "<div class='row mb-5'>";
echo "<div class='col-12'>";
echo "<div class='card border-warning'>";
echo "<div class='card-header bg-warning text-dark'>";
echo "<h5><i class='bi bi-file-code'></i> DEBUG 2: File Assets Sistema Media</h5>";
echo "</div>";
echo "<div class='card-body'>";

$assets = [
    'assets/css/advanced-media-manager.css' => 'CSS Media Manager',
    'assets/js/advanced-media-manager.js' => 'JS Media Manager'
];

echo "<div class='row'>";
foreach ($assets as $file => $description) {
    echo "<div class='col-md-6'>";
    echo "<div class='card mb-3'>";
    echo "<div class='card-header'><strong>{$description}</strong></div>";
    echo "<div class='card-body'>";
    
    if (file_exists($file)) {
        $size = round(filesize($file) / 1024, 1);
        echo "<div class='alert alert-success'>";
        echo "<strong>✅ File Exists</strong><br>";
        echo "Size: {$size} KB";
        echo "</div>";
        
        // Check syntax for JS
        if (strpos($file, '.js') !== false) {
            // Simple syntax check by looking for key functions
            $content = file_get_contents($file);
            $has_main_class = strpos($content, 'class AdvancedMediaManager') !== false;
            $has_init_method = strpos($content, 'initializeManager') !== false;
            
            echo "<p><strong>JS Structure:</strong></p>";
            echo "<ul>";
            echo "<li>Main Class: " . ($has_main_class ? "✅" : "❌") . "</li>";
            echo "<li>Init Method: " . ($has_init_method ? "✅" : "❌") . "</li>";
            echo "</ul>";
        }
        
    } else {
        echo "<div class='alert alert-danger'>";
        echo "<strong>❌ File Missing!</strong>";
        echo "</div>";
    }
    
    echo "</div></div></div>";
}
echo "</div>";

echo "</div></div></div></div>";

// DEBUG 3: Verifica integrazione in company-form.php
echo "<div class='row mb-5'>";
echo "<div class='col-12'>";
echo "<div class='card border-info'>";
echo "<div class='card-header bg-info text-white'>";
echo "<h5><i class='bi bi-file-text'></i> DEBUG 3: Integrazione Company Form</h5>";
echo "</div>";
echo "<div class='card-body'>";

$form_file = 'templates/company-form.php';
if (file_exists($form_file)) {
    $form_content = file_get_contents($form_file);
    
    // Check integration
    $has_include = strpos($form_content, "include __DIR__ . '/advanced-media-section.php'") !== false;
    $has_section_header = strpos($form_content, 'Media e Immagini') !== false;
    $has_config_vars = strpos($form_content, '$azienda_id = $form_mode') !== false;
    
    echo "<div class='row'>";
    echo "<div class='col-md-6'>";
    echo "<h6>Integrazione Template:</h6>";
    echo "<ul class='list-group'>";
    echo "<li class='list-group-item d-flex justify-content-between'>";
    echo "Include Statement <span class='badge " . ($has_include ? "bg-success" : "bg-danger") . "'>" . ($has_include ? "✅" : "❌") . "</span>";
    echo "</li>";
    echo "<li class='list-group-item d-flex justify-content-between'>";
    echo "Section Header <span class='badge " . ($has_section_header ? "bg-success" : "bg-danger") . "'>" . ($has_section_header ? "✅" : "❌") . "</span>";
    echo "</li>";
    echo "<li class='list-group-item d-flex justify-content-between'>";
    echo "Config Variables <span class='badge " . ($has_config_vars ? "bg-success" : "bg-danger") . "'>" . ($has_config_vars ? "✅" : "❌") . "</span>";
    echo "</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='col-md-6'>";
    echo "<h6>Simulazione Include in Form:</h6>";
    
    try {
        // Simulate form context
        $form_mode = 'register';
        $azienda = [];
        $azienda_id = 0;
        $context = 'register';
        $readonly = false;
        
        ob_start();
        
        // Simulate the section where media is included
        echo "<div class='form-section mb-5'>";
        echo "<div class='section-header mb-4'>";
        echo "<h5 class='section-title'><i class='bi bi-images'></i> Media e Immagini</h5>";
        echo "</div>";
        
        include 'templates/advanced-media-section.php';
        
        echo "</div>";
        
        $output = ob_get_clean();
        $has_form_output = !empty(trim($output));
        
        echo "<div class='alert " . ($has_form_output ? "alert-success" : "alert-danger") . "'>";
        echo "<strong>" . ($has_form_output ? "✅ Form Integration OK" : "❌ Form Integration Failed") . "</strong><br>";
        echo "Output: " . strlen($output) . " bytes";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>";
        echo "<strong>❌ Form Integration Error:</strong><br>";
        echo htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    
    echo "</div>";
    echo "</div>";
    
} else {
    echo "<div class='alert alert-danger'><strong>❌ Company Form File Missing!</strong></div>";
}

echo "</div></div></div></div>";

// DEBUG 4: Test delle variabili di contesto per limiti piano
echo "<div class='row mb-5'>";
echo "<div class='col-12'>";
echo "<div class='card border-success'>";
echo "<div class='card-header bg-success text-white'>";
echo "<h5><i class='bi bi-gear'></i> DEBUG 4: Limiti Piano e Contesto</h5>";
echo "</div>";
echo "<div class='card-body'>";

echo "<h6>Test Configurazione Limiti Piano:</h6>";

// Simulate different contexts
$test_contexts = [
    'register' => ['azienda_id' => 0, 'context' => 'register'],
    'edit' => ['azienda_id' => 123, 'context' => 'edit']
];

foreach ($test_contexts as $test_name => $config) {
    echo "<div class='card mb-3'>";
    echo "<div class='card-header'><strong>Context: {$test_name}</strong></div>";
    echo "<div class='card-body'>";
    
    $azienda_id = $config['azienda_id'];
    $context = $config['context'];
    $readonly = false;
    
    echo "<p><strong>Variables:</strong></p>";
    echo "<ul>";
    echo "<li>azienda_id: {$azienda_id}</li>";
    echo "<li>context: {$context}</li>";
    echo "<li>readonly: " . ($readonly ? 'true' : 'false') . "</li>";
    echo "</ul>";
    
    // Check if these would cause issues in JS
    $js_config = [
        'currentAziendaId' => (int)$azienda_id,
        'mediaContext' => $context,
        'mediaReadonly' => $readonly
    ];
    
    echo "<p><strong>JS Configuration:</strong></p>";
    echo "<pre class='bg-light p-2'>" . json_encode($js_config, JSON_PRETTY_PRINT) . "</pre>";
    
    echo "</div></div>";
}

echo "</div></div></div></div>";

// RIEPILOGO
echo "<div class='row'>";
echo "<div class='col-12'>";
echo "<div class='card border-primary'>";
echo "<div class='card-header bg-primary text-white text-center'>";
echo "<h4><i class='bi bi-clipboard-check'></i> 🏁 RIEPILOGO DEBUG ISSUES</h4>";
echo "</div>";
echo "<div class='card-body'>";

echo "<div class='alert alert-warning text-center'>";
echo "<h5><i class='bi bi-exclamation-triangle-fill'></i> <strong>PROBLEMI IDENTIFICATI</strong></h5>";
echo "<p>Possibili cause degli errori segnalati:</p>";
echo "<ol class='text-start'>";
echo "<li><strong>Upload mancante in registrazione:</strong> Possibile problema di inizializzazione JS o mancanza token azienda</li>";
echo "<li><strong>Limiti piano non caricati:</strong> Possibile problema di passaggio dati dal backend o inizializzazione JS</li>";
echo "</ol>";
echo "</div>";

echo "<div class='text-center mt-4'>";
echo "<a href='register_company.php' class='btn btn-success me-3' target='_blank'>";
echo "<i class='bi bi-plus-circle'></i> Test Registrazione Live";
echo "</a>";
echo "<a href='modifica_azienda_token.php?token=demo' class='btn btn-warning' target='_blank'>";
echo "<i class='bi bi-pencil-square'></i> Test Modifica Live";
echo "</a>";
echo "</div>";

echo "</div></div></div></div>";

echo "</div>";
echo "</body></html>";
?>
