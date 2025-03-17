<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Asegurar que el rol Administrador existe
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);

        // Crear usuario administrador si no existe
        $admin = User::firstOrCreate(
            ['email' => 'wilmer@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('12345678') // Cambia por una contraseña segura
            ]
        );

        // Asignar el rol usando Spatie
        $admin->assignRole($adminRole);
    }
}

