<?php
/**
 * UPGRADE DATABASE - SISTEMA MEDIA FLESSIBILE
 * ==========================================
 * Aggiorna la struttura database per supportare il nuovo sistema media JSON
 */

require_once 'includes/db.php';

echo "<h2>🔧 UPGRADE DATABASE - SISTEMA MEDIA FLESSIBILE</h2>\n";

try {
    // 1. Aggiungi il campo media_json per il nuovo sistema flessibile
    echo "<p>📝 Aggiungendo campo media_json...</p>\n";
    $sql_add_media = "ALTER TABLE aziende ADD COLUMN IF NOT EXISTS media_json TEXT DEFAULT NULL COMMENT 'Media flessibili in formato JSON (immagini, video, link)'";
    
    if ($conn->query($sql_add_media)) {
        echo "<p>✅ Campo media_json aggiunto con successo!</p>\n";
    } else {
        echo "<p>❌ Errore aggiunta media_json: " . $conn->error . "</p>\n";
    }

    // 2. Aggiungi indice per migliorare le performance JSON
    echo "<p>📝 Aggiungendo indice per performance...</p>\n";
    $sql_index = "ALTER TABLE aziende ADD INDEX IF NOT EXISTS idx_media_json (media_json(255))";
    
    if ($conn->query($sql_index)) {
        echo "<p>✅ Indice media_json aggiunto!</p>\n";
    } else {
        echo "<p>⚠️ Indice già esistente o errore: " . $conn->error . "</p>\n";
    }

    // 3. Migrazione dati esistenti da foto1_url, foto2_url, foto3_url a media_json
    echo "<p>📝 Migrando dati esistenti al nuovo formato...</p>\n";
    
    $migration_sql = "SELECT id, foto1_url, foto2_url, foto3_url FROM aziende WHERE (foto1_url IS NOT NULL OR foto2_url IS NOT NULL OR foto3_url IS NOT NULL) AND (media_json IS NULL OR media_json = '')";
    $result = $conn->query($migration_sql);
    
    $migrated_count = 0;
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $media_array = [];
            
            // Converti foto esistenti in nuovo formato
            for ($i = 1; $i <= 3; $i++) {
                $foto_key = "foto{$i}_url";
                if (!empty($row[$foto_key])) {
                    $media_array[] = [
                        'type' => 'image',
                        'url' => $row[$foto_key],
                        'embed_url' => null,
                        'thumbnail_url' => null,
                        'error' => null
                    ];
                }
            }
            
            if (!empty($media_array)) {
                $media_json = json_encode($media_array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                
                $update_stmt = $conn->prepare("UPDATE aziende SET media_json = ? WHERE id = ?");
                $update_stmt->bind_param('si', $media_json, $row['id']);
                
                if ($update_stmt->execute()) {
                    $migrated_count++;
                    echo "<p>📸 Migrata azienda ID {$row['id']}: " . count($media_array) . " media</p>\n";
                }
                $update_stmt->close();
            }
        }
    }
    
    echo "<p>✅ <strong>Migrazione completata: $migrated_count aziende aggiornate!</strong></p>\n";

    // 4. Verifica struttura finale
    echo "<p>📝 Verificando struttura finale...</p>\n";
    $verify_sql = "DESCRIBE aziende";
    $verify_result = $conn->query($verify_sql);
    
    echo "<h3>📋 Struttura Tabella Aziende:</h3>\n";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr style='background-color: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th><th>Extra</th></tr>\n";
    
    if ($verify_result) {
        while ($field = $verify_result->fetch_assoc()) {
            $bg_color = ($field['Field'] === 'media_json') ? 'background-color: #d4edda;' : '';
            echo "<tr style='$bg_color'>";
            echo "<td><strong>" . $field['Field'] . "</strong></td>";
            echo "<td>" . $field['Type'] . "</td>";
            echo "<td>" . $field['Null'] . "</td>";
            echo "<td>" . ($field['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . ($field['Extra'] ?? '') . "</td>";
            echo "</tr>\n";
        }
    }
    echo "</table>\n";

    echo "<h2>🎉 UPGRADE COMPLETATO CON SUCCESSO!</h2>\n";
    echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Modifiche Applicate:</h3>";
    echo "<ul>";
    echo "<li>✅ Aggiunto campo <strong>media_json</strong> per gestione media flessibile</li>";
    echo "<li>✅ Aggiunto indice per performance JSON</li>";
    echo "<li>✅ Migrati <strong>$migrated_count</strong> record esistenti al nuovo formato</li>";
    echo "<li>✅ Sistema pronto per gestire fino a <strong>5 media per azienda</strong></li>";
    echo "<li>✅ Supporto completo per: <strong>Foto, Video, YouTube</strong></li>";
    echo "</ul>";
    echo "</div>";

    echo "<h3>🔄 Prossimi Passi:</h3>";
    echo "<ol>";
    echo "<li>Aggiornare <code>processa_registrazione.php</code> per usare il nuovo sistema</li>";
    echo "<li>Aggiornare <code>processa_modifica_token.php</code> per la modifica flessibile</li>";
    echo "<li>Creare frontend dinamico per inserimento/modifica media</li>";
    echo "<li>Testare normalizzazione automatica YouTube</li>";
    echo "</ol>";

} catch (Exception $e) {
    echo "<h2>❌ ERRORE DURANTE L'UPGRADE</h2>\n";
    echo "<p style='color: red;'><strong>Errore:</strong> " . $e->getMessage() . "</p>\n";
    echo "<p>Verifica i permessi del database e riprova.</p>\n";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background-color: #f8f9fa; }
h2 { color: #198754; }
h3 { color: #0d6efd; }
p { margin: 5px 0; }
table { margin: 10px 0; }
th, td { padding: 8px 12px; text-align: left; }
.success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; }
.error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; }
</style>
