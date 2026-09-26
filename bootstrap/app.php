<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\UpdateLastSeen::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Redirect back gracefully when CSRF token expires (419 Page Expired)
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Your session has expired. Please refresh the page.',
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'email' => 'Your session timed out for security reasons. Please try submitting again.',
                ]);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Your session has expired. Please refresh the page.',
                    ], 419);
                }

                return redirect()->back()
                    ->withInput($request->except(['password', 'password_confirmation']))
                    ->withErrors([
                        'email' => 'Your session timed out for security reasons. Please try submitting again.',
                    ]);
            }
        });
    })->create();

