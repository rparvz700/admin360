<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create vendor_categories table
        Schema::create('vendor_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('module_scope')->default('general'); // vehicle, facilities, utility, general
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Create vendor_category_vendor pivot table
        Schema::create('vendor_category_vendor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('vendor_category_id')->constrained('vendor_categories')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['vendor_id', 'vendor_category_id']);
        });

        // 3. Add financial & metadata fields to vendors table
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('address');
            $table->string('bank_account_no')->nullable()->after('bank_name');
            $table->string('routing_number')->nullable()->after('bank_account_no');
            $table->string('tin_vat_no')->nullable()->after('routing_number');
            $table->json('metadata')->nullable()->after('rating');
        });

        // 4. Seed initial standard categories
        $now = now();
        $categories = [
            [
                'name' => 'Workshop / Repair Shop',
                'code' => 'WORKSHOP',
                'module_scope' => 'vehicle',
                'description' => 'Vehicle maintenance, servicing, and repair centers',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Spare Parts Supplier',
                'code' => 'PARTS_SUPPLIER',
                'module_scope' => 'vehicle',
                'description' => 'Automotive parts and component suppliers',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Landlord / Property Owner',
                'code' => 'LANDLORD',
                'module_scope' => 'facilities',
                'description' => 'Premises, building, and property owners/lessors',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Utility Provider',
                'code' => 'UTILITY',
                'module_scope' => 'utility',
                'description' => 'Electricity, water, gas, and telecommunication providers',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'IT & Office Supplies',
                'code' => 'IT_SUPPLIES',
                'module_scope' => 'general',
                'description' => 'IT equipment, office stationery, and software vendors',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'General Contractor',
                'code' => 'GENERAL_CONTRACTOR',
                'module_scope' => 'general',
                'description' => 'General maintenance, security, and facility service providers',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('vendor_categories')->insert($categories);

        // 5. Migrate existing vendor_type data into pivot table
        $workshopId = DB::table('vendor_categories')->where('code', 'WORKSHOP')->value('id');
        $partsId    = DB::table('vendor_categories')->where('code', 'PARTS_SUPPLIER')->value('id');

        $existingVendors = DB::table('vendors')->select('id', 'vendor_type')->get();

        foreach ($existingVendors as $vendor) {
            if ($vendor->vendor_type === 'workshop' && $workshopId) {
                DB::table('vendor_category_vendor')->insertOrIgnore([
                    'vendor_id' => $vendor->id,
                    'vendor_category_id' => $workshopId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } elseif ($vendor->vendor_type === 'parts_supplier' && $partsId) {
                DB::table('vendor_category_vendor')->insertOrIgnore([
                    'vendor_id' => $vendor->id,
                    'vendor_category_id' => $partsId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } elseif ($vendor->vendor_type === 'both') {
                if ($workshopId) {
                    DB::table('vendor_category_vendor')->insertOrIgnore([
                        'vendor_id' => $vendor->id,
                        'vendor_category_id' => $workshopId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
                if ($partsId) {
                    DB::table('vendor_category_vendor')->insertOrIgnore([
                        'vendor_id' => $vendor->id,
                        'vendor_category_id' => $partsId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_no', 'routing_number', 'tin_vat_no', 'metadata']);
        });

        Schema::dropIfExists('vendor_category_vendor');
        Schema::dropIfExists('vendor_categories');
    }
};
