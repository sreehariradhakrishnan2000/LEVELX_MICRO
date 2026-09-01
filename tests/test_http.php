<?php
/**
 * End-to-End HTTP Web Server Integration Tests
 */

$baseUrl = 'http://127.0.0.1:8000';
$cookieFile = __DIR__ . '/test_cookie.txt';
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

$testsPassed = 0;
$testsFailed = 0;

function runHttpTest(string $name, bool $result, string $details = '') {
    global $testsPassed, $testsFailed;
    if ($result) {
        $testsPassed++;
        echo "  \033[32m✔ PASS:\033[0m {$name}\n";
    } else {
        $testsFailed++;
        echo "  \033[31m✘ FAIL:\033[0m {$name} - {$details}\n";
    }
}

function httpGet(string $url, ?string $cookieJar = null): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($cookieJar) {
        curl_setopt($cookieJar ? $ch : 0, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($cookieJar ? $ch : 0, CURLOPT_COOKIEFILE, $cookieJar);
    }
    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ['status' => $info['http_code'], 'body' => $body, 'headers' => $info];
}

function httpPost(string $url, array $data, ?string $cookieJar = null): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);
    return ['status' => $status, 'headers' => $headers, 'body' => $body];
}

echo "\n======================================================\n";
echo "  EventSphere - End-to-End HTTP Web Server Tests      \n";
echo "======================================================\n\n";

// 1. Test Homepage
echo "▶ 1. Testing Homepage (index.php)...\n";
$homeRes = httpGet($baseUrl . '/index.php', $cookieFile);
runHttpTest("Homepage returns HTTP 200", $homeRes['status'] === 200);
runHttpTest("Homepage contains brand logo and event listings", 
    str_contains($homeRes['body'], 'EventSphere') && 
    str_contains($homeRes['body'], 'Upcoming Scheduled Summits') &&
    str_contains($homeRes['body'], 'Register')
);

// Extract CSRF token from registration page
echo "\n▶ 2. Testing Registration Form (register.php)...\n";
$regPageRes = httpGet($baseUrl . '/register.php?event_id=1', $cookieFile);
runHttpTest("Registration form returns HTTP 200", $regPageRes['status'] === 200);
runHttpTest("Form contains Name, Email, and Event fields", 
    str_contains($regPageRes['body'], 'id="name"') && 
    str_contains($regPageRes['body'], 'id="email"') && 
    str_contains($regPageRes['body'], 'id="event_id"')
);

preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $regPageRes['body'], $tokenMatches);
$csrfToken = $tokenMatches[1] ?? '';
runHttpTest("Extracted valid CSRF token from registration form", strlen($csrfToken) === 64);

// 3. Test Registration Form Submission (Invalid Data)
echo "\n▶ 3. Testing Registration Validation Errors...\n";
$invalidPost = httpPost($baseUrl . '/register.php', [
    'csrf_token' => $csrfToken,
    'name' => '',
    'email' => 'bademail',
    'event_id' => '0'
], $cookieFile);
runHttpTest("Invalid submission renders validation errors", 
    str_contains($invalidPost['body'], 'Full Name is required') || 
    str_contains($invalidPost['body'], 'valid email')
);

// 4. Test Registration Form Submission (Valid Data)
echo "\n▶ 4. Testing Successful Registration Flow...\n";
$validEmail = 'alex.morgan.' . time() . '@example.com';
$validPost = httpPost($baseUrl . '/register.php?event_id=1', [
    'csrf_token' => $csrfToken,
    'name' => 'Alex Morgan',
    'email' => $validEmail,
    'event_id' => 1
], $cookieFile);

runHttpTest("Valid submission redirects to success page (HTTP 302)", $validPost['status'] === 302);

preg_match('/Location:\s*([^\r\n]+)/i', $validPost['headers'], $locMatches);
$redirectUrl = trim($locMatches[1] ?? '');
runHttpTest("Redirect URL contains success.php with registration code", str_contains($redirectUrl, 'success.php?code='));

