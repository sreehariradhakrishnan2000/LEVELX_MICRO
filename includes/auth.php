<?php
/**
 * Admin Authentication and Authorization Module
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && !empty($_SESSION['admin_user_id']);
}

function getCurrentAdmin(): ?array {
    if (!isAdminLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['admin_user_id'],
        'username' => $_SESSION['admin_username'] ?? '',
        'email' => $_SESSION['admin_email'] ?? ''
    ];
}

function loginAdmin(string $username, string $password): bool {
    $username = trim($username);
    if (empty($username) || empty($password)) {
        return false;
    }

    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM admins WHERE username = :username OR email = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        // Regenerate session ID to prevent session fixation attacks
        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_login_time'] = time();

        return true;
    }

    return false;
}

function logoutAdmin(): void {
    $_SESSION['admin_logged_in'] = false;
    unset($_SESSION['admin_logged_in'], $_SESSION['admin_user_id'], $_SESSION['admin_username'], $_SESSION['admin_email'], $_SESSION['admin_login_time']);
    if (!headers_sent()) {
        session_regenerate_id(true);
    }
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        $baseUrl = getBaseUrl();
        setFlashMessage('error', 'You must be logged in as an administrator to access the admin area.');
        header('Location: ' . $baseUrl . '/admin/login.php');
        exit;
    }
}
