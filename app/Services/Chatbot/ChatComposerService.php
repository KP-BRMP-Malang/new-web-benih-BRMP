<?php

namespace App\Services\Chatbot;

use App\Contracts\LlmClientInterface;
use App\DTO\ChatResponse;
use App\DTO\RetrievalCandidate;
use App\DTO\RouterOutput;
use Illuminate\Support\Facades\Log;

/**
 * Service for composing final chat responses using LLM.
 */
class ChatComposerService
{
    private LlmClientInterface $llmClient;

    public function __construct(LlmClientInterface $llmClient)
    {
        $this->llmClient = $llmClient;
    }

    /**
     * Compose a response based on router output and retrieved candidates.
     *
     * @param string $userMessage Original user message
     * @param RouterOutput $routerOutput Output from router
     * @param array<RetrievalCandidate> $candidates Retrieved candidates
     * @param array $conversationHistory Recent conversation for context
     * @return ChatResponse
     */
    public function compose(
        string $userMessage,
        RouterOutput $routerOutput,
        array $candidates,
        array $conversationHistory = []
    ): ChatResponse {
        // Handle special cases before calling LLM
        if ($routerOutput->isOutOfScope()) {
            return ChatResponse::outOfScope(
                'Maaf, saya hanya bisa membantu pertanyaan seputar produk benih, artikel budidaya, dan informasi toko kami. Ada yang bisa saya bantu terkait hal tersebut?'
            );
        }

        if ($routerOutput->needsClarification()) {
            return ChatResponse::needClarification(
                $routerOutput->clarificationNeeded ?? 'Maaf, bisa tolong perjelas pertanyaan Anda?'
            );
        }

        if ($routerOutput->isGeneralChat()) {
            return $this->handleGeneralChat($routerOutput->intent, $userMessage);
        }

        // If we have sources but no candidates found
        if (!empty($routerOutput->sources) && empty($candidates)) {
            return ChatResponse::notFound(
                'Maaf, saya tidak menemukan data yang sesuai dengan pencarian Anda. Coba gunakan kata kunci lain atau tanyakan dengan lebih spesifik.'
            );
        }

        // Compose response using LLM
        return $this->composeWithLlm($userMessage, $routerOutput, $candidates, $conversationHistory);
    }

    /**
     * Handle general chat (greetings, thanks, etc.) without LLM.
     */
    private function handleGeneralChat(string $intent, string $userMessage): ChatResponse
    {
        $responses = [
            'greeting' => [
                'Halo! Selamat datang di toko benih kami. Ada yang bisa saya bantu?',
                'Hai! Saya siap membantu Anda mencari benih berkualitas. Apa yang Anda cari?',
                'Selamat datang! Silakan tanyakan tentang produk benih, tips budidaya, atau informasi pemesanan.',
            ],
            'thanks' => [
                'Sama-sama! Senang bisa membantu. Ada lagi yang ingin ditanyakan?',
                'Terima kasih kembali! Jangan ragu untuk bertanya lagi ya.',
            ],
            'farewell' => [
                'Sampai jumpa! Terima kasih sudah berkunjung.',
                'Selamat berbelanja! Jika ada pertanyaan lagi, saya siap membantu.',
            ],
            'chat' => [
                'Halo! Saya adalah asisten BRMP untuk membantu Anda. Saya bisa membantu mencari produk benih, memberikan tips budidaya, atau menjawab pertanyaan seputar toko kami. Ada yang bisa saya bantu?',
                'Hai! Senang bertemu dengan Anda. Saya siap membantu pertanyaan seputar produk benih, artikel budidaya, dan informasi toko kami. Apa yang ingin Anda ketahui?',
            ],
            'general_chat' => [
                'Saya adalah asisten virtual untuk toko benih. Saya bisa membantu Anda mencari produk, memberikan informasi budidaya, atau menjawab pertanyaan seputar toko.',
            ],
        ];

        $messageOptions = $responses[$intent] ?? $responses['general_chat'];
        $message = $messageOptions[array_rand($messageOptions)];

        return ChatResponse::chat($message);
    }

