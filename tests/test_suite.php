<?php
/**
 * Automated Unit and Integration Test Suite
 * Run via CLI: php tests/test_suite.php
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Setup environment for testing (use SQLite memory or file if MySQL is not reachable, or test MySQL directly)
putenv('DB_TYPE=sqlite');
putenv('DB_SQLITE_PATH=' . __DIR__ . '/test_database.sqlite');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Color formatting for CLI
$passed = 0;
$failed = 0;

function assertTest(string $testName, bool $condition, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  \033[32m✔ PASS:\033[0m {$testName}\n";
    } else {
        $failed++;
        echo "  \033[31m✘ FAIL:\033[0m {$testName} - {$details}\n";
    }
}

echo "\n======================================================\n";
echo "  EventSphere - PHP & Database Automated Test Suite   \n";
echo "======================================================\n\n";

// ----------------------------------------------------
// Section 1: Security & Helper Functions Tests
// ----------------------------------------------------
echo "▶ 1. Testing Security & Utility Helpers...\n";

// Sanitization
$rawInput = "   <script>alert('xss')</script>John Doe   ";
$sanitized = sanitize($rawInput);
assertTest("Sanitize strips script tags & trims whitespace", $sanitized === "alert('xss')John Doe", "Result: '{$sanitized}'");

// HTML Escaping
$htmlInput = '<img src="x" onerror="alert(1)">';
$escaped = e($htmlInput);
assertTest("HTML Escaping encodes special chars", $escaped === '&lt;img src=&quot;x&quot; onerror=&quot;alert(1)&quot;&gt;', "Result: '{$escaped}'");

// Email Validation
assertTest("Valid email passes validation (john@example.com)", validateEmail('john@example.com') === true);
assertTest("Invalid email fails validation (john.doe@)", validateEmail('john.doe@') === false);
assertTest("Invalid email fails validation (plainaddress)", validateEmail('plainaddress') === false);

// CSRF Token
$token = generateCSRFToken();
assertTest("CSRF Token is 64 hex characters", strlen($token) === 64);
assertTest("CSRF Token verification succeeds on matching token", verifyCSRFToken($token) === true);
assertTest("CSRF Token verification fails on wrong token", verifyCSRFToken('invalid_token_123') === false);

// Registration Code Generator
$regCode = generateRegistrationCode();
assertTest("Registration Code matches format REG-YYYY-XXXXXX", preg_match('/^REG-\d{4}-[A-Z0-9]{6}$/', $regCode) === 1, "Code: {$regCode}");


// ----------------------------------------------------
// Section 2: Database Initialization & Migrations
// ----------------------------------------------------
echo "\n▶ 2. Testing Database Setup & Migrations...\n";

// Execute setup script
require __DIR__ . '/../database/setup.php';

$pdo = getDBConnection();
assertTest("Database connection instance is active PDO", $pdo instanceof PDO);

$eventCount = (int) $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
assertTest("Events table exists and has seed records (>0)", $eventCount > 0, "Count: {$eventCount}");

$adminCount = (int) $pdo->query("SELECT COUNT(*) FROM admins WHERE username = 'admin'")->fetchColumn();
assertTest("Admin table exists and contains default admin user", $adminCount === 1, "Count: {$adminCount}");


// ----------------------------------------------------
// Section 3: Event Repository Tests
// ----------------------------------------------------
echo "\n▶ 3. Testing Event Model & Data Retrieval...\n";

$allEvents = getAllEvents();
assertTest("getAllEvents() returns array of events", is_array($allEvents) && count($allEvents) > 0);

$firstEvent = $allEvents[0];
$fetchedEvent = getEventById((int)$firstEvent['id']);
assertTest("getEventById() fetches exact event record", $fetchedEvent !== null && $fetchedEvent['title'] === $firstEvent['title']);

// Create a custom event
$newEventId = createEvent([
    'title' => 'Automated Test Hackathon 2026',
    'description' => 'Test event description for automated testing verification.',
    'event_date' => '2026-12-01 10:00:00',
    'location' => 'Virtual Lab',
    'category' => 'Testing',
    'capacity' => 50,
    'image_url' => 'https://example.com/test.jpg'
]);
assertTest("createEvent() inserts new event and returns ID > 0", $newEventId > 0, "New ID: {$newEventId}");

$retrievedNewEvent = getEventById($newEventId);
assertTest("Retrieve newly created event by ID", $retrievedNewEvent['title'] === 'Automated Test Hackathon 2026');


// ----------------------------------------------------
// Section 4: Registration Workflow & Validation Tests
// ----------------------------------------------------
echo "\n▶ 4. Testing Registration Validation & Flow...\n";

// Test Validation: Empty Name
$resEmptyName = createRegistration([
    'name' => '',
    'email' => 'alice@example.com',
    'event_id' => $newEventId
]);
assertTest("Empty name returns validation error", $resEmptyName['success'] === false && isset($resEmptyName['errors']['name']));

// Test Validation: Invalid Email
$resInvalidEmail = createRegistration([
    'name' => 'Alice Johnson',
    'email' => 'invalid-email-format',
    'event_id' => $newEventId
]);
assertTest("Invalid email returns validation error", $resInvalidEmail['success'] === false && isset($resInvalidEmail['errors']['email']));

// Test Validation: Invalid Event ID
$resInvalidEvent = createRegistration([
    'name' => 'Alice Johnson',
    'email' => 'alice@example.com',
    'event_id' => 999999
]);
assertTest("Non-existent event ID returns validation error", $resInvalidEvent['success'] === false && isset($resInvalidEvent['errors']['event_id']));

// Test Valid Registration
$validReg = createRegistration([
    'name' => 'Alice Johnson',
    'email' => 'alice.johnson@example.com',
    'event_id' => $newEventId
]);
assertTest("Valid registration creates record successfully", $validReg['success'] === true && !empty($validReg['registration_code']));

$regId = $validReg['id'];
$regCode = $validReg['registration_code'];

// Fetch by ID
$fetchedRegById = getRegistrationById($regId);
assertTest("getRegistrationById() returns participant & joined event title", 
    $fetchedRegById !== null && 
    $fetchedRegById['name'] === 'Alice Johnson' && 
    $fetchedRegById['event_title'] === 'Automated Test Hackathon 2026'
);

// Fetch by Code
$fetchedRegByCode = getRegistrationByCode($regCode);
assertTest("getRegistrationByCode() returns registration matching reference code", 
    $fetchedRegByCode !== null && 
    $fetchedRegByCode['id'] == $regId
);

// Duplicate Registration handling
$duplicateReg = createRegistration([
    'name' => 'Alice Johnson',
    'email' => 'alice.johnson@example.com',
    'event_id' => $newEventId
]);
assertTest("Submitting duplicate registration returns existing registration gracefully", 
    $duplicateReg['success'] === true && 
    !empty($duplicateReg['is_existing']) && 
    $duplicateReg['id'] == $regId
);


// ----------------------------------------------------
// Section 5: Admin Authentication & Dashboard Tests
// ----------------------------------------------------
echo "\n▶ 5. Testing Admin Authentication & Access Control...\n";

// Test invalid credentials
$badLogin = loginAdmin('admin', 'wrongpassword');
assertTest("Invalid admin credentials fail login", $badLogin === false && isAdminLoggedIn() === false);

// Test valid credentials
$goodLogin = loginAdmin('admin', 'admin123');
assertTest("Valid admin login (admin/admin123) succeeds", $goodLogin === true && isAdminLoggedIn() === true);

$currentAdmin = getCurrentAdmin();
assertTest("getCurrentAdmin() returns logged-in admin data", $currentAdmin !== null && $currentAdmin['username'] === 'admin');

// Test Dashboard stats
$stats = getDashboardStats();
assertTest("getDashboardStats() returns total counts", $stats['total_registrations'] >= 1 && $stats['total_events'] >= 1);

// Test Listing & Search
$allRegistrations = getAllRegistrations();
assertTest("getAllRegistrations() returns all registered attendees", count($allRegistrations) >= 1);

$searchResult = getAllRegistrations('Alice');
assertTest("getAllRegistrations('Alice') filters by attendee name", count($searchResult) >= 1 && $searchResult[0]['name'] === 'Alice Johnson');

$eventFilterResult = getAllRegistrations(null, $newEventId);
assertTest("getAllRegistrations(null, eventId) filters by event", count($eventFilterResult) >= 1 && (int)$eventFilterResult[0]['event_id'] === $newEventId);

// Test Logout
logoutAdmin();
assertTest("logoutAdmin() clears admin session", isAdminLoggedIn() === false && getCurrentAdmin() === null);


// ----------------------------------------------------
// Section 6: Deletion & Cleanup Tests
// ----------------------------------------------------
echo "\n▶ 6. Testing Deletion & Cleanup...\n";

$deleteResult = deleteRegistration($regId);
assertTest("deleteRegistration() deletes record by ID", $deleteResult === true);

$deletedCheck = getRegistrationById($regId);
assertTest("Verified registration record no longer exists after deletion", $deletedCheck === null);

// Cleanup test database file
if (file_exists(__DIR__ . '/test_database.sqlite')) {
    @unlink(__DIR__ . '/test_database.sqlite');
}

// ----------------------------------------------------
// Summary
// ----------------------------------------------------
echo "\n======================================================\n";
echo "  Test Results Summary: \033[32m{$passed} Passed\033[0m, \033[" . ($failed > 0 ? "31" : "32") . "m{$failed} Failed\033[0m\n";
echo "======================================================\n\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
