<?php

$url = "http://localhost/Projek/Replyai/REPLYAI/public/api/web/chat";
$data = [
    'api_key' => 'rw_ou4CdK55ChwcpOVD6ArxEb0RtzBmFFy5',
    'visitor_id' => 'debug_user_5',
    'message' => 'halo',
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

curl_close($ch);
