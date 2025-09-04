<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$token_valido = false;
$error_message = '';
$azienda = null;
$tipo_pacchetto = 'foto'; // Default

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Cerca un token di modifica attivo e non scaduto
    $stmt = $conn->prepare("SELECT * FROM tokens WHERE token = ? AND type = 'modifica' AND status = 'attivo' AND data_scadenza > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $token_result = $stmt->get_result();

    if ($token_result->num_rows === 1) {
        $token_data = $token_result->fetch_assoc();
        $id_azienda = $token_data['id_azienda'];
        $tipo_pacchetto = $token_data['tipo_pacchetto'] ?? 'foto';

        // Recupera i dati dell'azienda associata
        $azienda_stmt = $conn->prepare("SELECT * FROM aziende WHERE id = ?");
        $azienda_stmt->bind_param('i', $id_azienda);
        $azienda_stmt->execute();
        $azienda_result = $azienda_stmt->get_result();

        if ($azienda_result->num_rows === 1) {
            $token_valido = true;
            $azienda = $azienda_result->fetch_assoc();
        } else {
            $error_message = 'Errore: l\'azienda associata a questo token non è stata trovata.';
        }
        $azienda_stmt->close();
    } else {
        $error_message = 'Il token fornito non è valido, è scaduto o è già stato utilizzato.';
    }
    $stmt->close();
} else {
    $error_message = 'Nessun token fornito. Impossibile procedere.';
}

