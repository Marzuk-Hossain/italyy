<?php
// Prevent multiple inclusions
if (!defined('ITALY_OPTICS_CONFIG')) {
    define('ITALY_OPTICS_CONFIG', true);

    // Error reporting during development
    error_reporting(E_ALL);
    ini_set('display_errors', '0'); // Clean HTML output, errors logged

    // Session initialization with project-local session path
    $sessionDir = __DIR__ . '/../sessions';
    if (!is_dir($sessionDir)) {
        @mkdir($sessionDir, 0777, true);
    }
    if (is_writable($sessionDir)) {
        @session_save_path($sessionDir);
    }
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    // Database Configuration (supports optional 1-click setup override)
    $dbOverrideFile = __DIR__ . '/db_override.json';
    $dbOverride = is_file($dbOverrideFile) ? (json_decode((string)file_get_contents($dbOverrideFile), true) ?: []) : [];
    define('DB_HOST', getenv('DB_HOST') ?: ($dbOverride['host'] ?? 'localhost'));
    define('DB_PORT', getenv('DB_PORT') ?: ($dbOverride['port'] ?? '3306'));
    define('DB_NAME', getenv('DB_NAME') ?: ($dbOverride['dbname'] ?? 'italy_optics'));
    define('DB_USER', getenv('DB_USER') ?: ($dbOverride['user'] ?? 'root'));
    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($dbOverride['pass'] ?? ''));
    define('DB_CHARSET', 'utf8mb4');

    // Store Details in Clean English
    define('SITE_NAME', 'Italy Optics');
    define('SITE_NAME_TITLE', 'Italy Optics – Luxury Eyewear & Optician in Chattogram');
    define('SITE_TAGLINE', 'Optician in Chittagong · Luxury Monochrome Eyewear Studio');
    define('STORE_ADDRESS', '12 Jamal Khan Road, Chattogram 4000');
    define('STORE_ADDRESS_EN', '12 Jamal Khan Road, Chittagong 4000, Bangladesh');
    define('STORE_PHONE', '01708-541674');
    define('STORE_EMAIL', 'info@italyoptics.bd');
    define('STORE_HOURS', 'Open · Closes 10 PM');
    define('STORE_RATING', '5.0');
    define('STORE_REVIEWS_COUNT', '3 Google reviews');
    define('FACEBOOK_URL', 'https://facebook.com/italyoptics.ctg');
    define('CURRENCY', '৳');

    // Google Search SEO Meta Keywords & Description
    define('SITE_KEYWORDS', 'italy optics, italy optics chattogram, italy optics chittagong, ইতালি অপটিক্স, optician in chittagong, optical shop jamal khan, eyewear chattogram, eyeglasses chittagong, chashma shop chittagong, prescription glasses bangladesh, polarized sunglasses chattogram, blue light blocking glasses, computer glasses bangladesh, contact lenses chattogram, luxury italian eyewear, titanium eyeglass frames bd, reading glasses bangladesh');
    define('SITE_DESCRIPTION', 'Shop handcrafted Italian titanium eyeglasses, polarized UV400 sunglasses, blue light & contact lenses at Italy Optics, 12 Jamal Khan Road, Chattogram 4000.');

    // Base URL calculation
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $basePath = rtrim(str_replace(['/admin', '/api'], '', $scriptDir), '/\\');
    define('BASE_URL', $protocol . $host . $basePath);
}
