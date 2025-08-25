<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained('agreements')->onDelete('cascade');
            $table->date('maintain_from')->nullable();
            $table->boolean('meter_docs_handover')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('maintenance');
    }
};
