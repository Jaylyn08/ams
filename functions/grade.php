<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/grades.php';

$successMessage = '';
$errorMessage = '';
$editingGrade = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $gradeId = intval($_POST['grade_id'] ?? 0);

    if ($action === 'create' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            $errorMessage = 'Grade name is required.';
        } elseif ($action === 'create') {
            $stmt = mysqli_prepare($conn, 'INSERT INTO grade (name) VALUES (?)');
            mysqli_stmt_bind_param($stmt, 's', $name);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = 'Grade added successfully.';
            } elseif (mysqli_errno($conn) === 1062) {
                $errorMessage = 'A grade with that name already exists.';
            } else {
                $errorMessage = 'Failed to add grade.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $stmt = mysqli_prepare($conn, 'UPDATE grade SET name = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'si', $name, $gradeId);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = 'Grade updated successfully.';
            } elseif (mysqli_errno($conn) === 1062) {
                $errorMessage = 'A grade with that name already exists.';
            } else {
                $errorMessage = 'Failed to update grade.';
            }
            mysqli_stmt_close($stmt);
        }
    }

    if ($action === 'delete') {
        $stmt = mysqli_prepare($conn, 'DELETE FROM grade WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $gradeId);
        if (mysqli_stmt_execute($stmt)) {
            $successMessage = 'Grade deleted successfully.';
        } elseif (mysqli_errno($conn) === 1451) {
            $errorMessage = 'This grade still has students assigned to it. Move or remove them first.';
        } else {
            $errorMessage = 'Failed to delete grade.';
        }
        mysqli_stmt_close($stmt);
    }
}

if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $stmt = mysqli_prepare($conn, 'SELECT id, name FROM grade WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $editId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editingGrade = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

$gradeCounts = [];
$countResult = mysqli_query($conn, 'SELECT grade_id, COUNT(*) AS total FROM student GROUP BY grade_id');
if ($countResult) {
    foreach (mysqli_fetch_all($countResult, MYSQLI_ASSOC) as $row) {
        $gradeCounts[(int) $row['grade_id']] = (int) $row['total'];
    }
}

$grades = getGrades($conn);
