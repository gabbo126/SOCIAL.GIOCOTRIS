<?php
/**
 * Template unificato moderno - identico alla pagina modifica
 */

// Valori di default
$form_mode = $form_mode ?? 'create';
$azienda = $azienda ?? [];
$tipo_pacchetto = $tipo_pacchetto ?? 'foto';
$token = $token ?? '';
$form_action = $form_action ?? 'processa_registrazione.php';

// Helper function
function getFieldValue($field, $azienda, $default = '') {
    return isset($azienda[$field]) ? htmlspecialchars($azienda[$field]) : $default;
}
?>

<div class="card shadow-lg">
    <div class="card-header bg-warning text-dark">
        <h2 class="mb-0">
            <?php echo $form_mode === 'edit' ? 'Modifica Dati Azienda' : 'Registra la tua Azienda'; ?>
        </h2>
    </div>
    
    <div class="card-body p-4">
        <!-- Info Pacchetto -->
        <div class="alert alert-info border-start border-4 border-info d-flex align-items-center mb-4">
            <div class="flex-shrink-0">
                <?php if ($tipo_pacchetto === 'foto_video'): ?>
                    <i class="bi bi-star-fill fs-3 text-warning"></i>
                <?php else: ?>
                    <i class="bi bi-image fs-3 text-info"></i>
                <?php endif; ?>
            </div>
            <div class="flex-grow-1 ms-3">
                <?php if ($tipo_pacchetto === 'foto_video'): ?>
                    <h5 class="mb-1">🌟 Piano Pro</h5>
                    <p class="mb-0 small">Logo aziendale + fino a 5 media (foto, video, YouTube, foto link)</p>
                <?php else: ?>
                    <h5 class="mb-1">🏷️ Piano Base</h5>
                    <p class="mb-0 small">Logo aziendale + foto e foto link</p>
                <?php endif; ?>
            </div>
        </div>

