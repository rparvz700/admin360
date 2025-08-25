<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->string('owner_name');
            $table->string('vendor_code')->nullable();
            $table->string('account_title')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_no')->nullable();
            $table->string('routing_no')->nullable();
            $table->string('mobile_number', 20)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('owners');
    }
};
