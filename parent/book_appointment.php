<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/parent_auth_check.php';

$parentId = $_SESSION['parent_id'];

$childrenStmt = $pdo->prepare("SELECT Child_ID, Child_Name FROM child WHERE Parent_ID = ? ORDER BY Child_Name");
$childrenStmt->execute([$parentId]);
$children = $childrenStmt->fetchAll();

$hospitals = $pdo->query("SELECT Hospital_ID, Hospital_Name, Location FROM hospital WHERE Status = 'Approved' ORDER BY Hospital_Name")->fetchAll();
$vaccines  = $pdo->query("SELECT Vaccine_ID, Vaccine_Name, Age_Group, Stock_Status FROM vaccine WHERE Stock_Status != 'Out of Stock' ORDER BY Vaccine_Name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $childId   = (int)($_POST['child_id'] ?? 0);
    $hospitalId= (int)($_POST['hospital_id'] ?? 0);
    $vaccineId = (int)($_POST['vaccine_id'] ?? 0);
    $apptDate  = $_POST['appointment_date'] ?? '';

    // Verify the child actually belongs to this parent
    $check = $pdo->prepare("SELECT COUNT(*) FROM child WHERE Child_ID = ? AND Parent_ID = ?");
    $check->execute([$childId, $parentId]);
    $ownsChild = (bool)$check->fetchColumn();

    if (!$childId || !$hospitalId || !$vaccineId || !$apptDate) {
        flash_set('error', 'Please complete every field to book an appointment.');
    } elseif (!$ownsChild) {
        flash_set('error', 'Invalid child selection.');
    } elseif (strtotime($apptDate) < strtotime('today')) {
        flash_set('error', 'Appointment date cannot be in the past.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO booking_appointment (Booking_Date, Appointment_Date, Status, Child_ID, Hospital_ID, Vaccine_ID) VALUES (CURDATE(), ?, 'Pending', ?, ?, ?)");
        $stmt->execute([$apptDate, $childId, $hospitalId, $vaccineId]);
        flash_set('success', 'Appointment request submitted! You\'ll be notified once the hospital reviews it.');
        redirect('bookings.php');
    }
}

$pageTitle = 'Book Appointment';
$pageSubtitle = 'Schedule a vaccination for one of your children.';
include '../includes/parent_header.php';
?>

<?php if (!$children): ?>
  <div class="card-panel">
    <div class="empty-state">
      <i class="fa-solid fa-child-reaching"></i>
      <h4>Add a child before booking</h4>
      <p>You need at least one child profile before you can book a vaccination appointment.</p>
      <a href="children.php" class="btn btn-primary btn-sm" style="margin-top:10px;"><i class="fa-solid fa-plus"></i> Add Child</a>
    </div>
  </div>
<?php elseif (!$hospitals): ?>
  <div class="card-panel">
    <div class="empty-state">
      <i class="fa-solid fa-hospital"></i>
      <h4>No approved hospitals yet</h4>
      <p>There are currently no approved hospital partners available for booking. Please check back soon.</p>
    </div>
  </div>
<?php else: ?>
<div class="card-panel" style="max-width:640px;">
  <div class="card-panel-head"><h3>New Appointment Request</h3></div>
  <div class="card-panel-body">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

      <div class="form-group">
        <label class="form-label">Child</label>
        <select class="form-control" name="child_id" required>
          <option value="">Select child</option>
          <?php foreach ($children as $c): ?>
            <option value="<?= (int)$c['Child_ID'] ?>"><?= e($c['Child_Name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Vaccine</label>
        <select class="form-control" name="vaccine_id" required>
          <option value="">Select vaccine</option>
          <?php foreach ($vaccines as $v): ?>
            <option value="<?= (int)$v['Vaccine_ID'] ?>"><?= e($v['Vaccine_Name']) ?><?= $v['Age_Group'] ? ' — ' . e($v['Age_Group']) : '' ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Hospital</label>
        <select class="form-control" name="hospital_id" required>
          <option value="">Select hospital</option>
          <?php foreach ($hospitals as $h): ?>
            <option value="<?= (int)$h['Hospital_ID'] ?>"><?= e($h['Hospital_Name']) ?><?= $h['Location'] ? ' — ' . e($h['Location']) : '' ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Preferred Appointment Date</label>
        <input type="date" class="form-control" name="appointment_date" min="<?= date('Y-m-d') ?>" required>
        <div class="form-hint">The hospital will confirm or adjust this date upon approval.</div>
      </div>

      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-calendar-check"></i> Submit Request</button>
    </form>
  </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
