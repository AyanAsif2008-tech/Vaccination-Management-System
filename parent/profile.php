<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/parent_auth_check.php';

$parentId = $_SESSION['parent_id'];
$stmt = $pdo->prepare("SELECT * FROM parent WHERE Parent_ID = ?");
$stmt->execute([$parentId]);
$parent = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name === '' || $email === '') {
            flash_set('error', 'Name and email are required.');
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE parent SET Name=?, Email=?, Phone=?, Address=? WHERE Parent_ID=?");
                $stmt->execute([$name, $email, $phone, $address, $parentId]);
                $_SESSION['parent_name'] = $name;
                flash_set('success', 'Profile updated successfully.');
            } catch (PDOException $ex) {
                flash_set('error', 'Could not update — that email may already be in use.');
            }
        }
        redirect('profile.php');
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!verify_admin_password($current, $parent['Password'])) {
            flash_set('error', 'Current password is incorrect.');
        } elseif (strlen($new) < 6) {
            flash_set('error', 'New password must be at least 6 characters.');
        } elseif ($new !== $confirm) {
            flash_set('error', 'New password and confirmation do not match.');
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE parent SET Password=? WHERE Parent_ID=?");
            $stmt->execute([$hash, $parentId]);
            flash_set('success', 'Password changed successfully.');
        }
        redirect('profile.php');
    }
}

$pageTitle = 'Profile';
$pageSubtitle = 'Manage your account details and password.';
include '../includes/parent_header.php';
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="dash-grid">
  <div class="card-panel">
    <div class="card-panel-head"><h3>Account Details</h3></div>
    <div class="card-panel-body">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="update_profile">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" name="name" value="<?= e($parent['Name']) ?>" pattern="^(?=.*[A-Za-z])[A-Za-z\s'\-]{2,100}$" title="Enter a valid name (letters only, at least 2 characters)" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" value="<?= e($parent['Email']) ?>" pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}" title="Enter a valid email address" required>
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="tel" class="form-control" name="phone" value="<?= e($parent['Phone']) ?>" pattern="(0[0-9]{10}|\+92[0-9]{10}|\+91[0-9]{10})" title="Enter an 11-digit number starting with 0 (e.g. 03001234567), or +92/+91 followed by 10 digits">
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <textarea class="form-control" name="address" rows="2" style="resize: none;"><?= e($parent['Address']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
      </form>
    </div>
  </div>

  <div class="card-panel">
    <div class="card-panel-head"><h3>Change Password</h3></div>
    <div class="card-panel-body">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="change_password">
        <div class="form-group">
          <label class="form-label">Current Password</label>
          <input type="password" class="form-control" name="current_password" required>
        </div>
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input type="password" class="form-control" name="new_password" pattern="(?=.*[A-Za-z])(?=.*\d).{6,}" title="Password must be at least 6 characters and contain at least one letter and one number" required minlength="6">
        </div>
        <div class="form-group">
          <label class="form-label">Confirm New Password</label>
          <input type="password" class="form-control" name="confirm_password" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-key"></i> Update Password</button>
      </form>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
