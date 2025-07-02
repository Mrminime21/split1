<?php
/**
 * Starlink Rent Devices - Main Entry Point
 * Version: 2.0.0
 * PHP-based satellite internet rental platform
 */

// Check if installation is needed
if (!file_exists('config.php') && !strpos($_SERVER['REQUEST_URI'], 'install.php')) {
    header('Location: install.php');
    exit;
}

// Load configuration if available
if (file_exists('config.php')) {
    $config = require_once 'config.php';
}

// Start session
session_start();

// Basic routing
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = trim($path, '/');

// Remove query parameters
$path = strtok($path, '?');

// Define routes
$routes = [
    '' => 'pages/home.php',
    'home' => 'pages/home.php',
    'login' => 'pages/login.php',
    'register' => 'pages/register.php',
    'dashboard' => 'pages/dashboard.php',
    'rental' => 'pages/rental.php',
    'investment' => 'pages/investment.php',
    'referrals' => 'pages/referrals.php',
    'payment' => 'pages/payment.php',
    'admin' => 'admin/index.php',
    'api' => 'api/index.php',
    'logout' => 'includes/logout.php'
];

// Handle routing
if (array_key_exists($path, $routes)) {
    $file = $routes[$path];
    if (file_exists($file)) {
        require_once $file;
    } else {
        require_once 'pages/404.php';
    }
} else {
    // Check for admin routes
    if (strpos($path, 'admin/') === 0) {
        require_once 'admin/index.php';
    } elseif (strpos($path, 'api/') === 0) {
        require_once 'api/index.php';
    } else {
        require_once 'pages/404.php';
    }
}
?>