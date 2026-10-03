<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['applicant']);

$employee = getEmployeeByUserId(currentUser()['id']);
$leaveTypes = getLeaveTypes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leaveTypeId = (int)($_POST['leave_type_id'] ?? 0);
    $startDate = trim($_POST['leave_start_date'] ?? '');
    $endDate = trim($_POST['leave_end_date'] ?? '');
    $days = (float)($_POST['number_of_days'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    $draftSubmit = $_POST['action'] ?? 'save';

    $conn = db();
    $applicationNo = generateApplicationNumber();
    $stmt = $conn->prepare('INSERT INTO leave_applications (application_no, employee_id, leave_type_id, leave_start_date, leave_end_date, number_of_days, reason, status, is_draft, current_approver_id, current_office_id, date_submitted, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, ?, NOW())');
    $status = ($draftSubmit === 'save') ? 'DRAFT' : 'SUBMITTED';
    $submittedAt = ($draftSubmit === 'save') ? null : date('Y-m-d H:i:s');
    $stmt->execute([$applicationNo, $employee['id'], $leaveTypeId, $startDate, $endDate, $days, $reason, $status, ($draftSubmit === 'save') ? 1 : 0, $submittedAt]);

    $applicationId = $conn->lastInsertId();
    auditLog('APPLICATION_' . $status, 'Leave application created', $applicationId);

    if ($draftSubmit !== 'save') {
        $currentApprover = 2;
        $currentOffice = 1;
        $conn->prepare('UPDATE leave_applications SET current_approver_id = ?, current_office_id = ?, status = ?, is_draft = 0, date_submitted = NOW() WHERE id = ?')->execute([$currentApprover, $currentOffice, 'PENDING_APPROVAL', $applicationId]);

        $conn->prepare('INSERT INTO approval_history (application_id, action_by, status, remarks, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())')->execute([$applicationId, $employee['id'], 'SUBMITTED', 'Application submitted for review', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
        $conn->prepare('INSERT INTO email_notifications (application_id, recipient, email_address, notification_type, subject, content, delivery_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())')->execute([$applicationId, 'Applicant', $employee['preferred_email'], 'APPLICATION_SUBMITTED', 'Application Submitted', 'Submitted', 'PENDING',]);
    }

    setFlash('success', 'Application saved successfully.');
    header('Location: dashboard.php');
    exit;
}

include __DIR__ . '/../template/header.php';
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">CSC Form No. 6 - Application for Leave</h4>
                </div>
                <div class="card-body">
                    <form method="post" action="leave_form.php" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Applicant Name</label>
                                <input class="form-control" value="<?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Position</label>
                                <input class="form-control" value="<?php echo htmlspecialchars($employee['position_title'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">School/Office</label>
                                <input class="form-control" value="<?php echo htmlspecialchars($employee['school_name'] ?? $employee['office_name'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Filing</label>
                                <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Type of Leave</label>
                                <select name="leave_type_id" class="form-select" required>
                                    <option value="">Select leave type</option>
                                    <?php foreach ($leaveTypes as $type): ?>
                                        <option value="<?php echo (int)$type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Inclusive Dates (From)</label>
                                <input type="date" name="leave_start_date" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Inclusive Dates (To)</label>
                                <input type="date" name="leave_end_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Number of Days</label>
                                <input type="number" name="number_of_days" step="0.5" min="0.5" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reason / Details</label>
                                <textarea name="reason" rows="4" class="form-control" required></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Supporting Documents</label>
                                <input type="file" name="supporting_files[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" name="action" value="save" class="btn btn-secondary">Save Draft</button>
                            <button type="submit" name="action" value="submit" class="btn btn-primary">Submit Application</button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../template/footer.php'; ?>
