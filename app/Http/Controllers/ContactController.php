<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\WaMessage;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $platform = $request->get('platform', 'all');
        $search = $request->get('search', '');
        $tag = $request->get('tag', '');
        
        // Collect contacts from both platforms
        $contacts = collect();
        
        // Instagram contacts
        if ($platform === 'all' || $platform === 'instagram') {
            $igQuery = Conversation::withCount('messages');
            
            if ($search) {
                $igQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('display_name', 'like', "%{$search}%");
                });
            }
            
            if ($tag) {
                $igQuery->whereJsonContains('tags', $tag);
            }
            
            $igContacts = $igQuery->get()->map(function($c) {
                return [
                    'id' => 'ig_' . $c->id,
                    'name' => $c->display_name ?? $c->name ?? 'Tanpa Nama',
                    'phone' => $c->phone_number ?? '-',
                    'platform' => 'instagram',
                    'messages_count' => $c->messages_count,
                    'tags' => $c->tags ?? [],
                    'avatar' => $c->avatar,
                    'last_active' => $c->updated_at,
                    'conversation_id' => $c->id,
                    'source' => 'instagram',
                ];
            });
            
            $contacts = $contacts->merge($igContacts);
        }
        
        // WhatsApp contacts
        if ($platform === 'all' || $platform === 'whatsapp') {
            $waQuery = WaMessage::select('phone_number', 'push_name')
                ->selectRaw('COUNT(*) as messages_count')
                ->selectRaw('MAX(created_at) as last_active')
                ->groupBy('phone_number', 'push_name');
            
            if ($search) {
                $waQuery->where(function($q) use ($search) {
                    $q->where('phone_number', 'like', "%{$search}%")
                      ->orWhere('push_name', 'like', "%{$search}%");
                });
            }
            
            $waContacts = $waQuery->get()->map(function($c) {
                return [
                    'id' => 'wa_' . $c->phone_number,
                    'name' => $c->push_name ?: '+' . $c->phone_number,
                    'phone' => '+' . $c->phone_number,
                    'platform' => 'whatsapp',
                    'messages_count' => $c->messages_count,
                    'tags' => [],
                    'avatar' => null,
                    'last_active' => $c->last_active,
                    'conversation_id' => null,
                    'source' => 'whatsapp',
                ];
            });
            
            $contacts = $contacts->merge($waContacts);
        }
        
        // Sort by last active
        $contacts = $contacts->sortByDesc('last_active')->values();
        
        // Simple pagination
        $page = $request->get('page', 1);
        $perPage = 15;
        $total = $contacts->count();
        $contacts = $contacts->forPage($page, $perPage);

        return view('pages.contacts.index', [
            'contacts' => $contacts,
            'currentPlatform' => $platform,
            'currentSearch' => $search,
            'currentTag' => $tag,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'hasMore' => ($page * $perPage) < $total,
        ]);
    }

    /**
     * Export contacts as CSV
     */
    public function export(Request $request)
    {
        $platform = $request->get('platform', 'all');
        
        $csvContent = "Nama,Telepon,Platform,Total Pesan,Terakhir Aktif\n";
        
        // Instagram contacts
        if ($platform === 'all' || $platform === 'instagram') {
            $igContacts = Conversation::withCount('messages')->get();
            foreach ($igContacts as $c) {
                $name = str_replace(',', ' ', $c->display_name ?? $c->name ?? 'Tanpa Nama');
                $csvContent .= "{$name},{$c->phone_number},Instagram,{$c->messages_count},{$c->updated_at}\n";
            }
        }
        
        // WhatsApp contacts
        if ($platform === 'all' || $platform === 'whatsapp') {
            $waContacts = WaMessage::select('phone_number', 'push_name')
                ->selectRaw('COUNT(*) as messages_count')
                ->selectRaw('MAX(created_at) as last_active')
                ->groupBy('phone_number', 'push_name')
                ->get();
                
            foreach ($waContacts as $c) {
                $name = str_replace(',', ' ', $c->push_name ?: '+' . $c->phone_number);
                $csvContent .= "{$name},+{$c->phone_number},WhatsApp,{$c->messages_count},{$c->last_active}\n";
            }
        }

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="contacts_' . date('Y-m-d') . '.csv"',
        ]);
    }
}
