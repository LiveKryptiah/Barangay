/**
 * Barangay Management System - Inverted Ink Footer Component
 * Full-bleed near-black {colors.ink} (#141414) footer with rounded top corners.
 */

class Footer {
  static render() {
    const footerPlaceholder = document.getElementById('footer-mount');
    if (!footerPlaceholder) return;

    const ext = window.location.pathname.endsWith('.html') ? '.html' : '.php';

    footerPlaceholder.innerHTML = `
      <footer class="footer-inverted">
        <div class="footer-inner">
          <div class="footer-top">
            <div class="footer-brand">
              <h2>BarangayOS.</h2>
              <p>Minimalist, privacy-first local community administration platform.</p>
            </div>
            <div class="footer-columns">
              <div class="footer-column">
                <h4>System Modules</h4>
                <ul>
                  <li><a href="dashboard${ext}">Executive Dashboard</a></li>
                  <li><a href="residents${ext}">Resident Directory</a></li>
                  <li><a href="certificates${ext}">Clearance Issuance</a></li>
                  <li><a href="blotter${ext}">Blotter & Incident Records</a></li>
                </ul>
              </div>
              <div class="footer-column">
                <h4>Security & Platform</h4>
                <ul>
                  <li><a href="#">PBKDF2 Cryptography</a></li>
                  <li><a href="#">IndexedDB Storage</a></li>
                  <li><a href="#">Data Privacy Act Compliance</a></li>
                  <li><a href="#">System Health Status</a></li>
                </ul>
              </div>
            </div>
          </div>
          <div class="footer-bottom">
            <span>&copy; ${new Date().getFullYear()} Barangay Management System. All rights reserved.</span>
            <span>Design standard: Mobbin Monochrome &bull; Zero Dummy Data Architecture</span>
          </div>
        </div>
      </footer>
    `;
  }
}

window.Footer = Footer;
