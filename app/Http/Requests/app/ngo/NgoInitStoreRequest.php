<?php

namespace App\Http\Requests\app\ngo;

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;

class NgoInitStoreRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * This method is called before the validation rules are applied.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Get the raw contents field from the request (this is your JSON string)
        $jsonData = $this->input('content');

        // Decode the JSON string into an array
        if ($jsonData) {
            $decodedData = json_decode($jsonData, true);

            // If the JSON is valid, merge the decoded data into the request
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge($decodedData);
            } else {
                // Log or handle error if JSON is invalid
                Log::error('Invalid JSON data received', ['data' => $jsonData]);
            }
        }
    }
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

            "name_english" => 'required|string|max:128|min:5',
            "name_farsi" =>  'required|string|max:128|min:5',
            "name_pashto" => 'required|string|max:128|min:5',
            "abbr" => "required|string",
            "type.id" => "required|exists:ngo_types,id",
            "contact" => "required",
            "email" => "required",
            "moe_registration_no" => "required|unique:ngos,moe_registration_no",
            "country.id" => "required|exists:countries,id",
            "establishment_date" => "required",
            "province.id" => "required|exists:provinces,id",
            "district.id" => "required|exists:districts,id",
            "area_english" => "required|max:128|min:5",
            "area_pashto" => "required|max:128|min:5",
            "area_farsi" => "required|max:128|min:5",
            // director
            "director_name_english" => "required|max:128|min:3",
            "director_name_farsi"  => "required|max:128|min:3",
            "director_name_pashto" => "required|max:128|min:3",
            "surname_english" => "required|max:128|min:3",
            "surname_pashto" => "required|max:128|min:3",
            "surname_farsi" => "required|max:128|min:3",
            "director_contact" => "required|unique:contacts,value",
            "director_email" => "required|unique:emails,value",
            "gender.id" => "required|exists:genders,id",
            "nationality.id" => "required|exists:countries,id",
            "identity_type.id" => "required|exists:nid_types,id",
            "nid" => "required|unique:directors,nid_no",
            "director_province.id" => "required|exists:provinces,id",
            "director_dis.id" => "required|exists:districts,id",
            "director_area_english" => "required|max:128|min:5",
            "director_area_farsi" => "required|max:128|min:5",
            "director_area_pashto" => "required|max:128|min:5",
            // ngo details
            "vision_english" =>  "required|min:5",
            "vision_pashto" =>  "required|min:5",
            "vision_farsi" =>  "required|min:5",
            "mission_english" =>  "required|min:5",
            "mission_pashto" =>  "required|min:5",
            "mission_farsi" =>  "required|min:5",
            "general_objes_english" =>  "required|min:5",
            "general_objes_pashto" =>  "required|min:5",
            "general_objes_farsi" =>  "required|min:5",
            "objes_in_afg_english" =>  "required|min:5",
            "objes_in_afg_pashto" =>  "required|min:5",
            "objes_in_afg_farsi" =>  "required|min:5",
        ];
    }
}
