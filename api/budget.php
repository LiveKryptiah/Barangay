<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$input  = get_json_input();
if (!empty($input['action'])) {
    $action = $input['action'];
}

require_auth();

$current_year = date('Y');

// Action aliases for seamless frontend compatibility
if ($action === 'get_allocations') $action = 'allocations';
if ($action === 'get_vouchers') $action = 'vouchers';
if ($action === 'get_collections') $action = 'collections';
if ($action === 'get_reports') $action = 'reports';
if ($action === 'get_fund_summary') $action = 'fund_summary';
if ($action === 'save_allocation') $action = !empty($input['id']) ? 'update_allocation' : 'create_allocation';
if ($action === 'save_voucher') $action = !empty($input['id']) ? 'update_voucher' : 'create_voucher';
if ($action === 'save_collection') $action = !empty($input['id']) ? 'update_collection' : 'record_collection';
if ($action === 'delete_allocation') { $input['type'] = 'allocation'; $action = 'delete'; }
if ($action === 'delete_voucher') { $input['type'] = 'voucher'; $action = 'delete'; }
if ($action === 'delete_collection') { $input['type'] = 'collection'; $action = 'delete'; }
if ($action === 'delete_report') { $input['type'] = 'report'; $action = 'delete'; }
if ($action === 'delete_obligation') { $input['type'] = 'obligation'; $action = 'delete'; }
if ($action === 'update_voucher_status') {
    if (empty($input['new_status']) && !empty($input['status'])) {
        $input['new_status'] = $input['status'];
    }
    $action = 'approve_voucher';
}

if ($action === 'stats') {
    // Dashboard KPIs
    $year = $_GET['year'] ?? $current_year;
    
    // total_budget: SUM of budget_allocations.approved_budget for current fiscal year
    $budget_sql = "SELECT SUM(approved_budget) as total_budget, SUM(obligated_amount) as total_obligated FROM `budget_allocations` WHERE fiscal_year = ?";
    $budget_res = db_fetch_one($budget_sql, [$year]);
    $total_budget = (float)($budget_res['total_budget'] ?? 0);
    $total_obligated = (float)($budget_res['total_obligated'] ?? 0);
    $available_balance = $total_budget - $total_obligated;
    
    // total_disbursements: SUM of disbursement_vouchers.amount WHERE status != 'Cancelled'
    $disb_sql = "SELECT SUM(amount) as total_disbursements FROM `disbursement_vouchers` WHERE status != 'Cancelled' AND YEAR(created_at) = ?";
    $disb_res = db_fetch_one($disb_sql, [$year]);
    $total_disbursements = (float)($disb_res['total_disbursements'] ?? 0);
    
    // total_revenue: SUM of revenue_collections.amount WHERE status != 'Voided'
    $rev_sql = "SELECT SUM(amount) as total_revenue FROM `revenue_collections` WHERE status != 'Voided' AND YEAR(created_at) = ?";
    $rev_res = db_fetch_one($rev_sql, [$year]);
    $total_revenue = (float)($rev_res['total_revenue'] ?? 0);
    
    // pending_vouchers: COUNT of disbursement_vouchers WHERE status IN ('Draft', 'Certified')
    $pending_vouchers_sql = "SELECT COUNT(*) as count FROM `disbursement_vouchers` WHERE status IN ('Draft', 'Certified')";
    $pending_vouchers = (int)(db_fetch_one($pending_vouchers_sql, [])['count'] ?? 0);
    
    // released_vouchers: COUNT WHERE status = 'Released'
    $released_vouchers_sql = "SELECT COUNT(*) as count FROM `disbursement_vouchers` WHERE status = 'Released'";
    $released_vouchers = (int)(db_fetch_one($released_vouchers_sql, [])['count'] ?? 0);
    
    // deposited_collections: COUNT WHERE status = 'Deposited'
    $deposited_collections_sql = "SELECT COUNT(*) as count FROM `revenue_collections` WHERE status = 'Deposited'";
    $deposited_collections = (int)(db_fetch_one($deposited_collections_sql, [])['count'] ?? 0);
    
    // utilization_rate: percentage (total_obligated / total_budget * 100)
    $utilization_rate = $total_budget > 0 ? ($total_obligated / $total_budget) * 100 : 0;
    $balance_percent = $total_budget > 0 ? ($available_balance / $total_budget) * 100 : 0;
    
    // fund_breakdown: GROUP BY fund_source from budget_allocations with totals
    $fund_breakdown_sql = "SELECT fund_source, SUM(approved_budget) as total_budget, SUM(obligated_amount) as total_obligated FROM `budget_allocations` WHERE fiscal_year = ? GROUP BY fund_source";
    $fund_breakdown = db_fetch_all($fund_breakdown_sql, [$year]);
    
    json_response(true, [
        'total_budget' => $total_budget,
        'aip' => $total_budget,
        'total_obligated' => $total_obligated,
        'available_balance' => $available_balance,
        'balance' => $available_balance,
        'balance_percent' => round($balance_percent, 2),
        'total_disbursements' => $total_disbursements,
        'disbursed' => $total_disbursements,
        'disbursed_pending' => $pending_vouchers,
        'total_revenue' => $total_revenue,
        'revenue' => $total_revenue,
        'revenue_deposited' => $deposited_collections,
        'pending_vouchers' => $pending_vouchers,
        'released_vouchers' => $released_vouchers,
        'deposited_collections' => $deposited_collections,
        'utilization_rate' => round($utilization_rate, 2),
        'aip_utilization' => round($utilization_rate, 2),
        'fund_breakdown' => $fund_breakdown
    ]);
}

