<?php

namespace App\Http\Controllers;

use App\Models\WaBroadcast;
use App\Models\WaBroadcastTarget;
use App\Models\WaMessage;
use App\Jobs\SendBroadcastJob;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class WhatsAppBroadcastController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $broadcasts = WaBroadcast::withCount(['targets', 'targets as sent_count' => function ($query) {
            $query->where('status', 'sent');
        }])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return view('pages.whatsapp.broadcast.index', [
            'broadcasts' => $broadcasts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // Get potential contacts count (unique private chats)
        $contactsCount = WaMessage::select('phone_number')
            ->where('remote_jid', 'not like', '%@g.us')
            ->where('remote_jid', 'not like', '%@newsletter')
            ->distinct()
            ->count('phone_number');

        return view('pages.whatsapp.broadcast.create', [
            'contactsCount' => $contactsCount
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required_without:file|nullable|string',
            'file' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,mp4', // 10MB
            'target_type' => 'required|in:all_contacts,manual',
            'manual_numbers' => 'required_if:target_type,manual|nullable|string'
        ]);

        // Handle File Upload
        $mediaPath = null;
        if ($request->hasFile('file')) {
            $mediaPath = $request->file('file')->store('whatsapp-broadcast', 'public');
        }

        // Create Broadcast Campaign
        $broadcast = WaBroadcast::create([
            'title' => $request->title,
            'message' => $request->message,
            'media_path' => $mediaPath,
            'status' => 'processing',
            'filters' => ['type' => $request->target_type]
        ]);

        // Determine Targets
        $targets = [];
        if ($request->target_type === 'all_contacts') {
            // Fetch From DB
            $contacts = WaMessage::select('phone_number')
                ->where('remote_jid', 'not like', '%@g.us')
                ->where('remote_jid', 'not like', '%@newsletter')
                ->whereRaw('LENGTH(phone_number) <= 15')
                ->distinct()
                ->pluck('phone_number');
            
            foreach ($contacts as $phone) {
                $targets[] = $phone;
            }
        } elseif ($request->target_type === 'manual') {
            // Parse Manual Input
            $numbers = preg_split('/[\r\n,]+/', $request->manual_numbers);
            foreach ($numbers as $num) {
                $cleanNum = preg_replace('/[^0-9]/', '', trim($num));
                if (!empty($cleanNum)) {
                    // Auto-fix ID format (628...)
                    if (str_starts_with($cleanNum, '08')) {
                        $cleanNum = '62' . substr($cleanNum, 1);
                    }
                    $targets[] = $cleanNum;
                }
            }
            $targets = array_unique($targets);
        }

        // Create Targets & Dispatch Jobs
        foreach ($targets as $phone) {
            $target = WaBroadcastTarget::create([
                'wa_broadcast_id' => $broadcast->id,
                'phone_number' => $phone,
                'status' => 'pending'
            ]);

            // Dispatch Job
            SendBroadcastJob::dispatch($target);
        }

        return redirect()->route('whatsapp.broadcast.index')
            ->with('success', 'Broadcast created and processing started for ' . count($targets) . ' targets.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WaBroadcast $broadcast): View
    {
        $broadcast->load(['targets' => function($q) {
            $q->paginate(20);
        }]);

        $stats = [
            'total' => $broadcast->targets()->count(),
            'sent' => $broadcast->targets()->where('status', 'sent')->count(),
            'failed' => $broadcast->targets()->where('status', 'failed')->count(),
            'pending' => $broadcast->targets()->where('status', 'pending')->count(),
        ];

        return view('pages.whatsapp.broadcast.show', [
            'broadcast' => $broadcast,
            'stats' => $stats
        ]);
    }
}
