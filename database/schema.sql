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
('email', 'info@sanisidro.local'),
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

-- ------------------------------------------------------------
-- 15. ANNUAL INVESTMENT PROGRAM & BUDGET ALLOCATIONS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `budget_allocations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fiscal_year` YEAR NOT NULL DEFAULT '2026',
  `fund_source` ENUM('General Fund', '20% Barangay Development Fund', '5% BDRRM Calamity Fund', '10% SK Youth Development Fund', '5% GAD Fund', '1% Senior / PWD Fund', '1% LCPC Fund') NOT NULL,
  `program_title` VARCHAR(200) NOT NULL,
  `implementing_committee` VARCHAR(150) NOT NULL DEFAULT 'Committee on Appropriations',
  `approved_budget` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `obligated_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_budget_year` (`fiscal_year`),
  INDEX `idx_budget_fund` (`fund_source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 16. BIDS & AWARDS COMMITTEE (BAC) PROCUREMENT PROJECTS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `procurement_projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pr_number` VARCHAR(40) NOT NULL UNIQUE,
  `po_number` VARCHAR(40) DEFAULT NULL UNIQUE,
  `project_title` VARCHAR(255) NOT NULL,
  `classification` ENUM('Goods & Supplies', 'Infrastructure Projects', 'Consulting Services') NOT NULL DEFAULT 'Goods & Supplies',
  `procurement_mode` ENUM('Small Value Procurement (SVP)', 'Competitive Public Bidding', 'Shopping', 'Emergency Cases', 'Direct Contracting') NOT NULL DEFAULT 'Small Value Procurement (SVP)',
  `fund_source` VARCHAR(100) NOT NULL DEFAULT '20% Barangay Development Fund',
  `budget_allocation_id` INT DEFAULT NULL,
  `abc_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `philgeps_ref` VARCHAR(60) DEFAULT NULL,
  `end_user_committee` VARCHAR(150) NOT NULL DEFAULT 'Committee on Infrastructure',
  `status` ENUM('PR Draft', 'Approved for Canvass', 'Canvass / RFQ Open', 'Bids Evaluated', 'Awarded / PO Issued', 'Delivered & Inspected', 'Completed', 'Cancelled') NOT NULL DEFAULT 'PR Draft',
  `winning_bidder` VARCHAR(200) DEFAULT NULL,
  `winning_amount` DECIMAL(12, 2) DEFAULT NULL,
  `date_awarded` DATE DEFAULT NULL,
  `target_delivery_days` INT NOT NULL DEFAULT 15,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_proc_pr` (`pr_number`),
  INDEX `idx_proc_po` (`po_number`),
  INDEX `idx_proc_status` (`status`),
  INDEX `idx_proc_fund` (`fund_source`),
  CONSTRAINT `fk_proc_budget` FOREIGN KEY (`budget_allocation_id`) REFERENCES `budget_allocations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 17. CANVASS QUOTATIONS & ABSTRACT OF BIDS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `procurement_bids` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `supplier_name` VARCHAR(200) NOT NULL,
  `tin_number` VARCHAR(50) DEFAULT NULL,
  `contact_person` VARCHAR(100) DEFAULT NULL,
  `contact_no` VARCHAR(50) DEFAULT NULL,
  `quotation_amount` DECIMAL(12, 2) NOT NULL,
  `compliance_status` ENUM('Responsive', 'Non-Responsive', 'Disqualified') NOT NULL DEFAULT 'Responsive',
  `ranking` INT DEFAULT 1,
  `remarks` TEXT DEFAULT NULL,
  `canvassed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_bid_project` (`project_id`),
  INDEX `idx_bid_status` (`compliance_status`),
  CONSTRAINT `fk_bid_project` FOREIGN KEY (`project_id`) REFERENCES `procurement_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- DEFAULT BUDGET ALLOCATIONS & PROCUREMENT SEED DATA
-- ------------------------------------------------------------
INSERT INTO `budget_allocations` (`fiscal_year`, `fund_source`, `program_title`, `implementing_committee`, `approved_budget`, `obligated_amount`) VALUES
('2026', '20% Barangay Development Fund', 'Barangay Street Lighting & Road Concreting Project', 'Committee on Infrastructure', 2500000.00, 485000.00),
('2026', '5% BDRRM Calamity Fund', 'Disaster Preparedness Equipment & Emergency Relief Stockpiling', 'BDRRMC / Committee on Peace & Order', 950000.00, 280000.00),
('2026', '10% SK Youth Development Fund', 'Annual Barangay Youth Sports League & Leadership Congress', 'Sangguniang Kabataan (SK)', 1200000.00, 310000.00),
('2026', '5% GAD Fund', 'Maternal & Reproductive Healthcare Outreach and Livelihood Training', 'Committee on Women & Family', 650000.00, 145000.00),
('2026', 'General Fund', 'Barangay Health Station Pharmaceutical & Clinic Supplies', 'Committee on Health & Sanitation', 800000.00, 240000.00),
('2026', '1% Senior / PWD Fund', 'Senior Citizens Wellness Kits & Maintenance Medicine Subsidy', 'Office of Senior Citizens Affairs (OSCA)', 350000.00, 110000.00);

INSERT INTO `procurement_projects` (`pr_number`, `po_number`, `project_title`, `classification`, `procurement_mode`, `fund_source`, `budget_allocation_id`, `abc_amount`, `philgeps_ref`, `end_user_committee`, `status`, `winning_bidder`, `winning_amount`, `date_awarded`, `target_delivery_days`) VALUES
('PR-2026-0041', 'PO-2026-0019', 'Supply & Installation of 60W Integrated Solar LED Streetlights (Purok 1-4)', 'Infrastructure Projects', 'Small Value Procurement (SVP)', '20% Barangay Development Fund', 1, 485000.00, 'PHILGEPS-2026-98124', 'Committee on Infrastructure', 'Awarded / PO Issued', 'Luzon Green Energy Solutions Corp.', 478500.00, '2026-08-15', 30),
('PR-2026-0042', 'PO-2026-0020', 'Procurement of Essential Maintenance Medicines & Clinic Supplies', 'Goods & Supplies', 'Shopping', 'General Fund', 5, 240000.00, 'PHILGEPS-2026-98311', 'Committee on Health & Sanitation', 'Delivered & Inspected', 'Metro Pharma Distribution Inc.', 234200.00, '2026-08-20', 15),
('PR-2026-0043', NULL, 'Emergency Disaster Relief Food Packs & Hygiene Kits (500 Family Packs)', 'Goods & Supplies', 'Emergency Cases', '5% BDRRM Calamity Fund', 2, 280000.00, 'PHILGEPS-2026-98502', 'BDRRMC / Peace & Order', 'Canvass / RFQ Open', NULL, NULL, NULL, 7);

INSERT INTO `procurement_bids` (`project_id`, `supplier_name`, `tin_number`, `contact_person`, `contact_no`, `quotation_amount`, `compliance_status`, `ranking`, `remarks`) VALUES
(1, 'Luzon Green Energy Solutions Corp.', '234-567-890-000', 'Engr. Dennis Santos', '0917-555-4321', 478500.00, 'Responsive', 1, 'Lowest Calculated and Responsive Bid (LCRB) complying with technical specifications.'),
(1, 'Solaria Philippines Industrial Inc.', '345-678-901-000', 'Ms. Rachel Tan', '0918-666-7890', 482000.00, 'Responsive', 2, 'Compliant second lowest quotation.'),
(1, 'Apex Tech Power Solutions', '456-789-012-000', 'Mr. Kevin Cruz', '0920-777-1234', 484900.00, 'Responsive', 3, 'Compliant quotation within ABC limit.'),
(2, 'Metro Pharma Distribution Inc.', '123-456-789-001', 'Dr. Arlene Ramos', '0917-888-2345', 234200.00, 'Responsive', 1, 'LCRB with FDA compliance certificates submitted.'),
(2, 'San Isidro Community Drug Distributor', '234-567-890-002', 'Mr. Vicente Gomez', '0919-999-3456', 238000.00, 'Responsive', 2, 'Complying second lowest quote.'),
(2, 'Pharmasure Health Supply Co.', '345-678-901-003', 'Ms. Teresa Lim', '0922-111-4567', 239500.00, 'Responsive', 3, 'Complying quotation within ABC.');

-- ------------------------------------------------------------
-- 18. SANGGUNIANG BARANGAY LEGISLATIVE DOCUMENTS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `legislative_documents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `doc_type` ENUM('Barangay Ordinance', 'Barangay Resolution', 'Executive Order') NOT NULL DEFAULT 'Barangay Ordinance',
  `control_number` VARCHAR(50) NOT NULL UNIQUE,
  `title` VARCHAR(255) NOT NULL,
  `sponsor_name` VARCHAR(150) NOT NULL DEFAULT 'Hon. Rafael G. Dizon',
  `co_sponsors` TEXT DEFAULT NULL,
  `committee` VARCHAR(150) NOT NULL DEFAULT 'Committee on Rules & Ethics',
  `reading_stage` ENUM('First Reading', 'Committee Hearing', 'Second Reading', 'Third & Final Reading', 'Enacted / Approved', 'Disapproved', 'Under Sangguniang Panlungsod Review') NOT NULL DEFAULT 'Enacted / Approved',
  `date_enacted` DATE DEFAULT NULL,
  `date_posted` DATE DEFAULT NULL,
  `effectivity_date` DATE DEFAULT NULL,
  `city_council_review_status` ENUM('Pending Review', 'Declared Operative / Valid', 'Returned with Comments', 'Disapproved / Ultra Vires') NOT NULL DEFAULT 'Declared Operative / Valid',
  `sanctions_penalties` TEXT DEFAULT NULL,
  `document_body` LONGTEXT DEFAULT NULL,
  `status` ENUM('Draft', 'Active / In Effect', 'Repealed / Amended', 'Archived') NOT NULL DEFAULT 'Active / In Effect',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_leg_control` (`control_number`),
  INDEX `idx_leg_type` (`doc_type`),
  INDEX `idx_leg_stage` (`reading_stage`),
  INDEX `idx_leg_status` (`status`),
  INDEX `idx_leg_committee` (`committee`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 19. SANGGUNIANG BARANGAY SESSIONS & MINUTES TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `legislative_sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `session_number` VARCHAR(50) NOT NULL UNIQUE,
  `session_type` ENUM('Regular Session', 'Special Session', 'Committee Hearing', 'Public Consultation') NOT NULL DEFAULT 'Regular Session',
  `session_date` DATE NOT NULL,
  `session_time` TIME NOT NULL DEFAULT '09:00:00',
  `presiding_officer` VARCHAR(150) NOT NULL DEFAULT 'Hon. Antonio S. Valdez',
  `quorum_status` ENUM('Quorum Present', 'No Quorum') NOT NULL DEFAULT 'Quorum Present',
  `present_count` INT NOT NULL DEFAULT 8,
  `total_members` INT NOT NULL DEFAULT 9,
  `agenda_topics` TEXT DEFAULT NULL,
  `minutes_summary` LONGTEXT DEFAULT NULL,
  `session_status` ENUM('Scheduled', 'In Progress', 'Adjourned', 'Cancelled') NOT NULL DEFAULT 'Adjourned',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_sess_number` (`session_number`),
  INDEX `idx_sess_date` (`session_date`),
  INDEX `idx_sess_type` (`session_type`),
  INDEX `idx_sess_status` (`session_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- DEFAULT LEGISLATIVE SEED DATA (RA 7160 COMPLIANT)
-- ------------------------------------------------------------
INSERT INTO `legislative_documents` (`doc_type`, `control_number`, `title`, `sponsor_name`, `co_sponsors`, `committee`, `reading_stage`, `date_enacted`, `date_posted`, `effectivity_date`, `city_council_review_status`, `sanctions_penalties`, `document_body`, `status`) VALUES
('Barangay Ordinance', 'ORD-2026-001', 'Comprehensive Ecological Solid Waste Management & Mandatory Waste Segregation-at-Source Ordinance', 'Hon. Benjamin Alcantara', 'Hon. Teresa Morales, Hon. Carlos Santos', 'Committee on Environment & Sanitation', 'Enacted / Approved', '2026-02-15', '2026-02-18', '2026-03-05', 'Declared Operative / Valid', '1st Offense: Reprimand & 4h community service; 2nd Offense: PHP 500 fine; 3rd Offense: PHP 1,000 fine & blotter entry.', 'AN ORDINANCE INSTITUTIONALIZING MANDATORY SOLID WASTE SEGREGATION-AT-SOURCE, REGULATING SINGLE-USE PLASTICS, AND ESTABLISHING PUROK MATERIAL RECOVERY FACILITIES (MRF) PURSUANT TO REPUBLIC ACT NO. 9003.', 'Active / In Effect'),
('Barangay Ordinance', 'ORD-2026-002', 'Barangay Child Protection & Curfew Hours Regulation for Minors (10:00 PM to 4:00 AM)', 'Hon. Teresa B. Morales', 'Hon. Rafael Dizon, Hon. Joshua Hernandez (SK)', 'Committee on Peace & Order', 'Enacted / Approved', '2026-03-10', '2026-03-12', '2026-03-27', 'Declared Operative / Valid', '1st Offense: Minor escorted home & parental warning; 2nd Offense: Parental counseling with BCPC; 3rd Offense: 8h community service.', 'AN ORDINANCE PRESCRIBING CURFEW HOURS FOR UNACCOMPANIED MINORS FROM 10:00 PM TO 4:00 AM TO PROMOTE CHILD PROTECTION AND CRIME PREVENTION PURSUANT TO RA 7160 AND RA 9344.', 'Active / In Effect'),
('Barangay Ordinance', 'ORD-2026-003', 'Mandatory Anti-Rabies Vaccination, Pet Registration, and Stray Animal Control Ordinance', 'Hon. Carlos M. Santos', 'Hon. Benjamin Alcantara', 'Committee on Agriculture & Animal Welfare', 'Enacted / Approved', '2026-07-20', '2026-07-22', '2026-08-06', 'Under Sangguniang Panlungsod Review', 'Failure to register/vaccinate dog: PHP 500; Stray dog impounding redemption fee: PHP 300.', 'AN ORDINANCE REQUIRING ANNUAL ANTI-RABIES VACCINATION AND BARANGAY REGISTRATION OF CANINE AND FELINE PETS, REGULATING STRAY ANIMALS PURSUANT TO RA 9482 (ANTI-RABIES ACT OF 2007).', 'Active / In Effect'),
('Barangay Resolution', 'RES-2026-014', 'A Resolution Approving and Adopting the Annual Investment Program (AIP) for Fiscal Year 2026 amounting to PHP 8,450,000.00', 'Hon. Rafael G. Dizon', 'All Sangguniang Barangay Members', 'Committee on Appropriations', 'Enacted / Approved', '2026-01-15', '2026-01-18', '2026-01-18', 'Declared Operative / Valid', 'N/A - Appropriation Measure', 'A RESOLUTION FORMALLY ADOPTING AND ENDORSING THE ANNUAL INVESTMENT PROGRAM (AIP) AND 20% BARANGAY DEVELOPMENT FUND (BDF) BUDGET FOR CALENDAR YEAR 2026.', 'Active / In Effect'),
('Barangay Resolution', 'RES-2026-015', 'A Resolution Authorizing the Punong Barangay to Enter into a MOA with the Department of Health (DOH) for Health Station Modernization', 'Hon. Elena Ramos', 'Hon. Teresa Morales', 'Committee on Health & Sanitation', 'Enacted / Approved', '2026-05-12', '2026-05-15', '2026-05-15', 'Declared Operative / Valid', 'N/A', 'A RESOLUTION GRANTING SPECIAL AUTHORITY TO HON. ANTONIO S. VALDEZ TO SIGN THE PRIMARY CARE CLINIC UPGRADE MEMORANDUM OF AGREEMENT WITH THE DOH REGIONAL OFFICE.', 'Active / In Effect');

INSERT INTO `legislative_sessions` (`session_number`, `session_type`, `session_date`, `session_time`, `presiding_officer`, `quorum_status`, `present_count`, `total_members`, `agenda_topics`, `minutes_summary`, `session_status`) VALUES
('RS-2026-015', 'Regular Session', '2026-08-10', '09:00:00', 'Hon. Antonio S. Valdez', 'Quorum Present', 8, 9, '1. Review of Ecological Solid Waste segregation monitoring\n2. Requisition of solar streetlighting procurement\n3. Katarungang Pambarangay case updates', 'Session convened at 9:00 AM with 8 of 9 council members present (Quorum certified by Secretary). Committee on Environment reported 88% household compliance with segregation in Purok 1-3. Council unanimously approved BAC resolution for solar streetlighting PR-2026-0041. Session adjourned at 11:45 AM.', 'Adjourned'),
('RS-2026-016', 'Regular Session', '2026-08-24', '09:00:00', 'Hon. Antonio S. Valdez', 'Quorum Present', 9, 9, '1. Anti-Rabies pet registration drive progress\n2. Review of Sangguniang Bayan feedback on Curfew Ordinance\n3. BDRRMC Monsoon preparedness protocol', 'Session called to order at 9:02 AM with complete attendance (9 of 9 members). Sangguniang Bayan official certification declaring Ordinance No. 2026-002 operative was entered into the official journal. BDRRMC presented relief stockpile inventory. Meeting adjourned at 12:15 PM.', 'Adjourned');

-- ------------------------------------------------------------
-- 20. LUPONG TAGAPAMAYAPA MEMBERS ROSTER TABLE (RA 7160 SEC. 399)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lupon_members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `resident_id` INT DEFAULT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `committee_assignment` VARCHAR(100) NOT NULL DEFAULT 'Conciliation Panel',
  `profession_background` VARCHAR(150) DEFAULT 'Community Elder',
  `contact_no` VARCHAR(50) DEFAULT '',
  `appointment_date` DATE NOT NULL,
  `oath_date` DATE DEFAULT NULL,
  `status` ENUM('Active', 'Inactive', 'On Leave') NOT NULL DEFAULT 'Active',
  `cases_handled_count` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_lupon_name` (`full_name`),
  INDEX `idx_lupon_status` (`status`),
  CONSTRAINT `fk_lupon_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 21. KATARUNGANG PAMBARANGAY (KP) DISPUTE CASES TABLE (SEC. 408-418)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lupon_cases` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `case_number` VARCHAR(50) NOT NULL UNIQUE,
  `blotter_case_id` INT DEFAULT NULL,
  `complainant_name` VARCHAR(150) NOT NULL,
  `complainant_address` VARCHAR(255) NOT NULL,
  `complainant_contact` VARCHAR(50) DEFAULT '',
  `respondent_name` VARCHAR(150) NOT NULL,
  `respondent_address` VARCHAR(255) NOT NULL,
  `respondent_contact` VARCHAR(50) DEFAULT '',
  `dispute_type` VARCHAR(100) NOT NULL,
  `complaint_details` TEXT NOT NULL,
  `relief_sought` TEXT DEFAULT NULL,
  `date_filed` DATE NOT NULL,
  `stage` ENUM('PB Mediation', 'Pangkat Conciliation', 'Arbitrated', 'Amicably Settled', 'Repudiated', 'CFA Issued', 'Dismissed') NOT NULL DEFAULT 'PB Mediation',
  `pangkat_chairman` VARCHAR(150) DEFAULT NULL,
  `pangkat_secretary` VARCHAR(150) DEFAULT NULL,
  `pangkat_member` VARCHAR(150) DEFAULT NULL,
  `pb_deadline` DATE DEFAULT NULL,
  `pangkat_deadline` DATE DEFAULT NULL,
  `settlement_terms` TEXT DEFAULT NULL,
  `settlement_date` DATE DEFAULT NULL,
  `settlement_amount` DECIMAL(10, 2) DEFAULT 0.00,
  `compliance_due_date` DATE DEFAULT NULL,
  `cfa_reason` VARCHAR(255) DEFAULT NULL,
  `cfa_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_kp_case_no` (`case_number`),
  INDEX `idx_kp_stage` (`stage`),
  INDEX `idx_kp_type` (`dispute_type`),
  INDEX `idx_kp_date` (`date_filed`),
  CONSTRAINT `fk_kp_blotter` FOREIGN KEY (`blotter_case_id`) REFERENCES `blotter_cases` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 22. KP MEDIATION & CONCILIATION HEARINGS DOCKET
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lupon_hearings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `case_id` INT NOT NULL,
  `hearing_number` VARCHAR(20) NOT NULL DEFAULT '1st Hearing',
  `hearing_type` ENUM('PB Mediation Hearing', 'Pangkat Conciliation Hearing', 'Arbitration Proceeding') NOT NULL DEFAULT 'PB Mediation Hearing',
  `scheduled_date` DATE NOT NULL,
  `scheduled_time` TIME NOT NULL DEFAULT '14:00:00',
  `venue` VARCHAR(150) NOT NULL DEFAULT 'Barangay Hall Mediation Room',
  `presiding_officer` VARCHAR(150) NOT NULL DEFAULT 'Hon. Antonio S. Valdez',
  `complainant_present` TINYINT(1) NOT NULL DEFAULT 1,
  `respondent_present` TINYINT(1) NOT NULL DEFAULT 1,
  `proceedings_summary` TEXT DEFAULT NULL,
  `next_action` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_hearing_case` (`case_id`),
  INDEX `idx_hearing_date` (`scheduled_date`),
  CONSTRAINT `fk_hearing_case` FOREIGN KEY (`case_id`) REFERENCES `lupon_cases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- DEFAULT LUPONG TAGAPAMAYAPA SEED DATA (RA 7160 COMPLIANT)
-- ------------------------------------------------------------
INSERT INTO `lupon_members` (`full_name`, `committee_assignment`, `profession_background`, `contact_no`, `appointment_date`, `oath_date`, `status`, `cases_handled_count`) VALUES
('Atty. Ernesto M. Salcedo', 'Legal & Arbitration Committee', 'Retired Labor Arbiter / Attorney', '0917-234-5678', '2025-01-10', '2025-01-12', 'Active', 14),
('Prof. Lydia V. Gonzaga', 'Family & Neighborhood Conciliation', 'Retired Public School Principal', '0918-345-6789', '2025-01-10', '2025-01-12', 'Active', 19),
('Engr. Danilo R. Fernandez', 'Boundary & Property Disputes', 'Retired Civil Engineer & Geodetic Surveyor', '0920-456-7890', '2025-01-10', '2025-01-12', 'Active', 11),
('Pastor Manuel S. De Jesus', 'Moral Guidance & Youth Restitution', 'Community Pastor / BCPC Member', '0922-567-8901', '2025-01-10', '2025-01-12', 'Active', 16),
('Mrs. Corazon F. Mercado', 'Financial Obligations & Tenancy Panel', 'Retired Bank Branch Manager', '0927-678-9012', '2025-01-10', '2025-01-12', 'Active', 22),
('Mr. Rolando T. Navarro', 'Conciliation Panel', 'Barangay Senior Citizen Association Officer', '0919-789-0123', '2025-01-10', '2025-01-12', 'Active', 8),
('Dr. Alicia B. Soriano', 'Public Health & Sanitation Disputes', 'Retired Community Physician', '0915-890-1234', '2025-01-10', '2025-01-12', 'Active', 9),
('Mr. Felipe K. Pangilinan', 'Commercial & Market Stall Disputes', 'Local Business Owners Association Lead', '0916-901-2345', '2025-01-10', '2025-01-12', 'Active', 13),
('Ms. Beatriz N. Villanueva', 'Women & Children Crisis Liaison', 'Registered Social Worker (RSW)', '0928-012-3456', '2025-01-10', '2025-01-12', 'Active', 17),
('Mr. Virgilio C. Santos', 'Conciliation Panel', 'Former Kagawad on Peace & Order', '0939-123-4567', '2025-01-10', '2025-01-12', 'Active', 15);

INSERT INTO `lupon_cases` (`case_number`, `blotter_case_id`, `complainant_name`, `complainant_address`, `complainant_contact`, `respondent_name`, `respondent_address`, `respondent_contact`, `dispute_type`, `complaint_details`, `relief_sought`, `date_filed`, `stage`, `pangkat_chairman`, `pangkat_secretary`, `pangkat_member`, `pb_deadline`, `pangkat_deadline`, `settlement_terms`, `settlement_date`, `settlement_amount`, `compliance_due_date`, `cfa_reason`, `cfa_date`) VALUES
('KP-2026-0001', 1, 'Maria Santos y Dela Cruz', 'House 14, Block 2, Mabini St., Purok 3', '0917-555-0101', 'Pedro Reyes y Alcantara', 'House 88, Sampaguita St., Purok 5', '0918-555-0202', 'Unpaid Debt / Financial Obligation', 'Complainant lent respondent the sum of PHP 15,000.00 on August 15, 2025 under a handwritten promissory note due on December 15, 2025. Despite repeated verbal and written demands, respondent has failed and refused to pay the outstanding obligation.', 'Full repayment of the principal loan amount of PHP 15,000.00 without interest.', '2026-01-10', 'Amicably Settled', 'Prof. Lydia V. Gonzaga', 'Mrs. Corazon F. Mercado', 'Mr. Rolando T. Navarro', '2026-01-25', '2026-02-10', 'Respondent agrees to settle the full obligation of PHP 15,000.00 in three (3) monthly installments of PHP 5,000.00 every 15th of February, March, and April 2026 payable at the Barangay Treasury. Failure to pay any installment shall make the entire unpaid balance immediately due and demandable with execution pursuant to RA 7160 Sec. 417.', '2026-01-22', 15000.00, '2026-04-15', NULL, NULL),
('KP-2026-0002', 3, 'Rodrigo M. Garcia', 'Lot 12, Purok 1 Riverside Drive', '0919-555-0303', 'Eduardo T. Mendoza', 'Lot 13, Purok 1 Riverside Drive', '0920-555-0404', 'Property & Boundary Dispute', 'Respondent erected an unauthorized concrete perimeter wall extending 0.65 meters into complainant registered property line, obstructing rainwater drainage and causing recurring flash ponding during downpours.', 'Demolition/removal of encroaching concrete boundary fence and restoration of common drainage right-of-way.', '2026-02-05', 'Pangkat Conciliation', 'Engr. Danilo R. Fernandez', 'Atty. Ernesto M. Salcedo', 'Mr. Virgilio C. Santos', '2026-02-20', '2026-03-20', NULL, NULL, 0.00, NULL, NULL, NULL),
('KP-2026-0003', NULL, 'Gemma L. Rivera', '142 Rizal Ave., Purok 4', '0922-555-0505', 'Marilou P. Dimaculangan', '148 Rizal Ave., Purok 4', '0923-555-0606', 'Verbal Defamation & Slander', 'Respondent publicly shouted malicious and defamatory remarks accusing complainant of misappropriating homeowners association funds during a purok gathering on February 28, 2026, causing public humiliation.', 'Public retraction of defamatory accusations, written apology before the Lupon, and undertaking to cease further slander.', '2026-03-02', 'PB Mediation', NULL, NULL, NULL, '2026-03-17', NULL, NULL, NULL, 0.00, NULL, NULL, NULL),
('KP-2026-0004', 2, 'Kagawad Ramon Santos', 'Barangay Compound, Purok 2', '0917-555-0707', 'Antonio B. Macaraeg', 'Purok 6 Annex Commercial Strip', '0928-555-0808', 'Noise Disturbance & Public Nuisance', 'Respondent operates an outdoor commercial videoke bar operating beyond 10:00 PM in direct violation of Barangay Curfew & Anti-Noise Ordinance No. 2026-002, disrupting resting families and students.', 'Compliance with ordinance operating hours and relocation of speakers indoors.', '2026-01-18', 'CFA Issued', 'Atty. Ernesto M. Salcedo', 'Ms. Beatriz N. Villanueva', 'Mr. Felipe K. Pangilinan', '2026-02-02', '2026-03-04', NULL, NULL, 0.00, NULL, 'Willful failure of respondent to appear before the Punong Barangay and Pangkat despite three (3) consecutive duly served summonses without valid cause.', '2026-03-05');

INSERT INTO `lupon_hearings` (`case_id`, `hearing_number`, `hearing_type`, `scheduled_date`, `scheduled_time`, `venue`, `presiding_officer`, `complainant_present`, `respondent_present`, `proceedings_summary`, `next_action`) VALUES
(1, '1st Hearing', 'PB Mediation Hearing', '2026-01-15', '14:00:00', 'Barangay Hall Mediation Room', 'Hon. Antonio S. Valdez', 1, 1, 'Both parties appeared. Complainant presented promissory note dated Aug 15, 2025. Respondent acknowledged debt but requested installment arrangement due to business slowdown.', 'PB suggested 3-installment plan; parties referred to drafting of Amicable Settlement.'),
(1, '2nd Hearing', 'PB Mediation Hearing', '2026-01-22', '14:30:00', 'Barangay Hall Mediation Room', 'Hon. Antonio S. Valdez', 1, 1, 'Parties formally executed KP Form 16 (Kasunduang Pag-aayos). Initial installment scheduled for February 15, 2026.', 'Case closed as Amicably Settled. Secretary to monitor compliance.'),
(2, '1st Hearing', 'PB Mediation Hearing', '2026-02-12', '10:00:00', 'Barangay Hall Session Hall', 'Hon. Antonio S. Valdez', 1, 1, 'Parties appeared with lot sketch plans. Disagreement on boundary stones. PB mediation unable to reach compromise within 15-day window.', 'Referred to Pangkat ng Tagapagkasundo. Parties selected Engr. Danilo Fernandez, Atty. Ernesto Salcedo, and Virgilio Santos.'),
(2, '2nd Hearing', 'Pangkat Conciliation Hearing', '2026-03-02', '14:00:00', 'Purok 1 Riverside Site & Lupon Office', 'Engr. Danilo R. Fernandez', 1, 1, 'Pangkat conducted ocular relocation survey. Verified 0.52-meter encroachment by respondent perimeter wall into natural watercourse.', 'Pangkat drafted proposed amicable realignment. Final conciliation session set for next week.');

-- ------------------------------------------------------------
-- 23. DISBURSEMENT VOUCHERS & EXPENDITURE TRACKING TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `disbursement_vouchers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `dv_number` VARCHAR(40) NOT NULL UNIQUE,
  `budget_allocation_id` INT DEFAULT NULL,
  `payee_name` VARCHAR(200) NOT NULL,
  `particulars` TEXT NOT NULL,
  `fund_source` ENUM('General Fund', '20% Barangay Development Fund', '5% BDRRM Calamity Fund', '10% SK Youth Development Fund', '5% GAD Fund', '1% Senior / PWD Fund', '1% LCPC Fund') NOT NULL DEFAULT 'General Fund',
  `amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `check_no` VARCHAR(50) DEFAULT NULL,
  `check_date` DATE DEFAULT NULL,
  `bank_name` VARCHAR(100) DEFAULT 'Land Bank of the Philippines',
  `expense_class` ENUM('Personal Services', 'MOOE', 'Capital Outlay') NOT NULL DEFAULT 'MOOE',
  `certified_by` VARCHAR(150) NOT NULL DEFAULT 'Maria Santos - Barangay Treasurer',
  `approved_by` VARCHAR(150) NOT NULL DEFAULT 'Hon. Antonio S. Valdez - Punong Barangay',
  `status` ENUM('Draft', 'Certified', 'Approved', 'Released', 'Cancelled') NOT NULL DEFAULT 'Draft',
  `released_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_dv_number` (`dv_number`),
  INDEX `idx_dv_fund` (`fund_source`),
  INDEX `idx_dv_status` (`status`),
  INDEX `idx_dv_expense` (`expense_class`),
  INDEX `idx_dv_created` (`created_at`),
  CONSTRAINT `fk_dv_budget` FOREIGN KEY (`budget_allocation_id`) REFERENCES `budget_allocations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 24. REVENUE COLLECTIONS & OFFICIAL RECEIPTS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `revenue_collections` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `or_number` VARCHAR(40) NOT NULL UNIQUE,
  `rcd_number` VARCHAR(40) DEFAULT NULL,
  `payer_name` VARCHAR(200) NOT NULL,
  `revenue_source` ENUM('Clearance Fees', 'Business Permits', 'Rental Income', 'IRA Share', 'Real Property Tax Share', 'Donations & Grants', 'Other Local Revenue') NOT NULL DEFAULT 'Clearance Fees',
  `particulars` TEXT DEFAULT NULL,
  `amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `fund_destination` ENUM('General Fund', '20% Barangay Development Fund', '5% BDRRM Calamity Fund', '10% SK Youth Development Fund', '5% GAD Fund', '1% Senior / PWD Fund', '1% LCPC Fund') NOT NULL DEFAULT 'General Fund',
  `collected_by` VARCHAR(150) NOT NULL DEFAULT 'Maria Santos - Barangay Treasurer',
  `receipt_date` DATE NOT NULL,
  `deposit_date` DATE DEFAULT NULL,
  `deposit_bank` VARCHAR(100) DEFAULT NULL,
  `deposit_slip_no` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('Collected', 'Deposited', 'Remitted', 'Voided') NOT NULL DEFAULT 'Collected',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_rc_or` (`or_number`),
  INDEX `idx_rc_source` (`revenue_source`),
  INDEX `idx_rc_fund` (`fund_destination`),
  INDEX `idx_rc_status` (`status`),
  INDEX `idx_rc_date` (`receipt_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 25. BUDGET OBLIGATION REQUESTS (OBR) TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `budget_obligations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `obr_number` VARCHAR(40) NOT NULL UNIQUE,
  `budget_allocation_id` INT DEFAULT NULL,
  `obligation_type` ENUM('Purchase Order', 'Contract', 'Payroll', 'Utility', 'Other') NOT NULL DEFAULT 'Purchase Order',
  `obligee_name` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `date_obligated` DATE NOT NULL,
  `status` ENUM('Pending', 'Approved', 'Disbursed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_obr_number` (`obr_number`),
  INDEX `idx_obr_budget` (`budget_allocation_id`),
  INDEX `idx_obr_status` (`status`),
  INDEX `idx_obr_type` (`obligation_type`),
  CONSTRAINT `fk_obr_budget` FOREIGN KEY (`budget_allocation_id`) REFERENCES `budget_allocations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 26. FINANCIAL REPORTS & STATEMENT SNAPSHOTS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `financial_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `report_code` VARCHAR(40) NOT NULL UNIQUE,
  `report_type` ENUM('Statement of Receipts & Expenditures', 'Annual Budget Report', 'Quarterly Financial Report', 'Fund Utilization Report') NOT NULL DEFAULT 'Statement of Receipts & Expenditures',
  `fiscal_year` YEAR NOT NULL DEFAULT '2026',
  `period_label` VARCHAR(50) NOT NULL,
  `total_receipts` DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
  `total_expenditures` DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
  `net_balance` DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
  `report_data` LONGTEXT DEFAULT NULL,
  `generated_by` VARCHAR(150) NOT NULL DEFAULT 'System',
  `status` ENUM('Draft', 'Finalized', 'Submitted to COA') NOT NULL DEFAULT 'Draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_fr_code` (`report_code`),
  INDEX `idx_fr_type` (`report_type`),
  INDEX `idx_fr_year` (`fiscal_year`),
  INDEX `idx_fr_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- DEFAULT BUDGET & FINANCIAL MANAGEMENT SEED DATA
-- ------------------------------------------------------------
INSERT INTO `disbursement_vouchers` (`dv_number`, `budget_allocation_id`, `payee_name`, `particulars`, `fund_source`, `amount`, `check_no`, `check_date`, `bank_name`, `expense_class`, `certified_by`, `approved_by`, `status`, `released_at`) VALUES
('DV-2026-0001', 5, 'Metro Pharma Distribution Inc.', 'Payment for procurement of essential maintenance medicines & clinic supplies per PO-2026-0020', 'General Fund', 234200.00, 'CHK-0892741', '2026-08-25', 'Land Bank of the Philippines', 'MOOE', 'Maria Santos - Barangay Treasurer', 'Hon. Antonio S. Valdez - Punong Barangay', 'Released', '2026-08-25 14:30:00'),
('DV-2026-0002', 1, 'Luzon Green Energy Solutions Corp.', 'Partial payment (50%) for solar LED streetlights installation per PO-2026-0019 - Mobilization', 'General Fund', 239250.00, 'CHK-0892742', '2026-08-20', 'Land Bank of the Philippines', 'Capital Outlay', 'Maria Santos - Barangay Treasurer', 'Hon. Antonio S. Valdez - Punong Barangay', 'Released', '2026-08-20 10:15:00'),
('DV-2026-0003', NULL, 'Barangay Staff - August 2026 Payroll', 'Payment of salaries and wages for Barangay Secretary, Treasurer, and 5 Tanod personnel for August 2026', 'General Fund', 87500.00, 'CHK-0892743', '2026-08-31', 'Land Bank of the Philippines', 'Personal Services', 'Maria Santos - Barangay Treasurer', 'Hon. Antonio S. Valdez - Punong Barangay', 'Released', '2026-08-31 09:00:00'),
('DV-2026-0004', 4, 'Barangay Maternal Health Outreach Program', 'Reimbursement of expenses for maternal healthcare awareness seminar and livelihood training materials', '5% GAD Fund', 45000.00, 'CHK-0892744', '2026-09-05', 'Land Bank of the Philippines', 'MOOE', 'Maria Santos - Barangay Treasurer', 'Hon. Antonio S. Valdez - Punong Barangay', 'Approved', NULL),
('DV-2026-0005', 6, 'San Isidro Senior Citizens Association', 'Quarterly wellness kits distribution and maintenance medicine subsidy for 85 registered senior citizens', '1% Senior / PWD Fund', 68000.00, NULL, NULL, 'Land Bank of the Philippines', 'MOOE', 'Maria Santos - Barangay Treasurer', 'Hon. Antonio S. Valdez - Punong Barangay', 'Certified', NULL),
('DV-2026-0006', 3, 'San Isidro Youth Sports League Committee', 'Procurement of sports equipment and uniform sets for Annual Barangay Youth Basketball League 2026', '10% SK Youth Development Fund', 125000.00, NULL, NULL, 'Land Bank of the Philippines', 'MOOE', 'Maria Santos - Barangay Treasurer', 'Hon. Antonio S. Valdez - Punong Barangay', 'Draft', NULL);

INSERT INTO `revenue_collections` (`or_number`, `rcd_number`, `payer_name`, `revenue_source`, `particulars`, `amount`, `fund_destination`, `collected_by`, `receipt_date`, `deposit_date`, `deposit_bank`, `deposit_slip_no`, `status`) VALUES
('OR-2026-0451', 'RCD-2026-08-01', 'Juan Dela Cruz', 'Clearance Fees', 'Barangay Clearance for employment purposes', 50.00, 'General Fund', 'Maria Santos - Barangay Treasurer', '2026-08-01', '2026-08-02', 'Land Bank of the Philippines', 'DS-2026-0821', 'Deposited'),
('OR-2026-0452', 'RCD-2026-08-01', 'Rosario Fernandez', 'Clearance Fees', 'Barangay Clearance and Certificate of Residency', 80.00, 'General Fund', 'Maria Santos - Barangay Treasurer', '2026-08-01', '2026-08-02', 'Land Bank of the Philippines', 'DS-2026-0821', 'Deposited'),
('OR-2026-0453', 'RCD-2026-08-05', 'Sari-Sari Store ni Aling Nena', 'Business Permits', 'Annual Barangay Business Clearance renewal - Retail / Sari-Sari Store', 150.00, 'General Fund', 'Maria Santos - Barangay Treasurer', '2026-08-05', '2026-08-06', 'Land Bank of the Philippines', 'DS-2026-0822', 'Deposited'),
('OR-2026-0454', 'RCD-2026-08-10', 'Department of Budget and Management', 'IRA Share', 'Internal Revenue Allotment (IRA) share for Q3 2026 - 2nd tranche', 2850000.00, 'General Fund', 'Maria Santos - Barangay Treasurer', '2026-08-10', '2026-08-10', 'Land Bank of the Philippines', 'DS-2026-0823', 'Deposited'),
('OR-2026-0455', 'RCD-2026-08-15', 'Municipal Treasurer Office', 'Real Property Tax Share', 'Barangay share of Real Property Tax collections for July 2026', 185000.00, 'General Fund', 'Maria Santos - Barangay Treasurer', '2026-08-15', '2026-08-16', 'Land Bank of the Philippines', 'DS-2026-0824', 'Deposited'),
('OR-2026-0456', NULL, 'Pedro Mendoza', 'Clearance Fees', 'Barangay Clearance for NBI requirement', 50.00, 'General Fund', 'Maria Santos - Barangay Treasurer', '2026-09-01', NULL, NULL, NULL, 'Collected'),
('OR-2026-0457', NULL, 'Lions Club International - District 301-A2', 'Donations & Grants', 'Cash donation for Barangay Health Station medical equipment upgrade', 75000.00, 'General Fund', 'Maria Santos - Barangay Treasurer', '2026-09-05', NULL, NULL, NULL, 'Collected'),
('OR-2026-0458', 'RCD-2026-08-20', 'Commercial Space Tenant - Mercado Family', 'Rental Income', 'Monthly rental of Barangay Hall commercial space - August 2026', 8500.00, 'General Fund', 'Maria Santos - Barangay Treasurer', '2026-08-20', '2026-08-21', 'Land Bank of the Philippines', 'DS-2026-0825', 'Deposited');

INSERT INTO `budget_obligations` (`obr_number`, `budget_allocation_id`, `obligation_type`, `obligee_name`, `description`, `amount`, `date_obligated`, `status`) VALUES
('OBR-2026-0001', 1, 'Purchase Order', 'Luzon Green Energy Solutions Corp.', 'Obligation for solar LED streetlights PO-2026-0019 (full contract amount)', 478500.00, '2026-08-15', 'Disbursed'),
('OBR-2026-0002', 5, 'Purchase Order', 'Metro Pharma Distribution Inc.', 'Obligation for medicines and clinic supplies PO-2026-0020', 234200.00, '2026-08-20', 'Disbursed'),
('OBR-2026-0003', NULL, 'Payroll', 'Barangay Regular Staff', 'Monthly payroll obligation for 8 regular barangay personnel - August 2026', 87500.00, '2026-08-01', 'Disbursed'),
('OBR-2026-0004', 4, 'Contract', 'Barangay GAD Focal Point System', 'Obligation for maternal healthcare outreach and livelihood training program', 45000.00, '2026-09-01', 'Approved');

-- ------------------------------------------------------------
-- 27. DRRM EVACUATION CENTERS & TEMPORARY SHELTER TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `drrm_evacuation_centers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `center_name` VARCHAR(150) NOT NULL,
  `purok` VARCHAR(50) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `capacity_families` INT NOT NULL DEFAULT 50,
  `capacity_individuals` INT NOT NULL DEFAULT 250,
  `current_families` INT NOT NULL DEFAULT 0,
  `current_individuals` INT NOT NULL DEFAULT 0,
  `has_generator` TINYINT(1) NOT NULL DEFAULT 1,
  `has_water_supply` TINYINT(1) NOT NULL DEFAULT 1,
  `has_clinic_station` TINYINT(1) NOT NULL DEFAULT 1,
  `center_manager` VARCHAR(100) NOT NULL DEFAULT 'BDRRMC Camp Manager',
  `contact_no` VARCHAR(30) DEFAULT '',
  `status` ENUM('Standby / Inactive', 'Active / Open', 'Full Capacity', 'Decommissioned') NOT NULL DEFAULT 'Standby / Inactive',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_evac_purok` (`purok`),
  INDEX `idx_evac_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 28. DRRM EVACUEES & DISPLACED FAMILIES MASTERLIST TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `drrm_evacuees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `evacuee_code` VARCHAR(40) NOT NULL UNIQUE,
  `evacuation_center_id` INT NOT NULL,
  `household_id` INT DEFAULT NULL,
  `family_head_name` VARCHAR(150) NOT NULL,
  `purok_origin` VARCHAR(50) NOT NULL,
  `contact_no` VARCHAR(30) DEFAULT '',
  `members_count` INT NOT NULL DEFAULT 1,
  `seniors_count` INT NOT NULL DEFAULT 0,
  `children_count` INT NOT NULL DEFAULT 0,
  `pwd_count` INT NOT NULL DEFAULT 0,
  `pregnant_lactating_count` INT NOT NULL DEFAULT 0,
  `room_tent_no` VARCHAR(50) DEFAULT 'Tent 1',
  `special_medical_needs` TEXT DEFAULT NULL,
  `check_in_date` DATETIME NOT NULL,
  `check_out_date` DATETIME DEFAULT NULL,
  `status` ENUM('Sheltered', 'Transferred', 'Decamped / Returned Home') NOT NULL DEFAULT 'Sheltered',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_evacuee_center` (`evacuation_center_id`),
  INDEX `idx_evacuee_code` (`evacuee_code`),
  INDEX `idx_evacuee_purok` (`purok_origin`),
  INDEX `idx_evacuee_status` (`status`),
  CONSTRAINT `fk_evac_center` FOREIGN KEY (`evacuation_center_id`) REFERENCES `drrm_evacuation_centers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_evac_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 29. DRRM RELIEF GOODS & EMERGENCY STOCKPILE INVENTORY TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `drrm_relief_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `item_code` VARCHAR(40) NOT NULL UNIQUE,
  `item_name` VARCHAR(150) NOT NULL,
  `category` ENUM('Food Packs', 'Hygiene Kits', 'Medical & First Aid', 'Bedding & Shelter', 'Emergency Tools & Rescue') NOT NULL DEFAULT 'Food Packs',
  `unit` VARCHAR(30) NOT NULL DEFAULT 'packs',
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `reorder_level` INT NOT NULL DEFAULT 100,
  `unit_cost` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `fund_source` VARCHAR(100) DEFAULT '5% BDRRM Calamity Fund',
  `expiry_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_relief_code` (`item_code`),
  INDEX `idx_relief_cat` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 30. DRRM RELIEF DISTRIBUTIONS & DAFAC JOURNAL TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `drrm_relief_distributions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `distribution_code` VARCHAR(40) NOT NULL UNIQUE,
  `calamity_name` VARCHAR(150) NOT NULL,
  `evacuee_id` INT DEFAULT NULL,
  `recipient_name` VARCHAR(150) NOT NULL,
  `purok` VARCHAR(50) NOT NULL,
  `relief_item_id` INT NOT NULL,
  `quantity_given` INT NOT NULL DEFAULT 1,
  `distributed_by` VARCHAR(150) NOT NULL DEFAULT 'BDRRMC Relief Operations Team',
  `distributed_at` DATETIME NOT NULL,
  `remarks` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_dist_code` (`distribution_code`),
  INDEX `idx_dist_evacuee` (`evacuee_id`),
  INDEX `idx_dist_item` (`relief_item_id`),
  INDEX `idx_dist_date` (`distributed_at`),
  CONSTRAINT `fk_dist_evacuee` FOREIGN KEY (`evacuee_id`) REFERENCES `drrm_evacuees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dist_item` FOREIGN KEY (`relief_item_id`) REFERENCES `drrm_relief_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- DEFAULT DRRM SEED DATA (RA 10121 COMPLIANT)
-- ------------------------------------------------------------
INSERT INTO `drrm_evacuation_centers` (`center_name`, `purok`, `address`, `capacity_families`, `capacity_individuals`, `current_families`, `current_individuals`, `has_generator`, `has_water_supply`, `has_clinic_station`, `center_manager`, `contact_no`, `status`) VALUES
('San Isidro Multi-Purpose Evacuation Center', 'Purok 2', 'Barangay Complex, J.P. Rizal St., Purok 2', 80, 400, 18, 82, 1, 1, 1, 'Kagawad Benjamin Alcantara', '0917-555-1122', 'Active / Open'),
('San Isidro Elementary School - Gymnasium', 'Purok 3', 'School Compound, Mabini Ext., Purok 3', 120, 600, 0, 0, 1, 1, 1, 'Prof. Lydia V. Gonzaga', '0918-555-3344', 'Standby / Inactive'),
('Riverside Community Covered Court', 'Purok 1', 'Riverside Drive near Marikina River tributary, Purok 1', 45, 200, 0, 0, 0, 1, 0, 'Tanod Team Leader Danilo Cruz', '0920-555-5566', 'Standby / Inactive');

INSERT INTO `drrm_evacuees` (`evacuee_code`, `evacuation_center_id`, `household_id`, `family_head_name`, `purok_origin`, `contact_no`, `members_count`, `seniors_count`, `children_count`, `pwd_count`, `pregnant_lactating_count`, `room_tent_no`, `special_medical_needs`, `check_in_date`, `status`) VALUES
('EVAC-2026-0001', 1, 1, 'Eduardo T. Mendoza', 'Purok 1', '0920-555-0404', 5, 1, 2, 0, 0, 'Tent A-01', 'Hypertension maintenance required (Losartan 50mg)', '2026-09-08 18:30:00', 'Sheltered'),
('EVAC-2026-0002', 1, 2, 'Elena Vda. de Castro', 'Purok 1', '0917-555-0912', 4, 2, 1, 1, 0, 'Tent A-02', 'Wheelchair accessibility needed; diabetic maintenance', '2026-09-08 19:15:00', 'Sheltered'),
('EVAC-2026-0003', 1, NULL, 'Rodel C. Bautista', 'Purok 6', '0922-555-8831', 6, 0, 3, 0, 1, 'Tent B-05', 'Infant formula & pediatric oral rehydration needed', '2026-09-09 06:45:00', 'Sheltered');

INSERT INTO `drrm_relief_items` (`item_code`, `item_name`, `category`, `unit`, `stock_quantity`, `reorder_level`, `unit_cost`, `fund_source`, `expiry_date`) VALUES
('REL-FP-01', 'BDRRMC Family Food Pack (6kg Rice, Canned Goods, Coffee)', 'Food Packs', 'packs', 420, 150, 560.00, '5% BDRRM Calamity Fund', '2027-08-31'),
('REL-HK-02', 'Family Emergency Hygiene Kit (Soap, Toothpaste, Sanitizer, Pads)', 'Hygiene Kits', 'kits', 280, 100, 350.00, '5% BDRRM Calamity Fund', '2028-06-30'),
('REL-FA-03', 'First Aid Trauma Kit & Basic OTC Medications', 'Medical & First Aid', 'kits', 85, 40, 750.00, '5% BDRRM Calamity Fund', '2027-12-31'),
('REL-BD-04', 'Emergency Sleeping Mat & Thermal Blanket Set', 'Bedding & Shelter', 'sets', 310, 80, 420.00, '5% BDRRM Calamity Fund', NULL),
('REL-EQ-05', 'Emergency Rechargeable LED Searchlight & Siren', 'Emergency Tools & Rescue', 'units', 35, 15, 1200.00, '5% BDRRM Calamity Fund', NULL);

INSERT INTO `drrm_relief_distributions` (`distribution_code`, `calamity_name`, `evacuee_id`, `recipient_name`, `purok`, `relief_item_id`, `quantity_given`, `distributed_by`, `distributed_at`, `remarks`) VALUES
('DAFAC-2026-0001', 'Habagat Heavy Monsoon & Flash Flood', 1, 'Eduardo T. Mendoza', 'Purok 1', 1, 2, 'Kag. Benjamin Alcantara', '2026-09-09 08:00:00', 'Initial 3-day family ration pack issued upon arrival'),
('DAFAC-2026-0002', 'Habagat Heavy Monsoon & Flash Flood', 1, 'Eduardo T. Mendoza', 'Purok 1', 2, 1, 'BHW Teresa Morales', '2026-09-09 08:05:00', 'Family hygiene set released'),
('DAFAC-2026-0003', 'Habagat Heavy Monsoon & Flash Flood', 2, 'Elena Vda. de Castro', 'Purok 1', 1, 2, 'Kag. Benjamin Alcantara', '2026-09-09 08:30:00', 'Initial food assistance ration'),
('DAFAC-2026-0004', 'Habagat Heavy Monsoon & Flash Flood', 3, 'Rodel C. Bautista', 'Purok 6', 1, 2, 'Kag. Benjamin Alcantara', '2026-09-09 09:15:00', 'Food pack issued with supplementary infant pack');

SET FOREIGN_KEY_CHECKS = 1;




