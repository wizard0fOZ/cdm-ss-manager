-- Fresh start: keep only system essentials + sysadmin account
-- Set your sysadmin email below before running.

SET @sysadmin_email = 'osmund.dev@gmail.com';
SET @sysadmin_id = (SELECT id FROM users WHERE email = @sysadmin_email LIMIT 1);

-- Safety check
SELECT @sysadmin_id AS sysadmin_id;

SET FOREIGN_KEY_CHECKS = 0;

-- Clear operational data
TRUNCATE TABLE attendance_records;
TRUNCATE TABLE class_sessions;
TRUNCATE TABLE class_teacher_assignments;
TRUNCATE TABLE student_class_enrollments;
TRUNCATE TABLE student_guardians;
TRUNCATE TABLE student_sacrament_info;
TRUNCATE TABLE students;
TRUNCATE TABLE faith_book_records;
TRUNCATE TABLE lesson_plans;
TRUNCATE TABLE teacher_training_records;
TRUNCATE TABLE calendar_events;
TRUNCATE TABLE announcements;
TRUNCATE TABLE import_job_rows;
TRUNCATE TABLE import_jobs;
TRUNCATE TABLE user_invites;
TRUNCATE TABLE password_resets;
TRUNCATE TABLE refresh_tokens;
TRUNCATE TABLE audit_logs;

-- Clear academic setup data
TRUNCATE TABLE terms;
TRUNCATE TABLE sessions;
TRUNCATE TABLE classes;
TRUNCATE TABLE academic_years;

-- Keep system settings (comment out to reset settings too)
-- TRUNCATE TABLE system_settings;

-- Keep only sysadmin user + roles
DELETE FROM user_roles WHERE user_id <> @sysadmin_id AND @sysadmin_id IS NOT NULL;
DELETE FROM users WHERE id <> @sysadmin_id AND @sysadmin_id IS NOT NULL;

SET FOREIGN_KEY_CHECKS = 1;
