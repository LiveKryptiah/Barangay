<?php
/**
 * Barangay Management System (BarangayOS)
 * Sign In Portal
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

// Authentication disabled per user request: automatically redirect to dashboard
header('Location: dashboard.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="BarangayOS - Open-source Local Government Unit (LGU) administration and community management platform. Developed for administrative demonstration and educational use.">
  <title>Sign In &bull; Barangay Management System</title>
  <link rel="stylesheet" href="css/design-system.css">
  <script src="js/components/theme.js"></script>
  <style>
    .auth-page-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: var(--spacing-xl) var(--spacing-md) var(--spacing-section);
      min-height: calc(100vh - 160px);
    }

    .system-status-pill {
      display: inline-flex;
      align-items: center;
      gap: var(--spacing-xs);
      padding: 6px 16px;
      background-color: var(--color-canvas-soft);
      border-radius: var(--rounded-full);
      font-size: 0.8125rem;
      font-weight: var(--font-weight-title);
      color: var(--color-text-muted);
      margin-bottom: var(--spacing-xl);
    }

    .status-dot {
      width: 8px;
      height: 8px;
      background-color: #10b981;
      border-radius: 50%;
    }

    .setup-alert-card {
      background-color: var(--color-canvas-soft);
      border: 1px solid var(--color-hairline);
      border-radius: var(--rounded-sm);
      padding: var(--spacing-md);
      margin-bottom: var(--spacing-lg);
      display: flex;
      flex-direction: column;
      gap: var(--spacing-xs);
    }
  </style>
</head>
<body>

  <!-- Floating Nav Pill Mount -->
  <div id="navbar-mount"></div>

  <!-- Main Content Area -->
  <main class="auth-page-container">

    <div class="system-status-pill">
      <span class="status-dot"></span>
      <span>Database Engine: Relational MySQL (PHP PDO) Active</span>
    </div>

    <div class="auth-card" style="text-align: center; max-width: 500px;">
      <div class="mb-lg">
        <div class="nav-brand-icon" style="width: 48px; height: 48px; margin: 0 auto var(--spacing-sm); border-radius: var(--rounded-md);">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
        </div>
        <h1 class="typography-heading-3 mb-xs">BarangayOS</h1>
        <p class="typography-body-sm" style="color: var(--color-text-muted);">
          Open-Source Local Government Administration System (PHP &amp; MySQL)
        </p>
      </div>

      <div style="background-color: var(--color-canvas-soft); border-radius: var(--rounded-sm); padding: var(--spacing-md); margin-bottom: var(--spacing-lg); text-align: left;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
          <span class="typography-label" style="color: var(--color-ink); font-weight: 700;">DIRECT ACCESS ACTIVE</span>
          <span class="badge-emerald">Auto Authenticated</span>
        </div>
        <p class="typography-body-sm" style="color: var(--color-text-muted);">
          Authentication requirements are disabled. You have full administrative access to all modules, records, and telemetry.
        </p>
      </div>

      <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
        <a href="dashboard.php" class="button-primary w-full" style="height: 44px; display: flex; align-items: center; justify-content: center; gap: 8px;">
          <span>Enter Operations Console</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/>
            <polyline points="12 5 19 12 12 19"/>
          </svg>
        </a>
        <a href="portal.php" class="button-outline w-full" style="height: 40px; display: flex; align-items: center; justify-content: center; gap: 8px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
          </svg>
          <span>Citizen Public Portal</span>
        </a>
      </div>

      <div style="margin-top: var(--spacing-xl); padding-top: var(--spacing-md); border-top: 1px solid var(--color-hairline-soft); text-align: center;">
        <p class="typography-caption" style="color: var(--color-text-muted); font-size: 0.75rem; line-height: 1.4;">
          <strong>Educational &amp; Open-Source Software Project.</strong> Developed strictly for administrative research, software architecture demonstration, and academic review. Not affiliated with any official government entity.
        </p>
      </div>
    </div>
  </main>

  <footer class="footer-inverse">
    <div class="footer-content">
      <div class="footer-brand">
        <h3 class="typography-heading-4">BarangayOS.</h3>
        <p class="typography-body-sm mt-xs" style="color: var(--color-canvas-soft); opacity: 0.8;">
          Open-Source Local Government Administration Platform
        </p>
      </div>
      <div class="footer-bottom">
        <p class="typography-caption" style="opacity: 0.6;">
          &copy; 2026 BarangayOS. Powered by PHP &amp; MySQL.
        </p>
      </div>
    </div>
  </footer>

  <script src="js/api.js"></script>
  <script src="js/components/nav.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', async () => {
      if (window.AppNavbar) {
        AppNavbar.render();
      }
      window.location.replace('dashboard.php');
    });
  </script>
</body>
</html>
