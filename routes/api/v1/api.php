<?php

use App\Http\Controllers\API\AddressController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CooperativeController;
use App\Http\Controllers\API\FarmController;
use App\Http\Controllers\API\FarmerController;
use App\Http\Controllers\API\ReceiptController;

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
            Route::post('/login', [AuthController::class, 'cooperativeLogin']);
            Route::post('/', [AuthController::class, 'registerCooperative']);
        });
    
    Route::group(['prefix' => 'farmer'], function(){
            Route::post('/login', [AuthController::class, 'farmerLogin']);
            Route::post('/', [AuthController::class, 'registerFarmer']);
    });
});

//Routes with auth
Route::group(['middleware' => 'auth:api'], function(){
    //Logout
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);

    //Cooperative routes
    Route::group(['prefix' => 'cooperative'], function()
    {
        Route::get('/farmers', [CooperativeController::class, 'viewCooperativeFarmers']);
        Route::get('/{id}', [CooperativeController::class, 'view']);
        //? Should this path be /farmer/cooperative?
        Route::post('/farmer/{id}', [CooperativeController::class, 'addFarmerToCooperative']);
        Route::put('/{id}', [CooperativeController::class, 'update']);
        Route::delete('/farmer/{id}', [CooperativeController::class, 'deleteFarmerFromCooperative']);
    });

    //Farmer routes
    Route::group(['prefix' => 'farmer'], function()
    {        
        Route::get('/cooperatives', [FarmerController::class, 'viewFarmerCooperatives']);
        Route::get('/{id}', [FarmerController::class, 'view']);
        Route::post('/', [FarmerController::class, 'create']);
        Route::post('/check', [FarmerController::class, 'checkFarmer']);
        Route::put('/{id}', [FarmerController::class, 'update']);
    });

    //Address routes
    Route::group(['prefix' => 'address'], function()
    {
        Route::post('/', [AddressController::class, 'create']);
        Route::put('/{id}', [AddressController::class, 'update']);
    });

    //Receipt routes
    Route::group(['prefix' => 'receipt'], function()
    {
        Route::get('/', [ReceiptController::class, 'viewReceiptsCooperative']);
        Route::get('/{id}', [ReceiptController::class, 'viewDetails']);
        Route::post('/', [ReceiptController::class, 'create']);
        //? Is neccesary?
        //Route::put('/{id}', [ReceiptController::class, 'update']);
        Route::delete('/{id}', [ReceiptController::class, 'delete']);

    });

    //Farm routes
    Route::group(['prefix' => 'farm'], function()
    {
        Route::get('/farmer/{id}', [FarmController::class, 'viewFarmerFarms']);
        Route::get('/{id}', [FarmController::class, 'view']);
        Route::post('/{id}', [FarmController::class, 'create']);
        Route::put('/{id}', [FarmController::class, 'update']);
    });

    //Admin routes
    Route::group(['prefix' => 'admin'], function()
    {
        Route::group(['prefix' => 'cooperative'], function()
        {
            Route::get('/', [CooperativeController::class, 'viewAll']);
            Route::post('/', [CooperativeController::class, 'create']);
        });

        Route::group(['prefix' => 'farmer'], function()
        {
            Route::get('/farmer', [FarmerController::class, 'viewAll']);
        });
    });
});

