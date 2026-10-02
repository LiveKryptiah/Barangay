<?php
/**
 * Barangay Management System (BarangayOS)
 * Families & Social Welfare Management REST API Endpoint
 * Compliant with DSWD, DILG, and CBMS Standards
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Require authentication for family management
require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$input  = get_json_input();
if (!empty($input['action'])) {
    $action = $input['action'];
}

/**
 * Helper to calculate income bracket & poverty status based on monthly income (PHP)
 */
function evaluate_poverty_status($income) {
    $income = (float)$income;
    if ($income < 10000) {
        return [
            'bracket' => 'Under 10,000',
            'status'  => 'Indigent / Below Poverty Threshold'
        ];
    } elseif ($income <= 20000) {
        return [
            'bracket' => '10,001 - 20,000',
            'status'  => 'Low Income / Subsistence'
        ];
    } elseif ($income <= 40000) {
        return [
            'bracket' => '20,001 - 40,000',
            'status'  => 'Lower Middle Class'
        ];
    } elseif ($income <= 70000) {
        return [
            'bracket' => '40,001 - 70,000',
            'status'  => 'Middle Class'
        ];
    } else {
        return [
            'bracket' => 'Above 70,000',
            'status'  => 'Above Average'
        ];
    }
}

// ----------------------------------------------------
// 1. STATS ACTION
// ----------------------------------------------------
if ($action === 'stats') {
    $totalFamilies = db_count('families', "`status` = 'Active'");
    $totalMembers  = db_count('family_members');
    $avgFamilySize = $totalFamilies > 0 ? round($totalMembers / $totalFamilies, 1) : 0.0;

    $indigentFamilies = db_count('families', "`status` = 'Active' AND `poverty_status` = 'Indigent / Below Poverty Threshold'");
    $fourPsFamilies   = db_count('families', "`status` = 'Active' AND `is_4ps_beneficiary` = 1");
    $soloParentFam    = db_count('families', "`status` = 'Active' AND `family_type` = 'Solo Parent'");
    $ayudaPriority    = db_count('families', "`status` = 'Active' AND `is_ayuda_priority` = 1");

    // Total Assistance disbursed
    $assistanceSum = db_fetch_one("SELECT SUM(`amount_value`) AS total_val, COUNT(`id`) AS total_count FROM `family_assistance_records` WHERE `status` = 'Received'");
    $totalAssistanceValue = (float)($assistanceSum['total_val'] ?? 0);
    $totalAssistanceGrants = (int)($assistanceSum['total_count'] ?? 0);

    // Breakdown by Purok
    $purokRows = db_fetch_all("
        SELECT `purok`, COUNT(`id`) as total 
        FROM `families` 
        WHERE `status` = 'Active' 
        GROUP BY `purok` 
        ORDER BY `purok` ASC
    ");

    // Breakdown by Family Type
    $typeRows = db_fetch_all("
        SELECT `family_type`, COUNT(`id`) as total 
        FROM `families` 
        WHERE `status` = 'Active' 
        GROUP BY `family_type` 
        ORDER BY total DESC
    ");

    json_response(true, [
        'total'                  => $totalFamilies,
        'avg_family_size'        => $avgFamilySize,
        'indigent_families'      => $indigentFamilies,
        'four_ps_families'       => $fourPsFamilies,
        'solo_parent_families'   => $soloParentFam,
        'ayuda_priority'         => $ayudaPriority,
        'total_assistance_value' => $totalAssistanceValue,
        'total_assistance_grants'=> $totalAssistanceGrants,
        'purok_breakdown'        => $purokRows,
        'type_breakdown'         => $typeRows
    ]);
}

// ----------------------------------------------------
// 2. GET SINGLE FAMILY DOSSIER
// ----------------------------------------------------
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? ($input['id'] ?? 0));
    if ($id <= 0) {
        json_response(false, null, 'Invalid family ID.', 400);
    }

    $family = db_fetch_one("
        SELECT f.*,
               r.first_name AS head_first_name,
               r.middle_name AS head_middle_name,
               r.last_name AS head_last_name,
               r.suffix AS head_suffix,
               r.resident_code AS head_resident_code,
               r.birthdate AS head_birthdate,
               r.age AS head_age,
               r.gender AS head_gender,
               r.civil_status AS head_civil_status,
               r.contact_no AS head_contact,
               r.occupation AS head_occupation,
               r.is_senior AS head_is_senior,
               r.is_pwd AS head_is_pwd,
               r.is_solo_parent AS head_is_solo_parent,
               r.photo_url AS head_photo_url,
               h.household_no,
               h.street AS household_street,
               h.structure_type AS household_structure,
               h.tenure_status AS household_tenure,
               h.hazard_risk AS household_hazard
        FROM `families` f
        LEFT JOIN `residents` r ON f.head_resident_id = r.id
        LEFT JOIN `households` h ON f.household_id = h.id
        WHERE f.id = ?
    ", [$id]);

    if (!$family) {
        json_response(false, null, 'Family record not found.', 404);
    }

    // Build Head Full Name
    $family['head_full_name'] = trim(sprintf('%s %s %s %s', 
        $family['head_first_name'], 
        $family['head_middle_name'], 
        $family['head_last_name'], 
        $family['head_suffix']
    ));

    // Fetch Family Members with Resident Demographics
    $members = db_fetch_all("
        SELECT fm.*,
               r.resident_code,
               r.first_name,
               r.middle_name,
               r.last_name,
               r.suffix,
               r.birthdate,
               r.age,
               r.gender,
               r.civil_status,
               r.voter_status,
               r.is_senior,
               r.is_pwd,
               r.is_solo_parent,
               r.is_indigent,
               r.is_4ps,
               r.contact_no,
               r.photo_url
        FROM `family_members` fm
        JOIN `residents` r ON fm.resident_id = r.id
        WHERE fm.family_id = ?
        ORDER BY 
            CASE 
                WHEN fm.relationship_to_head = 'Head' THEN 1
                WHEN fm.relationship_to_head = 'Spouse' THEN 2
                ELSE 3
            END,
            r.birthdate ASC
    ", [$id]);

    // Format member full names
    foreach ($members as &$m) {
        $m['full_name'] = trim(sprintf('%s %s %s %s', 
            $m['first_name'], 
            $m['middle_name'], 
            $m['last_name'], 
            $m['suffix']
        ));
    }
    unset($m);

    // Fetch Assistance / Ayuda History
    $assistance = db_fetch_all("
        SELECT * 
        FROM `family_assistance_records`
        WHERE `family_id` = ?
        ORDER BY `date_provided` DESC, `id` DESC
    ", [$id]);

    $family['members'] = $members;
    $family['member_count'] = count($members);
    $family['assistance_records'] = $assistance;

    json_response(true, $family, 'Family dossier retrieved successfully.');
}

// ----------------------------------------------------
// 3. CREATE FAMILY ACTION
// ----------------------------------------------------
if ($action === 'create' || ($method === 'POST' && empty($action))) {
    $familyName      = trim($input['family_name'] ?? '');
    $householdId     = !empty($input['household_id']) ? (int)$input['household_id'] : null;
    $headResidentId  = (int)($input['head_resident_id'] ?? 0);
    $familyType      = trim($input['family_type'] ?? 'Nuclear');
    $purok           = trim($input['purok'] ?? '');
    $monthlyIncome   = (float)($input['monthly_income'] ?? 0.0);
    $is4ps           = !empty($input['is_4ps_beneficiary']) ? 1 : 0;
    $fourPsNumber    = !empty($input['four_ps_number']) ? trim($input['four_ps_number']) : null;
    $isAyudaPriority = !empty($input['is_ayuda_priority']) ? 1 : 0;
    $housingTenure   = trim($input['housing_tenure'] ?? 'Owner');
    $mainIncome      = trim($input['main_source_of_income'] ?? 'Employment / Wages');
    $remarks         = trim($input['remarks'] ?? '');
    $members         = $input['members'] ?? []; // array of { resident_id, relationship_to_head, is_income_earner, monthly_income, occupation, education_level }

    if (empty($headResidentId)) {
        json_response(false, null, 'A registered resident must be designated as Head of Family.', 400);
    }

    // Verify Head Resident exists
    $headResident = db_fetch_one("SELECT * FROM `residents` WHERE `id` = ?", [$headResidentId]);
    if (!$headResident) {
        json_response(false, null, 'Designated Head Resident not found.', 404);
    }

    // Inherit purok from head if not provided
    if (empty($purok)) {
        $purok = $headResident['purok'] ?? 'Purok 1';
    }

    // Default Family Name if not provided
    if (empty($familyName)) {
        $familyName = trim($headResident['last_name']) . ' Family';
    }

    // Auto-generate sequential family code (e.g. FAM-2026-00001)
    $year = date('Y');
    $lastFam = db_fetch_one("SELECT `id` FROM `families` ORDER BY `id` DESC LIMIT 1");
    $nextSeq = ($lastFam ? (int)$lastFam['id'] : 0) + 1;
    $familyCode = sprintf('FAM-%s-%05d', $year, $nextSeq);

    // Sum member incomes if provided
    $totalIncome = $monthlyIncome;
    foreach ($members as $m) {
        if (!empty($m['is_income_earner']) && !empty($m['monthly_income'])) {
            $totalIncome += (float)$m['monthly_income'];
        }
    }

    $eval = evaluate_poverty_status($totalIncome);

    $familyData = [
        'family_code'          => $familyCode,
        'family_name'          => $familyName,
        'household_id'         => $householdId,
        'head_resident_id'     => $headResidentId,
        'family_type'          => $familyType,
        'purok'                => $purok,
        'monthly_income'       => $totalIncome,
        'income_bracket'       => $eval['bracket'],
        'poverty_status'       => $eval['status'],
        'is_4ps_beneficiary'   => $is4ps,
        'four_ps_number'       => $fourPsNumber,
        'is_ayuda_priority'    => $isAyudaPriority,
        'housing_tenure'       => $housingTenure,
        'main_source_of_income'=> $mainIncome,
        'remarks'              => $remarks,
        'status'               => 'Active'
    ];

    $familyId = db_insert('families', $familyData);
    if (!$familyId) {
        json_response(false, null, 'Failed to create family record.', 500);
    }

    // 1. Insert Head into family_members
    db_insert('family_members', [
        'family_id'            => $familyId,
        'resident_id'          => $headResidentId,
        'relationship_to_head' => 'Head',
        'is_income_earner'     => $monthlyIncome > 0 ? 1 : 0,
        'monthly_income'       => $monthlyIncome,
        'occupation'           => $headResident['occupation'] ?? 'Head of Family',
        'education_level'      => 'High School',
        'is_dependent'         => 0
    ]);

    // 2. Insert additional members
    foreach ($members as $mem) {
        $mResId = (int)($mem['resident_id'] ?? 0);
        if ($mResId <= 0 || $mResId === $headResidentId) continue;

        $rel = trim($mem['relationship_to_head'] ?? 'Son');
        $isEarn = !empty($mem['is_income_earner']) ? 1 : 0;
        $mIncome = (float)($mem['monthly_income'] ?? 0.0);
        $occ = trim($mem['occupation'] ?? '');
        $edu = trim($mem['education_level'] ?? 'High School');
        $isDep = isset($mem['is_dependent']) ? (int)$mem['is_dependent'] : ($isEarn ? 0 : 1);

        try {
            db_insert('family_members', [
                'family_id'            => $familyId,
                'resident_id'          => $mResId,
                'relationship_to_head' => $rel,
                'is_income_earner'     => $isEarn,
                'monthly_income'       => $mIncome,
                'occupation'           => $occ,
                'education_level'      => $edu,
                'is_dependent'         => $isDep
            ]);
        } catch (Exception $e) {
            // Ignore duplicate resident constraint
        }
    }

    log_audit_action('CREATE_FAMILY', 'families', "Registered $familyName ($familyCode) under Head #$headResidentId");
    json_response(true, ['id' => $familyId, 'family_code' => $familyCode], 'Family created successfully.', 201);
}

// ----------------------------------------------------
// 4. UPDATE FAMILY ACTION
// ----------------------------------------------------
if ($action === 'update') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid family ID.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM `families` WHERE `id` = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Family not found.', 404);
    }

    $updateData = [];
    $allowed = [
        'family_name', 'household_id', 'family_type', 'purok', 
        'housing_tenure', 'main_source_of_income', 'remarks', 
        'is_4ps_beneficiary', 'four_ps_number', 'is_ayuda_priority', 'status'
    ];

    foreach ($allowed as $f) {
        if (isset($input[$f])) {
            $updateData[$f] = $input[$f];
        }
    }

    // Recompute total income if updated
    if (isset($input['monthly_income'])) {
        $updateData['monthly_income'] = (float)$input['monthly_income'];
        $eval = evaluate_poverty_status($updateData['monthly_income']);
        $updateData['income_bracket'] = $eval['bracket'];
        $updateData['poverty_status'] = $eval['status'];
    }

    if (!empty($updateData)) {
        db_update('families', $updateData, "`id` = ?", [$id]);
    }

    log_audit_action('UPDATE_FAMILY', 'families', "Updated family record ID #$id ({$existing['family_code']})");
    json_response(true, ['id' => $id], 'Family updated successfully.');
}

// ----------------------------------------------------
// 5. ADD MEMBER TO FAMILY
// ----------------------------------------------------
if ($action === 'add_member') {
    $familyId    = (int)($input['family_id'] ?? 0);
    $residentId  = (int)($input['resident_id'] ?? 0);
    $relation    = trim($input['relationship_to_head'] ?? 'Son');
    $isEarner    = !empty($input['is_income_earner']) ? 1 : 0;
    $income      = (float)($input['monthly_income'] ?? 0.0);
    $occupation  = trim($input['occupation'] ?? '');
    $education   = trim($input['education_level'] ?? 'High School');
    $isDependent = isset($input['is_dependent']) ? (int)$input['is_dependent'] : ($isEarner ? 0 : 1);

    if ($familyId <= 0 || $residentId <= 0) {
        json_response(false, null, 'Family ID and Resident ID are required.', 400);
    }

    // Check duplicate
    $exists = db_fetch_one("SELECT `id` FROM `family_members` WHERE `family_id` = ? AND `resident_id` = ?", [$familyId, $residentId]);
    if ($exists) {
        json_response(false, null, 'This resident is already enlisted in this family.', 409);
    }

    $newMemberId = db_insert('family_members', [
        'family_id'            => $familyId,
        'resident_id'          => $residentId,
        'relationship_to_head' => $relation,
        'is_income_earner'     => $isEarner,
        'monthly_income'       => $income,
        'occupation'           => $occupation,
        'education_level'      => $education,
        'is_dependent'         => $isDependent
    ]);

    // Recalculate family income
    $sumIncome = db_fetch_one("SELECT SUM(`monthly_income`) as tot FROM `family_members` WHERE `family_id` = ?", [$familyId]);
    $newTot = (float)($sumIncome['tot'] ?? 0);
    $eval = evaluate_poverty_status($newTot);

    db_update('families', [
        'monthly_income' => $newTot,
        'income_bracket' => $eval['bracket'],
        'poverty_status' => $eval['status']
    ], "`id` = ?", [$familyId]);

    log_audit_action('ADD_FAMILY_MEMBER', 'family_members', "Added resident #$residentId ($relation) to Family #$familyId");
    json_response(true, ['member_id' => $newMemberId], 'Member added to family successfully.');
}

// ----------------------------------------------------
// 6. REMOVE MEMBER FROM FAMILY
// ----------------------------------------------------
if ($action === 'remove_member') {
    $familyId = (int)($input['family_id'] ?? 0);
    $memberId = (int)($input['member_id'] ?? ($input['id'] ?? 0));

    if ($memberId <= 0) {
        json_response(false, null, 'Member ID is required.', 400);
    }

    if ($familyId <= 0) {
        $mRecord = db_fetch_one("SELECT `family_id` FROM `family_members` WHERE `id` = ?", [$memberId]);
        if ($mRecord) {
            $familyId = (int)$mRecord['family_id'];
        }
    }

    if ($familyId <= 0) {
        json_response(false, null, 'Associated Family ID could not be identified.', 400);
    }

    $member = db_fetch_one("SELECT * FROM `family_members` WHERE `id` = ? AND `family_id` = ?", [$memberId, $familyId]);
    if (!$member) {
        json_response(false, null, 'Family member record not found.', 404);
    }

    if ($member['relationship_to_head'] === 'Head') {
        json_response(false, null, 'Cannot remove the Head of Family directly. Please reassign the family head first.', 400);
    }

    db_delete('family_members', "`id` = ?", [$memberId]);

    // Recalculate family income
    $sumIncome = db_fetch_one("SELECT SUM(`monthly_income`) as tot FROM `family_members` WHERE `family_id` = ?", [$familyId]);
    $newTot = (float)($sumIncome['tot'] ?? 0);
    $eval = evaluate_poverty_status($newTot);

    db_update('families', [
        'monthly_income' => $newTot,
        'income_bracket' => $eval['bracket'],
        'poverty_status' => $eval['status']
    ], "`id` = ?", [$familyId]);

    log_audit_action('REMOVE_FAMILY_MEMBER', 'family_members', "Removed member #$memberId from Family #$familyId");
    json_response(true, null, 'Family member removed successfully.');
}

// ----------------------------------------------------
// 7. CHANGE FAMILY HEAD
// ----------------------------------------------------
if ($action === 'change_head') {
    $familyId    = (int)($input['family_id'] ?? 0);
    $newHeadId   = (int)($input['new_head_resident_id'] ?? 0);
    $prevHeadRel = trim($input['previous_head_relationship'] ?? 'Spouse');

    if ($familyId <= 0 || $newHeadId <= 0) {
        json_response(false, null, 'Family ID and New Head Resident ID are required.', 400);
    }

    $family = db_fetch_one("SELECT * FROM `families` WHERE `id` = ?", [$familyId]);
    if (!$family) {
        json_response(false, null, 'Family not found.', 404);
    }

    $oldHeadId = $family['head_resident_id'];

    // 1. Demote old head in family_members
    db_update('family_members', ['relationship_to_head' => $prevHeadRel], "`family_id` = ? AND `resident_id` = ?", [$familyId, $oldHeadId]);

    // 2. Check if new head is already a member; if yes, promote, else insert
    $newHeadMember = db_fetch_one("SELECT `id` FROM `family_members` WHERE `family_id` = ? AND `resident_id` = ?", [$familyId, $newHeadId]);
    if ($newHeadMember) {
        db_update('family_members', ['relationship_to_head' => 'Head', 'is_dependent' => 0], "`id` = ?", [$newHeadMember['id']]);
    } else {
        $resInfo = db_fetch_one("SELECT `occupation` FROM `residents` WHERE `id` = ?", [$newHeadId]);
        db_insert('family_members', [
            'family_id'            => $familyId,
            'resident_id'          => $newHeadId,
            'relationship_to_head' => 'Head',
            'is_income_earner'     => 1,
            'monthly_income'       => 0,
            'occupation'           => $resInfo['occupation'] ?? 'Head of Family',
            'education_level'      => 'High School',
            'is_dependent'         => 0
        ]);
    }

    // 3. Update family record
    db_update('families', ['head_resident_id' => $newHeadId], "`id` = ?", [$familyId]);

    log_audit_action('CHANGE_FAMILY_HEAD', 'families', "Transferred leadership of Family #$familyId from Resident #$oldHeadId to #$newHeadId");
    json_response(true, null, 'Family head successfully updated.');
}

// ----------------------------------------------------
// 8. RECORD ASSISTANCE / AYUDA GRANT
// ----------------------------------------------------
if ($action === 'record_assistance') {
    $familyId    = (int)($input['family_id'] ?? 0);
    $progName    = trim($input['program_name'] ?? 'Barangay Emergency Assistance');
    $asstType    = trim($input['assistance_type'] ?? 'Food Pack / In-Kind');
    $amount      = (float)($input['amount_value'] ?? 0.0);
    $items       = trim($input['items_description'] ?? '');
    $dateProv    = trim($input['date_provided'] ?? date('Y-m-d'));
    $disbursedBy = trim($input['disbursed_by'] ?? 'Barangay Social Welfare Committee');
    $dafacNo     = trim($input['dafac_no'] ?? '');
    $remarks     = trim($input['remarks'] ?? '');

    if ($familyId <= 0) {
        json_response(false, null, 'Valid Family ID is required.', 400);
    }

    $asstId = db_insert('family_assistance_records', [
        'family_id'         => $familyId,
        'program_name'      => $progName,
        'assistance_type'   => $asstType,
        'amount_value'      => $amount,
        'items_description' => $items,
        'date_provided'     => $dateProv,
        'disbursed_by'      => $disbursedBy,
        'dafac_no'          => $dafacNo,
        'status'            => 'Received',
        'remarks'           => $remarks
    ]);

    log_audit_action('RECORD_AYUDA', 'family_assistance_records', "Logged $asstType ($progName) of ₱" . number_format($amount, 2) . " to Family #$familyId");
    json_response(true, ['assistance_id' => $asstId], 'Assistance grant recorded successfully.', 201);
}

// ----------------------------------------------------
// 9. DELETE ASSISTANCE ENTRY
// ----------------------------------------------------
if ($action === 'delete_assistance') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid assistance ID.', 400);
    }

    db_delete('family_assistance_records', "`id` = ?", [$id]);
    log_audit_action('DELETE_AYUDA', 'family_assistance_records', "Deleted assistance record #$id");
    json_response(true, null, 'Assistance entry removed successfully.');
}

