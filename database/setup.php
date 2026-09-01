<?php
/**
 * Database Setup & Migration Script
 * Can be run via CLI: php database/setup.php
 * Or accessed in browser: /database/setup.php
 */

require_once __DIR__ . '/../config/config.php';

$isCli = (php_sapi_name() === 'cli');
$results = [];
$status = 'pending';
$error = null;

try {
    $dbType = getenv('DB_TYPE') ?: 'mysql';

    if ($dbType === 'sqlite') {
        $sqlitePath = getenv('DB_SQLITE_PATH') ?: __DIR__ . '/database.sqlite';
        $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $pdo->exec("PRAGMA foreign_keys = ON;");

        // Create tables for SQLite
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS events (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT NOT NULL,
                event_date TEXT NOT NULL,
                location TEXT NOT NULL,
                category TEXT DEFAULT 'Technology',
                capacity INTEGER DEFAULT 100,
                image_url TEXT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS registrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                event_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                registration_code TEXT UNIQUE NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS admins (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");

        $results[] = "Created SQLite database and tables successfully at {$sqlitePath}";
    } else {
        // MySQL setup
        // Step 1: Connect to server without database selected to create database
        $serverDsn = sprintf("mysql:host=%s;port=%s;charset=%s", DB_HOST, DB_PORT, DB_CHARSET);
        $serverPdo = new PDO($serverDsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $results[] = "Database `" . DB_NAME . "` verified / created.";

        // Step 2: Connect to the specific database
        $dbDsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=%s", DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
        $pdo = new PDO($dbDsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);

        // Step 3: Create tables
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `events` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `event_date` DATETIME NOT NULL,
                `location` VARCHAR(255) NOT NULL,
                `category` VARCHAR(100) DEFAULT 'Technology',
                `capacity` INT DEFAULT 100,
                `image_url` VARCHAR(500) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $results[] = "Table `events` verified / created.";

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `registrations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `event_id` INT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NOT NULL,
                `registration_code` VARCHAR(64) UNIQUE NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT `fk_registrations_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $results[] = "Table `registrations` verified / created.";

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `admins` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(100) UNIQUE NOT NULL,
                `email` VARCHAR(255) UNIQUE NOT NULL,
                `password_hash` VARCHAR(255) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $results[] = "Table `admins` verified / created.";
    }

    // Step 4: Seed sample events if empty
    $eventCount = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    if ($eventCount == 0) {
        $sampleEvents = [
            [
                'title' => 'Global AI & Cloud Summit 2026',
                'description' => 'Explore next-generation artificial intelligence breakthroughs, edge computing architectures, and cloud modernization strategies with industry leaders.',
                'event_date' => '2026-10-15 09:30:00',
                'location' => 'Convention Center, Hall A (Bengaluru / Hybrid)',
                'category' => 'Technology',
                'capacity' => 250,
                'image_url' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&auto=format&fit=crop&q=80'
            ],
            [
                'title' => 'Full-Stack Web Dev Workshop',
                'description' => 'Deep-dive interactive session on modern PHP, REST APIs, reactive frontends, and database scalability optimization.',
                'event_date' => '2026-10-22 14:00:00',
                'location' => 'Tech Hub Arena, Room 402',
                'category' => 'Workshop',
                'capacity' => 80,
                'image_url' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&auto=format&fit=crop&q=80'
            ],
            [
                'title' => 'Cybersecurity Defense & Zero Trust',
                'description' => 'Learn practical penetration testing defense, vulnerability scanning, and how to implement Zero-Trust architecture in enterprise environments.',
                'event_date' => '2026-11-05 10:00:00',
                'location' => 'Cyber Park Auditorium',
                'category' => 'Security',
                'capacity' => 150,
                'image_url' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800&auto=format&fit=crop&q=80'
            ],
            [
                'title' => 'Startup Pitch & Networking Night',
                'description' => 'Connect with venture capitalists, pitch disruptive startup ideas, and network with passionate founders and innovators.',
                'event_date' => '2026-11-18 18:00:00',
                'location' => 'Skyline Lounge & Rooftop Garden',
                'category' => 'Networking',
                'capacity' => 120,
                'image_url' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&auto=format&fit=crop&q=80'
            ]
        ];

        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location, category, capacity, image_url) VALUES (:title, :description, :event_date, :location, :category, :capacity, :image_url)");
        foreach ($sampleEvents as $ev) {
            $stmt->execute($ev);
        }
        $results[] = "Seeded " . count($sampleEvents) . " sample upcoming events.";
    } else {
        $results[] = "Events table already contains {$eventCount} events.";
    }

    // Step 5: Seed default admin user
    $adminCount = $pdo->query("SELECT COUNT(*) FROM admins WHERE username = 'admin'")->fetchColumn();
    if ($adminCount == 0) {
        $adminPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $adminStmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash) VALUES (:username, :email, :password_hash)");
        $adminStmt->execute([
            'username' => 'admin',
            'email' => 'admin@eventsphere.com',
            'password_hash' => $adminPasswordHash
        ]);
        $results[] = "Created default admin account: Username: <strong>admin</strong> | Password: <strong>admin123</strong>";
    } else {
        $results[] = "Admin account `admin` already exists.";
    }

    $status = 'success';

} catch (Exception $e) {
    $status = 'error';
    $error = $e->getMessage();
}

if ($isCli && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    echo "==========================================\n";
    echo " EventSphere Database Setup & Migration\n";
    echo "==========================================\n";
    if ($status === 'success') {
        foreach ($results as $res) {
            echo "[OK] " . strip_tags($res) . "\n";
        }
        echo "\nSetup completed successfully!\n";
        echo "Default Admin: admin / admin123\n";
    } else {
        echo "[ERROR] " . $error . "\n";
        exit(1);
    }
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - <?= APP_NAME ?></title>
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: #334155;
            --success: #10b981;
            --error: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }
        .setup-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 36px;
            max-width: 580px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        }
        .header {
            margin-bottom: 24px;
            text-align: center;
        }
        .header h1 {
            font-size: 1.75rem;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #a5b4fc, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .header p { color: var(--text-muted); font-size: 0.95rem; }
        .log-list {
            list-style: none;
            margin: 20px 0;
            background: #090d16;
            border-radius: 10px;
            padding: 16px;
            border: 1px solid var(--border);
        }
        .log-item {
            padding: 8px 12px;
            font-size: 0.9rem;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #1e293b;
        }
        .log-item:last-child { border-bottom: none; }
        .log-item .icon { font-size: 1.1rem; }
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid var(--error);
            color: #fca5a5;
            padding: 14px 18px;
            border-radius: 8px;
            margin: 16px 0;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        .admin-info {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 10px;
            padding: 16px;
            margin: 20px 0;
        }
        .admin-info h3 { font-size: 0.95rem; color: #a5b4fc; margin-bottom: 8px; }
        .admin-info p { font-size: 0.88rem; color: var(--text-muted); line-height: 1.5; }
        .actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .btn {
            flex: 1;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .btn-secondary { background: #334155; color: var(--text-main); }
        .btn-secondary:hover { background: #475569; }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="header">
            <h1><?= APP_NAME ?> Database Setup</h1>
            <p>Database & Table Initialization Wizard</p>
        </div>

        <?php if ($status === 'success'): ?>
            <ul class="log-list">
                <?php foreach ($results as $res): ?>
                    <li class="log-item">
                        <span class="icon">✅</span>
                        <span><?= $res ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="admin-info">
                <h3>👑 Default Administrator Credentials</h3>
                <p><strong>Username:</strong> admin<br><strong>Password:</strong> admin123</p>
            </div>

            <div class="actions">
                <a href="../index.php" class="btn btn-primary">Go to Homepage →</a>
                <a href="../admin/login.php" class="btn btn-secondary">Admin Login</a>
            </div>
        <?php else: ?>
            <div class="alert-error">
                <strong>Setup Failed:</strong><br>
                <?= htmlspecialchars($error) ?>
            </div>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 12px;">
                Please ensure MySQL is running, or check your <code>config/config.php</code> database credentials.
            </p>
            <div class="actions">
                <a href="setup.php" class="btn btn-primary">Retry Setup ↺</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
