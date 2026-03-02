<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use App\Models\SupportTicket;
use App\Models\PromoCode;
use App\Models\SystemSetting;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

/**
 * Controller untuk Data Import/Export
 */
class DataTransferController extends Controller
{
    protected $exportableTypes = [
        'users' => [
            'label' => 'Users',
            'icon' => 'group',
            'model' => User::class,
            'columns' => ['id', 'name', 'email', 'phone', 'company', 'role', 'status', 'created_at'],
        ],
        'payments' => [
            'label' => 'Payments',
            'icon' => 'payments',
            'model' => Payment::class,
            'columns' => ['id', 'user_id', 'invoice_number', 'amount', 'status', 'payment_method', 'created_at', 'paid_at'],
        ],
        'tickets' => [
            'label' => 'Support Tickets',
            'icon' => 'support_agent',
            'model' => SupportTicket::class,
            'columns' => ['id', 'user_id', 'subject', 'status', 'priority', 'created_at', 'resolved_at'],
        ],
        'promo_codes' => [
            'label' => 'Promo Codes',
            'icon' => 'local_offer',
            'model' => PromoCode::class,
            'columns' => ['id', 'code', 'discount_type', 'discount_value', 'usage_limit', 'used_count', 'valid_from', 'valid_until', 'is_active'],
        ],
    ];

    protected $backupTables = [
        'users',
        'payments',
        'subscriptions',
        'support_tickets',
        'promo_codes',
        'plans',
        'whatsapp_devices',
        'instagram_accounts',
        'kb_articles',
        'auto_reply_rules',
    ];

    /**
     * Tampilkan halaman import/export
     */
    public function index()
    {
        $backups = $this->getBackupFiles();
        
        return view('admin.data-transfer.index', [
            'exportableTypes' => $this->exportableTypes,
            'backupTables' => $this->backupTables,
            'backups' => $backups,
        ]);
    }

    /**
     * Export data to CSV/Excel
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:users,payments,tickets,promo_codes',
            'format' => 'required|in:csv,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $type = $validated['type'];
        $format = $validated['format'];
        $config = $this->exportableTypes[$type];

        // Build query
        $query = $config['model']::query();

        // Apply date filters
        if (!empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        $data = $query->get();

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'data_export',
            "Exported {$config['label']} data",
            [
                'type' => $type,
                'format' => $format,
                'count' => $data->count(),
                'date_from' => $validated['date_from'] ?? null,
                'date_to' => $validated['date_to'] ?? null,
            ]
        );

        if ($format === 'csv') {
            return $this->exportToCsv($data, $config['columns'], $type);
        }

        return $this->exportToExcel($data, $config['columns'], $type);
    }

    /**
     * Export to CSV
     */
    protected function exportToCsv($data, $columns, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}_" . now()->format('Y-m-d_H-i-s') . '.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, $columns);

