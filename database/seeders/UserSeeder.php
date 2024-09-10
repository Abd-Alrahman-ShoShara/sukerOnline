<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        User::create(
            [
                'name'=>'Admin1',
                'phone'=> '0948347729',
                'password'=> bcrypt('123456789'),
                'role'=> '0',
            ]);
            User::create(
            [
                'name'=>'abd',
                'phone'=> '0943959774',
                'password'=> bcrypt('123456789'),
                'role'=> '1',
                'nameOfStore'=>'agkerde',
                'adress'=>'damas',
                'classification_id'=>'1',
            ]);
    }
}
