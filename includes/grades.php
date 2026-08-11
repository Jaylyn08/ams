<?php
// Shared helper for reading the `grade` table — add a row there and it
// shows up in every dropdown/filter automatically, no code changes needed.

function getGrades(mysqli $conn): array
{
    $result = mysqli_query($conn, 'SELECT id, name FROM grade ORDER BY name');
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function getGradeNames(mysqli $conn): array
{
    return array_column(getGrades($conn), 'name');
}
