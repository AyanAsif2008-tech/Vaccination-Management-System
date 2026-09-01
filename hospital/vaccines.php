<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/hospital_auth_check.php';

$hospitalId = $_SESSION['hospital_id'];
$pcStmt = $pdo->prepare("SELECT COUNT(*) FROM booking_appointment WHERE Hospital_ID = ? AND Status = 'Pending'");
$pcStmt->execute([$hospitalId]);
$pendingBookingsCount = $pcStmt->fetchColumn();

$vaccines = $pdo->query("SELECT * FROM vaccine ORDER BY Vaccine_Name")->fetchAll();

$pageTitle = 'Vaccine Catalog';
$pageSubtitle = 'Vaccines available across the system (managed by admin).';
include '../includes/hospital_header.php';
?>

<div class="card-panel">
  <div class="card-panel-head"><h3>Vaccine Catalog <span class="id-chip"><?= count($vaccines) ?> total</span></h3></div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Vaccine</th><th>Description</th><th>Age Group</th><th>Stock</th></tr></thead>
      <tbody>
      <?php if (!$vaccines): ?>
        <tr><td colspan="4"><div class="empty-state"><i class="fa-solid fa-vial-circle-check"></i><h4>No vaccines added yet</h4></div></td></tr>
      <?php endif; ?>
      <?php foreach ($vaccines as $v): ?>
        <tr>
          <td class="cell-primary"><?= e($v['Vaccine_Name']) ?></td>
          <td class="cell-sub" style="max-width:320px;"><?= e($v['Description']) ?: '—' ?></td>
          <td><?= e($v['Age_Group']) ?: '—' ?></td>
          <td><?= status_badge($v['Stock_Status']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
