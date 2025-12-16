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
        $prompt = $this->buildComposerPrompt($userMessage, $routerOutput, $candidates, $conversationHistory);

        try {
            $result = $this->llmClient->generateJson($prompt, [
                'temperature' => 0.7,
                'max_tokens' => 1024,
            ]);

            Log::debug('Composer output', ['result' => $result]);

            return $this->parseComposerOutput($result, $candidates);
        } catch (\Exception $e) {
            Log::error('Composer LLM error', [
                'error' => $e->getMessage(),
                'message' => $userMessage,
            ]);

            // Fallback: return candidates without LLM composition
            return $this->fallbackResponse($candidates);
        }
    }

    /**
     * Build the composer prompt.
     */
    private function buildComposerPrompt(
        string $userMessage,
        RouterOutput $routerOutput,
        array $candidates,
        array $conversationHistory
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

        return <<<PROMPT
Kamu adalah asisten penjualan untuk toko benih tanaman. Tugasmu adalah merangkai jawaban yang informatif dan helpful berdasarkan data kandidat yang diberikan.

ATURAN PENTING:
1. JANGAN mengarang harga, stok, atau detail produk - gunakan HANYA data dari kandidat
2. JANGAN membuat link sendiri - gunakan link yang sudah ada di data kandidat
3. Jawab dalam bahasa Indonesia yang ramah dan profesional
4. Jika kandidat kosong atau tidak relevan, akui bahwa tidak menemukan data
5. Untuk produk, selalu sebutkan harga dan ketersediaan stok
6. Untuk artikel, berikan ringkasan singkat dan ajak user membaca selengkapnya
7. Sertakan link untuk setiap item yang direkomendasi

{$contextSection}
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
