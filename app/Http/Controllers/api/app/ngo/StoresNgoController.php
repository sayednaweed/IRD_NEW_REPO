<?php

namespace App\Http\Controllers\api\app\ngo;

use Carbon\Carbon;
use App\Models\Ngo;
use App\Models\Email;
use App\Enums\RoleEnum;
use App\Models\Address;
use App\Models\Contact;
use App\Models\NgoTran;
use App\Models\Director;
use App\Models\Document;
use App\Models\Agreement;
use App\Models\CheckList;
use App\Models\NgoStatus;
use App\Enums\CountryEnum;
use App\Enums\LanguageEnum;
use App\Models\AddressTran;
use App\Models\PendingTask;
use App\Models\Representer;
use App\Models\DirectorTran;
use App\Enums\PermissionEnum;
use App\Models\NgoPermission;
use App\Models\CheckListTrans;
use App\Models\StatusTypeTran;
use App\Models\RepresenterTran;
use App\Enums\CheckListTypeEnum;
use App\Enums\Type\TaskTypeEnum;
use App\Models\AgreementDirector;
use App\Models\AgreementDocument;
use App\Enums\Type\StatusTypeEnum;
use Illuminate\Support\Facades\DB;
use App\Models\PendingTaskDocument;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Enums\CheckList\CheckListEnum;
use App\Enums\Type\RepresenterTypeEnum;
use App\Http\Requests\app\ngo\NgoRegisterRequest;
use App\Http\Requests\app\ngo\NgoInitStoreRequest;
use App\Repositories\Task\PendingTaskRepositoryInterface;

class StoresNgoController extends Controller
{
    protected $pendingTaskRepository;

