<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Fix for shared hosting: strip /public from SCRIPT_NAME
// so Laravel generates clean URLs (e.g. /login instead of /public/login)
if (isset($_SERVER['SCRIPT_NAME']) && str_starts_with($_SERVER['SCRIPT_NAME'], '/public')) {
    $_SERVER['SCRIPT_NAME'] = str_replace('/public', '', $_SERVER['SCRIPT_NAME']);
    $_SERVER['PHP_SELF'] = str_replace('/public', '', $_SERVER['PHP_SELF']);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
