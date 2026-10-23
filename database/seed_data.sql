-- ============================================================================
-- School Gate Entry Management System - Sample Seed Data
-- Database: saad
-- ============================================================================

USE `saad`;

-- System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('school_name', 'St. Jude International Academy', 'Official school institution name'),
('school_motto', 'Excellence, Integrity & Leadership', 'School motto shown on badges & portal'),
('gate_name', 'Main Campus Gate A (Security Post)', 'Designation of the current gate terminal'),
('cutoff_time', '07:30:00', 'Morning cutoff time after which arrivals are automatically flagged as late'),
('emergency_mode', '0', 'Emergency mode active flag (1 = Active Evacuation, 0 = Normal Operation)'),
('sms_alerts_enabled', '1', 'Simulate automatic SMS/Email dispatches to parents for late arrivals and early exits'),
('allow_visitor_walkin', '1', 'Allow visitor on-the-spot registration at the gate');

-- Users
-- admin / admin123
-- guard / guard123
-- teacher / teacher123
-- principal / principal123
INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `email`, `phone`) VALUES
(1, 'admin', '$2y$10$/uIlYhvIyASCnkhxP3ujCuwVAYgJZRJEdwhXZ7/QnydMetRK9EAi2', 'Arthur Pendelton (IT Administrator)', 'admin', 'admin@stjude.ac.ke', '+254 700 111 222'),
(2, 'guard', '$2y$10$te5OgPFW2MaE6Rj11wr3A.4J0PSNlDueFmBlEfStcVn03.euN5MCa', 'Officer Michael Omondi (Chief Gate Guard)', 'guard', 'guard@stjude.ac.ke', '+254 711 333 444'),
(3, 'teacher', '$2y$10$9.JEHR1vc1pB/NeZapn.L.oVSyC1rMnKYKCbdLhS6evRl/m6vHqwS', 'Sarah K. Jenkins (Class Master - Form 4)', 'teacher', 'sarah.jenkins@stjude.ac.ke', '+254 722 555 666'),
(4, 'principal', '$2y$10$K8U4DxWoWCaMiygCjft4.OEd8S7D2Q.F6N8sIhU.ISX/q72/BZxDy', 'Dr. Evelyn W. Mbeki (School Principal)', 'principal', 'principal@stjude.ac.ke', '+254 733 777 888');

