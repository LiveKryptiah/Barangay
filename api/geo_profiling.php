<?php
/**
 * Barangay Management System (BarangayOS)
 * Purok Demographic Density & Geo-Profiling REST API Endpoint
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Require authentication for demographic profiling data
require_auth();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'summary');

// 1. STATS: Overall KPI telemetry
if ($action === 'stats') {
    $totalResidents = db_count('residents', "`status` = 'Active'");
    $totalHouseholds = db_count('households');
    
    // Most populated purok
    $popByPurok = db_fetch_all("
        SELECT `purok`, COUNT(*) as `count`
        FROM `residents`
        WHERE `status` = 'Active'
        GROUP BY `purok`
        ORDER BY `count` DESC
    ");
    $mostPopulated = $popByPurok[0] ?? ['purok' => 'Purok 1', 'count' => 0];

    // Highest vulnerability purok (seniors + pwd + solo parent + 4ps + indigent)
    $vulnByPurok = db_fetch_all("
        SELECT `purok`, 
               (SUM(`is_senior` = 1 OR `age` >= 60) + 
               SUM(`is_pwd` = 1) + 
               SUM(`is_solo_parent` = 1) + 
               SUM(`is_4ps` = 1) + 
               SUM(`is_indigent` = 1)) as `vuln_score`
        FROM `residents`
        WHERE `status` = 'Active'
        GROUP BY `purok`
        ORDER BY `vuln_score` DESC
    ");
    $highestVuln = $vulnByPurok[0] ?? ['purok' => 'Purok 1', 'vuln_score' => 0];

    // Critical disaster exposure households
    $criticalHazards = db_count('households', "`hazard_risk` IN ('High', 'Critical')");

    json_response(true, [
        'total_residents'  => $totalResidents,
        'total_households' => $totalHouseholds,
        'most_populated'   => $mostPopulated,
        'highest_vuln'     => $highestVuln,
        'critical_hazards' => $criticalHazards,
        'purok_count'      => 7
    ]);
}

// 2. PUROKS SUMMARY: Detailed spatial aggregation per Purok
$standardPuroks = ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5', 'Purok 6', 'Purok 7'];

// Query resident metrics grouped by purok
$resPurokData = db_fetch_all("
    SELECT `purok`,
           COUNT(*) as `total_residents`,
           SUM(`gender` = 'Male') as `male_count`,
           SUM(`gender` = 'Female') as `female_count`,
           SUM(`voter_status` = 'Registered') as `voter_count`,
           SUM(`is_senior` = 1 OR `age` >= 60) as `senior_count`,
           SUM(`is_pwd` = 1) as `pwd_count`,
           SUM(`is_solo_parent` = 1) as `solo_parent_count`,
           SUM(`is_4ps` = 1) as `four_ps_count`,
           SUM(`is_indigent` = 1) as `indigent_count`,
           SUM(`age` < 18) as `child_count`,
           SUM(`age` >= 18 AND `age` < 60) as `adult_count`
    FROM `residents`
    WHERE `status` = 'Active'
    GROUP BY `purok`
");
$resMap = [];
if (is_array($resPurokData)) {
    foreach ($resPurokData as $r) {
        $resMap[$r['purok']] = $r;
    }
}

// Query household metrics grouped by purok
$hhPurokData = db_fetch_all("
    SELECT `purok`,
           COUNT(*) as `total_households`,
           SUM(`hazard_risk` = 'High' OR `hazard_risk` = 'Critical') as `high_risk_count`,
           SUM(`hazard_risk` = 'Medium' OR `hazard_risk` = 'Moderate') as `med_risk_count`,
           SUM(`hazard_risk` = 'Low') as `low_risk_count`
    FROM `households`
    GROUP BY `purok`
");
$hhMap = [];
if (is_array($hhPurokData)) {
    foreach ($hhPurokData as $h) {
        $hhMap[$h['purok']] = $h;
    }
}

// Static GIS spatial metadata per purok (landmarks, zone characteristics, leaders)
$purokMeta = [
    'Purok 1' => [
        'name' => 'Purok 1 - Riverside North',
        'subzone' => 'Waterfront & Lowland',
        'leader' => 'Kgd. Roberto Santos (Disaster Committee)',
        'evacuation_center' => 'Barangay Multi-Purpose Hall',
        'hazard_profile' => 'High Flood Risk (River Corridor)',
        'default_hazard' => 'High'
    ],
    'Purok 2' => [
        'name' => 'Purok 2 - Poblacion Central',
        'subzone' => 'Commercial & Market Center',
        'leader' => 'Kgd. Elena Bautista (Trade & Livelihood)',
        'evacuation_center' => 'Central Elementary Gymnasium',
        'hazard_profile' => 'Low Flood / Commercial Density',
        'default_hazard' => 'Low'
    ],
    'Purok 3' => [
        'name' => 'Purok 3 - Barangay Centro',
        'subzone' => 'Civic & Government Core',
        'leader' => 'Hon. Punong Barangay / Kgd. Manuel Cruz',
        'evacuation_center' => 'Barangay Hall Complex',
        'hazard_profile' => 'Safe Zone / Incident Command Post',
        'default_hazard' => 'Low'
    ],
    'Purok 4' => [
        'name' => 'Purok 4 - Residential Heights',
        'subzone' => 'Subdivision & Family Dwellings',
        'leader' => 'Kgd. Maria Flores (Health & Sanitation)',
        'evacuation_center' => 'Purok 4 Covered Court',
        'hazard_profile' => 'Minimal Hazard Exposure',
        'default_hazard' => 'Low'
    ],
    'Purok 5' => [
        'name' => 'Purok 5 - Western Hillside',
        'subzone' => 'Elevated Slope & Watershed',
        'leader' => 'Kgd. Antonio Reyes (Peace & Order)',
        'evacuation_center' => 'Hillside Chapel Annex',
        'hazard_profile' => 'Moderate Slope / Landslide Watch',
        'default_hazard' => 'Medium'
    ],
    'Purok 6' => [
        'name' => 'Purok 6 - Greenfields Agro',
        'subzone' => 'Agricultural & Open Plains',
        'leader' => 'Kgd. Josefa Dimaculangan (Agriculture)',
        'evacuation_center' => 'Greenfields Elementary School',
        'hazard_profile' => 'Open Wind Exposure / Low Flood',
        'default_hazard' => 'Low'
    ],
    'Purok 7' => [
        'name' => 'Purok 7 - Industrial Highway Rim',
        'subzone' => 'Perimeter & Highway Access',
        'leader' => 'Kgd. Danilo Mercado (Transportation)',
        'evacuation_center' => 'Highway Terminal Pavilion',
        'hazard_profile' => 'Vehicular Traffic / Drainage Focus',
        'default_hazard' => 'Medium'
    ]
];

$result = [];
foreach ($standardPuroks as $pName) {
    $rInfo = $resMap[$pName] ?? [
        'total_residents'   => 0,
        'male_count'        => 0,
        'female_count'      => 0,
        'voter_count'       => 0,
        'senior_count'      => 0,
        'pwd_count'         => 0,
        'solo_parent_count' => 0,
        'four_ps_count'     => 0,
        'indigent_count'    => 0,
        'child_count'       => 0,
        'adult_count'       => 0
    ];

    $hInfo = $hhMap[$pName] ?? [
        'total_households' => 0,
        'high_risk_count'  => 0,
        'med_risk_count'   => 0,
        'low_risk_count'   => 0
    ];

    $meta = $purokMeta[$pName] ?? [
        'name'              => $pName,
        'subzone'           => 'Barangay Sector',
        'leader'            => 'Barangay Council',
        'evacuation_center' => 'Barangay Hall',
        'hazard_profile'    => 'Standard',
        'default_hazard'    => 'Low'
    ];

    $vulnScore = (int)($rInfo['senior_count'] ?? 0) + 
                 (int)($rInfo['pwd_count'] ?? 0) + 
                 (int)($rInfo['solo_parent_count'] ?? 0) + 
                 (int)($rInfo['four_ps_count'] ?? 0) + 
                 (int)($rInfo['indigent_count'] ?? 0);

    $hhCount = (int)($hInfo['total_households'] ?? 0);
    $resCount = (int)($rInfo['total_residents'] ?? 0);
    $avgFamilySize = $hhCount > 0 ? round($resCount / $hhCount, 1) : 0;

    $hazardRating = $meta['default_hazard'];
    if ((int)($hInfo['high_risk_count'] ?? 0) > 0) {
        $hazardRating = 'High';
    } elseif ((int)($hInfo['med_risk_count'] ?? 0) > 0) {
        $hazardRating = 'Medium';
    }

    $result[] = array_merge($meta, [
        'purok'               => $pName,
        'residents'           => $resCount,
        'households'          => $hhCount,
        'avg_family_size'     => $avgFamilySize,
        'males'               => (int)($rInfo['male_count'] ?? 0),
        'females'             => (int)($rInfo['female_count'] ?? 0),
        'voters'              => (int)($rInfo['voter_count'] ?? 0),
        'seniors'             => (int)($rInfo['senior_count'] ?? 0),
        'pwd'                 => (int)($rInfo['pwd_count'] ?? 0),
        'solo_parents'        => (int)($rInfo['solo_parent_count'] ?? 0),
        'four_ps'             => (int)($rInfo['four_ps_count'] ?? 0),
        'indigents'           => (int)($rInfo['indigent_count'] ?? 0),
        'children'            => (int)($rInfo['child_count'] ?? 0),
        'adults'              => (int)($rInfo['adult_count'] ?? 0),
        'vulnerability_score' => $vulnScore,
        'hazard_rating'       => $hazardRating,
        'high_risk_hh'        => (int)($hInfo['high_risk_count'] ?? 0),
        'med_risk_hh'         => (int)($hInfo['med_risk_count'] ?? 0),
        'low_risk_hh'         => (int)($hInfo['low_risk_count'] ?? 0)
    ]);
}

json_response(true, $result);
