<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceSetting;
use Illuminate\Http\Request;

class FinanceSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Auto-seed default setting if missing
        if (FinanceSetting::where('key', 'npv_annual_discount_rate')->count() === 0) {
            $seeder = new \Database\Seeders\FinanceSettingSeeder();
            $seeder->run();
        }

        $settings = FinanceSetting::where('key', 'npv_annual_discount_rate')->get();
        return view('Admin.FinanceSettings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.id' => 'required|exists:finance_settings,id',
            'settings.*.value_numeric' => 'required|numeric|min:0|max:100',
        ]);

        foreach ($request->input('settings', []) as $settingData) {
            $setting = FinanceSetting::find($settingData['id']);
            if ($setting && $setting->key === 'npv_annual_discount_rate') {
                $setting->update([
                    'value_numeric' => (float) $settingData['value_numeric'],
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->route('admin.finance-settings.index')->with('success', 'NPV Annual Discount Rate updated successfully.');
    }
}