-- Students
INSERT INTO `students` (`id`, `admission_no`, `full_name`, `class_grade`, `parent_name`, `parent_phone`, `parent_email`, `card_number`, `gender`, `status`, `last_seen`) VALUES
(1, 'ADM-2024-001', 'Brian Kimani', 'Form 4 Science', 'David Kimani', '+254 712 101 202', 'dkimani@gmail.com', 'CARD-STU-1001', 'Male', 'on_campus', '2026-10-09 07:15:00'),
(2, 'ADM-2024-002', 'Faith Wanjiku', 'Form 4 Science', 'Grace Wanjiku', '+254 723 202 303', 'gwanjiku@yahoo.com', 'CARD-STU-1002', 'Female', 'on_campus', '2026-10-09 07:42:00'),
(3, 'ADM-2024-003', 'Kevin Kiprop', 'Form 3 Alpha', 'Samuel Kiprop', '+254 734 303 404', 'skiprop@hotmail.com', 'CARD-STU-1003', 'Male', 'on_campus', '2026-10-09 07:22:00'),
(4, 'ADM-2024-004', 'Mercy Chebet', 'Form 3 Alpha', 'Rose Chebet', '+254 745 404 505', 'rchebet@outlook.com', 'CARD-STU-1004', 'Female', 'off_campus', '2026-10-08 16:30:00'),
(5, 'ADM-2024-005', 'Daniel Otieno', 'Form 2 West', 'Patrick Otieno', '+254 756 505 606', 'potieno@gmail.com', 'CARD-STU-1005', 'Male', 'on_campus', '2026-10-09 07:48:00'),
(6, 'ADM-2024-006', 'Brenda Achieng', 'Form 2 West', 'Hellen Achieng', '+254 767 606 707', 'hachieng@gmail.com', 'CARD-STU-1006', 'Female', 'on_campus', '2026-10-09 07:10:00'),
(7, 'ADM-2024-007', 'Joshua Mutua', 'Form 1 East', 'James Mutua', '+254 778 707 808', 'jmutua@yahoo.com', 'CARD-STU-1008', 'Male', 'off_campus', '2026-10-08 16:15:00'),
(8, 'ADM-2024-008', 'Esther Nyambura', 'Form 1 East', 'Mary Nyambura', '+254 789 808 909', 'mnyambura@gmail.com', 'CARD-STU-1009', 'Female', 'on_campus', '2026-10-09 07:25:00'),
(9, 'ADM-2024-009', 'Dennis Korir', 'Form 4 Arts', 'Geoffrey Korir', '+254 790 909 010', 'gkorir@gmail.com', 'CARD-STU-1010', 'Male', 'on_campus', '2026-10-09 07:55:00'),
(10, 'ADM-2024-010', 'Purity Moraa', 'Form 3 Beta', 'Beatrice Moraa', '+254 701 112 233', 'bmoraa@yahoo.com', 'CARD-STU-1011', 'Female', 'on_campus', '2026-10-09 07:05:00'),
(11, 'ADM-2024-011', 'Lucas Njoroge', 'Form 2 East', 'Simon Njoroge', '+254 712 223 344', 'snjoroge@gmail.com', 'CARD-STU-1012', 'Male', 'off_campus', '2026-10-08 16:20:00'),
(12, 'ADM-2024-012', 'Joy Akinyi', 'Form 4 Science', 'Moses Akinyi', '+254 723 334 455', 'makinyi@gmail.com', 'CARD-STU-1013', 'Female', 'on_campus', '2026-10-09 07:28:00');

-- Staff
INSERT INTO `staff` (`id`, `staff_no`, `full_name`, `department`, `role_title`, `phone`, `email`, `card_number`, `status`, `last_seen`) VALUES
(1, 'STF-001', 'Prof. Lawrence Mwangi', 'Science & Mathematics', 'HOD Sciences', '+254 721 111 001', 'lmwangi@stjude.ac.ke', 'CARD-STF-2001', 'on_campus', '2026-10-09 07:00:00'),
(2, 'STF-002', 'Dr. Beatrice Wangari', 'Languages & Literature', 'Senior English Master', '+254 721 111 002', 'bwangari@stjude.ac.ke', 'CARD-STF-2002', 'on_campus', '2026-10-09 07:12:00'),
(3, 'STF-003', 'Mr. Robert Kibet', 'Physical Education & Games', 'Sports Coordinator', '+254 721 111 003', 'rkibet@stjude.ac.ke', 'CARD-STF-2003', 'on_campus', '2026-10-09 06:50:00'),
(4, 'STF-004', 'Mrs. Catherine Adhiambo', 'Finance & Accounts', 'Bursar', '+254 721 111 004', 'cadhiambo@stjude.ac.ke', 'CARD-STF-2004', 'on_campus', '2026-10-09 07:20:00'),
(5, 'STF-005', 'Eng. Dominic Barasa', 'ICT & Facilities', 'System Administrator', '+254 721 111 005', 'dbarasa@stjude.ac.ke', 'CARD-STF-2005', 'off_campus', '2026-10-08 17:00:00');

