<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminPromoCodeController extends Controller
{
    /**
     * Tampilkan daftar promo codes
     */
    public function index()
    {
        $promoCodes = PromoCode::orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.promo-codes.index', compact('promoCodes'));
    }

    /**
     * Tampilkan form create promo code
     */
    public function create()
    {
        return view('admin.promo-codes.create');
    }

    /**
     * Create promo code baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
            'is_active' => 'boolean',
        ]);

        $promoCode = PromoCode::create([
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'value' => $validated['value'],
            'max_uses' => $validated['max_uses'] ?? null,
            'used_count' => 0,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'create_promo_code',
            "Create promo code: {$promoCode->code}",
            ['promo_code_id' => $promoCode->id, 'code' => $promoCode->code],
            $promoCode
        );

        return redirect()->route('admin.promo-codes.index')
            ->with('success', "Promo code {$promoCode->code} berhasil dibuat!");
    }

    /**
     * Edit promo code
     */
    public function edit(PromoCode $promoCode)
    {
        return view('admin.promo-codes.edit', compact('promoCode'));
    }

    /**
     * Update promo code
     */
    public function update(Request $request, PromoCode $promoCode)
    {
        $validated = $request->validate([
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $oldData = $promoCode->toArray();

        $promoCode->update([
            'type' => $validated['type'],
            'value' => $validated['value'],
            'max_uses' => $validated['max_uses'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'update_promo_code',
            "Update promo code: {$promoCode->code}",
            ['promo_code_id' => $promoCode->id, 'old_data' => $oldData, 'new_data' => $promoCode->fresh()->toArray()],
            $promoCode
        );

        return redirect()->route('admin.promo-codes.index')
            ->with('success', "Promo code {$promoCode->code} berhasil diupdate!");
    }

    /**
     * Delete promo code
     */
    public function destroy(PromoCode $promoCode)
    {
        $code = $promoCode->code;

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'delete_promo_code',
            "Delete promo code: {$code}",
            ['promo_code_id' => $promoCode->id, 'code' => $code]
        );

        $promoCode->delete();

        return redirect()->route('admin.promo-codes.index')
            ->with('success', "Promo code {$code} berhasil dihapus!");
    }
}
