CREATE DATABASE IF NOT EXISTS `depedsdois_leave` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `depedsdois_leave`;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NULL,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NULL,
  `role_id` INT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `first_login` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_users_employee` (`employee_id`),
  KEY `idx_users_role` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS `positions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `is_school_head` TINYINT(1) DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `schools` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `school_name` VARCHAR(255) NOT NULL,
  `school_head_id` INT NULL,
  `district` VARCHAR(150) NULL,
  `municipality` VARCHAR(150) NULL,
  `address` TEXT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `offices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `office_name` VARCHAR(255) NOT NULL,
  `office_head_id` INT NULL,
  `approver_id` INT NULL,
  `alternate_approver_id` INT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `employee_id_no` VARCHAR(100) NOT NULL UNIQUE,
  `personnel_id` VARCHAR(100) NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `middle_name` VARCHAR(100) NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `extension_name` VARCHAR(50) NULL,
  `position_id` INT NULL,
  `plantilla_position` VARCHAR(255) NULL,
  `salary_grade` VARCHAR(50) NULL,
  `employment_status` VARCHAR(100) NULL,
  `school_id` INT NULL,
  `office_id` INT NULL,
  `district` VARCHAR(150) NULL,
  `municipality` VARCHAR(150) NULL,
  `deped_email` VARCHAR(255) NULL,
  `personal_email` VARCHAR(255) NULL,
  `preferred_email` VARCHAR(255) NULL,
  `contact_number` VARCHAR(50) NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_employee_user` (`user_id`),
  KEY `idx_employee_school` (`school_id`),
  KEY `idx_employee_office` (`office_id`),
  KEY `idx_employee_position` (`position_id`),
  CONSTRAINT `fk_employees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_employees_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_employees_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_employees_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL
);

ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS `leave_types` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `holidays` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `holiday_name` VARCHAR(255) NOT NULL,
  `holiday_date` DATE NOT NULL,
  `holiday_type` VARCHAR(50) DEFAULT 'Regular',
  `status` TINYINT(1) DEFAULT 1,
  `recurring` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `leave_credits` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `leave_type_id` INT NOT NULL,
  `beginning_balance` DECIMAL(10,2) DEFAULT 0.00,
  `earned` DECIMAL(10,2) DEFAULT 0.00,
  `used` DECIMAL(10,2) DEFAULT 0.00,
  `remaining` DECIMAL(10,2) DEFAULT 0.00,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_leave_credits_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_leave_credits_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `leave_credit_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `leave_type_id` INT NOT NULL,
  `application_id` INT NULL,
  `transaction_type` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(10,2) DEFAULT 0.00,
  `remarks` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_credit_trans_employee` (`employee_id`),
  KEY `idx_credit_trans_type` (`leave_type_id`),
  CONSTRAINT `fk_credit_trans_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_credit_trans_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `leave_applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `application_no` VARCHAR(50) NOT NULL UNIQUE,
  `employee_id` INT NOT NULL,
  `leave_type_id` INT NULL,
  `leave_start_date` DATE NULL,
  `leave_end_date` DATE NULL,
  `number_of_days` DECIMAL(10,2) DEFAULT 0.00,
  `reason` TEXT NULL,
  `status` VARCHAR(50) DEFAULT 'DRAFT',
  `current_approver_id` INT NULL,
  `current_office_id` INT NULL,
  `date_submitted` DATETIME NULL,
  `date_approved` DATETIME NULL,
  `remarks` TEXT NULL,
  `is_draft` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_leave_app_employee` (`employee_id`),
  KEY `idx_leave_app_status` (`status`),
  CONSTRAINT `fk_leave_app_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_leave_app_leave_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS `leave_application_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT NOT NULL,
  `field_name` VARCHAR(100) NOT NULL,
  `field_value` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_app_detail_application` (`application_id`),
  CONSTRAINT `fk_app_detail_application` FOREIGN KEY (`application_id`) REFERENCES `leave_applications` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `approval_routes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NULL,
  `office_id` INT NULL,
  `school_id` INT NULL,
  `approver_user_id` INT NULL,
  `route_order` INT DEFAULT 1,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `approval_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT NOT NULL,
  `action_by` INT NULL,
  `status` VARCHAR(50) NOT NULL,
  `remarks` TEXT NULL,
  `ip_address` VARCHAR(50) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_approval_history_app` (`application_id`),
  CONSTRAINT `fk_history_application` FOREIGN KEY (`application_id`) REFERENCES `leave_applications` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `signatures` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `signature_image` VARCHAR(255) NULL,
  `signature_name` VARCHAR(255) NULL,
  `position` VARCHAR(255) NULL,
  `office` VARCHAR(255) NULL,
  `date_signed` DATETIME NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_signature_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `attachments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(100) NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `uploaded_by` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_attachment_application` FOREIGN KEY (`application_id`) REFERENCES `leave_applications` (`id`) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT NULL,
  `recipient_user_id` INT NULL,
  `message` TEXT NULL,
  `type` VARCHAR(100) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `email_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(100) NOT NULL UNIQUE,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `email_notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT NULL,
  `recipient` VARCHAR(255) NULL,
  `email_address` VARCHAR(255) NOT NULL,
  `notification_type` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(255) NULL,
  `content` TEXT NULL,
  `sent_at` DATETIME NULL,
  `delivery_status` VARCHAR(20) DEFAULT 'PENDING',
  `error_message` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT NULL,
  `application_id` INT NULL,
  `ip_address` VARCHAR(50) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `document_verifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT NOT NULL,
  `verification_code` VARCHAR(255) NOT NULL,
  `is_valid` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_verification_code` (`verification_code`),
  CONSTRAINT `fk_verification_application` FOREIGN KEY (`application_id`) REFERENCES `leave_applications` (`id`) ON DELETE CASCADE
);

