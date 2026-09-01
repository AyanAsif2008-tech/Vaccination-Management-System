<?php
session_start();
unset($_SESSION['parent_id'], $_SESSION['parent_name']);
header('Location: login.php');
exit;
