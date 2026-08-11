<?php
// "Manage Grades" admin page — lets an admin add, rename, or delete the
// grade levels students can be assigned to. All the actual DB work happens
// in functions/grade.php; this file is just the HTML that renders the
// results it prepares ($successMessage, $errorMessage, $editingGrade,
// $gradeCounts, $grades).

require_once __DIR__ . '/includes/auth.php';

// Admin-only gate — same pattern used by admin.php, migrate.php, section.php.
if (!$isAdmin) {
    header('HTTP/1.1 403 Forbidden');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Forbidden</title><link href="assets/style.css" rel="stylesheet"></head><body><div class="page-center" style="min-height:auto;padding:30px;"><div class="wrap"><h2>Access denied</h2><p class="note">This page is for administrators only.</p><p class="note"><a href="index.php">Return to dashboard</a></p></div></div></body></html>';
    exit;
}
require_once __DIR__ . '/functions/grade.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="assets/style.css" rel="stylesheet">
    <title>Manage Grades</title>
</head>

<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="page-center" style="min-height:auto;padding:30px;">
            <div class="wrap" style="max-width:700px;width:100%;padding:32px;">
                <div class="auth-brand">
                    <span>Administrator</span>
                    <h2>Manage Grades</h2>
                    <p class="note">Add, rename, or remove the grade levels students can be assigned to.</p>
                </div>
                <div style="margin-top:24px;">
                    <!-- Flash messages set by functions/grade.php after a create/update/delete -->
                    <?php if ($successMessage !== ''): ?>
                        <div class="success" style="margin-top:16px;"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($errorMessage !== ''): ?>
                        <div class="error" style="margin-top:16px;"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>

                    <!--
                        One form handles both adding and editing:
                        - No $editingGrade (normal state) -> action=create, empty name field.
                        - $editingGrade set (came from ?edit_id=..) -> action=update, name
                          pre-filled, plus a hidden grade_id so the backend knows which row to update.
                    -->
                    <div style="margin-top:20px;padding:20px;border:1px solid rgba(75,124,236,.18);border-radius:18px;background:rgba(255,255,255,.95);">
                        <h4><?= $editingGrade ? 'Edit grade #' . htmlspecialchars($editingGrade['id']) : 'Add a new grade' ?></h4>
                        <form action="grade.php" method="post" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-top:12px;">
                            <input type="hidden" name="action" value="<?= $editingGrade ? 'update' : 'create' ?>">
                            <?php if ($editingGrade): ?>
                                <input type="hidden" name="grade_id" value="<?= htmlspecialchars($editingGrade['id']) ?>">
                            <?php endif; ?>
                            <label style="flex:1;min-width:200px;">
                                Grade name<br>
                                <input type="text" name="name" value="<?= htmlspecialchars($editingGrade['name'] ?? '') ?>" required style="width:100%;padding:10px;border:1px solid #dbe7f0;border-radius:12px;">
                            </label>
                            <button type="submit" class="btn" style="padding:12px 16px;"><?= $editingGrade ? 'Save changes' : 'Add grade' ?></button>
                            <?php if ($editingGrade): ?>
                                <a href="grade.php" style="align-self:center;color:#4b7bec;">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Existing grades, with a live student count and per-row edit/delete actions -->
                    <?php if (!empty($grades)): ?>
                        <div style="overflow-x:auto;margin-top:16px;">
                            <table style="width:100%;border-collapse:collapse;font-size:14px;color:#334e68;">
                                <thead>
                                    <tr style="background:rgba(75,124,236,.08);text-align:left;">
                                        <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">ID</th>
                                        <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Name</th>
                                        <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Students</th>
                                        <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $grade): ?>
                                        <tr style="border-top:1px solid #edf2f7;">
                                            <td style="padding:12px 14px;"><?= htmlspecialchars($grade['id']) ?></td>
                                            <td style="padding:12px 14px;"><?= htmlspecialchars($grade['name']) ?></td>
                                            <td style="padding:12px 14px;"><?= $gradeCounts[(int) $grade['id']] ?? 0 ?></td>
                                            <td style="padding:12px 14px;">
                                                <a href="grade.php?edit_id=<?= htmlspecialchars($grade['id']) ?>" style="margin-right:8px;padding:7px 12px;border-radius:10px;border:1px solid #4b7bec;color:#4b7bec;text-decoration:none;font-size:13px;">Edit</a>
                                                <!-- Delete is a POST form (not a plain link) so it can't be triggered
                                                     by accident/crawlers; the backend still blocks it if students
                                                     are assigned to this grade (see functions/grade.php). -->
                                                <form action="grade.php" method="post" style="display:inline-block;margin:0;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="grade_id" value="<?= htmlspecialchars($grade['id']) ?>">
                                                    <button type="submit" style="padding:7px 12px;border-radius:10px;border:1px solid #dc2626;background:#fff;color:#dc2626;font-size:13px;cursor:pointer;">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="note">No grades found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/app.js"></script>
</body>

</html>
