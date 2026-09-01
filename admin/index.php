<?php
/**
 * Admin Dashboard - Manage Registrations
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Require Admin Login
requireAdmin();

$pageTitle = "Admin Dashboard - Registrations";

// Handle Delete Request (POST with CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_registration') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (verifyCSRFToken($csrfToken)) {
        $regId = (int) ($_POST['registration_id'] ?? 0);
        if ($regId > 0) {
            $deleted = deleteRegistration($regId);
            if ($deleted) {
                setFlashMessage('success', "Registration #{$regId} was successfully removed.");
            } else {
                setFlashMessage('error', "Could not delete registration #{$regId}.");
            }
        }
    } else {
        setFlashMessage('error', "Security token mismatch. Delete action canceled.");
    }
    header('Location: ' . getBaseUrl() . '/admin/index.php');
    exit;
}

// Search & Filter Parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$eventId = isset($_GET['event_id']) && (int)$_GET['event_id'] > 0 ? (int)$_GET['event_id'] : null;

// Fetch Data
try {
    $registrations = getAllRegistrations($search, $eventId);
    $events = getAllEvents();
    $stats = getDashboardStats();
} catch (Exception $e) {
    $registrations = [];
    $events = [];
    $stats = ['total_registrations' => 0, 'total_events' => 0];
    setFlashMessage('error', 'Database Error: ' . $e->getMessage());
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="admin-title-area">
            <h1>📊 Registration Management Dashboard</h1>
            <p>Welcome, <strong><?= e($_SESSION['admin_username'] ?? 'Administrator') ?></strong>. Manage attendees and monitor event bookings.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?= $baseUrl ?>/admin/events.php" class="btn-ticket btn-ticket-primary">
                ➕ Add / Manage Events
            </a>
            <a href="<?= $baseUrl ?>/index.php" target="_blank" class="btn-ticket btn-ticket-outline">
                🌐 View Public Site ↗
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="admin-stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-purple">🎟️</div>
            <div class="stat-info">
                <h3><?= count($registrations) ?> <span style="font-size: 1rem; font-weight: normal; color: var(--text-muted);">/ <?= $stats['total_registrations'] ?></span></h3>
                <p>Total Registrations</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-emerald">🎪</div>
            <div class="stat-info">
                <h3><?= $stats['total_events'] ?></h3>
                <p>Active Events</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-cyan">🕒</div>
            <div class="stat-info">
                <h3><?= !empty($registrations) ? formatDate($registrations[0]['date_registered'], 'M j') : 'None' ?></h3>
                <p>Latest Registration</p>
            </div>
        </div>
    </div>

    <!-- Registrations Table Card -->
    <div class="table-wrapper-card">
        <!-- Toolbar & Filter Form -->
        <div class="table-toolbar">
            <div>
                <h3 style="font-size: 1.15rem; margin-bottom: 2px;">All Registrations</h3>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Showing <?= count($registrations) ?> participant records</span>
            </div>

            <!-- Search & Filter Form -->
            <form action="<?= $baseUrl ?>/admin/index.php" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="text" 
                       name="search" 
                       placeholder="Search name, email, ref..." 
                       value="<?= e($search ?? '') ?>" 
                       class="form-control" 
                       style="padding: 8px 12px; width: 220px; font-size: 0.88rem;">
                
                <select name="event_id" class="form-control form-control-select" style="padding: 8px 28px 8px 12px; font-size: 0.88rem; width: 190px;">
                    <option value="">All Events</option>
                    <?php foreach ($events as $ev): ?>
                        <option value="<?= (int)$ev['id'] ?>" <?= ($eventId === (int)$ev['id']) ? 'selected' : '' ?>>
                            <?= e($ev['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn-action btn-action-view" style="padding: 8px 16px;">Filter</button>
                <?php if ($search || $eventId): ?>
                    <a href="<?= $baseUrl ?>/admin/index.php" class="btn-action btn-action-delete" style="padding: 8px 14px;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table Listing Registrations -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Event</th>
                        <th>Date Registered</th>
                        <th style="text-align: right; width: 160px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registrations)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                                <div style="font-size: 2rem; margin-bottom: 8px;">📭</div>
                                <strong style="color: var(--text-primary);">No registrations found</strong>
                                <p style="font-size: 0.85rem; margin-top: 4px;">No attendees match the selected filter or no registrations have been made yet.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($registrations as $reg): ?>
                            <tr>
                                <td>
                                    <span class="badge-id">#<?= (int)$reg['id'] ?></span>
                                </td>
                                <td>
                                    <strong style="color: #ffffff;"><?= e($reg['name']) ?></strong>
                                    <div style="font-size: 0.78rem; color: var(--text-muted); font-family: monospace;">
                                        Ref: <?= e($reg['registration_code']) ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:<?= e($reg['email']) ?>" style="color: #93c5fd;">
                                        <?= e($reg['email']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge-event"><?= e($reg['event_title']) ?></span>
                                    <?php if (!empty($reg['event_date'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 3px;">
                                            📅 <?= formatDate($reg['event_date'], 'M j, Y') ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="color: var(--text-secondary);">
                                        <?= formatDate($reg['date_registered']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <!-- View Action Button -->
                                        <button type="button" 
                                                class="btn-action btn-action-view" 
                                                title="View full registration details"
                                                onclick='viewRegistrationDetails(<?= json_encode([
                                                    'id' => (int)$reg['id'],
                                                    'name' => $reg['name'],
                                                    'email' => $reg['email'],
                                                    'registration_code' => $reg['registration_code'],
                                                    'event_title' => $reg['event_title'],
                                                    'event_date' => !empty($reg['event_date']) ? formatDate($reg['event_date']) : 'N/A',
                                                    'event_location' => $reg['event_location'] ?? 'N/A',
                                                    'date_registered' => formatDate($reg['date_registered'])
                                                ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG) ?>)'>
                                            👁️ View
                                        </button>

                                        <!-- Delete Action Button -->
                                        <button type="button" 
                                                class="btn-action btn-action-delete" 
                                                title="Delete registration record"
                                                onclick="confirmDeleteRegistration(<?= (int)$reg['id'] ?>, '<?= e(addslashes($reg['name'])) ?>', '<?= e(addslashes($reg['event_title'])) ?>')">
                                            🗑️ Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 1. View Registration Details Modal -->
<div class="modal-overlay" id="viewDetailsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">📄 Registration Details</h3>
            <button type="button" class="modal-close" onclick="closeModal('viewDetailsModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="ticket-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 0;">
                <div class="ticket-field">
                    <span class="ticket-field-label">Registration ID</span>
                    <span class="ticket-field-value" id="modalRegId"></span>
                </div>
                <div class="ticket-field">
                    <span class="ticket-field-label">Reference Code</span>
                    <span class="ticket-field-value" id="modalRegCode" style="color: #38bdf8; font-family: monospace;"></span>
                </div>
                <div class="ticket-field" style="grid-column: 1 / -1;">
                    <span class="ticket-field-label">Full Name</span>
                    <span class="ticket-field-value" id="modalRegName"></span>
                </div>
                <div class="ticket-field" style="grid-column: 1 / -1;">
                    <span class="ticket-field-label">Email Address</span>
                    <span class="ticket-field-value" id="modalRegEmail"></span>
                </div>
                <div class="ticket-field" style="grid-column: 1 / -1;">
                    <span class="ticket-field-label">Registered Event</span>
                    <span class="ticket-field-value" id="modalRegEvent" style="color: #a5b4fc;"></span>
                </div>
                <div class="ticket-field">
                    <span class="ticket-field-label">Event Date</span>
                    <span class="ticket-field-value" id="modalEventDate"></span>
                </div>
                <div class="ticket-field">
                    <span class="ticket-field-label">Venue Location</span>
                    <span class="ticket-field-value" id="modalEventLocation"></span>
                </div>
                <div class="ticket-field" style="grid-column: 1 / -1;">
                    <span class="ticket-field-label">Date & Time Registered</span>
                    <span class="ticket-field-value" id="modalRegDate"></span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-ticket btn-ticket-outline" onclick="closeModal('viewDetailsModal')">Close</button>
        </div>
    </div>
</div>

<!-- 2. Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteConfirmModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" style="color: #f87171;">⚠️ Confirm Delete Registration</h3>
            <button type="button" class="modal-close" onclick="closeModal('deleteConfirmModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p style="color: #e2e8f0; font-size: 0.95rem; margin-bottom: 12px;">
                Are you sure you want to permanently delete this registration?
            </p>
            <div style="background: #111827; padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 0.9rem;">
                <p><strong>Participant:</strong> <span id="deleteParticipantName"></span></p>
                <p style="margin-top: 4px;"><strong>Event:</strong> <span id="deleteEventName"></span></p>
            </div>
            <p style="color: var(--text-muted); font-size: 0.82rem; margin-top: 12px;">
                This action cannot be undone. The database record will be removed immediately.
            </p>
        </div>
        <div class="modal-footer">
            <form action="<?= $baseUrl ?>/admin/index.php" method="POST" id="deleteForm">
                <?= renderCSRFInput() ?>
                <input type="hidden" name="action" value="delete_registration">
                <input type="hidden" name="registration_id" id="deleteRegIdInput" value="">
                <button type="button" class="btn-ticket btn-ticket-outline" onclick="closeModal('deleteConfirmModal')">Cancel</button>
                <button type="submit" class="btn-ticket" style="background: var(--danger); color: white;">Yes, Delete Registration</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
