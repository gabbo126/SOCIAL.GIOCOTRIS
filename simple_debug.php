<?php
// TEST SEMPLICE - NESSUNA DIPENDENZA
echo "<h1>SIMPLE DEBUG TEST</h1>";

// Connessione diretta
$conn = new mysqli("localhost", "root", "", "social_gioco_tris");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

echo "<h2>1. DATABASE ATTIVO:</h2>";
$result = $conn->query("SELECT DATABASE()");
if ($result) {
    $row = $result->fetch_row();
    echo "Database: <strong>" . $row[0] . "</strong><br>";
}

echo "<h2>2. COLONNA business_categories ESISTE?</h2>";
$result = $conn->query("DESCRIBE aziende");
if ($result) {
    $found = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] == 'business_categories') {
            echo "✅ <strong>business_categories TROVATA!</strong><br>";
            echo "Tipo: " . $row['Type'] . "<br>";
            echo "Null: " . $row['Null'] . "<br>";
            $found = true;
        }
    }
    if (!$found) {
        echo "❌ <strong>business_categories NON TROVATA!</strong><br>";
    }
} else {
    echo "Errore DESCRIBE: " . $conn->error . "<br>";
}

echo "<h2>3. TEST SELECT:</h2>";
$result = $conn->query("SELECT business_categories FROM aziende LIMIT 1");
if ($result) {
    echo "✅ <strong>SELECT business_categories FUNZIONA!</strong><br>";
} else {
    echo "❌ <strong>ERRORE SELECT:</strong> " . $conn->error . "<br>";
}

echo "<h2>4. TEST PREPARED UPDATE:</h2>";
$stmt = $conn->prepare("UPDATE aziende SET business_categories = ? WHERE id = ?");
if ($stmt) {
    echo "✅ <strong>PREPARE UPDATE FUNZIONA!</strong><br>";
    $stmt->close();
} else {
    echo "❌ <strong>ERRORE PREPARE:</strong> " . $conn->error . "<br>";
}

$conn->close();
?>
