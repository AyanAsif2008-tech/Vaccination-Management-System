<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/admin_auth_check.php';

// ---- Stats ----
$totalParents   = $pdo->query("SELECT COUNT(*) FROM parent")->fetchColumn();
$totalChildren  = $pdo->query("SELECT COUNT(*) FROM child")->fetchColumn();
$totalHospitals = $pdo->query("SELECT COUNT(*) FROM hospital")->fetchColumn();
$totalVaccines  = $pdo->query("SELECT COUNT(*) FROM vaccine")->fetchColumn();

$pendingBookings   = $pdo->query("SELECT COUNT(*) FROM booking_appointment WHERE Status = 'Pending'")->fetchColumn();
$approvedBookings  = $pdo->query("SELECT COUNT(*) FROM booking_appointment WHERE Status = 'Approved'")->fetchColumn();
$rejectedBookings  = $pdo->query("SELECT COUNT(*) FROM booking_appointment WHERE Status = 'Rejected'")->fetchColumn();
$completedVaccines = $pdo->query("SELECT COUNT(*) FROM vaccination_record WHERE Status IN ('Vaccinated','Completed')")->fetchColumn();

$pendingBookingsCount = $pendingBookings; // used by sidebar badge

// ---- Booking status breakdown (for donut chart) ----
$statusRows = $pdo->query("SELECT Status, COUNT(*) c FROM booking_appointment GROUP BY Status")->fetchAll();
$statusLabels = []; $statusData = [];
foreach ($statusRows as $row) { $statusLabels[] = $row['Status'] ?: 'Pending'; $statusData[] = (int)$row['c']; }
if (empty($statusLabels)) { $statusLabels = ['No data']; $statusData = [1]; }

