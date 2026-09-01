<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';

if (!empty($_SESSION['parent_id'])) {
    redirect('dashboard.php');
}

$error = '';
$old = ['name' => '', 'email' => '', 'phone' => '', 'username' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old['name']     = trim($_POST['name'] ?? '');
    $old['email']    = trim($_POST['email'] ?? '');
    $old['phone']    = trim($_POST['phone'] ?? '');
    $old['username'] = trim($_POST['username'] ?? '');
    $old['address']  = trim($_POST['address'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirm         = $_POST['confirm_password'] ?? '';

    if ($old['name'] === '' || $old['email'] === '' || $old['username'] === '' || $password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO parent (Name, Email, Phone, Username, Password, Address) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$old['name'], $old['email'], $old['phone'], $old['username'], $hash, $old['address']]);

            session_regenerate_id(true);
            $_SESSION['parent_id']   = $pdo->lastInsertId();
            $_SESSION['parent_name'] = $old['name'];
            flash_set('success', 'Welcome to ImmuTrack! Your account has been created.');
            redirect('dashboard.php');
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
<title>Create Account · ImmuTrack VMS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="login-shell">

  <div class="login-visual">
    <div class="login-card-brand">
      <div class="brand-mark"><i class="fa-solid fa-syringe"></i></div>
      <div class="brand-word">ImmuTrack<small>Parent Registration</small></div>
    </div>
    <div>
      <h1>Keep every dose, every date, in one place.</h1>
      <p class="lede">Register your family to book vaccination appointments at approved hospitals and track your children's immunization history.</p>
    </div>
    <div class="record-card">
      <div class="rc-row"><span>Book appointments</span><b>In a few clicks</b></div>
      <div class="rc-row"><span>Track approvals</span><b>Real-time status</b></div>
      <div class="rc-row"><span>Vaccination history</span><b>Always on hand</b></div>
    </div>
    <div class="login-foot-note">&copy; <?= date('Y') ?> ImmuTrack &middot; Vaccination Management System</div>
  </div>

  <div class="login-form-side">
    <div class="login-form-box">
      <a href="../index.php" class="back-home-link"><i class="fa-solid fa-arrow-left"></i> Home</a>
      <h2>Create your account</h2>
      <div class="sub">Register as a parent to manage your children's vaccinations.</div>

      <?php if ($error): ?>
        <div class="flash-alert flash-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="register.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" name="name" value="<?= e($old['name']) ?>" required>
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
        <div class="form-group">
          <label class="form-label">Address</label>
          <textarea class="form-control" name="address" rows="2"><?= e($old['address']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Create Account</button>
      </form>

      <p style="text-align:center;color:var(--slate);font-size:13px;margin-top:22px;">
        Already have an account? <a href="login.php" style="color:var(--teal);font-weight:600;">Sign in</a>
      </p>
    </div>
  </div>

</div>
</body>
</html>
