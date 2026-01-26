<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminPlanController extends Controller
{
    /**
     * Tampilkan daftar plans
     */
    public function index()
    {
        $plans = Plan::orderBy('price_monthly', 'asc')->get();
        
        return view('admin.plans.index', compact('plans'));
    }

    /**
     * Tampilkan form edit plan
     */
    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update plan
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            
            // Limits
            'limits.ai_messages' => 'required|integer|min:-1',
            'limits.contacts' => 'required|integer|min:-1',
            'limits.wa_devices' => 'required|integer|min:0',
            'limits.broadcast_per_month' => 'nullable|integer|min:0',
            
            // Features - AI & Bot
            'features.ai_reply' => 'boolean',
            'features.knowledge_base' => 'boolean',
            'features.rules_management' => 'boolean',
            'features.simulator' => 'boolean',
            'features.quick_reply' => 'boolean',
            
            // Features - Inbox & CRM
            'features.unified_inbox' => 'boolean',
            'features.takeover' => 'boolean',
            'features.crm_contacts' => 'boolean',
            'features.contact_import_export' => 'boolean',
            
            // Features - Platform Integration
            'features.whatsapp' => 'boolean',
            'features.instagram' => 'boolean',
            'features.web_widget' => 'boolean',
            
            // Features - Marketing & Automation
            'features.broadcast' => 'boolean',
            'features.sequences' => 'boolean',
            
            // Features - Analytics & Reports
            'features.analytics' => 'boolean',
            'features.export_reports' => 'boolean',
            'features.activity_logs' => 'boolean',
            
            // Features - Settings & Business
            'features.business_profile' => 'boolean',
            'features.multi_wa_device' => 'boolean',
            
            // Features - Support & Premium
            'features.support_ticket' => 'boolean',
            'features.priority_support' => 'boolean',
            'features.dedicated_support' => 'boolean',
            'features.api_access' => 'boolean',
        ]);

        $oldData = $plan->toArray();

        // Mulai dari features existing agar tidak kehilangan data
        $features = $plan->features ?? [];
        
        // Update/tambah fitur boolean dari form
        // Semua feature checkbox key
        $featureKeys = [
            'ai_reply', 'knowledge_base', 'rules_management', 'simulator', 'quick_reply',
            'unified_inbox', 'takeover', 'crm_contacts', 'contact_import_export',
            'whatsapp', 'instagram', 'web_widget',
            'broadcast', 'sequences',
            'analytics', 'export_reports', 'activity_logs',
            'business_profile', 'multi_wa_device',
            'support_ticket', 'priority_support', 'dedicated_support', 'api_access',
        ];
        
        foreach ($featureKeys as $key) {
            // Checkbox yang di-check akan ada di request, yang tidak di-check tidak ada
            $features[$key] = $request->has("features.{$key}") || (isset($validated['features'][$key]) && $validated['features'][$key]);
        }

        // Tambahkan limits ke features (ini adalah kuantitas, bukan boolean)
        if (isset($validated['limits'])) {
            foreach ($validated['limits'] as $key => $value) {
                $features[$key] = (int) $value;
            }
        }

        $plan->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price_monthly' => $validated['price_monthly'],
            'price_yearly' => $validated['price_yearly'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'features' => $features,
        ]);

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'update_plan',
            "Update plan {$plan->name}",
            [
                'plan_id' => $plan->id,
                'old_data' => $oldData,
                'new_data' => $plan->fresh()->toArray(),
            ],
            $plan
        );

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan {$plan->name} berhasil diupdate!");
    }
}
