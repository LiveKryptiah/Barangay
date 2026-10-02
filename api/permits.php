<?php
/**
 * Barangay Management System (BarangayOS)
 * Business Clearances & Local Permits Hub REST API Endpoint
 * Mandated by Republic Act 7160 (Local Government Code of 1991)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Global authentication is bypassed/managed per persistent admin session
require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$input  = get_json_input();
if (!empty($input['action'])) {
    $action = $input['action'];
}

/**
 * Standard fee schedule helper based on capital investment / gross sales tier
 */
function calculate_permit_fees($grossSalesTier, $capitalInvestment = 0) {
    switch ($grossSalesTier) {
        case 'Large (Above ₱5,000,000)':
            return [
                'clearance_fee'   => 2500.00,
                'garbage_fee'     => 800.00,
                'inspection_fee'  => 500.00,
                'total_fee'       => 3800.00
            ];
        case 'Medium (₱1,500,001 - ₱5,000,000)':
            return [
                'clearance_fee'   => 1200.00,
                'garbage_fee'     => 400.00,
                'inspection_fee'  => 300.00,
                'total_fee'       => 1900.00
            ];
        case 'Small (₱150,001 - ₱1,500,000)':
            return [
                'clearance_fee'   => 600.00,
                'garbage_fee'     => 250.00,
                'inspection_fee'  => 200.00,
                'total_fee'       => 1050.00
            ];
        case 'Micro (Below ₱150,000)':
        default:
            return [
                'clearance_fee'   => 300.00,
                'garbage_fee'     => 150.00,
                'inspection_fee'  => 100.00,
                'total_fee'       => 550.00
            ];
    }
}

