<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Veltrix Name & Branding
    |--------------------------------------------------------------------------
    |
    | Displayed in browser title, login screen, navigation bars, and footer.
    |
    */
    'name' => env('VELTRIX_NAME', 'Veltrix'),

    'tagline' => 'Familiar to Build. Modern by Design.',

    'logo' => env('VELTRIX_LOGO', '<span class="vx-logo-brand"><b>Vel</b>trix</span>'),

    'logo-mini' => env('VELTRIX_LOGO_MINI', '<span class="vx-logo-brand"><b>Vx</b></span>'),

    /*
    |--------------------------------------------------------------------------
    | Admin directory & bootstrap file
    |--------------------------------------------------------------------------
    |
    | Default application directory where scaffolding, routes, and controllers reside.
    |
    */
    'directory' => app_path('Admin'),

    'bootstrap' => app_path('Admin/bootstrap.php'),

    /*
    |--------------------------------------------------------------------------
    | Routing configuration
    |--------------------------------------------------------------------------
    */
    'route' => [
        'prefix'     => env('VELTRIX_ROUTE_PREFIX', env('ADMIN_ROUTE_PREFIX', 'admin')),
        'namespace'  => 'App\\Admin\\Controllers',
        'middleware' => ['web', 'admin'],
        'domain'     => env('VELTRIX_ROUTE_DOMAIN', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Browser Title & Favicon
    |--------------------------------------------------------------------------
    */
    'title'   => env('VELTRIX_TITLE', 'Veltrix Admin'),
    'favicon' => env('VELTRIX_FAVICON', null),

    /*
    |--------------------------------------------------------------------------
    | HTTPS enforcement
    |--------------------------------------------------------------------------
    */
    'https'  => env('VELTRIX_HTTPS', env('ADMIN_HTTPS', false)),
    'secure' => env('VELTRIX_SECURE', false),

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard & Providers
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'controller' => \Veltrix\Controllers\AuthController::class,

        'guard' => 'admin',

        'guards' => [
            'admin' => [
                'driver'   => 'session',
                'provider' => 'admin',
            ],
        ],

        'providers' => [
            'admin' => [
                'driver' => 'eloquent',
                'model'  => \Veltrix\Auth\Database\Administrator::class,
            ],
        ],

        'remember' => true,

        'redirect_to' => 'auth/login',

        // Paths excluded from authentication / authorization checks
        'excepts' => [
            'auth/login',
            'auth/logout',
            '_veltrix_assets_/*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Uploads
    |--------------------------------------------------------------------------
    */
    'upload' => [
        'disk' => env('VELTRIX_UPLOAD_DISK', 'public'),

        'directory' => [
            'image' => 'images',
            'file'  => 'files',
        ],

        // Security: strictly allowed extensions and max file sizes (KB)
        'allowed_file_types'  => ['pdf', 'zip', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt'],
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'disallowed_types'    => ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'sh', 'bash', 'exe', 'pl', 'cgi', 'bat'],
        'max_file_size'       => 20480, // 20 MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Database & Tables
    |--------------------------------------------------------------------------
    */
    'database' => [
        'connection' => '',

        'users_table' => 'admin_users',
        'users_model' => \Veltrix\Auth\Database\Administrator::class,

        'roles_table' => 'admin_roles',
        'roles_model' => \Veltrix\Auth\Database\Role::class,

        'permissions_table' => 'admin_permissions',
        'permissions_model' => \Veltrix\Auth\Database\Permission::class,

        'menu_table' => 'admin_menu',
        'menu_model' => \Veltrix\Auth\Database\Menu::class,

        'operation_log_table'    => 'admin_operation_log',
        'user_permissions_table' => 'admin_user_permissions',
        'role_users_table'       => 'admin_role_users',
        'role_permissions_table' => 'admin_role_permissions',
        'role_menu_table'        => 'admin_role_menu',
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Operation Logging
    |--------------------------------------------------------------------------
    */
    'operation_log' => [
        'enable' => env('VELTRIX_OPERATION_LOG', true),

        'allowed_methods' => ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'PATCH'],

        // Secret keys to mask from logged inputs
        'secret_fields' => ['password', 'password_confirmation', '_token'],

        'except' => [
            'auth/logs*',
            '_veltrix_assets_*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route & Menu Permissions Enforcement
    |--------------------------------------------------------------------------
    */
    'check_route_permission' => true,
    'check_menu_roles'       => true,
    'menu_bind_permission'   => true,

    /*
    |--------------------------------------------------------------------------
    | UI Theme & Preferences
    |--------------------------------------------------------------------------
    */
    'theme' => env('VELTRIX_THEME', 'system'), // light, dark, system

    'skin' => 'skin-veltrix',

    'layout' => ['sidebar-mini'],

    'default_avatar' => null,

    'show_version'     => true,
    'show_environment' => true,

    'enable_default_breadcrumb' => true,
    'enable_menu_search'        => true,

    'top_alert' => '',

    'grid_action_class' => \Veltrix\Grid\Displayers\DropdownActions::class,

    'extension_dir' => app_path('Admin/Extensions'),

    'extensions' => [],
];
