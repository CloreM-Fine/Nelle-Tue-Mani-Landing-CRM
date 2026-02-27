<?php
/**
 * Mobile Navigation Component
 * Include in tutte le pagine admin
 */
?>
<!-- Mobile Header -->
<header class="md:hidden bg-darkGreen text-white fixed top-0 left-0 right-0 z-50 h-16">
    <div class="flex items-center justify-between h-full px-4">
        <button id="mobile-menu-toggle" class="p-2 -ml-2">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <div class="flex items-center space-x-2">
            <img src="../assets/logo/526988232_17847756564535217_4050507715784280567_n.jpg" 
                 class="w-8 h-8 rounded-full border border-primary">
            <span class="font-serif font-bold text-sm">Nelle Tue Mani</span>
        </div>
        <a href="logout.php" class="p-2 -mr-2 text-red-400">
            <i class="fas fa-sign-out-alt text-lg"></i>
        </a>
    </div>
</header>

<!-- Mobile Sidebar Overlay -->
<div id="mobile-sidebar-overlay" class="md:hidden fixed inset-0 bg-black/50 z-40 hidden"></div>

<!-- Mobile Sidebar -->
<aside id="mobile-sidebar" class="md:hidden fixed top-16 left-0 bottom-0 w-72 bg-darkGreen text-white z-50 transform -translate-x-full transition-transform duration-300 overflow-y-auto">
    <div class="p-4">
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-white/10">
            <img src="../assets/logo/526988232_17847756564535217_4050507715784280567_n.jpg" 
                 class="w-12 h-12 rounded-full border-2 border-primary">
            <div>
                <h1 class="font-serif font-bold">Nelle Tue Mani</h1>
                <p class="text-xs text-primary">Admin Panel</p>
            </div>
        </div>
        
        <nav class="space-y-1">
            <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-primary/30 text-white' : 'text-white/70 hover:bg-white/10'; ?>">
                <i class="fas fa-calendar-alt w-8 text-center text-lg"></i>
                <span class="font-medium">Prenotazioni</span>
                <?php if (basename($_SERVER['PHP_SELF']) == 'dashboard.php'): ?>
                    <i class="fas fa-chevron-right ml-auto text-primary text-sm"></i>
                <?php endif; ?>
            </a>
            <a href="orari.php" class="flex items-center px-4 py-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'orari.php' ? 'bg-primary/30 text-white' : 'text-white/70 hover:bg-white/10'; ?>">
                <i class="fas fa-clock w-8 text-center text-lg"></i>
                <span class="font-medium">Gestione Orari</span>
                <?php if (basename($_SERVER['PHP_SELF']) == 'orari.php'): ?>
                    <i class="fas fa-chevron-right ml-auto text-primary text-sm"></i>
                <?php endif; ?>
            </a>
            <a href="logs.php" class="flex items-center px-4 py-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'bg-primary/30 text-white' : 'text-white/70 hover:bg-white/10'; ?>">
                <i class="fas fa-history w-8 text-center text-lg"></i>
                <span class="font-medium">Log Attività</span>
                <?php if (basename($_SERVER['PHP_SELF']) == 'logs.php'): ?>
                    <i class="fas fa-chevron-right ml-auto text-primary text-sm"></i>
                <?php endif; ?>
            </a>
        </nav>
        
        <div class="mt-6 pt-6 border-t border-white/10">
            <div class="flex items-center px-4 py-2 text-white/60 text-sm">
                <i class="fas fa-user-circle w-8 text-center text-lg mr-2"></i>
                <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'admin'); ?></span>
            </div>
            <a href="logout.php" class="flex items-center px-4 py-3 mt-2 text-red-400 hover:bg-red-500/10 rounded-lg">
                <i class="fas fa-sign-out-alt w-8 text-center text-lg"></i>
                <span class="font-medium">Disconnetti</span>
            </a>
        </div>
    </div>
</aside>

<!-- Spacer for mobile header -->
<div class="md:hidden h-16"></div>

<script>
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileSidebar = document.getElementById('mobile-sidebar');
    const mobileOverlay = document.getElementById('mobile-sidebar-overlay');
    
    function openMobileMenu() {
        mobileSidebar.classList.remove('-translate-x-full');
        mobileOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeMobileMenu() {
        mobileSidebar.classList.add('-translate-x-full');
        mobileOverlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    mobileMenuToggle.addEventListener('click', openMobileMenu);
    mobileOverlay.addEventListener('click', closeMobileMenu);
    
    // Close menu when clicking a link
    mobileSidebar.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', closeMobileMenu);
    });
</script>
