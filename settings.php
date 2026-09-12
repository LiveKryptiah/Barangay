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
  <title>System Settings &bull; Barangay Management System</title>
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
    .settings-nav-bar {
      display: flex;
      background-color: var(--color-canvas-soft);
      border-radius: var(--rounded-full);
      padding: 4px;
      gap: 4px;
      margin-bottom: var(--spacing-md);
      overflow-x: auto;
    }

    .settings-tab-btn {
      flex: 1;
      min-width: 150px;
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

    .settings-tab-btn.active {
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

    .settings-card {
      background-color: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: var(--spacing-md) var(--spacing-lg);
      margin-bottom: var(--spacing-md);
    }

    .settings-card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: var(--spacing-md);
      border-bottom: 1px solid var(--color-hairline-soft);
      padding-bottom: var(--spacing-sm);
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

    @media (max-width: 768px) {
      .form-grid-2, .form-grid-3 {
        grid-template-columns: 1fr;
      }
    }

    /* Live Letterhead Preview */
    .letterhead-preview {
      background: #ffffff;
      color: #111111;
      border: 1px dashed var(--color-hairline);
      border-radius: var(--rounded-md);
      padding: 20px;
      text-align: center;
      margin-top: var(--spacing-md);
      position: relative;
    }

    .logo-uploader-box {
      border: 2px dashed var(--color-hairline);
      border-radius: var(--rounded-md);
      padding: 16px;
      text-align: center;
      cursor: pointer;
      background: var(--color-canvas-soft);
      transition: border-color 0.15s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }

    .logo-uploader-box:hover {
      border-color: var(--color-primary);
    }

    .preview-logo-thumb {
      width: 64px;
      height: 64px;
      object-fit: contain;
      border-radius: 50%;
      border: 1px solid var(--color-hairline);
      background: #ffffff;
    }

    /* Backup Cards */
    .backup-action-card {
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: var(--spacing-md);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: var(--spacing-md);
      background-color: var(--color-canvas);
      transition: border-color 0.15s ease;
    }

    .backup-action-card:hover {
      border-color: var(--color-ink);
    }

    .danger-card {
      border: 1px solid rgba(239, 68, 68, 0.3);
      background-color: rgba(239, 68, 68, 0.02);
      border-radius: var(--rounded-md);
      padding: var(--spacing-md) var(--spacing-lg);
      margin-top: var(--spacing-lg);
    }

    /* Audit Filter Toolbar */
    .audit-filter-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: var(--spacing-xs);
      margin-bottom: var(--spacing-sm);
    }

    .audit-log-badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: var(--rounded-full);
      font-size: 0.6875rem;
      font-weight: 600;
      font-family: monospace;
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
                <h1 class="typography-heading-2">System Settings &amp; Backup.</h1>
                <span class="badge-neutral" id="db-engine-badge">IndexedDB v1</span>
              </div>
              <p class="typography-body-lg">
                Barangay identity customization, clearance fee schedules, database backup/restore, and security logs.
              </p>
            </div>
            <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
              <button class="button-primary" id="btn-quick-export-backup" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="7 10 12 15 17 10"/>
                  <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span>Export JSON Backup</span>
              </button>
            </div>
          </div>
        </section>

        <!-- System Telemetry Ladder -->
        <section>
          <div class="stats-ladder">
            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">ACTIVE JURISDICTION</span>
                <span class="badge-neutral">Local Gov</span>
              </div>
              <div class="stat-number" id="stat-barangay-display" style="font-size: 1.125rem; font-weight: 600; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin-top: 6px;">
                Barangay San Isidro
              </div>
              <div class="typography-caption" id="stat-city-display">City of San Isidro</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">DATABASE RECORDS</span>
                <span class="badge-blue">Total Footprint</span>
              </div>
              <div class="stat-number" id="stat-total-records">0</div>
              <div class="typography-caption" id="stat-sub-records">Across 7 data stores</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">SECURITY AUDIT TRAIL</span>
                <span class="badge-popular">PBKDF2 Hashed</span>
              </div>
              <div class="stat-number" id="stat-audit-count">0</div>
              <div class="typography-caption" id="stat-sub-audit">Recorded security events</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">BACKUP STATUS</span>
                <span class="badge-emerald" id="stat-backup-badge">Ready</span>
              </div>
              <div class="stat-number" id="stat-backup-date" style="font-size: 1.0625rem; font-weight: 600; margin-top: 6px;">
                Not Exported Yet
              </div>
              <div class="typography-caption">Client-side physical file</div>
            </div>
          </div>
        </section>

        <!-- Settings Segmented Navigation Tabs -->
        <nav class="settings-nav-bar" aria-label="Settings Sections">
          <button class="settings-tab-btn active" data-tab="tab-identity">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span>Barangay Identity</span>
          </button>
          <button class="settings-tab-btn" data-tab="tab-fees">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            <span>Clearance Fee Schedule</span>
          </button>
          <button class="settings-tab-btn" data-tab="tab-backup">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="17 8 12 3 7 8"/>
              <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            <span>Backup &amp; Restore</span>
          </button>
          <button class="settings-tab-btn" data-tab="tab-audit">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Audit Trail Logs</span>
          </button>
        </nav>

        <!-- TAB 1: BARANGAY IDENTITY & OFFICIAL SEALS -->
        <div id="tab-identity" class="tab-pane active">
          <div class="settings-card">
            <div class="settings-card-header">
              <div>
                <h3 class="typography-heading-4">Barangay Identity &amp; Government Branding.</h3>
                <p class="typography-caption">
                  Configures the official headers, logos, and jurisdiction names printed on Clearances, Blotter Summons, and Roster Sheets.
                </p>
              </div>
              <button class="button-primary" id="btn-save-identity" style="height: 34px; padding: 0 16px; font-size: 0.75rem;">
                Save Changes
              </button>
            </div>

            <form id="form-identity">
              <div class="form-grid-3" style="margin-bottom: var(--spacing-sm);">
                <div class="form-group">
                  <label class="form-label" for="setting-brgy-name">Barangay Name <span style="color: var(--color-primary); font-weight: 700;">*</span></label>
                  <input type="text" id="setting-brgy-name" class="text-input" placeholder="e.g. Barangay San Isidro" required>
                </div>

                <div class="form-group">
                  <label class="form-label" for="setting-city-name">Municipality / City <span style="color: var(--color-primary); font-weight: 700;">*</span></label>
                  <input type="text" id="setting-city-name" class="text-input" placeholder="e.g. City of San Isidro" required>
                </div>

                <div class="form-group">
                  <label class="form-label" for="setting-province-name">Province / Region <span style="color: var(--color-primary); font-weight: 700;">*</span></label>
                  <input type="text" id="setting-province-name" class="text-input" placeholder="e.g. Metropolitan Manila" required>
                </div>
              </div>

              <div class="form-grid-2" style="margin-bottom: var(--spacing-sm);">
                <div class="form-group">
                  <label class="form-label" for="setting-hall-address">Barangay Hall Physical Address</label>
                  <input type="text" id="setting-hall-address" class="text-input" placeholder="e.g. Barangay Hall Complex, J.P. Rizal St.">
                </div>

                <div class="form-group">
                  <label class="form-label" for="setting-contact-phone">Hotline &amp; Telephone Contacts</label>
                  <input type="text" id="setting-contact-phone" class="text-input" placeholder="e.g. (02) 8123-4567 / 0917-888-9999">
                </div>
              </div>

              <div class="form-grid-2" style="margin-bottom: var(--spacing-sm);">
                <div class="form-group">
                  <label class="form-label" for="setting-contact-email">Official Public Email</label>
                  <input type="email" id="setting-contact-email" class="text-input" placeholder="e.g. office@barangaysanisidro.local">
                </div>

                <div class="form-group">
                  <label class="form-label" for="setting-office-hours">Regular Service Hours</label>
                  <input type="text" id="setting-office-hours" class="text-input" placeholder="e.g. Monday &ndash; Friday, 8:00 AM &ndash; 5:00 PM">
                </div>
              </div>

              <!-- Official Seal / Logo Uploader -->
              <div class="form-group" style="margin-top: var(--spacing-sm); margin-bottom: var(--spacing-md);">
                <label class="form-label">Barangay Official Insignia / Seal</label>
                <div style="display: flex; align-items: center; gap: var(--spacing-md); flex-wrap: wrap;">
                  <div id="logo-preview-wrap" style="display: flex; align-items: center; gap: var(--spacing-sm);">
                    <div id="logo-preview-box" class="nav-brand-icon" style="width: 64px; height: 64px; border-radius: 50%; font-size: 1.5rem; flex-shrink: 0;">
                      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                      </svg>
                    </div>
                  </div>

                  <div style="flex: 1; min-width: 240px;">
                    <div class="logo-uploader-box" id="logo-uploader-dropzone">
                      <input type="file" id="logo-file-input" accept="image/png,image/jpeg,image/svg+xml" style="display: none;">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-primary);">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                      </svg>
                      <span style="font-size: 0.75rem; font-weight: 600;">Click or drop official seal image (PNG, JPG, SVG)</span>
                      <span class="typography-caption" style="font-size: 0.6875rem;">Stored locally in your secure browser database</span>
                    </div>
                  </div>

                  <button type="button" class="table-action-btn danger" id="btn-remove-logo" style="display: none; height: 32px; padding: 0 12px;">
                    Remove Logo
                  </button>
                </div>
              </div>
            </form>

            <!-- Live Letterhead Mockup Preview -->
            <div style="margin-top: var(--spacing-lg);">
              <span class="typography-label" style="color: var(--color-text-muted);">LIVE OFFICIAL LETTERHEAD PREVIEW</span>
              <div class="letterhead-preview" id="letterhead-live-mockup">
                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #666;">Republic of the Philippines</div>
                <div style="font-size: 0.8125rem; font-weight: 700; text-transform: uppercase; color: #333;" id="preview-jurisdiction">
                  Province of Metropolitan Manila &bull; City of San Isidro
                </div>
                <div style="font-size: 1.125rem; font-weight: 800; color: #111; letter-spacing: 0.02em; margin: 4px 0;" id="preview-brgy-title">
                  BARANGAY SAN ISIDRO
                </div>
                <div style="font-size: 0.78125rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #0066ff;">
                  OFFICE OF THE SANGGUNIANG BARANGAY
                </div>
                <div style="font-size: 0.6875rem; color: #666; margin-top: 4px;" id="preview-address-phone">
                  Barangay Hall Complex, J.P. Rizal St. &bull; Tel: (02) 8123-4567
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: CLEARANCE FEE SCHEDULE & POLICIES -->
        <div id="tab-fees" class="tab-pane">
          <div class="settings-card">
            <div class="settings-card-header">
              <div>
                <h3 class="typography-heading-4">Document Fee Schedule &amp; Exemption Policies.</h3>
                <p class="typography-caption">
                  Standardize clearance fees and automated exemption policies for indigent residents.
                </p>
              </div>
              <button class="button-primary" id="btn-save-fees" style="height: 34px; padding: 0 16px; font-size: 0.75rem;">
                Save Fees
              </button>
            </div>

            <form id="form-fees">
              <div class="form-grid-2" style="margin-bottom: var(--spacing-md);">
                <div class="form-group">
                  <label class="form-label" for="fee-brgy-clearance">Barangay Clearance Fee (₱)</label>
                  <input type="number" id="fee-brgy-clearance" class="text-input" min="0" step="1" value="50" required>
                </div>

                <div class="form-group">
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <label class="form-label" for="fee-indigency">Certificate of Indigency (₱)</label>
                    <span class="badge-emerald" style="font-size: 0.625rem;">Statutory Free</span>
                  </div>
                  <input type="number" id="fee-indigency" class="text-input" min="0" step="1" value="0" required>
                </div>

                <div class="form-group">
                  <label class="form-label" for="fee-residency">Certificate of Residency (₱)</label>
                  <input type="number" id="fee-residency" class="text-input" min="0" step="1" value="50" required>
                </div>

                <div class="form-group">
                  <label class="form-label" for="fee-business">Barangay Business Clearance (₱)</label>
                  <input type="number" id="fee-business" class="text-input" min="0" step="1" value="500" required>
                </div>
              </div>

              <div class="form-grid-2" style="margin-bottom: var(--spacing-md);">
                <div class="form-group">
                  <label class="form-label" for="setting-or-prefix">Official Receipt (OR) Series Prefix</label>
                  <input type="text" id="setting-or-prefix" class="text-input" value="OR-2026-" placeholder="e.g. OR-2026-">
                </div>

                <div class="form-group">
                  <label class="form-label" for="setting-validity-days">Standard Clearance Validity (Days)</label>
                  <input type="number" id="setting-validity-days" class="text-input" value="180" min="30" max="365">
                </div>
              </div>

              <!-- Automated Exemption Policy -->
              <div style="background: var(--color-canvas-soft); padding: 14px 18px; border-radius: var(--rounded-sm); margin-bottom: var(--spacing-md);">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.8125rem; font-weight: 600; color: var(--color-ink);">
                  <input type="checkbox" id="setting-auto-waive-indigent" checked style="accent-color: var(--color-primary); width: 16px; height: 16px;">
                  <span>Automated 4Ps / Indigent Fee Waiver</span>
                </label>
                <p class="typography-caption" style="margin-top: 4px; margin-left: 26px;">
                  When enabled, any resident flagged as <strong>Indigent</strong> or <strong>4Ps Beneficiary</strong> automatically has their clearance fee set to ₱0.00 with "INDIGENT WAIVED" printed on the official receipt.
                </p>
              </div>
            </form>
          </div>
        </div>

        <!-- TAB 3: BACKUP, EXPORT & RESTORE -->
        <div id="tab-backup" class="tab-pane">
          <div class="settings-card">
            <div class="settings-card-header">
              <div>
                <h3 class="typography-heading-4">Database Backup, Export &amp; Disaster Recovery.</h3>
                <p class="typography-caption">
                  Create physical, portable JSON database backups to safeguard community records and migrate between computers.
                </p>
              </div>
            </div>

            <!-- Action 1: Export Complete JSON -->
            <div class="backup-action-card" style="margin-bottom: var(--spacing-sm);">
              <div>
                <div style="font-weight: 600; font-size: 0.9375rem; color: var(--color-ink); display: flex; align-items: center; gap: 8px;">
                  <span>Export Full JSON Database Backup</span>
                  <span class="badge-blue">Recommended</span>
                </div>
                <p class="typography-body-sm" style="color: var(--color-text-muted); margin-top: 2px;">
                  Serializes all 7 IndexedDB tables into a single timestamped JSON file saved directly to your Downloads folder.
                </p>
              </div>
              <button class="button-primary" id="btn-export-backup" style="height: 38px; padding: 0 18px; font-size: 0.8125rem; flex-shrink: 0;">
                Download Backup
              </button>
            </div>

            <!-- Action 2: Restore from JSON File -->
            <div class="backup-action-card" style="margin-bottom: var(--spacing-md);">
              <div>
                <div style="font-weight: 600; font-size: 0.9375rem; color: var(--color-ink); display: flex; align-items: center; gap: 8px;">
                  <span>Restore Database from File</span>
                  <span class="badge-amber">Transaction Safe</span>
                </div>
                <p class="typography-body-sm" style="color: var(--color-text-muted); margin-top: 2px;">
                  Upload a previously saved BarangayOS JSON backup to restore or append community records.
                </p>
              </div>
              <div style="flex-shrink: 0;">
                <input type="file" id="restore-file-input" accept=".json,application/json" style="display: none;">
                <button class="button-outline" id="btn-trigger-restore" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
                  Select Backup File &rarr;
                </button>
              </div>
            </div>

            <!-- Danger Zone -->
            <div class="danger-card">
              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                  <line x1="12" y1="9" x2="12" y2="13"/>
                  <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <h4 class="typography-heading-4" style="color: #ef4444; font-size: 0.9375rem; margin: 0;">Danger Zone: Database Reset</h4>
              </div>
              <p class="typography-body-sm" style="color: var(--color-text-muted); margin-bottom: var(--spacing-md);">
                Purge specific modules or perform a factory reset. These actions are irreversible and require strict text confirmation.
              </p>
              <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-xs);">
                <button class="table-action-btn danger" onclick="confirmPurgeStore('blotter_cases', 'Blotter Cases');">
                  Purge Blotter Cases
                </button>
                <button class="table-action-btn danger" onclick="confirmPurgeStore('incidents', 'Incidents & Dispatch');">
                  Purge Incidents &amp; Patrol
                </button>
                <button class="table-action-btn danger" onclick="confirmPurgeStore('households', 'Households');">
                  Purge Households
                </button>
                <button class="table-action-btn danger" onclick="confirmPurgeStore('certificates', 'Certificates');">
                  Purge Certificates
                </button>
                <button class="table-action-btn danger" onclick="confirmPurgeStore('audit_logs', 'Audit Logs');">
                  Purge Security Logs
                </button>
                <button class="table-action-btn danger" onclick="confirmPurgeStore('ALL_RECORDS', 'All Records (Factory Reset)');" style="font-weight: 700;">
                  Factory Reset (Preserve Admin)
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 4: SECURITY AUDIT TRAIL LOGS -->
        <div id="tab-audit" class="tab-pane">
          <div class="settings-card">
            <div class="settings-card-header">
              <div>
                <h3 class="typography-heading-4">Cryptographic Security Audit Trail.</h3>
                <p class="typography-caption">
                  Immutable record of user logins, clearance issuances, resident registrations, and system administrative modifications.
                </p>
              </div>
              <div style="display: flex; gap: var(--spacing-xs);">
                <button class="button-outline" id="btn-export-audit-csv" style="height: 34px; padding: 0 14px; font-size: 0.75rem;">
                  Export CSV
                </button>
                <button class="button-pill-soft" id="btn-refresh-audit" style="height: 34px; padding: 0 12px; font-size: 0.75rem;">
                  Refresh
                </button>
              </div>
            </div>

            <div class="audit-filter-bar">
              <div style="position: relative; min-width: 250px;">
                <input type="text" id="audit-search-input" class="text-input" placeholder="Filter by user, action, details..." style="height: 36px; font-size: 0.8125rem;">
              </div>

              <select id="audit-action-filter" class="filter-select" style="height: 36px;">
                <option value="ALL">All Event Actions</option>
                <option value="USER_LOGIN">USER_LOGIN</option>
                <option value="USER_REGISTERED">USER_REGISTERED</option>
                <option value="RESIDENT_">RESIDENT Actions</option>
                <option value="CERTIFICATE_">CERTIFICATE Actions</option>
                <option value="BLOTTER_">BLOTTER Actions</option>
                <option value="OFFICIAL_">OFFICIAL Actions</option>
                <option value="SETTINGS_">SETTINGS Updates</option>
                <option value="DATABASE_">DATABASE Actions</option>
              </select>
            </div>

            <!-- Audit Log Table -->
            <div class="data-table-container">
              <div class="data-table-wrap" style="max-height: 480px; overflow-y: auto;">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Timestamp</th>
                      <th>Action Code</th>
                      <th>Event Details</th>
                      <th>Operator</th>
                    </tr>
                  </thead>
                  <tbody id="audit-logs-tbody">
                    <tr>
                      <td colspan="4" style="text-align: center; color: var(--color-text-muted); padding: 24px;">Loading security logs...</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Modal: Restore Confirmation & Progress Modal -->
  <dialog id="restore-preview-modal" class="modal-dialog" style="width: 90%; max-width: 520px;">
    <h3 class="typography-heading-4" style="margin-bottom: var(--spacing-xs);">Restore Database from Backup.</h3>
    <p class="typography-body-sm" id="restore-summary-text" style="color: var(--color-text-muted); margin-bottom: var(--spacing-md);">
      Validating backup file contents...
    </p>

    <div id="restore-breakdown-box" style="background: var(--color-canvas-soft); padding: 12px 16px; border-radius: var(--rounded-sm); font-size: 0.8125rem; margin-bottom: var(--spacing-md); line-height: 1.6;">
      <!-- Populated dynamically -->
    </div>

    <div class="form-group" style="margin-bottom: var(--spacing-md);">
      <label class="form-label">Restore Mode:</label>
      <div style="display: flex; flex-direction: column; gap: 8px;">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.8125rem;">
          <input type="radio" name="restore-mode" value="merge" checked style="accent-color: var(--color-primary);">
          <span><strong>Merge &amp; Update</strong> (Preserves existing data, adds new records)</span>
        </label>
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.8125rem;">
          <input type="radio" name="restore-mode" value="wipe" style="accent-color: var(--color-primary);">
          <span><strong>Wipe &amp; Replace</strong> (Completely clears tables before restoring)</span>
        </label>
      </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs);">
      <button class="button-pill-soft" onclick="document.getElementById('restore-preview-modal').close();" style="height: 36px; padding: 0 14px;">Cancel</button>
      <button class="button-primary" id="btn-execute-restore" style="height: 36px; padding: 0 18px;">Execute Restore</button>
    </div>
  </dialog>

  <!-- Modal: Danger Zone Confirmation Modal -->
  <dialog id="purge-confirm-modal" class="modal-dialog" style="width: 90%; max-width: 440px;">
    <h4 class="typography-heading-4" style="color: #ef4444; margin-bottom: var(--spacing-xs);">Confirm Purge Operation</h4>
    <p class="typography-body-sm" id="purge-confirm-desc" style="color: var(--color-text-muted); margin-bottom: var(--spacing-sm);">
      This action cannot be undone. All data in this table will be permanently wiped.
    </p>

    <div class="form-group" style="margin-bottom: var(--spacing-md);">
      <label class="form-label" for="purge-confirm-input" style="font-size: 0.75rem;">Type <strong>CONFIRM</strong> to authorize:</label>
      <input type="text" id="purge-confirm-input" class="text-input" placeholder="CONFIRM" autocomplete="off" style="height: 38px;">
    </div>

    <div style="display: flex; justify-content: flex-end; gap: var(--spacing-xs);">
      <button class="button-pill-soft" onclick="document.getElementById('purge-confirm-modal').close();" style="height: 34px; padding: 0 14px;">Cancel</button>
      <button class="button-primary" id="btn-execute-purge" style="height: 34px; padding: 0 16px; background-color: #ef4444; border-color: #ef4444;" disabled>Execute Purge</button>
    </div>
  </dialog>

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

      // 2. Render App Shell Sidebar & Topbar
      await AppSidebar.render('settings');

      // 3. Tab Switching Logic
      const tabButtons = document.querySelectorAll('.settings-tab-btn');
      const tabPanes = document.querySelectorAll('.tab-pane');

      tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          const target = btn.getAttribute('data-tab');
          tabButtons.forEach(b => b.classList.remove('active'));
          tabPanes.forEach(p => p.classList.remove('active'));

          btn.classList.add('active');
          const targetPane = document.getElementById(target);
          if (targetPane) targetPane.classList.add('active');

          if (target === 'tab-audit') {
            loadAuditLogs();
          }
        });
      });

      // 4. Logo Base64 Storage State
      let currentLogoBase64 = null;
      let targetStoreToPurge = null;
      let parsedBackupData = null;

      // 5. Load Stored Settings & Telemetry
      async function loadAllSettings() {
        try {
          // A. Load Identity Settings
          const identitySetting = await window.barangayDB.get('settings', 'identity');
          if (identitySetting && identitySetting.value) {
            const v = identitySetting.value;
            document.getElementById('setting-brgy-name').value = v.barangayName || 'Barangay San Isidro';
            document.getElementById('setting-city-name').value = v.municipalityCity || 'City of San Isidro';
            document.getElementById('setting-province-name').value = v.province || 'Metropolitan Manila';
            document.getElementById('setting-hall-address').value = v.address || '';
            document.getElementById('setting-contact-phone').value = v.contactNumber || '';
            document.getElementById('setting-contact-email').value = v.email || '';
            document.getElementById('setting-office-hours').value = v.officeHours || '';
            if (v.logoBase64) {
              setLogoPreview(v.logoBase64);
            }
          } else {
            // Defaults
            document.getElementById('setting-brgy-name').value = 'Barangay San Isidro';
            document.getElementById('setting-city-name').value = 'City of San Isidro';
            document.getElementById('setting-province-name').value = 'Metropolitan Manila';
            document.getElementById('setting-hall-address').value = 'Barangay Hall Complex, J.P. Rizal St.';
            document.getElementById('setting-contact-phone').value = '(02) 8123-4567 / 0917-888-9999';
            document.getElementById('setting-contact-email').value = 'office@barangaysanisidro.local';
            document.getElementById('setting-office-hours').value = 'Monday – Friday, 8:00 AM – 5:00 PM';
          }
          updateLivePreview();

          // B. Load Fee Schedule
          const feeSetting = await window.barangayDB.get('settings', 'fees');
          if (feeSetting && feeSetting.value) {
            const f = feeSetting.value;
            document.getElementById('fee-brgy-clearance').value = f.barangayClearance ?? 50;
            document.getElementById('fee-indigency').value = f.certificateIndigency ?? 0;
            document.getElementById('fee-residency').value = f.certificateResidency ?? 50;
            document.getElementById('fee-business').value = f.businessPermit ?? 500;
            document.getElementById('setting-or-prefix').value = f.orPrefix || 'OR-2026-';
            document.getElementById('setting-validity-days').value = f.validityDays || 180;
            document.getElementById('setting-auto-waive-indigent').checked = f.autoWaiveIndigent !== false;
          }

          // C. Load Telemetry Metrics
          await refreshTelemetry();
        } catch (err) {
          console.error('Failed to load settings:', err);
          showToast('Could not load settings configuration', 'error');
        }
      }

      // 6. Refresh Telemetry Metrics
      async function refreshTelemetry() {
        const uCount = await window.barangayDB.count('users');
        const rCount = await window.barangayDB.count('residents');
        const cCount = await window.barangayDB.count('certificates');
        const bCount = await window.barangayDB.count('blotter_cases');
        const oCount = await window.barangayDB.count('officials');
        const aCount = await window.barangayDB.count('audit_logs');
        const sCount = await window.barangayDB.count('settings');

        const totalRecords = uCount + rCount + cCount + bCount + oCount + aCount + sCount;
        document.getElementById('stat-total-records').textContent = totalRecords;
        document.getElementById('stat-audit-count').textContent = aCount;

        const brgyName = document.getElementById('setting-brgy-name').value || 'Barangay San Isidro';
        const cityName = document.getElementById('setting-city-name').value || 'City of San Isidro';
        document.getElementById('stat-barangay-display').textContent = brgyName;
        document.getElementById('stat-city-display').textContent = cityName;

        // Check last backup in localStorage
        const lastBackup = localStorage.getItem('bms_last_backup_time');
        if (lastBackup) {
          const dt = new Date(lastBackup);
          document.getElementById('stat-backup-date').textContent = dt.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });
          document.getElementById('stat-backup-badge').textContent = 'Saved';
          document.getElementById('stat-backup-badge').className = 'badge-emerald';
        }
      }

      // 7. Live Preview Updaters
      function updateLivePreview() {
        const brgy = document.getElementById('setting-brgy-name').value.trim() || 'Barangay San Isidro';
        const city = document.getElementById('setting-city-name').value.trim() || 'City of San Isidro';
        const prov = document.getElementById('setting-province-name').value.trim() || 'Metropolitan Manila';
        const addr = document.getElementById('setting-hall-address').value.trim() || 'Barangay Hall Complex';
        const phone = document.getElementById('setting-contact-phone').value.trim() || '(02) 8123-4567';

        document.getElementById('preview-jurisdiction').textContent = `${prov} • ${city}`;
        document.getElementById('preview-brgy-title').textContent = brgy.toUpperCase();
        document.getElementById('preview-address-phone').textContent = `${addr} • Tel: ${phone}`;
        document.getElementById('stat-barangay-display').textContent = brgy;
        document.getElementById('stat-city-display').textContent = city;
      }

      ['setting-brgy-name', 'setting-city-name', 'setting-province-name', 'setting-hall-address', 'setting-contact-phone'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateLivePreview);
      });

      // 8. Logo Upload & Base64 Handler
      const logoDropzone = document.getElementById('logo-uploader-dropzone');
      const logoFileInput = document.getElementById('logo-file-input');
      const btnRemoveLogo = document.getElementById('btn-remove-logo');

      logoDropzone.addEventListener('click', () => logoFileInput.click());
      logoDropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        logoDropzone.style.borderColor = 'var(--color-primary)';
      });
      logoDropzone.addEventListener('dragleave', () => {
        logoDropzone.style.borderColor = 'var(--color-hairline)';
      });
      logoDropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        logoDropzone.style.borderColor = 'var(--color-hairline)';
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
          processLogoFile(e.dataTransfer.files[0]);
        }
      });

      logoFileInput.addEventListener('change', () => {
        if (logoFileInput.files && logoFileInput.files[0]) {
          processLogoFile(logoFileInput.files[0]);
        }
      });

      function processLogoFile(file) {
        if (!file.type.startsWith('image/')) {
          showToast('Please select a valid image file', 'error');
          return;
        }
        if (file.size > 2 * 1024 * 1024) {
          showToast('Image file too large (max 2MB)', 'warning');
          return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
          setLogoPreview(e.target.result);
          showToast('Official seal loaded (click Save to persist)');
        };
        reader.readAsDataURL(file);
      }

      function setLogoPreview(dataUrl) {
        currentLogoBase64 = dataUrl;
        const box = document.getElementById('logo-preview-box');
        box.innerHTML = `<img src="${dataUrl}" class="preview-logo-thumb" alt="Seal">`;
        btnRemoveLogo.style.display = 'inline-flex';
      }

      btnRemoveLogo.addEventListener('click', () => {
        currentLogoBase64 = null;
        const box = document.getElementById('logo-preview-box');
        box.innerHTML = `
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
        `;
        btnRemoveLogo.style.display = 'none';
        showToast('Logo cleared (click Save to apply)');
      });

      // 9. Save Barangay Identity Form
      document.getElementById('btn-save-identity').addEventListener('click', async () => {
        const brgy = document.getElementById('setting-brgy-name').value.trim();
        const city = document.getElementById('setting-city-name').value.trim();
        const prov = document.getElementById('setting-province-name').value.trim();
        const addr = document.getElementById('setting-hall-address').value.trim();
        const phone = document.getElementById('setting-contact-phone').value.trim();
        const email = document.getElementById('setting-contact-email').value.trim();
        const hours = document.getElementById('setting-office-hours').value.trim();

        if (!brgy || !city || !prov) {
          showToast('Please provide Barangay, City, and Province names', 'error');
          return;
        }

        try {
          const identityData = {
            key: 'identity',
            value: {
              barangayName: brgy,
              municipalityCity: city,
              province: prov,
              address: addr,
              contactNumber: phone,
              email: email,
              officeHours: hours,
              logoBase64: currentLogoBase64,
              updatedAt: new Date().toISOString()
            }
          };

          await window.barangayDB.put('settings', identityData);

          await window.authService.logAuditTrail(
            currentAuthUser.id,
            'SETTINGS_IDENTITY_UPDATED',
            `Updated Barangay identity to ${brgy}, ${city}`
          );

          showToast('Barangay Identity saved successfully');
          updateLivePreview();
          await refreshTelemetry();
        } catch (err) {
          console.error('Failed to save identity:', err);
          showToast('Error saving identity settings', 'error');
        }
      });

      // 10. Save Fee Schedule Form
      document.getElementById('btn-save-fees').addEventListener('click', async () => {
        const brgyClearance = parseFloat(document.getElementById('fee-brgy-clearance').value) || 0;
        const indigency = parseFloat(document.getElementById('fee-indigency').value) || 0;
        const residency = parseFloat(document.getElementById('fee-residency').value) || 0;
        const business = parseFloat(document.getElementById('fee-business').value) || 0;
        const orPrefix = document.getElementById('setting-or-prefix').value.trim() || 'OR-2026-';
        const validityDays = parseInt(document.getElementById('setting-validity-days').value, 10) || 180;
        const autoWaiveIndigent = document.getElementById('setting-auto-waive-indigent').checked;

        try {
          const feeData = {
            key: 'fees',
            value: {
              barangayClearance: brgyClearance,
              certificateIndigency: indigency,
              certificateResidency: residency,
              businessPermit: business,
              orPrefix: orPrefix,
              validityDays: validityDays,
              autoWaiveIndigent: autoWaiveIndigent,
              updatedAt: new Date().toISOString()
            }
          };

          await window.barangayDB.put('settings', feeData);

          await window.authService.logAuditTrail(
            currentAuthUser.id,
            'SETTINGS_FEES_UPDATED',
            `Updated clearance fee schedule (Clearance: ₱${brgyClearance}, Business: ₱${business})`
          );

          showToast('Clearance Fee schedule saved');
          await refreshTelemetry();
        } catch (err) {
          console.error('Failed to save fees:', err);
          showToast('Error saving fee schedule', 'error');
        }
      });

      // 11. Database Export to JSON File
      async function exportDatabaseJSON() {
        try {
          showToast('Preparing complete database snapshot...');

          const users = await window.barangayDB.getAll('users');
          const residents = await window.barangayDB.getAll('residents');
          const certificates = await window.barangayDB.getAll('certificates');
          const blotters = await window.barangayDB.getAll('blotter_cases');
          const officials = await window.barangayDB.getAll('officials');
          const auditLogs = await window.barangayDB.getAll('audit_logs');
          const settings = await window.barangayDB.getAll('settings');
          const households = await window.barangayDB.getAll('households');
          const incidents = await window.barangayDB.getAll('incidents');

          const now = new Date();
          const timestamp = now.toISOString();
          const dateStr = now.toISOString().slice(0, 10);

          const backupPayload = {
            application: 'BarangayOS - Barangay Management System',
            version: 3,
            exportedAt: timestamp,
            exportedBy: currentAuthUser.fullName || currentAuthUser.username,
            recordCounts: {
              users: users.length,
              residents: residents.length,
              households: households.length,
              incidents: incidents.length,
              certificates: certificates.length,
              blotter_cases: blotters.length,
              officials: officials.length,
              audit_logs: auditLogs.length,
              settings: settings.length
            },
            database: {
              users: users,
              residents: residents,
              households: households,
              incidents: incidents,
              certificates: certificates,
              blotter_cases: blotters,
              officials: officials,
              audit_logs: auditLogs,
              settings: settings
            }
          };

          const jsonString = JSON.stringify(backupPayload, null, 2);
          const blob = new Blob([jsonString], { type: 'application/json' });
          const url = URL.createObjectURL(blob);

          const a = document.createElement('a');
          a.href = url;
          a.download = `BarangayOS_Database_Backup_${dateStr}.json`;
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          URL.revokeObjectURL(url);

          localStorage.setItem('bms_last_backup_time', timestamp);
          await refreshTelemetry();

          await window.authService.logAuditTrail(
            currentAuthUser.id,
            'DATABASE_BACKUP_EXPORTED',
            `Exported complete JSON backup (${residents.length} residents, ${certificates.length} certs, ${blotters.length} blotters)`
          );

          showToast('Database backup downloaded successfully');
        } catch (err) {
          console.error('Backup export failed:', err);
          showToast('Failed to generate backup JSON', 'error');
        }
      }

      document.getElementById('btn-export-backup').addEventListener('click', exportDatabaseJSON);
      document.getElementById('btn-quick-export-backup').addEventListener('click', exportDatabaseJSON);

      // 12. Database Restore from JSON File
      const restoreFileInput = document.getElementById('restore-file-input');
      const btnTriggerRestore = document.getElementById('btn-trigger-restore');
      const restoreModal = document.getElementById('restore-preview-modal');
      const restoreSummaryText = document.getElementById('restore-summary-text');
      const restoreBreakdownBox = document.getElementById('restore-breakdown-box');
      const btnExecuteRestore = document.getElementById('btn-execute-restore');

      btnTriggerRestore.addEventListener('click', () => {
        restoreFileInput.value = '';
        restoreFileInput.click();
      });

      restoreFileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (event) => {
          try {
            const data = JSON.parse(event.target.result);
            if (!data.database || !data.application) {
              showToast('Invalid backup file structure', 'error');
              return;
            }

            parsedBackupData = data;
            const counts = data.recordCounts || {
              residents: (data.database.residents || []).length,
              households: (data.database.households || []).length,
              incidents: (data.database.incidents || []).length,
              certificates: (data.database.certificates || []).length,
              blotter_cases: (data.database.blotter_cases || []).length,
              officials: (data.database.officials || []).length
            };

            restoreSummaryText.textContent = `Backup created on ${new Date(data.exportedAt || Date.now()).toLocaleString()} by ${data.exportedBy || 'Administrator'}.`;
            restoreBreakdownBox.innerHTML = `
              <div>&bull; <strong>Residents:</strong> ${counts.residents || 0} records</div>
              <div>&bull; <strong>Households:</strong> ${counts.households || 0} records</div>
              <div>&bull; <strong>Incidents:</strong> ${counts.incidents || 0} records</div>
              <div>&bull; <strong>Certificates:</strong> ${counts.certificates || 0} records</div>
              <div>&bull; <strong>Blotter Cases:</strong> ${counts.blotter_cases || 0} records</div>
              <div>&bull; <strong>Officials:</strong> ${counts.officials || 0} records</div>
              <div>&bull; <strong>Security Logs:</strong> ${(data.database.audit_logs || []).length} events</div>
            `;

            restoreModal.showModal();
          } catch (err) {
            console.error('Invalid JSON file:', err);
            showToast('Failed to parse JSON backup file', 'error');
          }
        };
        reader.readAsText(file);
      });

      btnExecuteRestore.addEventListener('click', async () => {
        if (!parsedBackupData || !parsedBackupData.database) return;

        const mode = document.querySelector('input[name="restore-mode"]:checked').value;
        const db = parsedBackupData.database;

        try {
          showToast('Executing restoration...');

          const storesToRestore = ['residents', 'households', 'incidents', 'certificates', 'blotter_cases', 'officials', 'settings'];

          if (mode === 'wipe') {
            for (const s of storesToRestore) {
              await window.barangayDB.clear(s);
            }
          }

          // Insert or Merge records
          for (const s of storesToRestore) {
            const list = db[s] || [];
            for (const item of list) {
              await window.barangayDB.put(s, item);
            }
          }

          // Also restore audit logs if wipe was selected
          if (mode === 'wipe' && db.audit_logs) {
            for (const log of db.audit_logs) {
              await window.barangayDB.put('audit_logs', log);
            }
          }

          await window.authService.logAuditTrail(
            currentAuthUser.id,
            'DATABASE_RESTORED',
            `Restored database from file in ${mode.toUpperCase()} mode.`
          );

          restoreModal.close();
          parsedBackupData = null;
          showToast('Database successfully restored!');
          await loadAllSettings();
        } catch (err) {
          console.error('Restoration execution failed:', err);
          showToast('Restoration encountered an error', 'error');
        }
      });

      // 13. Danger Zone Purge Logic
      const purgeModal = document.getElementById('purge-confirm-modal');
      const purgeInput = document.getElementById('purge-confirm-input');
      const purgeDesc = document.getElementById('purge-confirm-desc');
      const btnExecutePurge = document.getElementById('btn-execute-purge');

      window.confirmPurgeStore = (storeKey, displayName) => {
        targetStoreToPurge = storeKey;
        purgeInput.value = '';
        btnExecutePurge.disabled = true;
        purgeDesc.innerHTML = `You are about to permanently purge <strong>${displayName}</strong>. All data in this table will be destroyed.`;
        purgeModal.showModal();
      };

      purgeInput.addEventListener('input', () => {
        btnExecutePurge.disabled = purgeInput.value.trim().toUpperCase() !== 'CONFIRM';
      });

      btnExecutePurge.addEventListener('click', async () => {
        if (!targetStoreToPurge) return;

        try {
          if (targetStoreToPurge === 'ALL_RECORDS') {
            await window.barangayDB.clear('residents');
            await window.barangayDB.clear('households');
            await window.barangayDB.clear('incidents');
            await window.barangayDB.clear('certificates');
            await window.barangayDB.clear('blotter_cases');
            await window.barangayDB.clear('officials');
            await window.barangayDB.clear('audit_logs');

            await window.authService.logAuditTrail(
              currentAuthUser.id,
              'DATABASE_FACTORY_RESET',
              'Factory reset executed: cleared residents, certificates, blotters, and officials.'
            );
            showToast('Factory reset complete (admin preserved)');
          } else {
            await window.barangayDB.clear(targetStoreToPurge);
            await window.authService.logAuditTrail(
              currentAuthUser.id,
              'STORE_PURGED',
              `Purged all records in table ${targetStoreToPurge}.`
            );
            showToast(`Purged table: ${targetStoreToPurge}`);
          }

          purgeModal.close();
          targetStoreToPurge = null;
          await refreshTelemetry();
          if (document.getElementById('tab-audit').classList.contains('active')) {
            loadAuditLogs();
          }
        } catch (err) {
          console.error('Purge failed:', err);
          showToast('Failed to execute purge operation', 'error');
        }
      });

      // 14. Audit Trail View, Filters & CSV Export
      let allAuditLogs = [];
      const auditTbody = document.getElementById('audit-logs-tbody');
      const auditSearchInput = document.getElementById('audit-search-input');
      const auditActionFilter = document.getElementById('audit-action-filter');

      async function loadAuditLogs() {
        try {
          const logs = await window.barangayDB.getAll('audit_logs');
          logs.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
          allAuditLogs = logs;
          renderAuditLogs();
        } catch (err) {
          console.error('Failed to load audit logs:', err);
          auditTbody.innerHTML = `<tr><td colspan="4" style="color: #ef4444; padding: 18px;">Error loading logs: ${err.message}</td></tr>`;
        }
      }

      function renderAuditLogs() {
        const q = auditSearchInput.value.toLowerCase().trim();
        const actionFilter = auditActionFilter.value;

        const filtered = allAuditLogs.filter(log => {
          if (actionFilter !== 'ALL') {
            if (!log.action || !log.action.startsWith(actionFilter)) return false;
          }
          if (q) {
            const inAction = (log.action || '').toLowerCase().includes(q);
            const inDetails = (log.details || '').toLowerCase().includes(q);
            const inUser = String(log.userId || '').toLowerCase().includes(q);
            if (!inAction && !inDetails && !inUser) return false;
          }
          return true;
        });

        if (filtered.length === 0) {
          auditTbody.innerHTML = `
            <tr>
              <td colspan="4" style="text-align: center; color: var(--color-text-muted); padding: 24px;">
                No audit log entries found matching criteria.
              </td>
            </tr>
          `;
          return;
        }

        auditTbody.innerHTML = filtered.map(log => {
          const dateStr = new Date(log.timestamp).toLocaleString([], {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          });

          let badgeClass = 'badge-neutral';
          if (log.action.includes('LOGIN')) badgeClass = 'badge-blue';
          else if (log.action.includes('REGISTER') || log.action.includes('CREATE')) badgeClass = 'badge-emerald';
          else if (log.action.includes('UPDATE') || log.action.includes('SET')) badgeClass = 'badge-amber';
          else if (log.action.includes('DELETE') || log.action.includes('PURGE')) badgeClass = 'badge-rose';

          return `
            <tr>
              <td style="white-space: nowrap; font-size: 0.75rem; color: var(--color-text-faint);">${dateStr}</td>
              <td>
                <span class="${badgeClass} audit-log-badge">${log.action}</span>
              </td>
              <td style="font-size: 0.8125rem;">${log.details || '&mdash;'}</td>
              <td style="font-size: 0.75rem; color: var(--color-text-muted); white-space: nowrap;">User #${log.userId || 1}</td>
            </tr>
          `;
        }).join('');
      }

      auditSearchInput.addEventListener('input', renderAuditLogs);
      auditActionFilter.addEventListener('change', renderAuditLogs);
      document.getElementById('btn-refresh-audit').addEventListener('click', loadAuditLogs);

      // Export Audit Logs to CSV
      document.getElementById('btn-export-audit-csv').addEventListener('click', () => {
        if (allAuditLogs.length === 0) {
          showToast('No logs to export', 'warning');
          return;
        }

        const headers = ['ID', 'Timestamp', 'Action', 'Details', 'UserID'];
        const csvRows = [headers.join(',')];

        for (const log of allAuditLogs) {
          const row = [
            log.id || '',
            `"${log.timestamp || ''}"`,
            `"${log.action || ''}"`,
            `"${(log.details || '').replace(/"/g, '""')}"`,
            log.userId || ''
          ];
          csvRows.push(row.join(','));
        }

        const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `BarangayOS_Security_Audit_Logs_${new Date().toISOString().slice(0, 10)}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        showToast('Audit logs CSV downloaded');
      });

      // 15. Initial Load
      await loadAllSettings();
    });
  </script>
</body>
</html>
