<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    auditLog('LOGOUT', 'User logged out');
}

session_unset();
session_destroy();
header('Location: login.php');
exit;
