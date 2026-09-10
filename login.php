<?php
session_start();
require_once 'config/db.php';
require_once 'config/functions.php';

// If already logged in under any role, go straight to that dashboard
if (!empty($_SESSION['admin_id'])) {
    redirect('admin/dashboard.php');
}
if (!empty($_SESSION['hospital_id'])) {
    redirect('hospital/dashboard.php');
}
if (!empty($_SESSION['parent_id'])) {
    redirect('parent/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // 1. Try Admin
        $stmt = $pdo->prepare('SELECT * FROM admin WHERE Username = ?  LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        // 2. Try Hospital
        $stmt = $pdo->prepare('SELECT * FROM hospital WHERE Username = ? OR Email = ? LIMIT 1');
        $stmt->execute([$username, $username]);
        $hospital = $stmt->fetch();

        // 3. Try Parent
        $stmt = $pdo->prepare('SELECT * FROM parent WHERE Username = ? OR Email = ? LIMIT 1');
        $stmt->execute([$username, $username]);
        $parent = $stmt->fetch();

        if ($admin && verify_admin_password($password, $admin['Password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']       = $admin['Admin_ID'];
            $_SESSION['admin_name']     = $admin['Name'];
            $_SESSION['admin_username'] = $admin['Username'];
            $_SESSION['admin_role']     = $admin['Role'];
            redirect('admin/dashboard.php');

        } elseif ($hospital && verify_admin_password($password, $hospital['Password'])) {
            if ($hospital['Status'] === 'Rejected') {
                $error = 'Your hospital account request was rejected. Please contact the administrator.';
            } elseif ($hospital['Status'] !== 'Approved') {
                $error = 'Your registration request has been submitted. Please wait till the admin approves it.';
            } else {
                session_regenerate_id(true);
                $_SESSION['hospital_id']   = $hospital['Hospital_ID'];
                $_SESSION['hospital_name'] = $hospital['Hospital_Name'];
                redirect('hospital/dashboard.php');
            }

        } elseif ($parent && verify_admin_password($password, $parent['Password'])) {
            session_regenerate_id(true);
            $_SESSION['parent_id']   = $parent['Parent_ID'];
            $_SESSION['parent_name'] = $parent['Name'];
            redirect('parent/dashboard.php');

        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login · Vaccination Management System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-shell">

  <div class="login-visual">
    <div class="login-card-brand">
      <div class="logo">
        <img src="assets/media/Logo 1.png" alt="" style="width: 40px; ">
      </div>
      <div class="brand-word">Vacci Track<small>Sign In</small></div>
    </div>

    <div>
      <h1 style="font-weight: bold">Login Page</h1>
      <p class="lede">Parents, hospitals, and administrators all sign in here you'll be taken straight to your own dashboard.</p>
    </div>

    <div class="record-card">
      <div class="rc-row"><span>Parents</span><b>Book &amp; track vaccinations</b></div>
      <div class="rc-row"><span>Hospitals</span><b>Manage bookings &amp; inventory</b></div>
      <div class="rc-row"><span>Admin</span><b>Oversee the whole system</b></div>
    </div>

    <div class="login-foot-note">&copy; <?= date('Y') ?> &middot; Vaccination Management System</div>
  </div>

  <div class="login-form-side">
    <div class="login-form-box">
      <a href="index.php" class="back-home-link"><i class="fa-solid fa-arrow-left"></i> Home</a>
      <h2>Welcome back</h2>
      <div class="sub">Sign in with your username or email and password.</div>

      <?php if (($_GET['status'] ?? '') === 'pending_approval'): ?>
        <div class="flash-alert flash-success"><i class="fa-solid fa-circle-check"></i> Your registration request has been submitted. Please wait till the admin approves it.</div>
      <?php endif; ?>

      <?php $flash = flash_get(); if ($flash): ?>
        <div class="flash-alert flash-<?= e($flash['type']) ?>">
          <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="flash-alert flash-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
          <label class="form-label" for="username">Username or Email</label>
          <div class="input-icon-wrap">
            <i class="fa-solid fa-user"></i>
            <input type="text" class="form-control" id="username" name="username" required autofocus>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="input-icon-wrap">
            <i class="fa-solid fa-lock"></i>
            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-right-to-bracket"></i> Sign In</button>
      </form>

      <p style="text-align:center;color:var(--slate);font-size:13px;margin-top:22px;">
        Don't have an account? <a href="parent/register.php" style="color:var(--teal);font-weight:600;">Register as a parent</a>
        or <a href="hospital/register.php" style="color:var(--teal);font-weight:600;">as a hospital</a>
      </p>
      <div class="credentials" style="font-size: 13px; text-align: center;">
        <p>Admin credential <span style="font-weight: bolder; color: var(--slate)">admin/admin123</span></p>
        <p>Parent credential <span style="font-weight: bolder; color: var(--slate)">sahil@gmail.com/sahil123</span></p>
        <p>Hospital credential <span style="font-weight: bolder; color: var(--slate)">jinnah@gmail.org/jinnah123</span></p>
      </div>
    </div>
  </div>

</div>
</body>
</html>
