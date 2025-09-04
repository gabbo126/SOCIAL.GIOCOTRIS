<?php
// Avvia la sessione per tutte le pagine admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Includi il file di configurazione principale
// Gestisce il percorso sia per le pagine nella root di /admin/ sia in /admin/partials/
$config_path = file_exists('../config.php') ? '../config.php' : '../../config.php';
require_once $config_path;

// Solo per le pagine protette, non per login.php
if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Area Admin - Social Gioco Tris</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Admin CSS -->
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: 250px; padding: 1rem; 
            background-color: #343a40; color: white; 
        }
        .sidebar a { color: rgba(255,255,255,.8); text-decoration: none; display: block; padding: .5rem 1rem; }
        .sidebar a:hover, .sidebar a.active { color: white; background-color: #495057; border-radius: .25rem; }
        .content { margin-left: 250px; padding: 2rem; }
    </style>
</head>
<body>

<?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
<div class="sidebar">
    <h4 class="mb-4">Admin Panel</h4>
    <nav class="nav flex-column">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a>
        <a class="nav-link" href="logout.php">Logout</a>
    </nav>
</div>
<div class="content">
    <header class="admin-header">
        <div class="container">
            <h1><a href="dashboard.php">Admin Panel</a></h1>
            <nav>
                <?php if (isset($_SESSION['admin_logged_in'])): ?>
                    <span>Benvenuto, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</span>
                    <a href="logout.php">Logout</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="admin-main">
        <div class="container">
<?php endif; ?>
