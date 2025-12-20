<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\LLM\GeminiClient;
use Illuminate\Support\Facades\Log;

echo "--------------------------------------------------\n";
echo "Testing Gemini API Connectivity & Limits\n";
echo "--------------------------------------------------\n";

try {
    $client = new GeminiClient();
    echo "Sending test request to Gemini...\n";
    $result = $client->generate("Respond with 'OK' if you receive this.", ['max_tokens' => 10]);
    echo "--------------------------------------------------\n";
    echo "SUCCESS: API is responding correctly.\n";
    echo "Response: " . ($result['content'] ?? 'No content') . "\n";
    echo "Usage: " . json_encode($result['usage']) . "\n";
    echo "--------------------------------------------------\n";
} catch (\Exception $e) {
    echo "--------------------------------------------------\n";
    echo "ERROR DETECTED\n";
    echo "Message: " . $e->getMessage() . "\n";
    
    if (str_contains($e->getMessage(), '429') || str_contains(strtolower($e->getMessage()), 'rate limit')) {
        echo "\n>>> DIAGNOSIS: RATE LIMIT EXCEEDED <<<\n";
        echo "The API returned a 429 error. This means you have hit Google's quota.\n";
    } else {
        echo "\n>>> DIAGNOSIS: OTHER ERROR <<<\n";
    }
    echo "--------------------------------------------------\n";
}