if ($action === 'allocations' || $action === 'budget') {
    $year = $_GET['year'] ?? null;
    $sql = "SELECT * FROM `budget_allocations`";
    $params = [];
    if ($year) {
        $sql .= " WHERE fiscal_year = ?";
        $params[] = $year;
    }
    $sql .= " ORDER BY id DESC";
    $allocations = db_fetch_all($sql, $params);
    json_response(true, $allocations);
}

if ($action === 'create_allocation' || $action === 'create_budget') {
    $fiscal_year = $input['fiscal_year'] ?? date('Y');
    $fund_source = $input['fund_source'] ?? 'General Fund';
    $program_title = $input['program_title'] ?? '';
    $implementing_committee = $input['implementing_committee'] ?? '';
    $approved_budget = $input['approved_budget'] ?? 0;
    $obligated_amount = $input['obligated_amount'] ?? 0;
    
    if (empty($program_title)) {
        json_response(false, null, "Program title is required.");
    }
    if ($approved_budget <= 0) {
        json_response(false, null, "Approved budget must be greater than 0.");
    }
    
    $data = [
        'fiscal_year' => $fiscal_year,
        'fund_source' => $fund_source,
        'program_title' => $program_title,
        'implementing_committee' => $implementing_committee,
        'approved_budget' => $approved_budget,
        'obligated_amount' => $obligated_amount,
    ];
    
    $id = db_insert('budget_allocations', $data);
    if ($id) {
        log_audit_action('create', 'budget_allocations', "Created allocation: $program_title (₱" . number_format($approved_budget, 2) . ")");
        json_response(true, ['id' => $id], "Budget allocation created successfully.", 201);
    } else {
        json_response(false, null, "Failed to create budget allocation.");
    }
}

if ($action === 'update_allocation' || $action === 'update_budget') {
    $id = $input['id'] ?? null;
    if (!$id) json_response(false, null, "ID is required.");
    
    $data = [];
    $allowed_fields = ['fiscal_year', 'fund_source', 'program_title', 'implementing_committee', 'approved_budget', 'obligated_amount'];
    foreach ($allowed_fields as $field) {
        if (isset($input[$field])) {
            $data[$field] = $input[$field];
        }
    }
    
    if (empty($data)) {
        json_response(false, null, "No data to update.");
    }
    
    $updated = db_update('budget_allocations', $data, "id = ?", [$id]);
    if ($updated) {
        log_audit_action('update_allocation', 'budget_allocations', "Updated budget allocation ID: $id");
        $updated_allocation = db_fetch_one("SELECT * FROM `budget_allocations` WHERE id = ?", [$id]);
        json_response(true, $updated_allocation, "Budget allocation updated successfully.");
    } else {
        json_response(false, null, "Failed to update budget allocation or no changes made.");
    }
}

