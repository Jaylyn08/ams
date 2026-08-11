<?php
require_once __DIR__ . '/includes/auth.php';
?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="assets/style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <title>Dashboard</title>
</head>

<body>
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <div class="app-content">
    <div class="page-center" style="min-height:auto;padding:30px;">
      <div class="wrap" style="max-width:900px;width:100%;padding:32px;">
        <h3 style="margin-top:0;">Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?>!</h3>
        <p class="note">You are signed in as <strong><?= $isAdmin ? 'Administrator' : 'User'; ?></strong>.</p>
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </div>
  <!-- Optional JavaScript; choose one of the two! -->
  <!-- Option 1: Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  <script src="assets/app.js"></script>
</body>

</html>
