<?php

namespace App\Http\Controllers; // Sesuaikan namespace jika ada folder customer

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ChatbotController extends Controller
{
    public function handleChat(Request $request)
    {
        $userMessage = trim($request->input('message'));

        if (!$userMessage) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesan tidak boleh kosong.'
            ]);
        }

        // --- STRATEGI 1: CEK CACHE ---
        $cacheKey = 'chatbot_lite_' . md5(strtolower($userMessage));

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        // --- STRATEGI 2: PANGGIL GEMINI LITE ---
        $apiKey = env('GEMINI_API_KEY');
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key={$apiKey}";

        $systemInstruction = "You are 'BRMP Chatbot', a friendly AI assistant for a Seed Shop (BRMP Malang).
        Your Tasks:
        1. Extract the main plant/product 'keyword' (e.g., 'tembakau', 'wijen'). If none, set null.
        2. Identify 'intent': 'search' (buying/stock), 'knowledge' (how to plant), 'chat' (greetings).
        3. Generate 'reply_text' (Bahasa Indonesia):
           - IMPORTANT: NEVER mention specific product names, varieties, or prices in your text reply because you don't know the live stock.
           - Instead, say generic things like: 'Berikut adalah stok [keyword] yang tersedia:' or 'Kami memiliki varietas [keyword] unggulan:'.
           - If intent is 'knowledge', explain briefly about the plant.
        
        RETURN JSON ONLY: {'keyword': string|null, 'intent': string, 'reply_text': string}";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'systemInstruction' => ['parts' => [['text' => $systemInstruction]]],
                'contents' => [['role' => 'user', 'parts' => [['text' => $userMessage]]]],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'responseMimeType' => 'application/json' 
                ]
            ]);

            // --- STRATEGI 3: SILENT FALLBACK ---
            if ($response->failed()) {
                if ($response->status() == 429) {
                    Log::warning('Gemini Quota Exceeded (Lite). Switching to Manual Search.');
                } else {
                    Log::error('Gemini API Error: ' . $response->body());
                }
                return $this->fallbackSearch($userMessage);
            }

            $aiData = $response->json();
            $rawText = $aiData['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $parsedResult = json_decode($rawText, true);
            
            $intent = $parsedResult['intent'] ?? 'search';
            $keyword = $parsedResult['keyword'] ?? null;
            $aiReply = $parsedResult['reply_text'] ?? 'Baik, saya cek ketersediaannya.';

        } catch (\Exception $e) {
            Log::error('Chatbot Exception: ' . $e->getMessage());
            return $this->fallbackSearch($userMessage);
        }

        // --- STEP 3: PROSES DATA ---
        $finalResponse = null;

        if ($intent === 'chat' && !$keyword) {
            $finalResponse = [
                'status' => 'success',
                'message' => $aiReply,
                'data' => []
            ];
        } else {
            $products = [];
            if ($keyword) {
                $products = $this->queryProducts($keyword);
            }

            if (count($products) > 0) {
                $finalResponse = [
                    'status' => 'found',
                    'message' => $aiReply,
                    'data' => $products
                ];
            } elseif ($intent === 'knowledge') {
                $finalResponse = [
                    'status' => 'success',
                    'message' => $aiReply . "\n\n(Stok benih terkait belum tersedia di katalog kami).",
                    'data' => []
                ];
            } else {
                $finalResponse = [
                    'status' => 'not_found',
                    'message' => "Mohon maaf, stok untuk '{$keyword}' sedang kosong.",
                    'data' => []
                ];
            }
        }

        // Simpan Cache 24 Jam
        Cache::put($cacheKey, $finalResponse, 86400);

        return response()->json($finalResponse);
    }

    private function queryProducts($keyword)
    {
        $products = DB::table('products')
            ->join('plant_types', 'products.plant_type_id', '=', 'plant_types.plant_type_id')
            ->select('products.product_id', 'products.product_name', 'products.price_per_unit', 'products.stock', 'products.image1')
            ->where('products.product_name', 'LIKE', "%{$keyword}%")
            ->orWhere('plant_types.plant_type_name', 'LIKE', "%{$keyword}%")
            ->orWhere('plant_types.comodity', 'LIKE', "%{$keyword}%")
            ->limit(5)
            ->get();

        // MAPPING DATA AGAR KONSISTEN DENGAN JS
        return $products->map(function($item) {
            // Cek apakah gambar ada di storage atau folder public images biasa
            // Sesuaikan logika ini dengan struktur foldermu. 
            // Defaultnya ke storage, tapi fallback ke public/images jika perlu.
            $imageUrl = asset('storage/' . $item->image1);

            return [
                'name' => $item->product_name, // Key: name
                'price' => 'Rp ' . number_format($item->price_per_unit, 0, ',', '.'), // Key: price (String terformat)
                'stock' => $item->stock, // Key: stock
                'image_url' => $imageUrl, // Key: image_url
                'link' => url('/produk/' . $item->product_id) // Sesuaikan URL detail produk
            ];
        });
    }

    private function fallbackSearch($originalMessage)
    {
        $products = $this->queryProducts($originalMessage);
        
        $response = count($products) > 0 
            ? ['status' => 'found', 'message' => "Berikut produk yang ditemukan:", 'data' => $products]
            : ['status' => 'not_found', 'message' => "Maaf, produk tidak ditemukan.", 'data' => []];

        return response()->json($response);
    }
}