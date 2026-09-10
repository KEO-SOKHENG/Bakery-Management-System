<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Setting;

class LanguageController extends Controller
{
    public function switchLanguage(Request $request, $lang)
    {
        if (in_array($lang, ['en', 'km'])) {
            Session::put('locale', $lang);
            Setting::set('language', $lang);
        }

        return redirect()->back();
    }
}
