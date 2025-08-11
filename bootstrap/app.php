<?php

use App\Services\MessageService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*')) {
                return null;
            } else {
                return null;
            }
        });

        // Register custom middleware
        $middleware->alias([
            // Custom middleware aliases can be added here
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        request()->headers->set('Accept', 'application/json');

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                MessageService::abort(401, 'messages.you_are_not_logged_in_Please_log_in_first');
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                MessageService::abort(404, 'messages.route_not_found');
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                MessageService::abort(405, 'messages.invalid_request_method_Please_check_the_allowed_method');
            }
        });
    })


    ->create();
