<?php
/**
 * Registration Success & Summary Page
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Registration Confirmation Summary";

$code = isset($_GET['code']) ? trim($_GET['code']) : '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$registration = null;

if (!empty($code)) {
    $registration = getRegistrationByCode($code);
} elseif ($id > 0) {
    $registration = getRegistrationById($id);
}

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="success-layout">
        <?php if ($registration): ?>
            <div class="success-badge-icon">🎉</div>
            <h1 class="success-title">You're Officially Registered!</h1>
            <p class="success-subtitle">A seat has been reserved for you. Please retain your registration reference below.</p>

            <!-- Registration Summary Ticket Card -->
            <div class="ticket-card" id="registrationTicket">
                <div class="ticket-header">
                    <div>
                        <span class="ticket-field-label">Event Name</span>
                        <h2 class="ticket-event-name"><?= e($registration['event_title']) ?></h2>
                    </div>
                    <div class="ticket-code-pill">
                        Ref: <?= e($registration['registration_code']) ?>
                    </div>
                </div>

                <div class="ticket-grid">
                    <div class="ticket-field">
                        <span class="ticket-field-label">Attendee Name</span>
                        <span class="ticket-field-value">👤 <?= e($registration['name']) ?></span>
                    </div>
                    <div class="ticket-field">
                        <span class="ticket-field-label">Registered Email</span>
                        <span class="ticket-field-value">✉️ <?= e($registration['email']) ?></span>
                    </div>
                    <div class="ticket-field">
                        <span class="ticket-field-label">Event Date & Time</span>
                        <span class="ticket-field-value">📅 <?= formatDate($registration['event_date']) ?></span>
                    </div>
                    <div class="ticket-field">
                        <span class="ticket-field-label">Event Venue</span>
                        <span class="ticket-field-value">📍 <?= e($registration['event_location']) ?></span>
                    </div>
                    <div class="ticket-field">
                        <span class="ticket-field-label">Category</span>
                        <span class="ticket-field-value">🏷️ <?= e($registration['event_category'] ?? 'General') ?></span>
                    </div>
                    <div class="ticket-field">
                        <span class="ticket-field-label">Date Registered</span>
                        <span class="ticket-field-value">🕒 <?= formatDate($registration['date_registered']) ?></span>
                    </div>
                </div>

                <?php if (!empty($registration['event_description'])): ?>
                    <div class="ticket-description">
                        <strong>Event Overview:</strong><br>
                        <?= e($registration['event_description']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="ticket-actions">
                <button type="button" class="btn-ticket btn-ticket-primary" onclick="copyRegistrationCode('<?= e($registration['registration_code']) ?>', this)">
                    📋 Copy Reference Code
                </button>
                <button type="button" class="btn-ticket btn-ticket-outline" onclick="window.print();">
                    🖨️ Print Summary
                </button>
                <a href="<?= $baseUrl ?>/index.php" class="btn-ticket btn-ticket-outline">
                    🎪 Explore More Events
                </a>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">❓</div>
                <h3>Registration Not Found</h3>
                <p>We could not locate this registration record. It may have been modified or deleted.</p>
                <div style="margin-top: 24px;">
                    <a href="<?= $baseUrl ?>/index.php" class="btn-ticket btn-ticket-primary">Return to Homepage</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