<form action="<?php echo htmlspecialchars($form_action); ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <?php if ($form_mode === 'edit'): ?>
                <input type="hidden" name="id_azienda" value="<?php echo htmlspecialchars($azienda['id'] ?? ''); ?>">
            <?php endif; ?>
            
            <!-- ===== SEZIONE 1: INFORMAZIONI PRINCIPALI ===== -->
            <div class="form-section mb-5">
                <div class="section-header mb-4">
                    <h5 class="section-title"><i class="bi bi-building"></i> Informazioni Principali</h5>
                    <p class="section-subtitle text-muted">Nome, categoria e descrizione della tua attività</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="nome" class="form-label fw-semibold">Nome Attività <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="nome" name="nome" 
                               value="<?php echo getFieldValue('nome', $azienda); ?>" 
                               placeholder="Es. Pizzeria Da Mario, Hotel Bellavista..." required>
                        <div class="invalid-feedback">Inserisci il nome della tua attività</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Categorie Attività <span class="text-danger">*</span></label>
                        <div id="business-categories" 
                             data-business-categories
                             data-placeholder="Seleziona le categorie della tua attività..."
                             data-max-selections="3"
                             data-allow-custom="true"
                             data-required="true"
                             <?php if ($form_mode === 'edit'): ?>
                             data-preselected='<?php 
                                $existing_categories = [];
                                if (!empty($azienda['business_categories'])) {
                                    $categories_data = json_decode($azienda['business_categories'], true);
                                    if (is_array($categories_data)) {
                                        $existing_categories = $categories_data;
                                    }
                                } else if (!empty($azienda['tipo_struttura'])) {
                                    $existing_categories[] = trim($azienda['tipo_struttura']);
                                }
                                echo htmlspecialchars(json_encode($existing_categories)); 
                             ?>'
                             <?php endif; ?>
                             ></div>
                        <div class="form-text text-muted">Seleziona fino a 3 categorie che descrivono la tua attività</div>
                    </div>

                    <div class="col-12">
                        <label for="descrizione" class="form-label fw-semibold">Descrizione Attività <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-lg" id="descrizione" name="descrizione" rows="5" 
                                  placeholder="Descrivi la tua attività, i servizi offerti e ciò che ti rende unico..." 
                                  required><?php echo getFieldValue('descrizione', $azienda); ?></textarea>
                        <div class="invalid-feedback">Inserisci una descrizione della tua attività</div>
                    </div>
                </div>
            </div>
            
            <!-- ===== SEZIONE 2: INFORMAZIONI DI CONTATTO ===== -->
            <div class="form-section mb-5">
                <div class="section-header mb-4">
                    <h5 class="section-title"><i class="bi bi-geo-alt-fill"></i> Informazioni di Contatto</h5>
                    <p class="section-subtitle text-muted">Come i clienti possono raggiungerti</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-8">
                        <label for="indirizzo" class="form-label fw-semibold">Indirizzo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="indirizzo" name="indirizzo" 
                               value="<?php echo getFieldValue('indirizzo', $azienda); ?>" 
                               placeholder="Via Roma 123, 00100 Roma RM" required>
                        <div class="invalid-feedback">Inserisci l'indirizzo della tua attività</div>
                    </div>

                    <div class="col-md-4">
                        <label for="telefono" class="form-label fw-semibold">Telefono</label>
                        <input type="tel" class="form-control form-control-lg" id="telefono" name="telefono" 
                               value="<?php echo getFieldValue('telefono', $azienda); ?>" 
                               placeholder="+39 06 1234567">
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" 
                               value="<?php echo getFieldValue('email', $azienda); ?>" 
                               placeholder="info@tuaazienda.it">
                    </div>

                    <div class="col-md-6">
                        <label for="sito_web" class="form-label fw-semibold">Sito Web</label>
                        <input type="url" class="form-control form-control-lg" id="sito_web" name="sito_web" 
                               value="<?php echo getFieldValue('sito_web', $azienda); ?>" 
                               placeholder="https://www.tuosito.it">
                    </div>
                </div>
            </div>

            <!-- ===== SEZIONE 3: SERVIZI E SPECIALITÀ ===== -->
            <div class="form-section mb-5">
                <div class="section-header mb-4">
                    <h5 class="section-title"><i class="bi bi-gear-fill"></i> Servizi e Specialità</h5>
                    <p class="section-subtitle text-muted">Seleziona i servizi che offri dalla nostra lista completa</p>
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Servizi Offerti</label>
                        <div id="services-selector" 
                             data-services-selector
                             data-placeholder="Seleziona i servizi che offri..."
                             data-allow-custom="true"
                             <?php if ($form_mode === 'edit'): ?>
                             data-preselected='<?php 
                                $existing_services = [];
                                if (!empty($azienda['servizi_offerti'])) {
                                    $services_data = json_decode($azienda['servizi_offerti'], true);
                                    if (is_array($services_data)) {
                                        $existing_services = $services_data;
                                    } else {
                                        $existing_services = array_filter(array_map('trim', explode(',', $azienda['servizi_offerti'])));
                                    }
                                }
                                echo htmlspecialchars(json_encode($existing_services)); 
                             ?>'
                             <?php endif; ?>
                             ></div>
                        <div class="form-text text-muted">Aggiungi i servizi principali che offri ai tuoi clienti</div>
                    </div>
                </div>
            </div>

            <!-- ===== SEZIONE 4: SERVIZI E SPECIALITÀ ===== -->
            <div class="form-section mb-5">
                <div class="section-header mb-4">
                    <h5 class="section-title"><i class="bi bi-gear-fill"></i> Servizi e Specialità</h5>
                    <p class="section-subtitle text-muted">Seleziona i servizi che offri dalla nostra lista completa o aggiungine di personalizzati</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-12">
                        <?php
                        // Carica servizi esistenti dal database per modalità edit
                        $existing_services = [];
                        if ($form_mode === 'edit' && !empty($azienda['services_offered'])) {
                            // Nuovo campo JSON
                            $services_data = json_decode($azienda['services_offered'], true);
                            if (is_array($services_data)) {
                                $existing_services = $services_data;
                            }
                        } elseif ($form_mode === 'edit' && !empty($azienda['servizi'])) {
                            // Fallback: converti vecchio campo servizi
                            $existing_services = array_map('trim', explode(',', $azienda['servizi']));
                            $existing_services = array_filter($existing_services);
                        }
                        ?>
                        
                        <!-- SISTEMA SERVIZI MODERNO -->
                        <div id="businessServicesSelector" 
                             data-business-services="true"
                             data-max-selections="8"
                             data-allow-custom="true"
                             data-preselected-services='<?php echo json_encode($existing_services); ?>'>
                        </div>
                        
                        <!-- Info Aggiuntiva -->
                        <div class="alert alert-info mt-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <div>
                                    <strong>Sistema Intelligente:</strong> 
                                    Seleziona fino a <strong>8 servizi</strong> dalla nostra lista completa 
                                    organizzata per categorie, oppure aggiungi servizi personalizzati. 
                                    I servizi saranno utilizzati per migliorare la ricerca dei clienti.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== SEZIONE 5: ORARI DI APERTURA ===== -->
            <div class="form-section mb-5">
                <div class="section-header mb-4">
                    <h5 class="section-title"><i class="bi bi-clock-fill"></i> Orari di Apertura</h5>
                    <p class="section-subtitle text-muted">Quando i clienti possono trovarti</p>
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <label for="orari" class="form-label fw-semibold">Orari di Apertura</label>
                        <textarea class="form-control form-control-lg" id="orari" name="orari" rows="4" 
                                  placeholder="Es: Lun-Ven: 9:00-18:00&#10;Sab: 9:00-13:00&#10;Dom: Chiuso"><?php echo getFieldValue('orari', $azienda); ?></textarea>
                        <div class="form-text text-muted">Specifica i tuoi orari di apertura per ogni giorno della settimana</div>
                    </div>
                </div>
            </div>

            <!-- ===== SEZIONE 6: MEDIA E IMMAGINI AVANZATA ===== -->
            <?php 
            // Configurazione per sistema media avanzato
            $azienda_id = $form_mode === 'edit' ? ($azienda['id'] ?? 0) : 0;
            $context = $form_mode === 'edit' ? 'edit' : 'register';
            ?>
            <div class="form-section mb-5">
                <div class="section-header mb-4">
                    <h5 class="section-title"><i class="bi bi-images"></i> Media e Immagini</h5>
                    <p class="section-subtitle text-muted">Sistema avanzato per logo aziendale e galleria media</p>
                </div>

                <!-- Inclusione template sistema media avanzato -->
                <?php include __DIR__ . '/advanced-media-section.php'; ?>
            </div>

            <!-- Pulsanti Submit -->
            <div class="text-center mt-5">
                <button type="submit" class="btn btn-warning btn-lg px-5">
                    <i class="bi bi-check-lg"></i>
                    <?php echo $form_mode === 'edit' ? 'Salva Modifiche' : 'Registra Azienda'; ?>
                </button>
                <div class="form-text mt-2">Tutti i campi obbligatori devono essere compilati</div>
            </div>

        </form>

    </div><!-- /card-body -->
