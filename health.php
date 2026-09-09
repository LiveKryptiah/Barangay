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
  <title>Barangay Health Station &amp; Nutrition Hub &bull; Barangay Management System</title>
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
    .health-tab-nav {
      display: flex;
      gap: var(--spacing-xs);
      border-bottom: 1px solid var(--color-hairline-soft);
      margin-bottom: var(--spacing-md);
      overflow-x: auto;
    }

    .health-tab-btn {
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

    .health-tab-btn:hover {
      color: var(--color-ink);
    }

    .health-tab-btn.active {
      color: var(--color-ink);
      border-bottom-color: var(--color-ink);
    }

    /* Filter Toolbar */
    .health-filter-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: var(--spacing-xs);
      margin-bottom: var(--spacing-sm);
    }

    /* Studio Card */
    .studio-card {
      background-color: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: var(--spacing-md);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    /* Vitals Pill Grid */
    .vitals-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
      gap: 8px;
      margin-top: 8px;
    }

    .vital-pill {
      background: var(--color-canvas-soft);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-sm);
      padding: 8px 12px;
      display: flex;
      flex-direction: column;
    }

    .vital-label {
      font-size: 0.6875rem;
      color: var(--color-text-muted);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .vital-val {
      font-size: 0.9375rem;
      font-weight: 700;
      color: var(--color-ink);
      margin-top: 2px;
    }

    /* Table styles */
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

    /* EPI Milestone Matrix */
    .milestone-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: var(--spacing-md);
    }

    .milestone-card {
      background: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: var(--spacing-md);
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .milestone-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid var(--color-hairline-soft);
      padding-bottom: 8px;
    }

    .vaccine-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 6px 0;
      font-size: 0.8125rem;
      border-bottom: 1px dashed var(--color-hairline-soft);
    }

    .vaccine-item:last-child {
      border-bottom: none;
    }

    /* OPT Calculator Layout */
    .opt-layout {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--spacing-lg);
    }

    @media (max-width: 900px) {
      .opt-layout {
        grid-template-columns: 1fr;
      }
    }

    /* DOH Referral Slip Print Styles */
    @media print {
      body * {
        visibility: hidden;
      }
      #print-referral-slip, #print-referral-slip * {
        visibility: visible;
      }
      #print-referral-slip {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: #ffffff !important;
        color: #000000 !important;
        padding: 24px;
      }
      .no-print {
        display: none !important;
      }
    }

    /* Referral Slip Card */
    .referral-slip {
      background: #ffffff;
      color: #141414;
      border: 2px solid #141414;
      border-radius: 8px;
      padding: 28px;
      max-width: 800px;
      margin: 0 auto;
      font-family: 'Inter', serif;
    }

    .doh-header {
      text-align: center;
      border-bottom: 2px solid #141414;
      padding-bottom: 16px;
      margin-bottom: 20px;
    }

    .doh-seal-row {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 20px;
      margin-bottom: 8px;
    }

    .ref-field-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 16px;
    }

    .ref-field {
      border-bottom: 1px solid #cccccc;
      padding-bottom: 4px;
    }

    .ref-label {
      font-size: 0.6875rem;
      text-transform: uppercase;
      color: #555555;
      font-weight: 700;
    }

    .ref-value {
      font-size: 0.875rem;
      font-weight: 600;
      color: #000000;
      margin-top: 2px;
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
                <h1 class="typography-heading-2">Barangay Health Station &amp; Nutrition Hub.</h1>
                <span class="badge-neutral" style="display: inline-flex; align-items: center; gap: 6px;">
                  <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                  DOH Primary Care Station (Live MySQL)
                </span>
              </div>
              <p class="typography-body-lg">
                Comprehensive primary healthcare intake, Expanded Program on Immunization (EPI), Operation Timbang Plus child nutrition monitoring, and senior maintenance medicine dispensary.
              </p>
            </div>
            <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
              <button class="button-outline" onclick="openNewMedicineModal();" style="height: 38px; padding: 0 16px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 5v14M5 12h14"/>
                </svg>
                <span>Add Medicine</span>
              </button>
              <button class="button-primary" onclick="openIntakeModal();" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="12" y1="5" x2="12" y2="19"/>
                  <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Log Clinical Consultation</span>
              </button>
            </div>
          </div>
        </section>

        <!-- Executive Telemetry Ladder -->
        <section>
          <div class="stats-ladder">
            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">CLINICAL INTAKES</span>
                <span class="badge-neutral" id="badge-intake-period">Live DB</span>
              </div>
              <div class="stat-number" id="stat-total-consultations">0</div>
              <div class="typography-caption" id="stat-sub-consultations">Total consultations logged</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">PROFILED PATIENTS</span>
                <span class="badge-neutral" style="color: #10b981;">BHW Registry</span>
              </div>
              <div class="stat-number" id="stat-total-patients">0</div>
              <div class="typography-caption" id="stat-sub-patients">Unique resident dossiers</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">OPT+ MALNUTRITION CASES</span>
                <span class="badge-neutral" style="color: #f59e0b;">WHO Standards</span>
              </div>
              <div class="stat-number" id="stat-opt-malnourished">0</div>
              <div class="typography-caption" id="stat-sub-opt">Children on feeding plan</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">PHARMACY STOCK</span>
                <span class="badge-neutral" id="stat-low-stock-badge">Normal</span>
              </div>
              <div class="stat-number" id="stat-total-medicines">0</div>
              <div class="typography-caption" id="stat-sub-medicines">Units available &bull; 0 low stock</div>
            </div>
          </div>
        </section>

        <!-- Main Tab Navigation -->
        <nav class="health-tab-nav" aria-label="Health Station Views">
          <button type="button" class="health-tab-btn active" id="tab-btn-consultations" onclick="switchHealthTab('consultations');">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
              <polyline points="10 9 9 9 8 9"/>
            </svg>
            <span>Clinical Consultations &amp; Intake</span>
          </button>

          <button type="button" class="health-tab-btn" id="tab-btn-epi" onclick="switchHealthTab('epi');">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m18 2 4 4"/>
              <path d="m17 7 3-3"/>
              <path d="M19 9 8.7 19.3c-1 1-2.5 1-3.4 0l-.6-.6c-1-1-1-2.5 0-3.4L15 5"/>
              <path d="m9 11 4 4"/>
              <path d="m5 19-3 3"/>
              <path d="m14 4 6 6"/>
            </svg>
            <span>Maternal &amp; Child Health (EPI)</span>
          </button>

          <button type="button" class="health-tab-btn" id="tab-btn-opt" onclick="switchHealthTab('opt');">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M12 6v6l4 2"/>
            </svg>
            <span>Operation Timbang Plus (OPT+)</span>
          </button>

          <button type="button" class="health-tab-btn" id="tab-btn-pharmacy" onclick="switchHealthTab('pharmacy');">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/>
              <path d="m8.5 8.5 7 7"/>
            </svg>
            <span>Pharmacy &amp; Medicine Inventory</span>
          </button>
        </nav>

        <!-- ======================================================= -->
        <!-- TAB 1: CLINICAL CONSULTATIONS & INTAKE                  -->
        <!-- ======================================================= -->
        <section id="view-consultations" class="tab-pane">
          <div class="health-filter-bar">
            <div style="display: flex; align-items: center; gap: var(--spacing-xs); flex-wrap: wrap; flex: 1;">
              <input type="text" id="consultation-search" placeholder="Search by Record #, patient name, complaint..." class="text-input" style="max-width: 340px; height: 36px;" oninput="filterConsultations();">
              
              <select id="consultation-service-filter" class="text-input" style="width: auto; height: 36px;" onchange="filterConsultations();">
                <option value="">All Health Services</option>
                <option value="General Consultation">General Consultation</option>
                <option value="Prenatal Checkup">Prenatal Checkup</option>
                <option value="Postnatal Care">Postnatal Care</option>
                <option value="Child Immunization">Child Immunization</option>
                <option value="Nutrition OPT Plus">Nutrition OPT Plus</option>
                <option value="Senior Maintenance">Senior Maintenance</option>
                <option value="Animal Bite / Rabies">Animal Bite / Rabies</option>
                <option value="First Aid / Wound Care">First Aid / Wound Care</option>
              </select>

              <select id="consultation-status-filter" class="text-input" style="width: auto; height: 36px;" onchange="filterConsultations();">
                <option value="">All Statuses</option>
                <option value="Completed">Completed</option>
                <option value="Follow-Up Needed">Follow-Up Needed</option>
                <option value="Referred to RHU / Hospital">Referred to RHU / Hospital</option>
              </select>
            </div>

            <div style="font-size: 0.8125rem; color: var(--color-text-muted);">
              Showing <span id="consultations-count-text" style="font-weight: 600; color: var(--color-ink);">0</span> consultations
            </div>
          </div>

          <div class="data-table-wrap">
            <table class="data-table" id="consultations-table">
              <thead>
                <tr>
                  <th>Record No</th>
                  <th>Patient Name &amp; Purok</th>
                  <th>Service Type</th>
                  <th>Vitals (BP / Temp / BMI)</th>
                  <th>Chief Complaint &amp; Diagnosis</th>
                  <th>Attending Staff</th>
                  <th>Status</th>
                  <th style="text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody id="consultations-table-body">
                <tr>
                  <td colspan="8" style="text-align: center; padding: 32px; color: var(--color-text-muted);">
                    Loading clinical health records from MySQL...
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- ======================================================= -->
        <!-- TAB 2: MATERNAL & CHILD HEALTH / EPI IMMUNIZATION       -->
        <!-- ======================================================= -->
        <section id="view-epi" class="tab-pane" style="display: none;">
          <div style="margin-bottom: var(--spacing-md);">
            <h2 class="typography-heading-3" style="margin-bottom: 4px;">Expanded Program on Immunization (EPI) Matrix</h2>
            <p class="typography-caption" style="color: var(--color-text-muted);">
              Standard Department of Health (DOH) vaccination schedule for infants (0-12 months) and pregnant mothers.
            </p>
          </div>

          <div class="milestone-grid">
            <!-- Milestone: At Birth -->
            <div class="milestone-card">
              <div class="milestone-header">
                <span style="font-weight: 700; font-size: 0.875rem;">At Birth (0 Days)</span>
                <span class="badge-neutral" style="font-size: 0.625rem;">Immediate</span>
              </div>
              <div class="vaccine-item">
                <span><strong>BCG Vaccine</strong> (Tuberculosis)</span>
                <span class="typography-caption">0.05 mL &bull; Right Deltoid (ID)</span>
              </div>
              <div class="vaccine-item">
                <span><strong>Hepatitis B</strong> (Birth Dose)</span>
                <span class="typography-caption">0.5 mL &bull; Anterolateral Thigh (IM)</span>
              </div>
            </div>

            <!-- Milestone: 6 Weeks -->
            <div class="milestone-card">
              <div class="milestone-header">
                <span style="font-weight: 700; font-size: 0.875rem;">1 &frac12; Months (6 Weeks)</span>
                <span class="badge-neutral" style="font-size: 0.625rem;">Dose 1 Series</span>
              </div>
              <div class="vaccine-item">
                <span><strong>Pentavalent 1</strong> (DPT-HepB-Hib)</span>
                <span class="typography-caption">0.5 mL &bull; Thigh (IM)</span>
              </div>
              <div class="vaccine-item">
                <span><strong>OPV 1</strong> (Oral Polio)</span>
                <span class="typography-caption">2 drops &bull; Oral</span>
              </div>
              <div class="vaccine-item">
                <span><strong>PCV 1</strong> (Pneumococcal Conjugate)</span>
                <span class="typography-caption">0.5 mL &bull; Thigh (IM)</span>
              </div>
            </div>

            <!-- Milestone: 10 Weeks -->
            <div class="milestone-card">
              <div class="milestone-header">
                <span style="font-weight: 700; font-size: 0.875rem;">2 &frac12; Months (10 Weeks)</span>
                <span class="badge-neutral" style="font-size: 0.625rem;">Dose 2 Series</span>
              </div>
              <div class="vaccine-item">
                <span><strong>Pentavalent 2</strong></span>
                <span class="typography-caption">0.5 mL &bull; Thigh (IM)</span>
              </div>
              <div class="vaccine-item">
                <span><strong>OPV 2</strong></span>
                <span class="typography-caption">2 drops &bull; Oral</span>
              </div>
              <div class="vaccine-item">
                <span><strong>PCV 2</strong></span>
                <span class="typography-caption">0.5 mL &bull; Thigh (IM)</span>
              </div>
            </div>

            <!-- Milestone: 14 Weeks -->
            <div class="milestone-card">
              <div class="milestone-header">
                <span style="font-weight: 700; font-size: 0.875rem;">3 &frac12; Months (14 Weeks)</span>
                <span class="badge-neutral" style="font-size: 0.625rem;">Dose 3 Series</span>
              </div>
              <div class="vaccine-item">
                <span><strong>Pentavalent 3</strong></span>
                <span class="typography-caption">0.5 mL &bull; Thigh (IM)</span>
              </div>
              <div class="vaccine-item">
                <span><strong>OPV 3 &amp; IPV</strong> (Inactivated Polio)</span>
                <span class="typography-caption">0.5 mL &bull; Thigh (IM)</span>
              </div>
              <div class="vaccine-item">
                <span><strong>PCV 3</strong></span>
                <span class="typography-caption">0.5 mL &bull; Thigh (IM)</span>
              </div>
            </div>

            <!-- Milestone: 9 Months -->
            <div class="milestone-card">
              <div class="milestone-header">
                <span style="font-weight: 700; font-size: 0.875rem;">9 Months</span>
                <span class="badge-neutral" style="font-size: 0.625rem;">Measles 1</span>
              </div>
              <div class="vaccine-item">
                <span><strong>MMR 1 (MCV1)</strong> (Measles-Mumps-Rubella)</span>
                <span class="typography-caption">0.5 mL &bull; Subcutaneous</span>
              </div>
            </div>

            <!-- Milestone: 12 Months -->
            <div class="milestone-card">
              <div class="milestone-header">
                <span style="font-weight: 700; font-size: 0.875rem;">12 Months (1 Year)</span>
                <span class="badge-neutral" style="font-size: 0.625rem;">Fully Immunized Child (FIC)</span>
              </div>
              <div class="vaccine-item">
                <span><strong>MMR 2 (MCV2) Booster</strong></span>
                <span class="typography-caption">0.5 mL &bull; Subcutaneous</span>
              </div>
              <div class="vaccine-item">
                <span><strong>Certificate of Full Immunization</strong></span>
                <span class="typography-caption">Official BHW Endorsement</span>
              </div>
            </div>
          </div>

          <!-- Quick Maternal Schedule Section -->
          <div class="studio-card" style="margin-top: var(--spacing-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
              <h3 class="typography-heading-4">Maternal Healthcare &amp; Prenatal Schedule</h3>
              <span class="badge-neutral" style="color: #0066ff;">DOH Maternal Care</span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--spacing-sm);">
              <div style="background: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-sm);">
                <div style="font-weight: 700; font-size: 0.8125rem;">1st Trimester (&lt;12 Weeks)</div>
                <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 4px;">
                  Initial prenatal registration, CBC, urinalysis, blood typing, initial dose of Iron + Folic acid supplementation.
                </div>
              </div>

              <div style="background: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-sm);">
                <div style="font-weight: 700; font-size: 0.8125rem;">2nd Trimester (13&ndash;27 Weeks)</div>
                <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 4px;">
                  Tetanus Diphtheria (Td) toxoid injection, fundic height check, fetal heart tone auscultation, calcium carbonate.
                </div>
              </div>

              <div style="background: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-sm);">
                <div style="font-weight: 700; font-size: 0.8125rem;">3rd Trimester (28&ndash;40 Weeks)</div>
                <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 4px;">
                  Pre-delivery birth planning, emergency transport protocol, danger sign orientation, breastfeeding counseling.
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- ======================================================= -->
        <!-- TAB 3: OPERATION TIMBANG PLUS (OPT+) MALNUTRITION       -->
        <!-- ======================================================= -->
        <section id="view-opt" class="tab-pane" style="display: none;">
          <div class="opt-layout">
            <!-- Left: Interactive Assessment Tool -->
            <div class="studio-card">
              <div style="margin-bottom: var(--spacing-sm);">
                <h3 class="typography-heading-4">OPT+ Child Nutrition Assessment Calculator</h3>
                <p class="typography-caption" style="color: var(--color-text-muted);">
                  Calculates nutritional status according to WHO Child Growth Standards (0 to 59 months).
                </p>
              </div>

              <form id="opt-calc-form" onsubmit="handleOptSubmit(event);">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-sm); margin-bottom: 12px;">
                  <div>
                    <label class="form-label" for="opt-resident-id">Select Child Resident *</label>
                    <select id="opt-resident-id" class="text-input" required onchange="onOptResidentChange();">
                      <option value="">-- Choose child from registry --</option>
                    </select>
                  </div>
                  <div>
                    <label class="form-label" for="opt-age-months">Age in Months *</label>
                    <input type="number" id="opt-age-months" class="text-input" min="0" max="59" placeholder="e.g. 24" required oninput="calculateOptStatus();">
                  </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-sm); margin-bottom: 12px;">
                  <div>
                    <label class="form-label" for="opt-weight">Weight (kg) *</label>
                    <input type="number" step="0.01" id="opt-weight" class="text-input" placeholder="e.g. 11.2" required oninput="calculateOptStatus();">
                  </div>
                  <div>
                    <label class="form-label" for="opt-height">Height / Length (cm) *</label>
                    <input type="number" step="0.1" id="opt-height" class="text-input" placeholder="e.g. 86.5" required oninput="calculateOptStatus();">
                  </div>
                </div>

                <!-- Live Auto-computed Classification Preview -->
                <div style="background: var(--color-canvas-soft); border: 1px solid var(--color-hairline-soft); border-radius: var(--rounded-sm); padding: 14px; margin-bottom: 16px;">
                  <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-muted); margin-bottom: 8px;">
                    WHO Clinical Classification Output
                  </div>
                  <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; text-align: center;">
                    <div style="background: var(--color-canvas); padding: 8px; border-radius: 8px; border: 1px solid var(--color-hairline-soft);">
                      <div style="font-size: 0.6875rem; color: var(--color-text-muted);">Weight-for-Age</div>
                      <div id="opt-wfa-pill" style="font-weight: 700; font-size: 0.8125rem; margin-top: 4px; color: #10b981;">Normal</div>
                    </div>
                    <div style="background: var(--color-canvas); padding: 8px; border-radius: 8px; border: 1px solid var(--color-hairline-soft);">
                      <div style="font-size: 0.6875rem; color: var(--color-text-muted);">Height-for-Age</div>
                      <div id="opt-hfa-pill" style="font-weight: 700; font-size: 0.8125rem; margin-top: 4px; color: #10b981;">Normal</div>
                    </div>
                    <div style="background: var(--color-canvas); padding: 8px; border-radius: 8px; border: 1px solid var(--color-hairline-soft);">
                      <div style="font-size: 0.6875rem; color: var(--color-text-muted);">Weight-for-Height</div>
                      <div id="opt-wfh-pill" style="font-weight: 700; font-size: 0.8125rem; margin-top: 4px; color: #10b981;">Normal</div>
                    </div>
                  </div>

                  <div id="opt-action-plan" style="margin-top: 12px; font-size: 0.75rem; color: var(--color-ink-soft); line-height: 1.4;">
                    Action Plan: Routine growth monitoring; maintain age-appropriate balanced diet and micronutrient supplementation.
                  </div>
                </div>

                <button type="submit" class="button-primary" style="width: 100%; height: 40px; font-size: 0.8125rem;">
                  Save OPT+ Assessment to Clinical Records
                </button>
              </form>
            </div>

            <!-- Right: Malnutrition Registry & Monitoring List -->
            <div class="studio-card">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm);">
                <div>
                  <h3 class="typography-heading-4">Underweight &amp; Stunting Monitoring Roster</h3>
                  <p class="typography-caption" style="color: var(--color-text-muted);">
                    Flagged for supplementary feeding &amp; micronutrient pack distribution.
                  </p>
                </div>
                <span class="badge-neutral" id="opt-roster-count">0 Enrolled</span>
              </div>

              <div class="data-table-wrap">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Child Name</th>
                      <th>Age</th>
                      <th>Classification</th>
                      <th>Purok</th>
                      <th>Plan</th>
                    </tr>
                  </thead>
                  <tbody id="opt-roster-table-body">
                    <tr>
                      <td colspan="5" style="text-align: center; padding: 24px; color: var(--color-text-muted);">
                        No malnutrition cases recorded yet.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </section>

        <!-- ======================================================= -->
        <!-- TAB 4: PHARMACY & MEDICINE INVENTORY                    -->
        <!-- ======================================================= -->
        <section id="view-pharmacy" class="tab-pane" style="display: none;">
          <div class="health-filter-bar">
            <div style="display: flex; align-items: center; gap: var(--spacing-xs); flex-wrap: wrap; flex: 1;">
              <input type="text" id="medicine-search" placeholder="Search medicine brand or generic name..." class="text-input" style="max-width: 320px; height: 36px;" oninput="filterMedicines();">

              <select id="medicine-category-filter" class="text-input" style="width: auto; height: 36px;" onchange="filterMedicines();">
                <option value="">All Pharmaceutical Categories</option>
                <option value="Maintenance - Hypertension">Maintenance - Hypertension</option>
                <option value="Maintenance - Diabetes">Maintenance - Diabetes</option>
                <option value="Antibiotics">Antibiotics</option>
                <option value="Analgesic / Fever">Analgesic / Fever</option>
                <option value="Pediatric / Vitamins">Pediatric / Vitamins</option>
                <option value="Oral Rehydration">Oral Rehydration</option>
                <option value="First Aid">First Aid</option>
              </select>
            </div>

            <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
              <button class="button-outline" onclick="openSendRefillSmsModal();" style="height: 36px; padding: 0 14px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span>Dispatch Refill SMS Alert</span>
              </button>
            </div>
          </div>

          <div class="data-table-wrap">
            <table class="data-table" id="medicines-table">
              <thead>
                <tr>
                  <th>Medicine Brand &amp; Generic</th>
                  <th>Category</th>
                  <th>Dosage / Form</th>
                  <th>Stock Available</th>
                  <th>Status</th>
                  <th>Expiry Date</th>
                  <th>Batch No</th>
                  <th style="text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody id="medicines-table-body">
                <tr>
                  <td colspan="8" style="text-align: center; padding: 32px; color: var(--color-text-muted);">
                    Loading pharmacy inventory from MySQL...
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

      </main>
    </div>
  </div>

  <!-- ======================================================= -->
  <!-- MODAL: CLINICAL INTAKE FORM                             -->
  <!-- ======================================================= -->
  <dialog id="intake-modal" class="modal-dialog" style="max-width: 680px; width: 92%; border-radius: var(--rounded-md); border: 1px solid var(--color-hairline); background: var(--color-canvas); padding: 0; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);">
    <div style="padding: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h3 class="typography-heading-3" style="font-size: 1.125rem; margin-bottom: 2px;">Barangay Health Consultation Intake</h3>
        <p class="typography-caption" style="color: var(--color-text-muted);">Record vital signs, diagnosis, and medical intervention.</p>
      </div>
      <button type="button" class="icon-button" onclick="document.getElementById('intake-modal').close();" aria-label="Close modal">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form id="intake-form" onsubmit="handleIntakeSubmit(event);" style="padding: var(--spacing-md); max-height: 75vh; overflow-y: auto;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-sm); margin-bottom: 12px;">
        <div>
          <label class="form-label" for="intake-resident-id">Patient / Resident *</label>
          <select id="intake-resident-id" class="text-input" required onchange="onIntakeResidentSelect();">
            <option value="">-- Select Resident --</option>
          </select>
        </div>
        <div>
          <label class="form-label" for="intake-service-type">Service Type *</label>
          <select id="intake-service-type" class="text-input" required>
            <option value="General Consultation">General Consultation</option>
            <option value="Prenatal Checkup">Prenatal Checkup</option>
            <option value="Postnatal Care">Postnatal Care</option>
            <option value="Child Immunization">Child Immunization</option>
            <option value="Nutrition OPT Plus">Nutrition OPT Plus</option>
            <option value="Senior Maintenance">Senior Maintenance</option>
            <option value="Animal Bite / Rabies">Animal Bite / Rabies</option>
            <option value="First Aid / Wound Care">First Aid / Wound Care</option>
          </select>
        </div>
      </div>

      <!-- Vitals Capture Section -->
      <div style="background: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-sm); margin-bottom: 14px; border: 1px solid var(--color-hairline-soft);">
        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-muted); margin-bottom: 8px;">
          Patient Vital Signs &amp; Anthropometrics
        </div>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
          <div>
            <label class="form-label" style="font-size: 0.6875rem;" for="intake-bp">Blood Pressure</label>
            <input type="text" id="intake-bp" placeholder="e.g. 120/80" class="text-input" style="height: 34px;">
          </div>
          <div>
            <label class="form-label" style="font-size: 0.6875rem;" for="intake-temp">Temp (&deg;C)</label>
            <input type="number" step="0.1" id="intake-temp" placeholder="e.g. 36.6" class="text-input" style="height: 34px;">
          </div>
          <div>
            <label class="form-label" style="font-size: 0.6875rem;" for="intake-weight">Weight (kg)</label>
            <input type="number" step="0.1" id="intake-weight" placeholder="e.g. 62.5" class="text-input" style="height: 34px;" oninput="updateIntakeBmi();">
          </div>
          <div>
            <label class="form-label" style="font-size: 0.6875rem;" for="intake-height">Height (cm)</label>
            <input type="number" step="0.1" id="intake-height" placeholder="e.g. 165" class="text-input" style="height: 34px;" oninput="updateIntakeBmi();">
          </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding-top: 6px; border-top: 1px dashed var(--color-hairline-soft);">
          <div style="font-size: 0.75rem; color: var(--color-text-muted);">
            Auto-calculated BMI: <strong id="intake-bmi-val" style="color: var(--color-ink);">--</strong>
          </div>
          <span id="intake-bmi-badge" class="badge-neutral" style="font-size: 0.6875rem;">Awaiting inputs</span>
        </div>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="intake-complaint">Chief Complaint / Symptoms *</label>
        <textarea id="intake-complaint" class="text-input" rows="2" placeholder="e.g. High fever for 2 days, persistent cough, headache..." required></textarea>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="intake-notes">Clinical Assessment &amp; Findings</label>
        <textarea id="intake-notes" class="text-input" rows="2" placeholder="e.g. Pharyngeal erythema, clear breath sounds, advised hydration and rest..."></textarea>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-sm); margin-bottom: 12px;">
        <div>
          <label class="form-label" for="intake-medicines">Medicines Dispensed</label>
          <input type="text" id="intake-medicines" placeholder="e.g. Paracetamol 500mg (10 tabs)" class="text-input">
        </div>
        <div>
          <label class="form-label" for="intake-staff">Attending Staff / BHW *</label>
          <input type="text" id="intake-staff" value="Barangay Health Worker" class="text-input" required>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-sm); margin-bottom: 12px;">
        <div>
          <label class="form-label" for="intake-status">Clinical Status *</label>
          <select id="intake-status" class="text-input" required onchange="toggleReferralInput();">
            <option value="Completed">Completed / Discharged</option>
            <option value="Follow-Up Needed">Follow-Up Needed</option>
            <option value="Referred to RHU / Hospital">Referred to RHU / Hospital</option>
          </select>
        </div>
        <div>
          <label class="form-label" for="intake-followup">Follow-Up Date</label>
          <input type="date" id="intake-followup" class="text-input">
        </div>
      </div>

      <div id="referral-field-group" style="display: none; margin-bottom: 14px; background: #eff6ff; padding: 10px; border-radius: var(--rounded-sm); border: 1px solid #bfdbfe;">
        <label class="form-label" for="intake-referral" style="color: #1e40af;">Referral Target Facility / Hospital</label>
        <input type="text" id="intake-referral" placeholder="e.g. Rodriguez Rural Health Unit / Casimiro A. Ynares Memorial Hospital" class="text-input">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); margin-top: 16px;">
        <button type="button" class="button-outline" onclick="document.getElementById('intake-modal').close();">Cancel</button>
        <button type="submit" class="button-primary" id="btn-save-intake">Save Clinical Record</button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================= -->
  <!-- MODAL: DOH CLINICAL REFERRAL SLIP PRINT VIEW            -->
  <!-- ======================================================= -->
  <dialog id="referral-modal" class="modal-dialog" style="max-width: 860px; width: 95%; border-radius: var(--rounded-md); border: 1px solid var(--color-hairline); background: var(--color-canvas); padding: 0; box-shadow: 0 24px 48px rgba(0, 0, 0, 0.2);">
    <div style="padding: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); display: flex; justify-content: space-between; align-items: center;" class="no-print">
      <div style="display: flex; align-items: center; gap: 8px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
        <h3 class="typography-heading-3" style="font-size: 1.0625rem;">Official DOH Clinical Referral Slip</h3>
      </div>
      <div style="display: flex; align-items: center; gap: 8px;">
        <button type="button" class="button-primary" onclick="window.print();" style="height: 34px; padding: 0 14px; font-size: 0.8125rem;">
          Print Referral Letter
        </button>
        <button type="button" class="icon-button" onclick="document.getElementById('referral-modal').close();" aria-label="Close modal">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>

    <div style="padding: 24px; max-height: 80vh; overflow-y: auto;">
      <div class="referral-slip" id="print-referral-slip">
        <!-- Official Header -->
        <div class="doh-header">
          <div class="doh-seal-row">
            <div style="font-size: 1.5rem;">🇵🇭</div>
            <div>
              <div style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.1em; color: #444;">Republic of the Philippines &bull; Department of Health</div>
              <div style="font-size: 1.125rem; font-weight: 800; text-transform: uppercase; color: #000; letter-spacing: 0.05em; margin: 2px 0;">Barangay Health Station</div>
              <div style="font-size: 0.75rem; color: #333;">Barangay San Isidro, Rodriguez (Montalban), Rizal</div>
            </div>
            <div style="font-size: 1.5rem;">🏥</div>
          </div>
          <div style="font-size: 0.875rem; font-weight: 700; text-transform: uppercase; margin-top: 10px; text-decoration: underline; letter-spacing: 0.05em;">
            PATIENT CLINICAL REFERRAL FORM
          </div>
        </div>

        <!-- Referral Meta -->
        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 16px; border-bottom: 1px solid #141414; padding-bottom: 8px;">
          <div><strong>Control Ref No:</strong> <span id="ref-print-code">HLTH-2026-00000</span></div>
          <div><strong>Referral Date:</strong> <span id="ref-print-date">September 6, 2026</span></div>
        </div>

        <!-- Receiving Target -->
        <div style="background: #f8f9fa; border: 1px solid #ddd; padding: 10px 14px; border-radius: 4px; margin-bottom: 16px;">
          <div style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #666;">TO RECEIVING HEALTH FACILITY:</div>
          <div id="ref-print-facility" style="font-size: 0.9375rem; font-weight: 700; color: #000;">
            Rodriguez Rural Health Unit / District Hospital
          </div>
        </div>

        <!-- Patient Demographics Grid -->
        <div class="ref-field-grid">
          <div class="ref-field">
            <div class="ref-label">Patient Full Name</div>
            <div class="ref-value" id="ref-print-name">--</div>
          </div>
          <div class="ref-field">
            <div class="ref-label">Age / Sex / Civil Status</div>
            <div class="ref-value" id="ref-print-demog">--</div>
          </div>
          <div class="ref-field">
            <div class="ref-label">Residential Address &amp; Purok</div>
            <div class="ref-value" id="ref-print-address">--</div>
          </div>
          <div class="ref-field">
            <div class="ref-label">Contact / Emergency Phone</div>
            <div class="ref-value" id="ref-print-contact">--</div>
          </div>
        </div>

        <!-- Vitals Snapshot -->
        <div style="margin-bottom: 16px;">
          <div style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #666; margin-bottom: 6px;">
            BASELINE VITAL SIGNS
          </div>
          <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; text-align: center; border: 1px solid #ddd; padding: 8px; border-radius: 4px;">
            <div>
              <div style="font-size: 0.625rem; color: #666;">Blood Pressure</div>
              <div style="font-weight: 700; font-size: 0.8125rem;" id="ref-print-bp">--</div>
            </div>
            <div>
              <div style="font-size: 0.625rem; color: #666;">Temperature</div>
              <div style="font-weight: 700; font-size: 0.8125rem;" id="ref-print-temp">--</div>
            </div>
            <div>
              <div style="font-size: 0.625rem; color: #666;">Weight</div>
              <div style="font-weight: 700; font-size: 0.8125rem;" id="ref-print-weight">--</div>
            </div>
            <div>
              <div style="font-size: 0.625rem; color: #666;">Height</div>
              <div style="font-weight: 700; font-size: 0.8125rem;" id="ref-print-height">--</div>
            </div>
            <div>
              <div style="font-size: 0.625rem; color: #666;">Computed BMI</div>
              <div style="font-weight: 700; font-size: 0.8125rem;" id="ref-print-bmi">--</div>
            </div>
          </div>
        </div>

        <!-- Clinical Notes -->
        <div style="margin-bottom: 14px;">
          <div class="ref-label">Chief Complaint &amp; History of Present Illness</div>
          <div id="ref-print-complaint" style="font-size: 0.8125rem; line-height: 1.4; padding: 6px 0; border-bottom: 1px solid #eee;">--</div>
        </div>

        <div style="margin-bottom: 14px;">
          <div class="ref-label">Clinical Assessment &amp; Initial Care Rendered</div>
          <div id="ref-print-notes" style="font-size: 0.8125rem; line-height: 1.4; padding: 6px 0; border-bottom: 1px solid #eee;">--</div>
        </div>

        <div style="margin-bottom: 16px;">
          <div class="ref-label">Reason for Referral</div>
          <div id="ref-print-reason" style="font-size: 0.8125rem; line-height: 1.4; padding: 6px 0; font-style: italic;">
            Specialist medical evaluation, diagnostic laboratory workup, and advanced therapeutic management.
          </div>
        </div>

        <!-- Signatories -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 36px; padding-top: 16px; border-top: 1px solid #141414;">
          <div style="text-align: center;">
            <div id="ref-print-staff" style="font-weight: 700; font-size: 0.875rem; text-decoration: underline;">Barangay Health Worker</div>
            <div style="font-size: 0.6875rem; color: #666; margin-top: 2px;">Attending BHW / Public Health Nurse</div>
          </div>
          <div style="text-align: center;">
            <div style="font-weight: 700; font-size: 0.875rem; text-decoration: underline;">Hon. Barangay Captain</div>
            <div style="font-size: 0.6875rem; color: #666; margin-top: 2px;">Punong Barangay / Health Committee Chair</div>
          </div>
        </div>
      </div>
    </div>
  </dialog>

  <!-- ======================================================= -->
  <!-- MODAL: ADD / RESTOCK MEDICINE                           -->
  <!-- ======================================================= -->
  <dialog id="medicine-modal" class="modal-dialog" style="max-width: 500px; width: 90%; border-radius: var(--rounded-md); border: 1px solid var(--color-hairline); background: var(--color-canvas); padding: 0; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);">
    <div style="padding: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); display: flex; justify-content: space-between; align-items: center;">
      <h3 class="typography-heading-3" style="font-size: 1.0625rem;" id="med-modal-title">Add Medicine to Pharmacy</h3>
      <button type="button" class="icon-button" onclick="document.getElementById('medicine-modal').close();">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form id="medicine-form" onsubmit="handleMedicineSubmit(event);" style="padding: var(--spacing-md);">
      <div style="margin-bottom: 12px;">
        <label class="form-label" for="med-name">Brand Name *</label>
        <input type="text" id="med-name" placeholder="e.g. Amlodipine Besylate" class="text-input" required>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="med-generic">Generic Name *</label>
        <input type="text" id="med-generic" placeholder="e.g. Amlodipine" class="text-input" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-sm); margin-bottom: 12px;">
        <div>
          <label class="form-label" for="med-category">Category *</label>
          <select id="med-category" class="text-input" required>
            <option value="Maintenance - Hypertension">Maintenance - Hypertension</option>
            <option value="Maintenance - Diabetes">Maintenance - Diabetes</option>
            <option value="Antibiotics">Antibiotics</option>
            <option value="Analgesic / Fever">Analgesic / Fever</option>
            <option value="Pediatric / Vitamins">Pediatric / Vitamins</option>
            <option value="Oral Rehydration">Oral Rehydration</option>
            <option value="First Aid">First Aid</option>
          </select>
        </div>
        <div>
          <label class="form-label" for="med-dosage">Dosage *</label>
          <input type="text" id="med-dosage" placeholder="e.g. 5mg" class="text-input" required>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-sm); margin-bottom: 12px;">
        <div>
          <label class="form-label" for="med-stock">Initial Stock Quantity *</label>
          <input type="number" id="med-stock" placeholder="100" class="text-input" min="0" required>
        </div>
        <div>
          <label class="form-label" for="med-unit">Unit *</label>
          <select id="med-unit" class="text-input" required>
            <option value="tablets">tablets</option>
            <option value="capsules">capsules</option>
            <option value="bottles">bottles</option>
            <option value="sachets">sachets</option>
            <option value="vials">vials</option>
          </select>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-sm); margin-bottom: 14px;">
        <div>
          <label class="form-label" for="med-reorder">Reorder Threshold</label>
          <input type="number" id="med-reorder" value="30" class="text-input" min="5" required>
        </div>
        <div>
          <label class="form-label" for="med-expiry">Expiry Date *</label>
          <input type="date" id="med-expiry" class="text-input" required>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs); margin-top: 14px;">
        <button type="button" class="button-outline" onclick="document.getElementById('medicine-modal').close();">Cancel</button>
        <button type="submit" class="button-primary">Save to Inventory</button>
      </div>
    </form>
  </dialog>

  <!-- ======================================================= -->
  <!-- MODAL: SEND REFILL SMS                                  -->
  <!-- ======================================================= -->
  <dialog id="refill-sms-modal" class="modal-dialog" style="max-width: 500px; width: 90%; border-radius: var(--rounded-md); border: 1px solid var(--color-hairline); background: var(--color-canvas); padding: 0; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);">
    <div style="padding: var(--spacing-md); border-bottom: 1px solid var(--color-hairline-soft); display: flex; justify-content: space-between; align-items: center;">
      <h3 class="typography-heading-3" style="font-size: 1.0625rem;">Dispatch Maintenance Refill SMS</h3>
      <button type="button" class="icon-button" onclick="document.getElementById('refill-sms-modal').close();">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form id="refill-sms-form" onsubmit="handleSendRefillSmsSubmit(event);" style="padding: var(--spacing-md);">
      <div style="margin-bottom: 12px;">
        <label class="form-label" for="refill-patient-select">Registered Senior / Patient *</label>
        <select id="refill-patient-select" class="text-input" required>
          <option value="">-- Choose recipient --</option>
        </select>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="form-label" for="refill-med-select">Maintenance Medicine *</label>
        <select id="refill-med-select" class="text-input" required>
          <option value="">-- Select medicine --</option>
        </select>
      </div>

      <div style="background: var(--color-canvas-soft); padding: 12px; border-radius: var(--rounded-sm); font-size: 0.75rem; color: var(--color-text-muted); line-height: 1.4; margin-bottom: 14px;">
        <strong style="color: var(--color-ink);">SMS Template Preview:</strong><br>
        "PABATID mula sa Barangay Health Station: Magandang araw [Name], nakatakda na po ang inyong regular maintenance medicine refill ([Medicine]). Maaari po kayong magtungo sa Barangay Clinic Lunes hanggang Biyernes, 8:00 AM - 5:00 PM."
      </div>

      <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs);">
        <button type="button" class="button-outline" onclick="document.getElementById('refill-sms-modal').close();">Cancel</button>
        <button type="submit" class="button-primary" id="btn-dispatch-refill">Send SMS Alert</button>
      </div>
    </form>
  </dialog>

  <!-- Script dependencies: REST API Bridge first for PHP/MySQL parity -->
  <script src="js/api.js"></script>
  <script src="js/auth.js"></script>
  <script src="js/components/sidebar.js"></script>
  <script src="js/components/header.js"></script>

  <script>
    // State management
    let allConsultations = [];
    let allMedicines = [];
    let allResidents = [];

    document.addEventListener('DOMContentLoaded', async () => {
      // 1. Render App Shell Navigation
      if (window.AppSidebar) {
        await AppSidebar.render('health');
      }

      // 2. Load Residents and Data via REST API
      await loadResidents();
      await refreshAllData();
    });

    // Switch Studio Tabs
    window.switchHealthTab = function(tabName) {
      const tabs = ['consultations', 'epi', 'opt', 'pharmacy'];
      tabs.forEach(t => {
        const pane = document.getElementById(`view-${t}`);
        const btn = document.getElementById(`tab-btn-${t}`);
        if (pane) pane.style.display = (t === tabName) ? 'block' : 'none';
        if (btn) btn.classList.toggle('active', t === tabName);
      });
    };

    // Load registered residents for selectors
    async function loadResidents() {
      try {
        if (window.barangayDB) {
          allResidents = await barangayDB.getAll('residents') || [];
        }
        const intakeSelect = document.getElementById('intake-resident-id');
        const optSelect = document.getElementById('opt-resident-id');
        const refillSelect = document.getElementById('refill-patient-select');

        if (intakeSelect) {
          intakeSelect.innerHTML = '<option value="">-- Select Resident --</option>' +
            allResidents.map(r => `<option value="${r.id}">${r.firstName || r.first_name} ${r.lastName || r.last_name} (${r.purok || 'Purok 1'})</option>`).join('');
        }

        if (optSelect) {
          optSelect.innerHTML = '<option value="">-- Choose child from registry --</option>' +
            allResidents.map(r => `<option value="${r.id}">${r.firstName || r.first_name} ${r.lastName || r.last_name}</option>`).join('');
        }

        if (refillSelect) {
          refillSelect.innerHTML = '<option value="">-- Choose recipient --</option>' +
            allResidents.map(r => `<option value="${r.id}">${r.firstName || r.first_name} ${r.lastName || r.last_name} (${r.phone || r.contact_no || 'No Contact'})</option>`).join('');
        }
      } catch (err) {
        console.error('Failed to load residents:', err);
      }
    }

    // Refresh all tables and telemetry via REST API
    async function refreshAllData() {
      try {
        if (window.barangayDB) {
          allConsultations = await barangayDB.getAll('health_records') || [];
          allMedicines = await barangayDB.getAll('health_medicines') || [];
        }

        // Fetch telemetry stats directly from API if available
        try {
          const statsRes = await fetch('api/health.php?action=stats');
          if (statsRes.ok) {
            const json = await statsRes.json();
            if (json.data) {
              const s = json.data;
              document.getElementById('stat-total-consultations').textContent = (s.total || 0).toLocaleString();
              document.getElementById('stat-total-patients').textContent = (s.total_patients || 0).toLocaleString();
              document.getElementById('stat-opt-malnourished').textContent = (s.opt_malnourished || 0).toLocaleString();
              document.getElementById('stat-total-medicines').textContent = (s.total_meds_stock || 0).toLocaleString();
              document.getElementById('stat-sub-medicines').textContent = `Units available \u2022 ${s.low_stock_medicines || 0} low stock`;

              const lowBadge = document.getElementById('stat-low-stock-badge');
              if (lowBadge) {
                if ((s.low_stock_medicines || 0) > 0) {
                  lowBadge.textContent = `${s.low_stock_medicines} Need Restock`;
                  lowBadge.style.color = '#ef4444';
                } else {
                  lowBadge.textContent = 'Normal';
                  lowBadge.style.color = '#10b981';
                }
              }
            }
          }
        } catch (e) {
          updateTelemetryLocal();
        }

        // Populate Refill Medicine Select
        const refillMedSelect = document.getElementById('refill-med-select');
        if (refillMedSelect) {
          refillMedSelect.innerHTML = '<option value="">-- Select medicine --</option>' +
            allMedicines.map(m => `<option value="${m.medicineName || m.medicine_name}">${m.medicineName || m.medicine_name} (${m.dosage}) - Stock: ${m.stockQuantity || m.stock_quantity}</option>`).join('');
        }

        renderConsultationsTable();
        renderOptRosterTable();
        renderMedicinesTable();
      } catch (err) {
        console.error('Failed to refresh data:', err);
      }
    }

    // Local telemetry fallback
    function updateTelemetryLocal() {
      const totalConsultations = allConsultations.length;
      document.getElementById('stat-total-consultations').textContent = totalConsultations.toLocaleString();

      const patientIds = new Set(allConsultations.map(c => c.residentId || c.resident_id));
      document.getElementById('stat-total-patients').textContent = patientIds.size.toLocaleString();

      const malnourished = allConsultations.filter(c => 
        (c.serviceType || c.service_type) === 'Nutrition OPT Plus' && 
        ((c.clinicalNotes || '').toLowerCase().includes('underweight') || (c.clinicalNotes || '').toLowerCase().includes('stunted') || (c.clinicalNotes || '').toLowerCase().includes('wasted'))
      );
      document.getElementById('stat-opt-malnourished').textContent = malnourished.length.toLocaleString();

      const totalUnits = allMedicines.reduce((sum, m) => sum + (parseInt(m.stockQuantity || m.stock_quantity) || 0), 0);
      const lowStockCount = allMedicines.filter(m => (parseInt(m.stockQuantity || m.stock_quantity) || 0) <= (parseInt(m.reorderLevel || m.reorder_level) || 30)).length;

      document.getElementById('stat-total-medicines').textContent = totalUnits.toLocaleString();
      document.getElementById('stat-sub-medicines').textContent = `Units available \u2022 ${lowStockCount} low stock`;
    }

    // Render Consultations Table
    function renderConsultationsTable(recordsToRender = null) {
      const records = recordsToRender || allConsultations;
      const tbody = document.getElementById('consultations-table-body');
      document.getElementById('consultations-count-text').textContent = records.length;

      if (!tbody) return;

      if (records.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="8" style="text-align: center; padding: 32px; color: var(--color-text-muted);">
              No clinical consultations match your filter criteria.
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = records.map(rec => {
        const res = allResidents.find(r => r.id === (rec.residentId || rec.resident_id)) || {};
        const patientName = `${res.firstName || res.first_name || rec.first_name || 'Resident'} ${res.lastName || res.last_name || rec.last_name || '#' + (rec.residentId || rec.resident_id)}`;
        const purok = res.purok || rec.purok || 'Purok 1';

        // BMI Calculation if weight and height exist
        let bmiDisplay = '--';
        const w = parseFloat(rec.weightKg || rec.weight_kg);
        const h = parseFloat(rec.heightCm || rec.height_cm);
        if (w && h) {
          const hM = h / 100;
          const bmi = (w / (hM * hM)).toFixed(1);
          bmiDisplay = `BMI ${bmi}`;
        }

        const vitals = `${rec.bp || '--'} | ${rec.temperature ? rec.temperature + '&deg;C' : '--'} | ${bmiDisplay}`;

        let statusBadge = 'badge-neutral';
        const st = rec.status || 'Completed';
        if (st === 'Completed') statusBadge = 'badge-neutral';
        if (st === 'Follow-Up Needed') statusBadge = 'badge-neutral';
        if (st === 'Referred to RHU / Hospital') statusBadge = 'badge-neutral';

        return `
          <tr>
            <td>
              <strong style="font-family: monospace; font-size: 0.8125rem;">${rec.recordNo || rec.record_no || 'HLTH-' + rec.id}</strong>
              <div class="typography-caption" style="font-size: 0.6875rem; color: var(--color-text-muted);">
                ${new Date(rec.createdAt || rec.created_at || Date.now()).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
              </div>
            </td>
            <td>
              <div style="font-weight: 600;">${patientName}</div>
              <div class="typography-caption" style="color: var(--color-text-muted);">${purok}</div>
            </td>
            <td>
              <span class="badge-neutral" style="font-size: 0.6875rem;">${rec.serviceType || rec.service_type || 'General Consultation'}</span>
            </td>
            <td>
              <div style="font-size: 0.75rem; font-weight: 600;">${vitals}</div>
            </td>
            <td style="max-width: 220px;">
              <div style="font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${rec.chiefComplaint || rec.chief_complaint}">
                ${rec.chiefComplaint || rec.chief_complaint || '--'}
              </div>
              <div class="typography-caption" style="color: var(--color-text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                ${rec.clinicalNotes || rec.clinical_notes || 'No assessment notes'}
              </div>
            </td>
            <td>
              <div style="font-size: 0.75rem;">${rec.attendingStaff || rec.attending_staff || 'BHW'}</div>
            </td>
            <td>
              <span class="${statusBadge}" style="font-size: 0.6875rem; ${st.includes('Referred') ? 'color: #0066ff; font-weight: 600;' : (st.includes('Follow-Up') ? 'color: #f59e0b; font-weight: 600;' : '')}">
                ${st}
              </span>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <button class="button-outline" onclick="openReferralSlipModal(${rec.id});" style="height: 28px; padding: 0 8px; font-size: 0.6875rem; margin-right: 4px;" title="View &amp; Print Official Referral Slip">
                Referral Slip
              </button>
              <button class="icon-button" onclick="deleteConsultationRecord(${rec.id});" style="width: 28px; height: 28px; color: #ef4444;" title="Delete Record">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Filter consultations
    window.filterConsultations = function() {
      const q = (document.getElementById('consultation-search').value || '').toLowerCase().trim();
      const service = document.getElementById('consultation-service-filter').value;
      const status = document.getElementById('consultation-status-filter').value;

      const filtered = allConsultations.filter(rec => {
        const res = allResidents.find(r => r.id === (rec.residentId || rec.resident_id)) || {};
        const patientName = `${res.firstName || res.first_name || rec.first_name || ''} ${res.lastName || res.last_name || rec.last_name || ''}`.toLowerCase();
        const recNo = (rec.recordNo || rec.record_no || '').toLowerCase();
        const complaint = (rec.chiefComplaint || rec.chief_complaint || '').toLowerCase();

        const matchQ = !q || patientName.includes(q) || recNo.includes(q) || complaint.includes(q);
        const matchService = !service || (rec.serviceType || rec.service_type) === service;
        const matchStatus = !status || (rec.status || '') === status;

        return matchQ && matchService && matchStatus;
      });

      renderConsultationsTable(filtered);
    };

    // Render Pharmacy Medicines Table
    function renderMedicinesTable(medsToRender = null) {
      const meds = medsToRender || allMedicines;
      const tbody = document.getElementById('medicines-table-body');
      if (!tbody) return;

      if (meds.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="8" style="text-align: center; padding: 32px; color: var(--color-text-muted);">
              No pharmaceutical inventory found.
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = meds.map(m => {
        const stock = parseInt(m.stockQuantity || m.stock_quantity) || 0;
        const reorder = parseInt(m.reorderLevel || m.reorder_level) || 30;
        const isLow = stock <= reorder;

        return `
          <tr>
            <td>
              <div style="font-weight: 600;">${m.medicineName || m.medicine_name}</div>
              <div class="typography-caption" style="color: var(--color-text-muted); font-style: italic;">
                ${m.genericName || m.generic_name}
              </div>
            </td>
            <td>
              <span class="badge-neutral" style="font-size: 0.6875rem;">${m.category}</span>
            </td>
            <td>
              <div style="font-weight: 500;">${m.dosage}</div>
              <div class="typography-caption" style="color: var(--color-text-muted);">${m.unit}</div>
            </td>
            <td>
              <div style="font-weight: 700; font-size: 0.9375rem; color: ${isLow ? '#ef4444' : 'inherit'};">
                ${stock}
              </div>
            </td>
            <td>
              <span class="badge-neutral" style="font-size: 0.6875rem; ${isLow ? 'color: #ef4444; border-color: #fca5a5;' : 'color: #10b981;'}">
                ${isLow ? 'Reorder Needed' : 'In Stock'}
              </span>
            </td>
            <td>
              <div style="font-size: 0.75rem;">${m.expiryDate || m.expiry_date}</div>
            </td>
            <td>
              <div style="font-family: monospace; font-size: 0.75rem;">${m.batchNo || m.batch_no || 'BATCH-2026'}</div>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <button class="button-outline" onclick="quickRestockMedicine(${m.id});" style="height: 28px; padding: 0 8px; font-size: 0.6875rem; margin-right: 4px;">
                + Restock
              </button>
              <button class="button-outline" onclick="quickSendRefillForMed('${m.medicineName || m.medicine_name}');" style="height: 28px; padding: 0 8px; font-size: 0.6875rem;">
                SMS Alert
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Filter Medicines
    window.filterMedicines = function() {
      const q = (document.getElementById('medicine-search').value || '').toLowerCase().trim();
      const cat = document.getElementById('medicine-category-filter').value;

      const filtered = allMedicines.filter(m => {
        const name = (m.medicineName || m.medicine_name || '').toLowerCase();
        const generic = (m.genericName || m.generic_name || '').toLowerCase();
        const matchQ = !q || name.includes(q) || generic.includes(q);
        const matchCat = !cat || m.category === cat;
        return matchQ && matchCat;
      });

      renderMedicinesTable(filtered);
    };

    // Render OPT+ Malnutrition Roster Table
    function renderOptRosterTable() {
      const tbody = document.getElementById('opt-roster-table-body');
      const rosterCountEl = document.getElementById('opt-roster-count');
      if (!tbody) return;

      const optRecords = allConsultations.filter(c => (c.serviceType || c.service_type) === 'Nutrition OPT Plus');
      if (rosterCountEl) rosterCountEl.textContent = `${optRecords.length} Profiled`;

      if (optRecords.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="5" style="text-align: center; padding: 24px; color: var(--color-text-muted);">
              No children currently enrolled in OPT+ monitoring.
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = optRecords.map(rec => {
        const res = allResidents.find(r => r.id === (rec.residentId || rec.resident_id)) || {};
        const name = `${res.firstName || res.first_name || rec.first_name || 'Child'} ${res.lastName || res.last_name || rec.last_name || '#' + (rec.residentId || rec.resident_id)}`;
        const purok = res.purok || rec.purok || 'Purok 1';

        return `
          <tr>
            <td><strong>${name}</strong></td>
            <td>${(rec.weightKg || rec.weight_kg) ? (rec.weightKg || rec.weight_kg) + ' kg' : '--'}</td>
            <td><span class="badge-neutral" style="font-size: 0.6875rem; color: #f59e0b;">${rec.chiefComplaint || rec.chief_complaint || 'Monitored'}</span></td>
            <td>${purok}</td>
            <td class="typography-caption" style="max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
              ${rec.clinicalNotes || rec.clinical_notes || 'Feeding program'}
            </td>
          </tr>
        `;
      }).join('');
    }

    // Live BMI updater inside Intake modal
    window.updateIntakeBmi = function() {
      const w = parseFloat(document.getElementById('intake-weight').value);
      const h = parseFloat(document.getElementById('intake-height').value);
      const bmiVal = document.getElementById('intake-bmi-val');
      const bmiBadge = document.getElementById('intake-bmi-badge');

      if (w > 0 && h > 0) {
        const hM = h / 100;
        const bmi = (w / (hM * hM)).toFixed(1);
        bmiVal.textContent = bmi;

        if (bmi < 18.5) {
          bmiBadge.textContent = 'Underweight';
          bmiBadge.style.color = '#f59e0b';
        } else if (bmi <= 24.9) {
          bmiBadge.textContent = 'Normal';
          bmiBadge.style.color = '#10b981';
        } else if (bmi <= 29.9) {
          bmiBadge.textContent = 'Overweight';
          bmiBadge.style.color = '#f59e0b';
        } else {
          bmiBadge.textContent = 'Obese';
          bmiBadge.style.color = '#ef4444';
        }
      } else {
        bmiVal.textContent = '--';
        bmiBadge.textContent = 'Awaiting inputs';
        bmiBadge.style.color = 'inherit';
      }
    };

    // Toggle referral facility field
    window.toggleReferralInput = function() {
      const st = document.getElementById('intake-status').value;
      const refGroup = document.getElementById('referral-field-group');
      if (refGroup) {
        refGroup.style.display = (st === 'Referred to RHU / Hospital') ? 'block' : 'none';
      }
    };

    // Open Intake Modal
    window.openIntakeModal = function() {
      document.getElementById('intake-form').reset();
      updateIntakeBmi();
      toggleReferralInput();
      document.getElementById('intake-modal').showModal();
    };

    // Handle Intake Submission
    window.handleIntakeSubmit = async function(e) {
      e.preventDefault();
      const residentId = parseInt(document.getElementById('intake-resident-id').value);
      const serviceType = document.getElementById('intake-service-type').value;
      const bp = document.getElementById('intake-bp').value.trim();
      const temp = parseFloat(document.getElementById('intake-temp').value) || null;
      const weight = parseFloat(document.getElementById('intake-weight').value) || null;
      const height = parseFloat(document.getElementById('intake-height').value) || null;
      const complaint = document.getElementById('intake-complaint').value.trim();
      const notes = document.getElementById('intake-notes').value.trim();
      const dispensed = document.getElementById('intake-medicines').value.trim();
      const staff = document.getElementById('intake-staff').value.trim() || 'Barangay Health Worker';
      const status = document.getElementById('intake-status').value;
      const followup = document.getElementById('intake-followup').value || null;
      const referral = document.getElementById('intake-referral').value.trim();

      const btn = document.getElementById('btn-save-intake');
      btn.disabled = true;
      btn.textContent = 'Saving Consultation...';

      const rec = {
        resident_id: residentId,
        service_type: serviceType,
        bp: bp || 'N/A',
        temperature: temp,
        weight_kg: weight,
        height_cm: height,
        chief_complaint: complaint,
        clinical_notes: notes,
        medicines_dispensed: dispensed,
        attending_staff: staff,
        status: status,
        referral_target: referral || null,
        follow_up_date: followup
      };

      try {
        if (window.barangayDB) {
          await barangayDB.add('health_records', rec);
        }
        btn.disabled = false;
        btn.textContent = 'Save Clinical Record';
        document.getElementById('intake-modal').close();

        await refreshAllData();
        alert('Consultation logged successfully into MySQL registry!');
      } catch (err) {
        btn.disabled = false;
        btn.textContent = 'Save Clinical Record';
        alert('Error saving record: ' + err.message);
      }
    };

    // Open DOH Clinical Referral Slip Modal
    window.openReferralSlipModal = function(id) {
      const rec = allConsultations.find(c => c.id === id);
      if (!rec) return;

      const res = allResidents.find(r => r.id === (rec.residentId || rec.resident_id)) || {};
      const fullName = `${res.firstName || res.first_name || rec.first_name || 'Resident'} ${res.middleName || res.middle_name || rec.middle_name || ''} ${res.lastName || res.last_name || rec.last_name || ''}`;

      document.getElementById('ref-print-code').textContent = rec.recordNo || rec.record_no || 'HLTH-' + rec.id;
      document.getElementById('ref-print-date').textContent = new Date(rec.createdAt || rec.created_at || Date.now()).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
      document.getElementById('ref-print-facility').textContent = rec.referralTarget || rec.referral_target || 'Rodriguez Rural Health Unit (RHU) / District Hospital';
      document.getElementById('ref-print-name').textContent = fullName;

      // Demographics
      const bdate = res.birthdate || rec.birthdate;
      const age = bdate ? (new Date().getFullYear() - new Date(bdate).getFullYear()) + ' yrs old' : 'Adult';
      const sex = res.gender || rec.gender || 'Not specified';
      const civ = res.civilStatus || res.civil_status || rec.civil_status || 'Single';
      document.getElementById('ref-print-demog').textContent = `${age} / ${sex} / ${civ}`;

      document.getElementById('ref-print-address').textContent = `${res.street || res.address || rec.street || ''}, ${res.purok || rec.purok || 'Purok 1'}, San Isidro`;
      document.getElementById('ref-print-contact').textContent = res.phone || res.contact_no || rec.contact_no || 'N/A';

      // Vitals
      document.getElementById('ref-print-bp').textContent = rec.bp || '120/80';
      document.getElementById('ref-print-temp').textContent = rec.temperature ? rec.temperature + ' °C' : '36.6 °C';
      document.getElementById('ref-print-weight').textContent = (rec.weightKg || rec.weight_kg) ? (rec.weightKg || rec.weight_kg) + ' kg' : '--';
      document.getElementById('ref-print-height').textContent = (rec.heightCm || rec.height_cm) ? (rec.heightCm || rec.height_cm) + ' cm' : '--';

      let bmiText = '--';
      const w = parseFloat(rec.weightKg || rec.weight_kg);
      const h = parseFloat(rec.heightCm || rec.height_cm);
      if (w && h) {
        const hm = h / 100;
        bmiText = (w / (hm * hm)).toFixed(1);
      }
      document.getElementById('ref-print-bmi').textContent = bmiText;

      document.getElementById('ref-print-complaint').textContent = rec.chiefComplaint || rec.chief_complaint || 'General clinical consultation';
      document.getElementById('ref-print-notes').textContent = rec.clinicalNotes || rec.clinical_notes || 'Patient evaluated at Barangay Health Station; initial first aid provided.';
      document.getElementById('ref-print-staff').textContent = rec.attendingStaff || rec.attending_staff || 'Barangay Health Worker';

      document.getElementById('referral-modal').showModal();
    };

    // Delete Consultation Record
    window.deleteConsultationRecord = async function(id) {
      if (!confirm('Are you sure you want to remove this health consultation record from MySQL?')) return;
      try {
        if (window.barangayDB) {
          await barangayDB.delete('health_records', id);
        }
        await refreshAllData();
      } catch (err) {
        alert('Delete failed: ' + err.message);
      }
    };

    // Live OPT+ Calculator Status Output
    window.calculateOptStatus = function() {
      const ageMonths = parseInt(document.getElementById('opt-age-months').value) || 0;
      const weight = parseFloat(document.getElementById('opt-weight').value) || 0;
      const height = parseFloat(document.getElementById('opt-height').value) || 0;

      const wfaPill = document.getElementById('opt-wfa-pill');
      const hfaPill = document.getElementById('opt-hfa-pill');
      const wfhPill = document.getElementById('opt-wfh-pill');
      const planEl = document.getElementById('opt-action-plan');

      if (weight <= 0 || height <= 0 || ageMonths < 0) {
        wfaPill.textContent = 'Normal';
        wfaPill.style.color = '#10b981';
        hfaPill.textContent = 'Normal';
        hfaPill.style.color = '#10b981';
        wfhPill.textContent = 'Normal';
        wfhPill.style.color = '#10b981';
        return;
      }

      // Simple WHO median approximate logic
      const expectedWeight = 3.3 + (ageMonths * 0.38);
      const expectedHeight = 50 + (ageMonths * 1.5);

      let wfa = 'Normal';
      let hfa = 'Normal';
      let wfh = 'Normal';

      if (weight < expectedWeight * 0.8) {
        wfa = 'Underweight';
        wfaPill.style.color = '#f59e0b';
      } else {
        wfaPill.style.color = '#10b981';
      }

      if (height < expectedHeight * 0.88) {
        hfa = 'Stunted';
        hfaPill.style.color = '#ef4444';
      } else {
        hfaPill.style.color = '#10b981';
      }

      const ratio = weight / (height / 100);
      if (ratio < 11) {
        wfh = 'Wasted';
        wfhPill.style.color = '#ef4444';
      } else {
        wfhPill.style.color = '#10b981';
      }

      wfaPill.textContent = wfa;
      hfaPill.textContent = hfa;
      wfhPill.textContent = wfh;

      if (wfa !== 'Normal' || hfa !== 'Normal' || wfh !== 'Normal') {
        planEl.innerHTML = `<strong>Priority Intervention Required:</strong> Child meets criteria for targeted feeding. Schedule bi-weekly BHW home monitoring, distribute micro-nutrient supplements, and advise maternal nutrition counseling.`;
        planEl.style.color = '#b45309';
      } else {
        planEl.textContent = 'Action Plan: Routine growth monitoring; maintain age-appropriate balanced diet and micronutrient supplementation.';
        planEl.style.color = 'var(--color-ink-soft)';
      }
    };

    window.onOptResidentChange = function() {
      calculateOptStatus();
    };

    // Handle OPT+ Assessment Submit
    window.handleOptSubmit = async function(e) {
      e.preventDefault();
      const residentId = parseInt(document.getElementById('opt-resident-id').value);
      const ageMonths = document.getElementById('opt-age-months').value;
      const weight = parseFloat(document.getElementById('opt-weight').value);
      const height = parseFloat(document.getElementById('opt-height').value);

      const wfa = document.getElementById('opt-wfa-pill').textContent;
      const hfa = document.getElementById('opt-hfa-pill').textContent;
      const wfh = document.getElementById('opt-wfh-pill').textContent;

      const assessmentNotes = `OPT+ Nutritional Profile: Age ${ageMonths} mos, Weight-for-Age: ${wfa}, Height-for-Age: ${hfa}, Weight-for-Height: ${wfh}. Action plan initiated.`;

      const rec = {
        resident_id: residentId,
        service_type: 'Nutrition OPT Plus',
        bp: 'N/A',
        weight_kg: weight,
        height_cm: height,
        chief_complaint: `OPT+ Growth Screening (${wfa} / ${hfa})`,
        clinical_notes: assessmentNotes,
        attending_staff: 'BHW Nutrition Scholar',
        status: (wfa !== 'Normal' || hfa !== 'Normal') ? 'Follow-Up Needed' : 'Completed'
      };

      try {
        if (window.barangayDB) {
          await barangayDB.add('health_records', rec);
        }
        document.getElementById('opt-calc-form').reset();
        calculateOptStatus();
        await refreshAllData();
        alert('Child Operation Timbang Plus (OPT+) record logged successfully into MySQL!');
      } catch (err) {
        alert('Error saving assessment: ' + err.message);
      }
    };

    // Open Medicine Modal
    window.openNewMedicineModal = function() {
      document.getElementById('medicine-form').reset();
      document.getElementById('med-expiry').value = new Date(Date.now() + 365 * 86400000).toISOString().split('T')[0];
      document.getElementById('medicine-modal').showModal();
    };

    // Handle New Medicine Submit
    window.handleMedicineSubmit = async function(e) {
      e.preventDefault();
      const name = document.getElementById('med-name').value.trim();
      const generic = document.getElementById('med-generic').value.trim();
      const category = document.getElementById('med-category').value;
      const dosage = document.getElementById('med-dosage').value.trim();
      const stock = parseInt(document.getElementById('med-stock').value) || 0;
      const unit = document.getElementById('med-unit').value;
      const reorder = parseInt(document.getElementById('med-reorder').value) || 30;
      const expiry = document.getElementById('med-expiry').value;

      const med = {
        medicine_name: name,
        generic_name: generic,
        category: category,
        dosage: dosage,
        stock_quantity: stock,
        unit: unit,
        reorder_level: reorder,
        expiry_date: expiry,
        batch_no: `BATCH-${new Date().getFullYear()}-${Math.floor(10 + Math.random() * 90)}`
      };

      try {
        if (window.barangayDB) {
          await barangayDB.add('health_medicines', med);
        }
        document.getElementById('medicine-modal').close();
        await refreshAllData();
        alert(`Added ${name} (${stock} ${unit}) to MySQL pharmacy inventory!`);
      } catch (err) {
        alert('Error adding medicine: ' + err.message);
      }
    };

    // Quick Restock Medicine
    window.quickRestockMedicine = async function(id) {
      const med = allMedicines.find(m => m.id === id);
      if (!med) return;

      const addedStr = prompt(`Enter quantity to add for ${med.medicineName || med.medicine_name}:`, '50');
      if (!addedStr) return;
      const added = parseInt(addedStr);
      if (isNaN(added) || added <= 0) return;

      try {
        const res = await fetch('api/health.php?action=restock_medicine', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id, add_quantity: added })
        });
        const json = await res.json();
        if (json.success) {
          await refreshAllData();
          alert(`Restocked ${added} units of ${med.medicineName || med.medicine_name}!`);
        } else {
          alert('Restock failed: ' + (json.message || 'Error'));
        }
      } catch (err) {
        alert('Restock error: ' + err.message);
      }
    };

    // Quick trigger refill SMS modal with medicine pre-selected
    window.quickSendRefillForMed = function(medName) {
      document.getElementById('refill-sms-modal').showModal();
      const select = document.getElementById('refill-med-select');
      if (select) select.value = medName;
    };

    // Open General Refill SMS modal
    window.openSendRefillSmsModal = function() {
      document.getElementById('refill-sms-form').reset();
      document.getElementById('refill-sms-modal').showModal();
    };

    // Handle Send Refill SMS Submit via REST API
    window.handleSendRefillSmsSubmit = async function(e) {
      e.preventDefault();
      const patientId = parseInt(document.getElementById('refill-patient-select').value);
      const medName = document.getElementById('refill-med-select').value;

      const patient = allResidents.find(r => r.id === patientId);
      if (!patient) return;

      const btn = document.getElementById('btn-dispatch-refill');
      btn.disabled = true;
      btn.textContent = 'Dispatching Cellular SMS...';

      try {
        const res = await fetch('api/health.php?action=refill_sms', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            resident_id: patientId,
            medicine_name: medName
          })
        });

        const json = await res.json();
        if (json.success) {
          document.getElementById('refill-sms-modal').close();
          alert(`Maintenance refill reminder dispatched via SMS to ${json.data.recipient} (${json.data.contact})!\nTracking Code: ${json.data.dispatch_code}`);
          await refreshAllData();
        } else {
          alert('Refill SMS dispatch failed: ' + (json.message || 'Error'));
        }
      } catch (err) {
        alert('Network error during dispatch: ' + err.message);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Send SMS Alert';
      }
    };
  </script>
</body>
</html>
