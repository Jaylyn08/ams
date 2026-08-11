<?php
// Step 2: Open one mysqli connection that every page can reuse.
require_once __DIR__ . '/../config.php';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$checkUsersTable = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if ($checkUsersTable && mysqli_num_rows($checkUsersTable) > 0) {
    $checkRoleColumn = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
    if ($checkRoleColumn && mysqli_num_rows($checkRoleColumn) === 0) {
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN role ENUM('admin','user') NOT NULL DEFAULT 'user'");
    }
}

$checkStudentTable = mysqli_query($conn, "SHOW TABLES LIKE 'student'");
if ($checkStudentTable && mysqli_num_rows($checkStudentTable) > 0) {
    $createAttendanceTable = "CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        status ENUM('Present','Absent') NOT NULL,
        attendance_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY student_date_unique (student_id, attendance_date),
        INDEX idx_attendance_date (attendance_date),
        FOREIGN KEY (student_id) REFERENCES student(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB";
    mysqli_query($conn, $createAttendanceTable);
}
