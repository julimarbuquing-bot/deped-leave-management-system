<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['school_head', 'asds_division_approver', 'office_approver', 'hr_personnel_administrator']);

$user = currentUser();
$employee = getEmployeeByUserId($user['id']);
$conn = db();

$sql = 'SELECT la.*, lt.name AS leave_type_name, e.first_name, e.last_name FROM leave_applications la LEFT JOIN employees e ON e.id = la.employee_id LEFT JOIN leave_types lt ON lt.id = la.leave_type_id WHERE la.status IN ("PENDING_APPROVAL","RETURNED_FOR_CORRECTION","RESUBMITTED") ORDER BY la.created_at DESC';
$stmt = $conn->query($sql);
$applications = $stmt->fetchAll();

include __DIR__ . '/../template/header.php';
?>
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">Approver Dashboard</h2>
            <p class="text-muted mb-0">Applications requiring action</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Applicant</th>
                        <th>Leave Type</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$applications): ?>
                        <tr><td colspan="6" class="text-center text-muted">No applications pending.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($app['application_no']); ?></td>
                            <td><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['leave_type_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['leave_start_date'] . ' to ' . $app['leave_end_date']); ?></td>
                            <td><span class="badge bg-<?php echo statusBadge($app['status']); ?>"><?php echo htmlspecialchars($app['status']); ?></span></td>
                            <td><a href="application_view.php?id=<?php echo (int)$app['id']; ?>" class="btn btn-sm btn-primary">Review</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../template/footer.php'; ?>
