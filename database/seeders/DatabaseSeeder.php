<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Cria um usuário admin padrão apenas se ainda não houver nenhum usuário no banco
        if (User::count() === 0) {
            User::factory()->create([
                'name' => 'Administrador Loja',
                'email' => 'admin@revenda.test',
                'password' => Hash::make('password'),
            ]);
        }

        // 10 veículos disponíveis na vitrine
        Vehicle::factory()
            ->count(10)
            ->available()
            ->create();

        // 3 veículos em preparação no pátio
        Vehicle::factory()
            ->count(3)
            ->preparation()
            ->create();

        // 2 veículos com status aleatórios (reservado/vendido)
        Vehicle::factory()
            ->count(2)
            ->create();
    }
}
