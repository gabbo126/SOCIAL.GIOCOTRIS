<?php 
require_once 'config.php';
require_once 'templates/header.php'; 
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <h1 class="h2 mb-4 text-primary">
                        <i class="bi bi-lightbulb me-2"></i>
                        Come Funziona Social Gioco Tris
                    </h1>
                    
                    <p class="lead text-muted mb-5">
                        Scopri come portare la tua azienda online in pochi semplici passi
                    </p>
                    
                    <!-- Step by Step Process -->
                    <div class="steps-container">
                        <div class="row mb-5">
                            <div class="col-md-4 mb-4">
                                <div class="step-card text-center">
                                    <div class="step-number bg-primary text-white">1</div>
                                    <div class="step-icon text-primary mb-3">
                                        <i class="bi bi-person-plus-fill" style="font-size: 3rem;"></i>
                                    </div>
                                    <h4>Registrazione</h4>
                                    <p>Crea il profilo della tua azienda con informazioni complete, logo e media.</p>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="step-card text-center">
                                    <div class="step-number bg-success text-white">2</div>
                                    <div class="step-icon text-success mb-3">
                                        <i class="bi bi-credit-card-fill" style="font-size: 3rem;"></i>
                                    </div>
                                    <h4>Scegli Piano</h4>
                                    <p>Seleziona il piano più adatto: Base per iniziare o Pro per funzionalità avanzate.</p>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="step-card text-center">
                                    <div class="step-number bg-info text-white">3</div>
                                    <div class="step-icon text-info mb-3">
                                        <i class="bi bi-rocket-takeoff-fill" style="font-size: 3rem;"></i>
                                    </div>
                                    <h4>Vai Online</h4>
                                    <p>La tua azienda è subito visibile online e raggiungibile da tutti gli utenti.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Features Comparison -->
                    <div class="plans-comparison mb-5">
                        <h2 class="h3 mb-4 text-center">I Nostri Piani</h2>
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100 border-success shadow-sm">
                                    <div class="card-header bg-success text-white text-center">
                                        <h3 class="h4 mb-0">🏷️ Piano Base</h3>
                                        <p class="mb-0">Perfetto per iniziare</p>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="price-display text-center mb-4">
                                            <span class="h2 text-success">€9.99</span>
                                            <span class="text-muted">/mese</span>
                                        </div>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">✅ <strong>Logo aziendale</strong></li>
                                            <li class="mb-2">✅ <strong>Informazioni complete</strong></li>
                                            <li class="mb-2">✅ <strong>Fino a 3 media</strong></li>
                                            <li class="mb-2">✅ <strong>Foto e foto link</strong></li>
                                            <li class="mb-2">✅ <strong>Presenza online garantita</strong></li>
                                            <li class="mb-2">✅ <strong>Supporto via email</strong></li>
                                        </ul>
                                    </div>
                                    <div class="card-footer text-center">
                                        <a href="<?php echo BASE_URL; ?>/register_company.php" class="btn btn-success w-100">
                                            Inizia con Base
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100 border-info shadow-sm">
                                    <div class="card-header bg-info text-white text-center">
                                        <h3 class="h4 mb-0">🌟 Piano Pro</h3>
                                        <p class="mb-0">Funzionalità complete</p>
                                        <span class="badge bg-warning text-dark">Più Popolare</span>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="price-display text-center mb-4">
                                            <span class="h2 text-info">€19.99</span>
                                            <span class="text-muted">/mese</span>
                                        </div>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">✅ <strong>Tutto del Piano Base</strong></li>
                                            <li class="mb-2">✅ <strong>Fino a 5 media</strong></li>
                                            <li class="mb-2">✅ <strong>Video caricabili</strong></li>
                                            <li class="mb-2">✅ <strong>Link YouTube integrati</strong></li>
                                            <li class="mb-2">✅ <strong>Priorità nei risultati</strong></li>
                                            <li class="mb-2">✅ <strong>Supporto prioritario</strong></li>
                                        </ul>
                                    </div>
                                    <div class="card-footer text-center">
                                        <a href="<?php echo BASE_URL; ?>/register_company.php" class="btn btn-info w-100">
                                            Scegli Pro
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detailed Process -->
                    <div class="detailed-process mb-5">
                        <h2 class="h3 mb-4">Il Processo Completo</h2>
                        
                        <div class="accordion" id="processAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#step1Details">
                                        📋 Step 1: Preparazione Dati
                                    </button>
                                </h2>
                                <div id="step1Details" class="accordion-collapse collapse show" data-bs-parent="#processAccordion">
                                    <div class="accordion-body">
                                        <h5>Cosa ti serve per iniziare:</h5>
                                        <ul>
                                            <li><strong>Informazioni aziendali:</strong> Nome, descrizione, indirizzo, telefono, email</li>
                                            <li><strong>Logo:</strong> File immagine del tuo logo (PNG, JPG, WEBP)</li>
                                            <li><strong>Media:</strong> Foto dei prodotti/servizi, video promozionali (Piano Pro)</li>
                                            <li><strong>Metodo di pagamento:</strong> Account PayPal o carta di credito</li>
                                        </ul>
                                        <div class="alert alert-info">
                                            💡 <strong>Suggerimento:</strong> Prepara tutto in anticipo per completare la registrazione in 5 minuti!
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step2Details">
                                        💳 Step 2: Registrazione e Pagamento
                                    </button>
                                </h2>
                                <div id="step2Details" class="accordion-collapse collapse" data-bs-parent="#processAccordion">
                                    <div class="accordion-body">
                                        <h5>Processo di registrazione:</h5>
                                        <ol>
                                            <li>Clicca su <strong>"Registra Azienda"</strong> dalla homepage</li>
                                            <li>Compila il form con i dati della tua attività</li>
                                            <li>Carica logo e media</li>
                                            <li>Seleziona il piano desiderato</li>
                                            <li>Procedi al pagamento sicuro con PayPal</li>
                                            <li>Ricevi email di conferma con token di modifica</li>
                                        </ol>
                                        <div class="alert alert-success">
                                            🔒 <strong>Sicurezza garantita:</strong> Tutti i pagamenti sono protetti da crittografia SSL
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step3Details">
                                        🚀 Step 3: La Tua Azienda Online
                                    </button>
                                </h2>
                                <div id="step3Details" class="accordion-collapse collapse" data-bs-parent="#processAccordion">
                                    <div class="accordion-body">
                                        <h5>Dopo la registrazione:</h5>
                                        <ul>
                                            <li>✅ La tua azienda è <strong>immediatamente visibile</strong> online</li>
                                            <li>✅ Appare nei <strong>risultati di ricerca</strong> del sito</li>
                                            <li>✅ Gli utenti possono <strong>contattarti direttamente</strong></li>
                                            <li>✅ Ricevi il <strong>token di modifica</strong> via email per aggiornamenti futuri</li>
                                        </ul>
                                        
                                        <h5 class="mt-4">Gestione continua:</h5>
                                        <ul>
                                            <li>🔄 <strong>Modifiche quando vuoi:</strong> Usa il token per aggiornare i dati</li>
                                            <li>📈 <strong>Upgrade facile:</strong> Passa al Piano Pro in qualsiasi momento</li>
                                            <li>💬 <strong>Supporto dedicato:</strong> Ti aiutiamo sempre</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ -->
                    <div class="faq-section mb-5">
                        <h2 class="h3 mb-4">❓ Domande Frequenti</h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="faq-item mb-4">
                                    <h5 class="text-primary">Quanto tempo serve per andare online?</h5>
                                    <p>La tua azienda è online <strong>immediatamente</strong> dopo il completamento del pagamento, di solito entro 2-3 minuti dalla registrazione.</p>
                                </div>
                                
                                <div class="faq-item mb-4">
                                    <h5 class="text-primary">Posso modificare i dati dopo la registrazione?</h5>
                                    <p>Sì! Usa il <strong>token di modifica</strong> che ricevi via email per aggiornare informazioni, foto e media quando vuoi.</p>
                                </div>
                                
                                <div class="faq-item mb-4">
                                    <h5 class="text-primary">Cosa succede se cancello l'abbonamento?</h5>
                                    <p>Puoi cancellare in qualsiasi momento. I tuoi dati rimangono online fino alla fine del periodo pagato.</p>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="faq-item mb-4">
                                    <h5 class="text-primary">Posso cambiare piano in futuro?</h5>
                                    <p><strong>Upgrade immediato</strong> a Piano Pro. Downgrade possibile dal ciclo successivo (potrebbero esserci limitazioni sui media).</p>
                                </div>
                                
                                <div class="faq-item mb-4">
                                    <h5 class="text-primary">Che supporto ricevo?</h5>
                                    <p>Piano Base: supporto via email. Piano Pro: supporto prioritario con risposta garantita in 24 ore.</p>
                                </div>
                                
                                <div class="faq-item mb-4">
                                    <h5 class="text-primary">È sicuro il pagamento?</h5>
                                    <p>Assolutamente! Utilizziamo <strong>PayPal</strong> con crittografia SSL. Non memorizziamo mai i dati di pagamento.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- CTA Section -->
                    <div class="cta-section text-center p-5 bg-gradient rounded">
                        <h2 class="h3 mb-3">Pronto a Portare la Tua Azienda Online?</h2>
                        <p class="lead mb-4">Unisciti alle centinaia di aziende che hanno scelto Social Gioco Tris</p>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <a href="<?php echo BASE_URL; ?>/register_company.php" class="btn btn-primary btn-lg me-md-2">
                                        <i class="bi bi-rocket-takeoff me-2"></i>
                                        Inizia Subito
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/aziende.php" class="btn btn-outline-primary btn-lg">
                                        <i class="bi bi-eye me-2"></i>
                                        Vedi Esempi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-5">
                        <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Torna alla Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.step-card {
    padding: 2rem 1rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    position: relative;
}

.step-card:hover {
    transform: translateY(-5px);
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
}

.bg-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.faq-item h5 {
    margin-bottom: 0.5rem;
}

.price-display {
    border: 2px dashed #dee2e6;
    padding: 1rem;
    border-radius: 8px;
    background: #f8f9fa;
}
</style>

<?php require_once 'templates/footer.php'; ?>
