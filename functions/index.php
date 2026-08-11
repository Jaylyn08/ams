<?php
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$userRole = $_SESSION['user_role'] ?? 'user';
$isAdmin = $userRole === 'admin';
