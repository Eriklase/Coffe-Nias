<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KasirSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            if ($this->command) {
                $this->command->warn('Demo kasir hanya dibuat di environment local/testing.');
            }

            return;
        }

        User::firstOrCreate(
            ['email' => 'kasir@example.com'],
            [
                'name' => 'Kasir Nias Coffee',
                'password' => Hash::make('kasir123'),
                'role' => 'kasir',
            ]
        );
    }
}
