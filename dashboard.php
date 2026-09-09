<?php
/**
 * Barangay Management System (BarangayOS)
 * Executive Dashboard
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

require_auth('login.php');
$currentUser = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard &bull; Barangay Management System</title>
  <link rel="stylesheet" href="css/design-system.css">
  <script src="js/components/theme.js"></script>
  <style>
    .dashboard-hero {
      padding: var(--spacing-xs) 0 var(--spacing-sm);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: var(--spacing-sm);
      margin-bottom: var(--spacing-md);
    }

    @media (max-width: 1024px) {
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 640px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }

    .stat-card {
      background-color: var(--color-canvas);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: var(--spacing-sm) var(--spacing-md);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 96px;
      transition: border-color 0.15s ease;
    }

    .stat-card:hover {
      border-color: var(--color-ink);
    }

    .stat-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .stat-number {
      font-size: 1.625rem;
      font-weight: var(--font-weight-heading);
      line-height: 1.1;
      color: var(--color-ink);
      margin-top: 4px;
      margin-bottom: 2px;
    }

    .quick-actions-bar {
      display: flex;
      flex-wrap: wrap;
      gap: var(--spacing-xs);
      margin-bottom: var(--spacing-md);
    }

    .module-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: var(--spacing-md);
      margin-bottom: var(--spacing-md);
    }

    .module-grid .empty-state-card {
      padding: var(--spacing-lg) var(--spacing-md);
    }

    @media (max-width: 768px) {
      .module-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <div class="app-shell">
    <!-- Sidebar Mount -->
    <div id="sidebar-mount"></div>

    <!-- Main App Workspace -->
    <div class="app-main">
      <div id="mobile-header-mount"></div>
      <div id="app-topbar-mount"></div>

      <main class="app-content">

        <!-- Executive Hero Header -->
        <section class="dashboard-hero">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--spacing-md);">
            <div>
              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <span class="badge-neutral" id="current-user-role"><?= htmlspecialchars(strtoupper($currentUser['role'])) ?></span>
                <span class="typography-caption" style="color: var(--color-text-muted);">EXECUTIVE CONSOLE</span>
              </div>
              <h1 class="typography-heading-2">Operations Overview.</h1>
              <p class="typography-body-lg">
                Real-time community records, population telemetry, and administrative actions backed by MySQL.
              </p>
            </div>
            <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
              <div class="card-soft" style="padding: 10px 18px; border-radius: var(--rounded-full); display: flex; align-items: center; gap: 8px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></div>
                <span class="typography-caption" style="color: var(--color-ink); font-weight: 600;" id="user-display-name">
                  <?= htmlspecialchars($currentUser['full_name']) ?> (<?= htmlspecialchars($currentUser['position']) ?>)
                </span>
              </div>
            </div>
          </div>
        </section>

        <!-- Real-time Stats Ladder -->
        <section>
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">TOTAL RESIDENTS</span>
                <span class="badge-neutral">MySQL Live</span>
              </div>
              <div class="stat-number" id="stat-residents">0</div>
              <div class="typography-caption" id="stat-residents-sub">0 registered in Purok rosters</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">CLEARANCES ISSUED</span>
                <span class="badge-neutral">Certificates</span>
              </div>
              <div class="stat-number" id="stat-clearances">0</div>
              <div class="typography-caption">0 verified digital tracking codes</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">ACTIVE BLOTTERS</span>
                <span class="badge-neutral">Peace &amp; Order</span>
              </div>
              <div class="stat-number" id="stat-blotter">0</div>
              <div class="typography-caption" id="stat-blotter-sub">0 cases • 0 incidents</div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <span class="typography-label" style="color: var(--color-text-muted);">STAFF &amp; OFFICIALS</span>
                <span class="badge-popular">Bcrypt Secured</span>
              </div>
              <div class="stat-number" id="stat-users">1</div>
              <div class="typography-caption" id="stat-users-sub">Active in BarangayOS Directory</div>
            </div>
          </div>
        </section>

        <!-- Quick Action Stadium Pills -->
        <section>
          <div style="margin-bottom: var(--spacing-sm);">
            <h3 class="typography-title">Administrative Actions</h3>
          </div>
          <div class="quick-actions-bar">
            <a href="residents.php" class="button-primary" id="btn-quick-resident">
              + Record Resident
            </a>
            <a href="certificates.php" class="button-outline" id="btn-quick-clearance">
              + Issue Clearance
            </a>
            <a href="blotter.php" class="button-outline" id="btn-quick-blotter">
              + File Blotter Report
            </a>
            <a href="incidents.php" class="button-outline" id="btn-quick-incident" style="color: #ef4444;">
              + Emergency Dispatch
            </a>
            <a href="officials.php" class="button-outline" id="btn-quick-officials">
              Officials &amp; Staff
            </a>
            <a href="reports.php" class="button-outline" id="btn-quick-reports">
              Executive Reports
            </a>
            <a href="settings.php" class="button-outline" id="btn-quick-settings">
              Settings &amp; Backup
            </a>
            <button class="button-pill-soft" id="btn-quick-audit" onclick="showAuditModal();">
              View Audit Logs
            </button>
          </div>
        </section>

        <!-- Navigation Cards -->
        <section class="module-grid">
          <div class="empty-state-card">
            <div class="nav-brand-icon" style="width: 48px; height: 48px; border-radius: 30%; font-size: 1.5rem;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
            </div>
            <div>
              <h4 class="typography-heading-4">Resident Directory.</h4>
              <p class="typography-body-sm mt-xs" style="max-width: 360px;">
                Manage community rosters, voter eligibility, senior citizens, and household profiling.
              </p>
            </div>
            <a href="residents.php" class="button-pill-soft mt-xs">
              Open Resident Rosters &rarr;
            </a>
          </div>

          <div class="empty-state-card">
            <div class="nav-brand-icon" style="width: 48px; height: 48px; border-radius: 30%; font-size: 1.5rem;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
              </svg>
            </div>
            <div>
              <h4 class="typography-heading-4">Clearances &amp; Permits.</h4>
              <p class="typography-body-sm mt-xs" style="max-width: 360px;">
                Official Barangay Clearances, Indigency Certificates, and Business Permits are cryptographically tracked with MySQL persistence.
              </p>
            </div>
            <a href="certificates.php" class="button-pill-soft mt-xs">
              Open Clearance System &rarr;
            </a>
          </div>
        </section>

      </main>
    </div>
  </div>

  <!-- Audit Logs Modal -->
  <dialog id="audit-modal" style="border: 1px solid var(--color-hairline); border-radius: var(--rounded-md); padding: var(--spacing-xl); width: 90%; max-width: 680px; margin: auto; background: var(--color-canvas);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-lg);">
      <h3 class="typography-heading-4">Security Audit Log.</h3>
      <button onclick="document.getElementById('audit-modal').close()" class="button-pill-soft" style="height: 32px; padding: 0 12px;">Close</button>
    </div>
    <div id="audit-logs-list" style="max-height: 340px; overflow-y: auto; display: flex; flex-direction: column; gap: var(--spacing-xs);">
      <p class="typography-body-sm">Loading security logs...</p>
    </div>
  </dialog>

  <!-- Scripts -->
  <script src="js/api.js"></script>
  <script src="js/components/toast.js"></script>
  <script src="js/components/sidebar.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', async () => {
      // 1. Mount Sidebar
      await AppSidebar.render('dashboard');

      // 2. Query Real Database Metrics
      async function refreshStats() {
        try {
          const residentCount = await window.barangayDB.count('residents');
          const hhCount = await window.barangayDB.count('households');
          const certCount = await window.barangayDB.count('certificates');
          const blotterCount = await window.barangayDB.count('blotter_cases');
          const incCount = await window.barangayDB.count('incidents');
          const officialCount = await window.barangayDB.count('officials');

          document.getElementById('stat-residents').textContent = residentCount;
          if (document.getElementById('stat-residents-sub')) {
            document.getElementById('stat-residents-sub').textContent = `${residentCount} residents • ${hhCount} households`;
          }
          document.getElementById('stat-clearances').textContent = certCount;
          document.getElementById('stat-blotter').textContent = blotterCount;
          if (document.getElementById('stat-blotter-sub')) {
            document.getElementById('stat-blotter-sub').textContent = `${blotterCount} cases • ${incCount} incidents`;
          }
          document.getElementById('stat-users').textContent = officialCount;
          if (document.getElementById('stat-users-sub')) {
            document.getElementById('stat-users-sub').textContent = `${officialCount} registered in directory`;
          }
        } catch (err) {
          console.warn('Telemetry fetch error:', err);
        }
      }

      await refreshStats();

      // 3. Audit Log Modal Function
      window.showAuditModal = async () => {
        const modal = document.getElementById('audit-modal');
        const list = document.getElementById('audit-logs-list');
        list.innerHTML = '<p class="typography-body-sm">Fetching audit entries from MySQL...</p>';
        modal.showModal();

        try {
          const logs = await window.barangayDB.getAll('audit_logs');
          if (!logs || logs.length === 0) {
            list.innerHTML = '<p class="typography-body-sm" style="color: var(--color-text-muted);">No security events recorded yet.</p>';
            return;
          }

          list.innerHTML = logs.map(log => `
            <div style="padding: 10px 14px; background-color: var(--color-canvas-soft); border-radius: var(--rounded-sm); display: flex; justify-content: space-between; align-items: center; gap: 12px;">
              <div>
                <span style="font-weight: 600; font-size: 0.8125rem;">${log.action}</span>
                <p style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 2px;">${log.details || ''}</p>
              </div>
              <span style="font-size: 0.75rem; color: var(--color-text-faint); white-space: nowrap;">
                ${log.created_at ? new Date(log.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : ''}
              </span>
            </div>
          `).join('');
        } catch (e) {
          list.innerHTML = `<p class="typography-body-sm" style="color: #ff4444;">Failed to load logs: ${e.message}</p>`;
        }
      };
    });
  </script>
</body>
</html>
