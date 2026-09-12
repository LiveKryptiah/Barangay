<?php
/**
 * Barangay Management System (BarangayOS)
 * User Registration & Official Onboarding
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

// If already authenticated, direct to workspace
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
  <title>Register &bull; Barangay Management System</title>
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

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--spacing-md);
    }

    @media (max-width: 600px) {
      .form-row {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <!-- Floating Nav Pill Mount -->
  <div id="navbar-mount"></div>

  <!-- Main Content Area -->
  <main class="auth-page-container">

    <div class="auth-card" style="max-width: 540px;">
      <div class="mb-lg">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--spacing-xs);">
          <h1 id="page-title" class="typography-heading-3">Create an account.</h1>
          <span id="role-badge" class="badge-neutral" style="display: none;">Initial Setup</span>
        </div>
        <p id="page-subtitle" class="typography-body-lg">Register official credentials with persistent MySQL storage.</p>
      </div>

      <!-- Account Role Segmented Control -->
      <div class="form-group mb-lg" id="role-selector-group">
        <label class="form-label">Account Role</label>
        <div class="segmented-control" role="tablist">
          <button type="button" class="segmented-control-option active" data-role="admin">
            Administrator
          </button>
          <button type="button" class="segmented-control-option" data-role="staff">
            Barangay Staff
          </button>
          <button type="button" class="segmented-control-option" data-role="official">
            Barangay Official
          </button>
        </div>
        <input type="hidden" id="selected-role" value="admin">
      </div>

      <form id="register-form" autocomplete="off">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="full-name">Full Legal Name</label>
            <div class="text-input-wrap">
              <input 
                type="text" 
                id="full-name" 
                class="text-input" 
                placeholder="e.g. Hon. Juan Dela Cruz" 
                required
              >
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="position">Position / Designation</label>
            <div class="text-input-wrap">
              <select id="position" name="position" class="text-input" required>
                <optgroup label="Elected Barangay Officials">
                  <option value="Punong Barangay (Barangay Captain)" selected>Punong Barangay (Barangay Captain)</option>
                  <option value="Barangay Kagawad (Councilor)">Barangay Kagawad (Councilor)</option>
                  <option value="SK Chairperson (Youth Council)">SK Chairperson (Youth Council)</option>
                </optgroup>
                <optgroup label="Appointive Officials & Administrative Staff">
                  <option value="Barangay Secretary">Barangay Secretary</option>
                  <option value="Barangay Treasurer">Barangay Treasurer</option>
                  <option value="Barangay Administrator">Barangay Administrator</option>
                  <option value="Barangay Records Officer / Clerk">Barangay Records Officer / Clerk</option>
                </optgroup>
                <optgroup label="Peace, Order & Community Services">
                  <option value="Barangay Tanod (Executive Officer)">Barangay Tanod (Executive Officer)</option>
                  <option value="Lupong Tagapamayapa (Mediator)">Lupong Tagapamayapa (Mediator)</option>
                  <option value="Barangay Health Worker (BHW)">Barangay Health Worker (BHW)</option>
                  <option value="Barangay Nutrition Scholar (BNS)">Barangay Nutrition Scholar (BNS)</option>
                </optgroup>
              </select>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <div class="text-input-wrap">
              <input 
                type="text" 
                id="username" 
                class="text-input" 
                placeholder="e.g. brgy_admin" 
                required
              >
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="email">Official Email Address</label>
            <div class="text-input-wrap">
              <input 
                type="email" 
                id="email" 
                class="text-input" 
                placeholder="e.g. admin@barangay.gov.ph" 
                required
              >
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="text-input-wrap">
              <input 
                type="password" 
                id="password" 
                class="text-input" 
                placeholder="Minimum 6 characters" 
                required
              >
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="confirm-password">Confirm Password</label>
            <div class="text-input-wrap">
              <input 
                type="password" 
                id="confirm-password" 
                class="text-input" 
                placeholder="Repeat password" 
                required
              >
            </div>
          </div>
        </div>

        <button type="submit" id="submit-btn" class="button-primary w-full mt-md">
          Create Account
        </button>

        <div class="mt-lg text-center">
          <p class="typography-body-sm">
            Already have an account? 
            <a href="login.php" class="typography-link" style="font-size: 0.875rem; text-decoration: underline;">Sign in here</a>
          </p>
        </div>
      </form>
    </div>

  </main>

  <script src="js/api.js"></script>
  <script src="js/components/nav.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      if (window.AppNavbar) {
        AppNavbar.render();
      }

      // Role Segmented Controller
      const roleButtons = document.querySelectorAll('.segmented-control-option');
      const selectedRoleInput = document.getElementById('selected-role');

      roleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          roleButtons.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          selectedRoleInput.value = btn.dataset.role;
        });
      });

      // Form submission
      const form = document.getElementById('register-form');
      const submitBtn = document.getElementById('submit-btn');

      form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const fullName = document.getElementById('full-name').value.trim();
        const position = document.getElementById('position').value;
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        const role = selectedRoleInput.value;

        if (password !== confirmPassword) {
          alert('Passwords do not match. Please verify.');
          return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Registering credentials...';

        try {
          await window.barangayAuth.register({
            full_name: fullName,
            position: position,
            username: username,
            email: email,
            password: password,
            role: role
          });

          submitBtn.textContent = 'Account created! Redirecting...';
          window.location.href = 'dashboard.php';
        } catch (err) {
          alert(err.message || 'Registration failed.');
          submitBtn.disabled = false;
          submitBtn.textContent = 'Create Account';
        }
      });
    });
  </script>
</body>
</html>