-- Visitors
INSERT INTO `visitors` (`id`, `full_name`, `national_id`, `phone`, `purpose`, `person_to_visit`, `badge_number`, `vehicle_number`, `time_in`, `time_out`, `status`, `guard_id`) VALUES
(1, 'Hassan Abdi Mohamed', 'ID-28491029', '+254 722 888 999', 'Textbook supply inspection', 'Deputy Principal - Academics', 'BADGE-V-01', 'KCD 204X', '2026-10-09 08:15:00', NULL, 'on_campus', 2),
(2, 'Alice M. Kilonzo', 'ID-19827364', '+254 733 999 000', 'Parent consultation regarding fee balance', 'Bursar (Mrs. Adhiambo)', 'BADGE-V-02', 'KBZ 109M', '2026-10-09 08:30:00', NULL, 'on_campus', 2),
(3, 'Eng. Paul Kariuki', 'ID-23847561', '+254 711 777 666', 'Solar panel routine maintenance', 'Facilities Dept', 'BADGE-V-03', 'KDG 441L', '2026-10-08 09:00:00', '2026-10-08 14:30:00', 'off_campus', 2);

-- Vehicles
INSERT INTO `vehicles` (`id`, `plate_number`, `driver_name`, `driver_phone`, `driver_type`, `vehicle_type`, `purpose`, `time_in`, `time_out`, `status`, `guard_id`) VALUES
(1, 'KCD 204X', 'Hassan Abdi Mohamed', '+254 722 888 999', 'visitor', 'Pickup Van', 'Textbook delivery', '2026-10-09 08:15:00', NULL, 'inside', 2),
(2, 'KBZ 109M', 'Alice M. Kilonzo', '+254 733 999 000', 'parent', 'Saloon Car (Toyota)', 'Parent Consultation', '2026-10-09 08:30:00', NULL, 'inside', 2),
(3, 'KBA 555S', 'Prof. Lawrence Mwangi', '+254 721 111 001', 'staff', 'SUV (Subaru Forester)', 'Daily Staff Commute', '2026-10-09 06:58:00', NULL, 'inside', 2),
(4, 'KCC 908T', 'Peter Juma (Bus Driver)', '+254 710 444 333', 'staff', 'School Bus (60 Seater)', 'Morning Student Route Arrival', '2026-10-09 07:05:00', NULL, 'inside', 2);

