<?php 
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/menu-stats.php';

// Carica statistiche reali per il menu
$menu_stats = getMenuStatistics();
$total_companies = formatCount($menu_stats['total_companies']);
$ristorazione_count = formatCount(getCategoryCount('ristorazione'));
$servizi_count = formatCount(getCategoryCount('servizi'));
$commercio_count = formatCount(getCategoryCount('commercio'));
$live_events = formatCount($menu_stats['live_events']);

// Determina quale pagina è attualmente attiva
$current_page = basename($_SERVER['PHP_SELF']);

// Imposta le variabili per le classi active
$home_active = ($current_page == 'index.php') ? 'active' : '';
$aziende_active = ($current_page == 'aziende.php' || $current_page == 'azienda.php') ? 'active' : '';
$live_active = ($current_page == 'live.php') ? 'active' : '';
$admin_active = (strpos($current_page, 'admin') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <!-- Viewport ottimizzato per mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="theme-color" content="#64748b">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Social Gioco Tris - Il portale delle attività locali</title>

    <!-- Bootstrap 5 CSS con fallback per mobile -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" 
          crossorigin="anonymous"
          onerror="this.onerror=null;this.href='<?php echo BASE_URL; ?>/assets/css/bootstrap.min.css';">
    <!-- Bootstrap Icons con fallback -->
    <link rel="stylesheet" 
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" 
          crossorigin="anonymous"
          onerror="this.onerror=null;this.href='<?php echo BASE_URL; ?>/assets/css/bootstrap-icons.css';">
    
    <!-- CSS Debug per mobile - CSS inline di emergenza -->
    <style>
    /* CSS di emergenza se i file esterni non si caricano */
    @media (max-width: 768px) {
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .container { width: 95%; margin: 0 auto; padding: 0 15px; }
        nav { background: #fff; padding: 10px 0; border-bottom: 1px solid #ddd; }
        .btn { padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin: 10px 0; }
        .row { display: flex; flex-wrap: wrap; }
        .col, .col-md-4, .col-lg-4 { flex: 1; min-width: 280px; }
    }
    </style>
    <!-- Google Fonts - Inter Modern Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Hierarchy: Modern Design System prima, poi Legacy, infine Mobile Fixes -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/modern-design-system.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/modern-design-system.css'); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/mobile-fixes.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/mobile-fixes.css'); ?>">
    
    <!-- Sistema Servizi Business Moderno -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/business-services.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/business-services.css'); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/business-categories.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/business-categories.css'); ?>">
    
    <!-- JavaScript modules con loading ottimizzato -->
    <script src="<?php echo BASE_URL; ?>/assets/js/navbar-scroll.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/navbar-scroll.js'); ?>" defer></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/hamburger-menu-fixed.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/hamburger-menu-fixed.js'); ?>"></script>
    <?php if($current_page !== 'index.php'): ?>
    <script src="<?php echo BASE_URL; ?>/assets/js/search-overlay.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/search-overlay.js'); ?>" defer></script>
    <?php endif; ?>
    
    <!-- Sistema Business Moderno (Categorie e Servizi) -->
    <script src="<?php echo BASE_URL; ?>/assets/js/business-categories.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/business-categories.js'); ?>" defer></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/business-services.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/business-services.js'); ?>" defer></script>
</head>
<body>

<nav class="navbar-modern navbar-expand-lg sticky-top">
    <div class="container">
        <!-- Hamburger Menu Button (sostituisce brand-icon) -->
        <button class="brand-hamburger-btn" id="hamburgerMenuBtn" type="button" aria-label="Apri menu">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
        
        <!-- Brand Text Centered -->
        <div class="navbar-brand-modern">
            <div class="brand-text">
                <span class="brand-name">Social Gioco Tris</span>
                <span class="brand-tagline">Portale Locale</span>
            </div>
        </div>
        
        <!-- Desktop Navigation -->
        <div class="navbar-nav-container d-none d-lg-flex">
            <ul class="navbar-nav-modern ms-auto">
                <li class="nav-item-modern">
                    <a class="nav-link-modern <?php echo $home_active; ?>" <?php if($home_active) echo 'aria-current="page"'; ?> href="<?php echo BASE_URL; ?>/index.php">
                        <i class="bi bi-house nav-icon"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="nav-item-modern">
                    <a class="nav-link-modern <?php echo $aziende_active; ?>" <?php if($aziende_active) echo 'aria-current="page"'; ?> href="<?php echo BASE_URL; ?>/aziende.php">
                        <i class="bi bi-building nav-icon"></i>
                        <span>Aziende</span>
                    </a>
                </li>
                <li class="nav-item-modern">
                    <a class="nav-link-modern <?php echo $live_active; ?>" <?php if($live_active) echo 'aria-current="page"'; ?> href="<?php echo BASE_URL; ?>/live.php">
                        <i class="bi bi-broadcast nav-icon"></i>
                        <span>Live</span>
                        <span class="nav-badge">New</span>
                    </a>
                </li>
                <li class="nav-item-modern">
                    <a class="nav-link-modern admin-link <?php echo $admin_active; ?>" <?php if($admin_active) echo 'aria-current="page"'; ?> href="<?php echo BASE_URL; ?>/admin/login.php">
                        <i class="bi bi-gear nav-icon"></i>
                        <span>Admin</span>
                    </a>
                </li>
            </ul>
        </div>

    </div>
