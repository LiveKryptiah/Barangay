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
  <title>Household Profiling &bull; Barangay Management System</title>
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
      min-width: 240px;
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

    .view-toggle-wrap {
      display: inline-flex;
      background-color: var(--color-field);
      padding: 3px;
      border-radius: var(--rounded-full);
      gap: 2px;
    }

    .view-toggle-btn {
      height: 32px;
      padding: 0 14px;
      border: none;
      background: transparent;
      border-radius: var(--rounded-full);
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--color-text-muted);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.15s ease;
    }

    .view-toggle-btn.active {
      background-color: var(--color-canvas);
      color: var(--color-ink);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    /* Household Cards Grid */
    .households-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
      gap: var(--spacing-sm);
    }

    .household-card {
      background-color: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: border-color 0.15s ease, transform 0.15s ease;
    }

    .household-card:hover {
      border-color: var(--color-primary);
    }

    .card-header-lockup {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 12px;
    }

    .head-lockup {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 14px;
    }

    .head-avatar-squircle {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: rgba(0, 102, 255, 0.08);
      color: var(--color-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.9375rem;
      flex-shrink: 0;
      border: 1px solid rgba(0, 102, 255, 0.2);
    }

    .head-info-name {
      font-size: 0.9375rem;
      font-weight: 700;
      color: var(--color-ink);
      line-height: 1.2;
    }

    .head-info-subtitle {
      font-size: 0.75rem;
      color: var(--color-text-muted);
      margin-top: 2px;
    }

    .amenities-badges-row {
      display: flex;
      flex-wrap: wrap;
      gap: 4px;
      margin: 10px 0 14px;
    }

    .card-actions-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-top: 1px solid var(--color-hairline-soft);
      padding-top: 12px;
      margin-top: 8px;
    }

    /* Density Progress Bars */
    .purok-density-card {
      background-color: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: 16px;
      margin-bottom: 12px;
    }

    .purok-bar-track {
      width: 100%;
      height: 8px;
      background-color: var(--color-canvas-soft);
      border-radius: var(--rounded-full);
      overflow: hidden;
      margin-top: 8px;
    }

    .purok-bar-fill {
      height: 100%;
      background-color: var(--color-primary);
      border-radius: var(--rounded-full);
      transition: width 0.4s ease;
    }

    /* Modal Form Styles */
    .form-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--spacing-sm);
    }

    .form-grid-3 {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: var(--spacing-sm);
    }

    @media (max-width: 640px) {
      .form-grid-2, .form-grid-3 {
        grid-template-columns: 1fr;
      }
    }

    .member-table-wrap {
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-sm);
      overflow-x: auto;
      margin-top: 8px;
    }

    /* Family Tree Hierarchy Visual Diagram */
    .tree-diagram-container {
      padding: 24px 12px;
      background-color: var(--color-canvas-soft);
      border-radius: var(--rounded-md);
      display: flex;
      flex-direction: column;
      align-items: center;
      overflow-x: auto;
    }

    .tree-node-card {
      background-color: var(--color-canvas);
      border: 1.5px solid var(--color-hairline);
      border-radius: 14px;
      padding: 10px 16px;
      text-align: center;
      min-width: 160px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
      position: relative;
    }

    .tree-node-card.head {
      border-color: var(--color-primary);
      background-color: var(--color-canvas);
    }

    .tree-node-card.spouse {
      border-color: #8b5cf6;
    }

    .tree-node-card.child {
      border-color: #10b981;
    }

    .tree-line-v {
      width: 2px;
      height: 20px;
      background-color: var(--color-hairline);
      margin: 0 auto;
    }

    .tree-line-h {
      height: 2px;
      background-color: var(--color-hairline);
      margin: 0 auto;
    }

    .tree-children-row {
      display: flex;
      justify-content: center;
      gap: 16px;
      position: relative;
      padding-top: 10px;
    }

    /* Printable Template */
    #printable-household {
      display: none;
    }

    @media print {
      body * {
        visibility: hidden;
      }
      #print-modal, #printable-household, #printable-household * {
        visibility: visible;
      }
      #print-modal {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        border: none;
        box-shadow: none;
        background: #fff;
      }
      #printable-household {
        display: block !important;
        padding: 20px;
        color: #000;
        font-family: 'Inter', sans-serif;
      }
      .no-print {
        display: none !important;
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
                <h1 class="typography-heading-2">Household Profiling &amp; Family Tree.</h1>
                <span class="badge-neutral" id="household-count-badge">0 Households</span>
              </div>
              <p class="typography-body-lg">
                Demographic family unit mapping, dwelling structure conditions, utilities access, and disaster hazard vulnerability.
              </p>
            </div>
            <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
              <button class="button-outline" id="btn-export-csv" title="Export household census to CSV" style="height: 38px; padding: 0 16px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="7 10 12 15 17 10"/>
                  <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span>Export CSV</span>
              </button>
              <button class="button-primary" id="btn-open-household-modal" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="12" y1="5" x2="12" y2="19"/>
                  <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>+ New Household Profile</span>
              </button>
            </div>
          </div>
        </section>

        <!-- Demographic Telemetry Ladder -->
        <section>
          <div class="stats-ladder">
            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">TOTAL HOUSEHOLDS</span>
                <span class="badge-blue">Dwellings</span>
              </div>
              <div class="stat-number" id="stat-total-households">0</div>
              <div class="typography-caption" id="stat-sub-households">0 residents mapped</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">AVERAGE FAMILY SIZE</span>
                <span class="badge-neutral">Persons / Unit</span>
              </div>
              <div class="stat-number" id="stat-avg-family-size">0.0</div>
              <div class="typography-caption" id="stat-sub-family-size">Based on active family rosters</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">VULNERABLE UNITS</span>
                <span class="badge-amber">4Ps &bull; Low Income</span>
              </div>
              <div class="stat-number" id="stat-total-vulnerable">0</div>
              <div class="typography-caption" id="stat-sub-vulnerable">Indigent / makeshift housing</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">HAZARD-PRONE UNITS</span>
                <span class="badge-rose" style="color: #ef4444; background: rgba(239, 68, 68, 0.1);">Risk Zones</span>
              </div>
              <div class="stat-number" id="stat-total-hazard" style="color: #ef4444;">0</div>
              <div class="typography-caption" id="stat-sub-hazard">Flood or landslide exposed</div>
            </div>
          </div>
        </section>

        <!-- Filter & Search Toolbar -->
        <div class="filter-toolbar">
          <div class="filter-group">
            <div class="search-input-wrap">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <input type="text" id="search-households" class="text-input" placeholder="Search by HH #, Family Head, Street...">
            </div>

            <select id="filter-purok" class="filter-select">
              <option value="">All Puroks / Zones</option>
              <option value="Purok 1">Purok 1</option>
              <option value="Purok 2">Purok 2</option>
              <option value="Purok 3">Purok 3</option>
              <option value="Purok 4">Purok 4</option>
              <option value="Purok 5">Purok 5</option>
              <option value="Purok 6">Purok 6</option>
              <option value="Purok 7">Purok 7</option>
            </select>

            <select id="filter-tenure" class="filter-select">
              <option value="">All Housing Tenures</option>
              <option value="Owned House & Lot">Owned House &amp; Lot</option>
              <option value="Owned House / Rented Lot">Owned House / Rented Lot</option>
              <option value="Tenant / Renter">Tenant / Renter</option>
              <option value="Informal Settler">Informal Settler</option>
              <option value="Living with Relatives / Shared">Living with Relatives / Shared</option>
            </select>

            <select id="filter-hazard" class="filter-select">
              <option value="">All Hazard Ratings</option>
              <option value="Low Risk / Safe Zone">Low Risk / Safe Zone</option>
              <option value="Flood-Prone Area">Flood-Prone Area</option>
              <option value="Landslide Hazard Zone">Landslide Hazard Zone</option>
              <option value="Fire-Prone Cluster">Fire-Prone Cluster</option>
            </select>

            <button type="button" class="button-pill-soft" id="btn-clear-filters" style="display: none; height: 38px; padding: 0 12px; font-size: 0.75rem;">
              Clear Filters
            </button>
          </div>

          <div class="view-toggle-wrap">
            <button type="button" class="view-toggle-btn active" id="btn-view-cards" onclick="switchView('cards')">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="7" height="7" x="3" y="3" rx="1"/>
                <rect width="7" height="7" x="14" y="3" rx="1"/>
                <rect width="7" height="7" x="14" y="14" rx="1"/>
                <rect width="7" height="7" x="3" y="14" rx="1"/>
              </svg>
              <span>Household Roster</span>
            </button>
            <button type="button" class="view-toggle-btn" id="btn-view-density" onclick="switchView('density')">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10"/>
                <line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6" y1="20" x2="6" y2="14"/>
              </svg>
              <span>Purok Density</span>
            </button>
          </div>
        </div>

        <!-- MAIN VIEW 1: HOUSEHOLD CARDS GRID -->
        <div id="view-cards-container">
          <div class="households-grid" id="households-grid-mount"></div>

          <!-- Empty State -->
          <div id="empty-state" class="empty-state-card" style="display: none; padding: 48px 16px; text-align: center; background-color: var(--color-canvas); border: 1px dashed var(--color-hairline); border-radius: var(--rounded-md); margin-top: var(--spacing-sm);">
            <div class="nav-brand-icon" style="width: 52px; height: 52px; border-radius: 50%; font-size: 1.5rem; margin: 0 auto var(--spacing-sm); background-color: var(--color-canvas-soft); color: var(--color-text-muted);">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
            </div>
            <h4 class="typography-heading-4">No Households Profiled.</h4>
            <p class="typography-body-sm" style="color: var(--color-text-muted); max-width: 440px; margin: 4px auto var(--spacing-md);">
              Begin registering community households by linking registered residents as family heads, adding members, and mapping living conditions.
            </p>
            <button class="button-primary" onclick="openHouseholdModal();" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
              + Create First Household
            </button>
          </div>
        </div>

        <!-- MAIN VIEW 2: PUROK DENSITY BREAKDOWN -->
        <div id="view-density-container" style="display: none;">
          <div style="background-color: var(--color-canvas); border: 1px solid var(--color-hairline-soft); border-radius: var(--rounded-md); padding: var(--spacing-md); margin-bottom: var(--spacing-md);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 8px; margin-bottom: var(--spacing-md);">
              <div>
                <h3 class="typography-heading-4" style="margin-bottom: 4px;">Purok Household Density &amp; Concentration.</h3>
                <p class="typography-caption" style="color: var(--color-text-muted);">
                  Geographic distribution of profiled households and residents across community territorial zones.
                </p>
              </div>
              <a href="geo-profiling.php" class="button-primary" style="height: 32px; padding: 0 14px; font-size: 0.75rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                <span>Open GIS Spatial Heatmap &rarr;</span>
              </a>
            </div>

            <div id="purok-density-list"></div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- MODAL 1: REGISTER / EDIT HOUSEHOLD PROFILE -->
  <dialog id="household-modal" class="modal-dialog" style="max-width: 780px; width: 95%; max-height: 90vh; overflow-y: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-sm);">
      <div>
        <h3 class="typography-heading-4" id="modal-household-title">Create Household Profile.</h3>
        <p class="typography-caption">Organize residents into a physical dwelling unit with living condition metrics.</p>
      </div>
      <button type="button" class="button-pill-soft" onclick="document.getElementById('household-modal').close();" style="height: 30px; padding: 0 10px;">
        Cancel
      </button>
    </div>

    <form id="household-form" novalidate>
      <!-- Step 1: Location & Dwelling Identification -->
      <div style="margin-bottom: var(--spacing-md);">
        <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem; text-transform: uppercase;">
          1. Location &amp; Dwelling Identification
        </span>

        <div class="form-grid-3 mt-xs">
          <div class="form-group">
            <label class="form-label" for="hh-control-no">Household Reference # <span style="color: var(--color-primary);">*</span></label>
            <input type="text" id="hh-control-no" class="text-input" style="font-family: monospace; font-weight: 700;" readonly required>
          </div>

          <div class="form-group">
            <label class="form-label" for="hh-purok">Purok / Zone <span style="color: var(--color-primary);">*</span></label>
            <select id="hh-purok" class="filter-select" style="width: 100%; border-radius: var(--rounded-sm); height: 42px;" required>
              <option value="Purok 1">Purok 1</option>
              <option value="Purok 2">Purok 2</option>
              <option value="Purok 3">Purok 3</option>
              <option value="Purok 4">Purok 4</option>
              <option value="Purok 5">Purok 5</option>
              <option value="Purok 6">Purok 6</option>
              <option value="Purok 7">Purok 7</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="hh-address">House / Street Address <span style="color: var(--color-primary);">*</span></label>
            <input type="text" id="hh-address" class="text-input" placeholder="e.g. 142 Rizal St." required>
          </div>
        </div>

        <div class="form-grid-2 mt-xs">
          <div class="form-group">
            <label class="form-label" for="hh-structure">Dwelling Structure Type</label>
            <select id="hh-structure" class="filter-select" style="width: 100%; border-radius: var(--rounded-sm); height: 42px;">
              <option value="Concrete / Permanent">Concrete / Permanent Materials</option>
              <option value="Semi-Permanent / Wood">Semi-Permanent / Wood &amp; Galvanized</option>
              <option value="Makeshift / Light">Makeshift / Light / Salvaged Materials</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="hh-tenure">Housing Tenure Status</label>
            <select id="hh-tenure" class="filter-select" style="width: 100%; border-radius: var(--rounded-sm); height: 42px;">
              <option value="Owned House & Lot">Owned House &amp; Lot</option>
              <option value="Owned House / Rented Lot">Owned House / Rented Lot</option>
              <option value="Tenant / Renter">Tenant / Renter</option>
              <option value="Informal Settler">Informal Settler</option>
              <option value="Living with Relatives / Shared">Living with Relatives / Shared with Consent</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Step 2: Head of Household -->
      <div style="margin-bottom: var(--spacing-md); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem; text-transform: uppercase;">
          2. Head of Family (Puno ng Pamilya)
        </span>

        <div style="display: flex; gap: var(--spacing-xs); margin-top: 4px;">
          <select id="select-head-resident" class="text-input" style="height: 42px; padding: 0 12px; flex: 1;" required>
            <option value="">-- Select Registered Resident as Family Head --</option>
          </select>
          <a href="residents.php" target="_blank" class="button-outline" style="height: 42px; padding: 0 14px; font-size: 0.75rem; flex-shrink: 0;" title="Register new resident">
            + New Resident
          </a>
        </div>

        <div id="head-preview-card" style="display: none; margin-top: var(--spacing-xs); padding: 10px 14px; background-color: var(--color-canvas-soft); border-radius: var(--rounded-sm); font-size: 0.8125rem;">
          <div style="font-weight: 700;" id="head-preview-name">-</div>
          <div class="typography-caption" id="head-preview-meta">-</div>
        </div>
      </div>

      <!-- Step 3: Family Members & Relationship Tree Mapping -->
      <div style="margin-bottom: var(--spacing-md); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem; text-transform: uppercase;">
            3. Family Members &amp; Household Tree
          </span>
          <span class="typography-caption" id="member-counter-chip">1 Member Total</span>
        </div>

        <div style="display: flex; gap: var(--spacing-xs); margin-top: var(--spacing-xs); flex-wrap: wrap;">
          <select id="select-add-member" class="text-input" style="height: 40px; padding: 0 12px; flex: 2; min-width: 220px;">
            <option value="">-- Choose Resident to Add to Family --</option>
          </select>

          <select id="select-member-relation" class="filter-select" style="height: 40px; flex: 1; min-width: 180px; border-radius: var(--rounded-sm);">
            <option value="Spouse (Asawa)">Spouse (Asawa)</option>
            <option value="Son / Daughter (Anak)">Son / Daughter (Anak)</option>
            <option value="Parent / Father / Mother (Magulang)">Parent (Magulang)</option>
            <option value="Sibling (Kapatid)">Sibling (Kapatid)</option>
            <option value="Grandchild (Apo)">Grandchild (Apo)</option>
            <option value="Grandparent (Lolo / Lola)">Grandparent (Lolo / Lola)</option>
            <option value="In-Law (Biyanan / Manugang)">In-Law (Biyanan / Manugang)</option>
            <option value="Extended Relative (Kamag-anak)">Extended Relative (Kamag-anak)</option>
            <option value="Tenant / Boarder (Nangungupahan)">Tenant / Boarder (Nangungupahan)</option>
            <option value="Domestic Helper (Kasambahay)">Domestic Helper (Kasambahay)</option>
          </select>

          <button type="button" class="button-outline" id="btn-add-member-row" style="height: 40px; padding: 0 16px; font-size: 0.8125rem;">
            + Add Member
          </button>
        </div>

        <!-- Live Member Table -->
        <div class="member-table-wrap">
          <table class="data-table" style="font-size: 0.8125rem;">
            <thead>
              <tr>
                <th>Resident Member</th>
                <th>Relationship to Head</th>
                <th>Age / Status</th>
                <th>Classifications</th>
                <th style="text-align: right;">Action</th>
              </tr>
            </thead>
            <tbody id="household-members-tbody"></tbody>
          </table>
        </div>
      </div>

      <!-- Step 4: Utilities, Socio-Economic & Living Conditions -->
      <div style="margin-bottom: var(--spacing-md); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-sm);">
        <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem; text-transform: uppercase;">
          4. Utilities &amp; Living Conditions
        </span>

        <div class="form-grid-3 mt-xs">
          <div class="form-group">
            <label class="form-label" for="hh-water">Main Water Source</label>
            <select id="hh-water" class="filter-select" style="width: 100%; border-radius: var(--rounded-sm); height: 42px;">
              <option value="Level 3 Piped Connection">Level 3 (Individual Piped / Metered)</option>
              <option value="Level 2 Communal Faucet">Level 2 (Communal Faucet / Standpost)</option>
              <option value="Level 1 Deep / Shallow Well">Level 1 (Point Source / Well / Spring)</option>
              <option value="Bottled / Refilling Station">Bottled / Refilling Water</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="hh-toilet">Sanitation / Toilet Facility</label>
            <select id="hh-toilet" class="filter-select" style="width: 100%; border-radius: var(--rounded-sm); height: 42px;">
              <option value="Water-Sealed Flush (Septic Tank)">Water-Sealed Flush with Septic Tank</option>
              <option value="Shared Communal Toilet">Shared / Communal Toilet</option>
              <option value="Open Pit / Antipolo">Open Pit / Antipolo Type</option>
              <option value="None / No Facility">None / No Sanitary Toilet</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="hh-power">Lighting &amp; Power Source</label>
            <select id="hh-power" class="filter-select" style="width: 100%; border-radius: var(--rounded-sm); height: 42px;">
              <option value="Electric Grid (Metered)">Electric Grid (Metered Connection)</option>
              <option value="Sub-Metered / Shared">Sub-Metered / Shared Connection</option>
              <option value="Solar Power">Solar Power System</option>
              <option value="Kerosene / Battery / None">Kerosene / Battery / Generator</option>
            </select>
          </div>
        </div>

        <div class="form-grid-2 mt-xs">
          <div class="form-group">
            <label class="form-label" for="hh-income">Monthly Household Income</label>
            <select id="hh-income" class="filter-select" style="width: 100%; border-radius: var(--rounded-sm); height: 42px;">
              <option value="Low / Indigent (< ₱10,000)">Low Income / Indigent (&lt; ₱10,000)</option>
              <option value="Lower Middle (₱10,000 - ₱25,000)">Lower Middle (₱10,000 &ndash; ₱25,000)</option>
              <option value="Middle Class (₱25,000 - ₱50,000)">Middle Class (₱25,000 &ndash; ₱50,000)</option>
              <option value="Upper Middle (> ₱50,000)">Upper Middle / High (&gt; ₱50,000)</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="hh-hazard">Disaster Hazard Risk Exposure</label>
            <select id="hh-hazard" class="filter-select" style="width: 100%; border-radius: var(--rounded-sm); height: 42px;">
              <option value="Low Risk / Safe Zone">Low Risk / Safe Zone</option>
              <option value="Flood-Prone Area">Flood-Prone Area (Baha)</option>
              <option value="Landslide Hazard Zone">Landslide Hazard Zone (Guho)</option>
              <option value="Fire-Prone Cluster">Fire-Prone Informal Cluster</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); border-top: 1px solid var(--color-hairline-soft); padding-top: var(--spacing-md);">
        <button type="button" class="button-outline" onclick="document.getElementById('household-modal').close();" style="height: 42px; padding: 0 20px;">
          Cancel
        </button>
        <button type="submit" class="button-primary" id="btn-save-household" style="height: 42px; padding: 0 24px;">
          Save Household Profile
        </button>
      </div>
    </form>
  </dialog>

  <!-- MODAL 2: HOUSEHOLD DOSSIER & FAMILY TREE VISUALIZATION -->
  <dialog id="dossier-modal" class="modal-dialog" style="max-width: 840px; width: 95%; max-height: 90vh; overflow-y: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-sm);">
      <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
        <span class="badge-blue" id="dossier-hh-no">HH-2026-00001</span>
        <span class="typography-heading-4" id="dossier-head-name">Family Dossier</span>
      </div>
      <div style="display: flex; gap: var(--spacing-xs);">
        <button type="button" class="button-primary" id="btn-dossier-print" style="height: 34px; padding: 0 16px; font-size: 0.75rem;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
            <rect width="12" height="8" x="6" y="14"/>
          </svg>
          <span>Print Census Sheet</span>
        </button>
        <button type="button" class="button-pill-soft" onclick="document.getElementById('dossier-modal').close();" style="height: 34px; padding: 0 12px; font-size: 0.75rem;">
          Close
        </button>
      </div>
    </div>

    <!-- Family Tree Visual Hierarchy -->
    <div style="margin-bottom: var(--spacing-md);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xs);">
        <span class="typography-label" style="color: var(--color-primary);">FAMILY HIERARCHY TREE</span>
        <span class="typography-caption" id="dossier-meta-count">0 Members</span>
      </div>

      <div class="tree-diagram-container" id="tree-diagram-mount"></div>
    </div>

    <!-- Dwelling Scorecard Grid -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--spacing-xs); margin-bottom: var(--spacing-md);">
      <div style="padding: 10px; background-color: var(--color-canvas-soft); border-radius: var(--rounded-sm);">
        <div class="typography-caption" style="color: var(--color-text-muted);">STRUCTURE &amp; TENURE</div>
        <div style="font-weight: 700; font-size: 0.8125rem; margin-top: 2px;" id="dossier-score-structure">-</div>
        <div style="font-size: 0.6875rem; color: var(--color-text-muted);" id="dossier-score-tenure">-</div>
      </div>

      <div style="padding: 10px; background-color: var(--color-canvas-soft); border-radius: var(--rounded-sm);">
        <div class="typography-caption" style="color: var(--color-text-muted);">WATER SUPPLY</div>
        <div style="font-weight: 700; font-size: 0.8125rem; margin-top: 2px;" id="dossier-score-water">-</div>
        <div style="font-size: 0.6875rem; color: #10b981;">&#10003; Safe Supply</div>
      </div>

      <div style="padding: 10px; background-color: var(--color-canvas-soft); border-radius: var(--rounded-sm);">
        <div class="typography-caption" style="color: var(--color-text-muted);">SANITATION FACILITY</div>
        <div style="font-weight: 700; font-size: 0.8125rem; margin-top: 2px;" id="dossier-score-toilet">-</div>
        <div style="font-size: 0.6875rem; color: var(--color-text-muted);" id="dossier-score-power">-</div>
      </div>

      <div style="padding: 10px; background-color: var(--color-canvas-soft); border-radius: var(--rounded-sm);">
        <div class="typography-caption" style="color: var(--color-text-muted);">DISASTER HAZARD RATING</div>
        <div style="font-weight: 700; font-size: 0.8125rem; margin-top: 2px;" id="dossier-score-hazard">-</div>
        <div style="font-size: 0.6875rem; color: var(--color-text-muted);" id="dossier-score-income">-</div>
      </div>
    </div>

    <!-- Complete Member Roster Table -->
    <div style="margin-bottom: var(--spacing-sm);">
      <span class="typography-label" style="color: var(--color-text-muted); margin-bottom: 6px; display: block;">
        REGISTERED RESIDENTIAL ROSTER
      </span>
      <div class="member-table-wrap">
        <table class="data-table" style="font-size: 0.8125rem;">
          <thead>
            <tr>
              <th>Name</th>
              <th>Relation</th>
              <th>Age</th>
              <th>Civil Status</th>
              <th>Voter</th>
              <th>Special Assistance</th>
            </tr>
          </thead>
          <tbody id="dossier-members-tbody"></tbody>
        </table>
      </div>
    </div>
  </dialog>

  <!-- MODAL 3: PRINTABLE CERTIFICATE OF FAMILY COMPOSITION -->
  <dialog id="print-modal" class="modal-dialog" style="max-width: 860px; width: 95%; max-height: 90vh; overflow-y: auto;">
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-sm);">
      <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
        <span class="badge-blue" id="print-pill-hh">HH-2026-00001</span>
        <span class="typography-caption">Official Family Composition Document</span>
      </div>
      <div style="display: flex; gap: var(--spacing-xs);">
        <button type="button" class="button-outline" onclick="document.getElementById('print-modal').close();" style="height: 36px; padding: 0 14px; font-size: 0.8125rem;">
          Close
        </button>
        <button type="button" class="button-primary" onclick="window.print();" style="height: 36px; padding: 0 18px; font-size: 0.8125rem;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
            <rect width="12" height="8" x="6" y="14"/>
          </svg>
          <span>Print Census Sheet (Ctrl+P)</span>
        </button>
      </div>
    </div>

    <!-- Official Printable Paper Sheet -->
    <div id="printable-household">
      <div class="cert-header" style="text-align: center; margin-bottom: 24px; border-bottom: 2px solid #000; padding-bottom: 12px;">
        <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #555;">Republic of the Philippines</div>
        <div style="font-size: 0.95rem; font-weight: 700; text-transform: uppercase;" id="print-jurisdiction">Province of Metropolitan Manila &bull; City of San Isidro</div>
        <div style="font-size: 1.25rem; font-weight: 800; color: #111; letter-spacing: 0.02em; margin: 4px 0;" id="print-brgy-name">BARANGAY SAN ISIDRO</div>
        <div style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #0066ff;">OFFICE OF THE PUNONG BARANGAY</div>
      </div>

      <div style="text-align: center; margin-bottom: 24px;">
        <div style="font-size: 1.15rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">CERTIFICATE OF FAMILY COMPOSITION</div>
        <div style="font-size: 0.85rem; color: #555; margin-top: 2px;">BARANGAY RESIDENTIAL HOUSEHOLD CENSUS RECORD</div>
      </div>

      <div style="display: flex; justify-content: space-between; font-size: 0.875rem; margin-bottom: 16px; border: 1px solid #ddd; padding: 12px; border-radius: 6px; background-color: #fafafa;">
        <div>
          <div>Household Control No: <strong id="print-ctrl-no" style="font-family: monospace;">HH-2026-00001</strong></div>
          <div>Head of Family: <strong id="print-head-name">JUAN DELA CRUZ</strong></div>
        </div>
        <div style="text-align: right;">
          <div>Location: <strong id="print-full-address">Purok 4, Barangay San Isidro</strong></div>
          <div>Date Profiled: <span id="print-date">September 4, 2026</span></div>
        </div>
      </div>

      <p style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 16px;">
        <strong>TO WHOM IT MAY CONCERN:</strong><br>
        THIS IS TO CERTIFY that according to the official demographic census and residential registry of this Barangay, the following individuals constitute the bonafide household and family composition residing at the address stated above:
      </p>

      <!-- Member Census Table -->
      <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem; margin-bottom: 24px;">
        <thead>
          <tr style="background-color: #f0f0f0; border: 1px solid #333;">
            <th style="padding: 8px; border: 1px solid #333; text-align: left;">#</th>
            <th style="padding: 8px; border: 1px solid #333; text-align: left;">Full Legal Name</th>
            <th style="padding: 8px; border: 1px solid #333; text-align: left;">Relationship to Head</th>
            <th style="padding: 8px; border: 1px solid #333; text-align: center;">Age</th>
            <th style="padding: 8px; border: 1px solid #333; text-align: left;">Civil Status</th>
            <th style="padding: 8px; border: 1px solid #333; text-align: left;">Occupation / Remarks</th>
          </tr>
        </thead>
        <tbody id="print-table-tbody"></tbody>
      </table>

      <p style="font-size: 0.8125rem; line-height: 1.5; color: #444; margin-bottom: 24px;">
        This certification is issued for official local government reference, educational assistance, DSWD social welfare assessment, health services, and whatever legal purpose it may serve.
      </p>

      <!-- Dual Signatory Blocks -->
      <div style="display: flex; justify-content: space-between; margin-top: 40px; padding: 0 30px;">
        <div style="text-align: center; width: 220px;">
          <div style="height: 50px;"></div>
          <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: 700; font-size: 0.875rem;" id="print-secretary-name">
            BARANGAY SECRETARY
          </div>
          <div style="font-size: 0.75rem; color: #555;">Attested by: Barangay Secretary</div>
        </div>

        <div style="text-align: center; width: 240px;">
          <div style="height: 50px;"></div>
          <div style="border-top: 1px solid #111; padding-top: 4px; font-weight: 800; font-size: 0.9375rem;" id="print-punong-name">
            HON. PUNONG BARANGAY
          </div>
          <div style="font-size: 0.75rem; color: #333; font-weight: 600;">Punong Barangay</div>
        </div>
      </div>
    </div>
  </dialog>

  <!-- MODAL 4: DELETE CONFIRMATION -->
  <dialog id="delete-modal" class="modal-dialog" style="max-width: 420px; width: 90%; text-align: center;">
    <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-sm);">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
      </svg>
    </div>
    <h3 class="typography-heading-4">Delete Household Record?</h3>
    <p class="typography-body-sm" style="color: var(--color-text-muted); margin: 6px 0 var(--spacing-md);">
      Are you sure you want to remove household <strong id="delete-hh-no">HH-2026-00001</strong>? Resident profiles will remain intact in the resident directory.
    </p>
    <div style="display: flex; justify-content: center; gap: var(--spacing-xs);">
      <button type="button" class="button-outline" onclick="document.getElementById('delete-modal').close();" style="height: 38px; padding: 0 16px;">
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
    let allHouseholds = [];
    let allResidentsMap = new Map();
    let currentAuthUser = null;
    let editingHouseholdId = null;
    let activeHouseholdForAction = null;
    let currentHouseholdMembers = []; // Working list for modal

    document.addEventListener('DOMContentLoaded', async () => {
      // 1. Guard route: require authenticated official session
      const auth = await authService.requireAuth('login.php');
      if (!auth) return;
      currentAuthUser = auth.user;

      // 2. Render App Shell Sidebar & Topbar
      await AppSidebar.render('households');

      // 3. Load Settings for Letterhead & Signatories
      await loadBarangayMeta();

      // 4. Load Residents into memory & dropdowns
      await loadResidents();

      // 5. Query Households from IndexedDB
      await refreshHouseholdsList();

      // 6. Bind UI Event Listeners
      bindEventListeners();
    });

    // Load Identity & Officials Signatory
    async function loadBarangayMeta() {
      try {
        const idSetting = await window.barangayDB.get('settings', 'identity');
        if (idSetting && idSetting.value) {
          const v = idSetting.value;
          if (v.barangayName) {
            document.getElementById('print-brgy-name').textContent = v.barangayName.toUpperCase();
          }
          if (v.province && v.municipalityCity) {
            document.getElementById('print-jurisdiction').textContent = `${v.province.toUpperCase()} • ${v.municipalityCity.toUpperCase()}`;
          }
        }

        const officials = await window.barangayDB.getAll('officials');
        const activeSignatory = officials.find(o => o.isSignatory && o.status === 'active') ||
                                officials.find(o => o.position === 'Punong Barangay' && o.status === 'active');
        if (activeSignatory) {
          document.getElementById('print-punong-name').textContent = activeSignatory.fullName.toUpperCase();
        }

        const secretary = officials.find(o => o.position === 'Barangay Secretary' && o.status === 'active');
        if (secretary) {
          document.getElementById('print-secretary-name').textContent = secretary.fullName.toUpperCase();
        }
      } catch (err) {
        console.warn('Could not load barangay meta:', err);
      }
    }

    // Load Residents into memory
    async function loadResidents() {
      try {
        const residents = await window.barangayDB.getAll('residents');
        allResidentsMap.clear();
        residents.sort((a, b) => (a.lastName || '').localeCompare(b.lastName || ''));

        const selectHead = document.getElementById('select-head-resident');
        const selectMember = document.getElementById('select-add-member');

        selectHead.innerHTML = '<option value="">-- Select Registered Resident as Family Head --</option>';
        selectMember.innerHTML = '<option value="">-- Choose Resident to Add to Family --</option>';

        residents.forEach(r => {
          allResidentsMap.set(r.id, r);
          const fullName = [r.lastName, r.firstName, r.middleName, r.suffix].filter(Boolean).join(' ');
          const label = `${fullName} (${r.purok || 'Unassigned'}, Age: ${r.age || '—'})`;

          const opt1 = document.createElement('option');
          opt1.value = r.id;
          opt1.textContent = label;
          selectHead.appendChild(opt1);

          const opt2 = document.createElement('option');
          opt2.value = r.id;
          opt2.textContent = label;
          selectMember.appendChild(opt2);
        });
      } catch (err) {
        console.error('Error loading residents:', err);
      }
    }

    // Refresh Households list from DB
    async function refreshHouseholdsList() {
      try {
        allHouseholds = await window.barangayDB.getAll('households');
        allHouseholds.sort((a, b) => new Date(b.createdAt || 0) - new Date(a.createdAt || 0));

        updateTelemetry(allHouseholds);
        renderFilteredHouseholds();
        renderPurokDensity();
      } catch (err) {
        console.error('Failed to load households:', err);
        Toast.error('Could not load households database.');
      }
    }

    // Update Telemetry Counters
    function updateTelemetry(households) {
      const total = households.length;
      let totalResidents = 0;
      let vulnerableCount = 0;
      let hazardCount = 0;

      households.forEach(h => {
        const count = Array.isArray(h.members) ? h.members.length : 1;
        totalResidents += count;

        const isLowIncome = (h.monthlyIncome || '').includes('Low') || (h.monthlyIncome || '').includes('Indigent');
        const isMakeshift = (h.structureType || '').includes('Makeshift');
        const has4Ps = (h.members || []).some(m => m.is4Ps || m.isIndigent);
        if (isLowIncome || isMakeshift || has4Ps) {
          vulnerableCount++;
        }

        if (h.disasterHazard && h.disasterHazard !== 'Low Risk / Safe Zone') {
          hazardCount++;
        }
      });

      const avgSize = total > 0 ? (totalResidents / total).toFixed(1) : '0.0';

      document.getElementById('household-count-badge').textContent = `${total} ${total === 1 ? 'Household' : 'Households'}`;
      document.getElementById('stat-total-households').textContent = total;
      document.getElementById('stat-sub-households').textContent = `${totalResidents} residents mapped`;

      document.getElementById('stat-avg-family-size').textContent = avgSize;
      document.getElementById('stat-sub-family-size').textContent = `${totalResidents} total individuals recorded`;

      document.getElementById('stat-total-vulnerable').textContent = vulnerableCount;
      document.getElementById('stat-sub-vulnerable').textContent = `${total > 0 ? Math.round((vulnerableCount / total) * 100) : 0}% of all households`;

      document.getElementById('stat-total-hazard').textContent = hazardCount;
      document.getElementById('stat-sub-hazard').textContent = `${total > 0 ? Math.round((hazardCount / total) * 100) : 0}% in exposure zones`;
    }

    // Render Filtered Cards
    function renderFilteredHouseholds() {
      const search = (document.getElementById('search-households').value || '').toLowerCase().trim();
      const purok = document.getElementById('filter-purok').value;
      const tenure = document.getElementById('filter-tenure').value;
      const hazard = document.getElementById('filter-hazard').value;

      const hasActive = search || purok || tenure || hazard;
      document.getElementById('btn-clear-filters').style.display = hasActive ? 'inline-flex' : 'none';

      const filtered = allHouseholds.filter(h => {
        if (search) {
          const num = (h.householdNo || '').toLowerCase();
          const head = (h.headName || '').toLowerCase();
          const addr = (h.address || '').toLowerCase();
          if (!num.includes(search) && !head.includes(search) && !addr.includes(search)) return false;
        }
        if (purok && h.purok !== purok) return false;
        if (tenure && h.tenureStatus !== tenure) return false;
        if (hazard && h.disasterHazard !== hazard) return false;
        return true;
      });

      const container = document.getElementById('households-grid-mount');
      const emptyState = document.getElementById('empty-state');

      if (filtered.length === 0) {
        container.innerHTML = '';
        emptyState.style.display = 'block';
        return;
      }

      emptyState.style.display = 'none';
      container.innerHTML = filtered.map(h => {
        const members = h.members || [];
        const memberCount = members.length;
        const seniorCount = members.filter(m => m.isSenior).length;
        const minorCount = members.filter(m => m.isMinor).length;
        const indigentCount = members.filter(m => m.isIndigent || m.is4Ps).length;

        const initials = (h.headName || 'HH').split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();

        const isHazard = h.disasterHazard && h.disasterHazard !== 'Low Risk / Safe Zone';
        const hazardBadge = isHazard
          ? `<span class="badge-rose" style="font-size: 0.625rem;">${h.disasterHazard}</span>`
          : `<span class="badge-emerald" style="font-size: 0.625rem;">Low Risk</span>`;

        return `
          <div class="household-card">
            <div>
              <div class="card-header-lockup">
                <span class="badge-blue" style="font-family: monospace; font-size: 0.75rem;">${h.householdNo}</span>
                <div style="display: flex; gap: 4px;">
                  <span class="badge-neutral" style="font-size: 0.6875rem;">${h.purok}</span>
                  <span class="badge-neutral" style="font-size: 0.6875rem;">${h.tenureStatus || 'Resident'}</span>
                </div>
              </div>

              <div class="head-lockup">
                <div class="head-avatar-squircle">${initials}</div>
                <div>
                  <div class="head-info-name">${h.headName}</div>
                  <div class="head-info-subtitle">Head of Family &bull; ${h.address || h.purok}</div>
                </div>
              </div>

              <!-- Composition Chips -->
              <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 10px;">
                <span class="badge-neutral" style="font-weight: 700;">${memberCount} ${memberCount === 1 ? 'Member' : 'Members'}</span>
                ${seniorCount > 0 ? `<span class="badge-amber">${seniorCount} Senior</span>` : ''}
                ${minorCount > 0 ? `<span class="badge-neutral">${minorCount} Minor</span>` : ''}
                ${indigentCount > 0 ? `<span class="badge-emerald">${indigentCount} 4Ps / Indigent</span>` : ''}
              </div>

              <!-- Living Conditions Tag Row -->
              <div class="amenities-badges-row">
                <span class="badge-neutral" style="font-size: 0.625rem;">${h.structureType || 'Structure'}</span>
                <span class="badge-neutral" style="font-size: 0.625rem;">${h.waterSource || 'Water'}</span>
                <span class="badge-neutral" style="font-size: 0.625rem;">${h.toiletFacility || 'Sanitation'}</span>
                ${hazardBadge}
              </div>
            </div>

            <!-- Card Actions -->
            <div class="card-actions-row">
              <button class="button-outline" onclick="openDossierModal(${h.id})" style="height: 32px; padding: 0 12px; font-size: 0.75rem;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                  <circle cx="9" cy="7" r="4"/>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>Tree &amp; Dossier</span>
              </button>

              <div style="display: flex; align-items: center; gap: 4px;">
                <button class="table-action-btn" onclick="openPrintModal(${h.id})" title="Print Certificate of Family Composition">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect width="12" height="8" x="6" y="14"/>
                  </svg>
                </button>
                <button class="table-action-btn" onclick="openEditModal(${h.id})" title="Edit Household Profile">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </button>
                <button class="table-action-btn danger" onclick="openDeleteModal(${h.id})" title="Delete Household">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        `;
      }).join('');
    }

    // Render Purok Density Breakdown
    function renderPurokDensity() {
      const listMount = document.getElementById('purok-density-list');
      const puroks = ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5', 'Purok 6', 'Purok 7'];
      const totalHh = allHouseholds.length || 1;

      listMount.innerHTML = puroks.map(p => {
        const matched = allHouseholds.filter(h => h.purok === p);
        const count = matched.length;
        const residentsInPurok = matched.reduce((sum, h) => sum + (h.members ? h.members.length : 1), 0);
        const percent = Math.round((count / totalHh) * 100);

        return `
          <div class="purok-density-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <div>
                <strong style="font-size: 0.9375rem; color: var(--color-ink);">${p}</strong>
                <span class="typography-caption" style="margin-left: 8px;">${residentsInPurok} residents mapped</span>
              </div>
              <div>
                <strong style="color: var(--color-primary); font-size: 0.9375rem;">${count}</strong>
                <span class="typography-caption">households (${percent}%)</span>
              </div>
            </div>

            <div class="purok-bar-track">
              <div class="purok-bar-fill" style="width: ${percent}%;"></div>
            </div>
          </div>
        `;
      }).join('');
    }

    // Switch between Cards and Density
    window.switchView = function(mode) {
      const isCards = mode === 'cards';
      document.getElementById('view-cards-container').style.display = isCards ? 'block' : 'none';
      document.getElementById('view-density-container').style.display = isCards ? 'none' : 'block';

      document.getElementById('btn-view-cards').classList.toggle('active', isCards);
      document.getElementById('btn-view-density').classList.toggle('active', !isCards);
    };

    // Open Household Modal (Create)
    window.openHouseholdModal = function() {
      editingHouseholdId = null;
      document.getElementById('household-form').reset();
      document.getElementById('modal-household-title').textContent = 'Create Household Profile.';
      document.getElementById('btn-save-household').textContent = 'Save Household Profile';

      // Generate sequence: HH-2026-XXXXX
      const year = new Date().getFullYear();
      const sequence = String(allHouseholds.length + 1).padStart(5, '0');
      document.getElementById('hh-control-no').value = `HH-${year}-${sequence}`;

      currentHouseholdMembers = [];
      renderModalMembersTable();
      document.getElementById('head-preview-card').style.display = 'none';

      document.getElementById('household-modal').showModal();
    };

    // Open Edit Modal
    window.openEditModal = async function(id) {
      const h = allHouseholds.find(item => item.id === id);
      if (!h) return;

      editingHouseholdId = h.id;
      document.getElementById('household-form').reset();
      document.getElementById('modal-household-title').textContent = `Edit Household: ${h.householdNo}`;
      document.getElementById('btn-save-household').textContent = 'Update Household Profile';

      document.getElementById('hh-control-no').value = h.householdNo;
      document.getElementById('hh-purok').value = h.purok || 'Purok 1';
      document.getElementById('hh-address').value = h.address || '';
      document.getElementById('hh-structure').value = h.structureType || 'Concrete / Permanent';
      document.getElementById('hh-tenure').value = h.tenureStatus || 'Owned House & Lot';

      document.getElementById('hh-water').value = h.waterSource || 'Level 3 Piped Connection';
      document.getElementById('hh-toilet').value = h.toiletFacility || 'Water-Sealed Flush (Septic Tank)';
      document.getElementById('hh-power').value = h.powerSource || 'Electric Grid (Metered)';
      document.getElementById('hh-income').value = h.monthlyIncome || 'Lower Middle (₱10,000 - ₱25,000)';
      document.getElementById('hh-hazard').value = h.disasterHazard || 'Low Risk / Safe Zone';

      // Set Head
      document.getElementById('select-head-resident').value = h.headResidentId || '';
      handleHeadSelection(h.headResidentId);

      // Clone members
      currentHouseholdMembers = JSON.parse(JSON.stringify(h.members || []));
      renderModalMembersTable();

      document.getElementById('household-modal').showModal();
    };

    // Handle Head Selection
    function handleHeadSelection(residentId) {
      const res = allResidentsMap.get(parseInt(residentId, 10));
      const card = document.getElementById('head-preview-card');

      if (!res) {
        card.style.display = 'none';
        return;
      }

      card.style.display = 'block';
      const fullName = [res.firstName, res.middleName, res.lastName, res.suffix].filter(Boolean).join(' ');
      document.getElementById('head-preview-name').textContent = fullName;
      document.getElementById('head-preview-meta').textContent = `Age: ${res.age || '—'} • ${res.civilStatus || 'Single'} • Phone: ${res.contactNumber || 'N/A'}`;

      // Update or insert head into currentHouseholdMembers
      const existingHeadIdx = currentHouseholdMembers.findIndex(m => m.isHead);
      const headObj = {
        residentId: res.id,
        fullName: fullName,
        age: res.age || 0,
        relationship: 'Head of Family / Puno ng Pamilya',
        civilStatus: res.civilStatus || 'Single',
        occupation: res.occupation || 'N/A',
        isHead: true,
        isSenior: res.age >= 60,
        isMinor: res.age < 18,
        isIndigent: res.isIndigent || false,
        is4Ps: res.isFourPs || false,
        isPwd: res.isPwd || false
      };

      if (existingHeadIdx >= 0) {
        currentHouseholdMembers[existingHeadIdx] = headObj;
      } else {
        currentHouseholdMembers.unshift(headObj);
      }

      renderModalMembersTable();
    }

    // Add Member Row in Modal
    function addMemberRow() {
      const selectRes = document.getElementById('select-add-member');
      const selectRel = document.getElementById('select-member-relation');

      const resId = parseInt(selectRes.value, 10);
      if (!resId) {
        Toast.error('Please pick a registered resident to add.');
        return;
      }

      // Check if already added
      if (currentHouseholdMembers.some(m => m.residentId === resId)) {
        Toast.warning('Resident is already listed in this household.');
        return;
      }

      const res = allResidentsMap.get(resId);
      if (!res) return;

      const fullName = [res.firstName, res.middleName, res.lastName, res.suffix].filter(Boolean).join(' ');
      const relation = selectRel.value;

      currentHouseholdMembers.push({
        residentId: res.id,
        fullName: fullName,
        age: res.age || 0,
        relationship: relation,
        civilStatus: res.civilStatus || 'Single',
        occupation: res.occupation || 'N/A',
        isHead: false,
        isSenior: res.age >= 60,
        isMinor: res.age < 18,
        isIndigent: res.isIndigent || false,
        is4Ps: res.isFourPs || false,
        isPwd: res.isPwd || false
      });

      selectRes.value = '';
      renderModalMembersTable();
      Toast.success(`Added ${fullName} (${relation})`);
    }

    // Remove Member Row
    window.removeMemberRow = function(index) {
      if (currentHouseholdMembers[index].isHead) {
        Toast.warning('Cannot remove the Head of Family directly. Choose another resident as head.');
        return;
      }
      currentHouseholdMembers.splice(index, 1);
      renderModalMembersTable();
    };

    // Render Modal Members Table
    function renderModalMembersTable() {
      const tbody = document.getElementById('household-members-tbody');
      document.getElementById('member-counter-chip').textContent = `${currentHouseholdMembers.length} ${currentHouseholdMembers.length === 1 ? 'Member' : 'Members'} Total`;

      if (currentHouseholdMembers.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="5" style="text-align: center; color: var(--color-text-muted); padding: 16px;">
              No members added yet. Select a Family Head above.
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = currentHouseholdMembers.map((m, idx) => {
        return `
          <tr>
            <td>
              <div style="font-weight: 700; color: var(--color-ink);">${m.fullName}</div>
              <div class="typography-caption">${m.occupation}</div>
            </td>
            <td>
              <span class="${m.isHead ? 'badge-blue' : 'badge-neutral'}">${m.relationship}</span>
            </td>
            <td>${m.age} yrs &bull; ${m.civilStatus}</td>
            <td>
              <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                ${m.isSenior ? '<span class="badge-amber">Senior</span>' : ''}
                ${m.isMinor ? '<span class="badge-neutral">Minor</span>' : ''}
                ${m.is4Ps ? '<span class="badge-emerald">4Ps</span>' : ''}
                ${m.isPwd ? '<span class="badge-purple">PWD</span>' : ''}
              </div>
            </td>
            <td style="text-align: right;">
              ${m.isHead ? '<span class="typography-caption" style="color: var(--color-primary);">Head</span>' : `
                <button type="button" class="table-action-btn danger" onclick="removeMemberRow(${idx})" title="Remove member">
                  Remove
                </button>
              `}
            </td>
          </tr>
        `;
      }).join('');
    }

    // Open Family Tree & Dossier Modal
    window.openDossierModal = function(id) {
      const h = allHouseholds.find(item => item.id === id);
      if (!h) return;

      activeHouseholdForAction = h;

      document.getElementById('dossier-hh-no').textContent = h.householdNo;
      document.getElementById('dossier-head-name').textContent = `${h.headName}'s Household`;

      const members = h.members || [];
      document.getElementById('dossier-meta-count').textContent = `${members.length} Members Profiled`;

      // Scorecard
      document.getElementById('dossier-score-structure').textContent = h.structureType || 'Concrete';
      document.getElementById('dossier-score-tenure').textContent = h.tenureStatus || 'Owned';
      document.getElementById('dossier-score-water').textContent = h.waterSource || 'Level 3';
      document.getElementById('dossier-score-toilet').textContent = h.toiletFacility || 'Water-Sealed';
      document.getElementById('dossier-score-power').textContent = h.powerSource || 'Grid';
      document.getElementById('dossier-score-hazard').textContent = h.disasterHazard || 'Low Risk';
      document.getElementById('dossier-score-income').textContent = h.monthlyIncome || 'Middle';

      // Visual Family Tree Hierarchy Layout
      const treeMount = document.getElementById('tree-diagram-mount');
      const head = members.find(m => m.isHead) || members[0];
      const spouse = members.find(m => (m.relationship || '').includes('Spouse'));
      const children = members.filter(m => (m.relationship || '').includes('Son') || (m.relationship || '').includes('Daughter') || (m.relationship || '').includes('Anak'));
      const others = members.filter(m => m !== head && m !== spouse && !children.includes(m));

      let treeHTML = `
        <!-- Tier 1 & Tier 2: Head & Spouse -->
        <div style="display: flex; align-items: center; justify-content: center; gap: 20px; flex-wrap: wrap;">
          <!-- Head of Family -->
          <div class="tree-node-card head">
            <span class="badge-blue" style="font-size: 0.5625rem; margin-bottom: 4px;">PUNO NG PAMILYA</span>
            <div style="font-weight: 800; font-size: 0.875rem; color: var(--color-ink);">${head ? head.fullName : h.headName}</div>
            <div class="typography-caption">${head ? `${head.age} yrs • ${head.civilStatus}` : 'Head'}</div>
          </div>

          ${spouse ? `
            <div style="width: 20px; border-top: 2px dashed #8b5cf6;"></div>
            <!-- Spouse -->
            <div class="tree-node-card spouse">
              <span class="badge-purple" style="font-size: 0.5625rem; margin-bottom: 4px;">SPOUSE / ASAWA</span>
              <div style="font-weight: 800; font-size: 0.875rem; color: var(--color-ink);">${spouse.fullName}</div>
              <div class="typography-caption">${spouse.age} yrs • ${spouse.civilStatus}</div>
            </div>
          ` : ''}
        </div>
      `;

      if (children.length > 0) {
        treeHTML += `
          <div class="tree-line-v"></div>
          <div class="typography-label" style="font-size: 0.625rem; color: var(--color-text-muted); margin: 2px 0;">CHILDREN / MGA ANAK</div>
          <div class="tree-children-row">
            ${children.map(c => `
              <div class="tree-node-card child">
                <span class="badge-emerald" style="font-size: 0.5625rem; margin-bottom: 2px;">CHILD</span>
                <div style="font-weight: 700; font-size: 0.8125rem; color: var(--color-ink);">${c.fullName}</div>
                <div class="typography-caption">${c.age} yrs • ${c.isMinor ? 'Minor' : 'Adult'}</div>
              </div>
            `).join('')}
          </div>
        `;
      }

      if (others.length > 0) {
        treeHTML += `
          <div class="tree-line-v"></div>
          <div class="typography-label" style="font-size: 0.625rem; color: var(--color-text-muted); margin: 2px 0;">EXTENDED RELATIVES &amp; DEPENDENTS</div>
          <div class="tree-children-row" style="flex-wrap: wrap;">
            ${others.map(o => `
              <div class="tree-node-card">
                <span class="badge-neutral" style="font-size: 0.5625rem; margin-bottom: 2px;">${o.relationship}</span>
                <div style="font-weight: 700; font-size: 0.8125rem; color: var(--color-ink);">${o.fullName}</div>
                <div class="typography-caption">${o.age} yrs • ${o.occupation || 'Resident'}</div>
              </div>
            `).join('')}
          </div>
        `;
      }

      treeMount.innerHTML = treeHTML;

      // Render Roster Table in Dossier
      const tbody = document.getElementById('dossier-members-tbody');
      tbody.innerHTML = members.map(m => {
        const res = allResidentsMap.get(m.residentId);
        const voter = res && res.isVoter ? '<span style="color: #10b981;">&#10003; Registered</span>' : '<span style="color: var(--color-text-muted);">Non-Voter</span>';

        return `
          <tr>
            <td style="font-weight: 600;">${m.fullName}</td>
            <td><span class="${m.isHead ? 'badge-blue' : 'badge-neutral'}">${m.relationship}</span></td>
            <td>${m.age} yrs</td>
            <td>${m.civilStatus}</td>
            <td>${voter}</td>
            <td>
              <div style="display: flex; gap: 4px;">
                ${m.isSenior ? '<span class="badge-amber">Senior</span>' : ''}
                ${m.is4Ps ? '<span class="badge-emerald">4Ps</span>' : ''}
                ${m.isPwd ? '<span class="badge-purple">PWD</span>' : ''}
              </div>
            </td>
          </tr>
        `;
      }).join('');

      document.getElementById('dossier-modal').showModal();
    };

    // Open Print Modal
    window.openPrintModal = function(id) {
      const h = allHouseholds.find(item => item.id === id) || activeHouseholdForAction;
      if (!h) return;

      activeHouseholdForAction = h;

      document.getElementById('print-pill-hh').textContent = h.householdNo;
      document.getElementById('print-ctrl-no').textContent = h.householdNo;
      document.getElementById('print-head-name').textContent = h.headName.toUpperCase();
      document.getElementById('print-full-address').textContent = `${h.address || ''}, ${h.purok || 'Barangay San Isidro'}`;

      const now = new Date();
      document.getElementById('print-date').textContent = now.toLocaleDateString([], { month: 'long', day: 'numeric', year: 'numeric' });

      const members = h.members || [];
      const tbody = document.getElementById('print-table-tbody');
      tbody.innerHTML = members.map((m, idx) => {
        return `
          <tr style="border-bottom: 1px solid #ddd;">
            <td style="padding: 6px 8px; border: 1px solid #ddd;">${idx + 1}</td>
            <td style="padding: 6px 8px; border: 1px solid #ddd; font-weight: 700;">${m.fullName.toUpperCase()}</td>
            <td style="padding: 6px 8px; border: 1px solid #ddd;">${m.relationship}</td>
            <td style="padding: 6px 8px; border: 1px solid #ddd; text-align: center;">${m.age}</td>
            <td style="padding: 6px 8px; border: 1px solid #ddd;">${m.civilStatus}</td>
            <td style="padding: 6px 8px; border: 1px solid #ddd;">${m.occupation || (m.isMinor ? 'Student' : 'None')}</td>
          </tr>
        `;
      }).join('');

      document.getElementById('print-modal').showModal();
    };

    // Open Delete Modal
    window.openDeleteModal = function(id) {
      const h = allHouseholds.find(item => item.id === id);
      if (!h) return;

      activeHouseholdForAction = h;
      document.getElementById('delete-hh-no').textContent = `${h.householdNo} (${h.headName})`;
      document.getElementById('delete-modal').showModal();
    };

    // Confirm Delete
    async function confirmDelete() {
      if (!activeHouseholdForAction) return;
      const h = activeHouseholdForAction;

      try {
        await window.barangayDB.delete('households', h.id);

        if (window.authService) {
          await window.authService.logAudit(
            'HOUSEHOLD_DELETED',
            `Deleted household ${h.householdNo} (Head: ${h.headName}) with ${h.members ? h.members.length : 1} members`
          );
        }

        document.getElementById('delete-modal').close();
        Toast.success(`Household ${h.householdNo} removed.`);
        await refreshHouseholdsList();
      } catch (err) {
        console.error('Error deleting household:', err);
        Toast.error('Failed to delete household.');
      }
    }

    // Export CSV
    function exportHouseholdsCSV() {
      if (allHouseholds.length === 0) {
        Toast.warning('No households available to export.');
        return;
      }

      const headers = [
        'Household No',
        'Head of Family',
        'Purok',
        'Address',
        'Members Count',
        'Structure Type',
        'Housing Tenure',
        'Water Source',
        'Sanitation Facility',
        'Power Source',
        'Monthly Income',
        'Disaster Hazard',
        'Profiled Date'
      ];

      const rows = allHouseholds.map(h => [
        `"${h.householdNo}"`,
        `"${h.headName}"`,
        `"${h.purok}"`,
        `"${(h.address || '').replace(/"/g, '""')}"`,
        (h.members || []).length,
        `"${h.structureType || ''}"`,
        `"${h.tenureStatus || ''}"`,
        `"${h.waterSource || ''}"`,
        `"${h.toiletFacility || ''}"`,
        `"${h.powerSource || ''}"`,
        `"${h.monthlyIncome || ''}"`,
        `"${h.disasterHazard || ''}"`,
        `"${h.createdAt || ''}"`
      ]);

      const csvContent = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `Barangay_Households_Census_${new Date().toISOString().slice(0, 10)}.csv`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);

      Toast.success('Household CSV census exported successfully!');
    }

    // Bind Event Listeners
    function bindEventListeners() {
      document.getElementById('btn-open-household-modal').addEventListener('click', openHouseholdModal);
      document.getElementById('btn-export-csv').addEventListener('click', exportHouseholdsCSV);
      document.getElementById('btn-confirm-delete').addEventListener('click', confirmDelete);

      // Dossier print shortcut
      document.getElementById('btn-dossier-print').addEventListener('click', () => {
        document.getElementById('dossier-modal').close();
        if (activeHouseholdForAction) {
          openPrintModal(activeHouseholdForAction.id);
        }
      });

      // Filter events
      document.getElementById('search-households').addEventListener('input', renderFilteredHouseholds);
      document.getElementById('filter-purok').addEventListener('change', renderFilteredHouseholds);
      document.getElementById('filter-tenure').addEventListener('change', renderFilteredHouseholds);
      document.getElementById('filter-hazard').addEventListener('change', renderFilteredHouseholds);

      document.getElementById('btn-clear-filters').addEventListener('click', () => {
        document.getElementById('search-households').value = '';
        document.getElementById('filter-purok').value = '';
        document.getElementById('filter-tenure').value = '';
        document.getElementById('filter-hazard').value = '';
        renderFilteredHouseholds();
      });

      // Head selection change
      document.getElementById('select-head-resident').addEventListener('change', (e) => {
        handleHeadSelection(e.target.value);
      });

      // Add member row click
      document.getElementById('btn-add-member-row').addEventListener('click', addMemberRow);

      // Handle Household Form Submit
      document.getElementById('household-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const headIdVal = document.getElementById('select-head-resident').value;
        if (!headIdVal) {
          Toast.error('Please choose a Head of Family for this household.');
          return;
        }

        const headResident = allResidentsMap.get(parseInt(headIdVal, 10));
        if (!headResident) {
          Toast.error('Selected head resident could not be found.');
          return;
        }

        const headFullName = [headResident.firstName, headResident.middleName, headResident.lastName, headResident.suffix].filter(Boolean).join(' ');

        const controlNo = document.getElementById('hh-control-no').value.trim();
        const purok = document.getElementById('hh-purok').value;
        const address = document.getElementById('hh-address').value.trim();
        const structure = document.getElementById('hh-structure').value;
        const tenure = document.getElementById('hh-tenure').value;

        const water = document.getElementById('hh-water').value;
        const toilet = document.getElementById('hh-toilet').value;
        const power = document.getElementById('hh-power').value;
        const income = document.getElementById('hh-income').value;
        const hazard = document.getElementById('hh-hazard').value;

        if (!controlNo || !address) {
          Toast.error('Please fill in all required household fields.');
          return;
        }

        // Ensure head is present in members array
        if (!currentHouseholdMembers.some(m => m.isHead)) {
          handleHeadSelection(headResident.id);
        }

        const householdData = {
          householdNo: controlNo,
          headResidentId: headResident.id,
          headName: headFullName,
          purok: purok,
          address: address,
          structureType: structure,
          tenureStatus: tenure,
          waterSource: water,
          toiletFacility: toilet,
          powerSource: power,
          monthlyIncome: income,
          disasterHazard: hazard,
          members: currentHouseholdMembers,
          memberCount: currentHouseholdMembers.length,
          updatedAt: new Date().toISOString()
        };

        try {
          if (editingHouseholdId) {
            householdData.id = editingHouseholdId;
            const existing = allHouseholds.find(h => h.id === editingHouseholdId);
            householdData.createdAt = existing ? existing.createdAt : new Date().toISOString();

            await window.barangayDB.put('households', householdData);

            if (window.authService) {
              await window.authService.logAudit(
                'HOUSEHOLD_UPDATED',
                `Updated household profile ${controlNo} (Head: ${headFullName}) with ${currentHouseholdMembers.length} members`
              );
            }

            Toast.success(`Household ${controlNo} updated successfully.`);
          } else {
            householdData.createdAt = new Date().toISOString();
            const newId = await window.barangayDB.add('households', householdData);

            if (window.authService) {
              await window.authService.logAudit(
                'HOUSEHOLD_CREATED',
                `Profiled new household ${controlNo} (Head: ${headFullName}) in ${purok}`
              );
            }

            Toast.success(`Household ${controlNo} profiled successfully.`);
          }

          document.getElementById('household-modal').close();
          await refreshHouseholdsList();
        } catch (err) {
          console.error('Failed to save household:', err);
          Toast.error('Could not save household record.');
        }
      });
    }
  </script>
</body>
</html>