// ----------------------------------------------------
// 10. DELETE FAMILY
// ----------------------------------------------------
if ($action === 'delete') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        json_response(false, null, 'Invalid family ID.', 400);
    }

    $fam = db_fetch_one("SELECT `family_code`, `family_name` FROM `families` WHERE `id` = ?", [$id]);
    if (!$fam) {
        json_response(false, null, 'Family not found.', 404);
    }

    db_delete('families', "`id` = ?", [$id]);
    log_audit_action('DELETE_FAMILY', 'families', "Deleted {$fam['family_name']} ({$fam['family_code']})");
    json_response(true, null, 'Family record deleted successfully.');
}

// ----------------------------------------------------
// 11. LIST FAMILY MEMBERS
// ----------------------------------------------------
if ($action === 'members') {
    $familyId = (int)($_GET['family_id'] ?? ($input['family_id'] ?? 0));
    $sql = "
        SELECT fm.*,
               r.resident_code,
               r.first_name,
               r.middle_name,
               r.last_name,
               r.suffix,
               r.birthdate,
               r.age,
               r.gender,
               r.civil_status,
               r.contact_no,
               r.is_senior,
               r.is_pwd,
               r.is_solo_parent,
               r.photo_url
        FROM `family_members` fm
        JOIN `residents` r ON fm.resident_id = r.id
    ";
    $params = [];
    if ($familyId > 0) {
        $sql .= " WHERE fm.family_id = ?";
        $params[] = $familyId;
    }
    $sql .= " ORDER BY fm.id ASC";
    $members = db_fetch_all($sql, $params);
    foreach ($members as &$m) {
        $m['full_name'] = trim(sprintf('%s %s %s %s', $m['first_name'], $m['middle_name'], $m['last_name'], $m['suffix']));
    }
    unset($m);
    json_response(true, $members);
}

