<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/admin_auth_check.php';

$pendingBookingsCount = $pdo->query("SELECT COUNT(*) FROM booking_appointment WHERE Status = 'Pending'")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $name    = trim($_POST['vaccine_name'] ?? '');
        $desc    = trim($_POST['description'] ?? '');
        $ageGrp  = trim($_POST['age_group'] ?? '');
        $stock   = $_POST['stock_status'] ?? 'Available';

        if ($name === '') {
            flash_set('error', 'Vaccine name is required.');
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $pdo->prepare("INSERT INTO vaccine (Vaccine_Name, Description, Age_Group, Stock_Status) VALUES (?,?,?,?)");
                    $stmt->execute([$name, $desc, $ageGrp, $stock]);
                    flash_set('success', 'Vaccine added successfully.');
                } else {
                    $id = (int)$_POST['vaccine_id'];
                    $stmt = $pdo->prepare("UPDATE vaccine SET Vaccine_Name=?, Description=?, Age_Group=?, Stock_Status=? WHERE Vaccine_ID=?");
                    $stmt->execute([$name, $desc, $ageGrp, $stock, $id]);
                    flash_set('success', 'Vaccine updated successfully.');
                }
            } catch (PDOException $ex) {
                flash_set('error', 'Could not save vaccine. Please try again.');
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['vaccine_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM vaccine WHERE Vaccine_ID = ?");
            $stmt->execute([$id]);
            flash_set('success', 'Vaccine deleted.');
        } catch (PDOException $ex) {
            flash_set('error', 'Could not delete — this vaccine is referenced by existing bookings.');
        }
    }
    redirect('vaccines.php' . (!empty($_GET['q']) ? '?q=' . urlencode($_GET['q']) : ''));
}

$q = trim($_GET['q'] ?? '');
$where = ''; $params = [];
if ($q !== '') {
    $where = "WHERE Vaccine_Name LIKE ? OR Age_Group LIKE ?";
    $like = "%$q%";
    $params = [$like, $like];
}
$stmt = $pdo->prepare("SELECT v.*, (SELECT COUNT(*) FROM booking_appointment b WHERE b.Vaccine_ID = v.Vaccine_ID) AS booking_count
                        FROM vaccine v $where ORDER BY v.Vaccine_ID DESC");
$stmt->execute($params);
$vaccines = $stmt->fetchAll();

$pageTitle = 'Vaccines';
$pageSubtitle = 'Add, update and track available vaccines and stock status.';
include '../includes/admin_header.php';
?>

<div class="card-panel">
  <div class="card-panel-head">
    <h3>Vaccine Catalog <span class="id-chip"><?= count($vaccines) ?> total</span></h3>
    <div class="toolbar">
      <form method="GET" class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search vaccine or age group...">
      </form>
      <button class="btn btn-primary btn-sm" onclick="openVaccineModal()"><i class="fa-solid fa-plus"></i> Add Vaccine</button>
    </div>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Vaccine</th><th>Description</th><th>Age Group</th><th>Stock</th><th>Bookings</th><th style="text-align:right;">Actions</th></tr>
      </thead>
      <tbody>
      <?php if (!$vaccines): ?>
        <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-vial-circle-check"></i><h4>No vaccines added yet</h4><p>Add your first vaccine to get started.</p></div></td></tr>
      <?php endif; ?>
      <?php foreach ($vaccines as $v): ?>
        <tr>
          <td class="cell-primary"><?= e($v['Vaccine_Name']) ?></td>
          <td class="cell-sub" style="max-width:260px;"><?= e($v['Description']) ?: '—' ?></td>
          <td><?= e($v['Age_Group']) ?: '—' ?></td>
          <td><?= status_badge($v['Stock_Status']) ?></td>
          <td><span class="id-chip"><?= (int)$v['booking_count'] ?></span></td>
          <td>
            <div class="action-group" style="justify-content:flex-end;">
              <button class="action-btn" title="Edit" onclick='openVaccineModal(<?= json_encode($v, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fa-solid fa-pen"></i></button>
              <form method="POST" onsubmit="return confirmDelete('Delete vaccine \'<?= e(addslashes($v['Vaccine_Name'])) ?>\'?');" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="vaccine_id" value="<?= (int)$v['Vaccine_ID'] ?>">
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

<!-- Add/Edit Vaccine Modal -->
<div class="modal-overlay" id="vaccineModal">
  <div class="modal-box">
    <div class="modal-head">
      <h3 id="vaccineModalTitle">Add Vaccine</h3>
      <button class="modal-close" onclick="closeModal('vaccineModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" id="vaccineForm">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" id="vaccineAction" value="create">
        <input type="hidden" name="vaccine_id" id="vaccineId" value="">

        <div class="form-group">
          <label class="form-label">Vaccine Name</label>
          <input type="text" class="form-control" name="vaccine_name" id="vaccineName" required>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-control" name="description" id="vaccineDescription" rows="3"></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label">Age Group</label>
            <input type="text" class="form-control" name="age_group" id="vaccineAgeGroup" placeholder="e.g. 0–6 months">
          </div>
          <div class="form-group">
            <label class="form-label">Stock Status</label>
            <select class="form-control" name="stock_status" id="vaccineStock">
              <option value="Available">Available</option>
              <option value="Limited">Limited</option>
              <option value="Out of Stock">Out of Stock</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('vaccineModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Vaccine</button>
      </div>
    </form>
  </div>
</div>

<script>
function openVaccineModal(data) {
  const form = document.getElementById('vaccineForm');
  form.reset();
  if (data) {
    document.getElementById('vaccineModalTitle').textContent = 'Edit Vaccine';
    document.getElementById('vaccineAction').value = 'update';
    document.getElementById('vaccineId').value = data.Vaccine_ID;
    document.getElementById('vaccineName').value = data.Vaccine_Name;
    document.getElementById('vaccineDescription').value = data.Description || '';
    document.getElementById('vaccineAgeGroup').value = data.Age_Group || '';
    document.getElementById('vaccineStock').value = data.Stock_Status || 'Available';
  } else {
    document.getElementById('vaccineModalTitle').textContent = 'Add Vaccine';
    document.getElementById('vaccineAction').value = 'create';
    document.getElementById('vaccineId').value = '';
  }
  openModal('vaccineModal');
}
</script>

<?php include '../includes/footer.php'; ?>
