<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SettingsService
{
    public function all(): array
    {
        $defaults = config('vyora.settings_defaults');
        try {
            $stored = Cache::remember('vyora.settings', 300, fn () => Setting::pluck('value', 'key')->all());
        } catch (\Throwable) {
            $stored = [];
        }

        return array_merge($defaults, array_filter($stored, fn ($v) => $v !== null));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            if (array_key_exists($key, config('vyora.settings_defaults'))) {
                Setting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
            }
        }
        Cache::forget('vyora.settings');
    }
}
