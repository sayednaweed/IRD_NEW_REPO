
<?php

use App\Enums\PermissionEnum;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\app\news\NewsController;

Route::prefix('v1')->group(function () {
  Route::get('/public/newses', [NewsController::class, "publicNewses"]);
  Route::get('/public/news/{id}', [NewsController::class, "publicNews"]);
});
Route::prefix('v1')->middleware(['api.key', "authorized:" . 'user:api'])->group(function () {
  Route::get('/private/newses', [NewsController::class, "authNewses"])->middleware(["userHasMainViewPermission:" . PermissionEnum::news->value]);
  Route::get('/user/news/{id}', [NewsController::class, "authNews"])->middleware(["userHasMainViewPermission:" . PermissionEnum::news->value]);
  Route::post('news/store', [NewsController::class, 'store'])->middleware(["userHasMainAddPermission:" . PermissionEnum::news->value]);
  Route::post('/news/update', [NewsController::class, "update"])->middleware(["userHasMainEditPermission:" . PermissionEnum::news->value]);
  Route::delete('/news/{id}', [NewsController::class, "destroy"])->middleware(["userHasMainDeletePermission:" . PermissionEnum::news->value]);
});
