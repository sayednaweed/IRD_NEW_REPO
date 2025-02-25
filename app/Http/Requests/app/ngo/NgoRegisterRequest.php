<?php

namespace App\Http\Requests\app\ngo;

use Illuminate\Foundation\Http\FormRequest;

class NgoRegisterRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:emails,value',
            'contact' => 'required|unique:contacts,value',
            'province_id' => 'required|integer|exists:provinces,id',
            'district_id' => 'required|integer|exists:districts,id',
            "password" => "required",
            'area_english' => 'required|string|max:255',
            'area_farsi' => 'required|string|max:255',
            'area_pashto' => 'required|string|max:255',
            'abbr' => 'required|string|max:50|unique:ngos,abbr',
            'username' => 'required|string|max:50|unique:ngos,username',
            'ngo_type_id' => 'required|integer|exists:ngo_types,id',
            'name_english' => 'required|string|unique:ngo_trans,name',
            'name_pashto' => 'required|string|unique:ngo_trans,name',
            'name_farsi' => 'required|string|unique:ngo_trans,name',
            'full_name_english' => 'required|string|max:128',
            'full_name_farsi' => 'required|string|max:128',
            'full_name_pashto' => 'required|string|max:128',
            'pending_id' => 'required|integer'
        ];
    }
}
