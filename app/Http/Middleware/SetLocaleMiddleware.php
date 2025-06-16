<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\MessageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = explode(',', $request->header('Accept-Language', 'en'))[0];
        if (in_array($locale, ['ar', 'en'])) {
            app()->setLocale($locale);
        }

        // MessageService::abort(400, $locale);


        $user = User::auth();
        if ($user) {

            if ($user->language != $locale) {
                $user->language = $locale;
                $user->save();
            }
        }

        return $next($request);
    }
}
