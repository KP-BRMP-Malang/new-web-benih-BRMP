# Perbaikan Chatbot FAQ - Ringkasan Perubahan

## Masalah yang Diperbaiki

### ❌ Masalah Sebelumnya:

1. **FAQ tidak bisa mengembalikan respons** meskipun data sudah ada di database
2. **Keyword extraction dari LLM tidak akurat** untuk matching FAQ
3. **Response format tidak sesuai** - FAQ seharusnya langsung memberikan jawaban, bukan link seperti produk/artikel

### Contoh Kasus:

-   User bertanya: "kapan balai BRMP buka?"
-   Response: "Mohon maaf, saya tidak menemukan informasi terkait."
-   Padahal FAQ tentang jam buka ada di database dengan keywords: `lokasi,alamat,dimana,jam buka,buka`

---

## ✅ Solusi yang Diimplementasikan

### 1. **Algoritma Similarity Scoring (Tanpa Vektorisasi)**

Dibuat fungsi `findBestFaqMatch()` dengan scoring system multi-level:

#### **Level 1: Exact Match (100 poin)**

-   Jika pertanyaan user cocok persis dengan pertanyaan FAQ

#### **Level 2: Phrase Match (30 poin)**

-   Match untuk phrase/frasa panjang dari keywords (e.g., "jam buka")

#### **Level 3: LLM Keyword Match (15-20 poin)**

-   Keyword yang diekstrak LLM dicocokkan dengan keywords FAQ

#### **Level 4: Word-by-Word Match (10 poin per kata)**

-   Setiap kata dari user message dicocokkan dengan keywords dan pertanyaan FAQ

#### **Bonus: Relevance Boost (3 poin per kata)**

-   Jika ada 2+ kata yang match, dianggap lebih relevan

#### **Threshold: Minimum 15 poin**

-   FAQ dianggap match jika score >= 15

---

### 2. **Response Format yang Tepat**

#### **FAQ Response (Langsung):**

```json
{
    "status": "success",
    "type": "faq",
    "message": "Balai BRMP Malang berlokasi di:\nJl. Raya Karangploso KM 4...",
    "question": "Dimana lokasi Balai BRMP Malang?",
    "data": []
}
```

#### **Produk Response (Dengan Link):**

```json
{
  "status": "found",
  "type": "product",
  "message": "Berikut stok tembakau yang tersedia:",
  "data": [
    {
      "name": "Tembakau Virginia",
      "price": "Rp 50.000",
      "link": "/produk/123",
      ...
    }
  ]
}
```

#### **Artikel Response (Dengan Link):**

```json
{
  "status": "found",
  "type": "article",
  "message": "Berikut artikel tentang budidaya:",
  "data": [
    {
      "title": "Cara Menanam Tembakau",
      "link": "/artikel/45",
      ...
    }
  ]
}
```

---

### 3. **Perbaikan Frontend JavaScript**

-   Handler untuk `status === 'success'` (FAQ dan chat)
-   Menampilkan pertanyaan FAQ yang match (opsional)
-   Render products/articles hanya jika ada data

---

## 📊 Hasil Testing

### Test Cases yang Berhasil:

```
✅ "kapan balai BRMP buka?"       → Match: "Dimana lokasi Balai BRMP Malang?"
✅ "jam buka berapa?"              → Match: "Dimana lokasi Balai BRMP Malang?"
✅ "lokasi BRMP dimana?"           → Match: "Dimana lokasi Balai BRMP Malang?"
✅ "cara pesan benih gimana?"      → Match: "Bagaimana cara memesan benih?"
✅ "bisa bayar COD ga?"            → Match: "Apakah bisa COD (Cash on Delivery)?"
✅ "berapa ongkir ke surabaya?"    → Match: "Apakah ada biaya pengiriman?"
```

---

## 🔍 Apakah Perlu Vektorisasi?

### **TIDAK PERLU** untuk saat ini, karena:

✅ **Skala kecil**: Hanya 15 FAQ
✅ **Fast processing**: Simple string matching
✅ **Akurat**: Scoring algorithm sudah efektif
✅ **Easy maintenance**: Tidak perlu setup embedding service
✅ **No dependencies**: Tidak perlu library tambahan

### **PERLU VEKTORISASI jika:**

-   FAQ mencapai 100+ entries
-   Perlu semantic similarity (e.g., "kapan tutup" = "jam buka")
-   Multi-bahasa support
-   Need similarity untuk sinonim kompleks

### **Alternatif di Masa Depan:**

1. **Simple Vektorisasi**: Sentence-BERT (lightweight)
2. **Cloud Service**: OpenAI Embeddings, Google Vertex AI
3. **Local Vector DB**: ChromaDB, Milvus

---

## 📝 Files yang Dimodifikasi

1. **app/Http/Controllers/ChatbotController.php**

    - Mengganti `queryFaqs()` dengan `findBestFaqMatch()`
    - Mengubah response format FAQ
    - Menambahkan similarity scoring algorithm

2. **resources/views/customer/partials/chatbot.blade.php**
    - Menambahkan handler untuk `status === 'success'`
    - Menampilkan pertanyaan FAQ yang match
    - Perbaikan conditional rendering untuk data

---

## 🚀 Cara Testing

1. **Jalankan Laravel server:**

    ```bash
    php artisan serve
    ```

2. **Buka homepage dan test chatbot dengan:**

    - "kapan balai BRMP buka?"
    - "cara pesan benih?"
    - "bisa COD?"
    - "berapa ongkir?"

3. **Expected Result:**
    - FAQ langsung menampilkan jawaban
    - Tidak ada link/card seperti produk
    - Response cepat dan akurat

---

## 💡 Tips Maintenance

### **Menambah FAQ Baru:**

1. Update `database/seeders/FaqSeeder.php`
2. Tambahkan keywords yang relevan (comma-separated)
3. Run: `php artisan db:seed --class=FaqSeeder`

### **Meningkatkan Akurasi:**

-   Tambahkan lebih banyak keywords per FAQ
-   Gunakan variasi kata (e.g., "pesan,beli,order,pemesanan")
-   Test dengan berbagai cara bertanya user

### **Monitoring:**

-   Check Laravel logs untuk query FAQ
-   Tambahkan logging pada score calculation (optional)
-   Collect user feedback untuk FAQ yang tidak match

---

## ✨ Keunggulan Pendekatan Ini

1. ✅ **No API Quota**: Tidak tergantung penuh pada LLM
2. ✅ **Fast Response**: Local matching lebih cepat
3. ✅ **Cost Effective**: Tidak perlu embedding API
4. ✅ **Accurate**: Multi-level scoring untuk precision tinggi
5. ✅ **Easy Debug**: Simple algorithm, mudah di-trace
6. ✅ **Scalable**: Bisa diperluas ke fuzzy matching/levenshtein jika perlu
