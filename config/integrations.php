<?php

return [
    'ai' => [
        'formats' => [
            'openai_compatible' => \App\Adapters\Ai\OpenAiCompatibleAdapter::class,
            'anthropic'         => \App\Adapters\Ai\AnthropicAdapter::class,
            'gemini'            => \App\Adapters\Ai\GeminiAdapter::class,
            'image_generic'     => \App\Adapters\Ai\ImageGenericAdapter::class,
        ],
    ],

    'payment' => [
        'formats' => [
            'redirect_flow' => \App\Adapters\Payment\RedirectFlowAdapter::class,
            'embed_flow'    => \App\Adapters\Payment\EmbedFlowAdapter::class,
            'qris_flow'     => \App\Adapters\Payment\QrisFlowAdapter::class,
            'direct_charge' => \App\Adapters\Payment\DirectChargeAdapter::class,
        ],
    ],

    'sms' => [
        'formats' => [
            'rest' => \App\Adapters\Sms\RestSmsAdapter::class,
            'smpp' => \App\Adapters\Sms\SmppSmsAdapter::class,
        ],
    ],

    'whatsapp' => [
        'formats' => [
            'cloud_api'   => \App\Adapters\Whatsapp\CloudApiAdapter::class,
            'on_premises' => \App\Adapters\Whatsapp\OnPremAdapter::class,
            'aggregator'  => \App\Adapters\Whatsapp\AggregatorAdapter::class,
        ],
    ],

    'mail' => [
        'formats' => [
            'smtp' => \App\Adapters\Mail\SmtpMailAdapter::class,
            'api'  => \App\Adapters\Mail\ApiMailAdapter::class,
        ],
    ],

    'storage' => [
        'formats' => [
            's3_compatible' => \App\Adapters\Storage\S3CompatibleAdapter::class,
            'local'         => \App\Adapters\Storage\LocalAdapter::class,
        ],
    ],

    'captcha' => [
        'formats' => [
            'turnstile' => \App\Adapters\Captcha\TurnstileAdapter::class,
            'hcaptcha'  => \App\Adapters\Captcha\HcaptchaAdapter::class,
            'recaptcha' => \App\Adapters\Captcha\RecaptchaAdapter::class,
        ],
    ],

    'channel_adapters' => [
        'booking_com'   => \App\Adapters\Channel\BookingComAdapter::class,
        'agoda'         => \App\Adapters\Channel\AgodaAdapter::class,
        'traveloka'     => \App\Adapters\Channel\TravelokaAdapter::class,
        'tiket_com'     => \App\Adapters\Channel\TiketComAdapter::class,
        'expedia_eqc'   => \App\Adapters\Channel\ExpediaAdapter::class,
        'airbnb'        => \App\Adapters\Channel\AirbnbAdapter::class,
        'trip_com'      => \App\Adapters\Channel\TripComAdapter::class,
        'pegipegi'      => \App\Adapters\Channel\PegipegiAdapter::class,
        'mister_aladin' => \App\Adapters\Channel\MisterAladinAdapter::class,
    ],

    'channel_defaults' => [
        'booking_com' => [
            'base_url' => 'https://supply-xml.booking.com/hotels/ota/',
        ],
        'agoda' => [
            'base_url' => 'https://ycs.agoda.com/api/v1/',
        ],
        'traveloka' => [
            'base_url' => 'https://api.traveloka.com/v2/',
        ],
        'tiket_com' => [
            'base_url' => 'https://api.tiket.com/hotel/v1/',
        ],
        'expedia_eqc' => [
            'base_url' => 'https://services.expediapartnercentral.com/products/v1/',
            'oauth_base_url' => 'https://services.expediapartnercentral.com/',
        ],
        'airbnb' => [
            'base_url' => 'https://api.airbnb.com/v2/',
        ],
        'trip_com' => [
            'base_url' => 'https://api.trip.com/connect/v1/',
        ],
        'pegipegi' => [
            'base_url' => 'https://api.pegipegi.com/hotel/v2/',
        ],
        'mister_aladin' => [
            'base_url' => 'https://api.misteraladin.com/hotel/v1/',
        ],
    ],
];
