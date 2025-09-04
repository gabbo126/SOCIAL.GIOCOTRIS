<?php
$page_title = 'Gestione Token';
require_once 'partials/admin_header.php';

// --- LOGICA DI VISUALIZZAZIONE ---

// Messaggi di stato dalla sessione
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
$last_token = $_SESSION['last_token'] ?? null;

// Pulisci i messaggi dalla sessione dopo averli letti
unset($_SESSION['success_message'], $_SESSION['error_message'], $_SESSION['last_token']);

// Recupera tutte le aziende per il menu a tendina
$aziende_result = $conn->query("SELECT id, nome FROM aziende ORDER BY nome ASC");

// Recupera tutti i token per la visualizzazione
$conn->query("UPDATE tokens SET status = 'scaduto' WHERE data_scadenza < NOW() AND status = 'attivo'");
$tokens_result = $conn->query("SELECT t.*, a.nome as nome_azienda FROM tokens t LEFT JOIN aziende a ON t.id_azienda = a.id ORDER BY t.data_creazione DESC");

?>

<h1 class="h2 mb-4">Gestione Token</h1>

<!-- Messaggi di stato -->
<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Card del token appena generato -->
<?php if ($last_token): ?>
<div class="card shadow-lg mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Token Generato con Successo</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Tipo Token:</strong> <?php echo htmlspecialchars($last_token['type']); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Pacchetto:</strong>
                    <?php if ($last_token['tipo_pacchetto'] === 'foto_video'): ?>
                        <span class="badge bg-purple text-white">
                            <i class="bi bi-camera-video me-1"></i>🎥 Foto + Video
                        </span>
                    <?php else: ?>
                        <span class="badge bg-info text-white">
                            <i class="bi bi-image me-1"></i>📷 Solo Foto
                        </span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <p><strong>Link:</strong></p>
        <div class="input-group mb-3">
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($last_token['link']); ?>" id="tokenLink" readonly>
            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('#tokenLink')">Copia</button>
        </div>
        
        <div class="alert alert-light border-start border-4 <?php echo $last_token['tipo_pacchetto'] === 'foto_video' ? 'border-purple' : 'border-info'; ?> mb-2">
            <small class="text-muted">
                <strong>Funzionalità abilitate:</strong><br>
                <?php if ($last_token['tipo_pacchetto'] === 'foto_video'): ?>
                    ✅ Upload logo e 3 immagini<br>
                    ✅ Upload 2 video (max 100MB ciascuno)
                <?php else: ?>
                    ✅ Upload logo e 3 immagini<br>
                    ❌ Upload video non consentito
                <?php endif; ?>
            </small>
        </div>
        
        <p class="card-text"><small class="text-muted">Generato il: <?php echo date('d/m/Y H:i', strtotime($last_token['data_creazione'])); ?> | Scade il: <?php echo date('d/m/Y H:i', strtotime($last_token['data_scadenza'])); ?></small></p>
    </div>
</div>
<?php endif; ?>

