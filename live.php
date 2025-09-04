<?php 
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'templates/header.php';

// Ottieni l'ora corrente del server
$current_time = date('H:i');
$current_hour = (int)date('H');
$current_minute = (int)date('i');

// Determina lo stato della diretta in base all'orario
$live_status = '';
$is_live = false;
$is_pre_live = false;
$is_post_live = false;

// Dalle 19:00 alle 20:00 → Diretta in corso
if (($current_hour == 19) || ($current_hour == 19 && $current_minute >= 0 && $current_minute <= 59)) {
    $live_status = 'live';
    $is_live = true;
}
// Dalle 00:00 alle 18:59 → Countdown alla prossima diretta
elseif ($current_hour >= 0 && $current_hour < 19) {
    $live_status = 'pre_live';
    $is_pre_live = true;
}
// Dalle 20:00 alle 23:59 → Diretta terminata
else {
    $live_status = 'post_live';
    $is_post_live = true;
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm rounded">
                <div class="card-body text-center p-4">
                    
                    <?php if ($is_live): ?>
                        <!-- CASO 1: DIRETTA IN CORSO (19:00-20:00) -->
                        <h2 class="mb-4">🎥 LIVE STREAMING</h2>
                        <p class="text-muted mb-4">La diretta è in corso! Segui il nostro live streaming.</p>
                        
                        <div class="ratio ratio-16x9 mb-4">
                            <!-- Embed YouTube (canale fittizio Google Developers) -->
                            <iframe 
                                src="https://www.youtube.com/embed/live_stream?channel=UC_x5XG1OV2P6uZZ5FSM9Ttw" 
                                title="YouTube live stream" 
                                allowfullscreen>
                            </iframe>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Siamo in diretta ogni giorno dalle 19:00 alle 20:00!
                        </div>
                    
                    <?php elseif ($is_pre_live): ?>
                        <!-- CASO 2: COUNTDOWN ALLA PROSSIMA DIRETTA (00:00-18:59) -->
                        <h2 class="mb-4">⏳ La prossima live inizierà tra:</h2>
                        
                        <div class="countdown-container my-5">
                            <div class="row g-3 justify-content-center">
                                <div class="col-3 col-md-2">
                                    <div class="card bg-light">
                                        <div class="card-body p-2 p-md-3">
                                            <div id="countdown-hours" class="display-4 fw-bold text-primary">00</div>
                                            <div class="small text-muted">ore</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-3 col-md-2">
                                    <div class="card bg-light">
                                        <div class="card-body p-2 p-md-3">
                                            <div id="countdown-minutes" class="display-4 fw-bold text-primary">00</div>
                                            <div class="small text-muted">minuti</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-3 col-md-2">
                                    <div class="card bg-light">
                                        <div class="card-body p-2 p-md-3">
                                            <div id="countdown-seconds" class="display-4 fw-bold text-primary">00</div>
                                            <div class="small text-muted">secondi</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-muted">
                            <i class="bi bi-calendar-event me-2"></i>
                            Appuntamento alle 19:00 per la nostra diretta quotidiana!
                        </p>
                        
                    <?php else: ?>
                        <!-- CASO 3: DIRETTA TERMINATA (20:00-23:59) -->
                        <h2 class="mb-4">✅ La diretta di oggi è terminata</h2>
                        
                        <div class="my-5">
                            <i class="bi bi-calendar-check text-success display-1"></i>
                            <p class="lead mt-4">Grazie per averci seguito!</p>
                        </div>
                        
                        <div class="alert alert-success">
                            <i class="bi bi-alarm me-2"></i>
                            Torna domani alle 19:00 per una nuova diretta.
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script per il countdown (solo se siamo in pre-live) -->
<?php if ($is_pre_live): ?>
<script>
    // Funzione per calcolare il countdown fino alle 19:00 di oggi
    function updateCountdown() {
        // Ottieni la data e ora corrente
        const now = new Date();
        
        // Crea la data target per oggi alle 19:00
        const targetTime = new Date();
        targetTime.setHours(19, 0, 0, 0);
        
        // Se sono già passate le 19:00, imposta il target a domani
        if (now > targetTime) {
            targetTime.setDate(targetTime.getDate() + 1);
        }
        
        // Calcola la differenza in millisecondi
        const diff = targetTime - now;
        
        // Converti in ore, minuti e secondi
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        // Aggiorna gli elementi HTML
        document.getElementById('countdown-hours').textContent = hours.toString().padStart(2, '0');
        document.getElementById('countdown-minutes').textContent = minutes.toString().padStart(2, '0');
        document.getElementById('countdown-seconds').textContent = seconds.toString().padStart(2, '0');
        
        // Se il countdown è finito, ricarica la pagina
        if (diff <= 0) {
            location.reload();
        }
    }
    
    // Aggiorna il countdown ogni secondo
    updateCountdown();
    setInterval(updateCountdown, 1000);
</script>
<?php endif; ?>

<?php require_once 'templates/footer.php'; ?>
