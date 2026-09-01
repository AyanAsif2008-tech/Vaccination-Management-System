<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';

if (!empty($_SESSION['parent_id'])) {
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
        $stmt = $pdo->prepare('SELECT * FROM parent WHERE Username = ? OR Email = ? LIMIT 1');
        $stmt->execute([$username, $username]);
        $parent = $stmt->fetch();

        if ($parent && verify_admin_password($password, $parent['Password'])) {
            session_regenerate_id(true);
            $_SESSION['parent_id']   = $parent['Parent_ID'];
            $_SESSION['parent_name'] = $parent['Name'];
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
<title>Parent Login · ImmuTrack VMS</title>
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
      <h1>Welcome back to your family's health hub.</h1>
      <p class="lede">Sign in to book vaccination appointments and track your children's immunization history.</p>
    </div>
    <div class="record-card">
      <div class="rc-row"><span>Upcoming appointments</span><b>Tracked for you</b></div>
      <div class="rc-row"><span>Vaccination records</span><b>Always up to date</b></div>
    </div>
    <div class="login-foot-note">&copy; <?= date('Y') ?> &middot; Vaccination Management System</div>
  </div>

  <div class="login-form-side">
    <div class="login-form-box">
      <a href="../index.php" class="back-home-link"><i class="fa-solid fa-arrow-left"></i> Home</a>
      <h2>Parent sign in</h2>
      <div class="sub">Access your family's vaccination dashboard.</div>

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
        New here? <a href="register.php" style="color:var(--teal);font-weight:600;">Create an account</a>
      </p>
    </div>
  </div>

</div>
</body>
</html>
