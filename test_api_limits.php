<?php
/**
 * 🧪 TEST API LIMITS - Debug Errore "Limiti Piano Non Caricati"
 */

echo "<h1>🧪 TEST API MEDIA LIMITS</h1>";

// Test 1: Chiamata diretta API per azienda_id = 0 (registrazione)
echo "<h2>TEST 1: azienda_id = 0 (registrazione)</h2>";
$response1 = file_get_contents('http://localhost/SOCIAL.GIOCOTRIS/api/media_manager.php?action=limits&azienda_id=0');
echo "<strong>Response:</strong><br><pre>" . htmlspecialchars($response1) . "</pre>";

// Test 2: Chiamata diretta API per azienda_id = 1 (modifica)
echo "<h2>TEST 2: azienda_id = 1 (azienda esistente)</h2>";
$response2 = file_get_contents('http://localhost/SOCIAL.GIOCOTRIS/api/media_manager.php?action=limits&azienda_id=1');
echo "<strong>Response:</strong><br><pre>" . htmlspecialchars($response2) . "</pre>";

// Test 3: Verifica tabella piani_media_limits
echo "<h2>TEST 3: Verifica Tabella piani_media_limits</h2>";
require_once 'config.php';
require_once 'includes/db.php';

$result = $conn->query("SELECT * FROM piani_media_limits");
if ($result && $result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Piano</th><th>Max Media Totali</th><th>Max Media Galleria</th><th>Max File MB</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['piano']) . "</td>";
        echo "<td>" . htmlspecialchars($row['max_media_totali']) . "</td>";
        echo "<td>" . htmlspecialchars($row['max_media_galleria']) . "</td>";
        echo "<td>" . htmlspecialchars($row['max_file_size_mb']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<strong style='color: red;'>⚠️ TABELLA piani_media_limits VUOTA O INESISTENTE!</strong>";
}

// Test 4: Simulazione logica getMediaLimitsForCompany per azienda_id = 0
echo "<h2>TEST 4: Simulazione Query per azienda_id = 0</h2>";
$stmt = $conn->prepare("
    SELECT a.piano, p.max_media_totali, p.max_media_galleria, p.max_file_size_mb,
           COUNT(m.id) as current_total,
           SUM(CASE WHEN m.tipo_media = 'galleria' THEN 1 ELSE 0 END) as current_galleria
    FROM aziende a
    LEFT JOIN piani_media_limits p ON a.piano = p.piano
    LEFT JOIN azienda_media m ON a.id = m.azienda_id AND m.attivo = 1
    WHERE a.id = ?
    GROUP BY a.id
");

$azienda_id = 0;
$stmt->bind_param('i', $azienda_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo "<strong>Risultato Query:</strong><br>";
    echo "<pre>" . print_r($row, true) . "</pre>";
} else {
    echo "<strong style='color: red;'>⚠️ NESSUN RISULTATO per azienda_id = 0!</strong>";
}

echo "<h2>🎯 CONCLUSIONI DEBUG</h2>";
echo "<p>Verificare i risultati sopra per identificare la root cause dell'errore 'Limiti piano non caricati'.</p>";
?>