</div><!-- /card shadow-lg -->

<!-- ===== ASSET SISTEMA SERVIZI AVANZATO ===== -->
<link rel="stylesheet" href="assets/css/business-services.css">
<!-- Script business-services.js già incluso in header.php -->

<!-- ===== ASSET SISTEMA CATEGORIE AVANZATO ===== -->
<link rel="stylesheet" href="assets/css/business-categories.css">
<!-- Script business-categories.js già incluso in header.php -->

<!-- ===== ASSET ENHANCED MEDIA UPLOADER ===== -->
<link rel="stylesheet" href="assets/css/enhanced-media-uploader.css">
<script src="assets/js/enhanced-media-uploader.js"></script>

<!-- 🚀 INIZIALIZZAZIONE AUTOMATICA ENHANCED MEDIA UPLOADER -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎬 Avvio Enhanced Media Uploader...');
    
    // ✅ INIZIALIZZAZIONE AUTOMATICA CON FALLBACK IMMEDIATO
    try {
        if (typeof EnhancedMediaUploader !== 'undefined') {
            // Evita inizializzazioni multiple
            if (!window.enhancedUploader && !window._enhancedMediaUploaderInitialized) {
                console.log('🎯 Inizializzazione manuale EnhancedMediaUploader...');
                window.enhancedUploader = new EnhancedMediaUploader();
                window.enhancedUploader.init();
                window._enhancedMediaUploaderInitialized = true;
                console.log('✅ Enhanced Media Uploader inizializzato!');
            } else {
                console.log('ℹ️ Enhanced Media Uploader già inizializzato');
            }
        } else {
            console.error('❌ EnhancedMediaUploader non disponibile - usando fallback');
            activateBasicMediaFallback();
        }
    } catch (error) {
        console.error('❌ Errore inizializzazione uploader:', error);
        activateBasicMediaFallback();
    }
});

