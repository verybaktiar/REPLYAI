<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Conversation;
use App\Services\AutoReplyEngine;
use Illuminate\Support\Facades\Log;

class SimulatorController extends Controller
{
    protected AutoReplyEngine $engine;

    public function __construct(AutoReplyEngine $engine)
    {
        $this->engine = $engine;
    }

    public function index()
    {
        return view('pages.simulator.index');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $messageText = trim($request->message);

        try {
            // Gunakan method khusus simulator (tidak simpan ke DB)
            $result = $this->engine->simulateMessage($messageText);

            if ($result && !empty($result['response'])) {
                return response()->json([
                    'success' => true,
                    'response' => $result['response'],
                    'source' => $result['source'] ?? 'unknown',
                    'ai_used' => $result['ai_used'] ?? false,
                    'ai_confidence' => $result['ai_confidence'] ?? null,
                ]);
            }

            return response()->json([
                'success' => true,
                'response' => '🤷 Bot tidak memiliki respons untuk pesan ini. (Akan diteruskan ke CS)',
                'source' => 'no_response',
                'ai_used' => false,
            ]);

        } catch (\Throwable $e) {
            Log::error('❌ Simulator error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
