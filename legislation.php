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
  <title>Sangguniang Barangay Legislative Tracking &amp; Ordinance Management &bull; BarangayOS</title>
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
    .leg-tab-nav {
      display: flex;
      gap: var(--spacing-xs);
      border-bottom: 1px solid var(--color-hairline-soft);
      margin-bottom: var(--spacing-md);
      overflow-x: auto;
    }

    .leg-tab-btn {
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

    .leg-tab-btn:hover {
      color: var(--color-ink);
    }

    .leg-tab-btn.active {
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

    /* Reading Progress Stepper */
    .reading-stepper {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px;
      background: var(--color-canvas-soft);
      border-radius: var(--rounded-sm);
      border: 1px solid var(--color-hairline-soft);
      margin-bottom: 16px;
      overflow-x: auto;
    }

    .step-node {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--color-text-muted);
      white-space: nowrap;
    }

    .step-node.active {
      color: var(--color-ink);
    }

    .step-circle {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: var(--color-canvas);
      border: 1.5px solid var(--color-hairline);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.6875rem;
    }

    .step-node.active .step-circle {
      background: var(--color-ink);
      color: var(--color-canvas);
      border-color: var(--color-ink);
    }

    .step-arrow {
      color: var(--color-hairline);
      font-size: 0.75rem;
    }

    /* Roll Call Member Box */
    .roll-call-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 10px;
      margin-bottom: 14px;
    }

    .roll-call-item {
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-sm);
      padding: 10px;
      background: var(--color-canvas);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .roll-call-name {
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--color-ink);
    }

    .roll-call-role {
      font-size: 0.6875rem;
      color: var(--color-text-muted);
    }

    /* Statutory Callout */
    .statutory-callout {
      border: 1px solid #8b5cf6;
      background: rgba(139, 92, 246, 0.05);
      border-radius: var(--rounded-md);
      padding: 14px 16px;
      display: flex;
      align-items: flex-start;
      gap: 12px;
      margin-bottom: 16px;
      font-size: 0.8125rem;
    }

    /* Print Document Styles */
    @media print {
      body * {
        visibility: hidden;
      }
      #print-statutory-container, #print-statutory-container * {
        visibility: visible;
      }
      #print-statutory-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: #ffffff !important;
        color: #000000 !important;
        padding: 24px;
        font-family: 'Times New Roman', Times, serif;
      }
      .no-print {
        display: none !important;
      }
      @page {
        margin: 1.5cm;
        size: A4 portrait;
      }
    }

    .official-letterhead {
      text-align: center;
      border-bottom: 2px solid #000000;
      padding-bottom: 12px;
      margin-bottom: 20px;
    }

    .official-letterhead p {
      margin: 2px 0;
      font-size: 10pt;
    }

    .official-letterhead h3 {
      margin: 4px 0;
      font-size: 13pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .signatory-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 30px;
      margin-top: 40px;
      text-align: center;
    }

    .signatory-box {
      border-top: 1px solid #000;
      padding-top: 6px;
      font-size: 9pt;
    }
  </style>
