<?php
require_once __DIR__ . '/functions/register.php';
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Register</title>
	<link rel="stylesheet" href="assets/style.css">

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