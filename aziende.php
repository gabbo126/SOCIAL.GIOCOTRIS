<?php 
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'templates/header.php';

// Gestione della ricerca e del filtro
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$initial = isset($_GET['initial']) ? trim($_GET['initial']) : '';
$page_title_text = 'Le Nostre Aziende';
$page_subtitle_text = 'Scopri le attività commerciali che animano il nostro territorio.';

$sql = "SELECT id, nome, descrizione, logo_url, business_categories, servizi, telefono, indirizzo FROM aziende";

// La ricerca ha la priorità sul filtro per iniziale
if (!empty($search_query)) {
    $page_title_text = 'Risultati per: &quot;' . htmlspecialchars($search_query) . '&quot;';
    $page_subtitle_text = 'Ecco le aziende che corrispondono alla tua ricerca.';
    
    $sql .= " WHERE nome LIKE ? OR business_categories LIKE ? OR descrizione LIKE ? OR servizi LIKE ? ORDER BY nome ASC";
    $stmt = $conn->prepare($sql);
    $search_param = '%' . $search_query . '%';
    $stmt->bind_param('ssss', $search_param, $search_param, $search_param, $search_param);

} elseif (!empty($initial) && preg_match('/^[A-Z]$/', $initial)) {
    $sql .= " WHERE nome LIKE ? ORDER BY nome ASC";
    $stmt = $conn->prepare($sql);
    $search_param = $initial . '%';
    $stmt->bind_param('s', $search_param);

} else {
    $sql .= " ORDER BY nome ASC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-5">
    <!-- Header Section Moderna -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold text-primary-modern"><?php echo $page_title_text; ?></h1>
        <p class="lead text-secondary-modern"><?php echo $page_subtitle_text; ?></p>
    </div>

    <!-- Filtro alfabetico moderno -->
    <div class="alphabet-filter-modern mb-5">
        <div class="filter-container">
            <?php
            foreach (range('A', 'Z') as $letter) {
                $activeClass = ($initial == $letter) ? 'active' : '';
                echo "<a href='aziende.php?initial=$letter' class='filter-btn $activeClass'>$letter</a>";
            }
            $allActiveClass = (empty($initial)) ? 'active' : '';
            echo "<a href='aziende.php' class='filter-btn filter-btn-all $allActiveClass'>Tutte</a>";
            ?>
        </div>
    </div>

    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($azienda = $result->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="company-card-modern h-100">
                    <?php if (!empty($azienda['logo_url'])): ?>
                        <div class="company-logo-container">
                            <img src="<?php echo htmlspecialchars($azienda['logo_url']); ?>" class="company-logo" alt="Logo <?php echo htmlspecialchars($azienda['nome']); ?>">
                        </div>
                    <?php else: ?>
                        <div class="company-logo-placeholder">
                            <i class="bi bi-building"></i>
                        </div>
                    <?php endif; ?>
                    <div class="company-card-body">
                        <div class="company-header">
                            <h5 class="company-name mb-2"><?php echo htmlspecialchars($azienda['nome']); ?></h5>
                            
                            <?php
                            // IMPLEMENTAZIONE BUSINESS CATEGORIES - Cards Lista Aziende
                            $business_categories = [];
                            if (!empty($azienda['business_categories'])) {
                                $categories_data = json_decode($azienda['business_categories'], true);
                                if (is_array($categories_data)) {
                                    $business_categories = $categories_data;
                                }
                            }
                            
                            if (!empty($business_categories)): ?>
                                <div class="company-categories-cards mb-3">
                                    <?php 
                                    // Cards: massimo 2 categorie per layout compatto
                                    $displayed_categories = array_slice($business_categories, 0, 2);
                                    $remaining_count = count($business_categories) - 2;
                                    
                                    foreach ($displayed_categories as $index => $category): 
                                        // Colori brand distintivi per tipologie (stesso sistema del dettaglio)
                                        $badge_colors = [
                                            'Ristorante' => 'bg-primary', 'Bar' => 'bg-success', 'Pizzeria' => 'bg-warning text-dark',
                                            'Hotel' => 'bg-info', 'Negozio' => 'bg-secondary', 'Servizi' => 'bg-dark',
                                            'Alimentari' => 'bg-success', 'Abbigliamento' => 'bg-purple', 'Bellezza' => 'bg-pink',
                                            'Sport' => 'bg-orange', 'Tecnologia' => 'bg-cyan', 'Automotive' => 'bg-gray'
                                        ];
                                        
                                        // Determina colore badge basato sulla categoria
                                        $badge_class = 'bg-primary';
                                        foreach ($badge_colors as $key => $color) {
                                            if (stripos($category, $key) !== false) {
                                                $badge_class = $color;
                                                break;
                                            }
                                        }
                                    ?>
                                        <span class="badge <?php echo $badge_class; ?> fs-7 fw-normal px-2 py-1 me-1 mb-1">
                                            <i class="bi bi-tag-fill me-1"></i><?php echo htmlspecialchars($category); ?>
                                        </span>
                                    <?php endforeach; ?>
                                    
                                    <?php if ($remaining_count > 0): ?>
                                        <span class="badge bg-light text-dark fs-7 fw-normal px-2 py-1" title="<?php echo implode(', ', array_slice($business_categories, 2)); ?>">
                                            <i class="bi bi-plus"></i><?php echo $remaining_count; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($azienda['descrizione'])): ?>
                            <p class="company-description"><?php echo substr(htmlspecialchars($azienda['descrizione']), 0, 120) . '...'; ?></p>
                        <?php endif; ?>
                        <div class="company-contact-info">
                            <?php if (!empty($azienda['telefono'])): ?>
                                <div class="contact-item">
                                    <i class="bi bi-telephone"></i>
                                    <span><?php echo htmlspecialchars($azienda['telefono']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($azienda['indirizzo'])): ?>
                                <div class="contact-item">
                                    <i class="bi bi-geo-alt"></i>
                                    <span><?php echo htmlspecialchars($azienda['indirizzo']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="company-actions">
                            <a href="azienda.php?id=<?php echo $azienda['id']; ?>" class="btn-view-details">Vedi Dettagli</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    Nessuna azienda trovata. Prova a modificare i criteri di ricerca o a esplorare tutte le categorie.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
