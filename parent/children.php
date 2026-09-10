<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/parent_auth_check.php';

$parentId = $_SESSION['parent_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $name    = trim($_POST['child_name'] ?? '');
        $gender  = $_POST['gender'] ?? '';
        $dob     = $_POST['dob'] ?? '';
        $address = trim($_POST['address'] ?? '');
        $notes   = trim($_POST['notes'] ?? '');

        if ($name === '' || $dob === '') {
            flash_set('error', 'Child name and date of birth are required.');
        } elseif ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO child (Child_Name, Gender, Date_Of_Birth, Address, Notes, Parent_ID) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$name, $gender, $dob, $address, $notes, $parentId]);
            flash_set('success', 'Child added successfully.');
        } else {
            // Only allow editing a child that belongs to this parent
            $id = (int)$_POST['child_id'];
            $stmt = $pdo->prepare("UPDATE child SET Child_Name=?, Gender=?, Date_Of_Birth=?, Address=?, Notes=? WHERE Child_ID=? AND Parent_ID=?");
            $stmt->execute([$name, $gender, $dob, $address, $notes, $id, $parentId]);
            flash_set('success', 'Child details updated.');
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['child_id'];
        $stmt = $pdo->prepare("DELETE FROM child WHERE Child_ID=? AND Parent_ID=?");
        $stmt->execute([$id, $parentId]);
        flash_set('success', 'Child record removed.');
    }
    redirect('children.php');
}

$stmt = $pdo->prepare("SELECT c.*, (SELECT COUNT(*) FROM booking_appointment b WHERE b.Child_ID = c.Child_ID) AS booking_count
                        FROM child c WHERE c.Parent_ID = ? ORDER BY c.Child_ID DESC");
$stmt->execute([$parentId]);
$children = $stmt->fetchAll();

$pageTitle = 'My Children';
$pageSubtitle = 'Manage your children\'s profiles.';
include '../includes/parent_header.php';
?>

<div class="card-panel">
  <div class="card-panel-head">
    <h3>My Children <span class="id-chip"><?= count($children) ?> total</span></h3>
    <button class="btn btn-primary btn-sm" onclick="openChildModal()"><i class="fa-solid fa-plus"></i> Add Child</button>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Child</th><th>Gender</th><th>Date of Birth</th><th>Age</th><th>Bookings</th><th style="text-align:right;">Actions</th></tr></thead>
      <tbody>
      <?php if (!$children): ?>
        <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-child-reaching"></i><h4>No children added yet</h4><p>Add your first child to start booking vaccinations.</p></div></td></tr>
      <?php endif; ?>
      <?php foreach ($children as $c): ?>
        <tr>
          <td>
            <div class="row-flex">
              <div class="avatar-sm"><?= e(strtoupper(substr($c['Child_Name'],0,1))) ?></div>
              <div class="cell-primary"><?= e($c['Child_Name']) ?></div>
            </div>
          </td>
          <td><?= e($c['Gender']) ?: '—' ?></td>
          <td><?= format_date($c['Date_Of_Birth']) ?></td>
          <td><span class="id-chip"><?= calculate_age($c['Date_Of_Birth']) ?></span></td>
          <td><span class="id-chip"><?= (int)$c['booking_count'] ?></span></td>
          <td>
            <div class="action-group" style="justify-content:flex-end;">
              <button class="action-btn" title="Edit" onclick='openChildModal(<?= json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fa-solid fa-pen"></i></button>
              <form method="POST" onsubmit="return confirmDelete('Remove \'<?= e(addslashes($c['Child_Name'])) ?>\' from your family profile?');" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="child_id" value="<?= (int)$c['Child_ID'] ?>">
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

<div class="modal-overlay" id="childModal">
  <div class="modal-box">
    <div class="modal-head">
      <h3 id="childModalTitle">Add Child</h3>
      <button class="modal-close" onclick="closeModal('childModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" id="childForm">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" id="childAction" value="create">
        <input type="hidden" name="child_id" id="childId" value="">

        <div class="form-group">
          <label class="form-label">Child Name</label>
          <input type="text" class="form-control" name="child_name" id="childName" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label">Gender</label>
            <select class="form-control" name="gender" id="childGender">
              <option value="">Select</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Date of Birth</label>
            <input type="date" class="form-control" name="dob" id="childDob" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <input type="text" class="form-control" name="address" id="childAddress">
        </div>
        <div class="form-group">
          <label class="form-label">Notes</label>
          <textarea class="form-control" name="notes" id="childNotes" rows="2" placeholder="Allergies, medical notes, etc."></textarea>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('childModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Child</button>
      </div>
    </form>
  </div>
</div>

<script>
function openChildModal(data) {
  const form = document.getElementById('childForm');
  form.reset();
  if (data) {
    document.getElementById('childModalTitle').textContent = 'Edit Child';
    document.getElementById('childAction').value = 'update';
    document.getElementById('childId').value = data.Child_ID;
    document.getElementById('childName').value = data.Child_Name;
    document.getElementById('childGender').value = data.Gender || '';
    document.getElementById('childDob').value = data.Date_Of_Birth;
    document.getElementById('childAddress').value = data.Address || '';
    document.getElementById('childNotes').value = data.Notes || '';
  } else {
    document.getElementById('childModalTitle').textContent = 'Add Child';
    document.getElementById('childAction').value = 'create';
    document.getElementById('childId').value = '';
  }
  openModal('childModal');
}
</script>

<?php include '../includes/footer.php'; ?>
