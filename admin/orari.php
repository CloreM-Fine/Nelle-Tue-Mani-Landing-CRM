<?php
/**
 * Gestione Orari Disponibili
 */
define('ACCESS_PROTECTED', true);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance();
$adminUser = $_SESSION['username'];

$message = '';

// Azioni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!$auth->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token non valido';
    } else {
        switch ($_POST['action']) {
            case 'toggle_slot':
                $data = $_POST['data'] ?? '';
                $ora = $_POST['ora'] ?? '';
                $disponibile = $_POST['disponibile'] ?? '0';
                
                if ($data && $ora) {
                    // Usa INSERT ... ON DUPLICATE KEY UPDATE
                    $sql = "INSERT INTO disponibilita_orari (data, ora, disponibile) 
                            VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE disponibile = ?";
                    $db->query($sql, [$data, $ora, $disponibile, $disponibile]);
                    $auth->logAction($adminUser, 'Toggle Orario', "$data $ora -> $disponibile");
                    $message = 'Orario aggiornato';
                }
                break;
                
            case 'genera_slot':
                $dataInizio = $_POST['data_inizio'] ?? '';
                $giorni = intval($_POST['giorni'] ?? 30);
                
                if ($dataInizio && $giorni > 0 && $giorni <= 90) {
                    // Genera slot per i giorni richiesti
                    $count = 0;
                    for ($i = 0; $i < $giorni; $i++) {
                        $data = date('Y-m-d', strtotime($dataInizio . " +$i days"));
                        $giornoSettimana = date('w', strtotime($data));
                        
                        // 0 = Domenica (chiuso), 6 = Sabato (mattina solo)
                        if ($giornoSettimana == 0) continue;
                        
                        // Orari mattina
                        $orariMattina = ['09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30'];
                        // Orari pomeriggio (Lun-Ven)
                        $orariPomeriggio = ['15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30'];
                        
                        $orari = $orariMattina;
                        if ($giornoSettimana >= 1 && $giornoSettimana <= 5) {
                            $orari = array_merge($orariMattina, $orariPomeriggio);
                        }
                        
                        foreach ($orari as $ora) {
                            try {
                                $db->query("INSERT IGNORE INTO disponibilita_orari (data, ora, disponibile) VALUES (?, ?, TRUE)", 
                                          [$data, $ora]);
                                $count++;
                            } catch (Exception $e) {
                                // Slot già esistente, ignora
                            }
                        }
                    }
                    $auth->logAction($adminUser, 'Genera Slot', "Generati $count slot per $giorni giorni");
                    $message = "Generati $count slot orari";
                }
                break;
                
            case 'chiudi_giorno':
                $data = $_POST['data_chiusura'] ?? '';
                if ($data) {
                    $db->execute("UPDATE disponibilita_orari SET disponibile = FALSE WHERE data = ?", [$data]);
                    $auth->logAction($adminUser, 'Chiudi Giorno', $data);
                    $message = "Giorno $data chiuso";
                }
                break;
        }
    }
}

// Visualizzazione settimana
$settimanaCorrente = $_GET['settimana'] ?? date('Y-m-d');
$inizioSettimana = date('Y-m-d', strtotime('monday this week', strtotime($settimanaCorrente)));
$fineSettimana = date('Y-m-d', strtotime('sunday this week', strtotime($settimanaCorrente)));

// Recupera orari della settimana
$orariSettimana = $db->fetchAll(
    "SELECT * FROM disponibilita_orari 
     WHERE data BETWEEN ? AND ? 
     ORDER BY data, ora",
    [$inizioSettimana, $fineSettimana]
);

// Organizza per giorno
$slotsPerGiorno = [];
foreach ($orariSettimana as $slot) {
    $slotsPerGiorno[$slot['data']][] = $slot;
}

