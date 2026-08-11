-- Run this once in phpMyAdmin (or the mysql CLI) to create the database and table.

CREATE DATABASE IF NOT EXISTS ams;
USE ams;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- student and attendance were missing from this script even though every
-- page (add.php, edit.php, report.php, student.php) depends on them.
-- includes/db.php auto-creates `attendance` once `student` exists, but
-- never creates `student` itself.
CREATE TABLE IF NOT EXISTS student (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    gender ENUM('Male','Female') NOT NULL,
    section ENUM('Gumamela','Tulip') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    status ENUM('Present','Absent') NOT NULL,
    attendance_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY student_date_unique (student_id, attendance_date),
    INDEX idx_attendance_date (attendance_date),
    FOREIGN KEY (student_id) REFERENCES student(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
