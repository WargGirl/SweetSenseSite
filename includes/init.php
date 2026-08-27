<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = CLASSES_PATH . '/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

// Session and localization
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_GET['lang']) && in_array($_GET['lang'], ['uk', 'en'], true)) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('lang', $_GET['lang'], time() + (86400 * 30), '/');
}

$currentLang = $_SESSION['lang'] 
    ?? $_COOKIE['lang'] 
    ?? (defined('DEFAULT_LANG') ? DEFAULT_LANG : 'uk');

if (!in_array($currentLang, ['uk', 'en'], true)) {
    $currentLang = 'uk';
}

$_SESSION['lang'] = $currentLang;

$langPath = defined('LANG_PATH') ? LANG_PATH : __DIR__ . '/../lang';
$langFilePath = $langPath . '/' . $currentLang . '.php';

if (file_exists($langFilePath)) {
    $GLOBALS['translations'] = require $langFilePath;
} else {
    $defaultPath = $langPath . '/uk.php';
    $GLOBALS['translations'] = file_exists($defaultPath) ? require $defaultPath : [];
}

function __(string $key, array $replace = []): string 
{
    $text = $GLOBALS['translations'][$key] ?? $key;

    if (!empty($replace)) {
        return vsprintf($text, $replace);
    }

    return $text;
}

// Visit counter
function logUserVisit(): void 
{
    $_SESSION['page_views'] = ($_SESSION['page_views'] ?? 0) + 1;

    $totalVisits = (int)($_COOKIE['total_visits'] ?? 0) + 1;
    setcookie('total_visits', (string)$totalVisits, time() + (86400 * 30), '/');

    $logDir = dirname(__DIR__) . '/storage/logs/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }

    $ip        = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $url       = $_SERVER['REQUEST_URI'] ?? '/';
    $userId    = $_SESSION['user_id'] ?? 'guest';
    $timestamp = date('Y-m-d H:i:s');

    $logLine = "[{$timestamp}] IP: {$ip} | User: {$userId} | URL: {$url} | SessionViews: {$_SESSION['page_views']} | TotalVisits: {$totalVisits}" . PHP_EOL;
    @file_put_contents($logDir . 'access.log', $logLine, FILE_APPEND | LOCK_EX);
}

logUserVisit();