<?php

use App\Http\Controllers\AuthController;
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

// resetpassword

Route::post("/registration", [AuthController::class, 'register']);
Route::post("/forgetpassword", [AuthController::class, 'forgetPassword']);
Route::post("/resetpassword", [AuthController::class, 'resetpassword']);

Route::group(['middleware' => 'api', 'prefix' => 'maxton-auth'], function ($router) {
    Route::post("/login", [AuthController::class, 'login'])->name('login');   
    Route::get("/getprofile", [AuthController::class, 'getprofile']);
    Route::post("/updateoradd", [AuthController::class, 'updateOrAdd']);
    Route::get("/fetchAllOtherUser", [AuthController::class, 'fetchAllUser']);
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
