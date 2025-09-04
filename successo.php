<?php
require_once 'config.php';
$page_title = 'Registrazione Completata';
require_once 'templates/header.php';

$type = $_GET['type'] ?? 'registrazione';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h1 class="card-title text-success mb-3">Registrazione Completata!</h1>
                    
                    <p class="card-text lead mb-4">
                        La tua azienda è stata registrata con successo nel nostro sistema.
                    </p>
                    
                    <div class="alert alert-success border-start border-4 border-success">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <div>
                                <strong>Cosa succede ora?</strong><br>
                                I tuoi dati sono stati salvati e saranno presto disponibili nella piattaforma.
                                Riceverai ulteriori informazioni via email.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="index.php" class="btn btn-primary btn-lg me-2">
                            <i class="bi bi-house-fill me-1"></i> Torna alla Home
                        </a>
                        <a href="aziende.php" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-building me-1"></i> Vedi Aziende
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.text-success {
    color: #198754 !important;
}

.border-success {
    border-color: #198754 !important;
}
</style>

<?php require_once 'templates/footer.php'; ?>
