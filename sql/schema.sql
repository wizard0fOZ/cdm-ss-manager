all done with SQL
-- Adminer 5.4.1 MySQL 8.0.45 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

CREATE DATABASE `cdm_ss_manager` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `cdm_ss_manager`;

DROP TABLE IF EXISTS `academic_years`;
CREATE TABLE `academic_years` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(40) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ay_label` (`label`),
  KEY `idx_ay_active` (`is_active`),
  KEY `idx_ay_dates` (`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `attendance_records`;
CREATE TABLE `attendance_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_session_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `status` enum('PRESENT','ABSENT','LATE','EXCUSED') NOT NULL,
  `absence_reason` enum('SICK','FAMILY','TRAVEL','OTHER') DEFAULT NULL,
  `absence_note` text,
  `note` text,
  `marked_by` bigint unsigned NOT NULL,
  `marked_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_attendance_unique` (`class_session_id`,`student_id`),
  KEY `idx_attendance_status` (`status`),
  KEY `idx_attendance_student` (`student_id`),
  KEY `fk_ar_marked_by` (`marked_by`),
  CONSTRAINT `fk_ar_marked_by` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_ar_session` FOREIGN KEY (`class_session_id`) REFERENCES `class_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ar_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actor_user_id` bigint unsigned NOT NULL,
  `action` varchar(80) NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `entity_id` varchar(64) NOT NULL,
  `before_json` json DEFAULT NULL,
  `after_json` json DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_actor` (`actor_user_id`,`created_at`),
  KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  CONSTRAINT `fk_audit_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `calendar_events`;
CREATE TABLE `calendar_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `academic_year_id` bigint unsigned NOT NULL,
  `title` varchar(180) NOT NULL,
  `category` enum('HOLIDAY','NO_CLASS','CAMP','TRAINING','SPECIAL_EVENT','OTHER') NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT '0',
  `scope` enum('GLOBAL','CLASS') NOT NULL DEFAULT 'GLOBAL',
  `class_id` bigint unsigned DEFAULT NULL,
  `description` text,
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ce_year_range` (`academic_year_id`,`start_datetime`,`end_datetime`),
  KEY `idx_ce_scope_class` (`scope`,`class_id`),
  KEY `idx_ce_category` (`category`),
  KEY `fk_ce_class` (`class_id`),
  KEY `fk_ce_created_by` (`created_by`),
  CONSTRAINT `fk_ce_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ce_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_ce_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `class_sessions`;
CREATE TABLE `class_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `session_date` date NOT NULL,
  `status` enum('OPEN','LOCKED') NOT NULL DEFAULT 'OPEN',
  `locked_at` datetime DEFAULT NULL,
  `locked_by` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_class_session` (`class_id`,`session_date`),
  KEY `idx_sessions_date` (`session_date`),
  KEY `idx_sessions_status` (`status`),
  KEY `fk_sessions_locked_by` (`locked_by`),
  KEY `fk_sessions_created_by` (`created_by`),
  CONSTRAINT `fk_sessions_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sessions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_sessions_locked_by` FOREIGN KEY (`locked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `class_teacher_assignments`;
