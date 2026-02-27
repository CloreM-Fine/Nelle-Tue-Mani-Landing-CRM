<?php
/**
 * Admin Dashboard - Gestione Prenotazioni
 */
define('ACCESS_PROTECTED', true);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance();
$adminUser = $_SESSION['username'];

// Gestione azioni
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!$auth->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token non valido';
    } else {
        switch ($_POST['action']) {
            case 'update_status':
                $id = intval($_POST['id'] ?? 0);
                $stato = $_POST['stato'] ?? '';
                if ($id && in_array($stato, ['nuova', 'confermata', 'completata', 'cancellata'])) {
                    $db->execute("UPDATE prenotazioni SET stato = ? WHERE id = ?", [$stato, $id]);
                    $auth->logAction($adminUser, 'Update Prenotazione', "ID: $id - Stato: $stato");
                    $message = 'Stato aggiornato con successo';
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id'] ?? 0);
                if ($id) {
                    // Libera slot orario associato
                    $db->execute("UPDATE disponibilita_orari SET disponibile = TRUE, prenotazione_id = NULL WHERE prenotazione_id = ?", [$id]);
                    $db->execute("DELETE FROM prenotazioni WHERE id = ?", [$id]);
                    $auth->logAction($adminUser, 'Delete Prenotazione', "ID: $id");
                    $message = 'Prenotazione eliminata';
                }
                break;
                
            case 'add_note':
                $id = intval($_POST['id'] ?? 0);
                $note = $_POST['admin_notes'] ?? '';
                if ($id) {
                    $db->execute("UPDATE prenotazioni SET admin_notes = ? WHERE id = ?", [$note, $id]);
                    $auth->logAction($adminUser, 'Add Note', "ID: $id");
                    $message = 'Nota aggiunta';
                }
                break;
        }
    }
}

// Filtraggio
$statoFilter = $_GET['stato'] ?? 'tutte';
$dataFrom = $_GET['data_from'] ?? '';
$dataTo = $_GET['data_to'] ?? '';

// Query prenotazioni
$sql = "SELECT * FROM prenotazioni WHERE 1=1";
$params = [];

if ($statoFilter !== 'tutte') {
    $sql .= " AND stato = ?";
    $params[] = $statoFilter;
}
if ($dataFrom) {
    $sql .= " AND data_preferita >= ?";
    $params[] = $dataFrom;
}
if ($dataTo) {
    $sql .= " AND data_preferita <= ?";
    $params[] = $dataTo;
}

$sql .= " ORDER BY data_creazione DESC";
$prenotazioni = $db->fetchAll($sql, $params);

