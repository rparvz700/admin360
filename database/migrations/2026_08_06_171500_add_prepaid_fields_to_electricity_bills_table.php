<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('electricity_bills', function (Blueprint $table) {
            $table->decimal('last_recharge_amount', 14, 2)->nullable();
            $table->date('last_recharge_date')->nullable();
            $table->decimal('balance_after_last_recharge', 14, 2)->nullable();
            $table->decimal('last_balance', 14, 2)->nullable();
            $table->decimal('recharge_amount', 14, 2)->nullable();
            $table->decimal('current_balance', 14, 2)->nullable();
            $table->decimal('per_day_consumption', 14, 2)->nullable();
            $table->date('recharge_date')->nullable();
            $table->boolean('is_consumption_edited')->default(false);
            $table->text('consumption_edit_remarks')->nullable();
            $table->string('consumption_edit_attachment', 255)->nullable();
        });
    }

    public function down()
    {
        Schema::table('electricity_bills', function (Blueprint $table) {
            $columns = [
                'last_recharge_amount',
                'last_recharge_date',
                'balance_after_last_recharge',
                'last_balance',
                'recharge_amount',
                'current_balance',
                'per_day_consumption',
                'recharge_date',
                'is_consumption_edited',
                'consumption_edit_remarks',
                'consumption_edit_attachment',
            ];
            
            $existingColumns = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('electricity_bills', $column)) {
                    $existingColumns[] = $column;
                }
            }
            
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
