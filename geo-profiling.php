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

    /* GIS Vector Map Canvas Container */
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

    /* Map Viewport Area */
    .gis-viewport-wrap {
      width: 100%;
      height: 520px;
      background-color: var(--color-canvas-soft);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-sm);
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      user-select: none;
    }

    .gis-svg-map {
      width: 100%;
      height: 100%;
      transition: transform 0.25s ease-out;
      transform-origin: center center;
    }

    /* Purok Polygons & Interactions */
    .purok-polygon {
      stroke: #ffffff;
      stroke-width: 2px;
      cursor: pointer;
      transition: fill 0.3s ease, stroke-width 0.2s ease, filter 0.2s ease;
    }

    [data-theme="dark"] .purok-polygon {
      stroke: #18181b;
    }

    .purok-polygon:hover {
      stroke-width: 3.5px;
      stroke: var(--color-primary);
      filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.15));
    }

    .purok-polygon.selected {
      stroke-width: 4px;
      stroke: var(--color-ink);
      filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.25));
    }

    .purok-label-text {
      font-size: 13px;
      font-weight: 800;
      fill: #141414;
      text-anchor: middle;
      pointer-events: none;
      paint-order: stroke;
      stroke: rgba(255, 255, 255, 0.85);
      stroke-width: 3px;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    [data-theme="dark"] .purok-label-text {
      fill: #f4f4f5;
      stroke: rgba(20, 20, 20, 0.85);
    }

    .purok-sub-text {
      font-size: 10px;
      font-weight: 600;
      fill: #52525b;
      text-anchor: middle;
      pointer-events: none;
    }

    [data-theme="dark"] .purok-sub-text {
      fill: #a1a1aa;
    }

    /* River & Natural Hazard Path */
    .hazard-waterway {
      fill: none;
      stroke: #38bdf8;
      stroke-width: 14px;
      stroke-linecap: round;
      stroke-linejoin: round;
      opacity: 0.75;
      stroke-dasharray: 6 3;
    }

    /* Landmarks */
    .gis-landmark-group {
      cursor: pointer;
      transition: transform 0.2s ease;
    }

    .gis-landmark-group:hover {
      transform: scale(1.15);
    }

    /* Zoom / Pan Controls Overlay */
    .gis-controls-overlay {
      position: absolute;
      right: 14px;
      bottom: 14px;
      display: flex;
      flex-direction: column;
      gap: 6px;
      background: var(--color-canvas);
      padding: 4px;
      border-radius: var(--rounded-md);
      border: 1px solid var(--color-hairline);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      z-index: 10;
    }

    .gis-ctrl-btn {
      width: 32px;
      height: 32px;
      border: 1px solid transparent;
      background: transparent;
      color: var(--color-ink);
      border-radius: var(--rounded-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.15s ease;
    }

    .gis-ctrl-btn:hover {
      background-color: var(--color-canvas-soft);
      border-color: var(--color-hairline-soft);
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

    /* Floating Tooltip */
    #gis-tooltip {
      position: absolute;
      background: #141414;
      color: #ffffff;
      padding: 6px 12px;
      border-radius: 8px;
      font-size: 0.75rem;
      pointer-events: none;
      display: none;
      z-index: 50;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
      line-height: 1.3;
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
        <!-- Page Hero Section -->
        <section class="page-hero">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--spacing-md);">
            <div>
              <div style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-xs);">
                <h1 class="typography-heading-2">Purok Demographic Density &amp; Geo-Profiling.</h1>
                <span class="badge-neutral" id="gis-zone-count-badge">7 Official Puroks</span>
              </div>
              <p class="typography-body-lg">
                Interactive GIS vector spatial command center for population density, disaster hazard risks, and household vulnerability profiling.
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

        <!-- Executive Operations Telemetry Ladder -->
        <section>
          <div class="stats-ladder">
            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">MOST POPULATED ZONE</span>
                <span class="badge-blue">Demographics</span>
              </div>
              <div class="stat-number" id="kpi-most-populated">Purok 1</div>
              <div class="typography-caption" id="kpi-most-populated-sub">0 residents (0% of total)</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">HIGHEST VULNERABILITY</span>
                <span class="badge-amber">Social Welfare</span>
              </div>
              <div class="stat-number" id="kpi-highest-vuln">Purok 1</div>
              <div class="typography-caption" id="kpi-highest-vuln-sub">0 priority assisted sectors</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">CRITICAL DISASTER EXPOSURE</span>
                <span class="badge-rose">Disaster Risk</span>
              </div>
              <div class="stat-number" id="kpi-hazard-count" style="color: #ef4444;">0</div>
              <div class="typography-caption">Dwellings in high flood/hazard zones</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">CENSUS COVERAGE</span>
                <span class="badge-emerald">GIS Registry</span>
              </div>
              <div class="stat-number" id="kpi-coverage">100%</div>
              <div class="typography-caption" id="kpi-coverage-sub">All 7 Puroks active &amp; mapped</div>
            </div>
          </div>
        </section>

        <!-- Interactive GIS Stage & Inspection Drawer Grid -->
        <div class="gis-stage-layout">
          <!-- Left: Interactive Vector Map Card -->
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

            <!-- Viewport Stage -->
            <div class="gis-viewport-wrap" id="gis-viewport">
              <svg id="gis-map-svg" class="gis-svg-map" viewBox="0 0 920 540" xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <!-- Soft Grid Pattern for Background Canvas -->
                  <pattern id="gis-grid" width="30" height="30" patternUnits="userSpaceOnUse">
                    <path d="M 30 0 L 0 0 0 30" fill="none" stroke="rgba(0,0,0,0.04)" stroke-width="1"/>
                  </pattern>
                </defs>

                <!-- Canvas Background -->
                <rect width="920" height="540" fill="url(#gis-grid)" />

                <!-- River Hazard Corridor (Natural Waterway) -->
                <path class="hazard-waterway" d="M -10,130 C 180,110 320,190 480,160 C 640,130 780,240 940,220" />
                <text x="820" y="240" font-size="10" font-weight="700" fill="#0284c7" letter-spacing="0.08em">RIVER HAZARD CORRIDOR &rarr;</text>

                <!-- PUROK 1: Riverside North (Top Left / Riverfront) -->
                <polygon id="poly-Purok 1" class="purok-polygon" data-purok="Purok 1"
                  points="20,20 330,20 350,140 220,180 20,160" />

                <!-- PUROK 2: Poblacion Central (Top Right / Commercial) -->
                <polygon id="poly-Purok 2" class="purok-polygon" data-purok="Purok 2"
                  points="330,20 680,20 670,140 480,150 350,140" />

                <!-- PUROK 4: Residential Heights (Far Top-Right / East) -->
                <polygon id="poly-Purok 4" class="purok-polygon" data-purok="Purok 4"
                  points="680,20 900,20 900,200 670,140" />

                <!-- PUROK 3: Barangay Centro / Civic Core (Center Island) -->
                <polygon id="poly-Purok 3" class="purok-polygon" data-purok="Purok 3"
                  points="280,170 540,160 560,330 260,340" />

                <!-- PUROK 5: Western Hillside (Mid Left / Slope) -->
                <polygon id="poly-Purok 5" class="purok-polygon" data-purok="Purok 5"
                  points="20,160 220,180 260,340 240,510 20,510" />

                <!-- PUROK 7: Industrial Highway (Mid Right / Perimeter) -->
                <polygon id="poly-Purok 7" class="purok-polygon" data-purok="Purok 7"
                  points="540,160 900,200 900,420 580,360 560,330" />

                <!-- PUROK 6: Greenfields Agro Plains (Bottom Center / South) -->
                <polygon id="poly-Purok 6" class="purok-polygon" data-purok="Purok 6"
                  points="240,340 580,360 900,420 900,510 240,510" />

                <!-- Centroid Labels -->
                <!-- Purok 1 -->
                <g id="lbl-Purok 1">
                  <text x="175" y="90" class="purok-label-text">PUROK 1</text>
                  <text x="175" y="110" class="purok-sub-text" id="badge-Purok 1">Riverside North</text>
                </g>

                <!-- Purok 2 -->
                <g id="lbl-Purok 2">
                  <text x="500" y="75" class="purok-label-text">PUROK 2</text>
                  <text x="500" y="95" class="purok-sub-text" id="badge-Purok 2">Poblacion Central</text>
                </g>

                <!-- Purok 3 -->
                <g id="lbl-Purok 3">
                  <text x="410" y="240" class="purok-label-text">PUROK 3</text>
                  <text x="410" y="260" class="purok-sub-text" id="badge-Purok 3">Barangay Centro</text>
                </g>

                <!-- Purok 4 -->
                <g id="lbl-Purok 4">
                  <text x="790" y="85" class="purok-label-text">PUROK 4</text>
                  <text x="790" y="105" class="purok-sub-text" id="badge-Purok 4">Residential Heights</text>
                </g>

                <!-- Purok 5 -->
                <g id="lbl-Purok 5">
                  <text x="135" y="325" class="purok-label-text">PUROK 5</text>
                  <text x="135" y="345" class="purok-sub-text" id="badge-Purok 5">Western Hillside</text>
                </g>

                <!-- Purok 6 -->
                <g id="lbl-Purok 6">
                  <text x="540" y="445" class="purok-label-text">PUROK 6</text>
                  <text x="540" y="465" class="purok-sub-text" id="badge-Purok 6">Greenfields Agro</text>
                </g>

                <!-- Purok 7 -->
                <g id="lbl-Purok 7">
                  <text x="735" y="275" class="purok-label-text">PUROK 7</text>
                  <text x="735" y="295" class="purok-sub-text" id="badge-Purok 7">Industrial Highway</text>
                </g>

                <!-- Landmark Pins Overlay Layer -->
                <g id="gis-landmarks-layer">
                  <!-- 1. Barangay Hall Complex (Purok 3) -->
                  <g class="gis-landmark-group" transform="translate(395, 275)" data-name="Barangay Hall Complex &amp; Command Center">
                    <rect x="-14" y="-14" width="28" height="28" rx="8" fill="#141414" stroke="#ffffff" stroke-width="2"/>
                    <text x="0" y="5" font-size="14" text-anchor="middle" fill="#ffffff">&#127963;</text>
                  </g>

                  <!-- 2. Barangay Health Center (Purok 3) -->
                  <g class="gis-landmark-group" transform="translate(445, 275)" data-name="Barangay Health Center &amp; Birthing Clinic (Click to open Health Hub)" onclick="window.location.href='health.php';" title="Open Barangay Health Station">
                    <rect x="-12" y="-12" width="24" height="24" rx="6" fill="#10b981" stroke="#ffffff" stroke-width="2"/>
                    <text x="0" y="4" font-size="12" text-anchor="middle" fill="#ffffff">&#127973;</text>
                  </g>

                  <!-- 3. Primary Evacuation School (Purok 4) -->
                  <g class="gis-landmark-group" transform="translate(790, 130)" data-name="Central Elementary School (Primary Evacuation Hub)">
                    <rect x="-12" y="-12" width="24" height="24" rx="6" fill="#6366f1" stroke="#ffffff" stroke-width="2"/>
                    <text x="0" y="4" font-size="12" text-anchor="middle" fill="#ffffff">&#127979;</text>
                  </g>

                  <!-- 4. Tanod Outpost / River Watch (Purok 1) -->
                  <g class="gis-landmark-group" transform="translate(175, 135)" data-name="Riverside Tanod Outpost &amp; Early Flood Gauge">
                    <rect x="-12" y="-12" width="24" height="24" rx="6" fill="#ef4444" stroke="#ffffff" stroke-width="2"/>
                    <text x="0" y="4" font-size="12" text-anchor="middle" fill="#ffffff">&#128658;</text>
                  </g>

                  <!-- 5. Greenfields Agritech Post (Purok 6) -->
                  <g class="gis-landmark-group" transform="translate(540, 485)" data-name="Greenfields Livelihood Center &amp; Food Hub">
                    <rect x="-12" y="-12" width="24" height="24" rx="6" fill="#d97706" stroke="#ffffff" stroke-width="2"/>
                    <text x="0" y="4" font-size="12" text-anchor="middle" fill="#ffffff">&#127806;</text>
                  </g>
                </g>
              </svg>

              <!-- Zoom / Reset Controls -->
              <div class="gis-controls-overlay">
                <button type="button" class="gis-ctrl-btn" id="btn-zoom-in" title="Zoom In">+</button>
                <button type="button" class="gis-ctrl-btn" id="btn-zoom-out" title="Zoom Out">&minus;</button>
                <button type="button" class="gis-ctrl-btn" id="btn-zoom-reset" title="Reset Zoom" style="font-size: 0.6875rem;">1:1</button>
              </div>

              <!-- Floating Tooltip -->
              <div id="gis-tooltip"></div>
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

  <!-- Scripts -->
  <script src="js/api.js"></script>
  <script src="js/components/toast.js"></script>
  <script src="js/components/sidebar.js"></script>

  <script>
    let purokData = [];
    let activeLayer = 'population';
    let selectedPurok = 'Purok 3';
    let zoomLevel = 1.0;

    document.addEventListener('DOMContentLoaded', async () => {
      // 1. Render App Shell Sidebar immediately
      try {
        await AppSidebar.render('geo-profiling');
      } catch (err) {
        console.error('Sidebar mount error:', err);
      }

      // 2. Route Guard
      if (window.authService) {
        await authService.requireAuth('login.php');
      }

      // 3. Load Settings for Letterhead & Officials
      await loadBarangayIdentitySettings();

      // 4. Load & Calculate Purok Demographic Data
      await loadGeoProfilingData();

      // 5. Check URL parameter (e.g. ?purok=Purok+1)
      const params = new URLSearchParams(window.location.search);
      const urlPurok = params.get('purok');
      if (urlPurok) {
        selectPurok(urlPurok);
      } else {
        selectPurok('Purok 3');
      }

      // 6. Bind All Event Listeners
      bindGisEventListeners();
    });

    // Load Settings
    async function loadBarangayIdentitySettings() {
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
        if (officials && officials.length > 0) {
          const cap = officials.find(o => o.position === 'Punong Barangay' && o.status === 'active');
          if (cap) {
            document.getElementById('print-punong-brgy').textContent = cap.fullName.toUpperCase();
          }
        }
      } catch (err) {
        console.warn('Settings load error:', err);
      }
    }

    // Load Geo-Profiling Data from IndexedDB
    async function loadGeoProfilingData() {
      try {
        const residents = (await window.barangayDB.getAll('residents')) || [];
        const households = (await window.barangayDB.getAll('households')) || [];

        const standardPuroks = ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5', 'Purok 6', 'Purok 7'];

        const purokMeta = {
          'Purok 1': {
            name: 'Purok 1 - Riverside North',
            subzone: 'Waterfront & Lowland',
            leader: 'Kgd. Roberto Santos (Disaster Committee)',
            evacuation_center: 'Barangay Multi-Purpose Hall',
            hazard_profile: 'High Flood Risk (River Corridor)',
            default_hazard: 'High'
          },
          'Purok 2': {
            name: 'Purok 2 - Poblacion Central',
            subzone: 'Commercial & Market Center',
            leader: 'Kgd. Elena Bautista (Trade & Livelihood)',
            evacuation_center: 'Central Elementary Gymnasium',
            hazard_profile: 'Low Flood / Commercial Density',
            default_hazard: 'Low'
          },
          'Purok 3': {
            name: 'Purok 3 - Barangay Centro',
            subzone: 'Civic & Government Core',
            leader: 'Hon. Punong Barangay / Kgd. Manuel Cruz',
            evacuation_center: 'Barangay Hall Complex',
            hazard_profile: 'Safe Zone / Incident Command Post',
            default_hazard: 'Low'
          },
          'Purok 4': {
            name: 'Purok 4 - Residential Heights',
            subzone: 'Subdivision & Family Dwellings',
            leader: 'Kgd. Maria Flores (Health & Sanitation)',
            evacuation_center: 'Purok 4 Covered Court',
            hazard_profile: 'Minimal Hazard Exposure',
            default_hazard: 'Low'
          },
          'Purok 5': {
            name: 'Purok 5 - Western Hillside',
            subzone: 'Elevated Slope & Watershed',
            leader: 'Kgd. Antonio Reyes (Peace & Order)',
            evacuation_center: 'Hillside Chapel Annex',
            hazard_profile: 'Moderate Slope / Landslide Watch',
            default_hazard: 'Medium'
          },
          'Purok 6': {
            name: 'Purok 6 - Greenfields Agro',
            subzone: 'Agricultural & Open Plains',
            leader: 'Kgd. Josefa Dimaculangan (Agriculture)',
            evacuation_center: 'Greenfields Elementary School',
            hazard_profile: 'Open Wind Exposure / Low Flood',
            default_hazard: 'Low'
          },
          'Purok 7': {
            name: 'Purok 7 - Industrial Highway Rim',
            subzone: 'Perimeter & Highway Access',
            leader: 'Kgd. Danilo Mercado (Transportation)',
            evacuation_center: 'Highway Terminal Pavilion',
            hazard_profile: 'Vehicular Traffic / Drainage Focus',
            default_hazard: 'Medium'
          }
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
            purok: pName,
            name: meta.name,
            subzone: meta.subzone,
            leader: meta.leader,
            evacuation_center: meta.evacuation_center,
            hazard_profile: meta.hazard_profile,
            hazard_rating: hazardRating,
            residents: totalRes,
            households: totalHH,
            avg_family_size: avgSize,
            males,
            females,
            voters,
            seniors,
            pwd,
            solo_parents: soloParents,
            four_ps: fourPs,
            indigents,
            vulnerability_score: vulnScore,
            high_risk_hh: highRiskHH
          };
        });

        // Update KPI telemetry
        updateTelemetryLadder(residents.length);

        // Render Map Choropleth & Legend
        renderMapChoropleth();

        // Render Print Table
        renderPrintTable();
      } catch (err) {
        console.error('Failed to load geo profiling data:', err);
        Toast.error('Could not load spatial demographics.');
      }
    }

    // Update Telemetry Ladder
    function updateTelemetryLadder(totalPop) {
      if (purokData.length === 0) return;

      // 1. Most Populated
      const sortedByPop = [...purokData].sort((a, b) => b.residents - a.residents);
      const topPop = sortedByPop[0];
      const popPct = totalPop > 0 ? Math.round((topPop.residents / totalPop) * 100) : 0;
      document.getElementById('kpi-most-populated').textContent = topPop.purok;
      document.getElementById('kpi-most-populated-sub').textContent = `${topPop.residents} residents (${popPct}% of total)`;

      // 2. Highest Vulnerability
      const sortedByVuln = [...purokData].sort((a, b) => b.vulnerability_score - a.vulnerability_score);
      const topVuln = sortedByVuln[0];
      document.getElementById('kpi-highest-vuln').textContent = topVuln.purok;
      document.getElementById('kpi-highest-vuln-sub').textContent = `${topVuln.vulnerability_score} priority assisted sectors`;

      // 3. Critical Hazard Dwellings
      const totalHighHazard = purokData.reduce((acc, p) => acc + (p.high_risk_hh || 0), 0);
      document.getElementById('kpi-hazard-count').textContent = totalHighHazard;
    }

    // Render Map Choropleth Colors based on activeLayer
    function renderMapChoropleth() {
      const maxPop = Math.max(...purokData.map(p => p.residents), 1);
      const maxVuln = Math.max(...purokData.map(p => p.vulnerability_score), 1);
      const maxHH = Math.max(...purokData.map(p => p.households), 1);

      purokData.forEach(p => {
        const poly = document.getElementById(`poly-${p.purok}`);
        const badge = document.getElementById(`badge-${p.purok}`);
        if (!poly) return;

        let fillColor = '#e4e4e7';
        let subText = p.subzone;

        if (activeLayer === 'population') {
          const ratio = p.residents / maxPop;
          fillColor = getBlueDensityColor(ratio);
          subText = `${p.residents} Residents`;
        } else if (activeLayer === 'hazard') {
          if (p.hazard_rating === 'High') {
            fillColor = 'rgba(239, 68, 68, 0.65)';
            subText = 'HIGH FLOOD RISK';
          } else if (p.hazard_rating === 'Medium') {
            fillColor = 'rgba(245, 158, 11, 0.65)';
            subText = 'MODERATE WATCH';
          } else {
            fillColor = 'rgba(16, 185, 129, 0.6)';
            subText = 'SAFE LOW RISK';
          }
        } else if (activeLayer === 'vulnerability') {
          const ratio = p.vulnerability_score / maxVuln;
          fillColor = getAmberDensityColor(ratio);
          subText = `Vuln Index: ${p.vulnerability_score}`;
        } else if (activeLayer === 'households') {
          const ratio = p.households / maxHH;
          fillColor = getPurpleDensityColor(ratio);
          subText = `${p.households} Households`;
        }

        poly.style.fill = fillColor;
        if (badge) badge.textContent = subText;
      });

      renderLegend();
    }

    // Color Scales
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

    // Render Dynamic Legend
    function renderLegend() {
      const legendTitle = document.getElementById('gis-legend-title');
      const ramp = document.getElementById('gis-legend-ramp');

      if (activeLayer === 'population') {
        legendTitle.textContent = 'Population Density Concentration:';
        ramp.innerHTML = `
          <span>Low</span>
          <div class="legend-swatch" style="background: rgba(219, 234, 254, 0.75);"></div>
          <div class="legend-swatch" style="background: rgba(96, 165, 250, 0.65);"></div>
          <div class="legend-swatch" style="background: rgba(37, 99, 235, 0.75);"></div>
          <div class="legend-swatch" style="background: rgba(30, 58, 138, 0.85);"></div>
          <span>High Density</span>
        `;
      } else if (activeLayer === 'hazard') {
        legendTitle.textContent = 'Disaster Hazard Safety Zones:';
        ramp.innerHTML = `
          <div style="display: flex; align-items: center; gap: 4px;"><div class="legend-swatch" style="background: #10b981;"></div> Safe (Low)</div>
          <div style="display: flex; align-items: center; gap: 4px; margin-left: 8px;"><div class="legend-swatch" style="background: #f59e0b;"></div> Watch (Moderate)</div>
          <div style="display: flex; align-items: center; gap: 4px; margin-left: 8px;"><div class="legend-swatch" style="background: #ef4444;"></div> Floodway / Critical</div>
        `;
      } else if (activeLayer === 'vulnerability') {
        legendTitle.textContent = 'Social Vulnerability (Seniors, PWD, 4Ps):';
        ramp.innerHTML = `
          <span>Low</span>
          <div class="legend-swatch" style="background: rgba(254, 243, 199, 0.75);"></div>
          <div class="legend-swatch" style="background: rgba(251, 191, 36, 0.65);"></div>
          <div class="legend-swatch" style="background: rgba(217, 119, 6, 0.75);"></div>
          <div class="legend-swatch" style="background: rgba(180, 83, 9, 0.85);"></div>
          <span>High Priority</span>
        `;
      } else if (activeLayer === 'households') {
        legendTitle.textContent = 'Household Density & Congestion:';
        ramp.innerHTML = `
          <span>Sparse</span>
          <div class="legend-swatch" style="background: rgba(237, 233, 254, 0.75);"></div>
          <div class="legend-swatch" style="background: rgba(196, 181, 253, 0.65);"></div>
          <div class="legend-swatch" style="background: rgba(139, 92, 246, 0.75);"></div>
          <div class="legend-swatch" style="background: rgba(109, 40, 217, 0.85);"></div>
          <span>Congested</span>
        `;
      }
    }

    // Select Purok on Map & Deep-Dive Panel
    function selectPurok(pName) {
      const p = purokData.find(item => item.purok.toLowerCase() === pName.toLowerCase());
      if (!p) return;

      selectedPurok = p.purok;

      // Update Polygon Selected State
      document.querySelectorAll('.purok-polygon').forEach(poly => {
        poly.classList.remove('selected');
      });
      const selectedPoly = document.getElementById(`poly-${p.purok}`);
      if (selectedPoly) selectedPoly.classList.add('selected');

      // Update Jump Select
      document.getElementById('select-purok-jump').value = p.purok;

      // Update Detail Panel
      document.getElementById('detail-subzone').textContent = p.subzone.toUpperCase();
      document.getElementById('detail-purok-name').textContent = p.name;

      const hazardBadge = document.getElementById('detail-hazard-badge');
      if (p.hazard_rating === 'High') {
        hazardBadge.className = 'badge-rose';
        hazardBadge.textContent = 'High Flood / Hazard';
      } else if (p.hazard_rating === 'Medium') {
        hazardBadge.className = 'badge-amber';
        hazardBadge.textContent = 'Moderate Watch';
      } else {
        hazardBadge.className = 'badge-emerald';
        hazardBadge.textContent = 'Safe Zone';
      }

      document.getElementById('detail-leader-name').textContent = p.leader;
      document.getElementById('detail-resident-count').textContent = p.residents;
      document.getElementById('detail-gender-split').textContent = `${p.males} Males • ${p.females} Females`;
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

      // Update Drilldown Links
      const ext = window.location.pathname.endsWith('.php') ? '.php' : '.html';
      const resBtn = document.getElementById('btn-drilldown-residents');
      resBtn.href = `residents${ext}?purok=${encodeURIComponent(p.purok)}`;
      resBtn.textContent = `View Residents in ${p.purok} &rarr;`;

      const hhBtn = document.getElementById('btn-drilldown-households');
      hhBtn.href = `households${ext}?purok=${encodeURIComponent(p.purok)}`;
      hhBtn.textContent = `View Households in ${p.purok} &rarr;`;
    }

    // Bind Event Listeners
    function bindGisEventListeners() {
      // Layer Switcher
      document.querySelectorAll('.gis-layer-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          document.querySelectorAll('.gis-layer-btn').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          activeLayer = btn.getAttribute('data-layer');
          renderMapChoropleth();
        });
      });

      // Jump Select
      document.getElementById('select-purok-jump').addEventListener('change', (e) => {
        if (e.target.value) {
          selectPurok(e.target.value);
        }
      });

      // Landmark Overlay Toggle
      document.getElementById('toggle-landmarks').addEventListener('change', (e) => {
        const layer = document.getElementById('gis-landmarks-layer');
        if (layer) layer.style.display = e.target.checked ? 'block' : 'none';
      });

      // Polygon Click & Hover Tooltip
      const tooltip = document.getElementById('gis-tooltip');
      const stage = document.getElementById('gis-viewport');

      document.querySelectorAll('.purok-polygon').forEach(poly => {
        const pName = poly.getAttribute('data-purok');

        poly.addEventListener('click', () => {
          selectPurok(pName);
        });

        poly.addEventListener('mousemove', (e) => {
          const p = purokData.find(item => item.purok === pName);
          if (!p) return;

          const rect = stage.getBoundingClientRect();
          const x = e.clientX - rect.left + 12;
          const y = e.clientY - rect.top + 12;

          tooltip.style.left = `${x}px`;
          tooltip.style.top = `${y}px`;
          tooltip.style.display = 'block';
          tooltip.innerHTML = `
            <strong>${p.name}</strong><br>
            Residents: <strong>${p.residents}</strong> &bull; Households: <strong>${p.households}</strong><br>
            Hazard Status: <strong>${p.hazard_rating} Risk</strong>
          `;
        });

        poly.addEventListener('mouseleave', () => {
          tooltip.style.display = 'none';
        });
      });

      // Landmark Pin Hover
      document.querySelectorAll('.gis-landmark-group').forEach(group => {
        const landmarkName = group.getAttribute('data-name');
        group.addEventListener('mousemove', (e) => {
          const rect = stage.getBoundingClientRect();
          const x = e.clientX - rect.left + 14;
          const y = e.clientY - rect.top + 14;

          tooltip.style.left = `${x}px`;
          tooltip.style.top = `${y}px`;
          tooltip.style.display = 'block';
          tooltip.innerHTML = `<strong>Official Landmark:</strong><br>${landmarkName}`;
        });

        group.addEventListener('mouseleave', () => {
          tooltip.style.display = 'none';
        });
      });

      // Zoom Controls
      const mapSvg = document.getElementById('gis-map-svg');
      document.getElementById('btn-zoom-in').addEventListener('click', () => {
        zoomLevel = Math.min(zoomLevel + 0.25, 2.5);
        mapSvg.style.transform = `scale(${zoomLevel})`;
      });

      document.getElementById('btn-zoom-out').addEventListener('click', () => {
        zoomLevel = Math.max(zoomLevel - 0.25, 0.75);
        mapSvg.style.transform = `scale(${zoomLevel})`;
      });

      document.getElementById('btn-zoom-reset').addEventListener('click', () => {
        zoomLevel = 1.0;
        mapSvg.style.transform = 'scale(1.0)';
      });

      // Print Report
      document.getElementById('btn-print-geo-report').addEventListener('click', () => {
        window.print();
      });

      // Export CSV
      document.getElementById('btn-export-gis-csv').addEventListener('click', exportPurokCsv);
    }

    // Export Purok Demographics CSV
    function exportPurokCsv() {
      if (purokData.length === 0) return;

      const headers = ['Purok', 'Subzone', 'Zone Leader', 'Residents', 'Households', 'Avg Family Size', 'Males', 'Females', 'Voters', 'Seniors', 'PWD', 'Solo Parents', '4Ps Beneficiaries', 'Indigents', 'Hazard Rating', 'Evacuation Center'];
      const rows = purokData.map(p => [
        `"${p.purok}"`,
        `"${p.subzone}"`,
        `"${p.leader}"`,
        p.residents,
        p.households,
        p.avg_family_size,
        p.males,
        p.females,
        p.voters,
        p.seniors,
        p.pwd,
        p.solo_parents,
        p.four_ps,
        p.indigents,
        `"${p.hazard_rating}"`,
        `"${p.evacuation_center}"`
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

    // Render Print Table
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
  </script>
</body>
</html>
