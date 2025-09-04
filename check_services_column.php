<?php
/**
 * VERIFICA COLONNA SERVICES_OFFERED
 * Script diagnostico per verificare se la colonna services_offered esiste nella tabella aziende
 */

require_once 'config.php';
require_once 'includes/db.php';

echo "<h2>Verifica Colonna services_offered</h2>";

try {
    // Verifica struttura tabella aziende
    $stmt = $conn->query("DESCRIBE aziende");
    $columns = $stmt->fetch_all(MYSQLI_ASSOC);
    
    echo "<h3>Struttura Tabella aziende:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $services_offered_exists = false;
    foreach ($columns as $column) {
        echo "<tr>";
        foreach ($column as $key => $value) {
            if ($column['Field'] === 'services_offered' && $key === 'Field') {
                echo "<td style='background-color: #d4edda; font-weight: bold;'>{$value}</td>";
                $services_offered_exists = true;
            } else {
                echo "<td>{$value}</td>";
            }
        }
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Status Colonna services_offered:</h3>";
    if ($services_offered_exists) {
        echo "<div style='color: green; font-weight: bold;'>✅ Colonna services_offered ESISTE</div>";
    } else {
        echo "<div style='color: red; font-weight: bold;'>❌ Colonna services_offered NON ESISTE</div>";
        echo "<p>È necessario eseguire la seguente query SQL:</p>";
        echo "<pre style='background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff;'>";
        echo "ALTER TABLE aziende ADD COLUMN services_offered JSON AFTER servizi;";
        echo "</pre>";
    }
    
    // Test connessione
    echo "<h3>Test Connessione Database:</h3>";
    echo "<div style='color: green;'>✅ Connessione OK</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>❌ ERRORE: " . $e->getMessage() . "</div>";
}

$conn->close();
?>
