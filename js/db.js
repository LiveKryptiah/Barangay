/**
 * Barangay Management System - Persistent Database Engine
 * Real IndexedDB transaction-based database abstraction with indexing & promises.
 * Zero dummy data: Starts completely clean and persists on the user's disk.
 */

const DB_NAME = 'BarangayManagementDB';
const DB_VERSION = 9;

class BarangayDB {
  constructor() {
    this.db = null;
    this.initPromise = null;
  }

  async getDB() {
    if (this.db) return this.db;
    if (!this.initPromise) {
      this.initPromise = this.open();
    }
    return this.initPromise;
  }

  open() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(DB_NAME, DB_VERSION);

      request.onupgradeneeded = (event) => {
        const db = event.target.result;

        // 1. Users Store
        if (!db.objectStoreNames.contains('users')) {
          const userStore = db.createObjectStore('users', { keyPath: 'id', autoIncrement: true });
          userStore.createIndex('username', 'username', { unique: true });
          userStore.createIndex('email', 'email', { unique: true });
          userStore.createIndex('role', 'role', { unique: false });
          userStore.createIndex('createdAt', 'createdAt', { unique: false });
        }

        // 2. Sessions Store
        if (!db.objectStoreNames.contains('sessions')) {
          const sessionStore = db.createObjectStore('sessions', { keyPath: 'token' });
          sessionStore.createIndex('userId', 'userId', { unique: false });
          sessionStore.createIndex('expiresAt', 'expiresAt', { unique: false });
        }

        // 3. Residents Store
        if (!db.objectStoreNames.contains('residents')) {
          const residentStore = db.createObjectStore('residents', { keyPath: 'id', autoIncrement: true });
          residentStore.createIndex('fullName', 'fullName', { unique: false });
          residentStore.createIndex('purok', 'purok', { unique: false });
          residentStore.createIndex('voterStatus', 'voterStatus', { unique: false });
          residentStore.createIndex('createdAt', 'createdAt', { unique: false });
        }

        // 4. Certificates & Clearances Store
        if (!db.objectStoreNames.contains('certificates')) {
          const certStore = db.createObjectStore('certificates', { keyPath: 'id', autoIncrement: true });
          certStore.createIndex('trackingCode', 'trackingCode', { unique: true });
          certStore.createIndex('residentId', 'residentId', { unique: false });
          certStore.createIndex('type', 'type', { unique: false });
          certStore.createIndex('status', 'status', { unique: false });
          certStore.createIndex('issuedAt', 'issuedAt', { unique: false });
        }

        // 5. Blotter & Incident Cases Store
        if (!db.objectStoreNames.contains('blotter_cases')) {
          const blotterStore = db.createObjectStore('blotter_cases', { keyPath: 'id', autoIncrement: true });
          blotterStore.createIndex('caseNumber', 'caseNumber', { unique: true });
          blotterStore.createIndex('status', 'status', { unique: false });
          blotterStore.createIndex('incidentDate', 'incidentDate', { unique: false });
        }

        // 6. Officials Store
        if (!db.objectStoreNames.contains('officials')) {
          const officialStore = db.createObjectStore('officials', { keyPath: 'id', autoIncrement: true });
          officialStore.createIndex('position', 'position', { unique: false });
          officialStore.createIndex('order', 'order', { unique: false });
        }

        // 7. Audit Logs Store
        if (!db.objectStoreNames.contains('audit_logs')) {
          const logStore = db.createObjectStore('audit_logs', { keyPath: 'id', autoIncrement: true });
          logStore.createIndex('userId', 'userId', { unique: false });
          logStore.createIndex('action', 'action', { unique: false });
          logStore.createIndex('timestamp', 'timestamp', { unique: false });
        }

        // 8. Barangay Settings Store
        if (!db.objectStoreNames.contains('settings')) {
          db.createObjectStore('settings', { keyPath: 'key' });
        }

        // 9. Households Store
        if (!db.objectStoreNames.contains('households')) {
          const householdStore = db.createObjectStore('households', { keyPath: 'id', autoIncrement: true });
          householdStore.createIndex('householdNo', 'householdNo', { unique: true });
          householdStore.createIndex('headResidentId', 'headResidentId', { unique: false });
          householdStore.createIndex('purok', 'purok', { unique: false });
          householdStore.createIndex('createdAt', 'createdAt', { unique: false });
        }

        // 10. Incidents & Emergency Dispatch Store
        if (!db.objectStoreNames.contains('incidents')) {
          const incidentStore = db.createObjectStore('incidents', { keyPath: 'id', autoIncrement: true });
          incidentStore.createIndex('incidentNo', 'incidentNo', { unique: true });
          incidentStore.createIndex('status', 'status', { unique: false });
          incidentStore.createIndex('priority', 'priority', { unique: false });
          incidentStore.createIndex('type', 'type', { unique: false });
          incidentStore.createIndex('purok', 'purok', { unique: false });
          incidentStore.createIndex('createdAt', 'createdAt', { unique: false });
        }

        // 11. PVC Resident Identification Cards Store
        if (!db.objectStoreNames.contains('resident_ids')) {
          const idStore = db.createObjectStore('resident_ids', { keyPath: 'id', autoIncrement: true });
          idStore.createIndex('idNumber', 'idNumber', { unique: true });
          idStore.createIndex('residentId', 'residentId', { unique: false });
          idStore.createIndex('status', 'status', { unique: false });
          idStore.createIndex('createdAt', 'createdAt', { unique: false });
        }

        // 12. Notifications & SMS Dispatch Store
        if (!db.objectStoreNames.contains('notifications')) {
          const notifStore = db.createObjectStore('notifications', { keyPath: 'id', autoIncrement: true });
          notifStore.createIndex('dispatchCode', 'dispatchCode', { unique: true });
          notifStore.createIndex('recipientContact', 'recipientContact', { unique: false });
          notifStore.createIndex('channel', 'channel', { unique: false });
          notifStore.createIndex('category', 'category', { unique: false });
          notifStore.createIndex('status', 'status', { unique: false });
          notifStore.createIndex('createdAt', 'createdAt', { unique: false });
        }

        // 13. Notification Templates Store
        if (!db.objectStoreNames.contains('notification_templates')) {
          const tmplStore = db.createObjectStore('notification_templates', { keyPath: 'id', autoIncrement: true });
          tmplStore.createIndex('templateKey', 'templateKey', { unique: true });
          tmplStore.createIndex('category', 'category', { unique: false });
        }

        // 14. Health Station & Clinical Records Store
        if (!db.objectStoreNames.contains('health_records')) {
          const healthStore = db.createObjectStore('health_records', { keyPath: 'id', autoIncrement: true });
          healthStore.createIndex('recordNo', 'recordNo', { unique: true });
          healthStore.createIndex('residentId', 'residentId', { unique: false });
          healthStore.createIndex('serviceType', 'serviceType', { unique: false });
          healthStore.createIndex('status', 'status', { unique: false });
          healthStore.createIndex('followUpDate', 'followUpDate', { unique: false });
          healthStore.createIndex('createdAt', 'createdAt', { unique: false });
        }

        // 15. Health Pharmacy & Medicine Inventory Store
        if (!db.objectStoreNames.contains('health_medicines')) {
          const medStore = db.createObjectStore('health_medicines', { keyPath: 'id', autoIncrement: true });
          medStore.createIndex('medicineName', 'medicineName', { unique: false });
          medStore.createIndex('genericName', 'genericName', { unique: false });
          medStore.createIndex('category', 'category', { unique: false });
          medStore.createIndex('expiryDate', 'expiryDate', { unique: false });
          medStore.createIndex('batchNo', 'batchNo', { unique: false });
        }

        // 16. Budget Allocations Store (AIP & Statutory Funds)
        if (!db.objectStoreNames.contains('budget_allocations')) {
          const budgetStore = db.createObjectStore('budget_allocations', { keyPath: 'id', autoIncrement: true });
          budgetStore.createIndex('fiscalYear', 'fiscalYear', { unique: false });
          budgetStore.createIndex('fundSource', 'fundSource', { unique: false });
          budgetStore.createIndex('createdAt', 'createdAt', { unique: false });
        }

        // 17. BAC Procurement Projects Store (PRs, POs, Contracts)
        if (!db.objectStoreNames.contains('procurement_projects')) {
          const procStore = db.createObjectStore('procurement_projects', { keyPath: 'id', autoIncrement: true });
          procStore.createIndex('prNumber', 'prNumber', { unique: true });
          procStore.createIndex('poNumber', 'poNumber', { unique: false });
          procStore.createIndex('status', 'status', { unique: false });
          procStore.createIndex('fundSource', 'fundSource', { unique: false });
          procStore.createIndex('createdAt', 'createdAt', { unique: false });
        }

        // 18. Procurement Bids & Canvass Quotes Store
        if (!db.objectStoreNames.contains('procurement_bids')) {
          const bidStore = db.createObjectStore('procurement_bids', { keyPath: 'id', autoIncrement: true });
          bidStore.createIndex('projectId', 'projectId', { unique: false });
          bidStore.createIndex('complianceStatus', 'complianceStatus', { unique: false });
          bidStore.createIndex('canvassedAt', 'canvassedAt', { unique: false });
        }

        // 19. Sangguniang Barangay Legislative Documents (Ordinances, Resolutions, EOs)
        if (!db.objectStoreNames.contains('legislative_documents')) {
          const legStore = db.createObjectStore('legislative_documents', { keyPath: 'id', autoIncrement: true });
          legStore.createIndex('controlNumber', 'controlNumber', { unique: true });
          legStore.createIndex('docType', 'docType', { unique: false });
          legStore.createIndex('readingStage', 'readingStage', { unique: false });
          legStore.createIndex('status', 'status', { unique: false });
          legStore.createIndex('committee', 'committee', { unique: false });
          legStore.createIndex('createdAt', 'createdAt', { unique: false });
        }

        // 20. Sangguniang Barangay Sessions & Minutes
        if (!db.objectStoreNames.contains('legislative_sessions')) {
          const sessStore = db.createObjectStore('legislative_sessions', { keyPath: 'id', autoIncrement: true });
          sessStore.createIndex('sessionNumber', 'sessionNumber', { unique: true });
          sessStore.createIndex('sessionType', 'sessionType', { unique: false });
          sessStore.createIndex('sessionDate', 'sessionDate', { unique: false });
          sessStore.createIndex('sessionStatus', 'sessionStatus', { unique: false });
          sessStore.createIndex('createdAt', 'createdAt', { unique: false });
        }

        // 21. Lupong Tagapamayapa Members Roster (RA 7160 Sec. 399)
        if (!db.objectStoreNames.contains('lupon_members')) {
          const luponStore = db.createObjectStore('lupon_members', { keyPath: 'id', autoIncrement: true });
          luponStore.createIndex('fullName', 'fullName', { unique: false });
          luponStore.createIndex('status', 'status', { unique: false });
          luponStore.createIndex('committeeAssignment', 'committeeAssignment', { unique: false });
          luponStore.createIndex('createdAt', 'createdAt', { unique: false });
        }

        // 22. Katarungang Pambarangay (KP) Dispute Cases (RA 7160 Sec. 408-418)
        if (!db.objectStoreNames.contains('lupon_cases')) {
          const kpStore = db.createObjectStore('lupon_cases', { keyPath: 'id', autoIncrement: true });
          kpStore.createIndex('caseNumber', 'caseNumber', { unique: true });
          kpStore.createIndex('stage', 'stage', { unique: false });
          kpStore.createIndex('disputeType', 'disputeType', { unique: false });
          kpStore.createIndex('dateFiled', 'dateFiled', { unique: false });
          kpStore.createIndex('complainantName', 'complainantName', { unique: false });
          kpStore.createIndex('respondentName', 'respondentName', { unique: false });
          kpStore.createIndex('createdAt', 'createdAt', { unique: false });
        }

        // 23. KP Mediation & Conciliation Hearings Docket
        if (!db.objectStoreNames.contains('lupon_hearings')) {
          const hStore = db.createObjectStore('lupon_hearings', { keyPath: 'id', autoIncrement: true });
          hStore.createIndex('caseId', 'caseId', { unique: false });
          hStore.createIndex('scheduledDate', 'scheduledDate', { unique: false });
          hStore.createIndex('hearingType', 'hearingType', { unique: false });
          hStore.createIndex('createdAt', 'createdAt', { unique: false });
        }
      };

