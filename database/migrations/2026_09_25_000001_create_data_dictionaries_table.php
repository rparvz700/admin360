<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('data_dictionaries', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 255);
            $table->string('column_name', 255);
            $table->string('display_name', 255);
            $table->text('description')->nullable();
            $table->string('data_type', 50)->default('string'); // string, integer, decimal, date, datetime, boolean, text, json
            $table->string('module_name', 100)->default('General');
            $table->boolean('is_reportable')->default(true);
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_aggregatable')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['table_name', 'column_name']);
            $table->index('table_name');
            $table->index('module_name');
            $table->index(['table_name', 'is_reportable']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_dictionaries');
    }
};
