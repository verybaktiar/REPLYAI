<?php

namespace App\Http\Controllers;

use App\Models\WaMessage;
use App\Models\WaBroadcast;
use App\Models\WaBroadcastTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WhatsAppAnalyticsController extends Controller
{
    public function index()
    {
        // 1. Summary Cards
        $totalMessages = WaMessage::count();
        $totalContacts = WaMessage::distinct('phone_number')->count('phone_number');
        $totalBroadcasts = WaBroadcast::count();
        $broadcastsSent = WaBroadcastTarget::where('status', 'sent')->count();

        // 2. Daily Stats (Last 7 Days)
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        
        $dailyStats = WaMessage::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(CASE WHEN direction = "incoming" THEN 1 ELSE 0 END) as incoming'),
            DB::raw('SUM(CASE WHEN direction = "outgoing" THEN 1 ELSE 0 END) as outgoing')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Fill missing dates
        $chartData = [
            'dates' => [],
            'incoming' => [],
            'outgoing' => []
        ];

        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $stat = $dailyStats->firstWhere('date', $date);
            
            $chartData['dates'][] = Carbon::parse($date)->format('d M');
            $chartData['incoming'][] = $stat ? $stat->incoming : 0;
            $chartData['outgoing'][] = $stat ? $stat->outgoing : 0;
        }

        // 3. Top Active Contacts
        $topContacts = WaMessage::select('phone_number', 'push_name', DB::raw('count(*) as total'))
            ->where('remote_jid', 'not like', '%@g.us') // Exclude groups
            ->where('remote_jid', 'not like', '%@broadcast')
            ->groupBy('phone_number', 'push_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 4. Message Distribution (For Pie Chart)
        $distribution = [
            'user' => WaMessage::where('direction', 'incoming')->count(),
            'bot' => WaMessage::where('direction', 'outgoing')->whereNotNull('bot_reply')->count(),
            'manual' => WaMessage::where('direction', 'outgoing')->whereNull('bot_reply')->count(),
        ];

        return view('pages.whatsapp.analytics', [
            'summary' => compact('totalMessages', 'totalContacts', 'totalBroadcasts', 'broadcastsSent'),
            'chartData' => $chartData,
            'topContacts' => $topContacts,
            'distribution' => $distribution
        ]);
    }
}
