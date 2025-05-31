
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\app\projects\ProjectPdfController;




Route::prefix('v1')->group(function () {

  Route::get('project/mou/generate/{id}', [ProjectPdfController::class, 'generateForm']);
});
