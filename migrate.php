<?php
require_once __DIR__ . '/includes/auth.php';

if (!$isAdmin) {
    header('HTTP/1.1 403 Forbidden');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Forbidden</title><link href="assets/style.css" rel="stylesheet"></head><body><div class="page-center" style="min-height:auto;padding:30px;"><div class="wrap"><h2>Access denied</h2><p class="note">Only administrators can run the database migration.</p><p class="note"><a href="index.php">Return to dashboard</a></p></div></div></body></html>';
    exit;
}

require_once __DIR__ . '/config.php';

$success = true;
$messages = [];

try {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    $sql = file_get_contents(__DIR__ . '/sql/setup.sql');
    if ($sql === false) {
        throw new RuntimeException('Could not read sql/setup.sql.');
    }

    mysqli_multi_query($conn, $sql);
    do {
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_more_results($conn) && mysqli_next_result($conn));

    mysqli_close($conn);
    $messages[] = 'Your database schema is now up to date. This is safe to run again any time — it only changes what actually needs changing.';
} catch (Throwable $e) {
    $success = false;
    $messages[] = 'Migration failed: ' . $e->getMessage();
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="assets/style.css" rel="stylesheet">
    <title>Database Migration</title>
</head>

<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="page-center" style="min-height:auto;padding:30px;">
            <div class="wrap" style="max-width:560px;width:100%;padding:32px;">
                <h3 style="margin-top:0;">Database Migration</h3>
                <?php if ($success): ?>
                    <div class="success"><?= htmlspecialchars($messages[0]) ?></div>
                <?php else: ?>
                    <div class="error"><?= htmlspecialchars($messages[0]) ?></div>
                <?php endif; ?>
                <p class="note" style="margin-top:20px;">This runs <code>sql/setup.sql</code> against your database — creates any missing tables (including <code>grade</code>) and migrates older ones (like the old <code>student.section</code> column, or adding <code>student.grade_id</code>) to the current schema.</p>
                <a href="index.php">Back to dashboard</a>
            </div>
        </div>
    </div>
</body>

</html>