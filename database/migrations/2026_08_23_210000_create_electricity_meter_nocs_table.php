<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('electricity_meter_nocs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_id')->constrained('electricity_meters')->onDelete('cascade');
            $table->string('noc_number', 100);
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->string('issuing_authority', 100)->nullable();
            $table->string('file_path', 255);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('electricity_meter_nocs');
    }
};
