<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Services\SupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Controller: Support (Customer Side)
 * 
 * Controller untuk pelanggan mengelola tiket support mereka.
 */
class SupportController extends Controller
{
    protected SupportService $supportService;

    public function __construct(SupportService $supportService)
    {
        $this->supportService = $supportService;
    }

    /**
     * Daftar tiket user
     */
    public function index()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.support.index', compact('tickets'));
    }

    /**
     * Form buat tiket baru
     */
    public function create()
    {
        $categories = SupportTicket::getCategoryOptions();
        $priorities = SupportTicket::getPriorityOptions();

        return view('pages.support.create', compact('categories', 'priorities'));
    }

    /**
     * Simpan tiket baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachments.*' => 'nullable|file|max:5120', // Max 5MB each
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments', 'public');
                $attachments[] = Storage::url($path);
            }
        }

        $ticket = $this->supportService->createTicket(
            Auth::user(),
            $request->category,
            $request->subject,
            $request->message,
            $request->priority,
            $attachments ?: null
        );

        return redirect()->route('support.show', $ticket)
            ->with('success', 'Tiket berhasil dibuat! Tim support kami akan segera merespon.');
    }

    /**
     * Detail tiket
     */
    public function show(SupportTicket $ticket)
    {
        // Pastikan milik user ini
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $ticket->load('replies.sender');

        // Tandai balasan admin sebagai sudah dibaca
        $ticket->replies()
            ->where('sender_type', TicketReply::SENDER_ADMIN)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('pages.support.show', compact('ticket'));
    }

    /**
     * Kirim balasan
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        // Pastikan milik user ini
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        // Tidak bisa balas tiket yang sudah closed
        if ($ticket->status === SupportTicket::STATUS_CLOSED) {
            return back()->with('error', 'Tiket sudah ditutup, tidak bisa dibalas.');
        }

        $request->validate([
            'message' => 'required|string|max:5000',
            'attachments.*' => 'nullable|file|max:5120',
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments', 'public');
                $attachments[] = Storage::url($path);
            }
        }

        $this->supportService->replyAsCustomer(
            $ticket,
            Auth::user(),
            $request->message,
            $attachments ?: null
        );

        return back()->with('success', 'Balasan berhasil dikirim!');
    }

    /**
     * Beri rating tiket yang sudah resolved
     */
    public function rate(Request $request, SupportTicket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        if ($ticket->status !== SupportTicket::STATUS_RESOLVED) {
            return back()->with('error', 'Hanya bisa memberi rating untuk tiket yang sudah selesai.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $this->supportService->rateTicket($ticket, $request->rating, $request->feedback);

        // Tutup tiket setelah diberi rating
        $this->supportService->closeTicket($ticket);

        return back()->with('success', 'Terima kasih atas feedback Anda!');
    }

    /**
     * Reopen tiket yang sudah resolved
     */
    public function reopen(Request $request, SupportTicket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($ticket->status, [SupportTicket::STATUS_RESOLVED])) {
            return back()->with('error', 'Tiket tidak bisa dibuka kembali.');
        }

        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        // Reply akan otomatis mengubah status
        $this->supportService->replyAsCustomer($ticket, Auth::user(), $request->message);

        return back()->with('success', 'Tiket dibuka kembali.');
    }
}
