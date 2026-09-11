<?php
/**
 * Barangay Management System (BarangayOS)
 * Sangguniang Barangay Legislative Tracking & Ordinance Management REST API
 * Compliant with RA 7160 (Local Government Code of 1991) & DILG Legislative Standards
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
// 1. PUBLIC REGISTRY ACTION (No auth required for citizen transparency portal)
// ----------------------------------------------------
if ($action === 'public_registry' || $action === 'public_list') {
    $docType = $_GET['type'] ?? '';
    $query = "SELECT id, control_number, doc_type, title, sponsor_name, co_sponsors, reading_stage, 
                     date_enacted, date_posted, effectivity_date, posting_locations, 
                     city_council_review_status, sanctions_penalties, document_body, created_at
              FROM `legislative_documents` 
              WHERE `reading_stage` = 'Enacted' AND `city_council_review_status` != 'Disapproved'";
    $params = [];

    if (!empty($docType)) {
        $query .= " AND `doc_type` = ?";
        $params[] = $docType;
    }

    $query .= " ORDER BY `date_enacted` DESC, `id` DESC";
    $docs = db_fetch_all($query, $params);

    // Also fetch recent regular sessions with minutes summary
    $sessions = db_fetch_all("
        SELECT id, session_number, session_type, session_date, presiding_officer, 
               quorum_status, present_count, total_members, agenda_topics, minutes_summary, session_status 
        FROM `legislative_sessions` 
        WHERE `session_status` = 'Approved' 
        ORDER BY `session_date` DESC LIMIT 6
    ");

    json_response(true, [
        'documents' => $docs,
        'sessions'  => $sessions,
        'total_enacted' => count($docs)
    ], 'Public legislative repository loaded.');
}

// All subsequent actions require authenticated session
require_auth();

// ----------------------------------------------------
// 2. STATS ACTION
// ----------------------------------------------------
if ($action === 'stats') {
    $totalDocs       = db_count('legislative_documents');
    $ordinances      = db_count('legislative_documents', "`doc_type` = 'Ordinance'");
    $resolutions     = db_count('legislative_documents', "`doc_type` = 'Resolution'");
    $enactedDocs     = db_count('legislative_documents', "`reading_stage` = 'Enacted'");
    $pendingReading  = db_count('legislative_documents', "`reading_stage` IN ('1st Reading', 'Committee Hearing', '2nd Reading')");
    $underCityReview = db_count('legislative_documents', "`city_council_review_status` = 'Transmitted / Under Review'");
    $approvedCity    = db_count('legislative_documents', "`city_council_review_status` = 'Approved / Lapsed into Law'");
    
    $totalSessions   = db_count('legislative_sessions');
    $quorumMet       = db_count('legislative_sessions', "`quorum_status` = 'Quorum Present'");

    // Recent activity breakdown
    $recentDocs = db_fetch_all("
        SELECT id, control_number, doc_type, title, reading_stage, date_enacted, city_council_review_status 
        FROM `legislative_documents` 
        ORDER BY id DESC LIMIT 5
    ");

    json_response(true, [
        'total_documents' => $totalDocs,
        'ordinances_count' => $ordinances,
        'resolutions_count' => $resolutions,
        'enacted_count' => $enactedDocs,
        'pending_reading_count' => $pendingReading,
        'under_city_review' => $underCityReview,
        'approved_by_city' => $approvedCity,
        'total_sessions' => $totalSessions,
        'quorum_met_sessions' => $quorumMet,
        'recent_documents' => $recentDocs
    ], 'Legislative statistics loaded.');
}

// ----------------------------------------------------
// 3. SESSIONS ACTIONS (List, Get, Create, Update, Delete)
// ----------------------------------------------------
if ($action === 'sessions') {
    $sessions = db_fetch_all("
        SELECT * FROM `legislative_sessions` 
        ORDER BY `session_date` DESC, `id` DESC
    ");
    json_response(true, $sessions, 'Sessions retrieved successfully.');
}

if ($action === 'get_session') {
    $id = $_GET['id'] ?? ($input['id'] ?? 0);
    $session = db_fetch_one("SELECT * FROM `legislative_sessions` WHERE id = ?", [(int)$id]);
    if (!$session) {
        json_response(false, null, 'Legislative session not found.', 404);
    }
    json_response(true, $session, 'Session details loaded.');
}

if ($action === 'create_session' && $method === 'POST') {
    $sessionNumber    = trim($input['session_number'] ?? $input['sessionNumber'] ?? '');
    $sessionType      = trim($input['session_type'] ?? $input['sessionType'] ?? 'Regular Session');
    $sessionDate      = trim($input['session_date'] ?? $input['sessionDate'] ?? date('Y-m-d'));
    $sessionTime      = trim($input['session_time'] ?? $input['sessionTime'] ?? '09:00:00');
    $presidingOfficer = trim($input['presiding_officer'] ?? $input['presidingOfficer'] ?? 'Hon. Punong Barangay');
    $quorumStatus     = trim($input['quorum_status'] ?? $input['quorumStatus'] ?? 'Quorum Present');
    $presentCount     = (int)($input['present_count'] ?? $input['presentCount'] ?? 9);
    $totalMembers     = (int)($input['total_members'] ?? $input['totalMembers'] ?? 9);
    $rollCall         = is_array($input['roll_call'] ?? $input['rollCall'] ?? null) 
                        ? json_encode($input['roll_call'] ?? $input['rollCall']) 
                        : ($input['roll_call'] ?? $input['rollCall'] ?? null);
    $agendaTopics     = trim($input['agenda_topics'] ?? $input['agendaTopics'] ?? '');
    $minutesSummary   = trim($input['minutes_summary'] ?? $input['minutesSummary'] ?? '');
    $sessionStatus    = trim($input['session_status'] ?? $input['sessionStatus'] ?? 'Draft');

    if (empty($sessionNumber) || empty($sessionDate)) {
        json_response(false, null, 'Session number and date are required.', 422);
    }

    $sql = "INSERT INTO `legislative_sessions` 
            (`session_number`, `session_type`, `session_date`, `session_time`, `presiding_officer`, 
             `quorum_status`, `present_count`, `total_members`, `roll_call`, `agenda_topics`, 
             `minutes_summary`, `session_status`, `created_at`, `updated_at`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $newId = db_execute($sql, [
        $sessionNumber, $sessionType, $sessionDate, $sessionTime, $presidingOfficer,
        $quorumStatus, $presentCount, $totalMembers, $rollCall, $agendaTopics,
        $minutesSummary, $sessionStatus
    ]);

    log_audit('CREATE_SESSION', 'legislative_sessions', "Created session $sessionNumber ($sessionType)");

    $created = db_fetch_one("SELECT * FROM `legislative_sessions` WHERE id = ?", [$newId]);
    json_response(true, $created, 'Legislative session recorded successfully.');
}

if ($action === 'update_session' && $method === 'POST') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid session ID.', 422);
    }

    $existing = db_fetch_one("SELECT * FROM `legislative_sessions` WHERE id = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Legislative session not found.', 404);
    }

    $sessionNumber    = trim($input['session_number'] ?? $input['sessionNumber'] ?? $existing['session_number']);
    $sessionType      = trim($input['session_type'] ?? $input['sessionType'] ?? $existing['session_type']);
    $sessionDate      = trim($input['session_date'] ?? $input['sessionDate'] ?? $existing['session_date']);
    $sessionTime      = trim($input['session_time'] ?? $input['sessionTime'] ?? $existing['session_time']);
    $presidingOfficer = trim($input['presiding_officer'] ?? $input['presidingOfficer'] ?? $existing['presiding_officer']);
    $quorumStatus     = trim($input['quorum_status'] ?? $input['quorumStatus'] ?? $existing['quorum_status']);
    $presentCount     = (int)($input['present_count'] ?? $input['presentCount'] ?? $existing['present_count']);
    $totalMembers     = (int)($input['total_members'] ?? $input['totalMembers'] ?? $existing['total_members']);
    
    $rawRollCall = $input['roll_call'] ?? $input['rollCall'] ?? $existing['roll_call'];
    $rollCall    = is_array($rawRollCall) ? json_encode($rawRollCall) : $rawRollCall;

    $agendaTopics   = trim($input['agenda_topics'] ?? $input['agendaTopics'] ?? $existing['agenda_topics']);
    $minutesSummary = trim($input['minutes_summary'] ?? $input['minutesSummary'] ?? $existing['minutes_summary']);
    $sessionStatus  = trim($input['session_status'] ?? $input['sessionStatus'] ?? $existing['session_status']);

    $sql = "UPDATE `legislative_sessions` 
            SET `session_number` = ?, `session_type` = ?, `session_date` = ?, `session_time` = ?,
                `presiding_officer` = ?, `quorum_status` = ?, `present_count` = ?, `total_members` = ?,
                `roll_call` = ?, `agenda_topics` = ?, `minutes_summary` = ?, `session_status` = ?,
                `updated_at` = NOW()
            WHERE id = ?";

    db_execute($sql, [
        $sessionNumber, $sessionType, $sessionDate, $sessionTime,
        $presidingOfficer, $quorumStatus, $presentCount, $totalMembers,
        $rollCall, $agendaTopics, $minutesSummary, $sessionStatus,
        $id
    ]);

    log_audit('UPDATE_SESSION', 'legislative_sessions', "Updated session $sessionNumber (ID $id)");

    $updated = db_fetch_one("SELECT * FROM `legislative_sessions` WHERE id = ?", [$id]);
    json_response(true, $updated, 'Legislative session updated successfully.');
}

if ($action === 'delete_session' && $method === 'POST') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid session ID.', 422);
    }

    db_execute("DELETE FROM `legislative_sessions` WHERE id = ?", [$id]);
    log_audit('DELETE_SESSION', 'legislative_sessions', "Deleted session ID $id");
    json_response(true, ['id' => $id], 'Legislative session deleted.');
}

// ----------------------------------------------------
// 4. LEGISLATIVE DOCUMENTS (List, Get, Create, Update, Delete)
// ----------------------------------------------------
if ($action === 'get' || $action === 'get_document') {
    $id = $_GET['id'] ?? ($input['id'] ?? 0);
    $doc = db_fetch_one("SELECT * FROM `legislative_documents` WHERE id = ?", [(int)$id]);
    if (!$doc) {
        json_response(false, null, 'Legislative document not found.', 404);
    }
    json_response(true, $doc, 'Document retrieved successfully.');
}

if ($action === 'create_document' || ($action === 'create' && $method === 'POST')) {
    $controlNumber = trim($input['control_number'] ?? $input['controlNumber'] ?? '');
    $docType       = trim($input['doc_type'] ?? $input['docType'] ?? 'Ordinance');
    $title         = trim($input['title'] ?? '');
    $sponsorName   = trim($input['sponsor_name'] ?? $input['sponsorName'] ?? '');
    $coSponsors    = trim($input['co_sponsors'] ?? $input['coSponsors'] ?? '');
    $readingStage  = trim($input['reading_stage'] ?? $input['readingStage'] ?? '1st Reading');
    $dateEnacted   = !empty($input['date_enacted'] ?? $input['dateEnacted'] ?? null) ? ($input['date_enacted'] ?? $input['dateEnacted']) : null;
    $datePosted    = !empty($input['date_posted'] ?? $input['datePosted'] ?? null) ? ($input['date_posted'] ?? $input['datePosted']) : null;
    $effectivityDate = !empty($input['effectivity_date'] ?? $input['effectivityDate'] ?? null) ? ($input['effectivity_date'] ?? $input['effectivityDate']) : null;
    $postingLocations = trim($input['posting_locations'] ?? $input['postingLocations'] ?? 'Barangay Hall Bulletin, Public Market, Health Center');
    $cityCouncilReviewStatus = trim($input['city_council_review_status'] ?? $input['cityCouncilReviewStatus'] ?? 'Pending Transmission');
    $cityCouncilTransmittedDate = !empty($input['city_council_transmitted_date'] ?? $input['cityCouncilTransmittedDate'] ?? null) ? ($input['city_council_transmitted_date'] ?? $input['cityCouncilTransmittedDate']) : null;
    $cityCouncilActionDate = !empty($input['city_council_action_date'] ?? $input['cityCouncilActionDate'] ?? null) ? ($input['city_council_action_date'] ?? $input['cityCouncilActionDate']) : null;
    $sanctionsPenalties = trim($input['sanctions_penalties'] ?? $input['sanctionsPenalties'] ?? '');
    $documentBody = trim($input['document_body'] ?? $input['documentBody'] ?? '');

    if (empty($title)) {
        json_response(false, null, 'Document title is required.', 422);
    }

    // Auto-generate control number if omitted
    if (empty($controlNumber)) {
        $prefix = ($docType === 'Resolution') ? 'RES' : 'ORD';
        $year = date('Y');
        $count = db_count('legislative_documents', "`doc_type` = '$docType' AND YEAR(`created_at`) = $year") + 1;
        $controlNumber = sprintf("%s-%s-%03d", $prefix, $year, $count);
    }

    $sql = "INSERT INTO `legislative_documents` 
            (`control_number`, `doc_type`, `title`, `sponsor_name`, `co_sponsors`, `reading_stage`, 
             `date_enacted`, `date_posted`, `effectivity_date`, `posting_locations`, 
             `city_council_review_status`, `city_council_transmitted_date`, `city_council_action_date`, 
             `sanctions_penalties`, `document_body`, `created_at`, `updated_at`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    $newId = db_execute($sql, [
        $controlNumber, $docType, $title, $sponsorName, $coSponsors, $readingStage,
        $dateEnacted, $datePosted, $effectivityDate, $postingLocations,
        $cityCouncilReviewStatus, $cityCouncilTransmittedDate, $cityCouncilActionDate,
        $sanctionsPenalties, $documentBody
    ]);

    log_audit('CREATE_LEGISLATION', 'legislative_documents', "Created $docType $controlNumber: $title");

    $created = db_fetch_one("SELECT * FROM `legislative_documents` WHERE id = ?", [$newId]);
    json_response(true, $created, "$docType created successfully.");
}

if ($action === 'update_document' || ($action === 'update' && $method === 'POST')) {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid document ID.', 422);
    }

    $existing = db_fetch_one("SELECT * FROM `legislative_documents` WHERE id = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Legislative document not found.', 404);
    }

    $controlNumber = trim($input['control_number'] ?? $input['controlNumber'] ?? $existing['control_number']);
    $docType       = trim($input['doc_type'] ?? $input['docType'] ?? $existing['doc_type']);
    $title         = trim($input['title'] ?? $existing['title']);
    $sponsorName   = trim($input['sponsor_name'] ?? $input['sponsorName'] ?? $existing['sponsor_name']);
    $coSponsors    = trim($input['co_sponsors'] ?? $input['coSponsors'] ?? $existing['co_sponsors']);
    $readingStage  = trim($input['reading_stage'] ?? $input['readingStage'] ?? $existing['reading_stage']);
    $dateEnacted   = !empty($input['date_enacted'] ?? $input['dateEnacted'] ?? null) ? ($input['date_enacted'] ?? $input['dateEnacted']) : $existing['date_enacted'];
    $datePosted    = !empty($input['date_posted'] ?? $input['datePosted'] ?? null) ? ($input['date_posted'] ?? $input['datePosted']) : $existing['date_posted'];
    $effectivityDate = !empty($input['effectivity_date'] ?? $input['effectivityDate'] ?? null) ? ($input['effectivity_date'] ?? $input['effectivityDate']) : $existing['effectivity_date'];
    $postingLocations = trim($input['posting_locations'] ?? $input['postingLocations'] ?? $existing['posting_locations']);
    $cityCouncilReviewStatus = trim($input['city_council_review_status'] ?? $input['cityCouncilReviewStatus'] ?? $existing['city_council_review_status']);
    $cityCouncilTransmittedDate = !empty($input['city_council_transmitted_date'] ?? $input['cityCouncilTransmittedDate'] ?? null) ? ($input['city_council_transmitted_date'] ?? $input['cityCouncilTransmittedDate']) : $existing['city_council_transmitted_date'];
    $cityCouncilActionDate = !empty($input['city_council_action_date'] ?? $input['cityCouncilActionDate'] ?? null) ? ($input['city_council_action_date'] ?? $input['cityCouncilActionDate']) : $existing['city_council_action_date'];
    $sanctionsPenalties = trim($input['sanctions_penalties'] ?? $input['sanctionsPenalties'] ?? $existing['sanctions_penalties']);
    $documentBody = trim($input['document_body'] ?? $input['documentBody'] ?? $existing['document_body']);

    $sql = "UPDATE `legislative_documents` 
            SET `control_number` = ?, `doc_type` = ?, `title` = ?, `sponsor_name` = ?, 
                `co_sponsors` = ?, `reading_stage` = ?, `date_enacted` = ?, `date_posted` = ?, 
                `effectivity_date` = ?, `posting_locations` = ?, `city_council_review_status` = ?, 
                `city_council_transmitted_date` = ?, `city_council_action_date` = ?, 
                `sanctions_penalties` = ?, `document_body` = ?, `updated_at` = NOW()
            WHERE id = ?";

    db_execute($sql, [
        $controlNumber, $docType, $title, $sponsorName, $coSponsors, $readingStage,
        $dateEnacted, $datePosted, $effectivityDate, $postingLocations,
        $cityCouncilReviewStatus, $cityCouncilTransmittedDate, $cityCouncilActionDate,
        $sanctionsPenalties, $documentBody, $id
    ]);

    log_audit('UPDATE_LEGISLATION', 'legislative_documents', "Updated $docType $controlNumber (ID $id)");

    $updated = db_fetch_one("SELECT * FROM `legislative_documents` WHERE id = ?", [$id]);
    json_response(true, $updated, "$docType updated successfully.");
}

if ($action === 'delete' || $action === 'delete_document') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid document ID.', 422);
    }

    db_execute("DELETE FROM `legislative_documents` WHERE id = ?", [$id]);
    log_audit('DELETE_LEGISLATION', 'legislative_documents', "Deleted document ID $id");
    json_response(true, ['id' => $id], 'Legislative document deleted.');
}

// ----------------------------------------------------
// DEFAULT: LIST ALL DOCUMENTS WITH OPTIONAL FILTERS
// ----------------------------------------------------
$typeFilter   = $_GET['type'] ?? '';
$stageFilter  = $_GET['stage'] ?? '';
$reviewFilter = $_GET['review'] ?? '';
$search       = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM `legislative_documents` WHERE 1=1";
$params = [];

if (!empty($typeFilter)) {
    $sql .= " AND `doc_type` = ?";
    $params[] = $typeFilter;
}

if (!empty($stageFilter)) {
    $sql .= " AND `reading_stage` = ?";
    $params[] = $stageFilter;
}

if (!empty($reviewFilter)) {
    $sql .= " AND `city_council_review_status` = ?";
    $params[] = $reviewFilter;
}

if (!empty($search)) {
    $sql .= " AND (`title` LIKE ? OR `control_number` LIKE ? OR `sponsor_name` LIKE ? OR `document_body` LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY `id` DESC";
$documents = db_fetch_all($sql, $params);

json_response(true, $documents, 'Legislative documents retrieved.');
