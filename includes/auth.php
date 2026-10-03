<?php
session_start();

function currentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $conn = db();
    $stmt = $conn->prepare('SELECT u.*, r.slug AS role_slug, r.name AS role_name FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function userHasRole($slug) {
    $user = currentUser();
    if (!$user) {
        return false;
    }
    return strtolower($user['role_slug']) === strtolower($slug);
}

function hasAnyRole($slugs) {
    $user = currentUser();
    if (!$user) {
        return false;
    }
    $slugs = array_map('strtolower', (array)$slugs);
    return in_array(strtolower($user['role_slug']), $slugs, true);
}

function requireRole($slugs) {
    requireLogin();
    if (!hasAnyRole($slugs)) {
        http_response_code(403);
        echo 'Access denied.';
        exit;
    }
}

function formatDate($date) {
    if (empty($date)) {
        return '—';
    }
    return date('F j, Y', strtotime($date));
}

function formatDateTime($dateTime) {
    if (empty($dateTime)) {
        return '—';
    }
    return date('F j, Y h:i A', strtotime($dateTime));
}

function generateApplicationNumber() {
    $year = date('Y');
    $conn = db();
    $stmt = $conn->query('SELECT COUNT(*) AS total FROM leave_applications');
    $count = (int) $stmt->fetchColumn();
    $sequence = $count + 1;
    return 'LEAVE-' . $year . '-' . str_pad((string)$sequence, 6, '0', STR_PAD_LEFT);
}

function getEmployeeByUserId($userId) {
    $conn = db();
    $stmt = $conn->prepare('SELECT e.*, s.school_name, o.office_name, p.title AS position_title FROM employees e LEFT JOIN schools s ON s.id = e.school_id LEFT JOIN offices o ON o.id = e.office_id LEFT JOIN positions p ON p.id = e.position_id WHERE e.user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getEmployeeById($employeeId) {
    $conn = db();
    $stmt = $conn->prepare('SELECT e.*, s.school_name, o.office_name, p.title AS position_title FROM employees e LEFT JOIN schools s ON s.id = e.school_id LEFT JOIN offices o ON o.id = e.office_id LEFT JOIN positions p ON p.id = e.position_id WHERE e.id = ? LIMIT 1');
    $stmt->execute([$employeeId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getRoleNameBySlug($slug) {
    $conn = db();
    $stmt = $conn->prepare('SELECT name FROM roles WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['name'] ?? ucfirst(str_replace('_', ' ', $slug));
}

function auditLog($action, $details = '', $applicationId = null) {
    $user = currentUser();
    $conn = db();
    $stmt = $conn->prepare('INSERT INTO audit_logs (user_id, action, details, application_id, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->execute([
        $user['id'] ?? null,
        $action,
        $details,
        $applicationId,
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ]);
}

function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function getFlash($key) {
    if (!empty($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

function statusBadge($status) {
    $map = [
        'DRAFT' => 'secondary',
        'SUBMITTED' => 'primary',
        'PENDING_APPROVAL' => 'warning',
        'RETURNED_FOR_CORRECTION' => 'info',
        'RESUBMITTED' => 'primary',
        'APPROVED' => 'success',
        'DISAPPROVED' => 'danger',
        'CANCELLED' => 'dark',
        'RELEASED' => 'success',
        'ARCHIVED' => 'secondary'
    ];
    return $map[strtoupper($status)] ?? 'secondary';
}

function canViewApplication($applicationId) {
    $user = currentUser();
    if (!$user) {
        return false;
    }

    $conn = db();
    $stmt = $conn->prepare('SELECT e.* FROM leave_applications la LEFT JOIN employees e ON e.id = la.employee_id WHERE la.id = ? LIMIT 1');
    $stmt->execute([$applicationId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$application) {
        return false;
    }

    $role = strtolower($user['role_slug']);
    $userEmployee = getEmployeeByUserId($user['id']);

    if ($role === 'administrator' || $role === 'hr_personnel_administrator') {
        return true;
    }

    if ($userEmployee && (int)$userEmployee['id'] === (int)$application['id']) {
        return true;
    }

    if ($role === 'school_head') {
        return (int)$application['school_id'] === (int)$userEmployee['school_id'];
    }

    if ($role === 'asds_division_approver' || $role === 'office_approver') {
        return (int)$application['current_approver_id'] === (int)$userEmployee['user_id'] || (int)$application['current_office_id'] === (int)$userEmployee['office_id'];
    }

    return false;
}

function getLeaveTypes() {
    $conn = db();
    $stmt = $conn->query('SELECT * FROM leave_types WHERE status = 1 ORDER BY name ASC');
    return $stmt->fetchAll();
}

function getHolidayDates() {
    $conn = db();
    $stmt = $conn->query('SELECT holiday_date FROM holidays WHERE status = 1');
    $rows = $stmt->fetchAll();
    return array_map(fn($r) => $r['holiday_date'], $rows);
}

function getCurrentApproverForApplication($applicationId) {
    $conn = db();
    $stmt = $conn->prepare('SELECT la.current_approver_id, u.username, e.first_name, e.last_name, s.school_name, o.office_name FROM leave_applications la LEFT JOIN users u ON u.id = la.current_approver_id LEFT JOIN employees e ON e.user_id = u.id LEFT JOIN schools s ON s.id = e.school_id LEFT JOIN offices o ON o.id = e.office_id WHERE la.id = ? LIMIT 1');
    $stmt->execute([$applicationId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getApplicationTimeline($applicationId) {
    $conn = db();
    $stmt = $conn->prepare('SELECT ah.*, u.username, e.first_name, e.last_name FROM approval_history ah LEFT JOIN users u ON u.id = ah.action_by LEFT JOIN employees e ON e.user_id = u.id WHERE ah.application_id = ? ORDER BY ah.created_at ASC');
    $stmt->execute([$applicationId]);
    return $stmt->fetchAll();
}

function statusList() {
    return [
        'DRAFT',
        'SUBMITTED',
        'PENDING_APPROVAL',
        'RETURNED_FOR_CORRECTION',
        'RESUBMITTED',
        'APPROVED',
        'DISAPPROVED',
        'CANCELLED',
        'RELEASED',
        'ARCHIVED'
    ];
}

function redirect($location) {
    header('Location: ' . $location);
    exit;
}

function formatNumber($value) {
    return number_format((float)$value, 2, '.', ',');
}
