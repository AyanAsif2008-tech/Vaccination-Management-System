<?php
session_start();
unset($_SESSION['hospital_id'], $_SESSION['hospital_name']);
header('Location: login.php');
exit;
