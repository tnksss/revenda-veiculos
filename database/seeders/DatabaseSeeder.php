<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() === 0) {
            User::factory()->create([
                'name' => 'Administrador Loja',
                'email' => 'admin@revenda.test',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
            ]);
        }

        $this->call([
            VehicleSeeder::class,
        ]);
    }
}
