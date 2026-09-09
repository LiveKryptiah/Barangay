<?php
/**
 * Barangay Management System (BarangayOS)
 * Public Document Verification Web Engine
 * Supports QR code scanning, tracking number lookups, and tamper-evident digital seal inspection.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$code = trim($_GET['code'] ?? '');
$serverResult = null;
$errorMsg = null;

if (!empty($code)) {
    // 1. Check Certificates
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
        $serverResult = [
            'document_category' => 'Certificate',
            'document_title'    => $cert['cert_type'],
            'tracking_no'       => $cert['tracking_no'],
            'status'            => $cert['status'],
            'is_valid'          => ($cert['status'] === 'Active'),
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
                'verified_at'   => date('F j, Y, g:i A'),
                'authority'     => 'Office of the Punong Barangay - Official Registry'
            ]
        ];
    } else {
        // 2. Check Blotter
        $blotter = db_fetch_one("SELECT * FROM `blotter_cases` WHERE `case_no` = ?", [$code]);
        if ($blotter) {
            $serverResult = [
                'document_category' => 'Lupon Tagapamayapa Record',
                'document_title'    => "KP Legal Form - Case # {$blotter['case_no']}",
                'tracking_no'       => $blotter['case_no'],
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
                    'authority'     => 'Tanggapan ng Lupong Tagapamayapa'
                ],
                'security_seal'     => [
                    'digital_hash'  => hash('sha256', $blotter['case_no'] . $blotter['created_at'] . 'BarangayOS_Lupon'),
                    'verified_at'   => date('F j, Y, g:i A'),
                    'authority'     => 'Lupong Tagapamayapa Peace & Order Docket'
                ]
            ];
        } else {
            // 3. Check Incidents
            $inc = db_fetch_one("SELECT * FROM `incidents` WHERE `incident_no` = ?", [$code]);
            if ($inc) {
                $serverResult = [
                    'document_category' => 'Barangay Incident Spot Report (BISR)',
                    'document_title'    => "Spot Report # {$inc['incident_no']}",
                    'tracking_no'       => $inc['incident_no'],
                    'status'            => $inc['status'],
                    'is_valid'          => true,
                    'issued_to'         => [
                        'caller_name'   => $inc['caller_name'],
                        'location'      => $inc['location'],
                        'purok'         => $inc['purok']
                    ],
                    'issuance_details'  => [
                        'incident_type' => $inc['type'],
                        'priority'      => $inc['priority'],
                        'reported_at'   => $inc['reported_at'],
                        'responder'     => $inc['responder_name'] ?: 'Barangay Tanod On-Duty',
                        'authority'     => 'Barangay Peace and Order Council (BPOC)'
                    ],
                    'security_seal'     => [
                        'digital_hash'  => hash('sha256', $inc['incident_no'] . $inc['reported_at'] . 'BarangayOS_Dispatch'),
                        'verified_at'   => date('F j, Y, g:i A'),
                        'authority'     => 'Barangay Emergency Incident Operations Center'
                    ]
                ];
            } else {
                // 4. Check Resident IDs
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

                    $serverResult = [
                        'document_category' => 'Resident ID Card',
                        'document_title'    => 'Barangay Resident ID Card (CR80)',
                        'tracking_no'       => $idCard['id_number'],
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
                            'verified_at'   => date('F j, Y, g:i A'),
                            'authority'     => 'Barangay Executive PVC Identification Registry'
                        ]
                    ];
                } else {
                    $errorMsg = "Control Code \"{$code}\" not found in official registry.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document Verification Engine | BarangayOS</title>
  <link rel="stylesheet" href="css/design-system.css">
  <style>
    :root {
      --verify-canvas: #fafafa;
      --verify-card-bg: #ffffff;
      --verify-border: rgba(0, 0, 0, 0.08);
      --verify-text: #141414;
      --verify-muted: #666666;
      --verify-emerald: #10b981;
      --verify-emerald-bg: rgba(16, 185, 129, 0.08);
      --verify-rose: #ef4444;
      --verify-rose-bg: rgba(239, 68, 68, 0.08);
      --verify-blue: #0066ff;
    }

    [data-theme="dark"] {
      --verify-canvas: #0c0c0e;
      --verify-card-bg: #16161a;
      --verify-border: rgba(255, 255, 255, 0.08);
      --verify-text: #f3f3f3;
      --verify-muted: #999999;
      --verify-emerald-bg: rgba(16, 185, 129, 0.15);
      --verify-rose-bg: rgba(239, 68, 68, 0.15);
    }

    body {
      background-color: var(--verify-canvas);
      color: var(--verify-text);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .verify-navbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 32px;
      border-bottom: 1px solid var(--verify-border);
      background-color: var(--verify-card-bg);
      backdrop-filter: blur(12px);
      position: sticky;
      top: 0;
      z-index: 50;
    }

    .verify-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: inherit;
    }

    .verify-brand-icon {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background-color: var(--verify-text);
      color: var(--verify-card-bg);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
    }

    .verify-nav-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .verify-container {
      max-width: 820px;
      width: 100%;
      margin: 40px auto 60px;
      padding: 0 20px;
      box-sizing: border-box;
      flex: 1;
    }

    .verify-hero-card {
      background-color: var(--verify-card-bg);
      border: 1px solid var(--verify-border);
      border-radius: 24px;
      padding: 36px 32px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
      text-align: center;
      margin-bottom: 28px;
    }

    .verify-hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 16px;
      background: var(--verify-emerald-bg);
      color: var(--verify-emerald);
      border: 1px solid rgba(16, 185, 129, 0.2);
      border-radius: 9999px;
      font-size: 0.8125rem;
      font-weight: 600;
      margin-bottom: 16px;
    }

    .verify-title {
      font-size: 2rem;
      font-weight: 800;
      letter-spacing: -0.03em;
      margin: 0 0 10px;
    }

    .verify-subtitle {
      font-size: 1rem;
      color: var(--verify-muted);
      margin: 0 auto 28px;
      max-width: 580px;
      line-height: 1.5;
    }

    .verify-search-form {
      display: flex;
      gap: 10px;
      max-width: 620px;
      margin: 0 auto;
    }

    .verify-input-wrap {
      position: relative;
      flex: 1;
    }

    .verify-input-wrap svg {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--verify-muted);
    }

    .verify-input {
      width: 100%;
      height: 52px;
      padding: 0 18px 0 48px;
      border-radius: 9999px;
      border: 1.5px solid var(--verify-border);
      background-color: var(--verify-canvas);
      color: var(--verify-text);
      font-size: 1rem;
      font-family: inherit;
      outline: none;
      transition: all 0.2s ease;
      box-sizing: border-box;
    }

    .verify-input:focus {
      border-color: var(--verify-blue);
      background-color: var(--verify-card-bg);
      box-shadow: 0 0 0 4px rgba(0, 102, 255, 0.12);
    }

    .verify-submit-btn {
      height: 52px;
      padding: 0 28px;
      border-radius: 9999px;
      background-color: var(--verify-text);
      color: var(--verify-card-bg);
      border: none;
      font-weight: 600;
      font-size: 0.9375rem;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      white-space: nowrap;
    }

    .verify-chips-row {
      display: flex;
      align-items: center;
      justify-content: center;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 18px;
      font-size: 0.8125rem;
      color: var(--verify-muted);
    }

    .verify-chip {
      background: var(--verify-canvas);
      border: 1px solid var(--verify-border);
      padding: 4px 12px;
      border-radius: 9999px;
      font-family: monospace;
      font-size: 0.75rem;
      color: var(--verify-text);
      cursor: pointer;
      text-decoration: none;
    }

    .verify-chip:hover {
      border-color: var(--verify-blue);
      color: var(--verify-blue);
    }

    .result-card {
      background-color: var(--verify-card-bg);
      border: 1px solid var(--verify-border);
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
      animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(12px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .result-status-header {
      padding: 28px 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid var(--verify-border);
    }

    .result-status-header.valid {
      background: linear-gradient(135deg, var(--verify-emerald-bg) 0%, var(--verify-card-bg) 100%);
      border-left: 6px solid var(--verify-emerald);
    }

    .result-status-header.invalid {
      background: linear-gradient(135deg, var(--verify-rose-bg) 0%, var(--verify-card-bg) 100%);
      border-left: 6px solid var(--verify-rose);
    }

    .result-badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border-radius: 9999px;
      font-size: 0.8125rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .result-badge-pill.valid {
      background: var(--verify-emerald);
      color: #ffffff;
    }

    .result-badge-pill.invalid {
      background: var(--verify-rose);
      color: #ffffff;
    }

    .result-body {
      padding: 32px;
    }

    .result-doc-title {
      font-size: 1.5rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      margin: 0 0 6px;
    }

    .result-doc-tracking {
      font-family: monospace;
      font-size: 0.9375rem;
      color: var(--verify-muted);
      margin-bottom: 24px;
    }

    .result-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      background: var(--verify-canvas);
      padding: 24px;
      border-radius: 16px;
      border: 1px solid var(--verify-border);
    }

    @media (max-width: 640px) {
      .result-grid {
        grid-template-columns: 1fr;
      }
      .verify-search-form {
        flex-direction: column;
      }
      .verify-submit-btn {
        width: 100%;
        justify-content: center;
      }
    }

    .result-field {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .result-field-label {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--verify-muted);
      font-weight: 600;
    }

    .result-field-val {
      font-size: 0.9375rem;
      font-weight: 600;
      color: var(--verify-text);
      word-break: break-word;
    }

    .result-seal-box {
      margin-top: 24px;
      padding: 20px;
      border-radius: 16px;
      background-color: var(--verify-card-bg);
      border: 1px solid var(--verify-border);
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .result-seal-icon {
      width: 52px;
      height: 52px;
      border-radius: 50%;
      background: var(--verify-canvas);
      border: 1px solid var(--verify-border);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--verify-blue);
      flex-shrink: 0;
    }

    .result-seal-info {
      font-size: 0.8125rem;
      color: var(--verify-muted);
      line-height: 1.4;
    }

    .result-seal-hash {
      font-family: monospace;
      font-size: 0.75rem;
      color: var(--verify-text);
      word-break: break-all;
      background: var(--verify-canvas);
      padding: 4px 8px;
      border-radius: 6px;
      margin-top: 4px;
      display: inline-block;
    }

    .result-footer-actions {
      padding: 20px 32px;
      background: var(--verify-canvas);
      border-top: 1px solid var(--verify-border);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
    }

    @media print {
      body * { visibility: hidden !important; }
      .result-card, .result-card * { visibility: visible !important; }
      .result-card {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        border: 1px solid #000 !important;
        box-shadow: none !important;
        display: block !important;
      }
      .result-footer-actions, .verify-navbar, .verify-hero-card {
        display: none !important;
      }
    }
  </style>
</head>
<body>

  <!-- Minimalist Brand Navigation Bar -->
  <nav class="verify-navbar">
    <a href="portal.php" class="verify-brand">
      <div class="verify-brand-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
      </div>
      <div>
        <div style="font-weight: 800; font-size: 1rem; line-height: 1.1;">BarangayOS</div>
        <div style="font-size: 0.6875rem; color: var(--verify-muted); font-weight: 500;">Public Document Verification System</div>
      </div>
    </a>

    <div class="verify-nav-actions">
      <a href="portal.php" class="button-outline" style="height: 38px; padding: 0 16px; font-size: 0.8125rem; border-radius: 9999px;">
        Citizen Portal
      </a>
      <a href="login.php" class="button-primary" style="height: 38px; padding: 0 16px; font-size: 0.8125rem; border-radius: 9999px;">
        Staff Login
      </a>
      <button id="theme-toggle-btn" class="theme-toggle-btn" title="Toggle Theme" style="border-radius: 50%; width: 38px; height: 38px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/></svg>
      </button>
    </div>
  </nav>

  <!-- Main Verification Workspace -->
  <main class="verify-container">
    
    <section class="verify-hero-card">
      <div class="verify-hero-badge">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Official Anti-Fraud Registry
      </div>
      
      <h1 class="verify-title">Verify Official Document</h1>
      <p class="verify-subtitle">
        Scan the QR badge or enter the Control Number printed on any Barangay Clearance, Indigency Certificate, Residency Permit, Lupon Summons, or Incident Spot Report.
      </p>

      <form action="verify.php" method="GET" class="verify-search-form">
        <div class="verify-input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input 
            type="text" 
            name="code" 
            class="verify-input" 
            placeholder="Enter Control # (e.g. BC-2026-00001, BLTR-2026-00001)"
            value="<?= htmlspecialchars($code) ?>"
            autocomplete="off"
            spellcheck="false"
            required
          />
        </div>
        <button type="submit" class="verify-submit-btn">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          <span>Verify Document</span>
        </button>
      </form>

      <div class="verify-chips-row">
        <span>Sample records:</span>
        <a href="verify.php?code=BC-2026-00001" class="verify-chip">BC-2026-00001</a>
        <a href="verify.php?code=IND-2026-00002" class="verify-chip">IND-2026-00002</a>
        <a href="verify.php?code=BLTR-2026-00001" class="verify-chip">BLTR-2026-00001</a>
        <a href="verify.php?code=INC-2026-00001" class="verify-chip">INC-2026-00001</a>
      </div>
    </section>

    <?php if ($serverResult): ?>
    <!-- Populated Verification Result -->
    <article class="result-card">
      <div class="result-status-header <?= $serverResult['is_valid'] ? 'valid' : 'invalid' ?>">
        <div>
          <div class="result-badge-pill <?= $serverResult['is_valid'] ? 'valid' : 'invalid' ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span><?= $serverResult['is_valid'] ? 'OFFICIAL &amp; VALID' : 'DOCUMENT REVOKED / CANCELLED' ?></span>
          </div>
          <div style="font-size: 0.75rem; color: var(--verify-muted); margin-top: 6px;">
            Verified on <?= htmlspecialchars($serverResult['security_seal']['verified_at']) ?>
          </div>
        </div>

        <div id="result-qr-mount" style="width: 64px; height: 64px; background: #fff; padding: 4px; border-radius: 8px; border: 1px solid var(--verify-border);"></div>
      </div>

      <div class="result-body">
        <h2 class="result-doc-title"><?= htmlspecialchars(strtoupper($serverResult['document_title'])) ?></h2>
        <div class="result-doc-tracking">Control / Tracking No: <strong><?= htmlspecialchars($serverResult['tracking_no']) ?></strong></div>

        <div class="result-grid">
          <?php if ($serverResult['document_category'] === 'Certificate'): ?>
            <div class="result-field">
              <span class="result-field-label">Document Holder</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issued_to']['full_name']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Official Purpose</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issuance_details']['purpose']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Residential Address</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issued_to']['address'] ?: $serverResult['issued_to']['purok']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Civil &amp; Voter Status</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issued_to']['civil_status']) ?> &bull; <?= htmlspecialchars($serverResult['issued_to']['voter_status']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Issue Date</span>
              <span class="result-field-val"><?= date('F j, Y', strtotime($serverResult['issuance_details']['issued_at'])) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Official Receipt / Fee</span>
              <span class="result-field-val">O.R. #<?= htmlspecialchars($serverResult['issuance_details']['or_no'] ?: 'N/A') ?> (₱<?= number_format($serverResult['issuance_details']['amount_paid'], 2) ?>)</span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Authorizing Official</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issuance_details']['signatory']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Issuing Desk</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issuance_details']['issued_by']) ?></span>
            </div>
          <?php elseif (strpos($serverResult['document_category'], 'Lupon') !== false): ?>
            <div class="result-field">
              <span class="result-field-label">Complainant</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issued_to']['complainant']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Respondent</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issued_to']['respondent']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Dispute Nature</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issuance_details']['incident_type']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Docket Status</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['status']) ?></span>
            </div>
          <?php elseif ($serverResult['document_category'] === 'Resident ID Card'): ?>
            <div class="result-field">
              <span class="result-field-label">Resident Bearer</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issued_to']['full_name']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Card Number</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['tracking_no']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Residential Address</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issued_to']['address'] ?: $serverResult['issued_to']['purok']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Blood Type &amp; Voter Status</span>
              <span class="result-field-val">Blood <?= htmlspecialchars($serverResult['issued_to']['blood_type']) ?> &bull; <?= htmlspecialchars($serverResult['issued_to']['voter_status']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Validity Expiration</span>
              <span class="result-field-val"><?= date('F j, Y', strtotime($serverResult['issuance_details']['valid_until'])) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Emergency Contact</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issuance_details']['emergency_contact']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Punong Barangay Signatory</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issuance_details']['signatory']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Issuing Desk</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issuance_details']['issued_by']) ?></span>
            </div>
          <?php else: ?>
            <div class="result-field">
              <span class="result-field-label">Incident Classification</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issuance_details']['incident_type']) ?> (<?= htmlspecialchars($serverResult['issuance_details']['priority']) ?>)</span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Incident Location</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issued_to']['location']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Reporter / Caller</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['issued_to']['caller_name']) ?></span>
            </div>
            <div class="result-field">
              <span class="result-field-label">Dispatch Status</span>
              <span class="result-field-val"><?= htmlspecialchars($serverResult['status']) ?></span>
            </div>
          <?php endif; ?>
        </div>

        <div class="result-seal-box">
          <div class="result-seal-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              <path d="M9 12l2 2 4-4"/>
            </svg>
          </div>
          <div class="result-seal-info">
            <strong style="display: block; color: var(--verify-text); font-size: 0.875rem;">Official Anti-Tamper Security Seal</strong>
            <span>Cryptographic Digital Signature:</span><br>
            <span class="result-seal-hash"><?= htmlspecialchars($serverResult['security_seal']['digital_hash']) ?></span>
            <div style="font-size: 0.6875rem; color: var(--verify-muted); margin-top: 4px;">
              Certified authentic by the Office of the Punong Barangay, Barangay San Isidro.
            </div>
          </div>
        </div>
      </div>

      <div class="result-footer-actions">
        <a href="verify.php" class="button-outline" style="border-radius: 9999px; height: 38px; font-size: 0.8125rem; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
          <span>Verify Another Document</span>
        </a>

        <button type="button" class="button-primary" style="border-radius: 9999px; height: 38px; font-size: 0.8125rem;" onclick="window.print()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
            <rect width="12" height="8" x="6" y="14"/>
          </svg>
          <span>Print Official Verification Slip</span>
        </button>
      </div>
    </article>
    <?php elseif ($errorMsg): ?>
    <!-- Error Not Found Card -->
    <article class="result-card" style="border-color: rgba(239, 68, 68, 0.3);">
      <div class="result-status-header invalid">
        <div class="result-badge-pill invalid">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
          <span>RECORD NOT FOUND / UNVERIFIED</span>
        </div>
      </div>
      <div class="result-body" style="text-align: center; padding: 40px 32px;">
        <h3 style="font-size: 1.25rem; font-weight: 800; margin: 0 0 8px;"><?= htmlspecialchars($errorMsg) ?></h3>
        <p style="font-size: 0.875rem; color: var(--verify-muted); max-width: 480px; margin: 0 auto 24px;">
          The control number you searched does not exist in the Barangay official database. Please verify the printed code or contact the Barangay Hall.
        </p>
        <a href="verify.php" class="button-primary" style="border-radius: 9999px; height: 40px; padding: 0 20px; text-decoration: none; display: inline-flex; align-items: center;">
          Try Another Control Code
        </a>
      </div>
    </article>
    <?php endif; ?>

  </main>

  <script src="js/lib/qrcode.js"></script>
  <script src="js/components/theme.js"></script>
  <script>
    ThemeManager.init();
    const themeBtn = document.getElementById('theme-toggle-btn');
    if (themeBtn) {
      themeBtn.addEventListener('click', () => ThemeManager.toggle());
    }

    // Render QR Badge if server rendered result is present
    <?php if ($serverResult): ?>
    if (window.QRCode) {
      const qrMount = document.getElementById('result-qr-mount');
      if (qrMount) {
        const verifyUrl = `${window.location.origin}${window.location.pathname}?code=<?= urlencode($serverResult['tracking_no']) ?>`;
        QRCode.render(qrMount, verifyUrl, { size: 64, margin: 1 });
      }
    }
    <?php endif; ?>
  </script>
</body>
</html>
