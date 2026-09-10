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
        $name    = trim($_POST['child_name'] ?? '');
        $gender  = $_POST['gender'] ?? '';
        $dob     = $_POST['dob'] ?? '';
        $address = trim($_POST['address'] ?? '');
        $notes   = trim($_POST['notes'] ?? '');
        $parentId= (int)($_POST['parent_id'] ?? 0);

        if ($name === '' || $dob === '' || !$parentId) {
            flash_set('error', 'Child name, date of birth and parent are required.');
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $pdo->prepare("INSERT INTO child (Child_Name, Gender, Date_Of_Birth, Address, Notes, Parent_ID) VALUES (?,?,?,?,?,?)");
                    $stmt->execute([$name, $gender, $dob, $address, $notes, $parentId]);
                    flash_set('success', 'Child record added.');
                } else {
                    $id = (int)$_POST['child_id'];
                    $stmt = $pdo->prepare("UPDATE child SET Child_Name=?, Gender=?, Date_Of_Birth=?, Address=?, Notes=?, Parent_ID=? WHERE Child_ID=?");
                    $stmt->execute([$name, $gender, $dob, $address, $notes, $parentId, $id]);
                    flash_set('success', 'Child record updated.');
                }
            } catch (PDOException $ex) {
                flash_set('error', 'Could not save child — please select a valid parent.');
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['child_id'];
        $stmt = $pdo->prepare("DELETE FROM child WHERE Child_ID = ?");
        $stmt->execute([$id]);
        flash_set('success', 'Child record deleted (bookings & vaccination records removed too).');
    }
    redirect('children.php' . (!empty($_GET['q']) ? '?q=' . urlencode($_GET['q']) : ''));
}

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($q !== '') {
    $where = "WHERE c.Child_Name LIKE ? OR p.Name LIKE ?";
    $like = "%$q%";
    $params = [$like, $like];
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM child c JOIN parent p ON p.Parent_ID = c.Parent_ID $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

$sql = "SELECT c.*, p.Name AS Parent_Name,
        (SELECT COUNT(*) FROM booking_appointment b WHERE b.Child_ID = c.Child_ID) AS booking_count
        FROM child c JOIN parent p ON p.Parent_ID = c.Parent_ID
        $where ORDER BY c.Child_ID DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$children = $stmt->fetchAll();

$parentsList = $pdo->query("SELECT Parent_ID, Name FROM parent ORDER BY Name")->fetchAll();

$pageTitle = 'Children';
$pageSubtitle = 'All registered children across every parent account.';
include '../includes/admin_header.php';
?>

<div class="card-panel">
  <div class="card-panel-head">
    <h3>All Children <span class="id-chip"><?= $total ?> total</span></h3>
    <div class="toolbar">
      <form method="GET" class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search child or parent name...">
      </form>
      <button class="btn btn-primary btn-sm" onclick="openChildModal()"><i class="fa-solid fa-plus"></i> Add Child</button>
    </div>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Child</th><th>Gender</th><th>Date of Birth</th><th>Age</th><th>Parent</th><th>Bookings</th><th style="text-align:right;">Actions</th></tr>
      </thead>
      <tbody>
      <?php if (!$children): ?>
        <tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-child-reaching"></i><h4>No children found</h4></div></td></tr>
      <?php endif; ?>
      <?php foreach ($children as $c): ?>
        <tr>
          <td>
            <div class="row-flex">
              <div class="avatar-sm"><?= e(strtoupper(substr($c['Child_Name'],0,1))) ?></div>
              <div>
                <div class="cell-primary"><?= e($c['Child_Name']) ?></div>
                <div class="cell-sub">ID #<?= (int)$c['Child_ID'] ?></div>
              </div>
            </div>
          </td>
          <td><?= e($c['Gender']) ?: '—' ?></td>
          <td><?= format_date($c['Date_Of_Birth']) ?></td>
          <td><span class="id-chip"><?= calculate_age($c['Date_Of_Birth']) ?></span></td>
          <td><?= e($c['Parent_Name']) ?></td>
          <td><span class="id-chip"><?= (int)$c['booking_count'] ?></span></td>
          <td>
            <div class="action-group" style="justify-content:flex-end;">
              <button class="action-btn" title="Edit" onclick='openChildModal(<?= json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fa-solid fa-pen"></i></button>
              <form method="POST" onsubmit="return confirmDelete('Delete child \'<?= e(addslashes($c['Child_Name'])) ?>\'?');" style="display:inline;">
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

  <?php if ($totalPages > 1): ?>
  <div class="pager">
    <span>Page <?= $page ?> of <?= $totalPages ?></span>
    <div class="pager-links">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>&q=<?= urlencode($q) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Add/Edit Child Modal -->
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
          <label class="form-label">Parent</label>
          <select class="form-control" name="parent_id" id="childParent" required>
            <option value="">Select parent</option>
            <?php foreach ($parentsList as $p): ?>
              <option value="<?= (int)$p['Parent_ID'] ?>"><?= e($p['Name']) ?></option>
            <?php endforeach; ?>
          </select>
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
    document.getElementById('childParent').value = data.Parent_ID;
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