if ($action === 'vouchers') {
    $status = $_GET['status'] ?? null;
    $fund_source = $_GET['fund_source'] ?? null;
    $q = $_GET['q'] ?? null;
    
    $sql = "SELECT dv.*, ba.program_title, ba.fund_source as allocation_fund_source 
            FROM `disbursement_vouchers` dv 
            LEFT JOIN `budget_allocations` ba ON dv.budget_allocation_id = ba.id
            WHERE 1=1";
    $params = [];
    
    if ($status) {
        $sql .= " AND dv.status = ?";
        $params[] = $status;
    }
    if ($fund_source) {
        $sql .= " AND dv.fund_source = ?";
        $params[] = $fund_source;
    }
    if ($q) {
        $sql .= " AND (dv.payee_name LIKE ? OR dv.particulars LIKE ?)";
        $params[] = "%$q%";
        $params[] = "%$q%";
    }
    
    $sql .= " ORDER BY dv.id DESC";
    $vouchers = db_fetch_all($sql, $params);
    json_response(true, $vouchers);
}

if ($action === 'create_voucher') {
    $payee_name = $input['payee_name'] ?? '';
    $amount = $input['amount'] ?? 0;
    
    if (empty($payee_name)) {
        json_response(false, null, "Payee name is required.");
    }
    if ($amount <= 0) {
        json_response(false, null, "Amount must be greater than 0.");
    }
    
    $year = date('Y');
    $count = db_count('disbursement_vouchers', "YEAR(created_at) = ?", [$year]);
    $dv_number = sprintf("DV-%s-%04d", $year, $count + 1);
    
    $data = [
        'dv_number' => $dv_number,
        'budget_allocation_id' => $input['budget_allocation_id'] ?? null,
        'payee_name' => $payee_name,
        'particulars' => $input['particulars'] ?? '',
        'fund_source' => $input['fund_source'] ?? 'General Fund',
        'amount' => $amount,
        'check_no' => $input['check_no'] ?? null,
        'check_date' => $input['check_date'] ?? null,
        'bank_name' => $input['bank_name'] ?? null,
        'expense_class' => $input['expense_class'] ?? null,
        'certified_by' => $input['certified_by'] ?? null,
        'approved_by' => $input['approved_by'] ?? null,
        'status' => 'Draft',
    ];
    
    $id = db_insert('disbursement_vouchers', $data);
    if ($id) {
        log_audit_action('create_voucher', 'disbursement_vouchers', "Created disbursement voucher $dv_number for $payee_name, amount ₱" . number_format($amount, 2));
        $new_voucher = db_fetch_one("SELECT * FROM `disbursement_vouchers` WHERE id = ?", [$id]);
        json_response(true, $new_voucher, "Voucher created successfully.");
    } else {
        json_response(false, null, "Failed to create voucher.");
    }
}

if ($action === 'update_voucher') {
    $id = $input['id'] ?? null;
    if (!$id) json_response(false, null, "ID is required.");
    
    $data = [];
    $allowed_fields = ['budget_allocation_id', 'payee_name', 'particulars', 'fund_source', 'amount', 'check_no', 'check_date', 'bank_name', 'expense_class', 'certified_by', 'approved_by', 'status'];
    foreach ($allowed_fields as $field) {
        if (isset($input[$field])) {
            $data[$field] = $input[$field];
        }
    }
    
    if (empty($data)) {
        json_response(false, null, "No data to update.");
    }
    
    $updated = db_update('disbursement_vouchers', $data, "id = ?", [$id]);
    if ($updated) {
        $voucher = db_fetch_one("SELECT dv_number FROM `disbursement_vouchers` WHERE id = ?", [$id]);
        $dv_number = $voucher['dv_number'] ?? $id;
        log_audit_action('update_voucher', 'disbursement_vouchers', "Updated disbursement voucher $dv_number");
        $updated_voucher = db_fetch_one("SELECT * FROM `disbursement_vouchers` WHERE id = ?", [$id]);
        json_response(true, $updated_voucher, "Voucher updated successfully.");
    } else {
        json_response(false, null, "Failed to update voucher or no changes made.");
    }
}

if ($action === 'approve_voucher') {
    $id = $input['id'] ?? null;
    $new_status = $input['new_status'] ?? null;
    if (!$id || !$new_status) {
        json_response(false, null, "ID and new_status are required.");
    }
    
    $allowed_statuses = ['Draft', 'Certified', 'Approved', 'Released', 'Cancelled'];
    if (!in_array($new_status, $allowed_statuses, true)) {
        json_response(false, null, "Invalid status: $new_status.");
    }
    
    $voucher = db_fetch_one("SELECT status, dv_number FROM `disbursement_vouchers` WHERE id = ?", [$id]);
    if (!$voucher) {
        json_response(false, null, "Voucher not found.");
    }
    
    $data = ['status' => $new_status];
    if ($new_status === 'Released') {
        $data['released_at'] = date('Y-m-d H:i:s');
    }
    
    $updated = db_update('disbursement_vouchers', $data, "id = ?", [$id]);
    if ($updated) {
        log_audit_action('approve_voucher', 'disbursement_vouchers', "Changed voucher {$voucher['dv_number']} status to $new_status");
        $updated_voucher = db_fetch_one("SELECT * FROM `disbursement_vouchers` WHERE id = ?", [$id]);
        json_response(true, $updated_voucher, "Voucher status updated to $new_status.");
    } else {
        json_response(false, null, "Failed to update voucher status or no changes made.");
    }
}

