<?php
// ===================================================================
// 🔍 DEBUG CAMPO TYPE - ANALISI COMPLETA STRUTTURA
// ===================================================================
// ✅ Analizza definizione campo, charset, collation, vincoli
// ===================================================================

require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>DEBUG CAMPO TYPE</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    .success { color: green; font-weight: bold; background: #e8f5e8; padding: 15px; border-left: 5px solid green; margin: 10px 0; }
    .error { color: red; font-weight: bold; background: #ffe6e6; padding: 15px; border-left: 5px solid red; margin: 10px 0; }
    .info { color: blue; background: #e6f3ff; padding: 15px; border-left: 5px solid blue; margin: 10px 0; }
    .warning { color: orange; font-weight: bold; background: #fff8e6; padding: 15px; border-left: 5px solid orange; margin: 10px 0; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #f2f2f2; font-weight: bold; }
    code { background: #f4f4f4; padding: 5px 8px; border-radius: 3px; font-family: monospace; }
    .highlight { background: #ffffcc; font-weight: bold; }
    .query { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";
echo "</head><body>";

echo "<h1>🔍 DEBUG CAMPO TYPE - ANALISI COMPLETA</h1>";
echo "<div class='error'>🚨 Root Cause: Campo type salvato come stringa vuota invece di 'creazione'</div>";
echo "<hr>";

// ==========================================
// STEP 1: STRUTTURA DETTAGLIATA CAMPO TYPE
// ==========================================
echo "<h2>STEP 1: 🗃️ STRUTTURA DETTAGLIATA CAMPO TYPE</h2>";

try {
    $info = $conn->query("
        SELECT 
            COLUMN_NAME,
            DATA_TYPE,
            CHARACTER_MAXIMUM_LENGTH,
            IS_NULLABLE,
            COLUMN_DEFAULT,
            CHARACTER_SET_NAME,
            COLLATION_NAME,
            COLUMN_TYPE,
            EXTRA
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'tokens' 
        AND COLUMN_NAME = 'type'
    ");
    
    if ($info && $info->num_rows > 0) {
        $field = $info->fetch_assoc();
        
        echo "<table>";
        echo "<tr><th>Proprietà</th><th>Valore</th><th>Note</th></tr>";
        
        foreach ($field as $prop => $value) {
            $note = '';
            $class = '';
            
            if ($prop === 'CHARACTER_MAXIMUM_LENGTH') {
                if ($value && $value < 10) {
                    $note = "⚠️ TROPPO CORTO per 'creazione' (9 char)";
                    $class = ' class="highlight"';
                } else {
                    $note = "✅ Lunghezza sufficiente";
                }
            } elseif ($prop === 'CHARACTER_SET_NAME') {
                $note = $value === 'utf8mb4' ? '✅ UTF-8 corretto' : '⚠️ Charset problema';
            } elseif ($prop === 'COLLATION_NAME') {
                $note = strpos($value, 'utf8') !== false ? '✅ Collation UTF-8' : '⚠️ Collation problema';
            } elseif ($prop === 'IS_NULLABLE') {
                $note = $value === 'YES' ? '✅ Nullable' : '❌ NOT NULL';
            }
            
            echo "<tr$class>";
            echo "<td><strong>$prop</strong></td>";
            echo "<td><code>" . ($value ?? 'NULL') . "</code></td>";
            echo "<td>$note</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test lunghezza specifica
        $word_len = strlen('creazione');
        $max_len = $field['CHARACTER_MAXIMUM_LENGTH'];
        
        if ($max_len && $max_len < $word_len) {
            echo "<div class='error'>❌ PROBLEMA TROVATO: Campo troppo corto!</div>";
            echo "<div class='warning'>📏 Lunghezza 'creazione': $word_len caratteri</div>";
            echo "<div class='warning'>📏 Lunghezza massima campo: $max_len caratteri</div>";
            echo "<div class='info'>🔧 SOLUZIONE: Espandere il campo type a VARCHAR(20) o più</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Campo 'type' non trovato nella tabella tokens!</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE: " . $e->getMessage() . "</div>";
}

// ==========================================
// STEP 2: TEST INSERIMENTO DIVERSI VALORI
// ==========================================
echo "<h2>STEP 2: 🧪 TEST INSERIMENTO VALORI DIVERSI</h2>";

$test_values = [
    'a' => 'Singolo carattere',
    'test' => 'Parola corta',
    'creazione' => 'Valore target (9 char)',
    'modificamodifica' => 'Valore lungo (16 char)',
    '' => 'Stringa vuota',
    'créàziône' => 'Caratteri speciali UTF-8'
];

echo "<table>";
echo "<tr><th>Valore Test</th><th>Lunghezza</th><th>Inserimento</th><th>Recupero</th><th>Match</th></tr>";

foreach ($test_values as $test_value => $description) {
    $test_token = 'TEST_' . time() . '_' . rand(1000, 9999);
    $test_len = strlen($test_value);
    
    try {
        // Inserimento
        $stmt = $conn->prepare("INSERT INTO tokens (token, type, data_scadenza, status, tipo_pacchetto) VALUES (?, ?, NOW() + INTERVAL 1 HOUR, 'test', 'foto')");
        $stmt->bind_param('ss', $test_token, $test_value);
        $insert_ok = $stmt->execute();
        
        if ($insert_ok) {
            // Recupero
            $verify = $conn->prepare("SELECT type FROM tokens WHERE token = ?");
            $verify->bind_param('s', $test_token);
            $verify->execute();
            $result = $verify->get_result()->fetch_assoc();
            $retrieved = $result['type'] ?? 'NULL';
            
            $match = ($retrieved === $test_value);
            $match_icon = $match ? '✅' : '❌';
            $row_class = $match ? '' : ' style="background-color: #ffe6e6;"';
            
            echo "<tr$row_class>";
            echo "<td><code>'$test_value'</code><br><small>$description</small></td>";
            echo "<td>$test_len</td>";
            echo "<td>✅ OK</td>";
            echo "<td><code>'$retrieved'</code></td>";
            echo "<td>$match_icon</td>";
            echo "</tr>";
            
            // Cleanup
            $cleanup = $conn->prepare("DELETE FROM tokens WHERE token = ?");
            $cleanup->bind_param('s', $test_token);
            $cleanup->execute();
            
        } else {
            echo "<tr>";
            echo "<td><code>'$test_value'</code></td>";
            echo "<td>$test_len</td>";
            echo "<td>❌ ERRORE</td>";
            echo "<td>-</td>";
            echo "<td>❌</td>";
            echo "</tr>";
        }
        
    } catch (Exception $e) {
        echo "<tr>";
        echo "<td><code>'$test_value'</code></td>";
        echo "<td>$test_len</td>";
        echo "<td>❌ EXCEPTION</td>";
        echo "<td>" . $e->getMessage() . "</td>";
        echo "<td>❌</td>";
        echo "</tr>";
    }
}

echo "</table>";

// ==========================================
// STEP 3: CHECK VINCOLI E TRIGGER
// ==========================================
echo "<h2>STEP 3: 🔒 VINCOLI E TRIGGER</h2>";

try {
    // Check constraints
    $constraints = $conn->query("
        SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE 
        FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'tokens'
    ");
    
    if ($constraints && $constraints->num_rows > 0) {
        echo "<h3>📋 Vincoli Tabella:</h3>";
        echo "<table>";
        echo "<tr><th>Nome Vincolo</th><th>Tipo</th></tr>";
        while ($constraint = $constraints->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $constraint['CONSTRAINT_NAME'] . "</td>";
            echo "<td>" . $constraint['CONSTRAINT_TYPE'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check triggers
    $triggers = $conn->query("
        SELECT TRIGGER_NAME, EVENT_MANIPULATION, ACTION_TIMING 
        FROM INFORMATION_SCHEMA.TRIGGERS 
        WHERE TRIGGER_SCHEMA = DATABASE() 
        AND EVENT_OBJECT_TABLE = 'tokens'
    ");
    
    if ($triggers && $triggers->num_rows > 0) {
        echo "<h3>⚡ Trigger Tabella:</h3>";
        echo "<table>";
        echo "<tr><th>Nome Trigger</th><th>Evento</th><th>Timing</th></tr>";
        while ($trigger = $triggers->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $trigger['TRIGGER_NAME'] . "</td>";
            echo "<td>" . $trigger['EVENT_MANIPULATION'] . "</td>";
            echo "<td>" . $trigger['ACTION_TIMING'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<div class='warning'>⚠️ Trigger trovati! Potrebbero modificare i valori inseriti.</div>";
    } else {
        echo "<div class='success'>✅ Nessun trigger trovato sulla tabella tokens</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE CHECK VINCOLI: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<p><strong>🕐 Analisi completata:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='debug_dashboard_live.php'>🔄 Debug Live</a> | ";
echo "<a href='admin/dashboard_new.php'>🚀 Dashboard</a></p>";
echo "</body></html>";
?>
