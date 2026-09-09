-- ============================================================
-- BARANGAY MANAGEMENT SYSTEM (BarangayOS)
-- Relational MySQL Schema (v3.0 - PHP & MySQL Edition)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. USERS & AUTHENTICATION TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `role` ENUM('admin', 'staff', 'official') NOT NULL DEFAULT 'staff',
  `position` VARCHAR(100) DEFAULT 'Barangay Staff',
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. RESIDENTS & DEMOGRAPHIC PROFILE TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `residents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `resident_code` VARCHAR(30) NOT NULL UNIQUE,
  `first_name` VARCHAR(50) NOT NULL,
  `middle_name` VARCHAR(50) DEFAULT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `suffix` VARCHAR(10) DEFAULT NULL,
  `birthdate` DATE NOT NULL,
  `age` INT DEFAULT 0,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `civil_status` ENUM('Single', 'Married', 'Widowed', 'Separated', 'Divorced') NOT NULL DEFAULT 'Single',
  `occupation` VARCHAR(100) DEFAULT 'None',
  `purok` VARCHAR(50) NOT NULL,
  `street` VARCHAR(150) DEFAULT '',
  `contact_no` VARCHAR(30) DEFAULT '',
  `email` VARCHAR(100) DEFAULT '',
  `voter_status` ENUM('Registered', 'Unregistered') NOT NULL DEFAULT 'Unregistered',
  `is_4ps` TINYINT(1) NOT NULL DEFAULT 0,
  `is_indigent` TINYINT(1) NOT NULL DEFAULT 0,
  `is_pwd` TINYINT(1) NOT NULL DEFAULT 0,
  `is_solo_parent` TINYINT(1) NOT NULL DEFAULT 0,
  `is_senior` TINYINT(1) NOT NULL DEFAULT 0,
  `emergency_name` VARCHAR(100) DEFAULT '',
  `emergency_contact` VARCHAR(30) DEFAULT '',
  `photo_url` TEXT DEFAULT NULL,
  `status` ENUM('Active', 'Deceased', 'Relocated', 'Archived') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_residents_purok` (`purok`),
  INDEX `idx_residents_name` (`last_name`, `first_name`),
  INDEX `idx_residents_voter` (`voter_status`),
  INDEX `idx_residents_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. HOUSEHOLDS CENSUS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `households` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `household_no` VARCHAR(30) NOT NULL UNIQUE,
  `purok` VARCHAR(50) NOT NULL,
  `street` VARCHAR(150) DEFAULT '',
  `head_resident_id` INT DEFAULT NULL,
  `structure_type` VARCHAR(50) DEFAULT 'Concrete',
  `tenure_status` VARCHAR(50) DEFAULT 'Owned',
  `water_source` VARCHAR(50) DEFAULT 'Piped / Level 3',
  `toilet_facility` VARCHAR(50) DEFAULT 'Water-sealed',
  `power_source` VARCHAR(50) DEFAULT 'Grid Electric',
  `monthly_income` VARCHAR(50) DEFAULT 'Under 10,000',
  `hazard_risk` VARCHAR(50) DEFAULT 'Low',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_households_purok` (`purok`),
  CONSTRAINT `fk_households_head` FOREIGN KEY (`head_resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. HOUSEHOLD MEMBERS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `household_members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `household_id` INT NOT NULL,
  `resident_id` INT NOT NULL,
  `relationship` VARCHAR(50) NOT NULL DEFAULT 'Member',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_hm_household` (`household_id`),
  INDEX `idx_hm_resident` (`resident_id`),
  CONSTRAINT `fk_hm_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hm_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. CERTIFICATES & CLEARANCES TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `certificates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tracking_no` VARCHAR(40) NOT NULL UNIQUE,
  `resident_id` INT NOT NULL,
  `cert_type` VARCHAR(80) NOT NULL,
  `purpose` VARCHAR(200) NOT NULL,
  `or_no` VARCHAR(50) DEFAULT '',
  `amount_paid` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `is_waived` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('Active', 'Revoked', 'Expired') NOT NULL DEFAULT 'Active',
  `issued_by` VARCHAR(100) DEFAULT 'Barangay Secretary',
  `issued_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `qr_token` VARCHAR(64) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_cert_tracking` (`tracking_no`),
  INDEX `idx_cert_resident` (`resident_id`),
  INDEX `idx_cert_type` (`cert_type`),
  INDEX `idx_cert_status` (`status`),
  CONSTRAINT `fk_certificates_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. BLOTTER & PEACE AND ORDER CASES TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blotter_cases` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `case_no` VARCHAR(40) NOT NULL UNIQUE,
  `complainant_resident_id` INT DEFAULT NULL,
  `complainant_name` VARCHAR(100) NOT NULL,
  `respondent_resident_id` INT DEFAULT NULL,
  `respondent_name` VARCHAR(100) NOT NULL,
  `incident_type` VARCHAR(80) NOT NULL,
  `purok` VARCHAR(50) NOT NULL,
  `incident_date` DATE NOT NULL,
  `status` ENUM('Active Mediation', 'Hearing Scheduled', 'Amicably Settled', 'Escalated CFA') NOT NULL DEFAULT 'Active Mediation',
  `narrative` TEXT NOT NULL,
  `hearing_date` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_blotter_case_no` (`case_no`),
  INDEX `idx_blotter_status` (`status`),
  INDEX `idx_blotter_type` (`incident_type`),
  CONSTRAINT `fk_blotter_comp` FOREIGN KEY (`complainant_resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_blotter_resp` FOREIGN KEY (`respondent_resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 7. EMERGENCY INCIDENTS & DISPATCH TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `incidents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `incident_no` VARCHAR(40) NOT NULL UNIQUE,
  `type` VARCHAR(80) NOT NULL,
  `priority` VARCHAR(30) NOT NULL DEFAULT 'Routine',
  `caller_name` VARCHAR(100) NOT NULL,
  `caller_contact` VARCHAR(30) DEFAULT '',
  `location` VARCHAR(150) NOT NULL,
  `purok` VARCHAR(50) NOT NULL,
  `status` ENUM('Dispatched', 'On-Scene', 'Resolved', 'Referred') NOT NULL DEFAULT 'Dispatched',
  `narrative` TEXT DEFAULT NULL,
  `responder_name` VARCHAR(100) DEFAULT '',
  `vehicle_unit` VARCHAR(50) DEFAULT '',
  `minor_age` INT DEFAULT NULL,
  `guardian_name` VARCHAR(100) DEFAULT '',
  `guardian_contact` VARCHAR(30) DEFAULT '',
  `reported_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `on_scene_at` DATETIME DEFAULT NULL,
  `resolved_at` DATETIME DEFAULT NULL,
  `response_minutes` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_inc_no` (`incident_no`),
  INDEX `idx_inc_status` (`status`),
  INDEX `idx_inc_priority` (`priority`),
  INDEX `idx_inc_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 8. OFFICIALS & STAFF DIRECTORY TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `officials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `position` VARCHAR(100) NOT NULL,
  `category` ENUM('Elected', 'Appointive', 'Tanod / Security', 'Health Worker', 'SK Council') NOT NULL DEFAULT 'Appointive',
  `committee` VARCHAR(100) DEFAULT 'General Administration',
  `term_start` DATE DEFAULT NULL,
  `term_end` DATE DEFAULT NULL,
  `contact_no` VARCHAR(30) DEFAULT '',
  `email` VARCHAR(100) DEFAULT '',
  `photo_url` TEXT DEFAULT NULL,
  `rank_order` INT NOT NULL DEFAULT 99,
  `status` ENUM('Active', 'Inactive', 'On Leave', 'Term Ended') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_officials_position` (`position`),
  INDEX `idx_officials_rank` (`rank_order`),
  INDEX `idx_officials_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 9. AUDIT LOGS & SECURITY TRAIL TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `username` VARCHAR(50) DEFAULT 'System',
  `action` VARCHAR(80) NOT NULL,
  `entity` VARCHAR(50) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT '',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_audit_action` (`action`),
  INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 10. SYSTEM SETTINGS & BRANDING CONFIGURATION TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(60) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 11. PVC RESIDENT IDENTIFICATION CARDS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resident_ids` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_number` VARCHAR(40) NOT NULL UNIQUE,
  `resident_id` INT NOT NULL,
  `blood_type` VARCHAR(10) DEFAULT 'N/A',
  `emergency_name` VARCHAR(100) DEFAULT '',
  `emergency_contact` VARCHAR(30) DEFAULT '',
  `valid_until` DATE NOT NULL,
  `issued_by` VARCHAR(100) NOT NULL,
  `signatory_name` VARCHAR(100) NOT NULL,
  `status` ENUM('Active', 'Expired', 'Revoked', 'Replaced') NOT NULL DEFAULT 'Active',
  `photo_url` LONGTEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_resident_ids_resident` (`resident_id`),
  INDEX `idx_resident_ids_number` (`id_number`),
  INDEX `idx_resident_ids_status` (`status`),
  CONSTRAINT `fk_resident_ids_res` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 12. NOTIFICATIONS & SMS DISPATCH AUDIT TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `dispatch_code` VARCHAR(40) NOT NULL UNIQUE,
  `recipient_id` INT DEFAULT NULL,
  `recipient_name` VARCHAR(100) NOT NULL,
  `recipient_contact` VARCHAR(100) NOT NULL,
  `channel` ENUM('SMS', 'Email', 'Both') NOT NULL DEFAULT 'SMS',
  `category` ENUM('Clearance', 'Summons', 'Incident', 'Advisory', 'Relief', 'General') NOT NULL DEFAULT 'General',
  `subject` VARCHAR(150) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('Delivered', 'Sent', 'Queued', 'Failed') NOT NULL DEFAULT 'Delivered',
  `gateway_ref` VARCHAR(60) DEFAULT NULL,
  `cost_credits` INT NOT NULL DEFAULT 1,
  `error_message` VARCHAR(255) DEFAULT NULL,
  `dispatched_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_notif_code` (`dispatch_code`),
  INDEX `idx_notif_recipient` (`recipient_id`),
  INDEX `idx_notif_contact` (`recipient_contact`),
  INDEX `idx_notif_channel` (`channel`),
  INDEX `idx_notif_category` (`category`),
  INDEX `idx_notif_status` (`status`),
  INDEX `idx_notif_dispatched` (`dispatched_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 13. BARANGAY HEALTH STATION & CLINICAL RECORDS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `health_records` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `record_no` VARCHAR(40) NOT NULL UNIQUE,
  `resident_id` INT NOT NULL,
  `service_type` ENUM('General Consultation', 'Prenatal Checkup', 'Postnatal Care', 'Child Immunization', 'Nutrition OPT Plus', 'Senior Maintenance', 'Animal Bite / Rabies', 'First Aid / Wound Care') NOT NULL DEFAULT 'General Consultation',
  `bp` VARCHAR(20) DEFAULT NULL,
  `temperature` DECIMAL(4, 1) DEFAULT NULL,
  `weight_kg` DECIMAL(5, 2) DEFAULT NULL,
  `height_cm` DECIMAL(5, 2) DEFAULT NULL,
  `pulse_rate` INT DEFAULT NULL,
  `chief_complaint` TEXT NOT NULL,
  `clinical_notes` TEXT DEFAULT NULL,
  `medicines_dispensed` TEXT DEFAULT NULL,
  `attending_staff` VARCHAR(100) NOT NULL DEFAULT 'Barangay Health Worker',
  `status` ENUM('Completed', 'Follow-Up Needed', 'Referred to RHU / Hospital') NOT NULL DEFAULT 'Completed',
  `referral_target` VARCHAR(150) DEFAULT NULL,
  `follow_up_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_health_record_no` (`record_no`),
  INDEX `idx_health_resident` (`resident_id`),
  INDEX `idx_health_service` (`service_type`),
  INDEX `idx_health_status` (`status`),
  INDEX `idx_health_created` (`created_at`),
  CONSTRAINT `fk_health_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 14. HEALTH PHARMACY & MEDICINE INVENTORY TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `health_medicines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `medicine_name` VARCHAR(100) NOT NULL,
  `generic_name` VARCHAR(100) NOT NULL,
  `category` ENUM('Maintenance - Hypertension', 'Maintenance - Diabetes', 'Antibiotics', 'Analgesic / Fever', 'Pediatric / Vitamins', 'Oral Rehydration', 'First Aid') NOT NULL,
  `dosage` VARCHAR(50) NOT NULL DEFAULT '500mg',
  `stock_quantity` INT NOT NULL DEFAULT 100,
  `unit` VARCHAR(20) NOT NULL DEFAULT 'tablets',
  `reorder_level` INT NOT NULL DEFAULT 30,
  `expiry_date` DATE NOT NULL,
  `batch_no` VARCHAR(40) DEFAULT 'BATCH-2026',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_med_category` (`category`),
  INDEX `idx_med_expiry` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- DEFAULT MEDICINES SEED DATA
-- ------------------------------------------------------------
INSERT INTO `health_medicines` (`medicine_name`, `generic_name`, `category`, `dosage`, `stock_quantity`, `unit`, `reorder_level`, `expiry_date`, `batch_no`) VALUES
('Amlodipine Besylate', 'Amlodipine', 'Maintenance - Hypertension', '5mg', 350, 'tablets', 50, '2027-12-31', 'AML-2026-01'),
('Losartan Potassium', 'Losartan', 'Maintenance - Hypertension', '50mg', 280, 'tablets', 50, '2027-11-30', 'LOS-2026-02'),
('Metformin HCl', 'Metformin', 'Maintenance - Diabetes', '500mg', 320, 'tablets', 60, '2027-10-31', 'MET-2026-03'),
('Paracetamol', 'Paracetamol', 'Analgesic / Fever', '500mg', 500, 'tablets', 100, '2028-06-30', 'PAR-2026-04'),
('Amoxicillin Trihydrate', 'Amoxicillin', 'Antibiotics', '500mg', 180, 'capsules', 40, '2027-08-31', 'AMX-2026-05'),
('Oral Rehydration Salts', 'ORS / Hydrite', 'Oral Rehydration', '20.5g sachet', 140, 'sachets', 30, '2028-03-31', 'ORS-2026-06'),
('Ferrous Sulfate + Folic Acid', 'Iron + Folic', 'Pediatric / Vitamins', '60mg / 400mcg', 260, 'tablets', 50, '2027-09-30', 'FE-2026-07'),
('Ascorbic Acid (Vitamin C)', 'Vitamin C Syrup', 'Pediatric / Vitamins', '100mg/5mL 60mL', 90, 'bottles', 20, '2027-12-31', 'VIT-2026-08');


-- ------------------------------------------------------------
-- DEFAULT SETTINGS SEED DATA
-- ------------------------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('barangay_name', 'Barangay San Isidro'),
('municipality', 'Rodriguez (Montalban)'),
('province', 'Rizal'),
('hall_address', 'Barangay Hall Complex, J.P. Rizal St., San Isidro'),
('contact_no', '(02) 8987-6543 / 0917-123-4567'),
('email', 'info@sanisidro.gov.ph'),
('service_hours', 'Mon - Fri: 8:00 AM - 5:00 PM'),
('logo_url', ''),
('fee_clearance', '50.00'),
('fee_residency', '30.00'),
('fee_indigency', '0.00'),
('fee_business', '150.00'),
('waiver_policy', 'auto_waive_4ps_indigent'),
('sms_gateway_provider', 'simulation'),
('sms_sender_id', 'BRGY-OFFICE'),
('sms_credits_balance', '2450'),
('auto_notify_clearance', '1'),
('auto_notify_blotter', '1'),
('auto_notify_incident', '1')
ON DUPLICATE KEY UPDATE `setting_key`=`setting_key`;

SET FOREIGN_KEY_CHECKS = 1;

