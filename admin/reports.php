<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/admin_auth_check.php';

$pendingBookingsCount = $pdo->query("SELECT COUNT(*) FROM booking_appointment WHERE Status = 'Pending'")->fetchColumn();

// Child-wise report
$childReport = $pdo->query("
    SELECT c.Child_Name, p.Name AS Parent_Name,
           COUNT(b.Booking_ID) AS total_bookings,
           SUM(CASE WHEN b.Status = 'Approved' THEN 1 ELSE 0 END) AS approved,
           SUM(CASE WHEN vr.Status IN ('Vaccinated','Completed') THEN 1 ELSE 0 END) AS completed
    FROM child c
    JOIN parent p ON p.Parent_ID = c.Parent_ID
    LEFT JOIN booking_appointment b ON b.Child_ID = c.Child_ID
    LEFT JOIN vaccination_record vr ON vr.Booking_ID = b.Booking_ID
    GROUP BY c.Child_ID ORDER BY total_bookings DESC
")->fetchAll();

// Hospital-wise report
$hospitalReport = $pdo->query("
    SELECT h.Hospital_Name, h.Location,
           COUNT(b.Booking_ID) AS total_bookings,
           SUM(CASE WHEN b.Status = 'Pending' THEN 1 ELSE 0 END) AS pending,
           SUM(CASE WHEN b.Status = 'Approved' THEN 1 ELSE 0 END) AS approved,
           SUM(CASE WHEN vr.Status IN ('Vaccinated','Completed') THEN 1 ELSE 0 END) AS completed
    FROM hospital h
    LEFT JOIN booking_appointment b ON b.Hospital_ID = h.Hospital_ID
    LEFT JOIN vaccination_record vr ON vr.Booking_ID = b.Booking_ID
    GROUP BY h.Hospital_ID ORDER BY total_bookings DESC
")->fetchAll();

// Vaccine-wise report
$vaccineReport = $pdo->query("
    SELECT v.Vaccine_Name, v.Age_Group, v.Stock_Status,
           COUNT(b.Booking_ID) AS total_bookings,
           SUM(CASE WHEN vr.Status IN ('Vaccinated','Completed') THEN 1 ELSE 0 END) AS completed
    FROM vaccine v
    LEFT JOIN booking_appointment b ON b.Vaccine_ID = v.Vaccine_ID
    LEFT JOIN vaccination_record vr ON vr.Booking_ID = b.Booking_ID
    GROUP BY v.Vaccine_ID ORDER BY total_bookings DESC
")->fetchAll();

// Status overview
$statusOverview = $pdo->query("SELECT Status, COUNT(*) c FROM booking_appointment GROUP BY Status")->fetchAll();
$recordStatus = $pdo->query("SELECT Status, COUNT(*) c FROM vaccination_record GROUP BY Status")->fetchAll();

$pageTitle = 'Reports';
$pageSubtitle = 'Child, hospital and vaccine-wise vaccination reports.';
include '../includes/admin_header.php';
?>

<div class="toolbar no-print" style="margin-bottom:18px;">
  <button class="btn btn-sm btn-primary tab-btn active" data-tab="child">Child-wise</button>
  <button class="btn btn-sm btn-outline tab-btn" data-tab="hospital">Hospital-wise</button>
  <button class="btn btn-sm btn-outline tab-btn" data-tab="vaccine">Vaccine-wise</button>
  <button class="btn btn-sm btn-outline tab-btn" data-tab="status">Status Overview</button>
  <button class="btn btn-sm btn-outline" style="margin-left:auto;" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
</div>

<!-- Child-wise -->
<div class="card-panel report-tab" id="tab-child">
  <div class="card-panel-head"><h3>Child-wise Vaccination Report</h3></div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Child</th><th>Parent</th><th>Total Bookings</th><th>Approved</th><th>Completed</th></tr></thead>
      <tbody>
      <?php if (!$childReport): ?><tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-child-reaching"></i><h4>No data yet</h4></div></td></tr><?php endif; ?>
      <?php foreach ($childReport as $r): ?>
        <tr>
          <td class="cell-primary"><?= e($r['Child_Name']) ?></td>
          <td><?= e($r['Parent_Name']) ?></td>
          <td><span class="id-chip"><?= (int)$r['total_bookings'] ?></span></td>
          <td><?= (int)$r['approved'] ?></td>
          <td><?= status_badge((int)$r['completed'] > 0 ? 'Completed' : 'Pending') ?> <?= (int)$r['completed'] ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Hospital-wise -->
<div class="card-panel report-tab" id="tab-hospital" style="display:none;">
  <div class="card-panel-head"><h3>Hospital-wise Report</h3></div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Hospital</th><th>Location</th><th>Total</th><th>Pending</th><th>Approved</th><th>Completed</th></tr></thead>
      <tbody>
      <?php if (!$hospitalReport): ?><tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-hospital"></i><h4>No data yet</h4></div></td></tr><?php endif; ?>
      <?php foreach ($hospitalReport as $r): ?>
        <tr>
          <td class="cell-primary"><?= e($r['Hospital_Name']) ?></td>
          <td><?= e($r['Location']) ?: '—' ?></td>
          <td><span class="id-chip"><?= (int)$r['total_bookings'] ?></span></td>
          <td><?= (int)$r['pending'] ?></td>
          <td><?= (int)$r['approved'] ?></td>
          <td><?= (int)$r['completed'] ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Vaccine-wise -->
<div class="card-panel report-tab" id="tab-vaccine" style="display:none;">
  <div class="card-panel-head"><h3>Vaccine-wise Report</h3></div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Vaccine</th><th>Age Group</th><th>Stock</th><th>Total Bookings</th><th>Completed</th></tr></thead>
      <tbody>
      <?php if (!$vaccineReport): ?><tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-vial-circle-check"></i><h4>No data yet</h4></div></td></tr><?php endif; ?>
      <?php foreach ($vaccineReport as $r): ?>
        <tr>
          <td class="cell-primary"><?= e($r['Vaccine_Name']) ?></td>
          <td><?= e($r['Age_Group']) ?: '—' ?></td>
          <td><?= status_badge($r['Stock_Status']) ?></td>
          <td><span class="id-chip"><?= (int)$r['total_bookings'] ?></span></td>
          <td><?= (int)$r['completed'] ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Status overview -->
<div class="card-panel report-tab" id="tab-status" style="display:none;">
  <div class="card-panel-head"><h3>Booking &amp; Vaccination Status Overview</h3></div>
  <div class="card-panel-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
      <div>
        <h4 style="font-size:14px;color:var(--slate);margin-bottom:12px;">Booking Requests by Status</h4>
        <?php foreach ($statusOverview as $s): ?>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--line);">
            <?= status_badge($s['Status']) ?><span class="cell-primary"><?= (int)$s['c'] ?></span>
          </div>
        <?php endforeach; ?>
        <?php if (!$statusOverview): ?><p class="cell-sub">No bookings recorded yet.</p><?php endif; ?>
      </div>
      <div>
        <h4 style="font-size:14px;color:var(--slate);margin-bottom:12px;">Vaccination Records by Status</h4>
        <?php foreach ($recordStatus as $s): ?>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--line);">
            <?= status_badge($s['Status']) ?><span class="cell-primary"><?= (int)$s['c'] ?></span>
          </div>
        <?php endforeach; ?>
        <?php if (!$recordStatus): ?><p class="cell-sub">No vaccination records yet.</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => { b.classList.remove('btn-primary'); b.classList.add('btn-outline'); });
    btn.classList.remove('btn-outline'); btn.classList.add('btn-primary');
    document.querySelectorAll('.report-tab').forEach(t => t.style.display = 'none');
    document.getElementById('tab-' + btn.dataset.tab).style.display = 'block';
  });
});
</script>

<?php include '../includes/footer.php'; ?>
