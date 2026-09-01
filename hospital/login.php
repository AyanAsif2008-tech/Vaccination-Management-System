<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';

if (!empty($_SESSION['hospital_id'])) {
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
        $stmt = $pdo->prepare('SELECT * FROM hospital WHERE Username = ? OR Email = ? LIMIT 1');
        $stmt->execute([$username, $username]);
        $hospital = $stmt->fetch();

        if (!$hospital || !verify_admin_password($password, $hospital['Password'])) {
            $error = 'Invalid username or password.';
        } elseif ($hospital['Status'] !== 'Approved') {
            $error = 'Your hospital account is pending admin approval. Please check back later.';
        } else {
            session_regenerate_id(true);
            $_SESSION['hospital_id']   = $hospital['Hospital_ID'];
            $_SESSION['hospital_name'] = $hospital['Hospital_Name'];
            redirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hospital Login · ImmuTrack VMS</title>
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
      <div class="brand-word">Vaccinataion Management System<small>Protecting Lives</small></div>
    </div>
    <div>
      <h1>Manage appointment requests with confidence.</h1>
      <p class="lede">Review incoming booking requests, approve or reject them, and record completed vaccinations for every child in your care.</p>
    </div>
    <div class="record-card">
      <div class="rc-row"><span>Incoming requests</span><b>Reviewed in one place</b></div>
      <div class="rc-row"><span>Vaccination records</span><b>Logged instantly</b></div>
    </div>
    <div class="login-foot-note">&copy; <?= date('Y') ?>&middot; Vaccination Management System</div>
  </div>

  <div class="login-form-side">
    <div class="login-form-box">
      <a href="../index.php" class="back-home-link"><i class="fa-solid fa-arrow-left"></i> Home</a>
      <h2>Hospital sign in</h2>
      <div class="sub">Access your hospital's booking queue.</div>

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

      <p style="text-align:center;color:var(--slate);font-size:12.5px;margin-top:22px;">
        Hospital accounts are created and approved by the system administrator.
      </p>
    </div>
  </div>

</div>
</body>
</html>