CREATE TABLE `class_teacher_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `assignment_role` enum('MAIN','ASSISTANT') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_class_teacher_unique` (`class_id`,`user_id`),
  KEY `idx_cta_class_role_active` (`class_id`,`assignment_role`,`end_date`),
  KEY `idx_cta_user_role_active` (`user_id`,`assignment_role`,`end_date`),
  CONSTRAINT `fk_cta_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cta_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `classes`;
CREATE TABLE `classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `academic_year_id` bigint unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `program` enum('ENGLISH','KUBM','MANDARIN','TAMIL','RCIC','CONFIRMANDS') DEFAULT NULL,
  `grade_level` tinyint unsigned DEFAULT NULL,
  `stream` enum('PAUL','PETER','SINGLE') NOT NULL DEFAULT 'SINGLE',
  `room` varchar(80) DEFAULT NULL,
  `session_id` bigint unsigned NOT NULL,
  `status` enum('DRAFT','ACTIVE','INACTIVE') NOT NULL DEFAULT 'DRAFT',
  `max_students` int unsigned DEFAULT NULL,
  `version` int unsigned NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_classes_status` (`status`),
  KEY `idx_classes_program` (`program`),
  KEY `idx_classes_grade_stream` (`grade_level`,`stream`),
  KEY `idx_classes_session_id` (`session_id`),
  KEY `idx_classes_ay` (`academic_year_id`),
  CONSTRAINT `fk_classes_ay` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_classes_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `faith_book_records`;
CREATE TABLE `faith_book_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned DEFAULT NULL,
  `term_id` bigint unsigned DEFAULT NULL,
  `entry_date` date DEFAULT NULL,
  `entry_type` enum('NOTE','ACHIEVEMENT','DISCIPLINE','ASSESSMENT','OTHER') NOT NULL DEFAULT 'NOTE',
  `title` varchar(180) NOT NULL,
  `content` text NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fbr_student` (`student_id`,`created_at`),
  KEY `idx_fbr_student_year_term` (`student_id`,`academic_year_id`,`term_id`),
  KEY `idx_fbr_type` (`entry_type`),
  KEY `fk_fbr_year` (`academic_year_id`),
  KEY `fk_fbr_term` (`term_id`),
  KEY `fk_fbr_created_by` (`created_by`),
  CONSTRAINT `fk_fbr_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_fbr_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fbr_term` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fbr_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `import_job_rows`;
CREATE TABLE `import_job_rows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned NOT NULL,
  `row_num` int unsigned NOT NULL,
  `status` enum('SUCCESS','FAILED','SKIPPED') NOT NULL,
  `error_message` varchar(500) DEFAULT NULL,
  `payload_json` json DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_job_row` (`job_id`,`row_num`),
  KEY `idx_ijr_job_status` (`job_id`,`status`),
  CONSTRAINT `fk_ijr_job` FOREIGN KEY (`job_id`) REFERENCES `import_jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `import_jobs`;
CREATE TABLE `import_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_type` enum('STUDENTS','TEACHERS','CLASSES') NOT NULL,
  `status` enum('PENDING','RUNNING','COMPLETED','FAILED') NOT NULL DEFAULT 'PENDING',
  `original_filename` varchar(255) DEFAULT NULL,
  `stored_file_path` varchar(500) DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `total_rows` int unsigned DEFAULT NULL,
  `success_rows` int unsigned DEFAULT NULL,
  `failed_rows` int unsigned DEFAULT NULL,
  `error_summary` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_import_status` (`status`,`created_at`),
  KEY `fk_import_created_by` (`created_by`),
  CONSTRAINT `fk_import_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `lesson_plans`;
CREATE TABLE `lesson_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `session_date` date NOT NULL,
  `title` varchar(180) NOT NULL,
  `description` text,
  `content` text NOT NULL,
  `url` varchar(500) DEFAULT NULL,
  `status` enum('DRAFT','PUBLISHED') NOT NULL DEFAULT 'DRAFT',
  `version` int unsigned NOT NULL DEFAULT '1',
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_lesson_plan` (`class_id`,`session_date`),
  KEY `idx_lp_date` (`session_date`),
  KEY `idx_lp_status` (`status`),
  KEY `fk_lp_created_by` (`created_by`),
  KEY `fk_lp_updated_by` (`updated_by`),
  CONSTRAINT `fk_lp_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lp_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_lp_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_reset_token` (`token`),
  KEY `idx_reset_user` (`user_id`),
  KEY `idx_reset_expiry` (`expires_at`),
  CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(80) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `module` varchar(40) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_permissions_code` (`code`),
  KEY `idx_permissions_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `permissions` (`id`, `code`, `name`, `description`, `module`, `created_at`) VALUES
