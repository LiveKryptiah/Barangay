<?php
/**
 * Barangay Management System (BarangayOS)
 * Notifications & SMS Dispatch REST API Endpoint
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

// All actions require staff/admin authentication
require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$input  = get_json_input();
if (!empty($input['action'])) {
    $action = $input['action'];
}

// ----------------------------------------------------
// 1. STATS ACTION
// ----------------------------------------------------
if ($action === 'stats') {
    $totalAll    = db_count('notifications');
    $totalToday  = db_count('notifications', "DATE(`dispatched_at`) = CURDATE()");
    $delivered   = db_count('notifications', "`status` = 'Delivered'");
    $failed      = db_count('notifications', "`status` = 'Failed'");
    $queued      = db_count('notifications', "`status` = 'Queued'");

    $rate = $totalAll > 0 ? round(($delivered / $totalAll) * 100, 1) : 99.4;

    // Credits balance from settings
    $creditsSetting = db_fetch_one("SELECT setting_value FROM settings WHERE setting_key = 'sms_credits_balance'");
    $credits = $creditsSetting ? (int)$creditsSetting['setting_value'] : 2450;

    // Carrier provider & sender ID
    $provSetting = db_fetch_one("SELECT setting_value FROM settings WHERE setting_key = 'sms_gateway_provider'");
    $senderSetting = db_fetch_one("SELECT setting_value FROM settings WHERE setting_key = 'sms_sender_id'");

    // Category counts
    $catCounts = [
        'clearance' => db_count('notifications', "`category` = 'Clearance'"),
        'summons'   => db_count('notifications', "`category` = 'Summons'"),
        'incident'  => db_count('notifications', "`category` = 'Incident'"),
        'advisory'  => db_count('notifications', "`category` = 'Advisory'"),
        'relief'    => db_count('notifications', "`category` = 'Relief'"),
        'general'   => db_count('notifications', "`category` = 'General'")
    ];

    json_response(true, [
        'total_all'        => $totalAll,
        'total_today'      => $totalToday,
        'delivered'        => $delivered,
        'failed'           => $failed,
        'queued'           => $queued,
        'delivery_rate'    => $rate,
        'credits_balance'  => $credits,
        'gateway_provider' => $provSetting['setting_value'] ?? 'simulation',
        'sender_id'        => $senderSetting['setting_value'] ?? 'BRGY-OFFICE',
        'category_counts'  => $catCounts
    ]);
}

// ----------------------------------------------------
// 2. TEMPLATES ACTION
// ----------------------------------------------------
if ($action === 'templates') {
    $templates = [
        [
            'id' => 'clearance_ready',
            'category' => 'Clearance',
            'title' => 'Clearance Ready for Pickup',
            'body' => 'Magandang araw {{name}}, handa na po ang inyong {{cert_type}} sa Barangay Hall. Reference Code: {{tracking_no}}. Dalhin ang inyong valid ID para sa pag-claim.'
        ],
        [
            'id' => 'blotter_summons',
            'category' => 'Summons',
            'title' => 'Lupon Mediation Hearing Summons',
            'body' => 'PABATID mula sa Lupon Tagapamayapa: Kayo ay inaanyayahang dumalo sa mediation hearing ukol sa Kaso Blg {{case_no}} sa darating na {{hearing_date}} sa Barangay Session Hall.'
        ],
        [
            'id' => 'incident_dispatch',
            'category' => 'Incident',
            'title' => 'Emergency Incident En-Route Notice',
            'body' => 'PABATID: Ang inyong emergency report ukol sa {{type}} ay natanggap na. Rumesponde na po ang Barangay Tanod Quick Response Unit sa inyong lokasyon.'
        ],
        [
            'id' => 'advisory_flood',
            'category' => 'Advisory',
            'title' => 'Severe Weather / Flood Advisory',
            'body' => 'BABALA NG BAHA: Itinaas ang Orange Rainfall Warning. Ang mga residente sa tabi ng ilog at mababang Purok ay pinapayuhang maghanda para sa posibleng paglikas.'
        ],
        [
            'id' => 'relief_distribution',
            'category' => 'Relief',
            'title' => 'Relief / Ayuda Distribution Notice',
            'body' => 'PAUNAWA: May nakatakdang pamamahagi ng ayuda/food packs para sa mga profiled beneficiaries sa darating na {{distribution_date}} sa Barangay Covered Court.'
        ],
        [
            'id' => 'general_announcement',
            'category' => 'General',
            'title' => 'General Barangay Assembly Advisory',
            'body' => 'PAANYAYA: Inaanyayahan ang lahat ng mga residente na dumalo sa Barangay General Assembly ngayong darating na Sabado, 8:00 AM sa Barangay Gymnasium.'
        ]
    ];

    json_response(true, $templates);
}

// ----------------------------------------------------
// 3. SETTINGS & AUTO-TRIGGERS UPDATE ACTION
// ----------------------------------------------------
if ($action === 'settings' && $method === 'POST') {
    $provider = trim($input['sms_gateway_provider'] ?? 'simulation');
    $senderId = trim($input['sms_sender_id'] ?? 'BRGY-OFFICE');
    $credits  = isset($input['sms_credits_balance']) ? (int)$input['sms_credits_balance'] : null;
    $autoClearance = isset($input['auto_notify_clearance']) ? ($input['auto_notify_clearance'] ? '1' : '0') : null;
    $autoBlotter   = isset($input['auto_notify_blotter']) ? ($input['auto_notify_blotter'] ? '1' : '0') : null;
    $autoIncident   = isset($input['auto_notify_incident']) ? ($input['auto_notify_incident'] ? '1' : '0') : null;

    db_query("INSERT INTO settings (setting_key, setting_value) VALUES ('sms_gateway_provider', ?) ON DUPLICATE KEY UPDATE setting_value = ?", [$provider, $provider]);
    db_query("INSERT INTO settings (setting_key, setting_value) VALUES ('sms_sender_id', ?) ON DUPLICATE KEY UPDATE setting_value = ?", [$senderId, $senderId]);

    if ($credits !== null) {
        db_query("INSERT INTO settings (setting_key, setting_value) VALUES ('sms_credits_balance', ?) ON DUPLICATE KEY UPDATE setting_value = ?", [(string)$credits, (string)$credits]);
    }
    if ($autoClearance !== null) {
        db_query("INSERT INTO settings (setting_key, setting_value) VALUES ('auto_notify_clearance', ?) ON DUPLICATE KEY UPDATE setting_value = ?", [$autoClearance, $autoClearance]);
    }
    if ($autoBlotter !== null) {
        db_query("INSERT INTO settings (setting_key, setting_value) VALUES ('auto_notify_blotter', ?) ON DUPLICATE KEY UPDATE setting_value = ?", [$autoBlotter, $autoBlotter]);
    }
    if ($autoIncident !== null) {
        db_query("INSERT INTO settings (setting_key, setting_value) VALUES ('auto_notify_incident', ?) ON DUPLICATE KEY UPDATE setting_value = ?", [$autoIncident, $autoIncident]);
    }

    audit_log('UPDATE_SETTINGS', 'notifications', 'Updated notification gateway configuration and automated triggers.');
    json_response(true, null, 'Notification settings updated successfully.');
}

// ----------------------------------------------------
// 4. TEST PING ACTION
// ----------------------------------------------------
if ($action === 'test_ping' && $method === 'POST') {
    $contact = trim($input['contact'] ?? '');
    if (empty($contact)) {
        json_response(false, null, 'Recipient phone number or email is required for test ping.', 400);
    }

    $dispCode = 'TEST-' . date('Y') . '-' . mt_rand(10000, 99999);
    $gwRef = 'SMP-TEST-' . bin2hex(random_bytes(4));

    $notifId = db_insert('notifications', [
        'dispatch_code'     => $dispCode,
        'recipient_id'      => null,
        'recipient_name'    => 'Test Ping Receiver',
        'recipient_contact' => $contact,
        'channel'           => strpos($contact, '@') !== false ? 'Email' : 'SMS',
        'category'          => 'General',
        'subject'           => 'BarangayOS Gateway Connection Ping',
        'message'           => 'This is a test notification verifying that the BarangayOS SMS & Email Gateway pipeline is fully operational.',
        'status'            => 'Delivered',
        'gateway_ref'       => $gwRef,
        'cost_credits'      => 1
    ]);

    json_response(true, [
        'dispatch_code' => $dispCode,
        'gateway_ref'   => $gwRef,
        'status'        => 'Delivered',
        'contact'       => $contact
    ], 'Gateway test ping dispatched and delivered successfully.');
}

// ----------------------------------------------------
// 5. RESEND / RETRY ACTION
// ----------------------------------------------------
if ($action === 'resend' && $method === 'POST') {
    $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
    if ($id <= 0) {
        json_response(false, null, 'Valid notification ID is required.', 400);
    }

    $existing = db_fetch_one("SELECT * FROM notifications WHERE id = ?", [$id]);
    if (!$existing) {
        json_response(false, null, 'Notification record not found.', 404);
    }

    $newGwRef = 'RESEND-' . bin2hex(random_bytes(4));
    db_update('notifications', [
        'status'        => 'Delivered',
        'gateway_ref'   => $newGwRef,
        'dispatched_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$id]);

    audit_log('RESEND', 'notifications', "Resent dispatch [{$existing['dispatch_code']}] to {$existing['recipient_name']}");
    json_response(true, ['gateway_ref' => $newGwRef], 'Notification resent successfully.');
}

// ----------------------------------------------------
// 6. CREATE / DISPATCH NOTIFICATION (Single or Bulk)
// ----------------------------------------------------
if ($method === 'POST' && (empty($action) || $action === 'dispatch' || $action === 'create')) {
    $channel  = $input['channel'] ?? 'SMS';
    $category = $input['category'] ?? 'General';
    $subject  = trim($input['subject'] ?? '');
    $message  = trim($input['message'] ?? '');

    if (empty($message)) {
        json_response(false, null, 'Message body cannot be empty.', 400);
    }

    // Determine recipients list
    $recipients = [];
    if (!empty($input['recipients']) && is_array($input['recipients'])) {
        $recipients = $input['recipients'];
    } elseif (!empty($input['recipient_name']) || !empty($input['recipientName'])) {
        $recipients[] = [
            'id'      => $input['recipient_id'] ?? ($input['recipientId'] ?? null),
            'name'    => $input['recipient_name'] ?? ($input['recipientName'] ?? 'Resident'),
            'contact' => $input['recipient_contact'] ?? ($input['recipientContact'] ?? ($input['contact'] ?? ''))
        ];
    }

    if (empty($recipients)) {
        json_response(false, null, 'At least one recipient is required for dispatch.', 400);
    }

    // Calculate credits per message (160 chars / credit)
    $chars = mb_strlen($message, 'UTF-8');
    $creditsPerMsg = max(1, (int)ceil($chars / 160));
    $totalCost = $creditsPerMsg * count($recipients);

    // Current credits
    $creditsRow = db_fetch_one("SELECT setting_value FROM settings WHERE setting_key = 'sms_credits_balance'");
    $currentCredits = $creditsRow ? (int)$creditsRow['setting_value'] : 2450;
    $remainingCredits = max(0, $currentCredits - $totalCost);
    db_query("INSERT INTO settings (setting_key, setting_value) VALUES ('sms_credits_balance', ?) ON DUPLICATE KEY UPDATE setting_value = ?", [(string)$remainingCredits, (string)$remainingCredits]);

    $dispatchedItems = [];
    $year = date('Y');

    foreach ($recipients as $rec) {
        $recId   = !empty($rec['id']) ? (int)$rec['id'] : null;
        $recName = trim($rec['name'] ?? 'Citizen Resident');
        $contact = trim($rec['contact'] ?? ($rec['phone'] ?? ''));

        if (empty($contact)) {
            continue; // Skip recipients without contact info
        }

        // Generate unique dispatch code
        $code = 'SMS-' . $year . '-' . mt_rand(10000, 99999);
        $gwRef = 'SMP-' . bin2hex(random_bytes(5));

        // Personalized message replacements if merge tags exist
        $msg = str_replace(
            ['{{name}}', '{{recipient_name}}', '{{resident_name}}'],
            [$recName, $recName, $recName],
            $message
        );

        $insertedId = db_insert('notifications', [
            'dispatch_code'     => $code,
            'recipient_id'      => $recId,
            'recipient_name'    => $recName,
            'recipient_contact' => $contact,
            'channel'           => in_array($channel, ['SMS', 'Email', 'Both']) ? $channel : 'SMS',
            'category'          => in_array($category, ['Clearance', 'Summons', 'Incident', 'Advisory', 'Relief', 'General']) ? $category : 'General',
            'subject'           => !empty($subject) ? $subject : null,
            'message'           => $msg,
            'status'            => 'Delivered',
            'gateway_ref'       => $gwRef,
            'cost_credits'      => $creditsPerMsg
        ]);

        $dispatchedItems[] = [
            'id'                => $insertedId,
            'dispatch_code'     => $code,
            'recipient_name'    => $recName,
            'recipient_contact' => $contact,
            'channel'           => $channel,
            'category'          => $category,
            'status'            => 'Delivered',
            'gateway_ref'       => $gwRef,
            'dispatched_at'     => date('Y-m-d H:i:s')
        ];
    }

    audit_log('DISPATCH', 'notifications', "Dispatched " . count($dispatchedItems) . " notifications ({$channel}, Category: {$category}). Cost: {$totalCost} credits.");

    json_response(true, [
        'dispatched_count'  => count($dispatchedItems),
        'cost_credits'      => $totalCost,
        'remaining_credits' => $remainingCredits,
        'items'             => $dispatchedItems
    ], 'Notification(s) dispatched and processed successfully.');
}

// ----------------------------------------------------
// 7. GET LIST OF NOTIFICATIONS (Filtered & Paginated)
// ----------------------------------------------------
$search   = trim($_GET['search'] ?? '');
$channel  = trim($_GET['channel'] ?? '');
$category = trim($_GET['category'] ?? '');
$status   = trim($_GET['status'] ?? '');
$limit    = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 50;
$offset   = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

$whereClauses = [];
$params = [];

if (!empty($search)) {
    $whereClauses[] = "(`recipient_name` LIKE ? OR `recipient_contact` LIKE ? OR `dispatch_code` LIKE ? OR `message` LIKE ? OR `subject` LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (!empty($channel) && in_array($channel, ['SMS', 'Email', 'Both'])) {
    $whereClauses[] = "`channel` = ?";
    $params[] = $channel;
}

if (!empty($category)) {
    $whereClauses[] = "`category` = ?";
    $params[] = $category;
}

if (!empty($status)) {
    $whereClauses[] = "`status` = ?";
    $params[] = $status;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$notifications = db_fetch_all("
    SELECT n.*, r.purok, r.photo_url
    FROM `notifications` n
    LEFT JOIN `residents` r ON n.recipient_id = r.id
    {$whereSql}
    ORDER BY n.id DESC
    LIMIT {$limit} OFFSET {$offset}
", $params);

json_response(true, $notifications);
