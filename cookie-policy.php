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
                        <i class="bi bi-cookie me-2"></i>
                        Cookie Policy
                    </h1>
                    
                    <p class="text-muted mb-4">
                        Ultimo aggiornamento: <?php echo date('d/m/Y'); ?>
                    </p>
                    
                    <div class="cookie-content">
                        <h3 class="h4 mb-3">Cosa sono i Cookie?</h3>
                        <p>I cookie sono piccoli file di testo che vengono memorizzati sul tuo dispositivo quando visiti 
                        un sito web. Ci aiutano a fornire un'esperienza migliore e più personalizzata.</p>
                        
                        <h3 class="h4 mb-3 mt-4">Cookie che utilizziamo</h3>
                        
                        <div class="cookie-category mb-4">
                            <h5 class="text-success">🔧 Cookie Tecnici (Necessari)</h5>
                            <p>Questi cookie sono essenziali per il corretto funzionamento del sito:</p>
                            <ul>
                                <li><strong>Sessione PHP:</strong> Mantiene la tua sessione di navigazione</li>
                                <li><strong>Token di sicurezza:</strong> Protegge da attacchi CSRF</li>
                                <li><strong>Preferenze language:</strong> Ricorda le tue impostazioni</li>
                            </ul>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Questi cookie non richiedono consenso perché necessari per il funzionamento del sito.
                            </div>
                        </div>
                        
                        <div class="cookie-category mb-4">
                            <h5 class="text-warning">📊 Cookie Analitici (Opzionali)</h5>
                            <p>Utilizziamo questi cookie per capire come interagisci con il nostro sito:</p>
                            <ul>
                                <li><strong>Google Analytics:</strong> Statistiche anonime di utilizzo</li>
                                <li><strong>Pagine più visitate:</strong> Migliorare i contenuti</li>
                                <li><strong>Tempo di permanenza:</strong> Ottimizzare l'esperienza</li>
                            </ul>
                        </div>
                        
                        <div class="cookie-category mb-4">
                            <h5 class="text-info">💳 Cookie di Terze Parti</h5>
                            <p>Quando utilizzi i servizi di pagamento:</p>
                            <ul>
                                <li><strong>PayPal:</strong> Per elaborare i pagamenti sicuri</li>
                                <li><strong>Stripe:</strong> Backup per transazioni (se attivo)</li>
                            </ul>
                        </div>
                        
                        <h3 class="h4 mb-3 mt-4">Gestione dei Cookie</h3>
                        <p>Puoi gestire i tuoi cookie attraverso:</p>
                        <ul>
                            <li><strong>Impostazioni del browser:</strong> Blocca o elimina cookie specifici</li>
                            <li><strong>Preferenze del sito:</strong> Gestisci consensi per categoria</li>
                            <li><strong>Opt-out:</strong> Disabilita tracking analytics</li>
                        </ul>
                        
                        <div class="cookie-controls mt-4 p-4 bg-light rounded">
                            <h5>🎛️ Gestisci le tue preferenze</h5>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" checked disabled id="cookiesTechnical">
                                <label class="form-check-label" for="cookiesTechnical">
                                    <strong>Cookie Tecnici</strong> (Necessari - sempre attivi)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cookiesAnalytics">
                                <label class="form-check-label" for="cookiesAnalytics">
                                    <strong>Cookie Analitici</strong> (Opzionali)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cookiesMarketing">
                                <label class="form-check-label" for="cookiesMarketing">
                                    <strong>Cookie Marketing</strong> (Opzionali)
                                </label>
                            </div>
                            <button class="btn btn-primary mt-3" onclick="saveCookiePreferences()">
                                <i class="bi bi-check-circle me-2"></i>
                                Salva Preferenze
                            </button>
                        </div>
                        
                        <h3 class="h4 mb-3 mt-4">Disabilitazione Cookie</h3>
                        <p>Se disabiliti tutti i cookie, alcune funzionalità del sito potrebbero non essere disponibili:</p>
                        <ul>
                            <li>Login e gestione sessioni</li>
                            <li>Moduli di registrazione e modifica</li>
                            <li>Preferenze personalizzate</li>
                        </ul>
                        
                        <div class="alert alert-warning mt-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Nota importante:</strong> Questa policy può essere aggiornata. 
                            Controlla periodicamente per eventuali modifiche.
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

<script>
function saveCookiePreferences() {
    const analytics = document.getElementById('cookiesAnalytics').checked;
    const marketing = document.getElementById('cookiesMarketing').checked;
    
    // Save preferences (example implementation)
    localStorage.setItem('cookiePreferences', JSON.stringify({
        technical: true, // Always required
        analytics: analytics,
        marketing: marketing,
        timestamp: new Date().getTime()
    }));
    
    alert('✅ Preferenze salvate con successo!');
}

// Load existing preferences
window.addEventListener('DOMContentLoaded', function() {
    const preferences = localStorage.getItem('cookiePreferences');
    if (preferences) {
        const prefs = JSON.parse(preferences);
        document.getElementById('cookiesAnalytics').checked = prefs.analytics || false;
        document.getElementById('cookiesMarketing').checked = prefs.marketing || false;
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
