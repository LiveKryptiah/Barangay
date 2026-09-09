/**
 * Barangay Management System - Floating Nav Pill Component
 * Detached horizontally-centered stadium bar adhering to the Mobbin design specification.
 */

class NavPill {
  static async render(activePage = 'login') {
    const navPlaceholder = document.getElementById('navbar-mount');
    if (!navPlaceholder) return;

    let userSession = null;
    if (window.authService) {
      userSession = await window.authService.getCurrentSession();
    }

    const ext = window.location.pathname.endsWith('.html') ? '.html' : '.php';
    const isLoggedIn = !!userSession;
    const user = userSession ? userSession.user : null;

    let linksHtml = '';
    let actionsHtml = '';

    if (isLoggedIn) {
      linksHtml = `
        <ul class="nav-links">
          <li><a href="dashboard${ext}" class="nav-link ${activePage === 'dashboard' ? 'active' : ''}">Dashboard</a></li>
          <li><a href="residents${ext}" class="nav-link ${activePage === 'residents' ? 'active' : ''}">Residents</a></li>
          <li><a href="certificates${ext}" class="nav-link ${activePage === 'certificates' ? 'active' : ''}">Clearances</a></li>
          <li><a href="blotter${ext}" class="nav-link ${activePage === 'blotter' ? 'active' : ''}">Blotter</a></li>
          <li><a href="verify${ext}" class="nav-link ${activePage === 'verify' ? 'active' : ''}">Verify</a></li>
        </ul>
      `;

      actionsHtml = `
        <div class="nav-actions">
          <span class="badge-neutral" title="${user.fullName}">${user.username} (${user.role})</span>
          <button id="nav-logout-btn" class="button-outline" style="height: 38px; padding: 0 16px; font-size: 0.875rem;">
            Sign out
          </button>
        </div>
      `;
    } else {
      linksHtml = `
        <ul class="nav-links">
          <li><a href="portal${ext}" class="nav-link ${activePage === 'portal' ? 'active' : ''}">Citizen Portal</a></li>
          <li><a href="verify${ext}" class="nav-link ${activePage === 'verify' ? 'active' : ''}">Verify Document</a></li>
          <li><a href="login${ext}" class="nav-link ${activePage === 'login' ? 'active' : ''}">Staff Login</a></li>
        </ul>
      `;

      const themeBtnHtml = `
        <button id="nav-theme-toggle-btn" class="theme-toggle-btn" title="Toggle Theme" aria-label="Toggle Theme">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/></svg>
        </button>
      `;

      if (activePage === 'login') {
        actionsHtml = `
          <div class="nav-actions">
            ${themeBtnHtml}
            <a href="register.html" class="button-primary" style="height: 38px; padding: 0 16px; font-size: 0.875rem;">
              New Account
            </a>
          </div>
        `;
      } else {
        actionsHtml = `
          <div class="nav-actions">
            ${themeBtnHtml}
            <a href="login.html" class="button-primary" style="height: 38px; padding: 0 16px; font-size: 0.875rem;">
              Sign in
            </a>
          </div>
        `;
      }
    }

    navPlaceholder.innerHTML = `
      <div class="nav-pill-wrapper">
        <nav class="nav-pill" aria-label="Main Navigation">
          <a href="${isLoggedIn ? 'dashboard.html' : 'login.html'}" class="nav-brand">
            <div class="nav-brand-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
            </div>
            <span class="nav-brand-title">BarangayOS</span>
          </a>
          ${linksHtml}
          ${actionsHtml}
        </nav>
      </div>
    `;

    // Bind theme button
    const themeBtn = document.getElementById('nav-theme-toggle-btn');
    if (themeBtn && window.ThemeManager) {
      themeBtn.addEventListener('click', () => {
        window.ThemeManager.toggle();
      });
    }

    if (window.ThemeManager) {
      window.ThemeManager.updateAllIcons();
    }

    // Bind logout button
    const logoutBtn = document.getElementById('nav-logout-btn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', async () => {
        if (window.authService) {
          await window.authService.logout();
          window.location.href = 'login.html';
        }
      });
    }
  }
}

window.NavPill = NavPill;
