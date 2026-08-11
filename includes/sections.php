<?php
// Shared helper for reading the `section` table — add a row there and it
// shows up in every dropdown/filter automatically, no code changes needed.

function getSections(mysqli $conn): array
{
    $result = mysqli_query($conn, 'SELECT id, name FROM section ORDER BY name');
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function getSectionNames(mysqli $conn): array
{
    return array_column(getSections($conn), 'name');
}
