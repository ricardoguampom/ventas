<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert roles
        DB::table('roles')->insert([
            ['name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'seller', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'viewer', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Insert permissions
        DB::table('permissions')->insert([
            ['name' => 'manage_users', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manage_articles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'view_reports', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Assign permissions to roles
        DB::table('role_has_permissions')->insert([
            ['role_id' => 1, 'permission_id' => 1], // admin - manage_users
            ['role_id' => 1, 'permission_id' => 2], // admin - manage_articles
            ['role_id' => 1, 'permission_id' => 3], // admin - view_reports
            ['role_id' => 2, 'permission_id' => 2], // seller - manage_articles
            ['role_id' => 2, 'permission_id' => 3], // seller - view_reports
        ]);
    }
}
