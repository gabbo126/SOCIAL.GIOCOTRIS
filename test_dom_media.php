<?php
require_once 'config.php';
require_once 'includes/db.php';

// Test DOM per verificare rendering pulsante media
$token = $_GET['token'] ?? 'test_media_fix_1756720953';

// Verifica token
$query = "SELECT * FROM tokens WHERE token = ? AND type = 'creazione'";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$token_data = $result->fetch_assoc();

if (!$token_data || $token_data['status'] !== 'attivo' || strtotime($token_data['data_scadenza']) <= time()) {
    die("Token non valido o scaduto");
}

$tipo_pacchetto = $token_data['tipo_pacchetto'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test DOM Media Button</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        .debug-info { 
            background: #f8f9fa; 
            border: 1px solid #dee2e6; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 8px; 
        }
        .success { background: #d1edff; border-color: #b8daff; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .warning { background: #fff3cd; border-color: #ffeaa7; }
        .dom-inspector { 
            font-family: monospace; 
            background: #263238; 
            color: #fff; 
            padding: 15px; 
            border-radius: 5px; 
            white-space: pre-wrap; 
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1>🧪 Test DOM Media Button</h1>
        
        <div class="debug-info success">
            <h3>Token Info</h3>
            <p><strong>Token:</strong> <?php echo htmlspecialchars($token); ?></p>
            <p><strong>Tipo Pacchetto:</strong> <?php echo htmlspecialchars($tipo_pacchetto); ?></p>
        </div>

        <!-- SEZIONE MEDIA IDENTICA AL TEMPLATE -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-images"></i> Media e Immagini
                    <?php if ($tipo_pacchetto === 'foto_video'): ?>
                        <span class="badge bg-success ms-2">🌟 Piano Pro: Fino a 5 media</span>
                    <?php else: ?>
                        <span class="badge bg-info ms-2">🏷️ Piano Base: Fino a 3 media</span>
                    <?php endif; ?>
                </h6>
            </div>
            <div class="card-body">
                <?php if ($tipo_pacchetto === 'foto_video'): ?>
                    <p class="text-success mb-3">
                        <i class="bi bi-star-fill"></i> <strong>Piano Pro:</strong> 
                        Puoi caricare foto, video, link YouTube e link a immagini.
                    </p>
                    
                    <!-- Media Manager Pro -->
                    <div id="media-upload-container" class="border rounded p-3 bg-light" data-package="foto_video">
                        <div class="text-center mb-3">
                            <button type="button" class="btn btn-outline-success" id="add-media-btn">
                                <i class="bi bi-plus-lg"></i> Aggiungi Media (max 5)
                            </button>
                        </div>
                        <div id="media-list" class="row g-3">
                            <!-- Media items generati via JavaScript -->
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-info mb-3">
                        <i class="bi bi-image"></i> <strong>Piano Base:</strong> 
                        Puoi caricare foto e link a immagini (max 3).
                    </p>
                    
                    <!-- Media Manager Base -->
                    <div id="media-upload-container" class="border rounded p-3 bg-light" data-package="foto">
                        <div class="text-center mb-3">
                            <button type="button" class="btn btn-outline-info" id="add-media-btn">
                                <i class="bi bi-plus-lg"></i> Aggiungi Media (max 3)
                            </button>
                        </div>
                        <div id="media-list" class="row g-3">
                            <!-- Media items generati via JavaScript -->
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ZONA DEBUG -->
        <div class="debug-info warning mt-4">
            <h3>🔍 DOM Inspector JavaScript</h3>
            <p>Questo script verificherà se il pulsante è presente nel DOM e se il JavaScript funziona:</p>
            
            <button onclick="inspectDOM()" class="btn btn-primary">🧪 Ispeziona DOM</button>
            <button onclick="testClickEvent()" class="btn btn-success ms-2">🖱️ Test Click Event</button>
            
            <div id="dom-results" class="dom-inspector mt-3" style="display: none;"></div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- MEDIA MANAGER UNIFICATO -->
    <script src="assets/js/media-manager-unified.js"></script>
    
    <!-- SCRIPT DEBUG -->
    <script>
        function inspectDOM() {
            const results = document.getElementById('dom-results');
            results.style.display = 'block';
            
            let output = '=== DOM INSPECTION RESULTS ===\n\n';
            
            // 1. Verifica container
            const container = document.getElementById('media-upload-container');
            output += `1. Media Upload Container:\n`;
            output += `   - Trovato: ${container ? '✅ SÌ' : '❌ NO'}\n`;
            if (container) {
                output += `   - ID: ${container.id}\n`;
                output += `   - Classes: ${container.className}\n`;
                output += `   - Data-package: ${container.getAttribute('data-package')}\n`;
            }
            output += '\n';
            
            // 2. Verifica pulsante
            const button = document.getElementById('add-media-btn');
            output += `2. Add Media Button:\n`;
            output += `   - Trovato: ${button ? '✅ SÌ' : '❌ NO'}\n`;
            if (button) {
                output += `   - ID: ${button.id}\n`;
                output += `   - Classes: ${button.className}\n`;
                output += `   - Text: ${button.textContent.trim()}\n`;
                output += `   - Visible: ${window.getComputedStyle(button).display !== 'none' ? '✅ SÌ' : '❌ NO'}\n`;
            }
            output += '\n';
            
            // 3. Verifica script caricati
            const scripts = document.querySelectorAll('script[src]');
            output += `3. Script JavaScript caricati:\n`;
            scripts.forEach(script => {
                if (script.src.includes('media')) {
                    output += `   - ${script.src} ✅\n`;
                }
            });
            output += '\n';
            
            // 4. Verifica errori console
            output += `4. Test JavaScript globals:\n`;
            output += `   - window.MediaManager: ${typeof window.MediaManager !== 'undefined' ? '✅ PRESENTE' : '❌ NON TROVATO'}\n`;
            output += `   - window.Bootstrap: ${typeof window.bootstrap !== 'undefined' ? '✅ PRESENTE' : '❌ NON TROVATO'}\n`;
            
            results.textContent = output;
        }
        
        function testClickEvent() {
            const button = document.getElementById('add-media-btn');
            const results = document.getElementById('dom-results');
            
            let output = results.textContent || '';
            output += '\n=== CLICK EVENT TEST ===\n';
            
            if (!button) {
                output += 'ERRORE: Pulsante non trovato!\n';
                results.textContent = output;
                return;
            }
            
            // Simula click
            output += 'Simulando click sul pulsante...\n';
            
            try {
                button.click();
                output += '✅ Click eseguito senza errori JavaScript\n';
            } catch (error) {
                output += `❌ Errore durante click: ${error.message}\n`;
            }
            
            results.textContent = output;
        }
        
        // Auto-run inspection al caricamento
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🧪 TEST DOM - Pagina caricata');
            
            setTimeout(() => {
                console.log('🧪 TEST DOM - Auto-inspection dopo 2 secondi');
                inspectDOM();
            }, 2000);
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
