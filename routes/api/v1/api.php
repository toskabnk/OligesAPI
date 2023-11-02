<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

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

//Routes without auth
Route::group(['prefix' => 'cooperative'], function(){
        Route::post('login', [AuthController::class, 'cooperativeLogin']);
    });

Route::group(['prefix' => 'farmer'], function(){
        Route::post('login', [AuthController::class, 'farmerLogin']);
});

//Routes with auth
Route::group(['middleware' => 'auth:api'], function(){
    //Logout
    Route::post('logout', [AuthController::class, 'logout']);

    //Cooperative routes
    Route::group(['prefix' => 'cooperative'], function()
    {

    });

    //Farmer routes
    Route::group(['prefix' => 'farmer'], function()
    {

    });
});

