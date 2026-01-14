<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BusinessProfile;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

$keys = array_keys(BusinessProfile::INDUSTRIES);
echo "Available Keys: " . implode(', ', $keys) . "\n";

$testInput = 'property'; // The one user tried
echo "Testing input: '$testInput'\n";

$validator = Validator::make(
    ['business_type' => $testInput],
    ['business_type' => ['required', Rule::in($keys)]]
);

if ($validator->fails()) {
    echo "FAILED: " . implode(', ', $validator->errors()->all()) . "\n";
} else {
    echo "SUCCESS: Validation passed for '$testInput'\n";
}

// Check type of keys vs input
foreach ($keys as $k) {
    if ($k === $testInput) {
        echo "Match found: key '$k' equals input '$testInput'\n";
    }
}
