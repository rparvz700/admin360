<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE properties_floors ALTER COLUMN car_parking TYPE NUMERIC(12, 2) USING car_parking::numeric');

        Schema::create('rent_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_base_id')->constrained('rent_base')->onDelete('cascade');
            $table->string('component_type', 50);
            $table->decimal('area_sft', 12, 2)->default(0);
            $table->decimal('rate', 14, 2)->default(0);
            $table->decimal('rent_amount', 14, 2)->default(0);
            $table->boolean('vat_applicable')->default(false);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['rent_base_id', 'component_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_components');

        DB::statement('ALTER TABLE properties_floors ALTER COLUMN car_parking TYPE INTEGER USING car_parking::integer');
    }
};
