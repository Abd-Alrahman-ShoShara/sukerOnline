<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'startTime'   => '08:00',
            'endTime'     => '17:00',
            'abouteUs'    => 'نص تعريفي بالشركة',
            'Num1'        => '+1234567890',
            'Num2'        => '+0987654321',
            'Num3'        => '+1122334455',
            'storePrice'  => 50,
            'urgentPrice' => 100,
            'isActive'    => true,
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
