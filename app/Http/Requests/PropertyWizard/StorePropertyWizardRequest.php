<?php

namespace App\Http\Requests\PropertyWizard;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyWizardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'agreement_ref_no' => 'required|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
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
            'rent_components' => 'nullable|array',
            'rent_components.*.area_sft' => 'nullable|numeric|min:0',
            'rent_components.*.rent_amount' => 'nullable|numeric|min:0',

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
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
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
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'agreement_ref_no' => 'Agreement Reference No',
            'vendor_id' => 'Vendor',
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
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->requiresDepositRows() && !$this->hasDepositRows()) {
                $validator->errors()->add(
                    'deposits',
                    'Please add at least one deposit schedule row when Absorbable or Non-Absorbable amount is entered.'
                );
            }
        });
    }

    private function requiresDepositRows(): bool
    {
        return $this->moneyValue($this->input('security_deposit_absorbable')) > 0
            || $this->moneyValue($this->input('security_deposit_non_absorbable')) > 0;
    }

    private function hasDepositRows(): bool
    {
        return collect($this->input('deposits', []))
            ->contains(function ($deposit) {
                return collect($deposit)->contains(fn ($value) => $value !== null && $value !== '');
            });
    }

    private function moneyValue($value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
