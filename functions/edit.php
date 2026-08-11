<?php
// Backend for edit.php. Two jobs depending on how it's called:
//   1. POST (form was submitted)  -> validate-lite update of the student row,
//      then redirect back to student.php with a flash message.
//   2. GET ?id=N (or after a failed POST) -> load that student into
//      $student so edit.php can render the pre-filled form. $student stays
//      null if no matching row exists, and edit.php shows "Student not
//      found" in that case instead of erroring.

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/sections.php';
require_once __DIR__ . '/../includes/grades.php';

$sections = getSections($conn);
$grades = getGrades($conn);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On POST, the id comes from the hidden form field instead of the
    // querystring (the form action is just "edit.php", no ?id=).
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
                // Update failed — fall through to the GET-style lookup below
                // so the form re-renders with the student's (unsaved) data.
                $_SESSION['message'] = 'Unable to update student.';
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Load the student being edited (used to pre-fill the form fields).
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
