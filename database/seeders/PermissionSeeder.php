<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // 'create-role',
            // 'edit-role',
            // 'delete-role',
            // 'create-user',
            // 'edit-user',
            // 'delete-user',
            // 'create-link',
            // 'edit-link',
            // 'delete-link',
            // 'create-department',
            // 'edit-department',
            // 'delete-department',
            // 'create-subcenter',
            // 'edit-subcenter',
            // 'delete-subcenter'
            // 'create-building',
            // 'edit-building',
            // 'delete-building',

            // 'create-floor',
            // 'edit-floor',
            // 'delete-floor',

            // 'create-agreement',
            // 'edit-agreement',
            // 'delete-agreement',

            // 'create-rent',
            // 'edit-rent',
            // 'delete-rent'

            // 'asset-management',
            // 'vehicle-management',
            // 'vehicle-maintenance-management',
            // 'document-management',
            // 'ticket-management',
            // 'user-ticket-management',
            // 'admin-ticket-management',
            // 'invoice-management',
            // 'property-wizard'
         ];

         foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
          }
    }
}
