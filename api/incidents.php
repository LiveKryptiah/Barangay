<?php
/**
 * Barangay Management System (BarangayOS)
 * Emergency Incidents, Tanod Patrol & Curfew REST API Endpoint
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
    $total    = db_count('incidents');
    $active   = db_count('incidents', "`status` IN ('Dispatched', 'On-Scene')");
    $curfews  = db_count('incidents', "`type` = 'Curfew Violation'");

    $avgRow = db_fetch_one("SELECT AVG(response_minutes) AS avg_time FROM `incidents` WHERE `response_minutes` > 0");
    $avgResponse = $avgRow && $avgRow['avg_time'] ? round((float)$avgRow['avg_time'], 1) : 0;

    json_response(true, [
        'total'        => $total,
        'active'       => $active,
        'curfews'      => $curfews,
        'avg_response' => $avgResponse
    ]);
}

// ----------------------------------------------------
// 2. CREATE EMERGENCY INCIDENT / DISPATCH
// ----------------------------------------------------
if ($action === 'create' || ($method === 'POST' && empty($action))) {
    $type          = trim($input['type'] ?? 'Disturbance');
    $priority      = trim($input['priority'] ?? 'Routine');
    $callerName    = trim($input['caller_name'] ?? '');
    $callerContact = trim($input['caller_contact'] ?? '');
    $location      = trim($input['location'] ?? '');
    $purok         = trim($input['purok'] ?? 'Purok 1');
    $narrative     = trim($input['narrative'] ?? '');
    $responderName = trim($input['responder_name'] ?? '');
    $vehicleUnit   = trim($input['vehicle_unit'] ?? '');

    if (empty($callerName) || empty($location)) {
        json_response(false, null, 'Caller name and location are required.', 400);
    }

    $year = date('Y');
    $lastInc = db_fetch_one("SELECT `id` FROM `incidents` ORDER BY `id` DESC LIMIT 1");
    $nextSeq = ($lastInc ? (int)$lastInc['id'] : 0) + 1;
    $incNo = sprintf('INC-%s-%05d', $year, $nextSeq);

    $id = db_insert('incidents', [
        'incident_no'    => $incNo,
        'type'           => $type,
        'priority'       => $priority,
        'caller_name'    => $callerName,
        'caller_contact' => $callerContact,
        'location'       => $location,
        'purok'          => $purok,
        'status'         => 'Dispatched',
        'narrative'      => $narrative,
        'responder_name' => $responderName,
        'vehicle_unit'   => $vehicleUnit,
        'reported_at'    => date('Y-m-d H:i:s')
    ]);

    log_audit_action('INCIDENT_DISPATCHED', 'incidents', "Dispatched {$incNo} ({$type} - {$priority}) to {$location}.");

    $newInc = db_fetch_one("SELECT * FROM `incidents` WHERE `id` = ?", [$id]);
    json_response(true, $newInc, 'Incident dispatched successfully.', 201);
}

// ----------------------------------------------------
// 3. CREATE CURFEW CITATION
// ----------------------------------------------------
if ($action === 'create_curfew') {
    $minorName       = trim($input['caller_name'] ?? ($input['minor_name'] ?? ''));
    $minorAge        = (int)($input['minor_age'] ?? 15);
    $guardianName    = trim($input['guardian_name'] ?? '');
    $guardianContact = trim($input['guardian_contact'] ?? '');
    $location        = trim($input['location'] ?? '');
    $purok           = trim($input['purok'] ?? 'Purok 1');
    $narrative       = trim($input['narrative'] ?? 'Apprehended past community curfew hours without parent/guardian.');
    $responderName   = trim($input['responder_name'] ?? 'Patrol Tanod');

    if (empty($minorName)) {
        json_response(false, null, 'Minor name is required.', 400);
    }

    $year = date('Y');
    $lastInc = db_fetch_one("SELECT `id` FROM `incidents` ORDER BY `id` DESC LIMIT 1");
    $nextSeq = ($lastInc ? (int)$lastInc['id'] : 0) + 1;
    $incNo = sprintf('CRFW-%s-%05d', $year, $nextSeq);

    $id = db_insert('incidents', [
        'incident_no'      => $incNo,
        'type'             => 'Curfew Violation',
        'priority'         => 'Moderate',
        'caller_name'      => $minorName,
        'caller_contact'   => $guardianContact,
        'minor_age'        => $minorAge,
        'guardian_name'    => $guardianName,
        'guardian_contact' => $guardianContact,
        'location'         => $location,
        'purok'            => $purok,
        'status'           => 'Resolved',
        'narrative'        => $narrative,
        'responder_name'   => $responderName,
        'reported_at'      => date('Y-m-d H:i:s'),
        'resolved_at'      => date('Y-m-d H:i:s')
    ]);

    log_audit_action('CURFEW_CITATION_LOGGED', 'incidents', "Logged curfew citation {$incNo} for minor {$minorName} (Age: {$minorAge}).");

    $newCurfew = db_fetch_one("SELECT * FROM `incidents` WHERE `id` = ?", [$id]);
    json_response(true, $newCurfew, 'Curfew citation logged.', 201);
}

// ----------------------------------------------------
// 4. UPDATE STATUS / ARRIVAL ON-SCENE
// ----------------------------------------------------
if ($action === 'update_status') {
    $id     = (int)($input['id'] ?? 0);
    $status = trim($input['status'] ?? '');

    $validStatuses = ['Dispatched', 'On-Scene', 'Resolved', 'Referred'];
    if (!in_array($status, $validStatuses)) {
        json_response(false, null, 'Invalid status code.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM `incidents` WHERE `id` = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Incident not found.', 404);
    }

    $updateData = ['status' => $status];
    $now = date('Y-m-d H:i:s');

    if ($status === 'On-Scene' && empty($existing['on_scene_at'])) {
        $updateData['on_scene_at'] = $now;
        // Calculate response duration in minutes
        $reported = new DateTime($existing['reported_at']);
        $onScene  = new DateTime($now);
        $diffMins = max(1, (int)(($onScene->getTimestamp() - $reported->getTimestamp()) / 60));
        $updateData['response_minutes'] = $diffMins;
    } elseif ($status === 'Resolved') {
        $updateData['resolved_at'] = $now;
        if (empty($existing['on_scene_at'])) {
            $updateData['on_scene_at'] = $now;
            $reported = new DateTime($existing['reported_at']);
            $diffMins = max(1, (int)((time() - $reported->getTimestamp()) / 60));
            $updateData['response_minutes'] = $diffMins;
        }
    }

    db_update('incidents', $updateData, '`id` = ?', [$id]);
    log_audit_action('INCIDENT_STATUS_UPDATED', 'incidents', "Updated {$existing['incident_no']} status to {$status}.");

    $updated = db_fetch_one("SELECT * FROM `incidents` WHERE `id` = ?", [$id]);
    json_response(true, $updated, 'Incident status updated.');
}

// ----------------------------------------------------
// 5. DELETE INCIDENT
// ----------------------------------------------------
if ($action === 'delete') {
    $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
    $existing = db_fetch_one("SELECT * FROM `incidents` WHERE `id` = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Incident not found.', 404);
    }

    db_delete('incidents', '`id` = ?', [$id]);
    log_audit_action('INCIDENT_DELETED', 'incidents', "Deleted incident {$existing['incident_no']}.");

    json_response(true, ['id' => $id], 'Incident deleted.');
}

// ----------------------------------------------------
// 6. LIST ALL INCIDENTS
// ----------------------------------------------------
$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$priority = trim($_GET['priority'] ?? '');
$status   = trim($_GET['status'] ?? '');
$purok    = trim($_GET['purok'] ?? '');

$sql = "SELECT * FROM `incidents` WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (incident_no LIKE ? OR caller_name LIKE ? OR location LIKE ? OR responder_name LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if (!empty($category) && $category !== 'All') {
    $sql .= " AND type = ?";
    $params[] = $category;
}

if (!empty($priority) && $priority !== 'All') {
    $sql .= " AND priority LIKE ?";
    $params[] = "%{$priority}%";
}

if (!empty($status) && $status !== 'All') {
    $sql .= " AND status = ?";
    $params[] = $status;
}

if (!empty($purok) && $purok !== 'All') {
    $sql .= " AND purok = ?";
    $params[] = $purok;
}

$sql .= " ORDER BY id DESC";

$incidents = db_fetch_all($sql, $params);
json_response(true, $incidents, 'Incidents retrieved.');
