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

require_once __DIR__ . '/../config/database.php';

try {
    $dbType = getenv('DB_TYPE') ?: 'mysql';

    if ($dbType === 'sqlite' || (DB_DRIVER === 'auto' && $dbType !== 'mysql_forced')) {
        $pdo = getDBConnection();
        $results[] = "Created/verified database tables.";
    } else {
        // MySQL setup
        $serverDsn = sprintf("mysql:host=%s;port=%s;charset=%s", DB_HOST, DB_PORT, DB_CHARSET);
        $serverPdo = new PDO($serverDsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $results[] = "Database `" . DB_NAME . "` verified / created.";

        $pdo = getDBConnection();

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

    // Seed/Synchronize all 16 events
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE title = :title");
    $insertStmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location, category, capacity, image_url) VALUES (:title, :description, :event_date, :location, :category, :capacity, :image_url)");
    $insertedCount = 0;

    foreach ($sampleEvents as $ev) {
        $stmt->execute(['title' => $ev['title']]);
        $exists = (int)$stmt->fetchColumn();
        $stmt->closeCursor();
        if ($exists === 0) {
            $insertStmt->execute($ev);
            $insertStmt->closeCursor();
            $insertedCount++;
        }
    }
    $stmt = null;
    $insertStmt = null;

    $totalEvtStmt = $pdo->query("SELECT COUNT(*) FROM events");
    $totalEvents = (int) $totalEvtStmt->fetchColumn();
    $totalEvtStmt->closeCursor();
    $totalEvtStmt = null;

    $results[] = "Events catalog populated. Total active events: <strong>{$totalEvents}</strong> (" . ($insertedCount > 0 ? "Added {$insertedCount} new" : "Up to date") . ").";

    // Seed default admin user
    $adminCheckStmt = $pdo->query("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    $adminCount = (int) $adminCheckStmt->fetchColumn();
    $adminCheckStmt->closeCursor();
    $adminCheckStmt = null;

    if ($adminCount == 0) {
        $adminPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $adminStmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash) VALUES (:username, :email, :password_hash)");
        $adminStmt->execute([
            'username' => 'admin',
            'email' => 'admin@eventsphere.com',
            'password_hash' => $adminPasswordHash
        ]);
        $adminStmt->closeCursor();
        $adminStmt = null;
        $results[] = "Created default admin account: Username: <strong>admin</strong> | Password: <strong>admin123</strong>";
    } else {
        $results[] = "Admin account `admin` verified.";
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 24px;">
    <div class="form-card" style="max-width: 580px; width: 100%;">
        <div class="form-header">
            <div style="font-size: 2.4rem; margin-bottom: 8px;">⚡</div>
            <h1><?= APP_NAME ?> Database Setup</h1>
            <p>Schema initialization & 16+ events catalog sync</p>
        </div>

        <?php if ($status === 'success'): ?>
            <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($results as $res): ?>
                        <li style="display: flex; align-items: center; gap: 10px; font-size: 0.92rem; color: #e2e8f0;">
                            <span>✅</span>
                            <span><?= $res ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="credentials-box" style="margin-bottom: 24px; text-align: center; padding: 14px;">
                <h4 style="color: #93c5fd; font-size: 0.95rem; margin-bottom: 4px;">👑 Default Administrator</h4>
                <p style="font-size: 0.88rem; color: #cbd5e1;">Username: <strong>admin</strong> &nbsp;|&nbsp; Password: <strong>admin123</strong></p>
            </div>

            <div style="display: flex; gap: 12px;">
                <a href="../index.php" class="btn-ticket btn-ticket-primary" style="flex: 1; text-align: center; justify-content: center;">Browse 16+ Events →</a>
                <a href="../admin/login.php" class="btn-ticket btn-ticket-outline" style="text-align: center; justify-content: center;">Admin Portal</a>
            </div>
        <?php else: ?>
            <div class="alert alert-error">
                <div class="alert-content">
                    <span class="alert-icon">⚠️</span>
                    <span><strong>Setup Error:</strong> <?= htmlspecialchars($error) ?></span>
                </div>
            </div>
            <a href="setup.php" class="btn-ticket btn-ticket-primary" style="width: 100%; text-align: center; justify-content: center; margin-top: 16px;">Retry Setup ↺</a>
        <?php endif; ?>
    </div>
</body>
</html>
