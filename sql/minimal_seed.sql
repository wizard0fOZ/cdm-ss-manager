-- Minimal seed: sysadmin user + active academic year + sessions + terms
SET @sysadmin_email = 'osmund.dev@gmail.com';
SET @sysadmin_name = 'Osmund Dev';
SET @sysadmin_password_hash = '$2y$10$yRpVMpdb93eHmcQ5xJc9qucQ74e0N7DJxN9axWtzA0xdEh6xDsuj.';

-- Create/update sysadmin user
INSERT INTO users (full_name, email, password_hash, status, must_change_password)
VALUES (@sysadmin_name, @sysadmin_email, @sysadmin_password_hash, 'ACTIVE', 0)
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  password_hash = VALUES(password_hash),
  status = 'ACTIVE',
  must_change_password = 0;

SET @sysadmin_id = (SELECT id FROM users WHERE email = @sysadmin_email LIMIT 1);
SET @sysadmin_role_id = (SELECT id FROM roles WHERE code = 'SYSADMIN' LIMIT 1);

-- Ensure sysadmin role assignment
INSERT IGNORE INTO user_roles (user_id, role_id, assigned_by)
VALUES (@sysadmin_id, @sysadmin_role_id, @sysadmin_id);

-- Academic year
INSERT INTO academic_years (label, start_date, end_date, is_active)
VALUES ('2026', '2026-01-01', '2026-12-31', 1)
ON DUPLICATE KEY UPDATE
  start_date = VALUES(start_date),
  end_date = VALUES(end_date),
  is_active = 1;

-- Sessions
INSERT INTO sessions (name, start_time, end_time, sort_order)
VALUES
  ('Session 1', '08:30:00', '09:45:00', 1),
  ('Session 2', '10:00:00', '11:15:00', 2),
  ('Session 3', '11:30:00', '12:45:00', 3)
ON DUPLICATE KEY UPDATE
  start_time = VALUES(start_time),
  end_time = VALUES(end_time),
  sort_order = VALUES(sort_order);

-- Terms for 2026
SET @year_id = (SELECT id FROM academic_years WHERE label = '2026' LIMIT 1);
INSERT INTO terms (academic_year_id, term_number, label, start_date, end_date)
VALUES
  (@year_id, 1, 'Term 1', '2026-01-01', '2026-06-30'),
  (@year_id, 2, 'Term 2', '2026-07-01', '2026-12-31')
ON DUPLICATE KEY UPDATE
  start_date = VALUES(start_date),
  end_date = VALUES(end_date);
