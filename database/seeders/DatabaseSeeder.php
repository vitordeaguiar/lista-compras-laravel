<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo user — change before production!
        User::firstOrCreate(
            ['email' => 'demo@lista.com'],
            [
                'name'     => 'Demo',
                'password' => Hash::make('password'),
            ]
        );

        $this->call(AdminSeeder::class);
    }
}
