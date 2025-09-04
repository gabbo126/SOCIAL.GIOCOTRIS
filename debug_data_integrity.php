<?php
/**
 * 🔍 SCRIPT DEBUG DATA INTEGRITY
 * Verifica consistenza dati CREATE vs UPDATE aziende
 */

require_once 'config.php';
require_once 'includes/db.php';

$debug_results = [];

// Header HTML
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Debug Data Integrity - SOCIAL.GIOCOTRIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .debug-card { border-left: 4px solid #007bff; }
        .success-card { border-left: 4px solid #28a745; }
        .warning-card { border-left: 4px solid #ffc107; }
        .error-card { border-left: 4px solid #dc3545; }
        .code-block { background: #f8f9fa; border-radius: 4px; padding: 15px; font-family: 'Courier New', monospace; }
    </style>
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center mb-4">🔍 Debug Data Integrity</h1>
            <p class="text-center text-muted">Verifica consistenza dati CREATE vs UPDATE aziende</p>
        </div>
    </div>

<?php

// 🔍 TEST 1: Verifica separazione logo/galleria
echo '<div class="row mb-4"><div class="col-12"><div class="card debug-card">';
echo '<div class="card-header"><h5><i class="bi bi-image"></i> TEST 1: Separazione Logo/Galleria</h5></div>';
echo '<div class="card-body">';

try {
    $stmt = $conn->prepare("SELECT id, nome, logo_url, foto1_url, foto2_url, foto3_url, media_json FROM aziende WHERE logo_url IS NOT NULL AND media_json IS NOT NULL LIMIT 5");
    $stmt->execute();
    $companies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $contaminated = 0;
    $clean = 0;
    
    foreach ($companies as $company) {
        $media_array = [];
        if ($company['media_json']) {
            $media_array = json_decode($company['media_json'], true);
        }
        
        $logo_in_gallery = false;
        if (is_array($media_array)) {
            foreach ($media_array as $media) {
                if (isset($media['url']) && $media['url'] === $company['logo_url']) {
                    $logo_in_gallery = true;
                    break;
                }
            }
        }
        
        if ($logo_in_gallery) {
            $contaminated++;
            echo '<div class="alert alert-danger">❌ <strong>' . htmlspecialchars($company['nome']) . '</strong>: Logo contaminato in galleria!</div>';
        } else {
            $clean++;
            echo '<div class="alert alert-success">✅ <strong>' . htmlspecialchars($company['nome']) . '</strong>: Separazione corretta</div>';
        }
    }
    
    echo '<div class="mt-3"><strong>Risultato:</strong> ' . $clean . ' pulite, ' . $contaminated . ' contaminate</div>';
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Errore: ' . $e->getMessage() . '</div>';
}

echo '</div></div></div></div>';

// 🔍 TEST 2: Verifica preservazione categorie
echo '<div class="row mb-4"><div class="col-12"><div class="card debug-card">';
echo '<div class="card-header"><h5><i class="bi bi-tags"></i> TEST 2: Preservazione Categorie</h5></div>';
echo '<div class="card-body">';

try {
    $stmt = $conn->prepare("SELECT id, nome, business_categories FROM aziende WHERE business_categories IS NOT NULL AND business_categories != '' AND business_categories != '[]' LIMIT 10");
    $stmt->execute();
    $companies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $with_categories = 0;
    $without_categories = 0;
    
    foreach ($companies as $company) {
        $categories = json_decode($company['business_categories'], true);
        
        if (is_array($categories) && !empty($categories)) {
            $with_categories++;
            echo '<div class="alert alert-success">✅ <strong>' . htmlspecialchars($company['nome']) . '</strong>: ' . count($categories) . ' categorie</div>';
        } else {
            $without_categories++;
            echo '<div class="alert alert-warning">⚠️ <strong>' . htmlspecialchars($company['nome']) . '</strong>: Categorie vuote o malformate</div>';
        }
    }
    
    echo '<div class="mt-3"><strong>Risultato:</strong> ' . $with_categories . ' con categorie, ' . $without_categories . ' senza</div>';
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Errore: ' . $e->getMessage() . '</div>';
}

echo '</div></div></div></div>';

// 🔍 TEST 3: Verifica struttura database
echo '<div class="row mb-4"><div class="col-12"><div class="card debug-card">';
echo '<div class="card-header"><h5><i class="bi bi-database"></i> TEST 3: Struttura Database</h5></div>';
echo '<div class="card-body">';

try {
    $columns = ['business_categories', 'services_offered', 'media_json', 'logo_url'];
    
    foreach ($columns as $column) {
        $stmt = $conn->query("SHOW COLUMNS FROM aziende LIKE '$column'");
        if ($stmt && $stmt->num_rows > 0) {
            echo '<div class="alert alert-success">✅ Colonna <code>' . $column . '</code> presente</div>';
        } else {
            echo '<div class="alert alert-danger">❌ Colonna <code>' . $column . '</code> mancante</div>';
        }
    }
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Errore: ' . $e->getMessage() . '</div>';
}

echo '</div></div></div></div>';

// 🔍 TEST 4: Verifica media deserializzazione
echo '<div class="row mb-4"><div class="col-12"><div class="card debug-card">';
echo '<div class="card-header"><h5><i class="bi bi-file-code"></i> TEST 4: Media Deserializzazione</h5></div>';
echo '<div class="card-body">';

try {
    $stmt = $conn->prepare("SELECT id, nome, media_json FROM aziende WHERE media_json IS NOT NULL AND media_json != '' LIMIT 3");
    $stmt->execute();
    $companies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    foreach ($companies as $company) {
        $media_data = json_decode($company['media_json'], true);
        
        if (is_array($media_data)) {
            echo '<div class="alert alert-success">✅ <strong>' . htmlspecialchars($company['nome']) . '</strong>: ' . count($media_data) . ' media deserializzati</div>';
            echo '<div class="code-block">' . htmlspecialchars(json_encode($media_data, JSON_PRETTY_PRINT)) . '</div>';
        } else {
            echo '<div class="alert alert-warning">⚠️ <strong>' . htmlspecialchars($company['nome']) . '</strong>: Media JSON malformato</div>';
        }
    }
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Errore: ' . $e->getMessage() . '</div>';
}

echo '</div></div></div></div>';

// 📊 STATISTICHE FINALI
echo '<div class="row"><div class="col-12"><div class="card success-card">';
echo '<div class="card-header"><h5><i class="bi bi-graph-up"></i> Statistiche Sistema</h5></div>';
echo '<div class="card-body">';

try {
    $total_companies = $conn->query("SELECT COUNT(*) as total FROM aziende")->fetch_assoc()['total'];
    $with_logo = $conn->query("SELECT COUNT(*) as total FROM aziende WHERE logo_url IS NOT NULL")->fetch_assoc()['total'];
    $with_media = $conn->query("SELECT COUNT(*) as total FROM aziende WHERE media_json IS NOT NULL")->fetch_assoc()['total'];
    $with_categories = $conn->query("SELECT COUNT(*) as total FROM aziende WHERE business_categories IS NOT NULL AND business_categories != '' AND business_categories != '[]'")->fetch_assoc()['total'];
    
    echo '<div class="row text-center">';
    echo '<div class="col-md-3"><div class="card"><div class="card-body"><h3>' . $total_companies . '</h3><p>Aziende Totali</p></div></div></div>';
    echo '<div class="col-md-3"><div class="card"><div class="card-body"><h3>' . $with_logo . '</h3><p>Con Logo</p></div></div></div>';
    echo '<div class="col-md-3"><div class="card"><div class="card-body"><h3>' . $with_media . '</h3><p>Con Media</p></div></div></div>';
    echo '<div class="col-md-3"><div class="card"><div class="card-body"><h3>' . $with_categories . '</h3><p>Con Categorie</p></div></div></div>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Errore statistiche: ' . $e->getMessage() . '</div>';
}

echo '</div></div></div></div>';

?>

    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="index.php" class="btn btn-primary">← Torna alla Home</a>
            <button onclick="location.reload()" class="btn btn-secondary">🔄 Aggiorna Test</button>
        </div>
    </div>

</div>

</body>
</html>
