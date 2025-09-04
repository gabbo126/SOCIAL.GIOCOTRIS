<?php
// ===================================================================
// 🔗 CONFIGURAZIONE DATABASE - SOCIAL.GIOCOTRIS
// ✅ Aggiornato per supportare UTF8MB4 e business_categories
// ===================================================================

// Dettagli per la connessione al Database
$servername = "localhost";
$username = "root"; // Utente di default per XAMPP/MAMP
$password = ""; // Password di default per XAMPP/MAMP
$dbname = "social_gioco_tris";

// Crea la connessione
$conn = new mysqli($servername, $username, $password);

// Controlla la connessione
if ($conn->connect_error) {
    die("❌ ERRORE: Connessione al database fallita: " . $conn->connect_error);
}

// ✅ IMPOSTAZIONI CHARSET UTF8MB4 per supporto completo Unicode
$conn->set_charset("utf8mb4");

// Prova a creare il database se non esiste (con charset UTF8MB4)
$create_db = $conn->query("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
if (!$create_db) {
    die("❌ ERRORE: Impossibile creare database: " . $conn->error);
}

// Seleziona il database per le operazioni
if (!$conn->select_db($dbname)) {
    die("❌ ERRORE: Impossibile selezionare database $dbname: " . $conn->error);
}

// ✅ VERIFICA CONNESSIONE E CHARSET FINALE
$result = $conn->query("SELECT @@character_set_connection, @@collation_connection");
if ($result) {
    $row = $result->fetch_row();
    // Debug opzionale per sviluppo
    // echo "<!-- DB Connected: charset={$row[0]}, collation={$row[1]} -->";
}

// Imposta il charset a utf8
$conn->set_charset("utf8");
