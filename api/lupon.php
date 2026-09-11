<?php
/**
 * Barangay Management System (BarangayOS)
 * Lupong Tagapamayapa Katarungang Pambarangay (KP) Conciliation & Arbitration REST API
 * Fully compliant with RA 7160 (Local Government Code of 1991, Sec. 399-422) and DILG Guidelines
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

// All Lupon operations require authenticated session
require_auth();

// ----------------------------------------------------
// 1. STATS ACTION
// ----------------------------------------------------
if ($action === 'stats') {
    $totalCases      = db_count('lupon_cases');
    $activeDisputes  = db_count('lupon_cases', "`stage` IN ('PB Mediation', 'Pangkat Conciliation')");
    $pbMediation     = db_count('lupon_cases', "`stage` = 'PB Mediation'");
    $pangkatConcil   = db_count('lupon_cases', "`stage` = 'Pangkat Conciliation'");
    $amicablySettled = db_count('lupon_cases', "`stage` = 'Amicably Settled'");
    $cfaIssued       = db_count('lupon_cases', "`stage` = 'CFA Issued'");
    $arbitrated      = db_count('lupon_cases', "`stage` = 'Arbitrated'");
    $dismissed       = db_count('lupon_cases', "`stage` = 'Dismissed'");
    $activeLupon     = db_count('lupon_members', "`status` = 'Active'");

    $settlementRate = 0;
    if ($totalCases > 0) {
        $settlementRate = round(($amicablySettled / $totalCases) * 100, 1);
    }

    // Hearings scheduled this week
    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    $endOfWeek   = date('Y-m-d', strtotime('sunday this week'));
    $hearingsThisWeek = db_count('lupon_hearings', "`scheduled_date` BETWEEN '$startOfWeek' AND '$endOfWeek'");

    // Recent 5 active cases
    $recentCases = db_fetch_all("
        SELECT id, case_number, complainant_name, respondent_name, dispute_type, stage, date_filed, pb_deadline, pangkat_deadline 
        FROM `lupon_cases` 
        ORDER BY id DESC LIMIT 5
    ");

    json_response(true, [
        'total' => $totalCases,
        'total_cases' => $totalCases,
        'active_disputes' => $activeDisputes,
        'pb_mediation' => $pbMediation,
        'pangkat_conciliation' => $pangkatConcil,
        'amicably_settled' => $amicablySettled,
        'cfa_issued' => $cfaIssued,
        'arbitrated' => $arbitrated,
        'dismissed' => $dismissed,
        'settlement_rate' => $settlementRate,
        'hearings_this_week' => $hearingsThisWeek,
        'active_lupon_members' => $activeLupon,
        'recent_cases' => $recentCases
    ], 'Lupon statistics loaded.');
}

// ----------------------------------------------------
// 2. LUPON MEMBERS ROSTER (RA 7160 Sec. 399)
// ----------------------------------------------------
if ($action === 'members') {
    if ($method === 'GET') {
        $members = db_fetch_all("
            SELECT * FROM `lupon_members` 
            ORDER BY FIELD(`status`, 'Active', 'On Leave', 'Inactive'), `full_name` ASC
        ");
        json_response(true, $members, 'Lupon members roster loaded.');
    }
}

if ($action === 'add_member' && $method === 'POST') {
    $fullName = trim($input['full_name'] ?? ($input['fullName'] ?? ''));
    if (empty($fullName)) {
        json_response(false, null, 'Member full name is mandatory.', 422);
    }

    $committee = $input['committee_assignment'] ?? ($input['committeeAssignment'] ?? 'Conciliation Panel');
    $profession = $input['profession_background'] ?? ($input['professionBackground'] ?? 'Community Elder');
    $contact = $input['contact_no'] ?? ($input['contactNo'] ?? '');
    $appointmentDate = $input['appointment_date'] ?? ($input['appointmentDate'] ?? date('Y-m-d'));
    $oathDate = !empty($input['oath_date'] ?? ($input['oathDate'] ?? '')) ? ($input['oath_date'] ?? $input['oathDate']) : null;
    $status = $input['status'] ?? 'Active';
    $residentId = !empty($input['resident_id'] ?? ($input['residentId'] ?? '')) ? intval($input['resident_id'] ?? $input['residentId']) : null;

    $id = db_insert('lupon_members', [
        'resident_id' => $residentId,
        'full_name' => $fullName,
        'committee_assignment' => $committee,
        'profession_background' => $profession,
        'contact_no' => $contact,
        'appointment_date' => $appointmentDate,
        'oath_date' => $oathDate,
        'status' => $status,
        'cases_handled_count' => 0
    ]);

    log_audit('CREATE_LUPON_MEMBER', 'lupon_members', "Appointed Lupon Member: $fullName ($committee)");
    json_response(true, ['id' => $id], 'Lupon member appointed successfully.', 201);
}

if ($action === 'update_member' && $method === 'POST') {
    $id = intval($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid member ID.', 422);
    }

    $updateData = [];
    if (isset($input['full_name']) || isset($input['fullName'])) $updateData['full_name'] = trim($input['full_name'] ?? $input['fullName']);
    if (isset($input['committee_assignment']) || isset($input['committeeAssignment'])) $updateData['committee_assignment'] = $input['committee_assignment'] ?? $input['committeeAssignment'];
    if (isset($input['profession_background']) || isset($input['professionBackground'])) $updateData['profession_background'] = $input['profession_background'] ?? $input['professionBackground'];
    if (isset($input['contact_no']) || isset($input['contactNo'])) $updateData['contact_no'] = $input['contact_no'] ?? $input['contactNo'];
    if (isset($input['appointment_date']) || isset($input['appointmentDate'])) $updateData['appointment_date'] = $input['appointment_date'] ?? $input['appointmentDate'];
    if (isset($input['oath_date']) || isset($input['oathDate'])) $updateData['oath_date'] = $input['oath_date'] ?? $input['oathDate'];
    if (isset($input['status'])) $updateData['status'] = $input['status'];
    if (isset($input['cases_handled_count']) || isset($input['casesHandledCount'])) $updateData['cases_handled_count'] = intval($input['cases_handled_count'] ?? $input['casesHandledCount']);

    if (empty($updateData)) {
        json_response(false, null, 'No fields provided to update.', 422);
    }

    db_update('lupon_members', $updateData, 'id = ?', [$id]);
    log_audit('UPDATE_LUPON_MEMBER', 'lupon_members', "Updated Lupon member ID #$id");
    json_response(true, ['id' => $id], 'Lupon member updated successfully.');
}

// ----------------------------------------------------
// 3. HEARINGS DOCKET
// ----------------------------------------------------
if ($action === 'hearings') {
    if ($method === 'GET') {
        $caseId = intval($_GET['case_id'] ?? 0);
        $whereClause = '1=1';
        $params = [];

        if ($caseId > 0) {
            $whereClause .= ' AND h.case_id = ?';
            $params[] = $caseId;
        }

        $hearings = db_fetch_all("
            SELECT h.*, c.case_number, c.complainant_name, c.respondent_name, c.dispute_type, c.stage 
            FROM `lupon_hearings` h 
            INNER JOIN `lupon_cases` c ON h.case_id = c.id 
            WHERE $whereClause 
            ORDER BY h.scheduled_date DESC, h.scheduled_time ASC
        ", $params);

        json_response(true, $hearings, 'Hearings docket loaded.');
    }
}

if ($action === 'schedule_hearing' && $method === 'POST') {
    $caseId = intval($input['case_id'] ?? ($input['caseId'] ?? 0));
    if ($caseId <= 0) {
        json_response(false, null, 'Valid case ID is mandatory.', 422);
    }

    $case = db_fetch_one("SELECT * FROM `lupon_cases` WHERE id = ?", [$caseId]);
    if (!$case) {
        json_response(false, null, 'Referenced KP dispute case not found.', 404);
    }

    $hearingNumber = $input['hearing_number'] ?? ($input['hearingNumber'] ?? '1st Hearing');
    $hearingType   = $input['hearing_type'] ?? ($input['hearingType'] ?? 'PB Mediation Hearing');
    $schedDate     = $input['scheduled_date'] ?? ($input['scheduledDate'] ?? date('Y-m-d', strtotime('+3 days')));
    $schedTime     = $input['scheduled_time'] ?? ($input['scheduledTime'] ?? '14:00:00');
    $venue         = $input['venue'] ?? 'Barangay Hall Mediation Room';
    $presiding     = $input['presiding_officer'] ?? ($input['presidingOfficer'] ?? 'Hon. Antonio S. Valdez');
    $summary       = $input['proceedings_summary'] ?? ($input['proceedingsSummary'] ?? '');
    $nextAction    = $input['next_action'] ?? ($input['nextAction'] ?? '');

    $id = db_insert('lupon_hearings', [
        'case_id' => $caseId,
        'hearing_number' => $hearingNumber,
        'hearing_type' => $hearingType,
        'scheduled_date' => $schedDate,
        'scheduled_time' => $schedTime,
        'venue' => $venue,
        'presiding_officer' => $presiding,
        'complainant_present' => 1,
        'respondent_present' => 1,
        'proceedings_summary' => $summary,
        'next_action' => $nextAction
    ]);

    log_audit('SCHEDULE_KP_HEARING', 'lupon_hearings', "Scheduled $hearingNumber for Case {$case['case_number']} on $schedDate $schedTime");
    json_response(true, ['id' => $id], 'Hearing scheduled successfully.', 201);
}

if ($action === 'update_hearing' && $method === 'POST') {
    $id = intval($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid hearing ID.', 422);
    }

    $updateData = [];
    if (isset($input['hearing_number']) || isset($input['hearingNumber'])) $updateData['hearing_number'] = $input['hearing_number'] ?? $input['hearingNumber'];
    if (isset($input['hearing_type']) || isset($input['hearingType'])) $updateData['hearing_type'] = $input['hearing_type'] ?? $input['hearingType'];
    if (isset($input['scheduled_date']) || isset($input['scheduledDate'])) $updateData['scheduled_date'] = $input['scheduled_date'] ?? $input['scheduledDate'];
    if (isset($input['scheduled_time']) || isset($input['scheduledTime'])) $updateData['scheduled_time'] = $input['scheduled_time'] ?? $input['scheduledTime'];
    if (isset($input['venue'])) $updateData['venue'] = $input['venue'];
    if (isset($input['presiding_officer']) || isset($input['presidingOfficer'])) $updateData['presiding_officer'] = $input['presiding_officer'] ?? $input['presidingOfficer'];
    if (isset($input['complainant_present']) || isset($input['complainantPresent'])) $updateData['complainant_present'] = (!empty($input['complainant_present'] ?? $input['complainantPresent'])) ? 1 : 0;
    if (isset($input['respondent_present']) || isset($input['respondentPresent'])) $updateData['respondent_present'] = (!empty($input['respondent_present'] ?? $input['respondentPresent'])) ? 1 : 0;
    if (isset($input['proceedings_summary']) || isset($input['proceedingsSummary'])) $updateData['proceedings_summary'] = $input['proceedings_summary'] ?? $input['proceedingsSummary'];
    if (isset($input['next_action']) || isset($input['nextAction'])) $updateData['next_action'] = $input['next_action'] ?? $input['nextAction'];

    if (empty($updateData)) {
        json_response(false, null, 'No fields provided to update.', 422);
    }

    db_update('lupon_hearings', $updateData, 'id = ?', [$id]);
    log_audit('UPDATE_KP_HEARING', 'lupon_hearings', "Updated proceedings for hearing ID #$id");
    json_response(true, ['id' => $id], 'Hearing proceedings updated successfully.');
}

// ----------------------------------------------------
// 4. KP DISPUTE CASES (RA 7160 Sec. 408-418)
// ----------------------------------------------------
if ($action === 'get' || $action === 'get_case') {
    $id = intval($_GET['id'] ?? ($input['id'] ?? 0));
    if ($id <= 0) {
        json_response(false, null, 'Invalid case ID.', 422);
    }

    $case = db_fetch_one("SELECT * FROM `lupon_cases` WHERE id = ?", [$id]);
    if (!$case) {
        json_response(false, null, 'Dispute case not found.', 404);
    }

    // Attach hearings timeline
    $hearings = db_fetch_all("SELECT * FROM `lupon_hearings` WHERE case_id = ? ORDER BY scheduled_date ASC, scheduled_time ASC", [$id]);
    $case['hearings'] = $hearings;

    json_response(true, $case, 'KP Case details loaded.');
}

if ($action === 'create_case' || ($action === 'create' && $method === 'POST')) {
    $complainant = trim($input['complainant_name'] ?? ($input['complainantName'] ?? ''));
    $respondent  = trim($input['respondent_name'] ?? ($input['respondentName'] ?? ''));
    $disputeType = trim($input['dispute_type'] ?? ($input['disputeType'] ?? ''));
    $details     = trim($input['complaint_details'] ?? ($input['complaintDetails'] ?? ''));

    if (empty($complainant) || empty($respondent) || empty($disputeType)) {
        json_response(false, null, 'Complainant, Respondent, and Dispute Type are mandatory.', 422);
    }

    // Generate KP Case Number if not supplied
    $year = date('Y');
    $countThisYear = db_count('lupon_cases', "YEAR(`date_filed`) = '$year'") + 1;
    $caseNumber = sprintf('KP-%s-%04d', $year, $countThisYear);
    if (!empty($input['case_number'] ?? ($input['caseNumber'] ?? ''))) {
        $caseNumber = trim($input['case_number'] ?? $input['caseNumber']);
    }

    $dateFiled = $input['date_filed'] ?? ($input['dateFiled'] ?? date('Y-m-d'));
    // Strict 15-day statutory Punong Barangay mediation timeline (RA 7160 Sec. 410b)
    $pbDeadline = date('Y-m-d', strtotime($dateFiled . ' + 15 days'));

    $blotterId = !empty($input['blotter_case_id'] ?? ($input['blotterCaseId'] ?? '')) ? intval($input['blotter_case_id'] ?? $input['blotterCaseId']) : null;
    $compAddress = $input['complainant_address'] ?? ($input['complainantAddress'] ?? 'Barangay San Isidro, Cabuyao City');
    $compContact = $input['complainant_contact'] ?? ($input['complainantContact'] ?? '');
    $respAddress = $input['respondent_address'] ?? ($input['respondentAddress'] ?? 'Barangay San Isidro, Cabuyao City');
    $respContact = $input['respondent_contact'] ?? ($input['respondentContact'] ?? '');
    $reliefSought = $input['relief_sought'] ?? ($input['reliefSought'] ?? '');
    $stage = $input['stage'] ?? 'PB Mediation';

    $id = db_insert('lupon_cases', [
        'case_number' => $caseNumber,
        'blotter_case_id' => $blotterId,
        'complainant_name' => $complainant,
        'complainant_address' => $compAddress,
        'complainant_contact' => $compContact,
        'respondent_name' => $respondent,
        'respondent_address' => $respAddress,
        'respondent_contact' => $respContact,
        'dispute_type' => $disputeType,
        'complaint_details' => $details,
        'relief_sought' => $reliefSought,
        'date_filed' => $dateFiled,
        'stage' => $stage,
        'pb_deadline' => $pbDeadline
    ]);

    // If originated from blotter, update blotter case status
    if ($blotterId) {
        db_update('blotter_cases', ['status' => 'Referred to Lupon'], 'id = ?', [$blotterId]);
    }

    // Auto-create initial PB Mediation Hearing (KP Form 8 Notice) scheduled within 3 days
    $hearingDate = date('Y-m-d', strtotime($dateFiled . ' + 3 days'));
    db_insert('lupon_hearings', [
        'case_id' => $id,
        'hearing_number' => '1st Hearing',
        'hearing_type' => 'PB Mediation Hearing',
        'scheduled_date' => $hearingDate,
        'scheduled_time' => '14:00:00',
        'venue' => 'Barangay Hall Mediation Room',
        'presiding_officer' => 'Hon. Antonio S. Valdez',
        'proceedings_summary' => 'Initial mediation summons issued pursuant to KP Form 8.',
        'next_action' => 'Appear before Punong Barangay for preliminary confrontation.'
    ]);

    log_audit('CREATE_KP_CASE', 'lupon_cases', "Filed KP Dispute Case: $caseNumber ($complainant vs. $respondent)");
    json_response(true, ['id' => $id, 'case_number' => $caseNumber], 'KP Dispute Case filed and summons generated.', 201);
}

if ($action === 'update_case' || ($action === 'update' && $method === 'POST')) {
    $id = intval($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid case ID.', 422);
    }

    $updateData = [];
    if (isset($input['complainant_name']) || isset($input['complainantName'])) $updateData['complainant_name'] = trim($input['complainant_name'] ?? $input['complainantName']);
    if (isset($input['complainant_address']) || isset($input['complainantAddress'])) $updateData['complainant_address'] = $input['complainant_address'] ?? $input['complainantAddress'];
    if (isset($input['complainant_contact']) || isset($input['complainantContact'])) $updateData['complainant_contact'] = $input['complainant_contact'] ?? $input['complainantContact'];
    if (isset($input['respondent_name']) || isset($input['respondentName'])) $updateData['respondent_name'] = trim($input['respondent_name'] ?? $input['respondentName']);
    if (isset($input['respondent_address']) || isset($input['respondentAddress'])) $updateData['respondent_address'] = $input['respondent_address'] ?? $input['respondentAddress'];
    if (isset($input['respondent_contact']) || isset($input['respondentContact'])) $updateData['respondent_contact'] = $input['respondent_contact'] ?? $input['respondentContact'];
    if (isset($input['dispute_type']) || isset($input['disputeType'])) $updateData['dispute_type'] = $input['dispute_type'] ?? $input['disputeType'];
    if (isset($input['complaint_details']) || isset($input['complaintDetails'])) $updateData['complaint_details'] = $input['complaint_details'] ?? $input['complaintDetails'];
    if (isset($input['relief_sought']) || isset($input['reliefSought'])) $updateData['relief_sought'] = $input['relief_sought'] ?? $input['reliefSought'];
    if (isset($input['stage'])) $updateData['stage'] = $input['stage'];
    if (isset($input['pangkat_chairman']) || isset($input['pangkatChairman'])) $updateData['pangkat_chairman'] = $input['pangkat_chairman'] ?? $input['pangkatChairman'];
    if (isset($input['pangkat_secretary']) || isset($input['pangkatSecretary'])) $updateData['pangkat_secretary'] = $input['pangkat_secretary'] ?? $input['pangkatSecretary'];
    if (isset($input['pangkat_member']) || isset($input['pangkatMember'])) $updateData['pangkat_member'] = $input['pangkat_member'] ?? $input['pangkatMember'];
    if (isset($input['pb_deadline']) || isset($input['pbDeadline'])) $updateData['pb_deadline'] = $input['pb_deadline'] ?? $input['pbDeadline'];
    if (isset($input['pangkat_deadline']) || isset($input['pangkatDeadline'])) $updateData['pangkat_deadline'] = $input['pangkat_deadline'] ?? $input['pangkatDeadline'];
    if (isset($input['settlement_terms']) || isset($input['settlementTerms'])) $updateData['settlement_terms'] = $input['settlement_terms'] ?? $input['settlementTerms'];
    if (isset($input['settlement_date']) || isset($input['settlementDate'])) $updateData['settlement_date'] = $input['settlement_date'] ?? $input['settlementDate'];
    if (isset($input['settlement_amount']) || isset($input['settlementAmount'])) $updateData['settlement_amount'] = floatval($input['settlement_amount'] ?? $input['settlementAmount']);
    if (isset($input['compliance_due_date']) || isset($input['complianceDueDate'])) $updateData['compliance_due_date'] = $input['compliance_due_date'] ?? $input['complianceDueDate'];
    if (isset($input['cfa_reason']) || isset($input['cfaReason'])) $updateData['cfa_reason'] = $input['cfa_reason'] ?? $input['cfaReason'];
    if (isset($input['cfa_date']) || isset($input['cfaDate'])) $updateData['cfa_date'] = $input['cfa_date'] ?? $input['cfaDate'];

    // Auto-calculate Pangkat 15-day deadline if transitioning to Pangkat Conciliation
    if (isset($updateData['stage']) && $updateData['stage'] === 'Pangkat Conciliation' && empty($updateData['pangkat_deadline'])) {
        $updateData['pangkat_deadline'] = date('Y-m-d', strtotime('+15 days'));
    }

    if (empty($updateData)) {
        json_response(false, null, 'No fields provided to update.', 422);
    }

    db_update('lupon_cases', $updateData, 'id = ?', [$id]);
    log_audit('UPDATE_KP_CASE', 'lupon_cases', "Updated KP dispute case ID #$id");
    json_response(true, ['id' => $id], 'KP Dispute case updated successfully.');
}

// Formalize Amicable Settlement (KP Form 16 / Kasunduan)
if ($action === 'settle_case' && $method === 'POST') {
    $id = intval($input['id'] ?? 0);
    $terms = trim($input['settlement_terms'] ?? ($input['settlementTerms'] ?? ''));
    $amount = floatval($input['settlement_amount'] ?? ($input['settlementAmount'] ?? 0));
    $settlementDate = $input['settlement_date'] ?? ($input['settlementDate'] ?? date('Y-m-d'));
    $complianceDate = $input['compliance_due_date'] ?? ($input['complianceDueDate'] ?? date('Y-m-d', strtotime('+30 days')));

    if ($id <= 0 || empty($terms)) {
        json_response(false, null, 'Case ID and settlement agreement terms are required.', 422);
    }

    db_update('lupon_cases', [
        'stage' => 'Amicably Settled',
        'settlement_terms' => $terms,
        'settlement_amount' => $amount,
        'settlement_date' => $settlementDate,
        'compliance_due_date' => $complianceDate
    ], 'id = ?', [$id]);

    // Increment cases handled for assigned Pangkat members if any
    $case = db_fetch_one("SELECT pangkat_chairman, pangkat_secretary, pangkat_member FROM `lupon_cases` WHERE id = ?", [$id]);
    if ($case) {
        foreach ([$case['pangkat_chairman'], $case['pangkat_secretary'], $case['pangkat_member']] as $pName) {
            if (!empty($pName)) {
                db_query("UPDATE `lupon_members` SET `cases_handled_count` = `cases_handled_count` + 1 WHERE `full_name` = ?", [$pName]);
            }
        }
    }

    log_audit('SETTLE_KP_CASE', 'lupon_cases', "Executed Amicable Settlement for Case ID #$id (Amount: PHP $amount)");
    json_response(true, ['id' => $id], 'Amicable settlement (Kasunduang Pag-aayos) executed successfully.');
}

// Issue Certificate to File Action (KP Form 20 / CFA)
if ($action === 'issue_cfa' && $method === 'POST') {
    $id = intval($input['id'] ?? 0);
    $reason = trim($input['cfa_reason'] ?? ($input['cfaReason'] ?? ''));
    $cfaDate = $input['cfa_date'] ?? ($input['cfaDate'] ?? date('Y-m-d'));

    if ($id <= 0 || empty($reason)) {
        json_response(false, null, 'Case ID and statutory reason for CFA issuance are required.', 422);
    }

    db_update('lupon_cases', [
        'stage' => 'CFA Issued',
        'cfa_reason' => $reason,
        'cfa_date' => $cfaDate
    ], 'id = ?', [$id]);

    log_audit('ISSUE_CFA', 'lupon_cases', "Issued Certificate to File Action (CFA) for Case ID #$id: $reason");
    json_response(true, ['id' => $id], 'Certificate to File Action (Katunayan Upang Makadulog sa Hukuman) issued.');
}

if ($action === 'delete_case' || ($action === 'delete' && $method === 'POST')) {
    $id = intval($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid case ID.', 422);
    }

    db_delete('lupon_cases', 'id = ?', [$id]);
    log_audit('DELETE_KP_CASE', 'lupon_cases', "Archived/Deleted KP dispute case ID #$id");
    json_response(true, ['id' => $id], 'KP Dispute case removed.');
}

// Default action: List all cases
$stageFilter = $_GET['stage'] ?? '';
$typeFilter  = $_GET['type'] ?? '';
$search      = trim($_GET['search'] ?? '');

$query = "SELECT * FROM `lupon_cases` WHERE 1=1";
$params = [];

if (!empty($stageFilter)) {
    $query .= " AND `stage` = ?";
    $params[] = $stageFilter;
}

if (!empty($typeFilter)) {
    $query .= " AND `dispute_type` = ?";
    $params[] = $typeFilter;
}

if (!empty($search)) {
    $query .= " AND (`case_number` LIKE ? OR `complainant_name` LIKE ? OR `respondent_name` LIKE ? OR `complaint_details` LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$query .= " ORDER BY `date_filed` DESC, `id` DESC";
$cases = db_fetch_all($query, $params);

json_response(true, $cases, 'KP dispute cases docket loaded.');
