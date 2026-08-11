<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userRole = $_SESSION['user_role'] ?? 'user';
if ($userRole !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Forbidden</title><link href="assets/style.css" rel="stylesheet"></head><body><div class="page-center" style="min-height:auto;padding:30px;"><div class="wrap"><h2>Access denied</h2><p class="note">This page is for administrators only.</p><p class="note"><a href="index.php">Return to dashboard</a></p></div></div></body></html>';
    exit;
}
require_once __DIR__ . '/functions/admin.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="assets/style.css" rel="stylesheet">
    <title>Admin Panel</title>
</head>

<body>
    <div class="page-center" style="min-height:auto;padding:30px;">
        <div class="wrap" style="max-width:900px;width:100%;padding:32px;">
            <div class="auth-brand">
                <span>Administrator</span>
                <h2>Admin Panel</h2>
                <p class="note">Only users with admin role can view this page.</p>
            </div>
            <div class="topnav" style="position:relative;max-width:none;margin-top:20px;">
                <a href="index.php">Dashboard</a>
                <a href="report.php">Report</a>
                <a href="student.php">Student</a>
                <a href="logout.php">Logout</a>
            </div>
            <div style="margin-top:24px;">
                <h3>User list</h3>
                <?php if ($successMessage !== ''): ?>
                    <div class="success" style="margin-top:16px;"><?= htmlspecialchars($successMessage) ?></div>
                <?php endif; ?>
                <?php if ($errorMessage !== ''): ?>
                    <div class="error" style="margin-top:16px;"><?= htmlspecialchars($errorMessage) ?></div>
                <?php endif; ?>
                <?php if ($editingUser): ?>
                    <div style="margin-top:20px;padding:20px;border:1px solid rgba(75,124,236,.18);border-radius:18px;background:rgba(255,255,255,.95);">
                        <h4>Edit user #<?= htmlspecialchars($editingUser['id']) ?></h4>
                        <form action="admin.php" method="post" style="display:grid;gap:16px;margin-top:12px;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($editingUser['id']) ?>">
                            <label style="display:block;">
                                Name<br>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($editingUser['full_name']) ?>" required style="width:100%;padding:10px;border:1px solid #dbe7f0;border-radius:12px;">
                            </label>
                            <label style="display:block;">
                                Email<br>
                                <input type="email" name="email" value="<?= htmlspecialchars($editingUser['email']) ?>" required style="width:100%;padding:10px;border:1px solid #dbe7f0;border-radius:12px;">
                            </label>
                            <label style="display:block;">
                                Role<br>
                                <select name="role" style="width:100%;padding:10px;border:1px solid #dbe7f0;border-radius:12px;">
                                    <option value="user" <?= $editingUser['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= $editingUser['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </label>
                            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                                <button type="submit" class="btn" style="padding:12px 16px;">Save changes</button>
                                <a href="admin.php" style="align-self:center;color:#4b7bec;">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                <?php if (!empty($users)): ?>
                    <div style="overflow-x:auto;margin-top:16px;">
                        <table style="width:100%;border-collapse:collapse;font-size:14px;color:#334e68;">
                            <thead>
                                <tr style="background:rgba(75,124,236,.08);text-align:left;">
                                    <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">ID</th>
                                    <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Name</th>
                                    <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Email</th>
                                    <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Role</th>
                                    <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Created</th>
                                    <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr style="border-top:1px solid #edf2f7;">
                                        <td style="padding:12px 14px;"><?= htmlspecialchars($user['id']) ?></td>
                                        <td style="padding:12px 14px;"><?= htmlspecialchars($user['full_name']) ?></td>
                                        <td style="padding:12px 14px;"><?= htmlspecialchars($user['email']) ?></td>
                                        <td style="padding:12px 14px;text-transform:capitalize;"><?= htmlspecialchars($user['role']) ?></td>
                                        <td style="padding:12px 14px;"><?= htmlspecialchars($user['created_at']) ?></td>
                                        <td style="padding:12px 14px;">
                                            <a href="admin.php?edit_id=<?= htmlspecialchars($user['id']) ?>" style="margin-right:8px;padding:7px 12px;border-radius:10px;border:1px solid #4b7bec;color:#4b7bec;text-decoration:none;font-size:13px;">Edit</a>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <form action="admin.php" method="post" style="display:inline-block;margin:0;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                                    <button type="submit" style="padding:7px 12px;border-radius:10px;border:1px solid #dc2626;background:#fff;color:#dc2626;font-size:13px;cursor:pointer;">Delete</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="font-size:13px;color:#94a3b8;">Current</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="note">No users found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>