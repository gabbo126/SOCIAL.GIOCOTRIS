<?php
require_once 'config.php';
require_once 'includes/db.php';

// Crea token di test valido per registrazione azienda
$test_token = 'test_media_fix_' . time();
$tipo_pacchetto = 'foto_video'; // Token B con 5 media per test completo
$data_scadenza = date('Y-m-d H:i:s', strtotime('+1 hour'));

$query = "INSERT INTO tokens (token, type, tipo_pacchetto, status, data_scadenza) VALUES (?, 'creazione', ?, 'attivo', ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('sss', $test_token, $tipo_pacchetto, $data_scadenza);

if ($stmt->execute()) {
    echo "<!DOCTYPE html><html><head><title>Token Test Creato</title>";
    echo "<style>body{font-family:Arial;margin:20px;text-align:center;}</style></head><body>";
    echo "<div style='background:#d4edda;border:1px solid #c3e6cb;padding:20px;border-radius:8px;display:inline-block;'>";
    echo "<h2>✅ Token Test Creato con Successo!</h2>";
    echo "<p><strong>Token:</strong> {$test_token}</p>";
    echo "<p><strong>Tipo Pacchetto:</strong> {$tipo_pacchetto} (5 media)</p>";
    echo "<p><strong>Scadenza:</strong> {$data_scadenza}</p>";
    
    $test_url = "http://localhost/SOCIAL.GIOCOTRIS/register_company.php?token=" . urlencode($test_token);
    echo "<div style='margin-top:20px;'>";
    echo "<a href='{$test_url}' target='_blank' style='background:#007bff;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:bold;'>";
    echo "🧪 TEST REGISTRAZIONE AZIENDA";
    echo "</a>";
    echo "</div>";
    
    echo "<div style='margin-top:15px;'>";
    echo "<p style='color:#666;font-size:14px;'>Clicca il pulsante sopra per testare il fix dei pulsanti media in registrazione</p>";
    echo "</div>";
    
    echo "</div></body></html>";
} else {
    echo "❌ Errore nella creazione del token: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
