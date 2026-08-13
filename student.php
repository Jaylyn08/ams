<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/functions/student.php';
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/style.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>Student Details</title>
</head>

<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

   

    <div class="app-content">
        <div id="home" class="page-container container mt-4">

            <?php include('message.php'); ?>

            <div id="details" class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Student Details</h4>
                            <div>
                                <a href="add.php" class="btn btn-primary me-2">Add Student</a>

                            </div>

                        </div>



                        <form method="get" class=" mb-2 row gx-3 gy-3 align-items-right">

                            <div id="student" class="card-body">
                                <div class="col-md-2">
                                    <label for="section" class="form-label text-uppercase"><strong>Select Section:</strong></label>
                                    <select id="section" name="section" class="form-select  ">
                                        <option value="" <?= $filterSection === '' ? ' selected' : '' ?>>All</option>
                                        <?php foreach ($sections as $sec): ?>
                                            <option value="<?= htmlspecialchars($sec['name']) ?>" <?= $filterSection === $sec['name'] ? ' selected' : '' ?>><?= htmlspecialchars($sec['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-10">
                                    <div class="d-flex align-items-center gap-2">
                                        <div>
                                            <select name="page" id="page" class="form-select form-select-sm" style="width:70px;">
                                                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                                    <option value="<?= $p ?>" <?= $p === $currentPage ? 'selected' : '' ?>><?= $p ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>

                                        <div class="btn-group">
                                            <button type="submit" name="nav" value="prev" class="btn btn-sm btn-outline-secondary" <?= $currentPage <= 1 ? 'disabled' : '' ?>>&lt;</button>
                                            <button type="submit" name="nav" value="next" class="btn btn-sm btn-outline-secondary" <?= $currentPage >= $totalPages ? 'disabled' : '' ?>>&gt;</button>
                                        </div>
                                        <div class="ms-auto d-flex align-items-left gap-2">
                                            <label class="mb-0">Number of rows:</label>
                                            <select id="page_size" name="page_size" class="form-select form-select-sm" style="width:90px;">
                                                <option value="10" <?= $pageSize === 10 ? ' selected' : '' ?>>10</option>
                                                <option value="15" <?= $pageSize === 15 ? ' selected' : '' ?>>15</option>
                                                <option value="20" <?= $pageSize === 20 ? ' selected' : '' ?>>20</option>
                                                <option value="50" <?= $pageSize === 50 ? ' selected' : '' ?>>50</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                                        </div>
                                    </div>
                                </div>
                        </form>
                    </div>

                    <form id="attendanceForm" method="POST" class="mb-3">
                        <input type="hidden" name="attendance_date" value="<?= htmlspecialchars($attendanceDate) ?>">
                        <input type="hidden" name="section" value="<?= htmlspecialchars($filterSection) ?>">
                        <input type="hidden" name="gender" value="<?= htmlspecialchars($filterGender) ?>">

                        <table class="table table-bordered table-striped centered-table">
                            <thead>
                                <tr style="background:rgba(75,124,236,.08);text-align:left;">
                                    <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Full Name</th>
                                    <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Gender</th>
                                    <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Section</th>
                                    <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Grade</th>
                                    <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Attendance</th>
                                    <th style="padding:12px 14px;border-bottom:1px solid #dbe7f0;">Manage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($query_run && mysqli_num_rows($query_run) > 0) {
                                    foreach ($query_run as $user) {
                                ?>
                                        <tr>
                                            <td><?= $user['full_name']; ?></td>
                                            <td><?= $user['gender']; ?></td>
                                            <td><?= $user['section']; ?></td>
                                            <!-- $user['grade'] comes from the JOIN on the `grade` table in
                                             functions/student.php's $query, not a separate lookup -->
                                            <td><?= $user['grade']; ?></td>
                                            <td>
                                                <div class="d-flex gap-2 align-items-center flex-wrap">
                                                    <div class="form-check mb-0 ">
                                                        <input class="form-check-input" type="checkbox" name="present[<?= $user['id']; ?>]" id="present_<?= $user['id']; ?>" value="1" <?= !$clearAttendanceSelection && isset($attendanceMap[$user['id']]) && $attendanceMap[$user['id']] === 'Present' ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="present_<?= $user['id']; ?>">Present</label>
                                                    </div>

                                                </div>
                                                <input type="hidden" name="student_ids[]" value="<?= $user['id']; ?>">
                                            </td>
                                            <td class="text-left">
                                                <div class="btn-group" role="group" aria-label="Actions">
                                                    <a href="edit.php?id=<?= $user['id']; ?>" style="margin-right:8px;padding:7px 12px;border-radius:10px;border:1px solid #4b7bec;color:#4b7bec;text-decoration:none;font-size:13px;">Edit</a>

                                                    <a href="student.php?delete_id=<?= $user['id']; ?>" style="padding:7px 12px;border-radius:10px;border:1px solid #dc2626;background:#fff;color:#dc2626;font-size:13px;cursor:pointer;" onclick="return confirm('Delete this student? This will also remove attendance records.');">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    echo "<h5> No Record Found </h5>";
                                }
                                ?>

                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" id="resetAttendance" class="btn btn-secondary">Reset All Selection</button>
                            <button type="submit" class="btn btn-primary">Save Attendance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- closes: .app-content -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/app.js"></script>
    <script>
        // auto-submit when page select or page_size changes
        (function() {
            var pageSelect = document.getElementById('page');
            var pageSize = document.getElementById('page_size');
            var form = document.querySelector('form[method="get"]');
            if (pageSelect) {
                pageSelect.addEventListener('change', function() {
                    form.submit();
                });
            }
            if (pageSize) {
                pageSize.addEventListener('change', function() {
                    // reset to first page when page size changes
                    var pageInput = document.querySelector('select[name="page"]');
                    if (pageInput) {
                        pageInput.value = 1;
                    }
                    form.submit();
                });
            }
        })();
    </script>

</body>

</html>