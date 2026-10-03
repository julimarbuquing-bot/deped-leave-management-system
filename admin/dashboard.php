<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['school_head', 'asds_division_approver', 'office_approver', 'hr_personnel_administrator']);

$id = (int)($_GET['id'] ?? 0);
$conn = db();
$stmt = $conn->prepare('SELECT la.*, lt.name AS leave_type_name, e.first_name, e.last_name, s.school_name, o.office_name, p.title AS position_title FROM leave_applications la LEFT JOIN leave_types lt ON lt.id = la.leave_type_id LEFT JOIN employees e ON e.id = la.employee_id LEFT JOIN schools s ON s.id = e.school_id LEFT JOIN offices o ON o.id = e.office_id LEFT JOIN positions p ON p.id = e.position_id WHERE la.id = ? LIMIT 1');
$stmt->execute([$id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$app) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $remarks = trim($_POST['remarks'] ?? '');
    $status = match ($action) {
        'approve' => 'APPROVED',
        'return' => 'RETURNED_FOR_CORRECTION',
        'disapprove' => 'DISAPPROVED',
        default => $app['status'],
    };

    if ($action === 'return' && $remarks === '') {
        setFlash('error', 'A return reason is required.');
        header('Location: application_view.php?id=' . $id);
        exit;
    }

    $conn->prepare('UPDATE leave_applications SET status = ?, remarks = ?, date_approved = ? WHERE id = ?')->execute([
        $status,
        $remarks,
        ($action === 'approve') ? date('Y-m-d H:i:s') : null,
        $id
    ]);

    $user = currentUser();
    $conn->prepare('INSERT INTO approval_history (application_id, action_by, status, remarks, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())')->execute([
        $id,
        $user['id'],
        $status,
        $remarks,
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ]);

    setFlash('success', 'Application action recorded successfully.');
    header('Location: dashboard.php');
    exit;
}

include __DIR__ . '/../template/header.php';
?>
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><?php echo htmlspecialchars($app['application_no']); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Applicant:</strong> <?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></div>
                        <div class="col-md-6"><strong>Position:</strong> <?php echo htmlspecialchars($app['position_title']); ?></div>
                        <div class="col-md-6"><strong>School/Office:</strong> <?php echo htmlspecialchars($app['school_name'] ?? $app['office_name']); ?></div>
                        <div class="col-md-6"><strong>Leave Type:</strong> <?php echo htmlspecialchars($app['leave_type_name']); ?></div>
                        <div class="col-md-6"><strong>Dates:</strong> <?php echo htmlspecialchars($app['leave_start_date'] . ' to ' . $app['leave_end_date']); ?></div>
                        <div class="col-md-6"><strong>Days:</strong> <?php echo htmlspecialchars($app['number_of_days']); ?></div>
                        <div class="col-md-12"><strong>Reason:</strong> <?php echo nl2br(htmlspecialchars($app['reason'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Action</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="application_view.php?id=<?php echo (int)$id; ?>">
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="action" value="approve" class="btn btn-success">Approve</button>
                            <button type="submit" name="action" value="return" class="btn btn-warning">Return for Correction</button>
                            <button type="submit" name="action" value="disapprove" class="btn btn-danger">Disapprove</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../template/footer.php'; ?>
