<?php
require_once __DIR__ . '/includes/auth.php';
if (!$isAdmin) {
    header('HTTP/1.1 403 Forbidden');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Forbidden</title><link href="assets/style.css" rel="stylesheet"></head><body><div class="page-center" style="min-height:auto;padding:30px;"><div class="wrap"><h2>Access denied</h2><p class="note">This page is for administrators only.</p><p class="note"><a href="index.php">Return to dashboard</a></p></div></div></body></html>';
    exit;
}
require_once __DIR__ . '/functions/section.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="assets/style.css" rel="stylesheet">
    <title>Manage Sections</title>
</head>

<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="page-center" style="min-height:auto;padding:30px;">
            <div class="wrap" style="max-width:700px;width:100%;padding:32px;">
                <div class="auth-brand">
                    <span>Administrator</span>
                    <h2>Manage Sections</h2>
                    <p class="note">Add, rename, or remove the sections students can be assigned to. Each section belongs to one grade.</p>
                </div>
                <div style="margin-top:24px;">
                    <?php if ($successMessage !== ''): ?>
                        <div class="success" style="margin-top:16px;"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($errorMessage !== ''): ?>
                        <div class="error" style="margin-top:16px;"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>

                    <div style="margin-top:20px;padding:20px;border:1px solid rgba(75,124,236,.18);border-radius:18px;background:rgba(255,255,255,.95);">
                        <h4><?= $editingSection ? 'Edit section #' . htmlspecialchars($editingSection['id']) : 'Add a new section' ?></h4>
                        <form action="section.php" method="post" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-top:12px;">
                            <input type="hidden" name="action" value="<?= $editingSection ? 'update' : 'create' ?>">
                            <?php if ($editingSection): ?>
                                <input type="hidden" name="section_id" value="<?= htmlspecialchars($editingSection['id']) ?>">
                            <?php endif; ?>
                            <label style="flex:1;min-width:200px;">
                                Section name<br>
                                <input type="text" name="name" value="<?= htmlspecialchars($editingSection['name'] ?? '') ?>" required style="width:100%;padding:10px;border:1px solid #dbe7f0;border-radius:12px;">
                            </label>
                            <label style="min-width:160px;">
                                Grade<br>
                                <select name="grade_id" required style="width:100%;padding:10px;border:1px solid #dbe7f0;border-radius:12px;">
                                    <option value="" disabled <?= !$editingSection ? 'selected' : '' ?>>Select grade</option>
                                    <?php foreach ($grades as $gr): ?>
                                        <option value="<?= (int) $gr['id'] ?>" <?= $editingSection && (int) $editingSection['grade_id'] === (int) $gr['id'] ? 'selected' : '' ?>><?= htmlspecialchars($gr['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <button type="submit" class="btn" style="padding:12px 16px;"><?= $editingSection ? 'Save changes' : 'Add section' ?></button>
                            <?php if ($editingSection): ?>
                                <a href="section.php" style="align-self:center;color:#4b7bec;">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <?php if (!empty($sections)): ?>
                        <div style="overflow-x:auto;margin-top:16px;">
                            <table style="width:100%;border-collapse:collapse;font-size:14px;color:#334e68;">
                                <thead>
                                    <tr style="background:rgba(75,124,236,.08);text-align:left;">
                                        <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">ID</th>
                                        <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Name</th>
                                        <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Grade</th>
                                        <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Students</th>
                                        <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sections as $section): ?>
                                        <tr style="border-top:1px solid #edf2f7;">
                                            <td style="padding:12px 14px;"><?= htmlspecialchars($section['id']) ?></td>
                                            <td style="padding:12px 14px;"><?= htmlspecialchars($section['name']) ?></td>
                                            <td style="padding:12px 14px;"><?= htmlspecialchars($gradeNames[(int) $section['grade_id']] ?? '—') ?></td>
                                            <td style="padding:12px 14px;"><?= $sectionCounts[(int) $section['id']] ?? 0 ?></td>
                                            <td style="padding:12px 14px;">
                                                <a href="section.php?edit_id=<?= htmlspecialchars($section['id']) ?>" style="margin-right:8px;padding:7px 12px;border-radius:10px;border:1px solid #4b7bec;color:#4b7bec;text-decoration:none;font-size:13px;">Edit</a>
                                                <form action="section.php" method="post" style="display:inline-block;margin:0;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="section_id" value="<?= htmlspecialchars($section['id']) ?>">
                                                    <button type="submit" style="padding:7px 12px;border-radius:10px;border:1px solid #dc2626;background:#fff;color:#dc2626;font-size:13px;cursor:pointer;">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="note">No sections found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/app.js"></script>
</body>

</html>
