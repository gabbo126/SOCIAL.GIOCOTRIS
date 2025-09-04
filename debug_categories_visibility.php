<?php
require_once 'config.php';
require_once 'includes/db.php';

echo "<h2>DEBUG CATEGORIE VISIBILITÀ</h2>";

// Test su tutte le aziende per vedere i dati business_categories
$stmt = $conn->prepare("SELECT id, nome, business_categories, tipo_struttura, servizi FROM aziende LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
    echo "<h3>Azienda ID: " . $row['id'] . " - " . htmlspecialchars($row['nome']) . "</h3>";
    echo "<p><strong>business_categories (JSON):</strong> " . htmlspecialchars($row['business_categories'] ?? 'NULL') . "</p>";
    echo "<p><strong>tipo_struttura (legacy):</strong> " . htmlspecialchars($row['tipo_struttura'] ?? 'NULL') . "</p>";
    echo "<p><strong>servizi (legacy):</strong> " . htmlspecialchars($row['servizi'] ?? 'NULL') . "</p>";
    
    // Test parsing JSON
    if (!empty($row['business_categories'])) {
        $categories = json_decode($row['business_categories'], true);
        echo "<p><strong>Categorie decodificate:</strong> ";
        if (is_array($categories)) {
            echo htmlspecialchars(implode(', ', $categories));
        } else {
            echo "ERRORE DECODIFICA JSON";
        }
        echo "</p>";
    }
    echo "</div>";
}

$stmt->close();
$conn->close();
?>
