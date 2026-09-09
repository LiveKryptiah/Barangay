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

/**
 * Check if a user is currently authenticated
 * @return bool
 */
function is_logged_in() {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['username']);
}

/**
 * Get the currently logged-in user profile from session
 * @return array|null
 */
function current_user() {
    if (!is_logged_in()) {
        return null;
    }

    return [
        'id'        => $_SESSION['user_id'],
        'username'  => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'] ?? 'Barangay Staff',
        'role'      => $_SESSION['role'] ?? 'staff',
        'position'  => $_SESSION['position'] ?? 'Staff',
        'email'     => $_SESSION['email'] ?? ''
    ];
}

/**
 * Require authentication. Redirects to login.php for pages, or returns 401 for API endpoints
 * @param string $redirectUrl
 */
function require_auth($redirectUrl = 'login.php') {
    if (!is_logged_in()) {
        // Detect if request is an API request (JSON)
        $isApi = (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
              || (strpos($_SERVER['REQUEST_URI'], '/api/') !== false);

        if ($isApi) {
            json_response(false, null, 'Unauthorized. Please sign in.', 401);
        } else {
            header("Location: {$redirectUrl}");
            exit;
        }
    }
}

/**
 * Require admin role.
 * @param string $redirectUrl
 */
function require_admin($redirectUrl = 'dashboard.php') {
    require_auth();
    $user = current_user();

    if (($user['role'] ?? '') !== 'admin') {
        $isApi = (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
              || (strpos($_SERVER['REQUEST_URI'], '/api/') !== false);

        if ($isApi) {
            json_response(false, null, 'Forbidden. Administrator privileges required.', 403);
        } else {
            header("Location: {$redirectUrl}");
            exit;
        }
    }
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
 * Destroy current session
 */
function logout_user() {
    if (is_logged_in()) {
        $user = current_user();
        log_audit_action('USER_LOGOUT', 'users', "User {$user['username']} logged out.", $user['id'], $user['username']);
    }

    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
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
