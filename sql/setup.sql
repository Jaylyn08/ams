-- Run this in phpMyAdmin (or the mysql CLI) to bring your `ams` database up
-- to date. Safe to run at any time, regardless of your current state:
--   - Brand new / no database yet -> creates everything from scratch.
--   - Already has users/student/attendance from an older version of this
--     repo (student.section as a hardcoded ENUM) -> migrates section into
--     its own table and repoints student.section_id at it automatically.
--   - Already fully up to date -> every step below is a no-op.
-- Re-running this file is always safe.

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

-- Lookup table for the sections a student can belong to. Add a row here and
-- it shows up in every dropdown/filter in the app automatically.
CREATE TABLE IF NOT EXISTS section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

INSERT IGNORE INTO section (name) VALUES ('Gumamela'), ('Tulip');

-- Creates `student` from scratch (with section_id already in place) if it
-- doesn't exist yet. If it already exists — with or without section_id —
-- this is a no-op; the migration block below handles the old-schema case.
CREATE TABLE IF NOT EXISTS student (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    gender ENUM('Male','Female') NOT NULL,
    section_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (section_id) REFERENCES section(id)
);

-- --- Migration: student.section (ENUM) -> student.section_id (FK) ---
-- Only runs its steps when `student` still has the old `section` column and
-- doesn't have `section_id` yet. On a fresh install or an already-migrated
-- database, every IF below resolves to a harmless 'SELECT 1' no-op.
SET @needs_migration := (
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE table_schema = DATABASE() AND table_name = 'student' AND column_name = 'section') > 0
    AND
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE table_schema = DATABASE() AND table_name = 'student' AND column_name = 'section_id') = 0
);

SET @sql := IF(@needs_migration,
    'ALTER TABLE student ADD COLUMN section_id INT NULL AFTER gender',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@needs_migration,
    'UPDATE student s JOIN section sec ON sec.name = s.section SET s.section_id = sec.id',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@needs_migration,
    'ALTER TABLE student MODIFY section_id INT NOT NULL',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@needs_migration,
    'ALTER TABLE student ADD CONSTRAINT fk_student_section FOREIGN KEY (section_id) REFERENCES section(id)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@needs_migration,
    'ALTER TABLE student DROP COLUMN section',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
-- --- end migration ---

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
