<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['applicant']);

$id = (int)($_GET['id'] ?? 0);
$conn = db();
$stmt = $conn->prepare('SELECT la.*, lt.name AS leave_type_name, e.first_name, e.last_name, s.school_name, o.office_name FROM leave_applications la LEFT JOIN leave_types lt ON lt.id = la.leave_type_id LEFT JOIN employees e ON e.id = la.employee_id LEFT JOIN schools s ON s.id = e.school_id LEFT JOIN offices o ON o.id = e.office_id WHERE la.id = ? AND la.employee_id = (SELECT id FROM employees WHERE user_id = ?) LIMIT 1');
$stmt->execute([$id, $_SESSION['user_id']]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    header('Location: dashboard.php');
    exit;
}

$timeline = getApplicationTimeline($id);
include __DIR__ . '/../template/header.php';
?>
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Where Is My Leave Application?</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Application No.:</strong> <?php echo htmlspecialchars($app['application_no']); ?></div>
                        <div class="col-md-6"><strong>Status:</strong> <span class="badge bg-<?php echo statusBadge($app['status']); ?>"><?php echo htmlspecialchars($app['status']); ?></span></div>
                        <div class="col-md-6"><strong>Current Location:</strong> <?php echo htmlspecialchars($app['current_office_id'] ? 'Assigned Office' : 'Pending routing'); ?></div>
                        <div class="col-md-6"><strong>Current Approver:</strong> <?php echo htmlspecialchars($app['current_approver_id'] ? 'Assigned Approver' : 'Awaiting assignment'); ?></div>
                        <div class="col-md-6"><strong>Leave Type:</strong> <?php echo htmlspecialchars($app['leave_type_name']); ?></div>
                        <div class="col-md-6"><strong>Dates:</strong> <?php echo htmlspecialchars($app['leave_start_date'] . ' to ' . $app['leave_end_date']); ?></div>
                        <div class="col-md-6"><strong>Number of Days:</strong> <?php echo htmlspecialchars($app['number_of_days']); ?></div>
                        <div class="col-md-6"><strong>Action Required:</strong> Review / Approval</div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Application Timeline</h5>
                </div>
                <div class="card-body">
                    <?php if (!$timeline): ?>
                        No timeline yet.
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($timeline as $entry): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($entry['status']); ?></strong><br>
                                        <small><?php echo formatDateTime($entry['created_at']); ?> by <?php echo htmlspecialchars($entry['first_name'] . ' ' . $entry['last_name']); ?></small>
                                        <div class="small text-muted"><?php echo htmlspecialchars($entry['remarks'] ?? ''); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
                    <a href="leave_form.php" class="btn btn-outline-secondary">Create Another Form</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../template/footer.php'; ?>
