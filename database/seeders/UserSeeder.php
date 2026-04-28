<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run()
{
User::create([
    'name' => 'Owner',
    'email' => 'owner@gmail.com',
    'password' => Hash::make('12345678'),
    'role' => 'owner',
]);
}
}