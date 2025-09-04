<?php
require_once 'config.php';
require_once 'includes/db.php';

echo "<h1>🔍 DEBUG: Categorie con Media</h1>";

// Trova aziende che hanno sia media che categorie
$query = "SELECT id, nome, business_categories, tipo_struttura, servizi, 
                 logo_url, foto1_url, foto2_url, foto3_url, video1_url, video2_url
          FROM aziende 
          WHERE (logo_url IS NOT NULL OR foto1_url IS NOT NULL OR foto2_url IS NOT NULL 
                 OR foto3_url IS NOT NULL OR video1_url IS NOT NULL OR video2_url IS NOT NULL)
          ORDER BY id DESC 
          LIMIT 5";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<h2>📊 Aziende con Media (ultime 5):</h2>";
    
    while ($azienda = $result->fetch_assoc()) {
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
        echo "<h3>🏢 " . htmlspecialchars($azienda['nome']) . " (ID: {$azienda['id']})</h3>";
        
        // Media presenti
        $media_count = 0;
        $media_fields = ['logo_url', 'foto1_url', 'foto2_url', 'foto3_url', 'video1_url', 'video2_url'];
        foreach ($media_fields as $field) {
            if (!empty($azienda[$field])) {
                echo "📁 <strong>{$field}:</strong> " . htmlspecialchars($azienda[$field]) . "<br>";
                $media_count++;
            }
        }
        echo "<strong>Total Media:</strong> {$media_count}<br><br>";
        
        // Categorie business_categories
        echo "<strong>🏷️ business_categories:</strong> ";
        if (!empty($azienda['business_categories'])) {
            echo "<pre>" . htmlspecialchars($azienda['business_categories']) . "</pre>";
            
            $categories_data = json_decode($azienda['business_categories'], true);
            if (is_array($categories_data)) {
                echo "<strong>Decoded JSON:</strong> " . implode(', ', $categories_data) . "<br>";
            } else {
                echo "<span style='color: red;'>❌ JSON non valido</span><br>";
            }
        } else {
            echo "<span style='color: orange;'>⚠️ Campo vuoto</span><br>";
        }
        
        // Fallback vecchi campi
        echo "<strong>🔙 tipo_struttura:</strong> " . htmlspecialchars($azienda['tipo_struttura'] ?? 'N/A') . "<br>";
        echo "<strong>🔙 servizi:</strong> " . htmlspecialchars($azienda['servizi'] ?? 'N/A') . "<br>";
        
        // Simula logica di visualizzazione
        $display_categories = [];
        if (!empty($azienda['business_categories'])) {
            $categories_data = json_decode($azienda['business_categories'], true);
            if (is_array($categories_data)) {
                $display_categories = $categories_data;
            }
        } else {
            if (!empty($azienda['tipo_struttura'])) {
                $display_categories[] = trim($azienda['tipo_struttura']);
            }
            if (!empty($azienda['servizi'])) {
                $servizi_array = array_map('trim', explode(',', $azienda['servizi']));
                $display_categories = array_merge($display_categories, $servizi_array);
            }
        }
        $display_categories = array_unique(array_filter($display_categories));
        
        echo "<strong>🎯 Categorie da visualizzare:</strong> ";
        if (!empty($display_categories)) {
            echo "<span style='color: green; font-weight: bold;'>" . implode(', ', $display_categories) . "</span>";
        } else {
            echo "<span style='color: red; font-weight: bold;'>❌ NESSUNA CATEGORIA!</span>";
        }
        
        echo "</div>";
    }
} else {
    echo "<p>❌ Nessuna azienda con media trovata</p>";
}

$conn->close();
?>
