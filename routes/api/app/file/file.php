
<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\api\app\file\FileController;
use Illuminate\Support\Facades\Route;



Route::prefix('v1')->middleware(['api.key', "authorized:" . 'user:api'])->group(function () {
  Route::post('news/file/upload', [FileController::class, 'newsFileUpload']);
  Route::post('ngo/file/upload', [FileController::class, 'uploadNgoFile'])->withoutMiddleware('throttle');
});

Route::prefix('v1')->middleware(['api.key', "authorized:" . 'user:api'])->group(function () {
  Route::post('ngo/file/upload/{ngo_id}', [FileController::class, 'uploadNgoFile'])->withoutMiddleware('throttle');
  Route::post('ngo/representor/file/upload', [FileController::class, 'uploadFile'])->withoutMiddleware('throttle');
  Route::post('ngo/extend/file/upload/{ngo_id}', [FileController::class, 'uploadNgoExtendFile'])->withoutMiddleware('throttle');
});


Route::prefix('v1')->middleware(['api.key', "authorized:" . 'ngo:api'])->group(function () {
  Route::post('ngo/project/file/upload/{project_id}', [FileController::class, 'uploadProjectFile'])->withoutMiddleware('throttle');
});
