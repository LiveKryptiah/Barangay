/**
 * Barangay Management System - Cryptographic Authentication Service
 * Uses Web Cryptography API (PBKDF2-HMAC-SHA256) for zero-dependency, secure password hashing.
 * Manages real persistent sessions in IndexedDB.
 */

class AuthService {
  constructor() {
    this.SESSION_KEY = 'bms_auth_token';
    this.SESSION_TYPE_KEY = 'bms_auth_remember';
  }

  // --- Cryptographic Utilities ---

  async generateSalt() {
    if (window.crypto && window.crypto.getRandomValues) {
      const array = new Uint8Array(16);
      window.crypto.getRandomValues(array);
      return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    }
    // Fallback pseudo-random
    let s = '';
    for (let i = 0; i < 32; i++) s += Math.floor(Math.random() * 16).toString(16);
    return s;
  }

  // Pure JavaScript SHA-256 implementation as universal fallback
  static sha256Sync(ascii) {
    function rightRotate(value, amount) {
      return (value >>> amount) | (value << (32 - amount));
    }
    const mathPow = Math.pow;
    const maxWord = mathPow(2, 32);
    let i, j;
    let result = '';
    const words = [];
    const asciiBitLength = ascii.length * 8;
    let hash = [];
    const k = [];
    let primeCounter = 0;

    const isPrime = {};
    for (let candidate = 2; primeCounter < 64; candidate++) {
      if (!isPrime[candidate]) {
        for (i = 0; i < 300; i += candidate) isPrime[i] = candidate;
        hash[primeCounter] = (mathPow(candidate, 0.5) * maxWord) | 0;
        k[primeCounter++] = (mathPow(candidate, 1 / 3) * maxWord) | 0;
      }
    }

    ascii += '\x80';
    while ((ascii.length % 64) - 56) ascii += '\x00';
    for (i = 0; i < ascii.length; i++) {
      j = ascii.charCodeAt(i);
      if (j >> 8) return;
      words[i >> 2] |= j << (((3 - i) % 4) * 8);
    }
    words[words.length] = (asciiBitLength / maxWord) | 0;
    words[words.length] = asciiBitLength;

    for (j = 0; j < words.length; ) {
      const w = words.slice(j, (j += 16));
      const oldHash = hash;
      hash = hash.slice(0, 8);

      for (i = 0; i < 64; i++) {
        const i2 = i + j;
        const w15 = w[i - 15], w2 = w[i - 2];
        const a = hash[0], e = hash[4];
        const temp1 =
          hash[7] +
          (rightRotate(e, 6) ^ rightRotate(e, 11) ^ rightRotate(e, 25)) +
          ((e & hash[5]) ^ (~e & hash[6])) +
          k[i] +
          (w[i] =
            i < 16
              ? w[i]
              : (w[i - 16] +
                  (rightRotate(w15, 7) ^ rightRotate(w15, 18) ^ (w15 >>> 3)) +
                  w[i - 7] +
                  (rightRotate(w2, 17) ^ rightRotate(w2, 19) ^ (w2 >>> 10))) |
                0);
        const temp2 =
          (rightRotate(a, 2) ^ rightRotate(a, 13) ^ rightRotate(a, 22)) +
          ((a & hash[1]) ^ (a & hash[2]) ^ (hash[1] & hash[2]));

        hash = [(temp1 + temp2) | 0].concat(hash);
        hash[4] = (hash[4] + temp1) | 0;
      }

      for (i = 0; i < 8; i++) {
        hash[i] = (hash[i] + oldHash[i]) | 0;
      }
    }

    for (i = 0; i < 8; i++) {
      for (let b = 3; b >= 0; b--) {
        const byte = (hash[i] >> (b * 8)) & 255;
        result += (byte < 16 ? '0' : '') + byte.toString(16);
      }
    }
    return result;
  }

  async hashPassword(password, saltHex) {
    if (window.crypto && window.crypto.subtle) {
      try {
        const enc = new TextEncoder();
        const saltBytes = new Uint8Array(saltHex.match(/.{1,2}/g).map(byte => parseInt(byte, 16)));

        const keyMaterial = await window.crypto.subtle.importKey(
          'raw',
          enc.encode(password),
          { name: 'PBKDF2' },
          false,
          ['deriveBits']
        );

        const keyBits = await window.crypto.subtle.deriveBits(
          {
            name: 'PBKDF2',
            salt: saltBytes,
            iterations: 100000,
            hash: 'SHA-256'
          },
          keyMaterial,
          256
        );

        const hashArray = Array.from(new Uint8Array(keyBits));
        return hashArray.map(byte => byte.toString(16).padStart(2, '0')).join('');
      } catch (e) {
        console.warn('CryptoSubtle unavailable, falling back to pure SHA-256:', e);
      }
    }

    // Pure JS Fallback: Multi-round salted hash
    let h = saltHex + ':' + password;
    for (let round = 0; round < 1000; round++) {
      h = AuthService.sha256Sync(h + saltHex);
    }
    return h;
  }

  generateToken() {
    if (window.crypto && window.crypto.getRandomValues) {
      const array = new Uint8Array(32);
      window.crypto.getRandomValues(array);
      return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    }
    let t = '';
    for (let i = 0; i < 64; i++) t += Math.floor(Math.random() * 16).toString(16);
    return t;
  }

  // --- Account Existence & Setup Checks ---

  async hasAnyUser() {
    const count = await window.barangayDB.count('users');
    return count > 0;
  }

