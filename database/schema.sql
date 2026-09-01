-- Event Registration & Management System Schema
-- Database: event_management

CREATE DATABASE IF NOT EXISTS `event_management` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `event_management`;

-- 1. Events Table
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

-- 2. Registrations Table
CREATE TABLE IF NOT EXISTS `registrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `registration_code` VARCHAR(64) UNIQUE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_registrations_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) UNIQUE NOT NULL,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed 16 Sample Upcoming Events
INSERT INTO `events` (`title`, `description`, `event_date`, `location`, `category`, `capacity`, `image_url`) VALUES
('Global AI & NextGen Summit 2026', 'Join top AI researchers, LLM practitioners, and tech visionaries exploring frontier model architectures, agentic workflows, and ethical AI deployments at scale.', '2026-10-15 09:30:00', 'Convention Center, Grand Hall A (Bengaluru / Hybrid)', 'AI & Machine Learning', 350, 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&auto=format&fit=crop&q=80'),
('Full-Stack Modern Web Engineering', 'Master the contemporary full-stack ecosystem: PHP 8.4 modern features, high-throughput microservices, edge computing, and reactive frontend architectures.', '2026-10-22 10:00:00', 'Tech Hub Arena, Room 402 (San Francisco / Online)', 'Web Development', 120, 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&auto=format&fit=crop&q=80'),
('Cybersecurity & Zero-Trust Defense Expo', 'Discover enterprise defensive strategies, live ethical hacking demonstrations, cloud workload isolation, and implementing uncompromising Zero-Trust architectures.', '2026-11-05 09:00:00', 'Cyber Park Auditorium (London / Virtual)', 'Cybersecurity', 200, 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800&auto=format&fit=crop&q=80'),
('Cloud-Native & Kubernetes World 2026', 'Deep dive into container orchestration, service mesh resilience, multi-cloud disaster recovery, and automated continuous delivery pipelines.', '2026-11-12 11:00:00', 'Cloudplex Center, Stage 3 (Seattle / Live Stream)', 'Cloud & DevOps', 280, 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&auto=format&fit=crop&q=80'),
('UI/UX Design Systems & Product Craft', 'Uncover the secrets of world-class design systems, accessible interface typography, spatial interaction design, and seamless designer-developer collaboration.', '2026-11-19 14:00:00', 'Design Foundry Studio (Tokyo / Hybrid)', 'Design & UX', 95, 'https://images.unsplash.com/photo-1581291518633-83b4ebd1d83e?w=800&auto=format&fit=crop&q=80'),
('FinTech Disruptors & Open Banking Summit', 'Explore the future of algorithmic trading, instant global payment rails, automated compliance engines, and decentralized banking infrastructure.', '2026-11-26 09:30:00', 'Financial District Center, Tower B (Singapore)', 'FinTech', 180, 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=800&auto=format&fit=crop&q=80'),
('Healthcare Tech & Biotech Innovation 2026', 'Revolutionizing healthcare through predictive genomic models, telemedicine scalability, and high-precision clinical workflow software.', '2026-12-03 10:00:00', 'Life Sciences Pavilion (Boston / Hybrid)', 'Healthcare & Biotech', 150, 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=800&auto=format&fit=crop&q=80'),
('Startup Pitch Arena & VC Networking Night', 'Connect directly with top tier angel investors and venture funds. Watch 10 curated seed-stage startups battle for $2M in direct funding commitments.', '2026-12-10 18:00:00', 'Skyline Lounge & Rooftop Garden (New York City)', 'Startups & VC', 160, 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&auto=format&fit=crop&q=80'),
('Quantum Computing Frontiers 2026', 'Leading physicists and quantum software engineers demystify qubit error correction, quantum algorithms, and post-quantum cryptographic standards.', '2026-12-16 10:00:00', 'Institute of Advanced Physics, Main Hall (Zurich)', 'Deep Tech', 110, 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=800&auto=format&fit=crop&q=80'),
('Game Dev, XR & Spatial Computing Expo', 'Hands-on spatial audio design, Unreal Engine 5 real-time rendering, cross-platform VR headsets, and indie game mechanics workshops.', '2027-01-08 11:30:00', 'Interactive Media Center (Los Angeles / Metaverse)', 'Gaming & XR', 300, 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=800&auto=format&fit=crop&q=80'),
('Mobile Masters: Flutter, Swift & React Native', 'Master performance profiling for 120Hz mobile displays, offline-first sync engines, native bridging, and cross-platform architecture paradigms.', '2027-01-15 13:00:00', 'Developer Arena, Hall 2 (Berlin / Online)', 'Mobile Development', 140, 'https://images.unsplash.com/photo-1526498460520-4c246339dccb?w=800&auto=format&fit=crop&q=80'),
('Data Science, Big Data & Analytics Con', 'Real-time stream processing, lakehouse architectures, feature store management, and automated statistical testing pipelines for massive datasets.', '2027-01-22 09:00:00', 'Analytics Dome (Toronto / Hybrid)', 'Data Science', 220, 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&auto=format&fit=crop&q=80'),
('Product Leadership & Growth Strategy', 'Actionable playbooks on driving product-led growth, running rigorous user experimentation, and setting high-impact product roadmaps that scale.', '2027-01-29 14:30:00', 'Executive Forum Hall (Stockholm / Virtual)', 'Product & Growth', 100, 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&auto=format&fit=crop&q=80'),
('CleanTech & Sustainable Energy Forum', 'Innovations in smart grid intelligence, carbon accounting APIs, next-generation battery management, and circular electronics manufacturing.', '2027-02-05 10:00:00', 'Green Tech Center (Amsterdam / Live Stream)', 'Sustainability', 175, 'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?w=800&auto=format&fit=crop&q=80'),
('DevOps & Platform Engineering Gathering', 'Internal developer platforms (IDPs), GitOps best practices, infrastructure-as-code linting, and reducing cognitive developer friction.', '2027-02-12 11:00:00', 'Platform Engineering Hub (Austin, Texas / Hybrid)', 'Cloud & DevOps', 190, 'https://images.unsplash.com/photo-1618401471353-b98afee0b2eb?w=800&auto=format&fit=crop&q=80'),
('Blockchain, Web3 & Digital Assets Forum', 'Smart contract formal verification, zero-knowledge rollups, institutional asset custody, and decentralized identity standards.', '2027-02-19 13:00:00', 'Crypto Oasis Center (Dubai / Virtual)', 'Web3 & Blockchain', 250, 'https://images.unsplash.com/photo-1639762681485-074b7f938ba0?w=800&auto=format&fit=crop&q=80')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Seed Default Admin User: admin / admin123
INSERT INTO `admins` (`username`, `email`, `password_hash`) VALUES
('admin', 'admin@eventsphere.com', '$2y$10$w6sQ3vB7j3W6P1q9qF0W3.h9v9H6H.fK0t7/QYnUqK2I4M8n5XnVy')
ON DUPLICATE KEY UPDATE `id`=`id`;
