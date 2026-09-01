<?php
$current = basename($_SERVER['PHP_SELF']);
$hospitalName = $_SESSION['hospital_name'] ?? 'Hospital';
$initials = strtoupper(substr($hospitalName, 0, 2));

function hnavActive($file, $current) {
    return $file === $current ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' · ' : '' ?>VMS Hospital</title>
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
      <div class="brand-word">Hospital Portal
        <!-- <small>Hospital Portal</small> -->
    </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-label">Overview</div>
      <a href="dashboard.php" class="nav-link <?= hnavActive('dashboard.php', $current) ?>" title="Dashboard"><i class="fa-solid fa-gauge-high"></i> <span class="nav-text">Dashboard</span></a>

      <div class="nav-label">Operations</div>
      <a href="bookings.php" class="nav-link <?= hnavActive('bookings.php', $current) ?>" title="Bookings">
        <i class="fa-solid fa-calendar-check"></i> <span class="nav-text">Bookings</span>
        <?php if (!empty($GLOBALS['pendingBookingsCount'])): ?>
          <span class="nav-badge"><?= (int)$GLOBALS['pendingBookingsCount'] ?></span>
        <?php endif; ?>
      </a>
      <a href="vaccines.php" class="nav-link <?= hnavActive('vaccines.php', $current) ?>" title="Vaccine Catalog"><i class="fa-solid fa-vial-circle-check"></i> <span class="nav-text">Vaccine Catalog</span></a>

      <div class="nav-label">Account</div>
      <a href="profile.php" class="nav-link <?= hnavActive('profile.php', $current) ?>" title="Profile"><i class="fa-solid fa-user-gear"></i> <span class="nav-text">Profile</span></a>
      <a href="logout.php" class="nav-link" title="Logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> <span class="nav-text">Logout</span></a>
    </nav>

    <div class="sidebar-foot">Vaccination Management System<br>v1.0 &middot; Hospital Portal</div>
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
            <div class="admin-avatar"><?= e($initials ?: 'H') ?></div>
            <div>
              <div class="admin-chip-name"><?= e($hospitalName) ?></div>
              <div class="admin-chip-role">Hospital</div>
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
