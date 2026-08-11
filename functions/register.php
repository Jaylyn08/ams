<?php
require_once __DIR__ . '/../includes/db.php';

$errors = [];
$success = '';

if (isset($_POST["submit"])) {
	$fullName = trim($_POST["full_name"] ?? '');
	$email = trim($_POST["email"] ?? '');
	$password = $_POST["password"] ?? '';
	$password2 = $_POST["password2"] ?? '';

	if ($fullName === '') {
		$errors[] = 'Full name is required.';
	}
	if ($email === '') {
		$errors[] = 'Email is required.';
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors[] = 'Enter a valid email address.';
	}
	if ($password === '') {
		$errors[] = 'Password is required.';
	}
	if ($password !== $password2) {
		$errors[] = 'Passwords do not match.';
	}

	if (count($errors) === 0) {
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		$role = 'user';
		$sql = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)";
		$stmt = mysqli_stmt_init($conn);
		$prepareStmt = mysqli_stmt_prepare($stmt, $sql);
		if ($prepareStmt) {
			mysqli_stmt_bind_param($stmt, "ssss", $fullName, $email, $hashedPassword, $role);
			if (mysqli_stmt_execute($stmt)) {
				$success = 'Your account has been created successfully. You can now log in.';
			} else {
				// Use statement-specific errno to detect duplicate entry (1062)
				if (mysqli_stmt_errno($stmt) === 1062) {
					$errors[] = 'That email is already registered. Please log in instead.';
				} else {
					$errors[] = 'Something went wrong. Please try again.';
				}
			}
		} else {
			$errors[] = 'Something went wrong. Please try again.';
		}
	}
}
