<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->string('agreement_ref_no')->unique();
            $table->date('agreement_date')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->string('status', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('agreements');
    }
};
