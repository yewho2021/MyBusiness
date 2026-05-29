<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (isset($_SERVER['SCRIPT_NAME']) && str_starts_with($_SERVER['SCRIPT_NAME'], '/public')) {
    $_SERVER['SCRIPT_NAME'] = str_replace('/public', '', $_SERVER['SCRIPT_NAME']);
    $_SERVER['PHP_SELF'] = str_replace('/public', '', $_SERVER['PHP_SELF']);
}

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());