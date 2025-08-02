<?php

namespace Database\Seeders;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class AttendeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $eventIds = Event::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        // Create 50 attendees
        for ($i = 0; $i < 50; $i++) {
            Attendee::create([
                'event_id' => $faker->randomElement($eventIds) ?? 1,
                'user_id' => $faker->optional(0.7)->randomElement($userIds),
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->phoneNumber(),
            ]);
        }
    }
}
