<?php 
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'templates/header.php';

// Recupera 3 aziende casuali da mostrare in vetrina
$stmt = $conn->prepare("SELECT id, nome, descrizione, logo_url, tipo_struttura FROM aziende ORDER BY RAND() LIMIT 3");
$stmt->execute();
$aziende_in_vetrina = $stmt->get_result();
?>

<header class="hero-section-modern text-center text-white position-relative">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">Scopri. Connetti. Cresci.</h1>
        <p class="lead mb-5">La vetrina digitale per le eccellenze del nostro territorio. <br>Trova le migliori attività commerciali, a un clic di distanza.</p>
        <div class="row justify-content-center mt-4">
            <div class="col-lg-8">
                <form action="aziende.php" method="GET" class="search-form-modern">
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control form-control-modern search-input-modern" name="search" placeholder="Cerca per nome, tipo o servizio..." aria-label="Cerca azienda" required>
                        <button class="btn btn-modern btn-primary-modern" type="submit">
                            <i class="bi bi-search me-2"></i>Cerca
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="mt-5">
            <p class="mb-3 text-white-50">oppure</p>
            <a href="aziende.php" class="btn btn-outline-modern btn-lg">Esplora tutte le aziende</a>
        </div>
    </div>
</header>

<section class="py-3">
    <div class="container">
        <div class="text-center mb-3">
            <h2 class="h4 fw-semibold">Come Funziona</h2>
            <p class="text-muted small mb-0">Tre semplici passi per entrare in contatto con le realtà locali.</p>
        </div>
        <div class="row text-center g-3">
            <div class="col-md-4">
                <div class="p-2">
                    <i class="bi bi-search fs-3 text-primary mb-2"></i>
                    <h5 class="fw-medium">Esplora</h5>
                    <p class="small mb-0">Naviga tra decine di attività locali.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-2">
                    <i class="bi bi-chat-dots fs-3 text-primary mb-2"></i>
                    <h5 class="fw-medium">Interagisci</h5>
                    <p class="small mb-0">Scopri i servizi e contatta le aziende.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-2">
                    <i class="bi bi-heart fs-3 text-primary mb-2"></i>
                    <h5 class="fw-medium">Supporta</h5>
                    <p class="small mb-0">Sostieni l'economia locale.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($aziende_in_vetrina->num_rows > 0): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Aziende in Vetrina</h2>
            <p class="text-muted">Una selezione delle nostre migliori attività.</p>
        </div>
        <div class="row g-4">
            <?php while($azienda = $aziende_in_vetrina->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm card-fade-in">
                        <img src="<?php echo !empty($azienda['logo_url']) ? htmlspecialchars($azienda['logo_url']) : 'assets/img/default-logo.png'; ?>" class="card-img-top" alt="Logo <?php echo htmlspecialchars($azienda['nome']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($azienda['nome']); ?></h5>
                            <p class="card-text text-muted"><small><?php echo htmlspecialchars($azienda['tipo_struttura']); ?></small></p>
                            <p class="card-text flex-grow-1"><?php echo truncate_text(htmlspecialchars($azienda['descrizione']), 100); ?></p>
                            <a href="azienda.php?id=<?php echo $azienda['id']; ?>" class="btn btn-primary mt-auto align-self-start">Scopri di più</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php 
$stmt->close();
$conn->close();
require_once 'templates/footer.php'; 
?>
