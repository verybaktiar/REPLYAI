<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        // Fetch unique contacts from conversations
        // Assuming Conversation has 'contact_name', 'contact_phone', 'platform', etc.
        // Or we just list conversations as "Contacts" for now
        
        $query = Conversation::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
        }
        
        if ($request->has('tag') && $request->tag) {
            $query->whereJsonContains('tags', $request->tag);
        }
        
        // Paginate contacts
        $contacts = $query->latest()->paginate(10)->withQueryString();

        return view('pages.contacts.index', [
            'contacts' => $contacts
        ]);
    }
}
