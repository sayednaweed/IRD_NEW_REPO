<?php

namespace App\Http\Controllers\api\app\projects;

use App\Enums\LanguageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\app\project\ProjectBudgetUpdateRequest;
use App\Http\Requests\app\project\ProjectBudgetUpsateRequest;
use App\Http\Requests\app\project\ProjectDetailsRequest;
use App\Models\Project;
use App\Models\ProjectDetail;
use App\Models\ProjectDetailTran;
use App\Models\ProjectDistrictDetail;
use App\Models\ProjectDistrictDetailTran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectUpdateController extends Controller
{
    //



    public function details(ProjectDetailsRequest $request)
    {
        $map = [
            'preamble'           => 'preamble',
            'health_experience'  => 'exper_in_health',
            'goals'              => 'goals',
            'objectives'         => 'objective',
            'expected_outcome'   => 'expected_outcome',
            'expected_impact'    => 'expected_impact',
            'subject'            => 'subject',
            'main_activities'    => 'main_activities',
            'introduction'       => 'project_intro',
            'operational_plan'   => 'action_plan',
            'mission'            => 'mission',
            'vission'            => 'vission',
            'terminologies'      => 'abbreviat',
            'name'               => 'project_name',
            'organization_senior_manangement' => 'organization_sen_man',
            'project_structure'  => 'project_structure',
        ];

        // Build validation rules dynamically

        foreach ($map as $aliasBase) {
            $rules["{$aliasBase}_english"] = 'required|string|min:5';
            $rules["{$aliasBase}_farsi"]   = 'required|string|min:5';
            $rules["{$aliasBase}_pashto"]  = 'required|string|min:5';
        }

        $validated = $request->validated();
        // Loop through languages and update records
        foreach (
            [
                LanguageEnum::default->value => 'english',
                LanguageEnum::farsi->value   => 'farsi',
                LanguageEnum::pashto->value  => 'pashto',
            ] as $langValue => $langName
        ) {

            $updateData = [];
            foreach ($map as $dbColumn => $aliasBase) {
                $updateData[$dbColumn] = $validated["{$aliasBase}_{$langName}"] ?? '';
            }

            DB::table('project_trans')
                ->where('project_id', $validated['id'])
                ->where('language_name', $langValue)
                ->update($updateData);
        }

        return response()->json(['message' => __('app_translation.success')]);
    }


    public function budget(ProjectBudgetUpdateRequest $request)
    {
        $projectId = $request->input('id');
        $languages = LanguageEnum::LANGUAGES;

        DB::beginTransaction();

        // -------------------------
        // 1. Update Project
        // -------------------------
        Project::where('id', $projectId)->update([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_budget' => $request->budget,
            'donor_id' => $request->donor['id'] ?? null,
            'donor_registration_no' => $request->donor_register_no ?? '',
            'currency_id' => $request->currency['id'] ?? null,
        ]);

        // -------------------------
        // 2. Prepare province IDs
        // -------------------------
        $provinceIds = collect($request->centers_list)->pluck('province.id')->filter()->toArray();

        // -------------------------
        // 3. Delete removed project details
        // -------------------------
        $removedDetails = ProjectDetail::where('project_id', $projectId)
            ->whereNotIn('province_id', $provinceIds)
            ->get();

        foreach ($removedDetails as $detail) {
            ProjectDetailTran::where('project_detail_id', $detail->id)->delete();
            $districts = ProjectDistrictDetail::where('project_detail_id', $detail->id)->get();
            foreach ($districts as $district) {
                ProjectDistrictDetailTran::where('project_district_detail_id', $district->id)->delete();
            }
            ProjectDistrictDetail::where('project_detail_id', $detail->id)->delete();
            $detail->delete();
        }

        // -------------------------
        // 4. Update or create centers
        // -------------------------
        foreach ($request->centers_list as $center) {
            $provinceId = $center['province']['id'] ?? null;

            $detail = ProjectDetail::updateOrCreate(
                [
                    'project_id' => $projectId,
                    'province_id' => $provinceId,
                ],
                [
                    'budget' => $center['budget'] ?? 0,
                    'direct_beneficiaries' => $center['direct_benefi'] ?? 0,
                    'in_direct_beneficiaries' => $center['in_direct_benefi'] ?? 0,
                ]
            );

            // -------------------------
            // Translations
            // -------------------------
            foreach ($languages as $code => $lang) {
                ProjectDetailTran::updateOrCreate(
                    [
                        'project_detail_id' => $detail->id,
                        'language_name' => $code,
                    ],
                    [
                        'health_center' => json_encode($center["health_centers_$lang"] ?? ""),
                        'address' => $center["address_$lang"] ?? "",
                        'health_worker' => json_encode($center["health_worker_$lang"] ?? ""),
                        'managment_worker' => json_encode($center["fin_admin_employees_$lang"] ?? ""),
                    ]
                );
            }

            Log::info($request);
            // -------------------------
            // Delete old districts & villages
            // -------------------------
            $oldDistricts = ProjectDistrictDetail::where('project_detail_id', $detail->id)->get();
            foreach ($oldDistricts as $district) {
                ProjectDistrictDetailTran::where('project_district_detail_id', $district->id)->delete();
            }
            ProjectDistrictDetail::where('project_detail_id', $detail->id)->delete();


            // -------------------------
            // Insert new districts & villages

            // -------------------------
            foreach ($center['district'] ?? [] as $district) {
                $districtDetail = ProjectDistrictDetail::create([
                    'project_detail_id' => $detail->id,
                    'district_id' => $district['id'] ?? null,
                ]);

                foreach (array_values($center['villages'] ?? []) as $villageGroup) {
                    if (($villageGroup['district_id'] ?? null) === ($district['id'] ?? null)) {

                        foreach ($languages as $code => $lang) {

                            ProjectDistrictDetailTran::updateOrCreate(
                                [
                                    'project_district_detail_id' => $districtDetail->id,
                                    'language_name' => $code,
                                ],
                                [
                                    'villages' => json_encode($villageGroup["village_$lang"] ?? []),
                                ]
                            );
                        }
                    }
                }
            }
            return response()->json([
                'message' => __('app_translation.contact_exist'),
            ], 409, [], JSON_UNESCAPED_UNICODE);
        }

        DB::commit();

        return response()->json(['message' => 'Budget updated successfully']);
    }
}
