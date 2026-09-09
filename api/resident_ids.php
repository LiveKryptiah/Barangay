<?php
/**
 * Barangay Management System (BarangayOS)
 * PVC Resident ID Cards REST API Endpoint
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$input  = get_json_input();
if (!empty($input['action'])) {
    $action = $input['action'];
}

// ----------------------------------------------------
// 1. PUBLIC VERIFICATION ACTION (No auth required for QR scanning)
// ----------------------------------------------------
if ($action === 'verify') {
    $code = trim($_GET['code'] ?? ($input['code'] ?? ''));
    if (empty($code)) {
        json_response(false, null, 'ID number / control code is required.', 400);
    }

    $idCard = db_fetch_one("
        SELECT i.*, 
               r.first_name, r.middle_name, r.last_name, r.suffix, r.resident_code, 
               r.birthdate, r.age, r.gender, r.civil_status, r.occupation,
               r.purok, r.street, r.contact_no, r.voter_status,
               r.is_senior, r.is_pwd, r.is_solo_parent, r.is_4ps, r.is_indigent
        FROM `resident_ids` i
        JOIN `residents` r ON i.resident_id = r.id
        WHERE i.id_number = ?
    ", [$code]);

    if (!$idCard) {
        json_response(false, null, 'Resident ID record not found or invalid control number.', 404);
    }

    json_response(true, $idCard, 'Resident ID card verified successfully.');
}

// All actions below require authentication
require_auth();

// ----------------------------------------------------
// 2. STATS ACTION
// ----------------------------------------------------
if ($action === 'stats') {
    $total   = db_count('resident_ids');
    $active  = db_count('resident_ids', "`status` = 'Active'");
    $expired = db_count('resident_ids', "`status` = 'Expired' OR (`valid_until` < CURDATE() AND `status` = 'Active')");
    $revoked = db_count('resident_ids', "`status` = 'Revoked'");

    json_response(true, [
        'total'   => $total,
        'active'  => $active,
        'expired' => $expired,
        'revoked' => $revoked
    ]);
}

// ----------------------------------------------------
// 3. GET SINGLE RESIDENT ID
// ----------------------------------------------------
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? ($input['id'] ?? 0));
    if ($id <= 0) {
        json_response(false, null, 'Valid ID card identifier is required.', 400);
    }

    $idCard = db_fetch_one("
        SELECT i.*, 
               r.first_name, r.middle_name, r.last_name, r.suffix, r.resident_code, 
               r.birthdate, r.age, r.gender, r.civil_status, r.occupation,
               r.purok, r.street, r.contact_no, r.voter_status,
               r.is_senior, r.is_pwd, r.is_solo_parent, r.is_4ps, r.is_indigent
        FROM `resident_ids` i
        JOIN `residents` r ON i.resident_id = r.id
        WHERE i.id = ?
    ", [$id]);

    if (!$idCard) {
        json_response(false, null, 'Resident ID not found.', 404);
    }

    json_response(true, $idCard);
}

// ----------------------------------------------------
// 4. ISSUE / CREATE RESIDENT ID
// ----------------------------------------------------
if ($action === 'issue' || $action === 'create' || ($method === 'POST' && empty($action))) {
    $residentId   = (int)($input['resident_id'] ?? ($input['residentId'] ?? 0));
    $bloodType    = trim($input['blood_type'] ?? ($input['bloodType'] ?? 'N/A'));
    $emergName    = trim($input['emergency_name'] ?? ($input['emergencyName'] ?? ''));
    $emergContact = trim($input['emergency_contact'] ?? ($input['emergencyContact'] ?? ''));
    $validUntil   = $input['valid_until'] ?? ($input['validUntil'] ?? date('Y-m-d', strtotime('+1 year')));
    $signatoryName= trim($input['signatory_name'] ?? ($input['signatoryName'] ?? 'Hon. Punong Barangay'));
    $photoUrl     = $input['photo_url'] ?? ($input['photoUrl'] ?? null);

    $currentUser = current_user();
    $issuedBy = $currentUser['full_name'] ?? ($currentUser['username'] ?? 'Barangay Staff');

    if ($residentId <= 0) {
        json_response(false, null, 'Please select a valid resident to issue an ID card.', 400);
    }

    $resident = db_fetch_one("SELECT * FROM `residents` WHERE `id` = ?", [$residentId]);
    if (!$resident) {
        json_response(false, null, 'Selected resident does not exist in the database.', 404);
    }

    // Generate unique control number: BRGY-ID-YYYY-XXXXX
    $year = date('Y');
    $lastId = db_fetch_one("SELECT `id` FROM `resident_ids` ORDER BY `id` DESC LIMIT 1");
    $nextSeq = ($lastId ? (int)$lastId['id'] : 0) + 1;
    $idNumber = sprintf('BRGY-ID-%s-%05d', $year, $nextSeq);

    // Save ID record
    $insertedId = db_insert('resident_ids', [
        'id_number'         => $idNumber,
        'resident_id'       => $residentId,
        'blood_type'        => $bloodType,
        'emergency_name'    => $emergName ?: ($resident['emergency_name'] ?? ''),
        'emergency_contact' => $emergContact ?: ($resident['emergency_contact'] ?? ''),
        'valid_until'       => $validUntil,
        'issued_by'         => $issuedBy,
        'signatory_name'    => $signatoryName,
        'status'            => 'Active',
        'photo_url'         => $photoUrl
    ]);

    // If photo was provided, also update resident's master record
    if (!empty($photoUrl)) {
        db_update('residents', ['photo_url' => $photoUrl], '`id` = ?', [$residentId]);
    }

    $created = db_fetch_one("
        SELECT i.*, 
               r.first_name, r.middle_name, r.last_name, r.suffix, r.resident_code, 
               r.birthdate, r.age, r.gender, r.civil_status, r.occupation,
               r.purok, r.street, r.contact_no, r.voter_status,
               r.is_senior, r.is_pwd, r.is_solo_parent, r.is_4ps, r.is_indigent
        FROM `resident_ids` i
        JOIN `residents` r ON i.resident_id = r.id
        WHERE i.id = ?
    ", [$insertedId]);

    log_audit_action('RESIDENT_ID_ISSUED', 'resident_ids', "Issued PVC Resident ID {$idNumber} for {$resident['first_name']} {$resident['last_name']}.");

    json_response(true, $created, "Resident ID {$idNumber} issued successfully.", 201);
}

// ----------------------------------------------------
// 5. UPDATE RESIDENT ID STATUS / REVOCATION
// ----------------------------------------------------
if ($action === 'revoke') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Valid ID card identifier is required.', 400);
    }

    $existing = db_fetch_one("SELECT i.*, r.first_name, r.last_name FROM `resident_ids` i JOIN `residents` r ON i.resident_id = r.id WHERE i.id = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Resident ID record not found.', 404);
    }

    db_update('resident_ids', ['status' => 'Revoked'], '`id` = ?', [$id]);
    log_audit_action('RESIDENT_ID_REVOKED', 'resident_ids', "Revoked PVC Resident ID {$existing['id_number']} for {$existing['first_name']} {$existing['last_name']}.");

    json_response(true, null, "Resident ID {$existing['id_number']} has been revoked.");
}

// ----------------------------------------------------
// 6. DEFAULT: LIST ALL RESIDENT IDS
// ----------------------------------------------------
$statusFilter = $_GET['status'] ?? '';
$purokFilter  = $_GET['purok'] ?? '';

$where = "1=1";
$params = [];

if (!empty($statusFilter)) {
    $where .= " AND i.status = ?";
    $params[] = $statusFilter;
}

if (!empty($purokFilter)) {
    $where .= " AND r.purok = ?";
    $params[] = $purokFilter;
}

$idCards = db_fetch_all("
    SELECT i.*, 
           r.first_name, r.middle_name, r.last_name, r.suffix, r.resident_code, 
           r.birthdate, r.age, r.gender, r.civil_status, r.occupation,
           r.purok, r.street, r.contact_no, r.voter_status,
           r.is_senior, r.is_pwd, r.is_solo_parent, r.is_4ps, r.is_indigent
    FROM `resident_ids` i
    JOIN `residents` r ON i.resident_id = r.id
    WHERE {$where}
    ORDER BY i.id DESC
", $params);

json_response(true, $idCards);
