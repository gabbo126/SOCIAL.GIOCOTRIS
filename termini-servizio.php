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
                        <i class="bi bi-file-text me-2"></i>
                        Termini di Servizio
                    </h1>
                    
                    <p class="text-muted mb-4">
                        Ultimo aggiornamento: <?php echo date('d/m/Y'); ?>
                    </p>
                    
                    <div class="terms-content">
                        <h3 class="h4 mb-3">1. Accettazione dei Termini</h3>
                        <p>Utilizzando Social Gioco Tris, accetti questi Termini di Servizio. 
                        Se non accetti questi termini, ti preghiamo di non utilizzare il nostro servizio.</p>
                        
                        <h3 class="h4 mb-3 mt-4">2. Descrizione del Servizio</h3>
                        <p>Social Gioco Tris è una piattaforma digitale che permette alle aziende locali di:</p>
                        <ul>
                            <li>Creare una vetrina digitale per i propri servizi</li>
                            <li>Condividere informazioni, foto e video</li>
                            <li>Essere trovate facilmente dagli utenti</li>
                            <li>Gestire la propria presenza online</li>
                        </ul>
                        
                        <h3 class="h4 mb-3 mt-4">3. Piani di Abbonamento</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h5 class="card-title">🏷️ Piano Base</h5>
                                        <p class="card-text">Accesso limitato con fino a 3 media (foto e foto link).</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <h5 class="card-title">🌟 Piano Pro</h5>
                                        <p class="card-text">Accesso completo con fino a 5 media (foto, video, YouTube).</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h3 class="h4 mb-3 mt-4">4. Responsabilità dell'Utente</h3>
                        <p>Gli utenti si impegnano a:</p>
                        <ul>
                            <li>Fornire informazioni accurate e veritiere</li>
                            <li>Non pubblicare contenuti inappropriati, illegali o offensivi</li>
                            <li>Rispettare i diritti di copyright e proprietà intellettuale</li>
                            <li>Non utilizzare il servizio per attività fraudolente</li>
                            <li>Mantenere aggiornate le informazioni del profilo</li>
                        </ul>
                        
                        <h3 class="h4 mb-3 mt-4">5. Contenuti e Proprietà Intellettuale</h3>
                        <p>Mantieni la proprietà dei contenuti che pubblichi. Concedendoci una licenza limitata per 
                        visualizzare e distribuire i tuoi contenuti tramite la nostra piattaforma.</p>
                        
                        <h3 class="h4 mb-3 mt-4">6. Limitazione di Responsabilità</h3>
                        <p>Social Gioco Tris fornisce il servizio "così com'è". Non garantiamo che il servizio sia 
                        sempre disponibile, error-free o che soddisfi le tue aspettative specifiche.</p>
                        
                        <h3 class="h4 mb-3 mt-4">7. Modifiche ai Termini</h3>
                        <p>Ci riserviamo il diritto di modificare questi termini in qualsiasi momento. 
                        Le modifiche saranno comunicate tramite email o avviso sul sito.</p>
                        
                        <h3 class="h4 mb-3 mt-4">8. Risoluzione delle Controversie</h3>
                        <p>Eventuali controversie saranno risolte secondo la legge italiana e nella giurisdizione competente.</p>
                        
                        <div class="alert alert-warning mt-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Importante:</strong> Questi termini costituiscono un accordo legalmente vincolante 
                            tra te e Social Gioco Tris.
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