// ----------------------------------------------------
// 1. STATS ACTION
// ----------------------------------------------------
if ($action === 'stats') {
    $year = (int)($_GET['year'] ?? date('Y'));
    
    $totalBusinesses   = db_count('business_clearances');
    $issuedCount       = db_count('business_clearances', "`status` = 'Approved & Issued'");
    $pendingReview     = db_count('business_clearances', "`status` = 'Pending Review'");
    $underInspection   = db_count('business_clearances', "`status` = 'Under Inspection'");
    $compliantCount    = db_count('business_clearances', "`inspection_status` = 'Compliant'");
    $deficientCount    = db_count('business_clearances', "`inspection_status` = 'Deficient'");
    $pendingInspection = db_count('business_clearances', "`inspection_status` = 'Pending Inspection'");
    $unpaidCount       = db_count('business_clearances', "`payment_status` = 'Unpaid'");

    // Financial calculations
    $finSum = db_fetch_one("
        SELECT 
            SUM(`clearance_fee`) AS total_clearance,
            SUM(`garbage_fee`) AS total_garbage,
            SUM(`inspection_fee`) AS total_inspection,
            SUM(`total_fee`) AS total_collected
        FROM `business_clearances`
        WHERE `payment_status` = 'Paid'
    ");
    
    $totalCollected   = (float)($finSum['total_collected'] ?? 0);
    $totalClearance   = (float)($finSum['total_clearance'] ?? 0);
    $totalGarbage     = (float)($finSum['total_garbage'] ?? 0);
    $totalInspection  = (float)($finSum['total_inspection'] ?? 0);

    // Breakdown by Business Nature
    $natureRows = db_fetch_all("
        SELECT `business_nature`, COUNT(`id`) as count, SUM(`total_fee`) as projected_revenue
        FROM `business_clearances`
        GROUP BY `business_nature`
        ORDER BY count DESC
    ");

    // Breakdown by Purok
    $purokRows = db_fetch_all("
        SELECT `purok`, COUNT(`id`) as count
        FROM `business_clearances`
        GROUP BY `purok`
        ORDER BY `purok` ASC
    ");

    // Breakdown by Tier
    $tierRows = db_fetch_all("
        SELECT `gross_sales_tier`, COUNT(`id`) as count
        FROM `business_clearances`
        GROUP BY `gross_sales_tier`
        ORDER BY count DESC
    ");

    json_response(true, [
        'total'                => $totalBusinesses,
        'issued'               => $issuedCount,
        'pending_review'       => $pendingReview,
        'under_inspection'     => $underInspection,
        'compliant'            => $compliantCount,
        'deficient'            => $deficientCount,
        'pending_inspection'   => $pendingInspection,
        'unpaid_count'         => $unpaidCount,
        'total_revenue'        => $totalCollected,
        'total_clearance_fee'  => $totalClearance,
        'total_garbage_fee'    => $totalGarbage,
        'total_inspection_fee' => $totalInspection,
        'by_nature'            => $natureRows,
        'by_purok'             => $purokRows,
        'by_tier'              => $tierRows,
    ], "Business clearances telemetry loaded.");
}

// ----------------------------------------------------
// 2. LIST ACTION
// ----------------------------------------------------
if ($action === 'list' || ($method === 'GET' && empty($action))) {
    $where  = ["1=1"];
    $params = [];

    $q = trim($_GET['q'] ?? ($_GET['search'] ?? ''));
    if (!empty($q)) {
        $where[] = "(`business_name` LIKE ? OR `trade_name` LIKE ? OR `owner_name` LIKE ? OR `clearance_no` LIKE ? OR `plate_sticker_no` LIKE ? OR `business_address` LIKE ?)";
        $searchTerm = "%$q%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (!empty($_GET['status'])) {
        $where[]  = "`status` = ?";
        $params[] = $_GET['status'];
    }

    if (!empty($_GET['business_nature'])) {
        $where[]  = "`business_nature` = ?";
        $params[] = $_GET['business_nature'];
    }

    if (!empty($_GET['purok'])) {
        $where[]  = "`purok` = ?";
        $params[] = $_GET['purok'];
    }

    if (!empty($_GET['payment_status'])) {
        $where[]  = "`payment_status` = ?";
        $params[] = $_GET['payment_status'];
    }

    if (!empty($_GET['inspection_status'])) {
        $where[]  = "`inspection_status` = ?";
        $params[] = $_GET['inspection_status'];
    }

    if (!empty($_GET['validity_year'])) {
        $where[]  = "`validity_year` = ?";
        $params[] = (int)$_GET['validity_year'];
    }

    $whereClause = implode(" AND ", $where);
    $sql = "
        SELECT bc.*, 
               r.first_name AS res_first_name, 
               r.last_name AS res_last_name, 
               r.contact_number AS res_contact,
               r.avatar_url AS res_avatar
        FROM `business_clearances` bc
        LEFT JOIN `residents` r ON r.id = bc.owner_resident_id
        WHERE $whereClause
        ORDER BY bc.id DESC
    ";

    $records = db_fetch_all($sql, $params);
    json_response(true, $records, "Fetched " . count($records) . " business clearance(s).");
}

// ----------------------------------------------------
// 3. GET ACTION
// ----------------------------------------------------
if ($action === 'get') {
    $id    = (int)($_GET['id'] ?? ($input['id'] ?? 0));
    $code  = trim($_GET['clearance_no'] ?? ($input['clearance_no'] ?? ''));
    $token = trim($_GET['qr_token'] ?? ($input['qr_token'] ?? ''));

    if ($id > 0) {
        $sql = "
            SELECT bc.*, 
                   r.first_name AS res_first_name, 
                   r.last_name AS res_last_name, 
                   r.contact_number AS res_contact,
                   r.avatar_url AS res_avatar
            FROM `business_clearances` bc
            LEFT JOIN `residents` r ON r.id = bc.owner_resident_id
            WHERE bc.id = ?
        ";
        $record = db_fetch_one($sql, [$id]);
    } elseif (!empty($code)) {
        $sql = "
            SELECT bc.*, 
                   r.first_name AS res_first_name, 
                   r.last_name AS res_last_name, 
                   r.contact_number AS res_contact,
                   r.avatar_url AS res_avatar
            FROM `business_clearances` bc
            LEFT JOIN `residents` r ON r.id = bc.owner_resident_id
            WHERE bc.clearance_no = ?
        ";
        $record = db_fetch_one($sql, [$code]);
    } elseif (!empty($token)) {
        $sql = "
            SELECT bc.*, 
                   r.first_name AS res_first_name, 
                   r.last_name AS res_last_name, 
                   r.contact_number AS res_contact,
                   r.avatar_url AS res_avatar
            FROM `business_clearances` bc
            LEFT JOIN `residents` r ON r.id = bc.owner_resident_id
            WHERE bc.qr_token = ?
        ";
        $record = db_fetch_one($sql, [$token]);
    } else {
        json_response(false, null, "Record ID, Clearance Number, or QR Token required.", 400);
    }

    if (!$record) {
        json_response(false, null, "Business clearance not found.", 404);
    }

    json_response(true, $record, "Business clearance retrieved successfully.");
}

// ----------------------------------------------------
// 4. CREATE ACTION
// ----------------------------------------------------
if ($action === 'create') {
    $businessName     = trim($input['business_name'] ?? '');
    $tradeName        = trim($input['trade_name'] ?? ($businessName));
    $ownerName        = trim($input['owner_name'] ?? '');
    $ownerResidentId  = !empty($input['owner_resident_id']) ? (int)$input['owner_resident_id'] : null;
    $ownerContact     = trim($input['owner_contact'] ?? '');
    $ownerAddress     = trim($input['owner_address'] ?? '');
    $businessNature   = trim($input['business_nature'] ?? 'Retail / Sari-Sari Store');
    $ownershipType    = trim($input['ownership_type'] ?? 'Sole Proprietorship');
    $purok            = trim($input['purok'] ?? 'Purok 1');
    $businessAddress  = trim($input['business_address'] ?? '');
    $capitalInvestment= (float)($input['capital_investment'] ?? 0);
    $grossSalesTier   = trim($input['gross_sales_tier'] ?? 'Micro (Below ₱150,000)');
    $applicationType  = trim($input['application_type'] ?? 'New');
    $validityYear     = (int)($input['validity_year'] ?? date('Y'));
    $remarks          = trim($input['remarks'] ?? '');

    if (empty($businessName)) {
        json_response(false, null, "Business name is required.", 422);
    }
    if (empty($ownerName)) {
        json_response(false, null, "Proprietor / Owner name is required.", 422);
    }
    if (empty($businessAddress)) {
        json_response(false, null, "Business address is required.", 422);
    }

    // Auto-compute fees if not specifically overridden
    $feeDefaults = calculate_permit_fees($grossSalesTier, $capitalInvestment);
    $clearanceFee  = isset($input['clearance_fee']) ? (float)$input['clearance_fee'] : $feeDefaults['clearance_fee'];
    $garbageFee    = isset($input['garbage_fee']) ? (float)$input['garbage_fee'] : $feeDefaults['garbage_fee'];
    $inspectionFee = isset($input['inspection_fee']) ? (float)$input['inspection_fee'] : $feeDefaults['inspection_fee'];
    $totalFee      = $clearanceFee + $garbageFee + $inspectionFee;

    // Generate unique control clearance_no: BBC-YYYY-NNNNN
    $count = db_count('business_clearances', "`validity_year` = ?", [$validityYear]);
    $clearanceNo = sprintf("BBC-%04d-%05d", $validityYear, $count + 1);

    // Generate tamper-evident QR token
    $qrToken = bin2hex(random_bytes(32));

    $data = [
        'clearance_no'       => $clearanceNo,
        'business_name'      => $businessName,
        'trade_name'         => $tradeName,
        'owner_resident_id'  => $ownerResidentId,
        'owner_name'         => $ownerName,
        'owner_contact'      => $ownerContact,
        'owner_address'      => $ownerAddress,
        'business_nature'    => $businessNature,
        'ownership_type'     => $ownershipType,
        'purok'              => $purok,
        'business_address'   => $businessAddress,
        'capital_investment' => $capitalInvestment,
        'gross_sales_tier'   => $grossSalesTier,
        'application_type'   => $applicationType,
        'clearance_fee'      => $clearanceFee,
        'garbage_fee'        => $garbageFee,
        'inspection_fee'     => $inspectionFee,
        'total_fee'          => $totalFee,
        'or_number'          => null,
        'payment_status'     => 'Unpaid',
        'payment_date'       => null,
        'inspection_status'  => 'Pending Inspection',
        'inspected_by'       => null,
        'inspection_date'    => null,
        'inspection_notes'   => null,
        'status'             => 'Pending Review',
        'plate_sticker_no'   => null,
        'qr_token'           => $qrToken,
        'validity_year'      => $validityYear,
        'issue_date'         => null,
        'expiry_date'        => null,
        'issued_by'          => 'Maria Santos - Barangay Treasurer',
        'approved_by'        => 'Hon. Antonio S. Valdez - Punong Barangay',
        'remarks'            => $remarks
    ];

    $newId = db_insert('business_clearances', $data);
    if (!$newId) {
        json_response(false, null, "Failed to register business clearance application.", 500);
    }

    log_audit_action('create_business_clearance', 'business_clearances', "Created business clearance $clearanceNo for $businessName (Owner: $ownerName)");

    $created = db_fetch_one("SELECT * FROM `business_clearances` WHERE id = ?", [$newId]);
    json_response(true, $created, "Business clearance registered successfully.", 201);
}

// ----------------------------------------------------
// 5. UPDATE ACTION
// ----------------------------------------------------
if ($action === 'update') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, "Valid record ID is required.", 400);
    }

    $existing = db_fetch_one("SELECT * FROM `business_clearances` WHERE id = ?", [$id]);
    if (!$existing) {
        json_response(false, null, "Business clearance record not found.", 404);
    }

    $businessName     = trim($input['business_name'] ?? $existing['business_name']);
    $tradeName        = trim($input['trade_name'] ?? $existing['trade_name']);
    $ownerName        = trim($input['owner_name'] ?? $existing['owner_name']);
    $ownerResidentId  = array_key_exists('owner_resident_id', $input) ? ($input['owner_resident_id'] ? (int)$input['owner_resident_id'] : null) : $existing['owner_resident_id'];
    $ownerContact     = trim($input['owner_contact'] ?? $existing['owner_contact']);
    $ownerAddress     = trim($input['owner_address'] ?? $existing['owner_address']);
    $businessNature   = trim($input['business_nature'] ?? $existing['business_nature']);
    $ownershipType    = trim($input['ownership_type'] ?? $existing['ownership_type']);
    $purok            = trim($input['purok'] ?? $existing['purok']);
    $businessAddress  = trim($input['business_address'] ?? $existing['business_address']);
    $capitalInvestment= isset($input['capital_investment']) ? (float)$input['capital_investment'] : (float)$existing['capital_investment'];
    $grossSalesTier   = trim($input['gross_sales_tier'] ?? $existing['gross_sales_tier']);
    $applicationType  = trim($input['application_type'] ?? $existing['application_type']);
    $remarks          = trim($input['remarks'] ?? $existing['remarks']);

    $clearanceFee  = isset($input['clearance_fee']) ? (float)$input['clearance_fee'] : (float)$existing['clearance_fee'];
    $garbageFee    = isset($input['garbage_fee']) ? (float)$input['garbage_fee'] : (float)$existing['garbage_fee'];
    $inspectionFee = isset($input['inspection_fee']) ? (float)$input['inspection_fee'] : (float)$existing['inspection_fee'];
    $totalFee      = $clearanceFee + $garbageFee + $inspectionFee;

    $updateData = [
        'business_name'      => $businessName,
        'trade_name'         => $tradeName,
        'owner_resident_id'  => $ownerResidentId,
        'owner_name'         => $ownerName,
        'owner_contact'      => $ownerContact,
        'owner_address'      => $ownerAddress,
        'business_nature'    => $businessNature,
        'ownership_type'     => $ownershipType,
        'purok'              => $purok,
        'business_address'   => $businessAddress,
        'capital_investment' => $capitalInvestment,
        'gross_sales_tier'   => $grossSalesTier,
        'application_type'   => $applicationType,
        'clearance_fee'      => $clearanceFee,
        'garbage_fee'        => $garbageFee,
        'inspection_fee'     => $inspectionFee,
        'total_fee'          => $totalFee,
        'remarks'            => $remarks
    ];

    if (!empty($input['status'])) {
        $updateData['status'] = $input['status'];
    }

    db_update('business_clearances', $updateData, "id = ?", [$id]);
    log_audit_action('update_business_clearance', 'business_clearances', "Updated details for {$existing['clearance_no']} ($businessName)");

    $updated = db_fetch_one("SELECT * FROM `business_clearances` WHERE id = ?", [$id]);
    json_response(true, $updated, "Business clearance updated successfully.");
}

// ----------------------------------------------------
// 6. RECORD PAYMENT ACTION (Live Syncs to Budget & Revenue)
// ----------------------------------------------------
if ($action === 'record_payment') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, "Record ID is required.", 400);
    }

    $existing = db_fetch_one("SELECT * FROM `business_clearances` WHERE id = ?", [$id]);
    if (!$existing) {
        json_response(false, null, "Business clearance record not found.", 404);
    }

    $year = date('Y');
    $orNumber = trim($input['or_number'] ?? '');
    if (empty($orNumber)) {
        // Auto-generate official receipt number
        $orCount = db_count('revenue_collections', "YEAR(created_at) = ?", [$year]);
        $orNumber = sprintf("OR-%s-%04d", $year, $orCount + 1);
    }

    $paymentDate = !empty($input['payment_date']) ? $input['payment_date'] : date('Y-m-d');
    $paymentStatus = trim($input['payment_status'] ?? 'Paid');

    // Update business clearance
    db_update('business_clearances', [
        'or_number'      => $orNumber,
        'payment_status' => $paymentStatus,
        'payment_date'   => $paymentDate,
        'status'         => ($existing['status'] === 'Pending Review' && $existing['inspection_status'] === 'Compliant') ? 'Under Inspection' : $existing['status']
    ], "id = ?", [$id]);

    // Live Sync to Table 24: revenue_collections (General Fund, 'Business Permits')
    $existingRevenue = db_fetch_one("SELECT id FROM `revenue_collections` WHERE or_number = ?", [$orNumber]);
    if (!$existingRevenue && $paymentStatus === 'Paid') {
        $revenueData = [
            'or_number'        => $orNumber,
            'rcd_number'       => 'RCD-' . date('Y-m'),
            'payer_name'       => $existing['owner_name'] . ' (' . $existing['business_name'] . ')',
            'revenue_source'   => 'Business Permits',
            'particulars'      => 'Barangay Business Clearance Fee (' . $existing['clearance_no'] . ' - ' . $existing['business_nature'] . ')',
            'amount'           => (float)$existing['total_fee'],
            'fund_destination' => 'General Fund',
            'collected_by'     => 'Maria Santos - Barangay Treasurer',
            'receipt_date'     => $paymentDate,
            'status'           => 'Collected'
        ];
        db_insert('revenue_collections', $revenueData);
    }

    log_audit_action('record_business_permit_payment', 'business_clearances', "Processed payment of ₱" . number_format($existing['total_fee'], 2) . " with {$orNumber} for {$existing['clearance_no']}");

    $updated = db_fetch_one("SELECT * FROM `business_clearances` WHERE id = ?", [$id]);
    json_response(true, $updated, "Payment recorded and synced to Barangay Revenue Collections.");
}

