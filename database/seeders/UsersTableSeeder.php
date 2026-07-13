<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'branch_id' => 1,
                'first_name' => 'Victor',
                'last_name' => 'David',
                'middle_name' => null,
                'photo' => null,
                'username' => 'victor',
                'password' => Hash::make('12345678'),
                'status' => 'active',
                'email' => 'victor@example.com',
                'email_verified_at' => now(),
                'phone' => '07033274155',
                'gender' => 'male',
                'address' => null,
                'city' => 'Lagos',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'last_login' => null,
                'google2fa_secret' => null,
                'two_factor_enabled' => 0,
                'two_factor_confirmed_at' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'branch_id' => 1,
                'first_name' => 'Sam',
                'last_name' => 'Johnson',
                'middle_name' => null,
                'photo' => null,
                'username' => 'sam',
                'password' => Hash::make('12345678'),
                'status' => 'active',
                'email' => 'adejusamuel@gmail.com',
                'email_verified_at' => now(),
                'phone' => '08012345678',
                'gender' => 'male',
                'address' => null,
                'city' => 'Lagos',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'last_login' => null,
                'google2fa_secret' => null,
                'two_factor_enabled' => 0,
                'two_factor_confirmed_at' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
