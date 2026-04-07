<?php

namespace App\Http\Controllers\FacilitiesManagement;

use App\Models\PropertiesBuilding;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

use App\Http\Controllers\Controller;
use App\Models\TableSetting;
use Illuminate\Support\Facades\Log;

class BuildingsController extends Controller
{
    public function list(Request $request)
    {
        $query = PropertiesBuilding::query();
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('actions', function ($building) {
                return view('FacilitiesManagement.Buildings.partials.actions', compact('building'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
    public function index()
    {
        $buildings = PropertiesBuilding::all();
        $globalSettings = TableSetting::where('table_identifier', 'buildings_table')->first();
        $tableConfig = $globalSettings ? $globalSettings->settings : null;

        return view('FacilitiesManagement.Buildings.index', compact('buildings', 'tableConfig'));
    }

    public function create()
    {
        $upazillaUrl = config('app.baseline_base_url').'api/upazilla';
        $token = 'Authorization: Bearer '.config('app.baseline_api_token');

        $upazillaApiRes = $this->callAPI('GET', $upazillaUrl, '', $token);
        $upazillaApiResArr = json_decode($upazillaApiRes, true);
        $upazillas = $upazillaApiResArr['data'];

        $districtUrl = config('app.baseline_base_url').'api/district';
        $districtApiRes = $this->callAPI('GET', $districtUrl, '', $token);
        $districtApiResArr = json_decode($districtApiRes, true);
        $districts = $districtApiResArr['data'];

        return view('FacilitiesManagement.Buildings.create', compact('upazillas', 'districts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'site_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'upazila' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
        ]);
        $building = PropertiesBuilding::create($validated);
        return redirect()->route('buildings.index')->with('success', 'Building created successfully.');
    }

    public function show($id)
    {
        $building = PropertiesBuilding::findOrFail($id);
        return view('FacilitiesManagement.Buildings.show', compact('building'));
    }

    public function edit($id)
    {
        $building = PropertiesBuilding::findOrFail($id);

        $upazillaUrl = config('app.baseline_base_url').'api/upazilla';
        $token = 'Authorization: Bearer '.config('app.baseline_api_token');

        $upazillaApiRes = $this->callAPI('GET', $upazillaUrl, '', $token);
        $upazillaApiResArr = json_decode($upazillaApiRes, true);
        $upazillas = $upazillaApiResArr['data'];

        $districtUrl = config('app.baseline_base_url').'api/district';
        $districtApiRes = $this->callAPI('GET', $districtUrl, '', $token);
        $districtApiResArr = json_decode($districtApiRes, true);
        $districts = $districtApiResArr['data'];

        return view('FacilitiesManagement.Buildings.edit', compact('building', 'upazillas', 'districts'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'site_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'upazila' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
        ]);
        $building = PropertiesBuilding::findOrFail($id);
        $building->update($validated);
        return redirect()->route('buildings.index')->with('success', 'Building updated successfully.');
    }

    public function destroy($id)
    {
        $building = PropertiesBuilding::findOrFail($id);
        $building->delete();
        return redirect()->route('buildings.index')->with('success', 'Building deleted successfully.');
    }


    private function callAPI($method, $url, $data, $accessToken = null)
    {
        try{
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            switch ($method) {
                case "GET":
                    curl_setopt($curl, CURLOPT_POST, 0);
                    break;

                case "POST":
                    curl_setopt($curl, CURLOPT_POST, 1);
                    if ($data)
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    break;
                case "PUT":
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                    if ($data)
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    break;
                default:
                    if ($data)
                        $url = sprintf("%s?%s", $url, http_build_query($data));
            }

            // OPTIONS:
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                $accessToken
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            // EXECUTE:
            $result = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (!$result) {
                throw new \Exception("API responded with failure. HTTP Code: $httpCode. Response: " . $result);
                // die("Connection Failure");
            }
            curl_close($curl);
            return $result;

        }catch (\Exception $e) {
            Log::channel('custom_api_error')->error('Externel API call error : ' . $e->getMessage(), [
                'apiUrl' => $url,
                'class' => 'Helper',
                'function' => 'callAPI',
                'timestamp' => now(),
            ]);
            // return null;
            throw $e;
        }
    }
}
