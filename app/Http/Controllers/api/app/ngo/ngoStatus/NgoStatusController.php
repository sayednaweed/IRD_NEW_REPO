<?php

namespace App\Http\Controllers\api\app\ngo\ngoStatus;

use App\Enums\Type\StatusTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\NgoStatus;
use App\Models\StatusType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class NgoStatusController extends Controller
{
    //

    public function statuses()
    {

        $locale = App::getLocale();
        $status =   StatusType::leftjoin('status_type_trans as stt', 'stt.status_type_id', '=', 'status_types.id')->where('stt.language_name', $locale)->select('status_type_id as id', 'name')->get();

        return response()->json([
            'message' => __('app_translation.success'),
            'ngo' => $status,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function ngoStatus($id)
    {
        $locale = App::getLocale();

        // return the ngo status 
        $status =    NgoStatus::leftjoin('status_type_trans as stt', 'stt.status_type_id', '=', 'ngo_statuses.status_type_id')->where('language_name', $locale)
            ->orderByDesc('ngo_statuses.created_at')->limit(1)
            ->where('ngo_statuses.ngo_id', $id)->select('stt.name', 'stt.status_type_id as name')->get();


        return response()->json([
            'message' => __('app_translation.success'),
            'ngo' => $status,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function ngoStatuses($id)
    {
        $locale = App::getLocale();

        // return the ngo status 
        $status =    NgoStatus::leftjoin('status_type_trans as stt', 'stt.status_type_id', '=', 'ngo_statuses.status_type_id')->where('language_name', $locale)

            ->where('ngo_statuses.ngo_id', $id)->select(
                'stt.name',
                'stt.status_type_id as id',
                'ngo_statuses.comment'
            )->get();


        return response()->json([
            'message' => __('app_translation.success'),
            'ngo' => $status,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function changeNgoStatus(Request $request, $id)
    {
        // locale = App::getLocale();

        // return the ngo status 
        $status =    NgoStatus::leftjoin('status_type_trans as stt', 'stt.status_type_id', '=', 'ngo_statuses.status_type_id')
            ->orderByDesc('ngo_statuses.created_at')->limit(1)
            ->where('ngo_statuses.ngo_id', $id)->select('stt.status_type_id')->get();

        if ($status->status_type_id == StatusTypeEnum::active) {
            NgoStatus::create([
                'ngo_id' => $id,
                'status_type_id' => StatusTypeEnum::blocked,
                'comment' => $request->comment
            ]);
            return response()->json([
                'message' => __('app_translation.success'),
                'ngo' => __('app_translation.account_is_block'),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }
        if ($status->status_type_id == StatusTypeEnum::blocked) {
            NgoStatus::create([
                'ngo_id' => $id,
                'status_type_id' => StatusTypeEnum::active,
                'comment' => $request->comment
            ]);
            return response()->json([
                'message' => __('app_translation.success'),
                'ngo' => __('app_translation.account_is_lock'),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }
        return response()->json([
            'message' => __('app_translation.success'),
            'ngo' => __('app_translation.'),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