  async hasAdminAccount() {
    const users = await window.barangayDB.getAll('users');
    return users.some(u => u.role === 'admin' || u.role === 'captain' || u.role === 'secretary');
  }

  // --- Registration ---

  async register({ username, email, password, fullName, role = 'resident', position = '' }) {
    username = username.trim().toLowerCase();
    email = email.trim().toLowerCase();

    if (!username || username.length < 3) {
      throw new Error('Username must be at least 3 characters long.');
    }
    if (!email || !email.includes('@')) {
      throw new Error('Please provide a valid email address.');
    }
    if (!password || password.length < 6) {
      throw new Error('Password must be at least 6 characters long.');
    }
    if (!fullName || fullName.trim().length < 2) {
      throw new Error('Full name is required.');
    }

    // Check uniqueness
    const existingUser = await window.barangayDB.getByIndex('users', 'username', username);
    if (existingUser) {
      throw new Error('This username is already registered. Please choose another.');
    }

    const existingEmail = await window.barangayDB.getByIndex('users', 'email', email);
    if (existingEmail) {
      throw new Error('This email address is already registered.');
    }

    const salt = await this.generateSalt();
    const passwordHash = await this.hashPassword(password, salt);

    const userRecord = {
      username,
      email,
      passwordHash,
      salt,
      fullName: fullName.trim(),
      role,
      position: position.trim() || (role === 'admin' ? 'Barangay Administrator' : 'Resident'),
      status: 'active',
      createdAt: new Date().toISOString(),
      lastLogin: null
    };

    const userId = await window.barangayDB.add('users', userRecord);

    // Audit log
    await window.barangayDB.add('audit_logs', {
      userId,
      action: 'USER_REGISTERED',
      details: `User ${username} registered with role ${role}.`,
      timestamp: new Date().toISOString()
    });

    return { id: userId, ...userRecord };
  }

  // --- Login & Session Management ---

  async login(identifier, password, rememberMe = false) {
    identifier = identifier.trim().toLowerCase();

    if (!identifier || !password) {
      throw new Error('Please enter both username/email and password.');
    }

    // Look up by username or email
    let user = await window.barangayDB.getByIndex('users', 'username', identifier);
    if (!user) {
      user = await window.barangayDB.getByIndex('users', 'email', identifier);
    }

    if (!user) {
      throw new Error('No account found matching those credentials.');
    }

    if (user.status !== 'active') {
      throw new Error('This account has been deactivated. Please contact the Barangay Admin.');
    }

    const checkHash = await this.hashPassword(password, user.salt);
    if (checkHash !== user.passwordHash) {
      throw new Error('Incorrect password. Please try again.');
    }

    // Generate Session
    const token = this.generateToken();
    const durationDays = rememberMe ? 14 : 1;
    const expiresAt = new Date(Date.now() + durationDays * 24 * 60 * 60 * 1000).toISOString();

    const sessionRecord = {
      token,
      userId: user.id,
      username: user.username,
      role: user.role,
      createdAt: new Date().toISOString(),
      expiresAt
    };

    await window.barangayDB.add('sessions', sessionRecord);

    // Save token to browser storage
    if (rememberMe) {
      localStorage.setItem(this.SESSION_KEY, token);
      localStorage.setItem(this.SESSION_TYPE_KEY, 'local');
    } else {
      sessionStorage.setItem(this.SESSION_KEY, token);
      localStorage.setItem(this.SESSION_TYPE_KEY, 'session');
    }

    // Update user's last login
    user.lastLogin = new Date().toISOString();
    await window.barangayDB.put('users', user);

    // Audit log
    await window.barangayDB.add('audit_logs', {
      userId: user.id,
      action: 'USER_LOGIN',
      details: `User ${user.username} logged in successfully.`,
      timestamp: new Date().toISOString()
    });

    return { user, session: sessionRecord };
  }

  async logout() {
    const token = this.getToken();
    if (token) {
      try {
        await window.barangayDB.delete('sessions', token);
      } catch (e) {
        console.warn('Session delete error:', e);
      }
    }
    localStorage.removeItem(this.SESSION_KEY);
    sessionStorage.removeItem(this.SESSION_KEY);
    localStorage.removeItem(this.SESSION_TYPE_KEY);
  }

  getToken() {
    return localStorage.getItem(this.SESSION_KEY) || sessionStorage.getItem(this.SESSION_KEY);
  }

  async getCurrentSession() {
    const token = this.getToken();
    if (!token) return null;

    try {
      const session = await window.barangayDB.get('sessions', token);
      if (!session) {
        this.logout();
        return null;
      }

      // Check expiry
      if (new Date(session.expiresAt) < new Date()) {
        await this.logout();
        return null;
      }

      const user = await window.barangayDB.get('users', session.userId);
      if (!user || user.status !== 'active') {
        await this.logout();
        return null;
      }

      return { session, user };
    } catch (e) {
      console.error('Failed to get session:', e);
      return null;
    }
  }

  // --- Route Protection ---

  async requireAuth(redirectUrl = 'login.html') {
    const sessionData = await this.getCurrentSession();
    if (!sessionData) {
      window.location.href = redirectUrl;
      return null;
    }
    return sessionData;
  }

  async requireGuest(redirectUrl = 'dashboard.html') {
    const sessionData = await this.getCurrentSession();
    if (sessionData) {
      window.location.href = redirectUrl;
      return sessionData;
    }
    return null;
  }
}

// Global Singleton Auth Service
window.authService = new AuthService();
