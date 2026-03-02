<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactSegment;
use App\Models\ContactSegmentMember;
use App\Models\Conversation;
use App\Models\WaConversation;
use App\Models\WaMessage;
use App\Models\Tag;
use App\Models\ContactCustomField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContactSegmentController extends Controller
{
    /**
     * Display a listing of segments.
     */
    public function index()
    {
        $segments = ContactSegment::withCount('members')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Get statistics
        $stats = [
            'total_segments' => ContactSegment::where('user_id', auth()->id())->count(),
            'auto_update_segments' => ContactSegment::where('user_id', auth()->id())->where('is_auto_update', true)->count(),
            'manual_segments' => ContactSegment::where('user_id', auth()->id())->where('is_auto_update', false)->count(),
            'total_contacts_in_segments' => ContactSegmentMember::whereHas('segment', function($q) {
                $q->where('user_id', auth()->id());
            })->count(),
        ];

        return view('pages.segments.index', compact('segments', 'stats'));
    }

    /**
     * Show the form for creating a new segment.
     */
    public function create()
    {
        $colors = ContactSegment::COLORS;
        $tags = Tag::where('user_id', auth()->id())->pluck('name');
        $customFields = ContactCustomField::where('user_id', auth()->id())->get();

        return view('pages.segments.create', compact('colors', 'tags', 'customFields'));
    }

    /**
     * Store a newly created segment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'required|in:' . implode(',', array_keys(ContactSegment::COLORS)),
            'is_auto_update' => 'boolean',
            'filters' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $segment = ContactSegment::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'is_auto_update' => $request->boolean('is_auto_update', false),
            'filters' => $request->filters,
            'contacts_count' => 0,
        ]);

        // If auto-update, apply filters to populate segment
        if ($segment->is_auto_update && !empty($segment->filters)) {
            $this->applyFiltersToSegment($segment);
        }

        return redirect()->route('segments.show', $segment)
            ->with('success', 'Segment berhasil dibuat!');
    }

    /**
     * Display the specified segment.
     */
    public function show(Request $request, ContactSegment $segment)
    {
        $this->authorize('view', $segment);

        $search = $request->get('search', '');
        $platform = $request->get('platform', 'all');

        // Get contacts query
        $contacts = $this->getSegmentContactsQuery($segment, $search, $platform);
        
        $contacts = $contacts->paginate(20);

        // Get all available tags for add contact modal
        $availableTags = Tag::where('user_id', auth()->id())->pluck('name');

        return view('pages.segments.show', compact('segment', 'contacts', 'availableTags', 'search', 'platform'));
    }

    /**
     * Show the form for editing the segment.
     */
    public function edit(ContactSegment $segment)
    {
        $this->authorize('update', $segment);

        $colors = ContactSegment::COLORS;
        $tags = Tag::where('user_id', auth()->id())->pluck('name');
        $customFields = ContactCustomField::where('user_id', auth()->id())->get();

        return view('pages.segments.edit', compact('segment', 'colors', 'tags', 'customFields'));
    }

    /**
     * Update the specified segment.
     */
    public function update(Request $request, ContactSegment $segment)
    {
        $this->authorize('update', $segment);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'required|in:' . implode(',', array_keys(ContactSegment::COLORS)),
            'is_auto_update' => 'boolean',
            'filters' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $wasAutoUpdate = $segment->is_auto_update;

        $segment->update([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'is_auto_update' => $request->boolean('is_auto_update', false),
            'filters' => $request->filters,
        ]);

        // If switched to auto-update or filters changed, reapply filters
        if ($segment->is_auto_update && !empty($segment->filters)) {
            // Clear existing contacts and reapply
            $segment->members()->delete();
            $this->applyFiltersToSegment($segment);
        } elseif ($wasAutoUpdate && !$segment->is_auto_update) {
            // Switched to manual - keep existing contacts
            $segment->updateContactsCount();
        }

        return redirect()->route('segments.show', $segment)
            ->with('success', 'Segment berhasil diperbarui!');
    }

    /**
     * Remove the specified segment.
     */
    public function destroy(ContactSegment $segment)
    {
        $this->authorize('delete', $segment);

        $segment->members()->delete();
        $segment->delete();

        return redirect()->route('segments.index')
            ->with('success', 'Segment berhasil dihapus!');
    }

    /**
     * Add a contact to segment.
     */
    public function addContact(Request $request, ContactSegment $segment)
    {
        $this->authorize('update', $segment);

        $validator = Validator::make($request->all(), [
            'contact_type' => 'required|in:whatsapp,instagram',
            'contact_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $contactType = $request->contact_type;
        $contactId = $request->contact_id;

        // Resolve contact model
        $contact = $this->resolveContact($contactType, $contactId);

        if (!$contact) {
            return back()->with('error', 'Kontak tidak ditemukan.');
        }

        // Check if already in segment
        if ($segment->hasContact($contact)) {
            return back()->with('info', 'Kontak sudah ada di segment ini.');
        }

        $segment->addContact($contact);
        $segment->updateContactsCount();

        return back()->with('success', 'Kontak berhasil ditambahkan ke segment!');
    }

    /**
     * Remove a contact from segment.
     */
    public function removeContact(ContactSegment $segment, string $contactType, string $contactId)
    {
        $this->authorize('update', $segment);

        // For auto-update segments, don't allow manual removal
        if ($segment->is_auto_update) {
            return back()->with('error', 'Segment auto-update tidak dapat diubah manual. Silakan ubah filter criteria.');
        }

        $contact = $this->resolveContact($contactType, $contactId);

        if ($contact) {
            $segment->removeContact($contact);
            $segment->updateContactsCount();
        }

        return back()->with('success', 'Kontak berhasil dihapus dari segment!');
    }

    /**
     * Bulk add contacts to segment.
     */
    public function bulkAddContacts(Request $request, ContactSegment $segment)
    {
        $this->authorize('update', $segment);

        // For auto-update segments, don't allow manual addition
        if ($segment->is_auto_update) {
            return response()->json(['error' => 'Segment auto-update tidak dapat diubah manual.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'contacts' => 'required|array',
            'contacts.*.type' => 'required|in:whatsapp,instagram',
            'contacts.*.id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $added = 0;
        $skipped = 0;

        foreach ($request->contacts as $contactData) {
            $contact = $this->resolveContact($contactData['type'], $contactData['id']);

            if ($contact && !$segment->hasContact($contact)) {
                $segment->addContact($contact);
                $added++;
            } else {
                $skipped++;
            }
        }

        $segment->updateContactsCount();

        return response()->json([
            'success' => true,
            'added' => $added,
            'skipped' => $skipped,
            'message' => "{$added} kontak berhasil ditambahkan, {$skipped} dilewati.",
        ]);
    }

    /**
     * Get paginated contacts in segment.
     */
    public function getSegmentContacts(Request $request, ContactSegment $segment)
    {
        $this->authorize('view', $segment);

        $search = $request->get('search', '');
        $platform = $request->get('platform', 'all');
        $perPage = $request->get('per_page', 20);

        $contacts = $this->getSegmentContactsQuery($segment, $search, $platform)
            ->paginate($perPage);

        return response()->json([
            'contacts' => $contacts->items(),
            'pagination' => [
                'current_page' => $contacts->currentPage(),
                'last_page' => $contacts->lastPage(),
                'per_page' => $contacts->perPage(),
                'total' => $contacts->total(),
            ],
        ]);
    }

    /**
     * Update filter criteria for auto-update segment.
     */
    public function updateFilters(Request $request, ContactSegment $segment)
    {
        $this->authorize('update', $segment);

        $validator = Validator::make($request->all(), [
            'filters' => 'required|array',
            'filters.platform' => 'nullable|in:whatsapp,instagram,both',
            'filters.tags' => 'nullable|array',
            'filters.last_active_days' => 'nullable|integer|min:1',
            'filters.message_count_min' => 'nullable|integer|min:0',
            'filters.message_count_max' => 'nullable|integer|min:0',
            'filters.custom_fields' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $segment->update(['filters' => $request->filters]);

        // Reapply filters if auto-update
        if ($segment->is_auto_update) {
            $segment->members()->delete();
            $this->applyFiltersToSegment($segment);
        }

        return response()->json([
            'success' => true,
            'segment' => $segment->fresh(),
            'contacts_count' => $segment->members()->count(),
        ]);
    }

    /**
     * Preview contacts matching filters (without saving).
     */
    public function previewFilters(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filters' => 'required|array',
            'filters.platform' => 'nullable|in:whatsapp,instagram,both',
            'filters.tags' => 'nullable|array',
            'filters.last_active_days' => 'nullable|integer|min:1',
            'filters.message_count_min' => 'nullable|integer|min:0',
            'filters.message_count_max' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $count = $this->getContactsByFilters($request->filters)->count();

        // Get sample contacts (first 5)
        $sample = $this->getContactsByFilters($request->filters)->take(5)->map(function ($contact) {
            return [
                'id' => $contact['id'],
                'name' => $contact['name'],
                'platform' => $contact['platform'],
            ];
        });

        return response()->json([
            'count' => $count,
            'sample' => $sample,
        ]);
    }

    /**
     * Get available contacts for adding to segment.
     */
    public function getAvailableContacts(Request $request, ContactSegment $segment)
    {
        $this->authorize('update', $segment);

        $search = $request->get('search', '');
        $platform = $request->get('platform', 'all');
        $limit = $request->get('limit', 20);

        $contacts = collect();

        // WhatsApp contacts
        if ($platform === 'all' || $platform === 'whatsapp') {
            $waQuery = WaMessage::select('phone_number', 'push_name')
                ->selectRaw('COUNT(*) as messages_count')
                ->selectRaw('MAX(created_at) as last_active')
                ->groupBy('phone_number', 'push_name');

            if ($search) {
                $waQuery->where(function ($q) use ($search) {
                    $q->where('phone_number', 'like', "%{$search}%")
                        ->orWhere('push_name', 'like', "%{$search}%");
                });
            }

            $waContacts = $waQuery->limit($limit)->get()->map(function ($c) use ($segment) {
                $waConversation = WaConversation::where('phone_number', $c->phone_number)
                    ->where('user_id', auth()->id())
                    ->first();
                
                return [
                    'id' => 'wa_' . $c->phone_number,
                    'type' => 'whatsapp',
                    'type_id' => $c->phone_number,
                    'name' => $c->push_name ?: '+' . $c->phone_number,
                    'phone' => '+' . $c->phone_number,
                    'platform' => 'whatsapp',
                    'messages_count' => $c->messages_count,
                    'last_active' => $c->last_active,
                    'already_in_segment' => $waConversation ? $segment->hasContact($waConversation) : false,
                ];
            });

            $contacts = $contacts->merge($waContacts);
        }

        // Instagram contacts
        if ($platform === 'all' || $platform === 'instagram') {
            $igQuery = Conversation::withCount('messages');

            if ($search) {
                $igQuery->where(function ($q) use ($search) {
                    $q->where('display_name', 'like', "%{$search}%")
                        ->orWhere('ig_username', 'like', "%{$search}%");
                });
            }

            $igContacts = $igQuery->limit($limit)->get()->map(function ($c) use ($segment) {
                return [
                    'id' => 'ig_' . $c->id,
                    'type' => 'instagram',
                    'type_id' => $c->id,
                    'name' => $c->display_name ?? $c->ig_username ?? 'Tanpa Nama',
                    'username' => $c->ig_username,
                    'platform' => 'instagram',
                    'messages_count' => $c->messages_count,
                    'last_active' => $c->updated_at,
                    'already_in_segment' => $segment->hasContact($c),
                ];
            });

            $contacts = $contacts->merge($igContacts);
        }

        // Sort by last active
        $contacts = $contacts->sortByDesc('last_active')->values();

        return response()->json([
            'contacts' => $contacts->take($limit),
        ]);
    }

    /**
     * Apply filters to populate auto-update segment.
     */
    private function applyFiltersToSegment(ContactSegment $segment): void
    {
        $contacts = $this->getContactsByFilters($segment->filters ?? []);

        foreach ($contacts as $contactData) {
            $contact = $this->resolveContact($contactData['platform'], $contactData['id']);
            if ($contact) {
                $segment->addContact($contact);
            }
        }

        $segment->updateContactsCount();
    }

    /**
     * Get contacts by filters.
     */
    private function getContactsByFilters(array $filters): \Illuminate\Support\Collection
    {
        $platform = $filters['platform'] ?? 'both';
        $tags = $filters['tags'] ?? [];
        $lastActiveDays = $filters['last_active_days'] ?? null;
        $messageCountMin = $filters['message_count_min'] ?? null;
        $messageCountMax = $filters['message_count_max'] ?? null;

        $contacts = collect();

        // WhatsApp contacts
        if ($platform === 'both' || $platform === 'whatsapp') {
            $waQuery = WaMessage::select('phone_number', 'push_name')
                ->selectRaw('COUNT(*) as messages_count')
                ->selectRaw('MAX(created_at) as last_active')
                ->groupBy('phone_number', 'push_name');

            if ($messageCountMin !== null) {
                $waQuery->havingRaw('COUNT(*) >= ?', [$messageCountMin]);
            }
            if ($messageCountMax !== null && $messageCountMax > 0) {
                $waQuery->havingRaw('COUNT(*) <= ?', [$messageCountMax]);
            }
            if ($lastActiveDays !== null) {
                $waQuery->havingRaw('MAX(created_at) >= ?', [now()->subDays($lastActiveDays)]);
            }

            $waContacts = $waQuery->get()->map(function ($c) {
                return [
                    'id' => $c->phone_number,
                    'name' => $c->push_name ?: '+' . $c->phone_number,
                    'platform' => 'whatsapp',
                    'messages_count' => $c->messages_count,
                    'last_active' => $c->last_active,
                ];
            });

            $contacts = $contacts->merge($waContacts);
        }

        // Instagram contacts
        if ($platform === 'both' || $platform === 'instagram') {
            $igQuery = Conversation::withCount('messages');

            if (!empty($tags)) {
                $igQuery->where(function ($q) use ($tags) {
                    foreach ($tags as $tag) {
                        $q->orWhereJsonContains('tags', $tag);
                    }
                });
            }

            if ($lastActiveDays !== null) {
                $igQuery->where('updated_at', '>=', now()->subDays($lastActiveDays));
            }

            if ($messageCountMin !== null) {
                $igQuery->having('messages_count', '>=', $messageCountMin);
            }
            if ($messageCountMax !== null && $messageCountMax > 0) {
                $igQuery->having('messages_count', '<=', $messageCountMax);
            }

            $igContacts = $igQuery->get()->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->display_name ?? $c->ig_username ?? 'Tanpa Nama',
                    'platform' => 'instagram',
                    'messages_count' => $c->messages_count,
                    'last_active' => $c->updated_at,
                ];
            });

            $contacts = $contacts->merge($igContacts);
        }

        return $contacts->sortByDesc('last_active');
    }

    /**
     * Get segment contacts query.
     */
    private function getSegmentContactsQuery(ContactSegment $segment, string $search = '', string $platform = 'all')
    {
        $memberIds = $segment->members()->pluck('contact_id', 'contact_type')->toArray();

        $contacts = collect();

        // Get WhatsApp contacts
        if (($platform === 'all' || $platform === 'whatsapp') && !empty($memberIds)) {
            $waMemberIds = array_filter($memberIds, function ($type) {
                return $type === WaConversation::class || str_contains($type, 'WaConversation');
            }, ARRAY_FILTER_USE_KEY);

            if (!empty($waMemberIds)) {
                $waIds = array_values($waMemberIds);
                
                // If stored by phone_number
                $waQuery = WaMessage::select('phone_number', 'push_name')
                    ->selectRaw('COUNT(*) as messages_count')
                    ->selectRaw('MAX(created_at) as last_active')
                    ->whereIn('phone_number', $waIds)
                    ->groupBy('phone_number', 'push_name');

                if ($search) {
                    $waQuery->where(function ($q) use ($search) {
                        $q->where('phone_number', 'like', "%{$search}%")
                            ->orWhere('push_name', 'like', "%{$search}%");
                    });
                }

                $waContacts = $waQuery->get()->map(function ($c) {
                    return [
                        'id' => 'wa_' . $c->phone_number,
                        'type' => 'whatsapp',
                        'type_id' => $c->phone_number,
                        'name' => $c->push_name ?: '+' . $c->phone_number,
                        'phone' => '+' . $c->phone_number,
                        'platform' => 'whatsapp',
                        'messages_count' => $c->messages_count,
                        'last_active' => $c->last_active,
                        'avatar' => null,
                    ];
                });

                $contacts = $contacts->merge($waContacts);
            }
        }

        // Get Instagram contacts
        if (($platform === 'all' || $platform === 'instagram') && !empty($memberIds)) {
            $igMemberIds = array_filter($memberIds, function ($type) {
                return $type === Conversation::class || str_contains($type, 'Conversation');
            }, ARRAY_FILTER_USE_KEY);

            if (!empty($igMemberIds)) {
                $igIds = array_values($igMemberIds);
                
                $igQuery = Conversation::withCount('messages')
                    ->whereIn('id', $igIds);

                if ($search) {
                    $igQuery->where(function ($q) use ($search) {
                        $q->where('display_name', 'like', "%{$search}%")
                            ->orWhere('ig_username', 'like', "%{$search}%");
                    });
                }

                $igContacts = $igQuery->get()->map(function ($c) {
                    return [
                        'id' => 'ig_' . $c->id,
                        'type' => 'instagram',
                        'type_id' => $c->id,
                        'name' => $c->display_name ?? $c->ig_username ?? 'Tanpa Nama',
                        'username' => $c->ig_username,
                        'platform' => 'instagram',
                        'messages_count' => $c->messages_count,
                        'last_active' => $c->updated_at,
                        'avatar' => $c->avatar,
                    ];
                });

                $contacts = $contacts->merge($igContacts);
            }
        }

        // Return paginated collection
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $contacts->sortByDesc('last_active')->values(),
            $contacts->count(),
            20,
            request()->get('page', 1),
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Resolve contact by type and id.
     */
    private function resolveContact(string $type, string $id)
    {
        if ($type === 'whatsapp' || $type === WaConversation::class) {
            // Try to find WaConversation first, or create from messages
            $conversation = WaConversation::where('phone_number', $id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$conversation) {
                // Check if there are messages for this number
                $hasMessages = WaMessage::where('phone_number', $id)->exists();
                if ($hasMessages) {
                    // Create a conversation record
                    $conversation = WaConversation::create([
                        'user_id' => auth()->id(),
                        'phone_number' => $id,
                        'display_name' => null,
                        'status' => WaConversation::STATUS_BOT_ACTIVE,
                    ]);
                }
            }

            return $conversation;
        }

        if ($type === 'instagram' || $type === Conversation::class) {
            return Conversation::where('id', $id)
                ->where('user_id', auth()->id())
                ->first();
        }

        return null;
    }
}
