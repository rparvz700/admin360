<?php

namespace App\Http\Controllers\VehicleManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;

class DriverController extends Controller
{
    /**
     * Fetch drivers from external HR API and insert into drivers table.
     */
    public function importFromApi()
    {
        $client = new \GuzzleHttp\Client();
        $url = 'https://hrdb.summitcommunications.net/api/employees?company=SCOMM_EZONE';
        $token = 'de8a8e4f962dc19d2c6c48bed6606e4ab2f1ef365f950bf6e3f4846a5ef4dfdc';

        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
                'timeout' => 30,
                'verify' => false, // Disable SSL verification for local dev
            ]);
            $data = json_decode($response->getBody(), true);
            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $item) {
                    if (!isset($item['employee']) || empty($item['employee']['hr_id'])) {
                        continue; // skip if no employee or missing hr_id
                    }
                    $emp = $item['employee'];
                    // Convert invalid dates to null
                    $dateFields = ['date_of_birth', 'joining_date', 'confirmation_date', 'contract_end_date'];
                    foreach ($dateFields as $field) {
                        if (isset($emp[$field]) && ($emp[$field] === '0000-00-00' || $emp[$field] === '' || $emp[$field] === null)) {
                            $emp[$field] = null;
                        }
                    }
                    \App\Models\Driver::firstOrCreate(
                        [
                            'hr_id' => $emp['hr_id'],
                        ],
                        [
                            'name' => $emp['name'] ?? null,
                            'sur_name' => $emp['sur_name'] ?? null,
                            'email' => $emp['email'] ?? null,
                            'phone' => $emp['phone'] ?? null,
                            'gender' => $emp['gender'] ?? null,
                            'blood_group' => $emp['blood_group'] ?? null,
                            'marital_status' => $emp['marital_status'] ?? null,
                            'date_of_birth' => $emp['date_of_birth'] ?? null,
                            'joining_date' => $emp['joining_date'] ?? null,
                            'employment_contract' => $emp['employment_contract'] ?? null,
                            'contract_renewed' => $emp['contract_renewed'] ?? null,
                            'confirmation_date' => $emp['confirmation_date'] ?? null,
                            'contract_end_date' => $emp['contract_end_date'] ?? null,
                            'passport_no' => $emp['passport_no'] ?? null,
                            'designation' => $emp['designation'] ?? null,
                            'department' => $emp['department'] ?? null,
                            'division' => $emp['division'] ?? null,
                            'office_location' => $emp['office_location'] ?? null,
                            'subcenter' => $emp['subcenter'] ?? null,
                            'job_location' => $emp['job_location'] ?? null,
                            'supervisor_name' => $emp['supervisor_name'] ?? null,
                            'supervisor_email' => $emp['supervisor_email'] ?? null,
                            'supervisor_hr_id' => $emp['supervisor_hr_id'] ?? null,
                            'supervisor_company' => $emp['supervisor_company'] ?? null,
                            'bill_reviewer_name' => $emp['bill_reviewer_name'] ?? null,
                            'bill_reviewer_email' => $emp['bill_reviewer_email'] ?? null,
                            'bill_reviewer_hr_id' => $emp['bill_reviewer_hr_id'] ?? null,
                            'bill_reviewer_company' => $emp['bill_reviewer_company'] ?? null,
                        ]
                    );
                }
                return response()->json(['status' => 'success', 'message' => 'Drivers imported successfully.']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'No data found in API response.'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function index()
    {
        $drivers = Driver::all();
        return view('VehicleManagement.Drivers.index', compact('drivers'));
    }

    public function create()
    {
        return view('VehicleManagement.Drivers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hr_id' => 'required|unique:drivers',
            'name' => 'required',
            'email' => 'nullable|email',
            'phone' => 'nullable',
            // ... add other validation rules as needed ...
        ]);
        Driver::create($validated + $request->except(['_token']));
        return redirect()->route('drivers.index')->with('success', 'Driver created successfully.');
    }

    public function show($id)
    {
        $driver = Driver::findOrFail($id);
        return view('VehicleManagement.Drivers.show', compact('driver'));
    }

    public function edit($id)
    {
        $driver = Driver::findOrFail($id);
        return view('VehicleManagement.Drivers.edit', compact('driver'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'hr_id' => 'required|unique:drivers,hr_id,' . $id,
            'name' => 'required',
            'email' => 'nullable|email',
            'phone' => 'nullable',
            // ... add other validation rules as needed ...
        ]);
        $driver = Driver::findOrFail($id);
        $driver->update($validated + $request->except(['_token', '_method']));
        return redirect()->route('drivers.index')->with('success', 'Driver updated successfully.');
    }

    public function destroy($id)
    {
        $driver = Driver::findOrFail($id);
        $driver->delete();
        return redirect()->route('drivers.index')->with('success', 'Driver deleted successfully.');
    }
    public function list(Request $request)
    {
        $query = Driver::query();

        // DataTables server-side processing
        $draw = $request->get('draw');
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->input('search.value');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhere('hr_id', 'like', "%$search%")
                  ->orWhere('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%")
                ;
            });
        }

        $total = Driver::count();
        $filtered = $query->count();
        $drivers = $query->orderBy('id', 'desc')->skip($start)->take($length)->get();

        $data = [];
        foreach ($drivers as $driver) {
            $data[] = [
                'id' => $driver->id,
                'hr_id' => $driver->hr_id,
                'name' => $driver->name,
                'email' => $driver->email,
                'phone' => $driver->phone,
                'actions' => view('VehicleManagement.Drivers.partials.actions', compact('driver'))->render(),
            ];
        }

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }
}
