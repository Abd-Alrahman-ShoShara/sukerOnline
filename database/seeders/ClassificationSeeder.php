<?php

namespace Database\Seeders;

use App\Models\Classification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Classification::create([
            'name'=>'hotel'
        ]);
    
        Classification::create([
            'name'=>'cinema'
        ]);
    
        Classification::create([
            'name'=>'restraunt'
        ]);
    
        Classification::create([
            'name'=>'super market'
        ]);
    }
}
