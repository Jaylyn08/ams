<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/functions/index.php';
require_once __DIR__ . '/functions/student.php';
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
        <div class="d-flex justify-content-between align-items-center" style="margin-top:0;">
          <h3 class="mb-0">Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?>!</h3>
          <div id="dateTimeDisplay" class="text-muted">Philippine Standard Time: <?= $manilaFormatted ?></div>
        </div>

        <div class="summary-card " style="max-width:220px;margin:20px 0;">
          <h6>Section: Gumamela</h6>
          <div class="summary-value"><?= $sectionTotals['Gumamela'] ?></div>
        </div>

        <div class="summary-card" style="max-width:220px;margin:20px 0;">
          <h6>Section: Tulip </h6>
          <div class="summary-value"><?= $sectionTotals['Tulip'] ?></div>
        </div>
        
        <div class="summary-card" style="max-width:220px;margin:20px 0;">
          <h6>Total Students</h6>
          <div class="summary-value"><?= $totalStudents ?></div>
        </div>
      </div>
    </div>
    <!-- Optional JavaScript; choose one of the two! -->
    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="assets/app.js"></script>
    <script>
      // Keep the Manila time display in sync each second using the server timestamp
      (function() {
        var ts = <?= $manilaTimestamp ?> * 1000; // ms
        function updateClock() {
          ts += 1000;
          var d = new Date(ts);
          var options = {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
            timeZone: 'Asia/Manila'
          };
          var str = d.toLocaleString('en-US', options);
          var el = document.getElementById('dateTimeDisplay');
          if (el) el.textContent = 'Philippine Standard Time: ' + str;
        }
        updateClock();
        setInterval(updateClock, 1000);
      })();
    </script>
</body>

</html>