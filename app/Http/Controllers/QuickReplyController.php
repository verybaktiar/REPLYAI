<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuickReply;

class QuickReplyController extends Controller
{
    public function index()
    {
        $quickReplies = QuickReply::latest()->get();
        return view('pages.settings.quick_replies', compact('quickReplies'));
    }

    public function fetch()
    {
        // API untuk dipanggil dari Inbox
        return response()->json(QuickReply::where('is_active', true)->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'shortcut' => 'nullable|string|unique:quick_replies,shortcut|max:50',
            'message' => 'required|string',
        ]);

        QuickReply::create([
            'shortcut' => $request->shortcut,
            'message' => $request->message,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Quick Reply berhasil ditambahkan');
    }

    public function update(Request $request, QuickReply $quickReply)
    {
        $request->validate([
            'shortcut' => 'nullable|string|max:50|unique:quick_replies,shortcut,' . $quickReply->id,
            'message' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $quickReply->update([
            'shortcut' => $request->shortcut,
            'message' => $request->message,
            'is_active' => $request->input('is_active', true),
        ]);

        return redirect()->back()->with('success', 'Quick Reply berhasil diupdate');
    }

    public function destroy(QuickReply $quickReply)
    {
        $quickReply->delete();
        return redirect()->back()->with('success', 'Quick Reply berhasil dihapus');
    }
}
