<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Ngo;
use App\Models\News;
use App\Models\Role;
use App\Models\User;

use App\Models\Email;
use App\Models\Staff;
use App\Models\Gender;

use App\Enums\RoleEnum;
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
use App\Models\CheckListType;
use Sway\Models\RefreshToken;
use App\Models\RolePermission;
use App\Models\StatusTypeTran;
use App\Models\UserPermission;
use App\Enums\CheckListTypeEnum;
use App\Enums\SubPermissionEnum;
use App\Models\RolePermissionSub;
use App\Enums\DestinationTypeEnum;
use App\Enums\Type\StatusTypeEnum;
use App\Models\PendingTaskContent;
use Illuminate\Support\Facades\DB;
use App\Models\PendingTaskDocument;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Traits\Address\AddressTrait;
use function Laravel\Prompts\select;

use App\Enums\CheckList\CheckListEnum;
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

        $exclude = [
            CheckListEnum::ngo_representor_letter->value,
            CheckListEnum::ngo_register_form_en->value,
            CheckListEnum::ngo_register_form_fa->value,
            CheckListEnum::ngo_register_form_ps->value,
        ];
        return $documents = PendingTaskDocument::join('check_lists', 'check_lists.id', 'pending_task_documents.check_list_id')
            ->select('size', 'path', 'acceptable_mimes', 'check_list_id', 'actual_name', 'extension')
            ->where('pending_task_id', 3)
            // ->whereNotIn('check_lists.id', $exclude)
            ->get();

        $id = 9;
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
                'clttt.id as type_id',
                'clt_farsi.value as name_farsi', // Farsi translation
                'clt_english.value as name_english', // English translation
                'clt_pashto.value as name_pashto' // Pashto translation
            )
            ->orderBy('cltt.id')
            ->first();

        // Check if acceptable_mimes and acceptable_extensions are present
        if ($checklist) {
            // Exploding the comma-separated strings into arrays
            $acceptableMimes = explode(',', $checklist->acceptable_mimes);
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
            $checklist->extensions = $combined;
        }
        $checklist->status = (bool) $checklist->status;
        // Remove unwanted data from the checklist
        unset($checklist->acceptable_mimes);
        unset($checklist->acceptable_extensions);

        $convertedMimes = [];
        $convertedExtensions = [];
        foreach ($checklist->extensions as $extension) {
            $convertedMimes[] = $extension['frontEndName'];
            $convertedExtensions[] = $extension['name'];
        }

        $checklist->acceptable_mimes = implode(',', $convertedMimes);
        $checklist->acceptable_extensions = implode(',', $convertedExtensions);

        return $checklist;

        return [
            "id" => $checklist->id,
            "name_farsi" => $checklist->name_farsi,
            "name_english" => $checklist->name_english,
            "name_pashto" => $checklist->name_pashto,
            "detail" => $checklist->description,
            "status" => (bool) $checklist->active,
            "type" => [
                "id" => $checklist->type_id,
                "name" => $checklist->type,
            ],
            "extensions" => []
        ];



        DB::table('check_list_types as clt')
            ->join('check_list_type_trans as cltt', 'cltt.check_list_type_id', '=', 'clt.id')
            ->where('cltt.language_name', $locale)
            ->select(
                'cltt.value as name',
                'clt.id',
            )
            ->orderBy('cl.id')
            ->get();

        return UserPermission::find(28)
            ->select('id', 'edit', 'delete', 'add', 'view')->first();

        $user_id = 4;
        $role_id = 4;

        $rolePermissions = DB::table('role_permissions as rp')
            ->where('rp.role', '=', $role_id)
            ->join('permissions as p', 'rp.permission', '=', 'p.name')
            ->leftJoin('role_permission_subs as rps', 'rps.role_permission_id', '=', 'rp.id')
            ->leftJoin('sub_permissions as sp', 'rps.sub_permission_id', '=', 'sp.id')
            ->select(
                'p.name as permission',
                'sp.name',
                'p.priority',
                "rps.sub_permission_id",
                'sp.name'
            )
            ->orderBy('p.priority')  // Optional: If you want to order by priority, else remove
            ->get();

        $formattedRolePermissions = $rolePermissions->groupBy('permission')->map(function ($group) {
            $subPermissions = $group->filter(function ($item) {
                return $item->sub_permission_id !== null; // Filter for permissions that have sub-permissions
            });

            $permission = $group->first(); // Get the first permission for this group

            $permission->view = false;
            $permission->add = false;
            $permission->delete = false;
            $permission->edit = false;
            if ($subPermissions->isNotEmpty()) {

                $permission->sub = $subPermissions->map(function ($sub) {
                    return [
                        'id' => $sub->sub_permission_id,
                        'name' => $sub->name,
                        'add' => false,
                        'delete' => false,
                        'edit' => false,
                        'view' => false
                    ];
                });
            } else {
                $permission->sub = [];
            }
            // // If there are no sub-permissions, remove the unwanted fields
            unset($permission->sub_permission_id);
            unset($permission->name);
            // unset($permission->sub_delete);
            // unset($permission->sub_edit);

            return $permission;
        })->values();

        $permissions = DB::table('users as u')
            ->where('u.id', $user_id)
            ->join('user_permissions as up', 'u.id', '=', 'up.user_id')
            ->join('permissions as p', 'up.permission', '=', 'p.name')
            ->leftJoin('user_permission_subs as ups', 'up.id', '=', 'ups.user_permission_id')
            ->leftJoin('sub_permissions as sp', 'ups.sub_permission_id', '=', 'sp.id')
            ->select(
                'up.id as user_permission_id',
                'p.name as permission',
                'sp.name',
                'p.priority',
                'up.view',
                'up.edit',
                'up.delete',
                'up.add',
                'ups.sub_permission_id as sub_permission_id',
                'ups.add as sub_add',
                'ups.delete as sub_delete',
                'ups.edit as sub_edit',
                'ups.view as sub_view',
            )
            ->orderBy('p.priority')  // Optional: If you want to order by priority, else remove
            ->get();

        // Transform data to match desired structure (for example, if you need nested `sub` permissions)
        $formattedPermissions = $permissions->groupBy('user_permission_id')->map(function ($group) {
            $subPermissions = $group->filter(function ($item) {
                return $item->sub_permission_id !== null; // Filter for permissions that have sub-permissions
            });

            $permission = $group->first(); // Get the first permission for this group

            $permission->view = (bool) $permission->view;
            $permission->edit = (bool) $permission->edit;
            $permission->delete = (bool) $permission->delete;
            $permission->add = (bool) $permission->add;
            if ($subPermissions->isNotEmpty()) {
                $permission->sub = $subPermissions->map(function ($sub) {
                    return [
                        'id' => $sub->sub_permission_id,
                        'name' =>  $sub->name,
                        'add' => (bool) $sub->sub_add,
                        'delete' => (bool) $sub->sub_delete,
                        'edit' => (bool) $sub->sub_edit,
                        'view' => (bool) $sub->sub_view,
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
            unset($permission->sub_view);
            unset($permission->name);
            unset($permission->user_permission_id);

            return $permission;
        })->values();

        // Merger permissions
        $formattedRolePermissions->each(function ($permission) use (&$formattedPermissions) {
            $perm = $formattedPermissions->where("permission", $permission->permission)->first();
            // 1. If permission not found set
            if (!$perm) {
                $formattedPermissions->push($permission);
            } else {
                // 2. If permission found check for any missing Sub Permissions
                $permSub = $perm->sub;
                foreach ($permission->sub as $subPermission) {
                    $subExists = false;
                    for ($i = 0; $i < count($permSub); $i++) {
                        $sub = $permSub[$i];
                        if ($sub['id'] == $subPermission['id']) {
                            $subExists = true;
                            break;
                        }
                    }
                    if (!$subExists) {
                        $perm->sub[] = $subPermission;
                    }
                }
            }
        });
        return $formattedPermissions;

        return $formattedPermissions->where("permission", $permission->permission);

        // /

        $locale = App::getLocale();
        $permissions = [
            ['id' => 21, 'name' => 'language', 'edit' => false, 'delete' => false, 'add' => true, 'view' => true],
            ['id' => 22, 'name' => 'job', 'edit' => true, 'delete' => true, 'add' => true, 'view' => true],
            ['id' => 23, 'name' => 'destination', 'edit' => true, 'delete' => true, 'add' => true, 'view' => true],
        ];
        $user_id = RoleEnum::debugger->value;
        $role_id = RoleEnum::debugger->value;
        $permissionName = PermissionEnum::settings->value;
        // 1. Check Permission in Role Table
        $rolePermission = DB::table('role_permissions as rp')->where('rp.role', $role_id)
            ->where("rp.permission", $permissionName)
            ->join('role_permission_subs as rps', 'rps.role_permission_id', '=', 'rp.id')
            ->select('rps.role_permission_id', 'rps.sub_permission_id')
            ->get();

        if ($rolePermission && $rolePermission->isNotEmpty()) {
            // 2. Check Request permission matches the role Permission
            foreach ($permissions as $permission) {
                $per = $rolePermission->where('sub_permission_id', $permission['id'])
                    ->first();

                if (!$per) {
                    // User try to assign unauthorize permission
                    return response()->json([
                        'message' => __('app_translation.unauthorized_role_per')
                    ], 403, [], JSON_UNESCAPED_UNICODE);
                }
            }
            // 3. Get user Permission
            $userPermissions = UserPermission::where('user_permissions.user_id', $user_id)
                ->where("user_permissions.permission", $permissionName)
                ->join('user_permission_subs as ups', 'ups.user_permission_id', '=', 'user_permissions.id')
                ->select('ups.user_permission_id', 'ups.sub_permission_id', 'ups.id')
                ->get();

            // 3.1. PermissionName exist then update SubPermissions
            if ($userPermissions && $userPermissions->isNotEmpty()) {

                foreach ($permissions as $subPermission) {
                    $per = $userPermissions->where('sub_permission_id', $subPermission['id'])
                        ->first();

                    // 3.1. SubPermissions exist then update
                    if ($per) {
                        if ($subPermission['main'] == true) {
                            // It is UserPermission value
                            $user_permission_id = $userPermissions->first()->user_permission_id;
                            DB::table('user_permission_subs')
                                ->where('id', $user_permission_id)
                                ->update([
                                    'edit' => $subPermission['edit'],
                                    'delete' => $subPermission['delete'],
                                    'add' => $subPermission['add'],
                                    'view' => $subPermission['view'],
                                ]);
                        }
                        DB::table('user_permission_subs')
                            ->where('id', $per->id)
                            ->update([
                                'edit' => $subPermission['edit'],
                                'delete' => $subPermission['delete'],
                                'add' => $subPermission['add'],
                                'view' => $subPermission['view'],
                            ]);
                    } else {
                        // 3.1. SubPermissions doen't exist hence newly added to system
                        $user_permission_id = $userPermissions->first()->user_permission_id;
                        DB::table('user_permission_subs')->insert([
                            'user_permission_id' => $user_permission_id,  // Ensure this is set correctly
                            'sub_permission_id' => $subPermission['id'],
                            'edit' => $subPermission['edit'],
                            'delete' => $subPermission['delete'],
                            'add' => $subPermission['add'],
                            'view' => $subPermission['view'],
                        ]);
                    }
                }
            } else {
                // 3.2 PermissionName does not exist hence newly added to system hence
                // Permission and RolePermissions
                $userPermission = UserPermission::create([
                    "view" => false,
                    "visible" => false,
                    "user_id" => $user_id,
                    "permission " => $permissionName,
                ]);
                // 3.3 add all SubPermissions
                foreach ($permissions as $subPermission) {
                    if ($subPermission['main'] == true) {
                        // It is UserPermission value
                        $userPermission->edit =  $subPermission['edit'];
                        $userPermission->delete =  $subPermission['delete'];
                        $userPermission->add =  $subPermission['add'];
                        $userPermission->view =  $subPermission['view'];
                        $userPermission->save();
                    }
                    // 3.1. SubPermissions exist then update
                    DB::table('user_permission_subs')->insert([
                        'user_permission_id' => $userPermission->id,  // Ensure this is set correctly
                        'sub_permission_id' => $subPermission['id'],
                        'edit' => $subPermission['edit'],
                        'delete' => $subPermission['delete'],
                        'add' => $subPermission['add'],
                        'view' => $subPermission['view'],
                    ]);
                }
            }

            return "Success";
        } else {
            // Handle the case when the result is null or empty
            return response()->json([
                'message' => __('app_translation.unauthorized')
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }

        return "Success";
        // UserPermission::where("user_id",$user_id);
        return DB::table('role_permissions as rp')
            ->where('rp.role', $role_id)
            ->join('role_permission_subs as rps', function ($join) {
                $join->on('rps.role_permission_id', '=', 'rp.id');
            })
            ->leftJoin('user_permissions as up', function ($join) use ($user_id) {
                $join->on('up.sub_permission_id', 'rps.sub_permission_id')
                    ->where('up.user_id');
            })
            ->leftJoin('user_permission_subs as ups', function ($join) use ($user_id) {
                $join->on('ups.user_permission_id', 'up.id');
            })
            ->get();


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