</head>
<body class="app-layout">
  <div class="app-shell">
    <!-- Sidebar Mount -->
    <div id="sidebar-mount"></div>

    <!-- Main Workspace Container -->
    <div class="app-main">
      <div id="mobile-header-mount"></div>
      <div id="app-topbar-mount"></div>

      <main class="app-content">
        <!-- Page Hero Section -->
        <section class="page-hero">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--spacing-md);">
            <div>
              <div style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-xs);">
                <h1 class="typography-heading-2">Sangguniang Barangay Legislative Tracking.</h1>
                <span class="badge-neutral" style="display: inline-flex; align-items: center; gap: 6px;">
                  <span style="width: 6px; height: 6px; border-radius: 50%; background: #8b5cf6;"></span>
                  RA 7160 &bull; Local Government Code
                </span>
                <span class="badge-neutral" style="font-size: 0.6875rem;">DILG Standards</span>
              </div>
              <p class="typography-caption" style="max-width: 780px;">
                Legislative tracking and statutory ordinance management system. Administers the 3-Reading legislative process, Sangguniang Barangay session minutes, roll call quorum validation, 3-conspicuous-places posting gazettes (Sec. 59), and City Council 30-day review tracking (Sec. 57).
              </p>
            </div>

            <!-- Global Action Controls -->
            <div style="display: flex; gap: var(--spacing-xs); flex-wrap: wrap;">
              <button type="button" class="button-outline" onclick="openNewSessionModal();" style="font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                + Record Session &amp; Roll Call
              </button>
              <button type="button" class="button-primary" onclick="openNewDocModal();" style="font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                + New Ordinance / Resolution
              </button>
            </div>
          </div>
        </section>

        <!-- Statutory Callout -->
        <div class="statutory-callout">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
          </svg>
          <div>
            <strong>RA 7160 Statutory Mandate:</strong> The Sangguniang Barangay is the legislative body of the barangay. Quorum requires a majority of all members (&ge; 5 out of 9). Ordinances take effect 10 days after copies are posted in at least three (3) conspicuous places (Barangay Hall, Market, Plaza/Health Center) and transmitted within 8&ndash;10 days to the Sangguniang Panlungsod/Bayan for review.
          </div>
        </div>

        <!-- Telemetry Stats Ladder -->
        <section class="stats-ladder">
          <!-- Stat 1: Enacted Ordinances -->
          <div class="studio-card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <span class="typography-caption">Enacted Ordinances</span>
              <span class="badge-neutral" style="font-size: 0.625rem;">3 Readings</span>
            </div>
            <div class="typography-heading-2" style="margin: 6px 0 2px;" id="stat-ordinances-count">0</div>
            <div class="typography-caption" style="font-size: 0.6875rem; color: var(--color-text-muted);">Local penal &amp; regulatory laws</div>
          </div>

          <!-- Stat 2: Passed Resolutions -->
          <div class="studio-card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <span class="typography-caption">Adopted Resolutions</span>
              <span class="badge-neutral" style="font-size: 0.625rem;">Policy &amp; AIP</span>
            </div>
            <div class="typography-heading-2" style="margin: 6px 0 2px;" id="stat-resolutions-count">0</div>
            <div class="typography-caption" style="font-size: 0.6875rem; color: var(--color-text-muted);">Official expressions &amp; actions</div>
          </div>

          <!-- Stat 3: Sessions Held & Quorum Rate -->
          <div class="studio-card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <span class="typography-caption">Sangguniang Sessions</span>
              <span class="badge-neutral" style="font-size: 0.625rem;" id="stat-quorum-rate">100% Quorum</span>
            </div>
            <div class="typography-heading-2" style="margin: 6px 0 2px;" id="stat-sessions-count">0</div>
            <div class="typography-caption" style="font-size: 0.6875rem; color: var(--color-text-muted);">Mandatory twice monthly (Sec. 52)</div>
          </div>

          <!-- Stat 4: City Council Review Status -->
          <div class="studio-card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <span class="typography-caption">City Council Review</span>
              <span class="badge-neutral" style="font-size: 0.625rem;">Sec. 57</span>
            </div>
            <div class="typography-heading-2" style="margin: 6px 0 2px;" id="stat-city-review-count">0</div>
            <div class="typography-caption" style="font-size: 0.6875rem; color: var(--color-text-muted);">Transmitted to Sanggunian</div>
          </div>
        </section>

        <!-- Studio Tab Navigation -->
        <nav class="leg-tab-nav" aria-label="Legislation Navigation">
          <button type="button" class="leg-tab-btn active" id="tab-btn-docket" onclick="switchLegTab('docket');">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            Legislative Docket &amp; Registry
          </button>
          <button type="button" class="leg-tab-btn" id="tab-btn-sessions" onclick="switchLegTab('sessions');">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Session Journal &amp; Roll Call
          </button>
          <button type="button" class="leg-tab-btn" id="tab-btn-drafter" onclick="switchLegTab('drafter');">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Drafting Studio &amp; Readings
          </button>
          <button type="button" class="leg-tab-btn" id="tab-btn-compliance" onclick="switchLegTab('compliance');">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Statutory Posting &amp; Gazettes
          </button>
        </nav>

        <!-- ======================================================= -->
        <!-- TAB 1: LEGISLATIVE DOCKET & REGULATORY REGISTRY         -->
        <!-- ======================================================= -->
        <div id="view-docket">
          <div class="studio-card">
            <!-- Filter Bar -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
              <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; flex: 1;">
                <input type="text" id="filter-search" class="text-input" placeholder="Search control no, title, sponsor, keywords..." style="max-width: 280px; height: 36px; font-size: 0.8125rem;" oninput="applyDocketFilters();">

                <select id="filter-doc-type" class="text-input" style="width: auto; height: 36px; font-size: 0.8125rem;" onchange="applyDocketFilters();">
                  <option value="">All Document Types</option>
                  <option value="Ordinance">Barangay Ordinances</option>
                  <option value="Resolution">Sangguniang Resolutions</option>
                </select>

                <select id="filter-stage" class="text-input" style="width: auto; height: 36px; font-size: 0.8125rem;" onchange="applyDocketFilters();">
                  <option value="">All Reading Stages</option>
                  <option value="1st Reading">1st Reading (Introduction)</option>
                  <option value="Committee Hearing">Committee Hearing</option>
                  <option value="2nd Reading">2nd Reading (Floor Debate)</option>
                  <option value="Enacted">3rd Reading / Enacted</option>
                </select>

                <select id="filter-city-review" class="text-input" style="width: auto; height: 36px; font-size: 0.8125rem;" onchange="applyDocketFilters();">
                  <option value="">All Review Statuses</option>
                  <option value="Pending Transmission">Pending Transmission</option>
                  <option value="Transmitted / Under Review">Transmitted / Under Review</option>
                  <option value="Approved / Lapsed into Law">Approved / Lapsed into Law</option>
                  <option value="Disapproved">Disapproved</option>
                </select>
              </div>

              <div style="font-size: 0.75rem; color: var(--color-text-muted);">
                Showing <strong id="docket-count">0</strong> documents
              </div>
            </div>

            <!-- Documents Table -->
            <div class="data-table-wrap">
              <table class="data-table" id="docket-table">
                <thead>
                  <tr>
                    <th>Control No.</th>
                    <th>Type</th>
                    <th>Title &amp; Subject Matter</th>
                    <th>Principal Sponsor</th>
                    <th>Reading Stage</th>
                    <th>City Council Review</th>
                    <th>Date Enacted</th>
                    <th style="text-align: right;">Actions</th>
                  </tr>
                </thead>
                <tbody id="docket-table-body">
                  <!-- Dynamically populated -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ======================================================= -->
        <!-- TAB 2: SESSION JOURNAL & ROLL CALL                      -->
        <!-- ======================================================= -->
        <div id="view-sessions" style="display: none;">
          <div class="studio-card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
              <div>
                <h3 class="typography-heading-3" style="font-size: 1rem;">Sangguniang Barangay Session Journal (RA 7160 Sec. 49-53)</h3>
                <p style="font-size: 0.75rem; color: var(--color-text-muted);">
                  Official record of proceedings, roll call attendance, quorum certification, and Order of Business deliberations.
                </p>
              </div>
              <button type="button" class="button-primary" onclick="openNewSessionModal();" style="font-size: 0.8125rem; height: 34px;">
                + Record New Session
              </button>
            </div>

            <div class="data-table-wrap">
              <table class="data-table" id="sessions-table">
                <thead>
                  <tr>
                    <th>Session No.</th>
                    <th>Session Type</th>
                    <th>Date &amp; Time</th>
                    <th>Presiding Officer</th>
                    <th>Roll Call / Quorum</th>
                    <th>Agenda &amp; Business</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                  </tr>
                </thead>
                <tbody id="sessions-table-body">
                  <!-- Dynamically populated -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ======================================================= -->
        <!-- TAB 3: DRAFTING STUDIO & READING PROGRESSION            -->
        <!-- ======================================================= -->
        <div id="view-drafter" style="display: none;">
          <div class="studio-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <div>
                <h3 class="typography-heading-3" style="font-size: 1rem;">Legislative Drafter Studio</h3>
                <p style="font-size: 0.75rem; color: var(--color-text-muted);">
                  Draft official ordinances and resolutions following statutory enacting formulas.
                </p>
              </div>
              <span class="badge-neutral" style="font-size: 0.75rem;">3-Reading Lifecycle</span>
            </div>

            <div class="reading-stepper">
              <div class="step-node active">
                <div class="step-circle">1</div>
                <span>1st Reading: Title &amp; Committee Referral</span>
              </div>
              <div class="step-arrow">&rarr;</div>
              <div class="step-node">
                <div class="step-circle">2</div>
                <span>Committee Hearing &amp; Report</span>
              </div>
              <div class="step-arrow">&rarr;</div>
              <div class="step-node">
                <div class="step-circle">3</div>
                <span>2nd Reading: Floor Debate &amp; Amendments</span>
              </div>
              <div class="step-arrow">&rarr;</div>
              <div class="step-node">
                <div class="step-circle">4</div>
                <span>3rd Reading: Roll-Call Enactment &amp; Posting</span>
              </div>
            </div>

            <form id="studio-drafter-form" onsubmit="handleStudioDrafterSubmit(event);">
              <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                  <label class="form-label" for="draft-type">Document Classification *</label>
                  <select id="draft-type" class="text-input" required onchange="handleDraftTypeChange();">
                    <option value="Ordinance">Barangay Ordinance (Law with Sanctions)</option>
                    <option value="Resolution">Sangguniang Resolution (Policy/Adoption)</option>
                  </select>
                </div>
                <div>
                  <label class="form-label" for="draft-control">Control Number (Optional / Auto)</label>
                  <input type="text" id="draft-control" class="text-input" placeholder="e.g. ORD-2026-004">
                </div>
                <div>
                  <label class="form-label" for="draft-stage">Initial Reading Stage *</label>
                  <select id="draft-stage" class="text-input" required>
                    <option value="1st Reading">1st Reading (Introduction)</option>
                    <option value="Committee Hearing">Committee Hearing</option>
                    <option value="2nd Reading">2nd Reading (Floor Debate)</option>
                    <option value="Enacted">3rd Reading / Enacted</option>
                  </select>
                </div>
              </div>

              <div style="margin-bottom: 14px;">
                <label class="form-label" for="draft-title">Official Title / Subject Matter *</label>
                <input type="text" id="draft-title" class="text-input" placeholder="e.g., An Ordinance Regulating The Operation Of Videoke Machines..." required>
              </div>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                  <label class="form-label" for="draft-sponsor">Principal Sponsor (Kagawad) *</label>
                  <input type="text" id="draft-sponsor" class="text-input" placeholder="e.g., Kag. Benjamin Alcantara (Chair on Laws)" required>
                </div>
                <div>
                  <label class="form-label" for="draft-cosponsors">Co-Sponsors</label>
                  <input type="text" id="draft-cosponsors" class="text-input" placeholder="e.g., Kag. Ramon Santos, SK Chair Joshua Ramos">
                </div>
              </div>

              <div id="draft-penalties-wrap" style="margin-bottom: 14px;">
                <label class="form-label" for="draft-penalties">Penal Provisions &amp; Sanctions (RA 7160 Sec. 516 / Sec. 391 max &#8369;1,000 fine)</label>
                <textarea id="draft-penalties" class="text-input" rows="2" placeholder="1st Offense: Warning; 2nd Offense: &#8369;500.00 fine; 3rd Offense: &#8369;1,000.00 fine or 8 hours community service."></textarea>
              </div>

              <div style="margin-bottom: 14px;">
                <label class="form-label" for="draft-body">Document Text / Enacting Body *</label>
                <textarea id="draft-body" class="text-input" rows="7" placeholder="BE IT ORDAINED, by the Sangguniang Barangay in session assembled:&#10;&#10;SECTION 1. TITLE. ...&#10;SECTION 2. POLICY. ...&#10;SECTION 3. PROVISIONS. ..." required></textarea>
              </div>

              <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs);">
                <button type="reset" class="button-outline">Clear Drafter</button>
                <button type="submit" class="button-primary">Save Document to Docket &rarr;</button>
              </div>
            </form>
          </div>
        </div>

        <!-- ======================================================= -->
        <!-- TAB 4: STATUTORY COMPLIANCE & POSTING GAZETTES          -->
        <!-- ======================================================= -->
        <div id="view-compliance" style="display: none;">
          <div class="studio-card">
            <div style="margin-bottom: 16px;">
              <h3 class="typography-heading-3" style="font-size: 1rem;">Statutory Posting &amp; Review Compliance Dashboard</h3>
              <p style="font-size: 0.75rem; color: var(--color-text-muted);">
                Tracking statutory compliance with RA 7160 Section 59 (Posting in 3 conspicuous places) and Section 57 (Transmittal to Sangguniang Panlungsod/Bayan).
              </p>
            </div>

            <div class="data-table-wrap">
              <table class="data-table" id="compliance-table">
                <thead>
                  <tr>
                    <th>Control No.</th>
                    <th>Document Title</th>
                    <th>Posting Status (3 Places)</th>
                    <th>Posting Date</th>
                    <th>Effectivity Date</th>
                    <th>City Council Status</th>
                    <th style="text-align: right;">Compliance Action</th>
                  </tr>
                </thead>
                <tbody id="compliance-table-body">
                  <!-- Dynamically populated -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- ======================================================= -->
  <!-- MODAL 1: CREATE / EDIT DOCUMENT MODAL                   -->
  <!-- ======================================================= -->
  <dialog id="doc-modal" class="modal-dialog" style="max-width: 650px; width: 92%; border-radius: var(--rounded-md); border: 1px solid var(--color-hairline); background: var(--color-canvas); padding: 0; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
    <div style="padding: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); display: flex; justify-content: space-between; align-items: center;">
      <h3 class="typography-heading-3" style="font-size: 1.0625rem;" id="doc-modal-title">New Legislative Document</h3>
      <button type="button" class="icon-button" onclick="document.getElementById('doc-modal').close();">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form id="doc-form" onsubmit="handleDocFormSubmit(event);" style="padding: var(--spacing-md);">
      <input type="hidden" id="modal-doc-id">

      <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
        <div>
          <label class="form-label" for="modal-doc-type">Type *</label>
          <select id="modal-doc-type" class="text-input" required>
            <option value="Ordinance">Barangay Ordinance</option>
            <option value="Resolution">Resolution</option>
          </select>
        </div>
        <div>
          <label class="form-label" for="modal-control-number">Control No.</label>
          <input type="text" id="modal-control-number" class="text-input" placeholder="ORD-2026-XXXX">
        </div>
        <div>
          <label class="form-label" for="modal-reading-stage">Reading Stage *</label>
          <select id="modal-reading-stage" class="text-input" required>
            <option value="1st Reading">1st Reading</option>
            <option value="Committee Hearing">Committee Hearing</option>
            <option value="2nd Reading">2nd Reading</option>
            <option value="Enacted">3rd Reading / Enacted</option>
          </select>
        </div>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="modal-title">Document Title *</label>
        <input type="text" id="modal-title" class="text-input" placeholder="Title of Ordinance or Resolution" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
        <div>
          <label class="form-label" for="modal-sponsor">Principal Sponsor *</label>
          <input type="text" id="modal-sponsor" class="text-input" placeholder="Hon. Kagawad..." required>
        </div>
        <div>
          <label class="form-label" for="modal-cosponsors">Co-Sponsors</label>
          <input type="text" id="modal-cosponsors" class="text-input" placeholder="Kagawads...">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
        <div>
          <label class="form-label" for="modal-date-enacted">Date Enacted</label>
          <input type="date" id="modal-date-enacted" class="text-input">
        </div>
        <div>
          <label class="form-label" for="modal-date-posted">Date Posted</label>
          <input type="date" id="modal-date-posted" class="text-input">
        </div>
        <div>
          <label class="form-label" for="modal-effectivity-date">Effectivity Date</label>
          <input type="date" id="modal-effectivity-date" class="text-input">
        </div>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="modal-posting-locations">Posting Locations (RA 7160 Sec. 59)</label>
        <input type="text" id="modal-posting-locations" class="text-input" value="Barangay Hall Bulletin, Public Market, Health Center">
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="modal-penalties">Penal Provisions / Sanctions</label>
        <textarea id="modal-penalties" class="text-input" rows="2" placeholder="Fines, community service, or compliance mandates..."></textarea>
      </div>

      <div style="margin-bottom: 14px;">
        <label class="form-label" for="modal-body">Document Text / Provisions *</label>
        <textarea id="modal-body" class="text-input" rows="5" placeholder="Full statutory provisions and enacting clauses..." required></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs);">
        <button type="button" class="button-outline" onclick="document.getElementById('doc-modal').close();">Cancel</button>
        <button type="submit" class="button-primary">Save Document</button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================= -->
  <!-- MODAL 2: RECORD / EDIT SESSION & ROLL CALL              -->
  <!-- ======================================================= -->
  <dialog id="session-modal" class="modal-dialog" style="max-width: 680px; width: 92%; border-radius: var(--rounded-md); border: 1px solid var(--color-hairline); background: var(--color-canvas); padding: 0; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
    <div style="padding: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); display: flex; justify-content: space-between; align-items: center;">
      <h3 class="typography-heading-3" style="font-size: 1.0625rem;" id="session-modal-title">Record Sangguniang Session</h3>
      <button type="button" class="icon-button" onclick="document.getElementById('session-modal').close();">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form id="session-form" onsubmit="handleSessionFormSubmit(event);" style="padding: var(--spacing-md);">
      <input type="hidden" id="modal-session-id">

      <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
        <div>
          <label class="form-label" for="modal-session-number">Session Number *</label>
          <input type="text" id="modal-session-number" class="text-input" placeholder="RS-2026-017" required>
        </div>
        <div>
          <label class="form-label" for="modal-session-type">Session Type *</label>
          <select id="modal-session-type" class="text-input" required>
            <option value="Regular Session">Regular Session</option>
            <option value="Special Session">Special Session</option>
            <option value="Committee Hearing">Committee Hearing</option>
          </select>
        </div>
        <div>
          <label class="form-label" for="modal-session-status">Status *</label>
          <select id="modal-session-status" class="text-input" required>
            <option value="Approved">Approved Journal</option>
            <option value="Draft">Draft Minutes</option>
          </select>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px;">
        <div>
          <label class="form-label" for="modal-session-date">Date *</label>
          <input type="date" id="modal-session-date" class="text-input" required>
        </div>
        <div>
          <label class="form-label" for="modal-session-time">Time</label>
          <input type="time" id="modal-session-time" class="text-input" value="09:00">
        </div>
        <div>
          <label class="form-label" for="modal-presiding-officer">Presiding Officer *</label>
          <input type="text" id="modal-presiding-officer" class="text-input" value="Hon. Antonio S. Valdez (Punong Barangay)" required>
        </div>
      </div>

      <!-- Roll Call Roster -->
      <div style="margin-bottom: 14px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <label class="form-label" style="margin-bottom: 0;">Roll Call Attendance (RA 7160 Sec. 53 Quorum Check)</label>
          <div id="modal-quorum-badge" class="badge-neutral" style="font-size: 0.6875rem;">9 / 9 Present &bull; Quorum Present</div>
        </div>
        <div class="roll-call-grid" id="modal-roll-call-container">
          <!-- Dynamically populated 9 official checkboxes -->
        </div>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="modal-agenda">Order of Business &amp; Agenda *</label>
        <textarea id="modal-agenda" class="text-input" rows="2" placeholder="I. Call to Order&#10;II. Roll Call &amp; Quorum&#10;III. Reading of Previous Minutes&#10;IV. Unfinished Business..." required></textarea>
      </div>

      <div style="margin-bottom: 14px;">
        <label class="form-label" for="modal-minutes">Minutes &amp; Deliberations Summary *</label>
        <textarea id="modal-minutes" class="text-input" rows="4" placeholder="Summary of discussions, motions made, and legislative measures acted upon..." required></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs);">
        <button type="button" class="button-outline" onclick="document.getElementById('session-modal').close();">Cancel</button>
        <button type="submit" class="button-primary">Save Session Journal</button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================= -->
  <!-- MODAL 3: ADVANCE READING STAGE MODAL                    -->
  <!-- ======================================================= -->
  <dialog id="stage-modal" class="modal-dialog" style="max-width: 480px; width: 90%; border-radius: var(--rounded-md); border: 1px solid var(--color-hairline); background: var(--color-canvas); padding: 0; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
    <div style="padding: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); display: flex; justify-content: space-between; align-items: center;">
      <h3 class="typography-heading-3" style="font-size: 1.0625rem;">Advance Reading Stage</h3>
      <button type="button" class="icon-button" onclick="document.getElementById('stage-modal').close();">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form id="stage-form" onsubmit="handleStageFormSubmit(event);" style="padding: var(--spacing-md);">
      <input type="hidden" id="stage-doc-id">

      <div style="background: var(--color-canvas-soft); border: 1px solid var(--color-hairline-soft); padding: 12px; border-radius: var(--rounded-sm); margin-bottom: 14px; font-size: 0.8125rem;">
        <div style="color: var(--color-text-muted); font-size: 0.6875rem;">DOCUMENT</div>
        <div style="font-weight: 700; color: var(--color-ink);" id="stage-doc-title">--</div>
        <div style="font-size: 0.75rem; color: var(--color-text-muted);" id="stage-doc-control">--</div>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="stage-next-stage">Target Reading Stage *</label>
        <select id="stage-next-stage" class="text-input" required onchange="handleTargetStageChange();">
          <option value="Committee Hearing">Committee Hearing &amp; Report</option>
          <option value="2nd Reading">2nd Reading (Floor Debate &amp; Amendments)</option>
          <option value="Enacted">3rd Reading / Enactment (Final Approval)</option>
        </select>
      </div>

      <div id="stage-enactment-fields" style="display: none;">
        <div style="margin-bottom: 12px;">
          <label class="form-label" for="stage-date-enacted">Date Enacted *</label>
          <input type="date" id="stage-date-enacted" class="text-input">
        </div>
        <div style="margin-bottom: 12px;">
          <label class="form-label" for="stage-date-posted">Date Posted (3 Places) *</label>
          <input type="date" id="stage-date-posted" class="text-input">
        </div>
        <div style="margin-bottom: 12px;">
          <label class="form-label" for="stage-effectivity-date">Effectivity Date (10 days after posting per Sec. 59)</label>
          <input type="date" id="stage-effectivity-date" class="text-input">
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs);">
        <button type="button" class="button-outline" onclick="document.getElementById('stage-modal').close();">Cancel</button>
        <button type="submit" class="button-primary">Confirm Stage Transition</button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================= -->
  <!-- MODAL 4: UPDATE CITY COUNCIL REVIEW MODAL               -->
  <!-- ======================================================= -->
  <dialog id="review-modal" class="modal-dialog" style="max-width: 480px; width: 90%; border-radius: var(--rounded-md); border: 1px solid var(--color-hairline); background: var(--color-canvas); padding: 0; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
    <div style="padding: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); display: flex; justify-content: space-between; align-items: center;">
      <h3 class="typography-heading-3" style="font-size: 1.0625rem;">Update Sangguniang Review</h3>
      <button type="button" class="icon-button" onclick="document.getElementById('review-modal').close();">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form id="review-form" onsubmit="handleReviewFormSubmit(event);" style="padding: var(--spacing-md);">
      <input type="hidden" id="review-doc-id">

      <div style="background: var(--color-canvas-soft); border: 1px solid var(--color-hairline-soft); padding: 12px; border-radius: var(--rounded-sm); margin-bottom: 14px; font-size: 0.8125rem;">
        <div style="color: var(--color-text-muted); font-size: 0.6875rem;">ORDINANCE / MEASURE</div>
        <div style="font-weight: 700; color: var(--color-ink);" id="review-doc-title">--</div>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="review-status">Review Status (RA 7160 Sec. 57) *</label>
        <select id="review-status" class="text-input" required>
          <option value="Transmitted / Under Review">Transmitted / Under Review (Within 30 Days)</option>
          <option value="Approved / Lapsed into Law">Approved / Lapsed into Law (Declared Valid)</option>
          <option value="Pending Transmission">Pending Transmission (Within 8-10 Days)</option>
          <option value="Disapproved">Disapproved / Inconsistent with Law</option>
        </select>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="review-transmitted-date">Date Transmitted to City/Municipal Council</label>
        <input type="date" id="review-transmitted-date" class="text-input">
      </div>

      <div style="margin-bottom: 14px;">
        <label class="form-label" for="review-action-date">Council Action Date</label>
        <input type="date" id="review-action-date" class="text-input">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs);">
        <button type="button" class="button-outline" onclick="document.getElementById('review-modal').close();">Cancel</button>
        <button type="submit" class="button-primary">Save Review Status</button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================= -->
  <!-- STATUTORY PRINT CONTAINER (OFFICIAL RA 7160 FORMS)      -->
  <!-- ======================================================= -->
  <div id="print-statutory-container" style="display: none;">
    <!-- Injected dynamically via print engine -->
  </div>

  <!-- Scripts: REST API Client Bridge for PHP/MySQL Parity -->
  <script src="js/api.js"></script>
  <script src="js/auth.js"></script>
  <script src="js/components/sidebar.js"></script>
  <script src="js/components/header.js"></script>

  <script>
    // State management
    let allDocuments = [];
    let allSessions = [];

    // Official 9 Council Members for Quorum & Roll Call
    const councilMembers = [
      { id: 'pb', name: 'Hon. Antonio S. Valdez', role: 'Punong Barangay (Presiding Officer)' },
      { id: 'k1', name: 'Hon. Benjamin Alcantara', role: 'Barangay Kagawad (Committee on Laws)' },
      { id: 'k2', name: 'Hon. Ramon Santos', role: 'Barangay Kagawad (Peace & Order)' },
      { id: 'k3', name: 'Hon. Teresa Lim', role: 'Barangay Kagawad (Appropriations)' },
      { id: 'k4', name: 'Hon. Elena Ramos', role: 'Barangay Kagawad (Health & Sanitation)' },
      { id: 'k5', name: 'Hon. Carlos Bautista', role: 'Barangay Kagawad (Public Works)' },
      { id: 'k6', name: 'Hon. Sofia Mendoza', role: 'Barangay Kagawad (Women & Family)' },
      { id: 'k7', name: 'Hon. Danilo Cruz', role: 'Barangay Kagawad (Environment)' },
      { id: 'sk', name: 'Hon. Joshua Ramos', role: 'SK Chairperson (Youth & Sports)' }
    ];

    document.addEventListener('DOMContentLoaded', async () => {
      // 1. App Shell Sidebar
      if (window.AppSidebar) {
        await AppSidebar.render('legislation');
      }

      // 2. Populate Roll Call Member Checkboxes in Modal
      populateModalRollCall();

      // 3. Load all data via REST API bridge & render
      await refreshAllData();
    });

    // Tab Switcher
    window.switchLegTab = function(tabName) {
      const tabs = ['docket', 'sessions', 'drafter', 'compliance'];
      tabs.forEach(t => {
        const pane = document.getElementById(`view-${t}`);
        const btn = document.getElementById(`tab-btn-${t}`);
        if (pane) pane.style.display = (t === tabName) ? 'block' : 'none';
        if (btn) btn.classList.toggle('active', t === tabName);
      });
    };

    // Populate Roll Call Checkboxes
    function populateModalRollCall() {
      const container = document.getElementById('modal-roll-call-container');
      if (!container) return;

      container.innerHTML = councilMembers.map(m => `
        <div class="roll-call-item">
          <div>
            <div class="roll-call-name">${m.name}</div>
            <div class="roll-call-role">${m.role}</div>
          </div>
          <input type="checkbox" id="rc-${m.id}" name="roll_call_member" value="${m.name}" checked onchange="updateModalQuorumDisplay();">
        </div>
      `).join('');
    }

    window.updateModalQuorumDisplay = function() {
      const checkedCount = document.querySelectorAll('input[name="roll_call_member"]:checked').length;
      const totalCount = councilMembers.length;
      const isQuorum = checkedCount >= 5; // Majority of 9 is 5
      const badge = document.getElementById('modal-quorum-badge');
      if (badge) {
        badge.innerHTML = `${checkedCount} / ${totalCount} Present &bull; ${isQuorum ? 'Quorum Present' : 'No Quorum (Under 5)'}`;
        badge.style.color = isQuorum ? '#10b981' : '#ef4444';
      }
    };

    // Refresh All Data
    async function refreshAllData() {
      try {
        allDocuments = await barangayDB.getAll('legislative_documents');
        allSessions  = await barangayDB.getAll('legislative_sessions');

        renderStats();
        applyDocketFilters();
        renderSessionsTable();
        renderComplianceTable();
      } catch (err) {
        console.error('Error refreshing legislative data:', err);
      }
    }

    // Render Stats
    function renderStats() {
      const ordinances = allDocuments.filter(d => (d.docType || d.doc_type) === 'Ordinance');
      const resolutions = allDocuments.filter(d => (d.docType || d.doc_type) === 'Resolution');
      const underReview = allDocuments.filter(d => (d.cityCouncilReviewStatus || d.city_council_review_status) === 'Transmitted / Under Review');

      document.getElementById('stat-ordinances-count').innerText = ordinances.length;
      document.getElementById('stat-resolutions-count').innerText = resolutions.length;
      document.getElementById('stat-sessions-count').innerText = allSessions.length;
      document.getElementById('stat-city-review-count').innerText = underReview.length;

      const quorumMetCount = allSessions.filter(s => (s.quorumStatus || s.quorum_status) === 'Quorum Present').length;
      const rate = allSessions.length > 0 ? Math.round((quorumMetCount / allSessions.length) * 100) : 100;
      document.getElementById('stat-quorum-rate').innerText = `${rate}% Quorum`;
    }

    // Filter & Render Docket Table
    window.applyDocketFilters = function() {
      const search = (document.getElementById('filter-search')?.value || '').toLowerCase();
      const typeFilter = document.getElementById('filter-doc-type')?.value || '';
      const stageFilter = document.getElementById('filter-stage')?.value || '';
      const reviewFilter = document.getElementById('filter-city-review')?.value || '';

      const filtered = allDocuments.filter(doc => {
        const type = doc.docType || doc.doc_type || '';
        const stage = doc.readingStage || doc.reading_stage || '';
        const review = doc.cityCouncilReviewStatus || doc.city_council_review_status || '';
        const control = (doc.controlNumber || doc.control_number || '').toLowerCase();
        const title = (doc.title || '').toLowerCase();
        const sponsor = (doc.sponsorName || doc.sponsor_name || '').toLowerCase();

        if (typeFilter && type !== typeFilter) return false;
        if (stageFilter && stage !== stageFilter) return false;
        if (reviewFilter && review !== reviewFilter) return false;

        if (search) {
          return control.includes(search) || title.includes(search) || sponsor.includes(search);
        }
        return true;
      });

      document.getElementById('docket-count').innerText = filtered.length;
      const tbody = document.getElementById('docket-table-body');
      if (!tbody) return;

      if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: var(--color-text-muted); padding: 24px;">No legislative documents found matching filters.</td></tr>`;
        return;
      }

      tbody.innerHTML = filtered.map(doc => {
        const id = doc.id;
        const control = doc.controlNumber || doc.control_number || 'PENDING';
        const type = doc.docType || doc.doc_type || 'Ordinance';
        const title = doc.title || 'Untitled Measure';
        const sponsor = doc.sponsorName || doc.sponsor_name || 'Sangguniang Barangay';
        const stage = doc.readingStage || doc.reading_stage || '1st Reading';
        const review = doc.cityCouncilReviewStatus || doc.city_council_review_status || 'Pending Transmission';
        const dateEnacted = doc.dateEnacted || doc.date_enacted || '&mdash;';

        const stageBadge = (stage === 'Enacted') 
          ? `<span class="badge-neutral" style="background: rgba(16, 185, 129, 0.12); color: #10b981; font-weight: 600;">Enacted (Passed)</span>`
          : `<span class="badge-neutral" style="color: #f59e0b; font-weight: 600;">${stage}</span>`;

        let reviewBadge = `<span class="badge-neutral">${review}</span>`;
        if (review === 'Approved / Lapsed into Law') {
          reviewBadge = `<span class="badge-neutral" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">&check; Approved</span>`;
        } else if (review === 'Transmitted / Under Review') {
          reviewBadge = `<span class="badge-neutral" style="background: rgba(139, 92, 246, 0.12); color: #8b5cf6;">Under 30d Review</span>`;
        } else if (review === 'Disapproved') {
          reviewBadge = `<span class="badge-neutral" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">Disapproved</span>`;
        }

        return `
          <tr>
            <td><strong>${control}</strong></td>
            <td><span class="badge-neutral">${type}</span></td>
            <td style="max-width: 260px;">
              <div style="font-weight: 600; line-height: 1.3;">${title}</div>
            </td>
            <td style="font-size: 0.75rem; color: var(--color-text-muted);">${sponsor}</td>
            <td>${stageBadge}</td>
            <td>${reviewBadge}</td>
            <td>${dateEnacted}</td>
            <td style="text-align: right; white-space: nowrap;">
              <button type="button" class="button-outline" style="padding: 4px 8px; font-size: 0.6875rem;" onclick="printOfficialDocument(${id});" title="Print Official Statutory Document">
                Print Form &nearr;
              </button>
              ${stage !== 'Enacted' ? `
                <button type="button" class="button-primary" style="padding: 4px 8px; font-size: 0.6875rem;" onclick="openStageModal(${id});" title="Advance Reading Stage">
                  Advance &rarr;
                </button>
              ` : `
                <button type="button" class="button-outline" style="padding: 4px 8px; font-size: 0.6875rem;" onclick="printPostingCertificate(${id});" title="Print Certificate of Posting (Sec. 59)">
                  Posting Cert
                </button>
              `}
              <button type="button" class="button-outline" style="padding: 4px 8px; font-size: 0.6875rem;" onclick="openEditDocModal(${id});">
                Edit
              </button>
            </td>
          </tr>
        `;
      }).join('');
    };

    // Render Sessions Table
    function renderSessionsTable() {
      const tbody = document.getElementById('sessions-table-body');
      if (!tbody) return;

      if (allSessions.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: var(--color-text-muted); padding: 24px;">No session journals recorded yet.</td></tr>`;
        return;
      }

      tbody.innerHTML = allSessions.map(s => {
        const id = s.id;
        const number = s.sessionNumber || s.session_number;
        const type = s.sessionType || s.session_type;
        const date = s.sessionDate || s.session_date;
        const time = s.sessionTime || s.session_time || '';
        const officer = s.presidingOfficer || s.presiding_officer;
        const present = s.presentCount !== undefined ? s.presentCount : s.present_count;
        const total = s.totalMembers !== undefined ? s.totalMembers : (s.total_members || 9);
        const quorum = s.quorumStatus || s.quorum_status;
        const agenda = s.agendaTopics || s.agenda_topics || '';
        const status = s.sessionStatus || s.session_status || 'Approved';

        return `
          <tr>
            <td><strong>${number}</strong></td>
            <td><span class="badge-neutral">${type}</span></td>
            <td>${date} <span style="font-size: 0.6875rem; color: var(--color-text-muted);">${time}</span></td>
            <td style="font-size: 0.75rem;">${officer}</td>
            <td>
              <div style="font-weight: 600;">${present} / ${total} Present</div>
              <div style="font-size: 0.6875rem; color: ${quorum === 'Quorum Present' ? '#10b981' : '#ef4444'};">${quorum}</div>
            </td>
            <td style="max-width: 220px; font-size: 0.75rem; color: var(--color-text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
              ${agenda.split('\n')[0] || agenda}
            </td>
            <td>
              <span class="badge-neutral" style="${status === 'Approved' ? 'background: rgba(16, 185, 129, 0.12); color: #10b981;' : ''}">${status}</span>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <button type="button" class="button-outline" style="padding: 4px 8px; font-size: 0.6875rem;" onclick="printSessionMinutes(${id});">
                Extract Minutes &nearr;
              </button>
              <button type="button" class="button-outline" style="padding: 4px 8px; font-size: 0.6875rem;" onclick="openEditSessionModal(${id});">
                Edit
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Render Compliance Table
    function renderComplianceTable() {
      const tbody = document.getElementById('compliance-table-body');
      if (!tbody) return;

      const enactedOrdinances = allDocuments.filter(d => (d.docType || d.doc_type) === 'Ordinance' && (d.readingStage || d.reading_stage) === 'Enacted');

      if (enactedOrdinances.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 24px;">No enacted ordinances requiring statutory gazette posting.</td></tr>`;
        return;
      }

      tbody.innerHTML = enactedOrdinances.map(doc => {
        const id = doc.id;
        const control = doc.controlNumber || doc.control_number;
        const title = doc.title;
        const locations = doc.postingLocations || doc.posting_locations || 'Barangay Hall, Market, Health Center';
        const datePosted = doc.datePosted || doc.date_posted || 'Pending';
        const effectivity = doc.effectivityDate || doc.effectivity_date || 'Pending';
        const review = doc.cityCouncilReviewStatus || doc.city_council_review_status || 'Pending Transmission';

        return `
          <tr>
            <td><strong>${control}</strong></td>
            <td style="max-width: 220px; font-weight: 600;">${title}</td>
            <td style="font-size: 0.75rem;">
              <div style="color: #10b981; font-weight: 600;">&check; 3 Places Verified</div>
              <div style="color: var(--color-text-muted); font-size: 0.6875rem;">${locations}</div>
            </td>
            <td>${datePosted}</td>
            <td><strong>${effectivity}</strong></td>
            <td>
              <span class="badge-neutral">${review}</span>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <button type="button" class="button-primary" style="padding: 4px 8px; font-size: 0.6875rem;" onclick="printPostingCertificate(${id});">
                Posting Cert (Sec. 59)
              </button>
              <button type="button" class="button-outline" style="padding: 4px 8px; font-size: 0.6875rem;" onclick="openReviewModal(${id});">
                Update Review (Sec. 57)
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Drafter Studio Type Change handler
    window.handleDraftTypeChange = function() {
      const type = document.getElementById('draft-type').value;
      const penaltiesWrap = document.getElementById('draft-penalties-wrap');
      if (penaltiesWrap) {
        penaltiesWrap.style.display = (type === 'Ordinance') ? 'block' : 'none';
      }
    };

    // Modal Document Openers
    window.openNewDocModal = function() {
      document.getElementById('modal-doc-id').value = '';
      document.getElementById('doc-modal-title').innerText = 'New Legislative Measure';
      document.getElementById('doc-form').reset();
      document.getElementById('doc-modal').showModal();
    };

    window.openEditDocModal = function(id) {
      const doc = allDocuments.find(d => d.id === id);
      if (!doc) return;

      document.getElementById('modal-doc-id').value = doc.id;
      document.getElementById('doc-modal-title').innerText = `Edit ${doc.docType || doc.doc_type} ${doc.controlNumber || doc.control_number}`;
      document.getElementById('modal-doc-type').value = doc.docType || doc.doc_type;
      document.getElementById('modal-control-number').value = doc.controlNumber || doc.control_number || '';
      document.getElementById('modal-reading-stage').value = doc.readingStage || doc.reading_stage;
      document.getElementById('modal-title').value = doc.title;
      document.getElementById('modal-sponsor').value = doc.sponsorName || doc.sponsor_name;
      document.getElementById('modal-cosponsors').value = doc.coSponsors || doc.co_sponsors || '';
      document.getElementById('modal-date-enacted').value = doc.dateEnacted || doc.date_enacted || '';
      document.getElementById('modal-date-posted').value = doc.datePosted || doc.date_posted || '';
      document.getElementById('modal-effectivity-date').value = doc.effectivityDate || doc.effectivity_date || '';
      document.getElementById('modal-posting-locations').value = doc.postingLocations || doc.posting_locations || 'Barangay Hall Bulletin, Public Market, Health Center';
      document.getElementById('modal-penalties').value = doc.sanctionsPenalties || doc.sanctions_penalties || '';
      document.getElementById('modal-body').value = doc.documentBody || doc.document_body || '';

      document.getElementById('doc-modal').showModal();
    };

    // Handle Doc Form Submit
    window.handleDocFormSubmit = async function(e) {
      e.preventDefault();
      const id = document.getElementById('modal-doc-id').value;
      const data = {
        docType: document.getElementById('modal-doc-type').value,
        controlNumber: document.getElementById('modal-control-number').value,
        readingStage: document.getElementById('modal-reading-stage').value,
        title: document.getElementById('modal-title').value,
        sponsorName: document.getElementById('modal-sponsor').value,
        coSponsors: document.getElementById('modal-cosponsors').value,
        dateEnacted: document.getElementById('modal-date-enacted').value || null,
        datePosted: document.getElementById('modal-date-posted').value || null,
        effectivityDate: document.getElementById('modal-effectivity-date').value || null,
        postingLocations: document.getElementById('modal-posting-locations').value,
        sanctionsPenalties: document.getElementById('modal-penalties').value,
        documentBody: document.getElementById('modal-body').value,
        cityCouncilReviewStatus: 'Pending Transmission'
      };

      try {
        if (id) {
          data.id = parseInt(id, 10);
          await barangayDB.update('legislative_documents', data);
        } else {
          await barangayDB.add('legislative_documents', data);
        }
        document.getElementById('doc-modal').close();
        await refreshAllData();
      } catch (err) {
        alert('Failed to save document: ' + err.message);
      }
    };

    // Handle Studio Drafter Submit
    window.handleStudioDrafterSubmit = async function(e) {
      e.preventDefault();
      const docType = document.getElementById('draft-type').value;
      const control = document.getElementById('draft-control').value;
      const stage = document.getElementById('draft-stage').value;
      const title = document.getElementById('draft-title').value;
      const sponsor = document.getElementById('draft-sponsor').value;
      const cosponsors = document.getElementById('draft-cosponsors').value;
      const penalties = document.getElementById('draft-penalties').value;
      const body = document.getElementById('draft-body').value;

      const record = {
        docType,
        controlNumber: control,
        readingStage: stage,
        title,
        sponsorName: sponsor,
        coSponsors: cosponsors,
        sanctionsPenalties: penalties,
        documentBody: body,
        cityCouncilReviewStatus: 'Pending Transmission',
        postingLocations: 'Barangay Hall Bulletin, Public Market, Health Center'
      };

      if (stage === 'Enacted') {
        const today = new Date().toISOString().split('T')[0];
        record.dateEnacted = today;
        record.datePosted = today;
        const effDate = new Date();
        effDate.setDate(effDate.getDate() + 10);
        record.effectivityDate = effDate.toISOString().split('T')[0];
      }

      try {
        await barangayDB.add('legislative_documents', record);
        alert(`${docType} successfully saved to the legislative docket!`);
        document.getElementById('studio-drafter-form').reset();
        await refreshAllData();
        switchLegTab('docket');
      } catch (err) {
        alert('Failed to save drafted document: ' + err.message);
      }
    };

    // Modal Session Openers
    window.openNewSessionModal = function() {
      document.getElementById('modal-session-id').value = '';
      document.getElementById('session-modal-title').innerText = 'Record Sangguniang Session';
      document.getElementById('session-form').reset();

      // Set default next session number
      const year = new Date().getFullYear();
      const nextNum = allSessions.length + 1;
      document.getElementById('modal-session-number').value = `RS-${year}-${String(nextNum).padStart(3, '0')}`;
      document.getElementById('modal-session-date').value = new Date().toISOString().split('T')[0];

      // Check all council members by default
      councilMembers.forEach(m => {
        const el = document.getElementById(`rc-${m.id}`);
        if (el) el.checked = true;
      });
      updateModalQuorumDisplay();

      document.getElementById('session-modal').showModal();
    };

    window.openEditSessionModal = function(id) {
      const sess = allSessions.find(s => s.id === id);
      if (!sess) return;

      document.getElementById('modal-session-id').value = sess.id;
      document.getElementById('session-modal-title').innerText = `Edit Session ${sess.sessionNumber || sess.session_number}`;
      document.getElementById('modal-session-number').value = sess.sessionNumber || sess.session_number;
      document.getElementById('modal-session-type').value = sess.sessionType || sess.session_type;
      document.getElementById('modal-session-status').value = sess.sessionStatus || sess.session_status || 'Approved';
      document.getElementById('modal-session-date').value = sess.sessionDate || sess.session_date;
      document.getElementById('modal-session-time').value = sess.sessionTime || sess.session_time || '09:00';
      document.getElementById('modal-presiding-officer').value = sess.presidingOfficer || sess.presiding_officer;
      document.getElementById('modal-agenda').value = sess.agendaTopics || sess.agenda_topics;
      document.getElementById('modal-minutes').value = sess.minutesSummary || sess.minutes_summary;

      const rawRoll = sess.rollCall || sess.roll_call;
      const rollArr = Array.isArray(rawRoll) ? rawRoll : (typeof rawRoll === 'string' ? JSON.parse(rawRoll || '[]') : []);

      councilMembers.forEach(m => {
        const el = document.getElementById(`rc-${m.id}`);
        if (el) {
          el.checked = rollArr.length > 0 ? rollArr.includes(m.name) : true;
        }
      });
      updateModalQuorumDisplay();

      document.getElementById('session-modal').showModal();
    };

    // Handle Session Form Submit
    window.handleSessionFormSubmit = async function(e) {
      e.preventDefault();
      const id = document.getElementById('modal-session-id').value;

      const checkedMembers = Array.from(document.querySelectorAll('input[name="roll_call_member"]:checked')).map(cb => cb.value);
      const presentCount = checkedMembers.length;
      const quorumStatus = presentCount >= 5 ? 'Quorum Present' : 'No Quorum';

      const data = {
        sessionNumber: document.getElementById('modal-session-number').value,
        sessionType: document.getElementById('modal-session-type').value,
        sessionStatus: document.getElementById('modal-session-status').value,
        sessionDate: document.getElementById('modal-session-date').value,
        sessionTime: document.getElementById('modal-session-time').value,
        presidingOfficer: document.getElementById('modal-presiding-officer').value,
        presentCount: presentCount,
        totalMembers: councilMembers.length,
        quorumStatus: quorumStatus,
        rollCall: checkedMembers,
        agendaTopics: document.getElementById('modal-agenda').value,
        minutesSummary: document.getElementById('modal-minutes').value
      };

      try {
        if (id) {
          data.id = parseInt(id, 10);
          await barangayDB.update('legislative_sessions', data);
        } else {
          await barangayDB.add('legislative_sessions', data);
        }
        document.getElementById('session-modal').close();
        await refreshAllData();
      } catch (err) {
        alert('Failed to save session: ' + err.message);
      }
    };

    // Advance Reading Stage Modal Handlers
    window.openStageModal = function(id) {
      const doc = allDocuments.find(d => d.id === id);
      if (!doc) return;

      document.getElementById('stage-doc-id').value = doc.id;
      document.getElementById('stage-doc-title').innerText = doc.title;
      document.getElementById('stage-doc-control').innerText = `${doc.docType || doc.doc_type} ${doc.controlNumber || doc.control_number} (Current: ${doc.readingStage || doc.reading_stage})`;

      const currentStage = doc.readingStage || doc.reading_stage;
      const nextSelect = document.getElementById('stage-next-stage');
      if (currentStage === '1st Reading') {
        nextSelect.value = 'Committee Hearing';
      } else if (currentStage === 'Committee Hearing') {
        nextSelect.value = '2nd Reading';
      } else {
        nextSelect.value = 'Enacted';
      }

      handleTargetStageChange();
      document.getElementById('stage-modal').showModal();
    };

    window.handleTargetStageChange = function() {
      const target = document.getElementById('stage-next-stage').value;
      const enFields = document.getElementById('stage-enactment-fields');
      if (target === 'Enacted') {
        enFields.style.display = 'block';
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('stage-date-enacted').value = today;
        document.getElementById('stage-date-posted').value = today;
        const effDate = new Date();
        effDate.setDate(effDate.getDate() + 10);
        document.getElementById('stage-effectivity-date').value = effDate.toISOString().split('T')[0];
      } else {
        enFields.style.display = 'none';
      }
    };

    window.handleStageFormSubmit = async function(e) {
      e.preventDefault();
      const id = parseInt(document.getElementById('stage-doc-id').value, 10);
      const doc = allDocuments.find(d => d.id === id);
      if (!doc) return;

      const nextStage = document.getElementById('stage-next-stage').value;
      doc.readingStage = nextStage;

      if (nextStage === 'Enacted') {
        doc.dateEnacted = document.getElementById('stage-date-enacted').value;
        doc.datePosted = document.getElementById('stage-date-posted').value;
        doc.effectivityDate = document.getElementById('stage-effectivity-date').value;
        doc.cityCouncilReviewStatus = 'Pending Transmission';
      }

      try {
        await barangayDB.update('legislative_documents', doc);
        document.getElementById('stage-modal').close();
        await refreshAllData();
      } catch (err) {
        alert('Failed to advance stage: ' + err.message);
      }
    };

    // Review Modal Handlers
    window.openReviewModal = function(id) {
      const doc = allDocuments.find(d => d.id === id);
      if (!doc) return;

      document.getElementById('review-doc-id').value = doc.id;
      document.getElementById('review-doc-title').innerText = `${doc.controlNumber || doc.control_number}: ${doc.title}`;
      document.getElementById('review-status').value = doc.cityCouncilReviewStatus || doc.city_council_review_status || 'Transmitted / Under Review';
      document.getElementById('review-transmitted-date').value = doc.cityCouncilTransmittedDate || doc.city_council_transmitted_date || '';
      document.getElementById('review-action-date').value = doc.cityCouncilActionDate || doc.city_council_action_date || '';

      document.getElementById('review-modal').showModal();
    };

    window.handleReviewFormSubmit = async function(e) {
      e.preventDefault();
      const id = parseInt(document.getElementById('review-doc-id').value, 10);
      const doc = allDocuments.find(d => d.id === id);
      if (!doc) return;

      doc.cityCouncilReviewStatus = document.getElementById('review-status').value;
      doc.cityCouncilTransmittedDate = document.getElementById('review-transmitted-date').value || null;
      doc.cityCouncilActionDate = document.getElementById('review-action-date').value || null;

      try {
        await barangayDB.update('legislative_documents', doc);
        document.getElementById('review-modal').close();
        await refreshAllData();
      } catch (err) {
        alert('Failed to update review status: ' + err.message);
      }
    };

    // =======================================================
    // STATUTORY PRINT GENERATORS (OFFICIAL RA 7160 FORMS)
    // =======================================================
    function getOfficialLetterhead(docTitle, subTitle = 'Office of the Sangguniang Barangay') {
      return `
        <div class="official-letterhead">
          <p>Republic of the Philippines</p>
          <p>Province of Laguna &bull; City of Cabuyao</p>
          <h3>BARANGAY SAN ISIDRO</h3>
          <p style="font-weight: bold; font-size: 11pt; margin-top: 4px;">${subTitle.toUpperCase()}</p>
          <p style="font-size: 13pt; font-weight: bold; text-decoration: underline; margin-top: 8px;">${docTitle.toUpperCase()}</p>
        </div>
      `;
    }

    // Print Form 1 & 2: Ordinance or Resolution
    window.printOfficialDocument = function(id) {
      const doc = allDocuments.find(d => d.id === id);
      if (!doc) return;

      const isOrdinance = (doc.docType || doc.doc_type) === 'Ordinance';
      const container = document.getElementById('print-statutory-container');

      if (isOrdinance) {
        container.innerHTML = `
          ${getOfficialLetterhead(`BARANGAY ORDINANCE NO. ${doc.controlNumber || doc.control_number || '2026-XXX'}`)}
          
          <div style="text-align: center; margin: 16px 0; font-weight: bold; font-size: 11pt; padding: 0 30px; text-transform: uppercase;">
            "${doc.title}"
          </div>

          <div style="font-size: 9.5pt; line-height: 1.5; text-align: justify; margin-bottom: 16px;">
            <p style="text-indent: 40px; margin-bottom: 12px;">
              <strong>BE IT ORDAINED</strong>, by the Sangguniang Barangay of Barangay San Isidro, City of Cabuyao, Province of Laguna, in session duly assembled, that:
            </p>
            
            <div style="white-space: pre-wrap; font-family: inherit; margin-bottom: 16px;">
${doc.documentBody || doc.document_body || 'Provisions and enacting sections.'}
            </div>

            ${doc.sanctionsPenalties || doc.sanctions_penalties ? `
              <p style="margin-bottom: 8px;"><strong>PENAL PROVISIONS AND ADMINISTRATIVE SANCTIONS (RA 7160 Sec. 516):</strong></p>
              <div style="padding: 8px 12px; background: #f8f8f8; border-left: 3px solid #000; font-size: 9pt; margin-bottom: 16px;">
                ${doc.sanctionsPenalties || doc.sanctions_penalties}
              </div>
            ` : ''}

            <p style="text-indent: 40px; margin-bottom: 12px;">
              <strong>EFFECTIVITY:</strong> This Ordinance shall take effect on <strong>${doc.effectivityDate || doc.effectivity_date || 'ten (10) days after posting'}</strong> following compliance with the mandatory posting requirements in at least three (3) conspicuous places pursuant to Section 59 of Republic Act No. 7160 (The Local Government Code of 1991).
            </p>

            <p style="text-indent: 40px;">
              <strong>ENACTED AND APPROVED</strong> this <strong>${doc.dateEnacted || doc.date_enacted || new Date().toLocaleDateString()}</strong> at the Barangay Hall of Barangay San Isidro, City of Cabuyao, Philippines.
            </p>
          </div>

          <div style="margin-top: 30px; font-size: 9pt;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
              <div>
                <p>Introduced and Sponsored by:</p>
                <div class="signatory-box" style="margin-top: 25px; width: 220px;">
                  <strong>${doc.sponsorName || doc.sponsor_name}</strong><br>
                  Barangay Kagawad / Principal Sponsor
                </div>
              </div>
              <div>
                <p>Attested and Certified Correct:</p>
                <div class="signatory-box" style="margin-top: 25px; width: 220px;">
                  <strong>CARMELITA ESPERANZA</strong><br>
                  Barangay Secretary
                </div>
              </div>
            </div>

            <div style="text-align: center; margin-top: 20px;">
              <p>Approved by:</p>
              <div class="signatory-box" style="margin: 25px auto 0; width: 260px;">
                <strong>HON. ANTONIO S. VALDEZ</strong><br>
                Punong Barangay / Presiding Officer
              </div>
            </div>
          </div>
        `;
      } else {
        // Resolution Form
        container.innerHTML = `
          ${getOfficialLetterhead(`RESOLUTION NO. ${doc.controlNumber || doc.control_number || '2026-XXX'}`)}
          
          <p style="text-align: center; font-style: italic; font-size: 9.5pt; margin-bottom: 16px;">
            EXCERPT FROM THE MINUTES OF THE REGULAR SESSION OF THE SANGGUNIANG BARANGAY OF BARANGAY SAN ISIDRO HELD AT THE BARANGAY SESSION HALL.
          </p>

          <div style="text-align: center; margin: 16px 0; font-weight: bold; font-size: 11pt; padding: 0 30px; text-transform: uppercase;">
            "${doc.title}"
          </div>

          <div style="font-size: 9.5pt; line-height: 1.6; text-align: justify; margin-bottom: 24px;">
            <div style="white-space: pre-wrap; font-family: inherit;">
${doc.documentBody || doc.document_body || 'Resolution contents.'}
            </div>

            <p style="text-indent: 40px; margin-top: 16px;">
              <strong>RESOLVED FINALLY</strong>, that certified copies of this Resolution be forwarded to the Sangguniang Panlungsod of Cabuyao and agencies concerned for their information and guidance.
            </p>
          </div>

          <div class="signatory-grid" style="margin-top: 40px;">
            <div>
              <div class="signatory-box">
                <strong>CARMELITA ESPERANZA</strong><br>
                Barangay Secretary &bull; Attester
              </div>
            </div>
            <div>
              <div class="signatory-box">
                <strong>HON. ANTONIO S. VALDEZ</strong><br>
                Punong Barangay &bull; Approver
              </div>
            </div>
          </div>
        `;
      }

      window.print();
    };

    // Print Form 3: Session Minutes & Roll Call Extract
    window.printSessionMinutes = function(id) {
      const s = allSessions.find(item => item.id === id);
      if (!s) return;

      const rawRoll = s.rollCall || s.roll_call;
      const rollArr = Array.isArray(rawRoll) ? rawRoll : (typeof rawRoll === 'string' ? JSON.parse(rawRoll || '[]') : []);

      const container = document.getElementById('print-statutory-container');
      container.innerHTML = `
        ${getOfficialLetterhead('MINUTES OF SANGGUNIANG BARANGAY SESSION', 'Journal of Proceedings &bull; Republic of the Philippines')}

        <div style="border: 1px solid #000; padding: 12px; font-size: 9pt; margin-bottom: 16px;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
            <div><strong>Session Number:</strong> ${s.sessionNumber || s.session_number}</div>
            <div><strong>Session Type:</strong> ${s.sessionType || s.session_type}</div>
            <div><strong>Date &amp; Time:</strong> ${s.sessionDate || s.session_date} at ${s.sessionTime || s.session_time || '09:00 AM'}</div>
            <div><strong>Presiding Officer:</strong> ${s.presidingOfficer || s.presiding_officer}</div>
            <div><strong>Quorum Status:</strong> ${s.quorumStatus || s.quorum_status} (${s.presentCount || s.present_count} / ${s.totalMembers || s.total_members || 9} Present)</div>
            <div><strong>Session Status:</strong> ${s.sessionStatus || s.session_status || 'Approved'}</div>
          </div>
        </div>

        <div style="margin-bottom: 16px; font-size: 9pt;">
          <h4 style="margin: 0 0 6px; font-size: 9.5pt; text-transform: uppercase;">I. Roll Call Attendance Record</h4>
          <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 8.5pt;">
            <thead>
              <tr style="background: #f0f0f0;">
                <th style="border: 1px solid #000; padding: 4px 8px; text-align: left;">Council Member Name</th>
                <th style="border: 1px solid #000; padding: 4px 8px; text-align: left;">Position / Committee</th>
                <th style="border: 1px solid #000; padding: 4px 8px; text-align: center; width: 90px;">Attendance</th>
              </tr>
            </thead>
            <tbody>
              ${councilMembers.map(m => {
                const isPresent = rollArr.length > 0 ? rollArr.includes(m.name) : true;
                return `
                  <tr>
                    <td style="border: 1px solid #000; padding: 4px 8px;">${m.name}</td>
                    <td style="border: 1px solid #000; padding: 4px 8px;">${m.role}</td>
                    <td style="border: 1px solid #000; padding: 4px 8px; text-align: center; font-weight: bold; color: ${isPresent ? '#000' : '#888'};">
                      ${isPresent ? 'PRESENT' : 'ABSENT'}
                    </td>
                  </tr>
                `;
              }).join('')}
            </tbody>
          </table>
        </div>

        <div style="margin-bottom: 16px; font-size: 9pt;">
          <h4 style="margin: 0 0 6px; font-size: 9.5pt; text-transform: uppercase;">II. Order of Business &amp; Agenda</h4>
          <div style="border: 1px solid #000; padding: 8px 12px; white-space: pre-wrap; line-height: 1.4;">
${s.agendaTopics || s.agenda_topics}
          </div>
        </div>

        <div style="margin-bottom: 24px; font-size: 9pt;">
          <h4 style="margin: 0 0 6px; font-size: 9.5pt; text-transform: uppercase;">III. Summary of Proceedings &amp; Legislative Actions</h4>
          <div style="border: 1px solid #000; padding: 8px 12px; white-space: pre-wrap; line-height: 1.5;">
${s.minutesSummary || s.minutes_summary}
          </div>
        </div>

        <div class="signatory-grid">
          <div>
            <div class="signatory-box">
              <strong>CARMELITA ESPERANZA</strong><br>
              Barangay Secretary &bull; Journal Custodian
            </div>
          </div>
          <div>
            <div class="signatory-box">
              <strong>HON. ANTONIO S. VALDEZ</strong><br>
              Punong Barangay &bull; Presiding Officer
            </div>
          </div>
        </div>
      `;

      window.print();
    };

    // Print Form 4: Certificate of Posting & Effectivity (RA 7160 Sec. 59)
    window.printPostingCertificate = function(id) {
      const doc = allDocuments.find(d => d.id === id);
      if (!doc) return;

      const container = document.getElementById('print-statutory-container');
      container.innerHTML = `
        ${getOfficialLetterhead('CERTIFICATE OF POSTING', 'Statutory Publication Compliance &bull; RA 7160 Sec. 59')}

        <div style="font-size: 10pt; line-height: 1.7; text-align: justify; margin: 30px 20px;">
          <p style="margin-bottom: 16px;">
            <strong>TO WHOM IT MAY CONCERN:</strong>
          </p>

          <p style="text-indent: 40px; margin-bottom: 16px;">
            <strong>THIS IS TO CERTIFY</strong> that pursuant to <strong>Section 59 of Republic Act No. 7160</strong> (The Local Government Code of 1991), true and exact copies of <strong>${doc.docType || doc.doc_type} No. ${doc.controlNumber || doc.control_number}</strong>, entitled:
          </p>

          <div style="margin: 20px 30px; padding: 12px 16px; border-left: 3px solid #000; background: #f8f8f8; font-weight: bold; text-transform: uppercase; font-size: 10pt;">
            "${doc.title}"
          </div>

          <p style="text-indent: 40px; margin-bottom: 16px;">
            were formally posted on <strong>${doc.datePosted || doc.date_posted || new Date().toLocaleDateString()}</strong> in at least three (3) conspicuous public places within the territorial jurisdiction of Barangay San Isidro, City of Cabuyao, Province of Laguna, to wit:
          </p>

          <ol style="margin-left: 40px; margin-bottom: 20px;">
            <li><strong>Barangay Hall Official Gazette Bulletin Board</strong>, Barangay Hall, San Isidro</li>
            <li><strong>Public Market &amp; Commercial Hub Bulletin</strong>, San Isidro</li>
            <li><strong>Barangay Health Station &amp; Multi-Purpose Plaza</strong>, San Isidro</li>
          </ol>

          <p style="text-indent: 40px; margin-bottom: 16px;">
            <strong>FURTHER CERTIFIED</strong> that having complied with the three (3) consecutive weeks posting requirement, the said regulatory measure takes full effectivity on <strong>${doc.effectivityDate || doc.effectivity_date || 'ten (10) days post posting'}</strong>, and copies thereof were submitted to the Secretary to the Sangguniang Panlungsod of the City of Cabuyao for legal review pursuant to Section 57 of RA 7160.
          </p>

          <p style="text-indent: 40px; margin-top: 30px;">
            <strong>IN WITNESS WHEREOF</strong>, I have hereunto affixed my hand and the official seal of Barangay San Isidro this <strong>${new Date().toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' })}</strong>.
          </p>
        </div>

        <div class="signatory-grid" style="margin-top: 60px;">
          <div>
            <div class="signatory-box">
              <strong>CARMELITA ESPERANZA</strong><br>
              Barangay Secretary &bull; Certifying Officer
            </div>
          </div>
          <div>
            <div class="signatory-box">
              <strong>HON. ANTONIO S. VALDEZ</strong><br>
              Punong Barangay &bull; Attester
            </div>
          </div>
        </div>
      `;

      window.print();
    };
  </script>
</body>
</html>
