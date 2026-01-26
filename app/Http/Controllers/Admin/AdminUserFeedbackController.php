<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserFeedbackController extends Controller
{
    public function index()
    {
        return view('admin.feedback.index');
    }

    public function updateStatus(UserFeedback $feedback, Request $request)
    {
        $feedback->update([
            'status' => $request->status,
            'reviewed_by' => Auth::guard('admin')->id()
        ]);
        return back();
    }
}
