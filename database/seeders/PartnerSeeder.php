<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $partnershipLevels = ['Platinum', 'Gold', 'Silver', 'Bronze', 'Community'];
        $types = ['Corporate', 'Educational', 'Non-profit', 'Government', 'Media'];
        $categories = ['Technology', 'Finance', 'Education', 'Healthcare', 'Media', 'Retail'];
        $subCategories = ['Software', 'Hardware', 'Services', 'Consulting', 'Training', 'Research'];
        $statuses = ['active', 'inactive', 'pending', 'expired'];

        // Create 15 partners
        for ($i = 0; $i < 15; $i++) {
            $startDate = $faker->dateTimeBetween('-2 years', '-3 months');
            $endDate = $faker->dateTimeBetween('+3 months', '+2 years');
            $renewalDate = clone $endDate;
            $renewalDate->modify('-1 month');

            Partner::create([
                'organization_name' => $faker->company(),
                'contact_name' => $faker->name(),
                'contact_email' => $faker->safeEmail(),
                'contact_phone' => $faker->phoneNumber(),
                'website' => $faker->url(),
                'address' => $faker->streetAddress(),
                'city' => $faker->city(),
                'state' => $faker->state(),
                'zip' => $faker->postcode(),
                'country' => $faker->country(),
                'description' => $faker->paragraph(),
                'logo' => $faker->optional(0.8)->imageUrl(200, 100, 'business'),
                'status' => $faker->randomElement($statuses),
                'type' => $faker->randomElement($types),
                'category' => $faker->randomElement($categories),
                'sub_category' => $faker->randomElement($subCategories),
                'partnership_level' => $faker->randomElement($partnershipLevels),
                'partnership_start_date' => $startDate,
                'partnership_end_date' => $endDate,
                'partnership_renewal_date' => $renewalDate,
                'partnership_renewal_status' => $faker->randomElement(['pending', 'in-progress', 'completed', 'not-required']),
                'partnership_renewal_notes' => $faker->optional(0.6)->sentence(),
                'partnership_renewal_approval' => $faker->randomElement(['approved', 'rejected', 'pending']),
                'partnership_renewal_approval_notes' => $faker->optional(0.4)->sentence(),
                'partnership_renewal_approval_date' => $faker->optional(0.4)->dateTimeBetween('-1 month', 'now'),
                'partnership_renewal_approval_user' => $faker->optional(0.4)->name(),
            ]);
        }
    }
}
