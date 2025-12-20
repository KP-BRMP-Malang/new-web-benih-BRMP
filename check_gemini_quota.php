<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

$apiKey = config('services.gemini.api_key');
$model = config('services.gemini.model', 'gemini-1.5-flash');
$baseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');

echo "--------------------------------------------------\n";
echo "Inspecting Gemini API Headers for Quota Info\n";
echo "--------------------------------------------------\n";

$url = "{$baseUrl}/models/{$model}:generateContent?key={$apiKey}";
$payload = [
    'contents' => [
        ['parts' => [['text' => 'Hello']]]
    ],
    'generationConfig' => ['maxOutputTokens' => 10]
];

try {
    $response = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, $payload);

    echo "Status Code: " . $response->status() . "\n\n";
    echo "--- RESPONSE HEADERS ---\n";
    $headers = $response->headers();
    foreach ($headers as $key => $values) {
        // Filter purely for rate/quota related headers or print all interesting ones
        echo "{$key}: " . implode(', ', $values) . "\n";
    }
    echo "\n";
    
    if ($response->failed()) {
        echo "Response Body: " . $response->body() . "\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
