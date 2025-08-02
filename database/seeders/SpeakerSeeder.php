<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Speaker;
use App\Models\User;

class SpeakerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users
        $users = User::all();

        // Create speakers with valid user associations
        Speaker::factory()
            ->count(20)
            ->make()
            ->each(function ($speaker) use ($users) {
                // Assign a random user ID to each speaker
                $speaker->user_id = $users->random()->id;
                $speaker->save();
            });
    }
}
