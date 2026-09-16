<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/parent_auth_check.php';

$parentId = $_SESSION['parent_id'];

$totalChildren = $pdo->prepare("SELECT COUNT(*) FROM child WHERE Parent_ID = ?");
$totalChildren->execute([$parentId]);
$totalChildren = $totalChildren->fetchColumn();

$stmt = $pdo->prepare("
    SELECT b.Status, COUNT(*) c FROM booking_appointment b
    JOIN child c2 ON c2.Child_ID = b.Child_ID
    WHERE c2.Parent_ID = ? GROUP BY b.Status
");
$stmt->execute([$parentId]);
$statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$completedStmt = $pdo->prepare("
    SELECT COUNT(*) FROM vaccination_record vr
    JOIN child c2 ON c2.Child_ID = vr.Child_ID
    WHERE c2.Parent_ID = ? AND vr.Status IN ('Vaccinated','Completed')
");
$completedStmt->execute([$parentId]);
$completedCount = $completedStmt->fetchColumn();

$childrenStmt = $pdo->prepare("SELECT * FROM child WHERE Parent_ID = ? ORDER BY Child_ID DESC");
$childrenStmt->execute([$parentId]);
$children = $childrenStmt->fetchAll();

$upcomingStmt = $pdo->prepare("
    SELECT b.*, c.Child_Name, h.Hospital_Name, v.Vaccine_Name
    FROM booking_appointment b
    JOIN child c ON c.Child_ID = b.Child_ID
    JOIN hospital h ON h.Hospital_ID = b.Hospital_ID
    JOIN vaccine v ON v.Vaccine_ID = b.Vaccine_ID
    WHERE c.Parent_ID = ? AND b.Status IN ('Pending','Approved')
    ORDER BY b.Appointment_Date ASC LIMIT 5
");
$upcomingStmt->execute([$parentId]);
$upcoming = $upcomingStmt->fetchAll();

$pageTitle = 'Dashboard';
$pageSubtitle = 'A quick look at your family\'s vaccination status.';
include '../includes/parent_header.php';
?>

<div class="stat-grid" style="grid-template-columns:repeat(4,1fr);">
  <div class="stat-card tone-teal">
    <div class="stat-icon"><i class="fa-solid fa-child-reaching"></i></div>
    <div class="stat-number"><?= (int)$totalChildren ?></div>
    <div class="stat-label">My Children</div>
  </div>
  <div class="stat-card tone-amber">
    <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
    <div class="stat-number"><?= (int)($statusCounts['Pending'] ?? 0) ?></div>
    <div class="stat-label">Pending Requests</div>
  </div>
  <div class="stat-card tone-blue">
    <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
    <div class="stat-number"><?= (int)($statusCounts['Approved'] ?? 0) ?></div>
    <div class="stat-label">Approved Bookings</div>
  </div>
  <div class="stat-card tone-ink">
    <div class="stat-icon"><i class="fa-solid fa-shield-heart"></i></div>
    <div class="stat-number"><?= (int)$completedCount ?></div>
    <div class="stat-label">Completed Vaccinations</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1.3fr 1fr;gap:20px;" class="dash-grid">
  <div class="card-panel">
    <div class="card-panel-head">
      <h3>Upcoming Appointments</h3>
      <a href="bookings.php" class="btn btn-outline btn-sm">View all</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Child</th><th>Hospital</th><th>Vaccine</th><th>Appointment</th><th>Status</th></tr></thead>
        <tbody>
        <?php if (!$upcoming): ?>
          <tr><td colspan="5"><div class="empty-state" style="padding:24px;"><i class="fa-solid fa-calendar-plus"></i><h4>No upcoming appointments</h4><p><a href="book_appointment.php">Book one now</a></p></div></td></tr>
        <?php endif; ?>
        <?php foreach ($upcoming as $b): ?>
          <tr>
            <td class="cell-primary"><?= e($b['Child_Name']) ?></td>
            <td><?= e($b['Hospital_Name']) ?></td>
            <td><?= e($b['Vaccine_Name']) ?></td>
            <td class="cell-sub"><?= format_date($b['Appointment_Date']) ?></td>
            <td><?= status_badge($b['Status']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card-panel">
    <div class="card-panel-head">
      <h3>My Children</h3>
      <a href="children.php" class="btn btn-outline btn-sm">Manage</a>
    </div>
    <div class="card-panel-body" style="padding-top:10px;">
      <?php if (!$children): ?>
        <div class="empty-state" style="padding:20px;"><i class="fa-solid fa-child-reaching"></i><h4>No children added yet</h4><p><a href="children.php">Add your first child</a></p></div>
      <?php endif; ?>
      <?php foreach ($children as $c): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--line);">
          <div class="avatar-sm" style="margin-right:0;"><?= e(strtoupper(substr($c['Child_Name'],0,1))) ?></div>
          <div style="flex:1;">
            <div style="font-weight:600;font-size:13.8px;color:var(--ink);"><?= e($c['Child_Name']) ?></div>
            <div class="cell-sub"><?= calculate_age($c['Date_Of_Birth']) ?> old</div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<style>@media (max-width: 992px) { .dash-grid { grid-template-columns: 1fr !important; } }</style>

<?php include '../includes/footer.php'; ?>
