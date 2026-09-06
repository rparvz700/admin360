<?php

namespace App\Http\Controllers\VehicleManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:vehicle-management|create-driver|edit-driver|delete-driver', ['only' => ['index', 'show', 'list', 'importFromApi']]);
        $this->middleware('permission:create-driver', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-driver', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-driver', ['only' => ['destroy']]);
    }

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

                    $hrId = (string)($emp['hr_id'] ?? '');
                    if (strlen($hrId) > 0 && strlen($hrId) < 4) {
                        $hrId = str_pad($hrId, 4, '0', STR_PAD_LEFT);
                    }

                    \App\Models\Driver::firstOrCreate(
                        [
                            'hr_id' => $hrId,
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
        $stats = [
            'total' => Driver::count(),
            'permanent' => Driver::where(function($q) {
                $q->where('employment_contract', 'like', '%permanent%')
                  ->orWhere('employment_contract', 'like', '%regular%');
            })->count(),
            'contractual' => Driver::where('employment_contract', 'like', '%contract%')->count(),
            'with_nid' => Driver::whereNotNull('nid')->where('nid', '!=', '')->count(),
        ];

        return view('VehicleManagement.Drivers.index', compact('stats'));
    }

    public function create()
    {
        return view('VehicleManagement.Drivers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hr_id' => 'required|unique:drivers,hr_id',
            'name' => 'required|string|max:255',
            'sur_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'nid' => 'nullable|string|max:50',
            'emergency_contact' => 'nullable|string|max:50',
            'blood_group' => 'nullable|string|max:10',
            'gender' => 'nullable|string|max:20',
            'marital_status' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'nullable|date',
            'employment_contract' => 'nullable|string|max:100',
            'contract_renewed' => 'nullable|boolean',
            'confirmation_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
            'passport_no' => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'division' => 'nullable|string|max:100',
            'office_location' => 'nullable|string|max:100',
            'subcenter' => 'nullable|string|max:100',
            'job_location' => 'nullable|string|max:100',
            'supervisor_name' => 'nullable|string|max:255',
            'supervisor_email' => 'nullable|email|max:255',
            'supervisor_hr_id' => 'nullable|string|max:50',
            'supervisor_company' => 'nullable|string|max:255',
            'bill_reviewer_name' => 'nullable|string|max:255',
            'bill_reviewer_email' => 'nullable|email|max:255',
            'bill_reviewer_hr_id' => 'nullable|string|max:50',
            'bill_reviewer_company' => 'nullable|string|max:255',
        ]);

        $data = $request->except(['_token']);
        // Normalize hr_id: if length < 4 prepend leading 0
        if (!empty($data['hr_id']) && strlen(trim($data['hr_id'])) < 4) {
            $data['hr_id'] = str_pad(trim($data['hr_id']), 4, '0', STR_PAD_LEFT);
        }

        Driver::create($data);
        return redirect()->route('drivers.index')->with('success', 'Driver registered successfully.');
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
            'name' => 'required|string|max:255',
            'sur_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'nid' => 'nullable|string|max:50',
            'emergency_contact' => 'nullable|string|max:50',
            'blood_group' => 'nullable|string|max:10',
            'gender' => 'nullable|string|max:20',
            'marital_status' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date',
            'joining_date' => 'nullable|date',
            'employment_contract' => 'nullable|string|max:100',
            'contract_renewed' => 'nullable|boolean',
            'confirmation_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
            'passport_no' => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'division' => 'nullable|string|max:100',
            'office_location' => 'nullable|string|max:100',
            'subcenter' => 'nullable|string|max:100',
            'job_location' => 'nullable|string|max:100',
            'supervisor_name' => 'nullable|string|max:255',
            'supervisor_email' => 'nullable|email|max:255',
            'supervisor_hr_id' => 'nullable|string|max:50',
            'supervisor_company' => 'nullable|string|max:255',
            'bill_reviewer_name' => 'nullable|string|max:255',
            'bill_reviewer_email' => 'nullable|email|max:255',
            'bill_reviewer_hr_id' => 'nullable|string|max:50',
            'bill_reviewer_company' => 'nullable|string|max:255',
        ]);

        $driver = Driver::findOrFail($id);
        $data = $request->except(['_token', '_method']);

        if (!empty($data['hr_id']) && strlen(trim($data['hr_id'])) < 4) {
            $data['hr_id'] = str_pad(trim($data['hr_id']), 4, '0', STR_PAD_LEFT);
        }

        $driver->update($data);
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
                  ->orWhere('sur_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%")
                  ->orWhere('nid', 'like', "%$search%")
                  ->orWhere('designation', 'like', "%$search%")
                  ->orWhere('department', 'like', "%$search%")
                  ->orWhere('office_location', 'like', "%$search%")
                  ->orWhere('subcenter', 'like', "%$search%");
            });
        }

        $total = Driver::count();
        $filtered = $query->count();
        $drivers = $query->orderBy('id', 'desc')->skip($start)->take($length)->get();

        $data = [];
        foreach ($drivers as $driver) {
            $bloodBadge = $driver->blood_group 
                ? '<span class="badge bg-danger-light text-danger fw-bold"><i class="fa fa-tint me-1"></i>' . e($driver->blood_group) . '</span>'
                : '<span class="text-muted fs-xs">-</span>';

            $contractBadge = $driver->employment_contract
                ? '<span class="badge bg-primary-light text-primary">' . e($driver->employment_contract) . '</span>'
                : '<span class="text-muted fs-xs">Unspecified</span>';

            $location = e($driver->office_location ?? $driver->job_location ?? $driver->subcenter ?? 'N/A');

            $data[] = [
                'id' => $driver->id,
                'hr_id' => '<span class="badge bg-secondary font-monospace"><i class="fa fa-id-badge me-1"></i>' . e($driver->hr_id) . '</span>',
                'name' => '<div class="fw-semibold text-dark">' . e($driver->name) . ($driver->sur_name ? ' ' . e($driver->sur_name) : '') . '</div>' .
                          ($driver->designation ? '<div class="text-muted fs-xs">' . e($driver->designation) . '</div>' : ''),
                'contact' => '<div>' . ($driver->phone ? '<a href="tel:' . e($driver->phone) . '" class="text-dark fw-medium"><i class="fa fa-phone me-1 text-primary"></i>' . e($driver->phone) . '</a>' : '<span class="text-muted fs-xs">No Phone</span>') . '</div>' .
                             ($driver->email ? '<div class="fs-xs text-muted"><i class="fa fa-envelope me-1"></i>' . e($driver->email) . '</div>' : ''),
                'blood_group' => $bloodBadge,
                'employment' => $contractBadge . ($driver->joining_date ? '<div class="text-muted fs-xs mt-1"><i class="fa fa-calendar-alt me-1"></i>' . \Carbon\Carbon::parse($driver->joining_date)->format('M Y') . '</div>' : ''),
                'location' => '<span class="badge bg-light text-dark border">' . $location . '</span>',
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
