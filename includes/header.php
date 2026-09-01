<?php
/**
 * Global Header Component
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$pageTitle = isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME . ' | ' . APP_TAGLINE;
$baseUrl = getBaseUrl();
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isAdminSection = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin');
$flashMessages = getFlashMessages();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
</head>
<body class="<?= $isAdminSection ? 'admin-layout' : 'public-layout' ?>">
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="<?= $baseUrl ?>/index.php" class="brand-logo">
                <span class="logo-icon">⚡</span>
                <span class="logo-text"><?= APP_NAME ?></span>
            </a>

            <div class="nav-links" id="navLinks">
                <a href="<?= $baseUrl ?>/index.php" class="nav-link <?= ($currentScript === 'index.php' && !$isAdminSection) ? 'active' : '' ?>">
                    🎪 Upcoming Events
                </a>
                <a href="<?= $baseUrl ?>/register.php" class="nav-link <?= ($currentScript === 'register.php') ? 'active' : '' ?>">
                    📝 Register
                </a>

                <?php if (isAdminLoggedIn()): ?>
                    <a href="<?= $baseUrl ?>/admin/index.php" class="nav-link <?= ($isAdminSection && $currentScript === 'index.php') ? 'active' : '' ?>">
                        📊 Admin Dashboard
                    </a>
                    <a href="<?= $baseUrl ?>/admin/events.php" class="nav-link <?= ($isAdminSection && $currentScript === 'events.php') ? 'active' : '' ?>">
                        ➕ Manage Events
                    </a>
                    <a href="<?= $baseUrl ?>/admin/logout.php" class="nav-link nav-btn-danger">
                        🚪 Logout (<?= e($_SESSION['admin_username'] ?? 'Admin') ?>)
                    </a>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>/admin/login.php" class="nav-link nav-btn <?= ($currentScript === 'login.php') ? 'active' : '' ?>">
                        🔐 Admin Login
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Hamburger Toggle -->
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>
    </nav>

    <!-- Flash Notifications Container -->
    <?php if (!empty($flashMessages)): ?>
        <div class="container flash-container">
            <?php foreach ($flashMessages as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible">
                    <div class="alert-content">
                        <span class="alert-icon">
                            <?= $flash['type'] === 'success' ? '✅' : ($flash['type'] === 'error' ? '⚠️' : 'ℹ️') ?>
                        </span>
                        <span><?= e($flash['message']) ?></span>
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content Wrapper -->
    <main class="main-content">
