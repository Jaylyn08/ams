-- One-off migration for databases created before student.section_id existed
-- (i.e. anyone who ran the old setup.sql before this change). Safe to run
-- once; do NOT run this against a fresh database created from the current
-- setup.sql, which already defines section_id directly.

ALTER TABLE student ADD COLUMN section_id INT NULL AFTER gender;

UPDATE student s
JOIN section sec ON sec.name = s.section
SET s.section_id = sec.id;

ALTER TABLE student MODIFY section_id INT NOT NULL;
ALTER TABLE student ADD CONSTRAINT fk_student_section FOREIGN KEY (section_id) REFERENCES section(id);
ALTER TABLE student DROP COLUMN section;
