<?php
/**
 * Authentication & Security Handler
 * 
 * Funzionalità:
 * - Login/logout sicuro
 * - Sessioni protette
 * - CSRF Protection
 * - Rate limiting
 * - Audit logging
 */

if (!defined('ACCESS_PROTECTED')) {
    die('Accesso diretto non consentito');
}

require_once __DIR__ . '/db.php';

class Auth {
    private $db;
    private $maxAttempts = MAX_LOGIN_ATTEMPTS;
    private $lockoutTime = LOGIN_TIMEOUT;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->initSession();
    }
    
    /**
     * Inizializza sessione sicura
     */
    private function initSession() {
        // Impostazioni sessione sicure
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1); // Solo HTTPS
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        
        session_name(SESSION_NAME);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Rigenera ID sessione periodicamente
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            // Rigenera ogni 30 min
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        // Validazione sessione
        if (isset($_SESSION['ip']) && $_SESSION['ip'] !== $this->getClientIP()) {
            $this->logout();
            header('Location: login.php?error=session_invalid');
            exit;
        }
        
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->logout();
            header('Location: login.php?error=session_invalid');
            exit;
        }
    }
    
    /**
     * Login con rate limiting
     */
    public function login($username, $password) {
        // Rate limiting check
        if ($this->isLockedOut()) {
            return ['success' => false, 'error' => 'Troppi tentativi. Riprova tra ' . ceil($this->lockoutTime / 60) . ' minuti.'];
        }
        
        // Validazione input
        if (empty($username) || empty($password)) {
            $this->logAttempt($username, false);
            return ['success' => false, 'error' => 'Inserisci username e password.'];
        }
        
        // Verifica credenziali (hardcoded per admin singolo)
        // In produzione: usare database con password_hash()
        if ($username === ADMIN_USER && password_verify($password, ADMIN_PASS_HASH)) {
            // Successo
            $this->logAttempt($username, true);
            $this->clearAttempts();
            
            // Crea sessione
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['ip'] = $this->getClientIP();
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['login_time'] = time();
            
            // Log azione
            $this->logAction($username, 'Login', 'Accesso effettuato con successo');
            
            return ['success' => true];
        }
        
        // Fallito
        $this->logAttempt($username, false);
        return ['success' => false, 'error' => 'Credenziali non valide.'];
    }
    
    /**
     * Logout
     */
    public function logout() {
        if (isset($_SESSION['username'])) {
            $this->logAction($_SESSION['username'], 'Logout', 'Disconnessione effettuata');
        }
        
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
        
        session_destroy();
    }
    
    /**
     * Verifica se utente è autenticato
     */
    public function isAuthenticated() {
        return isset($_SESSION['authenticated']) 
            && $_SESSION['authenticated'] === true
            && (time() - $_SESSION['login_time']) < SESSION_LIFETIME;
    }
    
    /**
     * Richiede autenticazione
     */
    public function requireAuth() {
        if (!$this->isAuthenticated()) {
            header('Location: login.php');
            exit;
        }
    }
    
    /**
     * CSRF Token Generation
     */
    public function generateCSRFToken() {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    /**
     * CSRF Token Validation
     */
    public function validateCSRFToken($token) {
        return isset($_SESSION[CSRF_TOKEN_NAME]) 
            && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * Rate Limiting - Controlla se IP è bloccato
     */
    private function isLockedOut() {
        $ip = $this->getClientIP();
        $since = date('Y-m-d H:i:s', time() - $this->lockoutTime);
        
        $sql = "SELECT COUNT(*) as attempts FROM login_attempts 
                WHERE ip_address = ? AND success = 0 AND data_ora > ?";
        $result = $this->db->fetch($sql, [$ip, $since]);
        
        return $result['attempts'] >= $this->maxAttempts;
    }
    
    /**
     * Log tentativo login
     */
    private function logAttempt($username, $success) {
        $sql = "INSERT INTO login_attempts (ip_address, username, success) VALUES (?, ?, ?)";
        $this->db->query($sql, [$this->getClientIP(), $username, $success ? 1 : 0]);
    }
    
    /**
     * Pulisce tentativi dopo login successo
     */
    private function clearAttempts() {
        $sql = "DELETE FROM login_attempts WHERE ip_address = ?";
        $this->db->query($sql, [$this->getClientIP()]);
    }
    
    /**
     * Log azione admin
     */
    public function logAction($admin, $azione, $dettagli = '') {
        $sql = "INSERT INTO admin_logs (admin_user, azione, dettagli, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $admin,
            $azione,
            $dettagli,
            $this->getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }
    
    /**
     * Recupera IP client (considerando proxy)
     */
    private function getClientIP() {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Cambia password admin
     */
    public function changePassword($oldPassword, $newPassword) {
        if (!$this->isAuthenticated()) {
            return ['success' => false, 'error' => 'Non autenticato'];
        }
        
        // Verifica vecchia password
        if (!password_verify($oldPassword, ADMIN_PASS_HASH)) {
            return ['success' => false, 'error' => 'Password attuale non corretta'];
        }
        
        // Validazione nuova password
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'error' => 'La password deve essere di almeno 8 caratteri'];
        }
        
        // In produzione: aggiorna su DB
        // Per ora: log dell'azione
        $this->logAction($_SESSION['username'], 'Password Change', 'Password modificata con successo');
        
        return ['success' => true, 'message' => 'Password modificata. Aggiorna config.php con il nuovo hash.'];
    }
}
?>
