<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Initial Administrator
    |--------------------------------------------------------------------------
    |
    | Credentials of the main administrator account created by the database
    | seeder. Change them right after the first sign-in on any shared or
    | production installation.
    |
    */

    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@medsystem.test'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cashier Export
    |--------------------------------------------------------------------------
    |
    | The most orders one Excel export of the cashier list may hold. The file
    | is built during the request, and the spreadsheet library keeps every
    | cell in memory until it is written: past this the cashier is asked to
    | narrow the filter (a day, a patient) instead.
    |
    */

    'cashier_export_max_rows' => 10_000,

];
