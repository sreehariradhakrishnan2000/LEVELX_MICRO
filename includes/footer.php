    </main>

    <!-- Global Footer -->
    <footer class="site-footer">
        <div class="container footer-container">
            <div class="footer-brand">
                <div class="brand-logo footer-logo">
                    <span class="logo-icon">⚡</span>
                    <span class="logo-text"><?= APP_NAME ?></span>
                </div>
                <p class="footer-desc"><?= APP_TAGLINE ?></p>
                <div class="tech-badge">
                    <span>PHP 8.2</span> • <span>MySQL</span> • <span>Vanilla JS & CSS</span>
                </div>
            </div>
            
            <div class="footer-links">
                <h4>Quick Navigation</h4>
                <ul>
                    <li><a href="<?= $baseUrl ?>/index.php">Upcoming Events</a></li>
                    <li><a href="<?= $baseUrl ?>/register.php">Event Registration</a></li>
                    <li><a href="<?= $baseUrl ?>/admin/index.php">Admin Portal</a></li>
                    <li><a href="<?= $baseUrl ?>/database/setup.php">Database Wizard</a></li>
                </ul>
            </div>

            <div class="footer-admin-card">
                <h4>Admin Access</h4>
                <p>Default credentials for testing:</p>
                <div class="credentials-box">
                    <code>User: admin</code><br>
                    <code>Pass: admin123</code>
                </div>
                <a href="<?= $baseUrl ?>/admin/login.php" class="btn-admin-pill">Portal Login →</a>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container footer-bottom-content">
                <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> Inc. All rights reserved.</p>
                <p class="footer-tag">Crafted for fast, reliable & secure event registration.</p>
            </div>
        </div>
    </footer>

    <!-- Main JS Script -->
    <script src="<?= $baseUrl ?>/assets/js/main.js"></script>
</body>
</html>
