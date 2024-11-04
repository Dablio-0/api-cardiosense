<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Import Controller Classes */
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ESPController;

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


/* RRoutes ESP (Not Logged) */
Route::get('test/esp/get', [ESPController::class, 'testCommunicationESPGET'])->name('testCommunicationESPGET');
Route::post('test/esp/post', [ESPController::class, 'testCommunicationESPPOST'])->name('testCommunicationESPPOST');
Route::post('esp/data/receive', [ESPController::class, 'getDataESP'])->name('getDataESP');


/* With Middleware (Autenticatio    n) */
Route::group(['middleware' => ['auth:sanctum']], function(){

    Route::post('logout/{user}', [AuthController::class, 'logout'])->name('logout');
    Route::get('verifyLoginActive', [AuthController::class, 'verifyLoginActive'])->name('verifyLoginActive');
    Route::get('users', [UserController::class, 'index'])->name('users');

    Route::prefix('family')->name('family.')->controller(FamilyController::class)->group(function(){
        
        Route::post('/create', 'store')->name('store');
        Route::get('/{family}', 'retrieve')->name('show');
        Route::put('/{family}', 'update')->name('update');
        Route::delete('/{family}', 'delete')->name('destroy');

        Route::post('/members/sync', 'syncFamilyMembers')->name('members.syncFamilyMembers');

    });

});