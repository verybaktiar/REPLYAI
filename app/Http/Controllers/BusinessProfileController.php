<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class BusinessProfileController extends Controller
{
    /**
     * Display list of business profiles
     */
    public function index()
    {
        $profiles = BusinessProfile::orderBy('is_active', 'desc')->orderBy('business_name')->get();
        $industries = BusinessProfile::INDUSTRIES;
        
        return view('settings.business', compact('profiles', 'industries'));
    }
    
    /**
     * Alias for index (backwards compatibility)
     */
    public function edit()
    {
        return $this->index();
    }

    /**
     * Store new profile (AJAX)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => ['required', Rule::in(array_keys(BusinessProfile::INDUSTRIES))],
            'system_prompt_template' => 'required|string',
            'kb_fallback_message' => 'nullable|string',
        ]);

        $profile = BusinessProfile::create([
            'business_name' => $request->business_name,
            'business_type' => $request->business_type,
            'system_prompt_template' => $request->system_prompt_template,
            'kb_fallback_message' => $request->kb_fallback_message ?? $this->getDefaultFallbackMessage($request->business_type),
            'is_active' => BusinessProfile::count() === 0, // First profile is active by default
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil bisnis berhasil dibuat!',
            'profile' => $profile,
        ]);
    }

    /**
     * Update existing profile (AJAX)
     */
    public function update(Request $request, $id = null): JsonResponse
    {
        // Support both PUT /settings/business (legacy) and PUT /settings/business/{id}
        if ($id) {
            $profile = BusinessProfile::findOrFail($id);
        } else {
            $profile = BusinessProfile::first();
            if (!$profile) {
                return response()->json(['success' => false, 'error' => 'No profile found'], 404);
            }
        }

        $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => ['required', Rule::in(array_keys(BusinessProfile::INDUSTRIES))],
            'system_prompt_template' => 'required|string',
            'kb_fallback_message' => 'nullable|string',
        ]);

        $profile->update($request->only([
            'business_name',
            'business_type',
            'system_prompt_template',
            'kb_fallback_message',
        ]));

        // Handle redirect for legacy form submit
        if (!$request->wantsJson() && !$request->ajax()) {
            return redirect()->back()->with('success', 'Pengaturan bisnis berhasil diperbarui!');
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil bisnis berhasil diperbarui!',
            'profile' => $profile->fresh(),
        ]);
    }

    /**
     * Delete profile (AJAX)
     */
    public function destroy($id): JsonResponse
    {
        $profile = BusinessProfile::findOrFail($id);
        
        // Don't delete if it's the only active profile
        if ($profile->is_active && BusinessProfile::count() === 1) {
            return response()->json([
                'success' => false,
                'error' => 'Tidak dapat menghapus satu-satunya profil aktif.',
            ], 400);
        }

        // If deleting active profile, activate another one
        if ($profile->is_active) {
            $another = BusinessProfile::where('id', '!=', $id)->first();
            if ($another) {
                $another->update(['is_active' => true]);
            }
        }

        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profil bisnis berhasil dihapus!',
        ]);
    }

    /**
     * Set profile as default/active (AJAX)
     */
    public function setDefault($id): JsonResponse
    {
        // Deactivate all others
        BusinessProfile::where('id', '!=', $id)->update(['is_active' => false]);
        
        // Activate this one
        $profile = BusinessProfile::findOrFail($id);
        $profile->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Profil ini sekarang dijadikan default!',
        ]);
    }

    /**
     * API: Get default template for industry type
     */
    public function getTemplate(Request $request): JsonResponse
    {
        $type = $request->input('type', 'general');
        
        if (!array_key_exists($type, BusinessProfile::INDUSTRIES)) {
            $type = 'general';
        }

        return response()->json([
            'success' => true,
            'type' => $type,
            'industry' => BusinessProfile::INDUSTRIES[$type],
            'template' => BusinessProfile::getDefaultPromptTemplate($type),
            'terminology' => BusinessProfile::getDefaultTerminology($type),
            'fallback_message' => $this->getDefaultFallbackMessage($type),
        ]);
    }

    /**
     * Generate default fallback message based on industry
     */
    protected function getDefaultFallbackMessage(string $type): string
    {
        $terminology = BusinessProfile::getDefaultTerminology($type);
        $place = $terminology['place'] ?? 'tempat usaha';
        
        return "Terima kasih atas pertanyaannya kak. Mohon maaf, saya sedang mengalami kendala teknis. Silakan hubungi CS {$place} kami langsung ya 🙏";
    }
}
