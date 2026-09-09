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
  <title>Executive Reports &amp; Analytics &bull; Barangay Management System</title>
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

    /* Tab Navigation Segmented Bar */
    .reports-nav-bar {
      display: flex;
      background-color: var(--color-canvas-soft);
      border-radius: var(--rounded-full);
      padding: 4px;
      gap: 4px;
      margin-bottom: var(--spacing-md);
      overflow-x: auto;
    }

    .reports-tab-btn {
      flex: 1;
      min-width: 160px;
      text-align: center;
      padding: 8px 16px;
      font-size: 0.8125rem;
      font-weight: 600;
      color: var(--color-text-muted);
      border-radius: var(--rounded-full);
      cursor: pointer;
      border: none;
      background: transparent;
      outline: none;
      transition: all 0.15s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      white-space: nowrap;
    }

    .reports-tab-btn.active {
      background-color: var(--color-canvas);
      color: var(--color-ink);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .tab-pane {
      display: none;
    }

    .tab-pane.active {
      display: block;
    }

    .report-card {
      background-color: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: var(--spacing-md) var(--spacing-lg);
      margin-bottom: var(--spacing-md);
    }

    .report-card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: var(--spacing-md);
      border-bottom: 1px solid var(--color-hairline-soft);
      padding-bottom: var(--spacing-sm);
      flex-wrap: wrap;
      gap: var(--spacing-xs);
    }

    .analytics-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--spacing-md);
    }

    .analytics-grid-4 {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: var(--spacing-sm);
    }

    @media (max-width: 1024px) {
      .analytics-grid-4 {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .analytics-grid-2, .analytics-grid-4 {
        grid-template-columns: 1fr;
      }
    }

    /* Distribution Progress Bars */
    .progress-track {
      height: 8px;
      background-color: var(--color-canvas-soft);
      border-radius: var(--rounded-full);
      overflow: hidden;
      margin-top: 6px;
      display: flex;
    }

    .progress-fill {
      height: 100%;
      border-radius: var(--rounded-full);
      background-color: var(--color-primary);
      transition: width 0.3s ease;
    }

    .distribution-row {
      display: flex;
      flex-direction: column;
      padding: 10px 0;
      border-bottom: 1px solid var(--color-hairline-soft);
    }

    .distribution-row:last-child {
      border-bottom: none;
    }

    .distribution-label-group {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.8125rem;
    }

    /* Printable Accomplishment Report Letterhead */
    @media print {
      body {
        background: #ffffff !important;
        color: #000000 !important;
      }
      .app-shell, .app-sidebar, .app-topbar, .mobile-nav-bar, .page-hero,
      .stats-ladder, .reports-nav-bar, .toast-container,
      dialog, .no-print {
        display: none !important;
      }
      #printable-accomplishment-report {
        display: block !important;
        margin: 0;
        padding: 20px;
        width: 100%;
      }
    }

    #printable-accomplishment-report {
      display: none;
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <!-- Sidebar Mount -->
    <div id="sidebar-mount"></div>

    <!-- Main Content Workspace -->
    <div class="app-main">
      <div id="mobile-header-mount"></div>
      <div id="app-topbar-mount"></div>

      <main class="app-content">
        <!-- Page Hero Section -->
        <section class="page-hero">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--spacing-md);">
            <div>
              <div style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-xs);">
                <h1 class="typography-heading-2">Executive Reports &amp; Analytics.</h1>
                <span class="badge-neutral" id="report-period-badge">All-Time Cumulative</span>
              </div>
              <p class="typography-body-lg">
                Consolidated revenue collections, demographic population census, and katarungang pambarangay accomplishment metrics.
              </p>
            </div>
            <div style="display: flex; align-items: center; gap: var(--spacing-sm); flex-wrap: wrap;">
              <!-- Date Range Filter -->
              <select id="report-date-filter" class="filter-select" style="height: 38px;">
                <option value="all">All-Time Cumulative</option>
                <option value="month">This Month</option>
                <option value="quarter">This Quarter</option>
                <option value="year">This Year</option>
              </select>

              <button class="button-outline" id="btn-export-consolidated-csv" title="Download Complete CSV Data" style="height: 38px; padding: 0 16px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="7 10 12 15 17 10"/>
                  <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span>Export CSV</span>
              </button>

              <button class="button-primary" id="btn-print-report" title="Print Official DILG Accomplishment Report" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="6 9 6 2 18 2 18 9"/>
                  <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                  <rect width="12" height="8" x="6" y="14"/>
                </svg>
                <span>Print Report</span>
              </button>
            </div>
          </div>
        </section>

        <!-- Executive Telemetry Ladder -->
        <section>
          <div class="stats-ladder">
            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">TOTAL REVENUE</span>
                <span class="badge-emerald">Treasury</span>
              </div>
              <div class="stat-number" id="stat-kpi-revenue" style="color: #059669;">₱0.00</div>
              <div class="typography-caption" id="stat-kpi-revenue-sub">From 0 official receipts</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">POPULATION CENSUS</span>
                <span class="badge-blue">Demographics</span>
              </div>
              <div class="stat-number" id="stat-kpi-population">0</div>
              <div class="typography-caption" id="stat-kpi-population-sub">0 registered households</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">LUPON RESOLUTION</span>
                <span class="badge-purple">Peace &amp; Order</span>
              </div>
              <div class="stat-number" id="stat-kpi-resolution-rate">0%</div>
              <div class="typography-caption" id="stat-kpi-resolution-sub">0 of 0 cases settled</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">GOVERNANCE OUTPUT</span>
                <span class="badge-popular">Accomplishment</span>
              </div>
              <div class="stat-number" id="stat-kpi-output">0</div>
              <div class="typography-caption">Certificates &amp; actions rendered</div>
            </div>
          </div>
        </section>

        <!-- Reports Segmented Navigation Tabs -->
        <nav class="reports-nav-bar" aria-label="Report Views">
          <button class="reports-tab-btn active" data-tab="tab-revenue">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            <span>Revenue &amp; Treasury</span>
          </button>
          <button class="reports-tab-btn" data-tab="tab-demographics">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>Demographic Census</span>
          </button>
          <button class="reports-tab-btn" data-tab="tab-blotter">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Peace &amp; Order (Lupon)</span>
          </button>
          <button class="reports-tab-btn" data-tab="tab-summary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            <span>DILG Report Preview</span>
          </button>
        </nav>

        <!-- TAB 1: REVENUE & TREASURY LEDGER -->
        <div id="tab-revenue" class="tab-pane active">
          <div class="analytics-grid-4" style="margin-bottom: var(--spacing-md);">
            <div class="stat-card">
              <span class="typography-label" style="color: var(--color-text-muted);">BARANGAY CLEARANCES</span>
              <div class="stat-number" id="rev-brgy-clearance">₱0.00</div>
              <div class="typography-caption" id="rev-brgy-count">0 issuances</div>
            </div>

            <div class="stat-card">
              <span class="typography-label" style="color: var(--color-text-muted);">BUSINESS PERMITS</span>
              <div class="stat-number" id="rev-business-permit">₱0.00</div>
              <div class="typography-caption" id="rev-business-count">0 commercial permits</div>
            </div>

            <div class="stat-card">
              <span class="typography-label" style="color: var(--color-text-muted);">RESIDENCY CERTS</span>
              <div class="stat-number" id="rev-residency">₱0.00</div>
              <div class="typography-caption" id="rev-residency-count">0 issued</div>
            </div>

            <div class="stat-card">
              <span class="typography-label" style="color: var(--color-text-muted);">INDIGENT SUBSIDY WAIVED</span>
              <div class="stat-number" id="rev-waived" style="color: #d97706;">₱0.00</div>
              <div class="typography-caption" id="rev-waived-count">0 welfare fee exemptions</div>
            </div>
          </div>

          <div class="report-card">
            <div class="report-card-header">
              <div>
                <h3 class="typography-heading-4">Treasury Collections Ledger.</h3>
                <p class="typography-caption">
                  Official Receipt (OR) itemized transaction ledger of all issued certificates and paid clearances.
                </p>
              </div>
              <button class="button-outline" id="btn-export-ledger-csv" style="height: 32px; padding: 0 12px; font-size: 0.75rem;">
                Export Ledger CSV
              </button>
            </div>

            <div class="data-table-container">
              <div class="data-table-wrap" style="max-height: 420px; overflow-y: auto;">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>OR Number</th>
                      <th>Date Issued</th>
                      <th>Recipient Resident</th>
                      <th>Document Type</th>
                      <th>Purpose</th>
                      <th style="text-align: right;">Amount Paid</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="revenue-ledger-tbody">
                    <tr>
                      <td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 24px;">
                        No financial records recorded in database.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: DEMOGRAPHIC CENSUS & POPULATION -->
        <div id="tab-demographics" class="tab-pane">
          <div class="analytics-grid-2">
            <!-- Card A: Purok Population Density -->
            <div class="report-card">
              <div class="report-card-header">
                <div>
                  <h3 class="typography-heading-4">Purok Population Density.</h3>
                  <p class="typography-caption">Geographic distribution of community residents across zones.</p>
                </div>
                <a href="geo-profiling.php" class="button-outline" style="height: 32px; padding: 0 12px; font-size: 0.75rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                  <span>Interactive Heatmap &rarr;</span>
                </a>
              </div>
              <div id="purok-distribution-container">
                <!-- Populated dynamically -->
              </div>
            </div>

            <!-- Card B: Age Demographics & Electoral -->
            <div class="report-card">
              <div class="report-card-header">
                <div>
                  <h3 class="typography-heading-4">Age Brackets &amp; Voter Profile.</h3>
                  <p class="typography-caption">Age cohort breakdown and electoral participation ratio.</p>
                </div>
              </div>
              <div id="age-distribution-container">
                <!-- Populated dynamically -->
              </div>

              <div style="margin-top: var(--spacing-md); padding-top: var(--spacing-sm); border-top: 1px solid var(--color-hairline-soft);">
                <div class="distribution-label-group">
                  <span style="font-weight: 600;">Registered Electoral Voters:</span>
                  <span style="font-weight: 700; color: var(--color-primary);" id="voter-ratio-text">0 / 0 (0%)</span>
                </div>
                <div class="progress-track">
                  <div class="progress-fill" id="voter-progress-fill" style="width: 0%;"></div>
                </div>
              </div>

              <div style="margin-top: var(--spacing-md); padding-top: var(--spacing-sm); border-top: 1px solid var(--color-hairline-soft);">
                <span class="typography-label" style="color: var(--color-text-muted);">SPECIAL ASSISTANCE PROFILES</span>
                <div style="display: flex; gap: var(--spacing-sm); flex-wrap: wrap; margin-top: 8px;">
                  <span class="badge-amber" id="stat-count-seniors">0 Seniors (60+)</span>
                  <span class="badge-blue" id="stat-count-indigents">0 Indigents</span>
                  <span class="badge-purple" id="stat-count-fourps">0 4Ps Beneficiaries</span>
                  <span class="badge-emerald" id="stat-count-pwd">0 PWD</span>
                  <span class="badge-rose" id="stat-count-soloparent">0 Solo Parents</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 3: PEACE & ORDER / LUPON TAGAPAMAYAPA -->
        <div id="tab-blotter" class="tab-pane">
          <div class="analytics-grid-2">
            <!-- Dispute Resolution Funnel -->
            <div class="report-card">
              <div class="report-card-header">
                <div>
                  <h3 class="typography-heading-4">Mediation Case Lifecycle.</h3>
                  <p class="typography-caption">Barangay Justice System (KP) case resolution status.</p>
                </div>
                <span class="badge-emerald" id="badge-resolution-rate">0% Settled</span>
              </div>
              <div id="blotter-lifecycle-container">
                <!-- Populated dynamically -->
              </div>
            </div>

            <!-- Common Incidents -->
            <div class="report-card">
              <div class="report-card-header">
                <div>
                  <h3 class="typography-heading-4">Incident Nature Breakdown.</h3>
                  <p class="typography-caption">Frequency of reported dispute categories.</p>
                </div>
              </div>
              <div id="blotter-category-container">
                <!-- Populated dynamically -->
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 4: CONSOLIDATED DILG REPORT PREVIEW -->
        <div id="tab-summary" class="tab-pane">
          <div class="report-card">
            <div class="report-card-header">
              <div>
                <h3 class="typography-heading-4">Official Accomplishment Report Preview.</h3>
                <p class="typography-caption">
                  Standardized government accomplishment overview formatted for submission to DILG and City Hall.
                </p>
              </div>
              <button class="button-primary" onclick="document.getElementById('btn-print-report').click();" style="height: 34px; padding: 0 16px; font-size: 0.75rem;">
                Print Official Sheet &rarr;
              </button>
            </div>

            <div style="background: #ffffff; color: #111111; border: 1px solid var(--color-hairline); border-radius: var(--rounded-md); padding: 24px; font-size: 0.8125rem; line-height: 1.6;">
              <div style="text-align: center; border-bottom: 2px solid #111; padding-bottom: 12px; margin-bottom: 16px;">
                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #555;">Republic of the Philippines</div>
                <div style="font-size: 0.8125rem; font-weight: 700; text-transform: uppercase;" id="dilg-jurisdiction">Province of Metropolitan Manila &bull; City of San Isidro</div>
                <div style="font-size: 1.25rem; font-weight: 800; color: #111; margin: 4px 0;" id="dilg-brgy-name">BARANGAY SAN ISIDRO</div>
                <div style="font-size: 0.875rem; font-weight: 700; color: #0066ff; text-transform: uppercase;">OFFICIAL EXECUTIVE ACCOMPLISHMENT REPORT</div>
                <div style="font-size: 0.75rem; color: #666; margin-top: 2px;" id="dilg-period-text">Reporting Period: All-Time Cumulative</div>
              </div>

              <div style="margin-bottom: 16px;">
                <h4 style="font-weight: 800; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin-bottom: 8px;">I. EXECUTIVE REVENUE &amp; TREASURY SUMMARY</h4>
                <div style="display: flex; justify-content: space-between; gap: 12px;">
                  <span>&bull; Total Official Clearances Issued: <strong id="dilg-total-certs">0</strong></span>
                  <span>&bull; Total Collections: <strong id="dilg-total-revenue">₱0.00</strong></span>
                  <span>&bull; Social Welfare Subsidies Waived: <strong id="dilg-total-waived">₱0.00</strong></span>
                </div>
              </div>

              <div style="margin-bottom: 16px;">
                <h4 style="font-weight: 800; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin-bottom: 8px;">II. CIVIL DEMOGRAPHIC PROFILING</h4>
                <div style="display: flex; justify-content: space-between; gap: 12px;">
                  <span>&bull; Total Population: <strong id="dilg-total-pop">0</strong></span>
                  <span>&bull; Registered Voters: <strong id="dilg-voters">0</strong></span>
                  <span>&bull; Senior Citizens: <strong id="dilg-seniors">0</strong></span>
                  <span>&bull; 4Ps / Indigents: <strong id="dilg-indigents">0</strong></span>
                </div>
              </div>

              <div style="margin-bottom: 24px;">
                <h4 style="font-weight: 800; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin-bottom: 8px;">III. PEACE &amp; ORDER / KATARUNGANG PAMBARANGAY</h4>
                <div style="display: flex; justify-content: space-between; gap: 12px;">
                  <span>&bull; Total Cases Logged: <strong id="dilg-cases">0</strong></span>
                  <span>&bull; Amicably Settled: <strong id="dilg-settled">0</strong></span>
                  <span>&bull; Ongoing Mediation: <strong id="dilg-ongoing">0</strong></span>
                  <span>&bull; Resolution Efficiency: <strong id="dilg-rate">0%</strong></span>
                </div>
              </div>

              <div style="display: flex; justify-content: space-between; margin-top: 40px; padding-top: 10px;">
                <div style="text-align: center; width: 220px;">
                  <div style="border-bottom: 1px solid #111; height: 35px;"></div>
                  <div style="font-weight: 700; padding-top: 4px;" id="dilg-sec-sig">BARANGAY SECRETARY</div>
                  <div style="font-size: 0.75rem; color: #555;">Prepared by / Secretariat</div>
                </div>

                <div style="text-align: center; width: 220px;">
                  <div style="border-bottom: 1px solid #111; height: 35px;"></div>
                  <div style="font-weight: 700; padding-top: 4px;" id="dilg-pb-sig">HON. PUNONG BARANGAY</div>
                  <div style="font-size: 0.75rem; color: #555;">Attested &amp; Approved by</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Printable Official Accomplishment Sheet (@media print) -->
  <div id="printable-accomplishment-report">
    <div style="text-align: center; border-bottom: 2px solid #111; padding-bottom: 14px; margin-bottom: 20px;">
      <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #555;">Republic of the Philippines</div>
      <div style="font-size: 0.95rem; font-weight: 700; text-transform: uppercase;" id="print-jurisdiction">Province of Metropolitan Manila &bull; City of San Isidro</div>
      <div style="font-size: 1.4rem; font-weight: 800; color: #111; margin: 4px 0;" id="print-brgy-name">BARANGAY SAN ISIDRO</div>
      <div style="font-size: 1rem; font-weight: 700; color: #0066ff; text-transform: uppercase;">OFFICIAL EXECUTIVE ACCOMPLISHMENT REPORT</div>
      <div style="font-size: 0.8rem; color: #555; margin-top: 4px;" id="print-report-period">Document Reference: DILG-BMS-2026-REPORT</div>
    </div>

    <div style="margin-bottom: 24px;">
      <h3 style="font-size: 0.95rem; font-weight: 800; border-bottom: 1.5px solid #111; padding-bottom: 4px; margin-bottom: 10px;">
        1. LOCAL REVENUE &amp; CLEARANCE ISSUANCE
      </h3>
      <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; margin-bottom: 12px;">
        <thead>
          <tr style="background: #f3f3f3; text-align: left; border-bottom: 1px solid #111;">
            <th style="padding: 6px 10px;">SERVICE TYPE</th>
            <th style="padding: 6px 10px; text-align: center;">VOLUME ISSUED</th>
            <th style="padding: 6px 10px; text-align: right;">COLLECTIONS (PHP)</th>
          </tr>
        </thead>
        <tbody id="print-revenue-tbody">
          <!-- Injected dynamically -->
        </tbody>
      </table>
    </div>

    <div style="margin-bottom: 24px;">
      <h3 style="font-size: 0.95rem; font-weight: 800; border-bottom: 1.5px solid #111; padding-bottom: 4px; margin-bottom: 10px;">
        2. DEMOGRAPHIC CENSUS &amp; COMMUNITY ENROLMENT
      </h3>
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; font-size: 0.85rem; line-height: 1.6;">
        <div>&bull; Total Residents: <strong id="print-pop">0</strong></div>
        <div>&bull; Registered Voters: <strong id="print-voters">0</strong></div>
        <div>&bull; Senior Citizens (60+): <strong id="print-seniors">0</strong></div>
        <div>&bull; Indigent Beneficiaries: <strong id="print-indigents">0</strong></div>
        <div>&bull; 4Ps Program Beneficiaries: <strong id="print-fourps">0</strong></div>
        <div>&bull; Persons with Disability: <strong id="print-pwd">0</strong></div>
      </div>
    </div>

    <div style="margin-bottom: 24px;">
      <h3 style="font-size: 0.95rem; font-weight: 800; border-bottom: 1.5px solid #111; padding-bottom: 4px; margin-bottom: 10px;">
        3. PEACE &amp; ORDER / KATARUNGANG PAMBARANGAY
      </h3>
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; font-size: 0.85rem; line-height: 1.6;">
        <div>&bull; Cases Filed: <strong id="print-cases">0</strong></div>
        <div>&bull; Amicably Settled: <strong id="print-settled">0</strong></div>
        <div>&bull; Active Mediation: <strong id="print-active-cases">0</strong></div>
        <div>&bull; Resolution Efficiency: <strong id="print-rate">0%</strong></div>
      </div>
    </div>

    <div style="margin-top: 60px; display: flex; justify-content: space-between;">
      <div style="text-align: center; width: 220px;">
        <div style="border-bottom: 1px solid #111; height: 35px;"></div>
        <div style="font-weight: 700; padding-top: 4px; font-size: 0.85rem;" id="print-sec-name">BARANGAY SECRETARY</div>
        <div style="font-size: 0.75rem; color: #555;">Secretary to the Sangguniang Barangay</div>
      </div>

      <div style="text-align: center; width: 220px;">
        <div style="border-bottom: 1px solid #111; height: 35px;"></div>
        <div style="font-weight: 700; padding-top: 4px; font-size: 0.85rem;" id="print-pb-name">HON. PUNONG BARANGAY</div>
        <div style="font-size: 0.75rem; color: #555;">Punong Barangay / Council Presiding Officer</div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="js/api.js"></script>
  
  <script src="js/components/toast.js"></script>
  <script src="js/components/sidebar.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', async () => {
      // 1. Guard route: require authentication
      const authData = await authService.requireAuth('login.php');
      if (!authData) return;
      const { user: currentAuthUser } = authData;

      // 2. Mount persistent sidebar
      await AppSidebar.render('reports');

      // 3. Tab Switching
      const tabButtons = document.querySelectorAll('.reports-tab-btn');
      const tabPanes = document.querySelectorAll('.tab-pane');

      tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          const target = btn.getAttribute('data-tab');
          tabButtons.forEach(b => b.classList.remove('active'));
          tabPanes.forEach(p => p.classList.remove('active'));

          btn.classList.add('active');
          const targetPane = document.getElementById(target);
          if (targetPane) targetPane.classList.add('active');
        });
      });

      // 4. State Variables
      let allCertificates = [];
      let allResidents = [];
      let allBlotters = [];
      let allOfficials = [];
      let currentIdentity = null;

      // 5. Load All Data Stores
      async function loadAllReportData() {
        try {
          allCertificates = await window.barangayDB.getAll('certificates');
          allResidents = await window.barangayDB.getAll('residents');
          allBlotters = await window.barangayDB.getAll('blotter_cases');
          allOfficials = await window.barangayDB.getAll('officials');

          const idSetting = await window.barangayDB.get('settings', 'identity');
          currentIdentity = idSetting ? idSetting.value : null;

          applyFilterAndCompute();
        } catch (err) {
          console.error('Failed to load report data:', err);
          showToast('Failed to load reporting data', 'error');
        }
      }

      // 6. Filter & Computation Engine
      const dateFilter = document.getElementById('report-date-filter');
      dateFilter.addEventListener('change', () => {
        const val = dateFilter.value;
        const badge = document.getElementById('report-period-badge');
        if (val === 'month') badge.textContent = 'Current Month';
        else if (val === 'quarter') badge.textContent = 'Current Quarter';
        else if (val === 'year') badge.textContent = 'Current Calendar Year';
        else badge.textContent = 'All-Time Cumulative';

        applyFilterAndCompute();
      });

      function filterByDate(items, dateField) {
        const mode = dateFilter.value;
        if (mode === 'all') return items;

        const now = new Date();
        const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
        const startOfYear = new Date(now.getFullYear(), 0, 1);

        // Quarter start
        const currentQuarter = Math.floor(now.getMonth() / 3);
        const startOfQuarter = new Date(now.getFullYear(), currentQuarter * 3, 1);

        return items.filter(item => {
          const raw = item[dateField];
          if (!raw) return true;
          const dt = new Date(raw);

          if (mode === 'month') return dt >= startOfMonth;
          if (mode === 'quarter') return dt >= startOfQuarter;
          if (mode === 'year') return dt >= startOfYear;
          return true;
        });
      }

      function applyFilterAndCompute() {
        const certs = filterByDate(allCertificates, 'issuedAt');
        const blotters = filterByDate(allBlotters, 'incidentDate');
        const residents = allResidents; // Demographic snapshot represents live roster

        // A. Financial / Revenue Computation
        let totalRevenue = 0;
        let brgyClearanceRev = 0;
        let brgyClearanceCount = 0;
        let businessRev = 0;
        let businessCount = 0;
        let residencyRev = 0;
        let residencyCount = 0;
        let indigencyCount = 0;
        let waivedSubsidy = 0;

        certs.forEach(c => {
          if (c.status === 'Revoked') return;
          const amt = parseFloat(c.amountPaid) || 0;
          totalRevenue += amt;

          if (c.type === 'Barangay Clearance') {
            brgyClearanceRev += amt;
            brgyClearanceCount++;
          } else if (c.type === 'Business Clearance') {
            businessRev += amt;
            businessCount++;
          } else if (c.type === 'Certificate of Residency') {
            residencyRev += amt;
            residencyCount++;
          } else if (c.type === 'Certificate of Indigency') {
            indigencyCount++;
            waivedSubsidy += 50.00; // estimated statutory exemption subsidy
          }
        });

        // Update Top KPIs
        document.getElementById('stat-kpi-revenue').textContent = `₱${totalRevenue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        document.getElementById('stat-kpi-revenue-sub').textContent = `From ${certs.filter(c => parseFloat(c.amountPaid) > 0).length} official receipts`;

        document.getElementById('stat-kpi-population').textContent = residents.length;
        const purokCount = new Set(residents.map(r => r.purok).filter(Boolean)).size;
        document.getElementById('stat-kpi-population-sub').textContent = `Enrolled across ${purokCount || 7} Purok rosters`;

        // Lupon Resolution Rate
        const totalCases = blotters.length;
        const settledCases = blotters.filter(b => b.status === 'Amicably Settled').length;
        const resRate = totalCases > 0 ? Math.round((settledCases / totalCases) * 100) : 0;

        document.getElementById('stat-kpi-resolution-rate').textContent = `${resRate}%`;
        document.getElementById('stat-kpi-resolution-sub').textContent = `${settledCases} of ${totalCases} disputes resolved`;

        // Total Service Output
        const serviceOutput = certs.length + totalCases + residents.length;
        document.getElementById('stat-kpi-output').textContent = serviceOutput;

        // Revenue Breakdown Cards
        document.getElementById('rev-brgy-clearance').textContent = `₱${brgyClearanceRev.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
        document.getElementById('rev-brgy-count').textContent = `${brgyClearanceCount} clearances issued`;

        document.getElementById('rev-business-permit').textContent = `₱${businessRev.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
        document.getElementById('rev-business-count').textContent = `${businessCount} commercial permits`;

        document.getElementById('rev-residency').textContent = `₱${residencyRev.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
        document.getElementById('rev-residency-count').textContent = `${residencyCount} issued`;

        document.getElementById('rev-waived').textContent = `₱${waivedSubsidy.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
        document.getElementById('rev-waived-count').textContent = `${indigencyCount} indigents served free`;

        // Render Ledger Table
        renderLedgerTable(certs);

        // Render Demographic Breakdown
        renderDemographics(residents);

        // Render Peace & Order
        renderBlotterAnalytics(blotters, totalCases, settledCases, resRate);

        // Render DILG Previews
        renderDilgPreviews(totalRevenue, certs.length, waivedSubsidy, residents, totalCases, settledCases, resRate);
      }

      // 7. Render Revenue Ledger Table
      function renderLedgerTable(certs) {
        const tbody = document.getElementById('revenue-ledger-tbody');
        if (!certs || certs.length === 0) {
          tbody.innerHTML = `
            <tr>
              <td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 24px;">
                No clearance or revenue transactions recorded for this period.
              </td>
            </tr>
          `;
          return;
        }

        certs.sort((a, b) => new Date(b.issuedAt || 0) - new Date(a.issuedAt || 0));

        tbody.innerHTML = certs.map(c => {
          const isRevoked = c.status === 'Revoked';
          const amt = parseFloat(c.amountPaid) || 0;
          const dateStr = c.issuedAt ? new Date(c.issuedAt).toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' }) : '—';
          
          let badge = '<span class="badge-blue">Clearance</span>';
          if (c.type === 'Business Clearance') badge = '<span class="badge-purple">Business</span>';
          else if (c.type === 'Certificate of Indigency') badge = '<span class="badge-amber">Indigency</span>';
          else if (c.type === 'Certificate of Residency') badge = '<span class="badge-emerald">Residency</span>';

          return `
            <tr style="${isRevoked ? 'opacity: 0.5; text-decoration: line-through;' : ''}">
              <td style="font-family: monospace; font-weight: 700;">${c.orNumber || 'OR-N/A'}</td>
              <td style="font-size: 0.75rem; color: var(--color-text-faint);">${dateStr}</td>
              <td style="font-weight: 600;">${c.recipientName}</td>
              <td>${badge}</td>
              <td style="font-size: 0.75rem; color: var(--color-text-muted);">${c.purpose}</td>
              <td style="text-align: right; font-weight: 700; color: ${amt > 0 ? '#059669' : 'inherit'};">
                ${amt > 0 ? `₱${amt.toFixed(2)}` : '₱0.00 (Waived)'}
              </td>
              <td>
                <span class="${isRevoked ? 'badge-rose' : 'badge-emerald'}" style="font-size: 0.6875rem;">
                  ${isRevoked ? 'REVOKED' : 'PAID'}
                </span>
              </td>
            </tr>
          `;
        }).join('');
      }

      // 8. Render Demographic Census
      function renderDemographics(residents) {
        const total = residents.length;
        const purokMap = {};
        for (let i = 1; i <= 7; i++) {
          purokMap[`Purok ${i}`] = 0;
        }

        let minors = 0;
        let youth = 0;
        let adults = 0;
        let seniors = 0;
        let voters = 0;
        let indigents = 0;
        let fourPs = 0;
        let pwds = 0;
        let soloParents = 0;

        residents.forEach(r => {
          if (r.purok) {
            purokMap[r.purok] = (purokMap[r.purok] || 0) + 1;
          }

          const age = parseInt(r.age, 10) || 0;
          if (age < 18) minors++;
          else if (age <= 30) youth++;
          else if (age <= 59) adults++;
          else seniors++;

          if (r.voterStatus === 'Registered') voters++;
          if (r.isIndigent) indigents++;
          if (r.isFourPs) fourPs++;
          if (r.isPwd) pwds++;
          if (r.isSoloParent) soloParents++;
        });

        // Purok Distribution
        const purokContainer = document.getElementById('purok-distribution-container');
        purokContainer.innerHTML = Object.entries(purokMap).map(([purokName, count]) => {
          const pct = total > 0 ? Math.round((count / total) * 100) : 0;
          return `
            <div class="distribution-row">
              <div class="distribution-label-group">
                <span style="font-weight: 600;">${purokName}</span>
                <span style="color: var(--color-text-muted);">${count} residents (${pct}%)</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" style="width: ${pct}%;"></div>
              </div>
            </div>
          `;
        }).join('');

        // Age Cohorts
        const ageContainer = document.getElementById('age-distribution-container');
        const cohorts = [
          { label: 'Children & Minors (0–17)', count: minors },
          { label: 'Youth Council Age (18–30)', count: youth },
          { label: 'Working Age Adults (31–59)', count: adults },
          { label: 'Senior Citizens (60+)', count: seniors }
        ];

        ageContainer.innerHTML = cohorts.map(c => {
          const pct = total > 0 ? Math.round((c.count / total) * 100) : 0;
          return `
            <div class="distribution-row">
              <div class="distribution-label-group">
                <span>${c.label}</span>
                <span style="font-weight: 600;">${c.count} (${pct}%)</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" style="width: ${pct}%; background-color: var(--color-ink);"></div>
              </div>
            </div>
          `;
        }).join('');

        // Voters
        const voterPct = total > 0 ? Math.round((voters / total) * 100) : 0;
        document.getElementById('voter-ratio-text').textContent = `${voters} / ${total} (${voterPct}%)`;
        document.getElementById('voter-progress-fill').style.width = `${voterPct}%`;

        // Special Profile Badges
        document.getElementById('stat-count-seniors').textContent = `${seniors} Seniors (60+)`;
        document.getElementById('stat-count-indigents').textContent = `${indigents} Indigents`;
        document.getElementById('stat-count-fourps').textContent = `${fourPs} 4Ps Beneficiaries`;
        document.getElementById('stat-count-pwd').textContent = `${pwds} PWD`;
        document.getElementById('stat-count-soloparent').textContent = `${soloParents} Solo Parents`;
      }

      // 9. Render Peace & Order (Blotter Analytics)
      function renderBlotterAnalytics(blotters, total, settled, rate) {
        document.getElementById('badge-resolution-rate').textContent = `${rate}% Settled`;

        const activeMediation = blotters.filter(b => b.status === 'Active Mediation').length;
        const scheduled = blotters.filter(b => b.status === 'Hearing Scheduled').length;
        const escalated = blotters.filter(b => b.status === 'Escalated CFA').length;

        const lifecycleContainer = document.getElementById('blotter-lifecycle-container');
        const stages = [
          { name: 'Amicably Settled (Reconciled)', count: settled, color: '#059669' },
          { name: 'Hearing Scheduled (Active Summons)', count: scheduled, color: '#0066ff' },
          { name: 'Active Mediation (Preliminary)', count: activeMediation, color: '#f59e0b' },
          { name: 'Escalated to Court (CFA Issued)', count: escalated, color: '#ef4444' }
        ];

        lifecycleContainer.innerHTML = stages.map(s => {
          const pct = total > 0 ? Math.round((s.count / total) * 100) : 0;
          return `
            <div class="distribution-row">
              <div class="distribution-label-group">
                <span style="font-weight: 600;">${s.name}</span>
                <span style="color: var(--color-text-muted);">${s.count} cases (${pct}%)</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" style="width: ${pct}%; background-color: ${s.color};"></div>
              </div>
            </div>
          `;
        }).join('');

        // Common Incident Types
        const typeCounts = {};
        blotters.forEach(b => {
          const t = b.incidentType || 'General Dispute';
          typeCounts[t] = (typeCounts[t] || 0) + 1;
        });

        const categoryContainer = document.getElementById('blotter-category-container');
        if (Object.keys(typeCounts).length === 0) {
          categoryContainer.innerHTML = '<p class="typography-body-sm" style="color: var(--color-text-muted); padding: 20px 0;">No dispute incident records reported.</p>';
        } else {
          categoryContainer.innerHTML = Object.entries(typeCounts).map(([type, count]) => {
            const pct = total > 0 ? Math.round((count / total) * 100) : 0;
            return `
              <div class="distribution-row">
                <div class="distribution-label-group">
                  <span>${type}</span>
                  <span style="font-weight: 600;">${count} (${pct}%)</span>
                </div>
                <div class="progress-track">
                  <div class="progress-fill" style="width: ${pct}%;"></div>
                </div>
              </div>
            `;
          }).join('');
        }
      }

      // 10. Render DILG Previews & Printable Reports
      function renderDilgPreviews(totalRev, certsCount, waivedSub, residents, casesCount, settledCount, resRate) {
        const brgy = currentIdentity?.barangayName || 'Barangay San Isidro';
        const city = currentIdentity?.municipalityCity || 'City of San Isidro';
        const prov = currentIdentity?.province || 'Metropolitan Manila';

        // Update preview labels
        document.getElementById('dilg-jurisdiction').textContent = `${prov} • ${city}`;
        document.getElementById('dilg-brgy-name').textContent = brgy.toUpperCase();

        document.getElementById('dilg-total-certs').textContent = certsCount;
        document.getElementById('dilg-total-revenue').textContent = `₱${totalRev.toFixed(2)}`;
        document.getElementById('dilg-total-waived').textContent = `₱${waivedSub.toFixed(2)}`;

        document.getElementById('dilg-total-pop').textContent = residents.length;
        document.getElementById('dilg-voters').textContent = residents.filter(r => r.voterStatus === 'Registered').length;
        document.getElementById('dilg-seniors').textContent = residents.filter(r => (parseInt(r.age, 10) || 0) >= 60).length;
        document.getElementById('dilg-indigents').textContent = residents.filter(r => r.isIndigent || r.isFourPs).length;

        document.getElementById('dilg-cases').textContent = casesCount;
        document.getElementById('dilg-settled').textContent = settledCount;
        document.getElementById('dilg-ongoing').textContent = casesCount - settledCount;
        document.getElementById('dilg-rate').textContent = `${resRate}%`;

        // Signatory Names
        const captain = allOfficials.find(o => o.position === 'Punong Barangay');
        const secretary = allOfficials.find(o => o.position === 'Barangay Secretary');

        const captainName = captain ? captain.fullName.toUpperCase() : 'HON. PUNONG BARANGAY';
        const secName = secretary ? secretary.fullName.toUpperCase() : (currentAuthUser.fullName || 'BARANGAY SECRETARY').toUpperCase();

        document.getElementById('dilg-pb-sig').textContent = captainName;
        document.getElementById('dilg-sec-sig').textContent = secName;

        // Populate Printable Sheet
        document.getElementById('print-jurisdiction').textContent = `${prov} • ${city}`;
        document.getElementById('print-brgy-name').textContent = brgy.toUpperCase();
        document.getElementById('print-report-period').textContent = `Generated: ${new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`;

        document.getElementById('print-pop').textContent = residents.length;
        document.getElementById('print-voters').textContent = residents.filter(r => r.voterStatus === 'Registered').length;
        document.getElementById('print-seniors').textContent = residents.filter(r => (parseInt(r.age, 10) || 0) >= 60).length;
        document.getElementById('print-indigents').textContent = residents.filter(r => r.isIndigent).length;
        document.getElementById('print-fourps').textContent = residents.filter(r => r.isFourPs).length;
        document.getElementById('print-pwd').textContent = residents.filter(r => r.isPwd).length;

        document.getElementById('print-cases').textContent = casesCount;
        document.getElementById('print-settled').textContent = settledCount;
        document.getElementById('print-active-cases').textContent = casesCount - settledCount;
        document.getElementById('print-rate').textContent = `${resRate}%`;

        document.getElementById('print-pb-name').textContent = captainName;
        document.getElementById('print-sec-name').textContent = secName;

        // Print Revenue Table Rows
        const tbody = document.getElementById('print-revenue-tbody');
        tbody.innerHTML = `
          <tr style="border-bottom: 1px solid #ddd;">
            <td style="padding: 6px 10px;">Barangay Clearances</td>
            <td style="padding: 6px 10px; text-align: center;">${document.getElementById('rev-brgy-count').textContent}</td>
            <td style="padding: 6px 10px; text-align: right; font-weight: 600;">${document.getElementById('rev-brgy-clearance').textContent}</td>
          </tr>
          <tr style="border-bottom: 1px solid #ddd;">
            <td style="padding: 6px 10px;">Business Permits</td>
            <td style="padding: 6px 10px; text-align: center;">${document.getElementById('rev-business-count').textContent}</td>
            <td style="padding: 6px 10px; text-align: right; font-weight: 600;">${document.getElementById('rev-business-permit').textContent}</td>
          </tr>
          <tr style="border-bottom: 1px solid #ddd;">
            <td style="padding: 6px 10px;">Residency Certificates</td>
            <td style="padding: 6px 10px; text-align: center;">${document.getElementById('rev-residency-count').textContent}</td>
            <td style="padding: 6px 10px; text-align: right; font-weight: 600;">${document.getElementById('rev-residency').textContent}</td>
          </tr>
          <tr style="border-bottom: 2px solid #111; font-weight: 700; background: #f9f9f9;">
            <td style="padding: 8px 10px;">TOTAL COLLECTIONS</td>
            <td style="padding: 8px 10px; text-align: center;">${certsCount} Documents</td>
            <td style="padding: 8px 10px; text-align: right;">₱${totalRev.toFixed(2)}</td>
          </tr>
        `;
      }

      // 11. Print Trigger
      document.getElementById('btn-print-report').addEventListener('click', () => {
        window.print();
      });

      // 12. Export Consolidated CSV
      document.getElementById('btn-export-consolidated-csv').addEventListener('click', () => {
        const rows = [
          ['=== BARANGAY EXECUTIVE REPORT SUMMARY ==='],
          ['Barangay Jurisdiction', currentIdentity?.barangayName || 'Barangay San Isidro'],
          ['City / Municipality', currentIdentity?.municipalityCity || 'City of San Isidro'],
          ['Exported Date', new Date().toISOString()],
          [''],
          ['=== FINANCIAL REVENUE COLLECTIONS ==='],
          ['Total Revenue (PHP)', document.getElementById('stat-kpi-revenue').textContent.replace('₱', '')],
          ['Barangay Clearances', document.getElementById('rev-brgy-clearance').textContent.replace('₱', '')],
          ['Business Permits', document.getElementById('rev-business-permit').textContent.replace('₱', '')],
          ['Residency Certificates', document.getElementById('rev-residency').textContent.replace('₱', '')],
          ['Waived Social Welfare Subsidies', document.getElementById('rev-waived').textContent.replace('₱', '')],
          [''],
          ['=== POPULATION & DEMOGRAPHICS ==='],
          ['Total Population', allResidents.length],
          ['Registered Voters', allResidents.filter(r => r.voterStatus === 'Registered').length],
          ['Senior Citizens (60+)', allResidents.filter(r => (parseInt(r.age, 10) || 0) >= 60).length],
          ['Indigent Beneficiaries', allResidents.filter(r => r.isIndigent).length],
          ['4Ps Beneficiaries', allResidents.filter(r => r.isFourPs).length],
          [''],
          ['=== PEACE & ORDER (LUPON) ==='],
          ['Total Blotter Cases', allBlotters.length],
          ['Amicably Settled', allBlotters.filter(b => b.status === 'Amicably Settled').length],
          ['Resolution Rate', document.getElementById('stat-kpi-resolution-rate').textContent]
        ];

        const csvString = rows.map(r => r.join(',')).join('\n');
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `BarangayOS_Consolidated_Executive_Report_${new Date().toISOString().slice(0, 10)}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        showToast('Consolidated Executive Report CSV exported');
      });

      // Export Revenue Ledger CSV
      document.getElementById('btn-export-ledger-csv').addEventListener('click', () => {
        if (allCertificates.length === 0) {
          showToast('No revenue entries to export', 'warning');
          return;
        }

        const headers = ['OR_Number', 'TrackingCode', 'DateIssued', 'Recipient', 'DocumentType', 'Purpose', 'AmountPaid', 'Status'];
        const rows = [headers.join(',')];

        for (const c of allCertificates) {
          rows.push([
            `"${c.orNumber || ''}"`,
            `"${c.trackingCode || ''}"`,
            `"${c.issuedAt || ''}"`,
            `"${(c.recipientName || '').replace(/"/g, '""')}"`,
            `"${c.type || ''}"`,
            `"${(c.purpose || '').replace(/"/g, '""')}"`,
            c.amountPaid || '0.00',
            `"${c.status || 'Active'}"`
          ].join(','));
        }

        const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `BarangayOS_Revenue_Ledger_${new Date().toISOString().slice(0, 10)}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        showToast('Treasury Ledger CSV downloaded');
      });

      // 13. Initial Load
      await loadAllReportData();
    });
  </script>
</body>
</html>