// ----------------------------------------------------
// 12. LIST ASSISTANCE RECORDS
// ----------------------------------------------------
if ($action === 'assistance') {
    $familyId = (int)($_GET['family_id'] ?? ($input['family_id'] ?? 0));
    $sql = "
        SELECT ar.*,
               f.family_code,
               f.family_name,
               f.purok
        FROM `family_assistance_records` ar
        JOIN `families` f ON ar.family_id = f.id
    ";
    $params = [];
    if ($familyId > 0) {
        $sql .= " WHERE ar.family_id = ?";
        $params[] = $familyId;
    }
    $sql .= " ORDER BY ar.date_provided DESC, ar.id DESC";
    $records = db_fetch_all($sql, $params);
    json_response(true, $records);
}

// ----------------------------------------------------
// 13. DEFAULT LIST ACTION
// ----------------------------------------------------
$purokFilter    = trim($_GET['purok'] ?? '');
$povertyFilter  = trim($_GET['poverty_status'] ?? '');
$fourPsFilter   = isset($_GET['is_4ps']) ? trim($_GET['is_4ps']) : '';
$typeFilter     = trim($_GET['family_type'] ?? '');
$householdFilter= (int)($_GET['household_id'] ?? 0);
$q              = trim($_GET['q'] ?? '');