// ----------------------------------------------------
// 7. RECORD INSPECTION ACTION
// ----------------------------------------------------
if ($action === 'record_inspection') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, "Record ID is required.", 400);
    }

    $existing = db_fetch_one("SELECT * FROM `business_clearances` WHERE id = ?", [$id]);
    if (!$existing) {
        json_response(false, null, "Business clearance record not found.", 404);
    }

    $inspectionStatus = trim($input['inspection_status'] ?? 'Compliant');
    $inspectedBy      = trim($input['inspected_by'] ?? 'Tanod Insp. Roberto Diaz');
    $inspectionDate    = !empty($input['inspection_date']) ? $input['inspection_date'] : date('Y-m-d');
    $inspectionNotes   = trim($input['inspection_notes'] ?? '');

    $newOverallStatus = $existing['status'];
    if ($existing['status'] === 'Pending Review') {
        $newOverallStatus = 'Under Inspection';
    }

    db_update('business_clearances', [
        'inspection_status' => $inspectionStatus,
        'inspected_by'      => $inspectedBy,
        'inspection_date'   => $inspectionDate,
        'inspection_notes'  => $inspectionNotes,
        'status'            => $newOverallStatus
    ], "id = ?", [$id]);

    log_audit_action('record_business_inspection', 'business_clearances', "Recorded inspection ($inspectionStatus) by $inspectedBy for {$existing['clearance_no']}");

    $updated = db_fetch_one("SELECT * FROM `business_clearances` WHERE id = ?", [$id]);
    json_response(true, $updated, "Business inspection details recorded.");
}

