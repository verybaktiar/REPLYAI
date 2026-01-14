<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Clean up any existing test data just in case
    \App\Models\BusinessProfile::where('business_name', 'Test Cafe')->delete();

    $p = \App\Models\BusinessProfile::create([
        'business_name' => 'Test Cafe',
        'business_type' => 'fnb', // This previously failed because 'fnb' was not in enum
        'system_prompt_template' => 'test'
    ]);
    echo "SUCCESS: Created profile with type " . $p->business_type . "\n";
    $p->delete();
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
