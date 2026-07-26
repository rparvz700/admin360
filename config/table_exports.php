<?php

use App\Models\Agreement;
use App\Models\Asset;
use App\Models\AssetAttribute;
use App\Models\AssetCategory;
use App\Models\Driver;
use App\Models\GenericDocument;
use App\Models\GenericDocumentAttribute;
use App\Models\GenericDocumentCategory;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\PropertiesBuilding;
use App\Models\PropertiesFloor;
use App\Models\RentBase;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\VehicleOperationalLog;
use App\Models\VehiclePart;
use App\Models\VehicleType;
use App\Models\Vendor;

return [
    /*
    |--------------------------------------------------------------------------
    | DataTable Excel Exports
    |--------------------------------------------------------------------------
    |
    | Each key is intentionally whitelisted. The export uses a query-based
    | Maatwebsite export with chunk reading, so large lists are not loaded into
    | memory at once.
    |
    */

    'chunk_size' => 1000,

    'tables' => [
        'users' => [
            'model' => User::class,
            'file_name' => 'users',
            'relations' => ['roles'],
            'search' => ['name', 'email'],
            'columns' => [
                'name' => 'Name',
                'email' => 'Email',
                'roles.name' => 'Role',
                'status' => 'Status',
                'created_at' => 'Created At',
            ],
        ],
        'buildings' => [
            'model' => PropertiesBuilding::class,
            'file_name' => 'buildings',
            'search' => ['code', 'site_name', 'division', 'district', 'upazila', 'area'],
            'columns' => [
                'code' => 'Code',
                'site_name' => 'Name',
                'country' => 'Country',
                'division' => 'Division',
                'district' => 'District',
                'upazila' => 'Upazila',
                'area' => 'Area',
                'address' => 'Address',
                'created_at' => 'Created At',
            ],
        ],
        'floors' => [
            'model' => PropertiesFloor::class,
            'file_name' => 'floors',
            'relations' => ['building', 'agreement'],
            'search' => ['floor_label', 'floor_number', 'usage_type'],
            'columns' => [
                'building.site_name' => 'Building',
                'floor_label' => 'Floor Label',
                'floor_number' => 'Floor Number',
                'usage_type' => 'Usage Type',
                'size_sqft' => 'Size Sqft',
                'agreement.agreement_ref_no' => 'Agreement',
                'created_at' => 'Created At',
            ],
        ],
        'agreements' => [
            'model' => Agreement::class,
            'file_name' => 'agreements',
            'search' => ['agreement_ref_no', 'status', 'remarks'],
            'columns' => [
                'agreement_ref_no' => 'Reference No',
                'agreement_date' => 'Agreement Date',
                'from_date' => 'From Date',
                'to_date' => 'To Date',
                'status' => 'Status',
                'remarks' => 'Remarks',
                'created_at' => 'Created At',
            ],
        ],
        'rent' => [
            'model' => RentBase::class,
            'file_name' => 'rent',
            'relations' => ['agreement'],
            'search' => ['rent_type'],
            'columns' => [
                'agreement.agreement_ref_no' => 'Agreement',
                'base_rent' => 'Base Rent',
                'vat' => 'VAT',
                'tax' => 'Tax',
                'is_at_source' => 'At Source',
                'rent_type' => 'Rent Type',
                'created_at' => 'Created At',
            ],
        ],
        'assets' => [
            'model' => Asset::class,
            'file_name' => 'assets',
            'relations' => ['category', 'floor.building', 'parent', 'project'],
            'search' => ['asset_tag', 'asset_name', 'brand', 'model', 'serial_number', 'status'],
            'filters' => ['category_id' => 'category.category_name'],
            'columns' => [
                'asset_tag' => 'Asset Tag',
                'asset_name' => 'Asset Name',
                'category.category_name' => 'Category',
                'project.name' => 'Project',
                'floor.building.site_name' => 'Building',
                'floor.floor_label' => 'Floor',
                'brand' => 'Brand',
                'model' => 'Model',
                'serial_number' => 'Serial Number',
                'purchase_date' => 'Purchase Date',
                'warranty_expiry' => 'Warranty Expiry',
                'status' => 'Status',
            ],
        ],
        'projects' => [
            'model' => Project::class,
            'file_name' => 'projects',
            'search' => ['name', 'description', 'status'],
            'columns' => ['name' => 'Name', 'description' => 'Description', 'status' => 'Status', 'created_at' => 'Created At'],
        ],
        'asset-categories' => [
            'model' => AssetCategory::class,
            'file_name' => 'asset_categories',
            'search' => ['category_name', 'description'],
            'columns' => ['category_name' => 'Category', 'description' => 'Description', 'created_at' => 'Created At'],
        ],
        'asset-attributes' => [
            'model' => AssetAttribute::class,
            'file_name' => 'asset_attributes',
            'relations' => ['category'],
            'search' => ['attribute_name', 'attribute_type'],
            'columns' => [
                'category.category_name' => 'Category',
                'attribute_name' => 'Attribute',
                'attribute_type' => 'Type',
                'options' => 'Options',
                'created_at' => 'Created At',
            ],
        ],
        'vehicles' => [
            'model' => Vehicle::class,
            'file_name' => 'vehicles',
            'relations' => ['vehicleType'],
            'search' => ['registration_number', 'brand', 'model', 'engine_number', 'chassis_number', 'status'],
            'columns' => [
                'registration_number' => 'Registration Number',
                'vehicleType.type_name' => 'Vehicle Type',
                'brand' => 'Brand',
                'model' => 'Model',
                'manufacture_year' => 'Manufacture Year',
                'engine_number' => 'Engine Number',
                'chassis_number' => 'Chassis Number',
                'status' => 'Status',
            ],
        ],
        'vehicle-types' => [
            'model' => VehicleType::class,
            'file_name' => 'vehicle_types',
            'search' => ['type_name', 'description'],
            'columns' => ['type_name' => 'Type', 'description' => 'Description', 'created_at' => 'Created At'],
        ],
        'drivers' => [
            'model' => Driver::class,
            'file_name' => 'drivers',
            'search' => ['hr_id', 'name', 'sur_name', 'email', 'phone', 'designation', 'department', 'subcenter'],
            'columns' => [
                'hr_id' => 'HR ID',
                'name' => 'Name',
                'sur_name' => 'Surname',
                'email' => 'Email',
                'phone' => 'Phone',
                'designation' => 'Designation',
                'department' => 'Department',
                'subcenter' => 'Subcenter',
                'joining_date' => 'Joining Date',
            ],
        ],
        'generic-documents' => [
            'model' => GenericDocument::class,
            'file_name' => 'generic_documents',
            'relations' => ['category'],
            'search' => [],
            'columns' => [
                'category.category_name' => 'Category',
                'documentable_type' => 'Documentable Type',
                'documentable_id' => 'Documentable ID',
                'issue_date' => 'Issue Date',
                'expiry_date' => 'Expiry Date',
                'file_path' => 'File',
                'created_at' => 'Created At',
            ],
        ],
        'generic-document-categories' => [
            'model' => GenericDocumentCategory::class,
            'file_name' => 'generic_document_categories',
            'search' => ['category_name', 'description'],
            'columns' => ['category_name' => 'Name', 'description' => 'Description', 'created_at' => 'Created At'],
        ],
        'generic-document-attributes' => [
            'model' => GenericDocumentAttribute::class,
            'file_name' => 'generic_document_attributes',
            'relations' => ['category'],
            'search' => ['attribute_name', 'attribute_type'],
            'columns' => [
                'category.category_name' => 'Category',
                'attribute_name' => 'Name',
                'attribute_type' => 'Type',
                'options' => 'Options',
                'created_at' => 'Created At',
            ],
        ],
        'tickets' => [
            'model' => Ticket::class,
            'file_name' => 'tickets',
            'relations' => ['user', 'assignedTo', 'vehicleType', 'asset', 'assetCategory'],
            'search' => ['ticket_number', 'title', 'ticket_type', 'priority', 'status'],
            'filters' => ['ticket_type' => 'ticket_type', 'status' => 'status'],
            'columns' => [
                'ticket_number' => 'Ticket Number',
                'ticket_type' => 'Ticket Type',
                'user.name' => 'Requester',
                'title' => 'Title',
                'priority' => 'Priority',
                'status' => 'Status',
                'assignedTo.name' => 'Assigned To',
                'created_at' => 'Created At',
            ],
        ],
        'admin-tickets' => [
            'extends' => 'tickets',
            'file_name' => 'admin_tickets',
        ],
        'invoices' => [
            'model' => Invoice::class,
            'file_name' => 'invoices',
            'relations' => ['vendor'],
            'search' => ['invoice_number', 'payment_status', 'remarks'],
            'columns' => [
                'invoice_number' => 'Invoice Number',
                'vendor.name' => 'Vendor',
                'invoice_date' => 'Invoice Date',
                'due_date' => 'Due Date',
                'subtotal' => 'Subtotal',
                'tax_amount' => 'Tax Amount',
                'discount_amount' => 'Discount Amount',
                'total_amount' => 'Total Amount',
                'payment_status' => 'Payment Status',
                'paid_amount' => 'Paid Amount',
            ],
        ],
        'maintenance-vendors' => [
            'model' => Vendor::class,
            'file_name' => 'maintenance_vendors',
            'search' => ['name', 'contact_person', 'phone', 'email'],
            'columns' => ['name' => 'Name', 'contact_person' => 'Contact Person', 'phone' => 'Phone', 'email' => 'Email', 'is_active' => 'Active'],
        ],
        'maintenance-parts' => [
            'model' => VehiclePart::class,
            'file_name' => 'vehicle_parts',
            'search' => ['part_name', 'part_code', 'category'],
            'columns' => [
                'part_name' => 'Part Name',
                'part_code' => 'Part Code',
                'category' => 'Category',
                'typical_lifespan_km' => 'Typical Lifespan KM',
                'typical_lifespan_months' => 'Typical Lifespan Months',
                'is_active' => 'Active',
            ],
        ],
        'maintenance-records' => [
            'model' => VehicleMaintenance::class,
            'file_name' => 'vehicle_maintenances',
            'relations' => ['vehicle', 'vendor'],
            'search' => ['maintenance_type', 'status', 'service_description'],
            'columns' => [
                'vehicle.registration_number' => 'Vehicle',
                'maintenance_type' => 'Type',
                'status' => 'Status',
                'start_datetime' => 'Start',
                'vendor.name' => 'Vendor',
                'total_service_cost' => 'Total Cost',
                'next_service_due_date' => 'Next Due Date',
            ],
        ],
        'maintenance-operational-logs' => [
            'model' => VehicleOperationalLog::class,
            'file_name' => 'vehicle_operational_logs',
            'relations' => ['vehicle', 'assignedUser', 'logger'],
            'search' => ['log_type', 'remarks'],
            'columns' => [
                'vehicle.registration_number' => 'Vehicle',
                'assignedUser.name' => 'Assigned User',
                'log_type' => 'Log Type',
                'logged_at' => 'Logged At',
                'meter_reading' => 'Meter Reading',
                'vehicle_status' => 'Vehicle Status',
                'logger.name' => 'Logged By',
                'remarks' => 'Remarks',
            ],
        ],
    ],

    'paths' => [
        'users' => 'users',
        'buildings' => 'buildings',
        'floors' => 'floors',
        'agreements' => 'agreements',
        'rent' => 'rent',
        'assets' => 'assets',
        'projects' => 'projects',
        'asset-categories' => 'asset-categories',
        'asset-attributes' => 'asset-attributes',
        'vehicles' => 'vehicles',
        'vehicle-types' => 'vehicle-types',
        'drivers' => 'drivers',
        'generic-documents' => 'generic-documents',
        'generic-document-categories' => 'generic-document-categories',
        'generic-document-attributes' => 'generic-document-attributes',
        'tickets' => 'tickets',
        'admin/tickets' => 'admin-tickets',
        'invoices' => 'invoices',
        'maintenance/vendors' => 'maintenance-vendors',
        'maintenance/parts' => 'maintenance-parts',
        'maintenance/maintenances' => 'maintenance-records',
        'maintenance/operational-logs' => 'maintenance-operational-logs',
    ],
];
