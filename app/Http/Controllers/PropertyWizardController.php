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
        $request->validate(
            [
                'agreement_ref_no' => 'required|string|max:255',
                'agreement_date' => 'nullable|date',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date',
                'agreement_status' => 'required|in:0,1',
                'agreement_remarks' => 'nullable|string|max:1000',

                'building_code' => 'required|string|max:255',
                'site_name' => 'nullable|string|max:255',
                'division' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'upazila' => 'nullable|string|max:255',
                'area' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'lat' => 'nullable|numeric',
                'long' => 'nullable|numeric',

                'project_id' => 'nullable|exists:projects,id',
                'floor_label' => 'nullable|string|max:255',
                'floor_area_sft' => 'nullable|numeric|min:0',
                'car_parking' => 'nullable|integer|min:0',
                'dg_space_sft' => 'nullable|numeric|min:0',
                'store_space_sft' => 'nullable|numeric|min:0',
                'premises_type' => 'nullable|string|max:255',

                'base_rent' => 'required|numeric|min:0',
                'rent_type' => 'nullable|in:Monthly,Quarterly,Half Yearly,Yearly',
                'is_at_source' => 'required|in:0,1',

                'increments' => 'nullable|array',
                'increments.*.increment_start_date' => 'required_with:increments.*.years,increments.*.increment_end_date,increments.*.increment_amount|date',
                'increments.*.years' => 'required_with:increments.*.increment_start_date,increments.*.increment_end_date,increments.*.increment_amount|integer|min:1',
                'increments.*.increment_end_date' => 'required_with:increments.*.increment_start_date,increments.*.years,increments.*.increment_amount|date|after_or_equal:increments.*.increment_start_date',
                'increments.*.increment_amount' => 'required_with:increments.*.increment_start_date,increments.*.years,increments.*.increment_end_date|numeric|min:0',
                'increments.*.increment_percentage' => 'nullable|numeric|min:0',
                'increments.*.method_description' => 'nullable|string|max:1000',

                'security_deposit_total' => 'nullable|numeric|min:0',
                'security_deposit_absorbable' => 'nullable|numeric|min:0',
                'security_deposit_non_absorbable' => 'nullable|numeric|min:0',
                'deposits' => 'nullable|array',
                'deposits.*.absorb_amount' => 'nullable|numeric|min:0',
                'deposits.*.absorb_start_date' => 'nullable|date',
                'deposits.*.month_interval' => 'nullable|integer|min:1',
                'deposits.*.adjust_per_month' => 'nullable|numeric|min:0',
                'deposits.*.absorb_end_date' => 'nullable|date|after_or_equal:deposits.*.absorb_start_date',
                'deposits.*.method_description' => 'nullable|string|max:1000',
            ],
            [
                'agreement_ref_no.required' => 'Agreement Reference No is required.',
                'agreement_status.required' => 'Please select the agreement status.',
                'agreement_status.in' => 'Please select a valid agreement status.',
                'to_date.after_or_equal' => 'To Date must be the same as or after From Date.',
                'building_code.required' => 'Building Code is required.',
                'project_id.exists' => 'Please select a valid project.',
                'base_rent.required' => 'Base Rent is required.',
                'base_rent.numeric' => 'Base Rent must be a valid number.',
                'base_rent.min' => 'Base Rent cannot be negative.',
                'is_at_source.required' => 'Please select whether rent is at source.',
                'is_at_source.in' => 'Please select a valid Is At Source option.',
                'increments.*.increment_start_date.required_with' => 'Increment Start Date is required for each increment row.',
                'increments.*.years.required_with' => 'Years is required for each increment row.',
                'increments.*.increment_end_date.required_with' => 'Increment End Date is required for each increment row.',
                'increments.*.increment_amount.required_with' => 'Increment Amount is required for each increment row.',
                'deposits.*.absorb_amount.numeric' => 'Adjust Amount must be a valid number.',
                'deposits.*.absorb_end_date.after_or_equal' => 'Adjust End must be the same as or after Adjust Start.',
            ],
            [
                'agreement_ref_no' => 'Agreement Reference No',
                'agreement_date' => 'Agreement Date',
                'from_date' => 'From Date',
                'to_date' => 'To Date',
                'agreement_status' => 'Agreement Status',
                'agreement_remarks' => 'Agreement Remarks',
                'building_code' => 'Building Code',
                'site_name' => 'Site Name',
                'lat' => 'Latitude',
                'long' => 'Longitude',
                'project_id' => 'Project',
                'floor_label' => 'Floor Label',
                'floor_area_sft' => 'Floor Area',
                'car_parking' => 'Car Parking',
                'dg_space_sft' => 'DG Space',
                'store_space_sft' => 'Store Space',
                'premises_type' => 'Premises Type',
                'base_rent' => 'Base Rent',
                'rent_type' => 'Rent Type',
                'is_at_source' => 'Is At Source',
                'security_deposit_total' => 'Security Deposit Total',
                'security_deposit_absorbable' => 'Adjustable Security Deposit',
                'security_deposit_non_absorbable' => 'Non-Adjustable Security Deposit',
                'increments.*.increment_start_date' => 'Increment Start Date',
                'increments.*.years' => 'Increment Years',
                'increments.*.increment_end_date' => 'Increment End Date',
                'increments.*.increment_amount' => 'Increment Amount',
                'increments.*.increment_percentage' => 'Increment Percentage',
                'increments.*.method_description' => 'Increment Method Description',
                'deposits.*.absorb_amount' => 'Adjust Amount',
                'deposits.*.absorb_start_date' => 'Adjust Start',
                'deposits.*.month_interval' => 'Month Interval',
                'deposits.*.adjust_per_month' => 'Adjust Per Month',
                'deposits.*.absorb_end_date' => 'Adjust End',
                'deposits.*.method_description' => 'Deposit Method Description',
            ]
        );

        $securityDepositError = $this->validateSecurityDepositRequirement($request);

        if ($securityDepositError) {
            return back()->withInput()->withErrors(['deposits' => $securityDepositError]);
        }

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
                'area'      => $request->area,
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
            $runningRent = (float) $baseRent;
            if ($request->has('increments')) {
                foreach ($request->increments as $inc) {
                    $incrementAmount = $this->moneyValue($inc['increment_amount'] ?? null);
                    $runningRent += $incrementAmount;
                    RentIncrement::create([
                        'agreement_id'         => $agreement->id,
                        'base_rent_id'         => $base->id,
                        'incremented_amount'   => $runningRent,
                        'increment_amount'     => $inc['increment_amount'],
                        'increment_percentage' => $inc['increment_percentage'],
                        'increment_start_date' => $inc['increment_start_date'],
                        'increment_end_date'   => $inc['increment_end_date'],
                        'method_description'   => $inc['method_description'],
                    ]);
                }
            }

            // 7. Security Deposits
            $this->saveSecurityDeposits($request, $agreement->id);

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

    private function validateSecurityDepositRequirement(Request $request): ?string
    {
        $hasAbsorbable = $this->moneyValue($request->security_deposit_absorbable) > 0;
        $hasNonAbsorbable = $this->moneyValue($request->security_deposit_non_absorbable) > 0;

        if (($hasAbsorbable || $hasNonAbsorbable) && !$this->hasDepositRows($request)) {
            return 'Please add at least one deposit schedule row when Absorbable or Non-Absorbable amount is entered.';
        }

        return null;
    }

    private function saveSecurityDeposits(Request $request, int $agreementId): void
    {
        $summary = [
            'agreement_id' => $agreementId,
            'security_deposit_total' => $request->security_deposit_total,
            'security_deposit_absorbable' => $request->security_deposit_absorbable,
            'security_deposit_non_absorbable' => $request->security_deposit_non_absorbable,
        ];

        if ($this->hasDepositRows($request)) {
            foreach ($request->input('deposits', []) as $deposit) {
                SecurityDeposit::create(array_merge($this->depositPayload($deposit), $summary));
            }
            return;
        }

        if ($this->hasSecurityDepositSummary($request)) {
            SecurityDeposit::create($summary);
        }
    }

    private function hasDepositRows(Request $request): bool
    {
        return collect($request->input('deposits', []))
            ->contains(function ($deposit) {
                return collect($deposit)->contains(fn ($value) => $value !== null && $value !== '');
            });
    }

    private function depositPayload(array $deposit): array
    {
        return [
            'absorb_start_date' => $deposit['absorb_start_date'] ?? null,
            'absorb_end_date' => $deposit['absorb_end_date'] ?? null,
            'absorb_amount' => $deposit['absorb_amount'] ?? null,
            'absorb_frequency' => $deposit['month_interval'] ?? null,
            'method_description' => $deposit['method_description'] ?? null,
        ];
    }

    private function hasSecurityDepositSummary(Request $request): bool
    {
        return $this->moneyValue($request->security_deposit_total) > 0
            || $this->moneyValue($request->security_deposit_absorbable) > 0
            || $this->moneyValue($request->security_deposit_non_absorbable) > 0;
    }

    private function moneyValue($value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
