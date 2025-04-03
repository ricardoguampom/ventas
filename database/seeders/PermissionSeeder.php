<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // 📦 Categorías
            'categorias.crear' => 'Crear categorías',
            'categorias.listar' => 'Listar categorías',
            'categorias.editar' => 'Editar categorías',
            'categorias.eliminar' => 'Eliminar categorías',
 
            // 📦 Artículos
            'articulos.crear' => 'Crear artículos',
            'articulos.listar' => 'Listar artículos',
            'articulos.ver' => 'Ver artículos',
            'articulos.editar' => 'Editar artículos',
            'articulos.eliminar' => 'Eliminar artículos',
            'articulos.reporte_exportar' => 'Exportar reporte de artículos',
            'articulos.ver_inversion' => 'Ver total inversión de artículos',

            // 👥 Proveedores
            'proveedores.listar' => 'Listar proveedores',
            'proveedores.ver' => 'Ver proveedores',
            'proveedores.crear' => 'Crear proveedores',
            'proveedores.editar' => 'Editar proveedores',
            'proveedores.eliminar' => 'Eliminar proveedores',

            // 📥 Ingresos
            'ingresos.crear' => 'Crear ingresos',
            'ingresos.listar' => 'Listar ingresos',
            'ingresos.ver' => 'Ver ingresos',
            'ingresos.editar' => 'Editar ingresos',
            'ingresos.eliminar' => 'Eliminar ingresos',
            'ingresos.reporte' => 'Generar reporte de ingresos',
            'ingresos.importar_ingresos' => 'Importar Ingresos',
 
            // 👥 Clientes
            'clientes.listar' => 'Listar clientes',
            'clientes.ver' => 'Ver clientes',
            'clientes.crear' => 'Crear clientes',
            'clientes.editar' => 'Editar clientes',
            'clientes.eliminar' => 'Eliminar clientes',

            // 🛒 Ventas
            'ventas.crear' => 'Crear ventas',
            'ventas.listar' => 'Listar ventas',
            'ventas.ver' => 'Ver ventas',
            'ventas.editar' => 'Editar ventas',
            'ventas.eliminar' => 'Eliminar ventas',
            'ventas.reporte' => 'Generar reporte de ventas',

            // 👥 Usuarios
            'usuarios.listar' => 'Listar usuarios',
            'usuarios.ver' => 'Ver usuarios',
            'usuarios.crear' => 'Crear usuarios',
            'usuarios.editar' => 'Editar usuarios',
            'usuarios.eliminar' => 'Eliminar usuarios',
 
            // 🔐 Roles
            'roles.listar' => 'Listar roles',
            'roles.ver' => 'Ver roles',
            'roles.crear' => 'Ver roles',
            'roles.verUsuarios' => 'Ver Usuarios',
            'roles.editar' => 'Editar roles',
            'roles.eliminar' => 'Eliminar roles',
        ];

        
        foreach ($permissions as $key => $label) {
            Permission::firstOrCreate(['name' => $key], ['label' => $label]);
        }
    }
}
