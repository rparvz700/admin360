<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('security_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained('agreements')->onDelete('cascade');
            $table->decimal('security_deposit', 14, 2)->nullable();
            $table->string('settled_method')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('security_deposits');
    }
};