// ---- Monthly vaccination trend (last 6 months) ----
$trend = $pdo->query("
    SELECT DATE_FORMAT(Vaccinated_Date, '%Y-%m') ym, COUNT(*) c
    FROM vaccination_record
    WHERE Vaccinated_Date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY ym ORDER BY ym
")->fetchAll();
$trendMap = [];
for ($i = 5; $i >= 0; $i--) {
    $key = date('Y-m', strtotime("-$i months"));
    $trendMap[$key] = 0;
}
foreach ($trend as $t) { if (isset($trendMap[$t['ym']])) $trendMap[$t['ym']] = (int)$t['c']; }
$trendLabels = array_map(fn($k) => date('M', strtotime($k . '-01')), array_keys($trendMap));
$trendData   = array_values($trendMap);

// ---- Recent booking requests ----
$recentBookings = $pdo->query("
    SELECT b.Booking_ID, b.Booking_Date, b.Status, c.Child_Name, h.Hospital_Name, v.Vaccine_Name
    FROM booking_appointment b
    JOIN child c ON c.Child_ID = b.Child_ID
    JOIN hospital h ON h.Hospital_ID = b.Hospital_ID
    JOIN vaccine v ON v.Vaccine_ID = b.Vaccine_ID
    ORDER BY b.Booking_ID DESC LIMIT 6
")->fetchAll();

// ---- Vaccine stock overview ----
$vaccineStock = $pdo->query("SELECT Vaccine_Name, Stock_Status FROM vaccine ORDER BY Vaccine_ID DESC LIMIT 5")->fetchAll();

$pageTitle = 'Dashboard';
$pageSubtitle = 'Welcome back — here\'s what\'s happening across the system today.';
include '../includes/admin_header.php';
?>

<div class="stat-grid">
  <div class="stat-card tone-teal">
    <div class="stat-icon"><i class="fa-solid fa-user-group"></i></div>
    <div class="stat-number"><?= (int)$totalParents ?></div>
    <div class="stat-label">Total Parents</div>
  </div>
  <div class="stat-card tone-blue">
    <div class="stat-icon"><i class="fa-solid fa-child-reaching"></i></div>
    <div class="stat-number"><?= (int)$totalChildren ?></div>
    <div class="stat-label">Total Children</div>
  </div>
  <div class="stat-card tone-ink">
    <div class="stat-icon"><i class="fa-solid fa-hospital"></i></div>
    <div class="stat-number"><?= (int)$totalHospitals ?></div>
    <div class="stat-label">Total Hospitals</div>
  </div>
  <div class="stat-card tone-amber">
    <div class="stat-icon"><i class="fa-solid fa-vial-circle-check"></i></div>
    <div class="stat-number"><?= (int)$totalVaccines ?></div>
    <div class="stat-label">Total Vaccines</div>
  </div>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
  <div class="stat-card tone-amber">
    <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
    <div class="stat-number"><?= (int)$pendingBookings ?></div>
    <div class="stat-label">Pending Bookings</div>
  </div>
  <div class="stat-card tone-teal">
    <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
    <div class="stat-number"><?= (int)$approvedBookings ?></div>
    <div class="stat-label">Approved Bookings</div>
  </div>
  <div class="stat-card tone-blue">
    <div class="stat-icon"><i class="fa-solid fa-shield-heart"></i></div>
    <div class="stat-number"><?= (int)$completedVaccines ?></div>
    <div class="stat-label">Completed Vaccinations</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:20px;align-items:start;" class="dash-grid">
  <div class="card-panel">
    <div class="card-panel-head">
      <h3>Vaccination Trend</h3>
      <span class="cell-sub">Last 6 months</span>
    </div>
    <div class="card-panel-body">
      <canvas id="trendChart" height="130"></canvas>
    </div>
  </div>

  <div class="card-panel">
    <div class="card-panel-head">
      <h3>Booking Status Breakdown</h3>
    </div>
    <div class="card-panel-body">
      <canvas id="statusChart" height="180"></canvas>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:20px;align-items:start;margin-top:20px;" class="dash-grid">
  <div class="card-panel">
    <div class="card-panel-head">
      <h3>Recent Booking Requests</h3>
      <a href="bookings.php" class="btn btn-outline btn-sm">View all</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Child</th><th>Hospital</th><th>Vaccine</th><th>Booked</th><th>Status</th></tr></thead>
        <tbody>
        <?php if (!$recentBookings): ?>
          <tr><td colspan="5"><div class="empty-state" style="padding:24px;"><i class="fa-solid fa-inbox"></i><h4>No bookings yet</h4></div></td></tr>
        <?php endif; ?>
        <?php foreach ($recentBookings as $b): ?>
          <tr>
            <td class="cell-primary"><?= e($b['Child_Name']) ?></td>
            <td><?= e($b['Hospital_Name']) ?></td>
            <td><?= e($b['Vaccine_Name']) ?></td>
            <td><?= format_date($b['Booking_Date']) ?></td>
            <td><?= status_badge($b['Status']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card-panel">
    <div class="card-panel-head">
      <h3>Vaccine Stock</h3>
      <a href="vaccines.php" class="btn btn-outline btn-sm">Manage</a>
    </div>
    <div class="card-panel-body" style="padding-top:10px;">
      <?php if (!$vaccineStock): ?>
        <div class="empty-state" style="padding:20px;"><i class="fa-solid fa-vial"></i><h4>No vaccines added</h4></div>
      <?php endif; ?>
      <?php foreach ($vaccineStock as $v): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--line);">
          <span style="font-size:13.8px;font-weight:600;color:var(--ink);"><?= e($v['Vaccine_Name']) ?></span>
          <?= status_badge($v['Stock_Status']) ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
const trendCtx = document.getElementById('trendChart');
new Chart(trendCtx, {
  type: 'line',
  data: {
    labels: <?= json_encode($trendLabels) ?>,
    datasets: [{
      label: 'Vaccinations completed',
      data: <?= json_encode($trendData) ?>,
      borderColor: '#2563EB',
      backgroundColor: 'rgba(37,99,235,0.12)',
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#2563EB',
      pointRadius: 4,
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } }
  }
});

const statusCtx = document.getElementById('statusChart');
new Chart(statusCtx, {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($statusLabels) ?>,
    datasets: [{
      data: <?= json_encode($statusData) ?>,
      backgroundColor: ['#F5A623', '#2563EB', '#E15554', '#6366F1', '#0B2545'],
      borderWidth: 0,
    }]
  },
  options: {
    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11.5 } } } },
    cutout: '68%'
  }
});
</script>

<style>
@media (max-width: 992px) { .dash-grid { grid-template-columns: 1fr !important; } }
</style>

<?php include '../includes/footer.php'; ?>
