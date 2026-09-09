<?php
/**
 * Barangay Management System (BarangayOS)
 * System Settings, Branding, Audit Trails & Backup REST API Endpoint
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$input  = get_json_input();
if (!empty($input['action'])) {
    $action = $input['action'];
}

// ----------------------------------------------------
// 1. AUDIT LOGS ACTION
// ----------------------------------------------------
if ($action === 'audit_logs') {
    $search     = trim($_GET['search'] ?? '');
    $filterCode = trim($_GET['filter_code'] ?? '');

    $sql = "SELECT * FROM `audit_logs` WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (username LIKE ? OR action LIKE ? OR details LIKE ?)";
        $like = "%{$search}%";
        $params = array_merge($params, [$like, $like, $like]);
    }

    if (!empty($filterCode) && $filterCode !== 'ALL') {
        $sql .= " AND action LIKE ?";
        $params[] = "%{$filterCode}%";
    }

    $sql .= " ORDER BY id DESC LIMIT 200";

    $logs = db_fetch_all($sql, $params);
    json_response(true, $logs, 'Audit logs retrieved.');
}

// ----------------------------------------------------
// 2. EXPORT JSON DATABASE BACKUP
// ----------------------------------------------------
if ($action === 'export_backup') {
    $backupData = [
        'system' => 'BarangayOS - Barangay Management System',
        'export_timestamp' => date('c'),
        'version' => '3.0-mysql',
        'database' => DB_NAME,
        'records' => [
            'settings'          => db_fetch_all("SELECT * FROM `settings`"),
            'users'             => db_fetch_all("SELECT `id`, `username`, `full_name`, `email`, `role`, `position`, `status`, `created_at` FROM `users`"),
            'residents'         => db_fetch_all("SELECT * FROM `residents`"),
            'households'        => db_fetch_all("SELECT * FROM `households`"),
            'household_members' => db_fetch_all("SELECT * FROM `household_members`"),
            'certificates'      => db_fetch_all("SELECT * FROM `certificates`"),
            'blotter_cases'     => db_fetch_all("SELECT * FROM `blotter_cases`"),
            'incidents'         => db_fetch_all("SELECT * FROM `incidents`"),
            'officials'         => db_fetch_all("SELECT * FROM `officials`"),
            'audit_logs'        => db_fetch_all("SELECT * FROM `audit_logs` ORDER BY id DESC LIMIT 500")
        ]
    ];

    log_audit_action('DATABASE_BACKUP_DOWNLOADED', 'database', 'Exported full JSON database snapshot.');

    json_response(true, $backupData, 'Database backup serialized.');
}

// ----------------------------------------------------
// 3. PURGE TABLE / FACTORY RESET (Admin only)
// ----------------------------------------------------
if ($action === 'purge') {
    require_admin();

    $target = trim($input['target'] ?? '');
    $validTargets = [
        'residents', 'households', 'certificates', 'blotter_cases', 
        'incidents', 'officials', 'audit_logs', 'ALL_RECORDS'
    ];

    if (!in_array($target, $validTargets)) {
        json_response(false, null, 'Invalid purge target.', 400);
    }

    if ($target === 'ALL_RECORDS') {
        db_query("SET FOREIGN_KEY_CHECKS = 0");
        db_query("TRUNCATE TABLE `household_members`");
        db_query("TRUNCATE TABLE `households`");
        db_query("TRUNCATE TABLE `certificates`");
        db_query("TRUNCATE TABLE `blotter_cases`");
        db_query("TRUNCATE TABLE `incidents`");
        db_query("TRUNCATE TABLE `residents`");
        db_query("TRUNCATE TABLE `officials`");
        db_query("TRUNCATE TABLE `audit_logs`");
        db_query("SET FOREIGN_KEY_CHECKS = 1");

        log_audit_action('FACTORY_RESET_PURGE', 'system', 'Factory reset executed. All community data purged.');
        json_response(true, null, 'Factory reset executed. All module records have been wiped.');
    } else {
        if ($target === 'households') {
            db_query("TRUNCATE TABLE `household_members`");
        }
        db_query("TRUNCATE TABLE `{$target}`");
        log_audit_action('DATA_STORE_PURGED', $target, "Purged all records in table {$target}.");
        json_response(true, null, "Table {$target} purged successfully.");
    }
}

// ----------------------------------------------------
// 4. SAVE SYSTEM SETTINGS
// ----------------------------------------------------
if ($action === 'save' || ($method === 'POST' && empty($action))) {
    require_admin();

    $settings = $input['settings'] ?? $input;
    if (!is_array($settings)) {
        json_response(false, null, 'Settings payload must be key-value pairs.', 400);
    }

    $savedCount = 0;
    foreach ($settings as $key => $value) {
        if (is_string($key) && $key !== 'action') {
            $valStr = is_scalar($value) ? (string)$value : json_encode($value);
            db_query("
                INSERT INTO `settings` (`setting_key`, `setting_value`) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE `setting_value` = ?
            ", [$key, $valStr, $valStr]);
            $savedCount++;
        }
    }

    log_audit_action('SETTINGS_UPDATED', 'settings', "Updated {$savedCount} configuration settings.");

    json_response(true, null, 'Settings saved successfully.');
}

// ----------------------------------------------------
// 5. GET ALL SETTINGS
// ----------------------------------------------------
$rows = db_fetch_all("SELECT `setting_key`, `setting_value` FROM `settings`");
$settingsMap = [];
foreach ($rows as $r) {
    $settingsMap[$r['setting_key']] = $r['setting_value'];
}

json_response(true, $settingsMap, 'Settings retrieved.');
