<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $categories = ['Conference', 'Workshop', 'Seminar', 'Webinar', 'Meetup', 'Hackathon'];
        $statuses = ['upcoming', 'ongoing', 'completed', 'cancelled', 'postponed'];
        $visibilities = ['public', 'private', 'members-only'];
        $accessibilities = ['wheelchair', 'sign-language', 'closed-captions', 'audio-description'];

        // Create 20 events
        for ($i = 0; $i < 20; $i++) {
            $startDate = $faker->dateTimeBetween('-1 month', '+3 months');
            $endDate = clone $startDate;
            $endDate->modify('+' . $faker->numberBetween(1, 5) . ' days');

            $latitude = $faker->latitude(24.0, 49.0); // US bounds
            $longitude = $faker->longitude(-125.0, -66.0); // US bounds

            $tags = $faker->randomElements(['technology', 'business', 'design', 'marketing', 'development', 'ai', 'blockchain', 'cloud', 'mobile', 'web', 'data'], $faker->numberBetween(2, 5));

            // Ensure accessibility is always a string value (not null)
            $randomAccessibilities = $faker->boolean(50)
                ? $faker->randomElements($accessibilities, $faker->numberBetween(1, 3))
                : ['none'];

            Event::create([
                'name' => $faker->sentence(3),
                'photo' => $faker->optional(0.7)->imageUrl(800, 600, 'events'),
                'video' => $faker->optional(0.3)->url(),
                'description' => $faker->paragraphs(3, true),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'location' => $faker->address(),
                'organizer' => $faker->company(),
                'contact_email' => $faker->safeEmail(),
                'contact_phone' => $faker->phoneNumber(),
                'website' => $faker->url(),
                'social_media' => $faker->optional(0.6)->url(),
                'category' => $faker->randomElement($categories),
                'tags' => implode(',', $tags),
                'status' => $faker->randomElement($statuses),
                'visibility' => $faker->randomElement($visibilities),
                'accessibility' => implode(',', $randomAccessibilities),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);
        }
    }
}
