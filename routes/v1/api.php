<?php

use App\Http\Controllers\Api\V1\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::group(['prefix' => '/auth'], function ($router){
        

        //Google auth
        Route::post('/google/call-back', [GoogleAuthController::class, 'callbackGoogle']);
        Route::get('/google', [GoogleAuthController::class, 'redirect']);
    });
});