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
  <title>Families &amp; Social Welfare &bull; Barangay Management System</title>
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
      padding: 8px 12px;
    }

    .filter-group {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: var(--spacing-xs);
    }

    .search-input-wrap {
      position: relative;
      min-width: 250px;
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
      border: none;
      background: transparent;
      padding: 6px 12px;
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

    /* Families Card Grid */
    .families-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
      gap: var(--spacing-sm);
    }

    .family-card {
      background-color: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: 16px;
      transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
    }

    .family-card:hover {
      border-color: var(--color-primary-light);
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
      transform: translateY(-2px);
    }

    .family-card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 12px;
      gap: 8px;
    }

    .family-code-badge {
      font-family: monospace;
      font-weight: 700;
      font-size: 0.75rem;
      padding: 2px 8px;
      background-color: var(--color-canvas-soft);
      border: 1px solid var(--color-hairline);
      border-radius: var(--rounded-full);
      color: var(--color-ink);
    }

    .family-title {
      font-size: 1rem;
      font-weight: 700;
      color: var(--color-ink);
      margin-bottom: 2px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .family-head-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin: 10px 0;
      padding: 8px 10px;
      background-color: var(--color-canvas-soft);
      border-radius: var(--rounded-sm);
    }

    .family-head-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--color-field);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.8125rem;
      color: var(--color-primary);
      flex-shrink: 0;
      overflow: hidden;
      border: 1px solid var(--color-hairline);
    }

    .family-head-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .family-meta-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      margin: 12px 0;
      padding-top: 10px;
      border-top: 1px dashed var(--color-hairline-soft);
    }

    .family-meta-item {
      display: flex;
      flex-direction: column;
    }

    .family-meta-label {
      font-size: 0.6875rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: var(--color-text-muted);
      font-weight: 600;
    }

    .family-meta-value {
      font-size: 0.8125rem;
      color: var(--color-ink);
      font-weight: 600;
      margin-top: 2px;
    }

    .card-actions-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-top: 1px solid var(--color-hairline-soft);
      padding-top: 12px;
      margin-top: 8px;
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

    /* Tab Switcher */
    .tab-nav {
      display: flex;
      gap: 4px;
      border-bottom: 1px solid var(--color-hairline);
      margin-bottom: 16px;
    }

    .tab-btn {
      background: transparent;
      border: none;
      border-bottom: 2px solid transparent;
      padding: 8px 14px;
      font-size: 0.8125rem;
      font-weight: 600;
      color: var(--color-text-muted);
      cursor: pointer;
      transition: all 0.15s ease;
    }

    .tab-btn:hover {
      color: var(--color-ink);
    }

    .tab-btn.active {
      color: var(--color-primary);
      border-bottom-color: var(--color-primary);
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

    .tree-children-row {
      display: flex;
      justify-content: center;
      gap: 14px;
      position: relative;
      padding-top: 10px;
      flex-wrap: wrap;
    }

    /* Print Template */
    #printable-composition {
      display: none;
    }

    @media print {
      body * {
        visibility: hidden;
      }
      #print-modal, #printable-composition, #printable-composition * {
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
      #printable-composition {
        display: block !important;
        padding: 24px;
        color: #000;
        font-family: 'Times New Roman', Times, serif;
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
                <h1 class="typography-heading-2">Families &amp; Social Welfare.</h1>
                <span class="badge-neutral" id="family-count-badge">0 Families</span>
              </div>
              <p class="typography-body-lg">
                Kinship and socio-economic profiling, 4Ps beneficiary management, disaster relief tracking, and Certificate of Family Composition.
              </p>
            </div>
            <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
              <button class="button-outline" id="btn-export-csv" title="Export families list to CSV" style="height: 38px; padding: 0 16px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="7 10 12 15 17 10"/>
                  <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span>Export CSV</span>
              </button>
              <button class="button-primary" id="btn-open-family-modal" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="12" y1="5" x2="12" y2="19"/>
                  <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>+ Register Family Profile</span>
              </button>
            </div>
          </div>
        </section>

        <!-- Demographic Telemetry Ladder -->
        <section>
          <div class="stats-ladder">
            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">TOTAL FAMILIES</span>
                <span class="badge-blue">Pamilya</span>
              </div>
              <div class="stat-number" id="stat-total-families">0</div>
              <div class="typography-caption" id="stat-sub-families">Avg family size: 0.0</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">INDIGENT / SUBSISTENCE</span>
                <span class="badge-amber">Priority</span>
              </div>
              <div class="stat-number" id="stat-indigent-families">0</div>
              <div class="typography-caption" id="stat-sub-indigent">Below poverty threshold</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">4PS BENEFICIARIES</span>
                <span class="badge-emerald">DSWD</span>
              </div>
              <div class="stat-number" id="stat-4ps-families">0</div>
              <div class="typography-caption" id="stat-sub-4ps">Enlisted DSWD households</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">RELIEF &amp; AYUDA DISBURSED</span>
                <span class="badge-purple">Welfare</span>
              </div>
              <div class="stat-number" id="stat-total-grants">0</div>
              <div class="typography-caption" id="stat-sub-grants">₱0.00 total assistance value</div>
            </div>
          </div>
        </section>

        <!-- Filter & Search Toolbar -->
        <div class="filter-toolbar">
          <div class="filter-group">
            <div class="search-input-wrap">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <input type="text" id="search-filter" class="form-input" placeholder="Search by family name, head, or code..." autocomplete="off">
            </div>

            <select id="purok-filter" class="filter-select">
              <option value="">All Puroks</option>
              <option value="Purok 1">Purok 1</option>
              <option value="Purok 2">Purok 2</option>
              <option value="Purok 3">Purok 3</option>
              <option value="Purok 4">Purok 4</option>
              <option value="Purok 5">Purok 5</option>
              <option value="Purok 6">Purok 6</option>
              <option value="Purok 7">Purok 7</option>
            </select>

            <select id="poverty-filter" class="filter-select">
              <option value="">All Poverty Statuses</option>
              <option value="Indigent / Below Poverty Threshold">Indigent / Below Poverty</option>
              <option value="Low Income / Subsistence">Low Income / Subsistence</option>
              <option value="Lower Middle Class">Lower Middle Class</option>
              <option value="Middle Class">Middle Class</option>
              <option value="Above Average">Above Average</option>
            </select>

            <select id="fourps-filter" class="filter-select">
              <option value="">All Welfare Types</option>
              <option value="1">4Ps Beneficiary</option>
              <option value="0">Non-4Ps</option>
            </select>

            <select id="type-filter" class="filter-select">
              <option value="">All Family Types</option>
              <option value="Nuclear">Nuclear</option>
              <option value="Extended">Extended</option>
              <option value="Solo Parent">Solo Parent</option>
              <option value="Childless Couple">Childless Couple</option>
              <option value="Blended">Blended</option>
              <option value="Single Person">Single Person</option>
            </select>
          </div>

          <div class="view-toggle-wrap">
            <button type="button" class="view-toggle-btn active" id="btn-view-cards" onclick="switchView('cards')">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect width="7" height="7" x="3" y="3" rx="1"/>
                <rect width="7" height="7" x="14" y="3" rx="1"/>
                <rect width="7" height="7" x="14" y="14" rx="1"/>
                <rect width="7" height="7" x="3" y="14" rx="1"/>
              </svg>
              <span>Cards</span>
            </button>
            <button type="button" class="view-toggle-btn" id="btn-view-table" onclick="switchView('table')">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="8" y1="6" x2="21" y2="6"/>
                <line x1="8" y1="12" x2="21" y2="12"/>
                <line x1="8" y1="18" x2="21" y2="18"/>
                <line x1="3" y1="6" x2="3.01" y2="6"/>
                <line x1="3" y1="12" x2="3.01" y2="12"/>
                <line x1="3" y1="18" x2="3.01" y2="18"/>
              </svg>
              <span>Table</span>
            </button>
          </div>
        </div>

        <!-- MAIN VIEW 1: FAMILY CARDS GRID -->
        <div id="view-cards-container">
          <div class="families-grid" id="families-grid-mount"></div>

          <!-- Empty State -->
          <div id="empty-state" class="empty-state-card" style="display: none; padding: 48px 16px; text-align: center; background-color: var(--color-canvas); border: 1px dashed var(--color-hairline); border-radius: var(--rounded-md); margin-top: var(--spacing-sm);">
            <div class="nav-brand-icon" style="width: 52px; height: 52px; border-radius: 50%; font-size: 1.5rem; margin: 0 auto var(--spacing-sm); background-color: var(--color-canvas-soft); color: var(--color-text-muted);">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="7" r="3"/>
                <path d="M14 17a5 5 0 0 0-10 0v2h10v-2z"/>
                <circle cx="17" cy="10" r="2.5"/>
                <path d="M17 14c1.8 0 3.5 1 4 2.5v1.5h-5"/>
              </svg>
            </div>
            <h4 class="typography-heading-4">No Family Records Found.</h4>
            <p class="typography-body-sm" style="color: var(--color-text-muted); max-width: 440px; margin: 4px auto var(--spacing-md);">
              Register community families by setting a Family Head, adding kin members, and linking them to their physical dwelling.
            </p>
            <button class="button-primary" onclick="openFamilyModal();" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
              + Register First Family
            </button>
          </div>
        </div>

        <!-- MAIN VIEW 2: FAMILIES TABLE VIEW -->
        <div id="view-table-container" style="display: none;">
          <div style="background-color: var(--color-canvas); border: 1px solid var(--color-hairline-soft); border-radius: var(--rounded-md); overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.8125rem;">
              <thead>
                <tr style="border-bottom: 1px solid var(--color-hairline); background-color: var(--color-canvas-soft); text-align: left;">
                  <th style="padding: 10px 14px;">Family Code</th>
                  <th style="padding: 10px 14px;">Family Name &amp; Head</th>
                  <th style="padding: 10px 14px;">Dwelling / Purok</th>
                  <th style="padding: 10px 14px;">Type</th>
                  <th style="padding: 10px 14px;">Members</th>
                  <th style="padding: 10px 14px;">Monthly Income</th>
                  <th style="padding: 10px 14px;">Poverty Status</th>
                  <th style="padding: 10px 14px;">4Ps / Ayuda</th>
                  <th style="padding: 10px 14px; text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody id="families-table-tbody"></tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- ===================================================== -->
  <!-- MODAL 1: REGISTER / EDIT FAMILY PROFILE               -->
  <!-- ===================================================== -->
  <dialog id="family-modal" class="modal-dialog" style="max-width: 720px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-hairline); padding-bottom: 10px;">
      <div>
        <h3 class="typography-heading-3" id="modal-family-title">Register Family Profile.</h3>
        <p class="typography-caption" style="color: var(--color-text-muted);">Socio-economic profiling and kinship unit mapping.</p>
      </div>
      <button type="button" class="button-icon-soft" onclick="document.getElementById('family-modal').close();" aria-label="Close modal">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form id="family-form" style="max-height: 75vh; overflow-y: auto; padding-right: 4px;">
      <input type="hidden" id="family-edit-id" value="">

      <!-- Section: Identification & Head -->
      <div style="background-color: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-md); margin-bottom: var(--spacing-sm);">
        <div class="typography-label" style="margin-bottom: 8px; color: var(--color-primary); font-weight: 700;">1. FAMILY IDENTIFICATION &amp; HEAD OF FAMILY</div>
        
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label" for="fam-control-no">Family Control Number</label>
            <input type="text" id="fam-control-no" class="form-input" readonly style="background-color: var(--color-field); font-family: monospace; font-weight: 700;">
          </div>
          <div class="form-group">
            <label class="form-label" for="fam-name">Family Name / Clan Title *</label>
            <input type="text" id="fam-name" class="form-input" required placeholder="e.g. Santos Family">
          </div>
        </div>

        <div class="form-group" style="margin-top: 8px;">
          <label class="form-label" for="select-head-resident">Designate Head of Family (Puno ng Pamilya) *</label>
          <select id="select-head-resident" class="form-select" required onchange="handleHeadSelection(this.value)">
            <option value="">-- Choose Registered Resident as Family Head --</option>
          </select>
        </div>

        <div id="head-preview-card" style="display: none; margin-top: 8px; padding: 10px; background: var(--color-canvas); border: 1px solid var(--color-hairline); border-radius: var(--rounded-sm);">
          <div style="font-weight: 700; color: var(--color-ink);" id="head-preview-name">—</div>
          <div class="typography-caption" id="head-preview-meta" style="color: var(--color-text-muted);">—</div>
        </div>
      </div>

      <!-- Section: Dwelling & Living Details -->
      <div style="background-color: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-md); margin-bottom: var(--spacing-sm);">
        <div class="typography-label" style="margin-bottom: 8px; color: var(--color-primary); font-weight: 700;">2. DWELLING &amp; TERRITORIAL LOCATION</div>
        
        <div class="form-grid-3">
          <div class="form-group">
            <label class="form-label" for="select-household">Dwelling / Household Link</label>
            <select id="select-household" class="form-select">
              <option value="">-- Shared Structure / Select Household --</option>
            </select>
            <span class="typography-caption" style="color: var(--color-text-muted);">Leave empty if unhoused/temporary</span>
          </div>

          <div class="form-group">
            <label class="form-label" for="fam-purok">Purok / Zone *</label>
            <select id="fam-purok" class="form-select" required>
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
            <label class="form-label" for="fam-type">Family Kinship Type</label>
            <select id="fam-type" class="form-select">
              <option value="Nuclear">Nuclear (Parents + Kids)</option>
              <option value="Extended">Extended (Multi-Generation)</option>
              <option value="Solo Parent">Solo Parent</option>
              <option value="Childless Couple">Childless Couple</option>
              <option value="Blended">Blended / Reconstituted</option>
              <option value="Single Person">Single Person Unit</option>
            </select>
          </div>
        </div>

        <div class="form-grid-2" style="margin-top: 8px;">
          <div class="form-group">
            <label class="form-label" for="fam-tenure">Housing Tenure</label>
            <select id="fam-tenure" class="form-select">
              <option value="Owner">Owner (House &amp; Lot)</option>
              <option value="Tenant / Renter">Tenant / Renter</option>
              <option value="Sharer with Relative">Sharer with Relative / Co-Living</option>
              <option value="Informal Settler">Informal Settler (Dweller)</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="fam-main-income">Primary Source of Livelihood</label>
            <select id="fam-main-income" class="form-select">
              <option value="Employment / Wages">Employment / Wages</option>
              <option value="Informal / Daily Labor">Informal / Daily Labor</option>
              <option value="Small Business / Sari-Sari">Small Business / Sari-Sari Store</option>
              <option value="Farming / Agriculture">Farming / Agriculture</option>
              <option value="Fishing / Aquaculture">Fishing / Aquaculture</option>
              <option value="OFW / Foreign Remittances">OFW / Foreign Remittances</option>
              <option value="Pension / Social Grants">Pension / Social Grants</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Section: Socio-Economic & Social Welfare -->
      <div style="background-color: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-md); margin-bottom: var(--spacing-sm);">
        <div class="typography-label" style="margin-bottom: 8px; color: var(--color-primary); font-weight: 700;">3. SOCIO-ECONOMIC &amp; WELFARE CLASSIFICATION</div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label" for="fam-monthly-income">Monthly Family Income (PHP) *</label>
            <input type="number" id="fam-monthly-income" class="form-input" min="0" step="500" value="0" required oninput="updatePovertyPreview(this.value)">
          </div>

          <div class="form-group">
            <label class="form-label">Classification Status</label>
            <div id="poverty-badge-preview" style="padding: 8px 12px; background: var(--color-canvas); border: 1px solid var(--color-hairline); border-radius: var(--rounded-sm); font-weight: 700; font-size: 0.8125rem;">
              Indigent / Below Poverty Threshold
            </div>
          </div>
        </div>

        <div class="form-grid-2" style="margin-top: 10px;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" id="fam-is-4ps" style="width: 18px; height: 18px;" onchange="toggleFourPsInput(this.checked)">
            <label for="fam-is-4ps" style="font-weight: 600; font-size: 0.8125rem; cursor: pointer;">
              4Ps Beneficiary (Pantawid Pamilyang Pilipino Program)
            </label>
          </div>

          <div style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" id="fam-is-ayuda" style="width: 18px; height: 18px;">
            <label for="fam-is-ayuda" style="font-weight: 600; font-size: 0.8125rem; cursor: pointer;">
              Ayuda Priority / Vulnerable Household
            </label>
          </div>
        </div>

        <div class="form-group" id="group-4ps-no" style="display: none; margin-top: 8px;">
          <label class="form-label" for="fam-4ps-number">DSWD 4Ps Household ID Number</label>
          <input type="text" id="fam-4ps-number" class="form-input" placeholder="e.g. 4PS-042100-0012">
        </div>
      </div>

      <!-- Section: Initial Members Roster -->
      <div style="background-color: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-md); margin-bottom: var(--spacing-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
          <div class="typography-label" style="color: var(--color-primary); font-weight: 700;">4. ENLIST FAMILY MEMBERS</div>
          <span class="badge-neutral" id="modal-member-counter">1 Member</span>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
          <select id="select-add-member" class="form-select" style="flex: 2; min-width: 200px;">
            <option value="">-- Choose Resident to Add --</option>
          </select>
          <select id="select-member-relation" class="form-select" style="flex: 1; min-width: 120px;">
            <option value="Spouse">Spouse / Asawa</option>
            <option value="Son">Son / Anak na Lalaki</option>
            <option value="Daughter">Daughter / Anak na Babae</option>
            <option value="Parent">Parent / Magulang</option>
            <option value="Sibling">Sibling / Kapatid</option>
            <option value="Grandchild">Grandchild / Apo</option>
            <option value="In-Law">In-Law / Biyanan o Manugang</option>
            <option value="Dependent">Other Dependent</option>
          </select>
          <button type="button" class="button-outline" onclick="addMemberToModalList()" style="height: 38px; padding: 0 14px; font-size: 0.8125rem;">
            + Add
          </button>
        </div>

        <div style="border: 1px solid var(--color-hairline); border-radius: var(--rounded-sm); background: var(--color-canvas); overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem;">
            <thead>
              <tr style="background: var(--color-canvas-soft); border-bottom: 1px solid var(--color-hairline); text-align: left;">
                <th style="padding: 6px 10px;">Name</th>
                <th style="padding: 6px 10px;">Relationship</th>
                <th style="padding: 6px 10px;">Age / Details</th>
                <th style="padding: 6px 10px; text-align: right;">Action</th>
              </tr>
            </thead>
            <tbody id="modal-members-tbody"></tbody>
          </table>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="fam-remarks">Remarks &amp; Welfare Notes</label>
        <textarea id="fam-remarks" class="form-textarea" rows="2" placeholder="e.g. Beneficiary of DAFAC typhoon assistance; vulnerable senior residing."></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); margin-top: var(--spacing-md); border-top: 1px solid var(--color-hairline); padding-top: 12px;">
        <button type="button" class="button-outline" onclick="document.getElementById('family-modal').close();" style="height: 38px; padding: 0 16px;">
          Cancel
        </button>
        <button type="submit" class="button-primary" id="btn-save-family" style="height: 38px; padding: 0 20px;">
          Save Family Profile
        </button>
      </div>
    </form>
  </dialog>

  <!-- ===================================================== -->
  <!-- MODAL 2: FAMILY DOSSIER, TREE & AYUDA LEDGER          -->
  <!-- ===================================================== -->
  <dialog id="dossier-modal" class="modal-dialog" style="max-width: 860px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; border-bottom: 1px solid var(--color-hairline); padding-bottom: 10px;">
      <div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <span class="family-code-badge" id="dossier-fam-code">FAM-2026-00001</span>
          <h3 class="typography-heading-3" id="dossier-fam-name" style="margin: 0;">Family Dossier</h3>
        </div>
        <p class="typography-caption" id="dossier-fam-meta" style="color: var(--color-text-muted); margin-top: 4px;">
          Head: — &bull; Purok 1 &bull; 0 Members
        </p>
      </div>
      <div style="display: flex; gap: 8px; align-items: center;">
        <button type="button" class="button-outline" onclick="openPrintModal(activeFamilyForAction.id)" style="height: 32px; padding: 0 12px; font-size: 0.75rem;">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
          <span>Print Composition</span>
        </button>
        <button type="button" class="button-icon-soft" onclick="document.getElementById('dossier-modal').close();" aria-label="Close modal">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="tab-nav">
      <button type="button" class="tab-btn active" id="tab-btn-tree" onclick="switchDossierTab('tree')">
        Visual Family Tree
      </button>
      <button type="button" class="tab-btn" id="tab-btn-members" onclick="switchDossierTab('members')">
        Members Roster (<span id="dossier-tab-member-count">0</span>)
      </button>
      <button type="button" class="tab-btn" id="tab-btn-ayuda" onclick="switchDossierTab('ayuda')">
        Social Welfare &amp; Ayuda (<span id="dossier-tab-ayuda-count">0</span>)
      </button>
      <button type="button" class="tab-btn" id="tab-btn-socio" onclick="switchDossierTab('socio')">
        Socio-Economic Profile
      </button>
    </div>

    <!-- TAB 1: VISUAL FAMILY TREE -->
    <div id="dossier-tab-content-tree">
      <div class="tree-diagram-container" id="tree-diagram-mount">
        <!-- Rendered via JS -->
      </div>
    </div>

    <!-- TAB 2: MEMBERS ROSTER -->
    <div id="dossier-tab-content-members" style="display: none;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <span class="typography-label" style="color: var(--color-text-muted);">ENLISTED FAMILY MEMBERS</span>
        <button type="button" class="button-primary" onclick="promptAddDossierMember()" style="height: 30px; padding: 0 12px; font-size: 0.75rem;">
          + Add Resident to Family
        </button>
      </div>

      <div style="border: 1px solid var(--color-hairline); border-radius: var(--rounded-sm); overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem;">
          <thead>
            <tr style="background: var(--color-canvas-soft); border-bottom: 1px solid var(--color-hairline); text-align: left;">
              <th style="padding: 8px 12px;">Resident Name</th>
              <th style="padding: 8px 12px;">Relationship</th>
              <th style="padding: 8px 12px;">Age &amp; Status</th>
              <th style="padding: 8px 12px;">Occupation / Income</th>
              <th style="padding: 8px 12px;">Vulnerability</th>
              <th style="padding: 8px 12px; text-align: right;">Action</th>
            </tr>
          </thead>
          <tbody id="dossier-members-table-tbody"></tbody>
        </table>
      </div>
    </div>

    <!-- TAB 3: SOCIAL WELFARE & AYUDA LEDGER -->
    <div id="dossier-tab-content-ayuda" style="display: none;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <div>
          <span class="typography-label" style="color: var(--color-text-muted);">DISASTER RELIEF, SAP, &amp; AYUDA DISBURSEMENT HISTORY</span>
          <div class="typography-caption" id="dossier-ayuda-summary-caption">Total Received: ₱0.00</div>
        </div>
        <button type="button" class="button-primary" onclick="openRecordAyudaModal()" style="height: 30px; padding: 0 12px; font-size: 0.75rem;">
          + Record Ayuda Grant
        </button>
      </div>

      <div style="border: 1px solid var(--color-hairline); border-radius: var(--rounded-sm); overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem;">
          <thead>
            <tr style="background: var(--color-canvas-soft); border-bottom: 1px solid var(--color-hairline); text-align: left;">
              <th style="padding: 8px 12px;">Date</th>
              <th style="padding: 8px 12px;">Program / Source</th>
              <th style="padding: 8px 12px;">Assistance Type</th>
              <th style="padding: 8px 12px;">Amount / Items</th>
              <th style="padding: 8px 12px;">DAFAC / Ref No.</th>
              <th style="padding: 8px 12px;">Disbursed By</th>
              <th style="padding: 8px 12px; text-align: right;">Action</th>
            </tr>
          </thead>
          <tbody id="dossier-ayuda-table-tbody"></tbody>
        </table>
      </div>
    </div>

    <!-- TAB 4: SOCIO-ECONOMIC PROFILE -->
    <div id="dossier-tab-content-socio" style="display: none;">
      <div class="form-grid-2" style="gap: 12px;">
        <div style="background: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-sm);">
          <div class="typography-label" style="color: var(--color-primary); margin-bottom: 6px;">DWELLING STRUCTURE &amp; LOCATION</div>
          <div style="font-size: 0.8125rem; line-height: 1.6;">
            <div><strong>Household Dwelling:</strong> <span id="socio-hh-no">None / Unlinked</span></div>
            <div><strong>Dwelling Structure:</strong> <span id="socio-hh-structure">—</span></div>
            <div><strong>Housing Tenure:</strong> <span id="socio-tenure">—</span></div>
            <div><strong>Purok / Zone:</strong> <span id="socio-purok">—</span></div>
            <div><strong>Hazard Exposure:</strong> <span id="socio-hazard">—</span></div>
          </div>
        </div>

        <div style="background: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-sm);">
          <div class="typography-label" style="color: var(--color-primary); margin-bottom: 6px;">INCOME &amp; POVERTY ASSESSMENT</div>
          <div style="font-size: 0.8125rem; line-height: 1.6;">
            <div><strong>Monthly Family Income:</strong> <span id="socio-income">₱0.00</span></div>
            <div><strong>Income Bracket:</strong> <span id="socio-bracket">—</span></div>
            <div><strong>Poverty Status:</strong> <span id="socio-poverty">—</span></div>
            <div><strong>Main Livelihood:</strong> <span id="socio-livelihood">—</span></div>
            <div><strong>4Ps Status:</strong> <span id="socio-4ps">—</span></div>
            <div><strong>Ayuda Priority:</strong> <span id="socio-priority">—</span></div>
          </div>
        </div>
      </div>

      <div style="margin-top: 12px; background: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-sm);">
        <div class="typography-label" style="color: var(--color-primary); margin-bottom: 4px;">CASEWORKER REMARKS &amp; NOTES</div>
        <p class="typography-body-sm" id="socio-remarks" style="margin: 0; color: var(--color-ink);">No notes recorded.</p>
      </div>
    </div>
  </dialog>

  <!-- ===================================================== -->
  <!-- MODAL 3: RECORD AYUDA / RELIEF DISBURSEMENT           -->
  <!-- ===================================================== -->
  <dialog id="ayuda-modal" class="modal-dialog" style="max-width: 500px; width: 90%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-hairline); padding-bottom: 8px;">
      <h3 class="typography-heading-4">Record Ayuda / Assistance Grant</h3>
      <button type="button" class="button-icon-soft" onclick="document.getElementById('ayuda-modal').close();">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form id="ayuda-form">
      <input type="hidden" id="ayuda-family-id" value="">

      <div class="form-group">
        <label class="form-label" for="ayuda-program">Program / Assistance Source *</label>
        <input type="text" id="ayuda-program" class="form-input" required placeholder="e.g. DSWD AICS, BDRRMC Typhoon Relief, LGU Ayuda">
      </div>

      <div class="form-grid-2">
        <div class="form-group">
          <label class="form-label" for="ayuda-type">Assistance Type</label>
          <select id="ayuda-type" class="form-select">
            <option value="Food Packs / Relief Goods">Food Packs / Relief Goods</option>
            <option value="Cash Assistance / AICS">Cash Assistance / AICS</option>
            <option value="Medical Assistance">Medical Assistance</option>
            <option value="Educational Assistance">Educational Assistance</option>
            <option value="Emergency Shelter Assistance">Emergency Shelter Assistance</option>
            <option value="Burial Assistance">Burial Assistance</option>
            <option value="Livelihood Kit">Livelihood Kit</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="ayuda-amount">Amount / Value (PHP) *</label>
          <input type="number" id="ayuda-amount" class="form-input" min="0" step="50" value="0" required>
        </div>
      </div>

      <div class="form-group" style="margin-top: 8px;">
        <label class="form-label" for="ayuda-items">Items Description / Relief Package Particulars</label>
        <input type="text" id="ayuda-items" class="form-input" placeholder="e.g. 1 sack rice, 10 cans sardines, hygiene kit">
      </div>

      <div class="form-grid-2" style="margin-top: 8px;">
        <div class="form-group">
          <label class="form-label" for="ayuda-dafac">DAFAC / Reference No.</label>
          <input type="text" id="ayuda-dafac" class="form-input" placeholder="e.g. DAFAC-2026-089">
        </div>

        <div class="form-group">
          <label class="form-label" for="ayuda-date">Date Provided *</label>
          <input type="date" id="ayuda-date" class="form-input" required>
        </div>
      </div>

      <div class="form-group" style="margin-top: 8px;">
        <label class="form-label" for="ayuda-disbursed-by">Disbursed By (Officer / Caseworker)</label>
        <input type="text" id="ayuda-disbursed-by" class="form-input" placeholder="Barangay Social Welfare Officer">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); margin-top: var(--spacing-md); border-top: 1px solid var(--color-hairline); padding-top: 10px;">
        <button type="button" class="button-outline" onclick="document.getElementById('ayuda-modal').close();" style="height: 36px; padding: 0 14px;">
          Cancel
        </button>
        <button type="submit" class="button-primary" style="height: 36px; padding: 0 16px;">
          Record Grant
        </button>
      </div>
    </form>
  </dialog>

  <!-- ===================================================== -->
  <!-- MODAL 4: PRINTABLE CERTIFICATE OF FAMILY COMPOSITION   -->
  <!-- ===================================================== -->
  <dialog id="print-modal" class="modal-dialog" style="max-width: 800px; width: 95%;">
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--color-hairline); padding-bottom: 8px;">
      <h3 class="typography-heading-4">Official Certificate of Family Composition</h3>
      <div style="display: flex; gap: 8px;">
        <button type="button" class="button-primary" onclick="window.print();" style="height: 34px; padding: 0 16px; font-size: 0.8125rem;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
          <span>Print Certificate</span>
        </button>
        <button type="button" class="button-outline" onclick="document.getElementById('print-modal').close();" style="height: 34px; padding: 0 12px;">
          Close
        </button>
      </div>
    </div>

    <!-- Official Printable Document Template -->
    <div id="printable-composition">
      <!-- Letterhead -->
      <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 20px;">
        <div style="font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.05em;">Republic of the Philippines</div>
        <div style="font-size: 0.8125rem; text-transform: uppercase;" id="print-jurisdiction">Province of Rizal &bull; Municipality of Taytay</div>
        <div style="font-size: 1.125rem; font-weight: 800; text-transform: uppercase; margin: 4px 0;" id="print-brgy-name">BARANGAY SAN ISIDRO</div>
        <div style="font-size: 0.875rem; font-weight: 700; letter-spacing: 0.08em; color: #1e3a8a;">OFFICE OF THE PUNONG BARANGAY</div>
      </div>

      <!-- Document Title -->
      <div style="text-align: center; margin: 24px 0 20px;">
        <h2 style="font-size: 1.375rem; font-weight: 900; text-transform: uppercase; text-decoration: underline; margin-bottom: 4px;">
          CERTIFICATE OF FAMILY COMPOSITION
        </h2>
        <div style="font-size: 0.875rem; font-style: italic;">(Katunayan ng Komposisyon ng Pamilya)</div>
        <div style="font-size: 0.75rem; font-family: monospace; margin-top: 4px;">CONTROL NO: <strong id="print-ctrl-no">FAM-2026-00001</strong></div>
      </div>

      <!-- Body / Attestation -->
      <div style="font-size: 0.9375rem; line-height: 1.8; text-align: justify; margin-bottom: 20px;">
        <p style="text-indent: 32px; margin-bottom: 12px;">
          <strong>TO WHOM IT MAY CONCERN:</strong>
        </p>
        <p style="text-indent: 32px; margin-bottom: 16px;">
          This is to certify that according to the official demographic census and family records on file in this Barangay, 
          the family of <strong id="print-head-name" style="text-decoration: underline;">JUAN DELA CRUZ</strong>, 
          residing at <span id="print-address">Purok 1, Barangay San Isidro</span>, is composed of the following bonafide family members:
        </p>
      </div>

      <!-- Family Members Table -->
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 0.875rem;">
        <thead>
          <tr style="background-color: #f3f4f6; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000;">
            <th style="padding: 8px; border: 1px solid #000; text-align: center; width: 40px;">No.</th>
            <th style="padding: 8px; border: 1px solid #000; text-align: left;">Full Name</th>
            <th style="padding: 8px; border: 1px solid #000; text-align: left;">Relationship</th>
            <th style="padding: 8px; border: 1px solid #000; text-align: center; width: 50px;">Age</th>
            <th style="padding: 8px; border: 1px solid #000; text-align: left;">Civil Status</th>
            <th style="padding: 8px; border: 1px solid #000; text-align: left;">Occupation / Details</th>
          </tr>
        </thead>
        <tbody id="print-members-table-tbody"></tbody>
      </table>

      <!-- Attestation & Purpose -->
      <div style="font-size: 0.9375rem; line-height: 1.8; text-align: justify; margin-bottom: 36px;">
        <p style="text-indent: 32px;">
          This certification is issued upon the request of the interested party for the purpose of 
          <strong>DSWD 4Ps Validation, Social Pension, Educational / Medical / Financial Assistance, or Legal Reference</strong>, 
          and for whatever lawful purposes it may serve.
        </p>
        <p style="text-indent: 32px; margin-top: 12px;">
          Issued this <strong id="print-day">2nd</strong> day of <strong id="print-month-year">October 2026</strong> 
          at Barangay San Isidro Hall, Philippines.
        </p>
      </div>

      <!-- Signatories -->
      <div style="display: flex; justify-content: space-between; margin-top: 48px; padding: 0 20px;">
        <div style="text-align: center; min-width: 200px;">
          <div style="border-bottom: 1px solid #000; font-weight: 800; font-size: 0.9375rem; padding-bottom: 2px;" id="print-secretary-name">
            MARIA SANTOS
          </div>
          <div style="font-size: 0.75rem; text-transform: uppercase;">Barangay Secretary</div>
        </div>

        <div style="text-align: center; min-width: 220px;">
          <div style="border-bottom: 1px solid #000; font-weight: 800; font-size: 0.9375rem; padding-bottom: 2px;" id="print-punong-name">
            HON. ANTONIO S. VALDEZ
          </div>
          <div style="font-size: 0.75rem; text-transform: uppercase;">Punong Barangay</div>
        </div>
      </div>

      <div style="margin-top: 36px; text-align: center; font-size: 0.6875rem; color: #666; font-style: italic;">
        Not valid without official seal &bull; Barangay Management System (BarangayOS) Generated
      </div>
    </div>
  </dialog>

  <!-- ===================================================== -->
  <!-- MODAL 5: DELETE CONFIRMATION                          -->
  <!-- ===================================================== -->
  <dialog id="delete-modal" class="modal-dialog" style="max-width: 420px; width: 90%; text-align: center;">
    <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-sm);">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
      </svg>
    </div>
    <h3 class="typography-heading-4">Delete Family Record?</h3>
    <p class="typography-body-sm" style="color: var(--color-text-muted); margin: 6px 0 var(--spacing-md);">
      Are you sure you want to delete <strong id="delete-fam-name">Santos Family</strong> (<span id="delete-fam-code">FAM-2026-00001</span>)? Resident records will remain intact in the resident masterlist.
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
    let allFamilies = [];
    let allResidentsMap = new Map();
    let allHouseholdsMap = new Map();
    let currentAuthUser = null;
    let editingFamilyId = null;
    let activeFamilyForAction = null;
    let currentModalMembers = []; // Working list for registration modal

    document.addEventListener('DOMContentLoaded', async () => {
      // 1. Guard route: require authenticated official session
      const auth = await authService.requireAuth('login.php');
      if (!auth) return;
      currentAuthUser = auth.user;

      // 2. Render App Shell Sidebar & Topbar
      await AppSidebar.render('families');

      // 3. Load Settings for Letterhead & Signatories
      await loadBarangayMeta();

      // 4. Load Residents & Households into memory & dropdowns
      await loadResidents();
      await loadHouseholds();

      // 5. Query Families from API / DB
      await refreshFamiliesList();

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

    // Load Residents
    async function loadResidents() {
      try {
        const residents = await window.barangayDB.getAll('residents');
        allResidentsMap.clear();
        residents.sort((a, b) => (a.lastName || '').localeCompare(b.lastName || ''));

        const selectHead = document.getElementById('select-head-resident');
        const selectMember = document.getElementById('select-add-member');

        selectHead.innerHTML = '<option value="">-- Choose Registered Resident as Family Head --</option>';
        selectMember.innerHTML = '<option value="">-- Choose Resident to Add --</option>';

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

    // Load Households
    async function loadHouseholds() {
      try {
        const households = await window.barangayDB.getAll('households');
        allHouseholdsMap.clear();
        const selectHh = document.getElementById('select-household');
        selectHh.innerHTML = '<option value="">-- Shared Structure / Select Household --</option>';

        households.forEach(h => {
          allHouseholdsMap.set(h.id, h);
          const opt = document.createElement('option');
          opt.value = h.id;
          opt.textContent = `${h.householdNo || 'HH-#'} - ${h.address || h.purok || 'Dwelling'}`;
          selectHh.appendChild(opt);
        });
      } catch (err) {
        console.error('Error loading households:', err);
      }
    }

    // Refresh Families List
    async function refreshFamiliesList() {
      try {
        allFamilies = await window.barangayDB.getAll('families');
        if (!allFamilies || !Array.isArray(allFamilies)) {
          allFamilies = [];
        }
        allFamilies.sort((a, b) => new Date(b.createdAt || 0) - new Date(a.createdAt || 0));

        updateStatsLadder();
        applyFilters();
      } catch (err) {
        console.error('Failed to load families:', err);
        Toast.error('Could not load family profiles.');
      }
    }

    // Compute Telemetry Stats
    function updateStatsLadder() {
      const total = allFamilies.length;
      document.getElementById('family-count-badge').textContent = `${total} ${total === 1 ? 'Family' : 'Families'}`;
      document.getElementById('stat-total-families').textContent = total;

      const totalMembers = allFamilies.reduce((sum, f) => sum + (f.memberCount || 1), 0);
      const avgSize = total > 0 ? (totalMembers / total).toFixed(1) : '0.0';
      document.getElementById('stat-sub-families').textContent = `Avg family size: ${avgSize}`;

      const indigent = allFamilies.filter(f => (f.povertyStatus || '').toLowerCase().includes('indigent') || (f.povertyStatus || '').toLowerCase().includes('subsistence')).length;
      document.getElementById('stat-indigent-families').textContent = indigent;

      const fourPs = allFamilies.filter(f => f.is4psBeneficiary || f.is_4ps_beneficiary).length;
      document.getElementById('stat-4ps-families').textContent = fourPs;

      // Query assistance records stats if possible
      window.barangayDB.getAll('family_assistance').then(records => {
        if (records && Array.isArray(records)) {
          const totalVal = records.reduce((sum, r) => sum + (parseFloat(r.amountValue || r.amount_value) || 0), 0);
          document.getElementById('stat-total-grants').textContent = records.length;
          document.getElementById('stat-sub-grants').textContent = `₱${totalVal.toLocaleString('en-US', { minimumFractionDigits: 2 })} assistance value`;
        }
      }).catch(() => {});
    }

    // Filter Logic
    function applyFilters() {
      const q = document.getElementById('search-filter').value.toLowerCase().trim();
      const purok = document.getElementById('purok-filter').value;
      const poverty = document.getElementById('poverty-filter').value;
      const fourPs = document.getElementById('fourps-filter').value;
      const type = document.getElementById('type-filter').value;

      const filtered = allFamilies.filter(f => {
        const matchQ = !q || 
          (f.familyName || f.family_name || '').toLowerCase().includes(q) ||
          (f.familyCode || f.family_code || '').toLowerCase().includes(q) ||
          (f.headFullName || f.head_full_name || '').toLowerCase().includes(q);

        const matchPurok = !purok || (f.purok === purok);
        const matchPoverty = !poverty || (f.povertyStatus === poverty || f.poverty_status === poverty);
        const match4Ps = (fourPs === '') || (Boolean(f.is4psBeneficiary || f.is_4ps_beneficiary) === (fourPs === '1'));
        const matchType = !type || (f.familyType === type || f.family_type === type);

        return matchQ && matchPurok && matchPoverty && match4Ps && matchType;
      });

      renderCardsView(filtered);
      renderTableView(filtered);

      const empty = document.getElementById('empty-state');
      empty.style.display = filtered.length === 0 ? 'block' : 'none';
    }

    // Render Cards View
    function renderCardsView(list) {
      const mount = document.getElementById('families-grid-mount');
      mount.innerHTML = list.map(f => {
        const code = f.familyCode || f.family_code || 'FAM-000';
        const name = f.familyName || f.family_name || 'Family';
        const headName = f.headFullName || f.head_full_name || 'Unassigned Head';
        const purok = f.purok || 'Purok 1';
        const memberCount = f.memberCount || f.member_count || 1;
        const income = parseFloat(f.monthlyIncome || f.monthly_income || 0);
        const poverty = f.povertyStatus || f.poverty_status || 'Indigent';
        const is4Ps = f.is4psBeneficiary || f.is_4ps_beneficiary;
        const isAyuda = f.isAyudaPriority || f.is_ayuda_priority;
        const familyType = f.familyType || f.family_type || 'Nuclear';
        const dwelling = f.householdNo || f.household_no ? `Dwelling ${f.householdNo || f.household_no}` : 'No Dwelling Link';

        const povertyBadgeClass = poverty.includes('Indigent') ? 'badge-amber' : 
                                 (poverty.includes('Low Income') ? 'badge-amber' : 'badge-neutral');

        return `
          <div class="family-card">
            <div>
              <div class="family-card-header">
                <div>
                  <span class="family-code-badge">${code}</span>
                  <div class="family-title" style="margin-top: 4px;">
                    ${name}
                    ${isAyuda ? '<span title="Ayuda Priority Household" style="color: #f59e0b;">★</span>' : ''}
                  </div>
                </div>
                <div style="display: flex; gap: 4px; flex-direction: column; align-items: flex-end;">
                  <span class="${povertyBadgeClass}" style="font-size: 0.625rem;">${poverty}</span>
                  ${is4Ps ? '<span class="badge-emerald" style="font-size: 0.625rem;">4Ps Member</span>' : ''}
                </div>
              </div>

              <div class="family-head-row">
                <div class="family-head-avatar">
                  ${headName.charAt(0)}
                </div>
                <div style="overflow: hidden;">
                  <div class="typography-caption" style="color: var(--color-primary); font-weight: 700; font-size: 0.6875rem;">HEAD OF FAMILY</div>
                  <div style="font-weight: 700; font-size: 0.875rem; color: var(--color-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    ${headName}
                  </div>
                </div>
              </div>

              <div class="family-meta-grid">
                <div class="family-meta-item">
                  <span class="family-meta-label">Location / Dwelling</span>
                  <span class="family-meta-value">${purok} &bull; <span style="font-weight: 500; font-size: 0.75rem;">${dwelling}</span></span>
                </div>
                <div class="family-meta-item">
                  <span class="family-meta-label">Kinship Type</span>
                  <span class="family-meta-value">${familyType}</span>
                </div>
                <div class="family-meta-item">
                  <span class="family-meta-label">Family Size</span>
                  <span class="family-meta-value">${memberCount} ${memberCount === 1 ? 'member' : 'members'}</span>
                </div>
                <div class="family-meta-item">
                  <span class="family-meta-label">Est. Monthly Income</span>
                  <span class="family-meta-value">₱${income.toLocaleString('en-US', { minimumFractionDigits: 2 })}</span>
                </div>
              </div>
            </div>

            <div class="card-actions-row">
              <button type="button" class="button-primary" onclick="openDossierModal(${f.id})" style="height: 32px; padding: 0 12px; font-size: 0.75rem;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>Dossier &amp; Tree</span>
              </button>

              <div style="display: flex; gap: 4px;">
                <button class="table-action-btn" onclick="openPrintModal(${f.id})" title="Print Certificate of Family Composition">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                </button>
                <button class="table-action-btn" onclick="openEditModal(${f.id})" title="Edit Family Profile">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="table-action-btn danger" onclick="openDeleteModal(${f.id})" title="Delete Family Record">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </div>
            </div>
          </div>
        `;
      }).join('');
    }

    // Render Table View
    function renderTableView(list) {
      const tbody = document.getElementById('families-table-tbody');
      tbody.innerHTML = list.map(f => {
        const code = f.familyCode || f.family_code || 'FAM-000';
        const name = f.familyName || f.family_name || 'Family';
        const headName = f.headFullName || f.head_full_name || '—';
        const purok = f.purok || 'Purok 1';
        const memberCount = f.memberCount || f.member_count || 1;
        const income = parseFloat(f.monthlyIncome || f.monthly_income || 0);
        const poverty = f.povertyStatus || f.poverty_status || 'Indigent';
        const is4Ps = f.is4psBeneficiary || f.is_4ps_beneficiary;
        const isAyuda = f.isAyudaPriority || f.is_ayuda_priority;
        const familyType = f.familyType || f.family_type || 'Nuclear';
        const dwelling = f.householdNo || f.household_no ? `${f.householdNo || f.household_no}` : '—';

        return `
          <tr style="border-bottom: 1px solid var(--color-hairline-soft);">
            <td style="padding: 10px 14px; font-family: monospace; font-weight: 700;">${code}</td>
            <td style="padding: 10px 14px;">
              <div style="font-weight: 700; color: var(--color-ink);">${name}</div>
              <div class="typography-caption">Head: ${headName}</div>
            </td>
            <td style="padding: 10px 14px;">
              <div>${purok}</div>
              <div class="typography-caption">${dwelling}</div>
            </td>
            <td style="padding: 10px 14px;"><span class="badge-neutral">${familyType}</span></td>
            <td style="padding: 10px 14px; text-align: center; font-weight: 700;">${memberCount}</td>
            <td style="padding: 10px 14px;">₱${income.toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
            <td style="padding: 10px 14px;"><span class="${poverty.includes('Indigent') ? 'badge-amber' : 'badge-neutral'}">${poverty}</span></td>
            <td style="padding: 10px 14px;">
              ${is4Ps ? '<span class="badge-emerald">4Ps</span>' : ''}
              ${isAyuda ? '<span class="badge-amber" title="Ayuda Priority">Priority</span>' : ''}
            </td>
            <td style="padding: 10px 14px; text-align: right;">
              <div style="display: flex; gap: 4px; justify-content: flex-end;">
                <button class="table-action-btn" onclick="openDossierModal(${f.id})" title="View Dossier">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
                <button class="table-action-btn" onclick="openPrintModal(${f.id})" title="Print Certificate">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                </button>
                <button class="table-action-btn" onclick="openEditModal(${f.id})" title="Edit">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="table-action-btn danger" onclick="openDeleteModal(${f.id})" title="Delete">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Switch Views
    window.switchView = function(mode) {
      const isCards = mode === 'cards';
      document.getElementById('view-cards-container').style.display = isCards ? 'block' : 'none';
      document.getElementById('view-table-container').style.display = isCards ? 'none' : 'block';

      document.getElementById('btn-view-cards').classList.toggle('active', isCards);
      document.getElementById('btn-view-table').classList.toggle('active', !isCards);
    };

    // Open Family Registration Modal
    window.openFamilyModal = function() {
      editingFamilyId = null;
      document.getElementById('family-form').reset();
      document.getElementById('family-edit-id').value = '';
      document.getElementById('modal-family-title').textContent = 'Register Family Profile.';
      document.getElementById('btn-save-family').textContent = 'Save Family Profile';

      const year = new Date().getFullYear();
      const seq = String(allFamilies.length + 1).padStart(5, '0');
      document.getElementById('fam-control-no').value = `FAM-${year}-${seq}`;

      currentModalMembers = [];
      renderModalMembersList();
      document.getElementById('head-preview-card').style.display = 'none';
      document.getElementById('group-4ps-no').style.display = 'none';
      updatePovertyPreview(0);

      document.getElementById('family-modal').showModal();
    };

    // Open Edit Modal
    window.openEditModal = async function(id) {
      try {
        const fam = await fetchFamilyDossier(id);
        if (!fam) return;

        editingFamilyId = fam.id;
        document.getElementById('family-form').reset();
        document.getElementById('family-edit-id').value = fam.id;
        document.getElementById('modal-family-title').textContent = `Edit Family: ${fam.familyName || fam.family_name}`;
        document.getElementById('btn-save-family').textContent = 'Update Family Profile';

        document.getElementById('fam-control-no').value = fam.familyCode || fam.family_code;
        document.getElementById('fam-name').value = fam.familyName || fam.family_name;
        document.getElementById('select-head-resident').value = fam.headResidentId || fam.head_resident_id || '';
        handleHeadSelection(fam.headResidentId || fam.head_resident_id);

        document.getElementById('select-household').value = fam.householdId || fam.household_id || '';
        document.getElementById('fam-purok').value = fam.purok || 'Purok 1';
        document.getElementById('fam-type').value = fam.familyType || fam.family_type || 'Nuclear';
        document.getElementById('fam-tenure').value = fam.housingTenure || fam.housing_tenure || 'Owner';
        document.getElementById('fam-main-income').value = fam.mainSourceOfIncome || fam.main_source_of_income || 'Employment / Wages';

        const monthlyIncome = parseFloat(fam.monthlyIncome || fam.monthly_income || 0);
        document.getElementById('fam-monthly-income').value = monthlyIncome;
        updatePovertyPreview(monthlyIncome);

        const is4Ps = Boolean(fam.is4psBeneficiary || fam.is_4ps_beneficiary);
        document.getElementById('fam-is-4ps').checked = is4Ps;
        toggleFourPsInput(is4Ps);
        document.getElementById('fam-4ps-number').value = fam.fourPsNumber || fam.four_ps_number || '';

        document.getElementById('fam-is-ayuda').checked = Boolean(fam.isAyudaPriority || fam.is_ayuda_priority);
        document.getElementById('fam-remarks').value = fam.remarks || '';

        // Members
        currentModalMembers = (fam.members || []).map(m => {
          const res = allResidentsMap.get(m.residentId || m.resident_id);
          const fullName = res ? [res.firstName, res.middleName, res.lastName, res.suffix].filter(Boolean).join(' ') : (m.fullName || m.full_name || 'Resident');
          return {
            residentId: m.residentId || m.resident_id,
            fullName: fullName,
            relationship: m.relationshipToHead || m.relationship_to_head || 'Member',
            isHead: (m.relationshipToHead || m.relationship_to_head) === 'Head',
            age: res ? res.age : (m.age || 0),
            civilStatus: res ? res.civilStatus : 'Single'
          };
        });
        renderModalMembersList();

        document.getElementById('family-modal').showModal();
      } catch (err) {
        console.error('Error opening edit modal:', err);
        Toast.error('Could not load family record for editing.');
      }
    };

    // Handle Head Selection
    window.handleHeadSelection = function(residentId) {
      const res = allResidentsMap.get(parseInt(residentId, 10));
      const card = document.getElementById('head-preview-card');
      const famNameInput = document.getElementById('fam-name');

      if (!res) {
        card.style.display = 'none';
        return;
      }

      card.style.display = 'block';
      const fullName = [res.firstName, res.middleName, res.lastName, res.suffix].filter(Boolean).join(' ');
      document.getElementById('head-preview-name').textContent = fullName;
      document.getElementById('head-preview-meta').textContent = `${res.purok || 'Purok 1'} • Age: ${res.age || '—'} • ${res.civilStatus || 'Single'} • Phone: ${res.contactNo || res.phone || 'N/A'}`;

      if (!famNameInput.value.trim() || !editingFamilyId) {
        famNameInput.value = `${res.lastName || 'Santos'} Family`;
      }

      // Auto-populate purok if not manually set
      if (!editingFamilyId && res.purok) {
        document.getElementById('fam-purok').value = res.purok;
      }

      // Insert or update Head in currentModalMembers
      const headIdx = currentModalMembers.findIndex(m => m.isHead);
      const headObj = {
        residentId: res.id,
        fullName: fullName,
        relationship: 'Head',
        isHead: true,
        age: res.age || 0,
        civilStatus: res.civilStatus || 'Single'
      };

      if (headIdx >= 0) {
        currentModalMembers[headIdx] = headObj;
      } else {
        currentModalMembers.unshift(headObj);
      }
      renderModalMembersList();
    };

    // Add Member to Modal List
    window.addMemberToModalList = function() {
      const select = document.getElementById('select-add-member');
      const relSelect = document.getElementById('select-member-relation');
      const resId = parseInt(select.value, 10);
      const relation = relSelect.value;

      if (!resId) {
        Toast.warning('Please select a resident to add.');
        return;
      }

      if (currentModalMembers.some(m => m.residentId === resId)) {
        Toast.warning('This resident is already in the family list.');
        return;
      }

      const res = allResidentsMap.get(resId);
      const fullName = res ? [res.firstName, res.middleName, res.lastName, res.suffix].filter(Boolean).join(' ') : 'Resident';

      currentModalMembers.push({
        residentId: resId,
        fullName: fullName,
        relationship: relation,
        isHead: relation === 'Head',
        age: res ? res.age : 0,
        civilStatus: res ? res.civilStatus : 'Single'
      });

      select.value = '';
      renderModalMembersList();
    };

    // Remove Member from Modal List
    window.removeModalMember = function(idx) {
      if (currentModalMembers[idx].isHead) {
        Toast.warning('Cannot remove the Head of Family directly.');
        return;
      }
      currentModalMembers.splice(idx, 1);
      renderModalMembersList();
    };

    // Render Modal Members List
    function renderModalMembersList() {
      const tbody = document.getElementById('modal-members-tbody');
      document.getElementById('modal-member-counter').textContent = `${currentModalMembers.length} ${currentModalMembers.length === 1 ? 'Member' : 'Members'}`;

      if (currentModalMembers.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: var(--color-text-muted); padding: 12px;">No members enlisted yet. Select a Head of Family above.</td></tr>`;
        return;
      }

      tbody.innerHTML = currentModalMembers.map((m, idx) => `
        <tr style="border-bottom: 1px solid var(--color-hairline-soft);">
          <td style="padding: 6px 10px; font-weight: 600;">${m.fullName}</td>
          <td style="padding: 6px 10px;"><span class="${m.isHead ? 'badge-blue' : 'badge-neutral'}">${m.relationship}</span></td>
          <td style="padding: 6px 10px;">${m.age} yrs &bull; ${m.civilStatus}</td>
          <td style="padding: 6px 10px; text-align: right;">
            ${m.isHead ? '<span class="typography-caption" style="color: var(--color-primary); font-weight: 700;">HEAD</span>' : `
              <button type="button" class="table-action-btn danger" onclick="removeModalMember(${idx})" style="padding: 2px 6px;">Remove</button>
            `}
          </td>
        </tr>
      `).join('');
    }

    // Toggle 4Ps input field
    window.toggleFourPsInput = function(checked) {
      document.getElementById('group-4ps-no').style.display = checked ? 'block' : 'none';
    };

    // Dynamic Poverty Status Calculator
    window.updatePovertyPreview = function(income) {
      const inc = parseFloat(income) || 0;
      let status = 'Indigent / Below Poverty Threshold';
      let color = '#ef4444';

      if (inc < 10000) {
        status = 'Indigent / Below Poverty Threshold';
        color = '#ef4444';
      } else if (inc <= 20000) {
        status = 'Low Income / Subsistence';
        color = '#f59e0b';
      } else if (inc <= 40000) {
        status = 'Lower Middle Class';
        color = '#3b82f6';
      } else if (inc <= 70000) {
        status = 'Middle Class';
        color = '#10b981';
      } else {
        status = 'Above Average';
        color = '#8b5cf6';
      }

      const badge = document.getElementById('poverty-badge-preview');
      badge.textContent = status;
      badge.style.color = color;
    };

    // Fetch Full Family Dossier
    async function fetchFamilyDossier(id) {
      try {
        const resp = await fetch(`api/families.php?action=get&id=${id}`);
        const result = await resp.json();
        if (result && result.success) {
          return result.data;
        }
      } catch (err) {
        console.warn('API get failed, falling back to local object:', err);
      }
      return allFamilies.find(f => f.id === id);
    }

    // Open Family Dossier Modal
    window.openDossierModal = async function(id) {
      try {
        const fam = await fetchFamilyDossier(id);
        if (!fam) return;

        activeFamilyForAction = fam;

        document.getElementById('dossier-fam-code').textContent = fam.familyCode || fam.family_code;
        document.getElementById('dossier-fam-name').textContent = fam.familyName || fam.family_name;

        const members = fam.members || [];
        const assistance = fam.assistance_records || fam.assistanceRecords || [];
        const headName = fam.headFullName || fam.head_full_name || '—';

        document.getElementById('dossier-fam-meta').textContent = `Head: ${headName} • ${fam.purok} • ${members.length} Members`;
        document.getElementById('dossier-tab-member-count').textContent = members.length;
        document.getElementById('dossier-tab-ayuda-count').textContent = assistance.length;

        // Render Visual Tree
        renderVisualTree(fam, members);

        // Render Members Roster Table
        renderDossierMembersTable(members);

        // Render Ayuda Ledger
        renderDossierAyudaTable(fam, assistance);

        // Populate Socio-Economic Tab
        document.getElementById('socio-hh-no').textContent = fam.householdNo || fam.household_no ? `Household ${fam.householdNo || fam.household_no}` : 'Unlinked';
        document.getElementById('socio-hh-structure').textContent = fam.householdStructure || fam.household_structure || 'Standard';
        document.getElementById('socio-tenure').textContent = fam.housingTenure || fam.housing_tenure || 'Owner';
        document.getElementById('socio-purok').textContent = fam.purok || 'Purok 1';
        document.getElementById('socio-hazard').textContent = fam.householdHazard || fam.household_hazard || 'Low Hazard';

        const monthlyIncome = parseFloat(fam.monthlyIncome || fam.monthly_income || 0);
        document.getElementById('socio-income').textContent = `₱${monthlyIncome.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
        document.getElementById('socio-bracket').textContent = fam.incomeBracket || fam.income_bracket || 'Under 10,000';
        document.getElementById('socio-poverty').textContent = fam.povertyStatus || fam.poverty_status || 'Indigent';
        document.getElementById('socio-livelihood').textContent = fam.mainSourceOfIncome || fam.main_source_of_income || 'Wages';
        document.getElementById('socio-4ps').textContent = (fam.is4psBeneficiary || fam.is_4ps_beneficiary) ? `Yes (${fam.fourPsNumber || fam.four_ps_number || 'Enlisted'})` : 'No';
        document.getElementById('socio-priority').textContent = (fam.isAyudaPriority || fam.is_ayuda_priority) ? 'Yes (Priority Ayuda Beneficiary)' : 'Standard';
        document.getElementById('socio-remarks').textContent = fam.remarks || 'No casework notes recorded.';

        switchDossierTab('tree');
        document.getElementById('dossier-modal').showModal();
      } catch (err) {
        console.error('Error opening dossier modal:', err);
        Toast.error('Could not load family dossier.');
      }
    };

    // Render Visual Tree Diagram
    function renderVisualTree(fam, members) {
      const mount = document.getElementById('tree-diagram-mount');
      const head = members.find(m => (m.relationshipToHead || m.relationship_to_head) === 'Head') || members[0];
      const spouse = members.find(m => (m.relationshipToHead || m.relationship_to_head || '').includes('Spouse'));
      const children = members.filter(m => {
        const rel = m.relationshipToHead || m.relationship_to_head || '';
        return rel.includes('Son') || rel.includes('Daughter') || rel.includes('Child');
      });
      const extended = members.filter(m => m !== head && m !== spouse && !children.includes(m));

      const headName = head ? (head.fullName || head.full_name || `${head.first_name || ''} ${head.last_name || ''}`.trim()) : (fam.headFullName || fam.head_full_name);
      const headAge = head ? (head.age || '—') : '—';

      let html = `
        <div style="display: flex; align-items: center; justify-content: center; gap: 20px; flex-wrap: wrap;">
          <div class="tree-node-card head">
            <span class="badge-blue" style="font-size: 0.5625rem; margin-bottom: 4px;">PUNO NG PAMILYA (HEAD)</span>
            <div style="font-weight: 800; font-size: 0.875rem; color: var(--color-ink);">${headName}</div>
            <div class="typography-caption">${headAge} yrs &bull; ${head ? (head.occupation || 'Head') : 'Head'}</div>
          </div>

          ${spouse ? `
            <div style="width: 20px; border-top: 2px dashed #8b5cf6;"></div>
            <div class="tree-node-card spouse">
              <span class="badge-purple" style="font-size: 0.5625rem; margin-bottom: 4px;">SPOUSE / ASAWA</span>
              <div style="font-weight: 800; font-size: 0.875rem; color: var(--color-ink);">${spouse.fullName || spouse.full_name || `${spouse.first_name || ''} ${spouse.last_name || ''}`}</div>
              <div class="typography-caption">${spouse.age || '—'} yrs &bull; ${spouse.occupation || 'Spouse'}</div>
            </div>
          ` : ''}
        </div>
      `;

      if (children.length > 0) {
        html += `
          <div class="tree-line-v"></div>
          <div class="typography-label" style="font-size: 0.625rem; color: var(--color-text-muted); margin: 2px 0;">CHILDREN / MGA ANAK</div>
          <div class="tree-children-row">
            ${children.map(c => `
              <div class="tree-node-card child">
                <span class="badge-emerald" style="font-size: 0.5625rem; margin-bottom: 2px;">${c.relationshipToHead || c.relationship_to_head || 'Child'}</span>
                <div style="font-weight: 700; font-size: 0.8125rem; color: var(--color-ink);">${c.fullName || c.full_name || `${c.first_name || ''} ${c.last_name || ''}`}</div>
                <div class="typography-caption">${c.age || '—'} yrs &bull; ${c.occupation || 'Student / Dependent'}</div>
              </div>
            `).join('')}
          </div>
        `;
      }

      if (extended.length > 0) {
        html += `
          <div class="tree-line-v"></div>
          <div class="typography-label" style="font-size: 0.625rem; color: var(--color-text-muted); margin: 2px 0;">EXTENDED RELATIVES &amp; DEPENDENTS</div>
          <div class="tree-children-row">
            ${extended.map(e => `
              <div class="tree-node-card">
                <span class="badge-neutral" style="font-size: 0.5625rem; margin-bottom: 2px;">${e.relationshipToHead || e.relationship_to_head || 'Relative'}</span>
                <div style="font-weight: 700; font-size: 0.8125rem; color: var(--color-ink);">${e.fullName || e.full_name || `${e.first_name || ''} ${e.last_name || ''}`}</div>
                <div class="typography-caption">${e.age || '—'} yrs &bull; ${e.occupation || 'Dependent'}</div>
              </div>
            `).join('')}
          </div>
        `;
      }

      mount.innerHTML = html;
    }

    // Render Dossier Members Table
    function renderDossierMembersTable(members) {
      const tbody = document.getElementById('dossier-members-table-tbody');
      if (!members || members.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--color-text-muted); padding: 16px;">No members linked to this family yet.</td></tr>`;
        return;
      }

      tbody.innerHTML = members.map(m => {
        const fullName = m.fullName || m.full_name || `${m.first_name || ''} ${m.last_name || ''}`.trim();
        const rel = m.relationshipToHead || m.relationship_to_head || 'Member';
        const isHead = rel === 'Head';
        const age = m.age || '—';
        const occ = m.occupation || '—';
        const earner = m.isIncomeEarner || m.is_income_earner;
        const inc = parseFloat(m.monthlyIncome || m.monthly_income || 0);

        return `
          <tr style="border-bottom: 1px solid var(--color-hairline-soft);">
            <td style="padding: 8px 12px; font-weight: 700; color: var(--color-ink);">${fullName}</td>
            <td style="padding: 8px 12px;"><span class="${isHead ? 'badge-blue' : 'badge-neutral'}">${rel}</span></td>
            <td style="padding: 8px 12px;">${age} yrs &bull; ${m.civilStatus || m.civil_status || 'Single'}</td>
            <td style="padding: 8px 12px;">
              <div>${occ}</div>
              ${earner ? `<div class="typography-caption" style="color: #10b981;">₱${inc.toLocaleString('en-US', { minimumFractionDigits: 2 })}/mo</div>` : ''}
            </td>
            <td style="padding: 8px 12px;">
              <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                ${m.is_senior || m.isSenior ? '<span class="badge-amber">Senior</span>' : ''}
                ${m.is_pwd || m.isPwd ? '<span class="badge-purple">PWD</span>' : ''}
                ${m.is_solo_parent || m.isSoloParent ? '<span class="badge-blue">Solo Parent</span>' : ''}
              </div>
            </td>
            <td style="padding: 8px 12px; text-align: right;">
              ${isHead ? '<span class="typography-caption" style="color: var(--color-primary); font-weight: 700;">HEAD</span>' : `
                <button type="button" class="table-action-btn danger" onclick="removeDossierMember(${m.id})" style="padding: 2px 6px;">Remove</button>
              `}
            </td>
          </tr>
        `;
      }).join('');
    }

    // Render Ayuda Table in Dossier
    function renderDossierAyudaTable(fam, records) {
      const tbody = document.getElementById('dossier-ayuda-table-tbody');
      const totalVal = records.reduce((sum, r) => sum + (parseFloat(r.amountValue || r.amount_value) || 0), 0);
      document.getElementById('dossier-ayuda-summary-caption').textContent = `Total Assistance Received: ₱${totalVal.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;

      if (!records || records.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 16px;">No assistance grants recorded yet. Click "+ Record Ayuda Grant" above to log aid.</td></tr>`;
        return;
      }

      tbody.innerHTML = records.map(r => {
        const val = parseFloat(r.amountValue || r.amount_value || 0);
        const dt = r.dateProvided || r.date_provided || '—';

        return `
          <tr style="border-bottom: 1px solid var(--color-hairline-soft);">
            <td style="padding: 8px 12px; white-space: nowrap;">${dt}</td>
            <td style="padding: 8px 12px; font-weight: 600;">${r.programName || r.program_name}</td>
            <td style="padding: 8px 12px;"><span class="badge-blue">${r.assistanceType || r.assistance_type}</span></td>
            <td style="padding: 8px 12px;">
              <div><strong>₱${val.toLocaleString('en-US', { minimumFractionDigits: 2 })}</strong></div>
              <div class="typography-caption">${r.itemsDescription || r.items_description || '—'}</div>
            </td>
            <td style="padding: 8px 12px; font-family: monospace;">${r.dafacNo || r.dafac_no || '—'}</td>
            <td style="padding: 8px 12px;">${r.disbursedBy || r.disbursed_by || 'Social Worker'}</td>
            <td style="padding: 8px 12px; text-align: right;">
              <button type="button" class="table-action-btn danger" onclick="deleteAyudaRecord(${r.id})" title="Delete Grant">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Switch Tabs in Dossier Modal
    window.switchDossierTab = function(tabName) {
      const tabs = ['tree', 'members', 'ayuda', 'socio'];
      tabs.forEach(t => {
        const isCurrent = t === tabName;
        document.getElementById(`tab-btn-${t}`).classList.toggle('active', isCurrent);
        document.getElementById(`dossier-tab-content-${t}`).style.display = isCurrent ? 'block' : 'none';
      });
    };

    // Prompt Add Member inside Dossier
    window.promptAddDossierMember = function() {
      const fam = activeFamilyForAction;
      if (!fam) return;

      const residentIdStr = prompt('Enter Resident ID to add to this family:');
      if (!residentIdStr) return;
      const resId = parseInt(residentIdStr, 10);
      if (!resId || !allResidentsMap.has(resId)) {
        Toast.error('Resident ID not found in masterlist.');
        return;
      }

      const relation = prompt('Enter Relationship to Family Head (e.g. Spouse, Son, Daughter, Parent, Sibling):', 'Son');
      if (!relation) return;

      window.barangayDB.add('family_members', {
        family_id: fam.id,
        resident_id: resId,
        relationship_to_head: relation
      }).then(() => {
        Toast.success('Member added to family.');
        openDossierModal(fam.id);
        refreshFamiliesList();
      }).catch(err => {
        console.error('Failed to add member:', err);
        Toast.error('Could not add member.');
      });
    };

    // Remove Member inside Dossier
    window.removeDossierMember = function(memberId) {
      if (!confirm('Are you sure you want to remove this member from the family roster?')) return;
      window.barangayDB.delete('family_members', memberId).then(() => {
        Toast.success('Member removed from family.');
        openDossierModal(activeFamilyForAction.id);
        refreshFamiliesList();
      }).catch(err => {
        console.error('Failed to remove member:', err);
        Toast.error('Could not remove member.');
      });
    };

    // Open Record Ayuda Modal
    window.openRecordAyudaModal = function() {
      if (!activeFamilyForAction) return;
      document.getElementById('ayuda-form').reset();
      document.getElementById('ayuda-family-id').value = activeFamilyForAction.id;
      document.getElementById('ayuda-date').value = new Date().toISOString().split('T')[0];
      document.getElementById('ayuda-modal').showModal();
    };

    // Delete Ayuda Record
    window.deleteAyudaRecord = function(id) {
      if (!confirm('Are you sure you want to delete this assistance grant record?')) return;
      window.barangayDB.delete('family_assistance', id).then(() => {
        Toast.success('Assistance record removed.');
        openDossierModal(activeFamilyForAction.id);
        refreshFamiliesList();
      }).catch(err => {
        console.error('Failed to delete assistance:', err);
        Toast.error('Could not delete assistance record.');
      });
    };

    // Open Print Certificate Modal
    window.openPrintModal = async function(id) {
      try {
        const fam = await fetchFamilyDossier(id) || activeFamilyForAction;
        if (!fam) return;

        activeFamilyForAction = fam;
        document.getElementById('print-ctrl-no').textContent = fam.familyCode || fam.family_code || 'FAM-2026-00001';
        document.getElementById('print-head-name').textContent = (fam.headFullName || fam.head_full_name || 'JUAN DELA CRUZ').toUpperCase();
        document.getElementById('print-address').textContent = `${fam.purok || 'Purok 1'}, Barangay San Isidro, Taytay, Rizal`;

        const now = new Date();
        document.getElementById('print-day').textContent = now.getDate();
        document.getElementById('print-month-year').textContent = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

        const members = fam.members || [];
        const tbody = document.getElementById('print-members-table-tbody');

        tbody.innerHTML = members.map((m, idx) => {
          const fullName = (m.fullName || m.full_name || `${m.first_name || ''} ${m.last_name || ''}`).toUpperCase();
          const rel = m.relationshipToHead || m.relationship_to_head || 'Member';
          const age = m.age || '—';
          const civ = m.civilStatus || m.civil_status || 'Single';
          const occ = m.occupation || (m.age < 18 ? 'Student / Minor' : 'Resident');

          return `
            <tr style="border-bottom: 1px solid #000;">
              <td style="padding: 6px 8px; border: 1px solid #000; text-align: center;">${idx + 1}</td>
              <td style="padding: 6px 8px; border: 1px solid #000; font-weight: 700;">${fullName}</td>
              <td style="padding: 6px 8px; border: 1px solid #000;">${rel}</td>
              <td style="padding: 6px 8px; border: 1px solid #000; text-align: center;">${age}</td>
              <td style="padding: 6px 8px; border: 1px solid #000;">${civ}</td>
              <td style="padding: 6px 8px; border: 1px solid #000;">${occ}</td>
            </tr>
          `;
        }).join('');

        document.getElementById('print-modal').showModal();
      } catch (err) {
        console.error('Error opening print modal:', err);
        Toast.error('Could not prepare Certificate of Family Composition.');
      }
    };

    // Open Delete Family Modal
    window.openDeleteModal = function(id) {
      const fam = allFamilies.find(f => f.id === id);
      if (!fam) return;

      activeFamilyForAction = fam;
      document.getElementById('delete-fam-name').textContent = fam.familyName || fam.family_name;
      document.getElementById('delete-fam-code').textContent = fam.familyCode || fam.family_code;
      document.getElementById('delete-modal').showModal();
    };

    // Bind Event Listeners
    function bindEventListeners() {
      document.getElementById('btn-open-family-modal').addEventListener('click', openFamilyModal);

      // Search & Filters
      document.getElementById('search-filter').addEventListener('input', applyFilters);
      document.getElementById('purok-filter').addEventListener('change', applyFilters);
      document.getElementById('poverty-filter').addEventListener('change', applyFilters);
      document.getElementById('fourps-filter').addEventListener('change', applyFilters);
      document.getElementById('type-filter').addEventListener('change', applyFilters);

      // Export CSV
      document.getElementById('btn-export-csv').addEventListener('click', () => {
        if (!allFamilies || allFamilies.length === 0) {
          Toast.warning('No family records to export.');
          return;
        }

        let csv = 'Family Code,Family Name,Head of Family,Purok,Dwelling,Type,Members,Monthly Income,Poverty Status,4Ps Beneficiary\n';
        allFamilies.forEach(f => {
          const row = [
            `"${f.familyCode || f.family_code || ''}"`,
            `"${(f.familyName || f.family_name || '').replace(/"/g, '""')}"`,
            `"${(f.headFullName || f.head_full_name || '').replace(/"/g, '""')}"`,
            `"${f.purok || ''}"`,
            `"${f.householdNo || f.household_no || 'None'}"`,
            `"${f.familyType || f.family_type || ''}"`,
            f.memberCount || f.member_count || 1,
            f.monthlyIncome || f.monthly_income || 0,
            `"${f.povertyStatus || f.poverty_status || ''}"`,
            (f.is4psBeneficiary || f.is_4ps_beneficiary) ? 'Yes' : 'No'
          ];
          csv += row.join(',') + '\n';
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `families_census_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        Toast.success('Exported families census to CSV.');
      });

      // Save Family Form Submit
      document.getElementById('family-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const headId = parseInt(document.getElementById('select-head-resident').value, 10);
        if (!headId) {
          Toast.warning('Please designate a Head of Family.');
          return;
        }

        const familyData = {
          family_code: document.getElementById('fam-control-no').value,
          family_name: document.getElementById('fam-name').value.trim(),
          head_resident_id: headId,
          household_id: document.getElementById('select-household').value ? parseInt(document.getElementById('select-household').value, 10) : null,
          purok: document.getElementById('fam-purok').value,
          family_type: document.getElementById('fam-type').value,
          housing_tenure: document.getElementById('fam-tenure').value,
          main_source_of_income: document.getElementById('fam-main-income').value,
          monthly_income: parseFloat(document.getElementById('fam-monthly-income').value) || 0,
          is_4ps_beneficiary: document.getElementById('fam-is-4ps').checked ? 1 : 0,
          four_ps_number: document.getElementById('fam-4ps-number').value.trim() || null,
          is_ayuda_priority: document.getElementById('fam-is-ayuda').checked ? 1 : 0,
          remarks: document.getElementById('fam-remarks').value.trim(),
          members: currentModalMembers.map(m => ({
            resident_id: m.residentId,
            relationship_to_head: m.relationship,
            is_income_earner: 0,
            monthly_income: 0
          }))
        };

        try {
          if (editingFamilyId) {
            familyData.id = editingFamilyId;
            await window.barangayDB.put('families', familyData);
            Toast.success('Family profile updated successfully.');
          } else {
            await window.barangayDB.add('families', familyData);
            Toast.success('New family enrolled successfully.');
          }

          document.getElementById('family-modal').close();
          await refreshFamiliesList();
        } catch (err) {
          console.error('Failed to save family profile:', err);
          Toast.error('Could not save family profile.');
        }
      });

      // Record Ayuda Form Submit
      document.getElementById('ayuda-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const familyId = parseInt(document.getElementById('ayuda-family-id').value, 10);
        if (!familyId) return;

        const ayudaData = {
          family_id: familyId,
          program_name: document.getElementById('ayuda-program').value.trim(),
          assistance_type: document.getElementById('ayuda-type').value,
          amount_value: parseFloat(document.getElementById('ayuda-amount').value) || 0,
          items_description: document.getElementById('ayuda-items').value.trim(),
          dafac_no: document.getElementById('ayuda-dafac').value.trim(),
          date_provided: document.getElementById('ayuda-date').value,
          disbursed_by: document.getElementById('ayuda-disbursed-by').value.trim() || 'Barangay Social Welfare Officer'
        };

        try {
          await window.barangayDB.add('family_assistance', ayudaData);
          Toast.success('Assistance grant recorded successfully.');
          document.getElementById('ayuda-modal').close();
          if (activeFamilyForAction && activeFamilyForAction.id === familyId) {
            await openDossierModal(familyId);
          }
          await refreshFamiliesList();
        } catch (err) {
          console.error('Failed to record ayuda grant:', err);
          Toast.error('Could not save assistance grant.');
        }
      });

      // Confirm Delete
      document.getElementById('btn-confirm-delete').addEventListener('click', async () => {
        if (!activeFamilyForAction) return;

        try {
          await window.barangayDB.delete('families', activeFamilyForAction.id);
          Toast.success(`Family ${activeFamilyForAction.familyName || activeFamilyForAction.family_name} deleted.`);
          document.getElementById('delete-modal').close();
          await refreshFamiliesList();
        } catch (err) {
          console.error('Failed to delete family:', err);
          Toast.error('Could not delete family record.');
        }
      });
    }
  </script>
</body>
</html>
