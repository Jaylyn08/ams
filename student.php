<?php
require_once __DIR__ . '/includes/db.php';
session_start();
// Handle delete via GET (simple confirmation link)
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    if ($deleteId > 0) {
        // Delete attendance records for the student first
        $delAttend = mysqli_prepare($conn, "DELETE FROM attendance WHERE student_id = ?");
        if ($delAttend) {
            mysqli_stmt_bind_param($delAttend, 'i', $deleteId);
            mysqli_stmt_execute($delAttend);
            mysqli_stmt_close($delAttend);
        }
        // Then delete the student
        $delStmt = mysqli_prepare($conn, "DELETE FROM student WHERE id = ?");
        if ($delStmt) {
            mysqli_stmt_bind_param($delStmt, 'i', $deleteId);
            if (mysqli_stmt_execute($delStmt)) {
                $_SESSION['message'] = 'Student deleted successfully.';
            } else {
                $_SESSION['message'] = 'Unable to delete student.';
            }
            mysqli_stmt_close($delStmt);
        }
    }
    // Redirect back to the listing without the query param
    header('Location: student.php');
    exit;
}

$attendanceDate = date('Y-m-d');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_ids']) && is_array($_POST['student_ids'])) {
    $attendanceDate = $_POST['attendance_date'] ?? date('Y-m-d');
    $attendanceDate = mysqli_real_escape_string($conn, $attendanceDate);

    $presentIds = [];
    if (isset($_POST['present']) && is_array($_POST['present'])) {
        foreach (array_keys($_POST['present']) as $studentId) {
            $presentIds[] = intval($studentId);
        }
    }

    $absentIds = [];
    if (isset($_POST['absent']) && is_array($_POST['absent'])) {
        foreach (array_keys($_POST['absent']) as $studentId) {
            $absentIds[] = intval($studentId);
        }
    }

    $section = $_POST['section'] ?? '';
    $allowedSections = ['Gumamela', 'Tulip'];
    if (!in_array($section, $allowedSections, true)) {
        $_SESSION['message'] = 'Please select Gumamela or Tulip before saving attendance.';
        $sectionParam = isset($_POST['section']) ? '&section=' . urlencode($_POST['section']) : '';
        $genderParam = isset($_POST['gender']) ? '&gender=' . urlencode($_POST['gender']) : '';
        header('Location: student.php?' . ltrim($sectionParam . $genderParam, '&'));
        exit;
    }

    $studentIds = array_map('intval', $_POST['student_ids']);
    $safeSection = mysqli_real_escape_string($conn, $section);
    $validIds = [];
    if (!empty($studentIds)) {
        $idList = implode(',', $studentIds);
        $validQuery = "SELECT id FROM student WHERE section = '" . $safeSection . "' AND id IN (" . $idList . ")";
        $validResult = mysqli_query($conn, $validQuery);
        if ($validResult) {
            while ($row = mysqli_fetch_assoc($validResult)) {
                $validIds[] = intval($row['id']);
            }
            mysqli_free_result($validResult);
        }
    }

    $sql = "INSERT INTO attendance (student_id, status, attendance_date) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status), created_at = CURRENT_TIMESTAMP";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        if (!empty($validIds)) {
            foreach ($validIds as $studentId) {
                if (in_array($studentId, $presentIds, true)) {
                    $status = 'Present';
                } elseif (in_array($studentId, $absentIds, true)) {
                    $status = 'Absent';
                } else {
                    $status = 'Absent';
                }
                mysqli_stmt_bind_param($stmt, 'iss', $studentId, $status, $attendanceDate);
                mysqli_stmt_execute($stmt);
            }
            $_SESSION['message'] = 'Attendance saved for ' . count($validIds) . ' ' . $section . ' students on ' . $attendanceDate . '.';
            $_SESSION['clear_attendance_selection'] = true;
        } else {
            $_SESSION['message'] = 'No students in ' . htmlspecialchars($section) . ' were selected for attendance.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['message'] = 'Unable to save attendance records.';
    }
    $sectionParam = isset($_POST['section']) ? '&section=' . urlencode($_POST['section']) : '';
    $genderParam = isset($_POST['gender']) ? '&gender=' . urlencode($_POST['gender']) : '';
    $attendanceParam = '&attendance_date=' . urlencode($attendanceDate);
    header('Location: student.php?' . ltrim($sectionParam . $genderParam . $attendanceParam, '&'));
    exit;
}

$filterSection = $_GET['section'] ?? '';
$filterGender = $_GET['gender'] ?? '';
$allowedSections = ['Gumamela', 'Tulip'];
$allowedGenders = ['Male', 'Female'];
if (!in_array($filterSection, $allowedSections, true)) {
    $filterSection = '';
}
if (!in_array($filterGender, $allowedGenders, true)) {
    $filterGender = '';
}
//date
$filterAttendanceDate = $_GET['attendance_date'] ?? '';
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterAttendanceDate)) {
    $attendanceDate = $filterAttendanceDate;
}

$where = [];
if ($filterSection !== '') {
    $where[] = "section='" . mysqli_real_escape_string($conn, $filterSection) . "'";
}
if ($filterGender !== '') {
    $where[] = "gender='" . mysqli_real_escape_string($conn, $filterGender) . "'";
}
$query = "SELECT * FROM student";
if (!empty($where)) {
    $query .= ' WHERE ' . implode(' AND ', $where);
}
if ($filterGender === '') {
    $query .= " ORDER BY CASE WHEN gender='Male' THEN 0 ELSE 1 END, id ASC";
} else {
    $query .= ' ORDER BY id ASC';
}
$query_run = mysqli_query($conn, $query);

