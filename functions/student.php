<?php
require_once __DIR__ . '/../includes/db.php';

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
