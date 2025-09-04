<?php
/**
 * Template per la singola card di un'azienda.
 * 
 * @var array $azienda Dati dell'azienda passati dal ciclo che include questo file.
 * @var mysqli $conn Oggetto della connessione al database (se necessario, ma di solito i dati sono già pronti).
 */

// Gestisce il logo con un placeholder di default
$logo_url = (!empty($azienda['logo_url']) && file_exists(__DIR__ . '/../' . $azienda['logo_url'])) 
            ? BASE_URL . '/' . $azienda['logo_url'] 
            : 'https://via.placeholder.com/300x200.png?text=' . urlencode($azienda['nome']);

// Tronca la descrizione per l'anteprima
$descrizione_breve = strlen($azienda['descrizione']) > 100 
                    ? substr($azienda['descrizione'], 0, 100) . '...' 
                    : $azienda['descrizione'];
?>

<div class="azienda-card">
    <a href="azienda.php?id=<?php echo htmlspecialchars($azienda['id']); ?>" class="card-link">
        <div class="card-image-container">
            <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo di <?php echo htmlspecialchars($azienda['nome']); ?>" class="card-image">
        </div>
        <div class="card-content">
            <h3 class="card-title"><?php echo htmlspecialchars($azienda['nome']); ?></h3>
            <p class="card-description"><?php echo htmlspecialchars($descrizione_breve); ?></p>
            <span class="card-cta">Scopri di più</span>
        </div>
    </a>
</div>
