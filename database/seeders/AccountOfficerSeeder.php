<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountOfficer;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Str;

class AccountOfficerSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $users = User::all();

        if ($branches->isEmpty()) {
            $this->command->warn('No branches found. Seed branches first.');
            return;
        }

        foreach (range(1, 10) as $i) {

            $branch = $branches->random();
            $user = $users->isNotEmpty() ? $users->random() : null;

            AccountOfficer::create([
                'user_id' => $user?->id,
                'branch_id' => $branch->id,

                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),

                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->unique()->phoneNumber(),

                'gender' => fake()->randomElement(['male', 'female']),

                'code' => $this->generateOfficerCode(),

                'address' => fake()->address(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'country' => 'Nigeria',
            ]);
        }
    }

    private function generateOfficerCode(): string
    {
        do {
            $code = 'AO' . strtoupper(Str::random(6));
        } while (AccountOfficer::where('code', $code)->exists());

        return $code;
    }
}