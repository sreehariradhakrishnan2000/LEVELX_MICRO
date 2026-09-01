<?php
/**
 * Registration Page - User Event Registration Form
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Event Registration Form";

// Fetch all events for the dropdown
try {
    $events = getAllEvents();
} catch (Exception $e) {
    $events = [];
}

// Check if event_id is passed via GET (auto-fill / pre-selection)
$selectedEventId = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
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
                setFlashMessage('info', 'You were already registered for this event! Here is your registration summary.');
            } else {
                setFlashMessage('success', 'Congratulations! Your event registration was successful.');
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
    <div class="form-layout">
        <div class="form-card">
            <div class="form-header">
                <h1>🎪 Event Registration</h1>
                <p>Fill in your contact information below to secure your event seat.</p>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-error">
                    <div class="alert-content">
                        <span class="alert-icon">⚠️</span>
                        <span><?= e($errors['general']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($selectedEvent): ?>
                <div class="event-preview-banner">
                    <div class="preview-badge">🎟️</div>
                    <div class="preview-details">
                        <h4><?= e($selectedEvent['title']) ?></h4>
                        <p>📅 <?= formatDate($selectedEvent['event_date']) ?> &nbsp;•&nbsp; 📍 <?= e($selectedEvent['location']) ?></p>
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
                               placeholder="e.g. John Doe" 
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
                               placeholder="e.g. john@example.com" 
                               value="<?= e($formData['email']) ?>" 
                               required 
                               autocomplete="email">
                    </div>
                    <div class="form-hint">Confirmation badge and registration details will be associated with this email.</div>
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error">⚠️ <?= e($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Event Select Field (Auto-filled / Dropdown) -->
                <div class="form-group">
                    <label for="event_id" class="form-label">Selected Event <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <span class="input-icon">📅</span>
                        <select id="event_id" 
                                name="event_id" 
                                class="form-control form-control-select <?= isset($errors['event_id']) ? 'is-invalid' : '' ?>" 
                                required>
                            <option value="">-- Choose an Event --</option>
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
                        <span>Complete Registration</span>
                        <span>→</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