(1,	'students.view',	'View students',	NULL,	'students',	'2026-02-05 16:27:12'),
(2,	'students.create',	'Create students',	NULL,	'students',	'2026-02-05 16:27:12'),
(3,	'students.edit',	'Edit students',	NULL,	'students',	'2026-02-05 16:27:12'),
(4,	'students.export',	'Export students',	NULL,	'students',	'2026-02-05 16:27:12'),
(5,	'classes.view',	'View classes',	NULL,	'teaching',	'2026-02-05 16:27:12'),
(6,	'classes.manage',	'Manage classes',	NULL,	'teaching',	'2026-02-05 16:27:12'),
(7,	'classes.assign_teachers',	'Assign teachers',	NULL,	'teaching',	'2026-02-05 16:27:12'),
(8,	'attendance.view',	'View attendance',	NULL,	'teaching',	'2026-02-05 16:27:12'),
(9,	'attendance.mark',	'Mark attendance',	NULL,	'teaching',	'2026-02-05 16:27:12'),
(10,	'attendance.bulk_mark',	'Bulk mark attendance',	NULL,	'teaching',	'2026-02-05 16:27:12'),
(11,	'attendance.lock',	'Lock attendance',	NULL,	'teaching',	'2026-02-05 16:27:12'),
(12,	'lessons.view',	'View lesson plans',	NULL,	'teaching',	'2026-02-05 16:27:12'),
(13,	'lessons.create',	'Create lesson plans',	NULL,	'teaching',	'2026-02-05 16:27:12'),
(14,	'lessons.edit',	'Edit lesson plans',	NULL,	'teaching',	'2026-02-05 16:27:12'),
(15,	'lessons.publish',	'Publish lesson plans',	NULL,	'teaching',	'2026-02-05 16:27:12'),
(16,	'faithbook.view',	'View faith book',	NULL,	'faithbook',	'2026-02-05 16:27:12'),
(17,	'faithbook.write',	'Write faith book',	NULL,	'faithbook',	'2026-02-05 16:27:12'),
(18,	'faithbook.edit',	'Edit faith book',	NULL,	'faithbook',	'2026-02-05 16:27:12'),
(19,	'training.view',	'View training records',	NULL,	'staff',	'2026-02-05 16:27:12'),
(20,	'training.manage',	'Manage training records',	NULL,	'staff',	'2026-02-05 16:27:12'),
(21,	'calendar.manage',	'Manage calendar',	NULL,	'calendar',	'2026-02-05 16:27:12'),
(22,	'bulletins.manage',	'Manage announcements',	NULL,	'bulletins',	'2026-02-05 16:27:12'),
(23,	'imports.run',	'Run imports',	NULL,	'imports',	'2026-02-05 16:27:12'),
(24,	'imports.view',	'View import jobs',	NULL,	'imports',	'2026-02-05 16:27:12'),
(25,	'admin.users',	'Manage users',	NULL,	'admin',	'2026-02-05 16:27:12'),
(26,	'admin.roles',	'Manage roles and permissions',	NULL,	'admin',	'2026-02-05 16:27:12'),
(27,	'admin.settings',	'Manage system settings',	NULL,	'admin',	'2026-02-05 16:27:12'),
(28,	'admin.audit',	'View audit logs',	NULL,	'admin',	'2026-02-05 16:27:12');

DROP TABLE IF EXISTS `refresh_tokens`;
CREATE TABLE `refresh_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_refresh_token_hash` (`token_hash`),
  KEY `idx_refresh_user` (`user_id`,`expires_at`),
  CONSTRAINT `fk_refresh_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `granted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `granted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `idx_role_permissions_perm` (`permission_id`),
  KEY `fk_rp_granted_by` (`granted_by`),
  CONSTRAINT `fk_rp_granted_by` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted_at`, `granted_by`) VALUES
