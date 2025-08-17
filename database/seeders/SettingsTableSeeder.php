<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsTableSeeder extends Seeder
{
    public function run()
    {
        Setting::updateOrCreate(['key' => 'startTime'], ['value' => '09:00']);
        Setting::updateOrCreate(['key' => 'endTime'], ['value' => '12:00']);
        Setting::updateOrCreate(['key' => 'abouteUs'], ['value' => 'معلومات عن التطبيق ...']);
        Setting::updateOrCreate(['key' => 'Num1'], ['value' => '123456789']);
        Setting::updateOrCreate(['key' => 'Num2'], ['value' => '987654321']);
        Setting::updateOrCreate(['key' => 'Num3'], ['value' => '555555555']);
        Setting::updateOrCreate(['key' => 'storePrice'], ['value' => "10"]);
        Setting::updateOrCreate(['key' => 'urgentPrice'], ['value' => "20"]);
        Setting::updateOrCreate(['key' => 'isActive'], ['value' => true]);
    }
}
