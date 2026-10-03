<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (!isset($_SESSION)) {
    session_start();
}

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username === '' || $password === '') {
        $errors[] = 'Username and password are required.';
    } else {
        $conn = db();
        $stmt = $conn->prepare('SELECT u.*, r.slug AS role_slug FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.username = ? AND u.status = 1 LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['password'] === $password) {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role_slug'];
            auditLog('LOGIN', 'User logged in successfully');
            header('Location: dashboard.php');
            exit;
        }

        $errors[] = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | DepEd Leave System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light login-body">
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="brand-badge mx-auto mb-3">D</div>
                        <h2 class="fw-bold mb-1">DepEd SDO Ilocos Sur</h2>
                        <p class="text-muted mb-0">Leave / CSC Form No. 6 Management System</p>
                    </div>

                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo htmlspecialchars($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="login.php">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="admin" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" value="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>

                    <div class="mt-4 text-center small text-muted">
                        Demo Accounts:<br>
                        <strong>admin/password</strong> · <strong>schoolhead/password</strong> · <strong>schoolpersonnel/password</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
