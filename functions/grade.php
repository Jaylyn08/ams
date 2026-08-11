<?php
// Backend logic for grade.php (the "Manage Grades" admin page).
// Handles creating, editing, and deleting rows in the `grade` lookup table,
// then hands the page everything it needs to render (messages, the grade
// being edited, per-grade student counts, and the full grade list).

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/grades.php';

$successMessage = '';   // shown in a green banner when an action succeeds
$errorMessage = '';     // shown in a red banner when an action fails
$editingGrade = null;   // the grade row currently loaded into the edit form, if any

// --- Handle form submissions (add / rename / delete a grade) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $gradeId = intval($_POST['grade_id'] ?? 0);

    // "create" = new grade from the Add form, "update" = rename from the Edit form.
    // Both share the same name validation, so they're handled together.
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
                // 1062 = MySQL "duplicate entry" error, triggered by the
                // UNIQUE constraint on grade.name.
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
            // 1451 = MySQL "foreign key constraint fails" error, triggered
            // when student.grade_id still points at this grade row.
            $errorMessage = 'This grade still has students assigned to it. Move or remove them first.';
        } else {
            $errorMessage = 'Failed to delete grade.';
        }
        mysqli_stmt_close($stmt);
    }
}

// --- Load the grade to edit, if the page was opened via the "Edit" link ---
// (grade.php?edit_id=3) so the form above can be pre-filled with its name.
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $stmt = mysqli_prepare($conn, 'SELECT id, name FROM grade WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $editId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editingGrade = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// --- Count how many students are in each grade ---
// Used in the table's "Students" column so the admin can see at a glance
// which grades are safe to delete (0 students = no FK block on delete).
$gradeCounts = [];
$countResult = mysqli_query($conn, 'SELECT grade_id, COUNT(*) AS total FROM student GROUP BY grade_id');
if ($countResult) {
    foreach (mysqli_fetch_all($countResult, MYSQLI_ASSOC) as $row) {
        $gradeCounts[(int) $row['grade_id']] = (int) $row['total'];
    }
}

// --- Full list of grades for the table ---
$grades = getGrades($conn);
