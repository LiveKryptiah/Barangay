<?php
/**
 * Barangay Management System (BarangayOS)
 * Health Station & Nutrition Information System REST API Endpoint
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

// All actions require staff/admin authentication
require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$input  = get_json_input();
if (!empty($input['action'])) {
    $action = $input['action'];
}

// ----------------------------------------------------
// 1. STATS ACTION
// ----------------------------------------------------
if ($action === 'stats') {
    $totalRecords = db_count('health_records');
    $patientRow = db_fetch_one("SELECT COUNT(DISTINCT resident_id) AS total_patients FROM `health_records`");
    $totalPatients = (int)($patientRow['total_patients'] ?? 0);

    // OPT Malnutrition cases
    $malnourishedCount = db_count('health_records', "`service_type` = 'Nutrition OPT Plus' AND (`clinical_notes` LIKE '%Underweight%' OR `clinical_notes` LIKE '%Stunted%' OR `clinical_notes` LIKE '%Wasted%')");

    // Medicines inventory stats
    $medStockRow = db_fetch_one("SELECT COALESCE(SUM(stock_quantity), 0) AS total_stock FROM `health_medicines`");
    $totalMedsInStock = (int)($medStockRow['total_stock'] ?? 0);
    $lowStockMeds = db_count('health_medicines', "`stock_quantity` <= `reorder_level`");

    // Referrals count
    $referralsCount = db_count('health_records', "`status` = 'Referred to RHU / Hospital'");

    // Follow-ups due
    $followUpsDue = db_count('health_records', "`follow_up_date` IS NOT NULL AND `follow_up_date` <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND `status` = 'Follow-Up Needed'");

    // Service breakdown
    $services = db_fetch_all("SELECT service_type, COUNT(*) as count FROM `health_records` GROUP BY service_type ORDER BY count DESC");

    json_response(true, [
        'total'                 => $totalRecords,
        'total_patients'        => $totalPatients,
        'opt_malnourished'      => $malnourishedCount,
        'total_meds_stock'      => $totalMedsInStock,
        'low_stock_medicines'   => $lowStockMeds,
        'referrals_count'       => $referralsCount,
        'follow_ups_due'        => $followUpsDue,
        'service_breakdown'     => $services
    ]);
}

// ----------------------------------------------------
// 2. MEDICINES LIST ACTION
// ----------------------------------------------------
if ($action === 'medicines') {
    if ($method === 'GET') {
        $category = trim($_GET['category'] ?? '');
        if ($category !== '') {
            $meds = db_fetch_all("SELECT * FROM `health_medicines` WHERE category = ? ORDER BY medicine_name ASC", [$category]);
        } else {
            $meds = db_fetch_all("SELECT * FROM `health_medicines` ORDER BY medicine_name ASC");
        }
        json_response(true, $meds);
    }
}

// ----------------------------------------------------
// 3. CREATE MEDICINE ACTION
// ----------------------------------------------------
if ($action === 'create_medicine') {
    $medName  = trim($input['medicine_name'] ?? '');
    $generic  = trim($input['generic_name'] ?? '');
    $category = trim($input['category'] ?? 'Analgesic / Fever');
    $dosage   = trim($input['dosage'] ?? '500mg');
    $stock    = (int)($input['stock_quantity'] ?? 0);
    $unit     = trim($input['unit'] ?? 'tablets');
    $reorder  = (int)($input['reorder_level'] ?? 30);
    $expiry   = trim($input['expiry_date'] ?? date('Y-m-d', strtotime('+1 year')));
    $batchNo  = trim($input['batch_no'] ?? ('BATCH-' . date('Y')));

    if (empty($medName) || empty($generic)) {
        json_response(false, null, 'Medicine brand name and generic name are required.', 400);
    }

    $sql = "INSERT INTO `health_medicines` 
            (`medicine_name`, `generic_name`, `category`, `dosage`, `stock_quantity`, `unit`, `reorder_level`, `expiry_date`, `batch_no`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $id = db_insert($sql, [$medName, $generic, $category, $dosage, $stock, $unit, $reorder, $expiry, $batchNo]);

    log_audit_action('CREATE_MEDICINE', 'health_medicines', "Added medicine: $medName ($generic), Stock: $stock $unit");
    $created = db_fetch_one("SELECT * FROM `health_medicines` WHERE id = ?", [$id]);
    json_response(true, $created, 'Medicine added to pharmacy inventory.');
}

// ----------------------------------------------------
// 4. UPDATE MEDICINE ACTION / RESTOCK
// ----------------------------------------------------
if ($action === 'update_medicine' || $action === 'restock_medicine') {
    $id    = (int)($input['id'] ?? 0);
    $stock = isset($input['stock_quantity']) ? (int)$input['stock_quantity'] : null;
    $addStock = isset($input['add_quantity']) ? (int)$input['add_quantity'] : null;

    if ($id <= 0) {
        json_response(false, null, 'Invalid medicine ID.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM `health_medicines` WHERE id = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Medicine not found.', 404);
    }

    if ($addStock !== null) {
        $newStock = max(0, (int)$existing['stock_quantity'] + $addStock);
        db_execute("UPDATE `health_medicines` SET stock_quantity = ? WHERE id = ?", [$newStock, $id]);
        log_audit_action('RESTOCK_MEDICINE', 'health_medicines', "Restocked {$existing['medicine_name']} (+{$addStock} {$existing['unit']}). New total: $newStock");
    } elseif ($stock !== null) {
        db_execute("UPDATE `health_medicines` SET stock_quantity = ? WHERE id = ?", [$stock, $id]);
        log_audit_action('UPDATE_MEDICINE_STOCK', 'health_medicines', "Updated stock for {$existing['medicine_name']} to $stock");
    }

    // Optional updates for other fields
    if (!empty($input['expiry_date'])) {
        db_execute("UPDATE `health_medicines` SET expiry_date = ? WHERE id = ?", [trim($input['expiry_date']), $id]);
    }
    if (!empty($input['reorder_level'])) {
        db_execute("UPDATE `health_medicines` SET reorder_level = ? WHERE id = ?", [(int)$input['reorder_level'], $id]);
    }

    $updated = db_fetch_one("SELECT * FROM `health_medicines` WHERE id = ?", [$id]);
    json_response(true, $updated, 'Medicine stock updated successfully.');
}

// ----------------------------------------------------
// 5. REFILL SMS REMINDER ACTION
// ----------------------------------------------------
if ($action === 'refill_sms') {
    $residentId = (int)($input['resident_id'] ?? 0);
    $medName    = trim($input['medicine_name'] ?? 'maintenance medication');

    if ($residentId <= 0) {
        json_response(false, null, 'Resident ID is required for sending refill SMS.', 400);
    }

    $resident = db_fetch_one("SELECT * FROM `residents` WHERE id = ?", [$residentId]);
    if (!$resident) {
        json_response(false, null, 'Resident record not found.', 404);
    }

    $contact = trim($resident['contact_no'] ?? '');
    if (empty($contact) || $contact === 'N/A') {
        json_response(false, null, 'Resident does not have a registered contact number.', 400);
    }

    $fullName = trim($resident['first_name'] . ' ' . $resident['last_name']);
    $dispatchCode = 'NOTIF-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    $smsBody = "PABATID mula sa Barangay Health Station: Magandang araw {$fullName}, nakatakda na po ang inyong regular maintenance medicine refill ({$medName}). Maaari po kayong magtungo sa Barangay Clinic Lunes hanggang Biyernes, 8:00 AM - 5:00 PM.";

    // Insert into notifications log
    $sql = "INSERT INTO `notifications` 
            (`dispatch_code`, `recipient_id`, `recipient_name`, `recipient_contact`, `channel`, `category`, `subject`, `message`, `status`, `cost_credits`)
            VALUES (?, ?, ?, ?, 'SMS', 'General', 'Maintenance Medicine Refill Reminder', ?, 'Delivered', 1)";
    $notifId = db_insert($sql, [$dispatchCode, $residentId, $fullName, $contact, $smsBody]);

    // Deduct 1 credit if settings exists
    db_execute("UPDATE `settings` SET setting_value = GREATEST(0, CAST(setting_value AS SIGNED) - 1) WHERE setting_key = 'sms_credits_balance'");

    log_audit_action('DISPATCH_SMS', 'health_medicines', "Sent medicine refill alert to $fullName ($contact) for $medName. Code: $dispatchCode");

    json_response(true, [
        'dispatch_code' => $dispatchCode,
        'recipient'     => $fullName,
        'contact'       => $contact,
        'medicine'      => $medName,
        'message'       => $smsBody
    ], 'Maintenance medicine refill SMS dispatched successfully.');
}

// ----------------------------------------------------
// 6. SINGLE GET CONSULTATION RECORD
// ----------------------------------------------------
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? ($input['id'] ?? 0));
    if ($id <= 0) {
        json_response(false, null, 'Invalid consultation record ID.', 400);
    }

    $record = db_fetch_one("
        SELECT h.*,
               r.first_name, r.middle_name, r.last_name, r.suffix, r.resident_code,
               r.purok, r.street, r.contact_no, r.birthdate, r.gender, r.civil_status,
               r.is_senior, r.is_pwd, r.is_4ps, r.is_indigent
        FROM `health_records` h
        JOIN `residents` r ON h.resident_id = r.id
        WHERE h.id = ?
    ", [$id]);

    if (!$record) {
        json_response(false, null, 'Health consultation record not found.', 404);
    }

    json_response(true, $record);
}

// ----------------------------------------------------
// 7. CREATE CONSULTATION RECORD
// ----------------------------------------------------
if ($action === 'create' || ($method === 'POST' && empty($action))) {
    $residentId   = (int)($input['resident_id'] ?? 0);
    $serviceType  = trim($input['service_type'] ?? 'General Consultation');
    $bp           = trim($input['bp'] ?? '');
    $temp         = !empty($input['temperature']) ? (float)$input['temperature'] : null;
    $weight       = !empty($input['weight_kg']) ? (float)$input['weight_kg'] : null;
    $height       = !empty($input['height_cm']) ? (float)$input['height_cm'] : null;
    $pulse        = !empty($input['pulse_rate']) ? (int)$input['pulse_rate'] : null;
    $complaint    = trim($input['chief_complaint'] ?? '');
    $notes        = trim($input['clinical_notes'] ?? '');
    $dispensed    = trim($input['medicines_dispensed'] ?? '');
    $staff        = trim($input['attending_staff'] ?? 'Barangay Health Worker');
    $status       = trim($input['status'] ?? 'Completed');
    $referral     = trim($input['referral_target'] ?? '');
    $followUp     = !empty($input['follow_up_date']) ? trim($input['follow_up_date']) : null;

    if ($residentId <= 0) {
        json_response(false, null, 'Please select a registered community resident.', 400);
    }
    if (empty($complaint)) {
        json_response(false, null, 'Chief complaint or assessment is required.', 400);
    }

    $res = db_fetch_one("SELECT first_name, last_name, contact_no FROM `residents` WHERE id = ?", [$residentId]);
    if (!$res) {
        json_response(false, null, 'Selected resident does not exist in registry.', 404);
    }

    $recordNo = 'HLTH-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

    $sql = "INSERT INTO `health_records`
            (`record_no`, `resident_id`, `service_type`, `bp`, `temperature`, `weight_kg`, `height_cm`, `pulse_rate`, `chief_complaint`, `clinical_notes`, `medicines_dispensed`, `attending_staff`, `status`, `referral_target`, `follow_up_date`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $id = db_insert($sql, [$recordNo, $residentId, $serviceType, $bp, $temp, $weight, $height, $pulse, $complaint, $notes, $dispensed, $staff, $status, $referral, $followUp]);

    log_audit_action('CREATE_HEALTH_RECORD', 'health_records', "Created consultation {$recordNo} for {$res['first_name']} {$res['last_name']} ({$serviceType})");

    $created = db_fetch_one("
        SELECT h.*,
               r.first_name, r.middle_name, r.last_name, r.suffix, r.resident_code,
               r.purok, r.contact_no, r.birthdate, r.gender
        FROM `health_records` h
        JOIN `residents` r ON h.resident_id = r.id
        WHERE h.id = ?
    ", [$id]);

    json_response(true, $created, 'Health consultation record logged successfully.');
}

// ----------------------------------------------------
// 8. UPDATE CONSULTATION RECORD
// ----------------------------------------------------
if ($action === 'update') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid consultation record ID.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM `health_records` WHERE id = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Health consultation record not found.', 404);
    }

    $fields = [];
    $params = [];

    $updatable = [
        'service_type', 'bp', 'temperature', 'weight_kg', 'height_cm', 'pulse_rate',
        'chief_complaint', 'clinical_notes', 'medicines_dispensed', 'attending_staff',
        'status', 'referral_target', 'follow_up_date'
    ];

    foreach ($updatable as $col) {
        if (isset($input[$col])) {
            $fields[] = "`$col` = ?";
            $params[] = $input[$col] === '' ? null : $input[$col];
        }
    }

    if (empty($fields)) {
        json_response(false, null, 'No fields to update.', 400);
    }

    $params[] = $id;
    $sql = "UPDATE `health_records` SET " . implode(', ', $fields) . " WHERE id = ?";
    db_execute($sql, $params);

    log_audit_action('UPDATE_HEALTH_RECORD', 'health_records', "Updated consultation {$existing['record_no']}");

    $updated = db_fetch_one("
        SELECT h.*,
               r.first_name, r.middle_name, r.last_name, r.suffix, r.resident_code,
               r.purok, r.contact_no, r.birthdate, r.gender
        FROM `health_records` h
        JOIN `residents` r ON h.resident_id = r.id
        WHERE h.id = ?
    ", [$id]);

    json_response(true, $updated, 'Health record updated successfully.');
}

// ----------------------------------------------------
// 9. DELETE CONSULTATION RECORD
// ----------------------------------------------------
if ($action === 'delete') {
    $id = (int)($input['id'] ?? ($_POST['id'] ?? 0));
    if ($id <= 0) {
        json_response(false, null, 'Invalid consultation ID.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM `health_records` WHERE id = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Health record not found.', 404);
    }

    db_execute("DELETE FROM `health_records` WHERE id = ?", [$id]);
    log_audit_action('DELETE_HEALTH_RECORD', 'health_records', "Deleted consultation record {$existing['record_no']}");

    json_response(true, null, 'Health record removed.');
}

// ----------------------------------------------------
// 10. DEFAULT / LIST ALL HEALTH RECORDS
// ----------------------------------------------------
$search = trim($_GET['search'] ?? '');
$serviceFilter = trim($_GET['service_type'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$query = "
    SELECT h.*,
           r.first_name, r.middle_name, r.last_name, r.suffix, r.resident_code,
           r.purok, r.street, r.contact_no, r.birthdate, r.gender,
           r.is_senior, r.is_pwd, r.is_4ps, r.is_indigent
    FROM `health_records` h
    JOIN `residents` r ON h.resident_id = r.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (h.record_no LIKE ? OR r.first_name LIKE ? OR r.last_name LIKE ? OR h.chief_complaint LIKE ? OR h.medicines_dispensed LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like, $like]);
}
if (!empty($serviceFilter)) {
    $query .= " AND h.service_type = ?";
    $params[] = $serviceFilter;
}
if (!empty($statusFilter)) {
    $query .= " AND h.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY h.created_at DESC LIMIT 200";

$records = db_fetch_all($query, $params);
json_response(true, $records);
