<?php
/**
 * Barangay Management System (BarangayOS)
 * One-Click Web Database Installer & Environment Setup
 */

require_once __DIR__ . '/config/database.php';

$message = null;
$messageType = null;
$installationLog = [];
$isInstalled = false;

// Handle installation POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_install'])) {
    try {
        // 1. Connect to MySQL server (without selecting DB first to allow creation)
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $installationLog[] = "✓ Connected to MySQL server at " . DB_HOST . ":" . DB_PORT;

        // 2. Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $installationLog[] = "✓ Database `" . DB_NAME . "` verified / created";

        // 3. Switch to the database
        $pdo->exec("USE `" . DB_NAME . "`");

        // 4. Read schema.sql
        $schemaFile = __DIR__ . '/database/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found at database/schema.sql");
        }

        $sql = file_get_contents($schemaFile);
        $pdo->exec($sql);
        $installationLog[] = "✓ Relational schema executed successfully (10 tables established)";

        // 5. Check if default administrator exists
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM `users` WHERE `role` = 'admin'");
        $adminCount = (int)$stmt->fetchColumn();

        if ($adminCount === 0) {
            $defaultPassHash = password_hash('Password123!', PASSWORD_BCRYPT, ['cost' => 10]);
            $adminInsert = $pdo->prepare("
                INSERT INTO `users` (`username`, `password_hash`, `full_name`, `email`, `role`, `position`, `status`)
                VALUES (?, ?, ?, ?, 'admin', 'Punong Barangay / Administrator', 'active')
            ");
            $adminInsert->execute(['admin', $defaultPassHash, 'System Administrator', 'admin@barangay.local']);
            $installationLog[] = "✓ Initial administrator account provisioned (username: 'admin', default pass: 'Password123!')";
        } else {
            $installationLog[] = "✓ Existing administrator account preserved";
        }

        // 6. Record installation audit log
        $logInsert = $pdo->prepare("
            INSERT INTO `audit_logs` (`user_id`, `username`, `action`, `entity`, `details`, `ip_address`)
            VALUES (NULL, 'System', 'SYSTEM_INITIALIZED', 'database', 'BarangayOS database initialized successfully via install.php', ?)
        ");
        $logInsert->execute([$_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

        $message = "Database installation complete! All tables and configurations are active.";
        $messageType = "success";
        $isInstalled = true;

    } catch (Exception $e) {
        $message = "Installation failed: " . $e->getMessage();
        $messageType = "danger";
        $installationLog[] = "✗ Error: " . $e->getMessage();
    }
}

// Check current status
$connectionOk = false;
$existingTables = [];
try {
    $testDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $testPdo = new PDO($testDsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]);
    if ($testPdo) {
        $connectionOk = true;
        $tablesStmt = $testPdo->query("SHOW TABLES");
        if ($tablesStmt) {
            $existingTables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
            if (count($existingTables) >= 9) {
                $isInstalled = true;
            }
        }
    }
} catch (Exception $e) {
    $connectionOk = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Database Setup &bull; BarangayOS</title>
  <link rel="stylesheet" href="css/design-system.css">
  <script src="js/components/theme.js"></script>
  <style>
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background-color: var(--color-canvas);
      padding: var(--spacing-md);
      position: relative;
    }
    .setup-card {
      width: 100%;
      max-width: 580px;
      background-color: var(--color-canvas-soft);
      border: 1px solid var(--color-hairline-soft);
      border-radius: var(--rounded-md);
      padding: var(--spacing-lg);
      margin: auto;
    }
    .status-badge-ok {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background-color: rgba(16, 185, 129, 0.1);
      color: #10b981;
      padding: 4px 12px;
      border-radius: var(--rounded-full);
      font-size: 0.75rem;
      font-weight: 600;
    }
    .status-badge-warn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background-color: rgba(245, 158, 11, 0.1);
      color: #f59e0b;
      padding: 4px 12px;
      border-radius: var(--rounded-full);
      font-size: 0.75rem;
      font-weight: 600;
    }
    .terminal-box {
      background-color: var(--color-ink);
      color: #e5e7eb;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-size: 0.8125rem;
      border-radius: var(--rounded-sm);
      padding: 16px;
      margin: 16px 0;
      max-height: 200px;
      overflow-y: auto;
      line-height: 1.6;
    }
    .theme-fixed-toggle {
      position: absolute;
      top: 24px;
      right: 24px;
    }
  </style>
