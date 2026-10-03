<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['administrator']);

$conn = db();
$settings = $conn->query('SELECT * FROM system_settings ORDER BY setting_key ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['setting'] ?? [] as $key => $value) {
        $stmt = $conn->prepare('INSERT INTO system_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()');
        $stmt->execute([$key, $value]);
    }
    setFlash('success', 'System settings updated.');
    header('Location: settings.php');
    exit;
}

include __DIR__ . '/../template/header.php';
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">System Settings</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="post" action="settings.php">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">SDO Name</label>
                        <input class="form-control" name="setting[sdo_name]" value="Department of Education – Schools Division of Ilocos Sur">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SDO Address</label>
                        <input class="form-control" name="setting[sdo_address]" value="Vigan City, Ilocos Sur">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Host</label>
                        <input class="form-control" name="setting[smtp_host]" value="">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Port</label>
                        <input class="form-control" name="setting[smtp_port]" value="587">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Username</label>
                        <input class="form-control" name="setting[smtp_username]" value="">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" class="form-control" name="setting[smtp_password]" value="">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Encryption</label>
                        <select class="form-select" name="setting[smtp_encryption]">
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sender Email</label>
                        <input class="form-control" name="setting[smtp_from_email]" value="no-reply@deped.gov.ph">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../template/footer.php'; ?>
