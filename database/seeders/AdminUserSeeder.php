<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'KyleM.McPherson@outlook.com'],
            [
                'name'     => 'Super Admin',  // change to a secure name
                'phone'   => '+96895302945',  // change to a secure phone number
                'password' => Hash::make('Morgan146@'),  // change to a secure pass
                'role'     => 'admin',
            ]
        );
    }
}
