<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('incoming',[\App\Http\Controllers\Api\OcrController::class,'index']);
Route::post('ocr-cconvert',[\App\Http\Controllers\Api\OcrController::class,'ocrConvert']);
Route::post('static-incoming',[\App\Http\Controllers\Api\OcrController::class,'staticIncoming']);
Route::get('phpinfo', fn () => phpinfo());
