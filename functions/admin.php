<?php
require_once __DIR__ . '/../includes/db.php';
$successMessage = '';
$errorMessage = '';
$editingUser = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = intval($_POST['user_id'] ?? 0);

    if ($action === 'delete') {
        if ($userId === $_SESSION['user_id']) {
            $errorMessage = 'You cannot delete your own account while signed in.';
        } else {
            $stmt = mysqli_prepare($conn, 'DELETE FROM users WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $userId);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = 'User deleted successfully.';
            } else {
                $errorMessage = 'Failed to delete user.';
            }
            mysqli_stmt_close($stmt);
        }
    }

    if ($action === 'update') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'user');

        if ($fullName === '' || $email === '') {
            $errorMessage = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Enter a valid email address.';
        } else {
            $stmt = mysqli_prepare($conn, 'UPDATE users SET full_name = ?, email = ?, role = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'sssi', $fullName, $email, $role, $userId);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = 'User updated successfully.';
            } else {
                $errorMessage = 'Failed to update user.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $stmt = mysqli_prepare($conn, 'SELECT id, full_name, email, role FROM users WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $editId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editingUser = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

$userResult = mysqli_query($conn, 'SELECT id, full_name, email, role, created_at FROM users ORDER BY id DESC');
$users = $userResult ? mysqli_fetch_all($userResult, MYSQLI_ASSOC) : [];
