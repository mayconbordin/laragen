<?php
/*
|--------------------------------------------------------------------------
| Generator Config
|--------------------------------------------------------------------------
|
|
*/
return [
    'base_path'      => base_path(),
    'root_namespace' => 'App\\',
    
    
    'model' => [
        'namespace' => 'App\\Models\\',
        'path'      => app_path().'/Models'
    ],

    'repository' => [
        'namespace' => 'App\\Repositories\\',
        'path'      => app_path().'/Repositories'
    ],

    'controller' => [
        'namespace' => 'App\\Http\\Controllers\\',
        'path' => app_path().'/Http/Controllers'
    ],

    'request' => [
        'namespace' => 'App\\Http\\Requests\\',
        'path' => app_path().'/Http/Requests'
    ],

    'migration' => [
        'path' => base_path().'/database/migrations'
    ],

    'pivot' => [
        'path' => base_path().'/database/migrations'
    ],

    'view' => [
        'path' => base_path().'/resources/views'
    ],

    'seed' => [
        'path' => base_path().'/database/seeds'
    ],
];
