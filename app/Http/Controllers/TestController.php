<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Ngo;
use App\Models\News;
use App\Models\User;

use App\Models\Email;
use App\Models\Staff;
use App\Models\Gender;

use App\Models\Address;
use App\Models\Country;
use App\Models\NgoTran;
use App\Enums\StaffEnum;
use App\Models\Director;
use App\Models\District;
use App\Models\Document;
use App\Models\Province;
use App\Models\Agreement;
use App\Models\CheckList;
use App\Models\Translate;
use App\Enums\LanguageEnum;
use App\Models\AddressTran;
use App\Models\NidTypeTrans;
use Illuminate\Http\Request;
use App\Enums\PermissionEnum;
use Sway\Models\RefreshToken;
use App\Models\StatusTypeTran;
use App\Enums\SubPermissionEnum;
use App\Enums\DestinationTypeEnum;
use App\Enums\Type\StatusTypeEnum;
use App\Models\PendingTaskContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Traits\Address\AddressTrait;
use App\Repositories\ngo\NgoRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;

class TestController extends Controller
{
    protected $ngoRepository;
    protected $userRepository;

    public function __construct(
        NgoRepositoryInterface $ngoRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->ngoRepository = $ngoRepository;
        $this->userRepository = $userRepository;
    }
    use AddressTrait;
    public function index(Request $request)
    {
        $locale = App::getLocale();

        return $this->userRepository->formattedPermissions(1);

        $tr = DB::table('destinations as d')
            // Join for the destination translations per language
            ->joinSub(function ($query) {
                $query->from('destination_trans as dt')
                    ->select(
                        'destination_id',
                        DB::raw("MAX(CASE WHEN language_name = 'fa' THEN value END) as farsi"),
                        DB::raw("MAX(CASE WHEN language_name = 'en' THEN value END) as english"),
                        DB::raw("MAX(CASE WHEN language_name = 'ps' THEN value END) as pashto")
                    )
                    ->groupBy('destination_id');
            }, 'dt', 'dt.destination_id', '=', 'd.id')
            // Join for the destination type translation
            ->leftJoin('destination_type_trans as dtt', function ($join) use ($locale) {
                $join->on('dtt.destination_type_id', '=', 'd.destination_type_id')
                    ->where('dtt.language_name', $locale);
            })
            ->select(
                'd.id',
                'dt.english',
                'dt.farsi',
                'dt.pashto',
                'd.color',
                'd.destination_type_id',
                'dtt.value as type',
                'd.created_at'
            )
            ->first();


        return [
            "id" => $tr->id,
            "english" => $tr->english,
            "farsi" => $tr->farsi,
            "pashto" => $tr->pashto,
            "color" => $tr->color,
            "type" => ["id" => $tr->destination_type_id, "name" => $tr->type],
            "created_at" => $tr->created_at,
        ];


        return DB::table('destinations as d')
            ->where('d.destination_type_id', DestinationTypeEnum::muqam->value)
            ->join('destination_trans as dt', function ($join) use ($locale) {
                $join->on('dt.destination_id', '=', 'd.id')
                    ->where('dt.language_name', $locale);
            })->select('d.id', "dt.value as name")->get();



        // Define user ID
        $userId = 1;  // Example user ID. You can replace it with the variable as needed.
        $permissions = DB::table('users as u')
            ->where('u.id', $userId)
            ->join('user_permissions as up', 'u.id', '=', 'up.user_id')
            ->join('permissions as p', 'up.permission', '=', 'p.name')
            ->leftJoin('user_permission_subs as ups', 'up.id', '=', 'ups.user_permission_id')
            ->leftJoin('sub_permissions as sp', 'ups.sub_permission_id', '=', 'sp.id')
            ->select(
                'up.id as permission_id',
                'p.name as permission',
                'p.icon',
                'p.priority',
                'up.view',
                'up.visible',
                DB::raw('ups.sub_permission_id as sub_permission_id'),
                DB::raw('ups.add as sub_add'),
                DB::raw('ups.delete as sub_delete'),
                DB::raw('ups.edit as sub_edit')
            )
            ->orderBy('p.priority')  // Optional: If you want to order by priority, else remove
            ->get();

        // Transform data to match desired structure (for example, if you need nested `sub` permissions)
        $formattedPermissions = $permissions->groupBy('permission_id')->map(function ($group) {
            $subPermissions = $group->filter(function ($item) {
                return $item->sub_permission_id !== null; // Filter for permissions that have sub-permissions
            });

            $permission = $group->first(); // Get the first permission for this group

            $permission->view = $permission->view == 1;  // Convert 1 to true, 0 to false
            $permission->visible = $permission->visible == 1;  // Convert 1 to true, 0 to false
            if ($subPermissions->isNotEmpty()) {

                $permission->sub = $subPermissions->map(function ($sub) {
                    return [
                        'id' => $sub->sub_permission_id,
                        'add' => $sub->sub_add == 1,   // Convert 1 to true, 0 to false
                        'delete' => $sub->sub_delete == 1,  // Convert 1 to true, 0 to false
                        'edit' => $sub->sub_edit == 1   // Convert 1 to true, 0 to false
                    ];
                });
            } else {
                $permission->sub = [];
            }
            // If there are no sub-permissions, remove the unwanted fields
            unset($permission->sub_permission_id);
            unset($permission->sub_add);
            unset($permission->sub_delete);
            unset($permission->sub_edit);

            return $permission;
        })->values();


        $user = DB::table('users as u')
            ->where('u.id', $userId)
            ->join('model_job_trans as mjt', 'mjt.model_job_id', '=', 'u.job_id')
            ->join('contacts as c', 'c.id', '=', 'u.contact_id')
            ->join('emails as e', 'e.id', '=', 'u.email_id')
            ->join('roles as r', 'r.id', '=', 'u.role_id')
            ->join('destination_trans as dt', function ($join) use ($locale) {
                $join->on('dt.destination_id', '=', 'u.destination_id')
                    ->where('dt.language_name', $locale);
            })->select(
                'u.id',
                "u.profile",
                "u.status",
                "u.grant_permission",
                'u.full_name',
                'u.username',
                'c.value as contact',
                'u.contact_id',
                'e.value as email',
                'r.name as role_name',
                'u.role_id',
                'dt.value as destination',
                "mjt.value as job",
                "u.created_at"
            )
            ->first();

        // Output the transformed permissions
        return response()->json([
            "user" => [
                "id" => $user->id,
                "full_name" => $user->full_name,
                "username" => $user->username,
                'email' => $user->email,
                "profile" => $user->profile,
                "status" => $user->status,
                "grant" => $user->grant_permission,
                "role" => ["role" => $user->role_id, "name" => $user->role_name],
                'contact' => $user->contact,
                "destination" => $user->destination,
                "job" => $user->job,
                "created_at" => $user->created_at,
            ],
            'permissions' => $formattedPermissions
        ]);

        $type = "Ngo";
        return "App\Models\\{$type}";

        $ngo_id = 4;
        $ngo = Ngo::find($ngo_id);

        $authUser =  DB::table('ngos as n')
            ->where('n.id', $ngo->id)
            ->leftjoin('ngo_statuses as ns', function ($join) {
                $join->on('ns.ngo_id', '=', 'n.id')
                    ->whereRaw('ns.created_at = (select max(ns2.created_at) from ngo_statuses as ns2 where ns2.ngo_id = n.id)');
            })
            ->leftjoin('roles as r', function ($join) {
                $join->on('n.role_id', '=', 'r.id');
            })
            ->select(
                "n.id",
                "n.profile",
                "n.username",
                "n.is_editable",
                "n.created_at",
                "ns.status_type_id",
                "r.id as role_id",
                "r.name as role_name"
            )->first();

        return [
            "id" => $authUser->id,
            "profile" => $authUser->profile,
            "username" => $authUser->username,
            "is_editable" => $authUser->is_editable,
            "created_at" => $authUser->created_at,
            "role" => ["role" => $authUser->role_id, "name" => $authUser->role_name],
            "status_type_id" => $authUser->status_type_id
        ];

        $userPermissions = DB::table('ngos as n')
            ->where('n.id', $ngo_id)
            ->leftJoin('ngo_permissions as np', function ($join) {
                $join->on('np.ngo_id', '=', 'n.id');
            })
            ->join('permissions as p', function ($join) {
                $join->on('p.name', '=', 'np.permission');
            })
            ->select(
                "p.name as permission",
                "p.icon as icon",
                "p.priority as priority",
                "np.view",
                "np.add",
                "np.delete",
                "np.edit",
                "np.id",
            )
            ->orderBy("p.priority")
            ->get();


        return $userPermissions;

        $includes = [StatusTypeEnum::active->value, StatusTypeEnum::blocked->value];
        return $statusesType = DB::table('status_types as st')
            ->whereIn('st.id', $includes)
            ->leftjoin('status_type_trans as stt', function ($join) use ($locale, $includes) {
                $join->on('stt.status_type_id', '=', 'st.id')
                    ->where('stt.language_name', $locale);
            })
            ->select('st.id', 'stt.name')->get();


        $ngoTrans = NgoTran::where('ngo_id', 1)->get();
        return $ngoTrans->where('language_name', "en")->first();

        $query = $this->ngoRepository->ngo(4);  // Start with the base query
        $this->ngoRepository->transJoinLocales($query);
        $ngos = $query->select(
            'nt.vision',
            'nt.mission',
            'nt.general_objective',
            'nt.objective',
            'nt.language_name'
        )->get();

        $result = [];
        foreach ($ngos as $item) {
            $language = $item->language_name;

            if ($language === LanguageEnum::default->value) {
                $result['vision_english'] = $item->vision;
                $result['mission_english'] = $item->mission;
                $result['general_objes_english'] = $item->general_objective;
                $result['objes_in_afg_english'] = $item->objective;
            } elseif ($language === LanguageEnum::farsi->value) {
                $result['vision_farsi'] = $item->vision;
                $result['mission_farsi'] = $item->mission;
                $result['general_objes_farsi'] = $item->general_objective;
                $result['objes_in_afg_farsi'] = $item->objective;
            } else {
                $result['vision_pashto'] = $item->vision;
                $result['mission_pashto'] = $item->mission;
                $result['general_objes_farsi'] = $item->general_objective;
                $result['objes_in_afg_farsi'] = $item->objective;
            }
        }

        return $result;

        $ngo_id = 8;
        $query = $this->ngoRepository->ngo($ngo_id);
        $this->ngoRepository->agreementJoin($query);
        $agreement =  $query->select('ag.id')
            ->first();


        return $document =  Document::join('agreement_documents as agd', 'agd.document_id', 'documents.id')
            ->where('agd.agreement_id', $agreement->id)
            ->join('check_lists as cl', function ($join) {
                $join->on('documents.check_list_id', '=', 'cl.id');
            })
            ->join('check_list_trans as clt', function ($join) use ($locale) {
                $join->on('clt.check_list_id', '=', 'cl.id')
                    ->where('language_name', $locale);
            })
            ->select(
                'documents.path',
                'documents.size',
                'documents.check_list_id as checklist_id',
                'documents.type',
                'documents.actual_name',
                'clt.value as checklist_name',
                'cl.acceptable_extensions',
                'cl.acceptable_mimes'
            )
            ->get();

        $checklistMap = [];

        foreach ($document as $doc) {
            $checklistMap[] = [
                (int) $doc->check_list_id,  // First item in array (checklist ID)
                [
                    'name' => $doc->actual_name,
                    'size' => $doc->size,
                    'check_list_id' => (string) $doc->check_list_id,
                    'extension' => $doc->type,
                    'path' => $doc->path,
                ],
            ];
        }

        return response()->json([
            'message' => __('app_translation.success'),
            'checklistMap' => $checklistMap,

        ], 200, [], JSON_UNESCAPED_UNICODE);

        $addresses = AddressTran::where('address_id', 6)->get();
        return  $addresses->where('language_name', "en")->first();

        $locale = App::getLocale();
        $query = $this->ngoRepository->ngo();  // Start with the base query
        $this->ngoRepository->transJoin($query, $locale)
            ->statusJoin($query)
            ->statusTypeTransJoin($query, $locale)
            ->typeTransJoin($query, $locale)
            ->directorJoin($query)
            ->directorTransJoin($query, $locale)
            ->emailJoin($query)
            ->contactJoin($query);
        $query->select(
            'n.id',
            'n.registration_no',
            'n.date_of_establishment as establishment_date',
            'stt.status_type_id as status_id',
            'stt.name as status',
            'nt.name',
            'ntt.ngo_type_id as type_id',
            'ntt.value as type',
            'e.value as email',
            'c.value as contact',
            'n.created_at'
        );

        return $query->get();





        $query = DB::table('ngos as n')
            ->join('ngo_trans as nt', 'nt.ngo_id', '=', 'n.id')
            ->join('ngo_trans as nt', function ($join) use ($locale) {
                $join->on('nt.ngo_id', '=', 'n.id')
                    ->where('nt.language_name', $locale);
            })
            ->join('ngo_type_trans as ntt', function ($join) use ($locale) {
                $join->on('ntt.ngo_type_id', '=', 'n.ngo_type_id')
                    ->where('ntt.language_name', $locale);
            })
            ->leftjoin('ngo_statuses as ns', function ($join) {
                $join->on('ns.ngo_id', '=', 'n.id')
                    ->whereRaw('ns.created_at = (select max(ns2.created_at) from ngo_statuses as ns2 where ns2.ngo_id = n.id)');
            })
            ->leftJoin('status_type_trans as nstr', 'nstr.status_type_id', '=', 'ns.status_type_id')
            ->where(function ($query) use ($locale) {
                $query->where('nstr.language_name', $locale)
                    ->orWhereNull('nstr.language_name'); // Ensure NGOs with no status are included
            })
            ->leftJoin('emails as e', 'e.id', '=', 'n.email_id')
            ->leftJoin('contacts as c', 'c.id', '=', 'n.contact_id')
            ->orderBy('n.created_at', 'desc')
            ->select(
                'n.id',
                'n.profile',
                'n.abbr',
                'n.registration_no',
                'n.date_of_establishment as establishment_date',
                'nstr.status_type_id as status_id',
                'nstr.name as status',
                'nt.name',
                'ntt.ngo_type_id as type_id',
                'ntt.value as type',
                'e.value as email',
                'c.value as contact',
                'n.created_at'
            );
        $query->orderBy('n.created_at', 'asc');
        return $query->get();




        $query = DB::table('ngos as n')
            ->join('ngo_trans as nt', function ($join) use ($locale) {
                $join->on('nt.ngo_id', '=', 'n.id')
                    ->where('nt.language_name', $locale);
            })
            // ->leftjoin('ngo_statuses as ns', 'ns.ngo_id', '=', 'n.id')
            ->leftjoin('ngo_statuses as ns', function ($join) use ($locale) {
                $join->on('ns.ngo_id', '=', 'n.id')
                    ->whereRaw('ns.created_at = (select max(ns2.created_at) from ngo_statuses as ns2 where ns2.ngo_id = n.id)');
            })
            // ->whereIn('ns.status_type_id', $includedIds)
            ->leftjoin('status_type_trans as nstr', function ($join) use ($locale) {
                $join->on('nstr.status_type_id', '=', 'ns.status_type_id')
                    ->where('nstr.language_name', $locale);
            })
            ->join('ngo_type_trans as ntt', function ($join) use ($locale) {
                $join->on('ntt.ngo_type_id', '=', 'n.ngo_type_id')
                    ->where('ntt.language_name', $locale);
            })
            ->join('directors as dir', function ($join) {
                $join->on('dir.ngo_id', '=', 'n.id')
                    ->where('dir.is_active', true);
            })
            ->join('director_trans as dirt', function ($join) use ($locale) {
                $join->on('dir.id', '=', 'dirt.director_id')
                    ->where('dirt.language_name', $locale);
            })
            ->select(
                'n.id',
                'n.abbr',
                'nstr.name as status',
                'nt.name',
                'ntt.value as type',
                'dirt.name as director',
            );

        return $query->get();

        // Joining necessary tables to fetch the NGO data
        $ngo_id = 2;
        $director = DB::table('directors as d')
            ->where('d.ngo_id', $ngo_id)
            ->join('director_trans as dt', function ($join) use ($locale) {
                $join->on('dt.director_id', '=', 'd.id')
                    ->where('dt.language_name', '=', $locale);
            })
            ->join('contacts as c', 'd.contact_id', '=', 'c.id')
            ->join('emails as e', 'd.email_id', '=', 'e.id')
            ->select(
                'd.id',
                'd.is_active',
                'nt.name',
                'dt.name as director',
                'ntt.value as type'
            )
            ->get();
        return $director;
        $locale = App::getLocale();
        // Joining necessary tables to fetch the NGO data
        $directors = DB::table('directors as d')
            ->where('d.ngo_id', $ngo_id)
            ->join('director_trans as dt', 'd.id', '=', 'dt.director_id')
            ->join('nid_type_trans as ntt', 'd.nid_type_id', '=', 'ntt.nid_type_id')
            ->join('contacts as c', 'd.contact_id', '=', 'c.id')
            ->join('emails as e', 'd.email_id', '=', 'e.id')
            ->join('genders as g', 'd.gender_id', '=', 'g.id')
            ->join('addresses as ad', 'd.address_id', '=', 'ad.id')
            ->join('address_trans as adt', 'ad.id', '=', 'adt.address_id')
            ->select(
                'd.id',
                'd.nid_no as nid',
                'c.value as contact',
                'e.value as email',
                // Language-specific name and last name
                DB::raw("MAX(CASE WHEN dt.language_name = 'en' THEN dt.name END) as name_english"),
                DB::raw("MAX(CASE WHEN dt.language_name = 'fa' THEN dt.name END) as name_farsi"),
                DB::raw("MAX(CASE WHEN dt.language_name = 'ps' THEN dt.name END) as name_pashto"),
                DB::raw("MAX(CASE WHEN adt.language_name = 'en' THEN adt.area END) as area_english"),
                DB::raw("MAX(CASE WHEN adt.language_name = 'fa' THEN adt.area END) as area_farsi"),
                DB::raw("MAX(CASE WHEN adt.language_name = 'ps' THEN adt.area END) as area_pashto"),
                DB::raw("MAX(CASE WHEN dt.language_name = 'en' THEN dt.last_name END) as surname_english"),
                DB::raw("MAX(CASE WHEN dt.language_name = 'fa' THEN dt.last_name END) as surname_farsi"),
                DB::raw("MAX(CASE WHEN dt.language_name = 'ps' THEN dt.last_name END) as surname_pashto"),
                // Gender and identity fields
                'g.id as gender_id',
                'g.name_en as gender_name_en',
                'g.name_fa as gender_name_fa',
                'g.name_ps as gender_name_ps',
                'ntt.id as identity_type_id',
                'ntt.value as identity_type_value'
            )
            ->groupBy(
                'd.id',
                'g.name_en',
                'g.name_ps',
                'g.name_fa',
                'd.nid_no',
                'c.value',
                'e.value',
                'g.id',
                'ntt.id',
                'ntt.value'
            )
            ->get();

        // After the query, format the response in the controller
        $directors = $directors->map(function ($director) use ($locale) {
            // Select the appropriate gender name based on the locale
            $genderField = 'gender_name_' . $locale;
            $director->gender = [
                'name' => $director->{$genderField} ?? $director->gender_name_en, // fallback to English if locale is missing
                'id' => $director->gender_id
            ];

            // Format identity type
            $director->identity_type = [
                'name' => $director->identity_type_value,
                'id' => $director->identity_type_id
            ];

            // Clean up unnecessary fields
            unset($director->gender_name_en, $director->gender_name_fa, $director->gender_name_ps, $director->gender_id);
            unset($director->identity_type_value, $director->identity_type_id);

            return $director;
        });

        return response()->json($directors);




        $path = storage_path() . "/app/temp/c9424391-b967-4dbf-a3c3-747f6d8382a2.pdf";
        return dd(file_exists($path));
        return PendingTaskContent::where('pending_task_id', 2)
            ->select('content', 'id')
            ->orderBy('id', 'desc')
            ->first();
        $locale = App::getLocale();
        $query = DB::table('staff as s')
            ->where('staff_type_id', StaffEnum::manager->value)
            ->join('staff_trans as st', function ($join) use ($locale) {
                $join->on('st.staff_id', '=', 's.id')
                    ->where('st.language_name', '=', $locale);
            })
            ->select(
                's.id',
                's.contact',
                's.email',
                's.profile as picture',
                'st.name'
            )
            ->first();
        return $query;


        $ngo_id = 1;
        return DB::table('ngos as n')
            ->join('ngo_type_trans as ntt', 'ntt.ngo_type_id', '=', 'n.ngo_type_id')  // Join the ngo_type_trans table
            ->leftJoin('addresses as ad', 'ad.id', '=', 'n.address_id')
            ->leftJoin('address_trans as adt', function ($join) use ($locale) {
                $join->on('ad.id', '=', 'adt.address_id')
                    ->where('adt.language_name', '=', $locale);
            })
            ->leftJoin('emails as em', 'em.id', '=', 'n.email_id')
            ->leftJoin('contacts as c', 'c.id', '=', 'n.contact_id')
            ->where('n.id', $ngo_id)
            ->select(
                'n.id',
                'em.value',
                'c.value',
                DB::raw("MAX(CASE WHEN ntt.language_name = 'en' THEN ntt.value END) as name_english"),  // English translation
                DB::raw("MAX(CASE WHEN ntt.language_name = 'fa' THEN ntt.value END) as name_farsi"),   // Farsi translation
                DB::raw("MAX(CASE WHEN ntt.language_name = 'ps' THEN ntt.value END) as name_pashto")   // Pashto translation
            )
            ->groupBy('n.id', 'em.value', 'c.value')
            ->first();


        return CheckList::join('check_list_trans as ct', 'ct.check_list_id', '=', 'check_lists.id')
            ->where('ct.language_name', $locale)
            ->select('ct.value as name', 'check_lists.id', 'check_lists.file_extensions', 'check_lists.description')
            ->orderBy('check_lists.id', 'desc')
            ->get();


        return   $this->getCompleteAddress(1, 'fa');
        $lang = 'en';
        $id = 1;

        $irdDirector = Staff::with([
            'staffTran' => function ($query) use ($lang) {
                $query->select('staff_id', 'name', 'last_name')->where('language_name', $lang);
            }
        ])->select('id')->where('staff_type_id', StaffEnum::director->value)->first();


        return $irdDirector->staffTran[0]->name . '  ' . $irdDirector->staffTran[0]->last_name;

        $lang = 'en';
        $ngo = Ngo::with(
            [
                'ngoTrans' => function ($query) use ($lang) {
                    $query->select('ngo_id', 'name', 'vision', 'mission', 'general_objective', 'objective')->where('language_name', $lang);
                },
                'email:id,value',
                'contact:id,value',


            ]

        )->select(
            'id',
            'email_id',
            'contact_id',
            'address_id',
            'abbr',
            'registration_no',
            'date_of_establishment',
            'moe_registration_no',

        )->where('id', 1)->first();

        return    $this->getCompleteAddress($ngo->address_id, 'en');

        dd($query->toSql(), $query->getBindings());
        // ->get();

        // ->join('')
        $query = DB::table('news AS n')
            // Join for news translations (title, contents)
            ->join('news_trans AS ntr', function ($join) use ($locale) {
                $join->on('ntr.news_id', '=', 'n.id')
                    ->where('ntr.language_name', '=', $locale); // Filter by language
            })
            // Join for news type translations
            ->join('news_type_trans AS ntt', function ($join) use ($locale) {
                $join->on('ntt.news_type_id', '=', 'n.news_type_id')
                    ->where('ntt.language_name', '=', $locale); // Filter by language
            })
            // Join for priority translations
            ->join('priority_trans AS pt', function ($join) use ($locale) {
                $join->on('pt.priority_id', '=', 'n.priority_id')
                    ->where('pt.language_name', '=', $locale); // Filter by language
            })
            // Join for user (assuming the `users` table has the `username` field)
            ->join('users AS u', 'u.id', '=', 'n.user_id')
            // Left join for documents (to get all documents related to the news)
            ->leftJoin('news_documents AS nd', 'nd.news_id', '=', 'n.id')
            // Select required fields from all tables
            ->select(
                'n.id',
                'n.visible',
                'n.date',
                'n.visibility_date',
                'n.news_type_id',
                'ntt.value AS news_type',
                'n.priority_id',
                'pt.value AS priority',
                'u.username AS user',
                'ntr.title',
                'ntr.contents',
                'nd.url AS image'  // Assuming you want the first image URL
            )
            // Get the data
            ->get();

        return $query;

        // $query  = DB::table('news AS n')
        //     ->leftJoin('news_trans AS ntr', function ($join) use ($locale) {
        //         $join->on('ntr.news_id', '=', 'n.id')
        //             ->where('ntr.language_name', '=', $locale);
        //     })
        //     ->leftJoin('news_type_trans AS ntt', function ($join) use ($locale) {
        //         $join->on('ntt.news_type_id', '=', 'n.news_type_id')
        //             ->where('ntt.language_name', '=', $locale);
        //     })
        //     ->leftJoin('priority_trans AS pt', function ($join) use ($locale) {
        //         $join->on('pt.priority_id', '=', 'n.priority_id')
        //             ->where('pt.language_name', '=', $locale);
        //     })
        //     ->leftJoin('users AS u', function ($join) {
        //         $join->on('u.id', '=', 'n.user_id');
        //     })
        //     ->leftJoin('news_documents AS nd', 'nd.news_id', '=', 'n.id')
        //     ->distinct()
        //     ->select(
        //         // 'n.id',
        //         // "n.visible",
        //         // "date",
        //         // "visibility_date",
        //         // 'n.news_type_id',
        //         // 'ntt.value AS news_type',
        //         // 'n.priority_id',
        //         // 'pt.value AS priority',
        //         // 'u.username AS user',
        //         // 'ntr.title',
        //         // 'ntr.contents',
        //         // 'nd.url as image',
        //     )
        //     ->get();


        return $query;


        $ngoId = 2;
        $user = DB::table('ngos AS n')
            ->where('n.id', '=', $ngoId)
            ->join('ngo_trans AS ntr', function ($join) use ($locale) {
                $join->on('ntr.ngo_id', '=', 'n.id');
                // ->where('ntr.language_name', '=', $locale);
            })
            ->join('ngo_statuses AS ns', function ($join) {
                $join->on('ns.ngo_id', '=', 'n.id');
            })
            ->join('status_type_trans AS nst', function ($join) use ($locale) {
                $join->on('nst.status_type_id', '=', 'ns.status_type_id')
                    ->where('nst.language_name', '=', $locale);
            })
            ->join('ngo_types AS nt', 'n.ngo_type_id', '=', 'nt.id')
            ->join('ngo_type_trans AS ntt', function ($join) use ($locale) {
                $join->on('ntt.ngo_type_id', '=', 'nt.id')
                    ->where('nst.language_name', '=', $locale);
            })
            ->join('emails as e', 'n.email_id', '=', 'e.id')
            ->join('contacts as c', 'n.contact_id', '=', 'c.id')
            ->join('roles as r', 'n.role_id', '=', 'r.id')
            ->select(
                'n.id',
                'n.abbr',
                'n.registration_no',
                'n.address_id',
                'n.username',
                'ntr.name AS name',
                'ns.id AS status_id',
                'nst.name AS status_name',
                'n.ngo_type_id',
                'ntt.value AS ngo_type_name',
                'n.role_id',
                'r.name AS role',
                'n.email_id',
                'e.value AS email',
                'n.contact_id',
                'c.value AS contact',
            )
            ->first();
        return $user;
        $ngos = DB::select("
        SELECT
         COUNT(*) AS count,
            (SELECT COUNT(*) FROM ngos WHERE DATE(created_at) = CURDATE()) AS todayCount,
            (SELECT COUNT(*) FROM ngos n JOIN ngo_statuses ns ON n.id = ns.ngo_id WHERE ns.status_type_id = ?) AS activeCount,
         (SELECT COUNT(*) FROM ngos n JOIN ngo_statuses ns ON n.id = ns.ngo_id WHERE ns.status_type_id = ?) AS unRegisteredCount
        FROM ngos
    ", [StatusTypeEnum::active->value, StatusTypeEnum::unregistered->value]);
        return $ngos;
        return $statistics[0]->todayCount;

        $users = User::with([
            'contact' => function ($query) {
                $query->select('id', 'value'); // Load contact value
            },
            'email' => function ($query) {
                $query->select('id', 'value'); // Load email value
            },
            'destinationThrough' => function ($query) {
                $query->select('translable_id', 'value as destination')
                    ->where('translable_type', 'App\\Models\\Destination')
                    ->where('language_name', 'fa')
                    ->groupBy('translable_id');
            },
            'jobThrough' => function ($query) {
                $query->select('translable_id', 'value as job')
                    ->where('translable_type', 'App\\Models\\ModelJob')
                    ->where('language_name', 'fa')
                    ->groupBy('translable_id');
            }
        ])->get();
    }
}
