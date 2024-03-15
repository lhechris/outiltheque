<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\OutilsController;
use App\Http\Controllers\FileUpload;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminReservations;
use App\Http\Controllers\UsersController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::apiResource('todos', TodoController::class)->middleware('auth:sanctum');
//Route::apiResource('outils', OutilsController::class)->middleware('auth:sanctum');

Route::get('outils', [OutilsController::class,'index'])->middleware('auth:sanctum');
Route::get('outils/{outil}', [OutilsController::class,'show'])->middleware('auth:sanctum');
Route::post('outils', [OutilsController::class,'store'])->middleware('auth:sanctum','role:admin');
Route::put('outils/{outil}', [OutilsController::class,'update'])->middleware('auth:sanctum','role:admin');
Route::delete('outils/{outil}', [OutilsController::class,'destroy'])->middleware('auth:sanctum','role:admin');

Route::apiResource('reservations', ReservationController::class)->middleware('auth:sanctum');
Route::apiResource('adminreservations', AdminReservations::class)->middleware('auth:sanctum','role:admin');



Route::post('/upload-file', [FileUpload::class, 'fileUpload'])->name('fileUpload')->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::put('/users/{user}',[UsersController::class,'update'])->middleware('auth:sanctum');


