<?php

return [
    'mode' => env('APP_MODE', 'standalone'),

    'features' => [
        'channel_manager'      => true,
        'pos'                  => true,
        'housekeeping_mobile'  => true,
        'accounting'           => true,
        'pseo'                 => env('PSEO_ENABLED', true),
        'guest_portal'         => true,
        'self_checkin'         => false,
        'banquet'              => false,
        'spa'                  => false,
        'hr_payroll'           => false,
        'revenue_management'   => false,
        'ai_concierge'         => false,
        'marketplace_addons'   => false,
        'white_label'          => false,
    ],

    'limits' => [
        'max_rooms' => null,
        'max_users' => null,
        'max_properties' => 1,
    ],

    'currency_default' => 'IDR',
    'locale_default'   => env('APP_LOCALE', 'id'),

    'date_operating' => 'today',

    'auto_assign_room' => true,

    'room_assignment_rules' => [
        'guest_preference' => true,
        'floor_balance' => true,
        'room_proximity' => true,
        'previous_room' => true,
        'clean_first' => true,
        'first_available' => true,
    ],
];
