<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/admin_auth_check.php';

$pendingBookingsCount = $pdo->query("SELECT COUNT(*) FROM booking_appointment WHERE Status = 'Pending'")->fetchColumn();

// Read-only: vaccine inventory is now owned and managed by each hospital.
// Admin can only view and filter by hospital — no create/update/delete here.

$hospitalFilter = $_GET['hospital_id'] ?? '';
$q = trim($_GET['q'] ?? '');

$where = [];
$params = [];
if ($hospitalFilter !== '') {
    $where[] = "v.Hospital_ID = ?";
    $params[] = (int)$hospitalFilter;
}
if ($q !== '') {
    $where[] = "(v.Vaccine_Name LIKE ? OR v.Age_Group LIKE ?)";
    $like = "%$q%";
    array_push($params, $like, $like);
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("
    SELECT v.*, h.Hospital_Name,
           (SELECT COUNT(*) FROM booking_appointment b WHERE b.Vaccine_ID = v.Vaccine_ID) AS booking_count
    FROM vaccine v
    LEFT JOIN hospital h ON h.Hospital_ID = v.Hospital_ID
    $whereSql
    ORDER BY v.Hospital_ID IS NULL, h.Hospital_Name, v.Vaccine_Name
");
$stmt->execute($params);
$vaccines = $stmt->fetchAll();

$hospitalsList = $pdo->query("SELECT Hospital_ID, Hospital_Name FROM hospital WHERE Status = 'Approved' ORDER BY Hospital_Name")->fetchAll();

$pageTitle = 'Vaccines';
$pageSubtitle = 'View which vaccines each hospital has available';
include '../includes/admin_header.php';
?>

<div class="card-panel">
  <div class="card-panel-head">
    <h3>Vaccine Catalog <span class="id-chip"><?= count($vaccines) ?> shown</span></h3>
    <div class="toolbar">
      <form method="GET" class="search-box">
        <?php if ($hospitalFilter !== ''): ?><input type="hidden" name="hospital_id" value="<?= (int)$hospitalFilter ?>"><?php endif; ?>
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search vaccine or age group...">
      </form>
      <form method="GET">
        <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
        <select class="filter-select" name="hospital_id" onchange="this.form.submit()">
          <option value="">All hospitals</option>
          <?php foreach ($hospitalsList as $h): ?>
            <option value="<?= (int)$h['Hospital_ID'] ?>" <?= (string)$hospitalFilter === (string)$h['Hospital_ID'] ? 'selected' : '' ?>><?= e($h['Hospital_Name']) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Vaccine</th><th>Hospital</th><th>Description</th><th>Age Group</th><th>Stock</th><th>Bookings</th></tr>
      </thead>
      <tbody>
      <?php if (!$vaccines): ?>
        <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-vial-circle-check"></i><h4>No vaccines found</h4><p>Vaccines are added by each hospital from their own panel.</p></div></td></tr>
      <?php endif; ?>
      <?php foreach ($vaccines as $v): ?>
        <tr>
          <td class="cell-primary"><?= e($v['Vaccine_Name']) ?></td>
          <td><?= $v['Hospital_Name'] ? e($v['Hospital_Name']) : '<span class="cell-sub">Unassigned</span>' ?></td>
          <td class="cell-sub" style="max-width:240px;"><?= e($v['Description']) ?: '—' ?></td>
          <td><?= e($v['Age_Group']) ?: '—' ?></td>
          <td><?= status_badge($v['Stock_Status']) ?></td>
          <td><span class="id-chip"><?= (int)$v['booking_count'] ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