INSERT INTO `roles` (`name`, `slug`) VALUES
('System Administrator', 'administrator'),
('Applicant', 'applicant'),
('School Head', 'school_head'),
('ASDS / Division Approver', 'asds_division_approver'),
('Office Approver', 'office_approver'),
('HR / Personnel Administrator', 'hr_personnel_administrator');

INSERT INTO `positions` (`title`, `is_school_head`) VALUES
('Teacher I', 0),
('School Principal I', 1),
('Assistant Schools Division Superintendent', 0),
('Administrative Aide', 0),
('HR Specialist', 0),
('Office Clerk', 0);

INSERT INTO `schools` (`school_name`, `district`, `municipality`, `address`, `status`) VALUES
('San Vicente Elementary School', 'District I', 'Santa', 'San Vicente, Santa, Ilocos Sur', 1),
('Cabugao National High School', 'District II', 'Cabugao', 'Cabugao, Ilocos Sur', 1),
('Office of the Schools Division Superintendent', 'Division Office', 'Vigan City', 'Vigan City, Ilocos Sur', 1);

INSERT INTO `offices` (`office_name`, `status`) VALUES
('Office of the Schools Division Superintendent', 1),
('Office of the Assistant Schools Division Superintendent', 1),
('Curriculum Implementation Division', 1),
('School Governance and Operations Division', 1),
('Personnel/HR', 1),
('Accounting', 1),
('General Services', 1);

INSERT INTO `leave_types` (`name`, `code`, `description`, `status`) VALUES
('Vacation Leave', 'VL', 'Vacation or annual leave', 1),
('Sick Leave', 'SL', 'Sick leave', 1),
('Mandatory/Forced Leave', 'ML', 'Mandatory/forced leave', 1),
('Maternity Leave', 'MTL', 'Maternity leave', 1),
('Paternity Leave', 'PTL', 'Paternity leave', 1),
('Special Privilege Leave', 'SPL', 'Special privilege leave', 1),
('Solo Parent Leave', 'SPLP', 'Solo parent leave', 1),
('Study Leave', 'STL', 'Study leave', 1),
('Rehabilitation Privilege', 'RP', 'Rehabilitation privilege', 1),
('Special Leave Benefits for Women', 'SLBW', 'Special leave for women', 1),
('Other Authorized Leave', 'OAL', 'Other authorized leave', 1);

INSERT INTO `email_templates` (`code`, `subject`, `body`, `status`) VALUES
('leave_submitted', 'Leave Application Submitted - {APPLICATION_NO}', 'Dear {APPLICANT_NAME},\n\nYour leave application {APPLICATION_NO} has been submitted and is now pending review.\n\nLeave Type: {LEAVE_TYPE}\nDates: {LEAVE_DATES}\nDays: {NUMBER_OF_DAYS}\nCurrent status: {CURRENT_STATUS}\nApprover: {CURRENT_APPROVER}\nOffice: {CURRENT_OFFICE}\n\nPlease login to track the status of your application.\n\n{APPLICATION_LINK}', 1),
('leave_approved', 'Leave Application Approved - {APPLICATION_NO}', 'Dear {APPLICANT_NAME},\n\nYour application {APPLICATION_NO} has been approved.\n\nLeave Type: {LEAVE_TYPE}\nDates: {LEAVE_DATES}\nDays: {NUMBER_OF_DAYS}\nApprover: {CURRENT_APPROVER}\nDate Approved: {DATE_APPROVED}\n\nView the record here: {APPLICATION_LINK}\nPDF: {PDF_LINK}', 1),
('leave_approval_request', 'Leave Application for Approval - {APPLICATION_NO}', 'Dear {CURRENT_APPROVER},\n\nA leave application for {APPLICANT_NAME} requires your review and action.\n\nApplication Number: {APPLICATION_NO}\nLeave Type: {LEAVE_TYPE}\nDates: {LEAVE_DATES}\nDays: {NUMBER_OF_DAYS}\nApplicant Office: {CURRENT_OFFICE}\n\nAction Required: Review and approve.\n\n{APPLICATION_LINK}', 1);

INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('sdo_name', 'Department of Education – Schools Division of Ilocos Sur'),
('sdo_address', 'Vigan City, Ilocos Sur'),
('application_prefix', 'LEAVE'),
('smtp_host', ''),
('smtp_port', '587'),
('smtp_username', ''),
('smtp_password', ''),
('smtp_encryption', 'tls'),
('smtp_from_name', 'DepEd SDO Ilocos Sur'),
('smtp_from_email', 'no-reply@deped.gov.ph'),
('email_enabled', '0');

INSERT INTO `users` (`username`, `password`, `email`, `role_id`, `status`) VALUES
('admin', 'password', 'admin@deped.gov.ph', 1, 1),
('schoolhead', 'password', 'schoolhead@deped.gov.ph', 3, 1),
('schoolpersonnel', 'password', 'teacher@deped.gov.ph', 2, 1),
('asds', 'password', 'asds@deped.gov.ph', 4, 1),
('officeapprover', 'password', 'officeapprover@deped.gov.ph', 5, 1),
('hradmin', 'password', 'hradmin@deped.gov.ph', 6, 1);

INSERT INTO `employees` (`user_id`, `employee_id_no`, `personnel_id`, `first_name`, `middle_name`, `last_name`, `position_id`, `plantilla_position`, `salary_grade`, `employment_status`, `school_id`, `office_id`, `district`, `municipality`, `deped_email`, `personal_email`, `preferred_email`, `contact_number`, `status`) VALUES
(1, 'EMP-001', 'PERS-001', 'System', 'Admin', 'User', 5, 'HR Specialist', 'SG-18', 'Permanent', NULL, 5, 'Division Office', 'Vigan City', 'admin@deped.gov.ph', 'admin@gmail.com', 'admin@deped.gov.ph', '09170000001', 1),
(2, 'EMP-002', 'PERS-002', 'Maria', 'L.', 'Santos', 2, 'School Principal I', 'SG-18', 'Permanent', 1, NULL, 'District I', 'Santa', 'schoolhead@deped.gov.ph', 'principal@gmail.com', 'schoolhead@deped.gov.ph', '09170000002', 1),
(3, 'EMP-003', 'PERS-003', 'Juan', 'D.', 'Dela Cruz', 1, 'Teacher I', 'SG-11', 'Permanent', 1, NULL, 'District I', 'Santa', 'teacher@deped.gov.ph', 'juan@gmail.com', 'teacher@deped.gov.ph', '09170000003', 1),
(4, 'EMP-004', 'PERS-004', 'Liza', 'A.', 'Villanueva', 3, 'Assistant Schools Division Superintendent', 'SG-24', 'Permanent', NULL, 2, 'Division Office', 'Vigan City', 'asds@deped.gov.ph', 'liza@gmail.com', 'asds@deped.gov.ph', '09170000004', 1),
(5, 'EMP-005', 'PERS-005', 'Pedro', 'M.', 'Ramos', 6, 'Office Clerk', 'SG-9', 'Permanent', NULL, 4, 'Division Office', 'Vigan City', 'officeapprover@deped.gov.ph', 'pedro@gmail.com', 'officeapprover@deped.gov.ph', '09170000005', 1),
(6, 'EMP-006', 'PERS-006', 'Rosa', 'N.', 'Aquino', 5, 'HR Specialist', 'SG-18', 'Permanent', NULL, 5, 'Division Office', 'Vigan City', 'hradmin@deped.gov.ph', 'rosa@gmail.com', 'hradmin@deped.gov.ph', '09170000006', 1);

UPDATE `users` SET `employee_id` = 1 WHERE `username` = 'admin';
UPDATE `users` SET `employee_id` = 2 WHERE `username` = 'schoolhead';
UPDATE `users` SET `employee_id` = 3 WHERE `username` = 'schoolpersonnel';
UPDATE `users` SET `employee_id` = 4 WHERE `username` = 'asds';
UPDATE `users` SET `employee_id` = 5 WHERE `username` = 'officeapprover';
UPDATE `users` SET `employee_id` = 6 WHERE `username` = 'hradmin';

INSERT INTO `approval_routes` (`employee_id`, `school_id`, `office_id`, `approver_user_id`, `route_order`) VALUES
(3, 1, NULL, 2, 1),
(2, 1, NULL, 4, 1),
(5, NULL, 4, 5, 1);

INSERT INTO `leave_credits` (`employee_id`, `leave_type_id`, `beginning_balance`, `earned`, `used`, `remaining`) VALUES
(3, 1, 10.00, 2.00, 0.00, 12.00),
(3, 2, 15.00, 2.00, 0.00, 17.00),
(2, 1, 10.00, 2.00, 0.00, 12.00),
(2, 2, 15.00, 2.00, 0.00, 17.00);

DELIMITER $$
CREATE TRIGGER `trg_update_user_employee_fk`
AFTER INSERT ON `employees`
FOR EACH ROW
BEGIN
  UPDATE `users` SET `employee_id` = NEW.id WHERE `id` = NEW.user_id;
END$$
DELIMITER ;
