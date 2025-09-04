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
                        <i class="bi bi-question-circle me-2"></i>
                        Centro Assistenza
                    </h1>
                    
                    <p class="lead text-muted mb-5">
                        Trova rapidamente le risposte alle tue domande più frequenti
                    </p>
                    
                    <!-- Search Bar -->
                    <div class="search-bar mb-5">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="Cerca nella documentazione..." id="helpSearch">
                        </div>
                    </div>
                    
                    <!-- Quick Help Cards -->
                    <div class="row mb-5">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="bi bi-building text-primary" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-3">Registrazione Azienda</h5>
                                    <p class="card-text">Come registrare la tua attività sulla piattaforma</p>
                                    <a href="#registrazione" class="btn btn-outline-primary">Scopri di più</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="bi bi-credit-card text-success" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-3">Piani & Pagamenti</h5>
                                    <p class="card-text">Informazioni su piani, prezzi e pagamenti</p>
                                    <a href="#pagamenti" class="btn btn-outline-success">Scopri di più</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <i class="bi bi-gear text-info" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-3">Gestione Profilo</h5>
                                    <p class="card-text">Modifica e aggiorna i dati della tua azienda</p>
                                    <a href="#gestione" class="btn btn-outline-info">Scopri di più</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ Sections -->
                    <div class="help-sections">
                        
                        <!-- Registrazione -->
                        <section id="registrazione" class="mb-5">
                            <h2 class="h3 mb-4 text-primary">
                                <i class="bi bi-building me-2"></i>
                                Registrazione Azienda
                            </h2>
                            
                            <div class="accordion mb-4" id="registrazioneAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#reg1">
                                            Come registro la mia azienda?
                                        </button>
                                    </h2>
                                    <div id="reg1" class="accordion-collapse collapse show" data-bs-parent="#registrazioneAccordion">
                                        <div class="accordion-body">
                                            <ol>
                                                <li>Vai alla homepage e clicca su <strong>"Registra Azienda"</strong></li>
                                                <li>Compila tutti i campi obbligatori (nome, email, telefono, ecc.)</li>
                                                <li>Scegli il tipo di pacchetto (Piano Base o Pro)</li>
                                                <li>Carica logo e media se disponibili</li>
                                                <li>Procedi con il pagamento tramite PayPal</li>
                                                <li>Riceverai email di conferma con token di modifica</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#reg2">
                                            Che documenti servono per la registrazione?
                                        </button>
                                    </h2>
                                    <div id="reg2" class="accordion-collapse collapse" data-bs-parent="#registrazioneAccordion">
                                        <div class="accordion-body">
                                            Per la registrazione servono solo:
                                            <ul>
                                                <li><strong>Dati aziendali:</strong> Nome, descrizione, indirizzo</li>
                                                <li><strong>Contatti:</strong> Email, telefono, sito web (opzionale)</li>
                                                <li><strong>Media:</strong> Logo aziendale, foto dei prodotti/servizi</li>
                                            </ul>
                                            Non sono richiesti documenti ufficiali o partite IVA.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#reg3">
                                            Ho perso il token di modifica, cosa faccio?
                                        </button>
                                    </h2>
                                    <div id="reg3" class="accordion-collapse collapse" data-bs-parent="#registrazioneAccordion">
                                        <div class="accordion-body">
                                            Se hai perso il token di modifica:
                                            <ol>
                                                <li>Controlla la cartella spam della tua email</li>
                                                <li>Cerca email da <strong>info@socialgiocotris.it</strong></li>
                                                <li>Se non trovi l'email, <a href="<?php echo BASE_URL; ?>/contattaci.php">contattaci</a></li>
                                                <li>Fornisci nome azienda e email usata per la registrazione</li>
                                                <li>Ti invieremo un nuovo token entro 24 ore</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                        
                        <!-- Pagamenti -->
                        <section id="pagamenti" class="mb-5">
                            <h2 class="h3 mb-4 text-success">
                                <i class="bi bi-credit-card me-2"></i>
                                Piani & Pagamenti
                            </h2>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <h5 class="card-title">🏷️ Piano Base</h5>
                                            <ul class="list-unstyled">
                                                <li>✅ Logo aziendale</li>
                                                <li>✅ Fino a 3 media</li>
                                                <li>✅ Foto e foto link</li>
                                                <li>✅ Informazioni complete</li>
                                            </ul>
                                            <p class="text-success"><strong>€9.99/mese</strong></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-body">
                                            <h5 class="card-title">🌟 Piano Pro</h5>
                                            <ul class="list-unstyled">
                                                <li>✅ Tutto del Piano Base</li>
                                                <li>✅ Fino a 5 media</li>
                                                <li>✅ Video e YouTube</li>
                                                <li>✅ Supporto prioritario</li>
                                            </ul>
                                            <p class="text-info"><strong>€19.99/mese</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion" id="pagamentiAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#pag1">
                                            Quali metodi di pagamento accettate?
                                        </button>
                                    </h2>
                                    <div id="pag1" class="accordion-collapse collapse show" data-bs-parent="#pagamentiAccordion">
                                        <div class="accordion-body">
                                            Accettiamo pagamenti tramite <strong>PayPal</strong>:
                                            <ul>
                                                <li>💳 Carte di credito/debito</li>
                                                <li>🏦 Conto corrente</li>
                                                <li>💰 Saldo PayPal</li>
                                                <li>📱 PayPal mobile</li>
                                            </ul>
                                            Tutti i pagamenti sono sicuri e crittografati SSL.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pag2">
                                            Posso cambiare piano in qualsiasi momento?
                                        </button>
                                    </h2>
                                    <div id="pag2" class="accordion-collapse collapse" data-bs-parent="#pagamentiAccordion">
                                        <div class="accordion-body">
                                            <strong>Upgrade (Base → Pro):</strong> Immediato, paghi la differenza proporzionale.<br>
                                            <strong>Downgrade (Pro → Base):</strong> Effettivo dal prossimo ciclo di fatturazione. 
                                            Se hai più di 3 media, dovrai rimuoverne alcuni prima del downgrade.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pag3">
                                            Quanto costa modificare i dati azienda?
                                        </button>
                                    </h2>
                                    <div id="pag3" class="accordion-collapse collapse" data-bs-parent="#pagamentiAccordion">
                                        <div class="accordion-body">
                                            Le modifiche ai dati azienda hanno un costo una tantum:
                                            <ul>
                                                <li><strong>Modifiche base:</strong> €5.00 (nome, descrizione, contatti)</li>
                                                <li><strong>Aggiunta/modifica media:</strong> +€1.00 per ogni media</li>
                                            </ul>
                                            Il pagamento è sicuro tramite PayPal.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                        
                        <!-- Gestione -->
                        <section id="gestione" class="mb-5">
                            <h2 class="h3 mb-4 text-info">
                                <i class="bi bi-gear me-2"></i>
                                Gestione Profilo
                            </h2>
                            
                            <div class="accordion" id="gestioneAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#gest1">
                                            Come modifico i dati della mia azienda?
                                        </button>
                                    </h2>
                                    <div id="gest1" class="accordion-collapse collapse show" data-bs-parent="#gestioneAccordion">
                                        <div class="accordion-body">
                                            Per modificare i dati della tua azienda:
                                            <ol>
                                                <li>Usa il <strong>token di modifica</strong> ricevuto via email</li>
                                                <li>Vai su <code><?php echo BASE_URL; ?>/modifica_azienda.php</code></li>
                                                <li>Inserisci il token nel form</li>
                                                <li>Modifica i dati desiderati</li>
                                                <li>Procedi con il pagamento per salvare le modifiche</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gest2">
                                            Posso caricare video nel Piano Base?
                                        </button>
                                    </h2>
                                    <div id="gest2" class="accordion-collapse collapse" data-bs-parent="#gestioneAccordion">
                                        <div class="accordion-body">
                                            <strong>No</strong>, il Piano Base supporta solo:
                                            <ul>
                                                <li>📷 Foto caricate direttamente</li>
                                                <li>🔗 Link a immagini esterne</li>
                                            </ul>
                                            Per caricare video e link YouTube devi passare al <strong>Piano Pro</strong>.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gest3">
                                            Come cancello la mia azienda dal sito?
                                        </button>
                                    </h2>
                                    <div id="gest3" class="accordion-collapse collapse" data-bs-parent="#gestioneAccordion">
                                        <div class="accordion-body">
                                            Per cancellare la tua azienda:
                                            <ol>
                                                <li><a href="<?php echo BASE_URL; ?>/contattaci.php">Contattaci</a> tramite il form</li>
                                                <li>Specifica nell'oggetto: "Richiesta Cancellazione"</li>
                                                <li>Includi nome azienda e email di registrazione</li>
                                                <li>Confermeremo la cancellazione entro 48 ore</li>
                                            </ol>
                                            <div class="alert alert-warning">
                                                <strong>Attenzione:</strong> La cancellazione è definitiva e non reversibile.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    
                    <!-- Contact Support -->
                    <div class="contact-support mt-5 p-4 bg-light rounded">
                        <h3 class="h4 mb-3">❓ Non hai trovato quello che cercavi?</h3>
                        <p>Il nostro team di supporto è qui per aiutarti!</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="<?php echo BASE_URL; ?>/contattaci.php" class="btn btn-primary w-100">
                                    <i class="bi bi-envelope me-2"></i>
                                    Contatta il Supporto
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="mailto:info@socialgiocotris.it" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-envelope-at me-2"></i>
                                    Email Diretta
                                </a>
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

<script>
// Simple search functionality
document.getElementById('helpSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const sections = document.querySelectorAll('.accordion-item');
    
    sections.forEach(section => {
        const text = section.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            section.style.display = 'block';
        } else {
            section.style.display = searchTerm === '' ? 'block' : 'none';
        }
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>
