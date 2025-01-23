<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiloController;
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

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth.jwt');
    Route::get('me', [AuthController::class, 'me']);
});

Route::middleware(['role:admin'])->group(function () {});
Route::get('/silos', [SiloController::class, 'index']);
Route::post('/store', [SiloController::class, 'store']);
Route::get('/search', [SiloController::class, 'search']);
Route::put('/silos/{id}', [SiloController::class, 'update']);
Route::delete('/deleteSilo/{id}', [SiloController::class, 'deleteSilo']);
Route::delete('/silos/{id}', [SiloController::class, 'destroy']);
Route::get('/filter', [SiloController::class, 'getFilteredData']);
Route::get('/ExporterDATA', [SiloController::class, 'ExporterDATA']);
Route::get('/exportPDF', [SiloController::class, 'exportPDF']);


// Route::middleware(['role:editor'])->group(function () {
//     Route::get('/silos', [SiloController::class, 'index']);
//     Route::post('/store', [SiloController::class, 'store']);
//     Route::get('/search', [SiloController::class, 'search']);
//     Route::put('/silos/{id}', [SiloController::class, 'update']);
//     Route::delete('/deleteSilo/{id}', [SiloController::class, 'deleteSilo']);
//     Route::delete('/silos/{id}', [SiloController::class, 'destroy']);
//     Route::get('/filter', [SiloController::class, 'getFilteredData']);
//     Route::get('/ExporterDATA', [SiloController::class, 'ExporterDATA']);
//     Route::get('/exportPDF', [SiloController::class, 'exportPDF']);
// });
