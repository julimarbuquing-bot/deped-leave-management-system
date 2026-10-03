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
        $errors[] = 'Please enter both username and password.';
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

include __DIR__ . '/template/header.php';
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <h3 class="text-center mb-4">Login</h3>
          <?php if ($errors): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errors[0]); ?></div>
          <?php endif; ?>
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" name="username" value="admin" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="password" value="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Sign In</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/template/footer.php'; ?>
