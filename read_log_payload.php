<?php

$file = 'c:\laragon\www\Projek\Replyai\REPLYAI\storage\logs\laravel.log';
$content = file_get_contents($file);

// Find last occurrence of "Perplexity Payload:"
$pos = strrpos($content, 'Perplexity Payload:');

if ($pos === false) {
    echo "Payload tag not found in log.\n";
    exit;
}

$start = $pos + strlen('Perplexity Payload:');
$jsonStr = substr($content, $start);
// Find the array/json part. It might be logged as array structure or JSON.
// Laravel Log::info with array usually outputs json-like or print_r style.
// But if I passed array, Monolog formats it.

// Let's just print the next 2000 chars
echo substr($jsonStr, 0, 2000);
