<?php
require_once 'config.php';
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>DEBUG: Categorie Simple</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .azienda-box { border: 2px solid #ccc; padding: 15px; margin: 10px 0; border-radius: 8px; }
        .media-present { border-color: #28a745; background: #f8fff8; }
        .no-categories { border-color: #dc3545; background: #fff5f5; }
        .has-categories { border-color: #007bff; background: #f0f8ff; }
    </style>
</head>
<body>
    <h1>🔍 DEBUG SIMPLE: Categorie vs Media</h1>
    
    <?php
    // Query semplice per tutte le aziende con media
    $query = "SELECT id, nome, business_categories, tipo_struttura, servizi, 
                     logo_url, foto1_url, foto2_url, foto3_url, video1_url, video2_url
              FROM aziende 
              ORDER BY id DESC 
              LIMIT 10";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo "<h2>📊 Ultime 10 Aziende:</h2>";
        
        while ($azienda = $result->fetch_assoc()) {
            // Conta media
            $media_count = 0;
            $media_fields = ['logo_url', 'foto1_url', 'foto2_url', 'foto3_url', 'video1_url', 'video2_url'];
            foreach ($media_fields as $field) {
                if (!empty($azienda[$field])) {
                    $media_count++;
                }
            }
            
            // Verifica categorie
            $has_categories = false;
            $categories_display = "NESSUNA";
            
            if (!empty($azienda['business_categories'])) {
                $categories_data = json_decode($azienda['business_categories'], true);
                if (is_array($categories_data) && !empty($categories_data)) {
                    $has_categories = true;
                    $categories_display = implode(', ', $categories_data);
                }
            } else {
                // Fallback
                $fallback_cats = [];
                if (!empty($azienda['tipo_struttura'])) {
                    $fallback_cats[] = $azienda['tipo_struttura'];
                }
                if (!empty($azienda['servizi'])) {
                    $servizi_array = array_map('trim', explode(',', $azienda['servizi']));
                    $fallback_cats = array_merge($fallback_cats, $servizi_array);
                }
                if (!empty($fallback_cats)) {
                    $has_categories = true;
                    $categories_display = implode(', ', $fallback_cats) . " (FALLBACK)";
                }
            }
            
            // Determina classe CSS
            $css_class = "azienda-box";
            if ($media_count > 0) {
                $css_class .= " media-present";
            }
            if ($has_categories) {
                $css_class .= " has-categories";
            } else {
                $css_class .= " no-categories";
            }
            
            echo "<div class='{$css_class}'>";
            echo "<h3>🏢 " . htmlspecialchars($azienda['nome']) . " (ID: {$azienda['id']})</h3>";
            echo "<p><strong>📁 Media presenti:</strong> {$media_count}</p>";
            echo "<p><strong>🏷️ Categorie:</strong> {$categories_display}</p>";
            echo "<p><strong>🔍 business_categories RAW:</strong> " . htmlspecialchars($azienda['business_categories'] ?? 'NULL') . "</p>";
            echo "<p><strong>🔙 tipo_struttura:</strong> " . htmlspecialchars($azienda['tipo_struttura'] ?? 'NULL') . "</p>";
            echo "<p><strong>🔙 servizi:</strong> " . htmlspecialchars($azienda['servizi'] ?? 'NULL') . "</p>";
            
            // Problema identificato?
            if ($media_count > 0 && !$has_categories) {
                echo "<p style='color: red; font-weight: bold;'>❌ PROBLEMA: Azienda con media MA senza categorie!</p>";
            } elseif ($media_count > 0 && $has_categories) {
                echo "<p style='color: green; font-weight: bold;'>✅ OK: Azienda con media E categorie</p>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>❌ Errore query o nessun risultato</p>";
        if ($conn->error) {
            echo "<p>Errore MySQL: " . $conn->error . "</p>";
        }
    }
    
    $conn->close();
    ?>
</body>
</html>
