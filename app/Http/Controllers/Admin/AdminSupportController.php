<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSupportController extends Controller
{
    /**
     * Tampilkan daftar support tickets
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'open');
        
        $query = SupportTicket::with(['user'])
            ->orderBy('created_at', 'desc');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $tickets = $query->paginate(20);

        // Stats
        $stats = [
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'closed' => SupportTicket::where('status', 'closed')->count(),
        ];

        return view('admin.support.index', compact('tickets', 'stats', 'status'));
    }

    /**
     * Show ticket detail
     */
    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'replies.user']);
        
        return view('admin.support.show', compact('ticket'));
    }

    /**
     * Reply to ticket
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // Create reply
        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => null, // Admin reply
            'message' => $request->message,
            'is_staff' => true,
        ]);

        // Update ticket status jika masih open
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'reply_ticket',
            "Reply ticket #{$ticket->ticket_number}",
            ['ticket_id' => $ticket->id, 'reply_id' => $reply->id]
        );

        return back()->with('success', 'Balasan berhasil dikirim!');
    }

    /**
     * Close ticket
     */
    public function close(SupportTicket $ticket)
    {
        $ticket->update(['status' => 'closed']);

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'close_ticket',
            "Close ticket #{$ticket->ticket_number}",
            ['ticket_id' => $ticket->id],
            $ticket
        );

        return back()->with('success', 'Ticket berhasil ditutup!');
    }

    /**
     * Reopen ticket
     */
    public function reopen(SupportTicket $ticket)
    {
        $ticket->update(['status' => 'in_progress']);

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'reopen_ticket',
            "Reopen ticket #{$ticket->ticket_number}",
            ['ticket_id' => $ticket->id],
            $ticket
        );

        return back()->with('success', 'Ticket berhasil dibuka kembali!');
    }
}
