# Cara Perbaiki Chatbot di Deployment

## Problem
Chatbot sudah berjalan di lokal dengan model `gemini-2.5-flash`, tapi di deployment masih error.

## Solusi

### 1. Update Environment Variables di Platform Deployment

Pastikan environment variables berikut sudah di-set dengan nilai yang benar di platform deployment Anda (Railway, Heroku, atau lainnya):

```env
GEMINI_MODEL=gemini-2.5-flash
GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta
GEMINI_API_KEY= isi sendiri nanti
GEMINI_TIMEOUT=30
```

#### Untuk Railway:
1. Buka dashboard Railway
2. Pilih project Anda
3. Klik tab "Variables"
4. Update atau tambahkan variable:
   - `GEMINI_MODEL` = `gemini-2.5-flash`
   - `GEMINI_BASE_URL` = `https://generativelanguage.googleapis.com/v1beta`
   - Pastikan `GEMINI_API_KEY` sudah terisi

#### Untuk Heroku:
```bash
heroku config:set GEMINI_MODEL=gemini-2.5-flash
heroku config:set GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta
```

### 2. Clear Cache di Deployment

Setelah update environment variables, jalankan command berikut untuk clear cache:

```bash
# Untuk Railway/Heroku dengan CLI
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

Atau tambahkan di Dockerfile/build script untuk auto-clear cache setiap deploy:

```dockerfile
RUN php artisan config:clear && \
    php artisan cache:clear && \
    php artisan route:clear && \
    php artisan view:clear
```

### 3. Restart Deployment

Setelah update environment variables, restart deployment Anda:

#### Railway:
- Deployment akan otomatis restart setelah update environment variables

#### Heroku:
```bash
heroku restart
```

### 4. Verifikasi

Setelah restart, test chatbot dengan mengirim pesan "halo" atau "mau beli tembakau" untuk memastikan sudah berfungsi.

## Catatan Penting

⚠️ **JANGAN GANTI MODEL** - Model `gemini-2.5-flash` dengan API endpoint `v1beta` sudah terbukti berjalan dengan baik. Model lain seperti `gemini-1.5-flash` TIDAK tersedia di API endpoint ini.

## Troubleshooting

Jika masih error setelah langkah di atas:

1. **Cek logs deployment**:
   - Railway: Lihat di tab "Logs"
   - Heroku: `heroku logs --tail`

2. **Pastikan code sudah ter-deploy**:
   - Commit terakhir: `efce28a - API chatbot fix yang udh bisa running, jangan diganti`
   - Cek apakah deployment sudah pull commit terbaru

3. **Cek API Key**:
   - Pastikan API key yang sama (`AIzaSyBL7myvSCG20feo21dMOJ060T-ers_K_x0`) sudah di-set di deployment

4. **Rate Limit**:
   - Jika masih kena rate limit, tunggu 1-2 menit atau gunakan API key yang berbeda untuk deployment
