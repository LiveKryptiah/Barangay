<?php
/**
 * Barangay Management System (BarangayOS)
 * Analytical Reports & Aggregated Demographics REST API Endpoint
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

require_auth();

// 1. Demographics Breakdown
$totalResidents = db_count('residents', "`status` = 'Active'");
$maleCount      = db_count('residents', "`gender` = 'Male' AND `status` = 'Active'");
$femaleCount    = db_count('residents', "`gender` = 'Female' AND `status` = 'Active'");
$voterCount     = db_count('residents', "`voter_status` = 'Registered' AND `status` = 'Active'");
$seniorCount    = db_count('residents', "(`age` >= 60 OR `is_senior` = 1) AND `status` = 'Active'");
$pwdCount       = db_count('residents', "`is_pwd` = 1 AND `status` = 'Active'");
$indigentCount  = db_count('residents', "`is_indigent` = 1 AND `status` = 'Active'");
$fourPsCount    = db_count('residents', "`is_4ps` = 1 AND `status` = 'Active'");
$soloParentCount= db_count('residents', "`is_solo_parent` = 1 AND `status` = 'Active'");

// Age brackets
$ageChildren = db_count('residents', "`age` < 18 AND `status` = 'Active'");
$ageYouth    = db_count('residents', "`age` >= 18 AND `age` <= 30 AND `status` = 'Active'");
$ageAdults   = db_count('residents', "`age` >= 31 AND `age` <= 59 AND `status` = 'Active'");
$ageSeniors  = db_count('residents', "`age` >= 60 AND `status` = 'Active'");

// 2. Purok Distribution
$purokResidents = db_fetch_all("
    SELECT `purok`, COUNT(*) AS total 
    FROM `residents` 
    WHERE `status` = 'Active' 
    GROUP BY `purok` 
    ORDER BY `purok` ASC
");

$purokHouseholds = db_fetch_all("
    SELECT `purok`, COUNT(*) AS total 
    FROM `households` 
    GROUP BY `purok` 
    ORDER BY `purok` ASC
");

// 3. Certificates & Revenue Breakdown
$certCounts = db_fetch_all("
    SELECT `cert_type`, COUNT(*) AS total, COALESCE(SUM(`amount_paid`), 0) AS revenue 
    FROM `certificates` 
    WHERE `status` = 'Active' 
    GROUP BY `cert_type`
");

$revTotal = db_fetch_one("SELECT COALESCE(SUM(`amount_paid`), 0) AS total_rev FROM `certificates` WHERE `status` = 'Active'");

// 4. Blotter Disputes
$blotterStatuses = db_fetch_all("
    SELECT `status`, COUNT(*) AS total 
    FROM `blotter_cases` 
    GROUP BY `status`
");

$blotterTypes = db_fetch_all("
    SELECT `incident_type`, COUNT(*) AS total 
    FROM `blotter_cases` 
    GROUP BY `incident_type` 
    ORDER BY total DESC 
    LIMIT 5
");

// 5. Emergency Dispatches
$incidentPriorities = db_fetch_all("
    SELECT `priority`, COUNT(*) AS total 
    FROM `incidents` 
    GROUP BY `priority`
");

$incidentTypes = db_fetch_all("
    SELECT `type`, COUNT(*) AS total 
    FROM `incidents` 
    GROUP BY `type` 
    ORDER BY total DESC 
    LIMIT 5
");

$avgResp = db_fetch_one("SELECT AVG(response_minutes) AS avg_time FROM `incidents` WHERE `response_minutes` > 0");

json_response(true, [
    'demographics' => [
        'total'        => $totalResidents,
        'male'         => $maleCount,
        'female'       => $femaleCount,
        'voters'       => $voterCount,
        'seniors'      => $seniorCount,
        'pwd'          => $pwdCount,
        'indigent'     => $indigentCount,
        'four_ps'      => $fourPsCount,
        'solo_parent'  => $soloParentCount,
        'age_brackets' => [
            'children' => $ageChildren,
            'youth'    => $ageYouth,
            'adults'   => $ageAdults,
            'seniors'  => $ageSeniors
        ]
    ],
    'puroks' => [
        'residents'  => $purokResidents,
        'households' => $purokHouseholds
    ],
    'revenue' => [
        'total'       => (float)($revTotal['total_rev'] ?? 0),
        'by_document' => $certCounts
    ],
    'blotter' => [
        'by_status' => $blotterStatuses,
        'top_types' => $blotterTypes
    ],
    'incidents' => [
        'by_priority'  => $incidentPriorities,
        'top_types'    => $incidentTypes,
        'avg_response' => $avgResp && $avgResp['avg_time'] ? round((float)$avgResp['avg_time'], 1) : 0
    ]
], 'Analytical reports compiled.');
