<?php
/**
 * Admin Portal - Manage & Create Events
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = "Admin - Manage Events";

$errors = [];
$formData = [
    'title' => '',
    'description' => '',
    'event_date' => '',
    'location' => '',
    'category' => 'Technology',
    'capacity' => 100,
    'image_url' => ''
];

// Handle New Event Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_event') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrfToken)) {
        $errors['general'] = 'Security token invalid. Please try again.';
    } else {
        $formData['title'] = trim($_POST['title'] ?? '');
        $formData['description'] = trim($_POST['description'] ?? '');
        $formData['event_date'] = trim($_POST['event_date'] ?? '');
        $formData['location'] = trim($_POST['location'] ?? '');
        $formData['category'] = trim($_POST['category'] ?? 'Technology');
        $formData['capacity'] = (int) ($_POST['capacity'] ?? 100);
        $formData['image_url'] = trim($_POST['image_url'] ?? '');

        if (!isNotEmpty($formData['title'])) {
            $errors['title'] = 'Event title is required.';
        }
        if (!isNotEmpty($formData['description'])) {
            $errors['description'] = 'Event description is required.';
        }
        if (!isNotEmpty($formData['event_date'])) {
            $errors['event_date'] = 'Event date and time is required.';
        }
        if (!isNotEmpty($formData['location'])) {
            $errors['location'] = 'Event venue/location is required.';
        }

        if (empty($errors)) {
            try {
                $eventId = createEvent($formData);
                setFlashMessage('success', 'New event "' . e($formData['title']) . '" created successfully!');
                header('Location: ' . getBaseUrl() . '/admin/events.php');
                exit;
            } catch (Exception $e) {
                $errors['general'] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

try {
    $events = getAllEvents();
} catch (Exception $e) {
    $events = [];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="admin-header">
        <div class="admin-title-area">
            <h1>🎪 Event Management</h1>
            <p>Add new upcoming summits, webinars, and workshops.</p>
        </div>
        <a href="<?= $baseUrl ?>/admin/index.php" class="btn-ticket btn-ticket-outline">
            ← Back to Registrations Table
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 32px; align-items: start;">
        <!-- Left: List of Existing Events -->
        <div class="table-wrapper-card">
            <div class="table-toolbar">
                <h3 style="font-size: 1.1rem;">Existing Events (<?= count($events) ?>)</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Details</th>
                            <th>Date & Venue</th>
                            <th>Capacity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                    No events created yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $ev): ?>
                                <tr>
                                    <td>
                                        <strong style="color: #ffffff;"><?= e($ev['title']) ?></strong>
                                        <div style="margin-top: 4px;">
                                            <span class="event-category-badge" style="margin-bottom: 0; font-size: 0.7rem;">
                                                <?= e($ev['category'] ?? 'General') ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="color: #cbd5e1; font-size: 0.85rem;">
                                            📅 <?= formatDate($ev['event_date']) ?>
                                        </div>
                                        <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 2px;">
                                            📍 <?= e($ev['location']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-id"><?= (int)$ev['capacity'] ?> seats</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Add New Event Form -->
        <div class="form-card" style="padding: 28px;">
            <h3 style="font-size: 1.2rem; margin-bottom: 16px; color: #ffffff;">➕ Create New Event</h3>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">⚠️</span>
                    <span><?= e($errors['general']) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= $baseUrl ?>/admin/events.php" method="POST" novalidate>
                <?= renderCSRFInput() ?>
                <input type="hidden" name="action" value="create_event">

                <div class="form-group">
                    <label class="form-label">Event Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" placeholder="e.g. NextGen Cloud Expo" value="<?= e($formData['title']) ?>" required>
                    <?php if (isset($errors['title'])): ?>
                        <div class="form-error">⚠️ <?= e($errors['title']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control" placeholder="e.g. Technology, Workshop, Security" value="<?= e($formData['category']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Date & Time <span class="required">*</span></label>
                    <input type="datetime-local" name="event_date" class="form-control <?= isset($errors['event_date']) ? 'is-invalid' : '' ?>" value="<?= e($formData['event_date']) ?>" required>
                    <?php if (isset($errors['event_date'])): ?>
                        <div class="form-error">⚠️ <?= e($errors['event_date']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Location / Venue <span class="required">*</span></label>
                    <input type="text" name="location" class="form-control <?= isset($errors['location']) ? 'is-invalid' : '' ?>" placeholder="e.g. Grand Ballroom or Zoom" value="<?= e($formData['location']) ?>" required>
                    <?php if (isset($errors['location'])): ?>
                        <div class="form-error">⚠️ <?= e($errors['location']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Max Capacity</label>
                    <input type="number" name="capacity" class="form-control" min="1" max="10000" value="<?= (int)$formData['capacity'] ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Image URL (Optional)</label>
                    <input type="url" name="image_url" class="form-control" placeholder="https://images.unsplash.com/..." value="<?= e($formData['image_url']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Event Description <span class="required">*</span></label>
                    <textarea name="description" class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" rows="3" placeholder="Brief summary of agenda, speakers, topics..." required><?= e($formData['description']) ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <div class="form-error">⚠️ <?= e($errors['description']) ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-submit" style="padding: 12px 20px;">
                    Publish Event 🚀
                </button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
