<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user = currentUser();
$role = strtolower($user['role_slug'] ?? 'applicant');

if ($role === 'administrator') {
    header('Location: admin/dashboard.php');
    exit;
}
if (in_array($role, ['school_head', 'asds_division_approver', 'office_approver', 'hr_personnel_administrator'], true)) {
    header('Location: approver/dashboard.php');
    exit;
}
header('Location: applicant/dashboard.php');