// 5. Test Success Page
echo "\n▶ 5. Testing Confirmation Success Page...\n";
$successRes = httpGet($redirectUrl, $cookieFile);
runHttpTest("Success page returns HTTP 200", $successRes['status'] === 200);
runHttpTest("Success page displays user's name (Alex Morgan)", str_contains($successRes['body'], 'Alex Morgan'));
runHttpTest("Success page displays email and reference code", str_contains($successRes['body'], $validEmail) && str_contains($successRes['body'], 'Ref: REG-'));

// 6. Test Admin Protected Route Guard
echo "\n▶ 6. Testing Admin Portal Security & Login...\n";
$adminUnauth = httpGet($baseUrl . '/admin/index.php');
runHttpTest("Unauthenticated admin dashboard request redirects to login (HTTP 302)", $adminUnauth['status'] === 302);

$adminLoginPage = httpGet($baseUrl . '/admin/login.php', $cookieFile);
runHttpTest("Admin login page returns HTTP 200", $adminLoginPage['status'] === 200);
preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $adminLoginPage['body'], $adminTokenMatches);
$adminCsrf = $adminTokenMatches[1] ?? '';

// Perform Login
$adminLoginPost = httpPost($baseUrl . '/admin/login.php', [
    'csrf_token' => $adminCsrf,
    'username' => 'admin',
    'password' => 'admin123'
], $cookieFile);
runHttpTest("Admin credentials login redirects to dashboard (HTTP 302)", $adminLoginPost['status'] === 302);

// 7. Test Admin Dashboard with Authenticated Session
echo "\n▶ 7. Testing Admin Dashboard Registrations Table...\n";
$adminDashRes = httpGet($baseUrl . '/admin/index.php', $cookieFile);
runHttpTest("Admin dashboard loads with HTTP 200", $adminDashRes['status'] === 200);
runHttpTest("Dashboard displays required columns: ID, Name, Email, Event, Date Registered, Action", 
    str_contains($adminDashRes['body'], 'ID') &&
    str_contains($adminDashRes['body'], 'Name') &&
    str_contains($adminDashRes['body'], 'Email') &&
    str_contains($adminDashRes['body'], 'Event') &&
    str_contains($adminDashRes['body'], 'Date Registered') &&
    str_contains($adminDashRes['body'], 'Action')
);
runHttpTest("Dashboard displays participant registered in test (Alex Morgan)", 
    str_contains($adminDashRes['body'], 'Alex Morgan') &&
    str_contains($adminDashRes['body'], $validEmail)
);
runHttpTest("Dashboard contains View and Delete action buttons", 
    str_contains($adminDashRes['body'], 'View') &&
    str_contains($adminDashRes['body'], 'Delete')
);

// 8. Test Registration Deletion
echo "\n▶ 8. Testing Admin Delete Registration Action...\n";
preg_match('/confirmDeleteRegistration\((\d+),/', $adminDashRes['body'], $delMatches);
$deleteId = (int) ($delMatches[1] ?? 0);
runHttpTest("Found registration ID to delete", $deleteId > 0, "ID: {$deleteId}");

preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $adminDashRes['body'], $dashTokenMatches);
$dashCsrf = $dashTokenMatches[1] ?? '';

$deletePost = httpPost($baseUrl . '/admin/index.php', [
    'csrf_token' => $dashCsrf,
    'action' => 'delete_registration',
    'registration_id' => $deleteId
], $cookieFile);
runHttpTest("Delete action redirects (HTTP 302)", $deletePost['status'] === 302);

$afterDeleteDash = httpGet($baseUrl . '/admin/index.php', $cookieFile);
runHttpTest("Deleted registration #{$deleteId} is removed from the table", !str_contains($afterDeleteDash['body'], "Ref: REG-") || !str_contains($afterDeleteDash['body'], $validEmail));

// Cleanup
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

echo "\n======================================================\n";
echo "  HTTP Test Results: \033[32m{$testsPassed} Passed\033[0m, \033[" . ($testsFailed > 0 ? "31" : "32") . "m{$testsFailed} Failed\033[0m\n";
echo "======================================================\n\n";

if ($testsFailed > 0) {
    exit(1);
}
exit(0);
