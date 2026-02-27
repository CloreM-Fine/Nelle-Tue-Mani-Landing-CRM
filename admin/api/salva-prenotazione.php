<?php
/**
 * API Salva Prenotazione
 * Riceve dati dal form contatto e li salva su DB
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../includes/db.php';

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo non consentito']);
    exit;
}

// Recupera dati (supporta sia form-data che JSON)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

// Validazione
campi richiesti
$required = ['name', 'phone', 'service', 'date', 'time'];
$missing = [];
foreach ($required as $field) {
    if (empty($input[$field])) {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    http_response_code(400);
    echo json_encode(['error' => 'Campi mancanti: ' . implode(', ', $missing)]);
    exit;
}

// Sanitizzazione
$nome = htmlspecialchars(trim($input['name']));
$telefono = htmlspecialchars(trim($input['phone']));
$email = !empty($input['email']) ? filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL) : null;
$servizio = htmlspecialchars(trim($input['service']));
$data = $input['date'];
$ora = $input['time'];
$note = !empty($input['message']) ? htmlspecialchars(trim($input['message'])) : null;

// Validazione data
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) || $data < date('Y-m-d')) {
    http_response_code(400);
    echo json_encode(['error' => 'Data non valida']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Verifica disponibilità slot
    $slot = $db->fetch(
        "SELECT * FROM disponibilita_orari 
         WHERE data = ? AND ora = ? AND disponibile = TRUE",
        [$data, $ora]
    );
    
    if (!$slot) {
        http_response_code(409);
        echo json_encode(['error' => 'Slot orario non disponibile. Seleziona un altro orario.']);
        exit;
    }
    
    // Inserisci prenotazione
    $prenotazioneId = $db->insert(
        "INSERT INTO prenotazioni (nome, telefono, email, servizio, data_preferita, ora_preferita, note, stato) 
         VALUES (?, ?, ?, ?, ?, ?, ?, 'nuova')",
        [$nome, $telefono, $email, $servizio, $data, $ora, $note]
    );
    
    // Marca slot come occupato
    $db->execute(
        "UPDATE disponibilita_orari SET disponibile = FALSE, prenotazione_id = ? 
         WHERE id = ?",
        [$prenotazioneId, $slot['id']]
    );
    
    // Invia email notifica (opzionale - da implementare)
    // mail(ADMIN_EMAIL, "Nuova prenotazione", "...");
    
    echo json_encode([
        'success' => true,
        'message' => 'Prenotazione ricevuta! Ti contatteremo presto per confermare.',
        'id' => $prenotazioneId
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore nel salvataggio. Riprova più tardi.']);
    error_log("Save Prenotazione Error: " . $e->getMessage());
}
?>
