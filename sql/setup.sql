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

-- Lookup table for the grade levels a student can belong to. Add a row here
-- and it shows up in every dropdown/filter in the app automatically. Created
-- before `section` because section.grade_id references it.
CREATE TABLE IF NOT EXISTS grade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

INSERT IGNORE INTO grade (name) VALUES ('Grade 1'), ('Grade 2');

-- Lookup table for the sections a student can belong to. Add a row here and
-- it shows up in every dropdown/filter in the app automatically. Each
-- section belongs to exactly one grade, so picking a grade elsewhere in the
-- app (e.g. the Add Student form) narrows the section choices to just that
-- grade's sections.
CREATE TABLE IF NOT EXISTS section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    grade_id INT NOT NULL,
    FOREIGN KEY (grade_id) REFERENCES grade(id)
);

-- --- Migration: add section.grade_id to existing installs ---
-- Must run before the INSERT below: on an existing database the CREATE
-- TABLE above is a no-op (section already exists), so grade_id might not
-- exist on it yet. On a fresh install `section` already has grade_id from
-- the CREATE above, so every IF here resolves to a harmless 'SELECT 1'
-- no-op. Existing sections are backfilled to the lowest-id grade (same
-- convention used for students below) — rename them via the Sections admin
-- page afterwards if needed.
SET @needs_section_grade_migration := (
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE table_schema = DATABASE() AND table_name = 'section' AND column_name = 'grade_id') = 0
);

SET @sql := IF(@needs_section_grade_migration,
    'ALTER TABLE section ADD COLUMN grade_id INT NULL AFTER name',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@needs_section_grade_migration,
    'UPDATE section SET grade_id = (SELECT MIN(id) FROM grade) WHERE grade_id IS NULL',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@needs_section_grade_migration,
    'ALTER TABLE section MODIFY grade_id INT NOT NULL',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@needs_section_grade_migration,
    'ALTER TABLE section ADD CONSTRAINT fk_section_grade FOREIGN KEY (grade_id) REFERENCES grade(id)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
-- --- end migration ---

INSERT IGNORE INTO section (name, grade_id)
SELECT 'Gumamela', id FROM grade WHERE name = 'Grade 1'
UNION ALL
SELECT 'Tulip', id FROM grade WHERE name = 'Grade 1';

-- Creates `student` from scratch (with section_id/grade_id already in
-- place) if it doesn't exist yet. If it already exists — with or without
-- those columns — this is a no-op; the migration blocks below handle the
-- old-schema cases.
CREATE TABLE IF NOT EXISTS student (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    gender ENUM('Male','Female') NOT NULL,
    section_id INT NOT NULL,
    grade_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (section_id) REFERENCES section(id),
    FOREIGN KEY (grade_id) REFERENCES grade(id)
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

-- --- Migration: add student.grade_id to existing installs ---
-- On a fresh install `student` is already created with grade_id above, so
-- this resolves to harmless 'SELECT 1' no-ops there.
SET @needs_grade_migration := (
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE table_schema = DATABASE() AND table_name = 'student' AND column_name = 'grade_id') = 0
);

SET @sql := IF(@needs_grade_migration,
    'ALTER TABLE student ADD COLUMN grade_id INT NULL AFTER section_id',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@needs_grade_migration,
    'UPDATE student SET grade_id = (SELECT MIN(id) FROM grade) WHERE grade_id IS NULL',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@needs_grade_migration,
    'ALTER TABLE student MODIFY grade_id INT NOT NULL',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@needs_grade_migration,
    'ALTER TABLE student ADD CONSTRAINT fk_student_grade FOREIGN KEY (grade_id) REFERENCES grade(id)',
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
