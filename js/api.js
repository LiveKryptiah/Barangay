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

    // Procurement & BAC Projects
    if (rec.pr_number !== undefined && rec.prNumber === undefined) rec.prNumber = rec.pr_number;
    if (rec.po_number !== undefined && rec.poNumber === undefined) rec.poNumber = rec.po_number;
    if (rec.project_title !== undefined && rec.projectTitle === undefined) rec.projectTitle = rec.project_title;
    if (rec.procurement_mode !== undefined && rec.procurementMode === undefined) rec.procurementMode = rec.procurement_mode;
    if (rec.fund_source !== undefined && rec.fundSource === undefined) rec.fundSource = rec.fund_source;
    if (rec.budget_allocation_id !== undefined && rec.budgetAllocationId === undefined) rec.budgetAllocationId = rec.budget_allocation_id;
    if (rec.abc_amount !== undefined && rec.abcAmount === undefined) rec.abcAmount = rec.abc_amount;
    if (rec.philgeps_ref !== undefined && rec.philgepsRef === undefined) rec.philgepsRef = rec.philgeps_ref;
    if (rec.end_user_committee !== undefined && rec.endUserCommittee === undefined) rec.endUserCommittee = rec.end_user_committee;
    if (rec.winning_bidder !== undefined && rec.winningBidder === undefined) rec.winningBidder = rec.winning_bidder;
    if (rec.winning_amount !== undefined && rec.winningAmount === undefined) rec.winningAmount = rec.winning_amount;
    if (rec.date_awarded !== undefined && rec.dateAwarded === undefined) rec.dateAwarded = rec.date_awarded;
    if (rec.target_delivery_days !== undefined && rec.targetDeliveryDays === undefined) rec.targetDeliveryDays = rec.target_delivery_days;

    // Budget Allocations (AIP)
    if (rec.fiscal_year !== undefined && rec.fiscalYear === undefined) rec.fiscalYear = rec.fiscal_year;
    if (rec.program_title !== undefined && rec.programTitle === undefined) rec.programTitle = rec.program_title;
    if (rec.implementing_committee !== undefined && rec.implementingCommittee === undefined) rec.implementingCommittee = rec.implementing_committee;
    if (rec.approved_budget !== undefined && rec.approvedBudget === undefined) rec.approvedBudget = rec.approved_budget;
    if (rec.obligated_amount !== undefined && rec.obligatedAmount === undefined) rec.obligatedAmount = rec.obligated_amount;

    // Procurement Bids
    if (rec.project_id !== undefined && rec.projectId === undefined) rec.projectId = rec.project_id;
    if (rec.supplier_name !== undefined && rec.supplierName === undefined) rec.supplierName = rec.supplier_name;
    if (rec.tin_number !== undefined && rec.tinNumber === undefined) rec.tinNumber = rec.tin_number;
    if (rec.contact_person !== undefined && rec.contactPerson === undefined) rec.contactPerson = rec.contact_person;
    if (rec.quotation_amount !== undefined && rec.quotationAmount === undefined) rec.quotationAmount = rec.quotation_amount;
    if (rec.compliance_status !== undefined && rec.complianceStatus === undefined) rec.complianceStatus = rec.compliance_status;
    if (rec.canvassed_at !== undefined && rec.canvassedAt === undefined) rec.canvassedAt = rec.canvassed_at;

    // Legislative Documents (Ordinances & Resolutions)
    if (rec.control_number !== undefined && rec.controlNumber === undefined) rec.controlNumber = rec.control_number;
    if (rec.doc_type !== undefined && rec.docType === undefined) rec.docType = rec.doc_type;
    if (rec.sponsor_name !== undefined && rec.sponsorName === undefined) rec.sponsorName = rec.sponsor_name;
    if (rec.co_sponsors !== undefined && rec.coSponsors === undefined) rec.coSponsors = rec.co_sponsors;
    if (rec.reading_stage !== undefined && rec.readingStage === undefined) rec.readingStage = rec.reading_stage;
    if (rec.date_enacted !== undefined && rec.dateEnacted === undefined) rec.dateEnacted = rec.date_enacted;
    if (rec.date_posted !== undefined && rec.datePosted === undefined) rec.datePosted = rec.date_posted;
    if (rec.effectivity_date !== undefined && rec.effectivityDate === undefined) rec.effectivityDate = rec.effectivity_date;
    if (rec.posting_locations !== undefined && rec.postingLocations === undefined) rec.postingLocations = rec.posting_locations;
    if (rec.city_council_review_status !== undefined && rec.cityCouncilReviewStatus === undefined) rec.cityCouncilReviewStatus = rec.city_council_review_status;
    if (rec.city_council_transmitted_date !== undefined && rec.cityCouncilTransmittedDate === undefined) rec.cityCouncilTransmittedDate = rec.city_council_transmitted_date;
    if (rec.city_council_action_date !== undefined && rec.cityCouncilActionDate === undefined) rec.cityCouncilActionDate = rec.city_council_action_date;
    if (rec.sanctions_penalties !== undefined && rec.sanctionsPenalties === undefined) rec.sanctionsPenalties = rec.sanctions_penalties;
    if (rec.document_body !== undefined && rec.documentBody === undefined) rec.documentBody = rec.document_body;

    // Legislative Sessions (Minutes & Journals)
    if (rec.session_number !== undefined && rec.sessionNumber === undefined) rec.sessionNumber = rec.session_number;
    if (rec.session_type !== undefined && rec.sessionType === undefined) rec.sessionType = rec.session_type;
    if (rec.session_date !== undefined && rec.sessionDate === undefined) rec.sessionDate = rec.session_date;
    if (rec.session_time !== undefined && rec.sessionTime === undefined) rec.sessionTime = rec.session_time;
    if (rec.presiding_officer !== undefined && rec.presidingOfficer === undefined) rec.presidingOfficer = rec.presiding_officer;
    if (rec.quorum_status !== undefined && rec.quorumStatus === undefined) rec.quorumStatus = rec.quorum_status;
    if (rec.present_count !== undefined && rec.presentCount === undefined) rec.presentCount = rec.present_count;
    if (rec.total_members !== undefined && rec.totalMembers === undefined) rec.totalMembers = rec.total_members;
    if (rec.roll_call !== undefined && rec.rollCall === undefined) rec.rollCall = rec.roll_call;
    if (rec.agenda_topics !== undefined && rec.agendaTopics === undefined) rec.agendaTopics = rec.agenda_topics;
    if (rec.minutes_summary !== undefined && rec.minutesSummary === undefined) rec.minutesSummary = rec.minutes_summary;
    if (rec.session_status !== undefined && rec.sessionStatus === undefined) rec.sessionStatus = rec.session_status;

    // Lupon Members (RA 7160 Sec. 399)
    if (rec.committee_assignment !== undefined && rec.committeeAssignment === undefined) rec.committeeAssignment = rec.committee_assignment;
    if (rec.profession_background !== undefined && rec.professionBackground === undefined) rec.professionBackground = rec.profession_background;
    if (rec.appointment_date !== undefined && rec.appointmentDate === undefined) rec.appointmentDate = rec.appointment_date;
    if (rec.oath_date !== undefined && rec.oathDate === undefined) rec.oathDate = rec.oath_date;
    if (rec.cases_handled_count !== undefined && rec.casesHandledCount === undefined) rec.casesHandledCount = rec.cases_handled_count;

    // Lupon Cases (Katarungang Pambarangay)
    if (rec.case_number !== undefined && rec.caseNumber === undefined) rec.caseNumber = rec.case_number;
    if (rec.blotter_case_id !== undefined && rec.blotterCaseId === undefined) rec.blotterCaseId = rec.blotter_case_id;
    if (rec.complainant_address !== undefined && rec.complainantAddress === undefined) rec.complainantAddress = rec.complainant_address;
    if (rec.complainant_contact !== undefined && rec.complainantContact === undefined) rec.complainantContact = rec.complainant_contact;
    if (rec.respondent_address !== undefined && rec.respondentAddress === undefined) rec.respondentAddress = rec.respondent_address;
    if (rec.respondent_contact !== undefined && rec.respondentContact === undefined) rec.respondentContact = rec.respondent_contact;
    if (rec.dispute_type !== undefined && rec.disputeType === undefined) rec.disputeType = rec.dispute_type;
    if (rec.complaint_details !== undefined && rec.complaintDetails === undefined) rec.complaintDetails = rec.complaint_details;
    if (rec.relief_sought !== undefined && rec.reliefSought === undefined) rec.reliefSought = rec.relief_sought;
    if (rec.date_filed !== undefined && rec.dateFiled === undefined) rec.dateFiled = rec.date_filed;
    if (rec.pangkat_chairman !== undefined && rec.pangkatChairman === undefined) rec.pangkatChairman = rec.pangkat_chairman;
    if (rec.pangkat_secretary !== undefined && rec.pangkatSecretary === undefined) rec.pangkatSecretary = rec.pangkat_secretary;
    if (rec.pangkat_member !== undefined && rec.pangkatMember === undefined) rec.pangkatMember = rec.pangkat_member;
    if (rec.pb_deadline !== undefined && rec.pbDeadline === undefined) rec.pbDeadline = rec.pb_deadline;
    if (rec.pangkat_deadline !== undefined && rec.pangkatDeadline === undefined) rec.pangkatDeadline = rec.pangkat_deadline;
    if (rec.settlement_terms !== undefined && rec.settlementTerms === undefined) rec.settlementTerms = rec.settlement_terms;
    if (rec.settlement_date !== undefined && rec.settlementDate === undefined) rec.settlementDate = rec.settlement_date;
    if (rec.settlement_amount !== undefined && rec.settlementAmount === undefined) rec.settlementAmount = rec.settlement_amount;
    if (rec.compliance_due_date !== undefined && rec.complianceDueDate === undefined) rec.complianceDueDate = rec.compliance_due_date;
    if (rec.cfa_reason !== undefined && rec.cfaReason === undefined) rec.cfaReason = rec.cfa_reason;
    if (rec.cfa_date !== undefined && rec.cfaDate === undefined) rec.cfaDate = rec.cfa_date;

    // Lupon Hearings
    if (rec.case_id !== undefined && rec.caseId === undefined) rec.caseId = rec.case_id;
    if (rec.hearing_number !== undefined && rec.hearingNumber === undefined) rec.hearingNumber = rec.hearing_number;
    if (rec.hearing_type !== undefined && rec.hearingType === undefined) rec.hearingType = rec.hearing_type;
    if (rec.scheduled_date !== undefined && rec.scheduledDate === undefined) rec.scheduledDate = rec.scheduled_date;
    if (rec.scheduled_time !== undefined && rec.scheduledTime === undefined) rec.scheduledTime = rec.scheduled_time;
    if (rec.complainant_present !== undefined && rec.complainantPresent === undefined) rec.complainantPresent = Boolean(rec.complainant_present);
    if (rec.respondent_present !== undefined && rec.respondentPresent === undefined) rec.respondentPresent = Boolean(rec.respondent_present);
    if (rec.proceedings_summary !== undefined && rec.proceedingsSummary === undefined) rec.proceedingsSummary = rec.proceedings_summary;
    if (rec.next_action !== undefined && rec.nextAction === undefined) rec.nextAction = rec.next_action;

    // Disbursement Vouchers
    if (rec.dv_number !== undefined && rec.dvNumber === undefined) rec.dvNumber = rec.dv_number;
    if (rec.payee_name !== undefined && rec.payeeName === undefined) rec.payeeName = rec.payee_name;
    if (rec.check_no !== undefined && rec.checkNo === undefined) rec.checkNo = rec.check_no;
    if (rec.check_date !== undefined && rec.checkDate === undefined) rec.checkDate = rec.check_date;
    if (rec.bank_name !== undefined && rec.bankName === undefined) rec.bankName = rec.bank_name;
    if (rec.expense_class !== undefined && rec.expenseClass === undefined) rec.expenseClass = rec.expense_class;
    if (rec.certified_by !== undefined && rec.certifiedBy === undefined) rec.certifiedBy = rec.certified_by;
    if (rec.approved_by !== undefined && rec.approvedBy === undefined) rec.approvedBy = rec.approved_by;
    if (rec.released_at !== undefined && rec.releasedAt === undefined) rec.releasedAt = rec.released_at;

    // Revenue Collections
    if (rec.or_number !== undefined && rec.orNumber === undefined) rec.orNumber = rec.or_number;
    if (rec.rcd_number !== undefined && rec.rcdNumber === undefined) rec.rcdNumber = rec.rcd_number;
    if (rec.payer_name !== undefined && rec.payerName === undefined) rec.payerName = rec.payer_name;
    if (rec.revenue_source !== undefined && rec.revenueSource === undefined) rec.revenueSource = rec.revenue_source;
    if (rec.fund_destination !== undefined && rec.fundDestination === undefined) rec.fundDestination = rec.fund_destination;
    if (rec.collected_by !== undefined && rec.collectedBy === undefined) rec.collectedBy = rec.collected_by;
    if (rec.receipt_date !== undefined && rec.receiptDate === undefined) rec.receiptDate = rec.receipt_date;
    if (rec.deposit_date !== undefined && rec.depositDate === undefined) rec.depositDate = rec.deposit_date;
    if (rec.deposit_bank !== undefined && rec.depositBank === undefined) rec.depositBank = rec.deposit_bank;
    if (rec.deposit_slip_no !== undefined && rec.depositSlipNo === undefined) rec.depositSlipNo = rec.deposit_slip_no;

    // Budget Obligations
    if (rec.obr_number !== undefined && rec.obrNumber === undefined) rec.obrNumber = rec.obr_number;
    if (rec.obligation_type !== undefined && rec.obligationType === undefined) rec.obligationType = rec.obligation_type;
    if (rec.obligee_name !== undefined && rec.obligeeName === undefined) rec.obligeeName = rec.obligee_name;
    if (rec.date_obligated !== undefined && rec.dateObligated === undefined) rec.dateObligated = rec.date_obligated;

    // Financial Reports
    if (rec.report_code !== undefined && rec.reportCode === undefined) rec.reportCode = rec.report_code;
    if (rec.report_type !== undefined && rec.reportType === undefined) rec.reportType = rec.report_type;
    if (rec.period_label !== undefined && rec.periodLabel === undefined) rec.periodLabel = rec.period_label;
    if (rec.total_receipts !== undefined && rec.totalReceipts === undefined) rec.totalReceipts = rec.total_receipts;
    if (rec.total_expenditures !== undefined && rec.totalExpenditures === undefined) rec.totalExpenditures = rec.total_expenditures;
    if (rec.net_balance !== undefined && rec.netBalance === undefined) rec.netBalance = rec.net_balance;
    if (rec.report_data !== undefined && rec.reportData === undefined) rec.reportData = rec.report_data;
    if (rec.generated_by !== undefined && rec.generatedBy === undefined) rec.generatedBy = rec.generated_by;

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

    // Procurement & BAC Projects
    if (out.prNumber !== undefined && out.pr_number === undefined) out.pr_number = out.prNumber;
    if (out.poNumber !== undefined && out.po_number === undefined) out.po_number = out.poNumber;
    if (out.projectTitle !== undefined && out.project_title === undefined) out.project_title = out.projectTitle;
    if (out.procurementMode !== undefined && out.procurement_mode === undefined) out.procurement_mode = out.procurementMode;
    if (out.fundSource !== undefined && out.fund_source === undefined) out.fund_source = out.fundSource;
    if (out.budgetAllocationId !== undefined && out.budget_allocation_id === undefined) out.budget_allocation_id = out.budgetAllocationId;
    if (out.abcAmount !== undefined && out.abc_amount === undefined) out.abc_amount = out.abcAmount;
    if (out.philgepsRef !== undefined && out.philgeps_ref === undefined) out.philgeps_ref = out.philgepsRef;
    if (out.endUserCommittee !== undefined && out.end_user_committee === undefined) out.end_user_committee = out.endUserCommittee;
    if (out.winningBidder !== undefined && out.winning_bidder === undefined) out.winning_bidder = out.winningBidder;
    if (out.winningAmount !== undefined && out.winning_amount === undefined) out.winning_amount = out.winningAmount;
    if (out.dateAwarded !== undefined && out.date_awarded === undefined) out.date_awarded = out.dateAwarded;
    if (out.targetDeliveryDays !== undefined && out.target_delivery_days === undefined) out.target_delivery_days = out.targetDeliveryDays;

    // Budget Allocations (AIP)
    if (out.fiscalYear !== undefined && out.fiscal_year === undefined) out.fiscal_year = out.fiscalYear;
    if (out.programTitle !== undefined && out.program_title === undefined) out.program_title = out.programTitle;
    if (out.implementingCommittee !== undefined && out.implementing_committee === undefined) out.implementing_committee = out.implementingCommittee;
    if (out.approvedBudget !== undefined && out.approved_budget === undefined) out.approved_budget = out.approvedBudget;
    if (out.obligatedAmount !== undefined && out.obligated_amount === undefined) out.obligated_amount = out.obligatedAmount;

    // Procurement Bids
    if (out.projectId !== undefined && out.project_id === undefined) out.project_id = out.projectId;
    if (out.supplierName !== undefined && out.supplier_name === undefined) out.supplier_name = out.supplierName;
    if (out.tinNumber !== undefined && out.tin_number === undefined) out.tin_number = out.tinNumber;
    if (out.contactPerson !== undefined && out.contact_person === undefined) out.contact_person = out.contactPerson;
    if (out.quotationAmount !== undefined && out.quotation_amount === undefined) out.quotation_amount = out.quotationAmount;
    if (out.complianceStatus !== undefined && out.compliance_status === undefined) out.compliance_status = out.complianceStatus;
    if (out.canvassedAt !== undefined && out.canvassed_at === undefined) out.canvassed_at = out.canvassedAt;

    // Legislative Documents
    if (out.controlNumber !== undefined && out.control_number === undefined) out.control_number = out.controlNumber;
    if (out.docType !== undefined && out.doc_type === undefined) out.doc_type = out.docType;
    if (out.sponsorName !== undefined && out.sponsor_name === undefined) out.sponsor_name = out.sponsorName;
    if (out.coSponsors !== undefined && out.co_sponsors === undefined) out.co_sponsors = out.coSponsors;
    if (out.readingStage !== undefined && out.reading_stage === undefined) out.reading_stage = out.readingStage;
    if (out.dateEnacted !== undefined && out.date_enacted === undefined) out.date_enacted = out.dateEnacted;
    if (out.datePosted !== undefined && out.date_posted === undefined) out.date_posted = out.datePosted;
    if (out.effectivityDate !== undefined && out.effectivity_date === undefined) out.effectivity_date = out.effectivityDate;
    if (out.postingLocations !== undefined && out.posting_locations === undefined) out.posting_locations = out.postingLocations;
    if (out.cityCouncilReviewStatus !== undefined && out.city_council_review_status === undefined) out.city_council_review_status = out.cityCouncilReviewStatus;
    if (out.cityCouncilTransmittedDate !== undefined && out.city_council_transmitted_date === undefined) out.city_council_transmitted_date = out.cityCouncilTransmittedDate;
    if (out.cityCouncilActionDate !== undefined && out.city_council_action_date === undefined) out.city_council_action_date = out.cityCouncilActionDate;
    if (out.sanctionsPenalties !== undefined && out.sanctions_penalties === undefined) out.sanctions_penalties = out.sanctionsPenalties;
    if (out.documentBody !== undefined && out.document_body === undefined) out.document_body = out.documentBody;

    // Legislative Sessions
    if (out.sessionNumber !== undefined && out.session_number === undefined) out.session_number = out.sessionNumber;
    if (out.sessionType !== undefined && out.session_type === undefined) out.session_type = out.sessionType;
    if (out.sessionDate !== undefined && out.session_date === undefined) out.session_date = out.sessionDate;
    if (out.sessionTime !== undefined && out.session_time === undefined) out.session_time = out.sessionTime;
    if (out.presidingOfficer !== undefined && out.presiding_officer === undefined) out.presiding_officer = out.presidingOfficer;
    if (out.quorumStatus !== undefined && out.quorum_status === undefined) out.quorum_status = out.quorumStatus;
    if (out.presentCount !== undefined && out.present_count === undefined) out.present_count = out.presentCount;
    if (out.totalMembers !== undefined && out.total_members === undefined) out.total_members = out.totalMembers;
    if (out.rollCall !== undefined && out.roll_call === undefined) out.roll_call = out.rollCall;
    if (out.agendaTopics !== undefined && out.agenda_topics === undefined) out.agenda_topics = out.agendaTopics;
    if (out.minutesSummary !== undefined && out.minutes_summary === undefined) out.minutes_summary = out.minutesSummary;
    if (out.sessionStatus !== undefined && out.session_status === undefined) out.session_status = out.sessionStatus;

    // Lupon Members (RA 7160 Sec. 399)
    if (out.committeeAssignment !== undefined && out.committee_assignment === undefined) out.committee_assignment = out.committeeAssignment;
    if (out.professionBackground !== undefined && out.profession_background === undefined) out.profession_background = out.professionBackground;
    if (out.appointmentDate !== undefined && out.appointment_date === undefined) out.appointment_date = out.appointmentDate;
    if (out.oathDate !== undefined && out.oath_date === undefined) out.oath_date = out.oathDate;
    if (out.casesHandledCount !== undefined && out.cases_handled_count === undefined) out.cases_handled_count = out.casesHandledCount;

    // Lupon Cases (Katarungang Pambarangay)
    if (out.caseNumber !== undefined && out.case_number === undefined) out.case_number = out.caseNumber;
    if (out.blotterCaseId !== undefined && out.blotter_case_id === undefined) out.blotter_case_id = out.blotterCaseId;
    if (out.complainantAddress !== undefined && out.complainant_address === undefined) out.complainant_address = out.complainantAddress;
    if (out.complainantContact !== undefined && out.complainant_contact === undefined) out.complainant_contact = out.complainantContact;
    if (out.respondentAddress !== undefined && out.respondent_address === undefined) out.respondent_address = out.respondentAddress;
    if (out.respondentContact !== undefined && out.respondent_contact === undefined) out.respondent_contact = out.respondentContact;
    if (out.disputeType !== undefined && out.dispute_type === undefined) out.dispute_type = out.disputeType;
    if (out.complaintDetails !== undefined && out.complaint_details === undefined) out.complaint_details = out.complaintDetails;
    if (out.reliefSought !== undefined && out.relief_sought === undefined) out.relief_sought = out.reliefSought;
    if (out.dateFiled !== undefined && out.date_filed === undefined) out.date_filed = out.dateFiled;
    if (out.pangkatChairman !== undefined && out.pangkat_chairman === undefined) out.pangkat_chairman = out.pangkatChairman;
    if (out.pangkatSecretary !== undefined && out.pangkat_secretary === undefined) out.pangkat_secretary = out.pangkatSecretary;
    if (out.pangkatMember !== undefined && out.pangkat_member === undefined) out.pangkat_member = out.pangkatMember;
    if (out.pbDeadline !== undefined && out.pb_deadline === undefined) out.pb_deadline = out.pbDeadline;
    if (out.pangkatDeadline !== undefined && out.pangkat_deadline === undefined) out.pangkat_deadline = out.pangkatDeadline;
    if (out.settlementTerms !== undefined && out.settlement_terms === undefined) out.settlement_terms = out.settlementTerms;
    if (out.settlementDate !== undefined && out.settlement_date === undefined) out.settlement_date = out.settlementDate;
    if (out.settlementAmount !== undefined && out.settlement_amount === undefined) out.settlement_amount = out.settlementAmount;
    if (out.complianceDueDate !== undefined && out.compliance_due_date === undefined) out.compliance_due_date = out.complianceDueDate;
    if (out.cfaReason !== undefined && out.cfa_reason === undefined) out.cfa_reason = out.cfaReason;
    if (out.cfaDate !== undefined && out.cfa_date === undefined) out.cfa_date = out.cfaDate;

    // Lupon Hearings
    if (out.caseId !== undefined && out.case_id === undefined) out.case_id = out.caseId;
    if (out.hearingNumber !== undefined && out.hearing_number === undefined) out.hearing_number = out.hearingNumber;
    if (out.hearingType !== undefined && out.hearing_type === undefined) out.hearing_type = out.hearingType;
    if (out.scheduledDate !== undefined && out.scheduled_date === undefined) out.scheduled_date = out.scheduledDate;
    if (out.scheduledTime !== undefined && out.scheduled_time === undefined) out.scheduled_time = out.scheduledTime;
    if (out.complainantPresent !== undefined && out.complainant_present === undefined) out.complainant_present = out.complainantPresent ? 1 : 0;
    if (out.respondentPresent !== undefined && out.respondent_present === undefined) out.respondent_present = out.respondentPresent ? 1 : 0;
    if (out.proceedingsSummary !== undefined && out.proceedings_summary === undefined) out.proceedings_summary = out.proceedingsSummary;
    if (out.nextAction !== undefined && out.next_action === undefined) out.next_action = out.nextAction;

    // Disbursement Vouchers
    if (out.dvNumber !== undefined && out.dv_number === undefined) out.dv_number = out.dvNumber;
    if (out.payeeName !== undefined && out.payee_name === undefined) out.payee_name = out.payeeName;
    if (out.checkNo !== undefined && out.check_no === undefined) out.check_no = out.checkNo;
    if (out.checkDate !== undefined && out.check_date === undefined) out.check_date = out.checkDate;
    if (out.bankName !== undefined && out.bank_name === undefined) out.bank_name = out.bankName;
    if (out.expenseClass !== undefined && out.expense_class === undefined) out.expense_class = out.expenseClass;
    if (out.certifiedBy !== undefined && out.certified_by === undefined) out.certified_by = out.certifiedBy;
    if (out.approvedBy !== undefined && out.approved_by === undefined) out.approved_by = out.approvedBy;
    if (out.releasedAt !== undefined && out.released_at === undefined) out.released_at = out.releasedAt;

    // Revenue Collections
    if (out.orNumber !== undefined && out.or_number === undefined) out.or_number = out.orNumber;
    if (out.rcdNumber !== undefined && out.rcd_number === undefined) out.rcd_number = out.rcdNumber;
    if (out.payerName !== undefined && out.payer_name === undefined) out.payer_name = out.payerName;
    if (out.revenueSource !== undefined && out.revenue_source === undefined) out.revenue_source = out.revenueSource;
    if (out.fundDestination !== undefined && out.fund_destination === undefined) out.fund_destination = out.fundDestination;
    if (out.collectedBy !== undefined && out.collected_by === undefined) out.collected_by = out.collectedBy;
    if (out.receiptDate !== undefined && out.receipt_date === undefined) out.receipt_date = out.receiptDate;
    if (out.depositDate !== undefined && out.deposit_date === undefined) out.deposit_date = out.depositDate;
    if (out.depositBank !== undefined && out.deposit_bank === undefined) out.deposit_bank = out.depositBank;
    if (out.depositSlipNo !== undefined && out.deposit_slip_no === undefined) out.deposit_slip_no = out.depositSlipNo;

    // Budget Obligations
    if (out.obrNumber !== undefined && out.obr_number === undefined) out.obr_number = out.obrNumber;
    if (out.obligationType !== undefined && out.obligation_type === undefined) out.obligation_type = out.obligationType;
    if (out.obligeeName !== undefined && out.obligee_name === undefined) out.obligee_name = out.obligeeName;
    if (out.dateObligated !== undefined && out.date_obligated === undefined) out.date_obligated = out.dateObligated;

    // Financial Reports
    if (out.reportCode !== undefined && out.report_code === undefined) out.report_code = out.reportCode;
    if (out.reportType !== undefined && out.report_type === undefined) out.report_type = out.reportType;
    if (out.periodLabel !== undefined && out.period_label === undefined) out.period_label = out.periodLabel;
    if (out.totalReceipts !== undefined && out.total_receipts === undefined) out.total_receipts = out.totalReceipts;
    if (out.totalExpenditures !== undefined && out.total_expenditures === undefined) out.total_expenditures = out.totalExpenditures;
    if (out.netBalance !== undefined && out.net_balance === undefined) out.net_balance = out.netBalance;
    if (out.reportData !== undefined && out.report_data === undefined) out.report_data = out.reportData;
    if (out.generatedBy !== undefined && out.generated_by === undefined) out.generated_by = out.generatedBy;

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
    health_medicines:'api/health.php?action=medicines',
    procurement_projects:'api/procurement.php',
    budget_allocations:'api/procurement.php?action=budget',
    procurement_bids:'api/procurement.php?action=bids',
    legislative_documents:'api/legislative.php',
    legislative_sessions:'api/legislative.php?action=sessions',
    lupon_cases:   'api/lupon.php',
    lupon_members: 'api/lupon.php?action=members',
    lupon_hearings:'api/lupon.php?action=hearings',
    disbursement_vouchers:'api/budget.php?action=vouchers',
    revenue_collections:'api/budget.php?action=collections',
    budget_obligations:'api/budget.php?action=obligations',
    financial_reports:'api/budget.php?action=reports'
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
      if (storeName === 'procurement_projects') action = 'create_project';
      if (storeName === 'budget_allocations') action = 'create_budget';
      if (storeName === 'procurement_bids') action = 'add_bid';
      if (storeName === 'legislative_documents') action = 'create_document';
      if (storeName === 'legislative_sessions') action = 'create_session';
      if (storeName === 'lupon_cases') action = 'create_case';
      if (storeName === 'lupon_members') action = 'add_member';
      if (storeName === 'lupon_hearings') action = 'schedule_hearing';
      if (storeName === 'disbursement_vouchers') action = 'create_voucher';
      if (storeName === 'revenue_collections') action = 'record_collection';
      if (storeName === 'budget_obligations') action = 'create_obligation';
      if (storeName === 'financial_reports') action = 'generate_report';
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
      if (storeName === 'procurement_projects') action = 'update_project';
      if (storeName === 'budget_allocations') action = 'update_budget';
      if (storeName === 'legislative_documents') action = 'update_document';
      if (storeName === 'legislative_sessions') action = 'update_session';
      if (storeName === 'lupon_cases') action = 'update_case';
      if (storeName === 'lupon_members') action = 'update_member';
      if (storeName === 'lupon_hearings') action = 'update_hearing';
      if (storeName === 'disbursement_vouchers') action = 'update_voucher';
      if (storeName === 'revenue_collections') action = 'update_collection';
      if (storeName === 'budget_obligations') action = 'update_obligation';

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
