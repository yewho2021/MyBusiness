<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Debugbar Settings
     |--------------------------------------------------------------------------
     |
     | Debugbar is disabled by default. To enable, set DEBUGBAR_ENABLED=true
     | in your .env file. Never enable in production — it leaks request data,
     | queries, session info, and creates hundreds of files in storage/debugbar/.
     |
     */

    'enabled' => env('DEBUGBAR_ENABLED', false),

    'storage' => [
        'enabled' => true,
        'driver' => 'file',
        'path' => storage_path('debugbar'),
        'connection' => null,
        'provider' => '',
    ],

    'include_vendors' => true,

    'capture_ajax' => true,
    'add_ajax_timing' => false,

    'error_handler' => false,

    'collectors' => [
        'phpinfo'    => true,
        'messages'   => true,
        'time'       => true,
        'memory'     => true,
        'exceptions' => true,
        'log'        => true,
        'db'         => true,
        'views'      => true,
        'route'      => true,
        'auth'       => false,
        'gate'       => true,
        'session'    => true,
        'request'    => true,
        'mail'       => true,
        'laravel'    => false,
        'events'     => false,
        'default_request' => false,
        'logs'       => false,
        'files'      => false,
        'config'     => false,
        'cache'      => false,
        'models'     => true,
    ],

];
