<?php
require_once 'config.php';
require_once 'includes/db.php';

// Test per una singola azienda che dovrebbe mostrare il problema
$company_id = isset($_GET['id']) ? (int)$_GET['id'] : 4; // Default ID 4 (azienda valida)

$query = "SELECT * FROM aziende WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $company_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $azienda = $result->fetch_assoc();
    
    echo "<!DOCTYPE html><html><head><title>Test Singola Azienda</title>";
    echo "<style>body{font-family:Arial;margin:20px;}.section{border:1px solid #ddd;padding:10px;margin:10px 0;}</style>";
    echo "</head><body>";
    
    echo "<h1>🔍 TEST AZIENDA: " . htmlspecialchars($azienda['nome']) . "</h1>";
    
    // Simula esattamente la logica di azienda.php
    echo "<div class='section'>";
    echo "<h2>📊 Dati Raw Database:</h2>";
    echo "<p><strong>business_categories:</strong> " . htmlspecialchars($azienda['business_categories'] ?? 'NULL') . "</p>";
    echo "<p><strong>tipo_struttura:</strong> " . htmlspecialchars($azienda['tipo_struttura'] ?? 'NULL') . "</p>";
    echo "<p><strong>servizi:</strong> " . htmlspecialchars($azienda['servizi'] ?? 'NULL') . "</p>";
    echo "</div>";
    
    // Media check
    echo "<div class='section'>";
    echo "<h2>📁 Media presenti:</h2>";
    $media_fields = ['logo_url', 'foto1_url', 'foto2_url', 'foto3_url', 'video1_url', 'video2_url'];
    $media_count = 0;
    foreach ($media_fields as $field) {
        if (!empty($azienda[$field])) {
            echo "<p>{$field}: " . htmlspecialchars($azienda[$field]) . "</p>";
            $media_count++;
        }
    }
    echo "<p><strong>Totale media: {$media_count}</strong></p>";
    echo "</div>";
    
    // Simula ESATTAMENTE la logica di visualizzazione categorie da azienda.php
    echo "<div class='section'>";
    echo "<h2>🏷️ Test Logica Categorie (da azienda.php):</h2>";
    
    // COPIA ESATTA del codice da azienda.php linee 327-346
    $display_categories = [];
    if (!empty($azienda['business_categories'])) {
        // Se esiste il nuovo campo JSON, usalo
        $categories_data = json_decode($azienda['business_categories'], true);
        if (is_array($categories_data)) {
            $display_categories = $categories_data;
        }
    } else {
        // Fallback: combina tipo_struttura e servizi esistenti per compatibilità
        if (!empty($azienda['tipo_struttura'])) {
            $display_categories[] = trim($azienda['tipo_struttura']);
        }
        if (!empty($azienda['servizi'])) {
            $servizi_array = array_map('trim', explode(',', $azienda['servizi']));
            $display_categories = array_merge($display_categories, $servizi_array);
        }
    }
    $display_categories = array_unique(array_filter($display_categories));
    
    echo "<p><strong>display_categories array:</strong> " . print_r($display_categories, true) . "</p>";
    echo "<p><strong>empty(display_categories):</strong> " . (empty($display_categories) ? 'TRUE' : 'FALSE') . "</p>";
    
    // Test condizione visualizzazione
    if (!empty($display_categories)) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px;'>";
        echo "<h3>✅ CATEGORIE DOVREBBERO ESSERE VISIBILI:</h3>";
        foreach ($display_categories as $category) {
            echo "<span style='background: #28a745; color: white; padding: 4px 8px; border-radius: 12px; margin: 2px; display: inline-block;'>";
            echo htmlspecialchars($category);
            echo "</span>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px;'>";
        echo "<h3>❌ NESSUNA CATEGORIA DA VISUALIZZARE</h3>";
        echo "<p>Motivo: array display_categories è vuoto</p>";
        echo "</div>";
    }
    
    echo "</div>";
    echo "</body></html>";
    
} else {
    echo "Azienda con ID {$company_id} non trovata. Verifica l'ID nel database.";
}

$stmt->close();
$conn->close();
?>
