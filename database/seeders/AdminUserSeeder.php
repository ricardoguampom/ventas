<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ✅ Crear el rol 'Administrador' si no existe
        $role = Role::firstOrCreate(['name' => 'administrador']);

        // ✅ Asignar todos los permisos existentes
        $permissionIds = Permission::pluck('id')->all();
        $role->permissions()->sync($permissionIds);

        // ✅ Crear el usuario administrador
        $user = User::updateOrCreate(
            ['email' => 'administrador@gmail.com'],
            [
                'name' => 'Administrador del Sistema',
                'password' => Hash::make('admin1234'),
                'role_id' => $role->id,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
