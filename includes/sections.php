<?php
// Shared helper for reading the `section` table — add a row there and it
// shows up in every dropdown/filter automatically, no code changes needed.
// Each section belongs to exactly one grade (section.grade_id), so callers
// that need to filter sections by grade (e.g. the Add/Edit Student forms)
// can use grade_id straight off the rows returned here.

function getSections(mysqli $conn): array
{
    $result = mysqli_query($conn, 'SELECT id, name, grade_id FROM section ORDER BY name');
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function getSectionNames(mysqli $conn): array
{
    return array_column(getSections($conn), 'name');
}
