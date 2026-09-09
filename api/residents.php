<?php
/**
 * Barangay Management System (BarangayOS)
 * Residents Management REST API Endpoint
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Require authentication for resident data
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
    $total    = db_count('residents', "`status` = 'Active'");
    $voters   = db_count('residents', "`voter_status` = 'Registered' AND `status` = 'Active'");
    $seniors  = db_count('residents', "(`age` >= 60 OR `is_senior` = 1) AND `status` = 'Active'");
    $assisted = db_count('residents', "(`is_4ps` = 1 OR `is_indigent` = 1 OR `is_pwd` = 1 OR `is_solo_parent` = 1) AND `status` = 'Active'");

    json_response(true, [
        'total'    => $total,
        'voters'   => $voters,
        'seniors'  => $seniors,
        'assisted' => $assisted
    ]);
}

// ----------------------------------------------------
// 2. CREATE RESIDENT
// ----------------------------------------------------
if ($action === 'create' || ($method === 'POST' && empty($action))) {
    $firstName  = trim($input['first_name'] ?? '');
    $middleName = trim($input['middle_name'] ?? '');
    $lastName   = trim($input['last_name'] ?? '');
    $suffix     = trim($input['suffix'] ?? '');
    $birthdate  = $input['birthdate'] ?? '';
    $gender     = $input['gender'] ?? 'Male';
    $civilStatus= $input['civil_status'] ?? 'Single';
    $occupation = trim($input['occupation'] ?? 'None');
    $purok      = trim($input['purok'] ?? 'Purok 1');
    $street     = trim($input['street'] ?? '');
    $contactNo  = trim($input['contact_no'] ?? '');
    $email      = trim($input['email'] ?? '');
    $voterStatus= $input['voter_status'] ?? 'Unregistered';

    $is4ps      = !empty($input['is_4ps']) ? 1 : 0;
    $isIndigent = !empty($input['is_indigent']) ? 1 : 0;
    $isPwd      = !empty($input['is_pwd']) ? 1 : 0;
    $isSoloParent=!empty($input['is_solo_parent']) ? 1 : 0;

    $emergName  = trim($input['emergency_name'] ?? '');
    $emergContact = trim($input['emergency_contact'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($birthdate)) {
        json_response(false, null, 'First name, last name, and birthdate are required.', 400);
    }

    // Calculate age
    $birth = new DateTime($birthdate);
    $today = new DateTime();
    $age = $today->diff($birth)->y;
    $isSenior = ($age >= 60) ? 1 : 0;

    // Generate sequential resident code (e.g. RES-2026-00001)
    $year = date('Y');
    $lastRes = db_fetch_one("SELECT `id` FROM `residents` ORDER BY `id` DESC LIMIT 1");
    $nextSeq = ($lastRes ? (int)$lastRes['id'] : 0) + 1;
    $resCode = sprintf('RES-%s-%05d', $year, $nextSeq);

    $id = db_insert('residents', [
        'resident_code'     => $resCode,
        'first_name'        => $firstName,
        'middle_name'       => $middleName,
        'last_name'         => $lastName,
        'suffix'            => $suffix,
        'birthdate'         => $birthdate,
        'age'               => $age,
        'gender'            => $gender,
        'civil_status'      => $civilStatus,
        'occupation'        => $occupation,
        'purok'             => $purok,
        'street'            => $street,
        'contact_no'        => $contactNo,
        'email'             => $email,
        'voter_status'      => $voterStatus,
        'is_4ps'            => $is4ps,
        'is_indigent'       => $isIndigent,
        'is_pwd'            => $isPwd,
        'is_solo_parent'    => $isSoloParent,
        'is_senior'         => $isSenior,
        'emergency_name'    => $emergName,
        'emergency_contact' => $emergContact,
        'photo_url'         => $input['photo_url'] ?? ($input['photoUrl'] ?? null),
        'status'            => 'Active'
    ]);

    $newResident = db_fetch_one("SELECT * FROM `residents` WHERE `id` = ?", [$id]);
    log_audit_action('RESIDENT_CREATED', 'residents', "Registered resident {$firstName} {$lastName} ({$resCode}).");

    json_response(true, $newResident, 'Resident registered successfully.', 201);
}

// ----------------------------------------------------
// 3. UPDATE RESIDENT
// ----------------------------------------------------
if ($action === 'update') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid resident ID.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM `residents` WHERE `id` = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Resident not found.', 404);
    }

    $birthdate = $input['birthdate'] ?? $existing['birthdate'];
    $age = $existing['age'];
    if (!empty($birthdate)) {
        $birth = new DateTime($birthdate);
        $today = new DateTime();
        $age = $today->diff($birth)->y;
    }
    $isSenior = ($age >= 60) ? 1 : 0;

    $updateData = [
        'first_name'        => trim($input['first_name'] ?? $existing['first_name']),
        'middle_name'       => trim($input['middle_name'] ?? $existing['middle_name']),
        'last_name'         => trim($input['last_name'] ?? $existing['last_name']),
        'suffix'            => trim($input['suffix'] ?? $existing['suffix']),
        'birthdate'         => $birthdate,
        'age'               => $age,
        'gender'            => $input['gender'] ?? $existing['gender'],
        'civil_status'      => $input['civil_status'] ?? $existing['civil_status'],
        'occupation'        => trim($input['occupation'] ?? $existing['occupation']),
        'purok'             => trim($input['purok'] ?? $existing['purok']),
        'street'            => trim($input['street'] ?? $existing['street']),
        'contact_no'        => trim($input['contact_no'] ?? $existing['contact_no']),
        'email'             => trim($input['email'] ?? $existing['email']),
        'voter_status'      => $input['voter_status'] ?? $existing['voter_status'],
        'is_4ps'            => isset($input['is_4ps']) ? ($input['is_4ps'] ? 1 : 0) : $existing['is_4ps'],
        'is_indigent'       => isset($input['is_indigent']) ? ($input['is_indigent'] ? 1 : 0) : $existing['is_indigent'],
        'is_pwd'            => isset($input['is_pwd']) ? ($input['is_pwd'] ? 1 : 0) : $existing['is_pwd'],
        'is_solo_parent'    => isset($input['is_solo_parent']) ? ($input['is_solo_parent'] ? 1 : 0) : $existing['is_solo_parent'],
        'is_senior'         => $isSenior,
        'emergency_name'    => trim($input['emergency_name'] ?? $existing['emergency_name']),
        'emergency_contact' => trim($input['emergency_contact'] ?? $existing['emergency_contact']),
        'photo_url'         => $input['photo_url'] ?? ($input['photoUrl'] ?? $existing['photo_url']),
        'status'            => $input['status'] ?? $existing['status']
    ];

    db_update('residents', $updateData, '`id` = ?', [$id]);
    $updated = db_fetch_one("SELECT * FROM `residents` WHERE `id` = ?", [$id]);

    log_audit_action('RESIDENT_UPDATED', 'residents', "Updated resident {$updated['first_name']} {$updated['last_name']} ({$updated['resident_code']}).");

    json_response(true, $updated, 'Resident record updated.');
}

// ----------------------------------------------------
// 4. DELETE / ARCHIVE RESIDENT
// ----------------------------------------------------
if ($action === 'delete') {
    $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
    if ($id <= 0) {
        json_response(false, null, 'Invalid resident ID.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM `residents` WHERE `id` = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Resident not found.', 404);
    }

    db_delete('residents', '`id` = ?', [$id]);
    log_audit_action('RESIDENT_DELETED', 'residents', "Deleted resident {$existing['first_name']} {$existing['last_name']} ({$existing['resident_code']}).");

    json_response(true, ['id' => $id], 'Resident deleted successfully.');
}

// ----------------------------------------------------
// 5. GET RESIDENTS LIST / QUERY
// ----------------------------------------------------
$search = trim($_GET['search'] ?? '');
$purok  = trim($_GET['purok'] ?? '');
$voter  = trim($_GET['voter'] ?? '');
$status = trim($_GET['status'] ?? '');

$whereParts = ["`status` != 'Archived'"];
$params = [];

if (!empty($search)) {
    $whereParts[] = "(`first_name` LIKE ? OR `last_name` LIKE ? OR `resident_code` LIKE ? OR `contact_no` LIKE ? OR `street` LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if (!empty($purok) && $purok !== 'All') {
    $whereParts[] = "`purok` = ?";
    $params[] = $purok;
}

if (!empty($voter) && $voter !== 'All') {
    $whereParts[] = "`voter_status` = ?";
    $params[] = $voter;
}

if (!empty($status) && $status !== 'All') {
    $whereParts[] = "`status` = ?";
    $params[] = $status;
}

$whereSql = implode(' AND ', $whereParts);
$sql = "SELECT * FROM `residents` WHERE {$whereSql} ORDER BY `last_name` ASC, `first_name` ASC";
$residents = db_fetch_all($sql, $params);

json_response(true, $residents, 'Residents retrieved.');
