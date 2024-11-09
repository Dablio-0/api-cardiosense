<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

/* Importação das Classes de Controller */
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\ESPController;

/* Rotas de Teste */
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


/* Sem Middleware (Autenticação) */
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');


/* Recuperar Senha (Usuário Não Logado) */
Route::post('password/reset/code', [AuthController::class, 'sendPasswordResetCode'])->name('sendPasswordResetCode');
Route::post('password/reset/code/verify', [AuthController::class, 'verifyResetCode'])->name('verifyResetCode');
Route::post('password/reset/confirm', [AuthController::class, 'resetPassword'])->name('resetPassword');


/* Rotas ESP (Não Logado) */
Route::get('test/esp/get', [ESPController::class, 'testCommunicationESPGET'])->name('testCommunicationESPGET');
Route::post('test/esp/post', [ESPController::class, 'testCommunicationESPPOST'])->name('testCommunicationESPPOST');
Route::post('esp/data/receive', [ESPController::class, 'getDataESP'])->name('getDataESP');


/* Com Middleware (Autenticação) */
Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('logout/{user}', [AuthController::class, 'logout'])->name('logout');
    Route::get('verifyLoginActive', [AuthController::class, 'verifyLoginActive'])->name('verifyLoginActive');
    Route::get('users', [UserController::class, 'index'])->name('users');

    Route::prefix('user')->name('user.')->controller(UserController::class)->group(function () {
        Route::get('/{user}', 'retrieve')->name('show');
        Route::put('/{user}', 'edit')->name('update');
        Route::delete('/{user}', 'delete')->name('destroy');
    });

    Route::prefix('family')->name('family.')->controller(FamilyController::class)->group(function () {
        Route::post('/create', 'store')->name('store');
        Route::get('/{family}', 'retrieve')->name('show');
        Route::put('/{family}', 'update')->name('update');
        Route::delete('/{family}', 'delete')->name('destroy');
        
        Route::post('/members/sync', 'syncFamilyMembers')->name('members.syncFamilyMembers');
    });

});
