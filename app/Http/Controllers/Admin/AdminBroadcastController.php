<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminBroadcastController extends Controller
{
    public function index()
    {
        return view('admin.broadcast.index');
    }

    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:banner,email,both',
            'audience' => 'required|in:all,active,vip,free,expiring',
            'style' => 'required|in:info,success,warning,danger',
            'duration_days' => 'required|integer|min:1|max:30',
        ]);
        
        Announcement::create([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'style' => $request->style,
            'audience' => $request->audience,
            'is_active' => true,
            'starts_at' => now(),
            'expires_at' => now()->addDays((int) $request->duration_days),
            'created_by' => Auth::guard('admin')->id(),
        ]);
        
        return back()->with('success', 'Broadcast berhasil dikirim!');
    }
}
