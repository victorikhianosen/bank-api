<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('account_categories')->insert([
            [
                'id' => Str::ulid(),
                'name' => 'Savings',
                'code' => 'SAV',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::ulid(),
                'name' => 'Current',
                'code' => 'CUR',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::ulid(),
                'name' => 'Fixed Deposit',
                'code' => 'FD',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}