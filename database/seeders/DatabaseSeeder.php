<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         if (! app()->environment(['local', 'testing'])) {
             if ($this->command) {
                 $this->command->warn('Seeder akun demo hanya dijalankan di environment local/testing.');
             }

             return;
         }

         User::firstOrCreate(
             ['email' => 'admin@example.com'],
             [
                 'name' => 'Admin',
                 'password' => Hash::make('admin123'),
                 'role' => 'admin',
             ]
         );

        $this->call(KasirSeeder::class);
    }
}