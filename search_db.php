<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$search = 'tekno';
$tables = DB::select('SHOW TABLES');

foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    
    // Skip some tables that are too big or irrelevant
    if (in_array($tableName, ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches'])) {
        continue;
    }

    $columns = DB::select("DESC `$tableName` ");
    $textColumns = [];
    foreach ($columns as $column) {
        if (preg_match('/(char|text|varchar)/i', $column->Type)) {
            $textColumns[] = $column->Field;
        }
    }

    if (empty($textColumns)) continue;

    $query = DB::table($tableName);
    $query->where(function($q) use ($textColumns, $search) {
        foreach ($textColumns as $col) {
            $q->orWhere($col, 'LIKE', "%$search%");
        }
    });

    $results = $query->limit(5)->get();

    if ($results->count() > 0) {
        echo "Table: $tableName\n";
        foreach ($results as $row) {
            echo "  Row: " . json_encode($row, JSON_PRETTY_PRINT) . "\n";
        }
        echo "--------------------------------------------------\n";
    }
}
echo "Search finished.\n";
