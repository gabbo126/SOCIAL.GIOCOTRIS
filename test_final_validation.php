<?php
/**
 * 🎯 VALIDAZIONE FINALE DEFINITIVA - Test Media Uploader Fix
 * Verifica che l'errore "Limiti piano non caricati" sia stato risolto
 */
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎯 Validazione Finale Media Uploader Fix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .test-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .success { border-color: #28a745; background: #d4edda; }
        .error { border-color: #dc3545; background: #f8d7da; }
        .warning { border-color: #ffc107; background: #fff3cd; }
        pre { background: #f1f1f1; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .api-test { margin: 10px 0; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🎯 VALIDAZIONE FINALE DEFINITIVA</h1>
        <h2>Test Media Uploader Fix - "Limiti piano non caricati"</h2>
        
        <!-- Test 1: API Endpoint per Registrazione -->
        <div class="test-box" id="test1">
            <h3>TEST 1: API per Registrazione (azienda_id=0)</h3>
            <div class="api-test">
                <strong>Endpoint:</strong> <code>/api/media_manager.php?action=limits&azienda_id=0</code>
                <div id="result1">⏳ Testing...</div>
            </div>
        </div>
        
        <!-- Test 2: API Endpoint per Modifica Azienda -->
        <div class="test-box" id="test2">
            <h3>TEST 2: API per Modifica Azienda (azienda_id=1)</h3>
            <div class="api-test">
                <strong>Endpoint:</strong> <code>/api/media_manager.php?action=limits&azienda_id=1</code>
                <div id="result2">⏳ Testing...</div>
            </div>
        </div>
        
        <!-- Test 3: Simulazione Media Manager JavaScript -->
        <div class="test-box" id="test3">
            <h3>TEST 3: Simulazione Media Manager JavaScript</h3>
            <div id="mediaContainer">
                <div class="alert alert-info">Inizializzazione Media Manager...</div>
                <div id="mediaLimitsResult">⏳ Caricamento limiti piano...</div>
            </div>
        </div>
        
        <!-- Test 4: Riepilogo Database Fix -->
        <div class="test-box">
            <h3>TEST 4: Verifica Database Fix Applicati</h3>
            <?php
            require_once 'config.php';
            require_once 'includes/db.php';
            
            $tables_check = [
                'piani_media_limits' => 'Limiti piani Base/Pro',
                'azienda_media' => 'Storage media aziende',
            ];
            
            $columns_check = [
                'aziende.piano' => 'Colonna piano nelle aziende'
            ];
            
            echo "<h4>📊 Tabelle Create:</h4>";
            foreach ($tables_check as $table => $desc) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                if ($result && $result->num_rows > 0) {
                    echo "✅ <strong>$table</strong> - $desc<br>";
                } else {
                    echo "❌ <strong>$table</strong> - MANCANTE!<br>";
                }
            }
            
            echo "<h4>📊 Colonne Create:</h4>";
            $result = $conn->query("SHOW COLUMNS FROM aziende LIKE 'piano'");
            if ($result && $result->num_rows > 0) {
                echo "✅ <strong>aziende.piano</strong> - Colonna piano nelle aziende<br>";
            } else {
                echo "❌ <strong>aziende.piano</strong> - MANCANTE!<br>";
            }
            
            echo "<h4>📊 Dati Popolati:</h4>";
            $piani_count = $conn->query("SELECT COUNT(*) as count FROM piani_media_limits")->fetch_assoc()['count'];
            echo "📋 <strong>piani_media_limits:</strong> $piani_count record<br>";
            
            $aziende_piano = $conn->query("SELECT COUNT(*) as count FROM aziende WHERE piano IS NOT NULL AND piano != ''")->fetch_assoc()['count'];
            echo "📋 <strong>aziende con piano:</strong> $aziende_piano record<br>";
            ?>
        </div>
        
        <!-- Risultato Finale -->
        <div class="test-box" id="finalResult">
            <h3>🏁 RISULTATO FINALE</h3>
            <div id="finalStatus">⏳ Elaborazione test in corso...</div>
        </div>
    </div>

    <script>
    // Test API calls con fetch
    async function testAPI(url, containerId) {
        try {
            const response = await fetch(url);
            const data = await response.json();
            
            const container = document.getElementById(containerId);
            if (data.success) {
                container.innerHTML = `
                    <div class="alert alert-success">
                        ✅ <strong>SUCCESSO!</strong><br>
                        Piano: <strong>${data.data.piano || 'N/A'}</strong><br>
                        Max Media: <strong>${data.data.max_totali || 'N/A'}</strong><br>
                        Max Galleria: <strong>${data.data.max_galleria || 'N/A'}</strong>
                    </div>
                `;
                return true;
            } else {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        ❌ <strong>ERRORE:</strong> ${data.error}<br>
                        <small>Codice: ${data.error_code || 'N/A'}</small>
                    </div>
                `;
                return false;
            }
        } catch (error) {
            const container = document.getElementById(containerId);
            container.innerHTML = `
                <div class="alert alert-warning">
                    ⚠️ <strong>ERRORE RETE:</strong> ${error.message}
                </div>
            `;
            return false;
        }
    }
    
    // Simulazione loadMediaLimits del JavaScript
    function simulateMediaManager() {
        const container = document.getElementById('mediaLimitsResult');
        
        // Simula la chiamata che fa il media manager
        testAPI('/SOCIAL.GIOCOTRIS/api/media_manager.php?action=limits&azienda_id=0', 'dummy')
            .then(success => {
                if (success) {
                    container.innerHTML = `
                        <div class="alert alert-success">
                            ✅ <strong>Media Manager OK!</strong><br>
                            L'errore "Limiti piano non caricati" dovrebbe essere risolto!
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            ❌ <strong>Media Manager FALLITO!</strong><br>
                            L'errore "Limiti piano non caricati" persiste ancora.
                        </div>
                    `;
                }
            });
    }
    
    // Esegui tutti i test
    window.onload = function() {
        // Test API calls
        testAPI('/SOCIAL.GIOCOTRIS/api/media_manager.php?action=limits&azienda_id=0', 'result1')
            .then(test1Success => {
                return testAPI('/SOCIAL.GIOCOTRIS/api/media_manager.php?action=limits&azienda_id=1', 'result2');
            })
            .then(test2Success => {
                // Simula media manager
                simulateMediaManager();
                
                // Risultato finale
                setTimeout(() => {
                    const finalContainer = document.getElementById('finalStatus');
                    const test1Success = document.getElementById('result1').innerHTML.includes('SUCCESSO');
                    const test2Success = document.getElementById('result2').innerHTML.includes('SUCCESSO');
                    
                    if (test1Success && test2Success) {
                        finalContainer.innerHTML = `
                            <div class="alert alert-success">
                                🎉 <strong>SUCCESSO COMPLETO!</strong><br>
                                L'errore "Limiti piano non caricati" è stato <strong>COMPLETAMENTE RISOLTO</strong>!<br>
                                Il media uploader dovrebbe ora funzionare sia in registrazione che in modifica.
                            </div>
                        `;
                        document.getElementById('finalResult').classList.add('success');
                    } else {
                        finalContainer.innerHTML = `
                            <div class="alert alert-danger">
                                ❌ <strong>PROBLEMI ANCORA PRESENTI</strong><br>
                                Alcuni test hanno fallito. L'errore potrebbe persistere.
                            </div>
                        `;
                        document.getElementById('finalResult').classList.add('error');
                    }
                }, 2000);
            });
    };
    </script>
</body>
</html>
