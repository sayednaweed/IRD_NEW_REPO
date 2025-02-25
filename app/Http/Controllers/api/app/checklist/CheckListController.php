<?php

namespace App\Http\Controllers\api\app\checklist;

use App\Enums\LanguageEnum;
use Illuminate\Http\Request;
use App\Enums\CheckListTypeEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Enums\CheckList\CheckListEnum;
use App\Http\Requests\app\checklist\StoreCheckListRequest;
use App\Models\CheckList;
use App\Models\CheckListTrans;
use App\Models\CheckListType;

class CheckListController extends Controller
{
    public function ngoRegister()
    {
        $locale = App::getLocale();
        $exclude = [
            CheckListEnum::director_work_permit->value,
            CheckListEnum::ngo_representor_letter->value,
            CheckListEnum::ngo_register_form_en->value,
            CheckListEnum::ngo_register_form_fa->value,
            CheckListEnum::ngo_register_form_ps->value,
        ];
        $tr = DB::table('check_lists as cl')
            ->where('cl.active', true)
            ->where('cl.check_list_type_id', CheckListTypeEnum::ngoRegister->value)
            ->whereNotIn('cl.id',  $exclude)
            ->join('check_list_trans as clt', 'clt.check_list_id', '=', 'cl.id')
            ->where('clt.language_name', $locale)
            ->select(
                'clt.value as name',
                'cl.id',
                'cl.acceptable_mimes',
                'cl.acceptable_extensions',
                'cl.description'
            )
            ->orderBy('cl.id')
            ->get();
        return response()->json([
            'checklist' => $tr
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function ngoRegisterAbroadDirector()
    {
        $locale = App::getLocale();
        $exclude = [
            CheckListEnum::ngo_representor_letter->value,
            CheckListEnum::ngo_register_form_en->value,
            CheckListEnum::ngo_register_form_fa->value,
            CheckListEnum::ngo_register_form_ps->value,
        ];
        $tr = DB::table('check_lists as cl')
            ->where('cl.check_list_type_id', CheckListTypeEnum::ngoRegister->value)
            ->where('cl.active', true)
            ->whereNotIn('cl.id', $exclude)
            ->join('check_list_trans as clt', 'clt.check_list_id', '=', 'cl.id')
            ->where('clt.language_name', $locale)
            ->select(
                'clt.value as name',
                'cl.id',
                'cl.acceptable_mimes',
                'cl.acceptable_extensions',
                'cl.description'
            )
            ->orderBy('cl.id')
            ->get();
        return response()->json([
            'checklist' => $tr
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function projectRegister()
    {
        $tr =  [];

        return response()->json([
            'ngos' => $tr
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function store(StoreCheckListRequest $request)
    {
        $request->validated();
        $authUser = $request->user();
        $convertedMimes = [];
        $convertedExtensions = [];
        foreach ($request->file_type as $extension) {
            $convertedMimes[] = $extension['frontEndName'];
            $convertedExtensions[] = $extension['name'];
        }

        $checklist = CheckList::create([
            "check_list_type_id" => $request->type['id'],
            "active" => $request->status,
            "file_size" => $request->file_size,
            "description" => $request->detail,
            "user_id" => $authUser->id,
            "acceptable_extensions" => implode(',', $convertedExtensions),
            "acceptable_mimes" => implode(',', $convertedMimes),
        ]);
        foreach (LanguageEnum::LANGUAGES as $code => $name) {
            CheckListTrans::create([
                "value" => $request["name_{$name}"],
                "check_list_id" => $checklist->id,
                "language_name" => $code,
            ]);
        }
        $locale = App::getLocale();
        $name = $request->name_english;
        if ($locale == LanguageEnum::farsi->value) {
            $name = $request->name_farsi;
        } else {
            $name = $request->name_pashto;
        }
        $tr =  [
            "id" => $checklist->id,
            "name" => $name,
            "description" => $checklist->description,
            "active" => $request->status,
            "type" => $request->type['name'],
            "type_id" => $request->type['id'],
            "saved_by" => $authUser->username,
            "created_at" => $checklist->created_at,
        ];
        return response()->json(
            [
                "checklist" => $tr,
                'message' => __('app_translation.success'),
            ],
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }
    public function update(StoreCheckListRequest $request)
    {
        $request->validated();
        $request->validate([
            'id' => "required"
        ]);
        $authUser = $request->user();
        $id = $request->id;
        $checklist = CheckList::find($id);
        if (!$checklist) {
            return response()->json([
                'message' => __('app_translation.checklist_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        $type = CheckListType::find($request->type['id']);
        if (!$type) {
            return response()->json([
                'message' => __('app_translation.checklist_type_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        // Begin transaction
        DB::beginTransaction();
        // 1. Update checklist
        $convertedMimes = [];
        $convertedExtensions = [];
        foreach ($request->file_type as $extension) {
            $convertedMimes[] = $extension['frontEndName'];
            $convertedExtensions[] = $extension['name'];
        }

        $checklist->acceptable_mimes = implode(',', $convertedMimes);
        $checklist->acceptable_extensions = implode(',', $convertedExtensions);
        $checklist->check_list_type_id = $type->id;
        $checklist->active = $request->status;
        $checklist->file_size = $request->file_size;
        $checklist->description = $request->detail;
        $checklist->user_id  = $authUser->id;
        $checklist->save();
        // 2. Update translations
        $trans = CheckListTrans::where('check_list_id', $checklist->id)
            ->select('language_name', 'value', 'id')
            ->get();
        foreach (LanguageEnum::LANGUAGES as $code => $name) {
            $translation = $trans->where('language_name', $code)->first();
            if ($translation) {
                $translation->value = $request["name_{$name}"];
                $translation->save();
            } else {
                return response()->json([
                    'message' => __('app_translation.failed')
                ], 500, [], JSON_UNESCAPED_UNICODE);
            }
        }

        DB::commit();
        $locale = App::getLocale();
        $name = $request->name_english;
        if ($locale == LanguageEnum::farsi->value) {
            $name = $request->name_farsi;
        } else {
            $name = $request->name_pashto;
        }
        $tr =  [
            "id" => $checklist->id,
            "name" => $name,
            "description" => $checklist->description,
            "active" => $checklist->active,
            "type" => $request->type['name'],
            "type_id" => $request->type['id'],
            "saved_by" => $authUser->username,
            "created_at" => $checklist->created_at,
        ];
        return response()->json([
            'checklist' => $tr,
            'message' => __('app_translation.success'),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function destroy($id)
    {
        CheckList::find($id)->delete();
        return response()->json(
            [
                'message' => __('app_translation.success'),
            ],
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }
    public function checklists()
    {
        $locale = App::getLocale();
        $tr = DB::table('check_lists as cl')
            ->join('users as u', 'u.id', '=', 'cl.user_id')
            ->join('check_list_trans as clt', 'clt.check_list_id', '=', 'cl.id')
            ->where('clt.language_name', $locale)
            ->join('check_list_types as cltt', 'cltt.id', '=', 'cl.check_list_type_id')
            ->join('check_list_type_trans as clttt', 'clttt.check_list_type_id', '=', 'cltt.id')
            ->where('clttt.language_name', $locale)
            ->select(
                'clt.value as name',
                'cl.id',
                'cl.acceptable_mimes',
                'cl.acceptable_extensions',
                'cl.description',
                'cl.active',
                'clttt.value as type',
                'cltt.id as type_id',
                'u.username as saved_by',
                'cl.created_at'
            )
            ->orderBy('cltt.id')
            ->get();

        return response()->json(
            $tr,
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

    public function checklist($id)
    {
        $locale = App::getLocale();
        $checklist = DB::table('check_lists as cl')
            ->where('cl.id', $id)
            ->leftJoin('check_list_trans as clt_farsi', function ($join) {
                $join->on('clt_farsi.check_list_id', '=', 'cl.id')
                    ->where('clt_farsi.language_name', 'fa'); // Join for Farsi (fa)
            })
            ->leftJoin('check_list_trans as clt_english', function ($join) {
                $join->on('clt_english.check_list_id', '=', 'cl.id')
                    ->where('clt_english.language_name', 'en'); // Join for English (en)
            })
            ->leftJoin('check_list_trans as clt_pashto', function ($join) {
                $join->on('clt_pashto.check_list_id', '=', 'cl.id')
                    ->where('clt_pashto.language_name', 'ps'); // Join for Pashto (ps)
            })
            ->join('check_list_types as cltt', 'cltt.id', '=', 'cl.check_list_type_id')
            ->join('check_list_type_trans as clttt', 'clttt.check_list_type_id', '=', 'cltt.id')
            ->where('clttt.language_name', $locale)
            ->select(
                'cl.id',
                'cl.acceptable_mimes',
                'cl.acceptable_extensions',
                'cl.description',
                'cl.active as status',
                'cl.file_size',
                'clttt.value as type',
                'cltt.id as type_id',
                'clt_farsi.value as name_farsi', // Farsi translation
                'clt_english.value as name_english', // English translation
                'clt_pashto.value as name_pashto',
                'cl.created_at'
            )
            ->orderBy('cltt.id')
            ->first();

        // Check if acceptable_mimes and acceptable_extensions are present
        if ($checklist) {
            // Exploding the comma-separated strings into arrays
            $acceptableMimes = explode(',', $checklist->acceptable_mimes);
            $acceptableExtensions = explode(',', $checklist->acceptable_extensions);
            $acceptableExtensions = explode(',', $checklist->acceptable_extensions);

            // Combine them into an array of objects
            $combined = [];
            foreach ($acceptableMimes as $index => $mime) {
                // Assuming the index of mimes matches with extensions
                if (isset($acceptableExtensions[$index])) {
                    $combined[] = [
                        'name' => $acceptableExtensions[$index],
                        "label" => $mime,
                        'frontEndName' => $mime
                    ];
                }
            }

            // Assign the combined array to the checklist object
            $checklist->file_type = $combined;
        }
        $checklist->status = (bool) $checklist->status;
        // Remove unwanted data from the checklist
        unset($checklist->acceptable_mimes);
        unset($checklist->acceptable_extensions);
        $tr =  [
            "id" => $checklist->id,
            "name_farsi" => $checklist->name_farsi,
            "name_english" => $checklist->name_english,
            "name_pashto" => $checklist->name_pashto,
            "detail" => $checklist->description,
            "file_type" => $checklist->file_type,
            "type" => [
                'id' => $checklist->type_id,
                'name' => $checklist->type
            ],
            "status" => $checklist->status,
            "file_size" => $checklist->file_size,
            "created_at" => $checklist->created_at,
        ];
        return response()->json(
            $tr,
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }
    public function checklistTypes()
    {
        $locale = App::getLocale();
        $tr =  DB::table('check_list_types as clt')
            ->join('check_list_type_trans as cltt', 'cltt.check_list_type_id', '=', 'clt.id')
            ->where('cltt.language_name', $locale)
            ->select(
                'cltt.value as name',
                'clt.id',
            )
            ->orderBy('clt.id')
            ->get();

        return response()->json(
            $tr,
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }
}
