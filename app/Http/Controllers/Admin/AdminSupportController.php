<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\AdminActivityLog;
use App\Models\AdminNotification;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminSupportController extends Controller
{
    /**
     * Check authorization
     */
    private function checkAuthorization(): void
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin->canManageTenants()) {
            AdminActivityLog::log(
                $admin,
                'unauthorized_support_access',
                'Attempted to access support tickets without authorization',
                ['url' => request()->fullUrl()],
                null,
                7
            );
            abort(403, 'Only Support and Superadmin can manage tickets.');
        }
    }

    /**
     * Tampilkan daftar support tickets dengan filter dan stats
     */
    public function index(Request $request)
    {
        $this->checkAuthorization();
        $status = $request->get('status', 'open');
        $assignedTo = $request->get('assigned_to');
        $priority = $request->get('priority');
        
        $query = SupportTicket::with(['user', 'assignedAdmin'])
            ->orderByRaw("
                CASE priority 
                    WHEN 'urgent' THEN 1 
                    WHEN 'high' THEN 2 
                    WHEN 'medium' THEN 3 
                    ELSE 4 
                END
            ")
            ->orderBy('created_at', 'desc');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($assignedTo === 'me') {
            $query->where('assigned_admin_id', Auth::guard('admin')->id());
        } elseif ($assignedTo === 'unassigned') {
            $query->whereNull('assigned_admin_id');
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        $tickets = $query->paginate(20);

        // Stats
        $stats = [
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::whereIn('status', ['in_progress', 'waiting_customer'])->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
            'closed' => SupportTicket::where('status', 'closed')->count(),
            'my_tickets' => SupportTicket::where('assigned_admin_id', Auth::guard('admin')->id())
                ->whereNotIn('status', ['resolved', 'closed'])->count(),
            'unassigned' => SupportTicket::whereNull('assigned_admin_id')
                ->where('status', 'open')->count(),
            'urgent' => SupportTicket::where('priority', 'urgent')
                ->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];

        // Agents untuk filter
        $agents = AdminUser::where('is_active', true)
            ->whereIn('role', [AdminUser::ROLE_SUPPORT, AdminUser::ROLE_SUPERADMIN])
            ->get();

        // SLA stats
        $slaStats = [
            'breached' => SupportTicket::whereNotNull('sla_breach_minutes')->count(),
            'at_risk' => SupportTicket::where('status', 'open')
                ->where('created_at', '<=', now()->subHours(4))
                ->whereNull('first_response_at')
                ->count(),
        ];

        return view('admin.support.index', compact(
            'tickets', 'stats', 'agents', 'slaStats', 'status', 'assignedTo', 'priority'
        ));
    }

    /**
     * Show ticket detail
     */
    public function show(SupportTicket $ticket)
    {
        $this->checkAuthorization();
        $ticket->load(['user', 'assignedAdmin', 'replies.user', 'replies.admin']);
        
        // Mark as in_progress jika masih open dan assigned ke admin ini
        if ($ticket->status === SupportTicket::STATUS_OPEN && 
            $ticket->assigned_admin_id === Auth::guard('admin')->id()) {
            $ticket->update(['status' => SupportTicket::STATUS_IN_PROGRESS]);
        }

        // Available agents untuk reassignment
        $agents = AdminUser::where('is_active', true)
            ->whereIn('role', [AdminUser::ROLE_SUPPORT, AdminUser::ROLE_SUPERADMIN])
            ->get();

        // SLA info
        $slaInfo = $this->calculateSLA($ticket);

        return view('admin.support.show', compact('ticket', 'agents', 'slaInfo'));
    }

    /**
     * Reply to ticket
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $this->checkAuthorization();
        $request->validate([
            'message' => 'required|string|max:5000',
            'is_internal' => 'boolean',
        ]);

        $isInternal = $request->boolean('is_internal', false);

        // Check permission for internal notes
        if ($isInternal && !Auth::guard('admin')->user()->canManageTenants()) {
            abort(403, 'Only support team can add internal notes.');
        }

        // Create reply
        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => null,
            'admin_id' => $isInternal ? null : Auth::guard('admin')->id(),
            'message' => $request->message,
            'is_staff' => true,
            'is_internal' => $isInternal,
        ]);

        // Update first response time jika ini response pertama dari staff
        if (!$isInternal && !$ticket->first_response_at) {
            $ticket->update(['first_response_at' => now()]);
            
            // Check SLA breach
            $responseTimeMinutes = $ticket->created_at->diffInMinutes(now());
            $slaLimit = $this->getSLALimit($ticket->priority);
            
            if ($responseTimeMinutes > $slaLimit) {
                $ticket->update(['sla_breach_minutes' => $responseTimeMinutes - $slaLimit]);
            }
        }

        // Update ticket status jika masih open dan bukan internal
        if (!$isInternal && $ticket->status === SupportTicket::STATUS_OPEN) {
            $ticket->update(['status' => SupportTicket::STATUS_IN_PROGRESS]);
        }

        // Log activity
        $actionType = $isInternal ? 'add_internal_note' : 'reply_ticket';
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            $actionType,
            "{$actionType} on ticket #{$ticket->ticket_number}",
            ['ticket_id' => $ticket->id, 'reply_id' => $reply->id],
            $ticket
        );

        // Notify customer jika bukan internal
        if (!$isInternal) {
            // TODO: Send email notification to customer
        }

        return back()->with('success', $isInternal ? 'Internal note added!' : 'Reply sent successfully!');
    }

    /**
     * Assign ticket to agent
     */
    public function assign(Request $request, SupportTicket $ticket)
    {
        $this->checkAuthorization();
        $request->validate([
            'admin_id' => 'required|exists:admin_users,id',
        ]);

        $admin = AdminUser::findOrFail($request->admin_id);
        $oldAssignee = $ticket->assignedAdmin?->name ?? 'Unassigned';

        $ticket->update([
            'assigned_admin_id' => $request->admin_id,
            'status' => $ticket->status === SupportTicket::STATUS_OPEN 
                ? SupportTicket::STATUS_IN_PROGRESS 
                : $ticket->status,
        ]);

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'assign_ticket',
            "Assigned ticket #{$ticket->ticket_number} to {$admin->name}",
            [
                'ticket_id' => $ticket->id,
                'from' => $oldAssignee,
                'to' => $admin->name,
            ],
            $ticket
        );

        // Notify assigned admin
        AdminNotification::notify(
            AdminNotification::TYPE_SUPPORT,
            'New Ticket Assignment',
            "You have been assigned to ticket #{$ticket->ticket_number}: {$ticket->subject}",
            route('admin.support.show', $ticket),
            $admin->id,
            $ticket->priority === 'urgent' ? AdminNotification::PRIORITY_HIGH : AdminNotification::PRIORITY_MEDIUM
        );

        return back()->with('success', "Ticket assigned to {$admin->name}!");
    }

    /**
     * Close ticket
     */
    public function close(SupportTicket $ticket)
    {
        $this->checkAuthorization();
        $ticket->update([
            'status' => SupportTicket::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'close_ticket',
            "Closed ticket #{$ticket->ticket_number}",
            ['ticket_id' => $ticket->id],
            $ticket
        );

        return back()->with('success', 'Ticket closed successfully!');
    }

    /**
     * Resolve ticket
     */
    public function resolve(SupportTicket $ticket)
    {
        $this->checkAuthorization();
        $ticket->update([
            'status' => SupportTicket::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);

        // Calculate resolution SLA
        if ($ticket->created_at) {
            $resolutionMinutes = $ticket->created_at->diffInMinutes(now());
            $resolutionSLA = $this->getResolutionSLA($ticket->priority);
            
            if ($resolutionMinutes > $resolutionSLA) {
                $ticket->update(['sla_breach_minutes' => $resolutionMinutes - $resolutionSLA]);
            }
        }

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'resolve_ticket',
            "Resolved ticket #{$ticket->ticket_number}",
            ['ticket_id' => $ticket->id],
            $ticket
        );

        return back()->with('success', 'Ticket marked as resolved!');
    }

    /**
     * Reopen ticket
     */
    public function reopen(SupportTicket $ticket)
    {
        $this->checkAuthorization();
        $ticket->update([
            'status' => SupportTicket::STATUS_IN_PROGRESS,
            'resolved_at' => null,
            'closed_at' => null,
        ]);

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'reopen_ticket',
            "Reopened ticket #{$ticket->ticket_number}",
            ['ticket_id' => $ticket->id],
            $ticket
        );

        return back()->with('success', 'Ticket reopened!');
    }

    /**
     * Update ticket priority
     */
    public function updatePriority(Request $request, SupportTicket $ticket)
    {
        $this->checkAuthorization();
        $request->validate([
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $oldPriority = $ticket->priority;
        $ticket->update(['priority' => $request->priority]);

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'update_ticket_priority',
            "Changed ticket #{$ticket->ticket_number} priority from {$oldPriority} to {$request->priority}",
            [
                'ticket_id' => $ticket->id,
                'from' => $oldPriority,
                'to' => $request->priority,
            ],
            $ticket
        );

        return back()->with('success', 'Priority updated!');
    }

    /**
     * Update internal notes
     */
    public function updateInternalNotes(Request $request, SupportTicket $ticket)
    {
        $this->checkAuthorization();
        $request->validate([
            'internal_notes' => 'nullable|string|max:5000',
        ]);

        $ticket->update(['internal_notes' => $request->internal_notes]);

        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'update_internal_notes',
            "Updated internal notes for ticket #{$ticket->ticket_number}",
            ['ticket_id' => $ticket->id],
            $ticket
        );

        return back()->with('success', 'Internal notes updated!');
    }

    /**
     * Get SLA limit dalam menit
     */
    private function getSLALimit(string $priority): int
    {
        return match($priority) {
            'urgent' => 60,    // 1 hour
            'high' => 240,     // 4 hours
            'medium' => 480,   // 8 hours
            'low' => 1440,     // 24 hours
            default => 480,
        };
    }

    /**
     * Get resolution SLA dalam menit
     */
    private function getResolutionSLA(string $priority): int
    {
        return match($priority) {
            'urgent' => 240,    // 4 hours
            'high' => 1440,     // 24 hours
            'medium' => 2880,   // 48 hours
            'low' => 10080,     // 7 days
            default => 2880,
        };
    }

    /**
     * Calculate SLA info untuk ticket
     */
    private function calculateSLA(SupportTicket $ticket): array
    {
        $now = now();
        $firstResponseSLA = $this->getSLALimit($ticket->priority);
        $resolutionSLA = $this->getResolutionSLA($ticket->priority);
        
        $info = [
            'first_response' => [
                'limit_minutes' => $firstResponseSLA,
                'responded' => !is_null($ticket->first_response_at),
                'breached' => false,
                'time_remaining' => null,
            ],
            'resolution' => [
                'limit_minutes' => $resolutionSLA,
                'resolved' => !is_null($ticket->resolved_at),
                'breached' => false,
                'time_remaining' => null,
            ],
        ];

        // First response SLA
        if ($ticket->first_response_at) {
            $responseTime = $ticket->created_at->diffInMinutes($ticket->first_response_at);
            $info['first_response']['breached'] = $responseTime > $firstResponseSLA;
            $info['first_response']['actual_minutes'] = $responseTime;
        } else {
            $elapsed = $ticket->created_at->diffInMinutes($now);
            $info['first_response']['time_remaining'] = max(0, $firstResponseSLA - $elapsed);
            $info['first_response']['breached'] = $elapsed > $firstResponseSLA;
        }

        // Resolution SLA
        if ($ticket->resolved_at) {
            $resolutionTime = $ticket->created_at->diffInMinutes($ticket->resolved_at);
            $info['resolution']['breached'] = $resolutionTime > $resolutionSLA;
            $info['resolution']['actual_minutes'] = $resolutionTime;
        } else {
            $elapsed = $ticket->created_at->diffInMinutes($now);
            $info['resolution']['time_remaining'] = max(0, $resolutionSLA - $elapsed);
            $info['resolution']['breached'] = $elapsed > $resolutionSLA;
        }

        return $info;
    }
}
