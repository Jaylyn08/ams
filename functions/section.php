<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/sections.php';

$successMessage = '';
$errorMessage = '';
$editingSection = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $sectionId = intval($_POST['section_id'] ?? 0);

    if ($action === 'create' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            $errorMessage = 'Section name is required.';
        } elseif ($action === 'create') {
            $stmt = mysqli_prepare($conn, 'INSERT INTO section (name) VALUES (?)');
            mysqli_stmt_bind_param($stmt, 's', $name);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = 'Section added successfully.';
            } elseif (mysqli_errno($conn) === 1062) {
                $errorMessage = 'A section with that name already exists.';
            } else {
                $errorMessage = 'Failed to add section.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $stmt = mysqli_prepare($conn, 'UPDATE section SET name = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'si', $name, $sectionId);
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
    $stmt = mysqli_prepare($conn, 'SELECT id, name FROM section WHERE id = ?');
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

$sections = getSections($conn);
