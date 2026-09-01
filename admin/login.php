<?php
/**
 * Admin Portal - Login Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = "Admin Login";

// If already logged in, redirect to dashboard
if (isAdminLoggedIn()) {
    header('Location: ' . getBaseUrl() . '/admin/index.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrfToken)) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            if (loginAdmin($username, $password)) {
                setFlashMessage('success', 'Welcome back, ' . e($_SESSION['admin_username']) . '! You are logged in.');
                header('Location: ' . getBaseUrl() . '/admin/index.php');
                exit;
            } else {
                $error = 'Invalid admin username/email or password.';
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="form-layout" style="max-width: 480px;">
        <div class="form-card">
            <div class="form-header">
                <div style="font-size: 2.5rem; margin-bottom: 12px;">🔐</div>
                <h1>Admin Access</h1>
                <p>Sign in to manage event registrations and attendees.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <div class="alert-content">
                        <span class="alert-icon">⚠️</span>
                        <span><?= e($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= $baseUrl ?>/admin/login.php" method="POST" novalidate>
                <?= renderCSRFInput() ?>

                <div class="form-group">
                    <label for="username" class="form-label">Username or Email</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="form-control" 
                               placeholder="e.g. admin" 
                               value="<?= e($username) ?>" 
                               required 
                               autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="••••••••" 
                               required 
                               autocomplete="current-password">
                    </div>
                </div>

                <div style="margin-top: 28px;">
                    <button type="submit" class="btn-submit">
                        <span>Sign In to Dashboard</span>
                        <span>→</span>
                    </button>
                </div>
            </form>

            <div class="credentials-box" style="margin-top: 24px; text-align: center;">
                <span style="color: var(--text-muted); font-size: 0.8rem;">Demo Admin Credentials:</span><br>
                <strong>admin</strong> / <strong>admin123</strong>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
