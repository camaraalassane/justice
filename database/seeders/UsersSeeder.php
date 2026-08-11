<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrateur',
                'email' => 'admin@gpj.mil',
                'role' => 'ADMIN',
            ],
            [
                'name' => 'Chef Division Disciplinaire',
                'email' => 'cdd@gpj.mil',
                'role' => 'CDD',
            ],
            [
                'name' => 'Chef Section Statistique',
                'email' => 'cds@gpj.mil',
                'role' => 'CDS',
            ],
            [
                'name' => 'Chef Bureau Juridique',
                'email' => 'cdb@gpj.mil',
                'role' => 'CDB',
            ],
            [
                'name' => 'Agent de Saisie',
                'email' => 'ads@gpj.mil',
                'role' => 'ADS',
            ],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make('password'),
                'role' => $user['role'],
                'email_verified_at' => now(),
            ]);
        }
    }
}