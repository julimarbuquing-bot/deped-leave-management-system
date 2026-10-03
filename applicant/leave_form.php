<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['applicant']);

$conn = db();
$user = currentUser();
$employee = getEmployeeByUserId($user['id']);

$stmt = $conn->prepare('SELECT la.*, lt.name AS leave_type_name FROM leave_applications la LEFT JOIN leave_types lt ON lt.id = la.leave_type_id WHERE la.employee_id = ? ORDER BY la.created_at DESC');
$stmt->execute([$employee['id']]);
$applications = $stmt->fetchAll();

$creditRows = $conn->prepare('SELECT lc.*, lt.name AS leave_type_name FROM leave_credits lc LEFT JOIN leave_types lt ON lt.id = lc.leave_type_id WHERE lc.employee_id = ?');
$creditRows->execute([$employee['id']]);
$credits = $creditRows->fetchAll();

include __DIR__ . '/../template/header.php';
?>
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">Applicant Dashboard</h2>
            <p class="text-muted mb-0">Welcome, <?php echo htmlspecialchars(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? '')); ?></p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="leave_form.php" class="btn btn-primary">Create Leave Application</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Leave Credits</div>
                    <h4 class="mt-2 mb-0">Vacation Leave</h4>
                    <div class="display-6 fw-bold"><?php echo number_format((float) ($credits[0]['remaining'] ?? 0), 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Leave Credits</div>
                    <h4 class="mt-2 mb-0">Sick Leave</h4>
                    <div class="display-6 fw-bold"><?php echo number_format((float) ($credits[1]['remaining'] ?? 0), 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Applications</div>
                    <h4 class="mt-2 mb-0">Summary</h4>
                    <div class="mt-2"><?php echo count($applications); ?> total records</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">My Applications</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Application No.</th>
                                <th>Leave Type</th>
                                <th>Status</th>
                                <th>Dates</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$applications): ?>
                                <tr><td colspan="5" class="text-center text-muted">No applications found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($app['application_no']); ?></td>
                                        <td><?php echo htmlspecialchars($app['leave_type_name'] ?? '—'); ?></td>
                                        <td><span class="badge bg-<?php echo statusBadge($app['status']); ?>"><?php echo htmlspecialchars($app['status']); ?></span></td>
                                        <td><?php echo htmlspecialchars(($app['leave_start_date'] ?? '') . ' to ' . ($app['leave_end_date'] ?? '')); ?></td>
                                        <td><a href="track_application.php?id=<?php echo (int)$app['id']; ?>" class="btn btn-sm btn-outline-primary">Track</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0">Where Is My Application?</h5></div>
                <div class="card-body">
                    <?php $latest = $applications[0] ?? null; ?>
                    <?php if ($latest): ?>
                        <div class="mb-2"><strong>Status:</strong><br><?php echo htmlspecialchars($latest['status']); ?></div>
                        <div class="mb-2"><strong>Current Location:</strong><br><?php echo htmlspecialchars($latest['current_office_id'] ? 'Assigned Office/Unit' : 'Route Pending'); ?></div>
                        <div class="mb-2"><strong>Current Approver:</strong><br><?php echo htmlspecialchars($latest['current_approver_id'] ? 'Assigned Approver' : 'Not assigned yet'); ?></div>
                        <div class="text-center mt-3">
                            <a href="track_application.php?id=<?php echo (int)$latest['id']; ?>" class="btn btn-primary btn-sm">View Tracking</a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No active application yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../template/footer.php'; ?>
