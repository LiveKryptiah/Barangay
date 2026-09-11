/**
 * Barangay Management System - App Shell Sidebar Component
 * Features: Compact sidebar width, refined typography, collapse/expand toggle,
 * localStorage state persistence, keyboard shortcut (Ctrl+B), and mobile drawer support.
 * Updated for PHP & MySQL Environment with Dynamic Route Extension.
 */

class AppSidebar {
  static async render(activePage = 'dashboard') {
    const sidebarMount = document.getElementById('sidebar-mount');
    if (!sidebarMount) return;

    // Detect environment extension (.php or .html)
    const ext = window.location.pathname.endsWith('.html') ? '.html' : '.php';

    let user = null;
    if (window.barangayAuth) {
      user = window.barangayAuth.getUser();
      if (!user) {
        user = await window.barangayAuth.checkSession();
      }
    }
    if (!user && window.authService) {
      const sess = await window.authService.getCurrentSession();
      if (sess) user = sess.user;
    }
    if (!user) {
      const stored = sessionStorage.getItem('barangay_user');
      if (stored) {
        try { user = JSON.parse(stored); } catch (e) {}
      }
    }

    // Default fallback if session check is in-flight
    if (!user) {
      user = { fullName: 'Barangay Staff', role: 'Staff', position: 'Barangay Staff' };
    }

    const userName = user.full_name || user.fullName || user.username || 'Staff Member';
    const userRole = user.position || user.role || 'Staff';

    // Check saved collapse state
    const isCollapsed = localStorage.getItem('bms_sidebar_collapsed') === 'true';

    // Initials for avatar squircle
    const initials = userName
      ? userName.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
      : 'AD';

    sidebarMount.innerHTML = `
      <!-- Backdrop for mobile drawer -->
      <div id="sidebar-backdrop" class="sidebar-backdrop"></div>

      <!-- Main Sidebar Element -->
      <aside id="app-sidebar-element" class="app-sidebar ${isCollapsed ? 'collapsed' : ''}">
        <div>
          <!-- Sidebar Brand Header & Toggle Button -->
          <div class="sidebar-header">
            <a href="dashboard${ext}" class="sidebar-brand-lockup" title="BarangayOS">
              <div class="nav-brand-icon" style="width: 32px; height: 32px; border-radius: 30%; font-size: 0.9375rem; flex-shrink: 0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                  <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
              </div>
              <div class="sidebar-brand-text">
                <div class="typography-heading-4" style="font-size: 0.9375rem; line-height: 1.1;">BarangayOS.</div>
                <div class="typography-caption" style="font-size: 0.625rem;">Executive Portal</div>
              </div>
            </a>

            <!-- Actions: Sidebar Collapse (Ctrl+B) -->
            <div class="sidebar-header-actions" style="margin-left: auto;">
              <button id="sidebar-toggle-btn" class="sidebar-toggle-btn" title="Toggle Sidebar (Ctrl+B)" aria-label="Toggle Sidebar">
                <svg id="sidebar-toggle-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  ${isCollapsed 
                    ? '<polyline points="9 18 15 12 9 6"/>' 
                    : '<polyline points="15 18 9 12 15 6"/>'}
                </svg>
              </button>
            </div>
          </div>

          <!-- Navigation Rows -->
          <div class="sidebar-nav-section-label">Core Modules</div>
          <ul class="sidebar-nav">
            <li>
              <a href="dashboard${ext}" class="ex-app-shell-row ${activePage === 'dashboard' ? 'active' : ''}" title="Dashboard">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <rect width="7" height="9" x="3" y="3" rx="1"/>
                  <rect width="7" height="5" x="14" y="3" rx="1"/>
                  <rect width="7" height="9" x="14" y="12" rx="1"/>
                  <rect width="7" height="5" x="3" y="16" rx="1"/>
                </svg>
                <span>Dashboard</span>
              </a>
            </li>

            <li>
              <a href="residents${ext}" class="ex-app-shell-row ${activePage === 'residents' ? 'active' : ''}" title="Residents">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                  <circle cx="9" cy="7" r="4"/>
                  <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>Residents</span>
              </a>
            </li>

            <li>
              <a href="resident-id${ext}" class="ex-app-shell-row ${activePage === 'resident-id' ? 'active' : ''}" title="PVC Resident ID Studio">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <rect width="20" height="14" x="2" y="5" rx="2"/>
                  <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
                <span>Resident IDs</span>
                <span class="badge-neutral" style="font-size: 0.5625rem; padding: 1px 6px; margin-left: auto;">PVC</span>
              </a>
            </li>

            <li>
              <a href="households${ext}" class="ex-app-shell-row ${activePage === 'households' ? 'active' : ''}" title="Households & Families">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                  <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <span>Households</span>
              </a>
            </li>

            <li>
              <a href="geo-profiling${ext}" class="ex-app-shell-row ${activePage === 'geo-profiling' ? 'active' : ''}" title="Purok Geo-Profiling & Density Heatmap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/>
                  <line x1="8" y1="2" x2="8" y2="18"/>
                  <line x1="16" y1="6" x2="16" y2="22"/>
                </svg>
                <span>Geo-Profiling</span>
                <span class="badge-neutral" style="font-size: 0.5625rem; padding: 1px 6px; margin-left: auto;">GIS</span>
              </a>
            </li>

            <li>
              <a href="certificates${ext}" class="ex-app-shell-row ${activePage === 'certificates' ? 'active' : ''}" title="Clearances">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                  <polyline points="10 9 9 9 8 9"/>
                </svg>
                <span>Clearances</span>
              </a>
            </li>

            <li>
              <a href="blotter${ext}" class="ex-app-shell-row ${activePage === 'blotter' ? 'active' : ''}" title="Blotter Records">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <span>Blotter Records</span>
              </a>
            </li>

            <li>
              <a href="lupon${ext}" class="ex-app-shell-row ${activePage === 'lupon' ? 'active' : ''}" title="Lupong Tagapamayapa &amp; Katarungang Pambarangay Studio (RA 7160)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <path d="M12 3v18"/>
                  <path d="m3 7 9-4 9 4"/>
                  <path d="M6 7v6a6 6 0 0 0 12 0V7"/>
                </svg>
                <span>Lupon &amp; KP Conciliation</span>
                <span class="badge-neutral" style="font-size: 0.5625rem; padding: 1px 6px; margin-left: auto;">LUPON</span>
              </a>
            </li>

            <li>
              <a href="incidents${ext}" class="ex-app-shell-row ${activePage === 'incidents' ? 'active' : ''}" title="Emergency Dispatch & Tanod Patrol">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                </svg>
                <span>Incident Dispatch</span>
              </a>
            </li>

            <li>
              <a href="drrm${ext}" class="ex-app-shell-row ${activePage === 'drrm' ? 'active' : ''}" title="Disaster Risk Reduction &amp; Management (RA 10121)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                  <path d="M12 8v4"/><path d="M12 16h.01"/>
                </svg>
                <span>Disaster &amp; DRRM</span>
                <span class="badge-neutral" style="font-size: 0.5625rem; padding: 1px 6px; margin-left: auto;">BDRRMC</span>
              </a>
            </li>

            <li>
              <a href="notifications${ext}" class="ex-app-shell-row ${activePage === 'notifications' ? 'active' : ''}" title="SMS & Notification Dispatch">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span>Notifications</span>
                <span class="badge-neutral" style="font-size: 0.5625rem; padding: 1px 6px; margin-left: auto;">SMS</span>
              </a>
            </li>

            <li>
              <a href="health${ext}" class="ex-app-shell-row ${activePage === 'health' ? 'active' : ''}" title="Health Station & Nutrition Information System">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
                <span>Health Station</span>
                <span class="badge-neutral" style="font-size: 0.5625rem; padding: 1px 6px; margin-left: auto;">BHW</span>
              </a>
            </li>

            <li>
              <a href="procurement${ext}" class="ex-app-shell-row ${activePage === 'procurement' ? 'active' : ''}" title="Bids & Awards Committee (BAC) & Procurement Management">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                  <line x1="3" y1="6" x2="21" y2="6"/>
                  <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                <span>Bids &amp; Awards</span>
                <span class="badge-neutral" style="font-size: 0.5625rem; padding: 1px 6px; margin-left: auto;">BAC</span>
              </a>
            </li>

            <li>
              <a href="budget${ext}" class="ex-app-shell-row ${activePage === 'budget' ? 'active' : ''}" title="Budget &amp; Financial Management (COA Compliant)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                <span>Budget &amp; Finance</span>
                <span class="badge-neutral" style="font-size: 0.5625rem; padding: 1px 6px; margin-left: auto;">COA</span>
              </a>
            </li>

            <li>
              <a href="legislation${ext}" class="ex-app-shell-row ${activePage === 'legislation' ? 'active' : ''}" title="Sangguniang Barangay Legislative Tracking & Ordinances">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                  <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                  <line x1="9" y1="7" x2="15" y2="7"/>
                  <line x1="9" y1="11" x2="13" y2="11"/>
                </svg>
                <span>Legislation</span>
                <span class="badge-neutral" style="font-size: 0.5625rem; padding: 1px 6px; margin-left: auto;">SANGGUNIAN</span>
              </a>
            </li>

            <li>
              <a href="officials${ext}" class="ex-app-shell-row ${activePage === 'officials' ? 'active' : ''}" title="Officials & Staff">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <circle cx="12" cy="8" r="6"/>
                  <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>
                </svg>
                <span>Officials & Staff</span>
              </a>
            </li>

            <li>
              <a href="reports${ext}" class="ex-app-shell-row ${activePage === 'reports' ? 'active' : ''}" title="Reports & Analytics">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <line x1="18" y1="20" x2="18" y2="10"/>
                  <line x1="12" y1="20" x2="12" y2="4"/>
                  <line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
                <span>Reports &amp; Analytics</span>
              </a>
            </li>

            <li>
              <a href="settings${ext}" class="ex-app-shell-row ${activePage === 'settings' ? 'active' : ''}" title="System Settings">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <circle cx="12" cy="12" r="3"/>
                  <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <span>System Settings</span>
              </a>
            </li>
          </ul>

          <div class="sidebar-nav-section-label" style="margin-top: var(--spacing-sm);">Public Services</div>
          <ul class="sidebar-nav">
            <li>
              <a href="portal${ext}" target="_blank" class="ex-app-shell-row" title="Open Citizen Public Portal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <circle cx="12" cy="12" r="10"/>
                  <line x1="2" y1="12" x2="22" y2="12"/>
                  <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </svg>
                <span>Citizen Portal</span>
                <span class="badge-blue" style="font-size: 0.5625rem; padding: 1px 6px; margin-left: auto;">Public &nearr;</span>
              </a>
            </li>

            <li>
              <a href="verify${ext}" target="_blank" class="ex-app-shell-row ${activePage === 'verify' ? 'active' : ''}" title="Verify Official Document">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                  <path d="M9 12l2 2 4-4"/>
                </svg>
                <span>Verify Document</span>
                <span class="badge-neutral" style="font-size: 0.5625rem; padding: 1px 6px; margin-left: auto;">QR &nearr;</span>
              </a>
            </li>
          </ul>

          <div class="sidebar-telemetry-box" style="margin-top: var(--spacing-lg);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px;">
              <span style="font-size: 0.6875rem; font-weight: 600; color: var(--color-ink);">Database</span>
              <span class="badge-neutral" style="padding: 1px 6px; font-size: 0.625rem;">Live</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;" id="db-status-pill">
              <div style="width: 5px; height: 5px; border-radius: 50%; background: #10b981;"></div>
              <span style="font-size: 0.625rem; color: var(--color-text-muted);">MySQL Connected</span>
            </div>
          </div>
        </div>

        <!-- Sidebar User Footer Lockup -->
        <div class="sidebar-footer">
          <div class="sidebar-user-card" title="${userName} (${userRole})">
            <div class="sidebar-user-avatar">${initials}</div>
            <div class="sidebar-user-info">
              <span class="sidebar-user-name">${userName}</span>
              <span class="sidebar-user-role">${userRole}</span>
            </div>
          </div>

          <button id="sidebar-logout-btn" class="button-outline sidebar-logout-btn" title="Sign out">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span class="sidebar-logout-text">Sign out</span>
          </button>
        </div>
      </aside>
    `;

    // Hook up Open / Close Collapse Toggle
    const sidebarEl = document.getElementById('app-sidebar-element');
    const toggleBtn = document.getElementById('sidebar-toggle-btn');
    const toggleIcon = document.getElementById('sidebar-toggle-icon');

    function toggleSidebar() {
      const nowCollapsed = sidebarEl.classList.toggle('collapsed');
      localStorage.setItem('bms_sidebar_collapsed', nowCollapsed ? 'true' : 'false');
      if (toggleIcon) {
        toggleIcon.innerHTML = nowCollapsed
          ? '<polyline points="9 18 15 12 9 6"/>'
          : '<polyline points="15 18 9 12 15 6"/>';
      }
    }

    if (toggleBtn) {
      toggleBtn.addEventListener('click', toggleSidebar);
    }

    // Keyboard shortcut (Ctrl+B or Cmd+B) to toggle sidebar
    window.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
        e.preventDefault();
        toggleSidebar();
      }
    });

    // Render desktop App Topbar with Dark / Light Mode Toggle on the Top Right
    let topbarMount = document.getElementById('app-topbar-mount') || document.getElementById('topbar-mount');
    if (!topbarMount) {
      const appMain = document.querySelector('.app-main');
      if (appMain) {
        topbarMount = document.createElement('div');
        topbarMount.id = 'app-topbar-mount';
        const mobileMount = document.getElementById('mobile-header-mount');
        if (mobileMount) {
          mobileMount.after(topbarMount);
        } else {
          appMain.prepend(topbarMount);
        }
      }
    }

    const pageLabels = {
      dashboard: 'Executive Dashboard',
      residents: 'Resident Registry',
      'resident-id': 'PVC Resident ID Studio',
      households: 'Household Profiling & Family Tree',
      'geo-profiling': 'Purok Geo-Profiling & Heatmap',
      certificates: 'Clearances & Certifications',
      blotter: 'Peace & Order Blotter',
      lupon: 'Lupong Tagapamayapa & KP Studio',
      incidents: 'Incident Dispatch & Patrol',
      drrm: 'Disaster Risk Reduction & Management',
      notifications: 'SMS & Notification Dispatch',
      health: 'Barangay Health Station & Nutrition Hub',
      procurement: 'Bids, Awards & Fiscal Hub',
      budget: 'Budget & Financial Management',
      legislation: 'Sangguniang Legislation & Ordinances',
      officials: 'Officials & Staff Directory',
      reports: 'Executive Reports & Analytics',
      settings: 'System Settings & Backup'
    };
    const pageLabel = pageLabels[activePage] || 'Portal';

    if (topbarMount) {
      topbarMount.innerHTML = `
        <header class="app-topbar" aria-label="Workspace Header">
          <div class="app-topbar-left">
            <nav class="topbar-breadcrumb" aria-label="Breadcrumb">
              <a href="dashboard${ext}" class="topbar-breadcrumb-item">BarangayOS</a>
              <span class="topbar-breadcrumb-sep">/</span>
              <span class="topbar-breadcrumb-item current">${pageLabel}</span>
            </nav>
          </div>

          <div class="app-topbar-right">
            <div class="topbar-db-badge" title="MySQL Database Active">
              <div class="status-dot" style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></div>
              <span>MySQL Live</span>
            </div>

            <!-- Dark / Light Mode Toggle Button (Top Right) -->
            <button id="theme-toggle-btn-topbar" class="theme-toggle-btn" title="Toggle Theme" aria-label="Toggle Theme">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/>
              </svg>
            </button>

            <div class="topbar-user-pill" title="${userName} (${userRole})">
              <div class="topbar-user-avatar">${initials}</div>
              <span class="topbar-user-name">${userName}</span>
            </div>
          </div>
        </header>
      `;
    }

    // Render mobile eGov Admin top bar if container exists
    const mobileMount = document.getElementById('mobile-header-mount');
    if (mobileMount) {
      mobileMount.innerHTML = `
        <header class="egov-appbar">
          <div class="egov-appbar-left">
            <div class="nav-brand-icon" style="width: 32px; height: 32px; border-radius: 30%; font-size: 0.9375rem; flex-shrink: 0;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
            </div>
            <div class="egov-appbar-titles">
              <span class="egov-appbar-main" style="font-size: 0.9375rem; font-weight: 700; color: var(--color-ink);">BarangayOS.</span>
              <span class="egov-appbar-sub" style="font-size: 0.6875rem; font-weight: 500; color: var(--color-text-muted); text-transform: none; letter-spacing: 0;">Staff &bull; ${pageLabel}</span>
            </div>
          </div>
          <div class="egov-appbar-actions">
            <button id="theme-toggle-btn-mobile" class="theme-toggle-btn" title="Toggle Theme" aria-label="Toggle Theme" style="width: 34px; height: 34px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/></svg>
            </button>
            <div class="topbar-user-avatar" style="width: 32px; height: 32px; font-size: 0.75rem; border-radius: 30%;" title="${userName}">${initials}</div>
          </div>
        </header>
      `;

      const mobileThemeBtn = document.getElementById('theme-toggle-btn-mobile');
      if (mobileThemeBtn && window.ThemeManager) {
        mobileThemeBtn.addEventListener('click', () => {
          window.ThemeManager.toggle();
        });
      }
    }

    // Render Admin Fixed Bottom Navigation Bar for Mobile Viewports
    let adminBottomMount = document.getElementById('admin-bottom-nav-mount');
    if (!adminBottomMount) {
      adminBottomMount = document.createElement('div');
      adminBottomMount.id = 'admin-bottom-nav-mount';
      document.body.appendChild(adminBottomMount);
    }

    adminBottomMount.innerHTML = `
      <nav class="egov-bottom-nav" aria-label="Admin Mobile Navigation">
        <div class="egov-bottom-nav-inner">
          <a href="dashboard${ext}" class="egov-nav-item ${activePage === 'dashboard' ? 'active' : ''}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
            <span>Dashboard</span>
            ${activePage === 'dashboard' ? '<div class="egov-nav-pill-active"></div>' : ''}
          </a>

          <a href="residents${ext}" class="egov-nav-item ${activePage === 'residents' ? 'active' : ''}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Residents</span>
            ${activePage === 'residents' ? '<div class="egov-nav-pill-active"></div>' : ''}
          </a>

          <a href="certificates${ext}" class="egov-nav-item ${activePage === 'certificates' ? 'active' : ''}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <span>Clearances</span>
            ${activePage === 'certificates' ? '<div class="egov-nav-pill-active"></div>' : ''}
          </a>

          <a href="incidents${ext}" class="egov-nav-item ${activePage === 'incidents' ? 'active' : ''}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            <span>Incidents</span>
            ${activePage === 'incidents' ? '<div class="egov-nav-pill-active"></div>' : ''}
          </a>

          <button type="button" id="admin-mobile-more-btn" class="egov-nav-item ${['lupon','legislation','notifications','health','procurement','budget','drrm','geo-profiling','resident-id','households','blotter','officials','reports','settings'].includes(activePage) ? 'active' : ''}" aria-label="Open Full Menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            <span>More</span>
          </button>
        </div>
      </nav>
    `;

    const adminMoreBtn = document.getElementById('admin-mobile-more-btn');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (adminMoreBtn && sidebarEl && backdrop) {
      adminMoreBtn.addEventListener('click', () => {
        sidebarEl.classList.toggle('open');
        backdrop.classList.toggle('active');
      });

      backdrop.addEventListener('click', () => {
        sidebarEl.classList.remove('open');
        backdrop.classList.remove('active');
      });
    }

    if (window.ThemeManager) {
      window.ThemeManager.updateAllIcons();
    }

    // Bind logout button
    const logoutBtn = document.getElementById('sidebar-logout-btn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', async () => {
        if (window.barangayAuth) {
          await window.barangayAuth.logout();
        } else if (window.authService) {
          await window.authService.logout();
          window.location.href = 'login' + ext;
        } else {
          window.location.href = 'login' + ext;
        }
      });
    }
  }
}

window.AppSidebar = AppSidebar;
