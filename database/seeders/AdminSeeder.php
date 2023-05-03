<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'uuid' => Str::orderedUuid(),
            'first_name' => "Dennis",
            'last_name' => "Karimi",
            'is_admin' => true,
            'email' => 'admin@buckhill.co.uk',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'avatar' => '',
            'address' => '95 Nairobi',
            'phone_number' => '+254704128303',
            'is_marketing' => false
        ]);
    }
}
