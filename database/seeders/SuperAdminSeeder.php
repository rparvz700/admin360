<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::create([
            'name' => 'Raihan Parvez',
            'email' => 'raihan.parvez@summitcommunications.net',
            'password' => Hash::make('Scomm@123')
        ]);
        $superAdmin->assignRole('Super Admin');
    }
}
