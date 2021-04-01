<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    */

    'path' => [

        'interface' => app_path('Interfaces/'),
        
        'vues' => resource_path('js/Pages/'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    */

    'namespace' => [

        'interface' => 'App\Interfaces',

    ],

    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    |
    */

    'options' => [

        'subscription' => false, // Enable GraphQL Subscriptions; Requires Pusher.

    ],

    /*
    |--------------------------------------------------------------------------
    | Custom License Details
    |--------------------------------------------------------------------------
    |
    */
    'license' => [

        'package' => 'Incredible Package Name',

        'authors' => [

            'Jane Doe <jane.doe@example.com>',

            'Joe Blogs <joe.blogs@example.com>',

        ],

        'copyright' => '2019 ACME Corp.',

        'license' => 'license.md All rights reserved.',
    ],
];
