<?php

namespace App\Http\Controllers\api\template\task;

use Illuminate\Http\Request;
use App\Enums\Type\TaskTypeEnum;
use App\Http\Controllers\Controller;
use App\Repositories\Task\PendingTaskRepositoryInterface;

class PendingTaskController extends Controller
{
    protected $pendingTaskRepository;

    public function __construct(PendingTaskRepositoryInterface $pendingTaskRepository)
    {
        $this->pendingTaskRepository = $pendingTaskRepository;
    }
    public function storeNgoRegisterTask(Request $request, $id)
    {
        $request->validate([
            'contents' => 'required|string',
            'step' => 'required|string',
        ]);

        $this->pendingTaskRepository->storeTask(
            $request->user(),
            TaskTypeEnum::ngo_registeration,
            $id,
            $request->step,
            $request->contents
        );
        return response()->json(
            [
                'message' => __('app_translation.success'),
            ],
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }
}
