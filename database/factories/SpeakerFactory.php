<?php

namespace Database\Factories;

use App\Models\Speaker;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpeakerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Speaker::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Define industries array for random selection
        $industries = [
            'Technology',
            'Healthcare',
            'Finance',
            'Education',
            'Manufacturing',
            'Retail',
            'Media',
            'Consulting',
            'Energy',
            'Transportation',
            'Telecommunications',
            'Non-profit',
            'Government',
            'Entertainment',
            'Hospitality'
        ];

        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'company' => $this->faker->company(),
            'industry' => $this->faker->randomElement($industries),
            'position' => $this->faker->jobTitle(),
            'bio' => $this->faker->paragraphs(3, true),
            'photo' => 'https://via.placeholder.com/300x300.png/000055?text=speaker+' . $this->faker->word(),
            'CV_Resume' => 'https://via.placeholder.com/300x300.png/000055?text=CV+Resume+' . $this->faker->word(),
            'website' => $this->faker->url(),
            'linkedin' => $this->faker->url(),
            'twitter' => $this->faker->url(),
            'facebook' => $this->faker->url(),
            'instagram' => $this->faker->url(),
            'youtube' => $this->faker->url(),
            'tiktok' => $this->faker->url(),
            // Note: user_id is intentionally not defined here since we're setting it in the seeder
        ];
    }
}