(1,	1,	'2026-02-05 16:27:12',	NULL),
(1,	2,	'2026-02-05 16:27:12',	NULL),
(1,	3,	'2026-02-05 16:27:12',	NULL),
(1,	4,	'2026-02-05 16:27:12',	NULL),
(1,	5,	'2026-02-05 16:27:12',	NULL),
(1,	6,	'2026-02-05 16:27:12',	NULL),
(1,	7,	'2026-02-05 16:27:12',	NULL),
(1,	8,	'2026-02-05 16:27:12',	NULL),
(1,	9,	'2026-02-05 16:27:12',	NULL),
(1,	10,	'2026-02-05 16:27:12',	NULL),
(1,	11,	'2026-02-05 16:27:12',	NULL),
(1,	12,	'2026-02-05 16:27:12',	NULL),
(1,	13,	'2026-02-05 16:27:12',	NULL),
(1,	14,	'2026-02-05 16:27:12',	NULL),
(1,	15,	'2026-02-05 16:27:12',	NULL),
(1,	16,	'2026-02-05 16:27:12',	NULL),
(1,	17,	'2026-02-05 16:27:12',	NULL),
(1,	18,	'2026-02-05 16:27:12',	NULL),
(1,	19,	'2026-02-05 16:27:12',	NULL),
(1,	20,	'2026-02-05 16:27:12',	NULL),
(1,	21,	'2026-02-05 16:27:12',	NULL),
(1,	22,	'2026-02-05 16:27:12',	NULL),
(1,	23,	'2026-02-05 16:27:12',	NULL),
(1,	24,	'2026-02-05 16:27:12',	NULL),
(1,	25,	'2026-02-05 16:27:12',	NULL),
(1,	26,	'2026-02-05 16:27:12',	NULL),
(1,	27,	'2026-02-05 16:27:12',	NULL),
(1,	28,	'2026-02-05 16:27:12',	NULL),
(2,	1,	'2026-02-05 16:27:12',	NULL),
(2,	2,	'2026-02-05 16:27:12',	NULL),
(2,	3,	'2026-02-05 16:27:12',	NULL),
(2,	4,	'2026-02-05 16:27:12',	NULL),
(2,	5,	'2026-02-05 16:27:12',	NULL),
(2,	6,	'2026-02-05 16:27:12',	NULL),
(2,	7,	'2026-02-05 16:27:12',	NULL),
(2,	8,	'2026-02-05 16:27:12',	NULL),
(2,	9,	'2026-02-05 16:27:12',	NULL),
(2,	10,	'2026-02-05 16:27:12',	NULL),
(2,	11,	'2026-02-05 16:27:12',	NULL),
(2,	12,	'2026-02-05 16:27:12',	NULL),
(2,	13,	'2026-02-05 16:27:12',	NULL),
(2,	14,	'2026-02-05 16:27:12',	NULL),
(2,	15,	'2026-02-05 16:27:12',	NULL),
(2,	16,	'2026-02-05 16:27:12',	NULL),
(2,	17,	'2026-02-05 16:27:12',	NULL),
(2,	18,	'2026-02-05 16:27:12',	NULL),
(2,	19,	'2026-02-05 16:27:12',	NULL),
(2,	20,	'2026-02-05 16:27:12',	NULL),
(2,	21,	'2026-02-05 16:27:12',	NULL),
(2,	22,	'2026-02-05 16:27:12',	NULL),
(2,	23,	'2026-02-05 16:27:12',	NULL),
(2,	24,	'2026-02-05 16:27:12',	NULL),
(2,	25,	'2026-02-05 16:27:12',	NULL),
(2,	27,	'2026-02-05 16:27:12',	NULL),
(2,	28,	'2026-02-05 16:27:12',	NULL),
(3,	1,	'2026-02-05 16:27:12',	NULL),
(3,	5,	'2026-02-05 16:27:12',	NULL),
(3,	8,	'2026-02-05 16:27:12',	NULL),
(3,	9,	'2026-02-05 16:27:12',	NULL),
(3,	10,	'2026-02-05 16:27:12',	NULL),
(3,	12,	'2026-02-05 16:27:12',	NULL),
(3,	13,	'2026-02-05 16:27:12',	NULL),
(3,	14,	'2026-02-05 16:27:12',	NULL),
(3,	15,	'2026-02-05 16:27:12',	NULL),
(3,	16,	'2026-02-05 16:27:12',	NULL),
(3,	17,	'2026-02-05 16:27:12',	NULL),
(3,	18,	'2026-02-05 16:27:12',	NULL);

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) NOT NULL,
  `name` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_roles_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `roles` (`id`, `code`, `name`, `description`, `created_at`) VALUES
(1,	'SYSADMIN',	'SysAdmin',	'Full system access',	'2026-02-05 16:27:12'),
(2,	'STAFF_ADMIN',	'Admin Staff',	'Coordinator, Office Admins, Core Team',	'2026-02-05 16:27:12'),
(3,	'TEACHER',	'Teacher',	'Teacher and Assistant Teacher',	'2026-02-05 16:27:12');

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `sort_order` tinyint unsigned NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sessions_name` (`name`),
  KEY `idx_sessions_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `sessions` (`id`, `name`, `start_time`, `end_time`, `sort_order`, `created_at`, `updated_at`) VALUES
(1,	'Session 1',	'08:30:00',	'09:45:00',	1,	'2026-02-05 16:27:12',	'2026-02-05 16:27:12'),
(2,	'Session 2',	'10:00:00',	'11:15:00',	2,	'2026-02-05 16:27:12',	'2026-02-05 16:27:12'),
(3,	'Session 3',	'11:30:00',	'12:45:00',	3,	'2026-02-05 16:27:12',	'2026-02-05 16:27:12');

