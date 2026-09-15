<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class SiteSettingsService
{
    private string $path;

    public function __construct()
    {
        $this->path = storage_path('app/site_settings.json');
    }

    public function get(): array
    {
        return File::exists($this->path)
            ? (json_decode(File::get($this->path), true) ?? [])
            : [];
    }

    public function save(array $data): void
    {
        File::put($this->path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function merge(array $patch): void
    {
        $this->save(array_merge($this->get(), $patch));
    }

    public function stripeKey(): ?string
    {
        return $this->get()['stripe_secret_key'] ?? env('STRIPE_SECRET_KEY');
    }

    public function stripePublicKey(): ?string
    {
        return $this->get()['stripe_public_key'] ?? env('STRIPE_PUBLIC_KEY');
    }

    public function webhookSecret(): ?string
    {
        return $this->get()['stripe_webhook_secret'] ?? env('STRIPE_WEBHOOK_SECRET');
    }
}
