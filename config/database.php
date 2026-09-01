<?php
/**
 * Database Connection using PDO
 */

require_once __DIR__ . '/config.php';

function getDBConnection(): PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dbDriver = DB_DRIVER;

    if ($dbDriver === 'sqlite') {
        $sqlitePath = getenv('DB_SQLITE_PATH') ?: __DIR__ . '/../database/database.sqlite';
        return initSqliteConnection($sqlitePath);
    }

    // Try MySQL
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=%s",
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // If driver is 'auto' (default), fallback gracefully to SQLite so the app works immediately
        if ($dbDriver === 'auto') {
            $sqlitePath = getenv('DB_SQLITE_PATH') ?: __DIR__ . '/../database/database.sqlite';
            return initSqliteConnection($sqlitePath);
        }

        $errorMessage = $e->getMessage();
        if (php_sapi_name() !== 'cli') {
            http_response_code(500);
            echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Database Connection Required</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
                    .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 32px; max-width: 600px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); }
                    h1 { color: #f43f5e; margin-top: 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px; }
                    p { color: #94a3b8; line-height: 1.6; }
                    .code { background: #090d16; padding: 12px 16px; border-radius: 8px; font-family: monospace; color: #38bdf8; font-size: 0.9rem; overflow-x: auto; margin: 15px 0; border: 1px solid #1e293b; }
                    .btn { display: inline-block; background: #6366f1; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500; margin-top: 15px; }
                    .btn:hover { background: #4f46e5; }
                </style>
            </head>
            <body>
                <div class="card">
                    <h1>⚠️ Database Connection Error</h1>
                    <p>Unable to connect to MySQL database <strong>' . htmlspecialchars(DB_NAME) . '</strong> on <strong>' . htmlspecialchars(DB_HOST) . ':' . htmlspecialchars(DB_PORT) . '</strong>.</p>
                    <div class="code">' . htmlspecialchars($errorMessage) . '</div>
                    <p>If you haven\'t created the database yet, you can initialize it easily using our setup wizard:</p>
                    <a href="database/setup.php" class="btn">Launch Database Setup Wizard →</a>
                </div>
            </body>
            </html>';
            exit;
        }
        throw new PDOException("Database connection error: " . $errorMessage);
    }
}

function initSqliteConnection(string $sqlitePath): PDO {
    $isNew = !file_exists($sqlitePath);
    $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("PRAGMA foreign_keys = ON;");

    // Auto-create SQLite tables if new
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

    $count = (int) $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    if ($count === 0) {
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
    }

    $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM admins WHERE username = 'admin'")->fetchColumn();
    if ($adminCount === 0) {
        $adminStmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash) VALUES (:username, :email, :password_hash)");
        $adminStmt->execute([
            'username' => 'admin',
            'email' => 'admin@eventsphere.com',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT)
        ]);
    }

    return $pdo;
}
