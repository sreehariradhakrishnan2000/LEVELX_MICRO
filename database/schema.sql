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

-- Seed Sample Events
INSERT INTO `events` (`title`, `description`, `event_date`, `location`, `category`, `capacity`, `image_url`) VALUES
('Global AI & Cloud Summit 2026', 'Explore next-generation artificial intelligence breakthroughs, edge computing architectures, and cloud modernization strategies with industry leaders.', '2026-10-15 09:30:00', 'Convention Center, Hall A (Bengaluru / Virtual)', 'Technology', 250, 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&auto=format&fit=crop&q=80'),
('Full-Stack Web Dev Workshop', 'Deep-dive interactive session on modern PHP, REST APIs, reactive frontends, and database scalability optimization.', '2026-10-22 14:00:00', 'Tech Hub Arena, Room 402', 'Workshop', 80, 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&auto=format&fit=crop&q=80'),
('Cybersecurity Defense & Zero Trust', 'Learn practical penetration testing defense, vulnerability scanning, and how to implement Zero-Trust architecture in enterprise environments.', '2026-11-05 10:00:00', 'Cyber Park Auditorium', 'Security', 150, 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800&auto=format&fit=crop&q=80'),
('Startup Pitch & Networking Night', 'Connect with venture capitalists, pitch disruptive startup ideas, and network with passionate founders and innovators.', '2026-11-18 18:00:00', 'Skyline Lounge & Rooftop Garden', 'Networking', 120, 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&auto=format&fit=crop&q=80')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Seed Default Admin User: admin / admin123
-- Hash generated for 'admin123' via password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO `admins` (`username`, `email`, `password_hash`) VALUES
('admin', 'admin@eventsphere.com', '$2y$10$w6sQ3vB7j3W6P1q9qF0W3.h9v9H6H.fK0t7/QYnUqK2I4M8n5XnVy')
ON DUPLICATE KEY UPDATE `id`=`id`;
