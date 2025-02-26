
<?php

use App\Enums\PermissionEnum;
use App\Enums\SubPermissionEnum;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\app\checklist\CheckListController;



// Route::prefix('v1')->middleware(['api.key', "doubleAuthorized:" . 'user:api,ngo:api'])->group(function () {
Route::prefix('v1')->middleware(['api.key', "authorized:" . 'user:api,ngo:api'])->group(function () {
  Route::get('checklists', [CheckListController::class, 'checklists']);
  Route::get('ngo/checklist/types', [CheckListController::class, 'checklistTypes']);
  Route::get('project/register/checklist', [CheckListController::class, 'projectRegister']);
  Route::get('ngo/register/checklist', [CheckListController::class, 'ngoRegister']);
  Route::get('ngo/register/abroad/director-checklist', [CheckListController::class, 'ngoRegisterAbroadDirector']);
  Route::get('ngo-checklist/{id}', [CheckListController::class, 'checklist']);
});

Route::prefix('v1')->middleware(['api.key', "authorized:" . 'user:api'])->group(function () {
  Route::post('checklist/store', [CheckListController::class, 'store'])->middleware(["userHasSubAddPermission:" . PermissionEnum::settings->value . "," . SubPermissionEnum::setting_checklist->value]);
  Route::delete('checklist/{id}', [CheckListController::class, 'destroy'])->middleware(["userHasSubDeletePermission:" . PermissionEnum::settings->value . "," . SubPermissionEnum::setting_checklist->value]);;
  Route::post('checklist/update', [CheckListController::class, 'update'])->middleware(["userHasSubEditPermission:" . PermissionEnum::settings->value . "," . SubPermissionEnum::setting_checklist->value]);;
});
