<?php
// ===================================================================
// 🔍 ANALISI STRUTTURA TABELLA tokens E VINCOLI FOREIGN KEY
// ===================================================================

require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>DEBUG TOKENS STRUCTURE</title>";
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
</style>";
echo "</head><body>";

echo "<h1>🔍 ANALISI STRUTTURA tokens E FOREIGN KEY</h1>";
echo "<div class='info'>📋 Diagnosi completa vincoli e struttura tabella tokens</div>";
echo "<hr>";

// ==========================================
// STEP 1: STRUTTURA TABELLA tokens
// ==========================================
echo "<h2>STEP 1: 📊 STRUTTURA TABELLA tokens</h2>";

try {
    $describe = $conn->query("DESCRIBE tokens");
    if ($describe && $describe->num_rows > 0) {
        echo "<div class='success'>✅ Tabella tokens trovata</div>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $describe->fetch_assoc()) {
            $highlight = ($row['Field'] === 'id_azienda') ? ' style="background-color: #fff3cd;"' : '';
            echo "<tr$highlight>";
            echo "<td><strong>" . $row['Field'] . "</strong></td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        throw new Exception("Tabella tokens non trovata");
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE: " . $e->getMessage() . "</div>";
}

// ==========================================
// STEP 2: VINCOLI FOREIGN KEY
// ==========================================
echo "<h2>STEP 2: 🔗 VINCOLI FOREIGN KEY</h2>";

try {
    $constraints_sql = "
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME,
            UPDATE_RULE,
            DELETE_RULE
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = 'social_gioco_tris' 
        AND TABLE_NAME = 'tokens' 
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ";
    
    $constraints = $conn->query($constraints_sql);
    if ($constraints && $constraints->num_rows > 0) {
        echo "<div class='info'>🔗 Vincoli FOREIGN KEY trovati:</div>";
        echo "<table>";
        echo "<tr><th>Nome Vincolo</th><th>Colonna</th><th>Tabella Riferimento</th><th>Colonna Riferimento</th><th>ON UPDATE</th><th>ON DELETE</th></tr>";
        while ($row = $constraints->fetch_assoc()) {
            echo "<tr>";
            echo "<td><code>" . $row['CONSTRAINT_NAME'] . "</code></td>";
            echo "<td><strong>" . $row['COLUMN_NAME'] . "</strong></td>";
            echo "<td>" . $row['REFERENCED_TABLE_NAME'] . "</td>";
            echo "<td>" . $row['REFERENCED_COLUMN_NAME'] . "</td>";
            echo "<td>" . $row['UPDATE_RULE'] . "</td>";
            echo "<td>" . $row['DELETE_RULE'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ Nessun vincolo FOREIGN KEY trovato (potrebbe essere il problema)</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE VINCOLI: " . $e->getMessage() . "</div>";
}

// ==========================================
// STEP 3: ANALISI id_azienda NULL
// ==========================================
echo "<h2>STEP 3: 🎯 ANALISI CAMPO id_azienda</h2>";

try {
    // Controlla se id_azienda può essere NULL
    $null_check = $conn->query("
        SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_DEFAULT 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = 'social_gioco_tris' 
        AND TABLE_NAME = 'tokens' 
        AND COLUMN_NAME = 'id_azienda'
    ");
    
    if ($null_check && $null_check->num_rows > 0) {
        $col_info = $null_check->fetch_assoc();
        echo "<table>";
        echo "<tr><th>Proprietà</th><th>Valore</th><th>Significato</th></tr>";
        echo "<tr><td><strong>Campo</strong></td><td>" . $col_info['COLUMN_NAME'] . "</td><td>Nome colonna</td></tr>";
        echo "<tr><td><strong>Nullable</strong></td><td>" . $col_info['IS_NULLABLE'] . "</td><td>" . 
            ($col_info['IS_NULLABLE'] === 'YES' ? '✅ Può essere NULL' : '❌ NON può essere NULL') . "</td></tr>";
        echo "<tr><td><strong>Default</strong></td><td>" . ($col_info['COLUMN_DEFAULT'] ?? 'NULL') . "</td><td>Valore di default</td></tr>";
        echo "</table>";
        
        if ($col_info['IS_NULLABLE'] === 'NO') {
            echo "<div class='error'>❌ PROBLEMA IDENTIFICATO: id_azienda NON può essere NULL ma i token di CREAZIONE non hanno azienda!</div>";
        } else {
            echo "<div class='success'>✅ id_azienda può essere NULL - dovrebbe funzionare</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE ANALISI: " . $e->getMessage() . "</div>";
}

// ==========================================
// STEP 4: CONTROLLO AZIENDE ESISTENTI
// ==========================================
echo "<h2>STEP 4: 📊 CONTROLLO AZIENDE ESISTENTI</h2>";

try {
    $count_aziende = $conn->query("SELECT COUNT(*) as total FROM aziende");
    if ($count_aziende) {
        $total = $count_aziende->fetch_assoc()['total'];
        echo "<div class='info'>📈 Numero aziende nel database: <strong>$total</strong></div>";
        
        if ($total == 0) {
            echo "<div class='warning'>⚠️ NESSUNA AZIENDA NEL DATABASE - Questo spiega l'errore FOREIGN KEY!</div>";
        } else {
            echo "<div class='success'>✅ Ci sono aziende nel database</div>";
            
            // Mostra prime 5 aziende
            $sample = $conn->query("SELECT id, nome_azienda FROM aziende LIMIT 5");
            if ($sample && $sample->num_rows > 0) {
                echo "<h4>📋 Prime 5 aziende:</h4>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Nome Azienda</th></tr>";
                while ($row = $sample->fetch_assoc()) {
                    echo "<tr><td>" . $row['id'] . "</td><td>" . $row['nome_azienda'] . "</td></tr>";
                }
                echo "</table>";
            }
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE CONTEGGIO AZIENDE: " . $e->getMessage() . "</div>";
}

// ==========================================
// STEP 5: TOKENS ESISTENTI
// ==========================================
echo "<h2>STEP 5: 🎫 TOKENS ESISTENTI</h2>";

try {
    $tokens = $conn->query("SELECT id, token, type, id_azienda, status FROM tokens ORDER BY id DESC LIMIT 10");
    if ($tokens && $tokens->num_rows > 0) {
        echo "<div class='info'>🎫 Token presenti nel database:</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Token (primi 8 char)</th><th>Tipo</th><th>ID Azienda</th><th>Status</th></tr>";
        while ($row = $tokens->fetch_assoc()) {
            $highlight = ($row['type'] === 'creazione' && $row['id_azienda'] !== null) ? ' style="background-color: #ffe6e6;"' : '';
            echo "<tr$highlight>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td><code>" . substr($row['token'], 0, 8) . "...</code></td>";
            echo "<td><strong>" . $row['type'] . "</strong></td>";
            echo "<td>" . ($row['id_azienda'] ?? 'NULL') . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ Nessun token presente nel database</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE TOKENS: " . $e->getMessage() . "</div>";
}

// ==========================================
// DIAGNOSI E SOLUZIONI
// ==========================================
echo "<hr>";
echo "<h2>🔧 DIAGNOSI E SOLUZIONI</h2>";

echo "<div class='warning'>";
echo "<h3>🎯 PROBLEMA IDENTIFICATO:</h3>";
echo "Il token di <strong>CREAZIONE</strong> cerca di essere inserito con un <code>id_azienda</code> ma:<br>";
echo "1. I token di CREAZIONE non dovrebbero avere <code>id_azienda</code> (l'azienda non esiste ancora)<br>";
echo "2. Se il campo <code>id_azienda</code> è NOT NULL, fallisce<br>";
echo "3. Se c'è un vincolo FOREIGN KEY, fallisce se l'ID non esiste in <code>aziende</code>";
echo "</div>";

echo "<div class='success'>";
echo "<h3>✅ SOLUZIONI POSSIBILI:</h3>";
echo "<strong>SOLUZIONE 1 - MODIFICA SCHEMA:</strong><br>";
echo "• Rendere <code>id_azienda</code> NULLABLE nella tabella <code>tokens</code><br>";
echo "• I token di CREAZIONE avranno <code>id_azienda = NULL</code><br>";
echo "• I token di MODIFICA avranno <code>id_azienda</code> valido<br><br>";

echo "<strong>SOLUZIONE 2 - MODIFICA CODICE:</strong><br>";
echo "• Non inserire <code>id_azienda</code> nella query per token CREAZIONE<br>";
echo "• Inserire solo i campi necessari per token di creazione<br><br>";

echo "<strong>SOLUZIONE 3 - AZIENDA PLACEHOLDER:</strong><br>";
echo "• Creare azienda \"placeholder\" temporanea<br>";
echo "• Assegnare tutti i token di creazione a questa azienda<br>";
echo "• Aggiornare quando l'azienda vera viene creata";
echo "</div>";

echo "<div class='info'>";
echo "<h3>🚀 RACCOMANDAZIONE:</h3>";
echo "Implementare <strong>SOLUZIONE 1</strong> (schema NULLABLE) + <strong>SOLUZIONE 2</strong> (codice ottimizzato)<br>";
echo "È la soluzione più pulita e logicamente corretta.";
echo "</div>";

echo "<hr>";
echo "<p><strong>🕐 Debug completato:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='admin/dashboard_new.php'>← Torna alla Dashboard Admin</a></p>";
echo "</body></html>";
?>