-- Early Exit Permissions (Gate Passes)
INSERT INTO `exit_permissions` (`id`, `student_id`, `requested_by`, `approved_by_user_id`, `reason`, `destination`, `permit_date`, `expected_departure_time`, `status`, `actual_exit_time`, `guard_id`, `notes`) VALUES
(1, 1, 'Sarah K. Jenkins (Class Master)', 3, 'Urgent medical appointment at St. Francis Clinic (Dental emergency)', 'St. Francis Clinic', CURDATE(), '10:30:00', 'approved', NULL, NULL, 'Parent David Kimani called and approved via phone'),
(2, 3, 'Sarah K. Jenkins (Class Master)', 3, 'Inter-school science quiz competition representation at City Hall', 'National Science Centre', CURDATE(), '12:00:00', 'pending', NULL, NULL, 'Awaiting approval by Head of Science'),
(3, 9, 'Sarah K. Jenkins (Class Master)', 3, 'Sent home for medical recovery (Malaria symptoms)', 'Home', DATE_SUB(CURDATE(), INTERVAL 1 DAY), '11:00:00', 'used', CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 11:15:00'), 2, 'Picked up by father Geoffrey Korir at gate');

-- Gate Logs
INSERT INTO `gate_logs` (`person_type`, `person_id`, `person_name`, `identifier`, `direction`, `scan_method`, `timestamp`, `is_late`, `late_reason`, `permit_id`, `guard_id`, `notes`) VALUES
('staff', 1, 'Prof. Lawrence Mwangi', 'STF-001', 'in', 'card_scan', CONCAT(CURDATE(), ' 07:00:00'), 0, NULL, NULL, 2, 'Staff morning entry'),
('staff', 3, 'Mr. Robert Kibet', 'STF-003', 'in', 'card_scan', CONCAT(CURDATE(), ' 06:50:00'), 0, NULL, NULL, 2, 'Staff morning entry'),
('student', 10, 'Purity Moraa', 'ADM-2024-010', 'in', 'qr_scan', CONCAT(CURDATE(), ' 07:05:00'), 0, NULL, NULL, 2, 'Early student arrival'),
('student', 6, 'Brenda Achieng', 'ADM-2024-006', 'in', 'card_scan', CONCAT(CURDATE(), ' 07:10:00'), 0, NULL, NULL, 2, 'On time arrival'),
('student', 1, 'Brian Kimani', 'ADM-2024-001', 'in', 'card_scan', CONCAT(CURDATE(), ' 07:15:00'), 0, NULL, NULL, 2, 'On time arrival'),
('student', 3, 'Kevin Kiprop', 'ADM-2024-003', 'in', 'card_scan', CONCAT(CURDATE(), ' 07:22:00'), 0, NULL, NULL, 2, 'On time arrival'),
('student', 8, 'Esther Nyambura', 'ADM-2024-008', 'in', 'card_scan', CONCAT(CURDATE(), ' 07:25:00'), 0, NULL, NULL, 2, 'On time arrival'),
('student', 12, 'Joy Akinyi', 'ADM-2024-012', 'in', 'qr_scan', CONCAT(CURDATE(), ' 07:28:00'), 0, NULL, NULL, 2, 'On time arrival'),
('student', 2, 'Faith Wanjiku', 'ADM-2024-002', 'in', 'card_scan', CONCAT(CURDATE(), ' 07:42:00'), 1, 'Heavy traffic delay on Eastern bypass', NULL, 2, 'Late arrival recorded. Parent notified.'),
('student', 5, 'Daniel Otieno', 'ADM-2024-005', 'in', 'manual_search', CONCAT(CURDATE(), ' 07:48:00'), 1, 'Public transport vehicle breakdown (Matatu puncture)', NULL, 2, 'Late arrival recorded. Parent notified.'),
('student', 9, 'Dennis Korir', 'ADM-2024-009', 'in', 'card_scan', CONCAT(CURDATE(), ' 07:55:00'), 1, 'Morning family emergency at home', NULL, 2, 'Late arrival recorded. Parent notified.'),
('visitor', 1, 'Hassan Abdi Mohamed', 'BADGE-V-01', 'in', 'manual_search', CONCAT(CURDATE(), ' 08:15:00'), 0, NULL, NULL, 2, 'Visitor book check-in'),
('visitor', 2, 'Alice M. Kilonzo', 'BADGE-V-02', 'in', 'manual_search', CONCAT(CURDATE(), ' 08:30:00'), 0, NULL, NULL, 2, 'Visitor book check-in');

-- Parent Notifications (SMS/Email Alerts)
INSERT INTO `parent_notifications` (`student_id`, `student_name`, `parent_contact`, `channel`, `event_type`, `message`, `sent_at`, `status`) VALUES
(2, 'Faith Wanjiku', '+254 723 202 303', 'SMS', 'late_arrival', 'Dear Grace Wanjiku, your child Faith Wanjiku arrived late at St. Jude Academy at 07:42 AM. Reason noted: Heavy traffic delay on Eastern bypass.', CONCAT(CURDATE(), ' 07:42:05'), 'Delivered'),
(5, 'Daniel Otieno', '+254 756 505 606', 'SMS', 'late_arrival', 'Dear Patrick Otieno, your child Daniel Otieno arrived late at St. Jude Academy at 07:48 AM. Reason noted: Public transport vehicle breakdown.', CONCAT(CURDATE(), ' 07:48:10'), 'Delivered'),
(9, 'Dennis Korir', '+254 790 909 010', 'SMS', 'late_arrival', 'Dear Geoffrey Korir, your child Dennis Korir arrived late at St. Jude Academy at 07:55 AM. Reason noted: Morning family emergency at home.', CONCAT(CURDATE(), ' 07:55:04'), 'Delivered');
