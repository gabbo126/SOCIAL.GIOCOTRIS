<?php
/**
 * 🚨 FIX CRITICO: Creare Tabella piani_media_limits MANCANTE
 * Risolve l'errore "Limiti piano non caricati" nel media uploader
 */

require_once 'config.php';
require_once 'includes/db.php';

echo "<h1>🚨 FIX CRITICO: Creazione Tabella piani_media_limits</h1>";

try {
    // Step 1: Crea la tabella piani_media_limits
    $create_table_sql = "
    CREATE TABLE IF NOT EXISTS `piani_media_limits` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `piano` varchar(50) NOT NULL,
      `max_media_totali` int(11) NOT NULL DEFAULT 3,
      `max_media_galleria` int(11) NOT NULL DEFAULT 3,
      `max_file_size_mb` int(11) NOT NULL DEFAULT 5,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `piano` (`piano`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($create_table_sql) === TRUE) {
        echo "✅ <strong>Tabella piani_media_limits creata con successo!</strong><br><br>";
    } else {
        throw new Exception("Errore creazione tabella: " . $conn->error);
    }
    
    // Step 2: Popola la tabella con i limiti per Piano Base e Pro
    $insert_data_sql = "
    INSERT IGNORE INTO `piani_media_limits` (`piano`, `max_media_totali`, `max_media_galleria`, `max_file_size_mb`) VALUES
    ('foto', 3, 3, 5),        -- Piano Base (foto): max 3 media totali, 3 galleria, 5MB per file
    ('foto_video', 5, 5, 10)  -- Piano Pro (foto+video): max 5 media totali, 5 galleria, 10MB per file
    ";
    
    if ($conn->query($insert_data_sql) === TRUE) {
        echo "✅ <strong>Dati inseriti con successo!</strong><br><br>";
    } else {
        throw new Exception("Errore inserimento dati: " . $conn->error);
    }
    
    // Step 3: Verifica dati inseriti
    echo "<h2>📊 Verifica Dati Inseriti:</h2>";
    $result = $conn->query("SELECT * FROM piani_media_limits");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Piano</th><th>Max Media Totali</th><th>Max Media Galleria</th><th>Max File MB</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($row['piano']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['max_media_totali']) . "</td>";
            echo "<td>" . htmlspecialchars($row['max_media_galleria']) . "</td>";
            echo "<td>" . htmlspecialchars($row['max_file_size_mb']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Step 4: Test API dopo il fix
    echo "<h2>🧪 Test API dopo il Fix:</h2>";
    $api_url = "http://localhost/SOCIAL.GIOCOTRIS/api/media_manager.php?action=limits&azienda_id=0";
    echo "<strong>URL API:</strong> <a href='$api_url' target='_blank'>$api_url</a><br>";
    
    $api_response = @file_get_contents($api_url);
    if ($api_response) {
        echo "<strong>Risposta API:</strong><br>";
        echo "<pre style='background: #f8f8f8; padding: 10px; border: 1px solid #ccc;'>" . htmlspecialchars($api_response) . "</pre>";
        
        $json_data = json_decode($api_response, true);
        if ($json_data && isset($json_data['success']) && $json_data['success']) {
            echo "🎉 <strong style='color: green;'>SUCCESSO! API getMediaLimits() ora funziona correttamente!</strong><br>";
            echo "L'errore 'Limiti piano non caricati' dovrebbe essere risolto.";
        } else {
            echo "⚠️ <strong style='color: orange;'>API risponde ma ci potrebbero essere altri problemi nella logica.</strong>";
        }
    } else {
        echo "❌ <strong style='color: red;'>Impossibile testare l'API. Verificare manualmente.</strong>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong style='color: red;'>ERRORE:</strong> " . $e->getMessage();
}

echo "<br><br><h2>🏁 PROSSIMI PASSI</h2>";
echo "<p>1. Testare il media uploader nella pagina di registrazione azienda</p>";
echo "<p>2. Verificare che l'errore 'Limiti piano non caricati' non appaia più</p>";
echo "<p>3. Validare il corretto caricamento dei limiti per Piano Base e Pro</p>";
?>
