<?php
require_once 'config.php';

if (isset($_SESSION['session_log_id'])) {
    endSessionLog($_SESSION['session_log_id']);
}

session_destroy();
session_unset();

// Clear the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Force browsers to NOT cache this page or any page before it
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

redirect('login.php');
?>
