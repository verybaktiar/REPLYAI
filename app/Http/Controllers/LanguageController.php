<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch application language
     * 
     * @param string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch($locale)
    {
        if (array_key_exists($locale, config('app.locales', ['en' => 'English', 'id' => 'Bahasa Indonesia']))) {
            Session::put('applocale', $locale);
        }
        
        return redirect()->back();
    }
}