// ----------------------------------------------------
// 8. APPROVE & ISSUE CLEARANCE ACTION
// ----------------------------------------------------
if ($action === 'approve_issue') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, "Record ID is required.", 400);
    }

    $existing = db_fetch_one("SELECT * FROM `business_clearances` WHERE id = ?", [$id]);
    if (!$existing) {
        json_response(false, null, "Business clearance record not found.", 404);
    }

    $year = date('Y');
    $plateNo = trim($input['plate_sticker_no'] ?? $existing['plate_sticker_no']);
    if (empty($plateNo)) {
        $issuedCount = db_count('business_clearances', "`plate_sticker_no` IS NOT NULL AND `validity_year` = ?", [$year]);
        $plateNo = sprintf("BP-%04d-%04d", $year, $issuedCount + 1);
    }

    $issueDate  = date('Y-m-d');
    $expiryDate = sprintf("%04d-12-31", $year);
    $approvedBy = trim($input['approved_by'] ?? 'Hon. Antonio S. Valdez - Punong Barangay');
    $issuedBy   = trim($input['issued_by'] ?? 'Maria Santos - Barangay Treasurer');

    db_update('business_clearances', [
        'status'           => 'Approved & Issued',
        'plate_sticker_no' => $plateNo,
        'issue_date'       => $issueDate,
        'expiry_date'      => $expiryDate,
        'approved_by'      => $approvedBy,
        'issued_by'        => $issuedBy
    ], "id = ?", [$id]);

    log_audit_action('issue_business_clearance', 'business_clearances', "Approved & Issued official clearance {$existing['clearance_no']} with plate sticker $plateNo to {$existing['business_name']}");

    $updated = db_fetch_one("SELECT * FROM `business_clearances` WHERE id = ?", [$id]);
    json_response(true, $updated, "Barangay Business Clearance approved & officially issued.");
}

// ----------------------------------------------------
// 9. DELETE ACTION
// ----------------------------------------------------
if ($action === 'delete') {
    $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
    if ($id <= 0) {
        json_response(false, null, "Record ID is required.", 400);
    }

    $existing = db_fetch_one("SELECT * FROM `business_clearances` WHERE id = ?", [$id]);
    if (!$existing) {
        json_response(false, null, "Business clearance record not found.", 404);
    }

    db_delete('business_clearances', "id = ?", [$id]);
    log_audit_action('delete_business_clearance', 'business_clearances', "Deleted business clearance {$existing['clearance_no']} ({$existing['business_name']})");

    json_response(true, ['id' => $id], "Business clearance deleted successfully.");
}

// Fallback for unknown action
json_response(false, null, "Invalid or unsupported API action: " . htmlspecialchars($action), 400);
