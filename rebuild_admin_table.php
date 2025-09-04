<?php
// ===================================================================
// 🔑 RICOSTRUZIONE TABELLA admin_users - ACCESSO ADMIN
// ===================================================================
// ✅ Crea tabella admin_users e inserisce admin di default
// ===================================================================

require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>REBUILD ADMIN TABLE</title>";
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
    .credentials { background: #fff3cd; border: 2px solid #ffc107; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: center; }
</style>";
echo "</head><body>";

echo "<h1>🔑 RICOSTRUZIONE TABELLA admin_users</h1>";
echo "<div class='info'>📋 Ricostruzione completa accesso amministrativo dopo rebuild database</div>";
echo "<hr>";

$success = true;

// ==========================================
// STEP 1: ELIMINA TABELLA SE ESISTE
// ==========================================
echo "<h2>STEP 1: 🗑️ PULIZIA TABELLA ESISTENTE</h2>";

try {
    $drop_result = $conn->query("DROP TABLE IF EXISTS admin_users");
    if ($drop_result) {
        echo "<div class='success'>✅ Tabella admin_users eliminata (se esisteva)</div>";
    } else {
        throw new Exception("Errore DROP TABLE: " . $conn->error);
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE: " . $e->getMessage() . "</div>";
    $success = false;
}

// ==========================================
// STEP 2: CREA TABELLA admin_users
// ==========================================
echo "<h2>STEP 2: 🏗️ CREAZIONE TABELLA admin_users</h2>";

$create_admin_sql = "
CREATE TABLE admin_users (
    id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    username varchar(50) NOT NULL UNIQUE,
    password_hash varchar(255) NOT NULL,
    email varchar(100) DEFAULT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    last_login timestamp NULL DEFAULT NULL,
    is_active tinyint(1) DEFAULT 1,
    role varchar(20) DEFAULT 'admin',
    PRIMARY KEY (id),
    KEY idx_username (username),
    KEY idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $result = $conn->query($create_admin_sql);
    if ($result) {
        echo "<div class='success'>✅ Tabella admin_users creata con successo</div>";
        
        // Mostra struttura creata
        echo "<h3>📋 Struttura Tabella Creata:</h3>";
        $describe = $conn->query("DESCRIBE admin_users");
        if ($describe) {
            echo "<table>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            while ($row = $describe->fetch_assoc()) {
                echo "<tr>";
                echo "<td><strong>" . $row['Field'] . "</strong></td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
                echo "<td>" . $row['Extra'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        throw new Exception("Errore CREATE TABLE: " . $conn->error);
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE CREAZIONE: " . $e->getMessage() . "</div>";
    $success = false;
}

// ==========================================
// STEP 3: INSERISCI ADMIN DI DEFAULT
// ==========================================
if ($success) {
    echo "<h2>STEP 3: 👤 CREAZIONE ADMIN DI DEFAULT</h2>";
    
    // Credenziali di default
    $admin_username = 'admin';
    $admin_password = 'admin123';  // Password temporanea
    $admin_email = 'admin@socialgiocotris.it';
    
    // Hash della password
    $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    
    try {
        $insert_stmt = $conn->prepare("INSERT INTO admin_users (username, password_hash, email, role) VALUES (?, ?, ?, 'super_admin')");
        $insert_stmt->bind_param('sss', $admin_username, $password_hash, $admin_email);
        
        if ($insert_stmt->execute()) {
            $admin_id = $conn->insert_id;
            echo "<div class='success'>✅ Admin di default creato - ID: $admin_id</div>";
            
            // Mostra credenziali
            echo "<div class='credentials'>";
            echo "<h3>🔐 CREDENZIALI ADMIN DI DEFAULT</h3>";
            echo "<table style='margin: 0 auto; background: white;'>";
            echo "<tr><th>Campo</th><th>Valore</th></tr>";
            echo "<tr><td><strong>Username:</strong></td><td><code>$admin_username</code></td></tr>";
            echo "<tr><td><strong>Password:</strong></td><td><code>$admin_password</code></td></tr>";
            echo "<tr><td><strong>Email:</strong></td><td><code>$admin_email</code></td></tr>";
            echo "<tr><td><strong>Ruolo:</strong></td><td><code>super_admin</code></td></tr>";
            echo "</table>";
            echo "<div class='warning' style='margin-top: 15px'>";
            echo "⚠️ <strong>IMPORTANTE:</strong> Cambia la password dopo il primo accesso!";
            echo "</div>";
            echo "</div>";
            
        } else {
            throw new Exception("Errore INSERT admin: " . $insert_stmt->error);
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE INSERIMENTO ADMIN: " . $e->getMessage() . "</div>";
        $success = false;
    }
}

// ==========================================
// STEP 4: TEST LOGIN ADMIN
// ==========================================
if ($success) {
    echo "<h2>STEP 4: 🧪 TEST LOGIN ADMIN</h2>";
    
    try {
        $test_stmt = $conn->prepare("SELECT id, username, password_hash, email, role FROM admin_users WHERE username = ?");
        $test_username = 'admin';
        $test_stmt->bind_param('s', $test_username);
        $test_stmt->execute();
        $test_result = $test_stmt->get_result();
        
        if ($test_result->num_rows === 1) {
            $admin_data = $test_result->fetch_assoc();
            echo "<div class='success'>✅ Query login funziona correttamente</div>";
            
            // Test password verify
            if (password_verify('admin123', $admin_data['password_hash'])) {
                echo "<div class='success'>✅ Verifica password funziona correttamente</div>";
            } else {
                echo "<div class='error'>❌ Verifica password fallita</div>";
                $success = false;
            }
            
            echo "<h3>📊 Dati Admin Recuperati:</h3>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valore</th></tr>";
            foreach ($admin_data as $campo => $valore) {
                if ($campo === 'password_hash') {
                    $valore = substr($valore, 0, 20) . '... (troncato per sicurezza)';
                }
                echo "<tr><td><strong>$campo</strong></td><td>$valore</td></tr>";
            }
            echo "</table>";
            
        } else {
            throw new Exception("Admin non trovato o duplicati presenti");
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE TEST LOGIN: " . $e->getMessage() . "</div>";
        $success = false;
    }
}

// ==========================================
// STEP 5: VERIFICA COMPATIBILITÀ CON LOGIN.PHP
// ==========================================
if ($success) {
    echo "<h2>STEP 5: 🔍 VERIFICA COMPATIBILITÀ login.php</h2>";
    
    try {
        // Simula la query esatta di login.php
        $login_stmt = $conn->prepare("SELECT username, password_hash FROM admin_users WHERE username = ?");
        $test_user = 'admin';
        $login_stmt->bind_param('s', $test_user);
        $login_stmt->execute();
        $login_result = $login_stmt->get_result();
        
        if ($login_result->num_rows === 1) {
            echo "<div class='success'>✅ Query di login.php compatibile</div>";
            echo "<div class='info'>📋 La struttura tabella è perfettamente compatibile con il sistema di login esistente</div>";
        } else {
            throw new Exception("Query login.php non compatibile");
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERRORE COMPATIBILITÀ: " . $e->getMessage() . "</div>";
        $success = false;
    }
}

// ==========================================
// RISULTATO FINALE
// ==========================================
echo "<hr>";

if ($success) {
    echo "<div class='success' style='font-size: 20px; text-align: center; padding: 20px'>";
    echo "🎉 TABELLA admin_users CREATA CON SUCCESSO!";
    echo "</div>";
    
    echo "<h3>🚀 ACCESSO ADMIN RIPRISTINATO:</h3>";
    echo "<div class='info'>";
    echo "1. <strong>Vai alla dashboard admin:</strong> <a href='admin/dashboard_new.php'>Dashboard Admin</a><br>";
    echo "2. <strong>Login con credenziali:</strong> admin / admin123<br>";
    echo "3. <strong>Cambia password</strong> dopo il primo accesso<br>";
    echo "4. Il sistema admin è <strong>COMPLETAMENTE FUNZIONANTE</strong>! 🚀";
    echo "</div>";
    
    echo "<div class='warning'>";
    echo "<strong>📋 PROSSIMI STEP:</strong><br>";
    echo "• Accedi alla dashboard admin<br>";
    echo "• Cambia password di default<br>";
    echo "• Verifica funzionalità gestione aziende e token<br>";
    echo "• Il sistema è pronto per uso completo!";
    echo "</div>";
    
} else {
    echo "<div class='error' style='font-size: 20px; text-align: center; padding: 20px'>";
    echo "❌ ERRORE NELLA CREAZIONE TABELLA";
    echo "</div>";
    
    echo "<div class='warning'>";
    echo "<strong>🔧 AZIONI RICHIESTE:</strong><br>";
    echo "1. Verifica che MySQL sia attivo<br>";
    echo "2. Controlla i permessi database<br>";
    echo "3. Riavvia XAMPP se necessario<br>";
    echo "4. Riesegui questo script";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>🕐 Rebuild admin completato:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='index.php'>← Torna alla home</a> | ";
if ($success) {
    echo "<a href='admin/dashboard_new.php'>🔑 ACCEDI ALLA DASHBOARD ADMIN</a>";
} else {
    echo "<a href='rebuild_admin_table.php'>🔄 Riprova Rebuild Admin</a>";
}
echo "</p>";
echo "</body></html>";
?>
