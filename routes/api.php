<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import Controller Classes
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Without Middleware (Autentication)
Route::get('/', function () {
    return response()->json(['message' => 'Welcome to the API'], 200);
});
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');


// With Middleware (Autentication)
Route::group(['middleware' => ['auth:sanctum']], function(){

    Route::post('logout/{user}', [AuthController::class, 'logout'])->name('logout');
    Route::get('verifyLoginActive', [AuthController::class, 'verifyLoginActive'])->name('verifyLoginActive');
    Route::get('users', [UserController::class, 'index'])->name('users');
});