</nav>

<!-- Hamburger Menu Overlay Sobrio -->
<div class="hamburger-menu-overlay" id="hamburgerMenuOverlay">
    <div class="hamburger-menu-container">
        <!-- Header del Menu -->
        <div class="hamburger-menu-header">
            <div class="menu-title">
                <span class="menu-title-text">MENU</span>
            </div>
            <button class="hamburger-close-btn" id="hamburgerCloseBtn" aria-label="Chiudi menu">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <!-- Navigazione Principale -->
        <div class="hamburger-main-nav">
            <a class="hamburger-nav-item <?php echo $home_active ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php">
                <span class="nav-item-text">Home</span>
            </a>
            <a class="hamburger-nav-item <?php echo $aziende_active ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/aziende.php">
                <span class="nav-item-text">Aziende</span>
                <span class="nav-item-count"><?php echo $total_companies; ?></span>
            </a>
            <a class="hamburger-nav-item <?php echo $live_active ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/live.php">
                <span class="nav-item-text">Live</span>
                <span class="nav-item-count"><?php echo $live_events; ?></span>
                <span class="nav-badge-new">NEW</span>
            </a>
            <a class="hamburger-nav-item <?php echo $admin_active ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/login.php">
                <span class="nav-item-text">Admin</span>
            </a>
        </div>
        
        <!-- Categorie -->
        <div class="hamburger-categories">
            <div class="categories-title">CATEGORIE</div>
            <div class="category-list">
                <a class="category-item" href="<?php echo BASE_URL; ?>/aziende.php?categoria=ristorazione">
                    <span class="category-name">Ristorazione</span>
                    <span class="category-count"><?php echo $ristorazione_count; ?></span>
                </a>
                <a class="category-item" href="<?php echo BASE_URL; ?>/aziende.php?categoria=servizi">
                    <span class="category-name">Servizi</span>
                    <span class="category-count"><?php echo $servizi_count; ?></span>
                </a>
                <a class="category-item" href="<?php echo BASE_URL; ?>/aziende.php?categoria=commercio">
                    <span class="category-name">Commercio</span>
                    <span class="category-count"><?php echo $commercio_count; ?></span>
                </a>
                <a class="category-item" href="<?php echo BASE_URL; ?>/aziende.php">
                    <span class="category-name">Tutte le Aziende</span>
                    <span class="category-count"><?php echo $total_companies; ?></span>
                </a>
            </div>
        </div>
        <!-- Footer del Menu -->
        <div class="hamburger-menu-footer">
            <div class="menu-footer-text">
                <span class="footer-brand">Social Gioco Tris</span>
                <span class="footer-tagline">Il tuo portale locale</span>
            </div>
        </div>
    </div>
</div>

<?php if($current_page !== 'index.php'): ?>
<!-- Barra Ricerca Integrata (Solo fuori dalla Home) -->
<div class="search-bar-integrated" id="searchBarIntegrated">
    <div class="container">
        <form action="<?php echo BASE_URL; ?>/aziende.php" method="GET" class="search-form-integrated">
            <div class="search-input-wrapper-integrated">
                <i class="bi bi-search search-icon-integrated"></i>
                <input type="text" name="search" class="search-input-integrated" placeholder="Cerca aziende, categorie, località..." autocomplete="off" id="searchInputIntegrated">
                <button type="button" class="search-clear-btn" id="searchClearBtn" aria-label="Cancella ricerca" style="display: none;">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
            <button type="submit" class="search-submit-btn-integrated">
                <i class="bi bi-search"></i>
                <span>Cerca</span>
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- JavaScript per Hamburger Menu e Search Bar Mobile -->
<script src="<?php echo BASE_URL; ?>/assets/js/hamburger-menu.js?v=<?php echo time(); ?>"></script>

<main class="fade-in">

