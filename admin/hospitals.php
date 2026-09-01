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
        $name    = trim($_POST['hospital_name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $location= trim($_POST['location'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $username= trim($_POST['username'] ?? '');
        $password= $_POST['password'] ?? '';
        $status  = $_POST['status'] ?? 'Pending';

        if ($name === '' || $email === '' || $username === '' || $address === '') {
            flash_set('error', 'Hospital name, address, email and username are required.');
        } else {
            try {
                if ($action === 'create') {
                    $pass = $password !== '' ? $password : bin2hex(random_bytes(4));
                    $stmt = $pdo->prepare("INSERT INTO hospital (Hospital_Name, Address, Location, Phone, Email, Username, Password, Status) VALUES (?,?,?,?,?,?,?,?)");
                    $stmt->execute([$name, $address, $location, $phone, $email, $username, $pass, $status]);
                    flash_set('success', 'Hospital added successfully.');
                } else {
                    $id = (int)$_POST['hospital_id'];
                    if ($password !== '') {
                        $stmt = $pdo->prepare("UPDATE hospital SET Hospital_Name=?, Address=?, Location=?, Phone=?, Email=?, Username=?, Password=?, Status=? WHERE Hospital_ID=?");
                        $stmt->execute([$name, $address, $location, $phone, $email, $username, $password, $status, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE hospital SET Hospital_Name=?, Address=?, Location=?, Phone=?, Email=?, Username=?, Status=? WHERE Hospital_ID=?");
                        $stmt->execute([$name, $address, $location, $phone, $email, $username, $status, $id]);
                    }
                    flash_set('success', 'Hospital details updated.');
                }
            } catch (PDOException $ex) {
                flash_set('error', 'Could not save hospital: email or username may already be in use.');
            }
        }
    }

    if ($action === 'set_status') {
        $id = (int)$_POST['hospital_id'];
        $status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE hospital SET Status = ? WHERE Hospital_ID = ?");
        $stmt->execute([$status, $id]);
        flash_set('success', "Hospital status set to $status.");
    }

    if ($action === 'delete') {
        $id = (int)$_POST['hospital_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM hospital WHERE Hospital_ID = ?");
            $stmt->execute([$id]);
            flash_set('success', 'Hospital deleted (related bookings removed too).');
        } catch (PDOException $ex) {
            flash_set('error', 'Could not delete this hospital.');
        }
    }
    redirect('hospitals.php');
}

$q = trim($_GET['q'] ?? '');
$where = ''; $params = [];
if ($q !== '') {
    $where = "WHERE h.Hospital_Name LIKE ? OR h.Location LIKE ? OR h.Email LIKE ?";
    $like = "%$q%";
    $params = [$like, $like, $like];
}
$stmt = $pdo->prepare("SELECT h.*, (SELECT COUNT(*) FROM booking_appointment b WHERE b.Hospital_ID = h.Hospital_ID) AS booking_count
                        FROM hospital h $where ORDER BY h.Hospital_ID DESC");
$stmt->execute($params);
$hospitals = $stmt->fetchAll();

$pageTitle = 'Hospitals';
$pageSubtitle = 'Manage hospital partners and approve new registrations.';
include '../includes/admin_header.php';
?>

<div class="card-panel">
  <div class="card-panel-head">
    <h3>Hospitals <span class="id-chip"><?= count($hospitals) ?> total</span></h3>
    <div class="toolbar">
      <form method="GET" class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search hospital, location or email...">
      </form>
      <button class="btn btn-primary btn-sm" onclick="openHospitalModal()"><i class="fa-solid fa-plus"></i> Add Hospital</button>
    </div>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Hospital</th><th>Contact</th><th>Location</th><th>Status</th><th>Bookings</th><th style="text-align:right;">Actions</th></tr>
      </thead>
      <tbody>
      <?php if (!$hospitals): ?>
        <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-hospital"></i><h4>No hospitals added yet</h4></div></td></tr>
      <?php endif; ?>
      <?php foreach ($hospitals as $h): ?>
        <tr>
          <td>
            <div class="cell-primary"><?= e($h['Hospital_Name']) ?></div>
            <div class="cell-sub">ID #<?= (int)$h['Hospital_ID'] ?></div>
          </td>
          <td>
            <div><?= e($h['Email']) ?></div>
            <div class="cell-sub"><?= e($h['Phone']) ?: '—' ?></div>
          </td>
          <td><?= e($h['Location']) ?: '—' ?></td>
          <td><?= status_badge($h['Status']) ?></td>
          <td><span class="id-chip"><?= (int)$h['booking_count'] ?></span></td>
          <td>
            <div class="action-group" style="justify-content:flex-end;">
              <?php if ($h['Status'] !== 'Approved'): ?>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="set_status">
                  <input type="hidden" name="status" value="Approved">
                  <input type="hidden" name="hospital_id" value="<?= (int)$h['Hospital_ID'] ?>">
                  <button type="submit" class="action-btn" title="Approve"><i class="fa-solid fa-check"></i></button>
                </form>
              <?php endif; ?>
              <button class="action-btn" title="Edit" onclick='openHospitalModal(<?= json_encode($h, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fa-solid fa-pen"></i></button>
              <form method="POST" onsubmit="return confirmDelete('Delete hospital \'<?= e(addslashes($h['Hospital_Name'])) ?>\'?');" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="hospital_id" value="<?= (int)$h['Hospital_ID'] ?>">
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

<!-- Add/Edit Hospital Modal -->
<div class="modal-overlay" id="hospitalModal">
  <div class="modal-box modal-lg">
    <div class="modal-head">
      <h3 id="hospitalModalTitle">Add Hospital</h3>
      <button class="modal-close" onclick="closeModal('hospitalModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" id="hospitalForm">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" id="hospitalAction" value="create">
        <input type="hidden" name="hospital_id" id="hospitalId" value="">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label">Hospital Name</label>
            <input type="text" class="form-control" name="hospital_name" id="hospitalName" required>
          </div>
          <div class="form-group">
            <label class="form-label">Location / City</label>
            <input type="text" class="form-control" name="location" id="hospitalLocation">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Address</label>
          <textarea class="form-control" name="address" id="hospitalAddress" rows="2" required></textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" id="hospitalPhone">
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="hospitalEmail" required>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" name="username" id="hospitalUsername" required>
          </div>
          <div class="form-group">
            <label class="form-label">Password <span class="cell-sub" id="hospitalPwHint">(leave blank to keep unchanged)</span></label>
            <input type="password" class="form-control" name="password" id="hospitalPassword" placeholder="••••••••">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Status</label>
          <select class="form-control" name="status" id="hospitalStatus">
            <option value="Pending">Pending</option>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
          </select>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('hospitalModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Hospital</button>
      </div>
    </form>
  </div>
</div>

<script>
function openHospitalModal(data) {
  const form = document.getElementById('hospitalForm');
  form.reset();
  if (data) {
    document.getElementById('hospitalModalTitle').textContent = 'Edit Hospital';
    document.getElementById('hospitalAction').value = 'update';
    document.getElementById('hospitalId').value = data.Hospital_ID;
    document.getElementById('hospitalName').value = data.Hospital_Name;
    document.getElementById('hospitalLocation').value = data.Location || '';
    document.getElementById('hospitalAddress').value = data.Address;
    document.getElementById('hospitalPhone').value = data.Phone || '';
    document.getElementById('hospitalEmail').value = data.Email;
    document.getElementById('hospitalUsername').value = data.Username;
    document.getElementById('hospitalStatus').value = data.Status || 'Pending';
    document.getElementById('hospitalPwHint').style.display = 'inline';
  } else {
    document.getElementById('hospitalModalTitle').textContent = 'Add Hospital';
    document.getElementById('hospitalAction').value = 'create';
    document.getElementById('hospitalId').value = '';
    document.getElementById('hospitalPwHint').style.display = 'none';
  }
  openModal('hospitalModal');
}
</script>

<?php include '../includes/footer.php'; ?>
