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
  <title>Purok Geo-Profiling &amp; Heatmap &bull; Barangay Management System</title>
  <link rel="stylesheet" href="css/design-system.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
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

    /* GIS Stage Layout */
    .gis-stage-layout {
      display: grid;
      grid-template-columns: 1fr 380px;
      gap: var(--spacing-md);
      align-items: start;
      margin-bottom: var(--spacing-section);
    }

    @media (max-width: 1100px) {
      .gis-stage-layout {
        grid-template-columns: 1fr;
      }
    }

    /* GIS Map Card Container */
    .gis-map-card {
      background-color: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: var(--spacing-md);
      display: flex;
      flex-direction: column;
      gap: var(--spacing-sm);
      position: relative;
    }

    .gis-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: var(--spacing-xs);
      border-bottom: 1px solid var(--color-hairline-soft);
      padding-bottom: var(--spacing-xs);
    }

    /* Layer Segmented Control */
    .gis-layer-tabs {
      display: inline-flex;
      background-color: var(--color-canvas-soft);
      border-radius: var(--rounded-full);
      padding: 3px;
      gap: 3px;
      overflow-x: auto;
      max-width: 100%;
    }

    .gis-layer-btn {
      padding: 6px 14px;
      font-size: 0.75rem;
      font-weight: 600;
      border: none;
      background: transparent;
      color: var(--color-text-muted);
      border-radius: var(--rounded-full);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      white-space: nowrap;
      transition: all 0.15s ease;
    }

    .gis-layer-btn.active {
      background-color: var(--color-canvas);
      color: var(--color-ink);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    /* Leaflet Map Container */
    #leaflet-map {
      width: 100%;
      height: 520px;
      border-radius: var(--rounded-sm);
      border: 1px solid var(--color-hairline-soft);
      z-index: 0;
    }

    /* Custom Leaflet Label Styling */
    .purok-label-icon {
      background: none !important;
      border: none !important;
      box-shadow: none !important;
      font-size: 13px;
      font-weight: 800;
      color: #141414;
      text-align: center;
      white-space: nowrap;
      text-shadow: -1px -1px 0 rgba(255,255,255,0.85), 1px -1px 0 rgba(255,255,255,0.85), -1px 1px 0 rgba(255,255,255,0.85), 1px 1px 0 rgba(255,255,255,0.85), 0 0 6px rgba(255,255,255,0.7);
      pointer-events: none !important;
    }

    [data-theme="dark"] .purok-label-icon {
      color: #f4f4f5;
      text-shadow: -1px -1px 0 rgba(20,20,20,0.85), 1px -1px 0 rgba(20,20,20,0.85), -1px 1px 0 rgba(20,20,20,0.85), 1px 1px 0 rgba(20,20,20,0.85), 0 0 6px rgba(20,20,20,0.7);
    }

    /* Custom Landmark Marker */
    .landmark-marker-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      font-size: 14px;
      border: 2px solid #ffffff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.25);
      cursor: pointer;
      transition: transform 0.2s ease;
    }

    .landmark-marker-icon:hover {
      transform: scale(1.15);
    }

    /* Map Legend Box */
    .gis-legend-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: var(--spacing-xs);
      font-size: 0.75rem;
      color: var(--color-text-muted);
      border-top: 1px solid var(--color-hairline-soft);
      padding-top: var(--spacing-xs);
    }

    .legend-ramp-track {
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .legend-swatch {
      width: 22px;
      height: 10px;
      border-radius: 2px;
    }

    /* Right Deep-Dive Inspection Panel */
    .gis-detail-panel {
      background-color: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: var(--spacing-md);
      display: flex;
      flex-direction: column;
      gap: var(--spacing-md);
    }

    .panel-header-badge {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid var(--color-hairline-soft);
      padding-bottom: var(--spacing-sm);
    }

    .metric-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--spacing-xs);
    }

    .purok-metric-card {
      background-color: var(--color-canvas-soft);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-sm);
      padding: 10px;
    }

    .purok-metric-card .val {
      font-size: 1.25rem;
      font-weight: 800;
      color: var(--color-ink);
      line-height: 1.1;
      margin-top: 2px;
    }

    .purok-metric-card .lbl {
      font-size: 0.6875rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: var(--color-text-muted);
    }

    /* Quick Action Drilldown Buttons */
    .panel-action-btns {
      display: flex;
      flex-direction: column;
      gap: 6px;
      border-top: 1px solid var(--color-hairline-soft);
      padding-top: var(--spacing-sm);
    }

    /* Tile toggle button */
    .tile-toggle-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 1000;
      background: var(--color-canvas);
      border: 1px solid var(--color-hairline);
      border-radius: var(--rounded-sm);
      padding: 6px 10px;
      font-size: 0.7rem;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      gap: 4px;
    }

    /* Printable Official DILG / CDRRMO Geo-Report */
    @media print {
      body {
        background: #ffffff !important;
        color: #000000 !important;
      }
      .app-shell, .app-sidebar, .app-topbar, .mobile-nav-bar, .page-hero,
      .stats-ladder, .gis-controls-overlay, .gis-toolbar, .panel-action-btns,
      .toast-container, dialog, .no-print {
        display: none !important;
      }
      #printable-geo-report {
        display: block !important;
        margin: 0;
        padding: 20px;
        width: 100%;
      }
    }

    #printable-geo-report {
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
        <!-- Ultra-Minimal Level 1 Hero -->
        <section class="page-hero">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--spacing-md);">
            <div>
              <div style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: 2px;">
                <h1 class="typography-heading-2">Purok Demographic Density &amp; Geo-Profiling.</h1>
                <button type="button" class="info-trigger" onclick="toggleGeoInfoPopover(event)" aria-label="Geo-Profiling Guidelines">i</button>
                <span class="badge-neutral" id="gis-zone-count-badge">7 Official Puroks</span>
              </div>
              <p class="typography-body-lg" style="margin-bottom: 0;">
                Interactive GIS spatial command center for population density, disaster hazard risks, and household vulnerability profiling.
              </p>
            </div>
            <div style="display: flex; align-items: center; gap: var(--spacing-sm); flex-wrap: wrap;">
              <!-- Purok Jump Dropdown -->
              <select id="select-purok-jump" class="filter-select" style="height: 38px;">
                <option value="">-- Inspect Purok on Map --</option>
                <option value="Purok 1">Purok 1 - Riverside North</option>
                <option value="Purok 2">Purok 2 - Poblacion Central</option>
                <option value="Purok 3">Purok 3 - Barangay Centro</option>
                <option value="Purok 4">Purok 4 - Residential Heights</option>
                <option value="Purok 5">Purok 5 - Western Hillside</option>
                <option value="Purok 6">Purok 6 - Greenfields Agro</option>
                <option value="Purok 7">Purok 7 - Industrial Highway</option>
              </select>

              <button class="button-outline" id="btn-export-gis-csv" title="Export Geo-Profiling Census CSV" style="height: 38px; padding: 0 16px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="7 10 12 15 17 10"/>
                  <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span>Export CSV</span>
              </button>

              <button class="button-primary" id="btn-print-geo-report" title="Print Official DILG / CDRRMO Geo-Hazard Report" style="height: 38px; padding: 0 18px; font-size: 0.8125rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="6 9 6 2 18 2 18 9"/>
                  <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                  <rect width="12" height="8" x="6" y="14"/>
                </svg>
                <span>Print Geo-Report</span>
              </button>
            </div>
          </div>
        </section>

        <!-- Contextual Info Popover Card -->
        <div class="info-popover-card" id="geo-info-popover" style="display: none;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <strong style="font-size: 0.875rem; color: var(--color-ink);">GIS &amp; Demographic Mapping Guide</strong>
            <button type="button" class="drawer-close-btn" onclick="toggleGeoInfoPopover(event)" aria-label="Close popover" style="font-size: 1rem; width: 24px; height: 24px; border: none; background: transparent; cursor: pointer;">&times;</button>
          </div>
          <p style="font-size: 0.8125rem; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 8px;">
            Integrates DILG CBMS census telemetry with spatial Leaflet layers to visualize population concentration, hazard corridors, and welfare vulnerability indices across all Puroks.
          </p>
          <div style="display: flex; gap: 8px; font-size: 0.75rem; color: var(--color-text-muted);">
            <span>Spatial Projection: WGS 84</span> &bull; <span>Compliance: RA 10121 / DILG CBMS</span>
          </div>
        </div>

        <!-- Level 1 Minimal Borderless Metrics Strip -->
        <section style="margin-bottom: var(--spacing-lg);">
          <div class="metrics-strip">
            <div class="metric-item">
              <span class="metric-label">Most Populated Zone</span>
              <div class="metric-val" id="kpi-most-populated">Purok 1</div>
              <span class="metric-sub" id="kpi-most-populated-sub">0 residents (0% of total)</span>
            </div>
            <div class="metric-item">
              <span class="metric-label">Highest Vulnerability</span>
              <div class="metric-val" id="kpi-highest-vuln">Purok 1</div>
              <span class="metric-sub" id="kpi-highest-vuln-sub">0 priority assisted sectors</span>
            </div>
            <div class="metric-item">
              <span class="metric-label">Disaster Hazard Risk</span>
              <div class="metric-val" id="kpi-hazard-count" style="color: #ef4444;">0</div>
              <span class="metric-sub">Dwellings in critical hazard zone</span>
            </div>
            <div class="metric-item">
              <span class="metric-label">GIS Census Coverage</span>
              <div class="metric-val" id="kpi-coverage">100%</div>
              <span class="metric-sub" id="kpi-coverage-sub">All 7 Puroks active &amp; mapped</span>
            </div>
          </div>
        </section>

        <!-- Interactive GIS Stage & Inspection Drawer Grid -->
        <div class="gis-stage-layout">
          <!-- Left: Interactive Leaflet Map Card -->
          <div class="gis-map-card">
            <div class="gis-toolbar">
              <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 0.8125rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-muted);">
                  Thematic Layers:
                </span>
                <div class="gis-layer-tabs" role="tablist">
                  <button type="button" class="gis-layer-btn active" data-layer="population">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                    <span>Population Density</span>
                  </button>
                  <button type="button" class="gis-layer-btn" data-layer="hazard">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>Disaster Hazard Risk</span>
                  </button>
                  <button type="button" class="gis-layer-btn" data-layer="vulnerability">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>Vulnerability Index</span>
                  </button>
                  <button type="button" class="gis-layer-btn" data-layer="households">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span>Household Density</span>
                  </button>
                </div>
              </div>

              <!-- Landmark Overlay Toggle -->
              <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                <input type="checkbox" id="toggle-landmarks" checked style="accent-color: var(--color-primary);">
                <span>Landmarks &amp; Posts</span>
              </label>
            </div>

            <!-- Leaflet Map Viewport -->
            <div style="position: relative;">
              <div id="leaflet-map"></div>
              <button type="button" class="tile-toggle-btn" id="btn-toggle-tiles" title="Switch between Street and Satellite view">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                <span>Satellite</span>
              </button>
            </div>

            <!-- Dynamic Legend Bar -->
            <div class="gis-legend-bar" id="gis-legend-container">
              <span id="gis-legend-title" style="font-weight: 600;">Population Density Range:</span>
              <div class="legend-ramp-track" id="gis-legend-ramp">
                <!-- Injected based on active layer -->
              </div>
            </div>
          </div>

          <!-- Right: Deep-Dive Zone Intelligence Card -->
          <div class="gis-detail-panel" id="gis-detail-panel">
            <div class="panel-header-badge">
              <div>
                <span class="typography-label" id="detail-subzone">CIVIC &amp; GOVERNMENT CORE</span>
                <h3 class="typography-heading-3" id="detail-purok-name" style="margin-top: 2px;">Purok 3</h3>
              </div>
              <span class="badge-emerald" id="detail-hazard-badge">Safe Zone</span>
            </div>

            <div>
              <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-bottom: 2px;">Zone Council Leader</div>
              <div style="font-weight: 700; font-size: 0.875rem; color: var(--color-ink);" id="detail-leader-name">
                Hon. Punong Barangay / Kgd. Manuel Cruz
              </div>
            </div>

            <!-- Key Demographics Grid -->
            <div class="metric-grid-2">
              <div class="purok-metric-card">
                <div class="lbl">Residents</div>
                <div class="val" id="detail-resident-count">0</div>
                <div class="typography-caption" id="detail-gender-split">0 M &bull; 0 F</div>
              </div>
              <div class="purok-metric-card">
                <div class="lbl">Households</div>
                <div class="val" id="detail-household-count">0</div>
                <div class="typography-caption" id="detail-family-size">0 avg family</div>
              </div>
              <div class="purok-metric-card">
                <div class="lbl">Registered Voters</div>
                <div class="val" id="detail-voter-count">0</div>
                <div class="typography-caption" id="detail-voter-pct">0% participation</div>
              </div>
              <div class="purok-metric-card">
                <div class="lbl">Vulnerability Index</div>
                <div class="val" id="detail-vuln-count" style="color: #d97706;">0</div>
                <div class="typography-caption">Priority Assisted</div>
              </div>
            </div>

            <!-- Social Welfare Sector Breakdown -->
            <div>
              <span class="typography-label" style="color: var(--color-text-muted); margin-bottom: 6px; display: block;">
                Social Welfare &amp; Vulnerable Sectors
              </span>
              <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                <span class="badge-amber" id="detail-count-senior">0 Seniors (60+)</span>
                <span class="badge-purple" id="detail-count-pwd">0 PWD</span>
                <span class="badge-rose" id="detail-count-solo">0 Solo Parents</span>
                <span class="badge-emerald" id="detail-count-4ps">0 4Ps</span>
                <span class="badge-neutral" id="detail-count-indigent">0 Indigents</span>
              </div>
            </div>

            <!-- Disaster Preparedness & Evacuation Matrix -->
            <div style="background-color: var(--color-canvas-soft); border-radius: var(--rounded-sm); padding: 10px; border: 1px solid var(--color-hairline-soft);">
              <span class="typography-label" style="color: var(--color-text-muted); display: block; margin-bottom: 4px;">
                Disaster &amp; Evacuation Protocol
              </span>
              <div style="font-size: 0.8125rem; font-weight: 600; color: var(--color-ink);" id="detail-evac-hub">
                Barangay Hall Complex
              </div>
              <p class="typography-caption mt-xs" id="detail-hazard-desc">
                Designated incident command center and emergency stockpile depot. Minimal flooding risk.
              </p>
            </div>

            <!-- Quick Action Links -->
            <div class="panel-action-btns">
              <a href="residents.php?purok=Purok+3" class="button-primary" id="btn-drilldown-residents" style="height: 38px; font-size: 0.8125rem; justify-content: center; text-decoration: none;">
                View Residents in Purok 3 &rarr;
              </a>
              <a href="households.php?purok=Purok+3" class="button-outline" id="btn-drilldown-households" style="height: 38px; font-size: 0.8125rem; justify-content: center; text-decoration: none;">
                View Household Roster &rarr;
              </a>
              <a href="health.php" class="button-outline" id="btn-drilldown-health" style="height: 38px; font-size: 0.8125rem; justify-content: center; text-decoration: none; color: #10b981; border-color: #a7f3d0;">
                Health Station &amp; Clinic &rarr;
              </a>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- LEVEL 3: SLIDE-OVER PUROK DOSSIER DRAWER -->
  <div class="app-drawer-backdrop" id="geo-drawer-backdrop" onclick="closeGeoDrawer()"></div>
  <aside class="app-drawer" id="geo-detail-drawer" aria-label="Purok Demographic Dossier">
    <div class="drawer-header">
      <div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <span class="badge-neutral" id="gdrawer-subzone">CIVIC &amp; GOVERNMENT CORE</span>
          <span class="badge-emerald" id="gdrawer-hazard-badge">Safe Zone</span>
        </div>
        <h3 class="typography-heading-4" id="gdrawer-purok-name" style="margin-top: 4px;">Purok 3</h3>
      </div>
      <button type="button" class="drawer-close-btn" onclick="closeGeoDrawer()" aria-label="Close drawer">&times;</button>
    </div>

    <!-- Drawer Navigation Tabs -->
    <div class="drawer-tabs">
      <button type="button" class="drawer-tab-btn active" id="gdtab-btn-demo" onclick="switchGeoDrawerTab('demo')">Demographics</button>
      <button type="button" class="drawer-tab-btn" id="gdtab-btn-welfare" onclick="switchGeoDrawerTab('welfare')">Welfare Sectors</button>
      <button type="button" class="drawer-tab-btn" id="gdtab-btn-hazard" onclick="switchGeoDrawerTab('hazard')">Evacuation &amp; Hazard</button>
    </div>

    <div class="drawer-body">
      <!-- Tab A: Demographics -->
      <div id="gdtab-pane-demo">
        <div class="drawer-section">
          <div class="drawer-section-title">Leadership &amp; Council Jurisdiction</div>
          <div style="font-weight: 700; font-size: 0.875rem; color: var(--color-ink);" id="gdrawer-leader-name">
            Hon. Punong Barangay / Kgd. Manuel Cruz
          </div>
          <div class="typography-caption" style="margin-top: 2px;">Assigned Purok Focal Official</div>
        </div>

        <div class="drawer-section">
          <div class="drawer-section-title">Key Census Figures</div>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
            <div style="background: var(--color-canvas-soft); padding: 10px; border-radius: var(--rounded-sm); border: 1px solid var(--color-hairline-soft);">
              <div style="font-size: 0.6875rem; color: var(--color-text-muted); text-transform: uppercase;">Residents</div>
              <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-ink);" id="gdrawer-resident-count">0</div>
              <div class="typography-caption" id="gdrawer-gender-split">0 M &bull; 0 F</div>
            </div>
            <div style="background: var(--color-canvas-soft); padding: 10px; border-radius: var(--rounded-sm); border: 1px solid var(--color-hairline-soft);">
              <div style="font-size: 0.6875rem; color: var(--color-text-muted); text-transform: uppercase;">Households</div>
              <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-ink);" id="gdrawer-household-count">0</div>
              <div class="typography-caption" id="gdrawer-family-size">0 avg family</div>
            </div>
            <div style="background: var(--color-canvas-soft); padding: 10px; border-radius: var(--rounded-sm); border: 1px solid var(--color-hairline-soft);">
              <div style="font-size: 0.6875rem; color: var(--color-text-muted); text-transform: uppercase;">Voters</div>
              <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-ink);" id="gdrawer-voter-count">0</div>
              <div class="typography-caption" id="gdrawer-voter-pct">0% participation</div>
            </div>
            <div style="background: var(--color-canvas-soft); padding: 10px; border-radius: var(--rounded-sm); border: 1px solid var(--color-hairline-soft);">
              <div style="font-size: 0.6875rem; color: var(--color-text-muted); text-transform: uppercase;">Vulnerability Score</div>
              <div style="font-size: 1.25rem; font-weight: 800; color: #d97706;" id="gdrawer-vuln-count">0</div>
              <div class="typography-caption">Priority Assisted</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab B: Welfare Sectors -->
      <div id="gdtab-pane-welfare" style="display: none;">
        <div class="drawer-section">
          <div class="drawer-section-title">Assisted Vulnerable Sectors Breakdown</div>
          <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.8125rem;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: 6px;">
              <span>Senior Citizens (60+ yrs):</span>
              <strong id="gdrawer-count-senior">0</strong>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: 6px;">
              <span>Persons with Disability (PWD):</span>
              <strong id="gdrawer-count-pwd">0</strong>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: 6px;">
              <span>Solo Parents:</span>
              <strong id="gdrawer-count-solo">0</strong>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: 6px;">
              <span>Pantawid Pamilya (4Ps):</span>
              <strong id="gdrawer-count-4ps">0</strong>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-hairline-soft); padding-bottom: 6px;">
              <span>Indigent Households:</span>
              <strong id="gdrawer-count-indigent">0</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab C: Evacuation & Hazard -->
      <div id="gdtab-pane-hazard" style="display: none;">
        <div class="drawer-section">
          <div class="drawer-section-title">Disaster Risk &amp; Evacuation Protocol</div>
          <div style="background: var(--color-canvas-soft); border-radius: var(--rounded-sm); padding: 12px; border: 1px solid var(--color-hairline-soft); margin-bottom: 10px;">
            <div style="font-size: 0.8125rem; font-weight: 700; color: var(--color-ink);" id="gdrawer-evac-hub">
              Barangay Hall Complex
            </div>
            <p class="typography-caption mt-xs" id="gdrawer-hazard-desc">
              Designated incident command center and emergency stockpile depot. Minimal flooding risk.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="drawer-footer">
      <button type="button" class="button-outline" onclick="closeGeoDrawer()" style="height: 36px; padding: 0 16px;">
        Close
      </button>
      <a href="residents.php" class="button-primary" id="gdrawer-drilldown-residents" style="height: 36px; padding: 0 18px; text-decoration: none; display: inline-flex; align-items: center;">
        View Residents &rarr;
      </a>
    </div>
  </aside>

  <!-- Printable Official DILG / CDRRMO Geo-Report Letterhead -->
  <div id="printable-geo-report">
    <div style="text-align: center; border-bottom: 2px solid #141414; padding-bottom: 12px; margin-bottom: 20px;">
      <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: #52525b;">Republic of the Philippines</div>
      <div style="font-size: 0.875rem; font-weight: 700; text-transform: uppercase;" id="print-jurisdiction">Province of Rizal &bull; Municipality of Rodriguez</div>
      <div style="font-size: 1.25rem; font-weight: 800; color: #09090b; margin: 4px 0;" id="print-brgy-name">BARANGAY SAN ISIDRO</div>
      <div style="font-size: 0.9375rem; font-weight: 800; color: #0066ff; text-transform: uppercase; margin-top: 6px;">
        OFFICIAL PUROK DEMOGRAPHIC &amp; GEO-HAZARD EXECUTIVE AUDIT
      </div>
      <div style="font-size: 0.75rem; color: #71717a; margin-top: 2px;" id="print-timestamp">
        Generated on: March 2026 &bull; DILG-CDRRMO Compliance Document
      </div>
    </div>

    <!-- Official Tabular Summary -->
    <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem; margin-bottom: 24px;">
      <thead>
        <tr style="background-color: #f4f4f5; border-bottom: 1.5px solid #141414;">
          <th style="padding: 8px; text-align: left; border: 1px solid #d4d4d8;">Purok Zone</th>
          <th style="padding: 8px; text-align: right; border: 1px solid #d4d4d8;">Population</th>
          <th style="padding: 8px; text-align: right; border: 1px solid #d4d4d8;">Households</th>
          <th style="padding: 8px; text-align: right; border: 1px solid #d4d4d8;">Voters</th>
          <th style="padding: 8px; text-align: right; border: 1px solid #d4d4d8;">Seniors</th>
          <th style="padding: 8px; text-align: right; border: 1px solid #d4d4d8;">PWD</th>
          <th style="padding: 8px; text-align: right; border: 1px solid #d4d4d8;">4Ps/Indigent</th>
          <th style="padding: 8px; text-align: center; border: 1px solid #d4d4d8;">Hazard Level</th>
          <th style="padding: 8px; text-align: left; border: 1px solid #d4d4d8;">Assigned Evacuation Hub</th>
        </tr>
      </thead>
      <tbody id="print-purok-table-body">
        <!-- Injected dynamically -->
      </tbody>
    </table>

    <!-- Signature Block -->
    <div style="display: flex; justify-content: space-between; margin-top: 60px; padding-top: 20px;">
      <div style="text-align: center; width: 220px;">
        <div style="border-bottom: 1px solid #141414; height: 35px;"></div>
        <div style="font-weight: 700; padding-top: 6px; font-size: 0.8125rem;">BARANGAY DISASTER OFFICER</div>
        <div style="font-size: 0.6875rem; color: #71717a;">BDRRMO Secretariat</div>
      </div>

      <div style="text-align: center; width: 220px;">
        <div style="border-bottom: 1px solid #141414; height: 35px;"></div>
        <div style="font-weight: 700; padding-top: 6px; font-size: 0.8125rem;" id="print-punong-brgy">HON. PUNONG BARANGAY</div>
        <div style="font-size: 0.6875rem; color: #71717a;">Punong Barangay / Council Chair</div>
      </div>
    </div>
  </div>

  <!-- Scripts (PHP version uses api.js instead of db.js + auth.js) -->
  <script src="js/api.js"></script>
  <script src="js/components/toast.js"></script>
  <script src="js/components/sidebar.js"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

  <script>
    // =========================================================================
    // Purok Geo-Profiling with Real Leaflet.js Map
    // =========================================================================
    let purokData = [];
    let activeLayer = 'population';
    let selectedPurok = 'Purok 3';
    let map, geoLayer, landmarkLayerGroup;
    let streetTiles, satelliteTiles, darkTiles;
    let currentTileMode = 'street';
    let purokLabelMarkers = [];

    // ---- Purok GeoJSON Boundaries (Real GPS: Barangay San Isidro, Rodriguez, Rizal) ----
    const purokGeoJSON = {
      type: 'FeatureCollection',
      features: [
        {
          type: 'Feature',
          properties: { purok: 'Purok 1' },
          geometry: {
            type: 'Polygon',
            coordinates: [[
              [121.12528, 14.74463], [121.12966, 14.74463], [121.12995, 14.74241],
              [121.12811, 14.74167], [121.12528, 14.74204], [121.12528, 14.74463]
            ]]
          }
        },
        {
          type: 'Feature',
          properties: { purok: 'Purok 2' },
          geometry: {
            type: 'Polygon',
            coordinates: [[
              [121.12966, 14.74463], [121.13461, 14.74463], [121.13447, 14.74241],
              [121.13178, 14.74222], [121.12995, 14.74241], [121.12966, 14.74463]
            ]]
          }
        },
        {
          type: 'Feature',
          properties: { purok: 'Purok 3' },
          geometry: {
            type: 'Polygon',
            coordinates: [[
              [121.12896, 14.74185], [121.13263, 14.74204], [121.13291, 14.73889],
              [121.12868, 14.73870], [121.12896, 14.74185]
            ]]
          }
        },
        {
          type: 'Feature',
          properties: { purok: 'Purok 4' },
          geometry: {
            type: 'Polygon',
            coordinates: [[
              [121.13461, 14.74463], [121.13772, 14.74463], [121.13772, 14.74130],
              [121.13447, 14.74241], [121.13461, 14.74463]
            ]]
          }
        },
        {
          type: 'Feature',
          properties: { purok: 'Purok 5' },
          geometry: {
            type: 'Polygon',
            coordinates: [[
              [121.12528, 14.74204], [121.12811, 14.74167], [121.12868, 14.73870],
              [121.12839, 14.73556], [121.12528, 14.73556], [121.12528, 14.74204]
            ]]
          }
        },
        {
          type: 'Feature',
          properties: { purok: 'Purok 6' },
          geometry: {
            type: 'Polygon',
            coordinates: [[
              [121.12839, 14.73870], [121.13319, 14.73833], [121.13772, 14.73722],
              [121.13772, 14.73556], [121.12839, 14.73556], [121.12839, 14.73870]
            ]]
          }
        },
        {
          type: 'Feature',
          properties: { purok: 'Purok 7' },
          geometry: {
            type: 'Polygon',
            coordinates: [[
              [121.13263, 14.74204], [121.13772, 14.74130], [121.13772, 14.73722],
              [121.13319, 14.73833], [121.13291, 14.73889], [121.13263, 14.74204]
            ]]
          }
        }
      ]
    };

    // ---- Purok Label Centroids ----
    const purokCentroids = {
      'Purok 1': [14.74308, 121.12766],
      'Purok 2': [14.74334, 121.13209],
      'Purok 3': [14.74037, 121.13080],
      'Purok 4': [14.74309, 121.13613],
      'Purok 5': [14.73880, 121.12695],
      'Purok 6': [14.73718, 121.13252],
      'Purok 7': [14.73976, 121.13529]
    };

    // ---- Landmark Definitions (PHP version links to .php) ----
    const landmarkDefs = [
      { name: 'Barangay Hall Complex & Command Center', lat: 14.73991, lng: 121.13108, iconSvg: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>', color: '#141414', size: 28 },
      { name: 'Barangay Health Center & Birthing Clinic', lat: 14.73991, lng: 121.13179, iconSvg: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20"/><path d="M2 12h20"/></svg>', color: '#10b981', size: 24, link: 'health.php' },
      { name: 'Central Elementary School (Primary Evacuation Hub)', lat: 14.74259, lng: 121.13617, iconSvg: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>', color: '#6366f1', size: 24 },
      { name: 'Riverside Tanod Outpost & Early Flood Gauge', lat: 14.74250, lng: 121.12747, iconSvg: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>', color: '#ef4444', size: 24 },
      { name: 'Greenfields Livelihood Center & Food Hub', lat: 14.73602, lng: 121.13263, iconSvg: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>', color: '#d97706', size: 24 }
    ];

    // =========================================================================
    // INIT
    // =========================================================================
    document.addEventListener('DOMContentLoaded', async () => {
      try { await AppSidebar.render('geo-profiling'); } catch (err) { console.error('Sidebar mount error:', err); }
      if (window.authService) { await authService.requireAuth('login.php'); }

      await loadBarangayIdentitySettings();
      initLeafletMap();
      await loadGeoProfilingData();

      const params = new URLSearchParams(window.location.search);
      const urlPurok = params.get('purok');
      selectPurok(urlPurok || 'Purok 3');

      bindGisEventListeners();
    });

    // =========================================================================
    // LEAFLET MAP INITIALIZATION
    // =========================================================================
    function initLeafletMap() {
      map = L.map('leaflet-map', {
        center: [14.7400, 121.1315],
        zoom: 16,
        zoomControl: true,
        scrollWheelZoom: true
      });

      // Street tiles (default)
      streetTiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
      }).addTo(map);

      // Satellite tiles
      satelliteTiles = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics',
        maxZoom: 19
      });

      // Dark tiles (for dark mode)
      darkTiles = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>',
        maxZoom: 19
      });

      // Apply dark tiles if dark mode is active
      if (document.documentElement.getAttribute('data-theme') === 'dark') {
        map.removeLayer(streetTiles);
        darkTiles.addTo(map);
        currentTileMode = 'dark';
      }

      // Watch for theme changes
      const observer = new MutationObserver(() => {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        if (isDark && currentTileMode === 'street') {
          map.removeLayer(streetTiles);
          darkTiles.addTo(map);
          currentTileMode = 'dark';
        } else if (!isDark && currentTileMode === 'dark') {
          map.removeLayer(darkTiles);
          streetTiles.addTo(map);
          currentTileMode = 'street';
        }
      });
      observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

      // Initialize GeoJSON layer
      geoLayer = L.geoJSON(purokGeoJSON, {
        style: () => ({ fillColor: '#e4e4e7', fillOpacity: 0.6, color: '#ffffff', weight: 2 }),
        onEachFeature: (feature, layer) => {
          const pName = feature.properties.purok;

          layer.on('click', () => selectPurok(pName));

          layer.on('mouseover', (e) => {
            const p = purokData.find(item => item.purok === pName);
            if (p) {
              layer.bindTooltip(
                `<strong>${p.name}</strong><br>Residents: <strong>${p.residents}</strong> &bull; Households: <strong>${p.households}</strong><br>Hazard: <strong>${p.hazard_rating} Risk</strong>`,
                { sticky: true, className: 'purok-map-tooltip' }
              ).openTooltip();
            }
            layer.setStyle({ weight: 4, color: '#2563eb' });
            layer.bringToFront();
          });

          layer.on('mouseout', () => {
            layer.unbindTooltip();
            renderMapChoropleth();
            highlightSelectedPurok();
          });
        }
      }).addTo(map);

      // Add purok labels
      addPurokLabels();

      // Add landmark markers
      landmarkLayerGroup = L.layerGroup().addTo(map);
      addLandmarkMarkers();
    }

    function addPurokLabels() {
      Object.entries(purokCentroids).forEach(([pName, coords]) => {
        const label = L.marker(coords, {
          icon: L.divIcon({
            className: 'purok-label-icon',
            html: `<div style="line-height:1.3;"><strong>${pName.toUpperCase()}</strong><br><span id="map-badge-${pName}" style="font-size:10px; font-weight:600; opacity:0.75;"></span></div>`,
            iconSize: [120, 40],
            iconAnchor: [60, 20]
          }),
          interactive: false
        });
        label.addTo(map);
        purokLabelMarkers.push({ purok: pName, marker: label });
      });
    }

    function addLandmarkMarkers() {
      landmarkDefs.forEach(lm => {
        const icon = L.divIcon({
          className: '',
          html: `<div class="landmark-marker-icon" style="width:${lm.size}px; height:${lm.size}px; background:${lm.color}; display:flex; align-items:center; justify-content:center; color:#ffffff;" title="${lm.name}">${lm.iconSvg}</div>`,
          iconSize: [lm.size, lm.size],
          iconAnchor: [lm.size / 2, lm.size / 2]
        });

        const marker = L.marker([lm.lat, lm.lng], { icon });
        marker.bindTooltip(`<strong>Official Landmark:</strong><br>${lm.name}`, { direction: 'top', offset: [0, -lm.size / 2] });

        if (lm.link) {
          marker.on('click', () => { window.location.href = lm.link; });
        }

        marker.addTo(landmarkLayerGroup);
      });
    }

    function highlightSelectedPurok() {
      geoLayer.eachLayer(layer => {
        if (layer.feature.properties.purok === selectedPurok) {
          layer.setStyle({ weight: 4, color: '#141414' });
          layer.bringToFront();
        }
      });
    }

    // =========================================================================
    // SETTINGS
    // =========================================================================
    async function loadBarangayIdentitySettings() {
      try {
        const idSetting = await window.barangayDB.get('settings', 'identity');
        if (idSetting && idSetting.value) {
          const v = idSetting.value;
          if (v.barangayName) document.getElementById('print-brgy-name').textContent = v.barangayName.toUpperCase();
          if (v.province && v.municipalityCity) document.getElementById('print-jurisdiction').textContent = `${v.province.toUpperCase()} \u2022 ${v.municipalityCity.toUpperCase()}`;
        }
        const officials = await window.barangayDB.getAll('officials');
        if (officials && officials.length > 0) {
          const cap = officials.find(o => o.position === 'Punong Barangay' && o.status === 'active');
          if (cap) document.getElementById('print-punong-brgy').textContent = cap.fullName.toUpperCase();
        }
      } catch (err) { console.warn('Settings load error:', err); }
    }

    // =========================================================================
    // DATA LOADING
    // =========================================================================
    async function loadGeoProfilingData() {
      try {
        const residents = (await window.barangayDB.getAll('residents')) || [];
        const households = (await window.barangayDB.getAll('households')) || [];

        const standardPuroks = ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5', 'Purok 6', 'Purok 7'];

        const purokMeta = {
          'Purok 1': { name: 'Purok 1 - Riverside North', subzone: 'Waterfront & Lowland', leader: 'Kgd. Roberto Santos (Disaster Committee)', evacuation_center: 'Barangay Multi-Purpose Hall', hazard_profile: 'High Flood Risk (River Corridor)', default_hazard: 'High' },
          'Purok 2': { name: 'Purok 2 - Poblacion Central', subzone: 'Commercial & Market Center', leader: 'Kgd. Elena Bautista (Trade & Livelihood)', evacuation_center: 'Central Elementary Gymnasium', hazard_profile: 'Low Flood / Commercial Density', default_hazard: 'Low' },
          'Purok 3': { name: 'Purok 3 - Barangay Centro', subzone: 'Civic & Government Core', leader: 'Hon. Punong Barangay / Kgd. Manuel Cruz', evacuation_center: 'Barangay Hall Complex', hazard_profile: 'Safe Zone / Incident Command Post', default_hazard: 'Low' },
          'Purok 4': { name: 'Purok 4 - Residential Heights', subzone: 'Subdivision & Family Dwellings', leader: 'Kgd. Maria Flores (Health & Sanitation)', evacuation_center: 'Purok 4 Covered Court', hazard_profile: 'Minimal Hazard Exposure', default_hazard: 'Low' },
          'Purok 5': { name: 'Purok 5 - Western Hillside', subzone: 'Elevated Slope & Watershed', leader: 'Kgd. Antonio Reyes (Peace & Order)', evacuation_center: 'Hillside Chapel Annex', hazard_profile: 'Moderate Slope / Landslide Watch', default_hazard: 'Medium' },
          'Purok 6': { name: 'Purok 6 - Greenfields Agro', subzone: 'Agricultural & Open Plains', leader: 'Kgd. Josefa Dimaculangan (Agriculture)', evacuation_center: 'Greenfields Elementary School', hazard_profile: 'Open Wind Exposure / Low Flood', default_hazard: 'Low' },
          'Purok 7': { name: 'Purok 7 - Industrial Highway Rim', subzone: 'Perimeter & Highway Access', leader: 'Kgd. Danilo Mercado (Transportation)', evacuation_center: 'Highway Terminal Pavilion', hazard_profile: 'Vehicular Traffic / Drainage Focus', default_hazard: 'Medium' }
        };

        purokData = standardPuroks.map(pName => {
          const resInPurok = residents.filter(r => (r.purok || '').toLowerCase() === pName.toLowerCase() && r.status !== 'Deceased');
          const hhInPurok = households.filter(h => (h.purok || '').toLowerCase() === pName.toLowerCase());
          const meta = purokMeta[pName];
          const totalRes = resInPurok.length;
          const totalHH = hhInPurok.length;
          const avgSize = totalHH > 0 ? (totalRes / totalHH).toFixed(1) : 0;
          const males = resInPurok.filter(r => (r.gender || '').toLowerCase() === 'male').length;
          const females = resInPurok.filter(r => (r.gender || '').toLowerCase() === 'female').length;
          const voters = resInPurok.filter(r => r.isVoter || r.voterStatus === 'Registered').length;
          const seniors = resInPurok.filter(r => r.isSenior || (r.age >= 60)).length;
          const pwd = resInPurok.filter(r => r.isPwd).length;
          const soloParents = resInPurok.filter(r => r.isSoloParent).length;
          const fourPs = resInPurok.filter(r => r.isFourPs || r.is_4ps).length;
          const indigents = resInPurok.filter(r => r.isIndigent).length;
          const highRiskHH = hhInPurok.filter(h => (h.hazardRisk || '').toLowerCase() === 'high' || (h.hazard_risk || '').toLowerCase() === 'high').length;
          const medRiskHH = hhInPurok.filter(h => (h.hazardRisk || '').toLowerCase() === 'medium' || (h.hazard_risk || '').toLowerCase() === 'medium').length;
          let hazardRating = meta.default_hazard;
          if (highRiskHH > 0) hazardRating = 'High';
          else if (medRiskHH > 0) hazardRating = 'Medium';
          const vulnScore = seniors + pwd + soloParents + fourPs + indigents;

          return {
            purok: pName, name: meta.name, subzone: meta.subzone, leader: meta.leader,
            evacuation_center: meta.evacuation_center, hazard_profile: meta.hazard_profile,
            hazard_rating: hazardRating, residents: totalRes, households: totalHH,
            avg_family_size: avgSize, males, females, voters, seniors, pwd,
            solo_parents: soloParents, four_ps: fourPs, indigents,
            vulnerability_score: vulnScore, high_risk_hh: highRiskHH
          };
        });

        updateTelemetryLadder(residents.length);
        renderMapChoropleth();
        renderPrintTable();
      } catch (err) {
        console.error('Failed to load geo profiling data:', err);
        Toast.error('Could not load spatial demographics.');
      }
    }

    // =========================================================================
    // TELEMETRY KPIs
    // =========================================================================
    function updateTelemetryLadder(totalPop) {
      if (purokData.length === 0) return;
      const sortedByPop = [...purokData].sort((a, b) => b.residents - a.residents);
      const topPop = sortedByPop[0];
      const popPct = totalPop > 0 ? Math.round((topPop.residents / totalPop) * 100) : 0;
      document.getElementById('kpi-most-populated').textContent = topPop.purok;
      document.getElementById('kpi-most-populated-sub').textContent = `${topPop.residents} residents (${popPct}% of total)`;

      const sortedByVuln = [...purokData].sort((a, b) => b.vulnerability_score - a.vulnerability_score);
      const topVuln = sortedByVuln[0];
      document.getElementById('kpi-highest-vuln').textContent = topVuln.purok;
      document.getElementById('kpi-highest-vuln-sub').textContent = `${topVuln.vulnerability_score} priority assisted sectors`;

      const totalHighHazard = purokData.reduce((acc, p) => acc + (p.high_risk_hh || 0), 0);
      document.getElementById('kpi-hazard-count').textContent = totalHighHazard;
    }

    // =========================================================================
    // CHOROPLETH RENDERING
    // =========================================================================
    function renderMapChoropleth() {
      if (!geoLayer || purokData.length === 0) return;

      const maxPop = Math.max(...purokData.map(p => p.residents), 1);
      const maxVuln = Math.max(...purokData.map(p => p.vulnerability_score), 1);
      const maxHH = Math.max(...purokData.map(p => p.households), 1);

      geoLayer.eachLayer(layer => {
        const pName = layer.feature.properties.purok;
        const p = purokData.find(item => item.purok === pName);
        if (!p) return;

        let fillColor = '#e4e4e7';
        let badgeText = p.subzone;

        if (activeLayer === 'population') {
          const ratio = p.residents / maxPop;
          fillColor = getBlueDensityColor(ratio);
          badgeText = `${p.residents} Residents`;
        } else if (activeLayer === 'hazard') {
          if (p.hazard_rating === 'High') { fillColor = 'rgba(239, 68, 68, 0.65)'; badgeText = 'HIGH FLOOD RISK'; }
          else if (p.hazard_rating === 'Medium') { fillColor = 'rgba(245, 158, 11, 0.65)'; badgeText = 'MODERATE WATCH'; }
          else { fillColor = 'rgba(16, 185, 129, 0.6)'; badgeText = 'SAFE LOW RISK'; }
        } else if (activeLayer === 'vulnerability') {
          const ratio = p.vulnerability_score / maxVuln;
          fillColor = getAmberDensityColor(ratio);
          badgeText = `Vuln Index: ${p.vulnerability_score}`;
        } else if (activeLayer === 'households') {
          const ratio = p.households / maxHH;
          fillColor = getPurpleDensityColor(ratio);
          badgeText = `${p.households} Households`;
        }

        layer.setStyle({
          fillColor: fillColor,
          fillOpacity: 0.65,
          color: pName === selectedPurok ? '#141414' : '#ffffff',
          weight: pName === selectedPurok ? 4 : 2
        });

        // Update label badge text
        const badgeEl = document.getElementById(`map-badge-${pName}`);
        if (badgeEl) badgeEl.textContent = badgeText;
      });

      renderLegend();
    }

    // ---- Color Scales ----
    function getBlueDensityColor(r) {
      if (r > 0.75) return 'rgba(30, 58, 138, 0.85)';
      if (r > 0.50) return 'rgba(37, 99, 235, 0.75)';
      if (r > 0.25) return 'rgba(96, 165, 250, 0.65)';
      return 'rgba(219, 234, 254, 0.75)';
    }
    function getAmberDensityColor(r) {
      if (r > 0.75) return 'rgba(180, 83, 9, 0.85)';
      if (r > 0.50) return 'rgba(217, 119, 6, 0.75)';
      if (r > 0.25) return 'rgba(251, 191, 36, 0.65)';
      return 'rgba(254, 243, 199, 0.75)';
    }
    function getPurpleDensityColor(r) {
      if (r > 0.75) return 'rgba(109, 40, 217, 0.85)';
      if (r > 0.50) return 'rgba(139, 92, 246, 0.75)';
      if (r > 0.25) return 'rgba(196, 181, 253, 0.65)';
      return 'rgba(237, 233, 254, 0.75)';
    }

    // =========================================================================
    // LEGEND
    // =========================================================================
    function renderLegend() {
      const legendTitle = document.getElementById('gis-legend-title');
      const ramp = document.getElementById('gis-legend-ramp');

      if (activeLayer === 'population') {
        legendTitle.textContent = 'Population Density Concentration:';
        ramp.innerHTML = `<span>Low</span><div class="legend-swatch" style="background: rgba(219, 234, 254, 0.75);"></div><div class="legend-swatch" style="background: rgba(96, 165, 250, 0.65);"></div><div class="legend-swatch" style="background: rgba(37, 99, 235, 0.75);"></div><div class="legend-swatch" style="background: rgba(30, 58, 138, 0.85);"></div><span>High Density</span>`;
      } else if (activeLayer === 'hazard') {
        legendTitle.textContent = 'Disaster Hazard Safety Zones:';
        ramp.innerHTML = `<div style="display:flex;align-items:center;gap:4px;"><div class="legend-swatch" style="background:#10b981;"></div> Safe (Low)</div><div style="display:flex;align-items:center;gap:4px;margin-left:8px;"><div class="legend-swatch" style="background:#f59e0b;"></div> Watch (Moderate)</div><div style="display:flex;align-items:center;gap:4px;margin-left:8px;"><div class="legend-swatch" style="background:#ef4444;"></div> Floodway / Critical</div>`;
      } else if (activeLayer === 'vulnerability') {
        legendTitle.textContent = 'Social Vulnerability (Seniors, PWD, 4Ps):';
        ramp.innerHTML = `<span>Low</span><div class="legend-swatch" style="background: rgba(254, 243, 199, 0.75);"></div><div class="legend-swatch" style="background: rgba(251, 191, 36, 0.65);"></div><div class="legend-swatch" style="background: rgba(217, 119, 6, 0.75);"></div><div class="legend-swatch" style="background: rgba(180, 83, 9, 0.85);"></div><span>High Priority</span>`;
      } else if (activeLayer === 'households') {
        legendTitle.textContent = 'Household Density & Congestion:';
        ramp.innerHTML = `<span>Sparse</span><div class="legend-swatch" style="background: rgba(237, 233, 254, 0.75);"></div><div class="legend-swatch" style="background: rgba(196, 181, 253, 0.65);"></div><div class="legend-swatch" style="background: rgba(139, 92, 246, 0.75);"></div><div class="legend-swatch" style="background: rgba(109, 40, 217, 0.85);"></div><span>Congested</span>`;
      }
    }

    // =========================================================================
    // SELECT PUROK
    // =========================================================================
    function selectPurok(pName) {
      const p = purokData.find(item => item.purok.toLowerCase() === pName.toLowerCase());
      if (!p) return;
      selectedPurok = p.purok;

      renderMapChoropleth();

      const centroid = purokCentroids[p.purok];
      if (centroid) map.panTo(centroid);

      document.getElementById('select-purok-jump').value = p.purok;

      document.getElementById('detail-subzone').textContent = p.subzone.toUpperCase();
      document.getElementById('detail-purok-name').textContent = p.name;

      const hazardBadge = document.getElementById('detail-hazard-badge');
      if (p.hazard_rating === 'High') { hazardBadge.className = 'badge-rose'; hazardBadge.textContent = 'High Flood / Hazard'; }
      else if (p.hazard_rating === 'Medium') { hazardBadge.className = 'badge-amber'; hazardBadge.textContent = 'Moderate Watch'; }
      else { hazardBadge.className = 'badge-emerald'; hazardBadge.textContent = 'Safe Zone'; }

      document.getElementById('detail-leader-name').textContent = p.leader;
      document.getElementById('detail-resident-count').textContent = p.residents;
      document.getElementById('detail-gender-split').textContent = `${p.males} Males \u2022 ${p.females} Females`;
      document.getElementById('detail-household-count').textContent = p.households;
      document.getElementById('detail-family-size').textContent = `${p.avg_family_size} avg family size`;

      const voterPct = p.residents > 0 ? Math.round((p.voters / p.residents) * 100) : 0;
      document.getElementById('detail-voter-count').textContent = p.voters;
      document.getElementById('detail-voter-pct').textContent = `${voterPct}% electoral share`;

      document.getElementById('detail-vuln-count').textContent = p.vulnerability_score;
      document.getElementById('detail-count-senior').textContent = `${p.seniors} Seniors (60+)`;
      document.getElementById('detail-count-pwd').textContent = `${p.pwd} PWD`;
      document.getElementById('detail-count-solo').textContent = `${p.solo_parents} Solo Parents`;
      document.getElementById('detail-count-4ps').textContent = `${p.four_ps} 4Ps`;
      document.getElementById('detail-count-indigent').textContent = `${p.indigents} Indigents`;

      document.getElementById('detail-evac-hub').textContent = p.evacuation_center;
      document.getElementById('detail-hazard-desc').textContent = `${p.hazard_profile}. Assigned route leading to ${p.evacuation_center}.`;

      const ext = window.location.pathname.endsWith('.php') ? '.php' : '.html';
      const resBtn = document.getElementById('btn-drilldown-residents');
      resBtn.href = `residents${ext}?purok=${encodeURIComponent(p.purok)}`;
      resBtn.innerHTML = `View Residents in ${p.purok} &rarr;`;

      const hhBtn = document.getElementById('btn-drilldown-households');
      hhBtn.href = `households${ext}?purok=${encodeURIComponent(p.purok)}`;
      hhBtn.innerHTML = `View Households in ${p.purok} &rarr;`;

      // Populate Level 3 Slide-over Drawer
      const gdSubzone = document.getElementById('gdrawer-subzone');
      if (gdSubzone) gdSubzone.textContent = p.subzone.toUpperCase();
      const gdName = document.getElementById('gdrawer-purok-name');
      if (gdName) gdName.textContent = p.name;
      const gdHazard = document.getElementById('gdrawer-hazard-badge');
      if (gdHazard) {
        if (p.hazard_rating === 'High') { gdHazard.className = 'badge-rose'; gdHazard.textContent = 'High Flood / Hazard'; }
        else if (p.hazard_rating === 'Medium') { gdHazard.className = 'badge-amber'; gdHazard.textContent = 'Moderate Watch'; }
        else { gdHazard.className = 'badge-emerald'; gdHazard.textContent = 'Safe Zone'; }
      }
      const gdLeader = document.getElementById('gdrawer-leader-name');
      if (gdLeader) gdLeader.textContent = p.leader;
      const gdRes = document.getElementById('gdrawer-resident-count');
      if (gdRes) gdRes.textContent = p.residents;
      const gdGender = document.getElementById('gdrawer-gender-split');
      if (gdGender) gdGender.textContent = `${p.males} Males \u2022 ${p.females} Females`;
      const gdHH = document.getElementById('gdrawer-household-count');
      if (gdHH) gdHH.textContent = p.households;
      const gdFamily = document.getElementById('gdrawer-family-size');
      if (gdFamily) gdFamily.textContent = `${p.avg_family_size} avg family size`;
      const gdVoter = document.getElementById('gdrawer-voter-count');
      if (gdVoter) gdVoter.textContent = p.voters;
      const gdVoterPct = document.getElementById('gdrawer-voter-pct');
      if (gdVoterPct) gdVoterPct.textContent = `${voterPct}% electoral share`;
      const gdVuln = document.getElementById('gdrawer-vuln-count');
      if (gdVuln) gdVuln.textContent = p.vulnerability_score;
      const gdSenior = document.getElementById('gdrawer-count-senior');
      if (gdSenior) gdSenior.textContent = `${p.seniors} Seniors (60+)`;
      const gdPwd = document.getElementById('gdrawer-count-pwd');
      if (gdPwd) gdPwd.textContent = `${p.pwd} PWD`;
      const gdSolo = document.getElementById('gdrawer-count-solo');
      if (gdSolo) gdSolo.textContent = `${p.solo_parents} Solo Parents`;
      const gd4ps = document.getElementById('gdrawer-count-4ps');
      if (gd4ps) gd4ps.textContent = `${p.four_ps} 4Ps`;
      const gdInd = document.getElementById('gdrawer-count-indigent');
      if (gdInd) gdInd.textContent = `${p.indigents} Indigents`;
      const gdEvac = document.getElementById('gdrawer-evac-hub');
      if (gdEvac) gdEvac.textContent = p.evacuation_center;
      const gdDesc = document.getElementById('gdrawer-hazard-desc');
      if (gdDesc) gdDesc.textContent = `${p.hazard_profile}. Assigned route leading to ${p.evacuation_center}.`;
      const gdDrill = document.getElementById('gdrawer-drilldown-residents');
      if (gdDrill) {
        gdDrill.href = `residents${ext}?purok=${encodeURIComponent(p.purok)}`;
        gdDrill.innerHTML = `View Residents in ${p.purok} &rarr;`;
      }
    }

    // Level 3 Slide-Over Detail Drawer
    window.openGeoDrawer = function() {
      const drawer = document.getElementById('geo-detail-drawer');
      const backdrop = document.getElementById('geo-drawer-backdrop');
      if (drawer) drawer.classList.add('active');
      if (backdrop) backdrop.classList.add('active');
    };

    window.closeGeoDrawer = function() {
      const drawer = document.getElementById('geo-detail-drawer');
      const backdrop = document.getElementById('geo-drawer-backdrop');
      if (drawer) drawer.classList.remove('active');
      if (backdrop) backdrop.classList.remove('active');
    };

    window.switchGeoDrawerTab = function(tabName) {
      ['demo', 'welfare', 'hazard'].forEach(t => {
        const btn = document.getElementById(`gdtab-btn-${t}`);
        const pane = document.getElementById(`gdtab-pane-${t}`);
        if (btn) btn.classList.toggle('active', t === tabName);
        if (pane) pane.style.display = t === tabName ? 'block' : 'none';
      });
    };

    // Contextual Info Popover
    window.toggleGeoInfoPopover = function(e) {
      if (e) e.stopPropagation();
      const popover = document.getElementById('geo-info-popover');
      if (popover) {
        popover.style.display = popover.style.display === 'block' ? 'none' : 'block';
      }
    };

    document.addEventListener('click', (e) => {
      const popover = document.getElementById('geo-info-popover');
      if (popover && popover.style.display === 'block' && !e.target.closest('#geo-info-popover') && !e.target.closest('.info-trigger')) {
        popover.style.display = 'none';
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeGeoDrawer();
        const popover = document.getElementById('geo-info-popover');
        if (popover) popover.style.display = 'none';
      }
    });

    // =========================================================================
    // EVENT LISTENERS
    // =========================================================================
    function bindGisEventListeners() {
      document.querySelectorAll('.gis-layer-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          document.querySelectorAll('.gis-layer-btn').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          activeLayer = btn.getAttribute('data-layer');
          renderMapChoropleth();
        });
      });

      document.getElementById('select-purok-jump').addEventListener('change', (e) => {
        if (e.target.value) selectPurok(e.target.value);
      });

      document.getElementById('toggle-landmarks').addEventListener('change', (e) => {
        if (e.target.checked) { map.addLayer(landmarkLayerGroup); }
        else { map.removeLayer(landmarkLayerGroup); }
      });

      const tileBtn = document.getElementById('btn-toggle-tiles');
      tileBtn.addEventListener('click', () => {
        if (currentTileMode === 'satellite') {
          map.removeLayer(satelliteTiles);
          const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
          if (isDark) { darkTiles.addTo(map); currentTileMode = 'dark'; }
          else { streetTiles.addTo(map); currentTileMode = 'street'; }
          tileBtn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> <span>Satellite</span>';
        } else {
          if (currentTileMode === 'street') map.removeLayer(streetTiles);
          else if (currentTileMode === 'dark') map.removeLayer(darkTiles);
          satelliteTiles.addTo(map);
          currentTileMode = 'satellite';
          tileBtn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg> <span>Street</span>';
        }
      });

      document.getElementById('btn-print-geo-report').addEventListener('click', () => window.print());
      document.getElementById('btn-export-gis-csv').addEventListener('click', exportPurokCsv);
    }

    // =========================================================================
    // CSV EXPORT
    // =========================================================================
    function exportPurokCsv() {
      if (purokData.length === 0) return;
      const headers = ['Purok', 'Subzone', 'Zone Leader', 'Residents', 'Households', 'Avg Family Size', 'Males', 'Females', 'Voters', 'Seniors', 'PWD', 'Solo Parents', '4Ps Beneficiaries', 'Indigents', 'Hazard Rating', 'Evacuation Center'];
      const rows = purokData.map(p => [
        `"${p.purok}"`, `"${p.subzone}"`, `"${p.leader}"`, p.residents, p.households, p.avg_family_size,
        p.males, p.females, p.voters, p.seniors, p.pwd, p.solo_parents, p.four_ps, p.indigents,
        `"${p.hazard_rating}"`, `"${p.evacuation_center}"`
      ]);
      const csvContent = '\uFEFF' + [headers.join(','), ...rows.map(r => r.join(','))].join('\r\n');
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.setAttribute('href', url);
      link.setAttribute('download', `Barangay_Purok_GeoProfiling_${new Date().toISOString().split('T')[0]}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
      Toast.success('Purok Geo-Profiling CSV downloaded.');
    }

    // =========================================================================
    // PRINT TABLE
    // =========================================================================
    function renderPrintTable() {
      const tbody = document.getElementById('print-purok-table-body');
      if (!tbody) return;
      tbody.innerHTML = purokData.map(p => `
        <tr>
          <td style="padding: 6px 8px; border: 1px solid #d4d4d8; font-weight: 700;">${p.name}</td>
          <td style="padding: 6px 8px; border: 1px solid #d4d4d8; text-align: right;">${p.residents}</td>
          <td style="padding: 6px 8px; border: 1px solid #d4d4d8; text-align: right;">${p.households}</td>
          <td style="padding: 6px 8px; border: 1px solid #d4d4d8; text-align: right;">${p.voters}</td>
          <td style="padding: 6px 8px; border: 1px solid #d4d4d8; text-align: right;">${p.seniors}</td>
          <td style="padding: 6px 8px; border: 1px solid #d4d4d8; text-align: right;">${p.pwd}</td>
          <td style="padding: 6px 8px; border: 1px solid #d4d4d8; text-align: right;">${p.four_ps + p.indigents}</td>
          <td style="padding: 6px 8px; border: 1px solid #d4d4d8; text-align: center; font-weight: 700;">${p.hazard_rating}</td>
          <td style="padding: 6px 8px; border: 1px solid #d4d4d8;">${p.evacuation_center}</td>
        </tr>
      `).join('');
    }

    function toggleGeoInfoPopover(e) {
      if (e) e.stopPropagation();
      const card = document.getElementById('geo-info-popover');
      if (!card) return;
      card.style.display = card.style.display === 'block' ? 'none' : 'block';
    }

    document.addEventListener('click', (e) => {
      const popover = document.getElementById('geo-info-popover');
      if (popover && popover.style.display === 'block' && !e.target.closest('#geo-info-popover') && !e.target.closest('.info-trigger')) {
        popover.style.display = 'none';
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        const popover = document.getElementById('geo-info-popover');
        if (popover) popover.style.display = 'none';
      }
    });
  </script>
</body>
</html>
