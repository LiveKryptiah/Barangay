<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_auth('login.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Budget &amp; Financial Management &bull; Barangay Management System</title>
  <link rel="stylesheet" href="css/design-system.css">
  <script src="js/components/theme.js"></script>
  <style>
    .utilization-bar-wrap {
      width: 100%;
      height: 8px;
      background: var(--color-field);
      border-radius: var(--rounded-full);
      overflow: hidden;
      margin-top: 4px;
    }
    .utilization-bar {
      height: 100%;
      background: var(--color-primary);
      border-radius: var(--rounded-full);
      transition: width 0.3s ease;
    }
    .utilization-bar.warning { background: var(--color-amber); }
    .utilization-bar.danger { background: var(--color-rose); }
    .utilization-bar.success { background: var(--color-emerald); }
    
    .tab-nav {
      display: flex;
      gap: var(--spacing-md);
      border-bottom: 1px solid var(--color-canvas);
      margin-bottom: var(--spacing-lg);
      overflow-x: auto;
    }
    .tab-btn {
      background: none;
      border: none;
      padding: var(--spacing-sm) var(--spacing-md);
      color: var(--color-ink);
      opacity: 0.7;
      cursor: pointer;
      border-bottom: 2px solid transparent;
      font-weight: 500;
      white-space: nowrap;
    }
    .tab-btn:hover {
      opacity: 1;
    }
    .tab-btn.active {
      opacity: 1;
      border-bottom-color: var(--color-primary);
      color: var(--color-primary);
    }
    .tab-panel {
      display: none;
    }
    .tab-panel.active {
      display: block;
    }
    .stats-ladder {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: var(--spacing-md);
      margin-bottom: var(--spacing-xl);
    }
    @media (max-width: 1024px) {
      .stats-ladder { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
      .stats-ladder { grid-template-columns: 1fr; }
    }
    
    @media print {
      body * { visibility: hidden; }
      #print-statutory-container, #print-statutory-container * {
        visibility: visible;
      }
      #print-statutory-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 40px;
        background: #fff;
        color: #000;
        font-family: Arial, sans-serif;
      }
      .dv-print-header { text-align: center; margin-bottom: 20px; font-weight: bold; }
      .dv-print-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
      .dv-print-table th, .dv-print-table td { border: 1px solid #000; padding: 8px; }
      .dv-print-sig { display: flex; justify-content: space-between; margin-top: 60px; }
      .dv-print-sig-box { width: 45%; text-align: center; }
      .dv-print-sig-line { border-bottom: 1px solid #000; margin-bottom: 5px; height: 30px; }
    }
  </style>
</head>
<body class="app-layout">
  <div class="app-shell">
    <div id="sidebar-mount"></div>
    <div class="app-main">
      <div id="mobile-header-mount"></div>
      <div id="app-topbar-mount"></div>
      <main class="app-content">
        
        <header class="page-hero">
          <div class="page-hero-left">
            <h1 class="typography-heading-2">Budget &amp; Financial Management</h1>
            <p class="typography-body-lg text-muted">COA-compliant tracking, AIP allocations, and voucher management.</p>
          </div>
          <div class="page-hero-right">
            <span class="badge badge-emerald">COA Compliant</span>
          </div>
        </header>

        <div class="stats-ladder">
          <div class="studio-card">
            <div class="card-header">
              <h3 class="typography-heading-4">Total Approved Budget (AIP)</h3>
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="card-value" id="stat-aip">₱0.00</div>
            <div class="utilization-bar-wrap"><div class="utilization-bar" id="stat-aip-bar" style="width: 0%"></div></div>
          </div>
          <div class="studio-card">
            <div class="card-header">
              <h3 class="typography-heading-4">Total Disbursements</h3>
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l9.2-9.2M17 17V7H7"/></svg>
            </div>
            <div class="card-value" id="stat-disbursements">₱0.00</div>
            <p class="typography-caption text-muted" id="stat-disbursements-sub">0 Released / 0 Pending</p>
          </div>
          <div class="studio-card">
            <div class="card-header">
              <h3 class="typography-heading-4">Revenue Collected</h3>
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 6l-9.5 9.5-5-5L1 18M17 6h6v6"/></svg>
            </div>
            <div class="card-value" id="stat-revenue">₱0.00</div>
            <p class="typography-caption text-muted" id="stat-revenue-sub">₱0.00 Deposited</p>
          </div>
          <div class="studio-card">
            <div class="card-header">
              <h3 class="typography-heading-4">Available Balance</h3>
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-4H6a2 2 0 0 1-2-2V6z"/></svg>
            </div>
            <div class="card-value" id="stat-balance">₱0.00</div>
            <p class="typography-caption text-muted" id="stat-balance-sub">Remaining budget</p>
          </div>
        </div>

        <div class="tab-nav">
          <button class="tab-btn active" onclick="switchTab('allocations')">AIP &amp; Budget Allocations</button>
          <button class="tab-btn" onclick="switchTab('vouchers')">Disbursement Vouchers</button>
          <button class="tab-btn" onclick="switchTab('collections')">Revenue &amp; Collections</button>
          <button class="tab-btn" onclick="switchTab('reports')">Financial Statements</button>
        </div>

        <!-- Tab Panel 1 -->
        <div id="tab-allocations" class="tab-panel active">
          <div class="studio-card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
              <h2 class="typography-heading-4">Annual Investment Program (AIP)</h2>
              <div style="display:flex; gap:var(--spacing-sm);">
                <select id="filter-year" class="text-input" onchange="refreshAllocations()">
                  <option value="2026">2026</option>
                  <option value="2025">2025</option>
                  <option value="2024">2024</option>
                </select>
                <button class="button-primary" onclick="openModal('budget-alloc-modal')">+ New Allocation</button>
              </div>
            </div>
            <div class="data-table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Fund Source</th>
                    <th>Program/Project</th>
                    <th>Committee</th>
                    <th>Approved Budget</th>
                    <th>Obligated</th>
                    <th>Balance</th>
                    <th>Utilization</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="allocations-tbody">
                  <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Tab Panel 2 -->
        <div id="tab-vouchers" class="tab-panel">
          <div class="studio-card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
              <h2 class="typography-heading-4">Disbursement Vouchers</h2>
              <div style="display:flex; gap:var(--spacing-sm);">
                <select id="filter-voucher-status" class="text-input" onchange="refreshVouchers()">
                  <option value="">All Statuses</option>
                  <option value="Draft">Draft</option>
                  <option value="Certified">Certified</option>
                  <option value="Approved">Approved</option>
                  <option value="Released">Released</option>
                  <option value="Cancelled">Cancelled</option>
                </select>
                <input type="text" id="search-vouchers" class="text-input" placeholder="Search Payee/DV #..." oninput="refreshVouchers()">
                <button class="button-primary" onclick="openModal('voucher-modal')">+ New Voucher</button>
              </div>
            </div>
            <div class="data-table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>DV #</th>
                    <th>Payee</th>
                    <th>Particulars</th>
                    <th>Fund Source</th>
                    <th>Exp. Class</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="vouchers-tbody">
                  <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Tab Panel 3 -->
        <div id="tab-collections" class="tab-panel">
          <div class="studio-card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
              <h2 class="typography-heading-4">Revenue &amp; Collections</h2>
              <div style="display:flex; gap:var(--spacing-sm);">
                <select id="filter-collection-source" class="text-input" onchange="refreshCollections()">
                  <option value="">All Sources</option>
                  <option value="General Fund">General Fund</option>
                  <option value="Sangguniang Kabataan (SK)">Sangguniang Kabataan (SK)</option>
                  <option value="20% Development Fund">20% Development Fund</option>
                  <option value="Calamity Fund (BDRRMF)">Calamity Fund (BDRRMF)</option>
                </select>
                <input type="text" id="search-collections" class="text-input" placeholder="Search Payer/OR #..." oninput="refreshCollections()">
                <button class="button-primary" onclick="openModal('collection-modal')">+ Record Collection</button>
              </div>
            </div>
            <div class="data-table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>OR #</th>
                    <th>Payer</th>
                    <th>Source</th>
                    <th>Particulars</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="collections-tbody">
                  <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Tab Panel 4 -->
        <div id="tab-reports" class="tab-panel">
          <div class="studio-card" style="margin-bottom:var(--spacing-lg);">
            <div class="card-header">
              <h2 class="typography-heading-4">Fund Utilization Summary</h2>
            </div>
            <div id="fund-summary-container" style="padding:var(--spacing-md);">Loading...</div>
          </div>
          
          <div class="studio-card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
              <h2 class="typography-heading-4">Financial Reports</h2>
              <button class="button-primary" onclick="openModal('generate-report-modal')">+ Generate Report</button>
            </div>
            <div class="data-table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Period</th>
                    <th>Receipts</th>
                    <th>Expenditures</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="reports-tbody">
                  <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- Modals -->
  <dialog id="budget-alloc-modal" class="modal-dialog">
    <div class="studio-card" style="width:400px; max-width:90vw;">
      <div class="card-header"><h3 class="typography-heading-4">Budget Allocation</h3></div>
      <form id="alloc-form" onsubmit="submitAlloc(event)">
        <input type="hidden" id="alloc_id" name="id">
        <div style="margin-bottom:var(--spacing-md);">
          <label>Fiscal Year</label>
          <input type="number" id="alloc_year" name="fiscal_year" class="text-input" value="2026" required>
        </div>
        <div style="margin-bottom:var(--spacing-md);">
          <label>Fund Source</label>
          <select id="alloc_fund_source" name="fund_source" class="text-input" required>
            <option value="General Fund">General Fund</option>
            <option value="Sangguniang Kabataan (SK)">Sangguniang Kabataan (SK)</option>
            <option value="20% Development Fund">20% Development Fund</option>
            <option value="Calamity Fund (BDRRMF)">Calamity Fund (BDRRMF)</option>
            <option value="Gender and Development (GAD)">Gender and Development (GAD)</option>
            <option value="Senior Citizens and PWD">Senior Citizens and PWD</option>
            <option value="Other Trust Funds">Other Trust Funds</option>
          </select>
        </div>
        <div style="margin-bottom:var(--spacing-md);">
          <label>Program Title</label>
          <input type="text" id="alloc_program" name="program_title" class="text-input" required>
        </div>
        <div style="margin-bottom:var(--spacing-md);">
          <label>Committee</label>
          <input type="text" id="alloc_committee" name="committee" class="text-input">
        </div>
        <div style="margin-bottom:var(--spacing-md);">
          <label>Approved Budget</label>
          <input type="number" step="0.01" id="alloc_budget" name="approved_budget" class="text-input" required>
        </div>
        <div style="margin-bottom:var(--spacing-md);">
          <label>Obligated Amount</label>
          <input type="number" step="0.01" id="alloc_obligated" name="obligated_amount" class="text-input" value="0">
        </div>
        <div style="display:flex; justify-content:flex-end; gap:var(--spacing-sm);">
          <button type="button" class="button-outline" onclick="closeModal('budget-alloc-modal')">Cancel</button>
          <button type="submit" class="button-primary">Save</button>
        </div>
      </form>
    </div>
  </dialog>

  <dialog id="voucher-modal" class="modal-dialog">
    <div class="studio-card" style="width:500px; max-width:90vw;">
      <div class="card-header"><h3 class="typography-heading-4">Disbursement Voucher</h3></div>
      <form id="voucher-form" onsubmit="submitVoucher(event)">
        <input type="hidden" id="voucher_id" name="id">
        <div style="margin-bottom:var(--spacing-md);">
          <label>Budget Item (Allocation)</label>
          <select id="voucher_alloc_id" name="allocation_id" class="text-input"></select>
        </div>
        <div style="margin-bottom:var(--spacing-md);">
          <label>Payee Name</label>
          <input type="text" id="voucher_payee" name="payee_name" class="text-input" required>
        </div>
        <div style="margin-bottom:var(--spacing-md);">
          <label>Particulars</label>
          <textarea id="voucher_particulars" name="particulars" class="text-input" required></textarea>
        </div>
        <div style="display:flex; gap:var(--spacing-md); margin-bottom:var(--spacing-md);">
          <div style="flex:1;">
            <label>Fund Source</label>
            <select id="voucher_fund" name="fund_source" class="text-input" required>
              <option value="General Fund">General Fund</option>
              <option value="Sangguniang Kabataan (SK)">Sangguniang Kabataan (SK)</option>
              <option value="20% Development Fund">20% Development Fund</option>
              <option value="Calamity Fund (BDRRMF)">Calamity Fund (BDRRMF)</option>
              <option value="Gender and Development (GAD)">Gender and Development (GAD)</option>
              <option value="Senior Citizens and PWD">Senior Citizens and PWD</option>
              <option value="Other Trust Funds">Other Trust Funds</option>
            </select>
          </div>
          <div style="flex:1;">
            <label>Expense Class</label>
            <select id="voucher_expense" name="expense_class" class="text-input" required>
              <option value="PS">PS (Personnel Services)</option>
              <option value="MOOE">MOOE</option>
              <option value="CO">CO (Capital Outlay)</option>
            </select>
          </div>
        </div>
        <div style="margin-bottom:var(--spacing-md);">
          <label>Amount</label>
          <input type="number" step="0.01" id="voucher_amount" name="amount" class="text-input" required>
        </div>
        <div style="display:flex; gap:var(--spacing-md); margin-bottom:var(--spacing-md);">
          <div style="flex:1;">
            <label>Check No</label>
            <input type="text" id="voucher_check_no" name="check_no" class="text-input">
          </div>
          <div style="flex:1;">
            <label>Check Date</label>
            <input type="date" id="voucher_check_date" name="check_date" class="text-input">
          </div>
        </div>
        <div style="margin-bottom:var(--spacing-md);">
          <label>Bank</label>
          <input type="text" id="voucher_bank" name="bank" class="text-input" value="LBP">
        </div>
        <div style="display:flex; gap:var(--spacing-md); margin-bottom:var(--spacing-md);">
          <div style="flex:1;">
            <label>Certified By</label>
            <input type="text" id="voucher_certified" name="certified_by" class="text-input">
          </div>
          <div style="flex:1;">
            <label>Approved By</label>
            <input type="text" id="voucher_approved" name="approved_by" class="text-input">
          </div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:var(--spacing-sm);">
          <button type="button" class="button-outline" onclick="closeModal('voucher-modal')">Cancel</button>
          <button type="submit" class="button-primary">Save</button>
        </div>
      </form>
    </div>
  </dialog>

  <dialog id="collection-modal" class="modal-dialog">
    <div class="studio-card" style="width:400px; max-width:90vw;">
      <div class="card-header"><h3 class="typography-heading-4">Record Collection</h3></div>
      <form id="collection-form" onsubmit="submitCollection(event)">
        <input type="hidden" id="coll_id" name="id">
        <div style="margin-bottom:var(--spacing-md);"><label>Payer Name</label><input type="text" id="coll_payer" name="payer_name" class="text-input" required></div>
        <div style="margin-bottom:var(--spacing-md);">
          <label>Revenue Source</label>
          <select id="coll_source" name="revenue_source" class="text-input" required>
             <option value="General Fund">General Fund</option>
             <option value="Sangguniang Kabataan (SK)">Sangguniang Kabataan (SK)</option>
             <option value="20% Development Fund">20% Development Fund</option>
             <option value="Calamity Fund (BDRRMF)">Calamity Fund (BDRRMF)</option>
             <option value="Gender and Development (GAD)">Gender and Development (GAD)</option>
             <option value="Senior Citizens and PWD">Senior Citizens and PWD</option>
             <option value="Other Trust Funds">Other Trust Funds</option>
          </select>
        </div>
        <div style="margin-bottom:var(--spacing-md);"><label>Particulars</label><textarea id="coll_particulars" name="particulars" class="text-input"></textarea></div>
        <div style="margin-bottom:var(--spacing-md);"><label>Amount</label><input type="number" step="0.01" id="coll_amount" name="amount" class="text-input" required></div>
        <div style="margin-bottom:var(--spacing-md);">
          <label>Fund Destination</label>
          <select id="coll_dest" name="fund_destination" class="text-input">
             <option value="General Fund">General Fund</option>
             <option value="Sangguniang Kabataan (SK)">Sangguniang Kabataan (SK)</option>
             <option value="20% Development Fund">20% Development Fund</option>
          </select>
        </div>
        <div style="display:flex; gap:var(--spacing-md); margin-bottom:var(--spacing-md);">
            <div style="flex:1;"><label>Collected By</label><input type="text" id="coll_by" name="collected_by" class="text-input"></div>
            <div style="flex:1;"><label>Receipt Date</label><input type="date" id="coll_date" name="receipt_date" class="text-input"></div>
        </div>
        <div style="display:flex; gap:var(--spacing-md); margin-bottom:var(--spacing-md);">
            <div style="flex:1;"><label>RCD Number</label><input type="text" id="coll_rcd" name="rcd_number" class="text-input"></div>
            <div style="flex:1;"><label>Deposit Date</label><input type="date" id="coll_dep_date" name="deposit_date" class="text-input"></div>
        </div>
        <div style="display:flex; gap:var(--spacing-md); margin-bottom:var(--spacing-md);">
            <div style="flex:1;"><label>Deposit Bank</label><input type="text" id="coll_bank" name="deposit_bank" class="text-input"></div>
            <div style="flex:1;"><label>Deposit Slip No</label><input type="text" id="coll_slip" name="deposit_slip_no" class="text-input"></div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:var(--spacing-sm);">
          <button type="button" class="button-outline" onclick="closeModal('collection-modal')">Cancel</button>
          <button type="submit" class="button-primary">Save</button>
        </div>
      </form>
    </div>
  </dialog>

  <dialog id="approve-voucher-modal" class="modal-dialog">
    <div class="studio-card" style="width:300px; max-width:90vw;">
      <div class="card-header"><h3 class="typography-heading-4">Update Voucher Status</h3></div>
      <div style="margin-bottom:var(--spacing-md);" id="approve-voucher-details" class="typography-caption"></div>
      <form id="approve-voucher-form" onsubmit="submitVoucherStatus(event)">
        <input type="hidden" id="approve_v_id">
        <div style="margin-bottom:var(--spacing-md);">
          <label>New Status</label>
          <select id="approve_v_status" class="text-input">
            <option value="Certified">Certified</option>
            <option value="Approved">Approved</option>
            <option value="Released">Released</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:var(--spacing-sm);">
          <button type="button" class="button-outline" onclick="closeModal('approve-voucher-modal')">Cancel</button>
          <button type="submit" class="button-primary">Confirm</button>
        </div>
      </form>
    </div>
  </dialog>

  <dialog id="generate-report-modal" class="modal-dialog">
    <div class="studio-card" style="width:300px; max-width:90vw;">
      <div class="card-header"><h3 class="typography-heading-4">Generate Report</h3></div>
      <form id="generate-report-form" onsubmit="submitGenerateReport(event)">
        <div style="margin-bottom:var(--spacing-md);">
          <label>Report Type</label>
          <select name="report_type" id="rep_type" class="text-input">
            <option value="Statement of Receipts and Expenditures">Statement of Receipts and Expenditures</option>
            <option value="Cash Flow">Cash Flow</option>
            <option value="Trial Balance">Trial Balance</option>
            <option value="Balance Sheet">Balance Sheet</option>
          </select>
        </div>
        <div style="margin-bottom:var(--spacing-md);">
          <label>Fiscal Year</label>
          <input type="number" id="rep_year" name="fiscal_year" class="text-input" value="2026">
        </div>
        <div style="margin-bottom:var(--spacing-md);">
          <label>Period Label</label>
          <input type="text" id="rep_period" name="period_label" class="text-input" placeholder="e.g. Q3 2026">
        </div>
        <div style="display:flex; justify-content:flex-end; gap:var(--spacing-sm);">
          <button type="button" class="button-outline" onclick="closeModal('generate-report-modal')">Cancel</button>
          <button type="submit" class="button-primary">Generate</button>
        </div>
      </form>
    </div>
  </dialog>

  <dialog id="view-voucher-modal" class="modal-dialog">
    <div class="studio-card" style="width:500px; max-width:90vw;">
      <div class="card-header"><h3 class="typography-heading-4">Voucher Details</h3></div>
      <div id="view-voucher-content" style="margin-bottom:var(--spacing-md); line-height:1.6; font-size:14px;"></div>
      <div style="display:flex; justify-content:flex-end;">
        <button type="button" class="button-outline" onclick="closeModal('view-voucher-modal')">Close</button>
      </div>
    </div>
  </dialog>

  <div id="print-statutory-container" style="display: none;"></div>

  <script src="js/api.js"></script>
  <script src="js/auth.js"></script>
  <script src="js/components/toast.js"></script>
  <script src="js/components/sidebar.js"></script>
  <script>
    const formatCurrency = (val) => {
      const num = parseFloat(val) || 0;
      return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(num);
    };
    
    const formatDate = (d) => {
      if (!d) return '';
      return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    };

    const getStatusBadge = (status, type) => {
      if (type === 'voucher') {
        const map = {
          'Draft': 'badge-neutral',
          'Certified': 'badge-blue',
          'Approved': 'badge-amber',
          'Released': 'badge-green',
          'Cancelled': 'badge-rose'
        };
        return `<span class="badge ${map[status] || 'badge-neutral'}">${status}</span>`;
      } else if (type === 'collection') {
        const map = {
          'Collected': 'badge-blue',
          'Deposited': 'badge-green',
          'Remitted': 'badge-emerald',
          'Voided': 'badge-rose'
        };
        return `<span class="badge ${map[status] || 'badge-neutral'}">${status}</span>`;
      } else if (type === 'report') {
        const map = {
          'Draft': 'badge-neutral',
          'Final': 'badge-green'
        };
        return `<span class="badge ${map[status] || 'badge-neutral'}">${status}</span>`;
      }
      return `<span class="badge badge-neutral">${status}</span>`;
    };

    function openModal(id) {
      document.getElementById(id).showModal();
    }
    
    function closeModal(id) {
      const m = document.getElementById(id);
      m.close();
      const form = m.querySelector('form');
      if(form) form.reset();
    }

    let currentVoucherList = [];
    let currentAllocList = [];

    function switchTab(tabId) {
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.getElementById(`tab-${tabId}`).classList.add('active');
      document.querySelector(`.tab-btn[onclick="switchTab('${tabId}')"]`).classList.add('active');
      
      if (tabId === 'allocations') refreshAllocations();
      if (tabId === 'vouchers') refreshVouchers();
      if (tabId === 'collections') refreshCollections();
      if (tabId === 'reports') {
        refreshReports();
        refreshFundSummary();
      }
    }

    async function apiCall(action, method='GET', body=null) {
      let url = `api/budget.php?action=${action}`;
      let options = { method, headers: {} };
      if (body && method !== 'GET') {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
      }
      try {
        const res = await fetch(url, options);
        if (!res.ok) throw new Error('API request failed');
        const data = await res.json();
        return data;
      } catch (err) {
        console.error(err);
        return { success: false, data: [] };
      }
    }

    async function refreshAllData() {
        await refreshStats();
        
        // Refresh whichever tab is active
        const activeTabBtn = document.querySelector('.tab-btn.active');
        if (activeTabBtn) {
            const tabIdMatch = activeTabBtn.getAttribute('onclick').match(/'([^']+)'/);
            if (tabIdMatch && tabIdMatch[1]) {
                switchTab(tabIdMatch[1]);
            }
        } else {
            switchTab('allocations');
        }
    }

    async function refreshStats() {
      const res = await apiCall('stats');
      const d = res.data || { 
        aip: 0, aip_utilization: 0, 
        disbursed: 0, disbursed_pending: 0, 
        revenue: 0, revenue_deposited: 0, 
        balance: 0, balance_percent: 0 
      };
      
      document.getElementById('stat-aip').textContent = formatCurrency(d.aip);
      const aipBar = document.getElementById('stat-aip-bar');
      aipBar.style.width = Math.min(d.aip_utilization, 100) + '%';
      
      document.getElementById('stat-disbursements').textContent = formatCurrency(d.disbursed);
      document.getElementById('stat-disbursements-sub').textContent = `${formatCurrency(d.disbursed)} Released / ${formatCurrency(d.disbursed_pending)} Pending`;
      
      document.getElementById('stat-revenue').textContent = formatCurrency(d.revenue);
      document.getElementById('stat-revenue-sub').textContent = `${formatCurrency(d.revenue_deposited)} Deposited`;
      
      document.getElementById('stat-balance').textContent = formatCurrency(d.balance);
      const bColor = d.balance_percent > 50 ? 'success' : (d.balance_percent > 20 ? 'warning' : 'danger');
      document.getElementById('stat-balance').style.color = `var(--color-${bColor === 'success'?'emerald':(bColor==='warning'?'amber':'rose')})`;
    }

    async function refreshAllocations() {
      const year = document.getElementById('filter-year').value;
      const res = await apiCall(`get_allocations&year=${year}`);
      const list = res.data || [];
      currentAllocList = list;
      
      const tbody = document.getElementById('allocations-tbody');
      if(list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;" class="text-muted">No allocations found for this fiscal year.</td></tr>`;
        return;
      }
      
      tbody.innerHTML = list.map(a => {
        const bal = parseFloat(a.approved_budget) - parseFloat(a.obligated_amount);
        const pct = a.approved_budget > 0 ? (a.obligated_amount / a.approved_budget) * 100 : 0;
        return `
          <tr>
            <td>${a.fund_source}</td>
            <td><strong>${a.program_title}</strong></td>
            <td>${a.committee || '-'}</td>
            <td>${formatCurrency(a.approved_budget)}</td>
            <td>${formatCurrency(a.obligated_amount)}</td>
            <td>${formatCurrency(bal)}</td>
            <td>
              <div class="utilization-bar-wrap" style="height:4px; margin:0 0 2px 0;">
                <div class="utilization-bar" style="width:${Math.min(pct, 100)}%"></div>
              </div>
              <small class="typography-caption">${pct.toFixed(1)}%</small>
            </td>
            <td>
              <button class="icon-button" onclick="editAlloc(${a.id})" title="Edit">✏️</button>
              <button class="icon-button text-rose" onclick="deleteAlloc(${a.id})" title="Delete">🗑️</button>
            </td>
          </tr>
        `;
      }).join('');
      
      populateAllocDropdown();
    }
    
    function populateAllocDropdown() {
      const sel = document.getElementById('voucher_alloc_id');
      sel.innerHTML = '<option value="">-- None --</option>' + 
        currentAllocList.map(a => `<option value="${a.id}">${a.program_title} (${a.fund_source})</option>`).join('');
    }

    async function refreshVouchers() {
      const stat = document.getElementById('filter-voucher-status').value;
      const q = document.getElementById('search-vouchers').value.toLowerCase();
      
      const res = await apiCall(`get_vouchers&status=${stat}`);
      const list = res.data || [];
      currentVoucherList = list;
      
      const filtered = list.filter(v => 
        (v.payee_name || '').toLowerCase().includes(q) || 
        (v.dv_number || '').toLowerCase().includes(q) ||
        (v.particulars || '').toLowerCase().includes(q)
      );
      
      const tbody = document.getElementById('vouchers-tbody');
      if(filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;" class="text-muted">No vouchers found.</td></tr>`;
        return;
      }
      
      tbody.innerHTML = filtered.map(v => `
        <tr>
          <td>${v.dv_number || '-'}</td>
          <td>${v.payee_name}</td>
          <td style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${v.particulars}">${v.particulars}</td>
          <td>${v.fund_source}</td>
          <td>${v.expense_class}</td>
          <td><strong>${formatCurrency(v.amount)}</strong></td>
          <td>${getStatusBadge(v.status, 'voucher')}</td>
          <td>
            <button class="icon-button" onclick="viewVoucher(${v.id})" title="View">👁️</button>
            <button class="icon-button" onclick="promptApproveVoucher(${v.id})" title="Update Status">✅</button>
            <button class="icon-button" onclick="printDV(${v.id})" title="Print DV">🖨️</button>
          </td>
        </tr>
      `).join('');
    }

    async function refreshCollections() {
      const src = document.getElementById('filter-collection-source').value;
      const q = document.getElementById('search-collections').value.toLowerCase();
      
      const res = await apiCall(`get_collections&source=${src}`);
      const list = res.data || [];
      
      const filtered = list.filter(c => 
        (c.payer_name || '').toLowerCase().includes(q) || 
        (c.or_number || '').toLowerCase().includes(q)
      );
      
      const tbody = document.getElementById('collections-tbody');
      if(filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;" class="text-muted">No collections found.</td></tr>`;
        return;
      }
      
      tbody.innerHTML = filtered.map(c => `
        <tr>
          <td>${c.or_number || '-'}</td>
          <td>${c.payer_name}</td>
          <td>${c.revenue_source}</td>
          <td style="max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${c.particulars}</td>
          <td><strong>${formatCurrency(c.amount)}</strong></td>
          <td>${formatDate(c.receipt_date)}</td>
          <td>${getStatusBadge(c.status, 'collection')}</td>
          <td>
            <button class="icon-button text-rose" onclick="deleteCollection(${c.id})" title="Delete">🗑️</button>
          </td>
        </tr>
      `).join('');
    }

    async function refreshReports() {
      const res = await apiCall('get_reports');
      const list = res.data || [];
      const tbody = document.getElementById('reports-tbody');
      if(list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;" class="text-muted">No financial reports generated.</td></tr>`;
        return;
      }
      tbody.innerHTML = list.map(r => `
        <tr>
          <td>${r.report_code || '-'}</td>
          <td>${r.report_type}</td>
          <td>${r.period_label}</td>
          <td>${formatCurrency(r.total_receipts)}</td>
          <td>${formatCurrency(r.total_expenditures)}</td>
          <td><strong>${formatCurrency(r.ending_balance)}</strong></td>
          <td>${getStatusBadge(r.status, 'report')}</td>
          <td>
             <button class="icon-button" onclick="alert('Print not implemented for summary')" title="Print">🖨️</button>
             <button class="icon-button text-rose" onclick="deleteReport(${r.id})" title="Delete">🗑️</button>
          </td>
        </tr>
      `).join('');
    }

    async function refreshFundSummary() {
      const res = await apiCall('get_fund_summary');
      const list = res.data || [];
      const cont = document.getElementById('fund-summary-container');
      if (list.length === 0) { cont.innerHTML = '<span class="text-muted">No fund data available.</span>'; return; }
      
      cont.innerHTML = list.map(f => {
        const pct = f.budget > 0 ? (f.obligated / f.budget) * 100 : 0;
        return `
          <div style="margin-bottom:var(--spacing-md);">
            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
              <strong>${f.fund_source}</strong>
              <span class="text-muted">${formatCurrency(f.obligated)} / ${formatCurrency(f.budget)}</span>
            </div>
            <div class="utilization-bar-wrap" style="height:10px;">
              <div class="utilization-bar ${pct > 80 ? 'danger' : (pct > 50 ? 'warning' : 'success')}" style="width:${Math.min(pct, 100)}%"></div>
            </div>
          </div>
        `;
      }).join('');
    }

    // Modal submit handlers
    async function submitAlloc(e) {
      e.preventDefault();
      const form = e.target;
      const data = Object.fromEntries(new FormData(form).entries());
      const res = await apiCall('save_allocation', 'POST', data);
      if(res.success) {
        Toast.success('Allocation saved successfully');
        closeModal('budget-alloc-modal');
        refreshAllocations();
        refreshStats();
      } else { Toast.error(res.message || 'Failed to save'); }
    }

    async function submitVoucher(e) {
      e.preventDefault();
      const form = e.target;
      const data = Object.fromEntries(new FormData(form).entries());
      const res = await apiCall('save_voucher', 'POST', data);
      if(res.success) {
        Toast.success('Voucher saved successfully');
        closeModal('voucher-modal');
        refreshVouchers();
        refreshStats();
      } else { Toast.error(res.message || 'Failed to save'); }
    }

    async function submitCollection(e) {
      e.preventDefault();
      const form = e.target;
      const data = Object.fromEntries(new FormData(form).entries());
      const res = await apiCall('save_collection', 'POST', data);
      if(res.success) {
        Toast.success('Collection recorded successfully');
        closeModal('collection-modal');
        refreshCollections();
        refreshStats();
      } else { Toast.error(res.message || 'Failed to save'); }
    }
    
    async function submitVoucherStatus(e) {
      e.preventDefault();
      const id = document.getElementById('approve_v_id').value;
      const status = document.getElementById('approve_v_status').value;
      const res = await apiCall('update_voucher_status', 'POST', { id, status });
      if(res.success) {
        Toast.success('Status updated');
        closeModal('approve-voucher-modal');
        refreshVouchers();
        refreshStats();
      } else { Toast.error(res.message || 'Failed to update'); }
    }
    
    async function submitGenerateReport(e) {
      e.preventDefault();
      const form = e.target;
      const data = Object.fromEntries(new FormData(form).entries());
      const res = await apiCall('generate_report', 'POST', data);
      if(res.success) {
        Toast.success('Report generated');
        closeModal('generate-report-modal');
        refreshReports();
      } else { Toast.error(res.message || 'Failed to generate'); }
    }

    // Actions
    function editAlloc(id) {
      const a = currentAllocList.find(x => x.id == id);
      if(!a) return;
      document.getElementById('alloc_id').value = a.id;
      document.getElementById('alloc_year').value = a.fiscal_year;
      document.getElementById('alloc_fund_source').value = a.fund_source;
      document.getElementById('alloc_program').value = a.program_title;
      document.getElementById('alloc_committee').value = a.committee;
      document.getElementById('alloc_budget').value = a.approved_budget;
      document.getElementById('alloc_obligated').value = a.obligated_amount;
      openModal('budget-alloc-modal');
    }
    
    async function deleteAlloc(id) {
      if(!confirm('Are you sure you want to delete this allocation?')) return;
      const res = await apiCall('delete_allocation', 'POST', { id });
      if(res.success) { Toast.success('Deleted'); refreshAllocations(); refreshStats(); }
      else { Toast.error('Error deleting'); }
    }
    
    function viewVoucher(id) {
      const v = currentVoucherList.find(x => x.id == id);
      if(!v) return;
      document.getElementById('view-voucher-content').innerHTML = `
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
          <div><strong>DV Number:</strong> ${v.dv_number || '-'}</div>
          <div><strong>Status:</strong> ${v.status}</div>
          <div style="grid-column:1/-1;"><strong>Payee:</strong> ${v.payee_name}</div>
          <div style="grid-column:1/-1;"><strong>Particulars:</strong> ${v.particulars}</div>
          <div><strong>Fund Source:</strong> ${v.fund_source}</div>
          <div><strong>Expense Class:</strong> ${v.expense_class}</div>
          <div><strong>Amount:</strong> ${formatCurrency(v.amount)}</div>
          <div><strong>Check No:</strong> ${v.check_no || '-'}</div>
        </div>
      `;
      openModal('view-voucher-modal');
    }
    
    function promptApproveVoucher(id) {
      const v = currentVoucherList.find(x => x.id == id);
      if(!v) return;
      document.getElementById('approve_v_id').value = v.id;
      document.getElementById('approve-voucher-details').innerHTML = `Payee: <strong>${v.payee_name}</strong><br>Amount: <strong>${formatCurrency(v.amount)}</strong><br>Current Status: <strong>${v.status}</strong>`;
      openModal('approve-voucher-modal');
    }
    
    async function deleteCollection(id) {
      if(!confirm('Are you sure you want to delete this collection?')) return;
      const res = await apiCall('delete_collection', 'POST', { id });
      if(res.success) { Toast.success('Deleted'); refreshCollections(); refreshStats(); }
    }
    
    async function deleteReport(id) {
      if(!confirm('Are you sure?')) return;
      const res = await apiCall('delete_report', 'POST', { id });
      if(res.success) { Toast.success('Deleted'); refreshReports(); }
    }

    function printDV(id) {
      const v = currentVoucherList.find(x => x.id == id);
      if(!v) return;
      
      const printContainer = document.getElementById('print-statutory-container');
      printContainer.innerHTML = `
        <div class="dv-print-header">
          <h3>DISBURSEMENT VOUCHER</h3>
          <p>Republic of the Philippines<br>Province / City / Municipality<br><strong>Barangay</strong></p>
        </div>
        <div style="text-align:right; margin-bottom:10px;">
          <strong>No.:</strong> ${v.dv_number || '______________'}
        </div>
        <table class="dv-print-table">
          <tr>
            <td colspan="3"><strong>Payee:</strong> ${v.payee_name}</td>
            <td colspan="1"><strong>Fund Cluster:</strong> ${v.fund_source}</td>
          </tr>
          <tr>
            <td colspan="4"><strong>Address:</strong> _________________________________________</td>
          </tr>
          <tr style="text-align:center; font-weight:bold;">
            <td style="width:50%;">Particulars</td>
            <td>Responsibility Center</td>
            <td>MFO/PAP</td>
            <td>Amount</td>
          </tr>
          <tr style="height:200px; vertical-align:top;">
            <td>${v.particulars}</td>
            <td>${v.expense_class}</td>
            <td></td>
            <td style="text-align:right;">${formatCurrency(v.amount)}</td>
          </tr>
          <tr>
            <td colspan="3" style="text-align:right;"><strong>Total</strong></td>
            <td style="text-align:right;"><strong>${formatCurrency(v.amount)}</strong></td>
          </tr>
        </table>
        
        <div class="dv-print-sig">
          <div class="dv-print-sig-box">
            <p><strong>A. Certified:</strong></p>
            <br>
            <div class="dv-print-sig-line"><strong>${v.certified_by || ''}</strong></div>
            <p>Barangay Treasurer</p>
          </div>
          <div class="dv-print-sig-box">
            <p><strong>B. Approved for Payment:</strong></p>
            <br>
            <div class="dv-print-sig-line"><strong>${v.approved_by || ''}</strong></div>
            <p>Punong Barangay</p>
          </div>
        </div>
      `;
      window.print();
    }

    document.addEventListener('DOMContentLoaded', async () => {
      if (window.AppSidebar) await AppSidebar.render('budget');
      await refreshAllData();
    });
  </script>
</body>
</html>
