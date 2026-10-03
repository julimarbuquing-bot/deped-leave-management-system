<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = currentUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

$role = isset($user['role_slug']) ? strtolower($user['role_slug']) : 'applicant';

include __DIR__ . '/../template/header.php';
?>
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold">My Dashboard</h2>
            <p class="text-muted">Welcome, <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars(getRoleNameBySlug($role)); ?>)</p>
        </div>
    </div>

    <div class="alert alert-info">
        <strong>System Status:</strong> The application dashboard is now loading. Please navigate using the menu above.
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h5 class="mb-0">Quick Links</h5></div>
                <div class="card-body d-grid gap-2">
                    <?php if ($role === 'applicant'): ?>
                        <a href="applicant/dashboard.php" class="btn btn-outline-primary">My Applications</a>
                        <a href="applicant/leave_form.php" class="btn btn-outline-success">Submit New Application</a>
                    <?php elseif ($role === 'administrator'): ?>
                        <a href="admin/dashboard.php" class="btn btn-outline-primary">Admin Dashboard</a>
                        <a href="admin/users.php" class="btn btn-outline-primary">User Management</a>
                    <?php else: ?>
                        <a href="approver/dashboard.php" class="btn btn-outline-primary">Approver Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h5 class="mb-0">Account</h5></div>
                <div class="card-body d-grid gap-2">
                    <a href="logout.php" class="btn btn-outline-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../template/footer.php'; ?>
