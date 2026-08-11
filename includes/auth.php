<?php
// Shared auth guard: starts the session, requires a logged-in user, and
// exposes the current user's role so pages/sidebar can branch on it.
// Include this instead of calling session_start() + the user_id check by hand.
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$isAdmin = ($_SESSION['user_role'] ?? 'user') === 'admin';
$currentUserName = $_SESSION['user_name'] ?? '';
