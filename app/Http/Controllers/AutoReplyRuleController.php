<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\AutoReplyEngine;

use Illuminate\Http\Request;
use App\Models\AutoReplyRule;
use App\Services\ActivityLogService;

class AutoReplyRuleController extends Controller
{
    public function index()
    {
        $rules = AutoReplyRule::orderByDesc('is_active')
            ->orderByDesc('priority')
            ->latest()
            ->get();

        return view('pages.rules.index', [
            'title' => 'Auto Reply Rules',
            'rules' => $rules
        ]);
    }

    public function storeAjax(Request $request)
    {
        $validated = $request->validate([
            'trigger_keyword'   => ['required', 'string', 'max:255'],
            'response_text'     => ['required', 'string'],
            'priority'  => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $rule = AutoReplyRule::create($validated);

        ActivityLogService::logCreated($rule, "Membuat aturan bot baru: {$rule->trigger_keyword}");

        $rowHtml = view('pages.rules._row', [
            'rule' => $rule,
            'i' => 0, // nanti frontend renumber sendiri
        ])->render();

        return response()->json([
            'ok' => true,
            'message' => 'Rule created',
            'rule' => $rule,
            'rowHtml' => $rowHtml,
        ]);
    }

    public function updateAjax(Request $request, AutoReplyRule $rule)
    {
        $validated = $request->validate([
            'trigger_keyword'   => ['required', 'string', 'max:255'],
            'response_text'     => ['required', 'string'],
            'priority'  => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? $rule->is_active;

        $rule->update($validated);

        ActivityLogService::logUpdated($rule, "Memperbarui aturan bot: {$rule->trigger_keyword}");

        $rowHtml = view('pages.rules._row', [
            'rule' => $rule,
            'i' => 0,
        ])->render();

        return response()->json([
            'ok' => true,
            'message' => 'Rule updated',
            'rule' => $rule,
            'rowHtml' => $rowHtml,
        ]);
    }

    public function destroyAjax(AutoReplyRule $rule)
    {
        $id = $rule->id;
        ActivityLogService::logDeleted($rule, "Menghapus aturan bot: {$rule->trigger_keyword}");
        $rule->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Rule deleted',
            'id' => $id,
        ]);
    }

    public function create()
    {
        return view('pages.rules.create', [
            'title' => 'Tambah Rule'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'trigger_keyword' => 'required|string',
            'response_text' => 'required|string',
            'priority' => 'nullable|integer'
        ]);

        $rule = AutoReplyRule::create([
            'name' => $request->name,
            'trigger_keyword' => strtolower(trim($request->trigger_keyword)),
            'response_text' => $request->response_text,
            'priority' => $request->priority ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        ActivityLogService::logCreated($rule, "Membuat aturan bot baru: {$rule->name}");

        return redirect()->route('rules.index')->with('success', 'Rule berhasil dibuat');
    }

    public function edit(AutoReplyRule $rule)
    {
        return view('pages.rules.edit', [
            'title' => 'Edit Rule',
            'rule' => $rule
        ]);
    }

    public function update(Request $request, AutoReplyRule $rule)
    {
        $request->validate([
            'name' => 'required|string',
            'trigger_keyword' => 'required|string',
            'response_text' => 'required|string',
            'priority' => 'nullable|integer'
        ]);

        $rule->update([
            'name' => $request->name,
            'trigger_keyword' => strtolower(trim($request->trigger_keyword)),
            'response_text' => $request->response_text,
            'priority' => $request->priority ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('rules.index')->with('success', 'Rule berhasil diupdate');
    }

    public function destroy(AutoReplyRule $rule)
    {
        ActivityLogService::logDeleted($rule, "Menghapus aturan bot: " . ($rule->name ?? $rule->trigger_keyword));
        $rule->delete();
        return redirect()->route('rules.index')->with('success', 'Rule dihapus');
    }

    public function toggle(AutoReplyRule $rule)
    {
        $rule->is_active = !$rule->is_active;
        $rule->save();

        ActivityLogService::logUpdated($rule, ($rule->is_active ? 'Mengaktifkan' : 'Menonaktifkan') . " aturan bot: " . ($rule->name ?? $rule->trigger_keyword));

        return redirect()->route('rules.index')->with('success', 'Status rule diubah');
    }
 
    public function toggleAjax(AutoReplyRule $rule)
    {
        $rule->is_active = ! $rule->is_active;
        $rule->save();

        $rowHtml = view('pages.rules._row', compact('rule'))->render();

        return response()->json([
            'ok' => true,
            'message' => 'Rule toggled',
            'rule' => $rule,
            'rowHtml' => $rowHtml,
        ]);
    }

   public function testAjax(Request $request, AutoReplyEngine $engine)
        {
            $validated = $request->validate([
                'text' => ['required', 'string'],
            ]);

            $rule = $engine->matchRule($validated['text']);

            if (!$rule) {
                return response()->json([
                    'ok' => true,
                    'matched' => false,
                    'message' => 'Tidak ada rule yang cocok.',
                ]);
            }

            return response()->json([
                'ok' => true,
                'matched' => true,
                'rule' => [
                    'id' => $rule->id,
                    'trigger_keyword' => $rule->trigger_keyword,
                    'match_type' => $rule->match_type ?? 'contains',
                    'priority' => $rule->priority ?? 0,
                    'is_active' => (bool)$rule->is_active,
                ],
                'reply' => $rule->response_text,
            ]);
        }
}
