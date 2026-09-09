/**
 * Barangay Management System - Theme Manager
 * Provides zero-flash instant theme restoration, localStorage persistence,
 * system preference detection, and accessible toggle buttons.
 */

class ThemeManager {
  static STORAGE_KEY = 'bms_theme';

  static init() {
    const savedTheme = localStorage.getItem(this.STORAGE_KEY);
    const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const theme = savedTheme || (systemPrefersDark ? 'dark' : 'light');
    
    document.documentElement.setAttribute('data-theme', theme);
    this.updateAllIcons(theme);

    // Listen for system theme changes if user hasn't explicitly set a preference
    if (!savedTheme && window.matchMedia) {
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem(this.STORAGE_KEY)) {
          const newTheme = e.matches ? 'dark' : 'light';
          this.setTheme(newTheme, false);
        }
      });
    }
  }

  static getTheme() {
    return document.documentElement.getAttribute('data-theme') || 'light';
  }

  static setTheme(theme, save = true) {
    document.documentElement.setAttribute('data-theme', theme);
    if (save) {
      localStorage.setItem(this.STORAGE_KEY, theme);
    }
    this.updateAllIcons(theme);
    window.dispatchEvent(new CustomEvent('bms_theme_change', { detail: { theme } }));
  }

  static toggle() {
    const current = this.getTheme();
    const next = current === 'dark' ? 'light' : 'dark';
    this.setTheme(next, true);
    if (window.Toast) {
      window.Toast.info(`Switched to ${next === 'dark' ? 'Dark' : 'Light'} Mode`, 2000);
    }
    return next;
  }

  static getIconSvg(theme) {
    // If currently dark, show Sun icon to switch to light.
    // If currently light, show Moon icon to switch to dark.
    if (theme === 'dark') {
      return `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>`;
    } else {
      return `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>`;
    }
  }

  static updateAllIcons(theme) {
    const currentTheme = theme || this.getTheme();
    const buttons = document.querySelectorAll('.theme-toggle-btn');
    buttons.forEach(btn => {
      btn.innerHTML = this.getIconSvg(currentTheme);
      btn.setAttribute('title', `Switch to ${currentTheme === 'dark' ? 'Light' : 'Dark'} Mode`);
      btn.setAttribute('aria-label', `Switch to ${currentTheme === 'dark' ? 'Light' : 'Dark'} Mode`);
    });
  }
}

// Immediate execution to prevent flash
(() => {
  const savedTheme = localStorage.getItem('bms_theme');
  const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  const theme = savedTheme || (systemPrefersDark ? 'dark' : 'light');
  document.documentElement.setAttribute('data-theme', theme);
})();

// Delegated click listener for any .theme-toggle-btn anywhere in the DOM
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.theme-toggle-btn');
  if (btn) {
    e.preventDefault();
    ThemeManager.toggle();
  }
});

document.addEventListener('DOMContentLoaded', () => {
  ThemeManager.init();
});

window.ThemeManager = ThemeManager;

