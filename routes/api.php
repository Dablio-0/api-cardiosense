<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Import Controller Classes */
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/* Test Routes */
Route::get('/', function () {
    return response()->json(['message' => 'Welcome to the API'], 200);
});
Route::get('/send-email', function () {
    Mail::raw('Este é um email de teste', function ($message) {
        $message->to('example@example.com')
            ->subject('Teste de Email');
    });

    return response()->json(['message' => 'Email enviado'], 200);
});


/* Without Middleware (Autentication) */
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');


/* Recover Password (User Not Logged) */
Route::post('password/reset/code', [AuthController::class, 'sendPasswordResetCode'])->name('sendPasswordResetCode');
Route::post('password/reset/code/verify', [AuthController::class, 'verifyResetCode'])->name('verifyResetCode');
Route::post('password/reset/confirm', [AuthController::class, 'resetPassword'])->name('resetPassword');

/* With Middleware (Autentication) */
Route::group(['middleware' => ['auth:sanctum']], function(){

    Route::post('logout/{user}', [AuthController::class, 'logout'])->name('logout');
    Route::get('verifyLoginActive', [AuthController::class, 'verifyLoginActive'])->name('verifyLoginActive');
    Route::get('users', [UserController::class, 'index'])->name('users');
});

