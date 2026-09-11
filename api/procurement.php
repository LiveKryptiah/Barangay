<?php
/**
 * Barangay Management System (BarangayOS)
 * Barangay Bids, Awards, Budget & Procurement REST API Endpoint
 * Compliant with RA 9184 (Procurement Reform Act) & DILG Full Disclosure Policy
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
// 1. PUBLIC DISCLOSURES ACTION (No staff auth required for citizen transparency portal)
// ----------------------------------------------------
if ($action === 'public_disclosures' || $action === 'public_list') {
    $fiscalYear = $_GET['year'] ?? date('Y');
    
    // Public procurement projects (non-draft)
    $projects = db_fetch_all("
        SELECT p.*, b.program_title AS aip_program,
               (SELECT COUNT(*) FROM `procurement_bids` WHERE project_id = p.id) AS bids_count
        FROM `procurement_projects` p
        LEFT JOIN `budget_allocations` b ON p.budget_allocation_id = b.id
        WHERE p.status != 'PR Draft'
        ORDER BY p.id DESC
    ");

    // Public statutory budget allocations summary
    $budget = db_fetch_all("
        SELECT * FROM `budget_allocations` 
        WHERE `fiscal_year` = ?
        ORDER BY approved_budget DESC
    ", [$fiscalYear]);

    $totalBudgetRow = db_fetch_one("SELECT COALESCE(SUM(approved_budget), 0) AS total_budget, COALESCE(SUM(obligated_amount), 0) AS total_obligated FROM `budget_allocations` WHERE `fiscal_year` = ?", [$fiscalYear]);

    json_response(true, [
        'fiscal_year' => $fiscalYear,
        'projects' => $projects,
        'budget_allocations' => $budget,
        'summary' => [
            'total_budget' => (float)($totalBudgetRow['total_budget'] ?? 0),
            'total_obligated' => (float)($totalBudgetRow['total_obligated'] ?? 0),
            'balance' => (float)(($totalBudgetRow['total_budget'] ?? 0) - ($totalBudgetRow['total_obligated'] ?? 0))
        ]
    ], 'Public procurement disclosures loaded.');
}

// All subsequent actions require authenticated session
require_auth();

// ----------------------------------------------------
// 2. STATS ACTION
// ----------------------------------------------------
if ($action === 'stats') {
    $totalProjects = db_count('procurement_projects');
    $draftProjects = db_count('procurement_projects', "`status` = 'PR Draft'");
    $openCanvass   = db_count('procurement_projects', "`status` IN ('Approved for Canvass', 'Canvass / RFQ Open')");
    $evaluated     = db_count('procurement_projects', "`status` = 'Bids Evaluated'");
    $awarded       = db_count('procurement_projects', "`status` = 'Awarded / PO Issued'");
    $delivered     = db_count('procurement_projects', "`status` IN ('Delivered & Inspected', 'Completed')");
    $totalBids     = db_count('procurement_bids');

    // Financial totals
    $budgetRow = db_fetch_one("
        SELECT COALESCE(SUM(approved_budget), 0) AS total_budget, 
               COALESCE(SUM(obligated_amount), 0) AS total_obligated 
        FROM `budget_allocations`
    ");
    $totalBudget = (float)($budgetRow['total_budget'] ?? 0);
    $totalObligated = (float)($budgetRow['total_obligated'] ?? 0);
    $remainingBudget = max(0, $totalBudget - $totalObligated);

    // Savings calculation from awarded & delivered projects
    $savingsRow = db_fetch_one("
        SELECT COALESCE(SUM(abc_amount - winning_amount), 0) AS total_savings,
               COALESCE(SUM(abc_amount), 0) AS total_abc_awarded,
               COALESCE(SUM(winning_amount), 0) AS total_contract_awarded
        FROM `procurement_projects`
        WHERE `status` IN ('Awarded / PO Issued', 'Delivered & Inspected', 'Completed') 
          AND `winning_amount` IS NOT NULL 
          AND `winning_amount` > 0
    ");
    $totalSavings = max(0, (float)($savingsRow['total_savings'] ?? 0));

    // Breakdown by Fund Source
    $fundBreakdown = db_fetch_all("
        SELECT fund_source, 
               COALESCE(SUM(approved_budget), 0) AS total_allocated,
               COALESCE(SUM(obligated_amount), 0) AS total_obligated,
               (COALESCE(SUM(approved_budget), 0) - COALESCE(SUM(obligated_amount), 0)) AS balance
        FROM `budget_allocations`
        GROUP BY fund_source
        ORDER BY total_allocated DESC
    ");

    json_response(true, [
        'total'                 => $totalProjects,
        'draft_count'           => $draftProjects,
        'open_canvass_count'    => $openCanvass,
        'evaluated_count'       => $evaluated,
        'awarded_count'         => $awarded,
        'delivered_count'       => $delivered,
        'total_bids'            => $totalBids,
        'total_budget'          => $totalBudget,
        'total_obligated'       => $totalObligated,
        'remaining_budget'      => $remainingBudget,
        'total_savings'         => $totalSavings,
        'fund_breakdown'        => $fundBreakdown
    ]);
}

// ----------------------------------------------------
// 3. BUDGET ALLOCATIONS LIST & CRUD
// ----------------------------------------------------
if ($action === 'budget') {
    if ($method === 'GET') {
        $year = trim($_GET['year'] ?? '');
        if ($year !== '') {
            $allocations = db_fetch_all("SELECT * FROM `budget_allocations` WHERE fiscal_year = ? ORDER BY id ASC", [$year]);
        } else {
            $allocations = db_fetch_all("SELECT * FROM `budget_allocations` ORDER BY fiscal_year DESC, id ASC");
        }
        json_response(true, $allocations);
    }
}

if ($action === 'create_budget') {
    $year        = trim($input['fiscal_year'] ?? date('Y'));
    $fundSource  = trim($input['fund_source'] ?? '20% Barangay Development Fund');
    $program     = trim($input['program_title'] ?? '');
    $committee   = trim($input['implementing_committee'] ?? 'Committee on Appropriations');
    $approved    = (float)($input['approved_budget'] ?? 0);
    $obligated   = (float)($input['obligated_amount'] ?? 0);

    if (empty($program) || $approved <= 0) {
        json_response(false, null, 'Program title and approved budget amount (> 0) are required.', 400);
    }

    $id = db_insert('budget_allocations', [
        'fiscal_year'            => $year,
        'fund_source'            => $fundSource,
        'program_title'          => $program,
        'implementing_committee' => $committee,
        'approved_budget'        => $approved,
        'obligated_amount'       => $obligated
    ]);

    log_audit_action('CREATE_BUDGET', 'budget_allocations', "Created AIP budget item: {$program} (PHP " . number_format($approved, 2) . ")");
    $created = db_fetch_one("SELECT * FROM `budget_allocations` WHERE id = ?", [$id]);
    json_response(true, $created, 'Budget allocation added successfully.', 201);
}

if ($action === 'update_budget') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Budget allocation ID is required.', 400);
    }

    $data = [];
    if (isset($input['program_title'])) $data['program_title'] = trim($input['program_title']);
    if (isset($input['fund_source'])) $data['fund_source'] = trim($input['fund_source']);
    if (isset($input['implementing_committee'])) $data['implementing_committee'] = trim($input['implementing_committee']);
    if (isset($input['approved_budget'])) $data['approved_budget'] = (float)$input['approved_budget'];
    if (isset($input['obligated_amount'])) $data['obligated_amount'] = (float)$input['obligated_amount'];

    if (!empty($data)) {
        db_update('budget_allocations', $data, 'id = ?', [$id]);
        log_audit_action('UPDATE_BUDGET', 'budget_allocations', "Updated budget allocation ID #{$id}");
    }

    $updated = db_fetch_one("SELECT * FROM `budget_allocations` WHERE id = ?", [$id]);
    json_response(true, $updated, 'Budget allocation updated successfully.');
}

// ----------------------------------------------------
// 4. BIDS LIST & CRUD
// ----------------------------------------------------
if ($action === 'bids') {
    $projectId = (int)($_GET['project_id'] ?? ($input['project_id'] ?? 0));
    if ($projectId <= 0) {
        $bids = db_fetch_all("SELECT b.*, p.project_title, p.pr_number FROM `procurement_bids` b JOIN `procurement_projects` p ON b.project_id = p.id ORDER BY b.id DESC");
    } else {
        $bids = db_fetch_all("SELECT * FROM `procurement_bids` WHERE project_id = ? ORDER BY quotation_amount ASC, id ASC", [$projectId]);
    }
    json_response(true, $bids);
}

if ($action === 'add_bid') {
    $projectId  = (int)($input['project_id'] ?? 0);
    $supplier   = trim($input['supplier_name'] ?? '');
    $tin        = trim($input['tin_number'] ?? '');
    $contact    = trim($input['contact_person'] ?? '');
    $phone      = trim($input['contact_no'] ?? ($input['phone'] ?? ''));
    $quote      = (float)($input['quotation_amount'] ?? 0);
    $compliance = trim($input['compliance_status'] ?? 'Responsive');
    $remarks    = trim($input['remarks'] ?? '');

    if ($projectId <= 0 || empty($supplier) || $quote <= 0) {
        json_response(false, null, 'Project ID, supplier name, and quotation amount (> 0) are required.', 400);
    }

    $bidId = db_insert('procurement_bids', [
        'project_id'        => $projectId,
        'supplier_name'     => $supplier,
        'tin_number'        => $tin,
        'contact_person'    => $contact,
        'contact_no'        => $phone,
        'quotation_amount'  => $quote,
        'compliance_status' => $compliance,
        'ranking'           => 99,
        'remarks'           => $remarks
    ]);

    // Recalculate rankings for all responsive bids in this project
    $projectBids = db_fetch_all("
        SELECT id FROM `procurement_bids` 
        WHERE project_id = ? AND compliance_status = 'Responsive' 
        ORDER BY quotation_amount ASC, id ASC
    ", [$projectId]);

    $rank = 1;
    foreach ($projectBids as $pb) {
        db_update('procurement_bids', ['ranking' => $rank], 'id = ?', [$pb['id']]);
        $rank++;
    }

    log_audit_action('ADD_BID', 'procurement_bids', "Added bid from {$supplier} for project #{$projectId}: PHP " . number_format($quote, 2));
    $newBid = db_fetch_one("SELECT * FROM `procurement_bids` WHERE id = ?", [$bidId]);
    json_response(true, $newBid, 'Supplier quotation recorded.', 201);
}

if ($action === 'delete_bid') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Bid ID required.', 400);
    }
    $bid = db_fetch_one("SELECT * FROM `procurement_bids` WHERE id = ?", [$id]);
    if ($bid) {
        db_delete('procurement_bids', 'id = ?', [$id]);
        // Recalculate rankings
        $projectBids = db_fetch_all("
            SELECT id FROM `procurement_bids` 
            WHERE project_id = ? AND compliance_status = 'Responsive' 
            ORDER BY quotation_amount ASC
        ", [$bid['project_id']]);
        $rank = 1;
        foreach ($projectBids as $pb) {
            db_update('procurement_bids', ['ranking' => $rank], 'id = ?', [$pb['id']]);
            $rank++;
        }
        log_audit_action('DELETE_BID', 'procurement_bids', "Deleted bid ID #{$id}");
    }
    json_response(true, null, 'Bid removed.');
}

// ----------------------------------------------------
// 5. AWARD PROJECT & ISSUE PO ACTION
// ----------------------------------------------------
if ($action === 'award') {
    $projectId     = (int)($input['project_id'] ?? ($input['id'] ?? 0));
    $winningBidder = trim($input['winning_bidder'] ?? '');
    $winningAmount = (float)($input['winning_amount'] ?? 0);
    $poNumber      = trim($input['po_number'] ?? '');
    $deliveryDays  = (int)($input['target_delivery_days'] ?? 15);

    if ($projectId <= 0 || empty($winningBidder) || $winningAmount <= 0) {
        json_response(false, null, 'Project ID, winning bidder name, and awarded amount are required.', 400);
    }

    $project = db_fetch_one("SELECT * FROM `procurement_projects` WHERE id = ?", [$projectId]);
    if (!$project) {
        json_response(false, null, 'Procurement project not found.', 404);
    }

    if (empty($poNumber)) {
        $year = date('Y');
        $lastPO = db_fetch_one("SELECT id FROM `procurement_projects` WHERE po_number IS NOT NULL ORDER BY id DESC LIMIT 1");
        $nextSeq = ($lastPO ? (int)$lastPO['id'] : 0) + 1;
        $poNumber = sprintf('PO-%s-%04d', $year, $nextSeq);
    }

    db_update('procurement_projects', [
        'status'               => 'Awarded / PO Issued',
        'winning_bidder'       => $winningBidder,
        'winning_amount'       => $winningAmount,
        'po_number'            => $poNumber,
        'date_awarded'         => date('Y-m-d'),
        'target_delivery_days' => $deliveryDays
    ], 'id = ?', [$projectId]);

    // Obligate budget allocation if linked and not already obligated
    if (!empty($project['budget_allocation_id'])) {
        $allocId = (int)$project['budget_allocation_id'];
        $alloc = db_fetch_one("SELECT * FROM `budget_allocations` WHERE id = ?", [$allocId]);
        if ($alloc) {
            $newObligated = (float)$alloc['obligated_amount'] + $winningAmount;
            db_update('budget_allocations', ['obligated_amount' => $newObligated], 'id = ?', [$allocId]);
        }
    }

    log_audit_action('AWARD_PROCUREMENT', 'procurement_projects', "Awarded {$project['pr_number']} ({$poNumber}) to {$winningBidder} for PHP " . number_format($winningAmount, 2));

    $updated = db_fetch_one("SELECT * FROM `procurement_projects` WHERE id = ?", [$projectId]);
    json_response(true, $updated, "Contract awarded and {$poNumber} generated successfully.");
}

// ----------------------------------------------------
// 6. SINGLE PROJECT DETAIL WITH BIDS & BUDGET
// ----------------------------------------------------
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? ($input['id'] ?? 0));
    if ($id <= 0) {
        json_response(false, null, 'Project ID is required.', 400);
    }

    $project = db_fetch_one("
        SELECT p.*, b.program_title AS aip_program, b.approved_budget AS aip_total, b.obligated_amount AS aip_obligated
        FROM `procurement_projects` p
        LEFT JOIN `budget_allocations` b ON p.budget_allocation_id = b.id
        WHERE p.id = ?
    ", [$id]);

    if (!$project) {
        json_response(false, null, 'Project not found.', 404);
    }

    $bids = db_fetch_all("
        SELECT * FROM `procurement_bids` 
        WHERE project_id = ? 
        ORDER BY quotation_amount ASC, id ASC
    ", [$id]);

    $project['bids'] = $bids;
    json_response(true, $project);
}

// ----------------------------------------------------
// 7. CREATE PROCUREMENT PROJECT
// ----------------------------------------------------
if ($action === 'create_project' || ($method === 'POST' && empty($action))) {
    $title         = trim($input['project_title'] ?? '');
    $classification= trim($input['classification'] ?? 'Goods & Supplies');
    $mode          = trim($input['procurement_mode'] ?? 'Small Value Procurement (SVP)');
    $fundSource    = trim($input['fund_source'] ?? '20% Barangay Development Fund');
    $budgetId      = !empty($input['budget_allocation_id']) ? (int)$input['budget_allocation_id'] : null;
    $abc           = (float)($input['abc_amount'] ?? 0);
    $philgeps      = trim($input['philgeps_ref'] ?? '');
    $committee     = trim($input['end_user_committee'] ?? 'Committee on Infrastructure');
    $status        = trim($input['status'] ?? 'PR Draft');
    $deliveryDays  = (int)($input['target_delivery_days'] ?? 15);
    $prNumber      = trim($input['pr_number'] ?? '');

    if (empty($title) || $abc <= 0) {
        json_response(false, null, 'Project title and Approved Budget for the Contract (ABC > 0) are required.', 400);
    }

    if (empty($prNumber)) {
        $year = date('Y');
        $lastPR = db_fetch_one("SELECT id FROM `procurement_projects` ORDER BY id DESC LIMIT 1");
        $nextSeq = ($lastPR ? (int)$lastPR['id'] : 0) + 1;
        $prNumber = sprintf('PR-%s-%04d', $year, $nextSeq);
    }

    if (empty($philgeps)) {
        $philgeps = 'PHILGEPS-' . date('Y') . '-' . rand(10000, 99999);
    }

    $projectId = db_insert('procurement_projects', [
        'pr_number'            => $prNumber,
        'project_title'        => $title,
        'classification'       => $classification,
        'procurement_mode'     => $mode,
        'fund_source'          => $fundSource,
        'budget_allocation_id' => $budgetId,
        'abc_amount'           => $abc,
        'philgeps_ref'         => $philgeps,
        'end_user_committee'   => $committee,
        'status'               => $status,
        'target_delivery_days' => $deliveryDays
    ]);

    log_audit_action('CREATE_PROJECT', 'procurement_projects', "Created procurement PR {$prNumber}: {$title} (ABC: PHP " . number_format($abc, 2) . ")");

    $created = db_fetch_one("SELECT * FROM `procurement_projects` WHERE id = ?", [$projectId]);
    json_response(true, $created, 'Procurement project initialized.', 201);
}

// ----------------------------------------------------
// 8. UPDATE PROCUREMENT PROJECT
// ----------------------------------------------------
if ($action === 'update_project') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Project ID required.', 400);
    }

    $data = [];
    $allowed = [
        'project_title', 'classification', 'procurement_mode', 'fund_source',
        'budget_allocation_id', 'abc_amount', 'philgeps_ref', 'end_user_committee',
        'status', 'winning_bidder', 'winning_amount', 'po_number',
        'date_awarded', 'target_delivery_days'
    ];

    foreach ($allowed as $f) {
        if (isset($input[$f])) {
            $data[$f] = $input[$f];
        }
    }

    if (!empty($data)) {
        db_update('procurement_projects', $data, 'id = ?', [$id]);
        log_audit_action('UPDATE_PROJECT', 'procurement_projects', "Updated project ID #{$id}");
    }

    $updated = db_fetch_one("SELECT * FROM `procurement_projects` WHERE id = ?", [$id]);
    json_response(true, $updated, 'Project updated successfully.');
}

// ----------------------------------------------------
// 9. DELETE PROCUREMENT PROJECT
// ----------------------------------------------------
if ($action === 'delete_project' || $action === 'delete') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Project ID required.', 400);
    }

    $project = db_fetch_one("SELECT * FROM `procurement_projects` WHERE id = ?", [$id]);
    if ($project) {
        db_delete('procurement_bids', 'project_id = ?', [$id]);
        db_delete('procurement_projects', 'id = ?', [$id]);
        log_audit_action('DELETE_PROJECT', 'procurement_projects', "Deleted project {$project['pr_number']}: {$project['project_title']}");
    }

    json_response(true, null, 'Project deleted successfully.');
}

// ----------------------------------------------------
// 10. DEFAULT: LIST PROJECTS (WITH SEARCH & FILTER)
// ----------------------------------------------------
$q      = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$fund   = trim($_GET['fund_source'] ?? '');

$where = [];
$params = [];

if (!empty($q)) {
    $where[] = "(p.pr_number LIKE ? OR p.po_number LIKE ? OR p.project_title LIKE ? OR p.philgeps_ref LIKE ? OR p.winning_bidder LIKE ?)";
    $qWild = "%{$q}%";
    $params = array_merge($params, [$qWild, $qWild, $qWild, $qWild, $qWild]);
}

if (!empty($status)) {
    $where[] = "p.status = ?";
    $params[] = $status;
}

if (!empty($fund)) {
    $where[] = "p.fund_source = ?";
    $params[] = $fund;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT p.*, b.program_title AS aip_program,
           (SELECT COUNT(*) FROM `procurement_bids` WHERE project_id = p.id) AS bids_count,
           (SELECT MIN(quotation_amount) FROM `procurement_bids` WHERE project_id = p.id AND compliance_status = 'Responsive') AS lowest_bid_amount
    FROM `procurement_projects` p
    LEFT JOIN `budget_allocations` b ON p.budget_allocation_id = b.id
    {$whereClause}
    ORDER BY p.id DESC
";

$projects = db_fetch_all($sql, $params);
json_response(true, $projects);
