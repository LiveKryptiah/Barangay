<?php
/**
 * Barangay Management System (BarangayOS)
 * Officials & Staff Directory REST API Endpoint
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
    $total     = db_count('officials', "`status` = 'Active'");
    $elected   = db_count('officials', "`category` = 'Elected' AND `status` = 'Active'");
    $tanods    = db_count('officials', "`category` = 'Tanod / Security' AND `status` = 'Active'");
    $health    = db_count('officials', "`category` = 'Health Worker' AND `status` = 'Active'");
    $skCouncil = db_count('officials', "`category` = 'SK Council' AND `status` = 'Active'");

    json_response(true, [
        'total'     => $total,
        'elected'   => $elected,
        'tanods'    => $tanods,
        'health'    => $health,
        'sk_council'=> $skCouncil
    ]);
}

// ----------------------------------------------------
// 2. CREATE OFFICIAL
// ----------------------------------------------------
if ($action === 'create' || ($method === 'POST' && empty($action))) {
    $fullName  = trim($input['full_name'] ?? '');
    $position  = trim($input['position'] ?? 'Barangay Kagawad');
    $category  = trim($input['category'] ?? 'Elected');
    $committee = trim($input['committee'] ?? 'General Administration');
    $termStart = !empty($input['term_start']) ? $input['term_start'] : null;
    $termEnd   = !empty($input['term_end']) ? $input['term_end'] : null;
    $contactNo = trim($input['contact_no'] ?? '');
    $email     = trim($input['email'] ?? '');
    $photoUrl  = trim($input['photo_url'] ?? '');
    $rankOrder = (int)($input['rank_order'] ?? 99);

    if (empty($fullName) || empty($position)) {
        json_response(false, null, 'Full name and position are required.', 400);
    }

    $id = db_insert('officials', [
        'full_name'  => $fullName,
        'position'   => $position,
        'category'   => $category,
        'committee'  => $committee,
        'term_start' => $termStart,
        'term_end'   => $termEnd,
        'contact_no' => $contactNo,
        'email'      => $email,
        'photo_url'  => $photoUrl,
        'rank_order' => $rankOrder,
        'status'     => 'Active'
    ]);

    log_audit_action('OFFICIAL_ADDED', 'officials', "Added {$fullName} as {$position}.");

    $newOfficial = db_fetch_one("SELECT * FROM `officials` WHERE `id` = ?", [$id]);
    json_response(true, $newOfficial, 'Official added to directory.', 201);
}

// ----------------------------------------------------
// 3. UPDATE OFFICIAL
// ----------------------------------------------------
if ($action === 'update') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid official ID.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM `officials` WHERE `id` = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Official not found.', 404);
    }

    db_update('officials', [
        'full_name'  => trim($input['full_name'] ?? $existing['full_name']),
        'position'   => trim($input['position'] ?? $existing['position']),
        'category'   => trim($input['category'] ?? $existing['category']),
        'committee'  => trim($input['committee'] ?? $existing['committee']),
        'term_start' => !empty($input['term_start']) ? $input['term_start'] : $existing['term_start'],
        'term_end'   => !empty($input['term_end']) ? $input['term_end'] : $existing['term_end'],
        'contact_no' => trim($input['contact_no'] ?? $existing['contact_no']),
        'email'      => trim($input['email'] ?? $existing['email']),
        'photo_url'  => trim($input['photo_url'] ?? $existing['photo_url']),
        'rank_order' => isset($input['rank_order']) ? (int)$input['rank_order'] : $existing['rank_order'],
        'status'     => trim($input['status'] ?? $existing['status'])
    ], '`id` = ?', [$id]);

    log_audit_action('OFFICIAL_UPDATED', 'officials', "Updated official profile for {$existing['full_name']}.");

    $updated = db_fetch_one("SELECT * FROM `officials` WHERE `id` = ?", [$id]);
    json_response(true, $updated, 'Official record updated.');
}

// ----------------------------------------------------
// 4. DELETE OFFICIAL
// ----------------------------------------------------
if ($action === 'delete') {
    $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
    $existing = db_fetch_one("SELECT * FROM `officials` WHERE `id` = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Official not found.', 404);
    }

    db_delete('officials', '`id` = ?', [$id]);
    log_audit_action('OFFICIAL_DELETED', 'officials', "Removed official {$existing['full_name']}.");

    json_response(true, ['id' => $id], 'Official removed from directory.');
}

// ----------------------------------------------------
// 5. LIST ALL OFFICIALS
// ----------------------------------------------------
$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

$sql = "SELECT * FROM `officials` WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (full_name LIKE ? OR position LIKE ? OR committee LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like]);
}

if (!empty($category) && $category !== 'All') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY rank_order ASC, id ASC";

$officials = db_fetch_all($sql, $params);
json_response(true, $officials, 'Officials retrieved.');
