<?php
/**
 * 🎯 ADVANCED MEDIA SECTION - Template Integrazione v2.0
 * Sezione media rivoluzionaria per registrazione e modifica azienda
 */

// Parametri required: $azienda_id, $context (register|edit)
$azienda_id = $azienda_id ?? 0;
$context = $context ?? 'register';
$readonly = $readonly ?? false;
?>

<div class="advanced-media-integration">
    <!-- Include CSS e JS -->
    <link rel="stylesheet" href="assets/css/advanced-media-manager.css">
    
    <!-- Container principale del sistema media -->
    <div id="advanced-media-container" class="mb-4">
        <!-- Il contenuto verrà generato dinamicamente dal JavaScript -->
        <div class="loading-placeholder text-center py-5">
            <div class="spinner-border text-primary mb-3"></div>
            <p class="text-muted">Caricamento sistema media...</p>
        </div>
    </div>
    
    <!-- Hidden inputs per compatibilità con form esistenti -->
    <input type="hidden" id="legacy-logo-field" name="logo" value="">
    <input type="hidden" id="legacy-media-field" name="media_galleria" value="">
    
    <!-- Script inizializzazione -->
    <script src="assets/js/advanced-media-manager.js"></script>
    <script>
        // Configurazione globale per il media manager
        window.currentAziendaId = <?php echo (int)$azienda_id; ?>;
        window.mediaContext = '<?php echo htmlspecialchars($context); ?>';
        window.mediaReadonly = <?php echo $readonly ? 'true' : 'false'; ?>;
        
        // Inizializzazione automatica al caricamento DOM
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎬 Inizializzazione Advanced Media Manager - Context:', window.mediaContext, 'AziendaID:', window.currentAziendaId);
            
            // INIZIALIZZA SEMPRE il media manager (sia per registrazione che modifica)
            try {
                window.advancedMediaManager = new AdvancedMediaManager({
                    azienda_id: window.currentAziendaId,
                    api_base: 'api/media_manager.php',
                    context: window.mediaContext,
                    readonly: window.mediaReadonly
                });
                
                // Setup compatibility layer per form esistenti
                setupLegacyCompatibility();
                
                console.log('✅ Advanced Media Manager inizializzato con successo!');
            } catch (error) {
                console.error('❌ Errore inizializzazione Advanced Media Manager:', error);
                showFallbackInterface();
            }
        });
        
        /**
         * 🔄 COMPATIBILITY LAYER - Sincronizza con campi legacy
         */
        function setupLegacyCompatibility() {
            // Intercetta submit form per sincronizzare dati
            const forms = document.querySelectorAll('form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Sincronizza media con campi hidden legacy
                    syncMediaWithLegacyFields();
                });
            });
        }
        
        function syncMediaWithLegacyFields() {
            if (!window.advancedMediaManager) return;
            
            const mediaList = window.advancedMediaManager.media_list || [];
            
            // Trova logo
            const logo = mediaList.find(m => m.tipo === 'logo');
            if (logo) {
                document.getElementById('legacy-logo-field').value = logo.url;
            }
            
            // Trova galleria (come JSON)
            const galleria = mediaList.filter(m => m.tipo === 'galleria');
            if (galleria.length > 0) {
                document.getElementById('legacy-media-field').value = JSON.stringify(galleria.map(m => m.url));
            }
        }
        
        /**
         * 🛡️ INTERFACCIA FALLBACK per errori inizializzazione
         */
        function showFallbackInterface() {
            const container = document.getElementById('advanced-media-container');
            if (!container) return;
            
            container.innerHTML = `
                <div class="alert alert-warning text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem; color: #ffc107;"></i>
                    </div>
                    <h5 class="mb-3">Sistema Media Temporaneamente Non Disponibile</h5>
                    <p class="mb-3">Si è verificato un errore durante l'inizializzazione del sistema media avanzato.</p>
                    <p class="mb-0"><strong>Per ora puoi continuare con il resto della registrazione.</strong></p>
                    <small class="text-muted d-block mt-2">Potrai aggiungere media successivamente dalla pagina di modifica azienda.</small>
                </div>
            `;
        }
        
        /**
         * 📝 MESSAGGIO REGISTRAZIONE
         */
        function showRegistrationMessage() {
            const container = document.getElementById('advanced-media-container');
            if (!container) return;
            
            container.innerHTML = `
                <div class="alert alert-info d-flex align-items-center">
                    <i class="bi bi-info-circle me-3 fs-4"></i>
                    <div>
                        <h6 class="mb-1">Gestione Media</h6>
                        <p class="mb-0">Dopo aver completato la registrazione, potrai caricare logo e foto della tua azienda dalla pagina di modifica profilo.</p>
                    </div>
                </div>
                
                <div class="media-preview-placeholder">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="placeholder-card logo-placeholder">
                                <div class="placeholder-icon">
                                    <i class="bi bi-image fs-1 text-muted"></i>
                                </div>
                                <h6 class="text-muted">Logo Aziendale</h6>
                                <small class="text-muted">Disponibile dopo la registrazione</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="placeholder-card galleria-placeholder">
                                <div class="placeholder-icon">
                                    <i class="bi bi-images fs-1 text-muted"></i>
                                </div>
                                <h6 class="text-muted">Galleria Foto</h6>
                                <small class="text-muted">Disponibile dopo la registrazione</small>
                            </div>
                        </div>
                    </div>  
                </div>
                
                <style>
                .placeholder-card {
                    border: 2px dashed #dee2e6;
                    border-radius: 0.375rem;
                    padding: 2rem;
                    text-align: center;
                    background: #f8f9fa;
                    transition: all 0.15s ease-in-out;
                }
                .placeholder-card:hover {
                    border-color: #adb5bd;
                    background: #f1f3f4;
                }
                .placeholder-icon {
                    margin-bottom: 1rem;
                }
                .media-preview-placeholder {
                    margin-top: 1.5rem;
                }
                </style>
            `;
        }
        
        /**
         * 🔧 UTILITY FUNCTIONS
         */
        
        // Mostra errori del media manager nel context del form
        function showMediaError(message) {
            const container = document.getElementById('advanced-media-container');
            if (!container) return;
            
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
            alert.innerHTML = `
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Errore Media:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            container.appendChild(alert);
            
            // Auto remove dopo 8 secondi
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 8000);
        }
        
        // Mostra successi del media manager
        function showMediaSuccess(message) {
            const container = document.getElementById('advanced-media-container');
            if (!container) return;
            
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show mt-3';
            alert.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>
                <strong>Successo:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            container.appendChild(alert);
            
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        }
        
        // Esporta funzioni per uso esterno
        window.showMediaError = showMediaError;
        window.showMediaSuccess = showMediaSuccess;
    </script>
</div>

<?php if ($context === 'edit'): ?>
<!-- Script addizionale per context di modifica -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aggiungi pulsante refresh media nella toolbar
    addRefreshButton();
    
    // Setup auto-save per modifiche media
    setupAutoSave();
});

function addRefreshButton() {
    const mediaHeader = document.querySelector('.media-header .col-md-4');
    if (!mediaHeader) return;
    
    const refreshBtn = document.createElement('button');
    refreshBtn.type = 'button';
    refreshBtn.className = 'btn btn-outline-secondary btn-sm me-2';
    refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Aggiorna';
    refreshBtn.onclick = function() {
        if (window.advancedMediaManager) {
            window.advancedMediaManager.refreshMedia();
        }
    };
    
    mediaHeader.insertBefore(refreshBtn, mediaHeader.firstChild);
}

function setupAutoSave() {
    // Auto-sincronizza campi legacy quando media cambia
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && 
                (mutation.target.id === 'logo-preview' || mutation.target.id === 'galleria-preview')) {
                setTimeout(syncMediaWithLegacyFields, 500);
            }
        });
    });
    
    const logoPreview = document.getElementById('logo-preview');
    const galleriaPreview = document.getElementById('galleria-preview');
    
    if (logoPreview) observer.observe(logoPreview, { childList: true, subtree: true });
    if (galleriaPreview) observer.observe(galleriaPreview, { childList: true, subtree: true });
}
</script>
<?php endif; ?>

<?php if ($readonly): ?>
<!-- Script per modalità readonly -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Disabilita tutti i controlli di upload
    const dropzones = document.querySelectorAll('.media-dropzone');
    dropzones.forEach(zone => {
        zone.classList.add('disabled');
        zone.style.pointerEvents = 'none';
    });
    
    // Nascondi pulsanti di azione
    const actionButtons = document.querySelectorAll('.media-actions');
    actionButtons.forEach(actions => {
        actions.style.display = 'none';
    });
    
    // Mostra badge readonly
    const container = document.getElementById('advanced-media-container');
    if (container) {
        const readonlyBadge = document.createElement('div');
        readonlyBadge.className = 'alert alert-secondary text-center py-2';
        readonlyBadge.innerHTML = '<i class="bi bi-eye"></i> <strong>Modalità Solo Lettura</strong>';
        container.insertBefore(readonlyBadge, container.firstChild);
    }
});
</script>
<?php endif; ?>

<!-- Bootstrap Icons (se non già incluso) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
