<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
            'is.admin' => \App\Http\Middleware\IsAdmin::class,
            'verifier.heure.vente' => \App\Http\Middleware\VerifierHoraireVente::class,
            'is.superAdmin' => \App\Http\Middleware\IsSuperAdmin::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

