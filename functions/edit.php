<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/sections.php';
require_once __DIR__ . '/../includes/grades.php';

$sections = getSections($conn);
$grades = getGrades($conn);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $full_name = trim($_POST['full_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $sectionId = isset($_POST['section_id']) ? intval($_POST['section_id']) : 0;
    $gradeId = isset($_POST['grade_id']) ? intval($_POST['grade_id']) : 0;

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE student SET full_name = ?, gender = ?, section_id = ?, grade_id = ? WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssiii', $full_name, $gender, $sectionId, $gradeId, $id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = 'Student updated successfully.';
                mysqli_stmt_close($stmt);
                header('Location: student.php');
                exit;
            } else {
                $_SESSION['message'] = 'Unable to update student.';
                mysqli_stmt_close($stmt);
            }
        }
    }
}

$student = null;
if ($id > 0) {
    $q = mysqli_prepare($conn, "SELECT id, full_name, gender, section_id, grade_id FROM student WHERE id = ? LIMIT 1");
    if ($q) {
        mysqli_stmt_bind_param($q, 'i', $id);
        mysqli_stmt_execute($q);
        $res = mysqli_stmt_get_result($q);
        if ($res && mysqli_num_rows($res) > 0) {
            $student = mysqli_fetch_assoc($res);
        }
        mysqli_stmt_close($q);
    }
}
