<?php

namespace App\Http\Controllers;

use App\Models\WaSession;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index(): View
    {
        $session = WaSession::getDefault();
        
        return view('pages.settings.index', [
            'title' => 'Settings',
            'session' => $session,
        ]);
    }
}
