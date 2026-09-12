<?php
/**
 * Barangay Management System (BarangayOS)
 * Application Gateway & Route Dispatcher
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

// Check if database is initialized
try {
    $db = get_db_connection();
    $test = $db->query("SHOW TABLES LIKE 'users'")->fetch();
    if (!$test) {
        header('Location: install.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: install.php');
    exit;
}

// Redirect according to authentication state
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: portal.php');
    exit;
}
