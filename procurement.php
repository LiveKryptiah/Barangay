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
  <title>Bids, Awards &amp; Procurement Management &bull; Barangay Management System</title>
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
    .proc-tab-nav {
      display: flex;
      gap: var(--spacing-xs);
      border-bottom: 1px solid var(--color-hairline-soft);
      margin-bottom: var(--spacing-md);
      overflow-x: auto;
    }

    .proc-tab-btn {
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

    .proc-tab-btn:hover {
      color: var(--color-ink);
    }

    .proc-tab-btn.active {
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

    /* Table Styles */
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

    /* LCRB Callout Box */
    .lcrb-callout {
      border: 1px solid #10b981;
      background: rgba(16, 185, 129, 0.05);
      border-radius: var(--rounded-md);
      padding: 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 16px;
    }

    .warning-callout {
      border: 1px solid #f59e0b;
      background: rgba(245, 158, 11, 0.05);
      border-radius: var(--rounded-md);
      padding: 14px 16px;
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
      font-size: 0.8125rem;
      color: var(--color-ink);
    }

    /* Budget Progress Bar */
    .progress-track {
      height: 7px;
      background: var(--color-canvas-soft);
      border-radius: 4px;
      overflow: hidden;
      margin: 8px 0;
      border: 1px solid var(--color-hairline-soft);
    }

    .progress-fill {
      height: 100%;
      background: var(--color-ink);
      border-radius: 4px;
      transition: width 0.3s ease;
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
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
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
                <h1 class="typography-heading-2">Bids, Awards &amp; Procurement Hub.</h1>
                <span class="badge-neutral" style="display: inline-flex; align-items: center; gap: 6px;">
                  <span style="width: 6px; height: 6px; border-radius: 50%; background: #f59e0b;"></span>
                  RA 9184 &bull; BAC Secretariat
                </span>
                <span class="badge-neutral" style="font-size: 0.6875rem;">DILG Full Disclosure</span>
              </div>
              <p class="typography-body-lg">
                Statutory procurement pipeline, sealed canvass quotation matrix, automated Lowest Calculated and Responsive Bid (LCRB) evaluation, and Annual Investment Program (AIP) budget tracking.
              </p>
            </div>
            <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
              <button class="button-outline" onclick="openNewBidModal();" style="height: 38px; padding: 0 16px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 5v14M5 12h14"/>
                </svg>
                <span>Record Canvass Bid</span>
              </button>
              <button class="button-primary" onclick="openNewPRModal();" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="12" y1="5" x2="12" y2="19"/>
                  <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Draft Purchase Request (PR)</span>
              </button>
            </div>
          </div>
        </section>

        <!-- Telemetry Ladder (4 Metric Cards) -->
        <section class="stats-ladder" aria-label="Procurement Telemetry">
          <!-- Card 1: Total AIP Appropriations -->
          <div class="card" style="padding: var(--spacing-md); border: 1px solid var(--color-hairline-soft);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <span class="typography-caption" style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--color-text-muted);">
                AIP Budget Appropriations
              </span>
              <span class="badge-neutral" style="font-size: 0.625rem;" id="stat-fy-badge">FY 2026</span>
            </div>
            <div class="typography-heading-1" style="font-size: 1.625rem; font-weight: 800; margin-top: 6px;" id="stat-total-budget">
              &#8369;0.00
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px; font-size: 0.75rem; color: var(--color-text-muted);">
              <span id="stat-sub-budget">Across statutory funds</span>
              <span style="font-weight: 600; color: var(--color-ink);" id="stat-budget-pct">0% Obligated</span>
            </div>
          </div>

          <!-- Card 2: Contract Obligations -->
          <div class="card" style="padding: var(--spacing-md); border: 1px solid var(--color-hairline-soft);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <span class="typography-caption" style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--color-text-muted);">
                Obligated / Committed Funds
              </span>
              <span class="badge-neutral" style="font-size: 0.625rem; color: #3b82f6;">Committed</span>
            </div>
            <div class="typography-heading-1" style="font-size: 1.625rem; font-weight: 800; margin-top: 6px;" id="stat-total-obligated">
              &#8369;0.00
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px; font-size: 0.75rem; color: var(--color-text-muted);">
              <span>Unallocated Balance:</span>
              <span style="font-weight: 600; color: #10b981;" id="stat-balance-remaining">&#8369;0.00</span>
            </div>
          </div>

          <!-- Card 3: Active Procurement Pipeline -->
          <div class="card" style="padding: var(--spacing-md); border: 1px solid var(--color-hairline-soft);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <span class="typography-caption" style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--color-text-muted);">
                Procurement Pipeline
              </span>
              <span class="badge-neutral" style="font-size: 0.625rem; color: #f59e0b;" id="stat-active-badge">Active</span>
            </div>
            <div class="typography-heading-1" style="font-size: 1.625rem; font-weight: 800; margin-top: 6px;" id="stat-active-projects">
              0
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px; font-size: 0.75rem; color: var(--color-text-muted);">
              <span id="stat-pipeline-sub">0 Canvass &bull; 0 Evaluated</span>
              <span style="font-weight: 600; color: var(--color-ink);" id="stat-total-projects">0 Total</span>
            </div>
          </div>

          <!-- Card 4: Statutory Fiscal Savings -->
          <div class="card" style="padding: var(--spacing-md); border: 1px solid var(--color-hairline-soft);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <span class="typography-caption" style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--color-text-muted);">
                Statutory Fiscal Savings
              </span>
              <span class="badge-neutral" style="font-size: 0.625rem; color: #10b981;">LCRB Gain</span>
            </div>
            <div class="typography-heading-1" style="font-size: 1.625rem; font-weight: 800; margin-top: 6px; color: #10b981;" id="stat-total-savings">
              &#8369;0.00
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px; font-size: 0.75rem; color: var(--color-text-muted);">
              <span>Savings vs ABC</span>
              <span style="font-weight: 600; color: var(--color-ink);" id="stat-awarded-contracts">0 Contracts</span>
            </div>
          </div>
        </section>

        <!-- Studio Tab Navigation -->
        <nav class="proc-tab-nav" aria-label="Procurement Views">
          <button type="button" class="proc-tab-btn active" id="tab-btn-pipeline" onclick="switchProcTab('pipeline');">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            <span>BAC Procurement Pipeline</span>
            <span class="badge-neutral" style="font-size: 0.625rem;" id="badge-pipeline-count">0</span>
          </button>
          <button type="button" class="proc-tab-btn" id="tab-btn-abstract" onclick="switchProcTab('abstract');">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            <span>Abstract of Bids &amp; Canvass Evaluator</span>
            <span class="badge-neutral" style="font-size: 0.625rem; color: #10b981;">LCRB</span>
          </button>
          <button type="button" class="proc-tab-btn" id="tab-btn-budget" onclick="switchProcTab('budget');">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <span>AIP Statutory Budget Ledger</span>
          </button>
        </nav>

        <!-- ======================================================= -->
        <!-- TAB 1: BAC PIPELINE & TRACKING VIEW                     -->
        <!-- ======================================================= -->
        <div id="view-pipeline" style="display: block;">
          <!-- Filter Toolbar -->
          <div class="studio-card" style="padding: 12px 16px; margin-bottom: var(--spacing-sm);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
              <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 260px;">
                <input type="text" id="pipeline-search" class="text-input" placeholder="Search PR No., PO No., Title, PhilGEPS Ref, Supplier..." oninput="filterPipeline();" style="font-size: 0.8125rem;">
              </div>
              <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <select id="pipeline-status-filter" class="text-input" onchange="filterPipeline();" style="width: auto; font-size: 0.8125rem;">
                  <option value="">All Project Statuses</option>
                  <option value="PR Draft">PR Draft</option>
                  <option value="Approved for Canvass">Approved for Canvass</option>
                  <option value="Canvass / RFQ Open">Canvass / RFQ Open</option>
                  <option value="Bids Evaluated">Bids Evaluated</option>
                  <option value="Awarded / PO Issued">Awarded / PO Issued</option>
                  <option value="Delivered & Inspected">Delivered &amp; Inspected</option>
                  <option value="Completed">Completed</option>
                  <option value="Cancelled">Cancelled</option>
                </select>

                <select id="pipeline-mode-filter" class="text-input" onchange="filterPipeline();" style="width: auto; font-size: 0.8125rem;">
                  <option value="">All Procurement Modes</option>
                  <option value="Small Value Procurement (SVP)">Small Value Procurement (SVP)</option>
                  <option value="Competitive Public Bidding">Competitive Public Bidding</option>
                  <option value="Shopping">Shopping</option>
                  <option value="Emergency Cases">Emergency Cases</option>
                  <option value="Direct Contracting">Direct Contracting</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Procurement Table -->
          <div class="data-table-wrap">
            <table class="data-table" id="pipeline-table">
              <thead>
                <tr>
                  <th>PR / PO Reference</th>
                  <th>Project Title &amp; End-User</th>
                  <th>Procurement Mode</th>
                  <th>Fund Source</th>
                  <th>ABC Amount</th>
                  <th>Bids</th>
                  <th>Status</th>
                  <th>Awarded Contractor</th>
                  <th style="text-align: right;">Statutory Actions</th>
                </tr>
              </thead>
              <tbody id="pipeline-table-body">
                <!-- Dynamically Populated -->
              </tbody>
            </table>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; font-size: 0.75rem; color: var(--color-text-muted);">
            <span>Showing <strong id="pipeline-count-text" style="color: var(--color-ink);">0</strong> procurement projects</span>
            <span>Philippine RA 9184 &bull; BAC Standard Annexes</span>
          </div>
        </div>

        <!-- ======================================================= -->
        <!-- TAB 2: ABSTRACT OF BIDS & CANVASS EVALUATOR             -->
        <!-- ======================================================= -->
        <div id="view-abstract" style="display: none;">
          <div class="studio-card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
              <div style="display: flex; align-items: center; gap: 12px;">
                <label for="abstract-project-select" style="font-size: 0.8125rem; font-weight: 600; color: var(--color-ink); white-space: nowrap;">
                  Select Project to Evaluate:
                </label>
                <select id="abstract-project-select" class="text-input" onchange="loadAbstractForProject();" style="min-width: 320px; font-size: 0.8125rem;">
                  <option value="">-- Choose Procurement Project --</option>
                </select>
              </div>
              <div style="display: flex; gap: 8px;">
                <button type="button" class="button-outline" onclick="openNewBidModalForSelected();" style="font-size: 0.75rem; height: 32px; padding: 0 12px;">
                  + Add Supplier Bid
                </button>
                <button type="button" class="button-outline" onclick="printAbstractOfBids();" style="font-size: 0.75rem; height: 32px; padding: 0 12px;">
                  Print Abstract (Annex H)
                </button>
                <button type="button" class="button-outline" onclick="printBACResolution();" style="font-size: 0.75rem; height: 32px; padding: 0 12px;">
                  Print BAC Resolution
                </button>
              </div>
            </div>

            <!-- Project Details Strip -->
            <div id="abstract-project-details" style="background: var(--color-canvas-soft); border: 1px solid var(--color-hairline-soft); border-radius: var(--rounded-sm); padding: 12px 16px; margin-bottom: 16px; display: none;">
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; font-size: 0.8125rem;">
                <div>
                  <span style="color: var(--color-text-muted); font-size: 0.6875rem; text-transform: uppercase;">Approved Budget (ABC)</span>
                  <div style="font-weight: 700; font-size: 1rem; color: var(--color-ink);" id="abs-abc">&#8369;0.00</div>
                </div>
                <div>
                  <span style="color: var(--color-text-muted); font-size: 0.6875rem; text-transform: uppercase;">Procurement Mode</span>
                  <div style="font-weight: 600;" id="abs-mode">--</div>
                </div>
                <div>
                  <span style="color: var(--color-text-muted); font-size: 0.6875rem; text-transform: uppercase;">Fund Source</span>
                  <div style="font-weight: 600;" id="abs-fund">--</div>
                </div>
                <div>
                  <span style="color: var(--color-text-muted); font-size: 0.6875rem; text-transform: uppercase;">PhilGEPS Reference</span>
                  <div style="font-weight: 600; font-family: monospace;" id="abs-philgeps">--</div>
                </div>
                <div>
                  <span style="color: var(--color-text-muted); font-size: 0.6875rem; text-transform: uppercase;">Current Project Status</span>
                  <div id="abs-status-badge" style="margin-top: 2px;">--</div>
                </div>
              </div>
            </div>

            <!-- Statutory Warning / LCRB Callout -->
            <div id="abstract-lcrb-banner"></div>

            <!-- Canvass Table -->
            <div class="data-table-wrap">
              <table class="data-table" id="abstract-table">
                <thead>
                  <tr>
                    <th>Rank</th>
                    <th>Supplier / Contractor</th>
                    <th>TIN Number</th>
                    <th>Contact Info</th>
                    <th>Quotation Amount</th>
                    <th>Variance vs ABC</th>
                    <th>Compliance Status</th>
                    <th>Remarks / Evaluation</th>
                    <th style="text-align: right;">Action</th>
                  </tr>
                </thead>
                <tbody id="abstract-table-body">
                  <tr>
                    <td colspan="9" style="text-align: center; padding: 32px; color: var(--color-text-muted);">
                      Please select a procurement project above to evaluate supplier quotations.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Award Contract Bar -->
            <div id="abstract-award-bar" style="margin-top: 16px; padding: 14px 18px; border: 1px solid var(--color-hairline); border-radius: var(--rounded-sm); display: none; align-items: center; justify-content: space-between; background: var(--color-canvas);">
              <div>
                <strong style="color: var(--color-ink); font-size: 0.875rem;">Lowest Calculated &amp; Responsive Bidder Selected</strong>
                <p style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 2px;">
                  Ready to award contract, generate Purchase Order (PO), and obligate funds against AIP appropriation.
                </p>
              </div>
              <button type="button" class="button-primary" onclick="openAwardModalForSelected();" style="font-size: 0.8125rem;">
                Award Contract &amp; Issue PO &rarr;
              </button>
            </div>
          </div>
        </div>

        <!-- ======================================================= -->
        <!-- TAB 3: AIP STATUTORY BUDGET LEDGER                      -->
        <!-- ======================================================= -->
        <div id="view-budget" style="display: none;">
          <div class="studio-card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
              <div>
                <h3 class="typography-heading-3" style="font-size: 1rem;">Annual Investment Program (AIP) Statutory Funds</h3>
                <p style="font-size: 0.75rem; color: var(--color-text-muted);">
                  Mandatory statutory appropriations compliant with RA 7160 (Local Government Code) and DILG memorandum circulars.
                </p>
              </div>
              <button type="button" class="button-primary" onclick="openNewBudgetModal();" style="font-size: 0.8125rem; height: 34px;">
                + Add AIP Appropriation
              </button>
            </div>

            <div class="data-table-wrap">
              <table class="data-table" id="budget-table">
                <thead>
                  <tr>
                    <th>FY</th>
                    <th>Fund Source</th>
                    <th>Program / Project Title</th>
                    <th>Implementing Committee</th>
                    <th>Approved Budget</th>
                    <th>Obligated Amount</th>
                    <th>Remaining Balance</th>
                    <th style="width: 140px;">Utilization</th>
                    <th style="text-align: right;">Action</th>
                  </tr>
                </thead>
                <tbody id="budget-table-body">
                  <!-- Dynamically Populated -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- ======================================================= -->
  <!-- MODAL 1: CREATE / EDIT PURCHASE REQUEST (PR)             -->
  <!-- ======================================================= -->
  <dialog id="pr-modal" class="modal-dialog" style="max-width: 650px; width: 92%; border-radius: var(--rounded-md); border: 1px solid var(--color-hairline); background: var(--color-canvas); padding: 0; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
    <div style="padding: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); display: flex; justify-content: space-between; align-items: center;">
      <h3 class="typography-heading-3" style="font-size: 1.0625rem;" id="pr-modal-title">Draft Purchase Request (PR)</h3>
      <button type="button" class="icon-button" onclick="document.getElementById('pr-modal').close();">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form id="pr-form" onsubmit="handlePRFormSubmit(event);" style="padding: var(--spacing-md);">
      <input type="hidden" id="pr-id">

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
        <div>
          <label class="form-label" for="pr-number">PR Number (Auto)</label>
          <input type="text" id="pr-number" class="text-input" placeholder="PR-2026-XXXX">
        </div>
        <div>
          <label class="form-label" for="pr-classification">Classification *</label>
          <select id="pr-classification" class="text-input" required>
            <option value="Goods & Supplies">Goods &amp; Supplies</option>
            <option value="Infrastructure Projects">Infrastructure Projects</option>
            <option value="Consulting Services">Consulting Services</option>
          </select>
        </div>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="pr-title">Project Title / Scope of Work *</label>
        <input type="text" id="pr-title" class="text-input" placeholder="e.g., Supply & Installation of Solar Streetlights..." required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
        <div>
          <label class="form-label" for="pr-mode">Procurement Mode (RA 9184) *</label>
          <select id="pr-mode" class="text-input" required>
            <option value="Small Value Procurement (SVP)">Small Value Procurement (SVP, Sec. 53.9)</option>
            <option value="Shopping">Shopping (Sec. 52.1)</option>
            <option value="Competitive Public Bidding">Competitive Public Bidding</option>
            <option value="Emergency Cases">Emergency Cases (Sec. 53.2)</option>
            <option value="Direct Contracting">Direct Contracting (Sec. 50)</option>
          </select>
        </div>
        <div>
          <label class="form-label" for="pr-abc">Approved Budget for Contract (ABC &#8369;) *</label>
          <input type="number" id="pr-abc" class="text-input" step="0.01" min="1" placeholder="0.00" required>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
        <div>
          <label class="form-label" for="pr-budget-id">Linked AIP Statutory Fund *</label>
          <select id="pr-budget-id" class="text-input" required onchange="handlePRFundChange();">
            <option value="">-- Select AIP Budget Item --</option>
          </select>
        </div>
        <div>
          <label class="form-label" for="pr-committee">End-User / Implementing Committee *</label>
          <input type="text" id="pr-committee" class="text-input" value="Committee on Infrastructure" required>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
        <div>
          <label class="form-label" for="pr-philgeps">PhilGEPS Reference No.</label>
          <input type="text" id="pr-philgeps" class="text-input" placeholder="PHILGEPS-2026-XXXXX">
        </div>
        <div>
          <label class="form-label" for="pr-delivery-days">Delivery Period (Calendar Days) *</label>
          <input type="number" id="pr-delivery-days" class="text-input" value="15" min="1" required>
        </div>
      </div>

      <div style="margin-bottom: 14px;">
        <label class="form-label" for="pr-status">Initial Status *</label>
        <select id="pr-status" class="text-input" required>
          <option value="PR Draft">PR Draft (Internal Review)</option>
          <option value="Approved for Canvass">Approved for Canvass</option>
          <option value="Canvass / RFQ Open">Canvass / RFQ Open (PhilGEPS Posted)</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs);">
        <button type="button" class="button-outline" onclick="document.getElementById('pr-modal').close();">Cancel</button>
        <button type="submit" class="button-primary" id="btn-save-pr">Save Purchase Request</button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================= -->
  <!-- MODAL 2: ADD CANVASS QUOTATION / BID                     -->
  <!-- ======================================================= -->
  <dialog id="bid-modal" class="modal-dialog" style="max-width: 540px; width: 90%; border-radius: var(--rounded-md); border: 1px solid var(--color-hairline); background: var(--color-canvas); padding: 0; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
    <div style="padding: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); display: flex; justify-content: space-between; align-items: center;">
      <h3 class="typography-heading-3" style="font-size: 1.0625rem;">Record Supplier Canvass Quotation</h3>
      <button type="button" class="icon-button" onclick="document.getElementById('bid-modal').close();">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form id="bid-form" onsubmit="handleBidFormSubmit(event);" style="padding: var(--spacing-md);">
      <div style="margin-bottom: 12px;">
        <label class="form-label" for="bid-project-id">Target Procurement Project *</label>
        <select id="bid-project-id" class="text-input" required>
          <option value="">-- Choose Project --</option>
        </select>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
        <div>
          <label class="form-label" for="bid-supplier">Supplier / Business Name *</label>
          <input type="text" id="bid-supplier" class="text-input" placeholder="e.g. Acme Trading Corp." required>
        </div>
        <div>
          <label class="form-label" for="bid-tin">Tax Identification No. (TIN)</label>
          <input type="text" id="bid-tin" class="text-input" placeholder="000-000-000-000">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
        <div>
          <label class="form-label" for="bid-contact">Authorized Contact Person</label>
          <input type="text" id="bid-contact" class="text-input" placeholder="Full name">
        </div>
        <div>
          <label class="form-label" for="bid-phone">Contact Number</label>
          <input type="text" id="bid-phone" class="text-input" placeholder="0917-000-0000">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
        <div>
          <label class="form-label" for="bid-amount">Quotation Amount (&#8369;) *</label>
          <input type="number" id="bid-amount" class="text-input" step="0.01" min="1" placeholder="0.00" required>
        </div>
        <div>
          <label class="form-label" for="bid-compliance">Compliance Status *</label>
          <select id="bid-compliance" class="text-input" required>
            <option value="Responsive">Responsive (Complying)</option>
            <option value="Non-Responsive">Non-Responsive (Above ABC / Incomplete)</option>
            <option value="Disqualified">Disqualified</option>
          </select>
        </div>
      </div>

      <div style="margin-bottom: 14px;">
        <label class="form-label" for="bid-remarks">Technical Evaluation Remarks</label>
        <textarea id="bid-remarks" class="text-input" rows="2" placeholder="e.g., Quotation within ABC; complete DTI, Mayor's Permit, and PhilGEPS registration submitted."></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs);">
        <button type="button" class="button-outline" onclick="document.getElementById('bid-modal').close();">Cancel</button>
        <button type="submit" class="button-primary">Save Canvass Quotation</button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================= -->
  <!-- MODAL 3: AWARD CONTRACT & ISSUE PO                      -->
  <!-- ======================================================= -->
  <dialog id="award-modal" class="modal-dialog" style="max-width: 500px; width: 90%; border-radius: var(--rounded-md); border: 1px solid var(--color-hairline); background: var(--color-canvas); padding: 0; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
    <div style="padding: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); display: flex; justify-content: space-between; align-items: center;">
      <h3 class="typography-heading-3" style="font-size: 1.0625rem;">Award Contract &amp; Issue PO</h3>
      <button type="button" class="icon-button" onclick="document.getElementById('award-modal').close();">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form id="award-form" onsubmit="handleAwardFormSubmit(event);" style="padding: var(--spacing-md);">
      <input type="hidden" id="award-project-id">

      <div style="background: var(--color-canvas-soft); border: 1px solid var(--color-hairline-soft); padding: 12px; border-radius: var(--rounded-sm); margin-bottom: 14px; font-size: 0.8125rem;">
        <div style="color: var(--color-text-muted); font-size: 0.6875rem;">PROJECT TITLE</div>
        <div style="font-weight: 700; color: var(--color-ink);" id="award-modal-project-title">--</div>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="award-supplier">Winning Bidder / Supplier *</label>
        <input type="text" id="award-supplier" class="text-input" required readonly style="background: var(--color-canvas-soft);">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
        <div>
          <label class="form-label" for="award-amount">Contract Award Amount (&#8369;) *</label>
          <input type="number" id="award-amount" class="text-input" step="0.01" required readonly style="background: var(--color-canvas-soft);">
        </div>
        <div>
          <label class="form-label" for="award-po-number">PO Number (Generated)</label>
          <input type="text" id="award-po-number" class="text-input" placeholder="PO-2026-XXXX">
        </div>
      </div>

      <div style="margin-bottom: 14px;">
        <label class="form-label" for="award-delivery-days">Delivery Period (Calendar Days) *</label>
        <input type="number" id="award-delivery-days" class="text-input" value="15" min="1" required>
      </div>

      <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid #10b981; border-radius: var(--rounded-sm); padding: 10px; font-size: 0.75rem; color: var(--color-ink); margin-bottom: 14px;">
        &#10003; <strong>BAC Statutory Action:</strong> Project status will transition to <em>"Awarded / PO Issued"</em>, the contract amount will be automatically obligated against the linked AIP fund, and official printable BAC Resolution &amp; PO documents will be activated.
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs);">
        <button type="button" class="button-outline" onclick="document.getElementById('award-modal').close();">Cancel</button>
        <button type="submit" class="button-primary">Confirm Award &amp; Issue PO</button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================= -->
  <!-- MODAL 4: ADD AIP BUDGET ALLOCATION                      -->
  <!-- ======================================================= -->
  <dialog id="budget-modal" class="modal-dialog" style="max-width: 540px; width: 90%; border-radius: var(--rounded-md); border: 1px solid var(--color-hairline); background: var(--color-canvas); padding: 0; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
    <div style="padding: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); display: flex; justify-content: space-between; align-items: center;">
      <h3 class="typography-heading-3" style="font-size: 1.0625rem;">Add AIP Budget Appropriation</h3>
      <button type="button" class="icon-button" onclick="document.getElementById('budget-modal').close();">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form id="budget-form" onsubmit="handleBudgetFormSubmit(event);" style="padding: var(--spacing-md);">
      <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px; margin-bottom: 12px;">
        <div>
          <label class="form-label" for="alloc-year">Fiscal Year *</label>
          <input type="number" id="alloc-year" class="text-input" value="2026" min="2020" max="2035" required>
        </div>
        <div>
          <label class="form-label" for="alloc-fund">Statutory Fund Source *</label>
          <select id="alloc-fund" class="text-input" required>
            <option value="20% Barangay Development Fund">20% Barangay Development Fund (BDF)</option>
            <option value="5% BDRRM Calamity Fund">5% BDRRM Calamity Fund</option>
            <option value="10% SK Youth Development Fund">10% SK Youth Development Fund</option>
            <option value="5% GAD Fund">5% Gender and Development (GAD) Fund</option>
            <option value="General Fund">General Administrative Fund</option>
            <option value="1% Senior / PWD Fund">1% Senior Citizens &amp; PWD Fund</option>
            <option value="1% LCPC Fund">1% LCPC Child Protection Fund</option>
          </select>
        </div>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="alloc-title">Program / Project / Activity (PPA) Title *</label>
        <input type="text" id="alloc-title" class="text-input" placeholder="e.g., Barangay Solar Streetlighting Network..." required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
        <div>
          <label class="form-label" for="alloc-committee">Implementing Committee *</label>
          <input type="text" id="alloc-committee" class="text-input" value="Committee on Appropriations" required>
        </div>
        <div>
          <label class="form-label" for="alloc-approved">Approved Budget (&#8369;) *</label>
          <input type="number" id="alloc-approved" class="text-input" step="0.01" min="1000" placeholder="0.00" required>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs);">
        <button type="button" class="button-outline" onclick="document.getElementById('budget-modal').close();">Cancel</button>
        <button type="submit" class="button-primary">Save Appropriation</button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================= -->
  <!-- STATUTORY PRINT CONTAINER (OFFICIAL RA 9184 FORMS)     -->
  <!-- ======================================================= -->
  <div id="print-statutory-container" style="display: none;">
    <!-- Content injected dynamically via print engine -->
  </div>

  <!-- Scripts: REST API Client Bridge for PHP/MySQL Parity -->
  <script src="js/api.js"></script>
  <script src="js/auth.js"></script>
  <script src="js/components/sidebar.js"></script>
  <script src="js/components/header.js"></script>

  <script>
    // State management
    let allProjects = [];
    let allBudgets = [];
    let allBids = [];
    let selectedProjectId = null;

    document.addEventListener('DOMContentLoaded', async () => {
      // 1. App Shell Sidebar
      if (window.AppSidebar) {
        await AppSidebar.render('procurement');
      }

      // 2. Load data via REST API bridge
      await refreshAllData();
    });

    // Tab Switcher
    window.switchProcTab = function(tabName) {
      const tabs = ['pipeline', 'abstract', 'budget'];
      tabs.forEach(t => {
        const pane = document.getElementById(`view-${t}`);
        const btn = document.getElementById(`tab-btn-${t}`);
        if (pane) pane.style.display = (t === tabName) ? 'block' : 'none';
        if (btn) btn.classList.toggle('active', t === tabName);
      });

      if (tabName === 'abstract' && selectedProjectId) {
        loadAbstractForProject();
      }
    };

    // Refresh Data
    async function refreshAllData() {
      if (!window.barangayDB) return;

      try {
        allProjects = await barangayDB.getAll('procurement_projects');
        allBudgets  = await barangayDB.getAll('budget_allocations');
        allBids     = await barangayDB.getAll('procurement_bids');

        updateTelemetry();
        renderPipelineTable();
        populateProjectSelects();
        renderBudgetTable();
      } catch (err) {
        console.error('Error loading procurement data:', err);
      }
    }

    // Format Peso Currency
    function formatCurrency(num) {
      const val = parseFloat(num) || 0;
      return '&#8369;' + val.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Telemetry computation
    function updateTelemetry() {
      const totalBudget = allBudgets.reduce((sum, b) => sum + (parseFloat(b.approvedBudget || b.approved_budget) || 0), 0);
      const totalObligated = allBudgets.reduce((sum, b) => sum + (parseFloat(b.obligatedAmount || b.obligated_amount) || 0), 0);
      const balance = Math.max(0, totalBudget - totalObligated);
      const pct = totalBudget > 0 ? ((totalObligated / totalBudget) * 100).toFixed(1) : '0.0';

      document.getElementById('stat-total-budget').innerHTML = formatCurrency(totalBudget);
      document.getElementById('stat-budget-pct').textContent = `${pct}% Obligated`;

      document.getElementById('stat-total-obligated').innerHTML = formatCurrency(totalObligated);
      document.getElementById('stat-balance-remaining').innerHTML = formatCurrency(balance);

      const activeProjects = allProjects.filter(p => !['Completed', 'Cancelled', 'Delivered & Inspected'].includes(p.status));
      document.getElementById('stat-active-projects').textContent = activeProjects.length;
      document.getElementById('stat-total-projects').textContent = `${allProjects.length} Total`;

      const canvassCount = allProjects.filter(p => (p.status || '').includes('Canvass')).length;
      const evalCount = allProjects.filter(p => p.status === 'Bids Evaluated').length;
      document.getElementById('stat-pipeline-sub').textContent = `${canvassCount} Canvass \u2022 ${evalCount} Evaluated`;
      document.getElementById('badge-pipeline-count').textContent = allProjects.length;

      // Savings Calculation
      let savings = 0;
      let awardedCount = 0;
      allProjects.forEach(p => {
        if (p.winningAmount && p.abcAmount && ['Awarded / PO Issued', 'Delivered & Inspected', 'Completed'].includes(p.status)) {
          const s = parseFloat(p.abcAmount) - parseFloat(p.winningAmount);
          if (s > 0) savings += s;
          awardedCount++;
        }
      });
      document.getElementById('stat-total-savings').innerHTML = formatCurrency(savings);
      document.getElementById('stat-awarded-contracts').textContent = `${awardedCount} Contracts`;
    }

    // Populate Selects
    function populateProjectSelects() {
      const absSelect = document.getElementById('abstract-project-select');
      const bidSelect = document.getElementById('bid-project-id');
      const prBudgetSelect = document.getElementById('pr-budget-id');

      const projOpts = '<option value="">-- Choose Procurement Project --</option>' +
        allProjects.map(p => `<option value="${p.id}">${p.prNumber || p.pr_number} - ${p.projectTitle || p.project_title}</option>`).join('');

      if (absSelect) absSelect.innerHTML = projOpts;
      if (bidSelect) bidSelect.innerHTML = projOpts;

      if (prBudgetSelect) {
        prBudgetSelect.innerHTML = '<option value="">-- Select AIP Budget Item --</option>' +
          allBudgets.map(b => `<option value="${b.id}">${b.fiscalYear || b.fiscal_year} &bull; ${b.fundSource || b.fund_source} - ${b.programTitle || b.program_title} (&#8369;${(b.approvedBudget || b.approved_budget).toLocaleString()})</option>`).join('');
      }

      if (selectedProjectId && absSelect) {
        absSelect.value = selectedProjectId;
      }
    }

    // Filter Pipeline Table
    window.filterPipeline = function() {
      const q = (document.getElementById('pipeline-search').value || '').toLowerCase().trim();
      const statusFilter = document.getElementById('pipeline-status-filter').value;
      const modeFilter = document.getElementById('pipeline-mode-filter').value;

      const filtered = allProjects.filter(p => {
        const matchesQ = !q ||
          (p.prNumber || '').toLowerCase().includes(q) ||
          (p.poNumber || '').toLowerCase().includes(q) ||
          (p.projectTitle || '').toLowerCase().includes(q) ||
          (p.philgepsRef || '').toLowerCase().includes(q) ||
          (p.winningBidder || '').toLowerCase().includes(q);

        const matchesStatus = !statusFilter || p.status === statusFilter;
        const matchesMode = !modeFilter || (p.procurementMode || p.procurement_mode) === modeFilter;

        return matchesQ && matchesStatus && matchesMode;
      });

      renderPipelineTable(filtered);
    };

    // Render Pipeline Table
    function renderPipelineTable(records = null) {
      const list = records || allProjects;
      const tbody = document.getElementById('pipeline-table-body');
      document.getElementById('pipeline-count-text').textContent = list.length;

      if (!tbody) return;

      if (list.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="9" style="text-align: center; padding: 32px; color: var(--color-text-muted);">
              No procurement projects found matching your search criteria.
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = list.map(p => {
        const bids = allBids.filter(b => b.projectId === p.id || b.project_id === p.id);
        const bidCount = bids.length;

        let statusBadge = 'badge-neutral';
        if (p.status === 'Canvass / RFQ Open') statusBadge = 'badge-blue';
        if (p.status === 'Bids Evaluated') statusBadge = 'badge-purple';
        if (p.status === 'Awarded / PO Issued') statusBadge = 'badge-green';
        if (p.status === 'Delivered & Inspected' || p.status === 'Completed') statusBadge = 'badge-green';

        return `
          <tr>
            <td>
              <strong style="color: var(--color-ink); font-family: monospace;">${p.prNumber || p.pr_number}</strong>
              ${p.poNumber ? `<br><span style="font-size: 0.6875rem; color: #10b981; font-family: monospace; font-weight: 600;">${p.poNumber}</span>` : ''}
            </td>
            <td>
              <div style="font-weight: 600; color: var(--color-ink);">${p.projectTitle || p.project_title}</div>
              <div style="font-size: 0.6875rem; color: var(--color-text-muted);">${p.endUserCommittee || p.end_user_committee || 'General'}</div>
            </td>
            <td><span class="badge-neutral" style="font-size: 0.6875rem;">${p.procurementMode || p.procurement_mode}</span></td>
            <td style="font-size: 0.75rem;">${p.fundSource || p.fund_source}</td>
            <td style="font-weight: 700; color: var(--color-ink);">${formatCurrency(p.abcAmount || p.abc_amount)}</td>
            <td>
              <button type="button" class="badge-neutral" style="cursor: pointer;" onclick="openAbstractView(${p.id});" title="Evaluate bids">
                ${bidCount} ${bidCount === 1 ? 'bid' : 'bids'}
              </button>
            </td>
            <td><span class="${statusBadge}" style="font-size: 0.6875rem;">${p.status}</span></td>
            <td>
              ${p.winningBidder ? `
                <div style="font-weight: 600; font-size: 0.75rem;">${p.winningBidder}</div>
                <div style="color: #10b981; font-weight: 700; font-size: 0.6875rem;">${formatCurrency(p.winningAmount)}</div>
              ` : '<span style="color: var(--color-text-muted); font-size: 0.75rem;">--</span>'}
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <div style="display: inline-flex; gap: 4px;">
                <button type="button" class="button-outline" style="height: 28px; padding: 0 8px; font-size: 0.6875rem;" onclick="openAbstractView(${p.id});" title="View &amp; Evaluate Abstract of Bids">
                  Abstract
                </button>
                <button type="button" class="button-outline" style="height: 28px; padding: 0 8px; font-size: 0.6875rem;" onclick="printPRDocument(${p.id});" title="Print Purchase Request (PR)">
                  PR
                </button>
                ${p.poNumber ? `
                  <button type="button" class="button-outline" style="height: 28px; padding: 0 8px; font-size: 0.6875rem; color: #10b981;" onclick="printPODocument(${p.id});" title="Print Purchase Order (PO)">
                    PO
                  </button>
                  <button type="button" class="button-outline" style="height: 28px; padding: 0 8px; font-size: 0.6875rem;" onclick="printIARDocument(${p.id});" title="Print Inspection &amp; Acceptance Report (IAR)">
                    IAR
                  </button>
                ` : ''}
                <button type="button" class="icon-button" style="width: 28px; height: 28px;" onclick="deleteProject(${p.id});" title="Delete Project">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Open Abstract View for a specific Project
    window.openAbstractView = function(projectId) {
      selectedProjectId = projectId;
      switchProcTab('abstract');
      document.getElementById('abstract-project-select').value = projectId;
      loadAbstractForProject();
    };

    // Load Abstract For Project
    window.loadAbstractForProject = function() {
      const select = document.getElementById('abstract-project-select');
      const pid = parseInt(select.value);
      selectedProjectId = pid || null;

      const detailsBox = document.getElementById('abstract-project-details');
      const tbody = document.getElementById('abstract-table-body');
      const banner = document.getElementById('abstract-lcrb-banner');
      const awardBar = document.getElementById('abstract-award-bar');

      if (!pid) {
        detailsBox.style.display = 'none';
        banner.innerHTML = '';
        awardBar.style.display = 'none';
        tbody.innerHTML = `
          <tr>
            <td colspan="9" style="text-align: center; padding: 32px; color: var(--color-text-muted);">
              Please select a procurement project above to evaluate supplier quotations.
            </td>
          </tr>
        `;
        return;
      }

      const project = allProjects.find(p => p.id === pid);
      if (!project) return;

      // Populate details strip
      detailsBox.style.display = 'block';
      document.getElementById('abs-abc').innerHTML = formatCurrency(project.abcAmount || project.abc_amount);
      document.getElementById('abs-mode').textContent = project.procurementMode || project.procurement_mode;
      document.getElementById('abs-fund').textContent = project.fundSource || project.fund_source;
      document.getElementById('abs-philgeps').textContent = project.philgepsRef || project.philgeps_ref || 'N/A';
      document.getElementById('abs-status-badge').innerHTML = `<span class="badge-neutral">${project.status}</span>`;

      // Get bids for this project
      const bids = allBids.filter(b => b.projectId === pid || b.project_id === pid);

      // Sort bids by quotation amount ASC
      bids.sort((a, b) => (parseFloat(a.quotationAmount || a.quotation_amount) || 0) - (parseFloat(b.quotationAmount || b.quotation_amount) || 0));

      const responsiveBids = bids.filter(b => (b.complianceStatus || b.compliance_status) === 'Responsive');
      const lcrb = responsiveBids.length > 0 ? responsiveBids[0] : null;

      // Update banner with statutory warning or LCRB callout
      let bannerHtml = '';
      if (['Small Value Procurement (SVP)', 'Shopping'].includes(project.procurementMode || project.procurement_mode) && bids.length < 3) {
        bannerHtml += `
          <div class="warning-callout">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" style="flex-shrink: 0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div>
              <strong>Philippine RA 9184 Statutory Notice (Sec. 53.9):</strong> Small Value Procurement &amp; Shopping require a minimum of <strong>3 price quotations</strong> from bona fide suppliers prior to BAC award. Current count: <strong>${bids.length} / 3 quotations</strong>.
            </div>
          </div>
        `;
      }

      if (lcrb) {
        const abc = parseFloat(project.abcAmount || project.abc_amount) || 0;
        const lcrbQuote = parseFloat(lcrb.quotationAmount || lcrb.quotation_amount) || 0;
        const savings = Math.max(0, abc - lcrbQuote);
        const savingsPct = abc > 0 ? ((savings / abc) * 100).toFixed(2) : 0;

        bannerHtml += `
          <div class="lcrb-callout">
            <div>
              <div style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #10b981;">
                &#10003; Lowest Calculated and Responsive Bid (LCRB) Auto-Detected
              </div>
              <div style="font-size: 1.0625rem; font-weight: 700; color: var(--color-ink); margin-top: 2px;">
                ${lcrb.supplierName || lcrb.supplier_name} &bull; ${formatCurrency(lcrbQuote)}
              </div>
              <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 2px;">
                Fiscal Savings: <strong>${formatCurrency(savings)} (${savingsPct}%)</strong> below Approved Budget (ABC).
              </div>
            </div>
            <div>
              ${project.status !== 'Awarded / PO Issued' && project.status !== 'Delivered & Inspected' && project.status !== 'Completed' ? `
                <button type="button" class="button-primary" onclick="openAwardModalForBid(${lcrb.id});" style="font-size: 0.8125rem;">
                  Award to ${lcrb.supplierName || lcrb.supplier_name}
                </button>
              ` : `
                <span class="badge-green" style="font-size: 0.8125rem;">Contract Awarded (${project.poNumber})</span>
              `}
            </div>
          </div>
        `;

        if (project.status !== 'Awarded / PO Issued' && project.status !== 'Delivered & Inspected' && project.status !== 'Completed') {
          awardBar.style.display = 'flex';
        } else {
          awardBar.style.display = 'none';
        }
      } else {
        awardBar.style.display = 'none';
      }

      banner.innerHTML = bannerHtml;

      if (bids.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="9" style="text-align: center; padding: 32px; color: var(--color-text-muted);">
              No canvass quotations have been recorded for this project yet. Click "+ Add Supplier Bid" above.
            </td>
          </tr>
        `;
        return;
      }

      const abc = parseFloat(project.abcAmount || project.abc_amount) || 0;

      tbody.innerHTML = bids.map((b, idx) => {
        const quote = parseFloat(b.quotationAmount || b.quotation_amount) || 0;
        const diff = quote - abc;
        const diffPct = abc > 0 ? ((diff / abc) * 100).toFixed(1) : 0;
        const varianceDisplay = diff <= 0
          ? `<span style="color: #10b981; font-weight: 600;">-${Math.abs(diffPct)}% (${formatCurrency(Math.abs(diff))})</span>`
          : `<span style="color: #ef4444; font-weight: 600;">+${diffPct}% (Above ABC)</span>`;

        const isLowest = lcrb && lcrb.id === b.id;
        const isResponsive = (b.complianceStatus || b.compliance_status) === 'Responsive';

        return `
          <tr style="${isLowest ? 'background-color: rgba(16, 185, 129, 0.04);' : ''}">
            <td>
              ${isLowest ? '<span class="badge-green" style="font-size: 0.6875rem;">1st (LCRB)</span>' : `<span class="badge-neutral" style="font-size: 0.6875rem;">#${idx + 1}</span>`}
            </td>
            <td>
              <strong style="color: var(--color-ink);">${b.supplierName || b.supplier_name}</strong>
            </td>
            <td style="font-family: monospace; font-size: 0.75rem;">${b.tinNumber || b.tin_number || '--'}</td>
            <td style="font-size: 0.75rem;">
              ${b.contactPerson || b.contact_person || ''}<br>
              <span style="color: var(--color-text-muted);">${b.phone || b.contact_no || ''}</span>
            </td>
            <td style="font-weight: 700; color: var(--color-ink); font-size: 0.875rem;">
              ${formatCurrency(quote)}
            </td>
            <td style="font-size: 0.75rem;">${varianceDisplay}</td>
            <td>
              <span class="${isResponsive ? 'badge-green' : 'badge-neutral'}" style="font-size: 0.6875rem;">
                ${b.complianceStatus || b.compliance_status}
              </span>
            </td>
            <td style="font-size: 0.75rem; max-width: 200px;">
              ${b.remarks || 'Standard submission'}
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <button type="button" class="icon-button" style="width: 26px; height: 26px;" onclick="deleteBid(${b.id});" title="Delete quotation">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </td>
          </tr>
        `;
      }).join('');
    };

    // Open Modals
    window.openNewPRModal = function() {
      document.getElementById('pr-form').reset();
      document.getElementById('pr-id').value = '';
      document.getElementById('pr-modal-title').textContent = 'Draft Purchase Request (PR)';
      document.getElementById('pr-number').value = `PR-2026-${Math.floor(1000 + Math.random() * 9000)}`;
      document.getElementById('pr-philgeps').value = `PHILGEPS-2026-${Math.floor(10000 + Math.random() * 90000)}`;
      document.getElementById('pr-modal').showModal();
    };

    window.openNewBidModal = function() {
      document.getElementById('bid-form').reset();
      document.getElementById('bid-modal').showModal();
    };

    window.openNewBidModalForSelected = function() {
      if (!selectedProjectId) {
        alert('Please select a procurement project first.');
        return;
      }
      document.getElementById('bid-form').reset();
      document.getElementById('bid-project-id').value = selectedProjectId;
      document.getElementById('bid-modal').showModal();
    };

    window.openNewBudgetModal = function() {
      document.getElementById('budget-form').reset();
      document.getElementById('budget-modal').showModal();
    };

    // Open Award Modal For Bid
    window.openAwardModalForBid = function(bidId) {
      const bid = allBids.find(b => b.id === bidId);
      if (!bid) return;
      const project = allProjects.find(p => p.id === (bid.projectId || bid.project_id));
      if (!project) return;

      document.getElementById('award-project-id').value = project.id;
      document.getElementById('award-modal-project-title').textContent = project.projectTitle || project.project_title;
      document.getElementById('award-supplier').value = bid.supplierName || bid.supplier_name;
      document.getElementById('award-amount').value = bid.quotationAmount || bid.quotation_amount;
      document.getElementById('award-po-number').value = `PO-2026-${Math.floor(1000 + Math.random() * 9000)}`;
      document.getElementById('award-delivery-days').value = project.targetDeliveryDays || project.target_delivery_days || 15;
      document.getElementById('award-modal').showModal();
    };

    window.openAwardModalForSelected = function() {
      if (!selectedProjectId) return;
      const bids = allBids.filter(b => (b.projectId === selectedProjectId || b.project_id === selectedProjectId) && (b.complianceStatus || b.compliance_status) === 'Responsive');
      bids.sort((a, b) => (parseFloat(a.quotationAmount || a.quotation_amount) || 0) - (parseFloat(b.quotationAmount || b.quotation_amount) || 0));
      if (bids.length === 0) {
        alert('No responsive quotations found for this project.');
        return;
      }
      openAwardModalForBid(bids[0].id);
    };

    // Form Submits
    window.handlePRFormSubmit = async function(e) {
      e.preventDefault();
      const prId = document.getElementById('pr-id').value;
      const budgetId = parseInt(document.getElementById('pr-budget-id').value) || null;
      const budget = allBudgets.find(b => b.id === budgetId);

      const record = {
        prNumber: document.getElementById('pr-number').value.trim(),
        classification: document.getElementById('pr-classification').value,
        projectTitle: document.getElementById('pr-title').value.trim(),
        procurementMode: document.getElementById('pr-mode').value,
        abcAmount: parseFloat(document.getElementById('pr-abc').value) || 0,
        fundSource: budget ? (budget.fundSource || budget.fund_source) : 'General Fund',
        budgetAllocationId: budgetId,
        endUserCommittee: document.getElementById('pr-committee').value.trim(),
        philgepsRef: document.getElementById('pr-philgeps').value.trim(),
        targetDeliveryDays: parseInt(document.getElementById('pr-delivery-days').value) || 15,
        status: document.getElementById('pr-status').value
      };

      if (prId) {
        record.id = parseInt(prId);
        await barangayDB.update('procurement_projects', record);
      } else {
        await barangayDB.add('procurement_projects', record);
      }

      document.getElementById('pr-modal').close();
      await refreshAllData();
      alert('Purchase Request (PR) recorded successfully.');
    };

    window.handleBidFormSubmit = async function(e) {
      e.preventDefault();
      const projId = parseInt(document.getElementById('bid-project-id').value);
      if (!projId) {
        alert('Target project is required.');
        return;
      }

      const bid = {
        projectId: projId,
        supplierName: document.getElementById('bid-supplier').value.trim(),
        tinNumber: document.getElementById('bid-tin').value.trim(),
        contactPerson: document.getElementById('bid-contact').value.trim(),
        phone: document.getElementById('bid-phone').value.trim(),
        quotationAmount: parseFloat(document.getElementById('bid-amount').value) || 0,
        complianceStatus: document.getElementById('bid-compliance').value,
        remarks: document.getElementById('bid-remarks').value.trim(),
        canvassedAt: new Date().toISOString()
      };

      await barangayDB.add('procurement_bids', bid);
      document.getElementById('bid-modal').close();
      await refreshAllData();

      if (selectedProjectId === projId) {
        loadAbstractForProject();
      }
      alert('Canvass quotation recorded.');
    };

    window.handleAwardFormSubmit = async function(e) {
      e.preventDefault();
      const projId = parseInt(document.getElementById('award-project-id').value);
      const supplier = document.getElementById('award-supplier').value;
      const amount = parseFloat(document.getElementById('award-amount').value) || 0;
      const poNum = document.getElementById('award-po-number').value.trim();
      const days = parseInt(document.getElementById('award-delivery-days').value) || 15;

      const project = allProjects.find(p => p.id === projId);
      if (!project) return;

      project.status = 'Awarded / PO Issued';
      project.winningBidder = supplier;
      project.winningAmount = amount;
      project.poNumber = poNum;
      project.dateAwarded = new Date().toISOString().split('T')[0];
      project.targetDeliveryDays = days;

      await barangayDB.update('procurement_projects', project);

      // Obligate Budget
      if (project.budgetAllocationId || project.budget_allocation_id) {
        const bId = project.budgetAllocationId || project.budget_allocation_id;
        const b = allBudgets.find(item => item.id === bId);
        if (b) {
          b.obligatedAmount = (parseFloat(b.obligatedAmount || b.obligated_amount) || 0) + amount;
          await barangayDB.update('budget_allocations', b);
        }
      }

      document.getElementById('award-modal').close();
      await refreshAllData();
      if (selectedProjectId === projId) {
        loadAbstractForProject();
      }
      alert(`Contract successfully awarded to ${supplier}!\nPurchase Order ${poNum} has been issued.`);
    };

    window.handleBudgetFormSubmit = async function(e) {
      e.preventDefault();
      const b = {
        fiscalYear: document.getElementById('alloc-year').value.trim(),
        fundSource: document.getElementById('alloc-fund').value,
        programTitle: document.getElementById('alloc-title').value.trim(),
        implementingCommittee: document.getElementById('alloc-committee').value.trim(),
        approvedBudget: parseFloat(document.getElementById('alloc-approved').value) || 0,
        obligatedAmount: 0.00
      };

      await barangayDB.add('budget_allocations', b);
      document.getElementById('budget-modal').close();
      await refreshAllData();
      alert('AIP Budget Appropriation recorded.');
    };

    // Delete Operations
    window.deleteProject = async function(id) {
      if (!confirm('Are you sure you want to delete this procurement project and its quotations?')) return;
      await barangayDB.delete('procurement_projects', id);
      await refreshAllData();
      if (selectedProjectId === id) {
        selectedProjectId = null;
        loadAbstractForProject();
      }
    };

    window.deleteBid = async function(bidId) {
      if (!confirm('Delete this supplier quotation?')) return;
      await barangayDB.delete('procurement_bids', bidId);
      await refreshAllData();
      loadAbstractForProject();
    };

    // Render Budget Table
    function renderBudgetTable() {
      const tbody = document.getElementById('budget-table-body');
      if (!tbody) return;

      tbody.innerHTML = allBudgets.map(b => {
        const approved = parseFloat(b.approvedBudget || b.approved_budget) || 0;
        const obligated = parseFloat(b.obligatedAmount || b.obligated_amount) || 0;
        const balance = Math.max(0, approved - obligated);
        const pct = approved > 0 ? Math.min(100, (obligated / approved) * 100).toFixed(1) : 0;

        return `
          <tr>
            <td style="font-weight: 700;">${b.fiscalYear || b.fiscal_year}</td>
            <td><span class="badge-neutral" style="font-size: 0.6875rem;">${b.fundSource || b.fund_source}</span></td>
            <td>
              <strong style="color: var(--color-ink);">${b.programTitle || b.program_title}</strong>
            </td>
            <td style="font-size: 0.75rem; color: var(--color-text-muted);">${b.implementingCommittee || b.implementing_committee}</td>
            <td style="font-weight: 700; color: var(--color-ink);">${formatCurrency(approved)}</td>
            <td style="color: #3b82f6; font-weight: 600;">${formatCurrency(obligated)}</td>
            <td style="color: #10b981; font-weight: 700;">${formatCurrency(balance)}</td>
            <td>
              <div style="display: flex; justify-content: space-between; font-size: 0.6875rem; color: var(--color-text-muted);">
                <span>${pct}%</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" style="width: ${pct}%;"></div>
              </div>
            </td>
            <td style="text-align: right;">
              <button type="button" class="icon-button" style="width: 26px; height: 26px;" onclick="deleteBudget(${b.id});" title="Delete Allocation">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    window.deleteBudget = async function(id) {
      if (!confirm('Are you sure you want to delete this AIP Budget Allocation?')) return;
      await barangayDB.delete('budget_allocations', id);
      await refreshAllData();
    };

    // =========================================================
    // STATUTORY PRINT ENGINE (PHILIPPINE RA 9184 TEMPLATES)
    // =========================================================

    // Letterhead Generator
    function getOfficialLetterhead(docTitle, docSubtitle = '') {
      return `
        <div class="official-letterhead">
          <p>Republic of the Philippines</p>
          <p>Province of Rizal &bull; Municipality of Rodriguez</p>
          <p><strong>BARANGAY SAN ISIDRO</strong></p>
          <p style="font-size: 8pt; color: #555;">OFFICE OF THE BIDS AND AWARDS COMMITTEE (BAC)</p>
          <h3 style="margin-top: 12px;">${docTitle}</h3>
          ${docSubtitle ? `<p style="font-size: 9pt; font-style: italic;">${docSubtitle}</p>` : ''}
        </div>
      `;
    }

    // Print Form 1: Purchase Request (PR)
    window.printPRDocument = function(projectId) {
      const p = allProjects.find(item => item.id === projectId);
      if (!p) return;

      const container = document.getElementById('print-statutory-container');
      container.innerHTML = `
        ${getOfficialLetterhead('PURCHASE REQUEST (PR)', 'Standard Form Annex G &bull; Local Government Unit')}
        
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 9pt;">
          <tr>
            <td style="width: 50%; padding: 4px 0;"><strong>Department / Office:</strong> Bids and Awards Committee</td>
            <td style="width: 50%; padding: 4px 0; text-align: right;"><strong>PR Number:</strong> ${p.prNumber || p.pr_number}</td>
          </tr>
          <tr>
            <td style="padding: 4px 0;"><strong>Section:</strong> ${p.endUserCommittee || p.end_user_committee}</td>
            <td style="padding: 4px 0; text-align: right;"><strong>Date:</strong> ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</td>
          </tr>
          <tr>
            <td style="padding: 4px 0;"><strong>Fund Source:</strong> ${p.fundSource || p.fund_source}</td>
            <td style="padding: 4px 0; text-align: right;"><strong>Mode of Procurement:</strong> ${p.procurementMode || p.procurement_mode}</td>
          </tr>
        </table>

        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 9pt; margin-bottom: 24px;">
          <thead>
            <tr style="background: #eee; border-bottom: 1px solid #000;">
              <th style="border: 1px solid #000; padding: 6px; width: 40px; text-align: center;">Item</th>
              <th style="border: 1px solid #000; padding: 6px; width: 60px; text-align: center;">Qty / Unit</th>
              <th style="border: 1px solid #000; padding: 6px;">Item Description &amp; Technical Specifications</th>
              <th style="border: 1px solid #000; padding: 6px; width: 100px; text-align: right;">Estimated Unit Cost</th>
              <th style="border: 1px solid #000; padding: 6px; width: 120px; text-align: right;">Total Cost (ABC)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="border: 1px solid #000; padding: 12px 6px; text-align: center;">1</td>
              <td style="border: 1px solid #000; padding: 12px 6px; text-align: center;">1 Lot</td>
              <td style="border: 1px solid #000; padding: 12px 6px;">
                <strong>${p.projectTitle || p.project_title}</strong><br>
                <span style="font-size: 8pt; color: #333;">
                  PhilGEPS Posting Reference: ${p.philgepsRef || p.philgeps_ref || 'N/A'}<br>
                  Classification: ${p.classification || 'Goods & Supplies'}<br>
                  Delivery Schedule: Within ${p.targetDeliveryDays || 15} calendar days upon receipt of approved Purchase Order (PO).
                </span>
              </td>
              <td style="border: 1px solid #000; padding: 12px 6px; text-align: right;">${formatCurrency(p.abcAmount || p.abc_amount)}</td>
              <td style="border: 1px solid #000; padding: 12px 6px; text-align: right; font-weight: bold;">${formatCurrency(p.abcAmount || p.abc_amount)}</td>
            </tr>
            <tr style="border-top: 1px solid #000; background: #fafafa;">
              <td colspan="4" style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold;">TOTAL APPROVED BUDGET FOR THE CONTRACT (ABC):</td>
              <td style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; font-size: 10pt;">${formatCurrency(p.abcAmount || p.abc_amount)}</td>
            </tr>
          </tbody>
        </table>

        <div style="font-size: 9pt; margin-bottom: 24px;">
          <strong>Purpose:</strong> For the official implementation of ${p.projectTitle || p.project_title} under the approved Annual Investment Program (AIP).
        </div>

        <div class="signatory-grid">
          <div>
            <p style="font-size: 8pt; text-align: left; margin-bottom: 35px;">Requested by:</p>
            <div class="signatory-box">
              <strong>HON. RAFAEL G. DIZON</strong><br>
              BAC Chairperson / Kagawad
            </div>
          </div>
          <div>
            <p style="font-size: 8pt; text-align: left; margin-bottom: 35px;">Appropriations Certified Available:</p>
            <div class="signatory-box">
              <strong>MA. LOURDES SANTOS</strong><br>
              Barangay Treasurer
            </div>
          </div>
          <div>
            <p style="font-size: 8pt; text-align: left; margin-bottom: 35px;">Approved by:</p>
            <div class="signatory-box">
              <strong>HON. ANTONIO S. VALDEZ</strong><br>
              Punong Barangay / Head of Procuring Entity (HoPE)
            </div>
          </div>
        </div>
      `;

      window.print();
    };

    // Print Form 2: Abstract of Bids / Canvass (Annex H)
    window.printAbstractOfBids = function() {
      if (!selectedProjectId) {
        alert('Please select a procurement project first.');
        return;
      }
      const p = allProjects.find(item => item.id === selectedProjectId);
      if (!p) return;
      const bids = allBids.filter(b => b.projectId === p.id || b.project_id === p.id);

      const container = document.getElementById('print-statutory-container');
      container.innerHTML = `
        ${getOfficialLetterhead('ABSTRACT OF BIDS / CANVASS QUOTATIONS', 'Philippine RA 9184 &bull; Standard Form Annex H')}

        <div style="font-size: 9pt; margin-bottom: 16px;">
          <p style="margin: 3px 0;"><strong>PROJECT:</strong> ${p.projectTitle || p.project_title}</p>
          <p style="margin: 3px 0;"><strong>PR NO.:</strong> ${p.prNumber || p.pr_number} &nbsp;|&nbsp; <strong>PhilGEPS REF:</strong> ${p.philgepsRef || p.philgeps_ref || 'N/A'}</p>
          <p style="margin: 3px 0;"><strong>APPROVED BUDGET FOR THE CONTRACT (ABC):</strong> ${formatCurrency(p.abcAmount || p.abc_amount)}</p>
          <p style="margin: 3px 0;"><strong>MODE OF PROCUREMENT:</strong> ${p.procurementMode || p.procurement_mode}</p>
        </div>

        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 9pt; margin-bottom: 24px;">
          <thead>
            <tr style="background: #eee;">
              <th style="border: 1px solid #000; padding: 6px; text-align: center;">Rank</th>
              <th style="border: 1px solid #000; padding: 6px;">Supplier / Bidder Name</th>
              <th style="border: 1px solid #000; padding: 6px;">TIN</th>
              <th style="border: 1px solid #000; padding: 6px; text-align: right;">Quotation Amount</th>
              <th style="border: 1px solid #000; padding: 6px; text-align: center;">Variance vs ABC</th>
              <th style="border: 1px solid #000; padding: 6px; text-align: center;">Compliance</th>
              <th style="border: 1px solid #000; padding: 6px;">Evaluation Remarks</th>
            </tr>
          </thead>
          <tbody>
            ${bids.map((b, i) => `
              <tr>
                <td style="border: 1px solid #000; padding: 8px 6px; text-align: center; font-weight: bold;">${i + 1}</td>
                <td style="border: 1px solid #000; padding: 8px 6px; font-weight: bold;">${b.supplierName || b.supplier_name}</td>
                <td style="border: 1px solid #000; padding: 8px 6px; font-family: monospace;">${b.tinNumber || b.tin_number || '--'}</td>
                <td style="border: 1px solid #000; padding: 8px 6px; text-align: right; font-weight: bold;">${formatCurrency(b.quotationAmount || b.quotation_amount)}</td>
                <td style="border: 1px solid #000; padding: 8px 6px; text-align: center;">
                  ${((((parseFloat(b.quotationAmount || b.quotation_amount) - (p.abcAmount || p.abc_amount))) / (p.abcAmount || p.abc_amount)) * 100).toFixed(1)}%
                </td>
                <td style="border: 1px solid #000; padding: 8px 6px; text-align: center;">${b.complianceStatus || b.compliance_status}</td>
                <td style="border: 1px solid #000; padding: 8px 6px; font-size: 8pt;">${b.remarks || 'Complied with requirements'}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>

        <div style="font-size: 8.5pt; text-align: justify; margin-bottom: 24px; line-height: 1.4;">
          <strong>BAC CERTIFICATION:</strong> WE HEREBY CERTIFY that the above quotations were opened and evaluated in accordance with Republic Act No. 9184 and its 2016 Revised Implementing Rules and Regulations. The quotation of <strong>${p.winningBidder || (bids[0] ? (bids[0].supplierName || bids[0].supplier_name) : 'the lowest complying bidder')}</strong> is hereby certified as the <strong>Lowest Calculated and Responsive Bid (LCRB)</strong>.
        </div>

        <p style="font-size: 8pt; font-weight: bold; margin-bottom: 40px;">BIDS AND AWARDS COMMITTEE (BAC):</p>
        <div class="signatory-grid">
          <div>
            <div class="signatory-box">
              <strong>HON. RAFAEL G. DIZON</strong><br>BAC Chairperson
            </div>
          </div>
          <div>
            <div class="signatory-box">
              <strong>HON. TERESA B. MORALES</strong><br>BAC Vice Chairperson
            </div>
          </div>
          <div>
            <div class="signatory-box">
              <strong>HON. CARLOS M. SANTOS</strong><br>BAC Member
            </div>
          </div>
        </div>
      `;

      window.print();
    };

    // Print Form 3: BAC Resolution Recommending Award
    window.printBACResolution = function() {
      if (!selectedProjectId) {
        alert('Please select a procurement project first.');
        return;
      }
      const p = allProjects.find(item => item.id === selectedProjectId);
      if (!p) return;

      const container = document.getElementById('print-statutory-container');
      container.innerHTML = `
        ${getOfficialLetterhead('BIDS AND AWARDS COMMITTEE', 'RESOLUTION NO. 2026-BAC-042')}

        <h4 style="text-align: center; margin: 16px 0; font-size: 11pt; text-transform: uppercase;">
          A RESOLUTION RECOMMENDING THE AWARD OF CONTRACT FOR THE "${p.projectTitle || p.project_title}" TO ${p.winningBidder || 'THE LOWEST CALCULATED AND RESPONSIVE BIDDER'} VIA ${p.procurementMode || p.procurement_mode} PURSUANT TO REPUBLIC ACT NO. 9184
        </h4>

        <div style="font-size: 9.5pt; text-align: justify; line-height: 1.5; margin-bottom: 24px;">
          <p><strong>WHEREAS,</strong> Barangay San Isidro, Municipality of Rodriguez, Province of Rizal, through its approved Annual Investment Program (AIP) for FY 2026, programmed the procurement of <strong>${p.projectTitle || p.project_title}</strong> with an Approved Budget for the Contract (ABC) of <strong>${formatCurrency(p.abcAmount || p.abc_amount)}</strong> funded under the <strong>${p.fundSource || p.fund_source}</strong>;</p>

          <p><strong>WHEREAS,</strong> pursuant to Section 53.9 of the 2016 Revised Implementing Rules and Regulations (IRR) of Republic Act No. 9184, the Bids and Awards Committee (BAC) posted the Request for Quotation (RFQ) on PhilGEPS under Reference No. <strong>${p.philgepsRef || p.philgeps_ref || 'N/A'}</strong> and canvassed prices from reputable bona fide suppliers;</p>

          <p><strong>WHEREAS,</strong> upon careful examination, validation, and post-qualification of all submitted sealed quotations, the BAC found the quotation of <strong>${p.winningBidder || 'the complying bidder'}</strong> amounting to <strong>${formatCurrency(p.winningAmount || p.abcAmount || p.abc_amount)}</strong> to be the Lowest Calculated and Responsive Bid (LCRB);</p>

          <p><strong>NOW, THEREFORE,</strong> for and in consideration of the foregoing premises, the Bids and Awards Committee hereby <strong>RESOLVES</strong>, as it is hereby <strong>RESOLVED</strong>, to recommend to the Punong Barangay / Head of Procuring Entity (HoPE) the approval of the award of contract and issuance of the Purchase Order to <strong>${p.winningBidder || 'the winning contractor'}</strong>.</p>

          <p><strong>RESOLVED FINALLY,</strong> this ${new Date().toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' })} at Barangay San Isidro, Rodriguez, Rizal.</p>
        </div>

        <div class="signatory-grid" style="margin-top: 30px;">
          <div>
            <div class="signatory-box">
              <strong>HON. RAFAEL G. DIZON</strong><br>BAC Chairperson
            </div>
          </div>
          <div>
            <div class="signatory-box">
              <strong>HON. TERESA B. MORALES</strong><br>BAC Vice Chairperson
            </div>
          </div>
          <div>
            <div class="signatory-box">
              <strong>HON. CARLOS M. SANTOS</strong><br>BAC Member
            </div>
          </div>
        </div>

        <div style="margin-top: 40px; text-align: center; width: 300px; margin-left: auto; margin-right: auto;">
          <p style="font-size: 8.5pt; margin-bottom: 30px;">APPROVED AND CONFIRMED:</p>
          <div class="signatory-box">
            <strong>HON. ANTONIO S. VALDEZ</strong><br>
            Punong Barangay / Head of Procuring Entity (HoPE)
          </div>
        </div>
      `;

      window.print();
    };

    // Print Form 4: Official Purchase Order (PO)
    window.printPODocument = function(projectId) {
      const p = allProjects.find(item => item.id === projectId);
      if (!p) return;

      const container = document.getElementById('print-statutory-container');
      container.innerHTML = `
        ${getOfficialLetterhead('PURCHASE ORDER (PO)', 'Local Government Unit &bull; Annex J Compliant')}

        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 8.5pt; margin-bottom: 16px;">
          <tr>
            <td style="width: 50%; padding: 6px; border: 1px solid #000;">
              <strong>Supplier:</strong> ${p.winningBidder || 'Winning Contractor Corp.'}<br>
              <strong>Address:</strong> Metro Manila / Rizal Province<br>
              <strong>TIN:</strong> 123-456-789-000
            </td>
            <td style="width: 50%; padding: 6px; border: 1px solid #000;">
              <strong>P.O. No.:</strong> <span style="font-weight: bold; font-family: monospace;">${p.poNumber || 'PO-2026-0001'}</span><br>
              <strong>Date:</strong> ${p.dateAwarded || new Date().toISOString().split('T')[0]}<br>
              <strong>Mode of Procurement:</strong> ${p.procurementMode || p.procurement_mode}<br>
              <strong>PR Ref No.:</strong> ${p.prNumber || p.pr_number}
            </td>
          </tr>
          <tr>
            <td style="padding: 6px; border: 1px solid #000;">
              <strong>Place of Delivery:</strong> Barangay Hall Complex, San Isidro<br>
              <strong>Date of Delivery:</strong> Within ${p.targetDeliveryDays || 15} calendar days
            </td>
            <td style="padding: 6px; border: 1px solid #000;">
              <strong>Delivery Term:</strong> Free On Board (FOB) Destination<br>
              <strong>Payment Term:</strong> Complete Delivery &amp; Inspection
            </td>
          </tr>
        </table>

        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 8.5pt; margin-bottom: 20px;">
          <thead>
            <tr style="background: #eee;">
              <th style="border: 1px solid #000; padding: 6px; width: 40px; text-align: center;">Item</th>
              <th style="border: 1px solid #000; padding: 6px; width: 60px; text-align: center;">Unit</th>
              <th style="border: 1px solid #000; padding: 6px;">Description</th>
              <th style="border: 1px solid #000; padding: 6px; width: 60px; text-align: center;">Qty</th>
              <th style="border: 1px solid #000; padding: 6px; width: 100px; text-align: right;">Unit Cost</th>
              <th style="border: 1px solid #000; padding: 6px; width: 110px; text-align: right;">Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="border: 1px solid #000; padding: 10px 6px; text-align: center;">1</td>
              <td style="border: 1px solid #000; padding: 10px 6px; text-align: center;">Lot</td>
              <td style="border: 1px solid #000; padding: 10px 6px;">
                <strong>${p.projectTitle || p.project_title}</strong><br>
                <span style="font-size: 7.5pt; color: #444;">PhilGEPS Posting Ref: ${p.philgepsRef || p.philgeps_ref || 'N/A'}</span>
              </td>
              <td style="border: 1px solid #000; padding: 10px 6px; text-align: center;">1</td>
              <td style="border: 1px solid #000; padding: 10px 6px; text-align: right;">${formatCurrency(p.winningAmount || p.abcAmount || p.abc_amount)}</td>
              <td style="border: 1px solid #000; padding: 10px 6px; text-align: right; font-weight: bold;">${formatCurrency(p.winningAmount || p.abcAmount || p.abc_amount)}</td>
            </tr>
            <tr style="background: #fafafa; font-weight: bold;">
              <td colspan="5" style="border: 1px solid #000; padding: 8px; text-align: right;">TOTAL CONTRACT AMOUNT:</td>
              <td style="border: 1px solid #000; padding: 8px; text-align: right;">${formatCurrency(p.winningAmount || p.abcAmount || p.abc_amount)}</td>
            </tr>
          </tbody>
        </table>

        <div style="font-size: 8pt; line-height: 1.4; margin-bottom: 24px; border: 1px dashed #777; padding: 8px;">
          <strong>LIQUIDATED DAMAGES CLAUSE (RA 9184):</strong> In case of failure to make the full delivery within the time specified above, a penalty of one-tenth (1/10) of one percent (1%) of the cost of the unperformed portion for every day of delay shall be imposed and deducted from the supplier's billing claim.
        </div>

        <div class="signatory-grid" style="margin-top: 20px;">
          <div>
            <p style="font-size: 8pt; text-align: left; margin-bottom: 30px;">Conforme / Accepted:</p>
            <div class="signatory-box">
              <strong>${p.winningBidder || 'Authorized Supplier Representative'}</strong><br>
              Signature over Printed Name / Date
            </div>
          </div>
          <div>
            <p style="font-size: 8pt; text-align: left; margin-bottom: 30px;">Funds Available / Obligated:</p>
            <div class="signatory-box">
              <strong>MA. LOURDES SANTOS</strong><br>
              Barangay Treasurer &bull; ${p.fundSource || p.fund_source}
            </div>
          </div>
          <div>
            <p style="font-size: 8pt; text-align: left; margin-bottom: 30px;">Very truly yours:</p>
            <div class="signatory-box">
              <strong>HON. ANTONIO S. VALDEZ</strong><br>
              Punong Barangay / HoPE
            </div>
          </div>
        </div>
      `;

      window.print();
    };

    // Print Form 5: Inspection and Acceptance Report (IAR)
    window.printIARDocument = function(projectId) {
      const p = allProjects.find(item => item.id === projectId);
      if (!p) return;

      const container = document.getElementById('print-statutory-container');
      container.innerHTML = `
        ${getOfficialLetterhead('INSPECTION AND ACCEPTANCE REPORT (IAR)', 'COA Circular Standard &bull; Republic of the Philippines')}

        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 8.5pt; margin-bottom: 16px;">
          <tr>
            <td style="width: 50%; padding: 6px; border: 1px solid #000;">
              <strong>Supplier:</strong> ${p.winningBidder || 'Contractor Corp.'}<br>
              <strong>PO No.:</strong> ${p.poNumber || 'N/A'}<br>
              <strong>Date:</strong> ${p.dateAwarded || new Date().toISOString().split('T')[0]}
            </td>
            <td style="width: 50%; padding: 6px; border: 1px solid #000;">
              <strong>IAR No.:</strong> IAR-2026-${String(p.id).padStart(4, '0')}<br>
              <strong>Invoice No.:</strong> INV-2026-${Math.floor(1000 + Math.random() * 9000)}<br>
              <strong>Requisitioning Office:</strong> ${p.endUserCommittee || p.end_user_committee}
            </td>
          </tr>
        </table>

        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 8.5pt; margin-bottom: 24px;">
          <thead>
            <tr style="background: #eee;">
              <th style="border: 1px solid #000; padding: 6px; width: 40px; text-align: center;">Item</th>
              <th style="border: 1px solid #000; padding: 6px;">Description</th>
              <th style="border: 1px solid #000; padding: 6px; width: 60px; text-align: center;">Qty</th>
              <th style="border: 1px solid #000; padding: 6px; width: 60px; text-align: center;">Unit</th>
              <th style="border: 1px solid #000; padding: 6px; width: 120px; text-align: right;">Total Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="border: 1px solid #000; padding: 12px 6px; text-align: center;">1</td>
              <td style="border: 1px solid #000; padding: 12px 6px;">
                <strong>${p.projectTitle || p.project_title}</strong><br>
                <span style="font-size: 7.5pt; color: #444;">Delivered in full accordance with specifications.</span>
              </td>
              <td style="border: 1px solid #000; padding: 12px 6px; text-align: center;">1</td>
              <td style="border: 1px solid #000; padding: 12px 6px; text-align: center;">Lot</td>
              <td style="border: 1px solid #000; padding: 12px 6px; text-align: right; font-weight: bold;">${formatCurrency(p.winningAmount || p.abcAmount || p.abc_amount)}</td>
            </tr>
          </tbody>
        </table>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; font-size: 8.5pt;">
          <div style="border: 1px solid #000; padding: 12px;">
            <p style="font-weight: bold; margin-bottom: 8px;">INSPECTION CERTIFICATION</p>
            <p style="font-size: 7.5pt; line-height: 1.4; margin-bottom: 25px;">
              Date Inspected: ${new Date().toLocaleDateString()}<br>
              Inspected, verified and found in order as to quantity and specifications.
            </p>
            <div class="signatory-box">
              <strong>KAG. BENJAMIN ALCANTARA</strong><br>
              Barangay Inspection Committee Officer
            </div>
          </div>

          <div style="border: 1px solid #000; padding: 12px;">
            <p style="font-weight: bold; margin-bottom: 8px;">ACCEPTANCE CERTIFICATION</p>
            <p style="font-size: 7.5pt; line-height: 1.4; margin-bottom: 25px;">
              Date Received: ${new Date().toLocaleDateString()}<br>
              Complete delivery received in good order and condition.
            </p>
            <div class="signatory-box">
              <strong>CRISTINA MERCADO</strong><br>
              Barangay Property / Supply Custodian
            </div>
          </div>
        </div>
      `;

      window.print();
    };
  </script>
</body>
</html>
