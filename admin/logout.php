<?php
/**
 * Admin Logout Handler
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

logoutAdmin();
setFlashMessage('info', 'You have been safely logged out.');
header('Location: ' . getBaseUrl() . '/admin/login.php');
exit;
