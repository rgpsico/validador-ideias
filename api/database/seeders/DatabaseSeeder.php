<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * O admin do Filament sai de ADMIN_SEED_EMAIL / ADMIN_SEED_PASSWORD (.env).
     * Sem senha definida, gera uma aleatória e mostra uma única vez.
     * Usuário que já existe não tem a senha alterada.
     */
    public function run(): void
    {
        $email = env('ADMIN_SEED_EMAIL', 'admin@example.com');
        $senha = env('ADMIN_SEED_PASSWORD');

        if (! $senha) {
            $senha = Str::random(20);
            $this->command?->warn("Senha gerada para {$email}: {$senha}");
        }

        User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($senha),
                'email_verified_at' => now(),
            ]
        );

        $this->call(PaoflixSeeder::class);
    }
}
