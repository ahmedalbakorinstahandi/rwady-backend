<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\MessageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = User::auth();

        if (!$user || !$user->isAdmin()) {
            MessageService::abort(400, 'messages.unauthorized');
        }

        return $next($request);
    }
}
