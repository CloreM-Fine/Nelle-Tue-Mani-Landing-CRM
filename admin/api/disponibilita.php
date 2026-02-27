<?php
/**
 * API Disponibilità Orari
 * Ritorna gli slot disponibili in formato JSON
 * Chiamato dal form di prenotazione sul sito frontend
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // In produzione: specificare dominio esatto
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../includes/db.php';

try {
    $db = Database::getInstance();
    
    // Parametri
    $data = $_GET['data'] ?? '';
    
    if (!$data || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Data non valida (formato: YYYY-MM-DD)']);
        exit;
    }
    
    // Verifica data futura
    if ($data < date('Y-m-d')) {
        echo json_encode(['orari' => []]);
        exit;
    }
    
    // Recupera orari disponibili
    $orari = $db->fetchAll(
        "SELECT ora FROM disponibilita_orari 
         WHERE data = ? AND disponibile = TRUE 
         ORDER BY ora",
        [$data]
    );
    
    // Formatta orari
    $orariFormattati = array_map(function($o) {
        return substr($o['ora'], 0, 5); // HH:MM
    }, $orari);
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'orari' => $orariFormattati
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore del server']);
    error_log("API Error: " . $e->getMessage());
}
?>
