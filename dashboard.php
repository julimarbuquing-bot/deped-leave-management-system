<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$user = currentUser();
if ($user) {
    $role = strtolower($user['role_slug'] ?? 'applicant');
    if ($role === 'administrator') {
        header('Location: /leave_system/admin/dashboard.php');
        exit;
    }
    if (in_array($role, ['school_head','asds_division_approver','office_approver','hr_personnel_administrator'], true)) {
        header('Location: /leave_system/approver/dashboard.php');
        exit;
    }
    header('Location: /leave_system/applicant/dashboard.php');
    exit;
}

header('Location: /leave_system/login.php');
exit;