$sql = "
    SELECT f.*,
           r.first_name AS head_first_name,
           r.middle_name AS head_middle_name,
           r.last_name AS head_last_name,
           r.suffix AS head_suffix,
           r.contact_no AS head_contact,
           r.photo_url AS head_photo_url,
           h.household_no,
           h.street AS household_street,
           (SELECT COUNT(fm.id) FROM `family_members` fm WHERE fm.family_id = f.id) AS member_count
    FROM `families` f
    LEFT JOIN `residents` r ON f.head_resident_id = r.id
    LEFT JOIN `households` h ON f.household_id = h.id
    WHERE f.status != 'Archived'
";
$params = [];

if (!empty($purokFilter)) {
    $sql .= " AND f.purok = ?";
    $params[] = $purokFilter;
}

if (!empty($povertyFilter)) {
    $sql .= " AND f.poverty_status = ?";
    $params[] = $povertyFilter;
}

if ($fourPsFilter !== '') {
    $sql .= " AND f.is_4ps_beneficiary = ?";
    $params[] = (int)$fourPsFilter;
}

if (!empty($typeFilter)) {
    $sql .= " AND f.family_type = ?";
    $params[] = $typeFilter;
}

if ($householdFilter > 0) {
    $sql .= " AND f.household_id = ?";
    $params[] = $householdFilter;
}

if (!empty($q)) {
    $sql .= " AND (f.family_name LIKE ? OR f.family_code LIKE ? OR r.first_name LIKE ? OR r.last_name LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY f.id DESC";

$families = db_fetch_all($sql, $params);

// Add formatted head full name
foreach ($families as &$f) {
    $f['head_full_name'] = trim(sprintf('%s %s %s %s', 
        $f['head_first_name'], 
        $f['head_middle_name'], 
        $f['head_last_name'], 
        $f['head_suffix']
    ));
}
unset($f);

json_response(true, $families);
