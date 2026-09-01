<?php
/**
 * Global Utility and Repository Functions
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// ==========================================
// Sanitization & Security Helpers
// ==========================================

function sanitize(string $input): string {
    return trim(strip_tags($input));
}

function e(?string $input): string {
    return htmlspecialchars($input ?? '', ENT_QUOTES, 'UTF-8');
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isNotEmpty(string $value): bool {
    return trim($value) !== '';
}

// CSRF Token Management
function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function renderCSRFInput(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(generateCSRFToken()) . '">';
}

// Flash Messages
function setFlashMessage(string $type, string $message): void {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
}

function getFlashMessages(): array {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

// Date Formatting Helper
function formatDate(string $dateString, string $format = 'M j, Y - g:i A'): string {
    try {
        $date = new DateTime($dateString);
        return $date->format($format);
    } catch (Exception $e) {
        return $dateString;
    }
}

// Generate unique registration reference code (e.g. EVT-2026-X8F4K)
function generateRegistrationCode(): string {
    $year = date('Y');
    $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    return "REG-{$year}-{$random}";
}

// ==========================================
// Database Repository Functions
// ==========================================

/**
 * Get all upcoming events
 */
function getAllEvents(): array {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
    return $stmt->fetchAll();
}

/**
 * Get a specific event by ID
 */
function getEventById(int $id): ?array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $event = $stmt->fetch();
    return $event ?: null;
}

/**
 * Create a new event
 */
function createEvent(array $data): int {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        INSERT INTO events (title, description, event_date, location, category, capacity, image_url)
        VALUES (:title, :description, :event_date, :location, :category, :capacity, :image_url)
    ");
    $stmt->execute([
        'title' => $data['title'],
        'description' => $data['description'],
        'event_date' => $data['event_date'],
        'location' => $data['location'],
        'category' => $data['category'] ?? 'Technology',
        'capacity' => $data['capacity'] ?? 100,
        'image_url' => $data['image_url'] ?? null
    ]);
    return (int) $pdo->lastInsertId();
}

/**
 * Register a user for an event with validation
 */
function createRegistration(array $data): array {
    $errors = [];
    $name = sanitize($data['name'] ?? '');
    $email = sanitize($data['email'] ?? '');
    $eventId = (int) ($data['event_id'] ?? 0);

    // Validation Rules
    if (!isNotEmpty($name)) {
        $errors['name'] = 'Full Name is required.';
    } elseif (mb_strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters.';
    }

    if (!isNotEmpty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!validateEmail($email)) {
        $errors['email'] = 'Please provide a valid email address.';
    }

    if ($eventId <= 0) {
        $errors['event_id'] = 'Please select a valid event.';
    } else {
        $event = getEventById($eventId);
        if (!$event) {
            $errors['event_id'] = 'The selected event does not exist.';
        }
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $pdo = getDBConnection();

    // Check if user already registered for this specific event with this email
    $checkStmt = $pdo->prepare("SELECT id, registration_code FROM registrations WHERE event_id = :event_id AND email = :email LIMIT 1");
    $checkStmt->execute(['event_id' => $eventId, 'email' => $email]);
    $existing = $checkStmt->fetch();
    if ($existing) {
        // Return existing registration
        return [
            'success' => true,
            'id' => (int) $existing['id'],
            'registration_code' => $existing['registration_code'],
            'is_existing' => true
        ];
    }

    $code = generateRegistrationCode();
    $stmt = $pdo->prepare("
        INSERT INTO registrations (event_id, name, email, registration_code)
        VALUES (:event_id, :name, :email, :registration_code)
    ");
    $stmt->execute([
        'event_id' => $eventId,
        'name' => $name,
        'email' => $email,
        'registration_code' => $code
    ]);

    $id = (int) $pdo->lastInsertId();

    return [
        'success' => true,
        'id' => $id,
        'registration_code' => $code,
        'is_existing' => false
    ];
}

/**
 * Get registration details by ID (joined with event details)
 */
function getRegistrationById(int $id): ?array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT 
            r.id,
            r.event_id,
            r.name,
            r.email,
            r.registration_code,
            r.created_at AS date_registered,
            e.title AS event_title,
            e.description AS event_description,
            e.event_date,
            e.location AS event_location,
            e.category AS event_category,
            e.image_url AS event_image
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        WHERE r.id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Get registration details by reference code
 */
function getRegistrationByCode(string $code): ?array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT 
            r.id,
            r.event_id,
            r.name,
            r.email,
            r.registration_code,
            r.created_at AS date_registered,
            e.title AS event_title,
            e.description AS event_description,
            e.event_date,
            e.location AS event_location,
            e.category AS event_category,
            e.image_url AS event_image
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        WHERE r.registration_code = :code
        LIMIT 1
    ");
    $stmt->execute(['code' => $code]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Get all registrations with event titles, optional search & event filter
 */
function getAllRegistrations(?string $search = null, ?int $eventId = null): array {
    $pdo = getDBConnection();
    $query = "
        SELECT 
            r.id,
            r.event_id,
            r.name,
            r.email,
            r.registration_code,
            r.created_at AS date_registered,
            e.title AS event_title,
            e.event_date,
            e.location AS event_location
        FROM registrations r
        JOIN events e ON r.event_id = e.id
    ";

    $params = [];
    $where = [];

    if ($search !== null && trim($search) !== '') {
        $where[] = "(r.name LIKE :search OR r.email LIKE :search OR r.registration_code LIKE :search OR e.title LIKE :search)";
        $params['search'] = '%' . trim($search) . '%';
    }

    if ($eventId !== null && $eventId > 0) {
        $where[] = "r.event_id = :event_id";
        $params['event_id'] = $eventId;
    }

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " ORDER BY r.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Delete a registration by ID
 */
function deleteRegistration(int $id): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = :id");
    return $stmt->execute(['id' => $id]);
}

/**
 * Get summary stats for Admin dashboard
 */
function getDashboardStats(): array {
    $pdo = getDBConnection();
    $totalRegistrations = (int) $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
    $totalEvents = (int) $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    
    // Recent 5 registrations
    $recentStmt = $pdo->query("
        SELECT r.id, r.name, r.email, r.created_at, e.title AS event_title
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $recentRegistrations = $recentStmt->fetchAll();

    return [
        'total_registrations' => $totalRegistrations,
        'total_events' => $totalEvents,
        'recent_registrations' => $recentRegistrations
    ];
}