<!-- Sezione Generazione Token -->
<div class="row mb-4">
    <!-- Genera Token Creazione -->
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Genera Token Creazione</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Crea un nuovo token che permette di registrare un'azienda nel sistema.</p>
                <form action="token_manager.php" method="POST">
                    <input type="hidden" name="action" value="generate_token">
                    <input type="hidden" name="type" value="creation">
                    
                    <!-- Selezione Tipo Pacchetto -->
                    <div class="mb-3">
                        <label class="form-label">Tipo di Pacchetto</label>
                        <div class="btn-group-vertical w-100" role="group" aria-label="Tipo pacchetto">
                            <input type="radio" class="btn-check" name="tipo_pacchetto" id="creation_foto" value="foto" checked>
                            <label class="btn btn-outline-primary text-start package-option" for="creation_foto">
                                <i class="bi bi-image me-2"></i>
                                <strong>📷 Solo Foto</strong>
                                <small class="d-block text-muted mt-1">Permette solo l'upload di immagini (logo + 3 foto)</small>
                            </label>
                            
                            <input type="radio" class="btn-check" name="tipo_pacchetto" id="creation_foto_video" value="foto_video">
                            <label class="btn btn-outline-primary text-start package-option" for="creation_foto_video">
                                <i class="bi bi-camera-video me-2"></i>
                                <strong>🎥 Foto + Video</strong>
                                <small class="d-block text-muted mt-1">Permette upload di immagini e video (logo + 3 foto + 2 video)</small>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="validitaCreation" class="form-label">Validità (ore)</label>
                        <input type="number" class="form-control" id="validitaCreation" name="validita" value="24" min="1" max="720" required>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i> Genera Token
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Genera Token Modifica -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Genera Token Modifica</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Crea un nuovo token che permette di modificare i dati di un'azienda esistente.</p>
                <form action="token_manager.php" method="POST">
                    <input type="hidden" name="action" value="generate_token">
                    <input type="hidden" name="type" value="modification">
                    <div class="mb-3">
                        <label for="aziendaSelect" class="form-label">Azienda</label>
                        <select class="form-select" id="aziendaSelect" name="id_azienda" required>
                            <option value="">Seleziona un'azienda...</option>
                            <?php while ($azienda = $aziende_result->fetch_assoc()): ?>
                            <option value="<?php echo $azienda['id']; ?>"><?php echo htmlspecialchars($azienda['nome']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- Selezione Tipo Pacchetto -->
                    <div class="mb-3">
                        <label class="form-label">Tipo di Pacchetto</label>
                        <div class="btn-group-vertical w-100" role="group" aria-label="Tipo pacchetto">
                            <input type="radio" class="btn-check" name="tipo_pacchetto" id="modification_foto" value="foto" checked>
                            <label class="btn btn-outline-secondary text-start package-option" for="modification_foto">
                                <i class="bi bi-image me-2"></i>
                                <strong>📷 Solo Foto</strong>
                                <small class="d-block text-muted mt-1">Permette solo l'upload di immagini (logo + 3 foto)</small>
                            </label>
                            
                            <input type="radio" class="btn-check" name="tipo_pacchetto" id="modification_foto_video" value="foto_video">
                            <label class="btn btn-outline-secondary text-start package-option" for="modification_foto_video">
                                <i class="bi bi-camera-video me-2"></i>
                                <strong>🎥 Foto + Video</strong>
                                <small class="d-block text-muted mt-1">Permette upload di immagini e video (logo + 3 foto + 2 video)</small>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="validitaModification" class="form-label">Validità (ore)</label>
                        <input type="number" class="form-control" id="validitaModification" name="validita" value="24" min="1" max="720" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i> Genera Token
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">
    <h2 class="h4 mb-4 pb-2 border-bottom">Storico dei Token</h2>
    
<?php
// Include la funzione per generare le sezioni token
require_once 'dashboard_token_sections.php';

// Raggruppiamo i token per tipo e stato
$creation_tokens = [];
$modification_tokens = [];
$has_creation_tokens = false;
$has_modification_tokens = false;

$tokens_result->data_seek(0);
while ($token = $tokens_result->fetch_assoc()) {
    if ($token['type'] == 'creazione') {
        $creation_tokens[$token['status']][] = $token;
        $has_creation_tokens = true;
    } else if ($token['type'] == 'modifica') {
        $modification_tokens[$token['status']][] = $token;
        $has_modification_tokens = true;
    }
}

// Generiamo le sezioni dei token usando la nuova funzione
generateTokenSection('Creazione', $creation_tokens, 'success', 'plus-circle');
generateTokenSection('Modifica', $modification_tokens, 'primary', 'pencil-square');
?>

</div>

<script src="../assets/js/admin-dashboard.js"></script>

<?php 
$conn->close();
require_once 'partials/admin_footer.php'; 
?>
