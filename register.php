<?php
require_once __DIR__ . '/includes/db.php';

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

?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Register</title>
	<link rel="stylesheet" href="style.css">

</head>

<body class="page-center">
	<div class="wrap">

		<div class="auth-brand">
			<span>AMS Registration</span>
			<h2>Create your account</h2>
			<p class="note" style="margin-top:10px;">Join AMS and manage attendance with your account.</p>
		</div>
		<div class="tabs">
			<a href="login.php">Login</a>
			<a href="register.php" class="active">Register</a>
		</div>

		<form action="register.php" method="post">
			<div class="field">
				<label for="reg-name">Full name</label>
				<input id="reg-name" name="full_name" type="text" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
			</div>
			<div class="field">
				<label for="reg-email">Email</label>
				<input id="reg-email" name="email" type="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
			</div>

			<div class="field" style="flex:1">
				<label for="reg-pass">Password</label>
				<input id="reg-pass" name="password" type="password" required>
			</div>

			<div class="field" style="flex:2">
				<label for="reg-pass2">Confirm</label>
				<input id="reg-pass2" name="password2" type="password" required>
			</div>


			<div class="actions">

				<button type="submit" name="submit" class="btn">Create account</button>
			</div>

			<?php if (!empty($errors)): ?>
				<div class="error">
					<?php foreach ($errors as $error): ?>
						<div><?= htmlspecialchars($error) ?></div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ($success !== ''): ?>
				<div class="success"><?= htmlspecialchars($success) ?></div>
			<?php endif; ?>
		</form>

		<div class="note">Already have an account? <a href="login.php">Sign in now</a>.</div>
	</div>
</body>

</html>