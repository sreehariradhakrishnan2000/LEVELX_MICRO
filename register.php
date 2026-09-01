<?php
/**
 * Registration Page - User Event Registration Form (Premium 2-Column Edition)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Event Registration";

// Fetch all events for the dropdown
try {
    $events = getAllEvents();
} catch (Exception $e) {
    $events = [];
}

// Check if event_id is passed via GET (auto-fill / pre-selection)
$selectedEventId = isset($_GET['event_id']) ? (int) $_GET['event_id'] : (isset($events[0]['id']) ? (int)$events[0]['id'] : 0);
$formData = [
    'name' => '',
    'email' => '',
    'event_id' => $selectedEventId
];
$errors = [];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF Token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrfToken)) {
        $errors['general'] = 'Invalid form session or security token. Please refresh and try again.';
    }

    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['event_id'] = (int) ($_POST['event_id'] ?? 0);
    $selectedEventId = $formData['event_id'];

    if (empty($errors)) {
        $result = createRegistration($formData);

        if ($result['success']) {
            if (!empty($result['is_existing'])) {
                setFlashMessage('info', 'You are already registered for this event! Here is your registration voucher.');
            } else {
                setFlashMessage('success', 'Congratulations! Your event registration has been confirmed.');
            }
            // Redirect to success page with registration code
            header('Location: ' . getBaseUrl() . '/success.php?code=' . urlencode($result['registration_code']));
            exit;
        } else {
            $errors = $result['errors'] ?? ['general' => 'Failed to process registration. Please check your inputs.'];
        }
    }
}

// Find pre-selected event details if any
$selectedEvent = null;
if ($selectedEventId > 0) {
    foreach ($events as $ev) {
        if ((int)$ev['id'] === $selectedEventId) {
            $selectedEvent = $ev;
            break;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="form-2col-layout">
        <!-- Left Column: Event Spotlight Card -->
        <aside class="event-spotlight-card">
            <?php if ($selectedEvent): ?>
                <?php if (!empty($selectedEvent['image_url'])): ?>
                    <img src="<?= e($selectedEvent['image_url']) ?>" alt="<?= e($selectedEvent['title']) ?>" class="spotlight-image">
                <?php endif; ?>
                <div class="spotlight-body">
                    <span class="spotlight-badge"><?= e($selectedEvent['category'] ?? 'Conference') ?></span>
                    <h2 class="spotlight-title"><?= e($selectedEvent['title']) ?></h2>

                    <div class="spotlight-meta-list">
                        <div class="meta-item">
                            <span class="meta-icon">📅</span>
                            <span><strong>Date:</strong> <?= formatDate($selectedEvent['event_date']) ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-icon">📍</span>
                            <span><strong>Venue:</strong> <?= e($selectedEvent['location']) ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-icon">💺</span>
                            <span><strong>Capacity:</strong> <?= e($selectedEvent['capacity'] ?? 100) ?> Attendees</span>
                        </div>
                    </div>

                    <div class="spotlight-desc">
                        <?= e($selectedEvent['description']) ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="spotlight-body" style="text-align: center; padding: 40px 20px;">
                    <div style="font-size: 3rem; margin-bottom: 12px;">🎟️</div>
                    <h3 style="margin-bottom: 8px;">Select an Event</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Choose a summit from the dropdown list to view venue details and reserve your pass.</p>
                </div>
            <?php endif; ?>
        </aside>

        <!-- Right Column: Registration Form Card -->
        <div class="form-card">
            <div class="form-header">
                <h1>Secure Your Event Pass</h1>
                <p>Please enter your contact information to finalize your registration.</p>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-error">
                    <div class="alert-content">
                        <span class="alert-icon">⚠️</span>
                        <span><?= e($errors['general']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= $baseUrl ?>/register.php<?= $selectedEventId ? '?event_id=' . $selectedEventId : '' ?>" method="POST" id="registrationForm" novalidate>
                <?= renderCSRFInput() ?>

                <!-- Full Name Field -->
                <div class="form-group">
                    <label for="name" class="form-label">Full Name <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                               placeholder="e.g. Sarah Connor" 
                               value="<?= e($formData['name']) ?>" 
                               required 
                               autocomplete="name">
                    </div>
                    <?php if (isset($errors['name'])): ?>
                        <div class="form-error">⚠️ <?= e($errors['name']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email Address Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <span class="input-icon">✉️</span>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               placeholder="e.g. sarah.connor@example.com" 
                               value="<?= e($formData['email']) ?>" 
                               required 
                               autocomplete="email">
                    </div>
                    <div class="form-hint">Confirmation badge, calendar invite, and ticket voucher will be sent to this email.</div>
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error">⚠️ <?= e($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Event Select Field (Auto-filled / Dropdown) -->
                <div class="form-group">
                    <label for="event_id" class="form-label">Select Summit / Conference <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <span class="input-icon">📅</span>
                        <select id="event_id" 
                                name="event_id" 
                                class="form-control form-control-select <?= isset($errors['event_id']) ? 'is-invalid' : '' ?>" 
                                onchange="if(this.value) window.location.href='register.php?event_id=' + this.value;"
                                required>
                            <option value="">-- Choose an Event (<?= count($events) ?> Available) --</option>
                            <?php foreach ($events as $ev): ?>
                                <option value="<?= (int)$ev['id'] ?>" <?= ((int)$formData['event_id'] === (int)$ev['id']) ? 'selected' : '' ?>>
                                    <?= e($ev['title']) ?> (<?= formatDate($ev['event_date'], 'M j, Y') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (isset($errors['event_id'])): ?>
                        <div class="form-error">⚠️ <?= e($errors['event_id']) ?></div>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 32px;">
                    <button type="submit" class="btn-submit">
                        <span>Confirm & Complete Registration</span>
                        <span>→</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
