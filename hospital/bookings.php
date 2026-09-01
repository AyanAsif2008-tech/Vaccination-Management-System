<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/hospital_auth_check.php';

$hospitalId = $_SESSION['hospital_id'];

$pcStmt = $pdo->prepare("SELECT COUNT(*) FROM booking_appointment WHERE Hospital_ID = ? AND Status = 'Pending'");
$pcStmt->execute([$hospitalId]);
$pendingBookingsCount = $pcStmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['booking_id'] ?? 0);

    // Always scope to this hospital's own bookings
    $own = $pdo->prepare("SELECT * FROM booking_appointment WHERE Booking_ID = ? AND Hospital_ID = ?");
    $own->execute([$id, $hospitalId]);
    $booking = $own->fetch();

    if (!$booking) {
        flash_set('error', 'Booking not found.');
    } elseif ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE booking_appointment SET Status='Approved', Approval_Date=CURDATE() WHERE Booking_ID=?");
        $stmt->execute([$id]);
        flash_set('success', 'Booking approved.');
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE booking_appointment SET Status='Rejected', Approval_Date=CURDATE() WHERE Booking_ID=?");
        $stmt->execute([$id]);
        flash_set('success', 'Booking rejected.');
    } elseif ($action === 'mark_vaccinated') {
        $remarks = trim($_POST['remarks'] ?? '');
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO vaccination_record (Vaccinated_Date, Status, Remarks, Booking_ID, Child_ID) VALUES (CURDATE(), 'Vaccinated', ?, ?, ?)");
            $stmt->execute([$remarks, $id, $booking['Child_ID']]);
            $stmt2 = $pdo->prepare("UPDATE booking_appointment SET Status='Approved' WHERE Booking_ID=?");
            $stmt2->execute([$id]);
            $pdo->commit();
            flash_set('success', 'Vaccination recorded successfully.');
        } catch (PDOException $ex) {
            $pdo->rollBack();
            flash_set('error', 'A vaccination record already exists for this booking.');
        }
    }
    redirect('bookings.php' . (!empty($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

$statusFilter = $_GET['status'] ?? '';
$where = "WHERE b.Hospital_ID = ?";
$params = [$hospitalId];
if ($statusFilter !== '') { $where .= " AND b.Status = ?"; $params[] = $statusFilter; }

$stmt = $pdo->prepare("
    SELECT b.*, c.Child_Name, p.Name AS Parent_Name, v.Vaccine_Name,
           (SELECT COUNT(*) FROM vaccination_record vr WHERE vr.Booking_ID = b.Booking_ID) AS has_record
    FROM booking_appointment b
    JOIN child c ON c.Child_ID = b.Child_ID
    JOIN parent p ON p.Parent_ID = c.Parent_ID
    JOIN vaccine v ON v.Vaccine_ID = b.Vaccine_ID
    $where
    ORDER BY FIELD(b.Status,'Pending','Approved','Rejected'), b.Booking_ID DESC
");
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$pageTitle = 'Bookings';
$pageSubtitle = 'Review, approve, and record vaccinations for appointment requests.';
include '../includes/hospital_header.php';
?>

<div class="toolbar" style="margin-bottom:18px;">
  <a href="bookings.php" class="btn btn-sm <?= $statusFilter === '' ? 'btn-primary' : 'btn-outline' ?>">All</a>
  <a href="?status=Pending" class="btn btn-sm <?= $statusFilter === 'Pending' ? 'btn-primary' : 'btn-outline' ?>">Pending</a>
  <a href="?status=Approved" class="btn btn-sm <?= $statusFilter === 'Approved' ? 'btn-primary' : 'btn-outline' ?>">Approved</a>
  <a href="?status=Rejected" class="btn btn-sm <?= $statusFilter === 'Rejected' ? 'btn-primary' : 'btn-outline' ?>">Rejected</a>
</div>

<div class="card-panel">
  <div class="card-panel-head"><h3>Booking Requests <span class="id-chip"><?= count($bookings) ?> shown</span></h3></div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Child / Parent</th><th>Vaccine</th><th>Appointment</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
      <tbody>
      <?php if (!$bookings): ?>
        <tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-calendar-check"></i><h4>No bookings found</h4></div></td></tr>
      <?php endif; ?>
      <?php foreach ($bookings as $b): ?>
        <tr>
          <td>
            <div class="cell-primary"><?= e($b['Child_Name']) ?></div>
            <div class="cell-sub">Parent: <?= e($b['Parent_Name']) ?></div>
          </td>
          <td><?= e($b['Vaccine_Name']) ?></td>
          <td class="cell-sub"><?= format_date($b['Appointment_Date']) ?></td>
          <td><?= status_badge($b['Status']) ?><?php if ($b['has_record']): ?> <span class="id-chip" style="margin-left:4px;">Recorded</span><?php endif; ?></td>
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
              <?php elseif ($b['Status'] === 'Approved' && !$b['has_record']): ?>
                <button class="btn btn-primary btn-sm" onclick='openVaccinateModal(<?= (int)$b['Booking_ID'] ?>, <?= json_encode($b['Child_Name']) ?>)'><i class="fa-solid fa-syringe"></i> Mark Vaccinated</button>
              <?php else: ?>
                <span class="cell-sub">—</span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Mark Vaccinated Modal -->
<div class="modal-overlay" id="vaccinateModal">
  <div class="modal-box">
    <div class="modal-head">
      <h3>Record Vaccination</h3>
      <button class="modal-close" onclick="closeModal('vaccinateModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="mark_vaccinated">
        <input type="hidden" name="booking_id" id="vaccinateBookingId" value="">
        <p style="font-size:13.8px;color:var(--slate);margin-bottom:16px;">Confirming vaccination for <b id="vaccinateChildName" style="color:var(--ink);"></b>, dated today.</p>
        <div class="form-group">
          <label class="form-label">Remarks (optional)</label>
          <textarea class="form-control" name="remarks" rows="3" placeholder="Any notes about the dose administered, reactions, etc."></textarea>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('vaccinateModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Confirm Vaccination</button>
      </div>
    </form>
  </div>
</div>

<script>
function openVaccinateModal(bookingId, childName) {
  document.getElementById('vaccinateBookingId').value = bookingId;
  document.getElementById('vaccinateChildName').textContent = childName;
  openModal('vaccinateModal');
}
</script>

<?php include '../includes/footer.php'; ?>
