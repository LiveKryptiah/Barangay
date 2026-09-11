<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_auth('login.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupong Tagapamayapa &amp; Katarungang Pambarangay Studio &bull; BarangayOS</title>
  <link rel="stylesheet" href="css/design-system.css">
  <script src="js/components/theme.js"></script>
  <style>
    .page-hero {
      padding: var(--spacing-xs) 0 var(--spacing-sm);
    }

    .stats-ladder {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: var(--spacing-sm);
      margin-bottom: var(--spacing-md);
    }

    @media (max-width: 1024px) {
      .stats-ladder {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 640px) {
      .stats-ladder {
        grid-template-columns: 1fr;
      }
    }

    /* Tab Navigation */
    .kp-tab-nav {
      display: flex;
      gap: var(--spacing-xs);
      border-bottom: 1px solid var(--color-hairline-soft);
      margin-bottom: var(--spacing-md);
      overflow-x: auto;
    }

    .kp-tab-btn {
      padding: 10px 18px;
      font-size: 0.8125rem;
      font-weight: 600;
      border: none;
      background: transparent;
      color: var(--color-text-muted);
      border-bottom: 2px solid transparent;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      white-space: nowrap;
      transition: all 0.15s ease;
    }

    .kp-tab-btn:hover {
      color: var(--color-ink);
    }

    .kp-tab-btn.active {
      color: var(--color-ink);
      border-bottom-color: var(--color-ink);
    }

    /* Studio Card */
    .studio-card {
      background-color: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: var(--spacing-md);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
      margin-bottom: var(--spacing-md);
    }

    /* Filter Toolbar */
    .filter-toolbar {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: var(--spacing-xs);
      margin-bottom: var(--spacing-sm);
      background-color: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: 6px 12px;
    }

    .filter-group {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: var(--spacing-xs);
    }

    .search-input-wrap {
      position: relative;
      min-width: 260px;
    }

    .search-input-wrap svg {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--color-text-faint);
      pointer-events: none;
    }

    .search-input-wrap input {
      height: 38px;
      padding-left: 38px;
      font-size: 0.8125rem;
      border-radius: var(--rounded-full);
    }

    .filter-select {
      height: 38px;
      padding: 0 12px;
      font-size: 0.8125rem;
      border-radius: var(--rounded-full);
      background-color: var(--color-field);
      border: 1px solid transparent;
      color: var(--color-ink);
      cursor: pointer;
    }

    /* Data Table */
    .data-table-wrap {
      overflow-x: auto;
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      background: var(--color-canvas);
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      font-size: 0.8125rem;
    }

    .data-table th {
      background-color: var(--color-canvas-soft);
      padding: 10px 14px;
      font-weight: 600;
      color: var(--color-text-muted);
      border-bottom: 1px solid var(--color-hairline-soft);
      white-space: nowrap;
    }

    .data-table td {
      padding: 12px 14px;
      border-bottom: 1px solid var(--color-hairline-soft);
      color: var(--color-ink);
      vertical-align: middle;
    }

    .data-table tr:hover td {
      background-color: var(--color-canvas-soft);
    }

    .table-action-btn {
      height: 30px;
      padding: 0 10px;
      font-size: 0.75rem;
      font-weight: 600;
      border-radius: var(--rounded-sm);
      border: 1px solid var(--color-hairline-soft);
      background: var(--color-canvas);
      color: var(--color-ink);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: background-color 0.15s;
    }

    .table-action-btn:hover {
      background: var(--color-canvas-soft);
    }

    /* Statutory 15-Day Progress Badge */
    .statutory-countdown-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 2px 8px;
      border-radius: var(--rounded-full);
      font-size: 0.6875rem;
      font-weight: 600;
      font-family: monospace;
      border: 1px solid transparent;
    }
    .countdown-urgent {
      background: rgba(239, 68, 68, 0.12);
      color: #dc2626;
      border-color: rgba(239, 68, 68, 0.3);
    }
    .countdown-warning {
      background: rgba(245, 158, 11, 0.12);
      color: #d97706;
      border-color: rgba(245, 158, 11, 0.3);
    }
    .countdown-normal {
      background: rgba(59, 130, 246, 0.1);
      color: #2563eb;
      border-color: rgba(59, 130, 246, 0.25);
    }
    .countdown-settled {
      background: rgba(16, 185, 129, 0.1);
      color: #059669;
      border-color: rgba(16, 185, 129, 0.25);
    }

    /* Roster Grid */
    .roster-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: var(--spacing-sm);
    }

    .member-card {
      background: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 10px;
      transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .member-card:hover {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .member-avatar {
      width: 42px;
      height: 42px;
      border-radius: 30%;
      background: var(--color-canvas-soft);
      border: 1px solid var(--color-hairline-soft);
      color: var(--color-ink);
      font-weight: 700;
      font-size: 0.9375rem;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    /* Official Printable Document Template */
    #printable-kp-doc {
      background: #ffffff;
      color: #111111;
      padding: 36px 44px;
      font-family: 'Times New Roman', Times, serif;
      line-height: 1.5;
      font-size: 11pt;
    }

    .kp-doc-header {
      text-align: center;
      border-bottom: 2px solid #111111;
      padding-bottom: 12px;
      margin-bottom: 20px;
    }

    .kp-parties-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 24px;
      border-bottom: 1px dashed #777777;
      padding-bottom: 16px;
    }

    .kp-doc-title {
      text-align: center;
      font-weight: bold;
      font-size: 14pt;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      margin: 20px 0;
      text-decoration: underline;
    }

    .kp-doc-body {
      text-align: justify;
      text-indent: 3em;
      margin-bottom: 30px;
    }

    .kp-signatures-row {
      display: flex;
      justify-content: space-between;
      margin-top: 40px;
      text-align: center;
    }

    @media print {
      body * {
        visibility: hidden;
      }
      #lupon-print-modal, #printable-kp-doc, #printable-kp-doc * {
        visibility: visible;
      }
      #lupon-print-modal {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        max-width: 100%;
        padding: 0;
        margin: 0;
        border: none;
        box-shadow: none;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar Container -->
  <div id="sidebar-mount"></div>

  <!-- Main Shell -->
  <main class="app-main">
    <div id="mobile-header-mount"></div>
    <div id="app-topbar-mount"></div>

    <div class="app-container" style="max-width: 1360px; padding: var(--spacing-sm) var(--spacing-md) var(--spacing-xl);">

      <!-- Hero Header -->
      <section class="page-hero">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: var(--spacing-sm);">
          <div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="badge-neutral" style="font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.05em; background: #e0e7ff; color: #4338ca;">RA 7160 SEC. 399-422</span>
              <span class="typography-caption" id="live-date-caption">Barangay San Isidro Mediation Court</span>
            </div>
            <h1 class="typography-heading-2 mt-xs" style="font-size: 1.5rem;">Katarungang Pambarangay Studio.</h1>
            <p class="typography-body mt-xs" style="color: var(--color-text-muted); max-width: 720px; font-size: 0.84rem;">
              Statutory dispute conciliation and arbitration suite. Tracks strict 15-day Punong Barangay mediation deadlines, Pangkat ng Tagapagkasundo panels, amicable settlements (KP Form 16), and Certificates to File Action (KP Form 20).
            </p>
          </div>

          <!-- Studio Actions -->
          <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-xs);">
            <button type="button" class="button-outline" id="btn-appoint-lupon" style="height: 38px; padding: 0 14px; font-size: 0.8125rem;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>
              </svg>
              + Appoint Lupon Member
            </button>
            <button type="button" class="button-outline" id="btn-schedule-summons" style="height: 38px; padding: 0 14px; font-size: 0.8125rem;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
              + Schedule Hearing (KP Form 8)
            </button>
            <button type="button" class="button-primary" id="btn-file-case" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
              </svg>
              + File New KP Case (KP Form 7)
            </button>
          </div>
        </div>
      </section>

      <!-- Executive Telemetry Ladder (4 Cards) -->
      <section class="stats-ladder">
        <div class="studio-card" style="margin-bottom: 0; padding: 14px 18px;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="typography-caption" style="text-transform: uppercase; font-weight: 600; letter-spacing: 0.04em;">Active Disputes</span>
            <div style="width: 28px; height: 28px; border-radius: 30%; background: #e0e7ff; display: flex; align-items: center; justify-content: center; color: #4338ca;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18"/><path d="m3 7 9-4 9 4"/><path d="M6 7v6a6 6 0 0 0 12 0V7"/></svg>
            </div>
          </div>
          <div class="typography-heading-3 mt-xs" style="font-size: 1.625rem; font-weight: 700;" id="stat-active-cases">0</div>
          <div class="typography-caption mt-xs" style="color: var(--color-text-muted);" id="stat-sub-active">0 in mediation / conciliation</div>
        </div>

        <div class="studio-card" style="margin-bottom: 0; padding: 14px 18px;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="typography-caption" style="text-transform: uppercase; font-weight: 600; letter-spacing: 0.04em;">Hearings This Week</span>
            <div style="width: 28px; height: 28px; border-radius: 30%; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #d97706;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
          </div>
          <div class="typography-heading-3 mt-xs" style="font-size: 1.625rem; font-weight: 700;" id="stat-hearings-week">0</div>
          <div class="typography-caption mt-xs" style="color: var(--color-text-muted);" id="stat-sub-hearings">Scheduled in Lupon Room</div>
        </div>

        <div class="studio-card" style="margin-bottom: 0; padding: 14px 18px;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="typography-caption" style="text-transform: uppercase; font-weight: 600; letter-spacing: 0.04em;">Settlement Rate</span>
            <div style="width: 28px; height: 28px; border-radius: 30%; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
          </div>
          <div class="typography-heading-3 mt-xs" style="font-size: 1.625rem; font-weight: 700;" id="stat-settlement-rate">0%</div>
          <div class="typography-caption mt-xs" style="color: var(--color-text-muted);" id="stat-sub-settlement">Amicable Kasunduan achieved</div>
        </div>

        <div class="studio-card" style="margin-bottom: 0; padding: 14px 18px;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="typography-caption" style="text-transform: uppercase; font-weight: 600; letter-spacing: 0.04em;">CFA Court Referrals</span>
            <div style="width: 28px; height: 28px; border-radius: 30%; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
            </div>
          </div>
          <div class="typography-heading-3 mt-xs" style="font-size: 1.625rem; font-weight: 700;" id="stat-cfa-count">0</div>
          <div class="typography-caption mt-xs" style="color: var(--color-text-muted);" id="stat-sub-cfa">Sec. 412 certificates issued</div>
        </div>
      </section>

      <!-- 4-Tab Navigation Strip -->
      <nav class="kp-tab-nav" aria-label="Lupon Studio Modules">
        <button type="button" class="kp-tab-btn active" data-tab="tab-docket">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="8" height="18" x="3" y="3" rx="1"/><path d="m15 3 6 6v12a1 1 0 0 1-1 1h-5"/></svg>
          Case Docket &amp; Pipeline
          <span class="badge-neutral" id="tab-docket-badge" style="font-size: 0.625rem;">0</span>
        </button>
        <button type="button" class="kp-tab-btn" data-tab="tab-hearings">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Hearing Docket &amp; Summons
          <span class="badge-neutral" id="tab-hearings-badge" style="font-size: 0.625rem;">0</span>
        </button>
        <button type="button" class="kp-tab-btn" data-tab="tab-roster">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Lupon Roster &amp; Pangkat Builder
          <span class="badge-neutral" id="tab-roster-badge" style="font-size: 0.625rem;">0</span>
        </button>
        <button type="button" class="kp-tab-btn" data-tab="tab-settlements">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
          Settlement &amp; Execution Ledger
          <span class="badge-neutral" id="tab-settlements-badge" style="font-size: 0.625rem;">0</span>
        </button>
      </nav>

      <!-- ======================================================== -->
      <!-- TAB 1: KP CASE DOCKET & PIPELINE -->
      <!-- ======================================================== -->
      <div id="tab-docket" class="tab-pane active">
        <!-- Filter Toolbar -->
        <div class="filter-toolbar">
          <div class="filter-group">
            <div class="search-input-wrap">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <input type="search" id="search-kp-cases" placeholder="Search case #, parties, facts..." aria-label="Search cases">
            </div>

            <select id="filter-stage" class="filter-select" aria-label="Filter by statutory stage">
              <option value="">All Stages</option>
              <option value="PB Mediation">PB Mediation (15-day PB Window)</option>
              <option value="Pangkat Conciliation">Pangkat Conciliation (30-day Panel)</option>
              <option value="Amicably Settled">Amicably Settled (Kasunduan)</option>
              <option value="CFA Issued">CFA Issued (Court Escalation)</option>
              <option value="Arbitrated">Arbitrated Award</option>
              <option value="Dismissed">Dismissed / Withdrawn</option>
            </select>

            <select id="filter-dispute-type" class="filter-select" aria-label="Filter by dispute type">
              <option value="">All Dispute Types</option>
              <option value="Property & Boundary Dispute">Property &amp; Boundary Dispute</option>
              <option value="Unpaid Debt & Financial Conflict">Unpaid Debt &amp; Financial Conflict</option>
              <option value="Physical Altercation / Slight Physical Injury">Physical Altercation</option>
              <option value="Verbal Defamation & Threat">Verbal Defamation &amp; Threat</option>
              <option value="Tenancy & Lease Conflict">Tenancy &amp; Lease Conflict</option>
              <option value="Neighborhood Noise & Nuisance">Noise &amp; Community Nuisance</option>
              <option value="Other Civil / Compound Dispute">Other Civil / Compound Dispute</option>
            </select>

            <button type="button" class="button-pill-soft" id="btn-clear-filters" style="height: 34px; padding: 0 12px; display: none;">
              Reset Filters
            </button>
          </div>

          <div style="font-size: 0.75rem; color: var(--color-text-muted);">
            Showing <strong id="shown-cases-count">0</strong> cases
          </div>
        </div>

        <!-- Cases Table -->
        <div class="data-table-wrap" id="cases-table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 170px;">Case Number &amp; Date</th>
                <th>Dispute Parties (Complainant vs. Respondent)</th>
                <th>Nature of Dispute</th>
                <th style="width: 160px;">Statutory Phase &amp; Timer</th>
                <th style="width: 150px;">Resolution / Deadline</th>
                <th style="width: 190px; text-align: right;">Action Triggers</th>
              </tr>
            </thead>
            <tbody id="cases-tbody">
              <!-- Rendered dynamically -->
            </tbody>
          </table>
        </div>

        <!-- Empty States -->
        <div id="cases-empty-state" class="studio-card" style="text-align: center; padding: 48px 24px; display: none;">
          <div style="width: 48px; height: 48px; border-radius: 30%; background: var(--color-canvas-soft); border: 1px solid var(--color-hairline-soft); margin: 0 auto 12px; display: flex; align-items: center; justify-content: center;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color: var(--color-text-muted);"><path d="M12 3v18"/><path d="m3 7 9-4 9 4"/><path d="M6 7v6a6 6 0 0 0 12 0V7"/></svg>
          </div>
          <h3 class="typography-heading-4">No Katarungang Pambarangay Cases Found</h3>
          <p class="typography-body mt-xs" style="color: var(--color-text-muted); font-size: 0.8125rem;">Start by filing a resident complaint or escalating a blotter entry into formal Lupon mediation.</p>
          <button type="button" class="button-primary mt-sm" onclick="document.getElementById('btn-file-case').click();">
            + File KP Complaint (KP Form 7)
          </button>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- TAB 2: HEARING CALENDAR & SUMMONS SCHEDULER -->
      <!-- ======================================================== -->
      <div id="tab-hearings" class="tab-pane" style="display: none;">
        <div class="studio-card" style="padding: 16px;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--spacing-xs); margin-bottom: 14px;">
            <div>
              <h3 class="typography-heading-4">Lupon Mediation &amp; Conciliation Hearings Docket</h3>
              <p class="typography-caption" style="color: var(--color-text-muted);">Summons, subpoenas, attendance tracker, and formal proceedings summary.</p>
            </div>
            <button type="button" class="button-primary" id="btn-schedule-hearing-tab" style="height: 36px; padding: 0 16px; font-size: 0.8125rem;">
              + Schedule Hearing / Summons (KP Form 8)
            </button>
          </div>

          <div class="data-table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width: 140px;">Date &amp; Time</th>
                  <th style="width: 130px;">Hearing Type</th>
                  <th>Case Docket &amp; Parties</th>
                  <th>Presiding Arbiter &amp; Venue</th>
                  <th style="width: 130px;">Attendance Status</th>
                  <th style="width: 170px; text-align: right;">Docket Actions</th>
                </tr>
              </thead>
              <tbody id="hearings-tbody">
                <!-- Injected via JS -->
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- TAB 3: LUPON ROSTER & PANGKAT BUILDER -->
      <!-- ======================================================== -->
      <div id="tab-roster" class="tab-pane" style="display: none;">
        <!-- Pangkat Builder Section -->
        <div class="studio-card" style="border-left: 4px solid #6366f1;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--spacing-sm); margin-bottom: 12px;">
            <div>
              <span class="badge-neutral" style="font-size: 0.6875rem; font-weight: 700; background: #e0e7ff; color: #4338ca;">RA 7160 SEC. 404</span>
              <h3 class="typography-heading-4 mt-xs">Pangkat ng Tagapagkasundo Constitution Builder</h3>
              <p class="typography-body mt-xs" style="font-size: 0.8125rem; color: var(--color-text-muted);">
                When Punong Barangay mediation fails within 15 days, choose three (3) active Lupon members to constitute the conciliation panel and generate KP Form 10 &amp; 11.
              </p>
            </div>
            <button type="button" class="button-outline" id="btn-trigger-pangkat-builder" style="height: 36px; padding: 0 16px; font-size: 0.8125rem;">
              Open 3-Member Pangkat Builder &rarr;
            </button>
          </div>
        </div>

        <!-- Lupon Members Directory -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <div>
            <h3 class="typography-heading-4">Lupong Tagapamayapa Members Roster</h3>
            <p class="typography-caption" style="color: var(--color-text-muted);">Statutory 10 to 20 community elders and arbiters appointed pursuant to RA 7160 Sec. 399.</p>
          </div>
          <button type="button" class="button-primary" id="btn-add-member-roster" style="height: 36px; padding: 0 16px; font-size: 0.8125rem;">
            + Appoint Member
          </button>
        </div>

        <div class="roster-grid" id="roster-container">
          <!-- Populated dynamically -->
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- TAB 4: SETTLEMENT & EXECUTION LEDGER -->
      <!-- ======================================================== -->
      <div id="tab-settlements" class="tab-pane" style="display: none;">
        <div class="studio-card" style="border-left: 4px solid #10b981; margin-bottom: var(--spacing-md);">
          <span class="badge-neutral" style="font-size: 0.6875rem; font-weight: 700; background: #d1fae5; color: #065f46;">RA 7160 SEC. 416 &amp; 417</span>
          <h3 class="typography-heading-4 mt-xs">Amicable Settlement &amp; Execution Legal Ledger</h3>
          <p class="typography-body mt-xs" style="font-size: 0.8125rem; color: var(--color-text-muted);">
            An amicable settlement has the force and effect of a final court judgment after 10 days. Repudiation must be executed under oath within 10 days. The Punong Barangay may enforce execution within 6 months.
          </p>
        </div>

        <div class="data-table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 140px;">Case No. &amp; Settled</th>
                <th>Dispute Parties</th>
                <th>Settlement Terms &amp; Monetary Terms</th>
                <th style="width: 170px;">10-Day Repudiation Window</th>
                <th style="width: 150px;">Voluntary Due Date</th>
                <th style="width: 180px;">6-Month PB Execution</th>
                <th style="width: 120px; text-align: right;">Print</th>
              </tr>
            </thead>
            <tbody id="settlements-tbody">
              <!-- Populated dynamically -->
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>

  <!-- ======================================================== -->
  <!-- MODAL: FILE NEW KP DISPUTE (KP FORM 7) -->
  <!-- ======================================================== -->
  <dialog id="file-case-modal" class="modal-dialog" style="max-width: 680px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-xs);">
      <div>
        <span class="badge-neutral" style="font-size: 0.625rem; font-weight: 700; background: #e0e7ff; color: #4338ca;">KP FORM 7: SUMBONG</span>
        <h3 class="typography-heading-4 mt-xs">File Katarungang Pambarangay Complaint.</h3>
      </div>
      <button type="button" class="button-pill-soft" onclick="document.getElementById('file-case-modal').close();" style="height: 28px; padding: 0 10px;">
        Cancel
      </button>
    </div>

    <form id="file-case-form">
      <input type="hidden" id="file-blotter-id">

      <div class="form-grid-2">
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="file-case-number">KP Case Number <span style="color: var(--color-primary);">*</span></label>
          <input type="text" id="file-case-number" class="text-input" style="height: 38px;" required>
        </div>
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="file-date-filed">Date Filed <span style="color: var(--color-primary);">*</span></label>
          <input type="date" id="file-date-filed" class="text-input" style="height: 38px;" required>
        </div>
      </div>

      <!-- Complainant Particulars -->
      <div style="background: var(--color-canvas-soft); padding: 10px 12px; border-radius: var(--rounded-sm); margin-bottom: var(--spacing-xs); border: 1px solid var(--color-hairline-soft);">
        <span class="typography-label" style="color: #4338ca; font-size: 0.6875rem;">NAGSUSUMBONG / COMPLAINANT PARTICULARS</span>
        <div class="form-grid-2 mt-xs">
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="file-comp-name">Full Name <span style="color: var(--color-primary);">*</span></label>
            <input type="text" id="file-comp-name" class="text-input" style="height: 36px;" placeholder="Juan Dela Cruz" required>
          </div>
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="file-comp-contact">Contact No. (for SMS Summons)</label>
            <input type="text" id="file-comp-contact" class="text-input" style="height: 36px;" placeholder="0917-000-0000">
          </div>
        </div>
        <div class="form-group mt-xs" style="margin-bottom: 0;">
          <label class="form-label" for="file-comp-address">Address / Purok</label>
          <input type="text" id="file-comp-address" class="text-input" style="height: 36px;" value="Purok 1, Barangay San Isidro, Cabuyao City">
        </div>
      </div>

      <!-- Respondent Particulars -->
      <div style="background: var(--color-canvas-soft); padding: 10px 12px; border-radius: var(--rounded-sm); margin-bottom: var(--spacing-xs); border: 1px solid var(--color-hairline-soft);">
        <span class="typography-label" style="color: #dc2626; font-size: 0.6875rem;">IPINAGSUSUMBONG / RESPONDENT PARTICULARS</span>
        <div class="form-grid-2 mt-xs">
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="file-resp-name">Full Name <span style="color: var(--color-primary);">*</span></label>
            <input type="text" id="file-resp-name" class="text-input" style="height: 36px;" placeholder="Pedro Santos" required>
          </div>
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="file-resp-contact">Contact No. (for SMS Summons)</label>
            <input type="text" id="file-resp-contact" class="text-input" style="height: 36px;" placeholder="0918-000-0000">
          </div>
        </div>
        <div class="form-group mt-xs" style="margin-bottom: 0;">
          <label class="form-label" for="file-resp-address">Address / Purok</label>
          <input type="text" id="file-resp-address" class="text-input" style="height: 36px;" value="Purok 2, Barangay San Isidro, Cabuyao City">
        </div>
      </div>

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="file-dispute-type">Dispute Classification <span style="color: var(--color-primary);">*</span></label>
        <select id="file-dispute-type" class="text-input" style="height: 38px; padding: 0 10px;" required>
          <option value="Property & Boundary Dispute">Property &amp; Boundary Dispute</option>
          <option value="Unpaid Debt & Financial Conflict">Unpaid Debt &amp; Financial Conflict (Pautang)</option>
          <option value="Physical Altercation / Slight Physical Injury">Physical Altercation / Slight Injury</option>
          <option value="Verbal Defamation & Threat">Verbal Defamation &amp; Threat (Pananakot)</option>
          <option value="Tenancy & Lease Conflict">Tenancy &amp; Lease Conflict</option>
          <option value="Neighborhood Noise & Nuisance">Neighborhood Noise &amp; Nuisance</option>
          <option value="Breach of Contract / Agreement">Breach of Contract / Agreement</option>
          <option value="Other Civil / Compound Dispute">Other Civil / Compound Dispute</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="file-details">Narrative of Complaint &amp; Specific Grievance <span style="color: var(--color-primary);">*</span></label>
        <textarea id="file-details" class="text-input" style="height: 70px; padding: 8px 12px; resize: vertical;" placeholder="Ipaliwanag nang malinaw ang buod ng sumbong, petsa ng pangyayari at dahilan ng alitan..." required></textarea>
      </div>

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="file-relief">Relief Sought / Lunas na Hinihiling (KP Form 7 requirement)</label>
        <input type="text" id="file-relief" class="text-input" style="height: 38px;" placeholder="e.g. Pagbabayad ng utang na PHP 15,000, pagpapatigil sa paninira, atbp.">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); margin-top: var(--spacing-sm); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <button type="button" class="button-outline" onclick="document.getElementById('file-case-modal').close();" style="height: 38px; padding: 0 16px;">
          Cancel
        </button>
        <button type="submit" class="button-primary" style="height: 38px; padding: 0 20px;">
          File Case &amp; Start 15-Day Mediation
        </button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================== -->
  <!-- MODAL: CASE DOSSIER BRIEFING -->
  <!-- ======================================================== -->
  <dialog id="case-dossier-modal" class="modal-dialog" style="max-width: 720px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-xs);">
      <div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <span class="badge-neutral" id="dossier-case-number" style="font-weight: 700; font-family: monospace;">KP-2026-0001</span>
          <span id="dossier-stage-badge" class="badge-blue">PB Mediation</span>
        </div>
        <h3 class="typography-heading-4 mt-xs" id="dossier-dispute-title">Property &amp; Boundary Dispute</h3>
      </div>
      <button type="button" class="button-pill-soft" onclick="document.getElementById('case-dossier-modal').close();" style="height: 28px; padding: 0 10px;">
        Close
      </button>
    </div>

    <!-- Parties Card -->
    <div class="form-grid-2 mb-xs">
      <div style="background: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-sm); border-left: 3px solid #4338ca;">
        <span class="typography-label" style="color: #4338ca; font-size: 0.6875rem;">NAGSUSUMBONG (COMPLAINANT)</span>
        <div style="font-weight: 700; font-size: 0.9375rem;" id="dossier-comp-name">Juan Dela Cruz</div>
        <div class="typography-caption" id="dossier-comp-address">Purok 1, Barangay San Isidro &bull; 0917-123-4567</div>
      </div>
      <div style="background: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-sm); border-left: 3px solid #dc2626;">
        <span class="typography-label" style="color: #dc2626; font-size: 0.6875rem;">IPINAGSUSUMBONG (RESPONDENT)</span>
        <div style="font-weight: 700; font-size: 0.9375rem;" id="dossier-resp-name">Pedro Santos</div>
        <div class="typography-caption" id="dossier-resp-address">Purok 2, Barangay San Isidro &bull; 0918-765-4321</div>
      </div>
    </div>

    <!-- Statutory Timeline Progress Banner -->
    <div id="dossier-timer-banner" style="padding: 10px 14px; background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.25); border-radius: var(--rounded-sm); margin-bottom: var(--spacing-xs); display: flex; justify-content: space-between; align-items: center;">
      <div>
        <div style="font-weight: 600; font-size: 0.8125rem; color: #4338ca;" id="dossier-timer-label">Statutory Punong Barangay 15-Day Mediation Timer</div>
        <div class="typography-caption" id="dossier-timer-sub">Filed on Jan 15, 2026 &bull; Mediation Deadline: Jan 30, 2026</div>
      </div>
      <span class="statutory-countdown-badge countdown-urgent" id="dossier-countdown-chip">Day 6 of 15</span>
    </div>

    <!-- Facts and Relief -->
    <div style="padding: 12px; background: var(--color-canvas-soft); border-radius: var(--rounded-sm); margin-bottom: var(--spacing-xs); border: 1px solid var(--color-hairline-soft);">
      <span class="typography-label" style="font-size: 0.6875rem; color: var(--color-text-muted);">COMPLAINT STATEMENT / SALAYSAY NG SUMBONG:</span>
      <p style="font-size: 0.8125rem; color: var(--color-ink); margin-top: 4px; line-height: 1.5; white-space: pre-wrap;" id="dossier-facts">-</p>
      <div style="margin-top: 8px; font-size: 0.75rem; color: var(--color-text-muted);">
        Lunas na Hinihiling: <strong style="color: var(--color-ink);" id="dossier-relief">-</strong>
      </div>
    </div>

    <!-- Pangkat Panel if constituted -->
    <div id="dossier-pangkat-box" style="display: none; padding: 10px 12px; background: #faf5ff; border: 1px solid #d8b4fe; border-radius: var(--rounded-sm); margin-bottom: var(--spacing-xs);">
      <span class="typography-label" style="color: #7e22ce; font-size: 0.6875rem;">CONSTITUTED PANGKAT NG TAGAPAGKASUNDO PANEL:</span>
      <div class="form-grid-3 mt-xs" style="font-size: 0.8125rem;">
        <div><strong>Chairman:</strong> <span id="dossier-pangkat-chair">-</span></div>
        <div><strong>Secretary:</strong> <span id="dossier-pangkat-sec">-</span></div>
        <div><strong>Member:</strong> <span id="dossier-pangkat-member">-</span></div>
      </div>
    </div>

    <!-- Settlement terms if settled -->
    <div id="dossier-settlement-box" style="display: none; padding: 10px 12px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: var(--rounded-sm); margin-bottom: var(--spacing-xs);">
      <span class="typography-label" style="color: #065f46; font-size: 0.6875rem;">EXECUTED AMICABLE SETTLEMENT (KASUNDUANG PAG-AAYOS):</span>
      <div style="font-size: 0.8125rem; margin-top: 4px; color: #064e3b;" id="dossier-settlement-terms">-</div>
      <div class="typography-caption mt-xs" id="dossier-settlement-meta">Amount: PHP 0.00 &bull; Compliance Due: -</div>
    </div>

    <!-- CFA Details if issued -->
    <div id="dossier-cfa-box" style="display: none; padding: 10px 12px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: var(--rounded-sm); margin-bottom: var(--spacing-xs);">
      <span class="typography-label" style="color: #9f1239; font-size: 0.6875rem;">CERTIFICATE TO FILE ACTION (CFA) ENDORSEMENT:</span>
      <div style="font-size: 0.8125rem; margin-top: 4px; color: #881337;" id="dossier-cfa-reason">-</div>
      <div class="typography-caption mt-xs" id="dossier-cfa-meta">Certified for court action pursuant to RA 7160 Sec. 412</div>
    </div>

    <!-- Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--spacing-xs); margin-top: var(--spacing-sm); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
      <div style="display: flex; gap: 6px;">
        <button type="button" class="button-outline" id="dossier-btn-print" style="height: 36px; padding: 0 14px; font-size: 0.75rem;">
          Print Official Forms (KP 7-20) &rarr;
        </button>
      </div>
      <div style="display: flex; gap: 6px;">
        <button type="button" class="button-outline" id="dossier-btn-pangkat" style="height: 36px; padding: 0 12px; font-size: 0.75rem;">
          Constitute Pangkat
        </button>
        <button type="button" class="button-outline" id="dossier-btn-settle" style="height: 36px; padding: 0 12px; font-size: 0.75rem; color: #059669; border-color: #a7f3d0;">
          Execute Kasunduan
        </button>
        <button type="button" class="button-outline" id="dossier-btn-cfa" style="height: 36px; padding: 0 12px; font-size: 0.75rem; color: #dc2626; border-color: #fecdd3;">
          Issue CFA
        </button>
      </div>
    </div>
  </dialog>

  <!-- ======================================================== -->
  <!-- MODAL: SCHEDULE SUMMONS / HEARING (KP FORM 8) -->
  <!-- ======================================================== -->
  <dialog id="schedule-hearing-modal" class="modal-dialog" style="max-width: 580px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-xs);">
      <div>
        <span class="badge-neutral" style="font-size: 0.625rem; font-weight: 700; background: #fef3c7; color: #92400e;">KP FORM 8: PATAWAG</span>
        <h3 class="typography-heading-4 mt-xs">Schedule Summons Hearing.</h3>
      </div>
      <button type="button" class="button-pill-soft" onclick="document.getElementById('schedule-hearing-modal').close();" style="height: 28px; padding: 0 10px;">
        Cancel
      </button>
    </div>

    <form id="schedule-hearing-form">
      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="hearing-case-select">Select KP Dispute Case <span style="color: var(--color-primary);">*</span></label>
        <select id="hearing-case-select" class="text-input" style="height: 38px; padding: 0 10px;" required>
          <!-- Injected via JS -->
        </select>
      </div>

      <div class="form-grid-2">
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="hearing-number">Hearing Sequence <span style="color: var(--color-primary);">*</span></label>
          <select id="hearing-number" class="text-input" style="height: 38px; padding: 0 10px;" required>
            <option value="1st Hearing">1st Hearing (Unang Paghaharap)</option>
            <option value="2nd Hearing">2nd Hearing (Ikalawang Paghaharap)</option>
            <option value="3rd Hearing">3rd Hearing (Ikatlong Paghaharap)</option>
            <option value="Special Hearing">Special Hearing</option>
          </select>
        </div>
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="hearing-type">Hearing Jurisdiction Phase <span style="color: var(--color-primary);">*</span></label>
          <select id="hearing-type" class="text-input" style="height: 38px; padding: 0 10px;" required>
            <option value="PB Mediation Hearing">PB Mediation Hearing (Punong Barangay)</option>
            <option value="Pangkat Conciliation Hearing">Pangkat Conciliation Hearing</option>
            <option value="Arbitration Proceeding">Arbitration Proceeding (Sec. 413)</option>
          </select>
        </div>
      </div>

      <div class="form-grid-2">
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="hearing-date">Scheduled Date <span style="color: var(--color-primary);">*</span></label>
          <input type="date" id="hearing-date" class="text-input" style="height: 38px;" required>
        </div>
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="hearing-time">Scheduled Time <span style="color: var(--color-primary);">*</span></label>
          <input type="time" id="hearing-time" class="text-input" style="height: 38px;" value="14:00" required>
        </div>
      </div>

      <div class="form-grid-2">
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="hearing-venue">Hearing Venue</label>
          <input type="text" id="hearing-venue" class="text-input" style="height: 38px;" value="Barangay Hall Mediation Room">
        </div>
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="hearing-presiding">Presiding Officer</label>
          <input type="text" id="hearing-presiding" class="text-input" style="height: 38px;" value="Hon. Antonio S. Valdez">
        </div>
      </div>

      <!-- SMS Summons Dispatch Toggle -->
      <div style="margin-top: 6px; padding: 10px 14px; background: var(--color-canvas-soft); border-radius: var(--rounded-sm); display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--color-hairline-soft);">
        <div>
          <div style="font-weight: 600; font-size: 0.8125rem;">Dispatch SMS Summons to Parties</div>
          <div class="typography-caption">Sends official KP Form 8 summons notice via SMS gateway.</div>
        </div>
        <label style="position: relative; display: inline-block; width: 36px; height: 20px; flex-shrink: 0;">
          <input type="checkbox" id="hearing-sms-toggle" checked style="opacity: 0; width: 0; height: 0;">
          <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #141414; border-radius: 20px; transition: .2s;"></span>
        </label>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); margin-top: var(--spacing-sm); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <button type="button" class="button-outline" onclick="document.getElementById('schedule-hearing-modal').close();" style="height: 38px; padding: 0 16px;">
          Cancel
        </button>
        <button type="submit" class="button-primary" style="height: 38px; padding: 0 20px;">
          Issue Summons &amp; Schedule
        </button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================== -->
  <!-- MODAL: CONSTITUTE PANGKAT NG TAGAPAGKASUNDO (KP FORM 10) -->
  <!-- ======================================================== -->
  <dialog id="pangkat-modal" class="modal-dialog" style="max-width: 600px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-xs);">
      <div>
        <span class="badge-neutral" style="font-size: 0.625rem; font-weight: 700; background: #e0e7ff; color: #4338ca;">KP FORM 10: PAGBUO NG PANGKAT</span>
        <h3 class="typography-heading-4 mt-xs">Constitute Pangkat ng Tagapagkasundo.</h3>
      </div>
      <button type="button" class="button-pill-soft" onclick="document.getElementById('pangkat-modal').close();" style="height: 28px; padding: 0 10px;">
        Cancel
      </button>
    </div>

    <form id="pangkat-form">
      <input type="hidden" id="pangkat-case-id">

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="pangkat-case-select">KP Dispute Case <span style="color: var(--color-primary);">*</span></label>
        <select id="pangkat-case-select" class="text-input" style="height: 38px; padding: 0 10px;" required>
          <!-- Populated dynamically -->
        </select>
      </div>

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="pangkat-chair-select">Pangkat Chairman (Tagapangulo) <span style="color: var(--color-primary);">*</span></label>
        <select id="pangkat-chair-select" class="text-input" style="height: 38px; padding: 0 10px;" required>
          <!-- Populated from active Lupon members -->
        </select>
      </div>

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="pangkat-sec-select">Pangkat Secretary (Kalihim) <span style="color: var(--color-primary);">*</span></label>
        <select id="pangkat-sec-select" class="text-input" style="height: 38px; padding: 0 10px;" required>
          <!-- Populated from active Lupon members -->
        </select>
      </div>

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="pangkat-mem-select">Pangkat Third Member (Kasapi) <span style="color: var(--color-primary);">*</span></label>
        <select id="pangkat-mem-select" class="text-input" style="height: 38px; padding: 0 10px;" required>
          <!-- Populated from active Lupon members -->
        </select>
      </div>

      <div style="padding: 10px 14px; background: rgba(99, 102, 241, 0.08); border-radius: var(--rounded-sm); margin-top: 10px; font-size: 0.75rem; color: #4338ca;">
        <strong>Statutory Notice (Sec. 410c):</strong> Upon constitution, the 15-day Pangkat conciliation period initiates. The panel shall convene within 3 days to hear both parties.
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); margin-top: var(--spacing-sm); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <button type="button" class="button-outline" onclick="document.getElementById('pangkat-modal').close();" style="height: 38px; padding: 0 16px;">
          Cancel
        </button>
        <button type="submit" class="button-primary" style="height: 38px; padding: 0 20px;">
          Constitute Panel &amp; Start Pangkat Phase
        </button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================== -->
  <!-- MODAL: EXECUTE AMICABLE SETTLEMENT (KP FORM 16) -->
  <!-- ======================================================== -->
  <dialog id="settlement-modal" class="modal-dialog" style="max-width: 620px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-xs);">
      <div>
        <span class="badge-neutral" style="font-size: 0.625rem; font-weight: 700; background: #d1fae5; color: #065f46;">KP FORM 16: KASUNDUANG PAG-AAYOS</span>
        <h3 class="typography-heading-4 mt-xs">Execute Amicable Settlement.</h3>
      </div>
      <button type="button" class="button-pill-soft" onclick="document.getElementById('settlement-modal').close();" style="height: 28px; padding: 0 10px;">
        Cancel
      </button>
    </div>

    <form id="settlement-form">
      <input type="hidden" id="settle-case-id">

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="settle-case-select">KP Dispute Case <span style="color: var(--color-primary);">*</span></label>
        <select id="settle-case-select" class="text-input" style="height: 38px; padding: 0 10px;" required>
          <!-- Dynamic -->
        </select>
      </div>

      <div class="form-grid-2">
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="settle-date">Settlement Date <span style="color: var(--color-primary);">*</span></label>
          <input type="date" id="settle-date" class="text-input" style="height: 38px;" required>
        </div>
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="settle-amount">Settlement Amount / Financial Obligation (PHP)</label>
          <input type="number" id="settle-amount" class="text-input" style="height: 38px;" placeholder="0.00" step="0.01">
        </div>
      </div>

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="settle-compliance-date">Voluntary Compliance Due Date</label>
        <input type="date" id="settle-compliance-date" class="text-input" style="height: 38px;">
      </div>

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="settle-terms">Agreed Terms &amp; Conditions (Kasunduan) <span style="color: var(--color-primary);">*</span></label>
        <textarea id="settle-terms" class="text-input" style="height: 90px; padding: 8px 12px; resize: vertical;" placeholder="Ilahad ang tiyak na kasunduan ng mga panig, mga paraan ng pagbabayad o pagtupad, at pangakong pananatilihin ang kapayapaan..." required></textarea>
      </div>

      <div style="padding: 10px 14px; background: rgba(16, 185, 129, 0.08); border-radius: var(--rounded-sm); font-size: 0.75rem; color: #065f46;">
        <strong>Legal Effect (Sec. 416 &amp; 417):</strong> This amicable settlement acquires the force of a final court judgment upon the lapse of 10 days from today. Execution may be enforced by PB within 6 months.
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); margin-top: var(--spacing-sm); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <button type="button" class="button-outline" onclick="document.getElementById('settlement-modal').close();" style="height: 38px; padding: 0 16px;">
          Cancel
        </button>
        <button type="submit" class="button-primary" style="height: 38px; padding: 0 20px;">
          Execute Kasunduan &amp; Close Dispute
        </button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================== -->
  <!-- MODAL: ISSUE CERTIFICATE TO FILE ACTION (KP FORM 20) -->
  <!-- ======================================================== -->
  <dialog id="cfa-modal" class="modal-dialog" style="max-width: 580px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-xs);">
      <div>
        <span class="badge-neutral" style="font-size: 0.625rem; font-weight: 700; background: #fee2e2; color: #dc2626;">KP FORM 20: CFA</span>
        <h3 class="typography-heading-4 mt-xs">Issue Certificate to File Action.</h3>
      </div>
      <button type="button" class="button-pill-soft" onclick="document.getElementById('cfa-modal').close();" style="height: 28px; padding: 0 10px;">
        Cancel
      </button>
    </div>

    <form id="cfa-form">
      <input type="hidden" id="cfa-case-id">

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="cfa-case-select">KP Dispute Case <span style="color: var(--color-primary);">*</span></label>
        <select id="cfa-case-select" class="text-input" style="height: 38px; padding: 0 10px;" required>
          <!-- Dynamic -->
        </select>
      </div>

      <div class="form-grid-2">
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="cfa-date">Date of Issuance <span style="color: var(--color-primary);">*</span></label>
          <input type="date" id="cfa-date" class="text-input" style="height: 38px;" required>
        </div>
      </div>

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="cfa-reason">Statutory Legal Ground for Referral (Sec. 412) <span style="color: var(--color-primary);">*</span></label>
        <select id="cfa-reason" class="text-input" style="height: 38px; padding: 0 10px;" required>
          <option value="Personal confrontation occurred before Punong Barangay / Pangkat but no amicable settlement was reached.">Personal confrontation occurred but no settlement reached</option>
          <option value="Respondent willfully and deliberately failed or refused to appear without justifiable cause despite due summons.">Respondent willfully failed to appear despite summons</option>
          <option value="Amicable settlement was formally repudiated by party under oath within the 10-day statutory window.">Amicable settlement was repudiated under oath within 10 days</option>
          <option value="Parties failed to agree on constitution of Pangkat ng Tagapagkasundo.">Parties failed to agree on Pangkat constitution</option>
        </select>
      </div>

      <div style="padding: 10px 14px; background: rgba(239, 68, 68, 0.08); border-radius: var(--rounded-sm); font-size: 0.75rem; color: #dc2626;">
        <strong>Judicial Threshold Certified:</strong> This certificate legally certifies to the Municipal Trial Court or Office of the City Prosecutor that the mandatory Katarungang Pambarangay conciliation condition precedent has been complied with.
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); margin-top: var(--spacing-sm); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <button type="button" class="button-outline" onclick="document.getElementById('cfa-modal').close();" style="height: 38px; padding: 0 16px;">
          Cancel
        </button>
        <button type="submit" class="button-primary" style="height: 38px; padding: 0 20px; background-color: #dc2626; border-color: #dc2626;">
          Issue CFA for Court Action
        </button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================== -->
  <!-- MODAL: APPOINT / EDIT LUPON MEMBER -->
  <!-- ======================================================== -->
  <dialog id="member-modal" class="modal-dialog" style="max-width: 520px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-xs);">
      <div>
        <span class="badge-neutral" style="font-size: 0.625rem; font-weight: 700;">RA 7160 SEC. 399</span>
        <h3 class="typography-heading-4 mt-xs" id="member-modal-title">Appoint Lupon Member.</h3>
      </div>
      <button type="button" class="button-pill-soft" onclick="document.getElementById('member-modal').close();" style="height: 28px; padding: 0 10px;">
        Cancel
      </button>
    </div>

    <form id="member-form">
      <input type="hidden" id="member-id">

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="member-name">Full Name <span style="color: var(--color-primary);">*</span></label>
        <input type="text" id="member-name" class="text-input" style="height: 38px;" placeholder="G. Juanito Dela Cruz" required>
      </div>

      <div class="form-grid-2">
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="member-committee">Committee Assignment</label>
          <select id="member-committee" class="text-input" style="height: 38px; padding: 0 10px;">
            <option value="Conciliation Panel">Conciliation Panel</option>
            <option value="Property & Boundary Panel">Property &amp; Boundary Panel</option>
            <option value="Family & Tenancy Panel">Family &amp; Tenancy Panel</option>
            <option value="Arbitration Panel">Arbitration Panel</option>
          </select>
        </div>
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="member-profession">Background / Profession</label>
          <input type="text" id="member-profession" class="text-input" style="height: 38px;" placeholder="Retired Educator / Elder">
        </div>
      </div>

      <div class="form-grid-2">
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="member-appointment-date">Appointment Date <span style="color: var(--color-primary);">*</span></label>
          <input type="date" id="member-appointment-date" class="text-input" style="height: 38px;" required>
        </div>
        <div class="form-group" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="member-contact">Contact No.</label>
          <input type="text" id="member-contact" class="text-input" style="height: 38px;" placeholder="0917-000-0000">
        </div>
      </div>

      <div class="form-group" style="margin-bottom: var(--spacing-xs);">
        <label class="form-label" for="member-status">Membership Status</label>
        <select id="member-status" class="text-input" style="height: 38px; padding: 0 10px;">
          <option value="Active">Active</option>
          <option value="On Leave">On Leave</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); margin-top: var(--spacing-sm); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <button type="button" class="button-outline" onclick="document.getElementById('member-modal').close();" style="height: 38px; padding: 0 16px;">
          Cancel
        </button>
        <button type="submit" class="button-primary" style="height: 38px; padding: 0 20px;">
          Save Member Appointment
        </button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================== -->
  <!-- MODAL: OFFICIAL STATUTORY PRINT SUITE (KP FORMS 7 - 20) -->
  <!-- ======================================================== -->
  <dialog id="lupon-print-modal" class="modal-dialog" style="max-width: 860px; width: 95%; max-height: 90vh; overflow-y: auto;">
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-xs);">
      <div style="display: flex; align-items: center; gap: 8px;">
        <span class="badge-neutral" id="print-modal-badge" style="font-weight: 700; font-family: monospace;">KP-2026-0001</span>
        <span class="typography-caption">Official Supreme Court &amp; DILG Katarungang Pambarangay Form</span>
      </div>
      <div style="display: flex; gap: 8px; align-items: center;">
        <select id="print-form-selector" class="filter-select" style="height: 36px; padding: 0 10px;">
          <option value="kp7">KP Form 7: Sumbong (Complaint)</option>
          <option value="kp8">KP Form 8: Patawag (Summons)</option>
          <option value="kp9">KP Form 9: Subpoena sa Saksi (Witness Subpoena)</option>
          <option value="kp10">KP Form 10: Pagbuo ng Pangkat (Notice of Pangkat)</option>
          <option value="kp14">KP Form 14: Kasunduan sa Pag-aarbitro (Arbitration)</option>
          <option value="kp16">KP Form 16: Kasunduang Pag-aayos (Amicable Settlement)</option>
          <option value="kp20">KP Form 20: Katunayan Upang Makadulog sa Hukuman (CFA)</option>
        </select>
        <button type="button" class="button-outline" onclick="document.getElementById('lupon-print-modal').close();" style="height: 36px; padding: 0 14px; font-size: 0.8125rem;">
          Close
        </button>
        <button type="button" class="button-primary" onclick="window.print();" style="height: 36px; padding: 0 18px; font-size: 0.8125rem;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 4px;">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
            <rect width="12" height="8" x="6" y="14"/>
          </svg>
          Print Form (Ctrl+P)
        </button>
      </div>
    </div>

    <!-- Official Paper Document -->
    <div id="printable-kp-doc">
      <div class="kp-doc-header">
        <div style="font-size: 9pt; text-transform: uppercase; letter-spacing: 0.08em; color: #444;">Republic of the Philippines &bull; Republika ng Pilipinas</div>
        <div style="font-size: 10pt; font-weight: bold; text-transform: uppercase;">Province of Laguna &bull; Lalawigan ng Laguna</div>
        <div style="font-size: 10pt; font-weight: bold; text-transform: uppercase;">City of Cabuyao &bull; Lungsod ng Cabuyao</div>
        <div style="font-size: 14pt; font-weight: 800; letter-spacing: 0.04em; margin: 4px 0;">BARANGAY SAN ISIDRO</div>
        <div style="font-size: 10pt; font-weight: bold; text-transform: uppercase; color: #1e3a8a; letter-spacing: 0.06em;">TANGGAPAN NG LUPONG TAGAPAMAYAPA</div>
      </div>

      <div class="kp-parties-grid">
        <div>
          <div style="font-weight: bold; font-size: 11pt;" id="print-comp-name">JUAN DELA CRUZ</div>
          <div style="font-size: 8.5pt; font-style: italic; color: #555;">May-sumbong / Complainant</div>
          <div style="font-size: 8.5pt;" id="print-comp-addr">Purok 1, Barangay San Isidro</div>
          <div style="margin: 10px 0; font-weight: bold; font-style: italic; font-size: 9pt;">— laban kay / vs. —</div>
          <div style="font-weight: bold; font-size: 11pt;" id="print-resp-name">PEDRO SANTOS</div>
          <div style="font-size: 8.5pt; font-style: italic; color: #555;">Ipinagsusumbong / Respondent</div>
          <div style="font-size: 8.5pt;" id="print-resp-addr">Purok 2, Barangay San Isidro</div>
        </div>
        <div style="text-align: right;">
          <div>Usaping Barangay Blg.: <strong id="print-case-num">KP-2026-0001</strong></div>
          <div style="margin-top: 4px;">Ukol sa / For: <strong id="print-dispute-type">Property &amp; Boundary Dispute</strong></div>
          <div style="margin-top: 4px; font-size: 8.5pt; color: #555;">Petsa / Date: <span id="print-date-filed">Jan 15, 2026</span></div>
        </div>
      </div>

      <div class="kp-doc-title" id="print-form-title">
        SUMBONG (COMPLAINT)
      </div>

      <div class="kp-doc-body" id="print-form-body">
        <!-- Dynamically injected based on selected form -->
      </div>

      <div class="kp-signatures-row" id="print-signatures-row">
        <!-- Dynamically rendered signatories -->
      </div>

      <!-- Live Scannable Security Footer -->
      <div style="margin-top: 36px; border-top: 1px dashed #999; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; font-size: 8pt; font-family: monospace; color: #555;">
        <div style="display: flex; align-items: center; gap: 12px;">
          <div id="print-kp-qr" style="width: 58px; height: 58px; background: #fff; border: 1px solid #ccc; padding: 2px; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"></div>
          <div style="font-size: 7.5pt; line-height: 1.3; font-family: sans-serif; color: #444;">
            <strong style="display: block; color: #111; font-size: 8.5pt; text-transform: uppercase;">Official KP Case Verification</strong>
            Scan to inspect certified resolution status &bull; <span>verify.php</span>
          </div>
        </div>
        <div style="text-align: right; font-size: 8pt; font-family: sans-serif; color: #666;">
          <div style="font-weight: bold; color: #111;">TANGGAPAN NG LUPONG TAGAPAMAYAPA</div>
          <div>Barangay San Isidro, Cabuyao City &bull; Katarungang Pambarangay</div>
        </div>
      </div>
    </div>
  </dialog>

  <!-- REST API Client Bridge for PHP/MySQL Parity -->
  <script src="js/api.js"></script>
  <script src="js/lib/qrcode.js"></script>
  <script src="js/auth.js"></script>
  <script src="js/components/toast.js"></script>
  <script src="js/components/sidebar.js"></script>

  <script>
    let allCases = [];
    let allMembers = [];
    let allHearings = [];
    let currentAuthUser = null;
    let selectedCaseForAction = null;

    document.addEventListener('DOMContentLoaded', async () => {
      // 1. App Shell Sidebar
      if (window.AppSidebar) {
        await AppSidebar.render('lupon');
      }

      // 2. Set Live Date caption
      const now = new Date();
      document.getElementById('live-date-caption').textContent = now.toLocaleDateString('en-PH', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
      });

      // 3. Load All Data via MySQL REST API
      await refreshAllData();

      // 4. Bind Events
      bindEventListeners();

      // 5. Check Blotter Escalation payload in sessionStorage
      checkBlotterEscalation();
    });

    // Refresh all data stores & telemetry via API
    async function refreshAllData() {
      try {
        allCases = await window.barangayDB.getAll('lupon_cases');
        allMembers = await window.barangayDB.getAll('lupon_members');
        allHearings = await window.barangayDB.getAll('lupon_hearings');

        updateTelemetry();
        renderDocketTable();
        renderHearingsDocket();
        renderLuponRoster();
        renderSettlementLedger();
        populateDropdowns();
      } catch (err) {
        console.error('Failed to load Lupon data:', err);
        Toast.error('Could not load Katarungang Pambarangay records.');
      }
    }

    // Calculate real-time stats
    function updateTelemetry() {
      const active = allCases.filter(c => c.stage === 'PB Mediation' || c.stage === 'Pangkat Conciliation').length;
      const settled = allCases.filter(c => c.stage === 'Amicably Settled').length;
      const cfa = allCases.filter(c => c.stage === 'CFA Issued').length;
      const rate = allCases.length > 0 ? Math.round((settled / allCases.length) * 100) : 0;

      // Hearings this week calculation
      const now = new Date();
      const currentDay = now.getDay();
      const diffToMonday = now.getDate() - currentDay + (currentDay === 0 ? -6 : 1);
      const monday = new Date(now.setDate(diffToMonday));
      monday.setHours(0, 0, 0, 0);
      const sunday = new Date(monday);
      sunday.setDate(sunday.getDate() + 6);
      sunday.setHours(23, 59, 59, 999);

      const hearingsThisWeek = allHearings.filter(h => {
        const d = new Date(h.scheduledDate || h.scheduled_date);
        return d >= monday && d <= sunday;
      }).length;

      document.getElementById('stat-active-cases').textContent = active;
      document.getElementById('stat-sub-active').textContent = `${active} active in PB/Pangkat`;

      document.getElementById('stat-hearings-week').textContent = hearingsThisWeek;
      document.getElementById('stat-sub-hearings').textContent = `${hearingsThisWeek} scheduled this week`;

      document.getElementById('stat-settlement-rate').textContent = `${rate}%`;
      document.getElementById('stat-sub-settlement').textContent = `${settled} resolved amicably`;

      document.getElementById('stat-cfa-count').textContent = cfa;
      document.getElementById('stat-sub-cfa').textContent = `${cfa} certificates issued`;

      document.getElementById('tab-docket-badge').textContent = allCases.length;
      document.getElementById('tab-hearings-badge').textContent = allHearings.length;
      document.getElementById('tab-roster-badge').textContent = allMembers.filter(m => m.status === 'Active').length;
      document.getElementById('tab-settlements-badge').textContent = settled;
    }

    // Calculate 15-day countdown badge for statutory phases
    function getStatutoryCountdownBadge(c) {
      if (c.stage === 'Amicably Settled') {
        return '<span class="statutory-countdown-badge countdown-settled">&#10003; Amicably Settled</span>';
      }
      if (c.stage === 'CFA Issued') {
        return '<span class="statutory-countdown-badge countdown-urgent">&#9888; CFA Issued</span>';
      }

      const filed = new Date(c.dateFiled || c.date_filed);
      const today = new Date();
      const diffTime = today - filed;
      const diffDays = Math.max(1, Math.floor(diffTime / (1000 * 60 * 60 * 24)));

      if (c.stage === 'PB Mediation') {
        if (diffDays > 15) {
          return `<span class="statutory-countdown-badge countdown-urgent">Day ${diffDays} of 15 &bull; PB Lapsed</span>`;
        } else if (diffDays >= 11) {
          return `<span class="statutory-countdown-badge countdown-warning">Day ${diffDays} of 15 &bull; PB Mediation</span>`;
        }
        return `<span class="statutory-countdown-badge countdown-normal">Day ${diffDays} of 15 &bull; PB Mediation</span>`;
      }

      if (c.stage === 'Pangkat Conciliation') {
        if (diffDays > 30) {
          return `<span class="statutory-countdown-badge countdown-urgent">Day ${diffDays} of 30 &bull; Panel Lapsed</span>`;
        }
        return `<span class="statutory-countdown-badge countdown-normal">Day ${diffDays} of 30 &bull; Pangkat</span>`;
      }

      return `<span class="statutory-countdown-badge countdown-normal">${c.stage}</span>`;
    }

    // Render TAB 1 Cases Docket Table
    function renderDocketTable() {
      const tbody = document.getElementById('cases-tbody');
      const search = (document.getElementById('search-kp-cases').value || '').toLowerCase().trim();
      const stageFilter = document.getElementById('filter-stage').value;
      const typeFilter = document.getElementById('filter-dispute-type').value;

      const hasFilters = search || stageFilter || typeFilter;
      document.getElementById('btn-clear-filters').style.display = hasFilters ? 'inline-flex' : 'none';

      const filtered = allCases.filter(c => {
        const stage = c.stage;
        const disputeType = c.disputeType || c.dispute_type;
        if (stageFilter && stage !== stageFilter) return false;
        if (typeFilter && disputeType !== typeFilter) return false;
        if (search) {
          const num = (c.caseNumber || c.case_number || '').toLowerCase();
          const comp = (c.complainantName || c.complainant_name || '').toLowerCase();
          const resp = (c.respondentName || c.respondent_name || '').toLowerCase();
          const details = (c.complaintDetails || c.complaint_details || '').toLowerCase();
          if (!num.includes(search) && !comp.includes(search) && !resp.includes(search) && !details.includes(search)) {
            return false;
          }
        }
        return true;
      });

      document.getElementById('shown-cases-count').textContent = filtered.length;

      if (filtered.length === 0) {
        document.getElementById('cases-table-wrap').style.display = 'none';
        document.getElementById('cases-empty-state').style.display = 'block';
        return;
      }

      document.getElementById('cases-table-wrap').style.display = 'block';
      document.getElementById('cases-empty-state').style.display = 'none';

      tbody.innerHTML = filtered.map(c => {
        const caseNumber = c.caseNumber || c.case_number;
        const dateFiled = c.dateFiled || c.date_filed;
        const compName = c.complainantName || c.complainant_name;
        const respName = c.respondentName || c.respondent_name;
        const disputeType = c.disputeType || c.dispute_type;
        const details = c.complaintDetails || c.complaint_details || '';
        const stage = c.stage;
        const pbDeadline = c.pbDeadline || c.pb_deadline || '15 Days';
        const settleAmount = c.settlementAmount || c.settlement_amount || 0;

        let stageBadge = '<span class="badge-blue">PB Mediation</span>';
        if (stage === 'Pangkat Conciliation') stageBadge = '<span class="badge-amber">Pangkat Conciliation</span>';
        else if (stage === 'Amicably Settled') stageBadge = '<span class="badge-emerald">&#10003; Settled</span>';
        else if (stage === 'CFA Issued') stageBadge = '<span class="badge-rose">CFA Issued</span>';

        const timerBadge = getStatutoryCountdownBadge(c);

        let resolutionInfo = `<span style="color: var(--color-text-muted);">Deadline: ${pbDeadline}</span>`;
        if (stage === 'Amicably Settled') {
          resolutionInfo = `<strong style="color: #059669;">Settled PHP ${parseFloat(settleAmount).toLocaleString()}</strong>`;
        } else if (stage === 'CFA Issued') {
          resolutionInfo = `<span style="color: #dc2626; font-size: 0.75rem;">Referred to Court</span>`;
        }

        return `
          <tr>
            <td>
              <div style="font-family: monospace; font-weight: 700; color: #4338ca; font-size: 0.8125rem;">${caseNumber}</div>
              <div class="typography-caption">Filed: ${dateFiled}</div>
            </td>
            <td>
              <div style="font-weight: 600; font-size: 0.8125rem;">${compName} <span class="typography-caption" style="color: #4338ca;">(Complainant)</span></div>
              <div class="typography-caption" style="color: var(--color-text-faint); margin: 1px 0;">vs.</div>
              <div style="font-weight: 600; font-size: 0.8125rem;">${respName} <span class="typography-caption" style="color: #dc2626;">(Respondent)</span></div>
            </td>
            <td>
              <div style="font-weight: 600; font-size: 0.8125rem;">${disputeType}</div>
              <div class="typography-caption" style="max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                ${details || '-'}
              </div>
            </td>
            <td>
              <div>${stageBadge}</div>
              <div style="margin-top: 4px;">${timerBadge}</div>
            </td>
            <td>
              <div style="font-size: 0.8125rem;">${resolutionInfo}</div>
            </td>
            <td style="text-align: right;">
              <div style="display: flex; justify-content: flex-end; gap: 4px; flex-wrap: wrap;">
                <button type="button" class="table-action-btn" onclick="openCaseDossier(${c.id})" title="View full dispute brief">
                  Dossier
                </button>
                <button type="button" class="table-action-btn" onclick="openPrintSuite(${c.id})" title="Generate &amp; Print KP Forms">
                  Print (7-20)
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Render TAB 2 Hearings Docket
    function renderHearingsDocket() {
      const tbody = document.getElementById('hearings-tbody');
      if (allHearings.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--color-text-muted); padding: 24px;">No hearings scheduled yet.</td></tr>`;
        return;
      }

      tbody.innerHTML = allHearings.map(h => {
        const caseId = h.caseId || h.case_id;
        const matchingCase = allCases.find(c => c.id === caseId) || {
          caseNumber: h.case_number || 'KP-CASE',
          complainantName: h.complainant_name || 'Complainant',
          respondentName: h.respondent_name || 'Respondent'
        };

        const schedDate = h.scheduledDate || h.scheduled_date;
        const schedTime = h.scheduledTime || h.scheduled_time || '14:00';
        const hearingNum = h.hearingNumber || h.hearing_number;
        const hearingType = h.hearingType || h.hearing_type;
        const officer = h.presidingOfficer || h.presiding_officer;
        const venue = h.venue;
        const compPresent = (h.complainantPresent !== undefined) ? h.complainantPresent : h.complainant_present;
        const respPresent = (h.respondentPresent !== undefined) ? h.respondentPresent : h.respondent_present;

        const compBadge = compPresent ? '<span class="badge-emerald" style="font-size: 0.625rem;">Comp: Present</span>' : '<span class="badge-rose" style="font-size: 0.625rem;">Comp: Absent</span>';
        const respBadge = respPresent ? '<span class="badge-emerald" style="font-size: 0.625rem;">Resp: Present</span>' : '<span class="badge-rose" style="font-size: 0.625rem;">Resp: Absent</span>';

        return `
          <tr>
            <td>
              <div style="font-weight: 700; font-size: 0.8125rem;">${schedDate}</div>
              <div class="typography-caption">${schedTime}</div>
            </td>
            <td>
              <span class="badge-neutral" style="font-size: 0.6875rem;">${hearingNum}</span>
              <div class="typography-caption" style="margin-top: 2px;">${hearingType}</div>
            </td>
            <td>
              <div style="font-family: monospace; font-weight: 700; color: #4338ca; font-size: 0.75rem;">${matchingCase.caseNumber || matchingCase.case_number}</div>
              <div style="font-size: 0.8125rem;">${matchingCase.complainantName || matchingCase.complainant_name} vs. ${matchingCase.respondentName || matchingCase.respondent_name}</div>
            </td>
            <td>
              <div style="font-size: 0.8125rem; font-weight: 600;">${officer}</div>
              <div class="typography-caption">${venue}</div>
            </td>
            <td>
              <div style="display: flex; flex-direction: column; gap: 2px;">
                ${compBadge}
                ${respBadge}
              </div>
            </td>
            <td style="text-align: right;">
              <div style="display: flex; justify-content: flex-end; gap: 4px;">
                <button type="button" class="table-action-btn" onclick="sendHearingSMS(${h.id})" title="Send SMS Summons Patawag">
                  SMS Summons
                </button>
                <button type="button" class="table-action-btn" onclick="openPrintSuite(${matchingCase.id}, 'kp8')" title="Print KP Form 8 Patawag">
                  Print Form 8
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Render TAB 3 Lupon Roster Grid
    function renderLuponRoster() {
      const container = document.getElementById('roster-container');
      if (allMembers.length === 0) {
        container.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: var(--color-text-muted); padding: 24px;">No appointed Lupon members found.</div>`;
        return;
      }

      container.innerHTML = allMembers.map(m => {
        const fullName = m.fullName || m.full_name;
        const committee = m.committeeAssignment || m.committee_assignment;
        const profession = m.professionBackground || m.profession_background;
        const appDate = m.appointmentDate || m.appointment_date;
        const casesCount = m.casesHandledCount !== undefined ? m.casesHandledCount : (m.cases_handled_count || 0);

        const initials = fullName.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
        const statusBadge = m.status === 'Active' 
          ? '<span class="badge-emerald" style="font-size: 0.625rem;">Active Member</span>' 
          : '<span class="badge-neutral" style="font-size: 0.625rem;">' + m.status + '</span>';

        return `
          <div class="member-card">
            <div style="display: flex; align-items: center; gap: 12px;">
              <div class="member-avatar">${initials}</div>
              <div>
                <div style="font-weight: 700; font-size: 0.9375rem; color: var(--color-ink);">${fullName}</div>
                <div class="typography-caption" style="color: #4338ca; font-weight: 600;">${committee}</div>
              </div>
            </div>

            <div style="font-size: 0.75rem; color: var(--color-text-muted); line-height: 1.4;">
              <div><strong>Background:</strong> ${profession || 'Community Arbiter'}</div>
              <div><strong>Appointed:</strong> ${appDate}</div>
              <div><strong>Disputes Mediated:</strong> ${casesCount} cases</div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--color-hairline-soft); padding-top: 8px;">
              ${statusBadge}
              <button type="button" class="table-action-btn" onclick="openEditMember(${m.id})">
                Edit Member
              </button>
            </div>
          </div>
        `;
      }).join('');
    }

    // Render TAB 4 Settlement & Execution Ledger
    function renderSettlementLedger() {
      const tbody = document.getElementById('settlements-tbody');
      const settledCases = allCases.filter(c => c.stage === 'Amicably Settled');

      if (settledCases.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 24px;">No executed amicable settlements recorded.</td></tr>`;
        return;
      }

      tbody.innerHTML = settledCases.map(c => {
        const caseNumber = c.caseNumber || c.case_number;
        const compName = c.complainantName || c.complainant_name;
        const respName = c.respondentName || c.respondent_name;
        const disputeType = c.disputeType || c.dispute_type;
        const settleAmount = c.settlementAmount || c.settlement_amount || 0;
        const settleTerms = c.settlementTerms || c.settlement_terms || '-';
        const settleDateStr = c.settlementDate || c.settlement_date || c.dateFiled || c.date_filed;
        const complianceDate = c.complianceDueDate || c.compliance_due_date || 'Per Agreement';

        const settleDate = new Date(settleDateStr);
        const today = new Date();
        const daysSinceSettle = Math.floor((today - settleDate) / (1000 * 60 * 60 * 24));

        let repudiationStatus = '';
        if (daysSinceSettle <= 10) {
          const daysLeft = 10 - daysSinceSettle;
          repudiationStatus = `<span class="statutory-countdown-badge countdown-warning">${daysLeft} days left to repudiate</span>`;
        } else {
          repudiationStatus = `<span class="statutory-countdown-badge countdown-settled">&#10003; Final &amp; Executory (Sec. 416)</span>`;
        }

        let executionStatus = '';
        const monthsSince = daysSinceSettle / 30;
        if (monthsSince <= 6) {
          executionStatus = `<span class="badge-emerald" style="font-size: 0.625rem;">PB Execution Valid (${Math.round(6 - monthsSince)} mos left)</span>`;
        } else {
          executionStatus = `<span class="badge-neutral" style="font-size: 0.625rem;">Municipal Court Jurisdiction</span>`;
        }

        return `
          <tr>
            <td>
              <div style="font-family: monospace; font-weight: 700; color: #059669; font-size: 0.8125rem;">${caseNumber}</div>
              <div class="typography-caption">Settled: ${settleDateStr}</div>
            </td>
            <td>
              <div style="font-weight: 600; font-size: 0.8125rem;">${compName} vs. ${respName}</div>
              <div class="typography-caption">${disputeType}</div>
            </td>
            <td>
              <div style="font-size: 0.8125rem; font-weight: 600; color: #059669;">PHP ${parseFloat(settleAmount).toLocaleString()}</div>
              <div class="typography-caption" style="max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${settleTerms}</div>
            </td>
            <td>
              ${repudiationStatus}
            </td>
            <td>
              <div style="font-size: 0.8125rem;">${complianceDate}</div>
            </td>
            <td>
              ${executionStatus}
            </td>
            <td style="text-align: right;">
              <button type="button" class="table-action-btn" onclick="openPrintSuite(${c.id}, 'kp16')">
                KP Form 16
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Populate dynamic modal select pickers
    function populateDropdowns() {
      // Hearing case picker
      const hearingSelect = document.getElementById('hearing-case-select');
      const activeCases = allCases.filter(c => c.stage !== 'Amicably Settled' && c.stage !== 'CFA Issued' && c.stage !== 'Dismissed');
      hearingSelect.innerHTML = activeCases.map(c => `<option value="${c.id}">${c.caseNumber || c.case_number}: ${c.complainantName || c.complainant_name} vs. ${c.respondentName || c.respondent_name}</option>`).join('');

      // Pangkat modal pickers
      const pangkatCaseSelect = document.getElementById('pangkat-case-select');
      pangkatCaseSelect.innerHTML = activeCases.map(c => `<option value="${c.id}">${c.caseNumber || c.case_number}: ${c.complainantName || c.complainant_name} vs. ${c.respondentName || c.respondent_name} (${c.stage})</option>`).join('');

      const activeMembers = allMembers.filter(m => m.status === 'Active');
      const memberOptions = activeMembers.map(m => `<option value="${m.fullName || m.full_name}">${m.fullName || m.full_name} (${m.committeeAssignment || m.committee_assignment})</option>`).join('');

      document.getElementById('pangkat-chair-select').innerHTML = memberOptions;
      document.getElementById('pangkat-sec-select').innerHTML = memberOptions;
      document.getElementById('pangkat-mem-select').innerHTML = memberOptions;

      // Settlement and CFA case pickers
      document.getElementById('settle-case-select').innerHTML = activeCases.map(c => `<option value="${c.id}">${c.caseNumber || c.case_number}: ${c.complainantName || c.complainant_name} vs. ${c.respondentName || c.respondent_name}</option>`).join('');
      document.getElementById('cfa-case-select').innerHTML = activeCases.map(c => `<option value="${c.id}">${c.caseNumber || c.case_number}: ${c.complainantName || c.complainant_name} vs. ${c.respondentName || c.respondent_name}</option>`).join('');
    }

    // Bind All User Interaction Events
    function bindEventListeners() {
      // Tab Switching
      document.querySelectorAll('.kp-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          document.querySelectorAll('.kp-tab-btn').forEach(b => b.classList.remove('active'));
          document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');

          btn.classList.add('active');
          const targetId = btn.dataset.tab;
          const targetPane = document.getElementById(targetId);
          if (targetPane) targetPane.style.display = 'block';
        });
      });

      // Filter Inputs
      document.getElementById('search-kp-cases').addEventListener('input', renderDocketTable);
      document.getElementById('filter-stage').addEventListener('change', renderDocketTable);
      document.getElementById('filter-dispute-type').addEventListener('change', renderDocketTable);
      document.getElementById('btn-clear-filters').addEventListener('click', () => {
        document.getElementById('search-kp-cases').value = '';
        document.getElementById('filter-stage').value = '';
        document.getElementById('filter-dispute-type').value = '';
        renderDocketTable();
      });

      // File Case Button
      document.getElementById('btn-file-case').addEventListener('click', () => {
        document.getElementById('file-case-form').reset();
        const year = new Date().getFullYear();
        const count = allCases.length + 1;
        document.getElementById('file-case-number').value = `KP-${year}-${String(count).padStart(4, '0')}`;
        document.getElementById('file-date-filed').value = new Date().toISOString().split('T')[0];
        document.getElementById('file-case-modal').showModal();
      });

      // Schedule Summons Button
      const openSchedModal = () => {
        document.getElementById('schedule-hearing-form').reset();
        const nextDate = new Date();
        nextDate.setDate(nextDate.getDate() + 3);
        document.getElementById('hearing-date').value = nextDate.toISOString().split('T')[0];
        document.getElementById('schedule-hearing-modal').showModal();
      };
      document.getElementById('btn-schedule-summons').addEventListener('click', openSchedModal);
      document.getElementById('btn-schedule-hearing-tab').addEventListener('click', openSchedModal);

      // Appoint Lupon Member
      const openAppointModal = () => {
        document.getElementById('member-form').reset();
        document.getElementById('member-id').value = '';
        document.getElementById('member-modal-title').textContent = 'Appoint Lupon Member.';
        document.getElementById('member-appointment-date').value = new Date().toISOString().split('T')[0];
        document.getElementById('member-modal').showModal();
      };
      document.getElementById('btn-appoint-lupon').addEventListener('click', openAppointModal);
      document.getElementById('btn-add-member-roster').addEventListener('click', openAppointModal);

      // Pangkat Builder Trigger
      document.getElementById('btn-trigger-pangkat-builder').addEventListener('click', () => {
        document.getElementById('pangkat-form').reset();
        document.getElementById('pangkat-modal').showModal();
      });

      // Submit: File KP Complaint
      document.getElementById('file-case-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const caseNumber = document.getElementById('file-case-number').value.trim();
        const dateFiled = document.getElementById('file-date-filed').value;
        const compName = document.getElementById('file-comp-name').value.trim();
        const compContact = document.getElementById('file-comp-contact').value.trim();
        const compAddress = document.getElementById('file-comp-address').value.trim();
        const respName = document.getElementById('file-resp-name').value.trim();
        const respContact = document.getElementById('file-resp-contact').value.trim();
        const respAddress = document.getElementById('file-resp-address').value.trim();
        const disputeType = document.getElementById('file-dispute-type').value;
        const details = document.getElementById('file-details').value.trim();
        const relief = document.getElementById('file-relief').value.trim();
        const blotterId = document.getElementById('file-blotter-id').value;

        const d = new Date(dateFiled);
        d.setDate(d.getDate() + 15);
        const pbDeadline = d.toISOString().split('T')[0];

        const newCase = {
          caseNumber,
          dateFiled,
          complainantName: compName,
          complainantContact: compContact,
          complainantAddress: compAddress,
          respondentName: respName,
          respondentContact: respContact,
          respondentAddress: respAddress,
          disputeType,
          complaintDetails: details,
          reliefSought: relief,
          stage: 'PB Mediation',
          pbDeadline,
          blotterCaseId: blotterId ? parseInt(blotterId) : null
        };

        try {
          await window.barangayDB.add('lupon_cases', newCase);

          document.getElementById('file-case-modal').close();
          Toast.success(`Case ${caseNumber} filed! 15-day PB mediation window initiated.`);
          await refreshAllData();
        } catch (err) {
          console.error(err);
          Toast.error('Failed to file KP dispute case.');
        }
      });

      // Submit: Schedule Hearing
      document.getElementById('schedule-hearing-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const caseId = parseInt(document.getElementById('hearing-case-select').value);
        const hearingNumber = document.getElementById('hearing-number').value;
        const hearingType = document.getElementById('hearing-type').value;
        const scheduledDate = document.getElementById('hearing-date').value;
        const scheduledTime = document.getElementById('hearing-time').value;
        const venue = document.getElementById('hearing-venue').value.trim();
        const presiding = document.getElementById('hearing-presiding').value.trim();
        const sendSMS = document.getElementById('hearing-sms-toggle').checked;

        const targetCase = allCases.find(c => c.id === caseId);

        try {
          await window.barangayDB.add('lupon_hearings', {
            caseId,
            caseNumber: targetCase ? (targetCase.caseNumber || targetCase.case_number) : '',
            hearingNumber,
            hearingType,
            scheduledDate,
            scheduledTime,
            venue,
            presidingOfficer: presiding,
            complainantPresent: 1,
            respondentPresent: 1,
            proceedingsSummary: `Scheduled ${hearingNumber} for ${scheduledDate}.`,
            nextAction: 'Parties to appear in person'
          });

          if (sendSMS && targetCase) {
            await window.barangayDB.add('notifications', {
              dispatchCode: `KP-SMS-${Date.now()}`,
              recipientName: targetCase.respondentName || targetCase.respondent_name,
              recipientContact: targetCase.respondentContact || targetCase.respondent_contact || '0918-000-0000',
              channel: 'SMS',
              category: 'KP Summons',
              message: `PATAWAG: Kayo ay inaatasan na humarap sa Lupon Tagapamayapa para sa Kaso ${targetCase.caseNumber || targetCase.case_number} sa darating na ${scheduledDate} sa ganap na ika-${scheduledTime}.`,
              status: 'Delivered',
              createdAt: new Date().toISOString()
            });
          }

          document.getElementById('schedule-hearing-modal').close();
          Toast.success(`Hearing scheduled for ${scheduledDate}!`);
          await refreshAllData();
        } catch (err) {
          console.error(err);
          Toast.error('Failed to schedule hearing.');
        }
      });

      // Submit: Constitute Pangkat
      document.getElementById('pangkat-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const caseId = parseInt(document.getElementById('pangkat-case-select').value);
        const chair = document.getElementById('pangkat-chair-select').value;
        const sec = document.getElementById('pangkat-sec-select').value;
        const mem = document.getElementById('pangkat-mem-select').value;

        if (chair === sec || sec === mem || chair === mem) {
          Toast.error('Pangkat members must be three (3) distinct individuals.');
          return;
        }

        const targetCase = allCases.find(c => c.id === caseId);
        if (!targetCase) return;

        const pangkatDeadline = new Date();
        pangkatDeadline.setDate(pangkatDeadline.getDate() + 15);

        try {
          targetCase.stage = 'Pangkat Conciliation';
          targetCase.pangkatChairman = chair;
          targetCase.pangkatSecretary = sec;
          targetCase.pangkatMember = mem;
          targetCase.pangkatDeadline = pangkatDeadline.toISOString().split('T')[0];

          await window.barangayDB.update('lupon_cases', targetCase);

          document.getElementById('pangkat-modal').close();
          Toast.success(`Pangkat constituted for Case ${targetCase.caseNumber || targetCase.case_number}! 15-day conciliation timer initiated.`);
          await refreshAllData();
        } catch (err) {
          console.error(err);
          Toast.error('Failed to constitute Pangkat panel.');
        }
      });

      // Submit: Execute Settlement (Kasunduan)
      document.getElementById('settlement-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const caseId = parseInt(document.getElementById('settle-case-select').value);
        const terms = document.getElementById('settle-terms').value.trim();
        const amount = parseFloat(document.getElementById('settle-amount').value) || 0;
        const settleDate = document.getElementById('settle-date').value;
        const complianceDate = document.getElementById('settle-compliance-date').value;

        const targetCase = allCases.find(c => c.id === caseId);
        if (!targetCase) return;

        try {
          targetCase.stage = 'Amicably Settled';
          targetCase.settlementTerms = terms;
          targetCase.settlementAmount = amount;
          targetCase.settlementDate = settleDate;
          targetCase.complianceDueDate = complianceDate;

          await window.barangayDB.update('lupon_cases', targetCase);

          document.getElementById('settlement-modal').close();
          Toast.success(`Amicable settlement executed for Case ${targetCase.caseNumber || targetCase.case_number}! 10-day repudiation window active.`);
          await refreshAllData();
        } catch (err) {
          console.error(err);
          Toast.error('Failed to record amicable settlement.');
        }
      });

      // Submit: Issue CFA
      document.getElementById('cfa-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const caseId = parseInt(document.getElementById('cfa-case-select').value);
        const reason = document.getElementById('cfa-reason').value;
        const cfaDate = document.getElementById('cfa-date').value;

        const targetCase = allCases.find(c => c.id === caseId);
        if (!targetCase) return;

        try {
          targetCase.stage = 'CFA Issued';
          targetCase.cfaReason = reason;
          targetCase.cfaDate = cfaDate;

          await window.barangayDB.update('lupon_cases', targetCase);

          document.getElementById('cfa-modal').close();
          Toast.success(`Certificate to File Action (CFA) issued for Case ${targetCase.caseNumber || targetCase.case_number}.`);
          await refreshAllData();
        } catch (err) {
          console.error(err);
          Toast.error('Failed to issue Certificate to File Action.');
        }
      });

      // Submit: Appoint / Edit Lupon Member
      document.getElementById('member-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('member-id').value;
        const fullName = document.getElementById('member-name').value.trim();
        const committee = document.getElementById('member-committee').value;
        const profession = document.getElementById('member-profession').value.trim();
        const appDate = document.getElementById('member-appointment-date').value;
        const contact = document.getElementById('member-contact').value.trim();
        const status = document.getElementById('member-status').value;

        try {
          if (id) {
            const mem = allMembers.find(m => m.id === parseInt(id));
            if (mem) {
              mem.fullName = fullName;
              mem.committeeAssignment = committee;
              mem.professionBackground = profession;
              mem.appointmentDate = appDate;
              mem.contactNo = contact;
              mem.status = status;
              await window.barangayDB.update('lupon_members', mem);
              Toast.success('Member updated successfully.');
            }
          } else {
            await window.barangayDB.add('lupon_members', {
              fullName,
              committeeAssignment: committee,
              professionBackground: profession,
              appointmentDate: appDate,
              contactNo: contact,
              status,
              casesHandledCount: 0
            });
            Toast.success('Lupon member appointed.');
          }

          document.getElementById('member-modal').close();
          await refreshAllData();
        } catch (err) {
          console.error(err);
          Toast.error('Failed to save Lupon member.');
        }
      });

      // Print Selector Switcher
      document.getElementById('print-form-selector').addEventListener('change', (e) => {
        renderPrintDocument(e.target.value);
      });
    }

    // Open Case Dossier Modal
    window.openCaseDossier = function(id) {
      const c = allCases.find(item => item.id === id);
      if (!c) return;
      selectedCaseForAction = c;

      const caseNumber = c.caseNumber || c.case_number;
      const disputeType = c.disputeType || c.dispute_type;
      const compName = c.complainantName || c.complainant_name;
      const compAddr = c.complainantAddress || c.complainant_address;
      const compContact = c.complainantContact || c.complainant_contact;
      const respName = c.respondentName || c.respondent_name;
      const respAddr = c.respondentAddress || c.respondent_address;
      const respContact = c.respondentContact || c.respondent_contact;
      const details = c.complaintDetails || c.complaint_details;
      const relief = c.reliefSought || c.relief_sought;
      const chair = c.pangkatChairman || c.pangkat_chairman;
      const sec = c.pangkatSecretary || c.pangkat_secretary;
      const mem = c.pangkatMember || c.pangkat_member;
      const settleTerms = c.settlementTerms || c.settlement_terms;
      const settleAmount = c.settlementAmount || c.settlement_amount || 0;
      const complianceDueDate = c.complianceDueDate || c.compliance_due_date;
      const cfaReason = c.cfaReason || c.cfa_reason;

      document.getElementById('dossier-case-number').textContent = caseNumber;
      document.getElementById('dossier-dispute-title').textContent = disputeType;
      document.getElementById('dossier-comp-name').textContent = compName;
      document.getElementById('dossier-comp-address').textContent = `${compAddr || 'Barangay San Isidro'} • ${compContact || 'No contact'}`;
      document.getElementById('dossier-resp-name').textContent = respName;
      document.getElementById('dossier-resp-address').textContent = `${respAddr || 'Barangay San Isidro'} • ${respContact || 'No contact'}`;
      document.getElementById('dossier-facts').textContent = details || 'No facts stated.';
      document.getElementById('dossier-relief').textContent = relief || 'None indicated.';

      const badgeEl = document.getElementById('dossier-stage-badge');
      badgeEl.textContent = c.stage;
      badgeEl.className = (c.stage === 'Amicably Settled') ? 'badge-emerald' : (c.stage === 'CFA Issued') ? 'badge-rose' : 'badge-blue';

      // Countdown Chip
      const chipEl = document.getElementById('dossier-countdown-chip');
      chipEl.innerHTML = getStatutoryCountdownBadge(c);

      // Pangkat section
      const pBox = document.getElementById('dossier-pangkat-box');
      if (chair) {
        pBox.style.display = 'block';
        document.getElementById('dossier-pangkat-chair').textContent = chair;
        document.getElementById('dossier-pangkat-sec').textContent = sec;
        document.getElementById('dossier-pangkat-member').textContent = mem;
      } else {
        pBox.style.display = 'none';
      }

      // Settlement section
      const sBox = document.getElementById('dossier-settlement-box');
      if (c.stage === 'Amicably Settled') {
        sBox.style.display = 'block';
        document.getElementById('dossier-settlement-terms').textContent = settleTerms || 'Agreed terms registered.';
        document.getElementById('dossier-settlement-meta').textContent = `Amount: PHP ${parseFloat(settleAmount).toLocaleString()} • Compliance Due: ${complianceDueDate || 'N/A'}`;
      } else {
        sBox.style.display = 'none';
      }

      // CFA section
      const cfaBox = document.getElementById('dossier-cfa-box');
      if (c.stage === 'CFA Issued') {
        cfaBox.style.display = 'block';
        document.getElementById('dossier-cfa-reason').textContent = cfaReason || 'Personal confrontation failed.';
      } else {
        cfaBox.style.display = 'none';
      }

      // Action buttons
      document.getElementById('dossier-btn-print').onclick = () => {
        document.getElementById('case-dossier-modal').close();
        openPrintSuite(c.id);
      };

      document.getElementById('dossier-btn-pangkat').onclick = () => {
        document.getElementById('case-dossier-modal').close();
        document.getElementById('pangkat-case-select').value = c.id;
        document.getElementById('pangkat-modal').showModal();
      };

      document.getElementById('dossier-btn-settle').onclick = () => {
        document.getElementById('case-dossier-modal').close();
        document.getElementById('settle-case-select').value = c.id;
        document.getElementById('settle-date').value = new Date().toISOString().split('T')[0];
        document.getElementById('settlement-modal').showModal();
      };

      document.getElementById('dossier-btn-cfa').onclick = () => {
        document.getElementById('case-dossier-modal').close();
        document.getElementById('cfa-case-select').value = c.id;
        document.getElementById('cfa-date').value = new Date().toISOString().split('T')[0];
        document.getElementById('cfa-modal').showModal();
      };

      document.getElementById('case-dossier-modal').showModal();
    };

    // Open Edit Member Modal
    window.openEditMember = function(id) {
      const m = allMembers.find(item => item.id === id);
      if (!m) return;

      document.getElementById('member-id').value = m.id;
      document.getElementById('member-modal-title').textContent = 'Edit Lupon Member.';
      document.getElementById('member-name').value = m.fullName || m.full_name;
      document.getElementById('member-committee').value = m.committeeAssignment || m.committee_assignment;
      document.getElementById('member-profession').value = m.professionBackground || m.profession_background || '';
      document.getElementById('member-appointment-date').value = m.appointmentDate || m.appointment_date || '';
      document.getElementById('member-contact').value = m.contactNo || m.contact_no || '';
      document.getElementById('member-status').value = m.status || 'Active';

      document.getElementById('member-modal').showModal();
    };

    // Direct SMS Summons trigger
    window.sendHearingSMS = async function(hearingId) {
      const h = allHearings.find(item => item.id === hearingId);
      if (!h) return;

      const caseId = h.caseId || h.case_id;
      const c = allCases.find(item => item.id === caseId);
      if (!c) return;

      const compName = c.complainantName || c.complainant_name;
      const respName = c.respondentName || c.respondent_name;
      const respContact = c.respondentContact || c.respondent_contact;
      const caseNumber = c.caseNumber || c.case_number;
      const schedDate = h.scheduledDate || h.scheduled_date;
      const schedTime = h.scheduledTime || h.scheduled_time;

      if (!confirm(`Dispatch SMS Notice of Hearing (KP Form 8) to:\n• Complainant: ${compName}\n• Respondent: ${respName}?`)) return;

      try {
        await window.barangayDB.add('notifications', {
          dispatchCode: `SMS-KP-${Date.now()}`,
          recipientName: respName,
          recipientContact: respContact || '0918-000-0000',
          channel: 'SMS',
          category: 'KP Summons',
          message: `PATAWAG (SUMMONS): Kayo ay inaatasan na humarap sa Lupon Tagapamayapa para sa Kaso ${caseNumber} sa darating na ${schedDate} sa ganap na ika-${schedTime} sa Barangay Hall.`,
          status: 'Delivered',
          costCredits: 1,
          createdAt: new Date().toISOString()
        });
        Toast.success(`Summons SMS successfully dispatched to ${respName}!`);
      } catch (err) {
        Toast.error('Failed to dispatch SMS.');
      }
    };

    // Open Official Print Suite (KP Forms 7 to 20)
    window.openPrintSuite = function(caseId, preselectedForm = 'kp7') {
      const c = allCases.find(item => item.id === caseId);
      if (!c) return;
      selectedCaseForAction = c;

      const caseNumber = c.caseNumber || c.case_number;
      const compName = c.complainantName || c.complainant_name;
      const compAddr = c.complainantAddress || c.complainant_address;
      const respName = c.respondentName || c.respondent_name;
      const respAddr = c.respondentAddress || c.respondent_address;
      const disputeType = c.disputeType || c.dispute_type;
      const dateFiled = c.dateFiled || c.date_filed;

      document.getElementById('print-modal-badge').textContent = caseNumber;
      document.getElementById('print-case-num').textContent = caseNumber;
      document.getElementById('print-comp-name').textContent = compName.toUpperCase();
      document.getElementById('print-comp-addr').textContent = compAddr || 'Barangay San Isidro, Cabuyao City';
      document.getElementById('print-resp-name').textContent = respName.toUpperCase();
      document.getElementById('print-resp-addr').textContent = respAddr || 'Barangay San Isidro, Cabuyao City';
      document.getElementById('print-dispute-type').textContent = disputeType;
      document.getElementById('print-date-filed').textContent = dateFiled;

      document.getElementById('print-form-selector').value = preselectedForm;
      renderPrintDocument(preselectedForm);

      const qrEl = document.getElementById('print-kp-qr');
      if (qrEl && window.QRCode) {
        qrEl.innerHTML = '';
        const verifyUrl = `${window.location.origin}/verify.php?code=${encodeURIComponent(caseNumber)}`;
        QRCode.render(qrEl, verifyUrl, { size: 54, margin: 1 });
      }

      document.getElementById('lupon-print-modal').showModal();
    };

    // Render exact statutory text for each KP Form
    function renderPrintDocument(formType) {
      const c = selectedCaseForAction;
      if (!c) return;

      const compName = c.complainantName || c.complainant_name;
      const respName = c.respondentName || c.respondent_name;
      const details = c.complaintDetails || c.complaint_details;
      const relief = c.reliefSought || c.relief_sought;
      const disputeType = c.disputeType || c.dispute_type;
      const dateFiled = c.dateFiled || c.date_filed;
      const chair = c.pangkatChairman || c.pangkat_chairman;
      const sec = c.pangkatSecretary || c.pangkat_secretary;
      const mem = c.pangkatMember || c.pangkat_member;
      const settleTerms = c.settlementTerms || c.settlement_terms;
      const settleAmount = c.settlementAmount || c.settlement_amount || 0;
      const complianceDueDate = c.complianceDueDate || c.compliance_due_date;
      const cfaReason = c.cfaReason || c.cfa_reason;

      const titleEl = document.getElementById('print-form-title');
      const bodyEl = document.getElementById('print-form-body');
      const sigEl = document.getElementById('print-signatures-row');

      if (formType === 'kp7') {
        titleEl.textContent = 'KP FORM 7: SUMBONG (COMPLAINT)';
        bodyEl.innerHTML = `
          <p>AKO/KAMI ay nagrereklamo laban sa ipinagsusumbong na binanggit sa itaas dahil sa paglabag sa aking/aming mga karapatan at kapakanan sa sumusunod na paraan:</p>
          <div style="padding: 12px 16px; background: #fafafa; border: 1px solid #ddd; margin: 12px 0; font-style: italic;">
            "${details || 'Walang nakatalang detalye.'}"
          </div>
          <p>DAHIL DITO, AKO/KAMI ay magalang na humihiling na igawad ang sumusunod na lunas:</p>
          <div style="padding: 12px 16px; background: #fafafa; border: 1px solid #ddd; margin: 12px 0; font-weight: bold;">
            "${relief || 'Makatarungang pag-aayos at pagbabayad alinsunod sa batas.'}"
          </div>
          <p>Ginawa ngayong ika-<strong>${new Date(dateFiled).getDate()}</strong> araw ng <strong>${new Date(dateFiled).toLocaleDateString('fil-PH', { month: 'long', year: 'numeric' })}</strong>.</p>
        `;
        sigEl.innerHTML = `
          <div style="width: 240px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">${compName.toUpperCase()}</div>
            <div style="font-size: 8.5pt;">May-sumbong / Complainant</div>
          </div>
          <div style="width: 240px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">HON. ANTONIO S. VALDEZ</div>
            <div style="font-size: 8.5pt;">Punong Barangay / Lupon Chairman</div>
          </div>
        `;
      } else if (formType === 'kp8') {
        titleEl.textContent = 'KP FORM 8: PATAWAG (SUMMONS)';
        bodyEl.innerHTML = `
          <p>KAY G./GNG. <strong>${respName.toUpperCase()}</strong>:</p>
          <p>Kayo ay tinatawagan at inaatasan na humarap sa akin nang personal, kasama ang inyong mga saksi, sa darating na araw ng pagdinig sa ganap na ika-<strong>2:00 ng hapon</strong> sa Bulwagan ng Barangay San Isidro, Cabuyao City, upang sagutin ang sumbong na inihain laban sa inyo ni <strong>${compName}</strong> ukol sa <em>${disputeType}</em>.</p>
          <p>Kayo ay binababalaan na ang inyong pagtanggi o pagkabigong humarap nang walang makatwirang dahilan ay magiging sanhi upang kayo ay mapatawan ng parusang hindi makapaghain ng kontra-habla o pagkakaloob ng Katunayan Upang Makadulog sa Hukuman (CFA) pabor sa may-sumbong alinsunod sa Seksyon 412 ng Batas Republika Blg. 7160.</p>
          <p>PINATUTUNAYAN ngayong ika-<strong>${new Date().getDate()}</strong> ng <strong>${new Date().toLocaleDateString('fil-PH', { month: 'long', year: 'numeric' })}</strong>.</p>
        `;
        sigEl.innerHTML = `
          <div style="width: 240px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">BARANGAY PEACE OFFICER</div>
            <div style="font-size: 8.5pt;">Officer's Return of Service</div>
          </div>
          <div style="width: 240px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">HON. ANTONIO S. VALDEZ</div>
            <div style="font-size: 8.5pt;">Punong Barangay / Lupon Chairman</div>
          </div>
        `;
      } else if (formType === 'kp9') {
        titleEl.textContent = 'KP FORM 9: SUBPOENA SA SAKSI (WITNESS SUBPOENA)';
        bodyEl.innerHTML = `
          <p>KAY: __________________________________________________ (Pangalan ng Saksi)</p>
          <p>Sa ngalan ng Katarungang Pambarangay, kayo ay inaatasan na humarap sa Tanggapan ng Lupong Tagapamayapa ng Barangay San Isidro upang magbigay ng inyong testimonya at patotoo bilang saksi sa usaping namamagitan kina <strong>${compName}</strong> at <strong>${respName}</strong>.</p>
          <p>Ang inyong pagkabigo na tumugon ay ipapataw alinsunod sa batas at kapangyarihan ng Lupong Tagapamayapa (Sec. 410b).</p>
        `;
        sigEl.innerHTML = `
          <div style="width: 240px;"></div>
          <div style="width: 240px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">HON. ANTONIO S. VALDEZ</div>
            <div style="font-size: 8.5pt;">Punong Barangay / Lupon Chairman</div>
          </div>
        `;
      } else if (formType === 'kp10') {
        titleEl.textContent = 'KP FORM 10: PAUNAWA UKOL SA PAGBUO NG PANGKAT';
        bodyEl.innerHTML = `
          <p>Yamang hindi nagkasundo ang mga panig sa pamamagitan ng pamamagitan ng Punong Barangay, ang mga panig ay pormal na pinagpasiyahang bumuo ng <strong>Pangkat ng Tagapagkasundo</strong> alinsunod sa Seksyon 404 ng Batas Republika Blg. 7160:</p>
          <div style="margin: 16px 0; padding: 12px; border: 1px solid #ccc;">
            <div>1. <strong>Tagapangulo (Chairman):</strong> ${chair || 'Gng. Corazon De Leon'}</div>
            <div style="margin: 6px 0;">2. <strong>Kalihim (Secretary):</strong> ${sec || 'Hon. Benjamin Alcantara'}</div>
            <div>3. <strong>Kasapi (Member):</strong> ${mem || 'Engr. Dominador Santos'}</div>
          </div>
          <p>Ang naturang Pangkat ay mayroong labing-limang (15) araw na palugit upang magsagawa ng pagdinig at ayusin ang alitan.</p>
        `;
        sigEl.innerHTML = `
          <div style="width: 220px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">${compName.toUpperCase()}</div>
            <div style="font-size: 8.5pt;">May-sumbong</div>
          </div>
          <div style="width: 220px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">${respName.toUpperCase()}</div>
            <div style="font-size: 8.5pt;">Ipinagsusumbong</div>
          </div>
          <div style="width: 220px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">HON. ANTONIO S. VALDEZ</div>
            <div style="font-size: 8.5pt;">Punong Barangay</div>
          </div>
        `;
      } else if (formType === 'kp14') {
        titleEl.textContent = 'KP FORM 14: KASUNDUAN SA PAG-AARBITRO (ARBITRATION AGREEMENT)';
        bodyEl.innerHTML = `
          <p>Kami, ang may-sumbong at ang ipinagsusumbong sa usaping ito, ay nagkakasundo na ipailalim ang aming alitan sa pinal na pag-aarbitro ng Lupong Tagapamayapa / Pangkat ng Tagapagkasundo alinsunod sa Seksyon 413 ng Batas Republika Blg. 7160.</p>
          <p>Kami ay buong pusong nangangako na tutuparin at igagalang ang anumang pasiya o arbitration award na kanilang igagawad, at kinikilala na ang desisyong ito ay pinal at maaaring ipatupad sa pamamagitan ng execution.</p>
        `;
        sigEl.innerHTML = `
          <div style="width: 240px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">${compName.toUpperCase()}</div>
            <div style="font-size: 8.5pt;">May-sumbong</div>
          </div>
          <div style="width: 240px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">${respName.toUpperCase()}</div>
            <div style="font-size: 8.5pt;">Ipinagsusumbong</div>
          </div>
        `;
      } else if (formType === 'kp16') {
        titleEl.textContent = 'KP FORM 16: KASUNDUANG PAG-AAYOS (AMICABLE SETTLEMENT)';
        bodyEl.innerHTML = `
          <p>KAMI, ang may-sumbong at ang ipinagsusumbong sa usaping ito, matapos ang masusing pagdinig at paliwanagan, ay buong pusong nagkakasundo sa sumusunod na mga kondisyon at pananagutan:</p>
          <div style="padding: 14px 18px; background: #fafafa; border: 1px solid #bbb; margin: 14px 0; font-weight: bold; line-height: 1.6;">
            ${settleTerms || 'Napagkasunduan ng mga panig ang buong kapayapaan at pagtupad sa mga napagkasunduang obligasyon.'}
          </div>
          <p><strong>Halagang Napagkasunduan:</strong> PHP ${parseFloat(settleAmount).toLocaleString()} &bull; <strong>Takdang Petsa ng Pagtupad:</strong> ${complianceDueDate || 'Kagyat / Agad'}.</p>
          <p style="font-size: 9pt; color: #444; border-top: 1px solid #ccc; padding-top: 8px;">
            <strong>BABALA ALINSUNOD SA SEKSYON 416:</strong> Ang kasunduang ito ay may bisa at lakas ng isang pinal na hatol ng hukuman pagkalipas ng sampung (10) araw mula sa petsang ito, maliban kung ito ay itakwil sa ilalim ng sinumpaang salaysay dahil sa panlilinlang, karahasan o pananakot.
          </p>
        `;
        sigEl.innerHTML = `
          <div style="width: 220px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">${compName.toUpperCase()}</div>
            <div style="font-size: 8.5pt;">May-sumbong / Complainant</div>
          </div>
          <div style="width: 220px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">${respName.toUpperCase()}</div>
            <div style="font-size: 8.5pt;">Ipinagsusumbong / Respondent</div>
          </div>
          <div style="width: 220px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">HON. ANTONIO S. VALDEZ</div>
            <div style="font-size: 8.5pt;">Punong Barangay / Lupon Chairman</div>
          </div>
        `;
      } else if (formType === 'kp20') {
        titleEl.textContent = 'KP FORM 20: KATUNAYAN UPANG MAKADULOG SA HUKUMAN (CFA)';
        bodyEl.innerHTML = `
          <p>PINATUTUNAYAN DITO NA:</p>
          <ol style="margin-left: 20px; line-height: 1.6;">
            <li>Nagkaroon ng personal na paghaharap ang mga panig na pinamunuan ng Punong Barangay / Pangkat ng Tagapagkasundo; ngunit</li>
            <li>Hindi nagkaroon ng anumang kasunduang pag-aayos sa kabila ng pagsisikap ng Lupon; O</li>
            <li>Ang ipinagsusumbong ay kusang hindi humarap nang walang makatwirang dahilan matapos maipatawag nang maayos alinsunod sa batas.</li>
          </ol>
          <div style="padding: 12px 16px; background: #fff5f5; border: 1px solid #feb2b2; margin: 14px 0;">
            <strong>Tiyak na Batayan ng Pagpapatunay (Sec. 412 Ground):</strong><br>
            <em>"${cfaReason || 'Personal confrontation occurred before Punong Barangay / Pangkat but no amicable settlement was reached.'}"</em>
          </div>
          <p>DAHIL DITO, ang kaukulang sumbong ukol sa alitang ito ay maaari nang ihain sa Kagalang-galang na Hukuman o Tanggapan ng Piskalya.</p>
        `;
        sigEl.innerHTML = `
          <div style="width: 240px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">${(sec || 'HON. BENJAMIN ALCANTARA').toUpperCase()}</div>
            <div style="font-size: 8.5pt;">Kalihim ng Pangkat / Lupon Secretary</div>
          </div>
          <div style="width: 240px;">
            <div style="height: 45px;"></div>
            <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: bold;">HON. ANTONIO S. VALDEZ</div>
            <div style="font-size: 8.5pt;">Punong Barangay / Lupon Chairman</div>
          </div>
        `;
      }
    }

    // Check if coming from blotter with escalate payload
    function checkBlotterEscalation() {
      const stored = sessionStorage.getItem('kp_escalate_case');
      if (stored) {
        try {
          const data = JSON.parse(stored);
          sessionStorage.removeItem('kp_escalate_case');

          document.getElementById('file-case-form').reset();
          const year = new Date().getFullYear();
          const count = allCases.length + 1;
          document.getElementById('file-case-number').value = `KP-${year}-${String(count).padStart(4, '0')}`;
          document.getElementById('file-date-filed').value = new Date().toISOString().split('T')[0];

          document.getElementById('file-comp-name').value = data.complainantName || '';
          document.getElementById('file-comp-address').value = data.complainantAddress || 'Barangay San Isidro';
          document.getElementById('file-comp-contact').value = data.complainantContact || '';

          document.getElementById('file-resp-name').value = data.respondentName || '';
          document.getElementById('file-resp-address').value = data.respondentAddress || 'Barangay San Isidro';
          document.getElementById('file-resp-contact').value = data.respondentContact || '';

          document.getElementById('file-details').value = data.complaintDetails || '';
          document.getElementById('file-relief').value = data.reliefSought || 'Amicable conciliation before Lupong Tagapamayapa.';
          document.getElementById('file-blotter-id').value = data.blotterCaseId || '';

          document.getElementById('file-case-modal').showModal();
          Toast.info(`Imported Blotter Case ${data.blotterCaseNo || ''} into Katarungang Pambarangay Studio.`);
        } catch (e) {
          console.warn('Could not parse blotter escalation payload:', e);
        }
      }
    }
  </script>
</body>
</html>
