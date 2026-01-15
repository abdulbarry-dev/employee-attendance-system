<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['en', 'fr', 'de', 'es'];

        // If user is authenticated
        if (auth()->check()) {
            $user = auth()->user();

            // If user has no preferred locale and is admin, detect from browser
            if (is_null($user->preferred_locale) && $user->hasRole('admin')) {
                $browserLocale = $request->getPreferredLanguage($supportedLocales);
                $detectedLocale = $browserLocale ?? 'en';

                // Save detected locale to user
                $user->update(['preferred_locale' => $detectedLocale]);
                app()->setLocale($detectedLocale);
            }
            // If user has preferred locale, use it
            elseif ($user->preferred_locale) {
                app()->setLocale($user->preferred_locale);
            }
            // Otherwise use default
            else {
                app()->setLocale(config('app.locale', 'en'));
            }
        }
        // If guest user, check session
        elseif (session()->has('locale')) {
            $locale = session('locale');
            if (in_array($locale, $supportedLocales)) {
                app()->setLocale($locale);
            }
        }

        return $next($request);
    }
}
