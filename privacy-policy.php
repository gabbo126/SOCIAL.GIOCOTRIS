<?php 
require_once 'config.php';
require_once 'templates/header.php'; 
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <h1 class="h2 mb-4 text-primary">
                        <i class="bi bi-shield-check me-2"></i>
                        Privacy Policy
                    </h1>
                    
                    <p class="text-muted mb-4">
                        Ultimo aggiornamento: <?php echo date('d/m/Y'); ?>
                    </p>
                    
                    <div class="privacy-content">
                        <h3 class="h4 mb-3">1. Informazioni che raccogliamo</h3>
                        <p>Social Gioco Tris raccoglie le seguenti informazioni:</p>
                        <ul>
                            <li><strong>Dati dell'azienda:</strong> Nome, descrizione, indirizzo, telefono, email, sito web</li>
                            <li><strong>Media:</strong> Logo, foto, video e link condivisi</li>
                            <li><strong>Dati di navigazione:</strong> Indirizzo IP, browser, dispositivo utilizzato</li>
                            <li><strong>Cookie tecnici:</strong> Per il corretto funzionamento del sito</li>
                        </ul>
                        
                        <h3 class="h4 mb-3 mt-4">2. Come utilizziamo i tuoi dati</h3>
                        <p>Utilizziamo i dati raccolti per:</p>
                        <ul>
                            <li>Fornire il servizio di vetrina digitale per le aziende locali</li>
                            <li>Migliorare l'esperienza utente del sito</li>
                            <li>Comunicazioni relative al servizio</li>
                            <li>Rispetto degli obblighi legali</li>
                        </ul>
                        
                        <h3 class="h4 mb-3 mt-4">3. Condivisione dei dati</h3>
                        <p>I dati aziendali sono condivisi pubblicamente come parte del servizio di vetrina digitale. 
                        Non condividiamo dati personali con terze parti senza consenso, eccetto quando richiesto dalla legge.</p>
                        
                        <h3 class="h4 mb-3 mt-4">4. I tuoi diritti</h3>
                        <p>Hai il diritto di:</p>
                        <ul>
                            <li>Accedere ai tuoi dati personali</li>
                            <li>Rettificare informazioni inesatte</li>
                            <li>Richiedere la cancellazione dei dati</li>
                            <li>Limitare il trattamento</li>
                            <li>Portabilità dei dati</li>
                        </ul>
                        
                        <h3 class="h4 mb-3 mt-4">5. Sicurezza</h3>
                        <p>Implementiamo misure di sicurezza tecniche e organizzative appropriate per proteggere 
                        i tuoi dati personali da accessi non autorizzati, alterazioni, divulgazioni o distruzioni.</p>
                        
                        <h3 class="h4 mb-3 mt-4">6. Contatti</h3>
                        <p>Per qualsiasi domanda riguardante questa Privacy Policy, contattaci:</p>
                        <ul>
                            <li><strong>Email:</strong> privacy@socialgiocotris.it</li>
                            <li><strong>Indirizzo:</strong> Territorio Locale, Italia</li>
                        </ul>
                        
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Nota:</strong> Questa Privacy Policy può essere aggiornata periodicamente. 
                            Ti invitiamo a consultarla regolarmente.
                        </div>
                    </div>
                    
                    <div class="text-center mt-5">
                        <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Torna alla Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'templates/footer.php'; ?>
