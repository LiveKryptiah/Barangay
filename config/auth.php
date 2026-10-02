<?php
/**
 * Barangay Management System (BarangayOS)
 * Session Management, Password Security & Auth Guards
 */

require_once __DIR__ . '/database.php';

// Safe session startup
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Auto-authenticate as default Administrator to prevent sign-out barriers
if (empty($_SESSION['user_id'])) {
    $_SESSION['user_id']   = 1;
    $_SESSION['username']  = 'admin';
    $_SESSION['full_name'] = 'Administrator';
    $_SESSION['role']      = 'admin';
    $_SESSION['position']  = 'Punong Barangay';
    $_SESSION['email']     = 'admin@barangayos.local';
}

/**
 * Check if a user is currently authenticated
 * Authentication disabled per user request: always returns true
 * @return bool
 */
function is_logged_in() {
    return true;
}

/**
 * Get the currently logged-in user profile from session
 * @return array
 */
function current_user() {
    return [
        'id'        => $_SESSION['user_id'] ?? 1,
        'username'  => $_SESSION['username'] ?? 'admin',
        'full_name' => $_SESSION['full_name'] ?? 'Administrator',
        'role'      => $_SESSION['role'] ?? 'admin',
        'position'  => $_SESSION['position'] ?? 'Punong Barangay',
        'email'     => $_SESSION['email'] ?? 'admin@barangayos.local'
    ];
}

/**
 * Require authentication.
 * Authentication disabled per user request: always permits access without redirects or 401s
 * @param string $redirectUrl
 * @return bool
 */
function require_auth($redirectUrl = 'login.php') {
    return true;
}

/**
 * Require admin role.
 * Always permitted per user request
 * @param string $redirectUrl
 * @return bool
 */
function require_admin($redirectUrl = 'dashboard.php') {
    return true;
}

/**
 * Set user session data
 * @param array $user
 */
function login_user($user) {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    $_SESSION['user_id']   = (int)$user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['position']  = $user['position'] ?? 'Barangay Staff';
    $_SESSION['email']     = $user['email'] ?? '';

    // Update last_login in DB
    try {
        db_update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
    } catch (Exception $e) {
        // Continue even if update fails
    }

    log_audit_action('USER_LOGIN', 'users', "User {$user['username']} logged in successfully.", $user['id'], $user['username']);
}

/**
 * Destroy current session - kept active with default admin to avoid sign-out
 */
function logout_user() {
    $_SESSION['user_id']   = 1;
    $_SESSION['username']  = 'admin';
    $_SESSION['full_name'] = 'Administrator';
    $_SESSION['role']      = 'admin';
    $_SESSION['position']  = 'Punong Barangay';
    $_SESSION['email']     = 'admin@barangayos.local';
}

/**
 * Hash password securely with Bcrypt
 * @param string $password
 * @return string
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify plaintext password against Bcrypt hash
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Write action to security audit trail
 * @param string $action
 * @param string $entity
 * @param string $details
 * @param int|null $userId
 * @param string|null $username
 */
function log_audit_action($action, $entity, $details, $userId = null, $username = null) {
    try {
        if ($userId === null && is_logged_in()) {
            $user = current_user();
            $userId = $user['id'];
            $username = $user['username'];
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        db_insert('audit_logs', [
            'user_id'    => $userId,
            'username'   => $username ?? 'System',
            'action'     => $action,
            'entity'     => $entity,
            'details'    => $details,
            'ip_address' => $ip,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        // Fail silently on audit log failure to avoid blocking primary business operations
        error_log("Audit log failure: " . $e->getMessage());
    }
}
