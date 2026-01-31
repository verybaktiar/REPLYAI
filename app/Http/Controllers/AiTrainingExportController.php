<?php

namespace App\Http\Controllers;

use App\Models\AiTrainingExample;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiTrainingExportController extends Controller
{
    /**
     * Export approved AI training data to CSV
     */
    public function exportCSV(): StreamedResponse
    {
        $examples = AiTrainingExample::where('user_id', auth()->id())
            ->where('is_approved', true)
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ai_training_data_'.now()->format('Y-m-d').'.csv"',
        ];

        $callback = function () use ($examples) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'User Query', 'Assistant Response', 'Rating', 'Created At']);

            foreach ($examples as $row) {
                fputcsv($file, [
                    $row->id,
                    $row->user_query,
                    $row->assistant_response,
                    $row->rating,
                    $row->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export approved AI training data to JSON (Backup format)
     */
    public function exportJSON()
    {
        $examples = AiTrainingExample::where('user_id', auth()->id())
            ->where('is_approved', true)
            ->get();

        return response()->json($examples, 200, [
            'Content-Disposition' => 'attachment; filename="ai_training_backup_'.now()->format('Y-m-d').'.json"',
        ]);
    }
}
