<?php

namespace Database\Seeders;

use App\Models\InteractiveActivity;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class InteractiveActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $userIds = User::pluck('id')->toArray();

        $activityTypes = [
            'Quiz',
            'Poll',
            'Q&A',
            'Survey',
            'Game',
            'Workshop',
            'Breakout Session',
            'Networking',
            'Discussion'
        ];

        // Create 20 interactive activities
        for ($i = 0; $i < 20; $i++) {
            $title = $faker->sentence(3);

            InteractiveActivity::create([
                'user_id' => $faker->randomElement($userIds) ?? 1,
                'title' => $title,
                'slug' => Str::slug($title),
                'description' => $faker->paragraph(3),
                'image' => $faker->optional(0.6)->imageUrl(640, 480, 'events'),
                'type' => $faker->randomElement($activityTypes),
            ]);
        }
    }
}