if ($action === 'collections') {
    $status = $_GET['status'] ?? null;
    $revenue_source = $_GET['revenue_source'] ?? ($_GET['source'] ?? null);
    $q = $_GET['q'] ?? null;
    
    $sql = "SELECT * FROM `revenue_collections` WHERE 1=1";
    $params = [];
    
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    if ($revenue_source) {
        $sql .= " AND revenue_source = ?";
        $params[] = $revenue_source;
    }
    if ($q) {
        $sql .= " AND payer_name LIKE ?";
        $params[] = "%$q%";
    }
    
    $sql .= " ORDER BY id DESC";
    $collections = db_fetch_all($sql, $params);
    json_response(true, $collections);
}

if ($action === 'record_collection') {
    $payer_name = $input['payer_name'] ?? '';
    $amount = $input['amount'] ?? 0;
    
    if (empty($payer_name)) {
        json_response(false, null, "Payer name is required.");
    }
    if ($amount <= 0) {
        json_response(false, null, "Amount must be greater than 0.");
    }
    
    $year = date('Y');
    $count = db_count('revenue_collections', "YEAR(created_at) = ?", [$year]);
    $or_number = sprintf("OR-%s-%04d", $year, $count + 1);
    
    $data = [
        'or_number' => $or_number,
        'payer_name' => $payer_name,
        'revenue_source' => $input['revenue_source'] ?? 'Local Taxes',
        'particulars' => $input['particulars'] ?? '',
        'amount' => $amount,
        'fund_destination' => $input['fund_destination'] ?? 'General Fund',
        'collected_by' => $input['collected_by'] ?? null,
        'receipt_date' => $input['receipt_date'] ?? date('Y-m-d'),
        'rcd_number' => $input['rcd_number'] ?? null,
        'deposit_date' => $input['deposit_date'] ?? null,
        'deposit_bank' => $input['deposit_bank'] ?? null,
        'deposit_slip_no' => $input['deposit_slip_no'] ?? null,
        'status' => 'Collected',
    ];
    
    $id = db_insert('revenue_collections', $data);
    if ($id) {
        log_audit_action('record_collection', 'revenue_collections', "Recorded collection $or_number from $payer_name, amount ₱" . number_format($amount, 2));
        $new_collection = db_fetch_one("SELECT * FROM `revenue_collections` WHERE id = ?", [$id]);
        json_response(true, $new_collection, "Collection recorded successfully.");
    } else {
        json_response(false, null, "Failed to record collection.");
    }
}

if ($action === 'update_collection') {
    $id = $input['id'] ?? null;
    if (!$id) json_response(false, null, "ID is required.");
    
    $data = [];
    $allowed_fields = ['payer_name', 'revenue_source', 'particulars', 'amount', 'fund_destination', 'collected_by', 'receipt_date', 'rcd_number', 'deposit_date', 'deposit_bank', 'deposit_slip_no', 'status'];
    foreach ($allowed_fields as $field) {
        if (isset($input[$field])) {
            $data[$field] = $input[$field];
        }
    }
    
    if (empty($data)) {
        json_response(false, null, "No data to update.");
    }
    
    $updated = db_update('revenue_collections', $data, "id = ?", [$id]);
    if ($updated) {
        $collection = db_fetch_one("SELECT or_number FROM `revenue_collections` WHERE id = ?", [$id]);
        $or_number = $collection['or_number'] ?? $id;
        log_audit_action('update_collection', 'revenue_collections', "Updated collection $or_number");
        $updated_collection = db_fetch_one("SELECT * FROM `revenue_collections` WHERE id = ?", [$id]);
        json_response(true, $updated_collection, "Collection updated successfully.");
    } else {
        json_response(false, null, "Failed to update collection or no changes made.");
    }
}

