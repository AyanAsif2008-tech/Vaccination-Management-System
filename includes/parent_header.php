<?php
$current = basename($_SERVER['PHP_SELF']);
$parentName = $_SESSION['parent_name'] ?? 'Parent';
$initials  = strtoupper(substr($parentName, 0, 1) . (strpos($parentName, ' ') ? substr(strrchr($parentName, ' '), 1, 1) : ''));

function pnavActive($file, $current) {
    return $file === $current ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' · ' : '' ?>VMS Parent</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="logo">
        <img src="../assets/media/logo.png" alt="" style="width: 40px; filter: invert(1);">
      </div>
      <div class="brand-word">Parent Portal</div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-label">Overview</div>
      <a href="dashboard.php" class="nav-link <?= pnavActive('dashboard.php', $current) ?>" title="Dashboard"><i class="fa-solid fa-gauge-high"></i> <span class="nav-text">Dashboard</span></a>

      <div class="nav-label">My Family</div>
      <a href="children.php" class="nav-link <?= pnavActive('children.php', $current) ?>" title="My Children"><i class="fa-solid fa-child-reaching"></i> <span class="nav-text">My Children</span></a>
      <a href="records.php" class="nav-link <?= pnavActive('records.php', $current) ?>" title="Vaccination History"><i class="fa-solid fa-shield-heart"></i> <span class="nav-text">Vaccination History</span></a>

      <div class="nav-label">Appointments</div>
      <a href="book_appointment.php" class="nav-link <?= pnavActive('book_appointment.php', $current) ?>" title="Book Appointment"><i class="fa-solid fa-calendar-plus"></i> <span class="nav-text">Book Appointment</span></a>
      <a href="bookings.php" class="nav-link <?= pnavActive('bookings.php', $current) ?>" title="My Bookings"><i class="fa-solid fa-calendar-check"></i> <span class="nav-text">My Bookings</span></a>

      <div class="nav-label">Account</div>
      <a href="profile.php" class="nav-link <?= pnavActive('profile.php', $current) ?>" title="Profile"><i class="fa-solid fa-user-gear"></i> <span class="nav-text">Profile</span></a>
      <a href="logout.php" class="nav-link" title="Logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> <span class="nav-text">Logout</span></a>
    </nav>

    <div class="sidebar-foot">Vaccination Management System<br>v1.0 &middot; Parent Portal</div>
  </aside>

  <div class="main-col">
    <header class="topbar">
      <div style="display:flex;align-items:center;gap:14px;">
        <button class="sidebar-toggle" id="sidebarToggle" title="Toggle sidebar" aria-label="Toggle sidebar">
          <i class="fa-solid fa-bars"></i>
        </button>
        <div>
          <div class="topbar-title"><?= e($pageTitle ?? 'Dashboard') ?></div>
          <?php if (!empty($pageSubtitle)): ?><div class="topbar-sub"><?= e($pageSubtitle) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="topbar-right">
        <a href="../index.php" class="btn btn-outline btn-sm no-print"><i class="fa-solid fa-house"></i> Home</a>
        <div style="position:relative;">
          <div class="admin-chip" id="adminChip">
            <div class="admin-avatar"><?= e($initials ?: 'P') ?></div>
            <div>
              <div class="admin-chip-name"><?= e($parentName) ?></div>
              <div class="admin-chip-role">Parent</div>
            </div>
            <i class="fa-solid fa-chevron-down" style="font-size:11px;color:var(--slate);margin-left:2px;"></i>
          </div>
          <div id="adminDropdown" class="admin-dropdown" style="position:absolute;right:0;top:52px;background:#fff;border:1px solid var(--line);border-radius:12px;box-shadow:var(--shadow-lift);width:180px;overflow:hidden;z-index:50;">
            <a href="profile.php" style="display:flex;align-items:center;gap:8px;padding:11px 16px;font-size:13.5px;color:var(--ink);"><i class="fa-solid fa-user-gear" style="width:16px;"></i> Profile</a>
            <a href="logout.php" style="display:flex;align-items:center;gap:8px;padding:11px 16px;font-size:13.5px;color:var(--coral);border-top:1px solid var(--line);"><i class="fa-solid fa-arrow-right-from-bracket" style="width:16px;"></i> Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="content">
      <?php $flash = flash_get(); if ($flash): ?>
        <div class="flash-alert flash-<?= e($flash['type']) ?>">
          <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>
