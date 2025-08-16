
<?php

use App\Enums\PermissionEnum;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\app\projects\ProjectController;
use App\Http\Controllers\api\app\projects\ProjectEditController;
use App\Http\Controllers\api\app\projects\ProjectStoreController;
use App\Http\Controllers\api\app\projects\ProjectUpdateController;

Route::get('/projects/details/{id}', [ProjectEditController::class, 'checklist']);

Route::get('/projects', [ProjectController::class, 'index']);
Route::prefix('v1')->middleware(["multiAuthorized:" . 'ngo:api,user:api'])->group(function () {
    Route::get('/projects/register-form/{id}', [ProjectController::class, 'startRegisterForm'])->middleware(["userHasMainAddPermission:" . PermissionEnum::projects->value]);
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectStoreController::class, 'store']);
    Route::post('/projects/pending-task/{id}', [ProjectController::class, 'destroyPendingTask']);
    Route::get('/projects/count', [ProjectController::class, "ngoProjects"]);

    Route::get('/projects/details/{id}', [ProjectEditController::class, 'details']);
    Route::put('/projects/details', [ProjectUpdateController::class, 'details']);
    Route::get('/projects/budget/{id}', [ProjectEditController::class, 'budget']);
    Route::put('/projects/budget', [ProjectUpdateController::class, 'budget']);
    Route::get('/projects/organization/structure/{id}', [ProjectEditController::class, 'structure']);
    Route::get('/projects/checklist/{id}', [ProjectEditController::class, 'checklist']);
});
Route::prefix('v1')->middleware(["multiAuthorized:" . 'user:api'])->group(function () {
    Route::get('/projects/with/name', [ProjectController::class, "projectsWithName"]);
});
