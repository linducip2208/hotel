<?php

namespace App\Services\Integrations;

use App\Models\Provider;
use App\Models\ProviderFeatureAssignment;

class ProviderRegistry
{
    public function __construct(protected AdapterFactory $factory) {}

    public function forFeature(int $propertyId, string $feature)
    {
        $assignment = ProviderFeatureAssignment::query()
            ->where('property_id', $propertyId)
            ->where('feature', $feature)
            ->first();

        $provider = $assignment?->provider ?? Provider::query()
            ->where('property_id', $propertyId)
            ->where('integration_type', $this->featureToType($feature))
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();

        return $provider ? $this->factory->make($provider) : null;
    }

    public function defaultFor(int $propertyId, string $integrationType)
    {
        $provider = Provider::query()
            ->where('property_id', $propertyId)
            ->where('integration_type', $integrationType)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
        return $provider ? $this->factory->make($provider) : null;
    }

    protected function featureToType(string $feature): string
    {
        return match (true) {
            str_starts_with($feature, 'ai_') => 'ai',
            str_starts_with($feature, 'sms_') => 'sms',
            str_starts_with($feature, 'wa_'), str_starts_with($feature, 'whatsapp_') => 'whatsapp',
            str_starts_with($feature, 'mail_') => 'mail',
            str_starts_with($feature, 'booking_payment'), str_starts_with($feature, 'payment_') => 'payment',
            str_starts_with($feature, 'storage_') => 'storage',
            str_starts_with($feature, 'captcha_') => 'captcha',
            default => 'other',
        };
    }
}
