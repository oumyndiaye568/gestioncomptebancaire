<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::firstOrCreate([
            'email' => 'admin@test.com'
        ], [
            'nom' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);

        Admin::firstOrCreate([
            'email' => 'oumyndiaye@gmail.com'
        ], [
            'nom' => 'Oumy Ndiaye',
            'email' => 'oumyndiaye@gmail.com',
            'password' => Hash::make('oumy123'),
        ]);
    }
}