</head>
<body>
  <div class="theme-fixed-toggle">
    <button class="btn btn-secondary btn-sm" onclick="toggleTheme()" title="Toggle Theme">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="5"></circle>
        <line x1="12" y1="1" x2="12" y2="3"></line>
        <line x1="12" y1="21" x2="12" y2="23"></line>
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
        <line x1="1" y1="12" x2="3" y2="12"></line>
        <line x1="21" y1="12" x2="23" y2="12"></line>
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
      </svg>
    </button>
  </div>

  <div class="setup-card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--spacing-sm);">
      <div style="display: flex; align-items: center; gap: 8px;">
        <span style="font-weight: 800; font-size: 1.125rem; letter-spacing: -0.02em;">BarangayOS</span>
        <span class="badge-neutral" style="font-size: 0.6875rem;">PHP &amp; MySQL</span>
      </div>
      <?php if ($isInstalled): ?>
        <span class="status-badge-ok">&bull; Ready &amp; Initialized</span>
      <?php else: ?>
        <span class="status-badge-warn">&bull; Setup Required</span>
      <?php endif; ?>
    </div>

    <h1 class="typography-h2" style="margin-bottom: 6px;">Database Installation.</h1>
    <p class="typography-body" style="color: var(--color-text-muted); font-size: 0.875rem; margin-bottom: var(--spacing-md);">
      Initialize and verify the relational MySQL schema, seed the default settings, and provision the administrator account.
    </p>

    <!-- Config status -->
    <div style="background-color: var(--color-canvas); border: 1px solid var(--color-hairline-soft); border-radius: var(--rounded-sm); padding: 14px; margin-bottom: var(--spacing-md);">
      <div class="typography-caption" style="color: var(--color-text-muted); margin-bottom: 8px;">TARGET DATABASE PARAMETERS:</div>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.8125rem;">
        <div><strong>Host:</strong> <?= htmlspecialchars(DB_HOST) ?>:<?= htmlspecialchars(DB_PORT) ?></div>
        <div><strong>Database:</strong> <code><?= htmlspecialchars(DB_NAME) ?></code></div>
        <div><strong>User:</strong> <?= htmlspecialchars(DB_USER) ?></div>
        <div><strong>Password:</strong> <?= empty(DB_PASS) ? '<em>(blank)</em>' : '••••••••' ?></div>
      </div>
      <div class="typography-caption" style="color: var(--color-text-muted); margin-top: 10px; font-size: 0.75rem;">
        Edit <code>config/database.php</code> anytime to adjust connection credentials.
      </div>
    </div>

    <?php if ($message): ?>
      <div style="padding: 12px 16px; border-radius: var(--rounded-sm); margin-bottom: var(--spacing-md); font-size: 0.8125rem; <?= $messageType === 'success' ? 'background-color: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);' : 'background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($installationLog)): ?>
      <div class="typography-caption" style="color: var(--color-text-muted);">EXECUTION LOG:</div>
      <div class="terminal-box">
        <?php foreach ($installationLog as $line): ?>
          <div><?= htmlspecialchars($line) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($isInstalled && empty($installationLog)): ?>
      <div style="margin-bottom: var(--spacing-md); padding: 12px; background-color: var(--color-canvas); border-radius: var(--rounded-sm); border: 1px solid var(--color-hairline-soft);">
        <div class="typography-caption" style="color: var(--color-text-muted); margin-bottom: 6px;">FOUND <?= count($existingTables) ?> EXISTING TABLES:</div>
        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
          <?php foreach ($existingTables as $tbl): ?>
            <span class="badge-neutral" style="font-family: monospace; font-size: 0.6875rem;"><?= htmlspecialchars($tbl) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <form method="POST" style="display: flex; flex-direction: column; gap: 10px;">
      <input type="hidden" name="run_install" value="1">

      <button type="submit" class="btn btn-primary" style="width: 100%; height: 44px; font-size: 0.875rem;">
        <?= $isInstalled ? 'Re-Run / Synchronize Database Schema' : 'Install Database &amp; Provision Admin' ?>
      </button>

      <?php if ($isInstalled): ?>
        <a href="login.php" class="btn btn-secondary" style="width: 100%; height: 44px; font-size: 0.875rem; text-decoration: none; display: flex; align-items: center; justify-content: center;">
          Go to Sign-In Portal &rarr;
        </a>
      <?php endif; ?>
    </form>
  </div>
</body>
</html>