if ($action === 'obligations') {
    $status = $_GET['status'] ?? null;
    
    $sql = "SELECT bo.*, ba.program_title, ba.fund_source 
            FROM `budget_obligations` bo 
            LEFT JOIN `budget_allocations` ba ON bo.budget_allocation_id = ba.id
            WHERE 1=1";
    $params = [];
    
    if ($status) {
        $sql .= " AND bo.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY bo.id DESC";
    $obligations = db_fetch_all($sql, $params);
    json_response(true, $obligations);
}

if ($action === 'create_obligation') {
    $obligee_name = $input['obligee_name'] ?? '';
    $amount = $input['amount'] ?? 0;
    
    if (empty($obligee_name)) {
        json_response(false, null, "Obligee name is required.");
    }
    if ($amount <= 0) {
        json_response(false, null, "Amount must be greater than 0.");
    }
    
    $year = date('Y');
    $count = db_count('budget_obligations', "YEAR(created_at) = ?", [$year]);
    $obr_number = sprintf("OBR-%s-%04d", $year, $count + 1);
    
    $data = [
        'obr_number' => $obr_number,
        'budget_allocation_id' => $input['budget_allocation_id'] ?? null,
        'obligation_type' => $input['obligation_type'] ?? 'Expense',
        'obligee_name' => $obligee_name,
        'description' => $input['description'] ?? '',
        'amount' => $amount,
        'date_obligated' => $input['date_obligated'] ?? date('Y-m-d'),
        'status' => 'Pending',
    ];
    
    $id = db_insert('budget_obligations', $data);
    if ($id) {
        log_audit_action('create_obligation', 'budget_obligations', "Created obligation $obr_number for $obligee_name, amount ₱" . number_format($amount, 2));
        $new_obligation = db_fetch_one("SELECT * FROM `budget_obligations` WHERE id = ?", [$id]);
        json_response(true, $new_obligation, "Obligation created successfully.");
    } else {
        json_response(false, null, "Failed to create obligation.");
    }
}

if ($action === 'update_obligation') {
    $id = $input['id'] ?? null;
    if (!$id) json_response(false, null, "ID is required.");
    
    $data = [];
    $allowed_fields = ['budget_allocation_id', 'obligation_type', 'obligee_name', 'description', 'amount', 'date_obligated', 'status'];
    foreach ($allowed_fields as $field) {
        if (isset($input[$field])) {
            $data[$field] = $input[$field];
        }
    }
    
    if (empty($data)) {
        json_response(false, null, "No data to update.");
    }
    
    $updated = db_update('budget_obligations', $data, "id = ?", [$id]);
    if ($updated) {
        $obligation = db_fetch_one("SELECT obr_number FROM `budget_obligations` WHERE id = ?", [$id]);
        $obr_number = $obligation['obr_number'] ?? $id;
        log_audit_action('update_obligation', 'budget_obligations', "Updated obligation $obr_number");
        $updated_obligation = db_fetch_one("SELECT * FROM `budget_obligations` WHERE id = ?", [$id]);
        json_response(true, $updated_obligation, "Obligation updated successfully.");
    } else {
        json_response(false, null, "Failed to update obligation or no changes made.");
    }
}

if ($action === 'generate_report') {
    $fiscal_year = $input['fiscal_year'] ?? date('Y');
    
    // total_receipts (from revenue_collections)
    $rev_sql = "SELECT SUM(amount) as total FROM `revenue_collections` WHERE status != 'Voided' AND YEAR(receipt_date) = ?";
    $rev_res = db_fetch_one($rev_sql, [$fiscal_year]);
    $total_receipts = $rev_res['total'] ?? 0;
    
    // total_expenditures (from disbursement_vouchers WHERE status='Released')
    $exp_sql = "SELECT SUM(amount) as total FROM `disbursement_vouchers` WHERE status = 'Released' AND YEAR(created_at) = ?";
    $exp_res = db_fetch_one($exp_sql, [$fiscal_year]);
    $total_expenditures = $exp_res['total'] ?? 0;
    
    $net_balance = $total_receipts - $total_expenditures;
    
    // fund breakdown
    $breakdown_sql = "SELECT fund_source, SUM(approved_budget) as total_budget, SUM(obligated_amount) as total_obligated FROM `budget_allocations` WHERE fiscal_year = ? GROUP BY fund_source";
    $breakdown = db_fetch_all($breakdown_sql, [$fiscal_year]);
    
    $count = db_count('financial_reports', "YEAR(created_at) = ?", [$fiscal_year]);
    $report_code = sprintf("FR-%s-%02d", $fiscal_year, $count + 1);
    
    $data = [
        'report_code' => $report_code,
        'report_type' => $input['report_type'] ?? 'Annual',
        'period_label' => $input['period_label'] ?? "FY $fiscal_year",
        'total_receipts' => $total_receipts,
        'total_expenditures' => $total_expenditures,
        'net_balance' => $net_balance,
        'report_data' => json_encode($breakdown),
        'generated_by' => $_SESSION['user_id'] ?? null
    ];
    
    $id = db_insert('financial_reports', $data);
    if ($id) {
        log_audit_action('generate_report', 'financial_reports', "Generated financial report $report_code for $fiscal_year");
        $new_report = db_fetch_one("SELECT * FROM `financial_reports` WHERE id = ?", [$id]);
        json_response(true, $new_report, "Report generated successfully.");
    } else {
        json_response(false, null, "Failed to generate report.");
    }
}

if ($action === 'reports') {
    $sql = "SELECT * FROM `financial_reports` ORDER BY id DESC";
    $reports = db_fetch_all($sql, []);
    json_response(true, $reports);
}

if ($action === 'fund_summary') {
    $year = $_GET['year'] ?? date('Y');
    
    // Get distinct fund sources
    $fund_sources_sql = "SELECT DISTINCT fund_source FROM `budget_allocations` WHERE fiscal_year = ?";
    $sources = db_fetch_all($fund_sources_sql, [$year]);
    
    $summary = [];
    $grand_totals = [
        'total_appropriation' => 0,
        'total_obligated' => 0,
        'total_disbursed' => 0,
        'total_collected' => 0
    ];
    
    foreach ($sources as $s) {
        $fs = $s['fund_source'];
        
        $app_res = db_fetch_one("SELECT SUM(approved_budget) as ta, SUM(obligated_amount) as tobl FROM `budget_allocations` WHERE fiscal_year = ? AND fund_source = ?", [$year, $fs]);
        $disb_res = db_fetch_one("SELECT SUM(amount) as td FROM `disbursement_vouchers` WHERE status = 'Released' AND fund_source = ? AND YEAR(created_at) = ?", [$fs, $year]);
        $coll_res = db_fetch_one("SELECT SUM(amount) as tc FROM `revenue_collections` WHERE status != 'Voided' AND fund_destination = ? AND YEAR(receipt_date) = ?", [$fs, $year]);
        
        $row = [
            'fund_source' => $fs,
            'total_appropriation' => $app_res['ta'] ?? 0,
            'total_obligated' => $app_res['tobl'] ?? 0,
            'total_disbursed' => $disb_res['td'] ?? 0,
            'total_collected' => $coll_res['tc'] ?? 0
        ];
        
        $summary[] = $row;
        
        $grand_totals['total_appropriation'] += $row['total_appropriation'];
        $grand_totals['total_obligated'] += $row['total_obligated'];
        $grand_totals['total_disbursed'] += $row['total_disbursed'];
        $grand_totals['total_collected'] += $row['total_collected'];
    }
    
    json_response(true, ['funds' => $summary, 'grand_totals' => $grand_totals]);
}

if ($action === 'delete') {
    $type = $input['type'] ?? null;
    $id = $input['id'] ?? null;
    
    if (!$type || !$id) {
        json_response(false, null, "Type and ID are required.");
    }
    
    $table_map = [
        'voucher' => 'disbursement_vouchers',
        'collection' => 'revenue_collections',
        'obligation' => 'budget_obligations',
        'allocation' => 'budget_allocations',
        'report' => 'financial_reports'
    ];
    
    if (!isset($table_map[$type])) {
        json_response(false, null, "Invalid type to delete.");
    }
    
    $table = $table_map[$type];
    
    $deleted = db_delete($table, "id = ?", [$id]);
    if ($deleted) {
        log_audit_action('delete', $table, "Deleted $type with ID $id");
        json_response(true, null, ucfirst($type) . " deleted successfully.");
    } else {
        json_response(false, null, "Failed to delete $type or it does not exist.");
    }
}

// Default fall-through: List all disbursement_vouchers (same as vouchers action without filters)
$sql = "SELECT dv.*, ba.program_title, ba.fund_source as allocation_fund_source 
        FROM `disbursement_vouchers` dv 
        LEFT JOIN `budget_allocations` ba ON dv.budget_allocation_id = ba.id
        ORDER BY dv.id DESC";
$vouchers = db_fetch_all($sql, []);
json_response(true, $vouchers);
