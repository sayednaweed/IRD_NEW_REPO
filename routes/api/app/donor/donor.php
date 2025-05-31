
<?php

use App\Enums\PermissionEnum;
use App\Enums\SubPermissionEnum;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\app\donor\DonorController;



Route::prefix('v1')->middleware(["authorized:" . 'user:api'])->group(function () {
  Route::POST('/donors', [DonorController::class, 'store'])->middleware(["userHasMainAddPermission:" . PermissionEnum::donor->value]);
  Route::get('/donors', [DonorController::class, 'index'])->middleware(["userHasMainViewPermission:" . PermissionEnum::donor->value]);
  Route::get('/donors/statistics', [DonorController::class, "donorStatistics"]);
  Route::get('/donors/statuses/{id}', [DonorController::class, "donorStatus"]);

  // ->middleware(["userHasMainViewPermission:" . PermissionEnum::donor->value]);
  Route::get('/donors/{id}', [DonorController::class, 'edit']);
  Route::POST('/donors/{id}', [DonorController::class, 'update']);
  Route::post('/donors/change/password', [DonorController::class, 'changePassword']);
  // ->middleware(["userHasSubEditPermission:" . PermissionEnum::ngo->value . "," . SubPermissionEnum::donor_update_account_password->value]);



  // ->middleware(["userHasMainViewPermission:" . PermissionEnum::donor->value]);
});
