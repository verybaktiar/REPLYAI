<?php

$file = 'c:\laragon\www\Projek\Replyai\REPLYAI\storage\logs\laravel.log';
$content = file_get_contents($file);

// Find last occurrence of "WhatsApp AI HTTP error"
$pos = strrpos($content, 'WhatsApp AI HTTP error');

if ($pos === false) {
    echo "Error tag not found in log.\n";
    exit;
}

// Print the context around the error
echo substr($content, $pos, 2000);
