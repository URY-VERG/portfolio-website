<?php
// 🔥 VERY IMPORTANT (must be before session_start)
ini_set('session.gc_maxlifetime', 7 * 24 * 60 * 60);

session_set_cookie_params([
    'lifetime' => 7 * 24 * 60 * 60,
    'path' => '/',
    'secure' => false,     // localhost साठी false
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
?>