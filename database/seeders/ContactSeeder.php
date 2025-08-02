<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Create 25 contacts
        for ($i = 0; $i < 25; $i++) {
            Contact::create([
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'email' => $faker->safeEmail(),
                'phone' => $faker->phoneNumber(),
                'message' => $faker->paragraph(),
            ]);
        }
    }
}
