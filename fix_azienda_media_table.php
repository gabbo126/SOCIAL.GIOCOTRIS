<?php
/**
 * 🚨 FIX CRITICO: Creare Tabella azienda_media MANCANTE
 * Completa il fix del media uploader dopo la creazione di piani_media_limits
 */

require_once 'config.php';
require_once 'includes/db.php';

echo "<h1>🚨 FIX CRITICO: Creazione Tabella azienda_media</h1>";

try {
    // Step 1: Crea la tabella azienda_media
    $create_table_sql = "
    CREATE TABLE IF NOT EXISTS `azienda_media` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `azienda_id` int(11) NOT NULL,
      `tipo_media` enum('logo','galleria','video') NOT NULL DEFAULT 'galleria',
      `nome_file` varchar(255) DEFAULT NULL,
      `url_media` text DEFAULT NULL,
      `tipo_sorgente` enum('upload','url','youtube','vimeo') NOT NULL DEFAULT 'upload',
      `dimensione_kb` int(11) DEFAULT 0,
      `ordine` int(11) NOT NULL DEFAULT 0,
      `attivo` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `azienda_id` (`azienda_id`),
      KEY `tipo_media` (`tipo_media`),
      KEY `attivo` (`attivo`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($create_table_sql) === TRUE) {
        echo "✅ <strong>Tabella azienda_media creata con successo!</strong><br><br>";
    } else {
        throw new Exception("Errore creazione tabella: " . $conn->error);
    }
    
    // Step 2: Inserisci alcuni dati di test per azienda_id = 1 (se esiste)
    $test_data_sql = "
    INSERT IGNORE INTO `azienda_media` (`azienda_id`, `tipo_media`, `nome_file`, `url_media`, `tipo_sorgente`, `ordine`, `attivo`) VALUES
    (1, 'logo', 'test_logo.jpg', 'uploads/aziende/1/logo.jpg', 'upload', 0, 1),
    (1, 'galleria', 'test_image1.jpg', 'uploads/aziende/1/gallery1.jpg', 'upload', 1, 1),
    (1, 'galleria', 'test_image2.jpg', 'uploads/aziende/1/gallery2.jpg', 'upload', 2, 1)
    ";
    
    // Verifica se esiste almeno un'azienda con ID 1 prima di inserire
    $check_azienda = $conn->query("SELECT id FROM aziende WHERE id = 1 LIMIT 1");
    if ($check_azienda && $check_azienda->num_rows > 0) {
        if ($conn->query($test_data_sql) === TRUE) {
            echo "✅ <strong>Dati di test inseriti per azienda ID 1!</strong><br><br>";
        } else {
            echo "⚠️ <strong>Dati di test non inseriti:</strong> " . $conn->error . "<br><br>";
        }
    } else {
        echo "ℹ️ <strong>Nessuna azienda con ID 1 trovata. Dati di test non inseriti.</strong><br><br>";
    }
    
    // Step 3: Verifica struttura tabella creata
    echo "<h2>📊 Verifica Struttura Tabella:</h2>";
    $result = $conn->query("DESCRIBE azienda_media");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Step 4: Test API completo dopo entrambi i fix
    echo "<h2>🧪 Test API Completo dopo Tutti i Fix:</h2>";
    
    // Test per azienda_id = 0 (registrazione)
    $api_url_0 = "http://localhost/SOCIAL.GIOCOTRIS/api/media_manager.php?action=limits&azienda_id=0";
    echo "<strong>Test 1 - Registrazione (azienda_id=0):</strong><br>";
    echo "<a href='$api_url_0' target='_blank'>$api_url_0</a><br>";
    
    $api_response_0 = @file_get_contents($api_url_0);
    if ($api_response_0) {
        echo "<pre style='background: #f8f8f8; padding: 10px; border: 1px solid #ccc;'>" . htmlspecialchars($api_response_0) . "</pre>";
        
        $json_data_0 = json_decode($api_response_0, true);
        if ($json_data_0 && isset($json_data_0['success']) && $json_data_0['success']) {
            echo "🎉 <strong style='color: green;'>SUCCESSO per azienda_id=0!</strong><br><br>";
        } else {
            echo "❌ <strong style='color: red;'>Errore ancora presente per azienda_id=0</strong><br><br>";
        }
    }
    
    // Test per azienda_id = 1 (modifica)
    $api_url_1 = "http://localhost/SOCIAL.GIOCOTRIS/api/media_manager.php?action=limits&azienda_id=1";
    echo "<strong>Test 2 - Modifica (azienda_id=1):</strong><br>";
    echo "<a href='$api_url_1' target='_blank'>$api_url_1</a><br>";
    
    $api_response_1 = @file_get_contents($api_url_1);
    if ($api_response_1) {
        echo "<pre style='background: #f8f8f8; padding: 10px; border: 1px solid #ccc;'>" . htmlspecialchars($api_response_1) . "</pre>";
        
        $json_data_1 = json_decode($api_response_1, true);
        if ($json_data_1 && isset($json_data_1['success']) && $json_data_1['success']) {
            echo "🎉 <strong style='color: green;'>SUCCESSO per azienda_id=1!</strong><br>";
        } else {
            echo "❌ <strong style='color: red;'>Errore ancora presente per azienda_id=1</strong><br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ <strong style='color: red;'>ERRORE:</strong> " . $e->getMessage();
}

echo "<br><br><h2>🏁 STATO FINALE</h2>";
echo "<p><strong>Tabelle create:</strong></p>";
echo "<ul>";
echo "<li>✅ piani_media_limits (limiti per piano Base/Pro)</li>";
echo "<li>✅ azienda_media (storage media delle aziende)</li>";
echo "</ul>";
echo "<p><strong>Prossimo step:</strong> Testare il media uploader nella pagina reale per verificare che l'errore 'Limiti piano non caricati' sia scomparso!</p>";
?>
