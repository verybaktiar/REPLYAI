<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRefundController extends Controller
{
    public function index()
    {
        return view('admin.refunds.index');
    }

    public function approve(Refund $refund)
    {
        $refund->update([
            'status' => 'approved',
            'processed_by' => Auth::guard('admin')->id(),
            'processed_at' => now(),
        ]);
        return back()->with('success', 'Refund approved!');
    }

    public function reject(Refund $refund)
    {
        $refund->update([
            'status' => 'rejected',
            'processed_by' => Auth::guard('admin')->id(),
            'processed_at' => now(),
        ]);
        return back()->with('success', 'Refund rejected');
    }
}
