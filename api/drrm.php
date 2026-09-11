<?php
/**
 * Barangay Management System (BarangayOS)
 * Disaster Risk Reduction & Management (DRRM) REST API Endpoint
 * Compliant with RA 10121 (DRRM Act of 2010) & DILG Camp Coordination Guidelines
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

// Action aliases for seamless frontend compatibility
if ($action === 'get_centers') $action = 'centers';
if ($action === 'get_evacuees') $action = 'evacuees';
if ($action === 'get_relief_items' || $action === 'get_relief_inventory' || $action === 'relief') $action = 'relief_inventory';
if ($action === 'get_distributions') $action = 'distributions';
if ($action === 'save_center') $action = !empty($input['id']) ? 'update_center' : 'create_center';
if ($action === 'save_evacuee') $action = !empty($input['id']) ? 'update_evacuee' : 'register_evacuee';
if ($action === 'save_relief_item' || $action === 'save_relief') $action = !empty($input['id']) ? 'update_relief_item' : 'create_relief_item';
if ($action === 'save_dafac') $action = 'record_distribution';
if ($action === 'delete_center') { $input['type'] = 'center'; $action = 'delete'; }
if ($action === 'delete_evacuee') { $input['type'] = 'evacuee'; $action = 'delete'; }
if ($action === 'delete_relief_item') { $input['type'] = 'relief_item'; $action = 'delete'; }
if ($action === 'delete_distribution') { $input['type'] = 'distribution'; $action = 'delete'; }

require_auth();

// ----------------------------------------------------
// 1. STATS: OVERVIEW KPI TELEMETRY
// ----------------------------------------------------
if ($action === 'stats') {
    $totalCenters = db_count('drrm_evacuation_centers');
    $activeCenters = db_count('drrm_evacuation_centers', "`status` = 'Active / Open'");
    $standbyCenters = db_count('drrm_evacuation_centers', "`status` != 'Active / Open'");
    
    $capacityRow = db_fetch_one("
        SELECT COALESCE(SUM(capacity_families), 0) as cap_fam,
               COALESCE(SUM(capacity_individuals), 0) as cap_ind,
               COALESCE(SUM(current_families), 0) as cur_fam,
               COALESCE(SUM(current_individuals), 0) as cur_ind
        FROM `drrm_evacuation_centers`
    ");
    
    // Evacuee vulnerable breakdown
    $vulnRow = db_fetch_one("
        SELECT COUNT(*) as sheltered_families,
               COALESCE(SUM(members_count), 0) as sheltered_individuals,
               COALESCE(SUM(seniors_count), 0) as total_seniors,
               COALESCE(SUM(children_count), 0) as total_children,
               COALESCE(SUM(pwd_count), 0) as total_pwd,
               COALESCE(SUM(pregnant_lactating_count), 0) as total_pregnant
        FROM `drrm_evacuees`
        WHERE `status` = 'Sheltered'
    ");
    
    // Critical hazard households from Geo-Profiling
    $criticalHazards = db_count('households', "`hazard_risk` IN ('High', 'Critical')");
    
    // Relief stockpile totals
    $reliefRow = db_fetch_one("
        SELECT COALESCE(SUM(stock_quantity), 0) as total_units,
               COALESCE(SUM(stock_quantity * unit_cost), 0) as total_valuation,
               COALESCE(SUM(CASE WHEN stock_quantity <= reorder_level THEN 1 ELSE 0 END), 0) as low_stock_count
        FROM `drrm_relief_items`
    ");
    
    // 5% Calamity Fund allocation
    $calamityFundRow = db_fetch_one("
        SELECT COALESCE(SUM(approved_budget), 0) as budget,
               COALESCE(SUM(obligated_amount), 0) as obligated
        FROM `budget_allocations`
        WHERE `fund_source` = '5% BDRRM Calamity Fund'
    ");
    $fundBudget = (float)($calamityFundRow['budget'] ?? 950000.00);
    $fundObligated = (float)($calamityFundRow['obligated'] ?? 280000.00);
    $fundBalance = max(0, $fundBudget - $fundObligated);
    $fundUtilization = ($fundBudget > 0) ? round(($fundObligated / $fundBudget) * 100, 1) : 0;

    json_response(true, [
        'total_centers'          => $totalCenters,
        'active_centers'         => $activeCenters,
        'standby_centers'        => $standbyCenters,
        'total_families'         => (int)($vulnRow['sheltered_families'] ?? $capacityRow['cur_fam'] ?? 0),
        'total_pax'              => (int)($vulnRow['sheltered_individuals'] ?? $capacityRow['cur_ind'] ?? 0),
        'capacity_families'      => (int)($capacityRow['cap_fam'] ?? 0),
        'capacity_individuals'   => (int)($capacityRow['cap_ind'] ?? 0),
        'current_families'       => (int)($vulnRow['sheltered_families'] ?? $capacityRow['cur_fam'] ?? 0),
        'current_individuals'    => (int)($vulnRow['sheltered_individuals'] ?? $capacityRow['cur_ind'] ?? 0),
        'occupancy_rate'         => ($capacityRow['cap_ind'] > 0) ? round(($vulnRow['sheltered_individuals'] / $capacityRow['cap_ind']) * 100, 1) : 0,
        'total_relief'           => (int)($reliefRow['total_units'] ?? 0),
        'low_stock'              => ((int)($reliefRow['low_stock_count'] ?? 0) > 0),
        'bdrrm_fund'             => $fundBalance,
        'fund_utilization'       => $fundUtilization,
        'alert_level'            => 'White',
        'vulnerabilities'        => [
            'seniors'             => (int)($vulnRow['total_seniors'] ?? 0),
            'children'            => (int)($vulnRow['total_children'] ?? 0),
            'pwd'                 => (int)($vulnRow['total_pwd'] ?? 0),
            'pregnant_lactating'  => (int)($vulnRow['total_pregnant'] ?? 0)
        ],
        'critical_hazards'       => $criticalHazards,
        'relief_stockpile'       => [
            'total_units'         => (int)($reliefRow['total_units'] ?? 0),
            'total_valuation'     => (float)($reliefRow['total_valuation'] ?? 0),
            'low_stock_count'     => (int)($reliefRow['low_stock_count'] ?? 0)
        ],
        'calamity_fund'          => [
            'approved_budget'     => $fundBudget,
            'obligated'           => $fundObligated,
            'balance'             => $fundBalance,
            'utilization_rate'    => $fundUtilization
        ]
    ]);
}

// ----------------------------------------------------
// 2. EVACUATION CENTERS ACTIONS
// ----------------------------------------------------
if ($action === 'centers') {
    $centers = db_fetch_all("
        SELECT c.*,
               c.center_name as name,
               c.capacity_families as capacity,
               (SELECT COUNT(*) FROM `drrm_evacuees` WHERE evacuation_center_id = c.id AND `status` = 'Sheltered') as occupants,
               (SELECT COUNT(*) FROM `drrm_evacuees` WHERE evacuation_center_id = c.id AND `status` = 'Sheltered') as live_families,
               (SELECT COALESCE(SUM(members_count), 0) FROM `drrm_evacuees` WHERE evacuation_center_id = c.id AND `status` = 'Sheltered') as live_individuals
        FROM `drrm_evacuation_centers` c
        ORDER BY c.status = 'Active / Open' DESC, c.id ASC
    ");
    
    foreach ($centers as &$center) {
        $cap = max(1, (int)$center['capacity_individuals']);
        $occ = (int)$center['live_individuals'];
        $center['occupancy_rate'] = round(($occ / $cap) * 100, 1);
        if ($center['status'] === 'Active / Open') {
            $center['status_short'] = 'Active';
        } else {
            $center['status_short'] = 'Standby';
        }
    }
    
    json_response(true, ['centers' => $centers]);
}

if ($action === 'create_center') {
    $name = trim($input['center_name'] ?? ($input['name'] ?? ''));
    $purok = trim($input['purok'] ?? 'Purok 1');
    $address = trim($input['address'] ?? '');
    
    if (empty($name)) {
        json_response(false, null, "Center name is required.", 400);
    }
    
    $data = [
        'center_name'          => $name,
        'purok'                => $purok,
        'address'              => $address,
        'capacity_families'    => (int)($input['capacity_families'] ?? ($input['capacity'] ?? 50)),
        'capacity_individuals' => (int)($input['capacity_individuals'] ?? (($input['capacity'] ?? 50) * 5)),
        'has_generator'        => !empty($input['has_generator']) ? 1 : 0,
        'has_water_supply'     => !empty($input['has_water_supply']) ? 1 : 0,
        'has_clinic_station'   => !empty($input['has_clinic_station']) ? 1 : 0,
        'center_manager'       => trim($input['center_manager'] ?? 'BDRRMC Camp Manager'),
        'contact_no'           => trim($input['contact_no'] ?? ''),
        'status'               => !empty($input['status']) ? (str_contains($input['status'], 'Active') ? 'Active / Open' : 'Standby / Inactive') : 'Active / Open'
    ];
    
    $id = db_insert('drrm_evacuation_centers', $data);
    log_audit_action('CREATE_EVAC_CENTER', 'drrm_evacuation_centers', "Registered evacuation shelter: {$name} ({$purok})");
    $created = db_fetch_one("SELECT * FROM `drrm_evacuation_centers` WHERE id = ?", [$id]);
    json_response(true, $created, "Evacuation center registered successfully.", 201);
}

if ($action === 'update_center') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) json_response(false, null, "Center ID is required.", 400);
    
    $fields = ['center_name', 'purok', 'address', 'capacity_families', 'capacity_individuals', 'has_generator', 'has_water_supply', 'has_clinic_station', 'center_manager', 'contact_no', 'status'];
    $data = [];
    foreach ($fields as $f) {
        if (isset($input[$f])) {
            $data[$f] = ($f === 'has_generator' || $f === 'has_water_supply' || $f === 'has_clinic_station')
                ? (!empty($input[$f]) ? 1 : 0)
                : $input[$f];
        }
    }
    
    if (!empty($data)) {
        db_update('drrm_evacuation_centers', $data, "id = ?", [$id]);
        log_audit_action('UPDATE_EVAC_CENTER', 'drrm_evacuation_centers', "Updated evacuation center ID #{$id}");
    }
    
    $updated = db_fetch_one("SELECT * FROM `drrm_evacuation_centers` WHERE id = ?", [$id]);
    json_response(true, $updated, "Evacuation center updated successfully.");
}

// ----------------------------------------------------
// 3. EVACUEES / CAMP MASTERLIST ACTIONS
// ----------------------------------------------------
if ($action === 'evacuees') {
    $centerId = (int)($_GET['center_id'] ?? 0);
    $status   = trim($_GET['status'] ?? '');
    $q        = trim($_GET['q'] ?? '');
    
    $sql = "
        SELECT e.*,
               e.family_head_name as family_head,
               e.purok_origin as purok,
               e.special_medical_needs as medical_needs,
               DATE_FORMAT(e.check_in_date, '%Y-%m-%d %H:%i') as date_in,
               c.center_name, c.purok as center_purok
        FROM `drrm_evacuees` e
        LEFT JOIN `drrm_evacuation_centers` c ON e.evacuation_center_id = c.id
        WHERE 1=1
    ";
    $params = [];
    
    if ($centerId > 0) {
        $sql .= " AND e.evacuation_center_id = ?";
        $params[] = $centerId;
    }
    if (!empty($status)) {
        $sql .= " AND e.status = ?";
        $params[] = $status;
    }
    if (!empty($q)) {
        $sql .= " AND (e.family_head_name LIKE ? OR e.evacuee_code LIKE ? OR e.purok_origin LIKE ?)";
        $params[] = "%$q%";
        $params[] = "%$q%";
        $params[] = "%$q%";
    }
    
    $sql .= " ORDER BY e.id DESC";
    $evacuees = db_fetch_all($sql, $params);
    
    foreach ($evacuees as &$evac) {
        $evac['vulnerable_count'] = (int)$evac['seniors_count'] + (int)$evac['children_count'] + (int)$evac['pwd_count'] + (int)$evac['pregnant_lactating_count'];
    }
    
    json_response(true, ['evacuees' => $evacuees]);
}

if ($action === 'register_evacuee') {
    $centerId = (int)($input['evacuation_center_id'] ?? ($input['center_id'] ?? 0));
    $headName = trim($input['family_head_name'] ?? ($input['family_head'] ?? ''));
    $purok    = trim($input['purok_origin'] ?? ($input['purok'] ?? 'Purok 1'));
    $members  = max(1, (int)($input['members_count'] ?? 1));
    $vulnCount = (int)($input['vulnerable_count'] ?? 0);
    
    if ($centerId <= 0 || empty($headName)) {
        json_response(false, null, "Evacuation center and Family head name are required.", 400);
    }
    
    $year = date('Y');
    $count = db_count('drrm_evacuees', "YEAR(created_at) = ?", [$year]);
    $code = sprintf("EVAC-%s-%04d", $year, $count + 1);
    
    $data = [
        'evacuee_code'             => $code,
        'evacuation_center_id'     => $centerId,
        'household_id'             => !empty($input['household_id']) ? (int)$input['household_id'] : null,
        'family_head_name'         => $headName,
        'purok_origin'             => $purok,
        'contact_no'               => trim($input['contact_no'] ?? ''),
        'members_count'            => $members,
        'seniors_count'            => (int)($input['seniors_count'] ?? min($vulnCount, 1)),
        'children_count'           => (int)($input['children_count'] ?? max(0, $vulnCount - 1)),
        'pwd_count'                => (int)($input['pwd_count'] ?? 0),
        'pregnant_lactating_count' => (int)($input['pregnant_lactating_count'] ?? 0),
        'room_tent_no'             => trim($input['room_tent_no'] ?? 'Tent 1'),
        'special_medical_needs'    => trim($input['special_medical_needs'] ?? ($input['medical_needs'] ?? '')),
        'check_in_date'            => !empty($input['check_in_date']) ? $input['check_in_date'] : date('Y-m-d H:i:s'),
        'status'                   => 'Sheltered'
    ];
    
    $id = db_insert('drrm_evacuees', $data);
    
    // Sync center live counts
    db_query("
        UPDATE `drrm_evacuation_centers` SET
            `current_families` = (SELECT COUNT(*) FROM `drrm_evacuees` WHERE evacuation_center_id = ? AND status = 'Sheltered'),
            `current_individuals` = (SELECT COALESCE(SUM(members_count), 0) FROM `drrm_evacuees` WHERE evacuation_center_id = ? AND status = 'Sheltered')
        WHERE id = ?
    ", [$centerId, $centerId, $centerId]);
    
    log_audit_action('CHECK_IN_EVACUEE', 'drrm_evacuees', "Registered evacuee family {$headName} ({$members} pax) at Shelter ID #{$centerId}");
    $created = db_fetch_one("SELECT * FROM `drrm_evacuees` WHERE id = ?", [$id]);
    json_response(true, $created, "Evacuee registered and checked in successfully.", 201);
}

if ($action === 'decamp_evacuee') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) json_response(false, null, "Evacuee ID is required.", 400);
    
    $evacuee = db_fetch_one("SELECT * FROM `drrm_evacuees` WHERE id = ?", [$id]);
    if (!$evacuee) json_response(false, null, "Evacuee record not found.", 404);
    
    db_update('drrm_evacuees', [
        'status'         => 'Decamped / Returned Home',
        'check_out_date' => date('Y-m-d H:i:s')
    ], "id = ?", [$id]);
    
    $centerId = $evacuee['evacuation_center_id'];
    db_query("
        UPDATE `drrm_evacuation_centers` SET
            `current_families` = (SELECT COUNT(*) FROM `drrm_evacuees` WHERE evacuation_center_id = ? AND status = 'Sheltered'),
            `current_individuals` = (SELECT COALESCE(SUM(members_count), 0) FROM `drrm_evacuees` WHERE evacuation_center_id = ? AND status = 'Sheltered')
        WHERE id = ?
    ", [$centerId, $centerId, $centerId]);
    
    log_audit_action('DECAMP_EVACUEE', 'drrm_evacuees', "Decamped evacuee {$evacuee['family_head_name']} ({$evacuee['evacuee_code']})");
    json_response(true, null, "Evacuee decamped and recorded successfully.");
}

// ----------------------------------------------------
// 4. RELIEF INVENTORY ACTIONS
// ----------------------------------------------------
if ($action === 'relief_inventory') {
    $cat = trim($_GET['category'] ?? '');
    $q   = trim($_GET['q'] ?? '');
    
    $sql = "SELECT *, stock_quantity as quantity FROM `drrm_relief_items` WHERE 1=1";
    $params = [];
    
    if (!empty($cat)) {
        $sql .= " AND category = ?";
        $params[] = $cat;
    }
    if (!empty($q)) {
        $sql .= " AND (item_name LIKE ? OR item_code LIKE ?)";
        $params[] = "%$q%";
        $params[] = "%$q%";
    }
    
    $sql .= " ORDER BY stock_quantity <= reorder_level DESC, item_name ASC";
    $items = db_fetch_all($sql, $params);
    json_response(true, ['items' => $items]);
}

if ($action === 'create_relief_item') {
    $name = trim($input['item_name'] ?? '');
    $cat  = trim($input['category'] ?? 'Food Packs');
    $qty  = max(0, (int)($input['quantity'] ?? ($input['stock_quantity'] ?? 0)));
    
    if (empty($name)) {
        json_response(false, null, "Item name is required.", 400);
    }
    
    $count = db_count('drrm_relief_items') + 1;
    $itemCode = sprintf("REL-%03d", $count);
    
    $data = [
        'item_code'      => $input['item_code'] ?? $itemCode,
        'item_name'      => $name,
        'category'       => $cat,
        'unit'           => trim($input['unit'] ?? 'packs'),
        'stock_quantity' => $qty,
        'reorder_level'  => max(1, (int)($input['reorder_level'] ?? 50)),
        'unit_cost'      => (float)($input['unit_cost'] ?? 0),
        'fund_source'    => trim($input['fund_source'] ?? '5% BDRRM Calamity Fund'),
        'expiry_date'    => !empty($input['expiry_date']) ? $input['expiry_date'] : null
    ];
    
    $id = db_insert('drrm_relief_items', $data);
    log_audit_action('CREATE_RELIEF_ITEM', 'drrm_relief_items', "Stockpiled relief item: {$name} ({$qty} {$data['unit']})");
    $created = db_fetch_one("SELECT * FROM `drrm_relief_items` WHERE id = ?", [$id]);
    json_response(true, $created, "Relief item registered into stockpile.", 201);
}

if ($action === 'restock_item') {
    $id  = (int)($input['id'] ?? 0);
    $qty = (int)($input['quantity'] ?? 0);
    
    if ($id <= 0 || $qty <= 0) {
        json_response(false, null, "Valid Item ID and positive quantity are required.", 400);
    }
    
    db_query("UPDATE `drrm_relief_items` SET `stock_quantity` = `stock_quantity` + ? WHERE id = ?", [$qty, $id]);
    $item = db_fetch_one("SELECT * FROM `drrm_relief_items` WHERE id = ?", [$id]);
    log_audit_action('RESTOCK_RELIEF_ITEM', 'drrm_relief_items', "Restocked +{$qty} units of {$item['item_name']} (Total: {$item['stock_quantity']})");
    json_response(true, $item, "Stock added successfully.");
}

// ----------------------------------------------------
// 5. DAFAC RELIEF DISTRIBUTIONS ACTIONS
// ----------------------------------------------------
if ($action === 'distributions') {
    $sql = "
        SELECT d.*, 
               DATE_FORMAT(d.distributed_at, '%Y-%m-%d %H:%i') as date,
               d.recipient_name as recipient,
               d.quantity_given as quantity,
               d.calamity_name as calamity,
               r.item_name, r.unit, r.category as item_category
        FROM `drrm_relief_distributions` d
        LEFT JOIN `drrm_relief_items` r ON d.relief_item_id = r.id
        ORDER BY d.id DESC
    ";
    $distributions = db_fetch_all($sql);
    json_response(true, ['distributions' => $distributions]);
}

if ($action === 'record_distribution') {
    $itemId    = (int)($input['relief_item_id'] ?? ($input['item_id'] ?? 0));
    $recipient = trim($input['recipient_name'] ?? ($input['recipient'] ?? ''));
    $purok     = trim($input['purok'] ?? 'Purok 1');
    $qty       = max(1, (int)($input['quantity_given'] ?? ($input['quantity'] ?? 1)));
    $calamity  = trim($input['calamity_name'] ?? ($input['calamity'] ?? 'Emergency Relief Ops'));
    
    if ($itemId <= 0 || empty($recipient)) {
        json_response(false, null, "Relief item and recipient name are required.", 400);
    }
    
    $item = db_fetch_one("SELECT * FROM `drrm_relief_items` WHERE id = ?", [$itemId]);
    if (!$item) json_response(false, null, "Relief item not found.", 404);
    if ($item['stock_quantity'] < $qty) {
        json_response(false, null, "Insufficient stock: only {$item['stock_quantity']} {$item['unit']} available.", 400);
    }
    
    $year = date('Y');
    $count = db_count('drrm_relief_distributions', "YEAR(created_at) = ?", [$year]);
    $distCode = sprintf("DAFAC-%s-%04d", $year, $count + 1);
    
    $data = [
        'distribution_code' => $distCode,
        'calamity_name'     => $calamity,
        'evacuee_id'        => !empty($input['evacuee_id']) ? (int)$input['evacuee_id'] : null,
        'recipient_name'    => $recipient,
        'purok'             => $purok,
        'relief_item_id'    => $itemId,
        'quantity_given'    => $qty,
        'distributed_by'    => trim($input['distributed_by'] ?? 'BDRRMC Relief Team'),
        'distributed_at'    => !empty($input['distributed_at']) ? $input['distributed_at'] : date('Y-m-d H:i:s'),
        'remarks'           => trim($input['remarks'] ?? '')
    ];
    
    $id = db_insert('drrm_relief_distributions', $data);
    
    // Decrement inventory
    db_query("UPDATE `drrm_relief_items` SET `stock_quantity` = GREATEST(0, `stock_quantity` - ?) WHERE id = ?", [$qty, $itemId]);
    
    log_audit_action('DAFAC_DISTRIBUTION', 'drrm_relief_distributions', "Distributed {$qty}x {$item['item_name']} to {$recipient} ({$purok}) under {$distCode}");
    $created = db_fetch_one("SELECT * FROM `drrm_relief_distributions` WHERE id = ?", [$id]);
    json_response(true, $created, "DAFAC distribution recorded and inventory adjusted.", 201);
}

// ----------------------------------------------------
// 6. EARLY WARNING & BROADCAST ADVISORY
// ----------------------------------------------------
if ($action === 'broadcast_advisory') {
    $alertLevel = trim($input['alert_level'] ?? 'Red Alert');
    $calamity   = trim($input['calamity_type'] ?? 'Typhoon / Flood');
    $purok      = trim($input['affected_purok'] ?? ($input['purok'] ?? 'All Puroks'));
    $message    = trim($input['message'] ?? '');
    
    if (empty($message)) {
        json_response(false, null, "Advisory message content is required.", 400);
    }
    
    $year = date('Y');
    $dispatchCode = sprintf("ADV-%s-%04d", $year, rand(1000, 9999));
    
    // Save to notifications engine
    db_insert('notifications', [
        'dispatch_code'     => $dispatchCode,
        'recipient_name'    => "BDRRMC Broadcast ({$purok})",
        'recipient_contact' => "Purok Broadcast Relay",
        'channel'           => 'SMS',
        'category'          => 'Advisory',
        'subject'           => "[{$alertLevel}] {$calamity} Advisory",
        'message'           => $message,
        'status'            => 'Delivered',
        'cost_credits'      => 5
    ]);
    
    log_audit_action('BROADCAST_ADVISORY', 'notifications', "Issued {$alertLevel} {$calamity} emergency advisory for {$purok}");
    json_response(true, ['dispatch_code' => $dispatchCode], "Emergency warning advisory broadcasted successfully.");
}

// ----------------------------------------------------
// 7. PUROK HAZARDS & ADVISORIES (INTEGRATED)
// ----------------------------------------------------
if ($action === 'hazards') {
    $puroks = db_fetch_all("
        SELECT p.purok,
               COUNT(DISTINCT r.id) as population,
               SUM(r.is_senior = 1 OR r.is_pwd = 1 OR r.is_solo_parent = 1 OR r.is_4ps = 1 OR r.is_indigent = 1) as vulnerable,
               CASE 
                   WHEN p.purok IN ('Purok 1', 'Purok 2') THEN 'High'
                   WHEN p.purok IN ('Purok 3', 'Purok 6') THEN 'Medium'
                   ELSE 'Low'
               END as flood_risk,
               CASE 
                   WHEN p.purok IN ('Purok 7', 'Purok 5') THEN 'High'
                   WHEN p.purok IN ('Purok 4') THEN 'Medium'
                   ELSE 'Low'
               END as landslide_risk,
               CASE 
                   WHEN p.purok IN ('Purok 1', 'Purok 6') THEN 'High'
                   ELSE 'Medium'
               END as fire_risk
        FROM (SELECT DISTINCT purok FROM residents WHERE status = 'Active') p
        LEFT JOIN residents r ON p.purok = r.purok AND r.status = 'Active'
        GROUP BY p.purok
        ORDER BY p.purok ASC
    ");
    json_response(true, ['hazards' => $puroks]);
}

if ($action === 'advisories') {
    $advisories = db_fetch_all("
        SELECT id, subject, message, recipient_name as affected_purok, created_at,
               CASE 
                   WHEN subject LIKE '%Red Alert%' THEN 'Red'
                   WHEN subject LIKE '%Blue Alert%' THEN 'Blue'
                   WHEN subject LIKE '%White Alert%' THEN 'White'
                   ELSE 'Normal'
               END as alert_level,
               'Typhoon / Flood Alert' as calamity_type
        FROM `notifications`
        WHERE `category` = 'Advisory'
        ORDER BY id DESC
        LIMIT 20
    ");
    json_response(true, ['advisories' => $advisories]);
}

// ----------------------------------------------------
// 8. UNIFIED DELETE ACTION
// ----------------------------------------------------
if ($action === 'delete') {
    $type = $input['type'] ?? null;
    $id   = (int)($input['id'] ?? 0);
    
    if (!$type || $id <= 0) {
        json_response(false, null, "Type and ID are required.", 400);
    }
    
    $tableMap = [
        'center'       => 'drrm_evacuation_centers',
        'evacuee'      => 'drrm_evacuees',
        'relief_item'  => 'drrm_relief_items',
        'distribution' => 'drrm_relief_distributions'
    ];
    
    if (!isset($tableMap[$type])) {
        json_response(false, null, "Invalid type for deletion.", 400);
    }
    
    $table = $tableMap[$type];
    $deleted = db_delete($table, "id = ?", [$id]);
    
    if ($deleted) {
        log_audit_action('DELETE', $table, "Deleted {$type} record ID #{$id}");
        json_response(true, null, ucfirst($type) . " deleted successfully.");
    } else {
        json_response(false, null, "Failed to delete record or record not found.", 404);
    }
}

// Fallback: Return centers summary
$centers = db_fetch_all("SELECT * FROM `drrm_evacuation_centers` ORDER BY id ASC");
json_response(true, ['centers' => $centers]);
