<?php
/**
 * Barangay Management System (BarangayOS)
 * Public QR Code & Document Verification REST API Endpoint
 * Verifies authenticity of Certificates, Blotter Records, and Incident Spot Reports
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Public endpoint: CORS enabled for external verification checks (e.g. employers, universities, PNP)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$input = get_json_input();
$code = trim($_GET['code'] ?? ($input['code'] ?? ''));

if (empty($code)) {
    json_response(false, null, 'Document Tracking Number or QR verification code is required.', 400);
}

// ----------------------------------------------------
// 1. Search in CERTIFICATES & CLEARANCES
// ----------------------------------------------------
$cert = db_fetch_one("
    SELECT c.*, 
           r.first_name, r.middle_name, r.last_name, r.suffix, r.resident_code, 
           r.purok, r.street, r.civil_status, r.voter_status
    FROM `certificates` c
    JOIN `residents` r ON c.resident_id = r.id
    WHERE c.tracking_no = ? OR c.qr_token = ?
", [$code, $code]);

if ($cert) {
    $fullName = trim("{$cert['first_name']} " . (!empty($cert['middle_name']) ? "{$cert['middle_name']} " : '') . "{$cert['last_name']} {$cert['suffix']}");
    $address  = trim("{$cert['street']}, {$cert['purok']}");
    $isValid  = ($cert['status'] === 'Active');

    $response = [
        'document_category' => 'Certificate',
        'document_title'    => $cert['cert_type'],
        'tracking_no'       => $cert['tracking_no'],
        'qr_token'          => $cert['qr_token'] ?? $cert['tracking_no'],
        'status'            => $cert['status'],
        'is_valid'          => $isValid,
        'issued_to'         => [
            'full_name'      => $fullName,
            'resident_code'  => $cert['resident_code'],
            'civil_status'   => $cert['civil_status'],
            'address'        => $address,
            'purok'          => $cert['purok'],
            'voter_status'   => $cert['voter_status'] ?? 'Registered'
        ],
        'issuance_details'  => [
            'purpose'       => $cert['purpose'],
            'or_no'         => $cert['or_no'],
            'amount_paid'   => (float)$cert['amount_paid'],
            'is_waived'     => (bool)$cert['is_waived'],
            'issued_by'     => $cert['issued_by'],
            'issued_at'     => $cert['issued_at'],
            'signatory'     => 'HON. PUNONG BARANGAY'
        ],
        'security_seal'     => [
            'digital_hash'  => hash('sha256', $cert['tracking_no'] . $cert['issued_at'] . 'BarangayOS_Seal'),
            'verified_at'   => date('Y-m-d H:i:s'),
            'authority'     => 'Office of the Punong Barangay - Official Registry'
        ]
    ];

    json_response(true, $response, 'Official Certificate verified successfully.');
}

// ----------------------------------------------------
// 2. Search in LUPON & BLOTTER CASES (KP Forms)
// ----------------------------------------------------
$blotter = db_fetch_one("
    SELECT b.*
    FROM `blotter_cases` b
    WHERE b.case_no = ?
", [$code]);

if ($blotter) {
    $response = [
        'document_category' => 'Lupon Tagapamayapa Record',
        'document_title'    => "KP Legal Form - Case # {$blotter['case_no']}",
        'tracking_no'       => $blotter['case_no'],
        'qr_token'          => $blotter['case_no'],
        'status'            => $blotter['status'],
        'is_valid'          => true,
        'issued_to'         => [
            'complainant'   => $blotter['complainant_name'],
            'respondent'    => $blotter['respondent_name'],
            'jurisdiction'  => "Barangay San Isidro, Purok {$blotter['purok']}"
        ],
        'issuance_details'  => [
            'incident_type' => $blotter['incident_type'],
            'incident_date' => $blotter['incident_date'],
            'hearing_date'  => $blotter['hearing_date'],
            'purok'         => $blotter['purok'],
            'case_status'   => $blotter['status'],
            'created_at'    => $blotter['created_at'],
            'authority'     => 'Tanggapan ng Lupong Tagapamayapa'
        ],
        'security_seal'     => [
            'digital_hash'  => hash('sha256', $blotter['case_no'] . $blotter['created_at'] . 'BarangayOS_Lupon'),
            'verified_at'   => date('Y-m-d H:i:s'),
            'authority'     => 'Lupong Tagapamayapa Peace & Order Docket'
        ]
    ];

    json_response(true, $response, 'Official Lupon Tagapamayapa record verified successfully.');
}

// ----------------------------------------------------
// 3. Search in INCIDENT SPOT REPORTS (BISR)
// ----------------------------------------------------
$incident = db_fetch_one("
    SELECT i.*
    FROM `incidents` i
    WHERE i.incident_no = ?
", [$code]);

if ($incident) {
    $response = [
        'document_category' => 'Barangay Incident Spot Report (BISR)',
        'document_title'    => "Spot Report # {$incident['incident_no']}",
        'tracking_no'       => $incident['incident_no'],
        'qr_token'          => $incident['incident_no'],
        'status'            => $incident['status'],
        'is_valid'          => true,
        'issued_to'         => [
            'caller_name'   => $incident['caller_name'],
            'contact'       => $incident['caller_contact'],
            'location'      => $incident['location'],
            'purok'         => $incident['purok']
        ],
        'issuance_details'  => [
            'incident_type' => $incident['type'],
            'priority'      => $incident['priority'],
            'reported_at'   => $incident['reported_at'],
            'responder'     => $incident['responder_name'] ?: 'Barangay Tanod On-Duty',
            'vehicle_unit'  => $incident['vehicle_unit'] ?: 'Patrol Desk',
            'authority'     => 'Barangay Peace and Order Council (BPOC)'
        ],
        'security_seal'     => [
            'digital_hash'  => hash('sha256', $incident['incident_no'] . $incident['reported_at'] . 'BarangayOS_Dispatch'),
            'verified_at'   => date('Y-m-d H:i:s'),
            'authority'     => 'Barangay Emergency Incident Operations Center'
        ]
    ];

    json_response(true, $response, 'Official Barangay Incident Spot Report verified successfully.');
}

// ----------------------------------------------------
// 4. Search in RESIDENT IDENTIFICATION CARDS (PVC CR80)
// ----------------------------------------------------
$idCard = db_fetch_one("
    SELECT i.*, 
           r.first_name, r.middle_name, r.last_name, r.suffix, r.resident_code, 
           r.birthdate, r.age, r.gender, r.civil_status, r.occupation,
           r.purok, r.street, r.contact_no, r.voter_status,
           r.is_senior, r.is_pwd, r.is_solo_parent, r.is_4ps, r.is_indigent
    FROM `resident_ids` i
    JOIN `residents` r ON i.resident_id = r.id
    WHERE i.id_number = ?
", [$code]);

if ($idCard) {
    $fullName = trim("{$idCard['first_name']} " . (!empty($idCard['middle_name']) ? "{$idCard['middle_name']} " : '') . "{$idCard['last_name']} {$idCard['suffix']}");
    $address  = trim("{$idCard['street']}, {$idCard['purok']}");
    $isValid  = ($idCard['status'] === 'Active');

    $response = [
        'document_category' => 'Resident ID Card',
        'document_title'    => 'Barangay Resident ID Card (CR80)',
        'tracking_no'       => $idCard['id_number'],
        'qr_token'          => $idCard['id_number'],
        'status'            => $idCard['status'],
        'is_valid'          => $isValid,
        'photo_url'         => $idCard['photo_url'] ?: null,
        'issued_to'         => [
            'full_name'      => $fullName,
            'resident_code'  => $idCard['resident_code'],
            'civil_status'   => $idCard['civil_status'],
            'gender'         => $idCard['gender'],
            'birthdate'      => $idCard['birthdate'],
            'age'            => $idCard['age'],
            'blood_type'     => $idCard['blood_type'],
            'address'        => $address,
            'purok'          => $idCard['purok'],
            'voter_status'   => $idCard['voter_status'] ?? 'Registered'
        ],
        'issuance_details'  => [
            'blood_type'        => $idCard['blood_type'],
            'emergency_contact' => $idCard['emergency_name'] ? "{$idCard['emergency_name']} ({$idCard['emergency_contact']})" : 'Barangay Desk Contact',
            'valid_until'       => $idCard['valid_until'],
            'signatory'         => $idCard['signatory_name'] ?: 'HON. PUNONG BARANGAY',
            'issued_by'         => $idCard['issued_by'],
            'issued_at'         => $idCard['created_at']
        ],
        'security_seal'     => [
            'digital_hash'  => hash('sha256', $idCard['id_number'] . $idCard['created_at'] . 'BarangayOS_PVC_ID'),
            'verified_at'   => date('Y-m-d H:i:s'),
            'authority'     => 'Barangay Executive PVC Identification Registry'
        ]
    ];

    json_response(true, $response, 'Official Resident ID card verified successfully.');
}

// ----------------------------------------------------
// 5. Record Not Found in Any Registry
// ----------------------------------------------------
json_response(false, [
    'searched_code' => $code,
    'status'        => 'NOT_FOUND',
    'verified_at'   => date('Y-m-d H:i:s')
], 'Document tracking code not found in official registry. This document may be invalid, fraudulent, or unregistered.', 404);
