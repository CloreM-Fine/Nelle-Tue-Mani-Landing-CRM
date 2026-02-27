<?php
/**
 * Logout Handler
 */
define('ACCESS_PROTECTED', true);
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->logout();

header('Location: login.php');
exit;
?>
