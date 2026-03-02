<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminBroadcastController extends Controller
{
    private function checkAuthorization(): void
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin->isSuperAdmin()) {
            AdminActivityLog::log(
                $admin,
                'unauthorized_broadcast_attempt',
                'Attempted to access broadcast without superadmin privilege',
                ['url' => request()->fullUrl()],
                null,
                8
            );
            abort(403, 'Only Superadmin can manage broadcasts.');
        }
    }

    public function index()
    {
        $this->checkAuthorization();
        return view('admin.broadcast.index');
    }

    public function send(Request $request)
    {
        $this->checkAuthorization();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:banner,email,both',
            'audience' => 'required|in:all,active,vip,free,expiring',
            'style' => 'required|in:info,success,warning,danger',
            'duration_days' => 'required|integer|min:1|max:30',
        ]);
        
        $announcement = Announcement::create([
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
        
        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'send_broadcast',
            "Sent broadcast: {$request->title} to {$request->audience}",
            [
                'announcement_id' => $announcement->id,
                'title' => $request->title,
                'audience' => $request->audience,
                'type' => $request->type
            ],
            $announcement
        );
        
        return back()->with('success', 'Broadcast berhasil dikirim!');
    }
}
