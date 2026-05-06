<?php
include "php/session.php";

// 🔥 Remove all session data
$_SESSION = [];

// 🔥 Destroy session
session_destroy();

// 🔥 Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// 🔥 Redirect
header("Location: login.php");
exit();
?>