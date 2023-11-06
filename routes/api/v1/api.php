<?php

use App\Http\Controllers\API\AddressController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CooperativeController;

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
Route::group(['prefix' => 'auth'], function(){
    Route::group(['prefix' => 'cooperative'], function(){
            Route::post('login', [AuthController::class, 'cooperativeLogin']);
        });
    
    Route::group(['prefix' => 'farmer'], function(){
            Route::post('login', [AuthController::class, 'farmerLogin']);
    });
});

//Routes with auth
Route::group(['middleware' => 'auth:api'], function(){
    //Logout
    Route::post('logout', [AuthController::class, 'logout']);

    //Cooperative routes
    Route::group(['prefix' => 'cooperative'], function()
    {
        Route::get('/', [CooperativeController::class, 'viewAll']);
        Route::get('/{id}', [CooperativeController::class, 'view']);
        Route::post('/', [CooperativeController::class, 'create']);
        Route::put('/{id}', [CooperativeController::class, 'update']);
    });

    //Farmer routes
    Route::group(['prefix' => 'farmer'], function()
    {

    });

    //Address routes
    Route::group(['prefix' => 'address'], function()
    {
        Route::post('/', [AddressController::class, 'create']);
        Route::put('/{id}', [AddressController::class, 'update']);
    });
});

