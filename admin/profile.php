<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';
require_once '../includes/admin_auth_check.php';

$pendingBookingsCount = $pdo->query("SELECT COUNT(*) FROM booking_appointment WHERE Status = 'Pending'")->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM admin WHERE Admin_ID = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');

        if ($name === '' || $email === '' || $username === '') {
            flash_set('error', 'Name, email and username are required.');
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE admin SET Name=?, Email=?, Username=? WHERE Admin_ID=?");
                $stmt->execute([$name, $email, $username, $admin['Admin_ID']]);
                $_SESSION['admin_name'] = $name;
                $_SESSION['admin_username'] = $username;
                flash_set('success', 'Profile updated successfully.');
            } catch (PDOException $ex) {
                flash_set('error', 'Could not update — email or username may already be in use.');
            }
        }
        redirect('profile.php');
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!verify_admin_password($current, $admin['Password'])) {
            flash_set('error', 'Current password is incorrect.');
        } elseif (strlen($new) < 6) {
            flash_set('error', 'New password must be at least 6 characters.');
        } elseif ($new !== $confirm) {
            flash_set('error', 'New password and confirmation do not match.');
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admin SET Password=? WHERE Admin_ID=?");
            $stmt->execute([$hash, $admin['Admin_ID']]);
            flash_set('success', 'Password changed successfully.');
        }
        redirect('profile.php');
    }
}

$pageTitle = 'Profile';
$pageSubtitle = 'Manage your admin account details and password.';
include '../includes/admin_header.php';
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="dash-grid">

  <div class="card-panel">
    <div class="card-panel-head"><h3>Account Details</h3></div>
    <div class="card-panel-body">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:22px;">
        <div class="admin-avatar" style="width:56px;height:56px;font-size:20px;"><?= e(strtoupper(substr($admin['Name'],0,1))) ?></div>
        <div>
          <div style="font-weight:700;font-size:16px;color:var(--ink);"><?= e($admin['Name']) ?></div>
          <div class="cell-sub"><?= e($admin['Role']) ?></div>
        </div>
      </div>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="update_profile">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" name="name" value="<?= e($admin['Name']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" value="<?= e($admin['Email']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Username</label>
          <input type="text" class="form-control" name="username" value="<?= e($admin['Username']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Role</label>
          <input type="text" class="form-control" value="<?= e($admin['Role']) ?>" disabled>
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
          <input type="password" class="form-control" name="new_password" required minlength="6">
          <div class="form-hint">At least 6 characters.</div>
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
