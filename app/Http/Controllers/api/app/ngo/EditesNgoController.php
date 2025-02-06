<?php

namespace App\Http\Controllers\api\app\ngo;

use App\Enums\LanguageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\app\ngo\NgoProfileUpdateRequest;
use App\Models\Ngo;
use App\Models\NgoTran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EditesNgoController extends Controller
{
    //


      public function profileUpdate(NgoProfileUpdateRequest $request, $id)
    {


        // Find the NGO
        $ngo = Ngo::find($id);

        if (!$ngo || $ngo->is_editable != 1) {
            return response()->json(['message' => __('app_translation.notEditable')], 403);
        }


        $validatedData = $request->validated();


        // Begin transaction
        DB::beginTransaction();

        $path = $this->storeProfile($request, 'ngo-profile');
        $ngo->update([
            "profile" =>  $path,
        ]);

        // Update default language record
        $ngoTran = NgoTran::where('ngo_id', $id)
            ->where('language_name', LanguageEnum::default->value)
            ->first();

        if ($ngoTran) {
            $ngoTran->update([
                'name' => $validatedData['name_english'],
                'vision' => $validatedData['vision_english'],
                'mission' => $validatedData['mission_english'],
                'general_objective' => $validatedData['general_objective_english'],
                'objective' => $validatedData['objective_english'],
                'introduction' => $validatedData['introduction_english']
            ]);
        } else {
            return response()->json(['message' => __('app_translation.not_found')], 404);
        }

        // Manage multilingual NgoTran records
        $languages = [
            'pashto',
            'farsi'

        ];

        NgoTran::create([
            'ngo_id' => $id,
            'language_name ' => 'ps',
            'name' => $validatedData["name_pashto"],
            'vision' => $validatedData["vision_pashto"],
            'mission' => $validatedData["mission_pashto"],
            'general_objective' => $validatedData["general_objective_pashto"],
            'objective' => $validatedData["objective_pashto"],
            'introduction' => $validatedData["introduction_pashto"]

        ]);
        NgoTran::create([
            'ngo_id' => $id,
            'language_name ' => 'fa',
            'name' => $validatedData["name_farsi"],
            'vision' => $validatedData["vision_farsi"],
            'mission' => $validatedData["mission_farsi"],
            'general_objective' => $validatedData["general_objective_farsi"],
            'objective' => $validatedData["objective_farsi"],
            'introduction' => $validatedData["introduction_farsi"]

        ]);



        // Instantiate DirectorController and call its store method
        $directorController = new \App\Http\Controllers\api\app\director\DirectorController();
        $directorController->store($request, $id);

        // store document
        // Commit transaction
        DB::commit();
        return response()->json(['message' => __('app_translation.success')], 200);
    }

   
}