            // Data
            foreach ($data as $row) {
                $rowData = [];
                foreach ($columns as $column) {
                    $value = $row->{$column} ?? '';
                    // Format dates
                    if ($value instanceof \Carbon\Carbon) {
                        $value = $value->format('Y-m-d H:i:s');
                    }
                    $rowData[] = $value;
                }
                fputcsv($file, $rowData);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel (HTML table format for simplicity)
     */
    protected function exportToExcel($data, $columns, $filename)
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename={$filename}_" . now()->format('Y-m-d_H-i-s') . '.xls',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ];

        $callback = function () use ($data, $columns) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta charset="UTF-8"></head><body>';
            echo '<table border="1">';
            
            // Headers
            echo '<tr>';
            foreach ($columns as $column) {
                echo '<th>' . ucwords(str_replace('_', ' ', $column)) . '</th>';
            }
            echo '</tr>';

            // Data
            foreach ($data as $row) {
                echo '<tr>';
                foreach ($columns as $column) {
                    $value = $row->{$column} ?? '';
                    if ($value instanceof \Carbon\Carbon) {
                        $value = $value->format('Y-m-d H:i:s');
                    }
                    echo '<td>' . htmlspecialchars($value) . '</td>';
                }
                echo '</tr>';
            }

            echo '</table></body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import users from CSV
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:users',
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'skip_header' => 'boolean',
        ]);

        $type = $validated['type'];
        $file = $request->file('file');
        $skipHeader = $request->boolean('skip_header', true);

        // Parse CSV
        $rows = [];
        $handle = fopen($file->getPathname(), 'r');
        
        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $rowIndex = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowIndex++;
            if ($skipHeader && $rowIndex === 1) {
                continue;
            }

            if ($type === 'users') {
                $rows[] = [
                    'row' => $rowIndex,
                    'name' => $row[0] ?? '',
                    'email' => $row[1] ?? '',
                    'phone' => $row[2] ?? '',
                    'company' => $row[3] ?? '',
                    'password' => $row[4] ?? '',
                ];
            }
        }
        fclose($handle);

        // Preview mode
        if ($request->has('preview')) {
            return response()->json([
                'success' => true,
                'preview' => array_slice($rows, 0, 5),
                'total' => count($rows),
            ]);
        }

        // Import mode
        $imported = 0;
        $errors = [];
        $skipped = 0;

        foreach ($rows as $rowData) {
            // Validate
            $validator = Validator::make($rowData, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'company' => 'nullable|string|max:255',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $rowData['row'],
                    'email' => $rowData['email'],
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            try {
                User::create([
                    'name' => $rowData['name'],
                    'email' => $rowData['email'],
                    'phone' => $rowData['phone'] ?: null,
                    'company' => $rowData['company'] ?: null,
                    'password' => bcrypt($rowData['password']),
                    'email_verified_at' => now(),
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = [
                    'row' => $rowData['row'],
                    'email' => $rowData['email'],
                    'errors' => [$e->getMessage()],
                ];
            }
        }

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'data_import',
            "Imported {$type} data",
            [
                'type' => $type,
                'total' => count($rows),
                'imported' => $imported,
                'errors' => count($errors),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Import completed: {$imported} imported, " . count($errors) . " errors",
            'imported' => $imported,
            'total' => count($rows),
            'errors' => $errors,
        ]);
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate($type)
    {
        if (!array_key_exists($type, $this->exportableTypes)) {
            abort(404, 'Template not found');
        }

        $templates = [
            'users' => [
                ['Name', 'Email', 'Phone', 'Company', 'Password'],
                ['John Doe', 'john@example.com', '08123456789', 'Acme Inc', 'password123'],
                ['Jane Smith', 'jane@example.com', '08987654321', 'Tech Corp', 'password456'],
            ],
            'payments' => [
                ['User ID', 'Invoice Number', 'Amount', 'Status', 'Payment Method'],
                ['1', 'INV-2024-001', '100000', 'paid', 'bank_transfer'],
            ],
            'tickets' => [
                ['User ID', 'Subject', 'Description', 'Priority'],
                ['1', 'Technical Support', 'Need help with setup', 'medium'],
            ],
            'promo_codes' => [
                ['Code', 'Discount Type', 'Discount Value', 'Usage Limit', 'Valid From', 'Valid Until'],
                ['DISCOUNT50', 'percentage', '50', '100', '2024-01-01', '2024-12-31'],
            ],
        ];

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$type}_template.csv",
        ];

        $callback = function () use ($templates, $type) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            foreach ($templates[$type] ?? [] as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'template_download',
            "Downloaded {$type} template"
        );

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Backup selected tables
     */
    public function backup(Request $request)
    {
        $validated = $request->validate([
            'tables' => 'required|array',
            'tables.*' => 'in:' . implode(',', $this->backupTables),
            'notes' => 'nullable|string|max:500',
        ]);

        $tables = $validated['tables'];
        $notes = $validated['notes'] ?? '';

        $backupDir = storage_path('app/backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup_' . now()->format('Y-m-d_H-i-s') . '_' . uniqid() . '.sql';
        $filepath = $backupDir . '/' . $filename;

        try {
            $sql = "-- REPLYAI Backup\n";
            $sql .= "-- Generated: " . now()->toDateTimeString() . "\n";
            $sql .= "-- Tables: " . implode(', ', $tables) . "\n";
            if ($notes) {
                $sql .= "-- Notes: " . $notes . "\n";
            }
            $sql .= "\n";

            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $sql .= "-- Table: {$table}\n";
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

                // Get create table SQL
                $createTable = DB::selectOne("SHOW CREATE TABLE `{$table}`");
                $createSql = $createTable->{'Create Table'};
                $sql .= $createSql . ";\n\n";

                // Get data
                $rows = DB::table($table)->get();
                if ($rows->isNotEmpty()) {
                    $sql .= "INSERT INTO `{$table}` VALUES\n";
                    $values = [];
                    foreach ($rows as $row) {
                        $rowValues = [];
                        foreach ((array) $row as $value) {
                            if ($value === null) {
                                $rowValues[] = 'NULL';
                            } else {
                                $rowValues[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $values[] = '(' . implode(',', $rowValues) . ')';
                    }
                    $sql .= implode(',\n', $values) . ";\n\n";
                }
            }

            file_put_contents($filepath, $sql);

            // Save backup metadata
            $metadata = [
                'filename' => $filename,
                'created_at' => now()->toDateTimeString(),
                'tables' => $tables,
                'size' => filesize($filepath),
                'notes' => $notes,
                'created_by' => Auth::guard('admin')->user()->name,
            ];

            $metadataFile = $backupDir . '/metadata.json';
            $allMetadata = [];
            if (file_exists($metadataFile)) {
                $allMetadata = json_decode(file_get_contents($metadataFile), true) ?: [];
            }
            $allMetadata[] = $metadata;
            file_put_contents($metadataFile, json_encode($allMetadata, JSON_PRETTY_PRINT));

            // Log activity
            AdminActivityLog::log(
                Auth::guard('admin')->user(),
                'backup_create',
                'Created database backup',
                [
                    'filename' => $filename,
                    'tables' => $tables,
                    'size' => $metadata['size'],
                ]
            );

            return back()->with('success', 'Backup created successfully: ' . $filename);
        } catch (\Exception $e) {
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Restore from backup
     */
    public function restore(Request $request)
    {
        $validated = $request->validate([
            'filename' => 'required|string',
        ]);

        $filename = basename($validated['filename']);
        $filepath = storage_path('app/backups/' . $filename);

        if (!file_exists($filepath)) {
            return back()->with('error', 'Backup file not found');
        }

        try {
            $sql = file_get_contents($filepath);
            
            // Split SQL into statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            DB::beginTransaction();

            foreach ($statements as $statement) {
                if (empty($statement) || str_starts_with($statement, '--')) {
                    continue;
                }
                DB::statement($statement);
            }

            DB::commit();

            // Log activity
            AdminActivityLog::log(
                Auth::guard('admin')->user(),
                'backup_restore',
                'Restored database from backup',
                ['filename' => $filename]
            );

            return back()->with('success', 'Database restored successfully from: ' . $filename);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Download backup file
     */
    public function downloadBackup(Request $request)
    {
        $filename = basename($request->input('filename'));
        $filepath = storage_path('app/backups/' . $filename);

        if (!file_exists($filepath)) {
            abort(404, 'Backup file not found');
        }

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'backup_download',
            'Downloaded backup file',
            ['filename' => $filename]
        );

        return response()->download($filepath, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Delete backup file
     */
    public function deleteBackup(Request $request)
    {
        $filename = basename($request->input('filename'));
        $filepath = storage_path('app/backups/' . $filename);

        if (!file_exists($filepath)) {
            return back()->with('error', 'Backup file not found');
        }

        unlink($filepath);

        // Update metadata
        $metadataFile = storage_path('app/backups/metadata.json');
        if (file_exists($metadataFile)) {
            $allMetadata = json_decode(file_get_contents($metadataFile), true) ?: [];
            $allMetadata = array_filter($allMetadata, function ($meta) use ($filename) {
                return $meta['filename'] !== $filename;
            });
            file_put_contents($metadataFile, json_encode(array_values($allMetadata), JSON_PRETTY_PRINT));
        }

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'backup_delete',
            'Deleted backup file',
            ['filename' => $filename]
        );

        return back()->with('success', 'Backup deleted successfully');
    }

    /**
     * Get list of backup files
     */
    protected function getBackupFiles()
    {
        $metadataFile = storage_path('app/backups/metadata.json');
        
        if (!file_exists($metadataFile)) {
            return [];
        }

        $metadata = json_decode(file_get_contents($metadataFile), true) ?: [];
        
        // Sort by created_at descending
        usort($metadata, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $metadata;
    }
}
