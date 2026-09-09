/**
 * Barangay Management System - Toast Notification Component
 * Minimalist monochrome design following the Mobbin specification.
 */

class Toast {
  static getContainer() {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      document.body.appendChild(container);
    }
    return container;
  }

  static show(message, type = 'normal', duration = 4000) {
    const container = this.getContainer();

    const toastEl = document.createElement('div');
    toastEl.className = `toast ${type === 'error' ? 'toast-error' : type === 'success' ? 'toast-success' : ''}`;

    let iconSvg = '';
    if (type === 'success') {
      iconSvg = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>`;
    } else if (type === 'error') {
      iconSvg = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>`;
    } else {
      iconSvg = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`;
    }

    toastEl.innerHTML = `
      <div style="display: flex; align-items: center; justify-content: center;">${iconSvg}</div>
      <span>${message}</span>
    `;

    container.appendChild(toastEl);

    setTimeout(() => {
      toastEl.style.opacity = '0';
      toastEl.style.transform = 'translateY(8px) scale(0.95)';
      toastEl.style.transition = 'all 0.2s ease';
      setTimeout(() => {
        if (toastEl.parentNode) {
          toastEl.parentNode.removeChild(toastEl);
        }
      }, 200);
    }, duration);
  }

  static success(message, duration) {
    this.show(message, 'success', duration);
  }

  static error(message, duration) {
    this.show(message, 'error', duration);
  }

  static info(message, duration) {
    this.show(message, 'normal', duration);
  }
}

window.Toast = Toast;
