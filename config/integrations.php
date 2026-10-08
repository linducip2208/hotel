<?php

use App\Adapters\Ai\AnthropicAdapter;
use App\Adapters\Ai\GeminiAdapter;
use App\Adapters\Ai\ImageGenericAdapter;
use App\Adapters\Ai\OpenAiCompatibleAdapter;
use App\Adapters\Captcha\HcaptchaAdapter;
use App\Adapters\Captcha\RecaptchaAdapter;
use App\Adapters\Captcha\TurnstileAdapter;
use App\Adapters\Channel\AgodaAdapter;
use App\Adapters\Channel\AirbnbAdapter;
use App\Adapters\Channel\BookingComAdapter;
use App\Adapters\Channel\ExpediaAdapter;
use App\Adapters\Channel\MisterAladinAdapter;
use App\Adapters\Channel\PegipegiAdapter;
use App\Adapters\Channel\TiketComAdapter;
use App\Adapters\Channel\TravelokaAdapter;
use App\Adapters\Channel\TripComAdapter;
use App\Adapters\Mail\ApiMailAdapter;
use App\Adapters\Mail\SmtpMailAdapter;
use App\Adapters\Payment\DirectChargeAdapter;
use App\Adapters\Payment\EmbedFlowAdapter;
use App\Adapters\Payment\QrisFlowAdapter;
use App\Adapters\Payment\RedirectFlowAdapter;
use App\Adapters\Sms\RestSmsAdapter;
use App\Adapters\Sms\SmppSmsAdapter;
use App\Adapters\Storage\LocalAdapter;
use App\Adapters\Storage\S3CompatibleAdapter;
use App\Adapters\Whatsapp\AggregatorAdapter;
use App\Adapters\Whatsapp\CloudApiAdapter;
use App\Adapters\Whatsapp\OnPremAdapter;

return [
    'ai' => [
        'formats' => [
            'openai_compatible' => OpenAiCompatibleAdapter::class,
            'anthropic' => AnthropicAdapter::class,
            'gemini' => GeminiAdapter::class,
            'image_generic' => ImageGenericAdapter::class,
        ],
    ],

    'payment' => [
        'formats' => [
            'redirect_flow' => RedirectFlowAdapter::class,
            'embed_flow' => EmbedFlowAdapter::class,
            'qris_flow' => QrisFlowAdapter::class,
            'direct_charge' => DirectChargeAdapter::class,
        ],
    ],

    'sms' => [
        'formats' => [
            'rest' => RestSmsAdapter::class,
            'smpp' => SmppSmsAdapter::class,
        ],
    ],

    'whatsapp' => [
        'formats' => [
            'cloud_api' => CloudApiAdapter::class,
            'on_premises' => OnPremAdapter::class,
            'aggregator' => AggregatorAdapter::class,
        ],
    ],

    'mail' => [
        'formats' => [
            'smtp' => SmtpMailAdapter::class,
            'api' => ApiMailAdapter::class,
        ],
    ],

    'storage' => [
        'formats' => [
            's3_compatible' => S3CompatibleAdapter::class,
            'local' => LocalAdapter::class,
        ],
    ],

    'captcha' => [
        'formats' => [
            'turnstile' => TurnstileAdapter::class,
            'hcaptcha' => HcaptchaAdapter::class,
            'recaptcha' => RecaptchaAdapter::class,
        ],
    ],

    'channel_adapters' => [
        'booking_com' => BookingComAdapter::class,
        'agoda' => AgodaAdapter::class,
        'traveloka' => TravelokaAdapter::class,
        'tiket_com' => TiketComAdapter::class,
        'expedia_eqc' => ExpediaAdapter::class,
        'airbnb' => AirbnbAdapter::class,
        'trip_com' => TripComAdapter::class,
        'pegipegi' => PegipegiAdapter::class,
        'mister_aladin' => MisterAladinAdapter::class,
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
