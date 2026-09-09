<?php
/**
 * Barangay Management System (BarangayOS)
 * Sign In Portal
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

// If already logged in, redirect directly to dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <div class="auth-card">
      <div class="mb-lg">
        <h1 class="typography-heading-3 mb-xs">Welcome back.</h1>
        <p class="typography-body-lg">Sign in to access your Barangay Management System records.</p>
      </div>

      <!-- First-time Setup Alert -->
      <div id="setup-alert" class="setup-alert-card" style="display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <span class="typography-title" style="font-size: 0.9375rem;">First-Time Setup</span>
          <span class="badge-popular">Required</span>
        </div>
        <p class="typography-body-sm" style="color: var(--color-ink);">
          No administrator account detected in the database. Initialize your barangay system by creating the primary administrator credentials.
        </p>
        <a href="install.php" class="button-pill-soft mt-xs" style="align-self: flex-start;">
          Run Database Setup &rarr;
        </a>
      </div>

      <form id="login-form" autocomplete="on">
        <div class="form-group">
          <label class="form-label" for="identifier">Username or Email Address</label>
          <div class="text-input-wrap">
            <input 
              type="text" 
              id="identifier" 
              name="identifier" 
              class="text-input" 
              placeholder="e.g. admin or brgy.secretary@gov.ph"
              required 
              autofocus
            >
          </div>
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <label class="form-label" for="password">Password</label>
          </div>
          <div class="text-input-wrap">
            <input 
              type="password" 
              id="password" 
              name="password" 
              class="text-input" 
              placeholder="Enter your secure password"
              required
            >
            <button type="button" id="toggle-password" class="text-input-icon-btn" title="Toggle password visibility" aria-label="Toggle password visibility">
              <svg id="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="form-group" style="flex-direction: row; align-items: center; justify-content: space-between; margin-top: var(--spacing-sm); margin-bottom: var(--spacing-xl);">
          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.875rem; color: var(--color-ink);">
            <input type="checkbox" id="remember-me" style="accent-color: var(--color-primary); width: 16px; height: 16px; border-radius: 4px;">
            <span>Keep me signed in</span>
          </label>
        </div>

        <button type="submit" id="submit-btn" class="button-primary w-full">
          Sign In
        </button>

        <div class="mt-lg text-center">
          <p class="typography-body-sm">
            Need an official account? 
            <a href="register.php" class="typography-link" style="font-size: 0.875rem; text-decoration: underline;">Register here</a>
          </p>
        </div>

        <div style="margin-top: var(--spacing-lg); padding-top: var(--spacing-md); border-top: 1px solid var(--color-hairline-soft); text-align: center;">
          <p class="typography-caption" style="color: var(--color-text-muted); margin-bottom: 8px;">
            Are you a resident requesting a document or tracking an application?
          </p>
          <a href="portal.php" class="button-outline" style="display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 16px; font-size: 0.8125rem; border-radius: var(--rounded-full);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <line x1="2" y1="12" x2="22" y2="12"/>
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
            <span>Citizen Public Portal &rarr;</span>
          </a>
        </div>
      </form>
    </div>

  </main>

  <footer class="footer-inverse">
    <div class="footer-content">
      <div class="footer-brand">
        <h3 class="typography-heading-4">Barangay Management System.</h3>
        <p class="typography-body-sm mt-xs" style="color: var(--color-canvas-soft); opacity: 0.8;">
          Republic of the Philippines &bull; Local Government Unit Management Platform
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
  <script src="js/components/navbar.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', async () => {
      // 1. Mount navbar
      if (window.AppNavbar) {
        AppNavbar.render();
      }

      // 2. Check first run
      try {
        const firstRun = await window.barangayAuth.checkFirstRun();
        if (firstRun && !firstRun.has_admin) {
          document.getElementById('setup-alert').style.display = 'flex';
        }
      } catch (e) {
        console.warn('First run check failed:', e);
      }

      // 3. Password visibility toggle
      const togglePasswordBtn = document.getElementById('toggle-password');
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eye-icon');

      togglePasswordBtn.addEventListener('click', () => {
        const isPassword = passwordInput.getAttribute('type') === 'password';
        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        
        if (isPassword) {
          eyeIcon.innerHTML = `
            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
            <line x1="2" y1="2" x2="22" y2="22"/>
          `;
        } else {
          eyeIcon.innerHTML = `
            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
            <circle cx="12" cy="12" r="3"/>
          `;
        }
      });

      // 4. Form Submission
      const form = document.getElementById('login-form');
      const submitBtn = document.getElementById('submit-btn');

      form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const username = document.getElementById('identifier').value.trim();
        const password = document.getElementById('password').value;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Verifying credentials...';

        try {
          const res = await window.barangayAuth.login(username, password);
          submitBtn.textContent = 'Redirecting to workspace...';
          window.location.href = 'dashboard.php';
        } catch (err) {
          alert(err.message || 'Authentication failed. Please verify credentials.');
          submitBtn.disabled = false;
          submitBtn.textContent = 'Sign In';
        }
      });
    });
  </script>
</body>
</html>
