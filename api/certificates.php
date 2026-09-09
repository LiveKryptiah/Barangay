<?php
/**
 * Barangay Management System (BarangayOS)
 * Clearances & Certificates REST API Endpoint
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
// 1. PUBLIC VERIFICATION ACTION (No auth required)
// ----------------------------------------------------
if ($action === 'verify') {
    $code = trim($_GET['code'] ?? ($input['code'] ?? ''));
    if (empty($code)) {
        json_response(false, null, 'Tracking code is required.', 400);
    }

    $cert = db_fetch_one("
        SELECT c.*, 
               r.first_name, r.middle_name, r.last_name, r.suffix, r.resident_code, r.purok, r.street, r.civil_status
        FROM `certificates` c
        JOIN `residents` r ON c.resident_id = r.id
        WHERE c.tracking_no = ? OR c.qr_token = ?
    ", [$code, $code]);

    if (!$cert) {
        json_response(false, null, 'Certificate not found or invalid digital tracking code.', 404);
    }

    json_response(true, $cert, 'Certificate verified.');
}

// All actions below require authentication
require_auth();

// ----------------------------------------------------
// 2. STATS ACTION
// ----------------------------------------------------
if ($action === 'stats') {
    $total    = db_count('certificates');
    $active   = db_count('certificates', "`status` = 'Active'");
    $revoked  = db_count('certificates', "`status` = 'Revoked'");

    $revRow = db_fetch_one("SELECT COALESCE(SUM(amount_paid), 0) AS total_revenue FROM `certificates` WHERE `status` = 'Active'");
    $totalRevenue = (float)($revRow['total_revenue'] ?? 0);

    json_response(true, [
        'total'         => $total,
        'active'        => $active,
        'revoked'       => $revoked,
        'total_revenue' => $totalRevenue
    ]);
}

// ----------------------------------------------------
// 3. ISSUE CERTIFICATE
// ----------------------------------------------------
if ($action === 'issue' || ($method === 'POST' && empty($action))) {
    $residentId = (int)($input['resident_id'] ?? 0);
    $certType   = trim($input['cert_type'] ?? 'Barangay Clearance');
    $purpose    = trim($input['purpose'] ?? 'Employment');
    $orNo       = trim($input['or_no'] ?? '');
    $amountPaid = (float)($input['amount_paid'] ?? 50.00);
    $isWaived   = !empty($input['is_waived']) ? 1 : 0;
    $issuedBy   = trim($input['issued_by'] ?? 'Barangay Secretary');

    if ($residentId <= 0) {
        json_response(false, null, 'Please select a registered community resident.', 400);
    }

    $resident = db_fetch_one("SELECT * FROM `residents` WHERE `id` = ?", [$residentId]);
    if (!$resident) {
        json_response(false, null, 'Resident not found.', 404);
    }

    // Check auto fee waiver policy
    if ($resident['is_4ps'] || $resident['is_indigent']) {
        $isWaived = 1;
        $amountPaid = 0.00;
    }

    // Generate unique tracking number (e.g. BC-2026-00001)
    $prefix = 'BC';
    if (stripos($certType, 'Indigency') !== false) {
        $prefix = 'CI';
    } elseif (stripos($certType, 'Residency') !== false) {
        $prefix = 'CR';
    } elseif (stripos($certType, 'Business') !== false) {
        $prefix = 'BP';
    }

    $year = date('Y');
    $lastCert = db_fetch_one("SELECT `id` FROM `certificates` ORDER BY `id` DESC LIMIT 1");
    $nextSeq = ($lastCert ? (int)$lastCert['id'] : 0) + 1;
    $trackingNo = sprintf('%s-%s-%05d', $prefix, $year, $nextSeq);
    $qrToken = bin2hex(random_bytes(16));

    $certId = db_insert('certificates', [
        'tracking_no' => $trackingNo,
        'resident_id' => $residentId,
        'cert_type'   => $certType,
        'purpose'     => $purpose,
        'or_no'       => $orNo,
        'amount_paid' => $amountPaid,
        'is_waived'   => $isWaived,
        'status'      => 'Active',
        'issued_by'   => $issuedBy,
        'qr_token'    => $qrToken
    ]);

    log_audit_action('CERTIFICATE_ISSUED', 'certificates', "Issued {$certType} ({$trackingNo}) to {$resident['first_name']} {$resident['last_name']}.");

    $newCert = db_fetch_one("
        SELECT c.*, r.first_name, r.last_name, r.resident_code, r.purok, r.street
        FROM `certificates` c
        JOIN `residents` r ON c.resident_id = r.id
        WHERE c.id = ?
    ", [$certId]);

    json_response(true, $newCert, 'Certificate issued successfully.', 201);
}

// ----------------------------------------------------
// 4. REVOKE CERTIFICATE
// ----------------------------------------------------
if ($action === 'revoke') {
    $id = (int)($input['id'] ?? 0);
    $reason = trim($input['reason'] ?? 'Document invalidated by administrator');

    if ($id <= 0) {
        json_response(false, null, 'Invalid certificate ID.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM `certificates` WHERE `id` = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Certificate not found.', 404);
    }

    db_update('certificates', ['status' => 'Revoked'], '`id` = ?', [$id]);
    log_audit_action('CERTIFICATE_REVOKED', 'certificates', "Revoked {$existing['tracking_no']}. Reason: {$reason}");

    json_response(true, ['id' => $id, 'status' => 'Revoked'], 'Certificate revoked.');
}

// ----------------------------------------------------
// 5. LIST CERTIFICATES
// ----------------------------------------------------
$search = trim($_GET['search'] ?? '');
$type   = trim($_GET['type'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = "
    SELECT c.*, 
           CONCAT(r.first_name, ' ', r.last_name) AS resident_name,
           r.resident_code, r.purok, r.civil_status, r.street
    FROM `certificates` c
    JOIN `residents` r ON c.resident_id = r.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (c.tracking_no LIKE ? OR r.first_name LIKE ? OR r.last_name LIKE ? OR c.or_no LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if (!empty($type) && $type !== 'All') {
    $sql .= " AND c.cert_type = ?";
    $params[] = $type;
}

if (!empty($status) && $status !== 'All') {
    $sql .= " AND c.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY c.id DESC";

$certs = db_fetch_all($sql, $params);
json_response(true, $certs, 'Certificates retrieved.');
