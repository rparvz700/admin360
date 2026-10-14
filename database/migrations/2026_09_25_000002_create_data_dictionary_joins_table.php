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
        Schema::create('data_dictionary_joins', function (Blueprint $table) {
            $table->id();
            $table->string('from_table', 255);
            $table->string('to_table', 255);
            $table->string('from_column', 255);
            $table->string('to_column', 255)->default('id');
            $table->string('join_type', 20)->default('LEFT'); // LEFT, INNER
            $table->string('description', 255)->nullable();
            $table->integer('priority')->default(0); // Lower or higher priority for path selection
            $table->timestamps();

            $table->unique(['from_table', 'to_table', 'from_column']);
            $table->index('from_table');
            $table->index('to_table');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_dictionary_joins');
    }
};