$csrfToken = $auth->generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Orari | Nelle Tue Mani</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#e91e8c',
                        primaryDark: '#c41873',
                        gold: '#d4af37',
                        darkGreen: '#1a3c27',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans">
    
    <!-- Desktop Sidebar -->
    <aside class="hidden md:block fixed left-0 top-0 h-full w-64 bg-darkGreen text-white z-50">
        <div class="p-6">
            <div class="flex items-center space-x-3 mb-8">
                <img src="../assets/logo/526988232_17847756564535217_4050507715784280567_n.jpg" 
                     class="w-12 h-12 rounded-full border-2 border-primary">
                <div>
                    <h1 class="font-serif font-bold">Nelle Tue Mani</h1>
                    <p class="text-xs text-primary">Admin Panel</p>
                </div>
            </div>
            
            <nav class="space-y-2">
                <a href="dashboard.php" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg text-white/80 transition">
                    <i class="fas fa-calendar-alt w-6"></i>Prenotazioni
                </a>
                <a href="orari.php" class="flex items-center px-4 py-3 bg-primary/20 rounded-lg text-white">
                    <i class="fas fa-clock w-6"></i>Gestione Orari
                </a>
                <a href="logs.php" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg text-white/80 transition">
                    <i class="fas fa-history w-6"></i>Log Attività
                </a>
            </nav>
        </div>
        
        <div class="absolute bottom-0 left-0 right-0 p-6 border-t border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-sm text-white/70"><?php echo htmlspecialchars($adminUser); ?></span>
                <a href="logout.php" class="text-red-400 hover:text-red-300 text-sm">
                    <i class="fas fa-sign-out-alt"></i> Esci
                </a>
            </div>
        </div>
    </aside>
    
    <!-- Mobile Navigation -->
    <?php include __DIR__ . '/includes/mobile-nav.php'; ?>
    
    <!-- Main Content -->
    <main class="md:ml-64 p-4 md:p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-serif font-bold text-darkGreen">Gestione Orari</h2>
                <p class="text-gray-500">Gestisci la disponibilità degli appuntamenti</p>
            </div>
        </div>
        
        <?php if ($message): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
            <p class="text-green-700"><i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($message); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Azioni Rapide -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-8">
            <!-- Genera Slot -->
            <div class="bg-white rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-darkGreen mb-4"><i class="fas fa-plus-circle mr-2"></i>Genera Slot Orari</h3>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="genera_slot">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Data inizio</label>
                            <input type="date" name="data_inizio" required 
                                   value="<?php echo date('Y-m-d'); ?>"
                                   class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Numero giorni (max 90)</label>
                            <input type="number" name="giorni" min="1" max="90" value="30" 
                                   class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primaryDark">
                        <i class="fas fa-magic mr-2"></i>Genera automaticamente
                    </button>
                </form>
            </div>
            
            <!-- Chiudi Giorno -->
            <div class="bg-white rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-darkGreen mb-4"><i class="fas fa-ban mr-2"></i>Chiudi Giornata</h3>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="chiudi_giorno">
                    
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Data da chiudere</label>
                        <input type="date" name="data_chiusura" required 
                               class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                        <i class="fas fa-calendar-times mr-2"></i>Chiudi tutti gli slot
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Navigazione Settimane -->
        <div class="flex justify-between items-center mb-6">
            <a href="?settimana=<?php echo date('Y-m-d', strtotime($inizioSettimana . ' -7 days')); ?>" 
               class="bg-white px-4 py-2 rounded-lg shadow-sm hover:bg-gray-50">
                <i class="fas fa-chevron-left mr-2"></i>Settimana precedente
            </a>
            <h3 class="text-xl font-bold text-darkGreen">
                <?php echo date('d/m/Y', strtotime($inizioSettimana)); ?> - 
                <?php echo date('d/m/Y', strtotime($fineSettimana)); ?>
            </h3>
            <a href="?settimana=<?php echo date('Y-m-d', strtotime($inizioSettimana . ' +7 days')); ?>" 
               class="bg-white px-4 py-2 rounded-lg shadow-sm hover:bg-gray-50">
                Settimana successiva<i class="fas fa-chevron-right ml-2"></i>
            </a>
        </div>
        
        <!-- Calendario Settimanale -->
        <div class="bg-white rounded-xl shadow-sm overflow-x-auto md:overflow-visible">
            <div class="grid grid-cols-7 divide-x divide-gray-200 min-w-[800px] md:min-w-0">
                <?php 
                $giorniSettimana = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
                for ($i = 0; $i < 7; $i++): 
                    $dataGiorno = date('Y-m-d', strtotime($inizioSettimana . " +$i days"));
                    $slots = $slotsPerGiorno[$dataGiorno] ?? [];
                    $isToday = $dataGiorno === date('Y-m-d');
                ?>
                <div class="min-h-[500px] <?php echo $isToday ? 'bg-blue-50' : ''; ?>">
                    <div class="p-3 bg-gray-50 border-b border-gray-200 text-center">
                        <div class="text-xs text-gray-500 uppercase"><?php echo $giorniSettimana[$i]; ?></div>
                        <div class="font-bold text-darkGreen <?php echo $isToday ? 'text-primary' : ''; ?>">
                            <?php echo date('d/m', strtotime($dataGiorno)); ?>
                        </div>
                    </div>
                    <div class="p-2 space-y-1">
                        <?php if (empty($slots)): ?>
                            <div class="text-center text-gray-400 text-sm py-8">
                                Nessuno slot
                            </div>
                        <?php else: ?>
                            <?php foreach ($slots as $slot): 
                                $isOccupied = !$slot['disponibile'];
                                $hasPrenotazione = !empty($slot['prenotazione_id']);
                            ?>
                            <form method="POST" class="block">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                <input type="hidden" name="action" value="toggle_slot">
                                <input type="hidden" name="data" value="<?php echo $slot['data']; ?>">
                                <input type="hidden" name="ora" value="<?php echo $slot['ora']; ?>">
                                <input type="hidden" name="disponibile" value="<?php echo $isOccupied ? '1' : '0'; ?>">
                                
                                <button type="submit" 
                                        class="w-full text-left px-3 py-2 rounded text-sm transition
                                               <?php echo $hasPrenotazione ? 'bg-red-100 text-red-700 cursor-not-allowed' : ''; ?>
                                               <?php echo !$hasPrenotazione && $isOccupied ? 'bg-gray-200 text-gray-500 hover:bg-gray-300' : ''; ?>
                                               <?php echo !$isOccupied ? 'bg-green-100 text-green-700 hover:bg-green-200' : ''; ?>"
                                        <?php echo $hasPrenotazione ? 'disabled title="Slot prenotato"' : 'title="Clicca per cambiare"'; ?>>
                                    <?php echo substr($slot['ora'], 0, 5); ?>
                                    <?php if ($hasPrenotazione): ?>
                                        <i class="fas fa-lock ml-1 text-xs"></i>
                                    <?php elseif ($isOccupied): ?>
                                        <i class="fas fa-times ml-1 text-xs"></i>
                                    <?php else: ?>
                                        <i class="fas fa-check ml-1 text-xs"></i>
                                    <?php endif; ?>
                                </button>
                            </form>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <!-- Legenda -->
        <div class="mt-6 flex flex-wrap gap-4 md:gap-6 text-sm">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-100 rounded mr-2"></div>
                <span>Disponibile</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-gray-200 rounded mr-2"></div>
                <span>Chiuso/Non disponibile</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-red-100 rounded mr-2"></div>
                <span>Prenotato</span>
            </div>
        </div>
    </main>
    
</body>
</html>
