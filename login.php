<?php
session_start();
require_once __DIR__ . '/functions/login.php';
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="assets/style.css" rel="stylesheet">
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
			<?php if ($errors !== ''): ?>
				<div class="error"><?= htmlspecialchars($errors) ?></div>
			<?php endif; ?>
		</form>

		<div class="note">Need an account? <a href="register.php">Create one now</a>.</div>
	</div>

	<script src="assets/app.js"></script>
</body>

</html>