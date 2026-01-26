<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAnnouncementController extends Controller
{
    public function markAsRead(Announcement $announcement)
    {
        $user = Auth::user();
        $readIds = $user->read_announcements ?? [];
        
        if (!in_array($announcement->id, $readIds)) {
            $readIds[] = $announcement->id;
            $user->update(['read_announcements' => $readIds]);
        }
        
        return back();
    }
}
