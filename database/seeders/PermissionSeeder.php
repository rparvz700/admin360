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

            // Property Management
            // 'property-wizard',
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
            // 'delete-rent',
            'create-utility-type',
            'edit-utility-type',
            'delete-utility-type',

            // Asset Management
            // 'asset-management',
            'create-asset',
            'edit-asset',
            'delete-asset',
            'create-project',
            'edit-project',
            'delete-project',
            'create-asset-category',
            'edit-asset-category',
            'delete-asset-category',
            'create-asset-attribute',
            'edit-asset-attribute',
            'delete-asset-attribute',

            // Vehicle Management
            // 'vehicle-management',
            'create-driver',
            'edit-driver',
            'delete-driver',
            'create-vehicle',
            'edit-vehicle',
            'delete-vehicle',
            'create-vehicle-type',
            'edit-vehicle-type',
            'delete-vehicle-type',

            // Vehicle Maintenance Management
            // 'vehicle-maintenance-management',
            'create-maintenance',
            'edit-maintenance',
            'delete-maintenance',
            'create-operational-log',
            'edit-operational-log',
            'delete-operational-log',
            'create-vehicle-part',
            'edit-vehicle-part',
            'delete-vehicle-part',
            'view-maintenance-report',

            // Generic Document Management
            // 'document-management',
            'create-generic-document',
            'edit-generic-document',
            'delete-generic-document',
            'create-generic-document-category',
            'edit-generic-document-category',
            'delete-generic-document-category',
            'create-generic-document-attribute',
            'edit-generic-document-attribute',
            'delete-generic-document-attribute',

            // Ticket Management
            // 'ticket-management',
            // 'user-ticket-management',
            // 'admin-ticket-management',

            // Invoice Management
            // 'invoice-management',
            'create-invoice',
            'edit-invoice',
            'delete-invoice',
            'create-vendor',
            'edit-vendor',
            'delete-vendor',
            'create-vat-tax',
            'edit-vat-tax',
            'delete-vat-tax',
         ];

         foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
         }
    }
}
