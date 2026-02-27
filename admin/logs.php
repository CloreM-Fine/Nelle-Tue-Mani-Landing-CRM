<?php
/**
 * Log Attività Admin
 */
define('ACCESS_PROTECTED', true);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance();
$adminUser = $_SESSION['username'];

// Pagination
$page = intval($_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Recupera logs
$logs = $db->fetchAll(
    "SELECT * FROM admin_logs ORDER BY data_ora DESC LIMIT ? OFFSET ?",
    [$perPage, $offset]
);

$totalLogs = $db->fetch("SELECT COUNT(*) as total FROM admin_logs")['total'];
$totalPages = ceil($totalLogs / $perPage);

$csrfToken = $auth->generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Attività | Nelle Tue Mani</title>
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
                <a href="orari.php" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg text-white/80 transition">
                    <i class="fas fa-clock w-6"></i>Gestione Orari
                </a>
                <a href="logs.php" class="flex items-center px-4 py-3 bg-primary/20 rounded-lg text-white">
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
                <h2 class="text-2xl md:text-3xl font-serif font-bold text-darkGreen">Log Attività</h2>
                <p class="text-gray-500 text-sm md:text-base">Traccia delle azioni amministrative</p>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="bg-white rounded-xl p-4 md:p-6 shadow-sm mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs md:text-sm">Totale azioni registrate</p>
                    <p class="text-2xl md:text-3xl font-bold text-darkGreen"><?php echo $totalLogs; ?></p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-history text-blue-600"></i>
                </div>
            </div>
        </div>
        
        <!-- Desktop Table -->
        <div class="hidden md:block bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Data/Ora</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Admin</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Azione</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Dettagli</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">
                            <?php echo date('d/m/Y H:i', strtotime($log['data_ora'])); ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium"><?php echo htmlspecialchars($log['admin_user']); ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 bg-primary/10 text-primary rounded text-xs">
                                <?php echo htmlspecialchars($log['azione']); ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <?php echo htmlspecialchars($log['dettagli'] ?? '-'); ?>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">
                            <?php echo $log['ip_address']; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Cards -->
        <div class="md:hidden space-y-3">
            <?php foreach ($logs as $log): ?>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-xs text-gray-500">
                        <?php echo date('d/m/Y H:i', strtotime($log['data_ora'])); ?>
                    </span>
                    <span class="px-2 py-1 bg-primary/10 text-primary rounded text-xs">
                        <?php echo htmlspecialchars($log['azione']); ?>
                    </span>
                </div>
                <div class="font-medium text-darkGreen mb-1">
                    <?php echo htmlspecialchars($log['admin_user']); ?>
                </div>
                <div class="text-sm text-gray-600 mb-2">
                    <?php echo htmlspecialchars($log['dettagli'] ?? '-'); ?>
                </div>
                <div class="text-xs text-gray-400">
                    IP: <?php echo $log['ip_address']; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex justify-center gap-2">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-white rounded-lg shadow-sm hover:bg-gray-50">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <span class="px-4 py-2 bg-primary text-white rounded-lg">
                <?php echo $page; ?> / <?php echo $totalPages; ?>
            </span>
            
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-white rounded-lg shadow-sm hover:bg-gray-50">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>
    
</body>
</html>
