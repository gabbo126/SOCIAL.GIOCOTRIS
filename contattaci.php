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
                        <i class="bi bi-envelope me-2"></i>
                        Contattaci
                    </h1>
                    
                    <p class="text-muted mb-4">
                        Hai domande o hai bisogno di assistenza? Siamo qui per aiutarti!
                    </p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="contact-info">
                                <h3 class="h4 mb-4">📍 Informazioni di Contatto</h3>
                                
                                <div class="contact-item-large mb-4">
                                    <div class="contact-icon">
                                        <i class="bi bi-envelope-fill text-primary"></i>
                                    </div>
                                    <div class="contact-details">
                                        <h5>Email</h5>
                                        <p class="mb-1"><strong>Supporto:</strong> <a href="mailto:info@socialgiocotris.it">info@socialgiocotris.it</a></p>
                                        <p class="mb-0"><strong>Tecnico:</strong> <a href="mailto:supporto@socialgiocotris.it">supporto@socialgiocotris.it</a></p>
                                    </div>
                                </div>
                                
                                <div class="contact-item-large mb-4">
                                    <div class="contact-icon">
                                        <i class="bi bi-telephone-fill text-success"></i>
                                    </div>
                                    <div class="contact-details">
                                        <h5>Telefono</h5>
                                        <p class="mb-1"><strong>Assistenza:</strong> <a href="tel:+39123456789">+39 123 456 789</a></p>
                                        <p class="mb-0"><small class="text-muted">Lun-Ven: 9:00 - 18:00</small></p>
                                    </div>
                                </div>
                                
                                <div class="contact-item-large mb-4">
                                    <div class="contact-icon">
                                        <i class="bi bi-geo-alt-fill text-danger"></i>
                                    </div>
                                    <div class="contact-details">
                                        <h5>Indirizzo</h5>
                                        <p class="mb-0">Territorio Locale<br>Italia</p>
                                    </div>
                                </div>
                                
                                <div class="contact-social mt-4">
                                    <h5>Seguici sui Social</h5>
                                    <div class="social-links-contact">
                                        <a href="#" class="btn btn-outline-primary btn-sm me-2">
                                            <i class="bi bi-facebook me-1"></i> Facebook
                                        </a>
                                        <a href="#" class="btn btn-outline-info btn-sm me-2">
                                            <i class="bi bi-instagram me-1"></i> Instagram
                                        </a>
                                        <a href="#" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-youtube me-1"></i> YouTube
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="contact-form">
                                <h3 class="h4 mb-4">💌 Inviaci un Messaggio</h3>
                                
                                <form action="#" method="POST" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="contactName" class="form-label">Nome Completo *</label>
                                        <input type="text" class="form-control" id="contactName" name="name" required>
                                        <div class="invalid-feedback">
                                            Inserisci il tuo nome completo.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="contactEmail" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="contactEmail" name="email" required>
                                        <div class="invalid-feedback">
                                            Inserisci un indirizzo email valido.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="contactPhone" class="form-label">Telefono</label>
                                        <input type="tel" class="form-control" id="contactPhone" name="phone">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="contactSubject" class="form-label">Oggetto *</label>
                                        <select class="form-select" id="contactSubject" name="subject" required>
                                            <option value="">Seleziona un oggetto...</option>
                                            <option value="support">Richiesta Supporto</option>
                                            <option value="technical">Problema Tecnico</option>
                                            <option value="billing">Domande su Pagamenti</option>
                                            <option value="feature">Richiesta Funzionalità</option>
                                            <option value="other">Altro</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Seleziona un oggetto.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="contactMessage" class="form-label">Messaggio *</label>
                                        <textarea class="form-control" id="contactMessage" name="message" rows="5" required></textarea>
                                        <div class="invalid-feedback">
                                            Scrivi il tuo messaggio.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="contactPrivacy" required>
                                            <label class="form-check-label" for="contactPrivacy">
                                                Accetto la <a href="<?php echo BASE_URL; ?>/privacy-policy.php" target="_blank">Privacy Policy</a> *
                                            </label>
                                            <div class="invalid-feedback">
                                                Devi accettare la Privacy Policy.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-send me-2"></i>
                                        Invia Messaggio
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="faq-section">
                                <h3 class="h4 mb-4">❓ Domande Frequenti</h3>
                                <div class="accordion" id="faqAccordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                                Come posso registrare la mia azienda?
                                            </button>
                                        </h2>
                                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Clicca su "Registra Azienda" nella homepage, compila il form con i dati della tua attività e scegli il piano più adatto alle tue esigenze.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                                Qual è la differenza tra Piano Base e Pro?
                                            </button>
                                        </h2>
                                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Il Piano Base permette fino a 3 media (foto e foto link), mentre il Piano Pro supporta fino a 5 media inclusi video e link YouTube.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                                Come posso modificare i dati della mia azienda?
                                            </button>
                                        </h2>
                                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Usa il token di modifica ricevuto via email durante la registrazione per accedere alla pagina di modifica dati.
                                            </div>
                                        </div>
                                    </div>
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
.contact-item-large {
    display: flex;
    align-items-start;
    gap: 1rem;
}

.contact-icon {
    font-size: 1.5rem;
    margin-top: 0.25rem;
}

.contact-details h5 {
    margin-bottom: 0.5rem;
    color: #333;
}

.social-links-contact .btn {
    margin-bottom: 0.5rem;
}
</style>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    event.preventDefault();
                    alert('✅ Messaggio inviato con successo! Ti risponderemo al più presto.');
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php require_once 'templates/footer.php'; ?>
