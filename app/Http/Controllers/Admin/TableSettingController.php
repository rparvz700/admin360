<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TableSetting;
use Illuminate\Http\Request;

class TableSettingController extends Controller
{
    public function save(Request $request)
    {
        // Add auth check here to ensure only admin can hit this
        if (!auth()->user()->hasRole('Super Admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        TableSetting::updateOrCreate(
            ['table_identifier' => $request->table_identifier],
            ['settings' => $request->settings]
        );

        return response()->json(['success' => true]);
    }
}
