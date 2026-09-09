<?php
/**
 * Barangay Management System (BarangayOS)
 * Households Census & Family Tree REST API Endpoint
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

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
    $total = db_count('households');
    $memberCount = db_count('household_members');
    $avgFamilySize = $total > 0 ? round(($memberCount + $total) / $total, 1) : 0;

    $vulnerable = db_count('households', "`monthly_income` = 'Under 10,000' OR `structure_type` = 'Makeshift' OR `hazard_risk` IN ('High', 'Critical')");
    $hazard = db_count('households', "`hazard_risk` IN ('Moderate', 'High', 'Critical')");

    json_response(true, [
        'total'          => $total,
        'avg_family_size'=> $avgFamilySize,
        'vulnerable'     => $vulnerable,
        'hazard'         => $hazard
    ]);
}

// ----------------------------------------------------
// 2. GET SINGLE HOUSEHOLD DOSSIER & FAMILY TREE
// ----------------------------------------------------
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? ($input['id'] ?? 0));
    if ($id <= 0) {
        json_response(false, null, 'Invalid household ID.', 400);
    }

    $household = db_fetch_one("
        SELECT h.*, 
               r.first_name AS head_first_name, 
               r.last_name AS head_last_name, 
               r.resident_code AS head_code,
               r.contact_no AS head_contact,
               r.birthdate AS head_birthdate,
               r.civil_status AS head_civil_status,
               r.occupation AS head_occupation
        FROM `households` h
        LEFT JOIN `residents` r ON h.head_resident_id = r.id
        WHERE h.id = ?
    ", [$id]);

    if (!$household) {
        json_response(false, null, 'Household not found.', 404);
    }

    // Fetch members with resident details
    $members = db_fetch_all("
        SELECT hm.id AS member_id, hm.relationship, r.*
        FROM `household_members` hm
        JOIN `residents` r ON hm.resident_id = r.id
        WHERE hm.household_id = ?
        ORDER BY hm.id ASC
    ", [$id]);

    $household['members'] = $members;
    json_response(true, $household, 'Household profile retrieved.');
}

// ----------------------------------------------------
// 3. CREATE HOUSEHOLD
// ----------------------------------------------------
if ($action === 'create' || ($method === 'POST' && empty($action))) {
    $purok         = trim($input['purok'] ?? 'Purok 1');
    $street        = trim($input['street'] ?? '');
    $headId        = !empty($input['head_resident_id']) ? (int)$input['head_resident_id'] : null;
    $structure     = trim($input['structure_type'] ?? 'Concrete');
    $tenure        = trim($input['tenure_status'] ?? 'Owned');
    $water         = trim($input['water_source'] ?? 'Piped / Level 3');
    $toilet        = trim($input['toilet_facility'] ?? 'Water-sealed');
    $power         = trim($input['power_source'] ?? 'Grid Electric');
    $income        = trim($input['monthly_income'] ?? 'Under 10,000');
    $hazard        = trim($input['hazard_risk'] ?? 'Low');
    $members       = $input['members'] ?? []; // array of { resident_id, relationship }

    // Generate sequential household number (e.g. HH-2026-00001)
    $year = date('Y');
    $lastHh = db_fetch_one("SELECT `id` FROM `households` ORDER BY `id` DESC LIMIT 1");
    $nextSeq = ($lastHh ? (int)$lastHh['id'] : 0) + 1;
    $hhNo = sprintf('HH-%s-%05d', $year, $nextSeq);

    $hhId = db_insert('households', [
        'household_no'     => $hhNo,
        'purok'            => $purok,
        'street'           => $street,
        'head_resident_id' => $headId,
        'structure_type'   => $structure,
        'tenure_status'    => $tenure,
        'water_source'     => $water,
        'toilet_facility'  => $toilet,
        'power_source'     => $power,
        'monthly_income'   => $income,
        'hazard_risk'      => $hazard
    ]);

    // Insert family members
    if (is_array($members)) {
        foreach ($members as $m) {
            $mResidentId = (int)($m['resident_id'] ?? 0);
            $mRel = trim($m['relationship'] ?? 'Member');
            if ($mResidentId > 0) {
                db_insert('household_members', [
                    'household_id' => $hhId,
                    'resident_id'  => $mResidentId,
                    'relationship' => $mRel
                ]);
            }
        }
    }

    log_audit_action('HOUSEHOLD_CREATED', 'households', "Created household {$hhNo} at {$purok}.");

    json_response(true, ['id' => $hhId, 'household_no' => $hhNo], 'Household created successfully.', 201);
}

// ----------------------------------------------------
// 4. UPDATE HOUSEHOLD
// ----------------------------------------------------
if ($action === 'update') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid household ID.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM `households` WHERE `id` = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Household not found.', 404);
    }

    $headId = isset($input['head_resident_id']) ? (int)$input['head_resident_id'] : $existing['head_resident_id'];

    db_update('households', [
        'purok'            => trim($input['purok'] ?? $existing['purok']),
        'street'           => trim($input['street'] ?? $existing['street']),
        'head_resident_id' => $headId,
        'structure_type'   => trim($input['structure_type'] ?? $existing['structure_type']),
        'tenure_status'    => trim($input['tenure_status'] ?? $existing['tenure_status']),
        'water_source'     => trim($input['water_source'] ?? $existing['water_source']),
        'toilet_facility'  => trim($input['toilet_facility'] ?? $existing['toilet_facility']),
        'power_source'     => trim($input['power_source'] ?? $existing['power_source']),
        'monthly_income'   => trim($input['monthly_income'] ?? $existing['monthly_income']),
        'hazard_risk'      => trim($input['hazard_risk'] ?? $existing['hazard_risk'])
    ], '`id` = ?', [$id]);

    // If members array provided, update membership
    if (isset($input['members']) && is_array($input['members'])) {
        db_delete('household_members', '`household_id` = ?', [$id]);
        foreach ($input['members'] as $m) {
            $mResidentId = (int)($m['resident_id'] ?? 0);
            $mRel = trim($m['relationship'] ?? 'Member');
            if ($mResidentId > 0) {
                db_insert('household_members', [
                    'household_id' => $id,
                    'resident_id'  => $mResidentId,
                    'relationship' => $mRel
                ]);
            }
        }
    }

    log_audit_action('HOUSEHOLD_UPDATED', 'households', "Updated household {$existing['household_no']}.");

    json_response(true, ['id' => $id], 'Household record updated.');
}

// ----------------------------------------------------
// 5. DELETE HOUSEHOLD
// ----------------------------------------------------
if ($action === 'delete') {
    $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
    if ($id <= 0) {
        json_response(false, null, 'Invalid household ID.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM `households` WHERE `id` = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Household not found.', 404);
    }

    db_delete('household_members', '`household_id` = ?', [$id]);
    db_delete('households', '`id` = ?', [$id]);

    log_audit_action('HOUSEHOLD_DELETED', 'households', "Deleted household {$existing['household_no']}.");

    json_response(true, ['id' => $id], 'Household deleted.');
}

// ----------------------------------------------------
// 6. LIST ALL HOUSEHOLDS
// ----------------------------------------------------
$search = trim($_GET['search'] ?? '');
$purok  = trim($_GET['purok'] ?? '');

$sql = "
    SELECT h.*, 
           CONCAT(r.first_name, ' ', r.last_name) AS head_name,
           r.resident_code AS head_code,
           (SELECT COUNT(*) FROM `household_members` hm WHERE hm.household_id = h.id) AS member_count
    FROM `households` h
    LEFT JOIN `residents` r ON h.head_resident_id = r.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (h.household_no LIKE ? OR r.first_name LIKE ? OR r.last_name LIKE ? OR h.street LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if (!empty($purok) && $purok !== 'All') {
    $sql .= " AND h.purok = ?";
    $params[] = $purok;
}

$sql .= " ORDER BY h.id DESC";

$households = db_fetch_all($sql, $params);
json_response(true, $households, 'Households retrieved.');
