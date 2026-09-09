<?php
/**
 * Barangay Management System (BarangayOS)
 * Blotter & Peace and Order REST API Endpoint
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
    $total     = db_count('blotter_cases');
    $active    = db_count('blotter_cases', "`status` = 'Active Mediation'");
    $scheduled = db_count('blotter_cases', "`status` = 'Hearing Scheduled'");
    $settled   = db_count('blotter_cases', "`status` = 'Amicably Settled'");
    $escalated = db_count('blotter_cases', "`status` = 'Escalated CFA'");

    json_response(true, [
        'total'     => $total,
        'active'    => $active,
        'scheduled' => $scheduled,
        'settled'   => $settled,
        'escalated' => $escalated
    ]);
}

// ----------------------------------------------------
// 2. CREATE BLOTTER CASE
// ----------------------------------------------------
if ($action === 'create' || ($method === 'POST' && empty($action))) {
    $complainantName = trim($input['complainant_name'] ?? '');
    $compResidentId  = !empty($input['complainant_resident_id']) ? (int)$input['complainant_resident_id'] : null;
    $respondentName  = trim($input['respondent_name'] ?? '');
    $respResidentId  = !empty($input['respondent_resident_id']) ? (int)$input['respondent_resident_id'] : null;
    $incidentType    = trim($input['incident_type'] ?? 'Physical Altercation');
    $purok           = trim($input['purok'] ?? 'Purok 1');
    $incidentDate    = $input['incident_date'] ?? date('Y-m-d');
    $narrative       = trim($input['narrative'] ?? '');

    if (empty($complainantName) || empty($respondentName) || empty($narrative)) {
        json_response(false, null, 'Complainant name, respondent name, and incident narrative are required.', 400);
    }

    // Generate unique case number (e.g. BLTR-2026-00001)
    $year = date('Y');
    $lastCase = db_fetch_one("SELECT `id` FROM `blotter_cases` ORDER BY `id` DESC LIMIT 1");
    $nextSeq = ($lastCase ? (int)$lastCase['id'] : 0) + 1;
    $caseNo = sprintf('BLTR-%s-%05d', $year, $nextSeq);

    $id = db_insert('blotter_cases', [
        'case_no'                 => $caseNo,
        'complainant_resident_id' => $compResidentId,
        'complainant_name'        => $complainantName,
        'respondent_resident_id'  => $respResidentId,
        'respondent_name'         => $respondentName,
        'incident_type'           => $incidentType,
        'purok'                   => $purok,
        'incident_date'           => $incidentDate,
        'status'                  => 'Active Mediation',
        'narrative'               => $narrative
    ]);

    log_audit_action('BLOTTER_FILED', 'blotter_cases', "Filed blotter case {$caseNo}: {$complainantName} vs {$respondentName}.");

    $newCase = db_fetch_one("SELECT * FROM `blotter_cases` WHERE `id` = ?", [$id]);
    json_response(true, $newCase, 'Blotter case filed successfully.', 201);
}

// ----------------------------------------------------
// 3. UPDATE STATUS
// ----------------------------------------------------
if ($action === 'update_status') {
    $id = (int)($input['id'] ?? 0);
    $status = trim($input['status'] ?? '');

    $validStatuses = ['Active Mediation', 'Hearing Scheduled', 'Amicably Settled', 'Escalated CFA'];
    if (!in_array($status, $validStatuses)) {
        json_response(false, null, 'Invalid status code.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM `blotter_cases` WHERE `id` = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Blotter case not found.', 404);
    }

    db_update('blotter_cases', ['status' => $status], '`id` = ?', [$id]);
    log_audit_action('BLOTTER_STATUS_UPDATED', 'blotter_cases', "Updated {$existing['case_no']} status to {$status}.");

    json_response(true, ['id' => $id, 'status' => $status], 'Case status updated.');
}

// ----------------------------------------------------
// 4. SCHEDULE HEARING
// ----------------------------------------------------
if ($action === 'schedule_hearing') {
    $id = (int)($input['id'] ?? 0);
    $hearingDate = trim($input['hearing_date'] ?? '');

    if (empty($hearingDate)) {
        json_response(false, null, 'Hearing date and time are required.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM `blotter_cases` WHERE `id` = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Blotter case not found.', 404);
    }

    db_update('blotter_cases', [
        'hearing_date' => $hearingDate,
        'status'       => 'Hearing Scheduled'
    ], '`id` = ?', [$id]);

    log_audit_action('BLOTTER_HEARING_SCHEDULED', 'blotter_cases', "Scheduled hearing for {$existing['case_no']} on {$hearingDate}.");

    json_response(true, ['id' => $id, 'hearing_date' => $hearingDate, 'status' => 'Hearing Scheduled'], 'Hearing scheduled.');
}

// ----------------------------------------------------
// 5. DELETE BLOTTER CASE
// ----------------------------------------------------
if ($action === 'delete') {
    $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
    $existing = db_fetch_one("SELECT * FROM `blotter_cases` WHERE `id` = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Case not found.', 404);
    }

    db_delete('blotter_cases', '`id` = ?', [$id]);
    log_audit_action('BLOTTER_DELETED', 'blotter_cases', "Deleted blotter case {$existing['case_no']}.");

    json_response(true, ['id' => $id], 'Case deleted.');
}

// ----------------------------------------------------
// 6. LIST ALL CASES
// ----------------------------------------------------
$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$type   = trim($_GET['type'] ?? '');
$purok  = trim($_GET['purok'] ?? '');

$sql = "SELECT * FROM `blotter_cases` WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (case_no LIKE ? OR complainant_name LIKE ? OR respondent_name LIKE ? OR narrative LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if (!empty($status) && $status !== 'All') {
    $sql .= " AND status = ?";
    $params[] = $status;
}

if (!empty($type) && $type !== 'All') {
    $sql .= " AND incident_type = ?";
    $params[] = $type;
}

if (!empty($purok) && $purok !== 'All') {
    $sql .= " AND purok = ?";
    $params[] = $purok;
}

$sql .= " ORDER BY id DESC";

$cases = db_fetch_all($sql, $params);
json_response(true, $cases, 'Blotter cases retrieved.');
