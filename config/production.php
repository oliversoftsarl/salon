<?php

/**
 * Configuration de production
 * Ce fichier contient les paramètres optimisés pour la production
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Production Optimizations
    |--------------------------------------------------------------------------
    |
    | Ces paramètres sont appliqués automatiquement en production
    |
    */

    'opcache' => [
        'enable' => true,
        'memory_consumption' => 256,
        'interned_strings_buffer' => 16,
        'max_accelerated_files' => 20000,
        'validate_timestamps' => false,
        'save_comments' => true,
        'fast_shutdown' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'security_headers' => [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'views' => true,
        'routes' => true,
        'config' => true,
        'events' => true,
    ],
];

