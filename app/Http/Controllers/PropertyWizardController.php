<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\GenericDocument;
use App\Models\Project;
use App\Models\PropertiesBuilding;
use App\Models\PropertiesFloor;
use App\Models\RentBase;
use App\Models\RentIncrement;
use App\Models\SecurityDeposit;
use App\Models\VatTax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyWizardController extends Controller
{

    public function create()
    {
        $activeMenu = 'wizard.property';
        $documents = GenericDocument::with('category')->get();

        $token = 'Authorization: Bearer '.config('app.baseline_api_token');

        $divisionUrl = config('app.baseline_base_url').'api/division';
        $divisionApiRes = $this->callAPI('GET', $divisionUrl, '', $token);
        $divisionApiResArr = json_decode($divisionApiRes, true);
        $divisions = $divisionApiResArr['data'] ?? [];

        $upazillaUrl = config('app.baseline_base_url').'api/upazilla';
        $upazillaApiRes = $this->callAPI('GET', $upazillaUrl, '', $token);
        $upazillaApiResArr = json_decode($upazillaApiRes, true);
        $upazillas = $upazillaApiResArr['data'] ?? [];

        $districtUrl = config('app.baseline_base_url').'api/district';
        $districtApiRes = $this->callAPI('GET', $districtUrl, '', $token);
        $districtApiResArr = json_decode($districtApiRes, true);
        $districts = $districtApiResArr['data'] ?? [];

        $projects = Project::where('status', 1)->get();

        return view('FacilitiesManagement.Wizard.create', compact('activeMenu', 'documents', 'divisions', 'districts', 'upazillas', 'projects'));
    }

    public function store(Request $request)
    {
        // 1. Unified Validation
        $request->validate([
            'agreement_ref_no' => 'required|string|max:255',
            'agreement_status' => 'required',
            'building_code'    => 'required|string|max:255',
            'base_rent'        => 'required|numeric',
            'is_at_source'     => 'required|in:0,1',
        ]);

        DB::beginTransaction();

        try {
            // 2. Create Agreement
            $agreement = Agreement::create([
                'agreement_ref_no' => $request->agreement_ref_no,
                'agreement_date'   => $request->agreement_date,
                'from_date'        => $request->from_date,
                'to_date'          => $request->to_date,
                'status'           => $request->agreement_status,
                'remarks'          => $request->agreement_remarks,
            ]);

            // 3. Create Building
            $building = PropertiesBuilding::create([
                'code'      => $request->building_code,
                'site_name' => $request->site_name,
                'division'  => $request->division,
                'district'  => $request->district,
                'upazila'   => $request->upazila,
                'address'   => $request->address,
                'lat'       => $request->lat,
                'long'      => $request->long,
                'country'   => 'Bangladesh',
            ]);

            // 4. Create Floor
            $floor = PropertiesFloor::create([
                'building_id'     => $building->id,
                'agreement_id'    => $agreement->id,
                'project_id'      => $request->project_id,
                'floor_label'     => $request->floor_label,
                'floor_area_sft'  => $request->floor_area_sft,
                'car_parking'     => $request->car_parking,
                'dg_space_sft'    => $request->dg_space_sft,
                'store_space_sft' => $request->store_space_sft,
                'premises_type'   => $request->premises_type,
                'status'          => 'Active',
            ]);

            // 5. Create Rent Base (VAT/Tax Logic from your original Rent controller)
            $vatTax = VatTax::where('type', 'rent')->where('status', 1)->first();
            $baseRent = $request->base_rent;
            $vatAmount = $vatTax ? ($baseRent * $vatTax->vat) / 100 : 0;
            $taxAmount = $vatTax ? ($baseRent * $vatTax->tax) / 100 : 0;

            $base = RentBase::create([
                'agreement_id' => $agreement->id,
                'base_rent'    => $baseRent,
                'vat'          => $vatAmount,
                'tax'          => $taxAmount,
                'is_at_source' => (int) $request->is_at_source,
                'rent_type'    => $request->rent_type,
                'remarks'      => $request->agreement_remarks,
            ]);

            // 6. Increments
            if ($request->has('increments')) {
                foreach ($request->increments as $inc) {
                    RentIncrement::create([
                        'agreement_id'         => $agreement->id,
                        'base_rent_id'         => $base->id,
                        'increment_amount'     => $inc['increment_amount'],
                        'increment_percentage' => $inc['increment_percentage'],
                        'increment_start_date' => $inc['increment_start_date'],
                        'increment_end_date'   => $inc['increment_end_date'],
                        'method_description'   => $inc['method_description'],
                    ]);
                }
            }

            // 7. Security Deposits
            if ($request->has('deposits')) {
                foreach ($request->deposits as $dep) {
                    SecurityDeposit::create([
                        'agreement_id'                    => $agreement->id,
                        'absorb_amount'                   => $dep['absorb_amount'],
                        'absorb_amount_percentage'        => $dep['absorb_amount_percentage'],
                        'absorb_start_date'               => $dep['absorb_start_date'],
                        'absorb_end_date'                 => $dep['absorb_end_date'],
                        'method_description'              => $dep['method_description'],
                        'security_deposit_total'          => $request->security_deposit_total,
                        'security_deposit_absorbable'     => $request->security_deposit_absorbable,
                        'security_deposit_non_absorbable' => $request->security_deposit_non_absorbable,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('agreements.index')->with('success', 'Full Property Setup Completed!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
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