$attendanceQuery = $attendanceDate !== '' ? '&attendance_date=' . urlencode($attendanceDate) : '';
$sectionQuery = $filterSection !== '' ? '&section=' . urlencode($filterSection) . $attendanceQuery : $attendanceQuery;
$genderQuery = $filterGender !== '' ? '&gender=' . urlencode($filterGender) . $attendanceQuery : $attendanceQuery;

$counts = [
    'Gumamela' => ['Male' => 0, 'Female' => 0],
    'Tulip' => ['Male' => 0, 'Female' => 0],
];
$countQuery = "SELECT section, gender, COUNT(*) AS total FROM student WHERE section IN ('Gumamela', 'Tulip') AND gender IN ('Male', 'Female') GROUP BY section, gender";
$countResult = mysqli_query($conn, $countQuery);
if ($countResult) {
    while ($countRow = mysqli_fetch_assoc($countResult)) {
        $section = $countRow['section'];
        $gender = $countRow['gender'];
        if (isset($counts[$section][$gender])) {
            $counts[$section][$gender] = $countRow['total'];
        }
    }
    mysqli_free_result($countResult);
}
$sectionTotals = [
    'Gumamela' => $counts['Gumamela']['Male'] + $counts['Gumamela']['Female'],
    'Tulip' => $counts['Tulip']['Male'] + $counts['Tulip']['Female'],
];
$overallTotal = $sectionTotals['Gumamela'] + $sectionTotals['Tulip'];

$attendanceMap = [];
$attendanceQuery = "SELECT student_id, status FROM attendance WHERE attendance_date = '" . mysqli_real_escape_string($conn, $attendanceDate) . "'";
$attendanceResult = mysqli_query($conn, $attendanceQuery);
if ($attendanceResult) {
    while ($row = mysqli_fetch_assoc($attendanceResult)) {
        $attendanceMap[intval($row['student_id'])] = $row['status'];
    }
    mysqli_free_result($attendanceResult);
}

$clearAttendanceSelection = false;
if (isset($_SESSION['clear_attendance_selection']) && $_SESSION['clear_attendance_selection'] === true) {
    $clearAttendanceSelection = true;
    unset($_SESSION['clear_attendance_selection']);
}

?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>Student Details</title>
</head>

<body>

    <div class="topnav">
        <a href="index.php">Home</a>
        <a class="active" href="student.php">Student</a>
        <a href="report.php">Report</a>
    </div>

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
                                    <option value="Gumamela" <?= $filterSection === 'Gumamela' ? ' selected' : '' ?>>Gumamela</option>
                                    <option value="Tulip" <?= $filterSection === 'Tulip' ? ' selected' : '' ?>>Tulip</option>
                                </select>
                            </div>

                            <div class="col-md-1 mb-3">
                                <button type="submit" class="btn btn-primary btn-apply">Apply</button>
                            </div>
                    </form>

                    <div class="row mb-2">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="p-3 rounded-3 bg-light border">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase text-secondary">Gumamela Total</h6>
                                        <p class="display-6 mb-0"><?= $sectionTotals['Gumamela'] ?></p>
                                    </div>
                                    <span class="badge bg-primary">Gumamela</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="p-3 rounded-3 bg-light border">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase text-secondary">Tulip Total</h6>
                                        <p class="display-6 mb-0"><?= $sectionTotals['Tulip'] ?></p>
                                    </div>
                                    <span class="badge bg-success">Tulip</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded-3 bg-light border">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="text-uppercase text-secondary">Date & Time</h6>
                                        <p class="mb-0" id="dateTimeDisplay">Philippine Standard Time: <?= date('l, F j, Y, g:i:s A') ?></p>
                                    </div>
                                    <span class="badge bg-info">Current</span>
                                </div>
                            </div>
                        </div>
                    </div>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('input[type="checkbox"][id^="present_"]').forEach(function(presentCheckbox) {
            presentCheckbox.addEventListener('change', function() {
                var studentId = this.id.replace('present_', '');
                var absentCheckbox = document.getElementById('absent_' + studentId);
                if (absentCheckbox && this.checked) {
                    absentCheckbox.checked = false;
                }
            });
        });

        document.querySelectorAll('input[type="checkbox"][id^="absent_"]').forEach(function(absentCheckbox) {
            absentCheckbox.addEventListener('change', function() {
                var studentId = this.id.replace('absent_', '');
                var presentCheckbox = document.getElementById('present_' + studentId);
                if (presentCheckbox && this.checked) {
                    presentCheckbox.checked = false;
                }
            });
        });

        document.getElementById('resetAttendance').addEventListener('click', function() {
            document.querySelectorAll('input[type="checkbox"][id^="present_"]').forEach(function(checkbox) {
                checkbox.checked = false;
            });
            document.querySelectorAll('input[type="checkbox"][id^="absent_"]').forEach(function(checkbox) {
                checkbox.checked = false;
            });
        });

        function updateClock() {
            var display = document.getElementById('dateTimeDisplay');
            if (!display) return;
            var now = new Date();
            var weekday = now.toLocaleDateString('en-US', { weekday: 'long' });
            var month = now.toLocaleDateString('en-US', { month: 'long' });
            var day = now.getDate();
            var year = now.getFullYear();
            var hours = now.getHours();
            var minutes = String(now.getMinutes()).padStart(2, '0');
            var seconds = String(now.getSeconds()).padStart(2, '0');
            var period = hours >= 12 ? 'PM' : 'AM';
            var displayHours = hours % 12 || 12;
            display.textContent = 'Philippine Standard Time: ' + weekday + ', ' + month + ' ' + day + ', ' + year + ', ' + displayHours + ':' + minutes + ':' + seconds + ' ' + period;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    
</body>

</html>
<?php
$conn->close();
?>