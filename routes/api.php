<?php

use App\Http\Controllers\auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/user', function (Request $request) {
    info('called again');
    info($request->user());
    return $request->user();
})->middleware('auth:sanctum');
