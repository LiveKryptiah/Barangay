<?php
/**
 * Barangay Management System (BarangayOS)
 * Authentication REST API Endpoint
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$input = get_json_input();
if (!empty($input['action'])) {
    $action = $input['action'];
}

switch ($action) {
    // ----------------------------------------------------
    // 1. SIGN IN / LOGIN
    // ----------------------------------------------------
    case 'login':
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            json_response(false, null, 'Username and password are required.', 400);
        }

        $user = db_fetch_one("SELECT * FROM `users` WHERE `username` = ?", [$username]);

        if (!$user) {
            json_response(false, null, 'Invalid username or password.', 401);
        }

        if ($user['status'] !== 'active') {
            json_response(false, null, 'Your account is ' . $user['status'] . '. Please contact the administrator.', 403);
        }

        if (!verify_password($password, $user['password_hash'])) {
            json_response(false, null, 'Invalid username or password.', 401);
        }

        login_user($user);

        // Strip password hash from returned object
        unset($user['password_hash']);

        json_response(true, [
            'user' => $user,
            'redirect' => 'dashboard.php'
        ], 'Authentication successful.');
        break;

    // ----------------------------------------------------
    // 2. SIGN OUT / LOGOUT
    // ----------------------------------------------------
    case 'logout':
        logout_user();
        json_response(true, ['redirect' => 'login.php'], 'Signed out successfully.');
        break;

    // ----------------------------------------------------
    // 3. REGISTRATION / ONBOARDING
    // ----------------------------------------------------
    case 'register':
        $username  = trim($input['username'] ?? '');
        $password  = $input['password'] ?? '';
        $fullName  = trim($input['full_name'] ?? '');
        $email     = trim($input['email'] ?? '');
        $role      = $input['role'] ?? 'staff';
        $position  = trim($input['position'] ?? 'Barangay Staff');

        if (empty($username) || empty($password) || empty($fullName)) {
            json_response(false, null, 'Username, password, and full name are required.', 400);
        }

        if (strlen($username) < 3) {
            json_response(false, null, 'Username must be at least 3 characters.', 400);
        }

        if (strlen($password) < 6) {
            json_response(false, null, 'Password must be at least 6 characters.', 400);
        }

        // Validate unique username
        $existing = db_fetch_one("SELECT `id` FROM `users` WHERE `username` = ?", [$username]);
        if ($existing) {
            json_response(false, null, 'Username already taken. Please choose another.', 409);
        }

        // Validate role
        $validRoles = ['admin', 'staff', 'official'];
        if (!in_array($role, $validRoles)) {
            $role = 'staff';
        }

        $passwordHash = hash_password($password);

        $newUserId = db_insert('users', [
            'username'      => $username,
            'password_hash' => $passwordHash,
            'full_name'     => $fullName,
            'email'         => $email,
            'role'          => $role,
            'position'      => $position,
            'status'        => 'active'
        ]);

        $newUser = db_fetch_one("SELECT `id`, `username`, `full_name`, `email`, `role`, `position`, `status` FROM `users` WHERE `id` = ?", [$newUserId]);

        // Auto-login newly registered user
        login_user($newUser);

        log_audit_action('USER_REGISTERED', 'users', "User {$username} registered account with role {$role}.", $newUserId, $username);

        json_response(true, [
            'user' => $newUser,
            'redirect' => 'dashboard.php'
        ], 'Registration successful. Welcome to BarangayOS!', 201);
        break;

    // ----------------------------------------------------
    // 4. CHECK CURRENT ACTIVE SESSION
    // ----------------------------------------------------
    case 'session':
        if (!is_logged_in()) {
            json_response(false, null, 'No active session.', 401);
        }

        $user = current_user();
        json_response(true, ['user' => $user], 'Active session.');
        break;

    // ----------------------------------------------------
    // 5. CHECK FIRST RUN / ADMIN STATUS
    // ----------------------------------------------------
    case 'check_first_run':
        $adminCount = db_count('users', "`role` = 'admin'");
        json_response(true, [
            'has_admin' => ($adminCount > 0),
            'total_users' => db_count('users')
        ]);
        break;

    default:
        json_response(false, null, 'Unknown authentication action.', 400);
}
