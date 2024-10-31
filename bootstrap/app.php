<?php

use App\Http\Middleware\CheckUserActivated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use App\Http\Middleware\CorsMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['api', 'auth:api']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            env('APP_URL') . 'api/*',
        ]);
        $middleware->append(CorsMiddleware::class);
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'activated' => CheckUserActivated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 401);
            }
        });

        $exceptions->render(function (PDOException $e, Request $request) {
            if ($request->is('api/*')) {
                if ($e->getCode() == 23000) {
                    return response()->json([
                        'error' => 'Duplicate entry detected.',
                        'message' => 'The data you are trying to create already exists.'
                    ], 409);
                }
            }
        });

        $exceptions->render(function ($e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 500);
            }
        });
    })
    ->create();