      request.onsuccess = (event) => {
        this.db = event.target.result;
        resolve(this.db);
      };

      request.onerror = (event) => {
        console.error('IndexedDB error:', event.target.error);
        reject(event.target.error);
      };
    });
  }

  async add(storeName, data) {
    const db = await this.getDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(storeName, 'readwrite');
      const store = tx.objectStore(storeName);
      const request = store.add(data);

      request.onsuccess = (e) => resolve(e.target.result);
      request.onerror = (e) => reject(e.target.error);
    });
  }

  async put(storeName, data) {
    const db = await this.getDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(storeName, 'readwrite');
      const store = tx.objectStore(storeName);
      const request = store.put(data);

      request.onsuccess = (e) => resolve(e.target.result);
      request.onerror = (e) => reject(e.target.error);
    });
  }

  async get(storeName, key) {
    const db = await this.getDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(storeName, 'readonly');
      const store = tx.objectStore(storeName);
      const request = store.get(key);

      request.onsuccess = (e) => resolve(e.target.result || null);
      request.onerror = (e) => reject(e.target.error);
    });
  }

  async getByIndex(storeName, indexName, value) {
    const db = await this.getDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(storeName, 'readonly');
      const store = tx.objectStore(storeName);
      const index = store.index(indexName);
      const request = index.get(value);

      request.onsuccess = (e) => resolve(e.target.result || null);
      request.onerror = (e) => reject(e.target.error);
    });
  }

  async getAllByIndex(storeName, indexName, value) {
    const db = await this.getDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(storeName, 'readonly');
      const store = tx.objectStore(storeName);
      const index = store.index(indexName);
      const request = index.getAll(value);

      request.onsuccess = (e) => resolve(e.target.result || []);
      request.onerror = (e) => reject(e.target.error);
    });
  }

  async getAll(storeName) {
    const db = await this.getDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(storeName, 'readonly');
      const store = tx.objectStore(storeName);
      const request = store.getAll();

      request.onsuccess = (e) => resolve(e.target.result || []);
      request.onerror = (e) => reject(e.target.error);
    });
  }

  async delete(storeName, key) {
    const db = await this.getDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(storeName, 'readwrite');
      const store = tx.objectStore(storeName);
      const request = store.delete(key);

      request.onsuccess = () => resolve(true);
      request.onerror = (e) => reject(e.target.error);
    });
  }

  async count(storeName) {
    const db = await this.getDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(storeName, 'readonly');
      const store = tx.objectStore(storeName);
      const request = store.count();

      request.onsuccess = (e) => resolve(e.target.result || 0);
      request.onerror = (e) => reject(e.target.error);
    });
  }

  async clear(storeName) {
    const db = await this.getDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(storeName, 'readwrite');
      const store = tx.objectStore(storeName);
      const request = store.clear();

      request.onsuccess = () => resolve(true);
      request.onerror = (e) => reject(e.target.error);
    });
  }
}

// Global Singleton Database Instance
window.barangayDB = new BarangayDB();
