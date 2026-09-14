<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Setting;

class LanguageController extends Controller
{
    public function switchLanguage(Request $request, $lang)
    {
        $lang = strtolower((string)$lang);
        if (in_array($lang, ['en', 'km'])) {
            Session::put('locale', $lang);
            try {
                Setting::set('language', $lang);
            } catch (\Throwable $e) {
                // Ignore DB setting update errors
            }
            cookie()->queue(cookie()->forever('locale', $lang));
            app()->setLocale($lang);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'locale' => $lang])
                ->withCookie(cookie()->forever('locale', $lang));
        }

        return redirect()->back(fallback: route('admin.dashboard'))
            ->withCookie(cookie()->forever('locale', $lang));
    }
}