    /**
     * Compose response using LLM.
     */
    private function composeWithLlm(
        string $userMessage,
        RouterOutput $routerOutput,
        array $candidates,
        array $conversationHistory
    ): ChatResponse {
        $summaryContext = null;

        // Special handling for large number of products: Summarize and sample
        if (count($candidates) > 5 && $this->determineType($candidates) === ChatResponse::TYPE_PRODUCT) {
            $totalFound = count($candidates);
            
            // Calculate counts by plant type
            $counts = [];
            $productsByType = [];
            foreach ($candidates as $candidate) {
                $typeName = $candidate->extra['plant_type_name'] ?? 'Lainnya';
                $counts[$typeName] = ($counts[$typeName] ?? 0) + 1;
                $productsByType[$typeName][] = $candidate;
            }

            // Build summary context string
            $details = [];
            foreach ($counts as $type => $count) {
                $details[] = "{$type}: {$count}";
            }
            $detailsStr = implode(', ', $details);
            $summaryContext = "Ditemukan total {$totalFound} produk ({$detailsStr}). Berikut 5 contoh diantaranya:";

            // Select 5 distinct samples
            $selectedCandidates = [];
            $limit = 5;
            
            // Round-robin selection to ensure diversity
            while (count($selectedCandidates) < $limit && !empty($productsByType)) {
                $types = array_keys($productsByType);
                $madeSelection = false;
                
                foreach ($types as $type) {
                    if (empty($productsByType[$type])) {
                        unset($productsByType[$type]);
                        continue;
                    }
                    
                    if (count($selectedCandidates) < $limit) {
                        $selectedCandidates[] = array_shift($productsByType[$type]);
                        $madeSelection = true;
                    }
                }
                
                if (!$madeSelection) break;
            }
            
            // Use only selected candidates for the prompt to save tokens
            $candidates = $selectedCandidates;
        }

        $prompt = $this->buildComposerPrompt($userMessage, $routerOutput, $candidates, $conversationHistory, $summaryContext);

        // Cache key based on prompt hash
        $cacheKey = 'chat_composer:' . md5($prompt);
        
        if ($cached = \Illuminate\Support\Facades\Cache::get($cacheKey)) {
            Log::debug('Composer result retrieved from cache', ['key' => $cacheKey]);
            return $cached;
        }

        // Retry logic
        $maxRetries = 3;
        $lastException = null;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $result = $this->llmClient->generateJson($prompt, [
                    'temperature' => 0.7,
                    'max_tokens' => 1024,
                ]);

                Log::debug('Composer output', ['result' => $result]);

                $response = $this->parseComposerOutput($result, $candidates);
                
                // Cache successful response (60 minutes)
                \Illuminate\Support\Facades\Cache::put($cacheKey, $response, 60 * 60);
                
                return $response;
            } catch (\Exception $e) {
                $lastException = $e;
                $errorMessage = $e->getMessage();
                
                // Check if it's a rate limit error - don't retry, return error message
                if (str_contains($errorMessage, 'rate limit') || str_contains($errorMessage, '429')) {
                    $isDailyLimit = str_contains($errorMessage, 'Resource has been exhausted') || str_contains($errorMessage, 'quota');
                    
                    Log::error($isDailyLimit ? 'Composer LLM daily quota exhausted' : 'Composer LLM rate limit hit', [
                        'error' => $errorMessage,
                        'message' => $userMessage,
                    ]);

                    return ChatResponse::needClarification(
                        $isDailyLimit 
                            ? 'Maaf, kuota harian sistem telah habis. Silakan coba lagi besok.' 
                            : 'Maaf, sistem sedang sibuk karena banyak permintaan. Silakan tunggu 1 menit dan coba lagi.'
                    );
                }
                
                // For other errors, retry
                if ($attempt < $maxRetries) {
                    Log::warning('Composer LLM error, retrying', [
                        'attempt' => $attempt,
                        'error' => $errorMessage,
                    ]);
                    sleep(1); // Backoff 1s
                    continue;
                }
                
                Log::error('Composer LLM failed after retries', [
                    'error' => $errorMessage,
                    'message' => $userMessage,
                ]);
            }
        }

        // Fallback: return candidates without LLM composition
        return $this->fallbackResponse($candidates);
    }

    /**
     * Build the composer prompt.
     */
    private function buildComposerPrompt(
        string $userMessage,
        RouterOutput $routerOutput,
        array $candidates,
        array $conversationHistory,
        ?string $summaryContext = null
    ): string {
        $candidatesJson = json_encode(
            array_map(fn($c) => $c->jsonSerialize(), $candidates),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );

        $contextSection = '';
        if (!empty($conversationHistory)) {
            $contextSection = "CONVERSATION HISTORY:\n";
            foreach (array_slice($conversationHistory, -3) as $msg) {
                $role = $msg['role'] === 'user' ? 'User' : 'Assistant';
                $contextSection .= "{$role}: {$msg['content']}\n";
            }
            $contextSection .= "\n";
        }

        $summarySection = '';
        if ($summaryContext) {
            $summarySection = "\nRINGKASAN HASIL PENCARIAN:\n{$summaryContext}\n";
        }

        return <<<PROMPT
Kamu adalah asisten penjualan untuk toko benih tanaman. Tugasmu adalah merangkai jawaban yang informatif dan helpful berdasarkan data kandidat yang diberikan.

ATURAN PENTING:
1. Berikan pengantar yang ramah dan menarik sebelum melist produk (JANGAN langsung list)
2. Jika ada Ringkasan Hasil Pencarian, gunakan informasinya untuk menjelaskan cakupan produk yang ditemukan
3. JANGAN membuat/mengarang link baru - gunakan HANYA link dari data kandidat
4. JANGAN mengarang harga, stok, atau fakta yang tidak ada di data
5. Jawab dalam bahasa Indonesia yang ramah dan profesional
6. Untuk produk, selalu sebutkan harga dan ketersediaan stok
7. Untuk artikel, berikan ringkasan singkat dan ajak user membaca selengkapnya
8. Sertakan link untuk setiap item yang direkomendasi
9. Jaga respons tetap ringkas dan padat. Hindari penjelasan yang bertele-tele (maks 200 kata kecuali diminta detail).

{$contextSection}
{$summarySection}
USER MESSAGE: {$userMessage}

INTENT: {$routerOutput->intent}
CONFIDENCE: {$routerOutput->confidence}

DATA KANDIDAT:
{$candidatesJson}

OUTPUT FORMAT (JSON):
{
  "status": "found|not_found|need_clarification",
  "type": "product|article|faq|mixed",
  "message": "string - pesan lengkap untuk user",
  "items": [
    {
      "title": "string",
      "summary": "string - deskripsi singkat",
      "price": number atau null,
      "stock": number atau null,
      "unit": "string atau null",
      "link": "string - URL"
    }
  ]
}

CONTOH OUTPUT BAIK:
- "Berikut benih tomat yang tersedia: [nama] dengan harga Rp X per [unit], stok Y tersedia. Lihat detail: [link]"
- "Saya menemukan artikel tentang [topik]: [ringkasan]. Baca selengkapnya di: [link]"

Berikan output JSON saja, tanpa penjelasan.
PROMPT;
    }

    /**
     * Parse composer output and build ChatResponse.
     */
    private function parseComposerOutput(array $output, array $candidates): ChatResponse
    {
        $status = $output['status'] ?? 'found';
        $type = $output['type'] ?? $this->determineType($candidates);
        $message = $output['message'] ?? '';
        $items = $output['items'] ?? [];

        // Map items to proper format with type field
        // Use the overall type for all items if it's a single-type response
        $data = array_map(function ($item) use ($type, $candidates) {
            // Determine item type: check if it matches a candidate by link
            $itemType = $type;
            $itemLink = $item['link'] ?? null;
            
            // Try to match with original candidate to get correct type
            foreach ($candidates as $candidate) {
                if ($candidate->link === $itemLink) {
                    $itemType = $candidate->type;
                    break;
                }
            }
            
            // For FAQ type, use question/answer format
            if ($itemType === 'faq') {
                return [
                    'type' => 'faq',
                    'id' => $item['id'] ?? null,
                    'question' => $item['title'] ?? $item['question'] ?? '',
                    'answer' => $item['summary'] ?? $item['answer'] ?? '',
                    'link' => null, // FAQ has no link
                ];
            }
            
            // For product type
            if ($itemType === 'product') {
                return [
                    'type' => 'product',
                    'id' => $item['id'] ?? null,
                    'title' => $item['title'] ?? '',
                    'summary' => $item['summary'] ?? '',
                    'price' => $item['price'] ?? null,
                    'stock' => $item['stock'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'link' => $item['link'] ?? null,
                ];
            }
            
            // For article type (default)
            return [
                'type' => 'article',
                'id' => $item['id'] ?? null,
                'title' => $item['title'] ?? '',
                'summary' => $item['summary'] ?? '',
                'link' => $item['link'] ?? null,
            ];
        }, $items);

        return match ($status) {
            'not_found' => ChatResponse::notFound($message),
            'need_clarification' => ChatResponse::needClarification($message),
            default => ChatResponse::found($type, $message, $data),
        };
    }

    /**
     * Determine response type from candidates.
     */
    private function determineType(array $candidates): string
    {
        if (empty($candidates)) {
            return ChatResponse::TYPE_CHAT;
        }

        $types = array_unique(array_map(fn($c) => $c->type, $candidates));

        if (count($types) === 1) {
            return $types[0];
        }

        return ChatResponse::TYPE_MIXED;
    }

    /**
     * Fallback response when LLM fails.
     */
    private function fallbackResponse(array $candidates): ChatResponse
    {
        if (empty($candidates)) {
            return ChatResponse::notFound(
                'Maaf, saya tidak menemukan data yang sesuai dengan pencarian Anda.'
            );
        }

        $type = $this->determineType($candidates);
        $items = array_map(fn($c) => $c->jsonSerialize(), array_slice($candidates, 0, 5));

        $message = match ($type) {
            'product' => 'Berikut produk yang saya temukan:',
            'article' => 'Berikut artikel yang mungkin membantu:',
            'faq' => 'Berikut informasi yang mungkin menjawab pertanyaan Anda:',
            default => 'Berikut hasil pencarian:',
        };

        return ChatResponse::found($type, $message, $items);
    }
}
