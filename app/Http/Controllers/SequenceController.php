<?php

namespace App\Http\Controllers;

use App\Models\Sequence;
use App\Models\SequenceStep;
use App\Models\SequenceEnrollment;
use App\Services\SequenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * SequenceController
 * 
 * Controller untuk mengelola Sequences/Drip Campaign dari dashboard.
 */
class SequenceController extends Controller
{
    protected SequenceService $sequenceService;

    public function __construct(SequenceService $sequenceService)
    {
        $this->sequenceService = $sequenceService;
    }

    /**
     * Tampilkan daftar semua sequences
     */
    public function index()
    {
        $sequences = Sequence::withCount([
            'steps',
            'enrollments as active_enrollments_count' => function ($query) {
                $query->where('status', 'active');
            },
            'enrollments as completed_enrollments_count' => function ($query) {
                $query->where('status', 'completed');
            },
        ])->latest()->get();

        return view('pages.sequences.index', [
            'title' => 'Sequences',
            'sequences' => $sequences,
        ]);
    }

    /**
     * Tampilkan form buat sequence baru
     */
    public function create()
    {
        return view('pages.sequences.create', [
            'title' => 'Buat Sequence Baru',
            'triggerTypes' => Sequence::TRIGGER_TYPES,
            'platforms' => Sequence::PLATFORMS,
            'delayTypes' => SequenceStep::DELAY_TYPES,
        ]);
    }

    /**
     * Simpan sequence baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'required|in:manual,first_message,keyword,tag_added',
            'trigger_value' => 'nullable|string|max:255',
            'platform' => 'required|in:all,whatsapp,instagram,web',
            'is_active' => 'boolean',
            'steps' => 'required|array|min:1',
            'steps.*.delay_type' => 'required|in:immediately,minutes,hours,days',
            'steps.*.delay_value' => 'required|integer|min:0',
            'steps.*.message_content' => 'required|string',
        ], [
            'name.required' => 'Nama sequence wajib diisi',
            'steps.required' => 'Minimal harus ada 1 langkah pesan',
            'steps.min' => 'Minimal harus ada 1 langkah pesan',
            'steps.*.message_content.required' => 'Isi pesan wajib diisi',
        ]);

        DB::transaction(function () use ($validated) {
            // Buat sequence
            $sequence = Sequence::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'trigger_type' => $validated['trigger_type'],
                'trigger_value' => $validated['trigger_value'] ?? null,
                'platform' => $validated['platform'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Buat steps
            foreach ($validated['steps'] as $index => $stepData) {
                SequenceStep::create([
                    'sequence_id' => $sequence->id,
                    'order' => $index,
                    'delay_type' => $stepData['delay_type'],
                    'delay_value' => $stepData['delay_value'],
                    'message_content' => $stepData['message_content'],
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('sequences.index')
            ->with('success', 'Sequence berhasil dibuat! 🎉');
    }

    /**
     * Tampilkan form edit sequence
     */
    public function edit(Sequence $sequence)
    {
        $sequence->load('steps');

        return view('pages.sequences.edit', [
            'title' => 'Edit Sequence',
            'sequence' => $sequence,
            'triggerTypes' => Sequence::TRIGGER_TYPES,
            'platforms' => Sequence::PLATFORMS,
            'delayTypes' => SequenceStep::DELAY_TYPES,
        ]);
    }

    /**
     * Update sequence
     */
    public function update(Request $request, Sequence $sequence)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'required|in:manual,first_message,keyword,tag_added',
            'trigger_value' => 'nullable|string|max:255',
            'platform' => 'required|in:all,whatsapp,instagram,web',
            'is_active' => 'boolean',
            'steps' => 'required|array|min:1',
            'steps.*.id' => 'nullable|integer',
            'steps.*.delay_type' => 'required|in:immediately,minutes,hours,days',
            'steps.*.delay_value' => 'required|integer|min:0',
            'steps.*.message_content' => 'required|string',
        ]);

        DB::transaction(function () use ($validated, $sequence) {
            // Update sequence
            $sequence->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'trigger_type' => $validated['trigger_type'],
                'trigger_value' => $validated['trigger_value'] ?? null,
                'platform' => $validated['platform'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Hapus steps lama yang tidak ada di request
            $keepStepIds = collect($validated['steps'])
                ->pluck('id')
                ->filter()
                ->toArray();

            $sequence->steps()->whereNotIn('id', $keepStepIds)->delete();

            // Update atau buat steps
            foreach ($validated['steps'] as $index => $stepData) {
                if (!empty($stepData['id'])) {
                    // Update existing step
                    SequenceStep::where('id', $stepData['id'])->update([
                        'order' => $index,
                        'delay_type' => $stepData['delay_type'],
                        'delay_value' => $stepData['delay_value'],
                        'message_content' => $stepData['message_content'],
                    ]);
                } else {
                    // Create new step
                    SequenceStep::create([
                        'sequence_id' => $sequence->id,
                        'order' => $index,
                        'delay_type' => $stepData['delay_type'],
                        'delay_value' => $stepData['delay_value'],
                        'message_content' => $stepData['message_content'],
                        'is_active' => true,
                    ]);
                }
            }
        });

        return redirect()->route('sequences.index')
            ->with('success', 'Sequence berhasil diupdate!');
    }

    /**
     * Hapus sequence
     */
    public function destroy(Sequence $sequence)
    {
        $sequence->delete(); // Steps dan enrollments akan terhapus karena cascade

        if (request()->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Sequence berhasil dihapus']);
        }

        return redirect()->route('sequences.index')
            ->with('success', 'Sequence berhasil dihapus');
    }

    /**
     * Toggle status aktif sequence
     */
    public function toggle(Sequence $sequence)
    {
        $sequence->update(['is_active' => !$sequence->is_active]);

        if (request()->wantsJson()) {
            return response()->json([
                'ok' => true,
                'is_active' => $sequence->is_active,
                'message' => $sequence->is_active ? 'Sequence diaktifkan' : 'Sequence dinonaktifkan',
            ]);
        }

        return back()->with('success', 
            $sequence->is_active ? 'Sequence diaktifkan' : 'Sequence dinonaktifkan'
        );
    }

    /**
     * Tampilkan daftar enrollment sequence
     */
    public function enrollments(Sequence $sequence)
    {
        $enrollments = $sequence->enrollments()
            ->with('currentStep')
            ->latest('enrolled_at')
            ->paginate(20);

        return view('pages.sequences.enrollments', [
            'title' => 'Enrollments - ' . $sequence->name,
            'sequence' => $sequence,
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Batalkan enrollment
     */
    public function cancelEnrollment(SequenceEnrollment $enrollment)
    {
        $enrollment->cancel();

        if (request()->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Enrollment dibatalkan']);
        }

        return back()->with('success', 'Enrollment dibatalkan');
    }

    /**
     * Manual enroll kontak ke sequence
     */
    public function manualEnroll(Request $request, Sequence $sequence)
    {
        $validated = $request->validate([
            'contact_identifier' => 'required|string',
            'platform' => 'required|in:whatsapp,instagram,web',
            'contact_name' => 'nullable|string|max:255',
        ]);

        $enrollment = $this->sequenceService->enrollContact(
            $sequence,
            $validated['contact_identifier'],
            $validated['platform'],
            $validated['contact_name'] ?? null
        );

        if (!$enrollment) {
            return back()->with('error', 'Gagal mendaftarkan kontak');
        }

        return back()->with('success', 'Kontak berhasil didaftarkan ke sequence');
    }
}
