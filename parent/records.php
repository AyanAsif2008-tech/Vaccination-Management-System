<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/parent_auth_check.php';

$parentId = $_SESSION['parent_id'];

$stmt = $pdo->prepare("
    SELECT vr.*, c.Child_Name, h.Hospital_Name, v.Vaccine_Name
    FROM vaccination_record vr
    JOIN child c ON c.Child_ID = vr.Child_ID
    JOIN booking_appointment b ON b.Booking_ID = vr.Booking_ID
    JOIN hospital h ON h.Hospital_ID = b.Hospital_ID
    JOIN vaccine v ON v.Vaccine_ID = b.Vaccine_ID
    WHERE c.Parent_ID = ?
    ORDER BY vr.Vaccinated_Date DESC
");
$stmt->execute([$parentId]);
$records = $stmt->fetchAll();

$pageTitle = 'Vaccination History';
$pageSubtitle = 'Completed vaccination records for your children.';
include '../includes/parent_header.php';
?>

<div class="card-panel">
  <div class="card-panel-head">
    <h3>Vaccination Records <span class="id-chip"><?= count($records) ?> total</span></h3>
    <button class="btn btn-outline btn-sm" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Child</th><th>Vaccine</th><th>Hospital</th><th>Date Administered</th><th>Status</th><th>Remarks</th></tr></thead>
      <tbody>
      <?php if (!$records): ?>
        <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-shield-heart"></i><h4>No vaccination records yet</h4><p>Completed vaccinations will appear here once a hospital confirms them.</p></div></td></tr>
      <?php endif; ?>
      <?php foreach ($records as $r): ?>
        <tr>
          <td class="cell-primary"><?= e($r['Child_Name']) ?></td>
          <td><?= e($r['Vaccine_Name']) ?></td>
          <td><?= e($r['Hospital_Name']) ?></td>
          <td class="cell-sub"><?= format_date($r['Vaccinated_Date']) ?></td>
          <td><?= status_badge($r['Status']) ?></td>
          <td class="cell-sub"><?= e($r['Remarks']) ?: '—' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
