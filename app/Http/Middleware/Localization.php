<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Check if the user is authenticated and has a valid language setting
        if ($user && in_array($user->language, ['en', 'ar'])) {
            $locale = $user->language;
        } else {
            $locale = 'ar'; // Default language
        }

        app()->setLocale($locale);

        return $next($request);
    }
}