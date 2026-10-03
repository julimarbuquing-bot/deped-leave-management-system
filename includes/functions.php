<?php

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
    $stmt->execute(array($userId));
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getEmployeeById($employeeId) {
    $conn = db();
    $stmt = $conn->prepare('SELECT e.*, s.school_name, o.office_name, p.title AS position_title FROM employees e LEFT JOIN schools s ON s.id = e.school_id LEFT JOIN offices o ON o.id = e.office_id LEFT JOIN positions p ON p.id = e.position_id WHERE e.id = ? LIMIT 1');
    $stmt->execute(array($employeeId));
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getRoleNameBySlug($slug) {
    $conn = db();
    $stmt = $conn->prepare('SELECT name FROM roles WHERE slug = ? LIMIT 1');
    $stmt->execute(array($slug));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['name'] : ucfirst(str_replace('_', ' ', $slug));
}

function auditLog($action, $details = '', $applicationId = null) {
    $user = currentUser();
    $conn = db();
    $stmt = $conn->prepare('INSERT INTO audit_logs (user_id, action, details, application_id, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $userId = $user ? $user['id'] : null;
    $ipAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    $stmt->execute(array($userId, $action, $details, $applicationId, $ipAddress));
}

function setFlash($key, $message) {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = array();
    }
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
    $map = array(
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
    );
    $statusUpper = strtoupper($status);
    return isset($map[$statusUpper]) ? $map[$statusUpper] : 'secondary';
}

function canViewApplication($applicationId) {
    $user = currentUser();
    if (!$user) {
        return false;
    }

    $conn = db();
    $stmt = $conn->prepare('SELECT e.* FROM leave_applications la LEFT JOIN employees e ON e.id = la.employee_id WHERE la.id = ? LIMIT 1');
    $stmt->execute(array($applicationId));
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$application) {
        return false;
    }

    $role = strtolower(isset($user['role_slug']) ? $user['role_slug'] : '');
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
        return (int)$application['current_approver_id'] === (int)$user['id'] || (int)$application['current_office_id'] === (int)$userEmployee['office_id'];
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
    $result = array();
    foreach ($rows as $r) {
        $result[] = $r['holiday_date'];
    }
    return $result;
}

function getCurrentApproverForApplication($applicationId) {
    $conn = db();
    $stmt = $conn->prepare('SELECT la.current_approver_id, u.username, e.first_name, e.last_name, s.school_name, o.office_name FROM leave_applications la LEFT JOIN users u ON u.id = la.current_approver_id LEFT JOIN employees e ON e.user_id = u.id LEFT JOIN schools s ON s.id = e.school_id LEFT JOIN offices o ON o.id = e.office_id WHERE la.id = ? LIMIT 1');
    $stmt->execute(array($applicationId));
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getApplicationTimeline($applicationId) {
    $conn = db();
    $stmt = $conn->prepare('SELECT ah.*, u.username, e.first_name, e.last_name FROM approval_history ah LEFT JOIN users u ON u.id = ah.action_by LEFT JOIN employees e ON e.user_id = u.id WHERE ah.application_id = ? ORDER BY ah.created_at ASC');
    $stmt->execute(array($applicationId));
    return $stmt->fetchAll();
}

function statusList() {
    return array(
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
    );
}

function redirect($location) {
    header('Location: ' . $location);
    exit;
}

function formatNumber($value) {
    return number_format((float)$value, 2, '.', ',');
}
