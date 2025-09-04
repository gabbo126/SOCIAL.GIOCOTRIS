<?php
require_once 'config.php';
require_once 'includes/db.php';

echo "<h1>🔍 Trova Azienda Valida per Test</h1>";

// Query per trovare prime 5 aziende esistenti
$query = "SELECT id, nome FROM aziende ORDER BY id ASC LIMIT 5";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<h2>📊 Aziende disponibili per test:</h2>";
    echo "<ul>";
    
    while ($azienda = $result->fetch_assoc()) {
        $test_url = "http://localhost/SOCIAL.GIOCOTRIS/azienda.php?id=" . $azienda['id'];
        echo "<li>";
        echo "<strong>ID {$azienda['id']}:</strong> " . htmlspecialchars($azienda['nome']);
        echo " - <a href='{$test_url}' target='_blank'>Test Pagina Dettaglio</a>";
        echo "</li>";
    }
    
    echo "</ul>";
    
    // Suggerisci ID per il test
    $first_result = $conn->query("SELECT id FROM aziende ORDER BY id ASC LIMIT 1");
    if ($first_result && $first_result->num_rows > 0) {
        $first_company = $first_result->fetch_assoc();
        $suggested_id = $first_company['id'];
        
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin-top: 20px;'>";
        echo "<h3>💡 Suggerimento per Test:</h3>";
        echo "<p>Usa <strong>ID {$suggested_id}</strong> per testare il bug delle categorie.</p>";
        echo "<p><a href='test_single_company.php?id={$suggested_id}' target='_blank' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Test Debug Categorie</a></p>";
        echo "</div>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Nessuna azienda trovata nel database</p>";
    if ($conn->error) {
        echo "<p>Errore MySQL: " . $conn->error . "</p>";
    }
}

$conn->close();
?>
