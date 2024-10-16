<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // Lançar exceção quando houver erro de autenticação (AuthenticationException)
        $exceptions->render(function(AuthenticationException $e){

            // Salvar log do erro
            \Log::error('Erro de autenticação: ' . $e->getMessage());

            // Retornar mensagem de erro
            return response()->json([
            'status' => false,
            'message' => 'Token de Autenticação Inválido.',
            ], 401);
        });

    })->create();
