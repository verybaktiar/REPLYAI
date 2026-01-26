<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BusinessProfile;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding wizard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Jika sudah selesai onboarding, redirect ke dashboard
        if ($user->onboarding_completed_at) {
            return redirect()->route('dashboard');
        }

        $industries = BusinessProfile::INDUSTRIES;
        
        return view('pages.onboarding.wizard', [
            'user' => $user,
            'industries' => $industries,
        ]);
    }

    /**
     * Store onboarding data and mark as complete
     */
    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'business_industry' => 'required|string|max:50',
        ]);

        $user = Auth::user();
        
        // Update user data
        $user->update([
            'name' => $request->business_name,
            'business_industry' => $request->business_industry,
            'onboarding_completed_at' => now(),
        ]);

        // Create or update business profile
        BusinessProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => $request->business_name,
                'business_type' => $request->business_industry,
                'is_active' => true,
            ]
        );

        return redirect()->route('dashboard')->with('success', 'Selamat! Onboarding selesai. Selamat menggunakan ReplyAI! 🎉');
    }

    /**
     * Skip onboarding (mark as complete without full setup)
     */
    public function skip()
    {
        $user = Auth::user();
        $user->update(['onboarding_completed_at' => now()]);

        return redirect()->route('dashboard');
    }
}
