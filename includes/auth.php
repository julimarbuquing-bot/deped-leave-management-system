<?php
function requireLogin() {
    if (empty($_SESSION['user_id'])) {
        header('Location: /leave_system/login.php');
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    $user = currentUser();
    $roles = array_map('strtolower', (array) $roles);
    if (!$user || !in_array(strtolower($user['role_slug'] ?? ''), $roles, true)) {
        http_response_code(403);
        echo 'Access denied.';
        exit;
    }
}

function currentUser() {
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $conn = db();
    $stmt = $conn->prepare('SELECT u.*, r.slug AS role_slug, r.name AS role_name FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
