<?php

namespace App\Services;

use App\Models\DataDictionary;
use App\Models\DataDictionaryJoin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DataDictionarySyncService
{
    /**
     * Tables excluded from the data dictionary.
     */
    protected array $ignoredTables = [
        'migrations',
        'password_reset_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'personal_access_tokens',
        'permissions',
        'roles',
        'model_has_permissions',
        'model_has_roles',
        'role_has_permissions',
        'activity_log',
        'table_settings',
        'log_field_mappings',
        'data_dictionaries',
        'data_dictionary_joins',
        'report_templates',
    ];

    /**
     * Module categorization map by table.
     */
    protected array $moduleMap = [
        'properties_building'               => 'Facilities Management',
        'properties_floors'                 => 'Facilities Management',
        'agreements'                        => 'Facilities Management',
        'owners'                            => 'Facilities Management',
        'rent_base'                         => 'Facilities Management',
        'rent_components'                   => 'Facilities Management',
        'rent_increments'                   => 'Facilities Management',
        'security_deposits'                 => 'Facilities Management',
        'agreement_utilities'               => 'Facilities Management',
        'utility_types'                     => 'Facilities Management',
        'payments'                          => 'Facilities Management',
        'npv_agreement_summaries'           => 'Facilities Management',
        'finance_settings'                  => 'Facilities Management',
        'locations'                         => 'Facilities Management',

        'electricity_bills'                 => 'Electricity Management',
        'electricity_meters'                => 'Electricity Management',
        'electricity_meter_nocs'            => 'Electricity Management',
        'electricity_meter_floors'          => 'Electricity Management',
        'rios'                              => 'Electricity Management',
        'rio_user'                          => 'Electricity Management',

        'vehicles'                          => 'Vehicle Management',
        'vehicle_types'                     => 'Vehicle Management',
        'vehicle_services'                  => 'Vehicle Management',
        'vehicle_fuel_logs'                 => 'Vehicle Management',
        'vehicle_documents'                 => 'Vehicle Management',
        'vehicle_document_categories'       => 'Vehicle Management',
        'vehicle_document_attributes'       => 'Vehicle Management',
        'vehicle_document_attribute_values' => 'Vehicle Management',
        'drivers'                           => 'Vehicle Management',
        'driver_availability'               => 'Vehicle Management',
        'vehicle_assignments'               => 'Vehicle Management',
        'tickets'                           => 'Vehicle Management',
        'ticket_updates'                    => 'Vehicle Management',
        'ticket_attachments'                => 'Vehicle Management',

        'vehicle_maintenances'              => 'Vehicle Maintenance',
        'vehicle_operational_logs'          => 'Vehicle Maintenance',
        'vehicle_parts'                     => 'Vehicle Maintenance',
        'vehicle_maintenance_parts'         => 'Vehicle Maintenance',

        'invoices'                          => 'Invoice Management',
        'rent_invoices'                     => 'Invoice Management',
        'vat_taxes'                         => 'Invoice Management',
        'vendors'                           => 'Invoice Management',
        'vendor_categories'                 => 'Invoice Management',
        'vendor_category_vendor'            => 'Invoice Management',

        'assets'                            => 'Asset Management',
        'asset_categories'                  => 'Asset Management',
        'asset_attributes'                  => 'Asset Management',
        'asset_attribute_values'            => 'Asset Management',
        'projects'                          => 'Asset Management',

        'generic_documents'                 => 'Document Management',
        'generic_document_categories'       => 'Document Management',
        'generic_document_attributes'       => 'Document Management',
        'generic_document_attribute_values' => 'Document Management',

        'users'                             => 'Administration',
        'departments'                       => 'Administration',
        'subcenters'                        => 'Administration',
    ];

    /**
     * Columns that should be hidden from reports by default.
     */
    protected array $sensitiveColumns = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'deleted_at',
        'email_verified_at',
        'access_token',
        'api_token',
    ];

    /**
     * Primary entry point: Introspects DB schema and synchronizes data dictionary.
     *
     * @return array Summary of sync actions
     */
    public function sync(): array
    {
        if (!Schema::hasTable('data_dictionaries') || !Schema::hasTable('data_dictionary_joins')) {
            return ['status' => 'skipped', 'message' => 'Data dictionary tables do not exist yet.'];
        }

        $tables = $this->getDatabaseTables();
        $createdColumns = 0;
        $updatedColumns = 0;
        $existingColumns = 0;
        $createdJoins = 0;

        foreach ($tables as $table) {
            if (in_array($table, $this->ignoredTables, true)) {
                continue;
            }

            $columns = $this->getTableColumns($table);
            $order = 1;

            foreach ($columns as $column) {
                $colName = $column['name'];
                $dbType  = strtolower($column['type_name'] ?? $column['type'] ?? 'string');
                $logicalType = $this->mapDataType($dbType);

                // CHECKING POINT: Does table + column already exist?
                $existing = DataDictionary::where('table_name', $table)
                    ->where('column_name', $colName)
                    ->first();

                if ($existing) {
                    // Entry already exists: Do NOT overwrite user customizations!
                    // Only update data_type if the underlying column type changed.
                    if ($existing->data_type !== $logicalType) {
                        $existing->update(['data_type' => $logicalType]);
                        $updatedColumns++;
                    } else {
                        $existingColumns++;
                    }
                } else {
                    // New column or new table! Auto-create with intelligent defaults
                    $displayName = $this->generateDisplayName($colName);
                    $moduleName  = $this->resolveModuleName($table);
                    $isReportable = !in_array($colName, $this->sensitiveColumns, true);
                    $isFilterable = !in_array($logicalType, ['text', 'json'], true) && $isReportable;
                    $isAggregatable = in_array($logicalType, ['decimal', 'integer'], true)
                        && $colName !== 'id'
                        && !Str::endsWith($colName, '_id');

                    DataDictionary::create([
                        'table_name'      => $table,
                        'column_name'     => $colName,
                        'display_name'    => $displayName,
                        'description'     => "Field {$displayName} from {$table}",
                        'data_type'       => $logicalType,
                        'module_name'     => $moduleName,
                        'is_reportable'   => $isReportable,
                        'is_filterable'   => $isFilterable,
                        'is_aggregatable' => $isAggregatable,
                        'sort_order'      => $order++,
                    ]);
                    $createdColumns++;
                }
            }

            // Auto-detect foreign keys for this table and register joins
            $createdJoins += $this->syncForeignKeysForTable($table);
        }

        // Also ensure known core domain joins are seeded/registered
        $createdJoins += $this->syncCoreDomainJoins();

        Log::info("Data Dictionary Synchronized: {$createdColumns} new columns, {$updatedColumns} updated, {$existingColumns} preserved, {$createdJoins} new joins.");

        return [
            'status'           => 'success',
            'created_columns'  => $createdColumns,
            'updated_columns'  => $updatedColumns,
            'existing_columns' => $existingColumns,
            'created_joins'    => $createdJoins,
        ];
    }

    /**
     * Get all tables in the database schema.
     */
    protected function getDatabaseTables(): array
    {
        try {
            if (method_exists(Schema::class, 'getTables')) {
                $tables = Schema::getTables();
                return array_map(function ($t) {
                    return is_array($t) ? $t['name'] : (is_object($t) ? $t->name : (string)$t);
                }, $tables);
            }
        } catch (\Throwable $e) {
            // Fallback for custom driver handling
        }

        // Direct database dialect fallback
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            $results = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'");
            return array_map(fn($r) => $r->table_name, $results);
        }

        if ($driver === 'mysql') {
            $results = DB::select("SHOW TABLES");
            $key = 'Tables_in_' . DB::getDatabaseName();
            return array_map(fn($r) => $r->$key, $results);
        }

        if ($driver === 'sqlite') {
            $results = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            return array_map(fn($r) => $r->name, $results);
        }

        return [];
    }

    /**
     * Get columns for a given table.
     */
    protected function getTableColumns(string $table): array
    {
        try {
            if (method_exists(Schema::class, 'getColumns')) {
                return Schema::getColumns($table);
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            $cols = DB::select("
                SELECT column_name as name, data_type as type_name
                FROM information_schema.columns
                WHERE table_schema = 'public' AND table_name = ?
                ORDER BY ordinal_position
            ", [$table]);

            return array_map(fn($c) => ['name' => $c->name, 'type_name' => $c->type_name], $cols);
        }

        // Standard Laravel fallback
        $names = Schema::getColumnListing($table);
        return array_map(function ($name) use ($table) {
            return [
                'name'      => $name,
                'type_name' => Schema::getColumnType($table, $name),
            ];
        }, $names);
    }

    /**
     * Map database type to normalized logical data type.
     */
    protected function mapDataType(string $dbType): string
    {
        $dbType = strtolower($dbType);

        if (Str::contains($dbType, ['int', 'smallint', 'bigint', 'serial'])) {
            return 'integer';
        }
        if (Str::contains($dbType, ['decimal', 'numeric', 'float', 'double', 'real'])) {
            return 'decimal';
        }
        if (Str::contains($dbType, ['date']) && !Str::contains($dbType, ['time'])) {
            return 'date';
        }
        if (Str::contains($dbType, ['timestamp', 'time', 'datetime'])) {
            return 'datetime';
        }
        if (Str::contains($dbType, ['bool'])) {
            return 'boolean';
        }
        if (Str::contains($dbType, ['json'])) {
            return 'json';
        }
        if (Str::contains($dbType, ['text'])) {
            return 'text';
        }

        return 'string';
    }

    /**
     * Generate human-readable label from column name.
     */
    protected function generateDisplayName(string $columnName): string
    {
        // Special abbreviations
        $specialMap = [
            'id'             => 'ID',
            'vat'            => 'VAT',
            'tax'            => 'Tax',
            'npv'            => 'NPV',
            'rio_id'         => 'RIO',
            'sft'            => 'Sq. Ft.',
            'dg_space_sft'   => 'DG Space (Sq. Ft.)',
            'store_space_sft'=> 'Store Space (Sq. Ft.)',
            'floor_area_sft' => 'Floor Area (Sq. Ft.)',
            'created_at'     => 'Created Date',
            'updated_at'     => 'Last Modified Date',
        ];

        if (isset($specialMap[$columnName])) {
            return $specialMap[$columnName];
        }

        // If ends with _id, remove _id and title-case
        if (Str::endsWith($columnName, '_id') && strlen($columnName) > 3) {
            $base = substr($columnName, 0, -3);
            return Str::headline($base) . ' ID';
        }

        return Str::headline($columnName);
    }

    /**
     * Resolve module name for a table.
     */
    protected function resolveModuleName(string $table): string
    {
        if (isset($this->moduleMap[$table])) {
            return $this->moduleMap[$table];
        }

        // Prefix heuristics
        if (Str::startsWith($table, ['properties_', 'rent_', 'agreement'])) {
            return 'Facilities Management';
        }
        if (Str::startsWith($table, ['electricity_'])) {
            return 'Electricity Management';
        }
        if (Str::startsWith($table, ['vehicle_', 'driver'])) {
            return 'Vehicle Management';
        }
        if (Str::startsWith($table, ['asset_'])) {
            return 'Asset Management';
        }
        if (Str::startsWith($table, ['generic_document'])) {
            return 'Document Management';
        }
        if (Str::startsWith($table, ['ticket'])) {
            return 'Ticket Management';
        }

        return Str::headline($table);
    }

    /**
     * Auto-detect foreign keys for a table and register joins.
     */
    protected function syncForeignKeysForTable(string $table): int
    {
        $newJoins = 0;

        try {
            if (method_exists(Schema::class, 'getForeignKeys')) {
                $fks = Schema::getForeignKeys($table);
                foreach ($fks as $fk) {
                    $fromCol = $fk['columns'][0] ?? null;
                    $toTable = $fk['foreign_table'] ?? null;
                    $toCol   = $fk['foreign_columns'][0] ?? 'id';

                    if ($fromCol && $toTable && !in_array($toTable, $this->ignoredTables, true)) {
                        $exists = DataDictionaryJoin::where('from_table', $table)
                            ->where('to_table', $toTable)
                            ->where('from_column', $fromCol)
                            ->exists();

                        if (!$exists) {
                            DataDictionaryJoin::create([
                                'from_table'   => $table,
                                'to_table'     => $toTable,
                                'from_column'  => $fromCol,
                                'to_column'    => $toCol,
                                'join_type'    => 'LEFT',
                                'description'  => "{$table}.{$fromCol} -> {$toTable}.{$toCol}",
                                'priority'     => 1,
                            ]);
                            $newJoins++;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently continue if FK inspection is unsupported
        }

        return $newJoins;
    }

    /**
     * Seed or sync core domain joins that define the Admin360 multi-hub graph.
     */
    public function syncCoreDomainJoins(): int
    {
        $coreJoins = [
            // Facilities Management
            ['properties_floors', 'properties_building', 'building_id', 'id', 'LEFT', 'Floor -> Building', 0],
            ['properties_floors', 'agreements', 'agreement_id', 'id', 'LEFT', 'Floor -> Agreement', 0],
            ['properties_floors', 'owners', 'owner_id', 'id', 'LEFT', 'Floor -> Owner', 0],
            ['properties_floors', 'projects', 'project_id', 'id', 'LEFT', 'Floor -> Project', 1],
            ['agreements', 'vendors', 'vendor_id', 'id', 'LEFT', 'Agreement -> Vendor (Landlord)', 0],
            ['rent_base', 'agreements', 'agreement_id', 'id', 'LEFT', 'Rent Base -> Agreement', 0],
            ['rent_base', 'invoices', 'invoice_id', 'id', 'LEFT', 'Rent Base -> Invoice', 1],
            ['rent_components', 'rent_base', 'rent_base_id', 'id', 'LEFT', 'Rent Component -> Rent Base', 0],
            ['rent_increments', 'agreements', 'agreement_id', 'id', 'LEFT', 'Rent Increment -> Agreement', 0],
            ['security_deposits', 'agreements', 'agreement_id', 'id', 'LEFT', 'Security Deposit -> Agreement', 0],
            ['agreement_utilities', 'agreements', 'agreement_id', 'id', 'LEFT', 'Agreement Utility -> Agreement', 0],
            ['agreement_utilities', 'utility_types', 'utility_type_id', 'id', 'LEFT', 'Utility -> Utility Type', 0],
            ['payments', 'agreements', 'agreement_id', 'id', 'LEFT', 'Payment -> Agreement', 0],
            ['properties_building', 'rios', 'rio_id', 'id', 'LEFT', 'Building -> RIO', 0],

            // Electricity Management
            ['electricity_meters', 'properties_building', 'building_id', 'id', 'LEFT', 'Meter -> Building', 0],
            ['electricity_bills', 'electricity_meters', 'meter_id', 'id', 'LEFT', 'Bill -> Meter', 0],
            ['electricity_bills', 'properties_building', 'building_id', 'id', 'LEFT', 'Bill -> Building', 0],
            ['electricity_bills', 'rios', 'rio_id', 'id', 'LEFT', 'Bill -> RIO', 1],
            ['electricity_meter_nocs', 'electricity_meters', 'electricity_meter_id', 'id', 'LEFT', 'Meter NOC -> Meter', 0],

            // Vehicle Management & Maintenance
            ['vehicles', 'vehicle_types', 'vehicle_type_id', 'id', 'LEFT', 'Vehicle -> Vehicle Type', 0],
            ['vehicle_maintenances', 'vehicles', 'vehicle_id', 'id', 'LEFT', 'Maintenance -> Vehicle', 0],
            ['vehicle_maintenances', 'vendors', 'vendor_id', 'id', 'LEFT', 'Maintenance -> Vendor (Workshop)', 0],
            ['vehicle_maintenances', 'invoices', 'invoice_id', 'id', 'LEFT', 'Maintenance -> Invoice', 1],
            ['vehicle_maintenance_parts', 'vehicle_maintenances', 'vehicle_maintenance_id', 'id', 'LEFT', 'Part -> Maintenance', 0],
            ['vehicle_maintenance_parts', 'vehicle_parts', 'vehicle_part_id', 'id', 'LEFT', 'Part -> Vehicle Part Catalog', 0],
            ['vehicle_assignments', 'vehicles', 'vehicle_id', 'id', 'LEFT', 'Assignment -> Vehicle', 0],
            ['vehicle_assignments', 'drivers', 'driver_id', 'id', 'LEFT', 'Assignment -> Driver', 0],
            ['vehicle_assignments', 'tickets', 'ticket_id', 'id', 'LEFT', 'Assignment -> Ticket', 0],
            ['vehicle_operational_logs', 'vehicles', 'vehicle_id', 'id', 'LEFT', 'Operational Log -> Vehicle', 0],
            ['vehicle_fuel_logs', 'vehicles', 'vehicle_id', 'id', 'LEFT', 'Fuel Log -> Vehicle', 0],
            ['vehicle_services', 'vehicles', 'vehicle_id', 'id', 'LEFT', 'Service -> Vehicle', 0],
            ['tickets', 'users', 'user_id', 'id', 'LEFT', 'Ticket -> Requester User', 0],

            // Invoices & Vendors
            ['invoices', 'vendors', 'vendor_id', 'id', 'LEFT', 'Invoice -> Vendor', 0],
            ['rent_invoices', 'rent_base', 'rent_base_id', 'id', 'LEFT', 'Rent Invoice -> Rent Base', 0],
            ['rent_invoices', 'invoices', 'invoice_id', 'id', 'LEFT', 'Rent Invoice -> Invoice', 0],

            // Assets
            ['assets', 'asset_categories', 'asset_category_id', 'id', 'LEFT', 'Asset -> Category', 0],
            ['assets', 'projects', 'project_id', 'id', 'LEFT', 'Asset -> Project', 0],
            ['assets', 'properties_floors', 'floor_id', 'id', 'LEFT', 'Asset -> Floor', 1],
            ['asset_attribute_values', 'assets', 'asset_id', 'id', 'LEFT', 'Attribute Value -> Asset', 0],
            ['asset_attribute_values', 'asset_attributes', 'asset_attribute_id', 'id', 'LEFT', 'Attribute Value -> Attribute', 0],

            // Administration
            ['users', 'departments', 'department_id', 'id', 'LEFT', 'User -> Department', 0],
            ['users', 'subcenters', 'subcenter_id', 'id', 'LEFT', 'User -> Subcenter', 0],
        ];

        $created = 0;
        foreach ($coreJoins as $join) {
            $exists = DataDictionaryJoin::where('from_table', $join[0])
                ->where('to_table', $join[1])
                ->where('from_column', $join[2])
                ->exists();

            if (!$exists) {
                // Verify both tables actually exist in schema before inserting
                if (Schema::hasTable($join[0]) && Schema::hasTable($join[1])) {
                    DataDictionaryJoin::create([
                        'from_table'  => $join[0],
                        'to_table'    => $join[1],
                        'from_column' => $join[2],
                        'to_column'   => $join[3],
                        'join_type'   => $join[4],
                        'description' => $join[5],
                        'priority'    => $join[6],
                    ]);
                    $created++;
                }
            }
        }

        return $created;
    }
}
