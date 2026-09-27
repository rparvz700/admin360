<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportBuilderRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'template_id'                  => 'nullable|integer',
            'selected_fields'              => 'required_without:template_id|array|min:1',
            'selected_fields.*.table'      => 'required_with:selected_fields|string|max:255',
            'selected_fields.*.column'     => 'required_with:selected_fields|string|max:255',
            'selected_fields.*.alias'      => 'nullable|string|max:255',
            'selected_fields.*.aggregate'  => 'nullable|string|in:sum,avg,count,max,min,',
            'filters'                      => 'nullable|array',
            'sorts'                        => 'nullable|array',
            'sorts.*.table'                => 'required_with:sorts|string|max:255',
            'sorts.*.column'               => 'required_with:sorts|string|max:255',
            'sorts.*.direction'            => 'nullable|string|in:asc,desc,ASC,DESC',
            'runtime_date_from'            => 'nullable|string|max:50',
            'runtime_date_to'              => 'nullable|string|max:50',
            'runtime_date_column'          => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'selected_fields.required' => 'Please select at least one field to generate the report.',
            'selected_fields.min'      => 'Please select at least one field to generate the report.',
        ];
    }
}
