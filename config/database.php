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
                <link rel="stylesheet" href="assets/css/style.css">
            </head>
            <body style="display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px;">
                <div class="form-card" style="max-width:540px;width:100%;">
                    <div class="form-header">
                        <div style="font-size:2.5rem;margin-bottom:10px;">⚠️</div>
                        <h1>Database Connection Required</h1>
                        <p>Unable to connect to MySQL database <strong>' . htmlspecialchars(DB_NAME) . '</strong>.</p>
                    </div>
                    <div class="credentials-box" style="margin:20px 0;word-break:break-all;color:#fca5a5;">' . htmlspecialchars($errorMessage) . '</div>
                    <a href="database/setup.php" class="btn-ticket btn-ticket-primary" style="width:100%;justify-content:center;">Launch Database Setup Wizard →</a>
                </div>
            </body>
            </html>';
            exit;
        }
        throw new PDOException("Database connection error: " . $errorMessage);
    }
}

function initSqliteConnection(string $sqlitePath): PDO {
    $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 15,
    ]);
    $pdo->exec("PRAGMA foreign_keys = ON;");
    $pdo->exec("PRAGMA busy_timeout = 15000;");

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

    $sampleEvents = [
        [
            'title' => 'Global AI & NextGen Summit 2026',
            'description' => 'Join top AI researchers, LLM practitioners, and tech visionaries exploring frontier model architectures, agentic workflows, and ethical AI deployments at scale.',
            'event_date' => '2026-10-15 09:30:00',
            'location' => 'Convention Center, Grand Hall A (Bengaluru / Hybrid)',
            'category' => 'AI & Machine Learning',
            'capacity' => 350,
            'image_url' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'Full-Stack Modern Web Engineering',
            'description' => 'Master the contemporary full-stack ecosystem: PHP 8.4 modern features, high-throughput microservices, edge computing, and reactive frontend architectures.',
            'event_date' => '2026-10-22 10:00:00',
            'location' => 'Tech Hub Arena, Room 402 (San Francisco / Online)',
            'category' => 'Web Development',
            'capacity' => 120,
            'image_url' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'Cybersecurity & Zero-Trust Defense Expo',
            'description' => 'Discover enterprise defensive strategies, live ethical hacking demonstrations, cloud workload isolation, and implementing uncompromising Zero-Trust architectures.',
            'event_date' => '2026-11-05 09:00:00',
            'location' => 'Cyber Park Auditorium (London / Virtual)',
            'category' => 'Cybersecurity',
            'capacity' => 200,
            'image_url' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'Cloud-Native & Kubernetes World 2026',
            'description' => 'Deep dive into container orchestration, service mesh resilience, multi-cloud disaster recovery, and automated continuous delivery pipelines.',
            'event_date' => '2026-11-12 11:00:00',
            'location' => 'Cloudplex Center, Stage 3 (Seattle / Live Stream)',
            'category' => 'Cloud & DevOps',
            'capacity' => 280,
            'image_url' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'UI/UX Design Systems & Product Craft',
            'description' => 'Uncover the secrets of world-class design systems, accessible interface typography, spatial interaction design, and seamless designer-developer collaboration.',
            'event_date' => '2026-11-19 14:00:00',
            'location' => 'Design Foundry Studio (Tokyo / Hybrid)',
            'category' => 'Design & UX',
            'capacity' => 95,
            'image_url' => 'https://images.unsplash.com/photo-1581291518633-83b4ebd1d83e?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'FinTech Disruptors & Open Banking Summit',
            'description' => 'Explore the future of algorithmic trading, instant global payment rails, automated compliance engines, and decentralized banking infrastructure.',
            'event_date' => '2026-11-26 09:30:00',
            'location' => 'Financial District Center, Tower B (Singapore)',
            'category' => 'FinTech',
            'capacity' => 180,
            'image_url' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'Healthcare Tech & Biotech Innovation 2026',
            'description' => 'Revolutionizing healthcare through predictive genomic models, telemedicine scalability, and high-precision clinical workflow software.',
            'event_date' => '2026-12-03 10:00:00',
            'location' => 'Life Sciences Pavilion (Boston / Hybrid)',
            'category' => 'Healthcare & Biotech',
            'capacity' => 150,
            'image_url' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'Startup Pitch Arena & VC Networking Night',
            'description' => 'Connect directly with top tier angel investors and venture funds. Watch 10 curated seed-stage startups battle for $2M in direct funding commitments.',
            'event_date' => '2026-12-10 18:00:00',
            'location' => 'Skyline Lounge & Rooftop Garden (New York City)',
            'category' => 'Startups & VC',
            'capacity' => 160,
            'image_url' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'Quantum Computing Frontiers 2026',
            'description' => 'Leading physicists and quantum software engineers demystify qubit error correction, quantum algorithms, and post-quantum cryptographic standards.',
            'event_date' => '2026-12-16 10:00:00',
            'location' => 'Institute of Advanced Physics, Main Hall (Zurich)',
            'category' => 'Deep Tech',
            'capacity' => 110,
            'image_url' => 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'Game Dev, XR & Spatial Computing Expo',
            'description' => 'Hands-on spatial audio design, Unreal Engine 5 real-time rendering, cross-platform VR headsets, and indie game mechanics workshops.',
            'event_date' => '2027-01-08 11:30:00',
            'location' => 'Interactive Media Center (Los Angeles / Metaverse)',
            'category' => 'Gaming & XR',
            'capacity' => 300,
            'image_url' => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'Mobile Masters: Flutter, Swift & React Native',
            'description' => 'Master performance profiling for 120Hz mobile displays, offline-first sync engines, native bridging, and cross-platform architecture paradigms.',
            'event_date' => '2027-01-15 13:00:00',
            'location' => 'Developer Arena, Hall 2 (Berlin / Online)',
            'category' => 'Mobile Development',
            'capacity' => 140,
            'image_url' => 'https://images.unsplash.com/photo-1526498460520-4c246339dccb?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'Data Science, Big Data & Analytics Con',
            'description' => 'Real-time stream processing, lakehouse architectures, feature store management, and automated statistical testing pipelines for massive datasets.',
            'event_date' => '2027-01-22 09:00:00',
            'location' => 'Analytics Dome (Toronto / Hybrid)',
            'category' => 'Data Science',
            'capacity' => 220,
            'image_url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'Product Leadership & Growth Strategy',
            'description' => 'Actionable playbooks on driving product-led growth, running rigorous user experimentation, and setting high-impact product roadmaps that scale.',
            'event_date' => '2027-01-29 14:30:00',
            'location' => 'Executive Forum Hall (Stockholm / Virtual)',
            'category' => 'Product & Growth',
            'capacity' => 100,
            'image_url' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'CleanTech & Sustainable Energy Forum',
            'description' => 'Innovations in smart grid intelligence, carbon accounting APIs, next-generation battery management, and circular electronics manufacturing.',
            'event_date' => '2027-02-05 10:00:00',
            'location' => 'Green Tech Center (Amsterdam / Live Stream)',
            'category' => 'Sustainability',
            'capacity' => 175,
            'image_url' => 'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'DevOps & Platform Engineering Gathering',
            'description' => 'Internal developer platforms (IDPs), GitOps best practices, infrastructure-as-code linting, and reducing cognitive developer friction.',
            'event_date' => '2027-02-12 11:00:00',
            'location' => 'Platform Engineering Hub (Austin, Texas / Hybrid)',
            'category' => 'Cloud & DevOps',
            'capacity' => 190,
            'image_url' => 'https://images.unsplash.com/photo-1618401471353-b98afee0b2eb?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'title' => 'Blockchain, Web3 & Digital Assets Forum',
            'description' => 'Smart contract formal verification, zero-knowledge rollups, institutional asset custody, and decentralized identity standards.',
            'event_date' => '2027-02-19 13:00:00',
            'location' => 'Crypto Oasis Center (Dubai / Virtual)',
            'category' => 'Web3 & Blockchain',
            'capacity' => 250,
            'image_url' => 'https://images.unsplash.com/photo-1639762681485-074b7f938ba0?w=800&auto=format&fit=crop&q=80'
        ]
    ];

    // Seed events if missing
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE title = :title");
    $insertStmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location, category, capacity, image_url) VALUES (:title, :description, :event_date, :location, :category, :capacity, :image_url)");
    foreach ($sampleEvents as $ev) {
        $checkStmt->execute(['title' => $ev['title']]);
        if ((int)$checkStmt->fetchColumn() === 0) {
            $insertStmt->execute($ev);
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