    public function __construct(PendingTaskRepositoryInterface $pendingTaskRepository)
    {
        $this->pendingTaskRepository = $pendingTaskRepository;
    }
    public function store(NgoRegisterRequest $request)
    {
        $validatedData = $request->validated();
        $authUser = $request->user();
        $locale = App::getLocale();
        // Create email
        $email = Email::where('value', '=', $validatedData['email'])->first();
        if ($email) {
            return response()->json([
                'message' => __('app_translation.email_exist'),
            ], 400, [], JSON_UNESCAPED_UNICODE);
        }
        $contact = Contact::where('value', '=', $validatedData['contact'])->first();
        if ($contact) {
            return response()->json([
                'message' => __('app_translation.contact_exist'),
            ], 400, [], JSON_UNESCAPED_UNICODE);
        }
        // Begin transaction
        DB::beginTransaction();
        $email = Email::create(['value' => $validatedData['email']]);
        $contact = Contact::create(['value' => $validatedData['contact']]);
        // Create address
        $address = Address::create([
            'district_id' => $validatedData['district_id'],
            'province_id' => $validatedData['province_id'],
        ]);

        // * Translations
        foreach (LanguageEnum::LANGUAGES as $code => $name) {
            AddressTran::create([
                'address_id' => $address->id,
                'area' => $validatedData["area_{$name}"],
                'language_name' =>  $code,
            ]);
        }
        // Create NGO
        $newNgo = Ngo::create([
            "user_id" => $authUser->id,
            'abbr' => $validatedData['abbr'],
            'registration_no' => "",
            'role_id' => RoleEnum::ngo->value,
            'ngo_type_id' => $validatedData['ngo_type_id'],
            'address_id' => $address->id,
            'email_id' => $email->id,
            'username' => $request->username,
            'contact_id' => $contact->id,
            "password" => Hash::make($validatedData['password']),
        ]);

        // Crea a registration_no
        $newNgo->registration_no = "IRD" . '-' . Carbon::now()->year . '-' . $newNgo->id;
        $newNgo->save();
        // Set ngo status
        NgoStatus::create([
            "ngo_id" => $newNgo->id,
            "user_id" => $authUser->id,
            "is_active" => true,
            "status_type_id" => StatusTypeEnum::not_logged_in->value,
            "comment" => "Newly Created"
        ]);

        // **Fix agreement creation**
        $agreement = Agreement::create([
            'ngo_id' => $newNgo->id,
            "agreement_no" => ""
        ]);
        $agreement->agreement_no = "AG" . '-' . Carbon::now()->year . '-' . $agreement->id;
        $agreement->save();

        $storerepresenterDoc = $this->storeRepresenter($request, $agreement->id, $newNgo->id);
        // * Translations
        if ($storerepresenterDoc) {
            return response()->json([
                'message' => __('app_translation.checklist_not_found'),

            ]);
        }
        foreach (LanguageEnum::LANGUAGES as $code => $name) {
            NgoTran::create([
                'ngo_id' => $newNgo->id,
                'language_name' => $code,
                'name' => $validatedData["name_{$name}"],
            ]);
        }
        // Create permissions
        $this->ngoPermissions($newNgo->id);

        $name =  $validatedData['name_english'];
        if ($locale == LanguageEnum::farsi->value) {
            $name = $validatedData['name_farsi'];
        } else if ($locale == LanguageEnum::pashto->value) {
            $name = $validatedData['name_pashto'];
        }

        // If everything goes well, commit the transaction
        DB::commit();

        $status = StatusTypeTran::where('status_type_id', StatusTypeEnum::not_logged_in->value)
            ->where('language_name', $locale)
            ->select('name')->first();
        return response()->json(
            [
                'message' => __('app_translation.success'),
                "ngo" => [
                    "id" => $newNgo->id,
                    "profile" => $newNgo->profile,
                    "abbr" => $newNgo->abbr,
                    "registration_no" => $newNgo->registration_no,
                    "status_id" => StatusTypeEnum::not_logged_in->value,
                    "status" => $status->name,
                    "type_id" => $validatedData['ngo_type_id'],
                    "establishment_date" => null,
                    "name" => $name,
                    "contact" => $validatedData['contact'],
                    "email" => $validatedData['email'],
                    "created_at" => $newNgo->created_at,
                ]
            ],
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

    protected function storeRepresenter($request, $agreement_id, $ngo_id)
    {
        $representer =  Representer::create([
            'type' => RepresenterTypeEnum::ngo,
            'represented_id' => $agreement_id,
            'user_id' => $request->user()->id,
        ]);
        foreach (LanguageEnum::LANGUAGES as $code => $name) {
            RepresenterTran::create([
                'representer_id' => $representer->id,
                'language_name' =>  $code,
                'full_name' => $request->representer_name_ . $name,
                'is_active' => true,
            ]);
        }


        $document =  $this->representerDocumentStore($request, $agreement_id, $ngo_id);
        if ($document) {
            return $document;
        }
    }

    public function registerFormCompleted(NgoInitStoreRequest $request)
    {
        // return $request;
        $id = $request->ngo_id;
        $validatedData =  $request->validated();

        $agreement = Agreement::where('ngo_id', $id)
            ->where('end_date', null) // Order by end_date descending
            ->first();           // Get the first record (most recent)

        // 1. If agreement exists do not allow process furtherly.
        if (!$agreement) {
            return response()->json([
                'message' => __('app_translation.agreement_not_exists')
            ], 409);
        }

        // 2. CheckListEnum:: NGO exist
        $ngo = Ngo::find($id);
        if (!$ngo) {
            return response()->json([
                'message' => __('app_translation.ngo_not_found'),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        // 3. Ensure task exists before proceeding
        $task = $this->pendingTaskRepository->pendingTaskExist(
            $request->user(),
            TaskTypeEnum::ngo_registeration,
            $id
        );
        if (!$task) {
            return response()->json([
                'error' => __('app_translation.task_not_found')
            ], 404);
        }
        // Do not follow these checklist Validate
        $exclude = [
            CheckListEnum::ngo_representor_letter->value,
            CheckListEnum::ngo_register_form_en->value,
            CheckListEnum::ngo_register_form_fa->value,
            CheckListEnum::ngo_register_form_ps->value,
        ];
        if ($validatedData["country"]["id"] != CountryEnum::afghanistan->value) {
            array_push($exclude, CheckListEnum::director_work_permit->value);
        }
        // 4. CheckListEnum:: task exists
        $errors = $this->validateCheckList($task, $exclude);
        if ($errors) {
            return response()->json([
                'message' => __('app_translation.checklist_not_found'),
                'errors' => $errors // Reset keys for cleaner JSON output
            ], 400);
        }


        Email::where('id', $ngo->email_id)->update(['value' => $validatedData['email']]);
        Contact::where('id', $ngo->contact_id)->update(['value' => $validatedData['contact']]);

        $ngo_en_tran = NgoTran::where('ngo_id', $id)->where('language_name', 'en')->first();
        $ngo_ps_tran = NgoTran::where('ngo_id', $id)->where('language_name', 'ps')->first();
        $ngo_fa_tran = NgoTran::where('ngo_id', $id)->where('language_name', 'fa')->first();
        $ngo_addres = Address::find($ngo->address_id);
        $ngo_addres_en = AddressTran::where('address_id', $ngo->address_id)->where('language_name', 'en')->first();
        $ngo_addres_ps = AddressTran::where('address_id', $ngo->address_id)->where('language_name', 'ps')->first();
        $ngo_addres_fa = AddressTran::where('address_id', $ngo->address_id)->where('language_name', 'fa')->first();

        $ngo_en_tran->name  =    $validatedData["name_english"];
        $ngo_ps_tran->name  =    $validatedData["name_pashto"];
        $ngo_fa_tran->name  =    $validatedData["name_farsi"];
        $ngo->abbr =  $validatedData["abbr"];
        $ngo->ngo_type_id  = $validatedData['type']['id'];
        // $ngo->ngo_type_id  = $validatedData["type.id"];
        $ngo->moe_registration_no  = $validatedData["moe_registration_no"];
        $ngo->place_of_establishment   = $validatedData["country"]["id"];
        $ngo->date_of_establishment  = $validatedData["establishment_date"];
        $ngo_addres->province_id  = $validatedData["province"]["id"];
        $ngo_addres->district_id  = $validatedData["district"]["id"];
        $ngo_addres_en->area = $validatedData["area_english"];
        $ngo_addres_ps->area = $validatedData["area_pashto"];
        $ngo_addres_fa->area = $validatedData["area_farsi"];

        $ngo_en_tran->vision  =    $validatedData["vision_english"];
        $ngo_ps_tran->vision  =    $validatedData["vision_pashto"];
        $ngo_fa_tran->vision  =    $validatedData["vision_farsi"];
        $ngo_en_tran->mission  =    $validatedData["mission_english"];
        $ngo_ps_tran->mission  =    $validatedData["mission_pashto"];
        $ngo_fa_tran->mission  =    $validatedData["mission_farsi"];
        $ngo_en_tran->general_objective  =    $validatedData["general_objes_english"];
        $ngo_ps_tran->general_objective  =    $validatedData["general_objes_pashto"];
        $ngo_fa_tran->general_objective  =    $validatedData["general_objes_farsi"];
        $ngo_en_tran->objective  =    $validatedData["objes_in_afg_english"];
        $ngo_ps_tran->objective  =    $validatedData["objes_in_afg_pashto"];
        $ngo_fa_tran->objective  =    $validatedData["objes_in_afg_farsi"];

        DB::beginTransaction();

        $ngo_en_tran->save();
        $ngo_ps_tran->save();
        $ngo_fa_tran->save();
        $ngo_addres->save();
        $ngo_addres_en->save();
        $ngo_addres_ps->save();
        $ngo_addres_fa->save();
        $ngo->save();

        // Make prevous state to false
        NgoStatus::where('ngo_id', $id)->update(['is_active' => false]);
        NgoStatus::create([
            'ngo_id' => $id,
            'user_id' => $request->user()->id,
            "is_active" => true,
            'status_type_id' => StatusTypeEnum::register_form_submited,
            'comment' => 'Register Form Complete',
        ]);


        $document =  $this->documentStore($request, $agreement->id, $id, $validatedData["name_english"]);
        if ($document) {
            return $document;
        }
        $this->directorStore($validatedData, $id, $agreement->id);

        $this->pendingTaskRepository->deletePendingTask(
            $request->user(),
            TaskTypeEnum::ngo_registeration,
            $id
        );


        DB::commit();
        return response()->json(
            [
                'message' => __('app_translation.success'),
            ],
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

    protected function validateCheckList($task, $exclude)
    {
        // Get checklist IDs
        $checkListIds = CheckList::where('check_list_type_id', CheckListTypeEnum::ngoRegister)
            ->whereNotIn('id', $exclude)
            ->pluck('id')
            ->toArray();

        // Get checklist IDs from documents
        $documentCheckListIds = $this->pendingTaskRepository->pendingTaskDocumentQuery(
            $task->id
        )->pluck('check_list_id')
            ->toArray();

        // Find missing checklist IDs
        $missingCheckListIds = array_diff($checkListIds, $documentCheckListIds);

        if (count($missingCheckListIds) > 0) {
            // Retrieve missing checklist names
            $missingCheckListNames = CheckListTrans::whereIn('check_list_id', $missingCheckListIds)
                ->where('language_name', app()->getLocale()) // If multilingual, get current language
                ->pluck('value');


            $errors = [];
            foreach ($missingCheckListNames as $item) {
                array_push($errors, [__('app_translation.checklist_not_found') . ' ' . $item]);
            }

            return $errors;
        }

        return null;
    }
    protected function documentStore($request, $agreement_id, $ngo_id)
    {
        $task = PendingTask::where('task_id', $request->pending_id)
            ->first();
        if (!$task) {
            return response()->json(['error' => __('app_translation.checklist_not_found')], 404);
        }
        // Get checklist IDs
        $documents = PendingTaskDocument::join('check_lists', 'check_lists.id', 'pending_task_documents.check_list_id')
            ->select('size', 'path', 'acceptable_mimes', 'check_list_id', 'actual_name', 'extension')
            ->where('pending_task_id', $task->id)
            ->get();

        foreach ($documents as $checklist) {

            $oldPath = storage_path("app/" . $checklist['path']); // Absolute path of temp file

            $newDirectory = storage_path() . "/app/private/ngos/{$ngo_id}/{$agreement_id}/{$checklist['check_list_id']}/";

            if (!file_exists($newDirectory)) {
                mkdir($newDirectory, 0775, true);
            }

            $newPath = $newDirectory . basename($checklist['path']); // Keep original filename

            $dbStorePath = "private/ngos/{$ngo_id}/{$agreement_id}/{$checklist['check_list_id']}/"
                . basename($checklist['path']);
            // Ensure the new directory exists

            // Move the file
            if (file_exists($oldPath)) {
                rename($oldPath, $newPath);
            } else {
                return response()->json(['error' => __('app_translation.not_found') . $oldPath], 404);
            }


            $document = Document::create([
                'actual_name' => $checklist['actual_name'],
                'size' => $checklist['size'],
                'path' => $dbStorePath,
                'type' => $checklist['extension'],
                'check_list_id' => $checklist['check_list_id'],
            ]);

            // **Fix whitespace issue in keys**
            AgreementDocument::create([
                'document_id' => $document->id,
                'agreement_id' => $agreement_id,
            ]);
        }
    }

    protected function representerDocumentStore($request, $agreement_id, $ngo_id)
    {
        $task = PendingTask::where('id', $request->pending_id)
            ->first();

        if (!$task) {
            return response()->json(['error' => __('app_translation.checklist_not_found')], 404);
        }
        // Get checklist IDs

        $documents = PendingTaskDocument::select('size', 'path', 'check_list_id', 'actual_name', 'extension')
            ->where('pending_task_id', $task->id)
            ->first();

        $oldPath = storage_path("app/" . $documents->path); // Absolute path of temp file
        $newDirectory = storage_path() . "/app/private/ngos/{$ngo_id}/{$agreement_id}/{$documents->check_list_id}/";

        if (!file_exists($newDirectory)) {
            mkdir($newDirectory, 0775, true);
        }

        $newPath = $newDirectory . basename($documents->path); // Keep original filename

        $dbStorePath = "private/ngos/{$ngo_id}/{$agreement_id}/{$documents->check_list_id}/"
            . basename($documents->path);
        // Ensure the new directory exists

        // Move the file
        if (file_exists($oldPath)) {
            rename($oldPath, $newPath);
        } else {
            return response()->json(['error' => __('app_translation.not_found') . $oldPath], 404);
        }


        $document = Document::create([
            'actual_name' => $documents->actual_name,
            'size' => $documents->size,
            'path' => $dbStorePath,
            'type' => $documents->extension,
            'check_list_id' => $documents->check_list_id,
        ]);

        // **Fix whitespace issue in keys**
        AgreementDocument::create([
            'document_id' => $document->id,
            'agreement_id' => $agreement_id,
        ]);
    }
    protected function directorStore($validatedData, $ngo_id, $agreement_id)
    {
        $email = Email::create(['value' => $validatedData['director_email']]);
        $contact = Contact::create(['value' => $validatedData['director_contact']]);

        // **Fix address creation**
        $address = Address::create([
            'province_id' => $validatedData['director_province']['id'],
            'district_id' => $validatedData['director_dis']['id'],
        ]);

        AddressTran::insert([
            ['language_name' => 'en', 'address_id' => $address->id, 'area' => $validatedData['director_area_english']],
            ['language_name' => 'ps', 'address_id' => $address->id, 'area' => $validatedData['director_area_pashto']],
            ['language_name' => 'fa', 'address_id' => $address->id, 'area' => $validatedData['director_area_farsi']],
        ]);

        $director = Director::create([
            'ngo_id' => $ngo_id,
            'nid_no' => $validatedData['nid'] ?? '',
            'nid_type_id' => $validatedData['identity_type']['id'],
            'is_Active' => 1,
            'gender_id' => $validatedData['gender']['id'],
            'country_id' => $validatedData['nationality']['id'],
            'address_id' => $address->id,
            'email_id' => $email->id,
            'contact_id' => $contact->id,
        ]);



        DirectorTran::insert([
            ['director_id' => $director->id, 'language_name' => 'en', 'name' => $validatedData['director_name_english'], 'last_name' => $validatedData['surname_english']],
            ['director_id' => $director->id, 'language_name' => 'ps', 'name' => $validatedData['director_name_pashto'], 'last_name' => $validatedData['surname_pashto']],
            ['director_id' => $director->id, 'language_name' => 'fa', 'name' => $validatedData['director_name_farsi'], 'last_name' => $validatedData['surname_farsi']],
        ]);

        AgreementDirector::create([
            'agreement_id' => $agreement_id,
            'director_id' => $director->id
        ]);
    }

    public function ngoPermissions($ngo_id)
    {
        NgoPermission::create([
            "view" => true,
            "edit" => true,
            "delete" => true,
            "add" => true,
            "ngo_id" => $ngo_id,
            "permission" => PermissionEnum::dashboard->value,
        ]);

        NgoPermission::create([
            "visible" => false,
            "view" => true,
            "edit" => true,
            "delete" => true,
            "add" => true,
            "ngo_id" => $ngo_id,
            "permission" => PermissionEnum::ngo->value,
        ]);
    }
}
