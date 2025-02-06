
<?php

use App\Http\Controllers\api\app\director\DirectorController;
use App\Http\Controllers\api\app\ngo\ngoStatus\NgoStatusController;
use Illuminate\Support\Facades\Route;






Route::get('/ngo/statuses', [NgoStatusController::class, 'statuses']);
Route::get('/ngo/status/{id}', [NgoStatusController::class, 'ngoStatus']);
Route::get('/ngo/statuses/{id}', [NgoStatusController::class, 'ngoStatuses']);
Route::get('/ngo/status/change/{id}', [NgoStatusController::class, 'changeNgoStatus']);



Route::prefix('v1')->group(function () {});

Route::prefix('v1')->middleware(['api.key', "authorized:" . 'user:api'])->group(function () {});

// ngo user 


Route::prefix('v1')->middleware(['api.key', "authorized:" . 'ngo:api'])->group(function () {});
