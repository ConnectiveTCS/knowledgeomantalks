<?php

namespace Database\Seeders;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get a user to assign topics to
        $user = User::first() ?? User::factory()->create();

        $topics = [
            'Artificial Intelligence',
            'Machine Learning',
            'Blockchain Technology',
            'Web Development',
            'Mobile App Development',
            'Cybersecurity',
            'Data Science',
            'Cloud Computing',
            'DevOps',
            'UI/UX Design',
            'Internet of Things (IoT)',
            'Augmented Reality',
            'Virtual Reality',
            'Digital Marketing',
            'Entrepreneurship'
        ];

        foreach ($topics as $topic) {
            $slug = Str::slug($topic);

            // Use firstOrCreate to avoid duplicate entries
            Topic::firstOrCreate(
                ['slug' => $slug],
                [
                    'foreign_id' => $faker->unique()->randomNumber(5),
                    'title' => $topic,
                    'description' => $faker->paragraph(3),
                    'image' => $faker->optional(0.7)->imageUrl(640, 480, 'technology'),
                    'user_id' => $user->id,
                ]
            );
        }
    }
}
