<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Crear roles
        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $cobrador = Role::firstOrCreate(['name' => 'Cobrador']);

        // Permisos
        $permissions = [
            // Permisos del administrador
            'gestionar_cobradores', // Crear, editar y eliminar cobradores
            'ver_todos_los_prestamos',
            'crear_prestamos',
            'editar_prestamos',
            'eliminar_prestamos',
            'ver_todas_las_cobranzas',
            'crear_cobranzas',
            'editar_cobranzas',
            'eliminar_cobranzas',
            'acceder_base_financiera',
            'acceder_informes_financieros',
            'gestionar_movimientos_financieros', // Ver y modificar entradas, salidas y gastos
            'ver_todos_los_clientes',
            'gestionar_clientes', // Crear, editar y eliminar clientes

            // Permisos del cobrador
            'ver_sus_prestamos',
            'crear_prestamos',
            'ver_todos_los_clientes',
            'crear_clientes',
            'ver_plan_pagos',
            'crear_cobranzas',
            'ver_su_resumen_financiero', // Ver cuánto prestó y cuánto le falta cobrar
            'registrar_movimientos_financieros', // Solo salidas y gastos
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Asignar permisos
        $admin->givePermissionTo(Permission::all());
        $cobrador->givePermissionTo([
            'ver_sus_prestamos',
            'crear_prestamos',
            'ver_todos_los_clientes',
            'crear_clientes',
            'ver_plan_pagos',
            'crear_cobranzas',
            'ver_su_resumen_financiero',
            'registrar_movimientos_financieros',
        ]);
    }
}
