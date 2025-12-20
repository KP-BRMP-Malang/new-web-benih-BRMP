<?php

use App\Services\Chatbot\ChatComposerService;
use App\DTO\RouterOutput;
use App\DTO\RetrievalCandidate;
use App\DTO\ChatResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$composer = app(ChatComposerService::class);

echo "\n--- TEST: Summary Logic for > 5 Products ---\n";

// Mock Router Output
$routerOutput = new RouterOutput(
    intent: 'product_search',
    filters: [],
    sources: ['products'],
    confidence: 0.9,
    clarificationNeeded: null
);

// Mock Candidates (12 items: 8 Sayuran, 4 Buah)
$candidates = [];

for ($i = 1; $i <= 8; $i++) {
    $candidates[] = new RetrievalCandidate(
        type: 'product',
        id: $i,
        title: "Benih Sayur $i",
        summary: "Deskripsi sayur $i",
        price: 10000,
        stock: 50,
        unit: 'pack',
        link: "http://test.com/sayur/$i",
        extra: ['plant_type_name' => 'Sayuran']
    );
}

for ($i = 9; $i <= 12; $i++) {
    $candidates[] = new RetrievalCandidate(
        type: 'product',
        id: $i,
        title: "Benih Buah $i",
        summary: "Deskripsi buah $i",
        price: 20000,
        stock: 50,
        unit: 'pack',
        link: "http://test.com/buah/$i",
        extra: ['plant_type_name' => 'Buah']
    );
}

echo "Total Candidates: " . count($candidates) . "\n";

echo "\n--- TEST 1: First Call (Should hit API/Mock) ---\n";
$start = microtime(true);
$response1 = $composer->compose("Ada benih apa saja?", $routerOutput, $candidates);
$time1 = microtime(true) - $start;
echo "Time: " . number_format($time1, 2) . "s\n";
echo "Message Length: " . strlen($response1->message) . "\n";

echo "\n--- TEST 2: Second Call (Should hit Cache) ---\n";
$start = microtime(true);
$response2 = $composer->compose("Ada benih apa saja?", $routerOutput, $candidates);
$time2 = microtime(true) - $start;
echo "Time: " . number_format($time2, 2) . "s\n";

if ($time2 < 0.1) {
    echo "\nPASS: Cache is working (response is instant).\n";
} else {
    echo "\nFAIL: Cache might not be working (Time: {$time2}s).\n";
}

if ($response1->message === $response2->message) {
    echo "PASS: Responses match.\n";
} else {
    echo "FAIL: Responses do not match.\n";
}