// 🛡️ FALLBACK BASIC MEDIA SEMPRE DISPONIBILE
function activateBasicMediaFallback() {
    console.log('🔧 Attivazione sistema upload basic...');
    
    const containers = [
        document.getElementById('media-upload-container'),
        document.getElementById('media-container'),
        document.querySelector('.enhanced-media-container'),
        document.querySelector('[data-package]')
    ].filter(Boolean);
    
    if (containers.length === 0) {
        console.warn('⚠️ Nessun container media trovato per fallback');
        return;
    }
    
    const container = containers[0];
    container.innerHTML = `
        <div class="alert alert-info">
            <h6><i class="bi bi-upload"></i> Caricamento Media Semplificato</h6>
            <p class="mb-3">Sistema di upload essenziale sempre funzionante</p>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Upload File</label>
                    <input type="file" class="form-control" name="media_files[]" multiple 
                           accept="image/*,video/*" onchange="previewBasicMedia(this)">
                    <small class="text-muted">Formati: JPG, PNG, GIF, MP4</small>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-semibold">URL Diretto</label>
                    <input type="url" class="form-control" name="media_urls[]" 
                           placeholder="https://example.com/image.jpg" 
                           onchange="previewBasicURL(this)">
                    <small class="text-muted">Link diretto a immagini/video</small>
                </div>
            </div>
            
            <div id="basic-preview" class="mt-3"></div>
        </div>
    `;
}

// 🖼️ Preview immediato per sistema basic
function previewBasicMedia(input) {
    const preview = document.getElementById('basic-preview');
    if (!preview) return;
    
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'd-inline-block m-2';
            div.innerHTML = `
                <img src="${e.target.result}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;" 
                     class="border shadow-sm">
                <div class="text-center small text-muted mt-1">${file.name}</div>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function previewBasicURL(input) {
    const preview = document.getElementById('basic-preview');
    if (!preview || !input.value) return;
    
    const div = document.createElement('div');
    div.className = 'd-inline-block m-2';
    div.innerHTML = `
        <img src="${input.value}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;" 
             class="border shadow-sm" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22><rect width=%22100%22 height=%22100%22 fill=%22%23f8f9fa%22/><text x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23666%22>URL</text></svg>'">
        <div class="text-center small text-muted mt-1">URL Media</div>
    `;
    preview.appendChild(div);
}
</script>

<!-- CSS Unificato per il form -->
<style>
.company-form.unified-form {
    max-width: 800px;
    margin: 0 auto;
}

.company-form fieldset {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 25px;
    background: #f8f9fa;
}

.company-form .info-section {
    border-color: #17a2b8;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
}

.company-form fieldset:hover {
    border-color: #007bff;
    background: white;
}

/* CSS CRITICO: SEPARAZIONE VISUALE LOGO/GALLERIA */
.logo-section {
    border: 3px solid #ffc107 !important;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3) !important;
    margin-bottom: 2rem;
}

.logo-section:hover {
    box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4) !important;
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

.enhanced-media-container {
    border: 3px solid #28a745 !important;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
}

.company-form .media-section {
    border-color: #28a745;
    background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%);
}

.company-form .upgrade-section {
    border-color: #ffc107;
    background: linear-gradient(135deg, #fffdf0 0%, #fff3cd 100%);
}

.company-form legend {
    font-weight: bold;
    padding: 0 10px;
    color: #495057;
}

.company-form .form-group {
    margin-bottom: 15px;
}

.company-form .form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #495057;
}

.company-form input, .company-form textarea, .company-form select {
    width: 100%;
    box-sizing: border-box;
}

.company-form .current-media-preview {
    margin-top: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
}

.company-form .existing-media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.company-form .existing-media-item {
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: white;
}

.company-form .upgrade-checkbox {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 15px;
    border: 1px solid #ffc107;
    border-radius: 8px;
    background: #fff3cd;
}

.company-form .upgrade-checkbox input[type="checkbox"] {
    width: auto;
    margin-top: 2px;
}

.media-controls {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 15px;
}

#mediaInputsContainer .media-input-group {
    margin-bottom: 15px;
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: white;
}
</style>
