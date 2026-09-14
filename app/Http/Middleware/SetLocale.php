<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } elseif ($request->hasCookie('locale')) {
            $locale = $request->cookie('locale');
        } else {
            try {
                $locale = Setting::get('language', 'en');
            } catch (\Throwable $e) {
                $locale = 'en';
            }
        }

        $locale = strtolower((string)$locale);
        if (!in_array($locale, ['en', 'km'])) {
            $locale = 'en';
        }

        App::setLocale($locale);

        if (Session::get('locale') !== $locale) {
            Session::put('locale', $locale);
        }

        $response = $next($request);

        if ($request->cookie('locale') !== $locale) {
            $response->headers->setCookie(cookie()->forever('locale', $locale));
        }

        return $response;
    }
}
