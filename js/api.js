/**
 * Barangay Management System (BarangayOS)
 * PHP & MySQL REST API Client Bridge
 * Features: Bidirectional CamelCase <-> Snake_Case attribute normalization
 * Drop-in replacement for client-side IndexedDB
 */

(function() {
  'use strict';

  // Bidirectional record normalizer: ensures both snake_case & camelCase properties exist
  function normalizeRecord(rec) {
    if (!rec || typeof rec !== 'object') return rec;

    // Residents
    if (rec.first_name !== undefined && rec.firstName === undefined) rec.firstName = rec.first_name;
    if (rec.middle_name !== undefined && rec.middleName === undefined) rec.middleName = rec.middle_name;
    if (rec.last_name !== undefined && rec.lastName === undefined) rec.lastName = rec.last_name;
    if (rec.resident_code !== undefined && rec.residentCode === undefined) rec.residentCode = rec.resident_code;
    if (rec.contact_no !== undefined && rec.phone === undefined) {
      rec.phone = rec.contact_no;
      rec.contactNo = rec.contact_no;
    }
    if (rec.voter_status !== undefined && rec.isVoter === undefined) {
      rec.isVoter = (rec.voter_status === 'Registered');
      rec.voterStatus = rec.voter_status;
    }
    if (rec.is_4ps !== undefined && rec.isFourPs === undefined) {
      rec.isFourPs = Boolean(rec.is_4ps);
      rec.is4ps = Boolean(rec.is_4ps);
    }
    if (rec.is_indigent !== undefined && rec.isIndigent === undefined) rec.isIndigent = Boolean(rec.is_indigent);
    if (rec.is_pwd !== undefined && rec.isPwd === undefined) rec.isPwd = Boolean(rec.is_pwd);
    if (rec.is_solo_parent !== undefined && rec.isSoloParent === undefined) rec.isSoloParent = Boolean(rec.is_solo_parent);
    if (rec.is_senior !== undefined && rec.isSenior === undefined) rec.isSenior = Boolean(rec.is_senior);
    if (rec.emergency_contact !== undefined && rec.emergencyContact === undefined) rec.emergencyContact = rec.emergency_contact;
    if (rec.emergency_name !== undefined && rec.emergencyName === undefined) rec.emergencyName = rec.emergency_name;
    if (rec.street !== undefined && rec.address === undefined) rec.address = rec.street;
    if (rec.photo_url !== undefined && rec.photoUrl === undefined) rec.photoUrl = rec.photo_url;
    if (rec.photoUrl !== undefined && rec.photo_url === undefined) rec.photo_url = rec.photoUrl;

    // Resident IDs
    if (rec.id_number !== undefined && rec.idNumber === undefined) rec.idNumber = rec.id_number;
    if (rec.blood_type !== undefined && rec.bloodType === undefined) rec.bloodType = rec.blood_type;
    if (rec.valid_until !== undefined && rec.validUntil === undefined) rec.validUntil = rec.valid_until;
    if (rec.signatory_name !== undefined && rec.signatoryName === undefined) rec.signatoryName = rec.signatory_name;

    // Households
    if (rec.household_no !== undefined && rec.householdNo === undefined) rec.householdNo = rec.household_no;
    if (rec.head_resident_id !== undefined && rec.headResidentId === undefined) rec.headResidentId = rec.head_resident_id;
    if (rec.structure_type !== undefined && rec.structureType === undefined) rec.structureType = rec.structure_type;
    if (rec.tenure_status !== undefined && rec.tenureStatus === undefined) rec.tenureStatus = rec.tenure_status;
    if (rec.water_source !== undefined && rec.waterSource === undefined) rec.waterSource = rec.water_source;
    if (rec.toilet_facility !== undefined && rec.toiletFacility === undefined) rec.toiletFacility = rec.toilet_facility;
    if (rec.power_source !== undefined && rec.powerSource === undefined) rec.powerSource = rec.power_source;
    if (rec.monthly_income !== undefined && rec.monthlyIncome === undefined) rec.monthlyIncome = rec.monthly_income;
    if (rec.hazard_risk !== undefined && rec.hazardRisk === undefined) rec.hazardRisk = rec.hazard_risk;

    // Certificates
    if (rec.tracking_no !== undefined && rec.trackingNo === undefined) rec.trackingNo = rec.tracking_no;
    if (rec.resident_id !== undefined && rec.residentId === undefined) rec.residentId = rec.resident_id;
    if (rec.cert_type !== undefined && rec.certType === undefined) rec.certType = rec.cert_type;
    if (rec.or_no !== undefined && rec.orNo === undefined) rec.orNo = rec.or_no;
    if (rec.amount_paid !== undefined && rec.amountPaid === undefined) rec.amountPaid = rec.amount_paid;
    if (rec.is_waived !== undefined && rec.isWaived === undefined) rec.isWaived = Boolean(rec.is_waived);
    if (rec.issued_by !== undefined && rec.issuedBy === undefined) rec.issuedBy = rec.issued_by;
    if (rec.issued_at !== undefined && rec.issuedAt === undefined) rec.issuedAt = rec.issued_at;

    // Blotter
    if (rec.case_no !== undefined && rec.caseNo === undefined) rec.caseNo = rec.case_no;
    if (rec.incident_type !== undefined && rec.incidentType === undefined) rec.incidentType = rec.incident_type;
    if (rec.incident_date !== undefined && rec.incidentDate === undefined) rec.incidentDate = rec.incident_date;
    if (rec.hearing_date !== undefined && rec.hearingDate === undefined) rec.hearingDate = rec.hearing_date;
    if (rec.complainant_name !== undefined && rec.complainantName === undefined) rec.complainantName = rec.complainant_name;
    if (rec.respondent_name !== undefined && rec.respondentName === undefined) rec.respondentName = rec.respondent_name;

    // Incidents
    if (rec.incident_no !== undefined && rec.incidentNo === undefined) rec.incidentNo = rec.incident_no;
    if (rec.caller_name !== undefined && rec.callerName === undefined) rec.callerName = rec.caller_name;
    if (rec.caller_contact !== undefined && rec.callerContact === undefined) rec.callerContact = rec.caller_contact;
    if (rec.responder_name !== undefined && rec.responderName === undefined) rec.responderName = rec.responder_name;
    if (rec.vehicle_unit !== undefined && rec.vehicleUnit === undefined) rec.vehicleUnit = rec.vehicle_unit;
    if (rec.minor_age !== undefined && rec.minorAge === undefined) rec.minorAge = rec.minor_age;
    if (rec.guardian_name !== undefined && rec.guardianName === undefined) rec.guardianName = rec.guardian_name;
    if (rec.guardian_contact !== undefined && rec.guardianContact === undefined) rec.guardianContact = rec.guardian_contact;
    if (rec.reported_at !== undefined && rec.reportedAt === undefined) rec.reportedAt = rec.reported_at;
    if (rec.on_scene_at !== undefined && rec.onSceneAt === undefined) rec.onSceneAt = rec.on_scene_at;
    if (rec.resolved_at !== undefined && rec.resolvedAt === undefined) rec.resolvedAt = rec.resolved_at;
    if (rec.response_minutes !== undefined && rec.responseMinutes === undefined) rec.responseMinutes = rec.response_minutes;

    // Officials
    if (rec.full_name !== undefined && rec.fullName === undefined) rec.fullName = rec.full_name;
    if (rec.term_start !== undefined && rec.termStart === undefined) rec.termStart = rec.term_start;
    if (rec.term_end !== undefined && rec.termEnd === undefined) rec.termEnd = rec.term_end;
    if (rec.rank_order !== undefined && rec.rankOrder === undefined) rec.rankOrder = rec.rank_order;
    if (rec.contact_no !== undefined && rec.phone === undefined) rec.phone = rec.contact_no;

    // Notifications
    if (rec.dispatch_code !== undefined && rec.dispatchCode === undefined) rec.dispatchCode = rec.dispatch_code;
    if (rec.recipient_id !== undefined && rec.recipientId === undefined) rec.recipientId = rec.recipient_id;
    if (rec.recipient_name !== undefined && rec.recipientName === undefined) rec.recipientName = rec.recipient_name;
    if (rec.recipient_contact !== undefined && rec.recipientContact === undefined) rec.recipientContact = rec.recipient_contact;
    if (rec.cost_credits !== undefined && rec.costCredits === undefined) rec.costCredits = rec.cost_credits;
    if (rec.gateway_ref !== undefined && rec.gatewayRef === undefined) rec.gatewayRef = rec.gateway_ref;
    if (rec.error_message !== undefined && rec.errorMessage === undefined) rec.errorMessage = rec.error_message;
    if (rec.dispatched_at !== undefined && rec.dispatchedAt === undefined) {
      rec.dispatchedAt = rec.dispatched_at;
      rec.createdAt = rec.dispatched_at;
    }

    // Health Records
    if (rec.record_no !== undefined && rec.recordNo === undefined) rec.recordNo = rec.record_no;
    if (rec.resident_id !== undefined && rec.residentId === undefined) rec.residentId = rec.resident_id;
    if (rec.service_type !== undefined && rec.serviceType === undefined) rec.serviceType = rec.service_type;
    if (rec.weight_kg !== undefined && rec.weightKg === undefined) rec.weightKg = rec.weight_kg;
    if (rec.height_cm !== undefined && rec.heightCm === undefined) rec.heightCm = rec.height_cm;
    if (rec.pulse_rate !== undefined && rec.pulseRate === undefined) rec.pulseRate = rec.pulse_rate;
    if (rec.chief_complaint !== undefined && rec.chiefComplaint === undefined) rec.chiefComplaint = rec.chief_complaint;
    if (rec.clinical_notes !== undefined && rec.clinicalNotes === undefined) rec.clinicalNotes = rec.clinical_notes;
    if (rec.medicines_dispensed !== undefined && rec.medicinesDispensed === undefined) rec.medicinesDispensed = rec.medicines_dispensed;
    if (rec.attending_staff !== undefined && rec.attendingStaff === undefined) rec.attendingStaff = rec.attending_staff;
    if (rec.referral_target !== undefined && rec.referralTarget === undefined) rec.referralTarget = rec.referral_target;
    if (rec.follow_up_date !== undefined && rec.followUpDate === undefined) rec.followUpDate = rec.follow_up_date;

    // Health Medicines
    if (rec.medicine_name !== undefined && rec.medicineName === undefined) rec.medicineName = rec.medicine_name;
    if (rec.generic_name !== undefined && rec.genericName === undefined) rec.genericName = rec.generic_name;
    if (rec.stock_quantity !== undefined && rec.stockQuantity === undefined) rec.stockQuantity = rec.stock_quantity;
    if (rec.reorder_level !== undefined && rec.reorderLevel === undefined) rec.reorderLevel = rec.reorder_level;
    if (rec.expiry_date !== undefined && rec.expiryDate === undefined) rec.expiryDate = rec.expiry_date;
    if (rec.batch_no !== undefined && rec.batchNo === undefined) rec.batchNo = rec.batch_no;

    return rec;
  }

  // Prepares outgoing JS object into backend snake_case parameters
  function denormalizeRecord(rec) {
    if (!rec || typeof rec !== 'object') return rec;
    const out = { ...rec };

    if (out.firstName !== undefined && out.first_name === undefined) out.first_name = out.firstName;
    if (out.lastName !== undefined && out.last_name === undefined) out.last_name = out.lastName;
    if (out.middleName !== undefined && out.middle_name === undefined) out.middle_name = out.middleName;
    if (out.phone !== undefined && out.contact_no === undefined) out.contact_no = out.phone;
    if (out.address !== undefined && out.street === undefined) out.street = out.address;
    if (out.emergencyContact !== undefined && out.emergency_contact === undefined) out.emergency_contact = out.emergencyContact;
    if (out.emergencyName !== undefined && out.emergency_name === undefined) out.emergency_name = out.emergencyName;
    if (out.isVoter !== undefined && out.voter_status === undefined) out.voter_status = out.isVoter ? 'Registered' : 'Unregistered';
    if (out.isFourPs !== undefined && out.is_4ps === undefined) out.is_4ps = out.isFourPs ? 1 : 0;

    if (out.headResidentId !== undefined && out.head_resident_id === undefined) out.head_resident_id = out.headResidentId;
    if (out.structureType !== undefined && out.structure_type === undefined) out.structure_type = out.structureType;
    if (out.tenureStatus !== undefined && out.tenure_status === undefined) out.tenure_status = out.tenureStatus;
    if (out.waterSource !== undefined && out.water_source === undefined) out.water_source = out.waterSource;
    if (out.toiletFacility !== undefined && out.toilet_facility === undefined) out.toilet_facility = out.toiletFacility;
    if (out.powerSource !== undefined && out.power_source === undefined) out.power_source = out.powerSource;
    if (out.monthlyIncome !== undefined && out.monthly_income === undefined) out.monthly_income = out.monthlyIncome;
    if (out.hazardRisk !== undefined && out.hazard_risk === undefined) out.hazard_risk = out.hazardRisk;

    if (out.residentId !== undefined && out.resident_id === undefined) out.resident_id = out.residentId;
    if (out.certType !== undefined && out.cert_type === undefined) out.cert_type = out.certType;
    if (out.orNo !== undefined && out.or_no === undefined) out.or_no = out.orNo;
    if (out.amountPaid !== undefined && out.amount_paid === undefined) out.amount_paid = out.amountPaid;
    if (out.isWaived !== undefined && out.is_waived === undefined) out.is_waived = out.isWaived ? 1 : 0;
    if (out.issuedBy !== undefined && out.issued_by === undefined) out.issued_by = out.issuedBy;

    if (out.incidentType !== undefined && out.incident_type === undefined) out.incident_type = out.incidentType;
    if (out.incidentDate !== undefined && out.incident_date === undefined) out.incident_date = out.incidentDate;
    if (out.complainantName !== undefined && out.complainant_name === undefined) out.complainant_name = out.complainantName;
    if (out.respondentName !== undefined && out.respondent_name === undefined) out.respondent_name = out.respondentName;

    if (out.callerName !== undefined && out.caller_name === undefined) out.caller_name = out.callerName;
    if (out.callerContact !== undefined && out.caller_contact === undefined) out.caller_contact = out.callerContact;
    if (out.responderName !== undefined && out.responder_name === undefined) out.responder_name = out.responderName;
    if (out.vehicleUnit !== undefined && out.vehicle_unit === undefined) out.vehicle_unit = out.vehicleUnit;
    if (out.minorAge !== undefined && out.minor_age === undefined) out.minor_age = out.minorAge;
    if (out.guardianName !== undefined && out.guardian_name === undefined) out.guardian_name = out.guardianName;
    if (out.guardianContact !== undefined && out.guardian_contact === undefined) out.guardian_contact = out.guardianContact;

    if (out.fullName !== undefined && out.full_name === undefined) out.full_name = out.fullName;
    if (out.termStart !== undefined && out.term_start === undefined) out.term_start = out.termStart;
    if (out.termEnd !== undefined && out.term_end === undefined) out.term_end = out.termEnd;
    if (out.rankOrder !== undefined && out.rank_order === undefined) out.rank_order = out.rankOrder;

    // Notifications
    if (out.dispatchCode !== undefined && out.dispatch_code === undefined) out.dispatch_code = out.dispatchCode;
    if (out.recipientId !== undefined && out.recipient_id === undefined) out.recipient_id = out.recipientId;
    if (out.recipientName !== undefined && out.recipient_name === undefined) out.recipient_name = out.recipientName;
    if (out.recipientContact !== undefined && out.recipient_contact === undefined) out.recipient_contact = out.recipientContact;
    if (out.costCredits !== undefined && out.cost_credits === undefined) out.cost_credits = out.costCredits;
    if (out.gatewayRef !== undefined && out.gateway_ref === undefined) out.gateway_ref = out.gatewayRef;
    if (out.errorMessage !== undefined && out.error_message === undefined) out.error_message = out.errorMessage;
    if (out.dispatchedAt !== undefined && out.dispatched_at === undefined) out.dispatched_at = out.dispatchedAt;

    // Health Records
    if (out.recordNo !== undefined && out.record_no === undefined) out.record_no = out.recordNo;
    if (out.residentId !== undefined && out.resident_id === undefined) out.resident_id = out.residentId;
    if (out.serviceType !== undefined && out.service_type === undefined) out.service_type = out.serviceType;
    if (out.weightKg !== undefined && out.weight_kg === undefined) out.weight_kg = out.weightKg;
    if (out.heightCm !== undefined && out.height_cm === undefined) out.height_cm = out.heightCm;
    if (out.pulseRate !== undefined && out.pulse_rate === undefined) out.pulse_rate = out.pulseRate;
    if (out.chiefComplaint !== undefined && out.chief_complaint === undefined) out.chief_complaint = out.chiefComplaint;
    if (out.clinicalNotes !== undefined && out.clinical_notes === undefined) out.clinical_notes = out.clinicalNotes;
    if (out.medicinesDispensed !== undefined && out.medicines_dispensed === undefined) out.medicines_dispensed = out.medicinesDispensed;
    if (out.attendingStaff !== undefined && out.attending_staff === undefined) out.attending_staff = out.attendingStaff;
    if (out.referralTarget !== undefined && out.referral_target === undefined) out.referral_target = out.referralTarget;
    if (out.followUpDate !== undefined && out.follow_up_date === undefined) out.follow_up_date = out.followUpDate;

    // Health Medicines
    if (out.medicineName !== undefined && out.medicine_name === undefined) out.medicine_name = out.medicineName;
    if (out.genericName !== undefined && out.generic_name === undefined) out.generic_name = out.genericName;
    if (out.stockQuantity !== undefined && out.stock_quantity === undefined) out.stock_quantity = out.stockQuantity;
    if (out.reorderLevel !== undefined && out.reorder_level === undefined) out.reorder_level = out.reorderLevel;
    if (out.expiryDate !== undefined && out.expiry_date === undefined) out.expiry_date = out.expiryDate;
    if (out.batchNo !== undefined && out.batch_no === undefined) out.batch_no = out.batchNo;

    return out;
  }

  // Helper for fetch with JSON headers
  async function apiRequest(endpoint, method = 'GET', data = null) {
    const options = {
      method: method,
      headers: {
        'Accept': 'application/json'
      }
    };

    if (data !== null) {
      options.headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(denormalizeRecord(data));
    }

    try {
      const response = await fetch(endpoint, options);
      const json = await response.json();

      if (!response.ok) {
        if (response.status === 401 && !endpoint.includes('auth.php')) {
          window.location.href = 'login.php';
        }
        throw new Error(json.message || 'API request failed');
      }

      if (Array.isArray(json.data)) {
        return json.data.map(normalizeRecord);
      } else if (json.data && typeof json.data === 'object') {
        return normalizeRecord(json.data);
      }

      return json.data;
    } catch (err) {
      console.error(`API Error [${endpoint}]:`, err);
      throw err;
    }
  }

  // Store to endpoint mapping
  const storeEndpoints = {
    residents:    'api/residents.php',
    resident_ids: 'api/resident_ids.php',
    households:   'api/households.php',
    certificates: 'api/certificates.php',
    blotter_cases:'api/blotter.php',
    incidents:    'api/incidents.php',
    officials:    'api/officials.php',
    audit_logs:   'api/settings.php?action=audit_logs',
    users:        'api/auth.php?action=users',
    geo_profiling:'api/geo_profiling.php',
    notifications:'api/notifications.php',
    health_records:'api/health.php',
    health_medicines:'api/health.php?action=medicines'
  };

  // Barangay DB Client
  const barangayDB = {
    async init() {
      try {
        await apiRequest('api/settings.php');
        return true;
      } catch (e) {
        return false;
      }
    },

    async getAll(storeName) {
      const endpoint = storeEndpoints[storeName];
      if (!endpoint) return [];
      return await apiRequest(endpoint);
    },

    async get(storeName, id) {
      const endpoint = storeEndpoints[storeName];
      if (!endpoint) return null;
      return await apiRequest(`${endpoint}${endpoint.includes('?') ? '&' : '?'}action=get&id=${encodeURIComponent(id)}`);
    },

    async add(storeName, record) {
      const endpoint = storeEndpoints[storeName];
      if (!endpoint) throw new Error(`Unknown store: ${storeName}`);

      let action = 'create';
      if (storeName === 'certificates') action = 'issue';
      if (storeName === 'resident_ids') action = 'issue';
      if (storeName === 'health_medicines') action = 'create_medicine';
      if (storeName === 'incidents' && (record.type === 'Curfew Violation' || record.minor_age)) {
        action = 'create_curfew';
      }

      const url = `${endpoint.split('?')[0]}?action=${action}`;
      const res = await apiRequest(url, 'POST', record);
      return res.id || res;
    },

    async update(storeName, record) {
      const endpoint = storeEndpoints[storeName];
      if (!endpoint) throw new Error(`Unknown store: ${storeName}`);

      let action = 'update';
      if (storeName === 'health_medicines') action = 'update_medicine';

      const url = `${endpoint.split('?')[0]}?action=${action}`;
      return await apiRequest(url, 'POST', record);
    },

    async put(storeName, record) {
      return await this.update(storeName, record);
    },

    async delete(storeName, id) {
      const endpoint = storeEndpoints[storeName];
      if (!endpoint) throw new Error(`Unknown store: ${storeName}`);

      const url = `${endpoint.split('?')[0]}?action=delete`;
      return await apiRequest(url, 'POST', { id: id });
    },

    async count(storeName) {
      try {
        const endpoint = storeEndpoints[storeName];
        if (!endpoint) return 0;
        const stats = await apiRequest(`${endpoint.split('?')[0]}?action=stats`);
        return stats.total !== undefined ? stats.total : (Array.isArray(stats) ? stats.length : 0);
      } catch (e) {
        try {
          const all = await this.getAll(storeName);
          return all.length;
        } catch {
          return 0;
        }
      }
    },

    async getSetting(key) {
      const all = await apiRequest('api/settings.php');
      return all ? all[key] : null;
    },

    async saveSetting(key, value) {
      return await apiRequest('api/settings.php?action=save', 'POST', { [key]: value });
    },

    async clear(storeName) {
      return await apiRequest('api/settings.php?action=purge', 'POST', { target: storeName });
    },

    async logAudit(action, entity, details) {
      try {
        return await apiRequest('api/settings.php?action=audit_logs');
      } catch (e) {
        return null;
      }
    }
  };

  // Barangay Auth Client
  const barangayAuth = {
    async login(username, password) {
      const res = await apiRequest('api/auth.php?action=login', 'POST', { username, password });
      if (res && res.user) {
        sessionStorage.setItem('barangay_user', JSON.stringify(res.user));
      }
      return res;
    },

    async register(userData) {
      const res = await apiRequest('api/auth.php?action=register', 'POST', userData);
      if (res && res.user) {
        sessionStorage.setItem('barangay_user', JSON.stringify(res.user));
      }
      return res;
    },

    async logout() {
      sessionStorage.removeItem('barangay_user');
      try {
        await apiRequest('api/auth.php?action=logout', 'POST');
      } catch (e) {}
      window.location.href = 'login.php';
    },

    getUser() {
      const stored = sessionStorage.getItem('barangay_user');
      return stored ? JSON.parse(stored) : null;
    },

    async checkSession() {
      try {
        const res = await apiRequest('api/auth.php?action=session');
        if (res && res.user) {
          sessionStorage.setItem('barangay_user', JSON.stringify(res.user));
          return res.user;
        }
      } catch (e) {
        sessionStorage.removeItem('barangay_user');
      }
      return null;
    },

    async requireAuth(redirectUrl = 'login.php') {
      const user = await this.checkSession();
      if (!user) {
        window.location.href = redirectUrl;
        return null;
      }
      return { user };
    },

    async checkFirstRun() {
      return await apiRequest('api/auth.php?action=check_first_run');
    }
  };

  // Backwards compatibility for window.authService
  window.authService = {
    async getCurrentSession() {
      const user = await barangayAuth.checkSession();
      return user ? { user } : null;
    },
    async requireAuth(url = 'login.php') {
      return await barangayAuth.requireAuth(url);
    },
    async logout() {
      return await barangayAuth.logout();
    },
    async logAudit(action, details) {
      return await barangayDB.logAudit(action, 'system', details);
    },
    async hasAdminAccount() {
      const fr = await barangayAuth.checkFirstRun();
      return fr && fr.has_admin;
    }
  };

  window.barangayDB = barangayDB;
  window.barangayAuth = barangayAuth;

  // DB status check
  document.addEventListener('DOMContentLoaded', () => {
    const liveIndicator = document.getElementById('db-status-pill');
    if (liveIndicator) {
      barangayDB.init().then(ok => {
        if (ok) {
          liveIndicator.innerHTML = '<div style="width:5px; height:5px; border-radius:50%; background:#10b981;"></div> <span style="font-size:0.625rem; color:var(--color-text-muted);">MySQL Connected</span>';
        } else {
          liveIndicator.innerHTML = '<div style="width:5px; height:5px; border-radius:50%; background:#ef4444;"></div> <span style="font-size:0.625rem; color:var(--color-text-muted);">DB Offline</span>';
        }
      });
    }
  });

})();
