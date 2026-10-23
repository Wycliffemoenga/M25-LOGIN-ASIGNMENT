-- ============================================================================
-- School Gate Entry Management System - Database Schema
-- Database: saad
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `saad` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `saad`;

-- Drop existing tables in reverse dependency order
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `emergency_muster_records`;
DROP TABLE IF EXISTS `emergency_roll_call`;
DROP TABLE IF EXISTS `parent_notifications`;
DROP TABLE IF EXISTS `gate_logs`;
DROP TABLE IF EXISTS `exit_permissions`;
DROP TABLE IF EXISTS `vehicles`;
DROP TABLE IF EXISTS `visitors`;
DROP TABLE IF EXISTS `staff`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `system_settings`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. SYSTEM SETTINGS
CREATE TABLE `system_settings` (
  `setting_key` VARCHAR(50) NOT NULL,
  `setting_value` TEXT NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. USERS (Roles: guard, teacher, admin, principal)
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('guard', 'teacher', 'admin', 'principal') NOT NULL DEFAULT 'guard',
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. STUDENTS
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admission_no` VARCHAR(30) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `class_grade` VARCHAR(30) NOT NULL,
  `parent_name` VARCHAR(100) NOT NULL,
  `parent_phone` VARCHAR(30) NOT NULL,
  `parent_email` VARCHAR(100) DEFAULT NULL,
  `card_number` VARCHAR(50) NOT NULL UNIQUE,
  `gender` ENUM('Male', 'Female') DEFAULT 'Male',
  `status` ENUM('on_campus', 'off_campus') NOT NULL DEFAULT 'off_campus',
  `last_seen` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_students_status` (`status`),
  INDEX `idx_students_class` (`class_grade`),
  INDEX `idx_students_card` (`card_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. STAFF
CREATE TABLE `staff` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `staff_no` VARCHAR(30) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `department` VARCHAR(60) NOT NULL,
  `role_title` VARCHAR(60) DEFAULT 'Teacher',
  `phone` VARCHAR(30) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `card_number` VARCHAR(50) NOT NULL UNIQUE,
  `status` ENUM('on_campus', 'off_campus') NOT NULL DEFAULT 'off_campus',
  `last_seen` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_staff_status` (`status`),
  INDEX `idx_staff_card` (`card_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. VISITORS
CREATE TABLE `visitors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `national_id` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `purpose` VARCHAR(255) NOT NULL,
  `person_to_visit` VARCHAR(100) NOT NULL,
  `badge_number` VARCHAR(30) NOT NULL,
  `vehicle_number` VARCHAR(30) DEFAULT NULL,
  `time_in` DATETIME NOT NULL,
  `time_out` DATETIME DEFAULT NULL,
  `status` ENUM('on_campus', 'off_campus') NOT NULL DEFAULT 'on_campus',
  `guard_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_visitors_status` (`status`),
  INDEX `idx_visitors_badge` (`badge_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. VEHICLES (Staff, Visitor, Delivery movement)
CREATE TABLE `vehicles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plate_number` VARCHAR(30) NOT NULL,
  `driver_name` VARCHAR(100) NOT NULL,
  `driver_phone` VARCHAR(30) DEFAULT NULL,
  `driver_type` ENUM('staff', 'visitor', 'parent', 'service/delivery') NOT NULL DEFAULT 'visitor',
  `vehicle_type` VARCHAR(50) NOT NULL DEFAULT 'Car',
  `purpose` VARCHAR(255) DEFAULT NULL,
  `time_in` DATETIME NOT NULL,
  `time_out` DATETIME DEFAULT NULL,
  `status` ENUM('inside', 'departed') NOT NULL DEFAULT 'inside',
  `guard_id` INT DEFAULT NULL,
  INDEX `idx_vehicles_status` (`status`),
  INDEX `idx_vehicles_plate` (`plate_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. EARLY EXIT PERMISSIONS (Gate Passes)
CREATE TABLE `exit_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `requested_by` VARCHAR(100) NOT NULL,
  `approved_by_user_id` INT DEFAULT NULL,
  `reason` TEXT NOT NULL,
  `destination` VARCHAR(150) DEFAULT NULL,
  `permit_date` DATE NOT NULL,
  `expected_departure_time` TIME DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'used', 'cancelled') NOT NULL DEFAULT 'approved',
  `actual_exit_time` DATETIME DEFAULT NULL,
  `guard_id` INT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_permits_student` (`student_id`),
  INDEX `idx_permits_status` (`status`),
  INDEX `idx_permits_date` (`permit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. GATE LOGS (Full audit trail of all entry/exit events)
CREATE TABLE `gate_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `person_type` ENUM('student', 'staff', 'visitor') NOT NULL,
  `person_id` INT NOT NULL,
  `person_name` VARCHAR(100) NOT NULL,
  `identifier` VARCHAR(50) NOT NULL,
  `direction` ENUM('in', 'out') NOT NULL,
  `scan_method` ENUM('card_scan', 'qr_scan', 'manual_search') NOT NULL DEFAULT 'card_scan',
  `timestamp` DATETIME NOT NULL,
  `is_late` TINYINT(1) NOT NULL DEFAULT 0,
  `late_reason` VARCHAR(255) DEFAULT NULL,
  `permit_id` INT DEFAULT NULL,
  `guard_id` INT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  INDEX `idx_gate_logs_time` (`timestamp`),
  INDEX `idx_gate_logs_person` (`person_type`, `person_id`),
  INDEX `idx_gate_logs_late` (`is_late`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. PARENT NOTIFICATIONS (SMS/Email Alerts)
CREATE TABLE `parent_notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `student_name` VARCHAR(100) NOT NULL,
  `parent_contact` VARCHAR(100) NOT NULL,
  `channel` ENUM('SMS', 'Email') NOT NULL DEFAULT 'SMS',
  `event_type` ENUM('late_arrival', 'early_exit', 'check_in', 'check_out', 'emergency') NOT NULL,
  `message` TEXT NOT NULL,
  `sent_at` DATETIME NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'Delivered',
  INDEX `idx_notif_student` (`student_id`),
  INDEX `idx_notif_time` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. EMERGENCY ROLL CALL
CREATE TABLE `emergency_roll_call` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `initiated_by_user_id` INT NOT NULL,
  `started_at` DATETIME NOT NULL,
  `ended_at` DATETIME DEFAULT NULL,
  `total_expected` INT NOT NULL DEFAULT 0,
  `accounted_count` INT NOT NULL DEFAULT 0,
  `missing_count` INT NOT NULL DEFAULT 0,
  `status` ENUM('active', 'completed') NOT NULL DEFAULT 'active',
  `notes` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. EMERGENCY MUSTER RECORDS
CREATE TABLE `emergency_muster_records` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `roll_call_id` INT NOT NULL,
  `person_type` ENUM('student', 'staff', 'visitor') NOT NULL,
  `person_id` INT NOT NULL,
  `person_name` VARCHAR(100) NOT NULL,
  `category_group` VARCHAR(60) NOT NULL,
  `muster_status` ENUM('unaccounted', 'safe', 'missing', 'injured') NOT NULL DEFAULT 'unaccounted',
  `verified_by_user_id` INT DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  INDEX `idx_muster_roll_call` (`roll_call_id`),
  INDEX `idx_muster_status` (`muster_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
