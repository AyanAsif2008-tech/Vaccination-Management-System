<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/admin_auth_check.php';

$pendingBookingsCount = $pdo->query("SELECT COUNT(*) FROM booking_appointment WHERE Status = 'Pending'")->fetchColumn();

// ---------- Handle actions ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $username= trim($_POST['username'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $password= $_POST['password'] ?? '';

        if ($name === '' || $email === '' || $username === '') {
            flash_set('error', 'Name, email and username are required.');
        } else {
            try {
                if ($action === 'create') {
                    $pass = $password !== '' ? $password : bin2hex(random_bytes(4));
                    $stmt = $pdo->prepare("INSERT INTO parent (Name, Email, Phone, Username, Password, Address) VALUES (?,?,?,?,?,?)");
                    $stmt->execute([$name, $email, $phone, $username, $pass, $address]);
                    flash_set('success', 'Parent account created successfully.');
                } else {
                    $id = (int)$_POST['parent_id'];
                    if ($password !== '') {
                        $stmt = $pdo->prepare("UPDATE parent SET Name=?, Email=?, Phone=?, Username=?, Password=?, Address=? WHERE Parent_ID=?");
                        $stmt->execute([$name, $email, $phone, $username, $password, $address, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE parent SET Name=?, Email=?, Phone=?, Username=?, Address=? WHERE Parent_ID=?");
                        $stmt->execute([$name, $email, $phone, $username, $address, $id]);
                    }
                    flash_set('success', 'Parent details updated.');
                }
            } catch (PDOException $ex) {
                flash_set('error', 'Could not save parent: email or username may already be in use.');
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['parent_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM parent WHERE Parent_ID = ?");
            $stmt->execute([$id]);
            flash_set('success', 'Parent record deleted (children & bookings removed too).');
        } catch (PDOException $ex) {
            flash_set('error', 'Could not delete this parent.');
        }
    }
    redirect('parents.php' . (!empty($_GET['q']) ? '?q=' . urlencode($_GET['q']) : ''));
}

// ---------- Search + pagination ----------
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($q !== '') {
    $where = "WHERE p.Name LIKE ? OR p.Email LIKE ? OR p.Username LIKE ? OR p.Phone LIKE ?";
    $like = "%$q%";
    $params = [$like, $like, $like, $like];
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM parent p $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

$sql = "SELECT p.*, (SELECT COUNT(*) FROM child c WHERE c.Parent_ID = p.Parent_ID) AS child_count
        FROM parent p $where ORDER BY p.Parent_ID DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$parents = $stmt->fetchAll();

$pageTitle = 'Parents';
$pageSubtitle = 'View, edit and manage registered parent accounts.';
include '../includes/admin_header.php';
?>

<div class="card-panel">
  <div class="card-panel-head">
    <h3>All Parents <span class="id-chip"><?= $total ?> total</span></h3>
    <div class="toolbar">
      <form method="GET" class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search name, email, username...">
      </form>
      <button class="btn btn-primary btn-sm" onclick="openParentModal()"><i class="fa-solid fa-plus"></i> Add Parent</button>
    </div>
  </div>

  <div class="table-wrap">
    <table class="data-table" id="parentsTable">
      <thead>
        <tr><th>Parent</th><th>Contact</th><th>Username</th><th>Children</th><th>Address</th><th style="text-align:right;">Actions</th></tr>
      </thead>
      <tbody>
      <?php if (!$parents): ?>
        <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-user-group"></i><h4>No parents found</h4><p>Try a different search or add a new parent.</p></div></td></tr>
      <?php endif; ?>
      <?php foreach ($parents as $p): ?>
        <tr>
          <td>
            <div class="row-flex">
              <div class="avatar-sm"><?= e(strtoupper(substr($p['Name'],0,1))) ?></div>
              <div>
                <div class="cell-primary"><?= e($p['Name']) ?></div>
                <div class="cell-sub">ID #<?= (int)$p['Parent_ID'] ?></div>
              </div>
            </div>
          </td>
          <td>
            <div><?= e($p['Email']) ?></div>
            <div class="cell-sub"><?= e($p['Phone']) ?></div>
          </td>
          <td><?= e($p['Username']) ?></td>
          <td><span class="id-chip"><?= (int)$p['child_count'] ?> child<?= $p['child_count'] == 1 ? '' : 'ren' ?></span></td>
          <td class="cell-sub" style="max-width:220px;"><?= e($p['Address']) ?: '—' ?></td>
          <td>
            <div class="action-group" style="justify-content:flex-end;">
              <button class="action-btn" title="Edit" onclick='openParentModal(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fa-solid fa-pen"></i></button>
              <form method="POST" onsubmit="return confirmDelete('Delete parent \'<?= e(addslashes($p['Name'])) ?>\'? This also removes their children and bookings.');" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="parent_id" value="<?= (int)$p['Parent_ID'] ?>">
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

<!-- Add/Edit Parent Modal -->
<div class="modal-overlay" id="parentModal">
  <div class="modal-box">
    <div class="modal-head">
      <h3 id="parentModalTitle">Add Parent</h3>
      <button class="modal-close" onclick="closeModal('parentModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" id="parentForm">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" id="parentAction" value="create">
        <input type="hidden" name="parent_id" id="parentId" value="">

        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" name="name" id="parentName" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" id="parentEmail" required>
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="text" class="form-control" name="phone" id="parentPhone">
        </div>
        <div class="form-group">
          <label class="form-label">Username</label>
          <input type="text" class="form-control" name="username" id="parentUsername" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password <span class="cell-sub" id="parentPwHint">(leave blank to keep unchanged)</span></label>
          <input type="password" class="form-control" name="password" id="parentPassword" placeholder="••••••••">
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <textarea class="form-control" name="address" id="parentAddress" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('parentModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Parent</button>
      </div>
    </form>
  </div>
</div>

<script>
function openParentModal(data) {
  const form = document.getElementById('parentForm');
  form.reset();
  if (data) {
    document.getElementById('parentModalTitle').textContent = 'Edit Parent';
    document.getElementById('parentAction').value = 'update';
    document.getElementById('parentId').value = data.Parent_ID;
    document.getElementById('parentName').value = data.Name;
    document.getElementById('parentEmail').value = data.Email;
    document.getElementById('parentPhone').value = data.Phone;
    document.getElementById('parentUsername').value = data.Username;
    document.getElementById('parentAddress').value = data.Address || '';
    document.getElementById('parentPwHint').style.display = 'inline';
  } else {
    document.getElementById('parentModalTitle').textContent = 'Add Parent';
    document.getElementById('parentAction').value = 'create';
    document.getElementById('parentId').value = '';
    document.getElementById('parentPwHint').style.display = 'none';
  }
  openModal('parentModal');
}
</script>

<?php include '../includes/footer.php'; ?>
