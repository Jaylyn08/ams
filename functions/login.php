<?php
require_once __DIR__ . '/../includes/db.php';

$errors = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';

	if ($email === '' || $password === '') {
		$errors = 'Please fill in both email and password.';
	} else {
		$stmt = mysqli_prepare($conn, 'SELECT id, full_name, password, IFNULL(role, "user") AS role FROM users WHERE email = ?');
		mysqli_stmt_bind_param($stmt, 's', $email);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$user = mysqli_fetch_assoc($result);
		mysqli_stmt_close($stmt);

		if ($user && password_verify($password, $user['password'])) {
			session_regenerate_id(true);
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['user_name'] = $user['full_name'];
			$_SESSION['user_role'] = $user['role'];
			header('Location: index.php');
			exit;
		}

		$errors = 'Invalid email or password.';
	}
}
