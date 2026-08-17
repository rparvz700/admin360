<?php

namespace Database\Seeders;

use App\Models\FinanceSetting;
use Illuminate\Database\Seeder;

class FinanceSettingSeeder extends Seeder
{
    public function run(): void
    {
        FinanceSetting::updateOrCreate(
            ['key' => 'npv_annual_discount_rate'],
            [
                'label' => 'NPV Annual Discount / Interest Rate (%)',
                'description' => 'Global default annual interest rate used for Net Present Value (NPV) calculations of property lease cash outflows.',
                'value_numeric' => 12.1600,
                'group' => 'npv',
                'is_active' => true,
            ]
        );
    }
}
