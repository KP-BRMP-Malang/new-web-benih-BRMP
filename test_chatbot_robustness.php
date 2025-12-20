<?php

use App\Services\Chatbot\ChatRouterService;
use App\Services\Chatbot\PromptRepository;
use App\Services\LLM\GeminiClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$router = app(ChatRouterService::class);

$message = "saya punya lahan 2 hektar di daerah pesisir cocoknya ditanam benih apa? " . time(); // Add time to avoid previous cache if any

echo "\n--- TEST 1: First Call (Should hit API) ---\n";
$start = microtime(true);
$result1 = $router->route($message);
$time1 = microtime(true) - $start;
echo "Time: " . number_format($time1, 2) . "s\n";
echo "Intent: {$result1->intent}\n";
echo "Filters: " . json_encode($result1->filters) . "\n";

echo "\n--- TEST 2: Second Call (Should hit Cache) ---\n";
$start = microtime(true);
$result2 = $router->route($message);
$time2 = microtime(true) - $start;
echo "Time: " . number_format($time2, 2) . "s\n";
echo "Intent: {$result2->intent}\n";

if ($time2 < 0.1) {
    echo "\nPASS: Cache is working (response is instant).\n";
} else {
    echo "\nFAIL: Cache might not be working (response took too long). Note: First run might be slow due to framework boot, but logic should be fast.\n";
}

if ($result1->intent === $result2->intent) {
    echo "PASS: Intents match.\n";
} else {
    echo "FAIL: Intents do not match.\n";
}
