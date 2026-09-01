<?php
/**
 * Homepage - Premier Events Showcase
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Discover & Register for Premier Global Events";

try {
    $events = getAllEvents();
} catch (Exception $e) {
    $events = [];
    $dbError = $e->getMessage();
}

// Extract unique categories for filter pills
$categories = [];
if (!empty($events)) {
    $categories = array_unique(array_filter(array_column($events, 'category')));
    sort($categories);
}

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="hero-badge">✨ 2026 - 2027 Global Conferences & Technical Summits</div>
        <h1 class="hero-title">Discover, Connect & <span class="gradient-text">Register for World-Class Events</span></h1>
        <p class="hero-subtitle">Explore <?= count($events) ?> hand-curated technology, design, leadership, and innovation conferences. Secure your seat seamlessly in seconds.</p>

        <!-- Search Control -->
        <div class="search-filter-bar">
            <div class="search-input-wrapper">
                <span class="search-icon">🔍</span>
                <input type="text" id="eventSearchInput" class="search-input" placeholder="Search summits by topic, keyword, or speaker..." autocomplete="off">
            </div>
            <span class="search-counter" id="searchCounter"><?= count($events) ?> Events</span>
        </div>

        <!-- Category Filter Pills -->
        <div class="category-pills-wrapper" id="categoryPills">
            <button type="button" class="category-pill active" data-category="all">All Events</button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="category-pill" data-category="<?= e(strtolower($cat)) ?>">
                    <?= e($cat) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Events Catalog Section -->
<div class="container">
    <div class="section-header">
        <div>
            <h2 class="section-title">Upcoming Scheduled Summits</h2>
            <p class="section-desc">Choose an event below to view venue agenda and complete your registration.</p>
        </div>
        <a href="<?= $baseUrl ?>/register.php" class="btn-register" style="background: var(--bg-card); border: 1px solid var(--border-color); color: #93c5fd;">
            <span>Direct Registration Form</span>
            <span>→</span>
        </a>
    </div>

    <?php if (empty($events)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📅</div>
            <h3>No Events Found</h3>
            <p>There are currently no events listed. Please run the database setup wizard to synchronize the catalog.</p>
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
                    
                    <div class="event-card-media">
                        <?php if (!empty($event['image_url'])): ?>
                            <img src="<?= e($event['image_url']) ?>" alt="<?= e($event['title']) ?>" class="event-card-image" loading="lazy">
                        <?php else: ?>
                            <div class="event-card-placeholder">🎪</div>
                        <?php endif; ?>
                        <div class="event-card-overlay"></div>
                        <span class="card-category-badge"><?= e($event['category'] ?? 'General') ?></span>
                    </div>

                    <div class="event-card-body">
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
                            <span class="event-capacity-info">💺 <?= e($event['capacity'] ?? 100) ?> Total Seats</span>
                            <a href="<?= $baseUrl ?>/register.php?event_id=<?= (int)$event['id'] ?>" class="btn-register">
                                <span>Register</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Empty state when search filter matches 0 -->
        <div class="empty-state" id="emptyFilterState" style="display: none;">
            <div class="empty-state-icon">🔍</div>
            <h3>No matching events found</h3>
            <p>Try searching for a different keyword or choose another category filter pill above.</p>
            <div style="margin-top: 18px;">
                <button type="button" class="btn-ticket btn-ticket-outline" onclick="resetFilters()">Reset All Filters</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
