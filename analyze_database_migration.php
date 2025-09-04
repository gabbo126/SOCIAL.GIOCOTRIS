<?php
require_once 'config.php';
require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head>";
echo "<title>Analisi Database - Migrazione Business Categories</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
    .container { max-width: 1200px; margin: 0 auto; }
    .card { background: white; border: 1px solid #dee2e6; border-radius: 8px; margin: 20px 0; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { border-left: 5px solid #28a745; background: #d4edda; }
    .warning { border-left: 5px solid #ffc107; background: #fff3cd; }
    .danger { border-left: 5px solid #dc3545; background: #f8d7da; }
    .info { border-left: 5px solid #17a2b8; background: #d1ecf1; }
    .sql-code { background: #263238; color: #fff; padding: 15px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; overflow-x: auto; }
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    th, td { padding: 8px 12px; border: 1px solid #dee2e6; text-align: left; }
    th { background: #e9ecef; font-weight: 600; }
    .badge { display: inline-block; padding: 4px 8px; font-size: 12px; border-radius: 12px; }
    .badge-success { background: #28a745; color: white; }
    .badge-warning { background: #ffc107; color: #212529; }
    .badge-danger { background: #dc3545; color: white; }
</style>";
echo "</head><body><div class='container'>";

echo "<h1>🔍 Analisi Database - Migrazione Business Categories</h1>";

// 1. VERIFICA STRUTTURA TABELLA AZIENDE
echo "<div class='card info'>";
echo "<h2>1. Struttura Tabella Aziende</h2>";
$result = $conn->query("DESCRIBE aziende");
$columns = [];
echo "<div class='table-responsive'>";
echo "<table><thead><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Status</th></tr></thead><tbody>";

while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
    $status = '';
    $badge_class = '';
    
    if ($row['Field'] === 'tipo_struttura') {
        $status = 'DA ELIMINARE';
        $badge_class = 'badge-danger';
    } elseif ($row['Field'] === 'business_categories') {
        $status = 'SISTEMA NUOVO';
        $badge_class = 'badge-success';
    } else {
        $status = 'OK';
        $badge_class = 'badge-success';
    }
    
    echo "<tr>";
    echo "<td><strong>{$row['Field']}</strong></td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "<td><span class='badge {$badge_class}'>{$status}</span></td>";
    echo "</tr>";
}

echo "</tbody></table></div>";
echo "</div>";

// 2. ANALISI DATI TIPO_STRUTTURA VS BUSINESS_CATEGORIES
echo "<div class='card warning'>";
echo "<h2>2. Confronto Dati Sistema Vecchio vs Nuovo</h2>";

$query = "SELECT 
    COUNT(*) as total_aziende,
    COUNT(CASE WHEN tipo_struttura IS NOT NULL AND tipo_struttura != '' THEN 1 END) as con_tipo_struttura,
    COUNT(CASE WHEN business_categories IS NOT NULL AND business_categories != '' AND business_categories != '[]' THEN 1 END) as con_business_categories,
    COUNT(CASE WHEN (tipo_struttura IS NULL OR tipo_struttura = '') AND (business_categories IS NULL OR business_categories = '' OR business_categories = '[]') THEN 1 END) as senza_categorie
FROM aziende";

$result = $conn->query($query);
$stats = $result->fetch_assoc();

echo "<div class='table-responsive'>";
echo "<table>";
echo "<tr><th>Metrica</th><th>Valore</th><th>Percentuale</th></tr>";
echo "<tr><td>Totale Aziende</td><td><strong>{$stats['total_aziende']}</strong></td><td>100%</td></tr>";
echo "<tr><td>Con tipo_struttura (vecchio)</td><td>{$stats['con_tipo_struttura']}</td><td>" . round(($stats['con_tipo_struttura'] / $stats['total_aziende']) * 100, 1) . "%</td></tr>";
echo "<tr><td>Con business_categories (nuovo)</td><td>{$stats['con_business_categories']}</td><td>" . round(($stats['con_business_categories'] / $stats['total_aziende']) * 100, 1) . "%</td></tr>";
echo "<tr><td>Senza categorie</td><td>{$stats['senza_categorie']}</td><td>" . round(($stats['senza_categorie'] / $stats['total_aziende']) * 100, 1) . "%</td></tr>";
echo "</table></div>";
echo "</div>";

// 3. RICERCA RIFERIMENTI NEI FILE PHP
echo "<div class='card danger'>";
echo "<h2>3. Ricerca Riferimenti 'tipo_struttura' nel Codice</h2>";
echo "<p>Identificazione file che utilizzano ancora il campo deprecato:</p>";

$files_to_check = [
    'azienda.php',
    'aziende.php', 
    'register_company.php',
    'modifica_azienda_token.php',
    'processa_registrazione.php',
    'processa_modifica_token.php',
    'templates/company-form.php',
    'includes/functions.php'
];

echo "<div class='table-responsive'>";
echo "<table><thead><tr><th>File</th><th>Riferimenti trovati</th><th>Status</th></tr></thead><tbody>";

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $count = substr_count(strtolower($content), 'tipo_struttura');
        
        $status = $count > 0 ? 'DA AGGIORNARE' : 'OK';
        $badge_class = $count > 0 ? 'badge-warning' : 'badge-success';
        
        echo "<tr>";
        echo "<td><strong>{$file}</strong></td>";
        echo "<td>{$count}</td>";
        echo "<td><span class='badge {$badge_class}'>{$status}</span></td>";
        echo "</tr>";
    } else {
        echo "<tr>";
        echo "<td><strong>{$file}</strong></td>";
        echo "<td>-</td>";
        echo "<td><span class='badge badge-danger'>FILE NON TROVATO</span></td>";
        echo "</tr>";
    }
}

echo "</tbody></table></div>";
echo "</div>";

// 4. CAMPIONI DATI PER VERIFICA
echo "<div class='card info'>";
echo "<h2>4. Campioni Dati per Verifica UI/UX</h2>";

$query = "SELECT id, nome, tipo_struttura, business_categories 
          FROM aziende 
          WHERE business_categories IS NOT NULL AND business_categories != '' AND business_categories != '[]'
          ORDER BY id DESC 
          LIMIT 10";

$result = $conn->query($query);

echo "<div class='table-responsive'>";
echo "<table><thead><tr><th>ID</th><th>Nome Azienda</th><th>Tipo Struttura (OLD)</th><th>Business Categories (NEW)</th></tr></thead><tbody>";

while ($row = $result->fetch_assoc()) {
    $categories = json_decode($row['business_categories'], true);
    $categories_display = is_array($categories) ? implode(', ', array_slice($categories, 0, 3)) : 'N/A';
    if (is_array($categories) && count($categories) > 3) {
        $categories_display .= ' +' . (count($categories) - 3);
    }
    
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td><strong>{$row['nome']}</strong></td>";
    echo "<td>{$row['tipo_struttura']}</td>";
    echo "<td>{$categories_display}</td>";
    echo "</tr>";
}

echo "</tbody></table></div>";
echo "</div>";

// 5. PIANO DI MIGRAZIONE
echo "<div class='card success'>";
echo "<h2>5. Piano di Migrazione Raccomandato</h2>";

echo "<h3>🗄️ FASE 1: Database</h3>";
echo "<div class='sql-code'>";
echo "-- BACKUP prima della migrazione\nCREATE TABLE aziende_backup AS SELECT * FROM aziende;\n\n";
echo "-- ELIMINAZIONE colonna tipo_struttura (dopo verifica completa)\nALTER TABLE aziende DROP COLUMN tipo_struttura;";
echo "</div>";

echo "<h3>🎨 FASE 2: UI/UX Refactoring</h3>";
echo "<ul>";
echo "<li><strong>azienda.php:</strong> Integrare business_categories sotto nome azienda</li>";
echo "<li><strong>aziende.php:</strong> Aggiornare cards con badge business_categories</li>";
echo "<li><strong>Stile CSS:</strong> Creare badge colorati per categorie</li>";
echo "<li><strong>Responsive:</strong> Ottimizzare per mobile/tablet</li>";
echo "</ul>";

echo "<h3>🔧 FASE 3: Backend Cleanup</h3>";
echo "<ul>";
echo "<li>Rimuovere tutti i riferimenti a tipo_struttura dal codice</li>";
echo "<li>Aggiornare query e API per utilizzare solo business_categories</li>";
echo "<li>Testing completo di tutte le funzionalità</li>";
echo "</ul>";

echo "<h3>✅ FASE 4: Testing & QA</h3>";
echo "<ul>";
echo "<li>Test pagina dettaglio azienda (layout integrato)</li>";
echo "<li>Test lista aziende (cards con badge)</li>";
echo "<li>Test responsive su mobile/tablet</li>";
echo "<li>Verifica performance (nessun impatto)</li>";
echo "</ul>";

echo "</div>";

$conn->close();
?>

</div></body></html>
