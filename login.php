<?php
session_start();
require_once __DIR__ . '/includes/db.php';

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
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="style.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
	<title>Login</title>
</head>

<body class="page-center">

	<div class="wrap">
		<div class="auth-brand">
			<span>AMS Login</span>
			<h2>Sign in to your account</h2>
		</div>

		<div class="tabs">
			<a href="login.php" class="active">Login</a>
			<a href="register.php">Register</a>
		</div>

		<form action="login.php" method="post">
			<div class="field">
				<label for="login-email">Email</label>
				<input id="login-email" name="email" type="email" required autocomplete="username" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
			</div>
			<div class="field">
				<label for="login-pass">Password</label>
				<div class="input-group mt-2">
					<input id="login-pass" name="password" type="password" required autocomplete="current-password" class="form-control">
					<span id="togglePass" class="input-group-text" role="button" aria-label="Toggle password visibility" style="cursor:pointer">
						<i id="iconEye" class="bi bi-eye" aria-hidden="true"></i>
					</span>
				</div>
			</div>
			<div class="actions">
				<button type="submit" name="submit" class="btn btn-primary">Sign in</button>
			</div>
			<script>
				(function() {
					var btn = document.getElementById('togglePass');
					var pass = document.getElementById('login-pass');
					var icon = document.getElementById('iconEye');
					if (!btn || !pass) return;
					btn.addEventListener('click', function() {
						if (pass.type === 'password') {
							pass.type = 'text';
							if (icon) {
								icon.classList.remove('bi-eye');
								icon.classList.add('bi-eye-slash');
							}
						} else {
							pass.type = 'password';
							if (icon) {
								icon.classList.remove('bi-eye-slash');
								icon.classList.add('bi-eye');
							}
						}
					});
				})();
			</script>
			<?php if ($errors !== ''): ?>
				<div class="error"><?= htmlspecialchars($errors) ?></div>
			<?php endif; ?>
		</form>

		<div class="note">Need an account? <a href="register.php">Create one now</a>.</div>
	</div>

</body>

</html>