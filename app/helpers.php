<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return Setting::find($key)->value ?? $default;
    }
}

if (!function_exists('set_setting')) {
    function set_setting(string $key, $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        // إذا استخدمت كاش لاحقًا احذف هذا المفتاح هنا
        // cache()->forget("setting.$key");
    }
}
