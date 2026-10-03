<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['administrator']);

$conn = db();
$userCount = $conn->query('SELECT COUNT(*) FROM users')->fetchColumn();
$schoolCount = $conn->query('SELECT COUNT(*) FROM schools')->fetchColumn();
$appCount = $conn->query('SELECT COUNT(*) FROM leave_applications')->fetchColumn();
$approvalCount = $conn->query('SELECT COUNT(*) FROM approval_history')->fetchColumn();

include __DIR__ . '/../template/header.php';
?>
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold">Administrator Dashboard</h2>
            <p class="text-muted mb-0">System administration and workflow monitoring</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted">Users</div><h3><?php echo (int)$userCount; ?></h3></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted">Schools</div><h3><?php echo (int)$schoolCount; ?></h3></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted">Applications</div><h3><?php echo (int)$appCount; ?></h3></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted">Audit Records</div><h3><?php echo (int)$approvalCount; ?></h3></div></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h5 class="mb-0">Management Modules</h5></div>
                <div class="card-body d-grid gap-2">
                    <a href="users.php" class="btn btn-outline-primary">User Management</a>
                    <a href="settings.php" class="btn btn-outline-primary">System Settings</a>
                    <a href="reports.php" class="btn btn-outline-primary">Reports</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h5 class="mb-0">System Tools</h5></div>
                <div class="card-body d-grid gap-2">
                    <a href="../dashboard.php" class="btn btn-outline-secondary">Go to Main Dashboard</a>
                    <a href="../logout.php" class="btn btn-outline-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../template/footer.php'; ?>