$page_title = 'Modifica i Dati della tua Azienda';
require_once 'templates/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-warning text-dark">
                    <h2 class="mb-0">Modifica Dati Azienda</h2>
                </div>
                <div class="card-body p-4">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <h4 class="alert-heading">Accesso Negato</h4>
                            <p><?php echo htmlspecialchars($error_message); ?></p>
                            <hr>
                            <p class="mb-0">Se credi si tratti di un errore, contatta l'amministratore del sito.</p>
                        </div>
                    <?php elseif ($token_valido && $azienda): ?>
                        <!-- Info Pacchetto -->
                        <div class="alert alert-info border-start border-4 border-info d-flex align-items-center mb-4">
                            <div class="flex-shrink-0">
                                <?php if ($tipo_pacchetto === 'foto_video'): ?>
                                    <i class="bi bi-camera-video fs-2 text-purple"></i>
                                <?php else: ?>
                                    <i class="bi bi-image fs-2 text-info"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="alert-heading mb-1">
                                    <?php if ($tipo_pacchetto === 'foto_video'): ?>
                                        🌟 Piano Pro
                                    <?php else: ?>
                                        🏷️ Piano Base
                                    <?php endif; ?>
                                </h5>
                                <p class="mb-0">
                                    <?php if ($tipo_pacchetto === 'foto_video'): ?>
                                        Puoi modificare <strong>logo + fino a 5 media</strong> (foto, video, YouTube, foto link).
                                    <?php else: ?>
                                        Puoi modificare <strong>logo + fino a 3 media</strong> (foto e foto link).
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="text-center mb-4">
                            <p class="lead text-muted">Aggiorna i dati della tua attività con facilità</p>
                            <p class="small text-secondary">I campi contrassegnati con <span class="text-danger fw-bold">*</span> sono obbligatori</p>
                        </div>

                        <form action="processa_modifica_token.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                            <input type="hidden" name="id_azienda" value="<?php echo $azienda['id']; ?>">
                            
                            <!-- ===== SEZIONE 1: INFORMAZIONI PRINCIPALI ===== -->
                            <div class="form-section mb-5">
                                <div class="section-header mb-4">
                                    <h5 class="section-title"><i class="bi bi-building"></i> Informazioni Principali</h5>
                                    <p class="section-subtitle text-muted">Nome, categoria e descrizione della tua attività</p>
                                </div>
                                
                                <div class="row g-4">
                                <!-- Dati Principali -->
                                <div class="col-md-6 mb-3">
                                    <label for="nome" class="form-label">Nome Azienda <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($azienda['nome']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="business_categories" class="form-label">Categorie Attività <span class="text-danger">*</span></label>
                                    <!-- SISTEMA CATEGORIE IDENTICO AL TEMPLATE FUNZIONANTE -->
                                    <div class="form-group">
                                        <div id="business-categories-edit-<?php echo $azienda['id']; ?>" 
                                             data-business-categories
                                             data-placeholder="Seleziona le categorie della tua attività..."
                                             data-max-selections="3"
                                             data-allow-custom="true"
                                             data-required="true"
                                             data-preselected='<?php 
                                                // 🔍 DEBUG: Mostra cosa contiene realmente il database
                                                echo "<!-- DEBUG DB: business_categories = " . htmlspecialchars($azienda['business_categories'] ?? 'NULL') . " -->";
                                                echo "<!-- DEBUG DB: tipo_struttura = " . htmlspecialchars($azienda['tipo_struttura'] ?? 'NULL') . " -->";
                                                echo "<!-- DEBUG DB: servizi = " . htmlspecialchars($azienda['servizi'] ?? 'NULL') . " -->";
                                                
                                                // Carica categorie dal nuovo campo JSON business_categories
                                                $existing_categories = [];
                                                if (!empty($azienda['business_categories'])) {
                                                    // Se esiste il nuovo campo JSON, usalo
                                                    $categories_data = json_decode($azienda['business_categories'], true);
                                                    if (is_array($categories_data)) {
                                                        $existing_categories = $categories_data;
                                                    }
                                                } else {
                                                    // Fallback: combina tipo_struttura e servizi esistenti per compatibilità
                                                    if (!empty($azienda['tipo_struttura'])) {
                                                        $existing_categories[] = trim($azienda['tipo_struttura']);
                                                    }
                                                    if (!empty($azienda['servizi'])) {
                                                        $servizi_array = array_map('trim', explode(',', $azienda['servizi']));
                                                        $existing_categories = array_merge($existing_categories, $servizi_array);
                                                    }
                                                }
                                                
                                                $final_categories = array_unique(array_filter($existing_categories));
                                                echo "<!-- DEBUG FINAL: " . htmlspecialchars(json_encode($final_categories)) . " -->";
                                                echo htmlspecialchars(json_encode($final_categories));
                                             ?>'></div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="descrizione" class="form-label fw-semibold">Descrizione Attività <span class="text-danger">*</span></label>
                                    <textarea class="form-control form-control-lg" id="descrizione" name="descrizione" rows="5" 
                                              placeholder="Descrivi la tua attività, i servizi offerti e ciò che ti rende unico..." 
                                              required><?php echo htmlspecialchars($azienda['descrizione']); ?></textarea>
                                    <div class="form-text">Scrivi una descrizione accattivante per attirare i clienti</div>
                                </div>
                            </div>
                            </div>
                            
                            <!-- ===== SEZIONE 2: INFORMAZIONI DI CONTATTO ===== -->
                            <div class="form-section mb-5">
                                <div class="section-header mb-4">
                                    <h5 class="section-title"><i class="bi bi-geo-alt"></i> Informazioni di Contatto</h5>
                                    <p class="section-subtitle text-muted">Come i clienti possono raggiungerti</p>
                                </div>
                                
                                <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="indirizzo" class="form-label fw-semibold">Indirizzo Completo <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                        <input type="text" class="form-control" id="indirizzo" name="indirizzo" 
                                               placeholder="Via, numero civico, città" 
                                               value="<?php echo htmlspecialchars($azienda['indirizzo']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label fw-semibold">Telefono</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                        <input type="tel" class="form-control" id="telefono" name="telefono" 
                                               placeholder="+39 123 456 7890" 
                                               value="<?php echo htmlspecialchars($azienda['telefono']); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="info@tuaazienda.it" 
                                               value="<?php echo htmlspecialchars($azienda['email']); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="sito_web" class="form-label fw-semibold">Sito Web</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                        <input type="url" class="form-control" id="sito_web" name="sito_web" 
                                               placeholder="https://www.tuosito.it" 
                                               value="<?php echo htmlspecialchars($azienda['sito_web']); ?>">
                                    </div>
                                </div>
                            </div>
                            </div>
                            
                            <!-- ===== SEZIONE 3: SERVIZI E SPECIALITÀ ===== -->
            <div class="form-section mb-5">
                <div class="section-header mb-4">
                    <h5 class="section-title"><i class="bi bi-gear-fill"></i> Servizi e Specialità</h5>
                    <p class="section-subtitle text-muted">Seleziona i servizi che offri dalla nostra lista completa o aggiungine di personalizzati</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-12">
                        <?php
                        // Carica servizi esistenti dal database
                        $existing_services = [];
                        if (!empty($azienda['services_offered'])) {
                            // Nuovo campo JSON
                            $services_data = json_decode($azienda['services_offered'], true);
                            if (is_array($services_data)) {
                                $existing_services = $services_data;
                            }
                        } elseif (!empty($azienda['servizi'])) {
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
            </div>                </div>
                            </div>
            <!-- ===== SEZIONE 4: MEDIA E IMMAGINI AVANZATA ===== -->
            <?php 
            // Configurazione per sistema media avanzato
            $azienda_id = $azienda['id'];
            $context = 'edit';
            ?>
            <div class="form-section mb-5">
                <div class="section-header mb-4">
                    <h5 class="section-title"><i class="bi bi-images"></i> Media e Immagini</h5>
                    <p class="section-subtitle text-muted">Sistema avanzato per logo aziendale e galleria media</p>
                </div>

                <!-- Inclusione template sistema media avanzato -->
                <?php include __DIR__ . '/templates/advanced-media-section.php'; ?>
            </div>

                            <!-- PULSANTE SALVATAGGIO CON VALIDAZIONE -->
                            <div class="mt-4 text-center">
                                <div class="form-submit-section p-4 bg-light rounded">
                                    <button type="submit" class="btn btn-warning btn-lg px-4 py-3">
                                        <i class="bi bi-check-circle"></i> Salva Tutte le Modifiche
                                    </button>
                                    <div class="mt-2">
                                        <small class="form-text text-muted">
                                            ✅ Le modifiche verranno applicate immediatamente con normalizzazione automatica dei media
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <style>
                            .form-submit-section {
                                border: 2px dashed #ffc107;
                                transition: all 0.3s ease;
                            }
                            .form-submit-section:hover {
                                border-style: solid;
                                background-color: #fff3cd !important;
                            }
                            .btn-warning {
                                background-color: #ffc107;
                                border-color: #ffc107;
                                border-radius: 25px;
                                transition: all 0.3s ease;
                                font-weight: 600;
                            }
                            .btn-warning:hover {
                                background-color: #e0a800;
                                transform: translateY(-2px);
                                box-shadow: 0 6px 12px rgba(255,193,7,0.3);
                            }
                            </style>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- 🎯 ASSET NECESSARI PER SISTEMA CATEGORIE -->
<link rel="stylesheet" href="assets/css/business-categories.css">
<!-- Script business-categories.js già incluso in header.php -->

<script>
// 🚀 INIZIALIZZAZIONE ROBUSTA SISTEMA CATEGORIE
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Inizializzazione categorie modifica azienda...');
    
    // ✅ DESERIALIZZAZIONE CATEGORIE DAL DATABASE
    <?php
    $categorie_esistenti = [];
    if (isset($azienda['business_categories']) && !empty($azienda['business_categories'])) {
        $decoded_categories = json_decode($azienda['business_categories'], true);
        if (is_array($decoded_categories)) {
            $categorie_esistenti = $decoded_categories;
            echo "console.log('📦 Categorie dal DB:', " . json_encode($decoded_categories) . ");
";
        } else {
            echo "console.warn('⚠️ business_categories non è un array valido:', " . json_encode($azienda['business_categories']) . ");
";
        }
    } else {
        echo "console.log('ℹ️ Nessuna categoria esistente trovata');
";
    }
    ?>
    
    // Attendi che tutti gli script siano caricati
    setTimeout(function() {
        const containerId = 'business-categories-edit-<?php echo $azienda['id']; ?>';
        const container = document.getElementById(containerId);
        
        if (!container) {
            console.error('❌ Container categorie non trovato:', containerId);
            return;
        }
        
        console.log('✅ Container trovato:', containerId);
        
        // Verifica che la classe BusinessCategoriesSelector sia disponibile
        if (typeof BusinessCategoriesSelector === 'undefined') {
            console.error('❌ BusinessCategoriesSelector non disponibile');
            // 🔧 FALLBACK: Crea dropdown semplice con preselezione
            const categorieEsistenti = <?php echo json_encode($categorie_esistenti); ?>;
            let fallbackOptions = '';
            ['Pizzeria', 'Ristorante', 'Bar', 'Negozio', 'tavola_calda', 'fast_food'].forEach(cat => {
                const selected = categorieEsistenti.includes(cat) ? 'selected' : '';
                fallbackOptions += `<option value="${cat}" ${selected}>${cat}</option>`;
            });
            container.innerHTML = `
                <select class="form-control" name="business_categories_fallback" multiple>
                    ${fallbackOptions}
                </select>
                <small class="text-muted">Sistema semplificato - ${categorieEsistenti.length} categorie preselezionate</small>
            `;
            return;
        }
        
        // Configurazione dalle data attributes
        const options = {
            placeholder: container.dataset.placeholder || 'Seleziona categorie...',
            maxSelections: parseInt(container.dataset.maxSelections) || 3,
            allowCustom: container.dataset.allowCustom !== 'false',
            required: container.dataset.required === 'true',
            preselected: <?php echo json_encode($categorie_esistenti); ?>
        };
        
        // Parse preselected data
        try {
            if (container.dataset.preselected) {
                options.preselected = JSON.parse(container.dataset.preselected);
            }
        } catch (e) {
            console.warn('⚠️ Errore parsing preselected:', e);
        }
        
        console.log('🎯 Opzioni categorie:', options);
        
        // Crea istanza
        try {
            new BusinessCategoriesSelector(containerId, options);
            console.log('✅ Sistema categorie inizializzato con successo!');
        } catch (error) {
            console.error('❌ Errore inizializzazione categorie:', error);
            // Fallback in caso di errore
            container.innerHTML = '<div class="alert alert-warning">Errore caricamento sistema categorie. Ricarica la pagina.</div>';
        }
    }, 500); // Attesa per caricamento completo
});
</script>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