// Statistiche
$stats = $db->fetch("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN stato = 'nuova' THEN 1 ELSE 0 END) as nuove,
    SUM(CASE WHEN stato = 'confermata' THEN 1 ELSE 0 END) as confermate,
    SUM(CASE WHEN data_preferita = CURDATE() THEN 1 ELSE 0 END) as oggi
FROM prenotazioni");

$csrfToken = $auth->generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Nelle Tue Mani</title>
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
                <a href="dashboard.php" class="flex items-center px-4 py-3 bg-primary/20 rounded-lg text-white">
                    <i class="fas fa-calendar-alt w-6"></i>Prenotazioni
                </a>
                <a href="orari.php" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg text-white/80 transition">
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
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-serif font-bold text-darkGreen">Prenotazioni</h2>
                <p class="text-gray-500">Gestisci gli appuntamenti dei clienti</p>
            </div>
            <a href="../index.html" target="_blank" class="text-primary hover:text-primaryDark">
                <i class="fas fa-external-link-alt mr-2"></i>Vedi sito
            </a>
        </div>
        
        <?php if ($message): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
            <p class="text-green-700"><i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($message); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mb-6 md:mb-8">
            <div class="bg-white rounded-xl p-4 md:p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs md:text-sm">Totali</p>
                        <p class="text-xl md:text-3xl font-bold text-darkGreen"><?php echo $stats['total']; ?></p>
                    </div>
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar text-blue-600 text-sm md:text-base"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-4 md:p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs md:text-sm">Nuove</p>
                        <p class="text-xl md:text-3xl font-bold text-primary"><?php echo $stats['nuove']; ?></p>
                    </div>
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-pink-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-bell text-primary text-sm md:text-base"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-4 md:p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs md:text-sm">Confermate</p>
                        <p class="text-xl md:text-3xl font-bold text-gold"><?php echo $stats['confermate']; ?></p>
                    </div>
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-gold text-sm md:text-base"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-4 md:p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs md:text-sm">Oggi</p>
                        <p class="text-xl md:text-3xl font-bold text-green-600"><?php echo $stats['oggi']; ?></p>
                    </div>
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-day text-green-600 text-sm md:text-base"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-xl p-4 mb-6 shadow-sm">
            <form method="GET" class="flex flex-col md:flex-row flex-wrap gap-3 md:gap-4 md:items-end">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Stato</label>
                    <select name="stato" class="border rounded-lg px-3 py-2">
                        <option value="tutte">Tutte</option>
                        <option value="nuova" <?php echo $statoFilter === 'nuova' ? 'selected' : ''; ?>>Nuove</option>
                        <option value="confermata" <?php echo $statoFilter === 'confermata' ? 'selected' : ''; ?>>Confermate</option>
                        <option value="completata" <?php echo $statoFilter === 'completata' ? 'selected' : ''; ?>>Completate</option>
                        <option value="cancellata" <?php echo $statoFilter === 'cancellata' ? 'selected' : ''; ?>>Cancellate</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Da</label>
                    <input type="date" name="data_from" value="<?php echo $dataFrom; ?>" class="border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">A</label>
                    <input type="date" name="data_to" value="<?php echo $dataTo; ?>" class="border rounded-lg px-3 py-2">
                </div>
                <button type="submit" class="bg-darkGreen text-white px-4 py-2 rounded-lg hover:bg-darkGreen/80">
                    <i class="fas fa-filter mr-2"></i>Filtra
                </button>
                <a href="dashboard.php" class="text-gray-500 hover:text-gray-700 px-4 py-2">Reset</a>
            </form>
        </div>
        
        <!-- Table Desktop -->
        <div class="hidden md:block bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Data/Ora</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Cliente</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Servizio</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Stato</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($prenotazioni as $p): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium"><?php echo date('d/m/Y', strtotime($p['data_preferita'])); ?></div>
                            <div class="text-sm text-gray-500"><?php echo $p['ora_preferita']; ?></div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium"><?php echo htmlspecialchars($p['nome']); ?></div>
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-phone mr-1"></i><?php echo $p['telefono']; ?>
                            </div>
                            <?php if ($p['email']): ?>
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-envelope mr-1"></i><?php echo $p['email']; ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="capitalize"><?php echo str_replace('_', ' ', $p['servizio']); ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <?php 
                            $statoColors = [
                                'nuova' => 'bg-blue-100 text-blue-800',
                                'confermata' => 'bg-green-100 text-green-800',
                                'completata' => 'bg-gray-100 text-gray-800',
                                'cancellata' => 'bg-red-100 text-red-800'
                            ];
                            ?>
                            <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $statoColors[$p['stato']]; ?>">
                                <?php echo ucfirst($p['stato']); ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex space-x-2">
                                <!-- View Details -->
                                <button onclick="toggleDetails(<?php echo $p['id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-800" title="Dettagli">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <!-- Change Status -->
                                <form method="POST" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <select name="stato" onchange="this.form.submit()" 
                                            class="text-xs border rounded px-2 py-1">
                                        <option value="">Cambia stato...</option>
                                        <option value="nuova">Nuova</option>
                                        <option value="confermata">Confermata</option>
                                        <option value="completata">Completata</option>
                                        <option value="cancellata">Cancellata</option>
                                    </select>
                                </form>
                                
                                <!-- Delete -->
                                <form method="POST" class="inline" onsubmit="return confirm('Eliminare questa prenotazione?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Elimina">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Hidden Details Row -->
                            <div id="details-<?php echo $p['id']; ?>" class="hidden mt-3 p-3 bg-gray-50 rounded text-sm">
                                <p><strong>Note:</strong> <?php echo nl2br(htmlspecialchars($p['note'] ?: 'Nessuna nota')); ?></p>
                                <p class="mt-2"><strong>Richiesta il:</strong> <?php echo date('d/m/Y H:i', strtotime($p['data_creazione'])); ?></p>
                                
                                <form method="POST" class="mt-3">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="action" value="add_note">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <textarea name="admin_notes" placeholder="Note interne..." 
                                              class="w-full border rounded p-2 text-sm"><?php echo htmlspecialchars($p['admin_notes'] ?? ''); ?></textarea>
                                    <button type="submit" class="mt-2 bg-darkGreen text-white px-3 py-1 rounded text-xs">
                                        Salva nota
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($prenotazioni)): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Nessuna prenotazione trovata</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Cards -->
        <div class="md:hidden space-y-3">
            <?php foreach ($prenotazioni as $p): 
                $statoColors = [
                    'nuova' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'confermata' => 'bg-green-100 text-green-800 border-green-200',
                    'completata' => 'bg-gray-100 text-gray-800 border-gray-200',
                    'cancellata' => 'bg-red-100 text-red-800 border-red-200'
                ];
            ?>
            <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 <?php echo str_replace(['bg-', 'text-'], '', explode(' ', $statoColors[$p['stato']])[2]); ?>">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="font-bold text-lg"><?php echo date('d/m/Y', strtotime($p['data_preferita'])); ?></div>
                        <div class="text-primary font-medium"><?php echo substr($p['ora_preferita'], 0, 5); ?></div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $statoColors[$p['stato']]; ?>">
                        <?php echo ucfirst($p['stato']); ?>
                    </span>
                </div>
                
                <div class="mb-3">
                    <div class="font-medium text-darkGreen"><?php echo htmlspecialchars($p['nome']); ?></div>
                    <a href="tel:<?php echo $p['telefono']; ?>" class="text-sm text-gray-500 hover:text-primary">
                        <i class="fas fa-phone mr-1"></i><?php echo $p['telefono']; ?>
                    </a>
                    <?php if ($p['email']): ?>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-envelope mr-1"></i><?php echo $p['email']; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="text-sm text-gray-600 mb-3">
                    <i class="fas fa-hand-sparkles mr-1 text-primary"></i>
                    <?php echo ucfirst(str_replace('_', ' ', $p['servizio'])); ?>
                </div>
                
                <!-- Mobile Actions -->
                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                    <button onclick="toggleDetailsMobile(<?php echo $p['id']; ?>)" 
                            class="flex-1 bg-blue-50 text-blue-600 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-eye mr-1"></i>Dettagli
                    </button>
                    
                    <form method="POST" class="flex-1" onsubmit="return confirm('Eliminare?');">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <button type="submit" class="w-full bg-red-50 text-red-600 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-trash mr-1"></i>Elimina
                        </button>
                    </form>
                </div>
                
                <!-- Mobile Status Change -->
                <form method="POST" class="mt-2">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <select name="stato" onchange="this.form.submit()" 
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-gray-50">
                        <option value="">Cambia stato...</option>
                        <option value="nuova">Nuova</option>
                        <option value="confermata">Confermata</option>
                        <option value="completata">Completata</option>
                        <option value="cancellata">Cancellata</option>
                    </select>
                </form>
                
                <!-- Hidden Details Mobile -->
                <div id="details-mobile-<?php echo $p['id']; ?>" class="hidden mt-3 p-3 bg-gray-50 rounded-lg text-sm">
                    <p class="mb-2"><strong>Note cliente:</strong><br><?php echo nl2br(htmlspecialchars($p['note'] ?: 'Nessuna nota')); ?></p>
                    <p class="text-xs text-gray-500 mb-3">Richiesta il: <?php echo date('d/m/Y H:i', strtotime($p['data_creazione'])); ?></p>
                    
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="action" value="add_note">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <textarea name="admin_notes" placeholder="Note interne..." 
                                  class="w-full border rounded-lg p-2 text-sm mb-2" rows="2"><?php echo htmlspecialchars($p['admin_notes'] ?? ''); ?></textarea>
                        <button type="submit" class="w-full bg-darkGreen text-white py-2 rounded-lg text-sm">
                            Salva nota
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($prenotazioni)): ?>
            <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-500">
                <i class="fas fa-inbox text-4xl mb-2"></i>
                <p>Nessuna prenotazione trovata</p>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        function toggleDetails(id) {
            const el = document.getElementById('details-' + id);
            el.classList.toggle('hidden');
        }
        
        function toggleDetailsMobile(id) {
            const el = document.getElementById('details-mobile-' + id);
            el.classList.toggle('hidden');
        }
    </script>
    
</body>
</html>
