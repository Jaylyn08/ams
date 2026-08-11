<?php
require_once __DIR__ . '/../includes/db.php';

$totalStudentsResult = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM student');
$totalStudentsRow = $totalStudentsResult ? mysqli_fetch_assoc($totalStudentsResult) : null;
$totalStudents = $totalStudentsRow ? (int) $totalStudentsRow['total'] : 0;
