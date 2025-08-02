<?php

namespace Database\Seeders;

use App\Models\Volunteer;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class VolunteerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $statuses = ['pending', 'approved', 'rejected'];

        // Create 30 volunteers
        for ($i = 0; $i < 30; $i++) {
            Volunteer::create([
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->phoneNumber(),
                'address' => $faker->streetAddress(),
                'city' => $faker->city(),
                'skills' => $faker->words(3, true),
                'availability' => $faker->randomElement(['weekends', 'evenings', 'weekdays', 'flexible']),
                'interests' => $faker->sentence(),
                'notes' => $faker->optional(0.7)->paragraph(),
                'active' => $faker->boolean(80),
                'status' => $faker->randomElement($statuses),
                'referral_source' => $faker->randomElement(['friend', 'social media', 'website', 'event', 'other']),
                'background_check_status' => $faker->randomElement(['pending', 'completed', 'not required']),
                'background_check_date' => $faker->optional(0.6)->dateTimeThisYear(),
                'background_check_notes' => $faker->optional(0.4)->sentence(),
                'training_status' => $faker->randomElement(['pending', 'completed', 'not required']),
                'training_date' => $faker->optional(0.6)->dateTimeThisYear(),
                'training_notes' => $faker->optional(0.4)->sentence(),
                'orientation_status' => $faker->randomElement(['pending', 'completed', 'not required']),
                'orientation_date' => $faker->optional(0.6)->dateTimeThisYear(),
                'orientation_notes' => $faker->optional(0.4)->sentence(),
                'emergency_contact_name' => $faker->name(),
                'emergency_contact_phone' => $faker->phoneNumber(),
                'emergency_contact_relationship' => $faker->randomElement(['parent', 'spouse', 'sibling', 'friend', 'relative']),
                'medical_conditions' => $faker->optional(0.3)->sentence(),
                'allergies' => $faker->optional(0.3)->words(2, true),
                'languages' => implode(',', $faker->randomElements(['English', 'Spanish', 'French', 'German', 'Chinese', 'Japanese'], $faker->numberBetween(1, 3)))
            ]);
        }
    }
}
