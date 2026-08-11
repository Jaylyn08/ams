<?php
// Backend for section.php (the "Manage Sections" admin page). Every section
// belongs to exactly one grade (section.grade_id), so create/update here
// always take a grade_id alongside the name — that's what lets the Add/Edit
// Student forms filter their Section dropdown down to whichever grade was
// picked.

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/sections.php';
require_once __DIR__ . '/../includes/grades.php';

$successMessage = '';
$errorMessage = '';
$editingSection = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $sectionId = intval($_POST['section_id'] ?? 0);

    if ($action === 'create' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $gradeId = intval($_POST['grade_id'] ?? 0);
        $validGradeIds = array_map('intval', array_column(getGrades($conn), 'id'));

        if ($name === '') {
            $errorMessage = 'Section name is required.';
        } elseif (!in_array($gradeId, $validGradeIds, true)) {
            $errorMessage = 'Please select a valid grade.';
        } elseif ($action === 'create') {
            $stmt = mysqli_prepare($conn, 'INSERT INTO section (name, grade_id) VALUES (?, ?)');
            mysqli_stmt_bind_param($stmt, 'si', $name, $gradeId);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = 'Section added successfully.';
            } elseif (mysqli_errno($conn) === 1062) {
                $errorMessage = 'A section with that name already exists.';
            } else {
                $errorMessage = 'Failed to add section.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $stmt = mysqli_prepare($conn, 'UPDATE section SET name = ?, grade_id = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'sii', $name, $gradeId, $sectionId);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = 'Section updated successfully.';
            } elseif (mysqli_errno($conn) === 1062) {
                $errorMessage = 'A section with that name already exists.';
            } else {
                $errorMessage = 'Failed to update section.';
            }
            mysqli_stmt_close($stmt);
        }
    }

    if ($action === 'delete') {
        $stmt = mysqli_prepare($conn, 'DELETE FROM section WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $sectionId);
        if (mysqli_stmt_execute($stmt)) {
            $successMessage = 'Section deleted successfully.';
        } elseif (mysqli_errno($conn) === 1451) {
            $errorMessage = 'This section still has students assigned to it. Move or remove them first.';
        } else {
            $errorMessage = 'Failed to delete section.';
        }
        mysqli_stmt_close($stmt);
    }
}

if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $stmt = mysqli_prepare($conn, 'SELECT id, name, grade_id FROM section WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $editId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editingSection = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

$sectionCounts = [];
$countResult = mysqli_query($conn, 'SELECT section_id, COUNT(*) AS total FROM student GROUP BY section_id');
if ($countResult) {
    foreach (mysqli_fetch_all($countResult, MYSQLI_ASSOC) as $row) {
        $sectionCounts[(int) $row['section_id']] = (int) $row['total'];
    }
}

// grade name per section, for the "Grade" column in the sections table
$gradeNames = array_column(getGrades($conn), 'name', 'id');

$sections = getSections($conn);
$grades = getGrades($conn);
