<?php
/**
 * Barangay Management System (BarangayOS)
 * Shared Page Header & Layout Inclusion
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// If $pageTitle is not set, provide default
$pageTitle = $pageTitle ?? 'Barangay Management System';

// Require auth by default unless $skipAuth is true (e.g. login, register, public portal)
if (empty($skipAuth)) {
    require_auth('login.php');
}

$currentUser = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> &bull; BarangayOS</title>
  <link rel="stylesheet" href="css/design-system.css">
  <script src="js/components/theme.js"></script>
  <script src="js/api.js"></script>
