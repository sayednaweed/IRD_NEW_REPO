<?php

namespace App\Http\Requests\app\project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class ProjectBudgetUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */


    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    // protected function prepareForValidation()
    // {

    //     $jsonData = $this->input('content');

    //     if ($jsonData) {
    //         $decodedData = json_decode($jsonData, true);


    //         if (json_last_error() === JSON_ERROR_NONE) {
    //             // Merge into "request" property directly
    //             $this->request->add(array_merge(
    //                 ['id' => $this->input('id')], // keep top-level id
    //                 $decodedData
    //             ));
    //         } else {
    //             Log::error('Invalid JSON data', ['content' => $jsonData]);
    //         }
    //     }
    // }

    protected function prepareForValidation()
    {
        // Get the raw contents field from the request (this is your JSON string)
        $jsonData = $this->input('content');

        // Decode the JSON string into an array
        if ($jsonData) {
            $decodedData = json_decode($jsonData, true);


            // Log::info($decodedData);

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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'budget' => 'required|numeric|min:0',
            'donor.id' => 'required|integer',
            'currency.id' => 'required|integer',
            'centers_list' => 'required|array|min:1',
            'centers_list.*.province.id' => 'required|integer',
            'centers_list.*.budget' => 'required|numeric|min:0',
            'centers_list.*.district' => 'required|array|min:1',
            'centers_list.*.villages' => 'required|array|min:1',
        ];
    }
}
