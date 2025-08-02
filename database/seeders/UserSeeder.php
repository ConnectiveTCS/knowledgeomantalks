<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Create admin user with firstOrCreate to avoid duplicates
        User::firstOrCreate(
            ['email' => 'kylem.mcpherson@outlook.com'],
            [
                'name' => 'Kyle',
                'password' => Hash::make('Morgan146@'),
                'email_verified_at' => now(),
            ]
        );

        // Create 20 regular users with unique emails
        for ($i = 0; $i < 20; $i++) {
            $email = $faker->unique()->safeEmail();

            // Skip if email already exists
            if (User::where('email', $email)->exists()) {
                continue;
            }

            User::create([
                'name' => $faker->name(),
                'email' => $email,
                'password' => Hash::make('password'),
                'email_verified_at' => $faker->optional(0.8)->dateTimeThisYear(),
            ]);
        }
    }
}
