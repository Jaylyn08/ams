<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/sections.php';

$sections = getSections($conn);
$sectionNames = array_column($sections, 'name');
// Note: the student list below shows each student's grade name via a SQL
// JOIN on the `grade` table directly (see $query), not through the
// includes/grades.php helper — there's no grade-based filter/stat cards
// yet (those are still section-only).

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
    if (!in_array($section, $sectionNames, true)) {
        $_SESSION['message'] = 'Please select a section before saving attendance.';
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
        $validQuery = "SELECT s.id FROM student s JOIN section sec ON sec.id = s.section_id WHERE sec.name = '" . $safeSection . "' AND s.id IN (" . $idList . ")";
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
$allowedGenders = ['Male', 'Female'];
if (!in_array($filterSection, $sectionNames, true)) {
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
    $where[] = "sec.name='" . mysqli_real_escape_string($conn, $filterSection) . "'";
}
if ($filterGender !== '') {
    $where[] = "s.gender='" . mysqli_real_escape_string($conn, $filterGender) . "'";
}
// Joins in both section and grade names so the listing table (student.php)
// can display them without a second query per row.
$query = "SELECT s.*, sec.name AS section, g.name AS grade FROM student s JOIN section sec ON sec.id = s.section_id JOIN grade g ON g.id = s.grade_id";
if (!empty($where)) {
    $query .= ' WHERE ' . implode(' AND ', $where);
}
if ($filterGender === '') {
    $query .= " ORDER BY CASE WHEN s.gender='Male' THEN 0 ELSE 1 END, s.id ASC";
} else {
    $query .= ' ORDER BY s.id ASC';
}
$query_run = mysqli_query($conn, $query);

$attendanceQuery = $attendanceDate !== '' ? '&attendance_date=' . urlencode($attendanceDate) : '';
$sectionQuery = $filterSection !== '' ? '&section=' . urlencode($filterSection) . $attendanceQuery : $attendanceQuery;
$genderQuery = $filterGender !== '' ? '&gender=' . urlencode($filterGender) . $attendanceQuery : $attendanceQuery;

$counts = [];
foreach ($sectionNames as $sectionName) {
    $counts[$sectionName] = ['Male' => 0, 'Female' => 0];
}
$countQuery = "SELECT sec.name AS section, s.gender, COUNT(*) AS total FROM student s JOIN section sec ON sec.id = s.section_id WHERE s.gender IN ('Male', 'Female') GROUP BY sec.name, s.gender";
$countResult = mysqli_query($conn, $countQuery);
if ($countResult) {
    while ($countRow = mysqli_fetch_assoc($countResult)) {
        $countSectionName = $countRow['section'];
        $gender = $countRow['gender'];
        if (isset($counts[$countSectionName][$gender])) {
            $counts[$countSectionName][$gender] = $countRow['total'];
        }
    }
    mysqli_free_result($countResult);
}
$sectionTotals = [];
foreach ($counts as $sectionName => $genderCounts) {
    $sectionTotals[$sectionName] = $genderCounts['Male'] + $genderCounts['Female'];
}
$overallTotal = array_sum($sectionTotals);

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
