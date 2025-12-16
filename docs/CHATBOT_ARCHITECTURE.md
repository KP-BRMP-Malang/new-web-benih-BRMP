# Chatbot Architecture Documentation

## Overview

Arsitektur chatbot 2 tahap untuk e-commerce toko benih dengan 3 sumber data: products, articles, faqs.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                            USER MESSAGE                                     │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                    ChatbotOrchestratorService                               │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │  STAGE 1: ChatRouterService (Router LLM)                            │    │
│  │  - Klasifikasi intent                                               │    │
│  │  - Ekstraksi filter (query, category, price range, etc.)            │    │
│  │  - Tentukan sumber data (products/articles/faqs)                    │    │
│  │  - Output: RouterOutput DTO                                         │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
│                                    │                                        │
│                                    ▼                                        │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │  STAGE 2a: RetrievalService                                         │    │
│  │  - Query database berdasarkan sources & filters                     │    │
│  │  - Support LIKE/FULLTEXT (siap untuk semantic search)               │    │
│  │  - Output: Array<RetrievalCandidate>                                │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
│                                    │                                        │
│                                    ▼                                        │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │  STAGE 2b: ChatComposerService (Composer LLM)                       │    │
│  │  - Rangkai jawaban dari kandidat DB                                 │    │
│  │  - Handle special cases (greeting, out_of_scope, etc.)              │    │
│  │  - Generate link produk/artikel dari backend                        │    │
│  │  - Output: ChatResponse DTO                                         │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                          JSON RESPONSE                                      │
│  {                                                                          │
│    "status": "found|not_found|need_clarification|out_of_scope|success",    │
│    "type": "product|article|faq|mixed|chat|warning",                       │
│    "message": "string",                                                     │
│    "data": [{title, summary, price, stock, unit, link}]                    │
│  }                                                                          │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Directory Structure

```
app/
├── Contracts/
│   ├── LlmClientInterface.php      # Interface untuk LLM clients
│   └── RetrievalServiceInterface.php # Interface untuk retrieval
├── DTO/
│   ├── ChatResponse.php            # Response DTO untuk API
│   ├── RetrievalCandidate.php      # Candidate dari DB
│   └── RouterOutput.php            # Output dari router LLM
├── Exceptions/
│   └── LlmException.php            # Custom exception untuk LLM errors
├── Http/
│   ├── Controllers/Api/
│   │   └── ChatbotController.php   # API Controller
│   └── Requests/
│       └── ChatRequest.php         # Form request validation
├── Models/
│   ├── ChatSession.php             # Session model (UUID)
│   └── ChatMessage.php             # Message model
├── Providers/
│   └── ChatbotServiceProvider.php  # Service binding
└── Services/
    ├── Chatbot/
    │   ├── ChatbotOrchestratorService.php  # Main orchestrator
    │   ├── ChatComposerService.php         # Composer LLM
    │   ├── ChatRouterService.php           # Router LLM
    │   └── RetrievalService.php            # DB retrieval
    └── LLM/
        └── GeminiClient.php        # Gemini API client
```

## API Endpoints

### POST /api/chat

Main chat endpoint.

**Request:**

```json
{
    "session_id": "unique-session-token",
    "message": "Cari benih tomat"
}
```

**Response:**

```json
{
    "status": "found",
    "type": "product",
    "message": "Berikut benih tomat yang tersedia:",
    "data": [
        {
            "title": "Benih Tomat Cherry",
            "summary": "Benih tomat cherry berkualitas tinggi",
            "price": 25000,
            "stock": 100,
            "unit": "pack",
            "link": "http://example.com/produk/1"
        }
    ]
}
```

### GET /api/chat/history?session_id=xxx

Get chat history for a session.

### DELETE /api/chat/history?session_id=xxx

Clear chat history.

### GET /api/chat/health

Health check endpoint.

## Configuration

### Environment Variables

Add to `.env`:

```env
# Google Gemini API
GEMINI_API_KEY=your-api-key
GEMINI_MODEL=gemini-1.5-flash
GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta
GEMINI_TIMEOUT=30
```

### Service Provider

The `ChatbotServiceProvider` is automatically registered in `bootstrap/providers.php`.

## Intent Types

Router recognizes these intents:

| Intent           | Description              | Sources  |
| ---------------- | ------------------------ | -------- |
| `product_search` | Mencari produk/benih     | products |
| `product_info`   | Detail produk tertentu   | products |
| `price_inquiry`  | Bertanya harga           | products |
| `stock_inquiry`  | Bertanya stok            | products |
| `article_search` | Mencari artikel budidaya | articles |
| `faq`            | Pertanyaan umum toko     | faqs     |
| `greeting`       | Sapaan                   | -        |
| `thanks`         | Ucapan terima kasih      | -        |
| `farewell`       | Pamit                    | -        |
| `general_chat`   | Obrolan umum             | -        |
| `out_of_scope`   | Di luar topik toko       | -        |

## Response Status

| Status               | Description                    |
| -------------------- | ------------------------------ |
| `found`              | Data ditemukan                 |
| `not_found`          | Data tidak ditemukan           |
| `need_clarification` | Perlu klarifikasi dari user    |
| `out_of_scope`       | Pertanyaan di luar topik       |
| `success`            | Berhasil (untuk greeting, dll) |
| `error`              | Terjadi error                  |

## Extending for Semantic Search

The `RetrievalService` is structured for easy migration to semantic/embedding search:

```php
// In RetrievalService.php

protected function semanticSearch(string $query, string $collection, int $limit = 5): array
{
    // TODO: Implement when adding embedding support
    // 1. Get embedding for query using embedding model
    // 2. Query vector database (pgvector, pinecone, etc.)
    // 3. Return candidates with cosine similarity scores
}
```

Steps to migrate:

1. Add embedding model (e.g., `text-embedding-ada-002`)
2. Add vector database (pgvector extension for PostgreSQL)
3. Index products, articles, faqs with embeddings
4. Implement `semanticSearch()` method
5. Update `searchProducts()`, `searchArticles()`, `searchFaqs()` to use semantic search

## Testing

Run chatbot tests:

```bash
php artisan test --filter=Chatbot
```

Test files:

-   `tests/Unit/Chatbot/RouterOutputTest.php`
-   `tests/Unit/Chatbot/ChatResponseTest.php`
-   `tests/Unit/Chatbot/RetrievalCandidateTest.php`
-   `tests/Feature/Chatbot/ChatbotOrchestratorTest.php`
-   `tests/Feature/Chatbot/ChatbotApiTest.php`

## Database Tables

### chat_sessions

```sql
- id (UUID, PRIMARY KEY)
- user_id (BIGINT, nullable, FK to users.user_id)
- session_token (VARCHAR 64, UNIQUE)
- context (JSON, nullable)
- status (ENUM: active, closed, archived)
- last_activity_at (TIMESTAMP)
- created_at, updated_at
```

### chat_messages

```sql
- id (BIGINT, PRIMARY KEY)
- chat_session_id (UUID, FK to chat_sessions.id)
- role (ENUM: user, assistant, system)
- content (TEXT)
- metadata (JSON, nullable)
- token_count (INT, nullable)
- created_at, updated_at
```

## Behavior Rules

1. **Jangan mengarang data** - Harga, stok, dan link hanya dari database
2. **Link dibentuk backend** - `url('/produk/{id}')` dan `url('/artikel/{id}')`
3. **Kandidat kosong** - Return `not_found` atau `need_clarification`
4. **Out of scope** - Tolak dengan sopan, arahkan ke topik toko
5. **Low confidence** - Minta klarifikasi, jangan jawab ngawur
