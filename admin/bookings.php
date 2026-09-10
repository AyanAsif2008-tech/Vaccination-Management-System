<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/admin_auth_check.php';

$pendingBookingsCount = $pdo->query("SELECT COUNT(*) FROM booking_appointment WHERE Status = 'Pending'")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['booking_id'] ?? 0);

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE booking_appointment SET Status='Approved', Approval_Date=CURDATE() WHERE Booking_ID=?");
        $stmt->execute([$id]);
        flash_set('success', 'Booking approved.');
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE booking_appointment SET Status='Rejected', Approval_Date=CURDATE() WHERE Booking_ID=?");
        $stmt->execute([$id]);
        flash_set('success', 'Booking rejected.');
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM booking_appointment WHERE Booking_ID=?");
        $stmt->execute([$id]);
        flash_set('success', 'Booking deleted.');
    } elseif ($action === 'reschedule') {
        $newDate = $_POST['appointment_date'] ?? '';
        if ($newDate) {
            $stmt = $pdo->prepare("UPDATE booking_appointment SET Appointment_Date=? WHERE Booking_ID=?");
            $stmt->execute([$newDate, $id]);
            flash_set('success', 'Appointment date updated.');
        }
    }
    redirect('bookings.php' . (!empty($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

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
$pageSubtitle = 'Review appointment requests and approve or reject them.';
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
        <tr><th>Child / Parent</th><th>Hospital</th><th>Vaccine</th><th>Booked</th><th>Appointment</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
      </thead>
      <tbody>
      <?php if (!$bookings): ?>
        <tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-calendar-check"></i><h4>No bookings found</h4></div></td></tr>
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
          <td>
            <div class="action-group" style="justify-content:flex-end;">
              <?php if ($b['Status'] === 'Pending'): ?>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="approve">
                  <input type="hidden" name="booking_id" value="<?= (int)$b['Booking_ID'] ?>">
                  <button type="submit" class="action-btn" title="Approve"><i class="fa-solid fa-check"></i></button>
                </form>
                <form method="POST" style="display:inline;" onsubmit="return confirmDelete('Reject this booking request?');">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="reject">
                  <input type="hidden" name="booking_id" value="<?= (int)$b['Booking_ID'] ?>">
                  <button type="submit" class="action-btn danger" title="Reject"><i class="fa-solid fa-xmark"></i></button>
                </form>
              <?php endif; ?>
              <form method="POST" onsubmit="return confirmDelete('Permanently delete this booking?');" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="booking_id" value="<?= (int)$b['Booking_ID'] ?>">
                <button type="submit" class="action-btn danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
