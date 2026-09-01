<?php
/**
 * Homepage - Upcoming Events Showcase
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Upcoming Events & Conferences";

try {
    $events = getAllEvents();
} catch (Exception $e) {
    $events = [];
    $dbError = $e->getMessage();
}

// Extract unique categories for filter dropdown
$categories = [];
if (!empty($events)) {
    $categories = array_unique(array_filter(array_column($events, 'category')));
    sort($categories);
}

include __DIR__ . '/includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <div class="hero-badge">✨ Explore 2026 Premier Conferences & Meetups</div>
        <h1 class="hero-title">Experience Unforgettable <span class="gradient-text">Events & Tech Summits</span></h1>
        <p class="hero-subtitle">Browse industry-leading workshops, summits, and networking events. Reserve your spot seamlessly in seconds.</p>

        <!-- Search & Filter Controls -->
        <div class="search-filter-bar">
            <div class="search-input-wrapper">
                <span class="search-icon">🔍</span>
                <input type="text" id="eventSearchInput" class="search-input" placeholder="Search events by keyword or topic..." autocomplete="off">
            </div>
            <select id="eventCategoryFilter" class="category-filter">
                <option value="all">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<div class="container">
    <div class="section-header">
        <div>
            <h2 class="section-title">Featured Upcoming Events</h2>
            <p class="section-desc">Select an event below to view details and proceed with fast registration.</p>
        </div>
        <a href="<?= $baseUrl ?>/register.php" class="btn-register" style="background: rgba(99, 102, 241, 0.15); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.3);">
            Quick Registration Form →
        </a>
    </div>

    <?php if (empty($events)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📅</div>
            <h3>No Events Found</h3>
            <p>There are currently no events listed. If you haven't set up the database, run the setup wizard.</p>
            <div style="margin-top: 20px;">
                <a href="<?= $baseUrl ?>/database/setup.php" class="btn-register">Run Database Setup Wizard</a>
            </div>
        </div>
    <?php else: ?>
        <div class="event-grid" id="eventsGrid">
            <?php foreach ($events as $event): ?>
                <article class="event-card" 
                         data-title="<?= e(strtolower($event['title'])) ?>" 
                         data-desc="<?= e(strtolower($event['description'])) ?>" 
                         data-category="<?= e(strtolower($event['category'] ?? 'general')) ?>">
                    <?php if (!empty($event['image_url'])): ?>
                        <img src="<?= e($event['image_url']) ?>" alt="<?= e($event['title']) ?>" class="event-card-image" loading="lazy">
                    <?php else: ?>
                        <div class="event-card-placeholder">🎪</div>
                    <?php endif; ?>

                    <div class="event-card-body">
                        <span class="event-category-badge"><?= e($event['category'] ?? 'General') ?></span>
                        <h3 class="event-title"><?= e($event['title']) ?></h3>

                        <div class="event-meta">
                            <div class="meta-item">
                                <span class="meta-icon">📅</span>
                                <span><?= formatDate($event['event_date']) ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-icon">📍</span>
                                <span><?= e($event['location']) ?></span>
                            </div>
                        </div>

                        <p class="event-desc"><?= e($event['description']) ?></p>

                        <div class="event-card-footer">
                            <span class="event-capacity">Capacity: <?= e($event['capacity'] ?? 100) ?> seats</span>
                            <a href="<?= $baseUrl ?>/register.php?event_id=<?= (int)$event['id'] ?>" class="btn-register">
                                Register Now →
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Hidden Empty State when search filter finds 0 matches -->
        <div class="empty-state" id="emptyFilterState" style="display: none;">
            <div class="empty-state-icon">🔍</div>
            <h3>No matching events found</h3>
            <p>Try searching for a different keyword or choose another category filter.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
