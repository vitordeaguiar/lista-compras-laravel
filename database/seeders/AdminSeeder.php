<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\User::where('email', 'vitordeaguiar0@gmail.com')
            ->update(['is_admin' => true]);
    }
}
