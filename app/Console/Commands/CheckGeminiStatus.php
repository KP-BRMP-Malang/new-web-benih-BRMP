<?php

namespace App\Console\Commands;

use App\Services\LLM\GeminiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckGeminiStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gemini:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check connectivity and quota status of Google Gemini API';

    /**
     * Execute the console command.
     */
    public function handle(GeminiClient $client)
    {
        $this->info("Checking Gemini API Status... (Model: " . $client->getModelName() . ")");
        
        try {
            $start = microtime(true);
            
            // Send a minimal token request
            $result = $client->generate("Ping", ['max_tokens' => 1]);
            
            $duration = round((microtime(true) - $start) * 1000, 2);

            $this->info("\u{2705} API Status: ACTIVE");
            $this->line("Response Time: {$duration}ms");
            
            if (isset($result['usage'])) {
                $this->line("Usage Info: " . json_encode($result['usage']));
            }
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            
            if (str_contains($msg, '429') || str_contains(strtolower($msg), 'rate limit')) {
                if (str_contains($msg, 'Resource has been exhausted') || str_contains($msg, 'quota')) {
                    $this->error("\u{274C} API Status: DAILY QUOTA EXHAUSTED");
                    $this->line("Detail: The daily free tier limit has been reached. Please wait until tomorrow (Pacific Time).");
                } else {
                    $this->error("\u{26A0} API Status: RATE LIMITED (RPM)");
                    $this->line("Detail: Too many requests in a short time. Please wait 1 minute.");
                }
            } else {
                $this->error("\u{274C} API Status: ERROR");
                $this->line("Detail: {$msg}");
            }
            
            return Command::FAILURE;
        }
    }
}
