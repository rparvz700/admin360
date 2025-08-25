<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => 'required|string',
            'email' => 'required|string|email:rfc,dns|max:250|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            // 'phone' => 'required|string',
            // 'hr_id' => 'required',
            // 'designation' => 'required|string',
            // 'department' => 'required',
            // 'subcenter' => 'required',
            'role' => 'required',
            'status' => 'required|in:0,1',
        ];
    }
}
