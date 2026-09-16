<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/hospital_auth_check.php';

$hospitalId = $_SESSION['hospital_id'];

$stmt = $pdo->prepare("SELECT Status, COUNT(*) c FROM booking_appointment WHERE Hospital_ID = ? GROUP BY Status");
$stmt->execute([$hospitalId]);
$statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$pendingBookingsCount = $statusCounts['Pending'] ?? 0;

$completedStmt = $pdo->prepare("
    SELECT COUNT(*) FROM vaccination_record vr
    JOIN booking_appointment b ON b.Booking_ID = vr.Booking_ID
    WHERE b.Hospital_ID = ? AND vr.Status IN ('Vaccinated','Completed')
");
$completedStmt->execute([$hospitalId]);
$completedCount = $completedStmt->fetchColumn();

$recentStmt = $pdo->prepare("
    SELECT b.*, c.Child_Name, v.Vaccine_Name
    FROM booking_appointment b
    JOIN child c ON c.Child_ID = b.Child_ID
    JOIN vaccine v ON v.Vaccine_ID = b.Vaccine_ID
    WHERE b.Hospital_ID = ?
    ORDER BY b.Booking_ID DESC LIMIT 6
");
$recentStmt->execute([$hospitalId]);
$recentBookings = $recentStmt->fetchAll();

$pageTitle = 'Dashboard';
$pageSubtitle = 'An overview of your hospital\'s vaccination activity.';
include '../includes/hospital_header.php';
?>

<div class="stat-grid" style="grid-template-columns:repeat(4,1fr);">
  <div class="stat-card tone-amber">
    <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
    <div class="stat-number"><?= (int)($statusCounts['Pending'] ?? 0) ?></div>
    <div class="stat-label">Pending Requests</div>
  </div>
  <div class="stat-card tone-teal">
    <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
    <div class="stat-number"><?= (int)($statusCounts['Approved'] ?? 0) ?></div>
    <div class="stat-label">Approved Bookings</div>
  </div>
  <div class="stat-card tone-coral">
    <div class="stat-icon"><i class="fa-solid fa-calendar-xmark"></i></div>
    <div class="stat-number"><?= (int)($statusCounts['Rejected'] ?? 0) ?></div>
    <div class="stat-label">Rejected</div>
  </div>
  <div class="stat-card tone-blue">
    <div class="stat-icon"><i class="fa-solid fa-shield-heart"></i></div>
    <div class="stat-number"><?= (int)$completedCount ?></div>
    <div class="stat-label">Vaccinations Completed</div>
  </div>
</div>

<div class="card-panel">
  <div class="card-panel-head">
    <h3>Recent Booking Requests</h3>
    <a href="bookings.php" class="btn btn-outline btn-sm">Manage all</a>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Child</th><th>Vaccine</th><th>Appointment</th><th>Status</th></tr></thead>
      <tbody>
      <?php if (!$recentBookings): ?>
        <tr><td colspan="4"><div class="empty-state" style="padding:24px;"><i class="fa-solid fa-inbox"></i><h4>No booking requests yet</h4></div></td></tr>
      <?php endif; ?>
      <?php foreach ($recentBookings as $b): ?>
        <tr>
          <td class="cell-primary"><?= e($b['Child_Name']) ?></td>
          <td><?= e($b['Vaccine_Name']) ?></td>
          <td class="cell-sub"><?= format_date($b['Appointment_Date']) ?></td>
          <td><?= status_badge($b['Status']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
