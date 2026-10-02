<?php
/**
 * Barangay Management System (BarangayOS)
 * Business Clearances & Local Permits Hub
 * Mandated by Republic Act 7160 (Local Government Code of 1991)
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_auth('login.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Business Clearances & Local Permits &bull; BarangayOS</title>
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

    /* Cards Grid View */
    .permits-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: var(--spacing-sm);
      margin-bottom: var(--spacing-lg);
    }

    .permit-card {
      background: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-lg);
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
      position: relative;
    }

    .permit-card:hover {
      border-color: var(--color-hairline);
      box-shadow: var(--shadow-sm);
    }

    .permit-card-header {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      margin-bottom: 12px;
    }

    .business-avatar {
      width: 44px;
      height: 44px;
      border-radius: var(--rounded-md);
      background: var(--color-field);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: var(--color-ink);
      flex-shrink: 0;
      border: 1px solid var(--color-hairline-soft);
    }

    .permit-badge-tag {
      font-size: 0.6875rem;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: var(--rounded-full);
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }

    .form-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--spacing-sm);
    }

    .form-grid-3 {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: var(--spacing-sm);
    }

    @media (max-width: 640px) {
      .form-grid-2, .form-grid-3 {
        grid-template-columns: 1fr;
      }
    }

    /* Print Document Styles */
    @media print {
      body * {
        visibility: hidden !important;
      }
      #print-permit-modal, #print-permit-modal * {
        visibility: visible !important;
      }
      #print-permit-modal {
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
      #printable-permit {
        box-shadow: none !important;
        padding: 15mm 20mm !important;
        width: 100% !important;
        max-width: 100% !important;
        background: #ffffff !important;
        color: #000000 !important;
      }
    }

    /* Fee Breakdown Box in Modal */
    .fee-calc-box {
      background: var(--color-field);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: 12px 16px;
      margin-top: 10px;
    }

    .fee-calc-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.8125rem;
      padding: 4px 0;
    }

    .fee-calc-row.total {
      border-top: 1px dashed var(--color-hairline);
      margin-top: 6px;
      padding-top: 8px;
      font-weight: 700;
      font-size: 0.9375rem;
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
                <h1 class="typography-heading-2">Business Permits & Licensing Hub</h1>
                <span class="badge-neutral" id="permit-count-badge">0 Registered</span>
                <span class="badge-neutral" style="font-size: 0.6875rem;">RA 7160 / BPLO</span>
              </div>
              <p class="typography-body-lg" style="max-width: 780px;">
                Mandatory barangay assessment, regulatory fee tiers, sanitation/safety inspections, and official issuance of Barangay Business Clearances for micro-enterprises and local establishments.
              </p>
            </div>
            <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
              <button class="button-primary" id="btn-open-permit-modal" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 6px;">
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Register Business</span>
              </button>
            </div>
          </div>
        </section>

        <!-- Stats Ladder -->
        <section class="stats-ladder" aria-label="Key Business Telemetry">
          <div class="card" style="padding: 16px;">
            <div class="typography-caption" style="margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.04em;">Commercial Establishments</div>
            <div class="typography-heading-2" id="stat-total-businesses" style="font-weight: 800;">0</div>
            <div class="typography-caption" style="color: var(--color-text-muted); margin-top: 4px;">Registered micro &amp; SMEs</div>
          </div>

          <div class="card" style="padding: 16px;">
            <div class="typography-caption" style="margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.04em;">Regulatory Clearance Issued</div>
            <div class="typography-heading-2" id="stat-issued-count" style="font-weight: 800; color: #10b981;">0</div>
            <div class="typography-caption" style="color: var(--color-text-muted); margin-top: 4px;">Active compliant permits</div>
          </div>

          <div class="card" style="padding: 16px;">
            <div class="typography-caption" style="margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.04em;">Pending Action / Inspection</div>
            <div class="typography-heading-2" id="stat-pending-inspection" style="font-weight: 800; color: #f59e0b;">0</div>
            <div class="typography-caption" style="color: var(--color-text-muted); margin-top: 4px;">Awaiting review or inspection</div>
          </div>

          <div class="card" style="padding: 16px;">
            <div class="typography-caption" style="margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.04em;">Total Fees Collected</div>
            <div class="typography-heading-2" id="stat-total-revenue" style="font-weight: 800;">₱0.00</div>
            <div class="typography-caption" style="color: var(--color-text-muted); margin-top: 4px;">Synced to General Fund</div>
          </div>
        </section>

        <!-- Filter Toolbar -->
        <section class="filter-toolbar" aria-label="Filters and Search">
          <div class="filter-group">
            <div class="search-input-wrap">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
              <input type="text" id="search-permits" class="text-input" placeholder="Search business, owner, clearance or plate #...">
            </div>

            <select id="filter-nature" class="filter-select">
              <option value="">All Business Natures</option>
              <option value="Retail / Sari-Sari Store">Retail / Sari-Sari Store</option>
              <option value="Eatery / Carenderia / Food Stall">Eatery / Carenderia / Food Stall</option>
              <option value="Service / Repair Shop">Service / Repair Shop</option>
              <option value="Personal Care (Salon / Barber)">Personal Care (Salon / Barber)</option>
              <option value="Wholesale / Grocery / Trading">Wholesale / Grocery / Trading</option>
              <option value="Transport / Tricycle / Pedicab">Transport / Tricycle / Pedicab</option>
              <option value="Real Estate / Rental / Boarding">Real Estate / Rental / Boarding</option>
              <option value="Bakery / Light Manufacturing">Bakery / Light Manufacturing</option>
              <option value="Professional Services / Clinic">Professional Services / Clinic</option>
              <option value="Others">Others</option>
            </select>

            <select id="filter-purok" class="filter-select">
              <option value="">All Puroks</option>
              <option value="Purok 1">Purok 1</option>
              <option value="Purok 2">Purok 2</option>
              <option value="Purok 3">Purok 3</option>
              <option value="Purok 4">Purok 4</option>
              <option value="Purok 5">Purok 5</option>
              <option value="Purok 6">Purok 6</option>
              <option value="Purok 7">Purok 7</option>
            </select>

            <select id="filter-status" class="filter-select">
              <option value="">All Clearance Status</option>
              <option value="Pending Review">Pending Review</option>
              <option value="Under Inspection">Under Inspection</option>
              <option value="Approved & Issued">Approved &amp; Issued</option>
              <option value="Expired">Expired</option>
              <option value="Revoked">Revoked</option>
            </select>

            <select id="filter-payment" class="filter-select">
              <option value="">All Payment Status</option>
              <option value="Paid">Paid</option>
              <option value="Unpaid">Unpaid</option>
              <option value="Exempt">Exempt</option>
            </select>
          </div>

          <div class="filter-group">
            <div style="display: flex; border: 1px solid var(--color-hairline-soft); border-radius: var(--rounded-md); overflow: hidden;">
              <button id="btn-view-cards" class="button-outline" style="border: none; border-radius: 0; height: 34px; padding: 0 10px; background: var(--color-field);" title="Cards Grid">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
              </button>
              <button id="btn-view-table" class="button-outline" style="border: none; border-radius: 0; height: 34px; padding: 0 10px;" title="Table View">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
              </button>
            </div>
          </div>
        </section>

        <!-- Main Display: Cards View -->
        <section id="permits-grid-view">
          <div class="permits-grid" id="permits-cards-container">
            <!-- Dynamic cards injected via JS -->
          </div>
        </section>

        <!-- Alternative: Table View (Hidden by default) -->
        <section id="permits-table-view" style="display: none; margin-bottom: var(--spacing-lg);">
          <div class="card" style="padding: 0; overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr>
                  <th>Clearance / Plate</th>
                  <th>Business &amp; Trade Name</th>
                  <th>Owner / Proprietor</th>
                  <th>Nature &amp; Purok</th>
                  <th>Sales Tier</th>
                  <th>Assessment Fee</th>
                  <th>Payment</th>
                  <th>Inspection</th>
                  <th>Status</th>
                  <th style="text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody id="permits-table-tbody">
                <!-- Dynamic table rows -->
              </tbody>
            </table>
          </div>
        </section>

        <!-- Empty State View -->
        <div id="permits-empty-state" class="card" style="display: none; text-align: center; padding: 48px 24px; margin-bottom: var(--spacing-lg);">
          <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--color-field); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-text-muted);">
              <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/>
              <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
              <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/>
              <path d="M2 7h20"/>
            </svg>
          </div>
          <h3 class="typography-heading-4" style="margin-bottom: 6px;">No business clearances found</h3>
          <p class="typography-body" style="color: var(--color-text-muted); max-width: 440px; margin: 0 auto 18px;">
            No commercial clearances match your filter criteria. Register a new business establishment to commence regulatory processing.
          </p>
          <button class="button-primary" onclick="openCreatePermitModal()">
            + Register Business Establishment
          </button>
        </div>
      </main>
    </div>
  </div>

  <!-- ===================================================== -->
  <!-- MODAL 1: REGISTER / EDIT BUSINESS CLEARANCE           -->
  <!-- ===================================================== -->
  <dialog id="permit-modal" class="modal-dialog" style="max-width: 760px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: var(--spacing-xs);">
      <div>
        <h3 class="typography-heading-4" id="permit-modal-title">Register Business Clearance</h3>
        <p class="typography-caption" style="color: var(--color-text-muted); margin-top: 2px;">
          Barangay evaluation for local Mayor's Permit compliance (RA 7160 Sec. 152)
        </p>
      </div>
      <button type="button" class="button-outline" onclick="document.getElementById('permit-modal').close();" style="height: 32px; padding: 0 10px;">
        &times;
      </button>
    </div>

    <!-- Wizard Section Navigation Tabs -->
    <div class="modal-section-tabs" id="permit-wizard-tabs">
      <button type="button" class="section-tab-btn active" data-step="1" onclick="switchPermitStep(1)">
        <span class="step-num">1</span>
        <span class="step-title">1. Establishment &amp; Owner</span>
      </button>
      <button type="button" class="section-tab-btn" data-step="2" onclick="switchPermitStep(2)">
        <span class="step-num">2</span>
        <span class="step-title">2. Classification &amp; Location</span>
      </button>
      <button type="button" class="section-tab-btn" data-step="3" onclick="switchPermitStep(3)">
        <span class="step-num">3</span>
        <span class="step-title">3. Assessment Fees &amp; Remarks</span>
      </button>
    </div>

    <form id="permit-form">
      <input type="hidden" id="permit-id" value="">

      <!-- Step 1: Establishment & Proprietor -->
      <div class="modal-step-pane active" id="permit-step-1">
        <div style="margin-bottom: var(--spacing-xs);">
          <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem; text-transform: uppercase;">
            1. Commercial Entity &amp; Proprietor Details
          </span>
        </div>

        <div class="form-grid-2 mt-xs">
          <div class="form-group">
            <label class="form-label" for="permit-business-name">Business / Store Name <span style="color: #ef4444;">*</span></label>
            <input type="text" id="permit-business-name" class="text-input" placeholder="e.g. Aling Nena Variety &amp; Sari-Sari Store" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="permit-trade-name">Trade / Signboard Name</label>
            <input type="text" id="permit-trade-name" class="text-input" placeholder="e.g. Nena Store (if different)">
          </div>
        </div>

        <div class="form-grid-2 mt-xs">
          <div class="form-group">
            <label class="form-label" for="permit-resident-select">Registered Resident Proprietor</label>
            <select id="permit-resident-select" class="filter-select" style="width: 100%; height: 42px;">
              <option value="">-- Non-Resident / External Proprietor --</option>
            </select>
            <span class="typography-caption" style="color: var(--color-text-muted); font-size: 0.6875rem;">Selecting a resident will auto-populate owner details</span>
          </div>
          <div class="form-group">
            <label class="form-label" for="permit-owner-name">Owner / Proprietor Full Name <span style="color: #ef4444;">*</span></label>
            <input type="text" id="permit-owner-name" class="text-input" placeholder="Juan Dela Cruz" required>
          </div>
        </div>

        <div class="form-grid-2 mt-xs">
          <div class="form-group">
            <label class="form-label" for="permit-owner-contact">Proprietor Contact #</label>
            <input type="text" id="permit-owner-contact" class="text-input" placeholder="0917-000-0000">
          </div>
          <div class="form-group">
            <label class="form-label" for="permit-owner-address">Owner Home Address</label>
            <input type="text" id="permit-owner-address" class="text-input" placeholder="Block &amp; Lot, Street, Purok">
          </div>
        </div>
      </div>

      <!-- Step 2: Classification & Location -->
      <div class="modal-step-pane" id="permit-step-2">
        <div style="margin-bottom: var(--spacing-xs);">
          <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem; text-transform: uppercase;">
            2. Business Activity &amp; Physical Location
          </span>
        </div>

        <div class="form-grid-3 mt-xs">
          <div class="form-group">
            <label class="form-label" for="permit-business-nature">Business Nature <span style="color: #ef4444;">*</span></label>
            <select id="permit-business-nature" class="filter-select" style="width: 100%; height: 42px;" required>
              <option value="Retail / Sari-Sari Store">Retail / Sari-Sari Store</option>
              <option value="Eatery / Carenderia / Food Stall">Eatery / Carenderia / Food Stall</option>
              <option value="Service / Repair Shop">Service / Repair Shop</option>
              <option value="Personal Care (Salon / Barber)">Personal Care (Salon / Barber)</option>
              <option value="Wholesale / Grocery / Trading">Wholesale / Grocery / Trading</option>
              <option value="Transport / Tricycle / Pedicab">Transport / Tricycle / Pedicab</option>
              <option value="Real Estate / Rental / Boarding">Real Estate / Rental / Boarding</option>
              <option value="Bakery / Light Manufacturing">Bakery / Light Manufacturing</option>
              <option value="Professional Services / Clinic">Professional Services / Clinic</option>
              <option value="Others">Others</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="permit-ownership-type">Ownership Type</label>
            <select id="permit-ownership-type" class="filter-select" style="width: 100%; height: 42px;">
              <option value="Sole Proprietorship">Sole Proprietorship</option>
              <option value="Partnership">Partnership</option>
              <option value="Corporation">Corporation</option>
              <option value="Cooperative">Cooperative</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="permit-purok">Barangay Purok <span style="color: #ef4444;">*</span></label>
            <select id="permit-purok" class="filter-select" style="width: 100%; height: 42px;" required>
              <option value="Purok 1">Purok 1</option>
              <option value="Purok 2">Purok 2</option>
              <option value="Purok 3">Purok 3</option>
              <option value="Purok 4">Purok 4</option>
              <option value="Purok 5">Purok 5</option>
              <option value="Purok 6">Purok 6</option>
              <option value="Purok 7">Purok 7</option>
            </select>
          </div>
        </div>

        <div class="form-group mt-xs">
          <label class="form-label" for="permit-business-address">Establishment Physical Address <span style="color: #ef4444;">*</span></label>
          <input type="text" id="permit-business-address" class="text-input" placeholder="Unit / Door No., Street, Purok, Barangay San Isidro" required>
        </div>
      </div>

      <!-- Step 3: Assessment Fees & Remarks -->
      <div class="modal-step-pane" id="permit-step-3">
        <div style="margin-bottom: var(--spacing-xs);">
          <span class="typography-label" style="color: var(--color-primary); font-size: 0.6875rem; text-transform: uppercase;">
            3. Regulatory Fee Calculation &amp; Authorization
          </span>
        </div>

        <div class="form-grid-3 mt-xs">
          <div class="form-group">
            <label class="form-label" for="permit-capital">Capital Investment (PHP)</label>
            <input type="number" step="0.01" id="permit-capital" class="text-input" placeholder="50000.00" value="50000.00">
          </div>

          <div class="form-group">
            <label class="form-label" for="permit-tier">Gross Sales Tier</label>
            <select id="permit-tier" class="filter-select" style="width: 100%; height: 42px;">
              <option value="Micro (Below ₱150,000)">Micro (Below ₱150,000)</option>
              <option value="Small (₱150,001 - ₱1,500,000)">Small (₱150,001 - ₱1,500,000)</option>
              <option value="Medium (₱1,500,001 - ₱5,000,000)">Medium (₱1,500,001 - ₱5,000,000)</option>
              <option value="Large (Above ₱5,000,000)">Large (Above ₱5,000,000)</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="permit-application-type">Application Type</label>
            <select id="permit-application-type" class="filter-select" style="width: 100%; height: 42px;">
              <option value="New">New Application</option>
              <option value="Renewal">Annual Renewal</option>
              <option value="Change of Location">Change of Location</option>
              <option value="Retirement / Closure">Retirement / Closure</option>
            </select>
          </div>
        </div>

        <!-- Dynamic Fee Breakdown Calculator -->
        <div class="fee-calc-box mt-xs">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-weight: 700; font-size: 0.8125rem; text-transform: uppercase; color: var(--color-ink);">Local Regulatory Assessment Fee Schedule</span>
            <span class="badge-neutral" style="font-size: 0.625rem;">Barangay Revenue Code</span>
          </div>
          <div class="form-grid-3">
            <div>
              <label class="typography-caption">Clearance Fee (₱)</label>
              <input type="number" step="0.01" id="permit-clearance-fee" class="text-input" style="height: 32px;" value="300.00">
            </div>
            <div>
              <label class="typography-caption">Solid Waste Fee (₱)</label>
              <input type="number" step="0.01" id="permit-garbage-fee" class="text-input" style="height: 32px;" value="150.00">
            </div>
            <div>
              <label class="typography-caption">Inspection Fee (₱)</label>
              <input type="number" step="0.01" id="permit-inspection-fee" class="text-input" style="height: 32px;" value="100.00">
            </div>
          </div>
          <div class="fee-calc-row total">
            <span>Total Regulatory Assessment:</span>
            <span id="permit-calc-total" style="font-size: 1.125rem; color: var(--color-ink);">₱550.00</span>
          </div>
        </div>

        <div class="form-group mt-xs" style="margin-bottom: 0;">
          <label class="form-label" for="permit-remarks">Administrative Remarks / Notes</label>
          <textarea id="permit-remarks" class="text-input" style="height: 52px; padding: 6px 10px; resize: none;" placeholder="Optional inspector guidance or notes"></textarea>
        </div>
      </div>

      <!-- Wizard Actions Footer -->
      <div class="modal-wizard-footer">
        <button type="button" class="button-outline" onclick="document.getElementById('permit-modal').close();" style="height: 42px; padding: 0 18px;">Cancel</button>
        <div style="display: flex; gap: var(--spacing-xs); align-items: center;">
          <button type="button" class="button-outline" id="btn-permit-prev" onclick="prevPermitStep()" style="height: 42px; padding: 0 16px; display: none;">&larr; Back</button>
          <button type="button" class="button-outline" id="btn-permit-next" onclick="nextPermitStep()" style="height: 42px; padding: 0 16px;">Next &rarr;</button>
          <button type="submit" class="button-primary" id="btn-save-permit" style="height: 42px; padding: 0 22px;">Register &amp; Save</button>
        </div>
      </div>
    </form>
  </dialog>

  <!-- ===================================================== -->
  <!-- MODAL 2: COMPLETE BUSINESS DOSSIER & COMPLIANCE METER -->
  <!-- ===================================================== -->
  <dialog id="dossier-modal" class="modal-dialog" style="max-width: 640px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--color-hairline); padding-bottom: 12px;">
      <div>
        <h3 class="typography-heading-4" id="dossier-business-title">Establishment Dossier</h3>
        <span class="typography-caption" id="dossier-clearance-no" style="font-family: monospace; color: var(--color-text-muted);">BBC-2026-00000</span>
      </div>
      <button type="button" class="button-outline" onclick="document.getElementById('dossier-modal').close();" style="height: 32px; padding: 0 10px;">
        &times;
      </button>
    </div>

    <div id="dossier-content" style="font-size: 0.875rem;">
      <!-- Populated dynamically via JS -->
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--color-hairline); padding-top: 12px; margin-top: 16px;">
      <div id="dossier-action-left"></div>
      <div style="display: flex; gap: 8px;">
        <button type="button" class="button-outline" onclick="document.getElementById('dossier-modal').close();">Close</button>
        <button type="button" class="button-primary" id="dossier-print-btn">Print Clearance</button>
      </div>
    </div>
  </dialog>

  <!-- ===================================================== -->
  <!-- MODAL 3: RECORD OFFICIAL RECEIPT (O.R.) PAYMENT      -->
  <!-- ===================================================== -->
  <dialog id="payment-modal" class="modal-dialog" style="max-width: 480px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--color-hairline); padding-bottom: 10px;">
      <h3 class="typography-heading-4">Record Clearance Payment</h3>
      <button type="button" class="button-outline" onclick="document.getElementById('payment-modal').close();" style="height: 30px; padding: 0 8px;">&times;</button>
    </div>

    <form id="payment-form">
      <input type="hidden" id="pay-permit-id">

      <div style="background: var(--color-field); padding: 12px; border-radius: var(--rounded-md); margin-bottom: 12px;">
        <div class="typography-caption" style="color: var(--color-text-muted);">Payor / Establishment</div>
        <div id="pay-business-name" style="font-weight: 700; color: var(--color-ink); margin-bottom: 4px;">Store Name</div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; border-top: 1px dashed var(--color-hairline); padding-top: 8px;">
          <span style="font-size: 0.8125rem;">Assessed Clearance Total:</span>
          <span id="pay-amount-due" style="font-size: 1.125rem; font-weight: 800; color: var(--color-ink);">₱550.00</span>
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 10px;">
        <label class="form-label" for="pay-or-number">Official Receipt (O.R.) Number</label>
        <div style="display: flex; gap: 6px;">
          <input type="text" id="pay-or-number" class="text-input" placeholder="OR-2026-0001" required>
          <button type="button" class="button-outline" onclick="generateAutoOrNumber()" style="font-size: 0.75rem; white-space: nowrap;">Auto #</button>
        </div>
      </div>

      <div class="form-grid-2" style="margin-bottom: 10px;">
        <div class="form-group">
          <label class="form-label" for="pay-date">Payment Date</label>
          <input type="date" id="pay-date" class="text-input" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="pay-status">Payment Status</label>
          <select id="pay-status" class="filter-select" style="width: 100%;">
            <option value="Paid">Paid (Full)</option>
            <option value="Exempt">Exempt / Waived</option>
          </select>
        </div>
      </div>

      <div class="typography-caption" style="color: var(--color-text-muted); margin-bottom: 16px; line-height: 1.4;">
        &bull; Marking this clearance paid will automatically insert an official collection record into the <strong>General Fund</strong> under <strong>Business Permits</strong> in the Budget &amp; Revenue ledger.
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 8px;">
        <button type="button" class="button-outline" onclick="document.getElementById('payment-modal').close();">Cancel</button>
        <button type="submit" class="button-primary">Confirm &amp; Record Payment</button>
      </div>
    </form>
  </dialog>

  <!-- ===================================================== -->
  <!-- MODAL 4: CONDUCT REGULATORY INSPECTION                -->
  <!-- ===================================================== -->
  <dialog id="inspection-modal" class="modal-dialog" style="max-width: 520px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--color-hairline); padding-bottom: 10px;">
      <h3 class="typography-heading-4">Conduct Regulatory Inspection</h3>
      <button type="button" class="button-outline" onclick="document.getElementById('inspection-modal').close();" style="height: 30px; padding: 0 8px;">&times;</button>
    </div>

    <form id="inspection-form">
      <input type="hidden" id="insp-permit-id">

      <div style="margin-bottom: 12px;">
        <div class="typography-caption" style="color: var(--color-text-muted);">Target Establishment</div>
        <div id="insp-business-name" style="font-weight: 700; color: var(--color-ink);">Store Name</div>
        <div id="insp-business-address" class="typography-caption" style="color: var(--color-text-muted);">Purok Address</div>
      </div>

      <!-- Checklist -->
      <div style="background: var(--color-field); padding: 12px; border-radius: var(--rounded-md); margin-bottom: 12px;">
        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; color: var(--color-ink);">
          Barangay Compliance Checklist
        </div>
        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; margin-bottom: 6px; cursor: pointer;">
          <input type="checkbox" id="chk-fire" checked> Fire safety extinguisher available &amp; unexpired
        </label>
        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; margin-bottom: 6px; cursor: pointer;">
          <input type="checkbox" id="chk-sanitation" checked> Sanitary conditions &amp; waste disposal compliant
        </label>
        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; margin-bottom: 6px; cursor: pointer;">
          <input type="checkbox" id="chk-drainage" checked> Proper grease trap or drainage connection
        </label>
        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; cursor: pointer;">
          <input type="checkbox" id="chk-obstruction" checked> No sidewalk obstruction / clear pathway
        </label>
      </div>

      <div class="form-grid-2" style="margin-bottom: 10px;">
        <div class="form-group">
          <label class="form-label" for="insp-status">Inspection Result <span style="color: #ef4444;">*</span></label>
          <select id="insp-status" class="filter-select" style="width: 100%;">
            <option value="Compliant">Compliant (Passed)</option>
            <option value="Deficient">Deficient (Needs Remediation)</option>
            <option value="Pending Inspection">Pending Re-Inspection</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="insp-date">Inspection Date</label>
          <input type="date" id="insp-date" class="text-input" required>
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 10px;">
        <label class="form-label" for="insp-inspector">Inspecting Officer / Tanod</label>
        <input type="text" id="insp-inspector" class="text-input" value="Tanod Insp. Roberto Diaz" required>
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label class="form-label" for="insp-notes">Inspector Findings / Notes</label>
        <textarea id="insp-notes" class="text-input" rows="2" placeholder="Clean premises, valid fire extinguisher, compliant waste management."></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 8px;">
        <button type="button" class="button-outline" onclick="document.getElementById('inspection-modal').close();">Cancel</button>
        <button type="submit" class="button-primary">Save Inspection Report</button>
      </div>
    </form>
  </dialog>

  <!-- ===================================================== -->
  <!-- MODAL 5: OFFICIAL PRINTABLE BARANGAY BUSINESS PERMIT  -->
  <!-- ===================================================== -->
  <dialog id="print-permit-modal" class="modal-dialog" style="max-width: 820px; width: 95%;">
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--color-hairline); padding-bottom: 8px;">
      <h3 class="typography-heading-4">Print Official Barangay Business Clearance</h3>
      <div style="display: flex; gap: 8px;">
        <button type="button" class="button-primary" onclick="window.print();" style="height: 34px; padding: 0 16px; font-size: 0.8125rem;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
          <span>Print Clearance</span>
        </button>
        <button type="button" class="button-outline" onclick="document.getElementById('print-permit-modal').close();" style="height: 34px; padding: 0 12px;">
          Close
        </button>
      </div>
    </div>

    <!-- Official Document Container -->
    <div id="printable-permit" style="background: #ffffff; color: #111827; padding: 24px 32px; font-family: 'Times New Roman', Times, serif; line-height: 1.5; border: 2px solid #1e3a8a; border-radius: 4px; position: relative;">
      
      <!-- Official Republic Header -->
      <div style="text-align: center; border-bottom: 2px double #1e3a8a; padding-bottom: 12px; margin-bottom: 20px;">
        <div style="font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.08em; font-family: Arial, sans-serif;">Republic of the Philippines</div>
        <div style="font-size: 0.875rem; text-transform: uppercase; font-family: Arial, sans-serif;" id="print-jurisdiction">Province of Rizal &bull; Municipality of Taytay</div>
        <div style="font-size: 1.375rem; font-weight: 900; text-transform: uppercase; margin: 4px 0; color: #1e3a8a; font-family: Arial, sans-serif;" id="print-brgy-name">BARANGAY SAN ISIDRO</div>
        <div style="font-size: 0.8125rem; font-weight: 700; letter-spacing: 0.1em; color: #4b5563; font-family: Arial, sans-serif;">
          OFFICE OF THE PUNONG BARANGAY &bull; BUSINESS PERMITS &amp; LICENSING SECTION
        </div>
      </div>

      <!-- Control Header -->
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-family: Arial, sans-serif; font-size: 0.8125rem;">
        <div>
          CONTROL NO: <strong id="print-ctrl-no" style="font-family: monospace; font-size: 0.9375rem;">BBC-2026-00001</strong>
        </div>
        <div>
          PLATE / STICKER NO: <strong id="print-plate-no" style="font-family: monospace; font-size: 0.9375rem;">BP-2026-0001</strong>
        </div>
      </div>

      <!-- Clearance Title -->
      <div style="text-align: center; margin: 24px 0 24px;">
        <h2 style="font-size: 1.625rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #1e3a8a; margin: 0 0 4px; font-family: Arial, sans-serif;">
          BARANGAY BUSINESS CLEARANCE
        </h2>
        <div style="font-size: 0.875rem; font-style: italic; color: #4b5563;">(Pursuant to Section 152-C of Republic Act No. 7160 / Local Government Code)</div>
      </div>

      <!-- Body / Attestation -->
      <div style="font-size: 1rem; line-height: 1.8; text-align: justify; margin-bottom: 20px;">
        <p style="margin-bottom: 14px;">
          <strong>TO WHOM IT MAY CONCERN:</strong>
        </p>
        <p style="text-indent: 40px; margin-bottom: 14px;">
          THIS IS TO CERTIFY that the commercial enterprise / business establishment described below has been duly inspected, evaluated, and granted <strong>BARANGAY BUSINESS CLEARANCE</strong> to operate within the territorial jurisdiction of this Barangay for the fiscal calendar year <strong id="print-validity-year">2026</strong>:
        </p>

        <!-- Establishment Details Box -->
        <table style="width: 100%; border-collapse: collapse; margin: 16px 0; font-family: Arial, sans-serif; font-size: 0.875rem; border: 1px solid #d1d5db;">
          <tr style="background: #f9fafb;">
            <td style="padding: 8px 12px; width: 30%; border: 1px solid #d1d5db; font-weight: bold;">Business Name:</td>
            <td style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: bold; font-size: 1rem; color: #1e3a8a;" id="print-biz-name">Nena Variety Store</td>
          </tr>
          <tr>
            <td style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: bold;">Trade / Signboard Name:</td>
            <td style="padding: 8px 12px; border: 1px solid #d1d5db;" id="print-trade-name">Nena Store</td>
          </tr>
          <tr style="background: #f9fafb;">
            <td style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: bold;">Proprietor / Operator:</td>
            <td style="padding: 8px 12px; border: 1px solid #d1d5db;" id="print-owner-name">Rosa De Castro</td>
          </tr>
          <tr>
            <td style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: bold;">Nature of Business:</td>
            <td style="padding: 8px 12px; border: 1px solid #d1d5db;" id="print-nature">Retail / Sari-Sari Store</td>
          </tr>
          <tr style="background: #f9fafb;">
            <td style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: bold;">Establishment Address:</td>
            <td style="padding: 8px 12px; border: 1px solid #d1d5db;" id="print-address">Block 4 Lot 12, San Isidro Main Road, Purok 1</td>
          </tr>
          <tr>
            <td style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: bold;">Ownership / Capital:</td>
            <td style="padding: 8px 12px; border: 1px solid #d1d5db;" id="print-ownership-capital">Sole Proprietorship &bull; Capital: ₱45,000.00</td>
          </tr>
        </table>

        <p style="text-indent: 40px; margin-bottom: 14px;">
          This clearance is issued as a prerequisite for the issuance of the <strong>Mayor's Business Permit / Municipal License</strong>, subject to regular post-audit inspections, continuous observance of environmental sanitation, fire safety ordinances, and existing barangay tax codes.
        </p>

        <p style="text-indent: 40px; margin-bottom: 24px;">
          Given this <strong id="print-day">2nd</strong> day of <strong id="print-month-year">October 2026</strong> at Barangay San Isidro, Taytay, Rizal, Philippines.
        </p>
      </div>

      <!-- Regulatory Fee & Security QR Footer -->
      <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 20px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-family: Arial, sans-serif;">
        <div style="font-size: 0.75rem; color: #4b5563; line-height: 1.6;">
          <div>Official Receipt No: <strong id="print-or-no">OR-2026-0101</strong></div>
          <div>Clearance Fee: <span id="print-fee-clearance">₱300.00</span> | Garbage Fee: <span id="print-fee-garbage">₱150.00</span> | Insp: <span id="print-fee-insp">₱100.00</span></div>
          <div>Total Regulatory Assessment Paid: <strong id="print-fee-total" style="font-size: 0.875rem; color: #111827;">₱550.00</strong></div>
          <div>Validity: <strong style="color: #10b981;">Valid until December 31, <span id="print-validity-term">2026</span></strong></div>
        </div>

        <div style="text-align: center;">
          <div id="print-qr-code" style="width: 80px; height: 80px; margin: 0 auto 4px; display: flex; align-items: center; justify-content: center; background: #ffffff;"></div>
          <div style="font-size: 0.5625rem; font-family: monospace; color: #6b7280;">SCAN FOR VERIFICATION</div>
        </div>
      </div>

      <!-- Signatures -->
      <div style="display: flex; justify-content: space-between; margin-top: 36px; padding-top: 16px; font-family: Arial, sans-serif;">
        <div style="text-align: center; width: 42%;">
          <div style="font-size: 0.8125rem; color: #6b7280; margin-bottom: 32px;">Assessed &amp; Certified by:</div>
          <div style="font-weight: 700; border-top: 1px solid #111827; padding-top: 4px; font-size: 0.9375rem;" id="print-treasurer">MARIA SANTOS</div>
          <div style="font-size: 0.75rem; color: #4b5563;">Barangay Treasurer</div>
        </div>

        <div style="text-align: center; width: 42%;">
          <div style="font-size: 0.8125rem; color: #6b7280; margin-bottom: 32px;">Approved &amp; Issued by:</div>
          <div style="font-weight: 700; border-top: 1px solid #111827; padding-top: 4px; font-size: 0.9375rem;" id="print-punong-barangay">HON. ANTONIO S. VALDEZ</div>
          <div style="font-size: 0.75rem; color: #4b5563;">Punong Barangay</div>
        </div>
      </div>

      <!-- Micro Disclaimer -->
      <div style="margin-top: 24px; font-size: 0.625rem; color: #9ca3af; text-align: center; font-family: Arial, sans-serif;">
        NOTICE: This clearance must be displayed prominently at the business establishment together with the official sticker plate. Any alteration or erasure invalidates this certificate.
      </div>
    </div>
  </dialog>

  <!-- Scripts -->
  <script src="js/db.js"></script>
  <script src="js/api.js"></script>
  <script src="js/auth.js"></script>
  <script src="js/components/toast.js"></script>
  <script src="js/components/sidebar.js"></script>
  <script src="js/lib/qrcode.js"></script>

  <script>
    let allClearances = [];
    let allResidentsMap = new Map();
    let currentAuthUser = null;
    let editingPermitId = null;
    let activePermitForAction = null;
    let currentViewMode = 'cards'; // 'cards' or 'table'

    document.addEventListener('DOMContentLoaded', async () => {
      // 1. Guard route: bypassable / persistent session
      const auth = await authService.requireAuth('login.php');
      currentAuthUser = auth ? auth.user : null;

      // 2. Render App Shell Sidebar & Topbar
      await AppSidebar.render('permits');

      // 3. Load Settings for Letterhead & Signatories
      await loadBarangayMeta();

      // 4. Load Residents into memory for quick linking
      await loadResidents();

      // 5. Query Clearances from DB / REST API
      await refreshClearancesList();

      // 6. Bind UI Event Listeners
      bindEventListeners();
    });

    // Load Barangay Meta from Settings
    async function loadBarangayMeta() {
      try {
        const idSetting = await window.barangayDB.getSetting('identity');
        if (idSetting) {
          if (idSetting.barangayName) {
            document.getElementById('print-brgy-name').textContent = idSetting.barangayName.toUpperCase();
          }
          if (idSetting.province && idSetting.municipalityCity) {
            document.getElementById('print-jurisdiction').textContent = `Province of ${idSetting.province} • Municipality of ${idSetting.municipalityCity}`;
          }
        }
      } catch (err) {
        console.warn('Could not load barangay meta:', err);
      }
    }

    // Load Residents
    async function loadResidents() {
      try {
        const residents = await window.barangayDB.getAll('residents');
        const selectEl = document.getElementById('permit-resident-select');
        selectEl.innerHTML = '<option value="">-- Non-Resident / External Proprietor --</option>';

        residents.forEach(res => {
          allResidentsMap.set(res.id, res);
          const fullName = [res.firstName || res.first_name, res.lastName || res.last_name].filter(Boolean).join(' ');
          const opt = document.createElement('option');
          opt.value = res.id;
          opt.textContent = `${fullName} (${res.purok || 'Resident'})`;
          selectEl.appendChild(opt);
        });
      } catch (err) {
        console.warn('Error loading residents:', err);
      }
    }

    // Refresh Business Clearances List
    async function refreshClearancesList() {
      try {
        allClearances = await window.barangayDB.getAll('business_clearances');
        applyFiltersAndRender();
        updateTelemetry();
      } catch (err) {
        console.error('Failed to fetch business clearances:', err);
        Toast.error('Could not load business clearances.');
      }
    }

    // Compute Telemetry Stats
    function updateTelemetry() {
      const total = allClearances.length;
      const issued = allClearances.filter(c => c.status === 'Approved & Issued').length;
      const pending = allClearances.filter(c => c.status === 'Pending Review' || c.status === 'Under Inspection' || c.inspectionStatus === 'Pending Inspection').length;
      const totalRev = allClearances
        .filter(c => c.paymentStatus === 'Paid')
        .reduce((sum, c) => sum + (parseFloat(c.totalFee) || 0), 0);

      document.getElementById('permit-count-badge').textContent = `${total} Registered`;
      document.getElementById('stat-total-businesses').textContent = total;
      document.getElementById('stat-issued-count').textContent = issued;
      document.getElementById('stat-pending-inspection').textContent = pending;
      document.getElementById('stat-total-revenue').textContent = `₱${totalRev.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    // Filter & Render
    function applyFiltersAndRender() {
      const query = (document.getElementById('search-permits').value || '').trim().toLowerCase();
      const nature = document.getElementById('filter-nature').value;
      const purok = document.getElementById('filter-purok').value;
      const status = document.getElementById('filter-status').value;
      const payment = document.getElementById('filter-payment').value;

      const filtered = allClearances.filter(c => {
        if (nature && c.businessNature !== nature) return false;
        if (purok && c.purok !== purok) return false;
        if (status && c.status !== status) return false;
        if (payment && c.paymentStatus !== payment) return false;

        if (query) {
          const matchStr = `${c.businessName} ${c.tradeName} ${c.ownerName} ${c.clearanceNo} ${c.plateStickerNo || ''} ${c.businessAddress}`.toLowerCase();
          if (!matchStr.includes(query)) return false;
        }

        return true;
      });

      renderClearances(filtered);
    }

    // Render Cards & Table
    function renderClearances(list) {
      const cardsContainer = document.getElementById('permits-cards-container');
      const tableTbody = document.getElementById('permits-table-tbody');
      const emptyState = document.getElementById('permits-empty-state');
      const cardsView = document.getElementById('permits-grid-view');
      const tableView = document.getElementById('permits-table-view');

      if (!list || list.length === 0) {
        cardsContainer.innerHTML = '';
        tableTbody.innerHTML = '';
        emptyState.style.display = 'block';
        return;
      }
      emptyState.style.display = 'none';

      // 1. Render Cards
      cardsContainer.innerHTML = list.map(c => {
        const initials = c.businessName ? c.businessName.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase() : 'BC';
        
        let statusBadge = 'badge-neutral';
        if (c.status === 'Approved & Issued') statusBadge = 'badge-emerald';
        else if (c.status === 'Under Inspection') statusBadge = 'badge-blue';
        else if (c.status === 'Pending Review') statusBadge = 'badge-amber';
        else if (c.status === 'Revoked') statusBadge = 'badge-rose';

        const paymentBadge = c.paymentStatus === 'Paid' ? '<span class="badge-neutral" style="color: #10b981; border-color: rgba(16,185,129,0.3); font-size: 0.6875rem;">PAID</span>' : '<span class="badge-neutral" style="color: #f59e0b; border-color: rgba(245,158,11,0.3); font-size: 0.6875rem;">UNPAID</span>';
        const inspBadge = c.inspectionStatus === 'Compliant' ? '<span class="badge-neutral" style="color: #10b981; font-size: 0.6875rem;">Compliant</span>' : '<span class="badge-neutral" style="color: #f59e0b; font-size: 0.6875rem;">' + (c.inspectionStatus || 'Pending Insp') + '</span>';

        return `
          <div class="permit-card">
            <div>
              <div class="permit-card-header">
                <div class="business-avatar">${initials}</div>
                <div style="flex: 1; min-width: 0;">
                  <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 4px;">
                    <span style="font-size: 0.6875rem; font-family: monospace; color: var(--color-text-muted);">${c.clearanceNo}</span>
                    <span class="${statusBadge} permit-badge-tag">${c.status}</span>
                  </div>
                  <h4 style="font-size: 0.9375rem; font-weight: 700; color: var(--color-ink); margin: 2px 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${c.businessName}">
                    ${c.businessName}
                  </h4>
                  <div class="typography-caption" style="color: var(--color-text-muted);">${c.businessNature} &bull; ${c.purok}</div>
                </div>
              </div>

              <div style="background: var(--color-field); border-radius: var(--rounded-md); padding: 8px 12px; margin-bottom: 12px; font-size: 0.75rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                  <span style="color: var(--color-text-muted);">Proprietor:</span>
                  <span style="font-weight: 600; color: var(--color-ink);">${c.ownerName}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                  <span style="color: var(--color-text-muted);">Plate Sticker:</span>
                  <span style="font-family: monospace; font-weight: 600;">${c.plateStickerNo || 'Pending Issue'}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                  <span style="color: var(--color-text-muted);">Payment:</span>
                  <div>${paymentBadge} <span style="font-weight: 700; margin-left: 4px;">₱${parseFloat(c.totalFee).toFixed(2)}</span></div>
                </div>
                <div style="display: flex; justify-content: space-between;">
                  <span style="color: var(--color-text-muted);">Inspection:</span>
                  <div>${inspBadge}</div>
                </div>
              </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--color-hairline-soft); padding-top: 10px; gap: 6px;">
              <button class="button-outline" onclick="openDossierModal(${c.id})" style="height: 30px; font-size: 0.75rem; padding: 0 10px;">
                Dossier
              </button>
              <div style="display: flex; gap: 4px;">
                ${c.paymentStatus !== 'Paid' ? `
                  <button class="button-outline" onclick="openPaymentModal(${c.id})" style="height: 30px; font-size: 0.75rem; padding: 0 8px;" title="Record Payment">
                    ₱ Pay
                  </button>
                ` : ''}
                <button class="button-outline" onclick="openInspectionModal(${c.id})" style="height: 30px; font-size: 0.75rem; padding: 0 8px;" title="Conduct Inspection">
                  Inspect
                </button>
                <button class="button-primary" onclick="openPrintPermitModal(${c.id})" style="height: 30px; font-size: 0.75rem; padding: 0 10px;" title="Print Clearance">
                  Print
                </button>
              </div>
            </div>
          </div>
        `;
      }).join('');

      // 2. Render Table Rows
      tableTbody.innerHTML = list.map(c => {
        let statusBadge = 'badge-neutral';
        if (c.status === 'Approved & Issued') statusBadge = 'badge-emerald';
        else if (c.status === 'Under Inspection') statusBadge = 'badge-blue';
        else if (c.status === 'Pending Review') statusBadge = 'badge-amber';
        else if (c.status === 'Revoked') statusBadge = 'badge-rose';

        return `
          <tr style="border-bottom: 1px solid var(--color-hairline-soft); font-size: 0.8125rem;">
            <td style="padding: 10px 14px;">
              <div style="font-family: monospace; font-weight: 700;">${c.clearanceNo}</div>
              <div style="font-size: 0.6875rem; color: var(--color-text-muted);">${c.plateStickerNo || 'No Plate'}</div>
            </td>
            <td style="padding: 10px 14px;">
              <div style="font-weight: 700; color: var(--color-ink);">${c.businessName}</div>
              <div style="font-size: 0.6875rem; color: var(--color-text-muted);">${c.tradeName || c.businessAddress}</div>
            </td>
            <td style="padding: 10px 14px;">
              <div>${c.ownerName}</div>
              <div style="font-size: 0.6875rem; color: var(--color-text-muted);">${c.ownershipType}</div>
            </td>
            <td style="padding: 10px 14px;">
              <div>${c.businessNature}</div>
              <div style="font-size: 0.6875rem; color: var(--color-text-muted);">${c.purok}</div>
            </td>
            <td style="padding: 10px 14px;">
              <span class="badge-neutral" style="font-size: 0.6875rem;">${c.grossSalesTier || 'Micro'}</span>
            </td>
            <td style="padding: 10px 14px; font-weight: 700;">
              ₱${parseFloat(c.totalFee).toFixed(2)}
            </td>
            <td style="padding: 10px 14px;">
              <span class="badge-neutral" style="font-size: 0.6875rem; ${c.paymentStatus === 'Paid' ? 'color:#10b981;' : 'color:#f59e0b;'}">${c.paymentStatus}</span>
            </td>
            <td style="padding: 10px 14px;">
              <span class="badge-neutral" style="font-size: 0.6875rem;">${c.inspectionStatus}</span>
            </td>
            <td style="padding: 10px 14px;">
              <span class="${statusBadge}" style="font-size: 0.6875rem;">${c.status}</span>
            </td>
            <td style="padding: 10px 14px; text-align: right; white-space: nowrap;">
              <button class="button-outline" onclick="openDossierModal(${c.id})" style="height: 28px; padding: 0 8px; font-size: 0.75rem;">View</button>
              <button class="button-outline" onclick="openEditPermitModal(${c.id})" style="height: 28px; padding: 0 8px; font-size: 0.75rem;">Edit</button>
              <button class="button-primary" onclick="openPrintPermitModal(${c.id})" style="height: 28px; padding: 0 8px; font-size: 0.75rem;">Print</button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Auto-calculate fee schedule when tier or fees change
    function updateCalculatedFees() {
      const tier = document.getElementById('permit-tier').value;
      const capital = parseFloat(document.getElementById('permit-capital').value) || 0;

      // Tier standard lookup
      let clearance = 300;
      let garbage = 150;
      let inspection = 100;

      if (tier === 'Large (Above ₱5,000,000)' || capital > 5000000) {
        clearance = 2500; garbage = 800; inspection = 500;
      } else if (tier === 'Medium (₱1,500,001 - ₱5,000,000)' || capital > 1500000) {
        clearance = 1200; garbage = 400; inspection = 300;
      } else if (tier === 'Small (₱150,001 - ₱1,500,000)' || capital > 150000) {
        clearance = 600; garbage = 250; inspection = 200;
      } else {
        clearance = 300; garbage = 150; inspection = 100;
      }

      document.getElementById('permit-clearance-fee').value = clearance.toFixed(2);
      document.getElementById('permit-garbage-fee').value = garbage.toFixed(2);
      document.getElementById('permit-inspection-fee').value = inspection.toFixed(2);

      const total = clearance + garbage + inspection;
      document.getElementById('permit-calc-total').textContent = `₱${total.toFixed(2)}`;
    }

    function recalculateTotalFromInputs() {
      const c = parseFloat(document.getElementById('permit-clearance-fee').value) || 0;
      const g = parseFloat(document.getElementById('permit-garbage-fee').value) || 0;
      const i = parseFloat(document.getElementById('permit-inspection-fee').value) || 0;
      const total = c + g + i;
      document.getElementById('permit-calc-total').textContent = `₱${total.toFixed(2)}`;
    }

    // Wizard Navigation State for Permit Modal
    let currentPermitStep = 1;

    window.switchPermitStep = function(step) {
      currentPermitStep = Math.max(1, Math.min(3, step));

      for (let i = 1; i <= 3; i++) {
        const pane = document.getElementById(`permit-step-${i}`);
        if (pane) {
          pane.style.display = (i === currentPermitStep) ? 'block' : 'none';
        }
      }

      document.querySelectorAll('#permit-wizard-tabs .section-tab-btn').forEach(btn => {
        const s = parseInt(btn.getAttribute('data-step'), 10);
        btn.classList.toggle('active', s === currentPermitStep);
      });

      const prevBtn = document.getElementById('btn-permit-prev');
      const nextBtn = document.getElementById('btn-permit-next');
      if (prevBtn) prevBtn.style.display = (currentPermitStep > 1) ? 'inline-flex' : 'none';
      if (nextBtn) nextBtn.style.display = (currentPermitStep < 3) ? 'inline-flex' : 'none';
    };

    window.nextPermitStep = function() {
      if (currentPermitStep === 1) {
        const name = document.getElementById('permit-business-name').value.trim();
        const owner = document.getElementById('permit-owner-name').value.trim();
        if (!name) {
          Toast.warning('Please enter a Business Name to proceed.');
          document.getElementById('permit-business-name').focus();
          return;
        }
        if (!owner) {
          Toast.warning('Please enter the Owner / Proprietor Name to proceed.');
          document.getElementById('permit-owner-name').focus();
          return;
        }
      } else if (currentPermitStep === 2) {
        const addr = document.getElementById('permit-business-address').value.trim();
        if (!addr) {
          Toast.warning('Please enter the Establishment Physical Address to proceed.');
          document.getElementById('permit-business-address').focus();
          return;
        }
      }
      switchPermitStep(currentPermitStep + 1);
    };

    window.prevPermitStep = function() {
      switchPermitStep(currentPermitStep - 1);
    };

    // Open Modal 1: Register New
    window.openCreatePermitModal = function() {
      editingPermitId = null;
      document.getElementById('permit-modal-title').textContent = 'Register Business Clearance';
      document.getElementById('permit-form').reset();
      document.getElementById('permit-id').value = '';
      document.getElementById('permit-tier').value = 'Micro (Below ₱150,000)';
      document.getElementById('permit-capital').value = '50000.00';
      updateCalculatedFees();
      switchPermitStep(1);
      document.getElementById('permit-modal').showModal();
    };

    // Open Modal 1: Edit Existing
    window.openEditPermitModal = function(id) {
      const permit = allClearances.find(c => c.id === id);
      if (!permit) return;

      editingPermitId = id;
      document.getElementById('permit-modal-title').textContent = `Edit Clearance (${permit.clearanceNo})`;
      document.getElementById('permit-id').value = id;
      document.getElementById('permit-business-name').value = permit.businessName || '';
      document.getElementById('permit-trade-name').value = permit.tradeName || '';
      document.getElementById('permit-resident-select').value = permit.ownerResidentId || '';
      document.getElementById('permit-owner-name').value = permit.ownerName || '';
      document.getElementById('permit-owner-contact').value = permit.ownerContact || '';
      document.getElementById('permit-owner-address').value = permit.ownerAddress || '';
      document.getElementById('permit-business-nature').value = permit.businessNature || 'Retail / Sari-Sari Store';
      document.getElementById('permit-ownership-type').value = permit.ownershipType || 'Sole Proprietorship';
      document.getElementById('permit-purok').value = permit.purok || 'Purok 1';
      document.getElementById('permit-business-address').value = permit.businessAddress || '';
      document.getElementById('permit-capital').value = permit.capitalInvestment || 0;
      document.getElementById('permit-tier').value = permit.grossSalesTier || 'Micro (Below ₱150,000)';
      document.getElementById('permit-application-type').value = permit.applicationType || 'New';
      document.getElementById('permit-clearance-fee').value = parseFloat(permit.clearanceFee || 300).toFixed(2);
      document.getElementById('permit-garbage-fee').value = parseFloat(permit.garbageFee || 150).toFixed(2);
      document.getElementById('permit-inspection-fee').value = parseFloat(permit.inspectionFee || 100).toFixed(2);
      document.getElementById('permit-remarks').value = permit.remarks || '';
      recalculateTotalFromInputs();

      switchPermitStep(1);
      document.getElementById('permit-modal').showModal();
    };

    // Open Modal 2: Business Dossier
    window.openDossierModal = function(id) {
      const c = allClearances.find(item => item.id === id);
      if (!c) return;
      activePermitForAction = c;

      document.getElementById('dossier-business-title').textContent = c.businessName;
      document.getElementById('dossier-clearance-no').textContent = `${c.clearanceNo} • Plate: ${c.plateStickerNo || 'Not issued'}`;

      let statusBadge = 'badge-neutral';
      if (c.status === 'Approved & Issued') statusBadge = 'badge-emerald';
      else if (c.status === 'Under Inspection') statusBadge = 'badge-blue';
      else if (c.status === 'Pending Review') statusBadge = 'badge-amber';

      const content = `
        <div style="display: flex; gap: 16px; margin-bottom: 16px; align-items: center; background: var(--color-field); padding: 12px; border-radius: var(--rounded-md);">
          <div style="flex: 1;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-muted);">Proprietor &amp; Ownership</div>
            <div style="font-weight: 700; color: var(--color-ink); font-size: 1rem;">${c.ownerName}</div>
            <div class="typography-caption" style="color: var(--color-text-muted);">${c.ownershipType} &bull; ${c.ownerContact || 'No contact provided'}</div>
          </div>
          <div>
            <span class="${statusBadge}" style="font-size: 0.75rem; text-transform: uppercase;">${c.status}</span>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
          <div>
            <span class="typography-caption" style="color: var(--color-text-muted);">Business Nature:</span>
            <div style="font-weight: 600;">${c.businessNature}</div>
          </div>
          <div>
            <span class="typography-caption" style="color: var(--color-text-muted);">Location &amp; Purok:</span>
            <div style="font-weight: 600;">${c.purok}</div>
          </div>
          <div>
            <span class="typography-caption" style="color: var(--color-text-muted);">Address:</span>
            <div style="font-weight: 600;">${c.businessAddress}</div>
          </div>
          <div>
            <span class="typography-caption" style="color: var(--color-text-muted);">Capital Investment:</span>
            <div style="font-weight: 600;">₱${parseFloat(c.capitalInvestment || 0).toLocaleString()} (${c.grossSalesTier})</div>
          </div>
        </div>

        <!-- Compliance & Payment Trail -->
        <div style="border-top: 1px solid var(--color-hairline-soft); padding-top: 12px; margin-bottom: 12px;">
          <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--color-ink); margin-bottom: 8px;">
            Regulatory Compliance &amp; Official Receipts
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 4px;">
            <span>Official Receipt:</span>
            <strong>${c.orNumber || 'UNPAID'}</strong>
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 4px;">
            <span>Payment Status &amp; Date:</span>
            <span>${c.paymentStatus} ${c.paymentDate ? '(' + c.paymentDate + ')' : ''}</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 4px;">
            <span>Regulatory Inspection:</span>
            <strong style="${c.inspectionStatus === 'Compliant' ? 'color:#10b981;' : 'color:#f59e0b;'}">${c.inspectionStatus}</strong>
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 0.8125rem;">
            <span>Inspecting Tanod / Date:</span>
            <span>${c.inspectedBy || 'Not yet inspected'} ${c.inspectionDate ? '(' + c.inspectionDate + ')' : ''}</span>
          </div>
          ${c.inspectionNotes ? `<div style="font-size: 0.75rem; background: var(--color-field); padding: 6px 10px; border-radius: 4px; margin-top: 6px;"><em>Notes:</em> ${c.inspectionNotes}</div>` : ''}
        </div>
      `;

      document.getElementById('dossier-content').innerHTML = content;

      // Bottom left action button (Approve & Issue if eligible)
      const leftActionMount = document.getElementById('dossier-action-left');
      if (c.status !== 'Approved & Issued' && c.paymentStatus === 'Paid' && c.inspectionStatus === 'Compliant') {
        leftActionMount.innerHTML = `
          <button class="button-primary" onclick="approveAndIssueClearance(${c.id})" style="background: #10b981; border-color: #10b981;">
            ✓ Approve &amp; Issue Plate
          </button>
        `;
      } else if (c.status !== 'Approved & Issued') {
        leftActionMount.innerHTML = `
          <button class="button-outline" onclick="openInspectionModal(${c.id})" style="font-size: 0.75rem;">
            Conduct Inspection
          </button>
        `;
      } else {
        leftActionMount.innerHTML = `
          <span class="badge-neutral" style="color: #10b981;">Officially Issued for ${c.validityYear || 2026}</span>
        `;
      }

      document.getElementById('dossier-print-btn').onclick = () => {
        document.getElementById('dossier-modal').close();
        openPrintPermitModal(c.id);
      };

      document.getElementById('dossier-modal').showModal();
    };

    // Open Modal 3: Payment
    window.openPaymentModal = function(id) {
      const permit = allClearances.find(c => c.id === id);
      if (!permit) return;

      document.getElementById('pay-permit-id').value = id;
      document.getElementById('pay-business-name').textContent = `${permit.businessName} (Owner: ${permit.ownerName})`;
      document.getElementById('pay-amount-due').textContent = `₱${parseFloat(permit.totalFee).toFixed(2)}`;
      document.getElementById('pay-date').value = new Date().toISOString().split('T')[0];
      
      const year = new Date().getFullYear();
      document.getElementById('pay-or-number').value = permit.orNumber || `OR-${year}-${Math.floor(1000 + Math.random() * 9000)}`;

      document.getElementById('payment-modal').showModal();
    };

    window.generateAutoOrNumber = function() {
      const year = new Date().getFullYear();
      document.getElementById('pay-or-number').value = `OR-${year}-${Math.floor(1000 + Math.random() * 9000)}`;
    };

    // Open Modal 4: Inspection
    window.openInspectionModal = function(id) {
      const permit = allClearances.find(c => c.id === id);
      if (!permit) return;

      document.getElementById('insp-permit-id').value = id;
      document.getElementById('insp-business-name').textContent = permit.businessName;
      document.getElementById('insp-business-address').textContent = `${permit.businessAddress}, ${permit.purok}`;
      document.getElementById('insp-status').value = permit.inspectionStatus === 'Compliant' ? 'Compliant' : 'Compliant';
      document.getElementById('insp-date').value = new Date().toISOString().split('T')[0];
      document.getElementById('insp-inspector').value = permit.inspectedBy || 'Tanod Insp. Roberto Diaz';
      document.getElementById('insp-notes').value = permit.inspectionNotes || 'Clean premises, fire extinguisher validated, garbage segregated.';

      document.getElementById('inspection-modal').showModal();
    };

    // Open Modal 5: Printable Certificate
    window.openPrintPermitModal = function(id) {
      const c = allClearances.find(item => item.id === id);
      if (!c) return;

      document.getElementById('print-ctrl-no').textContent = c.clearanceNo;
      document.getElementById('print-plate-no').textContent = c.plateStickerNo || 'PENDING-PLATE';
      document.getElementById('print-validity-year').textContent = c.validityYear || 2026;
      document.getElementById('print-validity-term').textContent = c.validityYear || 2026;
      document.getElementById('print-biz-name').textContent = c.businessName.toUpperCase();
      document.getElementById('print-trade-name').textContent = (c.tradeName || c.businessName).toUpperCase();
      document.getElementById('print-owner-name').textContent = c.ownerName.toUpperCase();
      document.getElementById('print-nature').textContent = c.businessNature;
      document.getElementById('print-address').textContent = `${c.businessAddress}, ${c.purok}`;
      document.getElementById('print-ownership-capital').textContent = `${c.ownershipType} • Capital: ₱${parseFloat(c.capitalInvestment || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;

      document.getElementById('print-or-no').textContent = c.orNumber || 'UNPAID';
      document.getElementById('print-fee-clearance').textContent = `₱${parseFloat(c.clearanceFee || 0).toFixed(2)}`;
      document.getElementById('print-fee-garbage').textContent = `₱${parseFloat(c.garbageFee || 0).toFixed(2)}`;
      document.getElementById('print-fee-insp').textContent = `₱${parseFloat(c.inspectionFee || 0).toFixed(2)}`;
      document.getElementById('print-fee-total').textContent = `₱${parseFloat(c.totalFee || 0).toFixed(2)}`;

      const today = new Date();
      const day = today.getDate();
      const suffix = (day === 1 || day === 21 || day === 31) ? 'st' : (day === 2 || day === 22) ? 'nd' : (day === 3 || day === 23) ? 'rd' : 'th';
      document.getElementById('print-day').textContent = `${day}${suffix}`;
      document.getElementById('print-month-year').textContent = today.toLocaleDateString([], { month: 'long', year: 'numeric' });

      // Generate verification QR code
      const qrMount = document.getElementById('print-qr-code');
      qrMount.innerHTML = '';
      if (window.QRCode) {
        const verifyUrl = `${window.location.origin}${window.location.pathname.replace('permits.php', 'verify.php').replace('permits.html', 'verify.html')}?code=${encodeURIComponent(c.clearanceNo)}`;
        QRCode.render(qrMount, verifyUrl, { size: 80, margin: 1 });
      }

      document.getElementById('print-permit-modal').showModal();
    };

    // Approve & Officially Issue Clearance
    window.approveAndIssueClearance = async function(id) {
      if (!confirm('Approve and officially issue this Barangay Business Clearance with a unique plate number?')) return;

      try {
        const permit = allClearances.find(c => c.id === id);
        if (!permit) return;

        const year = new Date().getFullYear();
        const plateNo = `BP-${year}-${String(allClearances.length + 1).padStart(4, '0')}`;

        permit.status = 'Approved & Issued';
        permit.plateStickerNo = plateNo;
        permit.issueDate = new Date().toISOString().split('T')[0];
        permit.expiryDate = `${year}-12-31`;
        permit.approvedBy = 'Hon. Antonio S. Valdez - Punong Barangay';
        permit.issuedBy = 'Maria Santos - Barangay Treasurer';

        await window.barangayDB.update('business_clearances', permit);
        Toast.success(`Clearance approved! Plate No: ${plateNo}`);
        document.getElementById('dossier-modal').close();
        await refreshClearancesList();
      } catch (err) {
        console.error('Approval failed:', err);
        Toast.error('Could not approve clearance.');
      }
    };

    // Bind Event Listeners
    function bindEventListeners() {
      // Topbar open modal button
      document.getElementById('btn-open-permit-modal').addEventListener('click', openCreatePermitModal);

      // Search and Filter Listeners
      document.getElementById('search-permits').addEventListener('input', applyFiltersAndRender);
      document.getElementById('filter-nature').addEventListener('change', applyFiltersAndRender);
      document.getElementById('filter-purok').addEventListener('change', applyFiltersAndRender);
      document.getElementById('filter-status').addEventListener('change', applyFiltersAndRender);
      document.getElementById('filter-payment').addEventListener('change', applyFiltersAndRender);

      // View switcher buttons
      document.getElementById('btn-view-cards').addEventListener('click', () => {
        document.getElementById('permits-grid-view').style.display = 'block';
        document.getElementById('permits-table-view').style.display = 'none';
        document.getElementById('btn-view-cards').style.background = 'var(--color-field)';
        document.getElementById('btn-view-table').style.background = 'transparent';
      });

      document.getElementById('btn-view-table').addEventListener('click', () => {
        document.getElementById('permits-grid-view').style.display = 'none';
        document.getElementById('permits-table-view').style.display = 'block';
        document.getElementById('btn-view-table').style.background = 'var(--color-field)';
        document.getElementById('btn-view-cards').style.background = 'transparent';
      });

      // Resident Select change listener
      document.getElementById('permit-resident-select').addEventListener('change', (e) => {
        const resId = parseInt(e.target.value, 10);
        if (resId && allResidentsMap.has(resId)) {
          const res = allResidentsMap.get(resId);
          document.getElementById('permit-owner-name').value = [res.firstName || res.first_name, res.lastName || res.last_name].filter(Boolean).join(' ');
          document.getElementById('permit-owner-contact').value = res.phone || res.contactNo || res.contact_no || '';
          document.getElementById('permit-owner-address').value = res.address || res.street || `${res.purok}, Barangay San Isidro`;
          if (res.purok) {
            document.getElementById('permit-purok').value = res.purok;
          }
        }
      });

      // Capital & Tier listener
      document.getElementById('permit-tier').addEventListener('change', updateCalculatedFees);
      document.getElementById('permit-capital').addEventListener('input', updateCalculatedFees);
      document.getElementById('permit-clearance-fee').addEventListener('input', recalculateTotalFromInputs);
      document.getElementById('permit-garbage-fee').addEventListener('input', recalculateTotalFromInputs);
      document.getElementById('permit-inspection-fee').addEventListener('input', recalculateTotalFromInputs);

      // Submit Form 1: Register / Edit
      document.getElementById('permit-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const businessName = document.getElementById('permit-business-name').value.trim();
        if (!businessName) {
          Toast.warning('Please enter a Business Name.');
          switchPermitStep(1);
          document.getElementById('permit-business-name').focus();
          return;
        }

        const tradeName = document.getElementById('permit-trade-name').value.trim() || businessName;
        const residentId = document.getElementById('permit-resident-select').value ? parseInt(document.getElementById('permit-resident-select').value, 10) : null;
        const ownerName = document.getElementById('permit-owner-name').value.trim();
        if (!ownerName) {
          Toast.warning('Please enter the Owner / Proprietor Name.');
          switchPermitStep(1);
          document.getElementById('permit-owner-name').focus();
          return;
        }

        const ownerContact = document.getElementById('permit-owner-contact').value.trim();
        const ownerAddress = document.getElementById('permit-owner-address').value.trim();
        const businessNature = document.getElementById('permit-business-nature').value;
        const ownershipType = document.getElementById('permit-ownership-type').value;
        const purok = document.getElementById('permit-purok').value;
        const businessAddress = document.getElementById('permit-business-address').value.trim();
        if (!businessAddress) {
          Toast.warning('Please enter the Establishment Physical Address.');
          switchPermitStep(2);
          document.getElementById('permit-business-address').focus();
          return;
        }
        const capitalInvestment = parseFloat(document.getElementById('permit-capital').value) || 0;
        const grossSalesTier = document.getElementById('permit-tier').value;
        const applicationType = document.getElementById('permit-application-type').value;
        const clearanceFee = parseFloat(document.getElementById('permit-clearance-fee').value) || 0;
        const garbageFee = parseFloat(document.getElementById('permit-garbage-fee').value) || 0;
        const inspectionFee = parseFloat(document.getElementById('permit-inspection-fee').value) || 0;
        const totalFee = clearanceFee + garbageFee + inspectionFee;
        const remarks = document.getElementById('permit-remarks').value.trim();

        const year = new Date().getFullYear();

        if (editingPermitId) {
          // Update
          const existing = allClearances.find(c => c.id === editingPermitId);
          if (!existing) return;

          const updatedPayload = {
            ...existing,
            businessName,
            tradeName,
            ownerResidentId: residentId,
            ownerName,
            ownerContact,
            ownerAddress,
            businessNature,
            ownershipType,
            purok,
            businessAddress,
            capitalInvestment,
            grossSalesTier,
            applicationType,
            clearanceFee,
            garbageFee,
            inspectionFee,
            totalFee,
            remarks
          };

          try {
            await window.barangayDB.update('business_clearances', updatedPayload);
            Toast.success('Business clearance record updated successfully.');
            document.getElementById('permit-modal').close();
            await refreshClearancesList();
          } catch (err) {
            console.error('Update error:', err);
            Toast.error('Could not update business clearance.');
          }
        } else {
          // Create
          const count = allClearances.length;
          const clearanceNo = `BBC-${year}-${String(count + 1).padStart(5, '0')}`;
          const qrToken = 'QR-' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);

          const newPayload = {
            clearanceNo,
            businessName,
            tradeName,
            ownerResidentId: residentId,
            ownerName,
            ownerContact,
            ownerAddress,
            businessNature,
            ownershipType,
            purok,
            businessAddress,
            capitalInvestment,
            grossSalesTier,
            applicationType,
            clearanceFee,
            garbageFee,
            inspectionFee,
            totalFee,
            orNumber: null,
            paymentStatus: 'Unpaid',
            paymentDate: null,
            inspectionStatus: 'Pending Inspection',
            inspectedBy: null,
            inspectionDate: null,
            inspectionNotes: null,
            status: 'Pending Review',
            plateStickerNo: null,
            qrToken,
            validityYear: year,
            issueDate: null,
            expiryDate: null,
            issuedBy: 'Maria Santos - Barangay Treasurer',
            approvedBy: 'Hon. Antonio S. Valdez - Punong Barangay',
            remarks
          };

          try {
            await window.barangayDB.add('business_clearances', newPayload);
            Toast.success(`Business registered! Clearance No: ${clearanceNo}`);
            document.getElementById('permit-modal').close();
            await refreshClearancesList();
          } catch (err) {
            console.error('Create error:', err);
            Toast.error('Could not register business establishment.');
          }
        }
      });

      // Submit Form 2: Payment
      document.getElementById('payment-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = parseInt(document.getElementById('pay-permit-id').value, 10);
        const permit = allClearances.find(c => c.id === id);
        if (!permit) return;

        const orNumber = document.getElementById('pay-or-number').value.trim();
        const paymentDate = document.getElementById('pay-date').value;
        const paymentStatus = document.getElementById('pay-status').value;

        permit.orNumber = orNumber;
        permit.paymentDate = paymentDate;
        permit.paymentStatus = paymentStatus;

        try {
          // If using REST API, action=record_payment handles DB update and syncs to revenue_collections
          if (window.barangayDB && window.fetch) {
            try {
              await fetch('api/permits.php?action=record_payment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, or_number: orNumber, payment_date: paymentDate, payment_status: paymentStatus })
              });
            } catch (apiErr) {
              await window.barangayDB.update('business_clearances', permit);
            }
          } else {
            await window.barangayDB.update('business_clearances', permit);
          }

          Toast.success(`Payment recorded with O.R. #${orNumber} and synced to Budget!`);
          document.getElementById('payment-modal').close();
          await refreshClearancesList();
        } catch (err) {
          console.error('Payment error:', err);
          Toast.error('Failed to record payment.');
        }
      });

      // Submit Form 3: Inspection
      document.getElementById('inspection-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = parseInt(document.getElementById('insp-permit-id').value, 10);
        const permit = allClearances.find(c => c.id === id);
        if (!permit) return;

        const inspectionStatus = document.getElementById('insp-status').value;
        const inspectionDate = document.getElementById('insp-date').value;
        const inspectedBy = document.getElementById('insp-inspector').value.trim();
        const inspectionNotes = document.getElementById('insp-notes').value.trim();

        permit.inspectionStatus = inspectionStatus;
        permit.inspectionDate = inspectionDate;
        permit.inspectedBy = inspectedBy;
        permit.inspectionNotes = inspectionNotes;
        if (permit.status === 'Pending Review') {
          permit.status = 'Under Inspection';
        }

        try {
          await window.barangayDB.update('business_clearances', permit);
          Toast.success(`Inspection recorded: ${inspectionStatus}`);
          document.getElementById('inspection-modal').close();
          await refreshClearancesList();
        } catch (err) {
          console.error('Inspection error:', err);
          Toast.error('Failed to save inspection.');
        }
      });
    }
  </script>
</body>
</html>
