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
  <title>Residents Registry &bull; Barangay Management System</title>
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

    @media (max-width: 768px) {
      .filter-toolbar {
        flex-direction: column;
        align-items: stretch;
        padding: 10px;
        gap: var(--spacing-xs);
      }
      .filter-toolbar .filter-group {
        flex-direction: column;
        align-items: stretch;
        width: 100%;
      }
      .filter-toolbar .search-input-wrap {
        width: 100%;
        min-width: 0;
      }
      .filter-toolbar .filter-select {
        width: 100%;
      }
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

    .checkbox-pill-grid {
      display: flex;
      flex-wrap: wrap;
      gap: var(--spacing-xs);
      margin-top: 4px;
    }

    .checkbox-pill-label {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 12px;
      background-color: var(--color-canvas-soft);
      border: 1px solid var(--color-hairline);
      border-radius: var(--rounded-full);
      font-size: 0.75rem;
      font-weight: 500;
      color: var(--color-ink);
      cursor: pointer;
      user-select: none;
      transition: all 0.15s ease;
    }

    .checkbox-pill-label:hover {
      border-color: var(--color-primary);
    }

    .checkbox-pill-label input[type="checkbox"]:checked + span {
      font-weight: 600;
      color: var(--color-primary);
    }

    .resident-avatar {
      width: 36px;
      height: 36px;
      border-radius: 30%;
      background-color: var(--color-canvas-soft);
      color: var(--color-ink);
      font-weight: 700;
      font-size: 0.8125rem;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1px solid var(--color-hairline);
      flex-shrink: 0;
    }

    .dossier-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: var(--spacing-md);
      margin-top: var(--spacing-md);
    }

    .dossier-item {
      display: flex;
      flex-direction: column;
      gap: 2px;
      padding: 10px 14px;
      background-color: var(--color-canvas-soft);
      border-radius: var(--rounded-sm);
    }

    .dossier-label {
      font-size: 0.6875rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--color-text-muted);
    }

    .dossier-val {
      font-size: 0.875rem;
      font-weight: 500;
      color: var(--color-ink);
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
                <h1 class="typography-heading-2">Resident Registry.</h1>
                <span class="badge-neutral" id="resident-count-badge">0 Records</span>
              </div>
              <p class="typography-body-lg">
                Official barangay demographic profiling, voter eligibility, and household roster.
              </p>
            </div>
            <div style="display: flex; align-items: center; gap: var(--spacing-sm); flex-wrap: wrap;">
              <a href="households.php" class="button-outline" title="Manage Household Profiles & Family Trees" style="height: 38px; padding: 0 14px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                  <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <span>Households &rarr;</span>
              </a>
              <a href="health.php" class="button-outline" title="Barangay Health Station & Nutrition Information System" style="height: 38px; padding: 0 14px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #10b981;">
                  <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
                <span>Health Station &rarr;</span>
              </a>
              <button class="button-outline" id="btn-export-csv" title="Export current records to CSV spreadsheet" style="height: 38px; padding: 0 16px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="7 10 12 15 17 10"/>
                  <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span>Export CSV</span>
              </button>
              <button class="button-primary" id="btn-open-add-modal" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="12" y1="5" x2="12" y2="19"/>
                  <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Register Resident</span>
              </button>
            </div>
          </div>
        </section>

        <!-- Demographics Telemetry Ladder -->
        <section>
          <div class="stats-ladder">
            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">TOTAL RESIDENTS</span>
                <span class="badge-neutral">Roster</span>
              </div>
              <div class="stat-number" id="stat-total-residents">0</div>
              <div class="typography-caption" id="stat-sub-residents">0 households recorded</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">REGISTERED VOTERS</span>
                <span class="badge-blue">Electoral</span>
              </div>
              <div class="stat-number" id="stat-total-voters">0</div>
              <div class="typography-caption" id="stat-sub-voters">0% voter participation</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">SENIOR CITIZENS</span>
                <span class="badge-amber">60+ Years</span>
              </div>
              <div class="stat-number" id="stat-total-seniors">0</div>
              <div class="typography-caption" id="stat-sub-seniors">Elderly care eligible</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">SPECIAL ASSISTANCE</span>
                <span class="badge-emerald">PWD / 4Ps</span>
              </div>
              <div class="stat-number" id="stat-total-assistance">0</div>
              <div class="typography-caption" id="stat-sub-assistance">Welfare beneficiaries</div>
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
                <input type="text" id="search-residents" class="text-input" placeholder="Search by name, address, or phone..." autocomplete="off">
              </div>

              <!-- Purok Filter -->
              <select id="filter-purok" class="filter-select">
                <option value="">All Puroks / Zones</option>
                <option value="Purok 1">Purok 1</option>
                <option value="Purok 2">Purok 2</option>
                <option value="Purok 3">Purok 3</option>
                <option value="Purok 4">Purok 4</option>
                <option value="Purok 5">Purok 5</option>
                <option value="Purok 6">Purok 6</option>
                <option value="Purok 7">Purok 7</option>
                <option value="Sitio Center">Sitio Center</option>
              </select>

              <!-- Classification Filter -->
              <select id="filter-classification" class="filter-select">
                <option value="">All Classifications</option>
                <option value="voter">Registered Voters</option>
                <option value="senior">Senior Citizens (60+)</option>
                <option value="pwd">PWD (Disability)</option>
                <option value="soloParent">Solo Parents</option>
                <option value="fourPs">4Ps Beneficiaries</option>
                <option value="indigent">Indigent Families</option>
              </select>

              <!-- Gender Filter -->
              <select id="filter-gender" class="filter-select">
                <option value="">All Genders</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>

            <div class="filter-group">
              <button id="btn-clear-filters" class="button-pill-soft" style="height: 34px; padding: 0 12px; font-size: 0.75rem; display: none;">
                Reset Filters
              </button>
            </div>
          </div>
        </section>

        <!-- Resident Data Table Section -->
        <section class="mb-section">
          <!-- Populated Table Container -->
          <div id="table-container" class="data-table-container" style="display: none;">
            <div class="data-table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Resident Name</th>
                    <th>Purok / Address</th>
                    <th>Age & Civil Status</th>
                    <th>Classifications</th>
                    <th>Contact</th>
                    <th style="text-align: right;">Actions</th>
                  </tr>
                </thead>
                <tbody id="resident-table-body">
                  <!-- Injected via JavaScript -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- Empty State (No records in DB) -->
          <div id="empty-state" class="empty-state-card" style="padding: var(--spacing-section) var(--spacing-xl);">
            <div class="nav-brand-icon" style="width: 56px; height: 56px; font-size: 1.5rem;">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
            </div>
            <div>
              <h3 class="typography-heading-3">No Resident Records Found.</h3>
              <p class="typography-body mt-xs" style="color: var(--color-text-muted); max-width: 460px;">
                The database is clean with zero dummy data. Click below to profile and register your first community resident.
              </p>
            </div>
            <button class="button-primary mt-sm" onclick="document.getElementById('btn-open-add-modal').click();">
              + Register First Resident
            </button>
          </div>

          <!-- Filter Match Empty State -->
          <div id="filter-empty-state" class="empty-state-card" style="display: none; padding: var(--spacing-xl);">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-text-muted);">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <h4 class="typography-heading-4">No Matching Residents.</h4>
            <p class="typography-body-sm" style="color: var(--color-text-muted);">
              No records match your active search terms or filter selections.
            </p>
            <button class="button-pill-soft" onclick="resetFilters();">Clear All Filters</button>
          </div>
        </section>
      </main>
    </div>
  </div>

  <!-- Resident Registration / Edit Modal -->
  <dialog id="resident-modal" class="modal-dialog" style="max-width: 700px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-sm);">
      <div>
        <h3 class="typography-heading-4" id="modal-title">Register Resident Profile.</h3>
        <p class="typography-caption">Enter official civil and demographic information.</p>
      </div>
      <button type="button" class="button-pill-soft" onclick="document.getElementById('resident-modal').close();" style="height: 30px; padding: 0 10px;">
        Cancel
      </button>
    </div>

    <form id="resident-form" novalidate>
      <input type="hidden" id="form-resident-id" value="">

      <!-- Section: Personal Details -->
      <div style="margin-bottom: var(--spacing-md);">
        <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem; text-transform: uppercase;">1. Personal Information</span>
        
        <div class="form-grid-3 mt-xs">
          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="res-first-name">First Name <span style="color: var(--color-primary);">*</span></label>
            <input type="text" id="res-first-name" class="text-input" style="height: 42px;" placeholder="e.g. Juan" required>
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="res-middle-name">Middle Name</label>
            <input type="text" id="res-middle-name" class="text-input" style="height: 42px;" placeholder="e.g. Mercado">
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="res-last-name">Last Name <span style="color: var(--color-primary);">*</span></label>
            <input type="text" id="res-last-name" class="text-input" style="height: 42px;" placeholder="e.g. Dela Cruz" required>
          </div>
        </div>

        <div class="form-grid-3 mt-xs">
          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="res-suffix">Suffix</label>
            <select id="res-suffix" class="text-input" style="height: 42px; padding: 0 12px;">
              <option value="">None</option>
              <option value="Jr.">Jr.</option>
              <option value="Sr.">Sr.</option>
              <option value="II">II</option>
              <option value="III">III</option>
              <option value="IV">IV</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="res-birthdate">Date of Birth <span style="color: var(--color-primary);">*</span></label>
            <input type="date" id="res-birthdate" class="text-input" style="height: 42px; padding: 0 12px;" required>
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="res-age">Calculated Age</label>
            <input type="text" id="res-age" class="text-input" style="height: 42px; background: var(--color-canvas-soft);" readonly placeholder="Select birthdate">
          </div>
        </div>

        <div class="form-grid-3 mt-xs">
          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="res-gender">Gender <span style="color: var(--color-primary);">*</span></label>
            <select id="res-gender" class="text-input" style="height: 42px; padding: 0 12px;" required>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="res-civil-status">Civil Status</label>
            <select id="res-civil-status" class="text-input" style="height: 42px; padding: 0 12px;">
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Widowed">Widowed</option>
              <option value="Separated">Separated</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="res-occupation">Occupation</label>
            <input type="text" id="res-occupation" class="text-input" style="height: 42px;" placeholder="e.g. Farmer / Teacher">
          </div>
        </div>
      </div>

      <!-- Section: Residency & Address -->
      <div style="margin-bottom: var(--spacing-md); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem; text-transform: uppercase;">2. Barangay Residency & Address</span>

        <div class="form-grid-2 mt-xs">
          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="res-purok">Purok / Zone <span style="color: var(--color-primary);">*</span></label>
            <select id="res-purok" class="text-input" style="height: 42px; padding: 0 12px;" required>
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

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="res-address">House / Street Address</label>
            <input type="text" id="res-address" class="text-input" style="height: 42px;" placeholder="e.g. Block 4 Lot 12 Rizal St.">
          </div>
        </div>

        <div class="form-grid-2 mt-xs">
          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="res-phone">Mobile / Telephone Number</label>
            <input type="tel" id="res-phone" class="text-input" style="height: 42px;" placeholder="e.g. 0917-123-4567">
          </div>

          <div class="form-group" style="margin-bottom: var(--spacing-xs);">
            <label class="form-label" for="res-emergency">Emergency Contact (Name & Phone)</label>
            <input type="text" id="res-emergency" class="text-input" style="height: 42px;" placeholder="e.g. Maria Dela Cruz (0918-987-6543)">
          </div>
        </div>
      </div>

      <!-- Section: Special Classifications & Welfare -->
      <div style="margin-bottom: var(--spacing-lg); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem; text-transform: uppercase;">3. Classifications & Government Assistance</span>

        <div class="checkbox-pill-grid mt-xs">
          <label class="checkbox-pill-label">
            <input type="checkbox" id="res-is-voter">
            <span>Registered Voter</span>
          </label>

          <label class="checkbox-pill-label">
            <input type="checkbox" id="res-is-senior">
            <span>Senior Citizen</span>
          </label>

          <label class="checkbox-pill-label">
            <input type="checkbox" id="res-is-pwd">
            <span>PWD (Disability)</span>
          </label>

          <label class="checkbox-pill-label">
            <input type="checkbox" id="res-is-solo-parent">
            <span>Solo Parent</span>
          </label>

          <label class="checkbox-pill-label">
            <input type="checkbox" id="res-is-four-ps">
            <span>4Ps Beneficiary</span>
          </label>

          <label class="checkbox-pill-label">
            <input type="checkbox" id="res-is-indigent">
            <span>Indigent Classification</span>
          </label>
        </div>

        <div id="precinct-wrap" class="form-group mt-xs" style="display: none; max-width: 280px;">
          <label class="form-label" for="res-precinct">Voter Precinct Number</label>
          <input type="text" id="res-precinct" class="text-input" style="height: 38px;" placeholder="e.g. 0042A">
        </div>
      </div>

      <!-- Form Actions -->
      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-md);">
        <button type="button" class="button-outline" onclick="document.getElementById('resident-modal').close();" style="height: 42px; padding: 0 20px;">
          Cancel
        </button>
        <button type="submit" class="button-primary" id="btn-save-resident" style="height: 42px; padding: 0 24px;">
          Save Resident Profile
        </button>
      </div>
    </form>
  </dialog>

  <!-- Resident Dossier View Modal -->
  <dialog id="dossier-modal" class="modal-dialog" style="max-width: 620px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-sm);">
      <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
        <div id="dossier-avatar" class="resident-avatar" style="width: 44px; height: 44px; font-size: 1.125rem;">JD</div>
        <div>
          <h3 class="typography-heading-4" id="dossier-name">Juan Dela Cruz</h3>
          <p class="typography-caption" id="dossier-purok">Purok 4 &bull; Resident</p>
        </div>
      </div>
      <button type="button" class="button-pill-soft" onclick="document.getElementById('dossier-modal').close();" style="height: 30px; padding: 0 10px;">
        Close
      </button>
    </div>

    <div id="dossier-badges" style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: var(--spacing-md);">
      <!-- Injected badges -->
    </div>

    <div class="dossier-grid">
      <div class="dossier-item">
        <span class="dossier-label">Age & Birthdate</span>
        <span class="dossier-val" id="dossier-age-bday">-</span>
      </div>

      <div class="dossier-item">
        <span class="dossier-label">Gender & Civil Status</span>
        <span class="dossier-val" id="dossier-gender-civil">-</span>
      </div>

      <div class="dossier-item">
        <span class="dossier-label">Address</span>
        <span class="dossier-val" id="dossier-address">-</span>
      </div>

      <div class="dossier-item">
        <span class="dossier-label">Contact Number</span>
        <span class="dossier-val" id="dossier-contact">-</span>
      </div>

      <div class="dossier-item">
        <span class="dossier-label">Occupation</span>
        <span class="dossier-val" id="dossier-occupation">-</span>
      </div>

      <div class="dossier-item">
        <span class="dossier-label">Voter Status</span>
        <span class="dossier-val" id="dossier-voter">-</span>
      </div>

      <div class="dossier-item" style="grid-column: span 2;">
        <span class="dossier-label">Emergency Contact</span>
        <span class="dossier-val" id="dossier-emergency">-</span>
      </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); margin-top: var(--spacing-lg); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-md);">
      <button type="button" class="button-outline" id="dossier-btn-issue-id" style="height: 38px; padding: 0 16px; font-size: 0.8125rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="20" height="14" x="2" y="5" rx="2"/>
          <line x1="2" y1="10" x2="22" y2="10"/>
        </svg>
        <span>Generate ID Card &rarr;</span>
      </button>
      <a href="health.php" class="button-outline" id="dossier-btn-health" style="height: 38px; padding: 0 16px; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #10b981;">
          <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
        </svg>
        <span>Health Record &rarr;</span>
      </a>
      <button type="button" class="button-outline" id="dossier-btn-issue-cert" style="height: 38px; padding: 0 16px; font-size: 0.8125rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
        </svg>
        <span>Issue Clearance &rarr;</span>
      </button>
      <button type="button" class="button-primary" id="dossier-btn-edit" style="height: 38px; padding: 0 16px; font-size: 0.8125rem;">
        Edit Record
      </button>
    </div>
  </dialog>

  <!-- Delete Confirmation Modal -->
  <dialog id="delete-modal" class="modal-dialog" style="max-width: 440px; width: 90%; text-align: center;">
    <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-sm);">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 6h18"/>
        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
      </svg>
    </div>
    <h3 class="typography-heading-4">Archive Resident Record?</h3>
    <p class="typography-body-sm mt-xs" style="color: var(--color-text-muted);">
      Are you sure you want to remove <strong id="delete-resident-name" style="color: var(--color-ink);"></strong> from the active resident database? This action will be logged in the security audit trail.
    </p>
    <div style="display: flex; justify-content: center; gap: var(--spacing-sm); margin-top: var(--spacing-lg);">
      <button type="button" class="button-outline" onclick="document.getElementById('delete-modal').close();" style="height: 38px; padding: 0 18px;">
        Cancel
      </button>
      <button type="button" class="button-primary" id="btn-confirm-delete" style="background-color: #ef4444; height: 38px; padding: 0 18px;">
        Confirm Delete
      </button>
    </div>
  </dialog>

  <!-- Scripts -->
  <script src="js/api.js"></script>
  
  <script src="js/components/toast.js"></script>
  <script src="js/components/sidebar.js"></script>

  <script>
    let allResidents = [];
    let activeResidentForAction = null;

    document.addEventListener('DOMContentLoaded', async () => {
      // 1. Render App Shell Sidebar & Topbar immediately
      try {
        await AppSidebar.render('residents');
      } catch (err) {
        console.error('Sidebar mount error:', err);
      }

      // 2. Bind UI Events
      bindEventListeners();

      // 3. Load initial database records
      await refreshResidentsList();
    });

    // Calculate age from birthdate
    function calculateAge(birthdateStr) {
      if (!birthdateStr) return null;
      const bday = new Date(birthdateStr);
      if (isNaN(bday)) return null;
      const today = new Date();
      let age = today.getFullYear() - bday.getFullYear();
      const m = today.getMonth() - bday.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < bday.getDate())) {
        age--;
      }
      return age >= 0 ? age : 0;
    }

    // Format Full Name
    function formatFullName(res) {
      const parts = [res.firstName, res.middleName, res.lastName];
      let name = parts.filter(Boolean).join(' ');
      if (res.suffix) name += ` ${res.suffix}`;
      return name;
    }

    // Query and render residents list
    async function refreshResidentsList() {
      try {
        allResidents = await window.barangayDB.getAll('residents');
        
        // Sort alphabetically by Last Name
        allResidents.sort((a, b) => (a.lastName || '').localeCompare(b.lastName || ''));

        updateTelemetry(allResidents);
        applyFiltersAndRender();
      } catch (e) {
        console.error('Failed to load residents:', e);
        Toast.error('Could not load residents database.');
      }
    }

    // Update real-time telemetry counters
    function updateTelemetry(residents) {
      const total = residents.length;
      const voters = residents.filter(r => r.isVoter).length;
      const seniors = residents.filter(r => (r.age >= 60) || r.isSenior).length;
      const assistance = residents.filter(r => r.isPwd || r.isFourPs || r.isIndigent || r.isSoloParent).length;

      document.getElementById('resident-count-badge').textContent = `${total} ${total === 1 ? 'Record' : 'Records'}`;
      document.getElementById('stat-total-residents').textContent = total;
      
      const householdCount = new Set(residents.map(r => r.address).filter(Boolean)).size;
      document.getElementById('stat-sub-residents').textContent = `${householdCount} unique addresses`;

      document.getElementById('stat-total-voters').textContent = voters;
      const voterPct = total > 0 ? Math.round((voters / total) * 100) : 0;
      document.getElementById('stat-sub-voters').textContent = `${voterPct}% of total population`;

      document.getElementById('stat-total-seniors').textContent = seniors;
      document.getElementById('stat-sub-seniors').textContent = `${seniors} registered senior citizens`;

      document.getElementById('stat-total-assistance').textContent = assistance;
      document.getElementById('stat-sub-assistance').textContent = `${assistance} welfare profiles`;
    }

    // Filter and display table rows
    function applyFiltersAndRender() {
      const searchTerm = (document.getElementById('search-residents').value || '').toLowerCase().trim();
      const purokFilter = document.getElementById('filter-purok').value;
      const classFilter = document.getElementById('filter-classification').value;
      const genderFilter = document.getElementById('filter-gender').value;

      const hasActiveFilters = searchTerm || purokFilter || classFilter || genderFilter;
      document.getElementById('btn-clear-filters').style.display = hasActiveFilters ? 'inline-flex' : 'none';

      const filtered = allResidents.filter(res => {
        // Search term filter
        if (searchTerm) {
          const fullName = formatFullName(res).toLowerCase();
          const address = (res.address || '').toLowerCase();
          const purok = (res.purok || '').toLowerCase();
          const phone = (res.phone || '').toLowerCase();
          const match = fullName.includes(searchTerm) || address.includes(searchTerm) || purok.includes(searchTerm) || phone.includes(searchTerm);
          if (!match) return false;
        }

        // Purok filter
        if (purokFilter && res.purok !== purokFilter) {
          return false;
        }

        // Classification filter
        if (classFilter) {
          if (classFilter === 'voter' && !res.isVoter) return false;
          if (classFilter === 'senior' && (res.age < 60 && !res.isSenior)) return false;
          if (classFilter === 'pwd' && !res.isPwd) return false;
          if (classFilter === 'soloParent' && !res.isSoloParent) return false;
          if (classFilter === 'fourPs' && !res.isFourPs) return false;
          if (classFilter === 'indigent' && !res.isIndigent) return false;
        }

        // Gender filter
        if (genderFilter && res.gender !== genderFilter) {
          return false;
        }

        return true;
      });

      renderTable(filtered, allResidents.length);
    }

    // Render Table or Appropriate Empty State
    function renderTable(residents, totalInDB) {
      const tableContainer = document.getElementById('table-container');
      const emptyState = document.getElementById('empty-state');
      const filterEmptyState = document.getElementById('filter-empty-state');
      const tbody = document.getElementById('resident-table-body');

      if (totalInDB === 0) {
        tableContainer.style.display = 'none';
        emptyState.style.display = 'flex';
        filterEmptyState.style.display = 'none';
        return;
      }

      emptyState.style.display = 'none';

      if (residents.length === 0) {
        tableContainer.style.display = 'none';
        filterEmptyState.style.display = 'flex';
        return;
      }

      filterEmptyState.style.display = 'none';
      tableContainer.style.display = 'block';

      tbody.innerHTML = residents.map(res => {
        const initials = `${(res.firstName || ' ')[0]}${(res.lastName || ' ')[0]}`.toUpperCase();
        const photo = res.photoUrl || res.photo_url;
        const avatarHtml = photo
          ? `<img src="${photo}" alt="Resident Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 30%;">`
          : initials;
        const fullName = formatFullName(res);
        const age = res.age || calculateAge(res.birthdate) || '—';

        // Badges HTML
        const badges = [];
        if (res.isVoter) badges.push(`<span class="badge-blue" title="Voter Precinct: ${res.precinct || 'Assigned'}">Voter</span>`);
        if (age >= 60 || res.isSenior) badges.push(`<span class="badge-amber">Senior</span>`);
        if (res.isPwd) badges.push(`<span class="badge-purple">PWD</span>`);
        if (res.isSoloParent) badges.push(`<span class="badge-rose">Solo Parent</span>`);
        if (res.isFourPs) badges.push(`<span class="badge-emerald">4Ps</span>`);
        if (res.isIndigent) badges.push(`<span class="badge-neutral">Indigent</span>`);

        const badgesHtml = badges.length > 0 ? badges.slice(0, 3).join(' ') : `<span class="typography-caption" style="color: var(--color-text-faint);">Standard</span>`;

        return `
          <tr>
            <td>
              <div style="display: flex; align-items: center; gap: 10px;">
                <div class="resident-avatar" style="overflow: hidden;">${avatarHtml}</div>
                <div>
                  <div style="font-weight: 600; color: var(--color-ink); font-size: 0.875rem;">${fullName}</div>
                  <div class="typography-caption">${res.gender || 'Not specified'} &bull; ${res.civilStatus || 'Single'}</div>
                </div>
              </div>
            </td>
            <td>
              <div style="font-weight: 500;">${res.purok || '—'}</div>
              <div class="typography-caption">${res.address || 'No street listed'}</div>
            </td>
            <td>
              <div style="font-weight: 600;">${age} yrs</div>
              <div class="typography-caption">${res.birthdate || 'No date'}</div>
            </td>
            <td>
              <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                ${badgesHtml}
                ${badges.length > 3 ? `<span class="badge-neutral">+${badges.length - 3}</span>` : ''}
              </div>
            </td>
            <td>
              <div style="font-size: 0.8125rem;">${res.phone || '<span style="color: var(--color-text-faint);">None</span>'}</div>
            </td>
            <td>
              <div style="display: flex; align-items: center; justify-content: flex-end; gap: 4px;">
                <button class="table-action-btn" onclick="openDossierModal(${res.id})" title="View complete dossier">
                  View
                </button>
                <button class="table-action-btn" onclick="window.location.href='resident-id.php?residentId=${res.id}'" title="Generate PVC Resident ID">
                  ID Card
                </button>
                <button class="table-action-btn" onclick="openEditModal(${res.id})" title="Edit resident details">
                  Edit
                </button>
                <button class="table-action-btn danger" onclick="openDeleteModal(${res.id})" title="Archive/Delete resident">
                  Delete
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Reset Search & Filters
    window.resetFilters = function() {
      document.getElementById('search-residents').value = '';
      document.getElementById('filter-purok').value = '';
      document.getElementById('filter-classification').value = '';
      document.getElementById('filter-gender').value = '';
      applyFiltersAndRender();
    };

    // Open Registration Modal for New Resident
    function openAddModal() {
      document.getElementById('resident-form').reset();
      document.getElementById('form-resident-id').value = '';
      document.getElementById('modal-title').textContent = 'Register Resident Profile.';
      document.getElementById('btn-save-resident').textContent = 'Save Resident Profile';
      document.getElementById('res-age').value = '';
      document.getElementById('precinct-wrap').style.display = 'none';
      document.getElementById('resident-modal').showModal();
    }

    // Open Modal to Edit Existing Resident
    window.openEditModal = async function(id) {
      try {
        const resident = await window.barangayDB.get('residents', id);
        if (!resident) {
          Toast.error('Resident record not found.');
          return;
        }

        document.getElementById('form-resident-id').value = resident.id;
        document.getElementById('modal-title').textContent = 'Update Resident Profile.';
        document.getElementById('btn-save-resident').textContent = 'Update Profile';

        document.getElementById('res-first-name').value = resident.firstName || '';
        document.getElementById('res-middle-name').value = resident.middleName || '';
        document.getElementById('res-last-name').value = resident.lastName || '';
        document.getElementById('res-suffix').value = resident.suffix || '';
        document.getElementById('res-birthdate').value = resident.birthdate || '';
        document.getElementById('res-gender').value = resident.gender || 'Male';
        document.getElementById('res-civil-status').value = resident.civilStatus || 'Single';
        document.getElementById('res-occupation').value = resident.occupation || '';

        document.getElementById('res-purok').value = resident.purok || 'Purok 1';
        document.getElementById('res-address').value = resident.address || '';
        document.getElementById('res-phone').value = resident.phone || '';
        document.getElementById('res-emergency').value = resident.emergencyContact || '';

        document.getElementById('res-is-voter').checked = !!resident.isVoter;
        document.getElementById('res-precinct').value = resident.precinct || '';
        document.getElementById('precinct-wrap').style.display = resident.isVoter ? 'block' : 'none';

        document.getElementById('res-is-senior').checked = !!resident.isSenior;
        document.getElementById('res-is-pwd').checked = !!resident.isPwd;
        document.getElementById('res-is-solo-parent').checked = !!resident.isSoloParent;
        document.getElementById('res-is-four-ps').checked = !!resident.isFourPs;
        document.getElementById('res-is-indigent').checked = !!resident.isIndigent;

        if (resident.birthdate) {
          const age = calculateAge(resident.birthdate);
          document.getElementById('res-age').value = `${age} years old`;
        }

        document.getElementById('resident-modal').showModal();
      } catch (e) {
        console.error(e);
        Toast.error('Error fetching resident profile.');
      }
    };

    // Open View Dossier Modal
    window.openDossierModal = async function(id) {
      try {
        const res = await window.barangayDB.get('residents', id);
        if (!res) return;

        activeResidentForAction = res;
        const initials = `${(res.firstName || ' ')[0]}${(res.lastName || ' ')[0]}`.toUpperCase();
        const fullName = formatFullName(res);
        const age = res.age || calculateAge(res.birthdate) || '—';

        const photo = res.photoUrl || res.photo_url;
        const avatarEl = document.getElementById('dossier-avatar');
        if (photo) {
          avatarEl.innerHTML = `<img src="${photo}" alt="Resident Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 30%;">`;
        } else {
          avatarEl.textContent = initials;
        }
        document.getElementById('dossier-name').textContent = fullName;
        document.getElementById('dossier-purok').textContent = `${res.purok || 'Unassigned'} • Community Member`;

        // Render badges
        const badgesContainer = document.getElementById('dossier-badges');
        badgesContainer.innerHTML = '';
        if (res.isVoter) badgesContainer.innerHTML += `<span class="badge-blue">Registered Voter (${res.precinct || 'Assigned'})</span>`;
        if (age >= 60 || res.isSenior) badgesContainer.innerHTML += `<span class="badge-amber">Senior Citizen</span>`;
        if (res.isPwd) badgesContainer.innerHTML += `<span class="badge-purple">Person with Disability</span>`;
        if (res.isSoloParent) badgesContainer.innerHTML += `<span class="badge-rose">Solo Parent</span>`;
        if (res.isFourPs) badgesContainer.innerHTML += `<span class="badge-emerald">4Ps Beneficiary</span>`;
        if (res.isIndigent) badgesContainer.innerHTML += `<span class="badge-neutral">Indigent Profile</span>`;

        document.getElementById('dossier-age-bday').textContent = `${age} years old (${res.birthdate || 'Not specified'})`;
        document.getElementById('dossier-gender-civil').textContent = `${res.gender || '—'} • ${res.civilStatus || 'Single'}`;
        document.getElementById('dossier-address').textContent = `${res.address || 'No street specified'}, ${res.purok || ''}`;
        document.getElementById('dossier-contact').textContent = res.phone || 'No phone recorded';
        document.getElementById('dossier-occupation').textContent = res.occupation || 'None stated';
        document.getElementById('dossier-voter').textContent = res.isVoter ? `Voter (Precinct ${res.precinct || 'Active'})` : 'Non-Voter';
        document.getElementById('dossier-emergency').textContent = res.emergencyContact || 'None listed';

        // Bind dossier buttons
        const issueIdBtn = document.getElementById('dossier-btn-issue-id');
        if (issueIdBtn) {
          issueIdBtn.onclick = () => {
            window.location.href = `resident-id.php?residentId=${res.id}`;
          };
        }

        document.getElementById('dossier-btn-issue-cert').onclick = () => {
          window.location.href = `certificates.php?residentId=${res.id}&name=${encodeURIComponent(fullName)}`;
        };

        document.getElementById('dossier-btn-edit').onclick = () => {
          document.getElementById('dossier-modal').close();
          openEditModal(res.id);
        };

        document.getElementById('dossier-modal').showModal();
      } catch (e) {
        console.error(e);
        Toast.error('Could not open resident dossier.');
      }
    };

    // Open Delete Confirmation Modal
    window.openDeleteModal = async function(id) {
      const res = await window.barangayDB.get('residents', id);
      if (!res) return;

      activeResidentForAction = res;
      document.getElementById('delete-resident-name').textContent = formatFullName(res);
      document.getElementById('delete-modal').showModal();
    };

    // Handle Delete Confirmation
    async function confirmDeleteResident() {
      if (!activeResidentForAction) return;
      const res = activeResidentForAction;

      try {
        await window.barangayDB.delete('residents', res.id);

        // Record Audit Log
        if (window.authService) {
          await window.authService.logAudit('RESIDENT_ARCHIVED', `Archived resident record: ${formatFullName(res)} (ID: ${res.id})`);
        }

        document.getElementById('delete-modal').close();
        Toast.success(`Resident ${formatFullName(res)} archived.`);
        await refreshResidentsList();
      } catch (e) {
        console.error(e);
        Toast.error('Failed to delete resident record.');
      }
    }

    // Export Records to CSV
    function exportToCSV() {
      if (allResidents.length === 0) {
        Toast.info('No resident records to export.');
        return;
      }

      const headers = ['ID', 'Last Name', 'First Name', 'Middle Name', 'Suffix', 'Gender', 'Birthdate', 'Age', 'Civil Status', 'Purok', 'Address', 'Phone', 'Voter Status', 'Precinct', 'Senior', 'PWD', 'Solo Parent', '4Ps', 'Indigent', 'Occupation'];
      
      const rows = allResidents.map(r => [
        r.id,
        `"${(r.lastName || '').replace(/"/g, '""')}"`,
        `"${(r.firstName || '').replace(/"/g, '""')}"`,
        `"${(r.middleName || '').replace(/"/g, '""')}"`,
        `"${(r.suffix || '').replace(/"/g, '""')}"`,
        r.gender || '',
        r.birthdate || '',
        r.age || '',
        r.civilStatus || '',
        `"${(r.purok || '').replace(/"/g, '""')}"`,
        `"${(r.address || '').replace(/"/g, '""')}"`,
        `"${(r.phone || '').replace(/"/g, '""')}"`,
        r.isVoter ? 'Yes' : 'No',
        r.precinct || '',
        r.isSenior || (r.age >= 60) ? 'Yes' : 'No',
        r.isPwd ? 'Yes' : 'No',
        r.isSoloParent ? 'Yes' : 'No',
        r.isFourPs ? 'Yes' : 'No',
        r.isIndigent ? 'Yes' : 'No',
        `"${(r.occupation || '').replace(/"/g, '""')}"`
      ]);

      const csvContent = '\uFEFF' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      const dateStr = new Date().toISOString().split('T')[0];
      link.setAttribute('href', url);
      link.setAttribute('download', `Barangay_Residents_Roster_${dateStr}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      Toast.success('Resident roster CSV downloaded.');
    }

    // Bind All Event Listeners
    function bindEventListeners() {
      document.getElementById('btn-open-add-modal').addEventListener('click', openAddModal);
      document.getElementById('btn-confirm-delete').addEventListener('click', confirmDeleteResident);
      document.getElementById('btn-export-csv').addEventListener('click', exportToCSV);

      // Search and Filter Listeners
      document.getElementById('search-residents').addEventListener('input', applyFiltersAndRender);
      document.getElementById('filter-purok').addEventListener('change', applyFiltersAndRender);
      document.getElementById('filter-classification').addEventListener('change', applyFiltersAndRender);
      document.getElementById('filter-gender').addEventListener('change', applyFiltersAndRender);

      // Birthdate change -> auto calculate age and auto-check senior
      const birthdateInput = document.getElementById('res-birthdate');
      birthdateInput.addEventListener('change', () => {
        const age = calculateAge(birthdateInput.value);
        if (age !== null) {
          document.getElementById('res-age').value = `${age} years old`;
          if (age >= 60) {
            document.getElementById('res-is-senior').checked = true;
          }
        } else {
          document.getElementById('res-age').value = '';
        }
      });

      // Voter checkbox toggle precinct input
      const voterCheck = document.getElementById('res-is-voter');
      voterCheck.addEventListener('change', () => {
        document.getElementById('precinct-wrap').style.display = voterCheck.checked ? 'block' : 'none';
      });

      // Handle Form Submit (Save / Update Resident)
      const form = document.getElementById('resident-form');
      form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const firstName = document.getElementById('res-first-name').value.trim();
        const lastName = document.getElementById('res-last-name').value.trim();
        const birthdate = document.getElementById('res-birthdate').value;

        if (!firstName || !lastName || !birthdate) {
          Toast.error('Please fill in required fields: First Name, Last Name, and Date of Birth.');
          return;
        }

        const editId = document.getElementById('form-resident-id').value;
        const age = calculateAge(birthdate);

        const residentData = {
          firstName,
          middleName: document.getElementById('res-middle-name').value.trim(),
          lastName,
          suffix: document.getElementById('res-suffix').value,
          birthdate,
          age: age,
          gender: document.getElementById('res-gender').value,
          civilStatus: document.getElementById('res-civil-status').value,
          occupation: document.getElementById('res-occupation').value.trim(),
          purok: document.getElementById('res-purok').value,
          address: document.getElementById('res-address').value.trim(),
          phone: document.getElementById('res-phone').value.trim(),
          emergencyContact: document.getElementById('res-emergency').value.trim(),
          isVoter: document.getElementById('res-is-voter').checked,
          precinct: document.getElementById('res-precinct').value.trim(),
          isSenior: document.getElementById('res-is-senior').checked || (age >= 60),
          isPwd: document.getElementById('res-is-pwd').checked,
          isSoloParent: document.getElementById('res-is-solo-parent').checked,
          isFourPs: document.getElementById('res-is-four-ps').checked,
          isIndigent: document.getElementById('res-is-indigent').checked,
          updatedAt: new Date().toISOString()
        };

        try {
          if (editId) {
            residentData.id = parseInt(editId, 10);
            await window.barangayDB.put('residents', residentData);
            if (window.authService) {
              await window.authService.logAudit('RESIDENT_UPDATED', `Updated profile of ${formatFullName(residentData)} (ID: ${editId})`);
            }
            Toast.success(`Resident profile updated.`);
          } else {
            residentData.createdAt = new Date().toISOString();
            const newId = await window.barangayDB.add('residents', residentData);
            if (window.authService) {
              await window.authService.logAudit('RESIDENT_REGISTERED', `Registered new resident: ${formatFullName(residentData)} (ID: ${newId})`);
            }
            Toast.success(`Resident ${formatFullName(residentData)} registered.`);
          }

          document.getElementById('resident-modal').close();
          await refreshResidentsList();
        } catch (err) {
          console.error('Error saving resident:', err);
          Toast.error('Could not save resident record.');
        }
      });
    }
  </script>
</body>
</html>
