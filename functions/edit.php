<?php
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $full_name = trim($_POST['full_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $section = $_POST['section'] ?? '';

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE student SET full_name = ?, gender = ?, section = ? WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sssi', $full_name, $gender, $section, $id);
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
    $q = mysqli_prepare($conn, "SELECT id, full_name, gender, section FROM student WHERE id = ? LIMIT 1");
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
