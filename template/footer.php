<?php
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DepEd Leave System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?php echo BASE_URL; ?>/dashboard.php">DepEd SDO Ilocos Sur</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php $user = currentUser(); if ($user): ?>
          <?php if (($user['role_slug'] ?? '') === 'administrator'): ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/admin/users.php">Users</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/admin/settings.php">Settings</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/admin/reports.php">Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/admin/backup.php">Backup</a></li>
          <?php elseif (in_array(($user['role_slug'] ?? ''), ['school_head','asds_division_approver','office_approver','hr_personnel_administrator'], true)): ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/approver/dashboard.php">Dashboard</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/applicant/dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/applicant/leave_form.php">New Application</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
      <?php if ($user): ?>
      <div class="d-flex align-items-center gap-3 text-white small">
        <span><?php echo htmlspecialchars($user['username']); ?> · <?php echo htmlspecialchars($user['role_name'] ?? ucfirst(str_replace('_',' ',$user['role_slug']))); ?></span>
        <a class="btn btn-light btn-sm" href="<?php echo BASE_URL; ?>/logout.php">Logout</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</nav>
<div class="container-fluid py-4">
