<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/admin_auth_check.php';

$pendingBookingsCount = $pdo->query("SELECT COUNT(*) FROM booking_appointment WHERE Status = 'Pending'")->fetchColumn();

// Read-only: booking requests are approved or rejected by the hospital
// they were booked with. Admin can only view and filter/search them.

$statusFilter = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');

$where = [];
$params = [];
if ($statusFilter !== '') { $where[] = "b.Status = ?"; $params[] = $statusFilter; }
if ($q !== '') { $where[] = "(c.Child_Name LIKE ? OR h.Hospital_Name LIKE ? OR v.Vaccine_Name LIKE ?)"; $like = "%$q%"; array_push($params, $like, $like, $like); }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT b.*, c.Child_Name, p.Name AS Parent_Name, h.Hospital_Name, v.Vaccine_Name
        FROM booking_appointment b
        JOIN child c ON c.Child_ID = b.Child_ID
        JOIN parent p ON p.Parent_ID = c.Parent_ID
        JOIN hospital h ON h.Hospital_ID = b.Hospital_ID
        JOIN vaccine v ON v.Vaccine_ID = b.Vaccine_ID
        $whereSql
        ORDER BY FIELD(b.Status,'Pending','Approved','Rejected','Vaccinated'), b.Booking_ID DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$statusCounts = $pdo->query("SELECT Status, COUNT(*) c FROM booking_appointment GROUP BY Status")->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Bookings';
$pageSubtitle = 'View appointment requests (read only approved or rejected by the hospital).';
include '../includes/admin_header.php';
?>

<div class="toolbar" style="margin-bottom:18px;">
  <a href="bookings.php" class="btn btn-sm <?= $statusFilter === '' ? 'btn-primary' : 'btn-outline' ?>">All <span class="id-chip" style="margin-left:6px;"><?= array_sum($statusCounts) ?></span></a>
  <a href="?status=Pending" class="btn btn-sm <?= $statusFilter === 'Pending' ? 'btn-primary' : 'btn-outline' ?>">Pending <span class="id-chip" style="margin-left:6px;"><?= $statusCounts['Pending'] ?? 0 ?></span></a>
  <a href="?status=Approved" class="btn btn-sm <?= $statusFilter === 'Approved' ? 'btn-primary' : 'btn-outline' ?>">Approved <span class="id-chip" style="margin-left:6px;"><?= $statusCounts['Approved'] ?? 0 ?></span></a>
  <a href="?status=Rejected" class="btn btn-sm <?= $statusFilter === 'Rejected' ? 'btn-primary' : 'btn-outline' ?>">Rejected <span class="id-chip" style="margin-left:6px;"><?= $statusCounts['Rejected'] ?? 0 ?></span></a>
</div>

<div class="card-panel">
  <div class="card-panel-head">
    <h3>Booking Requests <span class="id-chip"><?= count($bookings) ?> shown</span></h3>
    <form method="GET" class="search-box">
      <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search child, hospital, vaccine...">
    </form>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Child / Parent</th><th>Hospital</th><th>Vaccine</th><th>Booked</th><th>Appointment</th><th>Status</th></tr>
      </thead>
      <tbody>
      <?php if (!$bookings): ?>
        <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-calendar-check"></i><h4>No bookings found</h4></div></td></tr>
      <?php endif; ?>
      <?php foreach ($bookings as $b): ?>
        <tr>
          <td>
            <div class="cell-primary"><?= e($b['Child_Name']) ?></div>
            <div class="cell-sub">Parent: <?= e($b['Parent_Name']) ?></div>
          </td>
          <td><?= e($b['Hospital_Name']) ?></td>
          <td><?= e($b['Vaccine_Name']) ?></td>
          <td class="cell-sub"><?= format_date($b['Booking_Date']) ?></td>
          <td class="cell-sub"><?= format_date($b['Appointment_Date']) ?></td>
          <td><?= status_badge($b['Status']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
