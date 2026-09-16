<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/parent_auth_check.php';

$parentId = $_SESSION['parent_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['booking_id'] ?? 0);

    if ($action === 'cancel') {
        // Only allow cancelling own, still-pending bookings
        $stmt = $pdo->prepare("
            DELETE b FROM booking_appointment b
            JOIN child c ON c.Child_ID = b.Child_ID
            WHERE b.Booking_ID = ? AND c.Parent_ID = ? AND b.Status = 'Pending'
        ");
        $stmt->execute([$id, $parentId]);
        flash_set('success', 'Booking request cancelled.');
    }
    redirect('bookings.php');
}

$statusFilter = $_GET['status'] ?? '';
$where = "WHERE c.Parent_ID = ?";
$params = [$parentId];
if ($statusFilter !== '') { $where .= " AND b.Status = ?"; $params[] = $statusFilter; }

$stmt = $pdo->prepare("
    SELECT b.*, c.Child_Name, h.Hospital_Name, v.Vaccine_Name
    FROM booking_appointment b
    JOIN child c ON c.Child_ID = b.Child_ID
    JOIN hospital h ON h.Hospital_ID = b.Hospital_ID
    JOIN vaccine v ON v.Vaccine_ID = b.Vaccine_ID
    $where
    ORDER BY FIELD(b.Status,'Pending','Approved','Rejected','Vaccinated'), b.Booking_ID DESC
");
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$pageTitle = 'My Bookings';
$pageSubtitle = 'Track the status of your appointment requests.';
include '../includes/parent_header.php';
?>

<div class="toolbar" style="margin-bottom:18px;">
  <a href="bookings.php" class="btn btn-sm <?= $statusFilter === '' ? 'btn-primary' : 'btn-outline' ?>">All</a>
  <a href="?status=Pending" class="btn btn-sm <?= $statusFilter === 'Pending' ? 'btn-primary' : 'btn-outline' ?>">Pending</a>
  <a href="?status=Approved" class="btn btn-sm <?= $statusFilter === 'Approved' ? 'btn-primary' : 'btn-outline' ?>">Approved</a>
  <a href="?status=Rejected" class="btn btn-sm <?= $statusFilter === 'Rejected' ? 'btn-primary' : 'btn-outline' ?>">Rejected</a>
  <a href="book_appointment.php" class="btn btn-primary btn-sm" style="margin-left:auto;"><i class="fa-solid fa-plus"></i> New Booking</a>
</div>

<div class="card-panel">
  <div class="card-panel-head"><h3>Appointment Requests <span class="id-chip"><?= count($bookings) ?> shown</span></h3></div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Child</th><th>Hospital</th><th>Vaccine</th><th>Appointment Date</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
      <tbody>
      <?php if (!$bookings): ?>
        <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-calendar-check"></i><h4>No bookings found</h4><p><a href="book_appointment.php">Book your first appointment</a></p></div></td></tr>
      <?php endif; ?>
      <?php foreach ($bookings as $b): ?>
        <tr>
          <td class="cell-primary"><?= e($b['Child_Name']) ?></td>
          <td><?= e($b['Hospital_Name']) ?></td>
          <td><?= e($b['Vaccine_Name']) ?></td>
          <td class="cell-sub"><?= format_date($b['Appointment_Date']) ?></td>
          <td><?= status_badge($b['Status']) ?></td>
          <td>
            <?php if ($b['Status'] === 'Pending'): ?>
              <form method="POST" style="display:inline;" onsubmit="return confirmDelete('Cancel this appointment request?');">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="booking_id" value="<?= (int)$b['Booking_ID'] ?>">
                <button type="submit" class="btn btn-outline btn-sm"><i class="fa-solid fa-xmark"></i> Cancel</button>
              </form>
            <?php else: ?>
              <span class="cell-sub">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
