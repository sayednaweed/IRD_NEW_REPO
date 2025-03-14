<?php

namespace App\Http\Requests\template\destination;

use Illuminate\Foundation\Http\FormRequest;

class DestinationStoreRequest extends FormRequest
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
            "farsi" => "required|string",
            "pashto" => "required|string",
            "english" => "required|string",
            "color" => "required|string",
            "destination_type_id" => "required|string",
        ];
    }
}
