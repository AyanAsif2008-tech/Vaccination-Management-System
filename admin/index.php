<?php
session_start();
header('Location: ' . (!empty($_SESSION['admin_id']) ? 'dashboard.php' : 'login.php'));
exit;
