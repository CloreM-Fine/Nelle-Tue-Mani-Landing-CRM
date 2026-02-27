<?php
/**
 * Admin Login Page
 */
define('ACCESS_PROTECTED', true);
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();

// Se già loggato, vai alla dashboard
if ($auth->isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !$auth->validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Token di sicurezza non valido. Ricarica la pagina.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $result = $auth->login($username, $password);
        
        if ($result['success']) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

$csrfToken = $auth->generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | Nelle Tue Mani</title>
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
                    },
                    fontFamily: {
                        serif: ['Playfair Display', 'serif'],
                        sans: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #1a3c27 0%, #0d1f15 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="../assets/logo/526988232_17847756564535217_4050507715784280567_n.jpg" 
                 alt="Logo" 
                 class="w-20 h-20 rounded-full border-4 border-primary mx-auto mb-4 shadow-lg">
            <h1 class="font-serif text-3xl text-white font-bold">Nelle Tue Mani</h1>
            <p class="text-gold">Area Riservata</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="font-serif text-2xl text-darkGreen font-bold mb-6 text-center">
                Accedi alla Dashboard
            </h2>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-500 mt-1 mr-3"></i>
                    <p class="text-red-700 text-sm"><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Username
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               required 
                               autocomplete="username"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                               placeholder="admin">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               autocomplete="current-password"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                               placeholder="••••••••">
                    </div>
                </div>
                
                <button type="submit" 
                        class="w-full bg-primary hover:bg-primaryDark text-white font-medium py-3 rounded-lg transition-all duration-300 transform hover:scale-[1.02] shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>Accedi
                </button>
            </form>
            
            <div class="mt-6 text-center text-sm text-gray-500">
                <p><i class="fas fa-shield-alt mr-1"></i> Connessione sicura crittografata</p>
            </div>
        </div>
        
        <!-- Back to site -->
        <div class="text-center mt-8">
            <a href="../index.html" class="text-white/70 hover:text-white transition-colors text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Torna al sito
            </a>
        </div>
    </div>
    
</body>
</html>
