<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    */

    'path' => [
        'query'             => app_path('GraphQL/Query/'),

        'type'              => app_path('GraphQL/Type/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    */

    'namespace' => [
        'query'             => 'App\GraphQL\Query',

        'type'              => 'App\GraphQL\Type',
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
