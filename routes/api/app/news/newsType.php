
<?php

use App\Enums\PermissionEnum;
use App\Enums\SubPermissionEnum;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\app\news\NewsTypeController;

Route::prefix('v1')->middleware(['api.key', "authorized:" . 'user:api'])->group(function () {
  Route::get('/news-types', [NewsTypeController::class, "newsTypes"])->middleware(["userHasSubViewPermission:" . PermissionEnum::settings->value . "," . SubPermissionEnum::setting_news_type->value]);
  Route::delete('/news-types/{id}', [NewsTypeController::class, "destroy"])->middleware(["userHasSubDeletePermission:" . PermissionEnum::settings->value . "," . SubPermissionEnum::setting_news_type->value]);
  Route::get('/news-types/{id}', [NewsTypeController::class, "newsType"])->middleware(["userHasSubViewPermission:" . PermissionEnum::settings->value . "," . SubPermissionEnum::setting_news_type->value]);
  Route::post('/news-types/store', [NewsTypeController::class, "store"])->middleware(["userHasSubStorePermission:" . PermissionEnum::settings->value . "," . SubPermissionEnum::setting_news_type->value]);
  Route::post('/news-types/update', [NewsTypeController::class, "update"])->middleware(["userHasSubEditPermission:" . PermissionEnum::settings->value . "," . SubPermissionEnum::setting_news_type->value]);
});
