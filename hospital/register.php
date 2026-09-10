<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';

if (!empty($_SESSION['hospital_id'])) {
    redirect('dashboard.php');
}

$error = '';
$old = ['hospital_name' => '', 'address' => '', 'location' => '', 'phone' => '', 'email' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old['hospital_name'] = trim($_POST['hospital_name'] ?? '');
    $old['address']       = trim($_POST['address'] ?? '');
    $old['location']      = trim($_POST['location'] ?? '');
    $old['phone']         = trim($_POST['phone'] ?? '');
    $old['email']         = trim($_POST['email'] ?? '');
    $old['username']      = trim($_POST['username'] ?? '');
    $password             = $_POST['password'] ?? '';
    $confirm              = $_POST['confirm_password'] ?? '';

    if ($old['hospital_name'] === '' || $old['address'] === '' || $old['email'] === '' || $old['username'] === '' || $password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO hospital (Hospital_Name, Address, Location, Phone, Email, Username, Password, Status) VALUES (?,?,?,?,?,?,?, 'Pending')");
            $stmt->execute([$old['hospital_name'], $old['address'], $old['location'], $old['phone'], $old['email'], $old['username'], $hash]);

            // Status is already 'Pending' by default (see the INSERT above) —
            // the hospital must wait for admin approval before they can log in.
            redirect('../login.php?status=pending_approval');
        } catch (PDOException $ex) {
            $error = 'That email or username is already registered. Try logging in instead.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hospital Registration · Vaccination Management System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="login-shell">

  <div class="login-visual">
    <div class="login-card-brand">
      <div class="logo">
        <img src="../assets/media/Logo 1.png" alt="" style="width: 40px;">
      </div>
      <div class="brand-word">Vacci Track<small>Hospital Registration</small></div>
    </div>
    <div>
      <h1 style="font-weight: bold;">Bring your hospital on board.</h1>
      <p class="lede">Register your facility to receive vaccination appointment requests and manage your own vaccine inventory.</p>
    </div>
    <div class="record-card">
      <div class="rc-row"><span>Booking requests</span><b>Reviewed by you</b></div>
      <div class="rc-row"><span>Vaccine inventory</span><b>Fully self managed</b></div>
      <div class="rc-row"><span>Account approval</span><b>Confirmed by admin</b></div>
    </div>
    <div class="login-foot-note">&copy; <?= date('Y') ?> &middot; Vaccination Management System</div>
  </div>

  <div class="login-form-side">
    <div class="login-form-box">
      <a href="../index.php" class="back-home-link"><i class="fa-solid fa-arrow-left"></i> Home</a>
      <h2>Register your hospital</h2>
      <div class="sub">Your account will need admin approval before you can sign in.</div>

      <?php if ($error): ?>
        <div class="flash-alert flash-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="register.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
          <label class="form-label">Hospital Name</label>
          <input type="text" class="form-control" name="hospital_name" value="<?= e($old['hospital_name']) ?>" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= e($old['email']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" value="<?= e($old['phone']) ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <textarea class="form-control" name="address" rows="2" required style="resize: none;"><?= e($old['address']) ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Location / City</label>
          <input type="text" class="form-control" name="location" value="<?= e($old['location']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Username</label>
          <input type="text" class="form-control" name="username" value="<?= e($old['username']) ?>" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required minlength="6">
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" name="confirm_password" required minlength="6">
          </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Create Hospital Account</button>
      </form>

      <p style="text-align:center;color:var(--slate);font-size:13px;margin-top:22px;">
        Already registered? <a href="../login.php" style="color:var(--teal);font-weight:600;">Sign in</a>
      </p>
    </div>
  </div>

</div>
</body>
</html>