DROP TABLE IF EXISTS `student_class_enrollments`;
CREATE TABLE `student_class_enrollments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `class_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned DEFAULT NULL,
  `term_id` bigint unsigned DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sce_student_active` (`student_id`,`end_date`),
  KEY `idx_sce_class_active` (`class_id`,`end_date`),
  KEY `idx_sce_year_term` (`academic_year_id`,`term_id`),
  KEY `fk_sce_term` (`term_id`),
  CONSTRAINT `fk_sce_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sce_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sce_term` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sce_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `student_guardians`;
CREATE TABLE `student_guardians` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `relationship_label` varchar(80) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `can_pickup` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_guardians_student` (`student_id`),
  KEY `idx_guardians_primary` (`student_id`,`is_primary`),
  CONSTRAINT `fk_guardian_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `student_sacrament_info`;
CREATE TABLE `student_sacrament_info` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `church_of_baptism` varchar(180) DEFAULT NULL,
  `place_of_baptism` varchar(180) DEFAULT NULL,
  `date_of_baptism` date DEFAULT NULL,
  `godfather` varchar(180) DEFAULT NULL,
  `godmother` varchar(180) DEFAULT NULL,
  `date_of_first_holy_communion` date DEFAULT NULL,
  `place_of_first_holy_communion` varchar(180) DEFAULT NULL,
  `date_of_confirmation` date DEFAULT NULL,
  `place_of_confirmation` varchar(180) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sacrament_student` (`student_id`),
  CONSTRAINT `fk_sacrament_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `dob` date DEFAULT NULL,
  `identity_number` varchar(50) DEFAULT NULL,
  `is_rcic` tinyint(1) NOT NULL DEFAULT '0',
  `address` text,
  `status` enum('ACTIVE','INACTIVE','GRADUATED','TRANSFERRED') NOT NULL DEFAULT 'ACTIVE',
  `notes` text,
  `version` int unsigned NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_students_status` (`status`),
  KEY `idx_students_full_name` (`full_name`),
  KEY `idx_students_identity` (`identity_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(180) NOT NULL,
  `message` text NOT NULL,
  `scope` enum('GLOBAL','CLASS') NOT NULL DEFAULT 'GLOBAL',
  `class_id` bigint unsigned DEFAULT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `status` enum('DRAFT','PUBLISHED') NOT NULL DEFAULT 'DRAFT',
  `published_at` datetime DEFAULT NULL,
  `pin_until` datetime DEFAULT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `priority` tinyint unsigned NOT NULL DEFAULT '0',
  `version` int unsigned NOT NULL DEFAULT '1',
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sa_active` (`start_at`,`end_at`),
  KEY `idx_sa_scope_class` (`scope`,`class_id`),
  KEY `fk_sa_class` (`class_id`),
  KEY `fk_sa_created_by` (`created_by`),
  CONSTRAINT `fk_announcements_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_announcements_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text,
  `updated_by` bigint unsigned DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `teacher_training_records`;
CREATE TABLE `teacher_training_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` enum('PSO','FORMATION','OTHER') NOT NULL,
  `title` varchar(180) DEFAULT NULL,
  `provider` varchar(180) DEFAULT NULL,
  `attended_date` date DEFAULT NULL,
  `hours_fulfilled` decimal(5,2) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `evidence_url` varchar(500) DEFAULT NULL,
  `stored_file_path` varchar(500) DEFAULT NULL,
  `remarks` text,
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ttr_user` (`user_id`),
  KEY `idx_ttr_type` (`type`),
  KEY `idx_ttr_expiry` (`expiry_date`),
  KEY `fk_ttr_created_by` (`created_by`),
  CONSTRAINT `fk_ttr_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_ttr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `terms`;
CREATE TABLE `terms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `academic_year_id` bigint unsigned NOT NULL,
  `term_number` tinyint unsigned NOT NULL,
  `label` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_term_year_number` (`academic_year_id`,`term_number`),
  KEY `idx_terms_dates` (`start_date`,`end_date`),
  CONSTRAINT `fk_terms_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `user_invites`;
CREATE TABLE `user_invites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(190) NOT NULL,
  `role_code` varchar(40) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_invite_token` (`token`),
  KEY `idx_invite_email` (`email`),
  KEY `idx_invite_expiry` (`expires_at`),
  KEY `fk_invite_created_by` (`created_by`),
  CONSTRAINT `fk_invite_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `user_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `idx_user_roles_role` (`role_id`),
  KEY `fk_user_roles_assigned_by` (`assigned_by`),
  CONSTRAINT `fk_user_roles_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('ACTIVE','DISABLED') NOT NULL DEFAULT 'ACTIVE',
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `contact_number` varchar(50) DEFAULT NULL,
  `date_joined` date DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- 2026-02-05 16:29:01 UTC
