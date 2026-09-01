<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admin WHERE Username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && verify_admin_password($password, $admin['Password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']       = $admin['Admin_ID'];
            $_SESSION['admin_name']     = $admin['Name'];
            $_SESSION['admin_username'] = $admin['Username'];
            $_SESSION['admin_role']     = $admin['Role'];
            redirect('dashboard.php');
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
<title>Admin Login · ImmuTrack VMS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="login-shell">

  <div class="login-visual">
    <div class="login-card-brand">
      <div class="logo">
        <img src="../assets/media/logo.png" alt="" style="width: 30px; filter: invert(1);">
      </div>
      <div class="brand-word">Vaccination Management System<small>Protecting Lives</small></div>
    </div>

    <div>
      <h1>One record for every child's immunization journey.</h1>
      <p class="lede">Coordinate parents, hospitals, vaccine stock and booking approvals from a single, centralized admin console.</p>
    </div>

    <div class="record-card">
      <div class="rc-row"><span>Pending bookings</span><b>Reviewed daily</b></div>
      <div class="rc-row"><span>Hospitals onboarded</span><b>Verified &amp; active</b></div>
      <div class="rc-row"><span>Vaccination records</span><b>Synced in real time</b></div>
    </div>

    <div class="login-foot-note">&copy; <?= date('Y') ?> &middot; Vaccination Management System</div>
  </div>

  <div class="login-form-side">
    <div class="login-form-box">
      <a href="../index.php" class="back-home-link"><i class="fa-solid fa-arrow-left"></i> Home</a>
      <h2>Welcome back</h2>
      <div class="sub">Sign in to manage parents, hospitals, vaccines and bookings.</div>

      <?php if ($error): ?>
        <div class="flash-alert flash-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <div class="input-icon-wrap">
            <i class="fa-solid fa-user"></i>
            <input type="text" class="form-control" id="username" name="username" placeholder="admin" required autofocus>
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

      <p style="text-align:center;color:var(--slate);font-size:12px;margin-top:22px;">
        Admin credentials: <b>admin</b> / <b>admin123</b>
      </p>
    </div>
  </div>

</div>
</body>
</html>
