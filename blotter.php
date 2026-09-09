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
  <title>Blotter & Incident Records &bull; Barangay Management System</title>
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
      outline: none;
    }

    .filter-select:focus {
      border-color: var(--color-primary);
    }

    .form-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--spacing-md);
    }

    .form-grid-3 {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: var(--spacing-md);
    }

    @media (max-width: 640px) {
      .form-grid-2, .form-grid-3 {
        grid-template-columns: 1fr;
      }
    }

    .parties-card {
      background-color: var(--color-canvas-soft);
      border-radius: var(--rounded-sm);
      padding: 10px 14px;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .party-avatar {
      width: 30px;
      height: 30px;
      border-radius: 30%;
      background-color: var(--color-canvas);
      border: 1px solid var(--color-hairline);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.6875rem;
      font-weight: 700;
      color: var(--color-ink);
      flex-shrink: 0;
    }

    /* Official Lupon Document Print Styles */
    #printable-lupon-doc {
      background: #ffffff;
      color: #111111;
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 0 16px rgba(0, 0, 0, 0.08);
      font-family: 'Times New Roman', Times, serif;
      line-height: 1.5;
      max-width: 780px;
      margin: 0 auto;
    }

    .lupon-header {
      text-align: center;
      margin-bottom: 24px;
      border-bottom: 2px solid #111111;
      padding-bottom: 16px;
    }

    .lupon-title {
      font-size: 1.5rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin: 16px 0;
      text-align: center;
      font-family: var(--font-family), sans-serif;
    }

    .lupon-parties-block {
      display: flex;
      justify-content: space-between;
      margin-bottom: 24px;
      font-size: 0.95rem;
    }

    .lupon-body {
      font-size: 1.05rem;
      text-align: justify;
      line-height: 1.8;
      margin-bottom: 30px;
    }

    .lupon-body p {
      margin-bottom: 16px;
      text-indent: 40px;
    }

    .lupon-signatures {
      display: flex;
      justify-content: space-between;
      margin-top: 40px;
      padding-top: 20px;
    }

    @media print {
      body * {
        visibility: hidden !important;
      }
      #lupon-print-modal, #lupon-print-modal * {
        visibility: visible !important;
      }
      #lupon-print-modal {
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
        display: block !important;
      }
      .modal-dialog::backdrop {
        display: none !important;
      }
      .no-print {
        display: none !important;
      }
      #printable-lupon-doc {
        box-shadow: none !important;
        padding: 20mm !important;
        width: 100% !important;
        max-width: 100% !important;
      }
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
                <h1 class="typography-heading-2">Blotter & Incident Records.</h1>
                <span class="badge-neutral" id="blotter-count-badge">0 Cases</span>
              </div>
              <p class="typography-body-lg">
                Peace & Order incident reporting, Lupon Tagapamayapa dispute mediation, and legal notices.
              </p>
            </div>
            <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
              <button class="button-primary" id="btn-open-file-modal" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="12" y1="5" x2="12" y2="19"/>
                  <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>File Incident Report</span>
              </button>
            </div>
          </div>
        </section>

        <!-- Telemetry Ladder -->
        <section>
          <div class="stats-ladder">
            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">TOTAL CASES FILED</span>
                <span class="badge-neutral">Registry</span>
              </div>
              <div class="stat-number" id="stat-total-cases">0</div>
              <div class="typography-caption" id="stat-sub-cases">Peace & order entries</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">ACTIVE MEDIATION</span>
                <span class="badge-amber">In Progress</span>
              </div>
              <div class="stat-number" id="stat-active-cases">0</div>
              <div class="typography-caption" id="stat-sub-active">Awaiting hearing / settlement</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">HEARINGS SCHEDULED</span>
                <span class="badge-blue">Calendar</span>
              </div>
              <div class="stat-number" id="stat-scheduled-cases">0</div>
              <div class="typography-caption" id="stat-sub-scheduled">Summons issued</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">AMICABLY SETTLED</span>
                <span class="badge-emerald">Kasunduan</span>
              </div>
              <div class="stat-number" id="stat-settled-cases">0</div>
              <div class="typography-caption" id="stat-sub-settled">Peacefully resolved</div>
            </div>
          </div>
        </section>

        <!-- Filter & Search Toolbar -->
        <section>
          <div class="filter-toolbar">
            <div class="filter-group">
              <div class="search-input-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="11" cy="11" r="8"/>
                  <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" id="search-cases" class="text-input" placeholder="Search case #, complainant, respondent, or incident..." autocomplete="off">
              </div>

              <!-- Status Filter -->
              <select id="filter-case-status" class="filter-select">
                <option value="">All Case Statuses</option>
                <option value="Active Mediation">Active Mediation</option>
                <option value="Hearing Scheduled">Hearing Scheduled</option>
                <option value="Amicably Settled">Amicably Settled</option>
                <option value="Escalated (CFA Issued)">Escalated (CFA Issued)</option>
              </select>

              <!-- Incident Type Filter -->
              <select id="filter-incident-type" class="filter-select">
                <option value="">All Incident Types</option>
                <option value="Physical Altercation">Physical Altercation</option>
                <option value="Noise Disturbance">Noise Disturbance</option>
                <option value="Property & Boundary Dispute">Property & Boundary Dispute</option>
                <option value="Domestic / Family Conflict">Domestic / Family Conflict</option>
                <option value="Theft / Property Damage">Theft / Property Damage</option>
                <option value="Verbal Harassment / Threat">Verbal Harassment / Threat</option>
                <option value="Debt / Financial Conflict">Debt / Financial Conflict</option>
              </select>

              <!-- Purok Filter -->
              <select id="filter-case-purok" class="filter-select">
                <option value="">All Puroks</option>
                <option value="Purok 1">Purok 1</option>
                <option value="Purok 2">Purok 2</option>
                <option value="Purok 3">Purok 3</option>
                <option value="Purok 4">Purok 4</option>
                <option value="Purok 5">Purok 5</option>
                <option value="Purok 6">Purok 6</option>
                <option value="Purok 7">Purok 7</option>
                <option value="Sitio Center">Sitio Center</option>
              </select>
            </div>

            <div class="filter-group">
              <button id="btn-clear-blotter-filters" class="button-pill-soft" style="height: 34px; padding: 0 12px; font-size: 0.75rem; display: none;">
                Reset Filters
              </button>
            </div>
          </div>
        </section>

        <!-- Cases Table Section -->
        <section class="mb-section">
          <!-- Populated Table Container -->
          <div id="table-container" class="data-table-container" style="display: none;">
            <div class="data-table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Case Number</th>
                    <th>Parties (Complainant vs Respondent)</th>
                    <th>Incident Classification</th>
                    <th>Status / Stage</th>
                    <th>Hearing / Settlement</th>
                    <th style="text-align: right;">Actions</th>
                  </tr>
                </thead>
                <tbody id="blotter-table-body">
                  <!-- Injected via JavaScript -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- Empty State (No records in DB) -->
          <div id="empty-state" class="empty-state-card" style="padding: var(--spacing-section) var(--spacing-xl);">
            <div class="nav-brand-icon" style="width: 56px; height: 56px; font-size: 1.5rem;">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              </svg>
            </div>
            <div>
              <h3 class="typography-heading-3">No Blotter Cases Filed Yet.</h3>
              <p class="typography-body mt-xs" style="color: var(--color-text-muted); max-width: 460px;">
                The peace and order registry is completely clean with zero dummy data. Log community dispute mediations, hearings, and settlements here.
              </p>
            </div>
            <button class="button-primary mt-sm" onclick="document.getElementById('btn-open-file-modal').click();">
              + File First Incident Report
            </button>
          </div>

          <!-- Filter Match Empty State -->
          <div id="filter-empty-state" class="empty-state-card" style="display: none; padding: var(--spacing-xl);">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-text-muted);">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <h4 class="typography-heading-4">No Matching Blotter Cases.</h4>
            <p class="typography-body-sm" style="color: var(--color-text-muted);">
              No records match your active search terms or filter selections.
            </p>
            <button class="button-pill-soft" onclick="resetBlotterFilters();">Clear All Filters</button>
          </div>
        </section>
      </main>
    </div>
  </div>

  <!-- Incident Report Filing Modal -->
  <dialog id="file-modal" class="modal-dialog" style="max-width: 740px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-sm);">
      <div>
        <h3 class="typography-heading-4">File Barangay Incident Report.</h3>
        <p class="typography-caption">Official blotter entry and Lupong Tagapamayapa registration.</p>
      </div>
      <button type="button" class="button-pill-soft" onclick="document.getElementById('file-modal').close();" style="height: 30px; padding: 0 10px;">
        Cancel
      </button>
    </div>

    <form id="file-form" novalidate>
      <!-- Step 1: Complainant Information -->
      <div style="margin-bottom: var(--spacing-md);">
        <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem; text-transform: uppercase;">1. Complainant (Nagsusumbong)</span>
        
        <div class="form-grid-2 mt-xs">
          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="comp-resident-select">Select Registered Resident (Optional)</label>
            <select id="comp-resident-select" class="text-input" style="height: 42px; padding: 0 12px;">
              <option value="">-- Choose from Residents Registry --</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="comp-name">Complainant Full Name <span style="color: var(--color-primary);">*</span></label>
            <input type="text" id="comp-name" class="text-input" style="height: 42px;" placeholder="e.g. Maria Santos" required>
          </div>
        </div>

        <div class="form-grid-2 mt-xs">
          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="comp-purok">Complainant Purok / Address</label>
            <input type="text" id="comp-purok" class="text-input" style="height: 42px;" placeholder="e.g. Purok 3, Rizal St.">
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="comp-phone">Contact Number</label>
            <input type="tel" id="comp-phone" class="text-input" style="height: 42px;" placeholder="e.g. 0917-000-0000">
          </div>
        </div>
      </div>

      <!-- Step 2: Respondent Information -->
      <div style="margin-bottom: var(--spacing-md); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem; text-transform: uppercase;">2. Respondent / Accused Party (Ipinagsusumbong)</span>

        <div class="form-grid-2 mt-xs">
          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="resp-resident-select">Select Registered Resident (Optional)</label>
            <select id="resp-resident-select" class="text-input" style="height: 42px; padding: 0 12px;">
              <option value="">-- Choose from Residents Registry --</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="resp-name">Respondent Full Name <span style="color: var(--color-primary);">*</span></label>
            <input type="text" id="resp-name" class="text-input" style="height: 42px;" placeholder="e.g. Pedro Reyes" required>
          </div>
        </div>

        <div class="form-grid-2 mt-xs">
          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="resp-purok">Respondent Purok / Address</label>
            <input type="text" id="resp-purok" class="text-input" style="height: 42px;" placeholder="e.g. Purok 5, Bonifacio St.">
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="resp-phone">Contact Number</label>
            <input type="tel" id="resp-phone" class="text-input" style="height: 42px;" placeholder="e.g. 0918-000-0000">
          </div>
        </div>
      </div>

      <!-- Step 3: Incident Details -->
      <div style="margin-bottom: var(--spacing-md); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem; text-transform: uppercase;">3. Incident Facts & Narrative</span>

        <div class="form-grid-3 mt-xs">
          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="incident-type">Incident Type <span style="color: var(--color-primary);">*</span></label>
            <select id="incident-type" class="text-input" style="height: 42px; padding: 0 12px;" required>
              <option value="Physical Altercation">Physical Altercation (Suntukan/Pananakit)</option>
              <option value="Noise Disturbance">Noise Disturbance (Pang-iistorbo)</option>
              <option value="Property & Boundary Dispute">Property & Boundary Dispute (Alitan sa Hangganan)</option>
              <option value="Domestic / Family Conflict">Domestic / Family Conflict (Alitan sa Pamilya)</option>
              <option value="Theft / Property Damage">Theft / Property Damage (Pagnanakaw/Pagkasira)</option>
              <option value="Verbal Harassment / Threat">Verbal Harassment / Threat (Pananakot/Pagbabanta)</option>
              <option value="Debt / Financial Conflict">Debt / Financial Conflict (Pautang/Paniningil)</option>
              <option value="Other Community Dispute">Other Community Dispute</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="incident-date">Date of Incident <span style="color: var(--color-primary);">*</span></label>
            <input type="date" id="incident-date" class="text-input" style="height: 42px; padding: 0 12px;" required>
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="incident-time">Time of Incident</label>
            <input type="time" id="incident-time" class="text-input" style="height: 42px; padding: 0 12px;">
          </div>
        </div>

        <div class="form-grid-2 mt-xs">
          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="incident-location">Incident Location / Landmark <span style="color: var(--color-primary);">*</span></label>
            <input type="text" id="incident-location" class="text-input" style="height: 42px;" placeholder="e.g. Purok 4 Basketball Court" required>
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="case-officer">Recording Officer / Kagawad</label>
            <input type="text" id="case-officer" class="text-input" style="height: 42px;" value="Hon. Desk Officer">
          </div>
        </div>

        <div class="form-group mt-xs" style="margin-bottom: var(--spacing-xs);">
          <label class="form-label" for="incident-narrative">Detailed Narrative & Complaint Statement <span style="color: var(--color-primary);">*</span></label>
          <textarea id="incident-narrative" class="text-input" style="height: 90px; padding: 10px 14px; resize: vertical;" placeholder="Narrate facts of what transpired, damages incurred, or sequence of events..." required></textarea>
        </div>
      </div>

      <!-- Actions -->
      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-md);">
        <button type="button" class="button-outline" onclick="document.getElementById('file-modal').close();" style="height: 42px; padding: 0 20px;">
          Cancel
        </button>
        <button type="submit" class="button-primary" id="btn-submit-file" style="height: 42px; padding: 0 24px;">
          File Case & Generate Case #
        </button>
      </div>
    </form>
  </dialog>

  <!-- Status & Mediation Update Modal -->
  <dialog id="status-modal" class="modal-dialog" style="max-width: 580px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-sm);">
      <div>
        <h3 class="typography-heading-4">Update Case Lifecycle.</h3>
        <p class="typography-caption" id="status-modal-case-num">BLTR-2026-00001</p>
      </div>
      <button type="button" class="button-pill-soft" onclick="document.getElementById('status-modal').close();" style="height: 30px; padding: 0 10px;">
        Cancel
      </button>
    </div>

    <form id="status-form">
      <div class="form-group">
        <label class="form-label" for="update-status-select">Mediation Stage / Status</label>
        <select id="update-status-select" class="text-input" style="height: 42px; padding: 0 12px;">
          <option value="Active Mediation">Active Mediation (Case under inquiry)</option>
          <option value="Hearing Scheduled">Hearing Scheduled (Summons issued)</option>
          <option value="Amicably Settled">Amicably Settled (Kasunduan reached)</option>
          <option value="Escalated (CFA Issued)">Escalated (Certificate to File Action)</option>
        </select>
      </div>

      <!-- Conditional Hearing Date Fields -->
      <div id="hearing-fields" style="display: none; margin-top: var(--spacing-sm); padding: 12px; background: var(--color-canvas-soft); border-radius: var(--rounded-sm);">
        <span class="typography-label" style="color: var(--color-primary);">Schedule Mediation Hearing</span>
        <div class="form-grid-2 mt-xs">
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="hearing-date-input">Hearing Date</label>
            <input type="date" id="hearing-date-input" class="text-input" style="height: 38px; padding: 0 10px;">
          </div>
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="hearing-time-input">Hearing Time</label>
            <input type="time" id="hearing-time-input" class="text-input" style="height: 38px; padding: 0 10px;">
          </div>
        </div>
        <!-- SMS Summons Dispatch Toggle -->
        <div style="margin-top: 10px; padding: 8px 12px; background: #ffffff; border-radius: var(--rounded-sm); display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--color-hairline-soft);">
          <div>
            <div style="font-weight: 600; font-size: 0.75rem;">Send Hearing Summons via SMS</div>
            <div class="typography-caption" style="font-size: 0.625rem;">Notify Complainant &amp; Respondent of schedule.</div>
          </div>
          <label style="position: relative; display: inline-block; width: 36px; height: 20px;">
            <input type="checkbox" id="blotter-notify-sms" checked style="opacity: 0; width: 0; height: 0;">
            <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #141414; border-radius: 20px; transition: .2s;"></span>
          </label>
        </div>
      </div>

      <!-- Conditional Settlement Terms Fields -->
      <div id="settlement-fields" style="display: none; margin-top: var(--spacing-sm); padding: 12px; background: var(--color-canvas-soft); border-radius: var(--rounded-sm);">
        <span class="typography-label" style="color: #059669;">Amicable Settlement Terms (Kasunduan)</span>
        <div class="form-group mt-xs" style="margin-bottom: 0;">
          <label class="form-label" for="settlement-terms-input">Terms of Agreement & Conditions</label>
          <textarea id="settlement-terms-input" class="text-input" style="height: 70px; padding: 8px 12px; resize: vertical;" placeholder="Parties agreed to peaceably settle with conditions..."></textarea>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); margin-top: var(--spacing-md); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <button type="button" class="button-outline" onclick="document.getElementById('status-modal').close();" style="height: 38px; padding: 0 16px;">
          Cancel
        </button>
        <button type="submit" class="button-primary" style="height: 38px; padding: 0 20px;">
          Update Status
        </button>
      </div>
    </form>
  </dialog>

  <!-- Case Dossier Briefing Modal -->
  <dialog id="dossier-modal" class="modal-dialog" style="max-width: 680px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-sm);">
      <div>
        <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
          <span class="badge-blue" id="dossier-case-num">BLTR-2026-00001</span>
          <span id="dossier-status-badge" class="badge-amber">Active Mediation</span>
        </div>
        <h3 class="typography-heading-4 mt-xs" id="dossier-incident-title">Physical Altercation</h3>
      </div>
      <button type="button" class="button-pill-soft" onclick="document.getElementById('dossier-modal').close();" style="height: 30px; padding: 0 10px;">
        Close
      </button>
    </div>

    <!-- Parties Involved Cards -->
    <div class="form-grid-2 mb-sm">
      <div class="parties-card">
        <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem;">COMPLAINANT</span>
        <div style="font-weight: 700; font-size: 0.9375rem;" id="dossier-comp-name">Maria Santos</div>
        <div class="typography-caption" id="dossier-comp-meta">Purok 3 &bull; 0917-000-0000</div>
      </div>

      <div class="parties-card">
        <span class="typography-label" style="color: #ef4444; font-size: 0.6875rem;">RESPONDENT</span>
        <div style="font-weight: 700; font-size: 0.9375rem;" id="dossier-resp-name">Pedro Reyes</div>
        <div class="typography-caption" id="dossier-resp-meta">Purok 5 &bull; 0918-000-0000</div>
      </div>
    </div>

    <!-- Incident Facts -->
    <div style="padding: 12px; background-color: var(--color-canvas-soft); border-radius: var(--rounded-sm); margin-bottom: var(--spacing-sm);">
      <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.75rem; color: var(--color-text-muted);">
        <span>Date & Time: <strong style="color: var(--color-ink);" id="dossier-time-meta">-</strong></span>
        <span>Location: <strong style="color: var(--color-ink);" id="dossier-location">-</strong></span>
      </div>
      <div class="typography-label" style="color: var(--color-text-muted); font-size: 0.6875rem; margin-top: 8px;">INCIDENT NARRATIVE REPORT:</div>
      <p style="font-size: 0.84rem; color: var(--color-ink); line-height: 1.5; margin-top: 4px; white-space: pre-wrap;" id="dossier-narrative">-</p>
    </div>

    <!-- Settlement / Schedule details if any -->
    <div id="dossier-settlement-box" style="display: none; padding: 12px; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: var(--rounded-sm); margin-bottom: var(--spacing-sm);">
      <span class="typography-label" style="color: #059669;">AMICABLE SETTLEMENT TERMS (KASUNDUAN)</span>
      <p style="font-size: 0.84rem; color: var(--color-ink); margin-top: 4px;" id="dossier-settlement-text">-</p>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
      <button type="button" class="button-outline" id="dossier-btn-print" style="height: 38px; padding: 0 16px; font-size: 0.8125rem;">
        Print Legal Form &rarr;
      </button>
      <button type="button" class="button-primary" id="dossier-btn-update-status" style="height: 38px; padding: 0 16px; font-size: 0.8125rem;">
        Update Status
      </button>
    </div>
  </dialog>

  <!-- Official Legal Form Print Modal (Summons / Kasunduan) -->
  <dialog id="lupon-print-modal" class="modal-dialog" style="max-width: 840px; width: 95%; max-height: 90vh; overflow-y: auto;">
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-sm);">
      <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
        <span class="badge-blue" id="print-case-badge">BLTR-2026-00001</span>
        <span class="typography-caption">Official Lupong Tagapamayapa Notice</span>
      </div>
      <div style="display: flex; gap: var(--spacing-xs);">
        <select id="print-doc-type-selector" class="filter-select" style="height: 36px;">
          <option value="summons">KP Form 9: Patawag (Summons)</option>
          <option value="kasunduan">KP Form 16: Kasunduan (Settlement)</option>
        </select>
        <button type="button" class="button-outline" onclick="document.getElementById('lupon-print-modal').close();" style="height: 36px; padding: 0 14px; font-size: 0.8125rem;">
          Close
        </button>
        <button type="button" class="button-primary" onclick="window.print();" style="height: 36px; padding: 0 18px; font-size: 0.8125rem;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
            <rect width="12" height="8" x="6" y="14"/>
          </svg>
          <span>Print Notice (Ctrl+P)</span>
        </button>
      </div>
    </div>

    <!-- The Exact Printable Paper Template -->
    <div id="printable-lupon-doc">
      <div class="lupon-header">
        <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #555;">Republic of the Philippines</div>
        <div style="font-size: 0.95rem; font-weight: 700; text-transform: uppercase;" id="print-jurisdiction">Province of Metropolitan Manila &bull; City of San Isidro</div>
        <div style="font-size: 1.2rem; font-weight: 800; color: #111; letter-spacing: 0.02em; margin: 4px 0;" id="print-brgy-name">BARANGAY SAN ISIDRO</div>
        <div style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0066ff;">TANGGAPAN NG LUPONG TAGAPAMAYAPA</div>
      </div>

      <div class="lupon-parties-block">
        <div>
          <div style="font-weight: 700;" id="print-comp-name">MARIA SANTOS</div>
          <div style="font-size: 0.8rem; color: #555;">Nagsusumbong / May-habla (Complainant)</div>
          <div style="margin: 12px 0; font-weight: 700; font-style: italic;">— laban kay —</div>
          <div style="font-weight: 700;" id="print-resp-name">PEDRO REYES</div>
          <div style="font-size: 0.8rem; color: #555;">Ipinagsusumbong (Respondent)</div>
        </div>
        <div style="text-align: right;">
          <div>Barangay Case No.: <strong id="print-case-num-text">BLTR-2026-00001</strong></div>
          <div style="margin-top: 4px;">Ukol sa: <strong id="print-incident-text">Physical Altercation</strong></div>
        </div>
      </div>

      <div class="lupon-title" id="print-form-title">PATAWAG (SUMMONS)</div>

      <div class="lupon-body" id="print-form-body">
        <!-- Injected depending on Summons or Kasunduan -->
      </div>

      <!-- Signatures -->
      <div class="lupon-signatures">
        <div style="text-align: center; width: 220px;">
          <div style="height: 50px;"></div>
          <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: 700; font-size: 0.9rem;" id="print-officer-name">
            HON. PUNONG BARANGAY
          </div>
          <div style="font-size: 0.75rem; color: #555;">Punong Barangay / Lupon Chairman</div>
        </div>

        <div style="text-align: center; width: 220px;">
          <div style="height: 50px;"></div>
          <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: 700; font-size: 0.9rem;">
            PANGKAT SECRETARY
          </div>
          <div style="font-size: 0.75rem; color: #555;">Kalihim ng Lupon</div>
        </div>
      </div>

      <!-- Lupon Document Verification & Scannable QR Security Footer -->
      <div style="margin-top: 28px; border-top: 1px dashed #999; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; font-family: monospace; color: #444;">
        <div style="display: flex; align-items: center; gap: 12px;">
          <div id="print-lupon-qr" style="width: 60px; height: 60px; background: #ffffff; border: 1px solid #ccc; padding: 2px; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"></div>
          <div style="font-size: 0.6875rem; line-height: 1.3; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #555;">
            <strong style="display: block; color: #111; font-size: 0.75rem; text-transform: uppercase;">Official Docket Verification</strong>
            Scan to inspect official case status &bull; <span id="print-lupon-verify-url" style="color: #0066ff;">verify.php</span>
          </div>
        </div>
        <div style="text-align: right; font-size: 0.7rem; color: #666; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
          <div style="font-weight: 700; color: #111;">TANGGAPAN NG LUPONG TAGAPAMAYAPA</div>
          <div>Official Seal of Katarungang Pambarangay</div>
        </div>
      </div>
    </div>
  </dialog>

  <!-- Scripts -->
  <script src="js/api.js"></script>
  <script src="js/lib/qrcode.js"></script>
  <script src="js/components/toast.js"></script>
  <script src="js/components/sidebar.js"></script>

  <script>
    let allCases = [];
    let allResidentsMap = new Map();
    let currentAuthUser = null;
    let activeCaseForAction = null;

    document.addEventListener('DOMContentLoaded', async () => {
      // 1. Guard route: require authenticated official session
      const auth = await authService.requireAuth('login.php');
      if (!auth) return;
      currentAuthUser = auth.user;

      // 2. Render App Shell Sidebar & Topbar
      await AppSidebar.render('blotter');

      // 3. Populate default officer
      document.getElementById('case-officer').value = currentAuthUser.fullName || 'Hon. Desk Officer';

      // 3b. Load Barangay Identity from Settings
      try {
        const idSetting = await window.barangayDB.get('settings', 'identity');
        if (idSetting && idSetting.value) {
          const v = idSetting.value;
          if (v.barangayName) {
            const h = document.getElementById('print-brgy-name');
            if (h) h.textContent = v.barangayName.toUpperCase();
          }
          if (v.province && v.municipalityCity) {
            const j = document.getElementById('print-jurisdiction');
            if (j) j.textContent = `${v.province.toUpperCase()} • ${v.municipalityCity.toUpperCase()}`;
          }
        }
        const officials = await window.barangayDB.getAll('officials');
        const captain = officials.find(o => o.position === 'Punong Barangay' && o.status === 'active');
        if (captain) {
          window.captainName = captain.fullName;
        }
      } catch (e) {
        console.warn('Settings load error in blotter:', e);
      }

      // 4. Load residents into Complainant & Respondent dropdowns
      await loadResidentsDropdowns();

      // 5. Bind UI Events
      bindEventListeners();

      // 6. Refresh cases list
      await refreshCasesList();
    });

    // Populate Residents into select pickers
    async function loadResidentsDropdowns() {
      try {
        const residents = await window.barangayDB.getAll('residents');
        const compSelect = document.getElementById('comp-resident-select');
        const respSelect = document.getElementById('resp-resident-select');

        compSelect.innerHTML = '<option value="">-- Choose from Residents Registry --</option>';
        respSelect.innerHTML = '<option value="">-- Choose from Residents Registry --</option>';

        residents.sort((a, b) => (a.lastName || '').localeCompare(b.lastName || ''));

        residents.forEach(r => {
          allResidentsMap.set(r.id, r);
          const fullName = [r.firstName, r.middleName, r.lastName, r.suffix].filter(Boolean).join(' ');
          const optComp = document.createElement('option');
          optComp.value = r.id;
          optComp.textContent = `${fullName} (${r.purok || 'Resident'})`;
          compSelect.appendChild(optComp);

          const optResp = document.createElement('option');
          optResp.value = r.id;
          optResp.textContent = `${fullName} (${r.purok || 'Resident'})`;
          respSelect.appendChild(optResp);
        });
      } catch (e) {
        console.error('Error loading residents for blotter:', e);
      }
    }

    // Refresh and query cases list
    async function refreshCasesList() {
      try {
        allCases = await window.barangayDB.getAll('blotter_cases');
        allCases.sort((a, b) => new Date(b.filedAt || 0) - new Date(a.filedAt || 0));

        updateTelemetry(allCases);
        applyBlotterFiltersAndRender();
      } catch (e) {
        console.error('Failed to load blotter cases:', e);
        Toast.error('Could not load blotter database.');
      }
    }

    // Update real-time counters
    function updateTelemetry(cases) {
      const total = cases.length;
      const active = cases.filter(c => c.status === 'Active Mediation').length;
      const scheduled = cases.filter(c => c.status === 'Hearing Scheduled').length;
      const settled = cases.filter(c => c.status === 'Amicably Settled').length;

      document.getElementById('blotter-count-badge').textContent = `${total} ${total === 1 ? 'Case' : 'Cases'}`;
      document.getElementById('stat-total-cases').textContent = total;
      document.getElementById('stat-sub-cases').textContent = `${total} peace and order records`;

      document.getElementById('stat-active-cases').textContent = active;
      document.getElementById('stat-sub-active').textContent = `${active} active mediations`;

      document.getElementById('stat-scheduled-cases').textContent = scheduled;
      document.getElementById('stat-sub-scheduled').textContent = `${scheduled} summons scheduled`;

      document.getElementById('stat-settled-cases').textContent = settled;
      document.getElementById('stat-sub-settled').textContent = `${settled} amicably resolved`;
    }

    // Filter and render table
    function applyBlotterFiltersAndRender() {
      const searchTerm = (document.getElementById('search-cases').value || '').toLowerCase().trim();
      const statusFilter = document.getElementById('filter-case-status').value;
      const typeFilter = document.getElementById('filter-incident-type').value;
      const purokFilter = document.getElementById('filter-case-purok').value;

      const hasActiveFilters = searchTerm || statusFilter || typeFilter || purokFilter;
      document.getElementById('btn-clear-blotter-filters').style.display = hasActiveFilters ? 'inline-flex' : 'none';

      const filtered = allCases.filter(c => {
        if (searchTerm) {
          const num = (c.caseNumber || '').toLowerCase();
          const comp = (c.complainantName || '').toLowerCase();
          const resp = (c.respondentName || '').toLowerCase();
          const type = (c.incidentType || '').toLowerCase();
          const loc = (c.incidentLocation || '').toLowerCase();
          const match = num.includes(searchTerm) || comp.includes(searchTerm) || resp.includes(searchTerm) || type.includes(searchTerm) || loc.includes(searchTerm);
          if (!match) return false;
        }

        if (statusFilter && c.status !== statusFilter) return false;
        if (typeFilter && c.incidentType !== typeFilter) return false;
        if (purokFilter && !(c.incidentLocation || '').includes(purokFilter)) return false;

        return true;
      });

      renderTable(filtered, allCases.length);
    }

    // Render Table or Appropriate Empty State
    function renderTable(cases, totalInDB) {
      const tableContainer = document.getElementById('table-container');
      const emptyState = document.getElementById('empty-state');
      const filterEmptyState = document.getElementById('filter-empty-state');
      const tbody = document.getElementById('blotter-table-body');

      if (totalInDB === 0) {
        tableContainer.style.display = 'none';
        emptyState.style.display = 'flex';
        filterEmptyState.style.display = 'none';
        return;
      }

      emptyState.style.display = 'none';

      if (cases.length === 0) {
        tableContainer.style.display = 'none';
        filterEmptyState.style.display = 'flex';
        return;
      }

      filterEmptyState.style.display = 'none';
      tableContainer.style.display = 'block';

      tbody.innerHTML = cases.map(c => {
        let statusBadge = '<span class="badge-amber">Active Mediation</span>';
        if (c.status === 'Hearing Scheduled') statusBadge = '<span class="badge-blue">Hearing Scheduled</span>';
        else if (c.status === 'Amicably Settled') statusBadge = '<span class="badge-emerald">&#10003; Settled</span>';
        else if (c.status === 'Escalated (CFA Issued)') statusBadge = '<span class="badge-rose">Escalated</span>';

        const hearingInfo = c.hearingDate 
          ? `<strong>${c.hearingDate}</strong> ${c.hearingTime || ''}` 
          : (c.status === 'Amicably Settled' ? '<span style="color: #059669;">Resolved</span>' : '<span style="color: var(--color-text-faint);">None scheduled</span>');

        return `
          <tr>
            <td>
              <div style="font-family: monospace; font-weight: 700; color: var(--color-primary); font-size: 0.8125rem;">
                ${c.caseNumber}
              </div>
              <div class="typography-caption">Filed: ${new Date(c.filedAt || Date.now()).toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' })}</div>
            </td>
            <td>
              <div style="display: flex; flex-direction: column; gap: 2px;">
                <div style="font-size: 0.8125rem;"><strong>${c.complainantName}</strong> <span class="typography-caption">(Complainant)</span></div>
                <div class="typography-caption" style="color: var(--color-text-faint);">vs.</div>
                <div style="font-size: 0.8125rem;"><strong>${c.respondentName}</strong> <span class="typography-caption">(Respondent)</span></div>
              </div>
            </td>
            <td>
              <div style="font-weight: 600; font-size: 0.84rem;">${c.incidentType}</div>
              <div class="typography-caption">${c.incidentLocation} &bull; ${c.incidentDate || ''}</div>
            </td>
            <td>
              ${statusBadge}
            </td>
            <td>
              <div style="font-size: 0.8125rem;">${hearingInfo}</div>
            </td>
            <td>
              <div style="display: flex; align-items: center; justify-content: flex-end; gap: 4px;">
                <button class="table-action-btn" onclick="openDossierModal(${c.id})" title="View full case facts">
                  View
                </button>
                <button class="table-action-btn" onclick="openStatusModal(${c.id})" title="Update status or schedule hearing">
                  Status
                </button>
                <button class="table-action-btn" onclick="sendBlotterSummonsSMS(${c.id})" title="Send Hearing Summons SMS to parties">
                  Summons SMS
                </button>
                <button class="table-action-btn" onclick="openPrintModal(${c.id})" title="Print summons or amicable settlement">
                  Print
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Reset Filters
    window.resetBlotterFilters = function() {
      document.getElementById('search-cases').value = '';
      document.getElementById('filter-case-status').value = '';
      document.getElementById('filter-incident-type').value = '';
      document.getElementById('filter-case-purok').value = '';
      applyBlotterFiltersAndRender();
    };

    // Open Case Filing Modal
    function openFileModal() {
      document.getElementById('file-form').reset();
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('incident-date').value = today;
      document.getElementById('case-officer').value = currentAuthUser.fullName || 'Hon. Desk Officer';
      document.getElementById('file-modal').showModal();
    }

    // Open Status Update Modal
    window.openStatusModal = async function(id) {
      const c = await window.barangayDB.get('blotter_cases', id);
      if (!c) return;

      activeCaseForAction = c;
      document.getElementById('status-modal-case-num').textContent = `${c.caseNumber}: ${c.complainantName} vs. ${c.respondentName}`;
      document.getElementById('update-status-select').value = c.status || 'Active Mediation';

      document.getElementById('hearing-date-input').value = c.hearingDate || '';
      document.getElementById('hearing-time-input').value = c.hearingTime || '';
      document.getElementById('settlement-terms-input').value = c.settlementTerms || '';

      toggleStatusFields(c.status || 'Active Mediation');
      document.getElementById('status-modal').showModal();
    };

    function toggleStatusFields(statusVal) {
      document.getElementById('hearing-fields').style.display = (statusVal === 'Hearing Scheduled') ? 'block' : 'none';
      document.getElementById('settlement-fields').style.display = (statusVal === 'Amicably Settled') ? 'block' : 'none';
    }

    // Open Case Dossier Modal
    window.openDossierModal = async function(id) {
      const c = await window.barangayDB.get('blotter_cases', id);
      if (!c) return;

      activeCaseForAction = c;
      document.getElementById('dossier-case-num').textContent = c.caseNumber;
      document.getElementById('dossier-incident-title').textContent = c.incidentType;

      const badgeEl = document.getElementById('dossier-status-badge');
      badgeEl.textContent = c.status;
      badgeEl.className = (c.status === 'Amicably Settled') ? 'badge-emerald' : (c.status === 'Hearing Scheduled') ? 'badge-blue' : 'badge-amber';

      document.getElementById('dossier-comp-name').textContent = c.complainantName;
      document.getElementById('dossier-comp-meta').textContent = `${c.complainantPurok || 'Resident'} • Phone: ${c.complainantPhone || 'None'}`;

      document.getElementById('dossier-resp-name').textContent = c.respondentName;
      document.getElementById('dossier-resp-meta').textContent = `${c.respondentPurok || 'Resident'} • Phone: ${c.respondentPhone || 'None'}`;

      document.getElementById('dossier-time-meta').textContent = `${c.incidentDate || 'Unspecified'} ${c.incidentTime ? 'at ' + c.incidentTime : ''}`;
      document.getElementById('dossier-location').textContent = c.incidentLocation || 'Barangay San Isidro';
      document.getElementById('dossier-narrative').textContent = c.narrative || 'No statement provided.';

      const settlementBox = document.getElementById('dossier-settlement-box');
      if (c.status === 'Amicably Settled' && c.settlementTerms) {
        settlementBox.style.display = 'block';
        document.getElementById('dossier-settlement-text').textContent = c.settlementTerms;
      } else {
        settlementBox.style.display = 'none';
      }

      document.getElementById('dossier-btn-update-status').onclick = () => {
        document.getElementById('dossier-modal').close();
        openStatusModal(c.id);
      };

      document.getElementById('dossier-btn-print').onclick = () => {
        document.getElementById('dossier-modal').close();
        openPrintModal(c.id);
      };

      document.getElementById('dossier-modal').showModal();
    };

    // Open Legal Form Print Modal
    window.openPrintModal = async function(id) {
      const c = await window.barangayDB.get('blotter_cases', id);
      if (!c) return;

      activeCaseForAction = c;
      document.getElementById('print-case-badge').textContent = c.caseNumber;
      document.getElementById('print-case-num-text').textContent = c.caseNumber;
      document.getElementById('print-comp-name').textContent = c.complainantName.toUpperCase();
      document.getElementById('print-resp-name').textContent = c.respondentName.toUpperCase();
      document.getElementById('print-incident-text').textContent = c.incidentType;
      document.getElementById('print-officer-name').textContent = (window.captainName || c.officerInCharge || 'HON. PUNONG BARANGAY').toUpperCase();

      // Render Live Scannable QR Code Badge
      const qrContainer = document.getElementById('print-lupon-qr');
      if (qrContainer && window.QRCode) {
        const ext = window.location.pathname.endsWith('.html') ? '.html' : '.php';
        const verifyUrl = `${window.location.origin}/verify${ext}?code=${encodeURIComponent(c.caseNumber)}`;
        QRCode.render(qrContainer, verifyUrl, { size: 60, margin: 1 });
        const urlEl = document.getElementById('print-lupon-verify-url');
        if (urlEl) urlEl.textContent = `verify${ext}?code=${c.caseNumber}`;
      }

      renderPrintDocumentContent('summons', c);
      document.getElementById('lupon-print-modal').showModal();
    };

    // Render print template content
    function renderPrintDocumentContent(docType, c) {
      const titleEl = document.getElementById('print-form-title');
      const bodyEl = document.getElementById('print-form-body');

      if (docType === 'kasunduan' || c.status === 'Amicably Settled') {
        titleEl.textContent = 'KASUNDUAN (AMICABLE SETTLEMENT)';
        bodyEl.innerHTML = `
          <p>
            KAMI, ang may-habla at ipinagsusumbong sa usaping ito, ay nagkasundo na payapang ayusin ang aming alitan ukol sa <strong>${c.incidentType}</strong> na naganap noong ika-${c.incidentDate || '—'} sa ${c.incidentLocation || 'Barangay San Isidro'}.
          </p>
          <p>
            Alinsunod sa aming pag-uusap sa harap ng Lupong Tagapamayapa, KAMI ay nangangako na tutupad sa mga sumusunod na pinagkasunduang alituntunin:
          </p>
          <div style="padding: 12px 20px; border-left: 3px solid #111; margin: 16px 0; font-weight: 600; font-style: italic;">
            ${c.settlementTerms || 'Nagkasundo ang magkabilang panig na mamuhay nang mapayapa at walang anumang ganti o pananakit sa isa\'t isa.'}
          </div>
          <p>
            PINAGTITIBAY NAMIN ang kasunduang ito ngayong ika-${new Date().getDate()} ng ${new Date().toLocaleDateString([], { month: 'long', year: 'numeric' })} sa Bulwagan ng Barangay San Isidro nang bukal sa aming kalooban.
          </p>
        `;
      } else {
        titleEl.textContent = 'PATAWAG (SUMMONS)';
        const hearingDateStr = c.hearingDate ? `ika-${c.hearingDate}` : 'nakatakdang petsa';
        const hearingTimeStr = c.hearingTime ? `sa ganap na ${c.hearingTime}` : 'sa takdang oras';

        bodyEl.innerHTML = `
          <p>
            KAY: <strong style="text-decoration: underline;">${c.respondentName.toUpperCase()}</strong> (${c.respondentPurok || 'Barangay San Isidro'})
          </p>
          <p>
            Kayo ay tinatawagan upang personal na humarap sa akin kasama ang inyong mga testigo sa Tanggapan ng Punong Barangay / Lupong Tagapamayapa sa <strong>${hearingDateStr}</strong> ${hearingTimeStr}, upang sagutin ang sumbong na inihain laban sa inyo ni <strong>${c.complainantName}</strong> hinggil sa <strong>${c.incidentType}</strong>.
          </p>
          <p>
            IPINABABATID SA INYO na ang hindi pagtupad o sadyang pagtanggi sa patawag na ito ay maaaring magresulta sa pagpataw ng parusa o pag-endorso ng usapin sa hukuman (Certificate to File Action).
          </p>
          <p>
            Iginawad ngayong ika-${new Date().getDate()} ng ${new Date().toLocaleDateString([], { month: 'long', year: 'numeric' })} sa Barangay Hall, Barangay San Isidro.
          </p>
        `;
      }
    }

    // Bind UI Event Listeners
    function bindEventListeners() {
      document.getElementById('btn-open-file-modal').addEventListener('click', openFileModal);

      // Search & Filters
      document.getElementById('search-cases').addEventListener('input', applyBlotterFiltersAndRender);
      document.getElementById('filter-case-status').addEventListener('change', applyBlotterFiltersAndRender);
      document.getElementById('filter-incident-type').addEventListener('change', applyBlotterFiltersAndRender);
      document.getElementById('filter-case-purok').addEventListener('change', applyBlotterFiltersAndRender);

      // Auto-fill Complainant from Select
      document.getElementById('comp-resident-select').addEventListener('change', (e) => {
        const res = allResidentsMap.get(parseInt(e.target.value, 10));
        if (res) {
          document.getElementById('comp-name').value = [res.firstName, res.middleName, res.lastName, res.suffix].filter(Boolean).join(' ');
          document.getElementById('comp-purok').value = `${res.address ? res.address + ', ' : ''}${res.purok || ''}`;
          document.getElementById('comp-phone').value = res.phone || '';
        }
      });

      // Auto-fill Respondent from Select
      document.getElementById('resp-resident-select').addEventListener('change', (e) => {
        const res = allResidentsMap.get(parseInt(e.target.value, 10));
        if (res) {
          document.getElementById('resp-name').value = [res.firstName, res.middleName, res.lastName, res.suffix].filter(Boolean).join(' ');
          document.getElementById('resp-purok').value = `${res.address ? res.address + ', ' : ''}${res.purok || ''}`;
          document.getElementById('resp-phone').value = res.phone || '';
        }
      });

      // Status selector change in modal
      document.getElementById('update-status-select').addEventListener('change', (e) => {
        toggleStatusFields(e.target.value);
      });

      // Print document type change
      document.getElementById('print-doc-type-selector').addEventListener('change', (e) => {
        if (activeCaseForAction) {
          renderPrintDocumentContent(e.target.value, activeCaseForAction);
        }
      });

      // Handle Incident Filing Form Submit
      document.getElementById('file-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const compName = document.getElementById('comp-name').value.trim();
        const respName = document.getElementById('resp-name').value.trim();
        const incidentType = document.getElementById('incident-type').value;
        const incidentDate = document.getElementById('incident-date').value;
        const incidentLocation = document.getElementById('incident-location').value.trim();
        const narrative = document.getElementById('incident-narrative').value.trim();

        if (!compName || !respName || !incidentLocation || !narrative) {
          Toast.error('Please fill in required fields: Complainant, Respondent, Location, and Narrative.');
          return;
        }

        const year = new Date().getFullYear();
        const seq = String(allCases.length + 1).padStart(5, '0');
        const caseNumber = `BLTR-${year}-${seq}`;

        const caseData = {
          caseNumber: caseNumber,
          incidentType: incidentType,
          complainantName: compName,
          complainantPurok: document.getElementById('comp-purok').value.trim(),
          complainantPhone: document.getElementById('comp-phone').value.trim(),
          respondentName: respName,
          respondentPurok: document.getElementById('resp-purok').value.trim(),
          respondentPhone: document.getElementById('resp-phone').value.trim(),
          incidentDate: incidentDate,
          incidentTime: document.getElementById('incident-time').value,
          incidentLocation: incidentLocation,
          narrative: narrative,
          officerInCharge: document.getElementById('case-officer').value.trim() || 'Hon. Desk Officer',
          status: 'Active Mediation',
          filedAt: new Date().toISOString()
        };

        try {
          await window.barangayDB.add('blotter_cases', caseData);

          if (window.authService) {
            await window.authService.logAudit('BLOTTER_FILED', `Filed case ${caseNumber} (${incidentType}): ${compName} vs ${respName}`);
          }

          document.getElementById('file-modal').close();
          Toast.success(`Case ${caseNumber} successfully recorded.`);
          await refreshCasesList();
        } catch (err) {
          console.error('Error filing blotter case:', err);
          Toast.error('Could not save blotter case.');
        }
      });

      // Handle Status Update Submit
      document.getElementById('status-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!activeCaseForAction) return;

        const newStatus = document.getElementById('update-status-select').value;
        const hearingDate = document.getElementById('hearing-date-input').value;
        const hearingTime = document.getElementById('hearing-time-input').value;
        const settlementTerms = document.getElementById('settlement-terms-input').value.trim();

        activeCaseForAction.status = newStatus;
        if (newStatus === 'Hearing Scheduled') {
          activeCaseForAction.hearingDate = hearingDate;
          activeCaseForAction.hearingTime = hearingTime;

          // Dispatch Summons SMS
          const notifySms = document.getElementById('blotter-notify-sms');
          if (!notifySms || notifySms.checked) {
            const compPhone = activeCaseForAction.complainantPhone || '0917-000-0001';
            const respPhone = activeCaseForAction.respondentPhone || '0918-000-0002';
            const scheduleStr = `${hearingDate || 'Scheduled Date'} ${hearingTime || '9:00 AM'}`;

            try {
              // Complainant SMS
              await window.barangayDB.add('notifications', {
                dispatchCode: `SMS-${new Date().getFullYear()}-${Math.floor(10000 + Math.random() * 90000)}`,
                recipientName: activeCaseForAction.complainantName,
                recipientContact: compPhone,
                channel: 'SMS',
                category: 'Summons',
                message: `PABATID mula sa Lupon Tagapamayapa: Kayo ay inaanyayahang dumalo sa mediation hearing ukol sa Kaso Blg ${activeCaseForAction.caseNumber} sa darating na ${scheduleStr} sa Barangay Session Hall.`,
                status: 'Delivered',
                gatewayRef: 'SMP-' + Math.random().toString(16).substr(2, 6),
                costCredits: 1,
                createdAt: new Date().toISOString()
              });

              // Respondent SMS
              await window.barangayDB.add('notifications', {
                dispatchCode: `SMS-${new Date().getFullYear()}-${Math.floor(10000 + Math.random() * 90000)}`,
                recipientName: activeCaseForAction.respondentName,
                recipientContact: respPhone,
                channel: 'SMS',
                category: 'Summons',
                message: `PATAWAG (SUMMONS) mula sa Lupon Tagapamayapa: Hinihiling ang inyong pagdalo sa mediation hearing ukol sa Kaso Blg ${activeCaseForAction.caseNumber} sa darating na ${scheduleStr} sa Barangay Session Hall.`,
                status: 'Delivered',
                gatewayRef: 'SMP-' + Math.random().toString(16).substr(2, 6),
                costCredits: 1,
                createdAt: new Date().toISOString()
              });

              Toast.info(`Lupon summons SMS dispatched to both parties.`);
            } catch (err) {
              console.warn('Summons SMS error:', err);
            }
          }
        } else if (newStatus === 'Amicably Settled') {
          activeCaseForAction.settlementTerms = settlementTerms || 'Amicably resolved between parties.';
          activeCaseForAction.settledAt = new Date().toISOString();
        }

        activeCaseForAction.updatedAt = new Date().toISOString();

        try {
          await window.barangayDB.put('blotter_cases', activeCaseForAction);

          if (window.authService) {
            await window.authService.logAudit('BLOTTER_STATUS_UPDATED', `Updated case ${activeCaseForAction.caseNumber} to "${newStatus}"`);
          }

          document.getElementById('status-modal').close();
          Toast.success(`Case ${activeCaseForAction.caseNumber} status updated.`);
          await refreshCasesList();
        } catch (err) {
          console.error(err);
          Toast.error('Could not update case status.');
        }
      });
    }

    // Direct Summons SMS Trigger
    window.sendBlotterSummonsSMS = async function(id) {
      try {
        const c = await window.barangayDB.get('blotter_cases', id);
        if (!c) return;

        const scheduleStr = c.hearingDate ? `${c.hearingDate} ${c.hearingTime || ''}` : 'Upcoming Hearing';
        if (!confirm(`Dispatch Lupon Summons SMS for Case ${c.caseNumber} to:\n• Complainant: ${c.complainantName}\n• Respondent: ${c.respondentName}?`)) return;

        // Complainant SMS
        await window.barangayDB.add('notifications', {
          dispatchCode: `SMS-${new Date().getFullYear()}-${Math.floor(10000 + Math.random() * 90000)}`,
          recipientName: c.complainantName,
          recipientContact: c.complainantPhone || '0917-000-0001',
          channel: 'SMS',
          category: 'Summons',
          message: `PABATID mula sa Lupon Tagapamayapa: Kayo ay inaanyayahang dumalo sa mediation hearing ukol sa Kaso Blg ${c.caseNumber} sa darating na ${scheduleStr} sa Barangay Session Hall.`,
          status: 'Delivered',
          gatewayRef: 'SMP-' + Math.random().toString(16).substr(2, 6),
          costCredits: 1,
          createdAt: new Date().toISOString()
        });

        // Respondent SMS
        await window.barangayDB.add('notifications', {
          dispatchCode: `SMS-${new Date().getFullYear()}-${Math.floor(10000 + Math.random() * 90000)}`,
          recipientName: c.respondentName,
          recipientContact: c.respondentPhone || '0918-000-0002',
          channel: 'SMS',
          category: 'Summons',
          message: `PATAWAG (SUMMONS) mula sa Lupon Tagapamayapa: Hinihiling ang inyong pagdalo sa mediation hearing ukol sa Kaso Blg ${c.caseNumber} sa darating na ${scheduleStr} sa Barangay Session Hall.`,
          status: 'Delivered',
          gatewayRef: 'SMP-' + Math.random().toString(16).substr(2, 6),
          costCredits: 1,
          createdAt: new Date().toISOString()
        });

        Toast.success(`Summons SMS successfully dispatched to ${c.complainantName} & ${c.respondentName}!`);
      } catch (err) {
        console.error('Failed to send summons SMS:', err);
        Toast.error('Could not dispatch summons SMS.');
      }
    };
  </script>
</body>
</html>
