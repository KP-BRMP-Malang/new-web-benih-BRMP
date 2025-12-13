<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            // KATEGORI: ORDER (Pemesanan)
            [
                'question' => 'Bagaimana cara memesan benih?',
                'answer' => "Anda dapat memesan benih melalui beberapa cara:\n1. Melalui website: Tambahkan produk ke keranjang dan checkout\n2. WhatsApp: Hubungi kami di 0812-xxxx-xxxx\n3. Datang langsung ke Balai BRMP Malang di Jl. Raya Karangploso KM 4, Malang\n\nPastikan Anda sudah membuat akun dan login untuk memesan melalui website.",
                'category' => 'order',
                'keywords' => 'pesan,beli,order,pemesanan,cara beli',
                'order' => 1,
            ],
            [
                'question' => 'Berapa minimum pemesanan?',
                'answer' => "Minimum pemesanan benih di BRMP Malang adalah 1 kilogram untuk setiap jenis benih. Untuk pemesanan dalam jumlah besar (di atas 100 kg), silakan hubungi kami terlebih dahulu untuk ketersediaan stok.",
                'category' => 'order',
                'keywords' => 'minimum,minimal,jumlah pesan',
                'order' => 2,
            ],
            [
                'question' => 'Apakah bisa pre-order benih?',
                'answer' => "Ya, kami menerima pre-order untuk benih yang stoknya sedang kosong atau untuk varietas tertentu. Waktu tunggu pre-order biasanya 2-4 minggu tergantung jenis benih. Silakan hubungi customer service kami untuk informasi lebih lanjut.",
                'category' => 'order',
                'keywords' => 'pre order,preorder,pesan dulu,indent',
                'order' => 3,
            ],

            // KATEGORI: PAYMENT (Pembayaran)
            [
                'question' => 'Metode pembayaran apa saja yang diterima?',
                'answer' => "Kami menerima pembayaran melalui:\n1. Transfer Bank (BRI, BNI, Mandiri)\n2. Tunai (untuk pembelian langsung di Balai)\n3. QRIS (untuk pembelian langsung)\n\nUntuk pemesanan online, pembayaran harus dilakukan dalam waktu 24 jam setelah checkout. Setelah transfer, mohon upload bukti pembayaran di halaman pesanan.",
                'category' => 'payment',
                'keywords' => 'bayar,pembayaran,transfer,metode bayar,cara bayar',
                'order' => 4,
            ],
            [
                'question' => 'Apakah bisa COD (Cash on Delivery)?',
                'answer' => "Untuk saat ini, kami belum menyediakan layanan COD. Pembayaran harus dilakukan terlebih dahulu sebelum pengiriman. Namun, jika Anda berada di area Malang, Anda bisa datang langsung ke Balai untuk pembelian tunai.",
                'category' => 'payment',
                'keywords' => 'cod,cash on delivery,bayar ditempat',
                'order' => 5,
            ],

            // KATEGORI: DELIVERY (Pengiriman)
            [
                'question' => 'Berapa lama waktu pengiriman?',
                'answer' => "Estimasi waktu pengiriman:\n- Wilayah Malang & sekitar: 1-2 hari kerja\n- Jawa Timur: 2-3 hari kerja\n- Luar Jawa: 3-7 hari kerja\n\nWaktu pengiriman tergantung pada jasa ekspedisi yang dipilih dan kondisi cuaca. Anda akan mendapatkan nomor resi setelah barang dikirim.",
                'category' => 'delivery',
                'keywords' => 'kirim,pengiriman,ongkir,ongkos kirim,lama kirim',
                'order' => 6,
            ],
            [
                'question' => 'Apakah ada biaya pengiriman?',
                'answer' => "Ya, biaya pengiriman dihitung berdasarkan:\n1. Berat total pesanan\n2. Jarak tujuan pengiriman\n3. Jasa ekspedisi yang dipilih (JNE, J&T, SiCepat, dll)\n\nBiaya pengiriman akan otomatis muncul saat checkout. Untuk pengiriman dalam jumlah besar, hubungi kami untuk mendapatkan harga khusus.",
                'category' => 'delivery',
                'keywords' => 'biaya kirim,ongkir,ongkos,gratis ongkir',
                'order' => 7,
            ],
            [
                'question' => 'Pengiriman ke wilayah mana saja?',
                'answer' => "Kami melayani pengiriman ke seluruh Indonesia melalui jasa ekspedisi terpercaya (JNE, J&T, SiCepat). Untuk wilayah terpencil atau dengan akses sulit, mohon konfirmasi terlebih dahulu ketersediaan layanan ekspedisi.",
                'category' => 'delivery',
                'keywords' => 'wilayah,area,jangkauan,kirim kemana',
                'order' => 8,
            ],

            // KATEGORI: ABOUT (Tentang BRMP)
            [
                'question' => 'Apa itu BRMP Malang?',
                'answer' => "BRMP (Balai Penelitian dan Pengembangan Tanaman Pangan) Malang adalah lembaga pemerintah yang fokus pada penelitian dan pengembangan benih tanaman pangan berkualitas. Kami menyediakan benih unggul hasil riset untuk mendukung pertanian Indonesia yang lebih produktif.",
                'category' => 'about',
                'keywords' => 'tentang,profil,siapa,lembaga,penelitian,pengembangan',
                'order' => 9,
            ],
            [
                'question' => 'Dimana lokasi Balai BRMP Malang?',
                'answer' => "Balai BRMP Malang berlokasi di:\nJl. Raya Karangploso KM 4, Malang, Jawa Timur\n\nJam operasional:\nSenin - Jumat: 08.00 - 16.00 WIB\nSabtu: 08.00 - 12.00 WIB\nMinggu & Libur Nasional: Tutup\n\nAnda bisa mengunjungi kami langsung untuk konsultasi atau pembelian benih.",
                'category' => 'about',
                'keywords' => 'lokasi,alamat,dimana,jam buka,buka,kapan,jam operasional,jam berapa,balai brmp malang',
                'order' => 10,
            ],
            [
                'question' => 'Apakah benih yang dijual bersertifikat?',
                'answer' => "Ya, semua benih yang kami jual adalah benih bersertifikat dan telah melalui uji mutu sesuai standar nasional. Setiap pembelian dilengkapi dengan label benih yang mencantumkan informasi varietas, kadar air, daya tumbuh, dan tanggal produksi.",
                'category' => 'about',
                'keywords' => 'sertifikat,mutu,kualitas,standar,label',
                'order' => 11,
            ],

            // KATEGORI: GENERAL (Umum)
            [
                'question' => 'Bagaimana cara cek stok benih?',
                'answer' => "Anda dapat mengecek stok benih melalui:\n1. Website: Lihat status stok di halaman produk\n2. Chatbot: Tanyakan langsung kepada chatbot kami\n3. WhatsApp: Hubungi customer service kami\n\nStok yang ditampilkan di website adalah real-time dan update otomatis.",
                'category' => 'general',
                'keywords' => 'stok,tersedia,ada,cek stok,ketersediaan',
                'order' => 12,
            ],
            [
                'question' => 'Apakah ada garansi untuk benih yang dibeli?',
                'answer' => "Kami memberikan garansi daya tumbuh minimal 80% sesuai label benih. Jika benih tidak memenuhi standar (rusak, berjamur, atau daya tumbuh rendah), Anda dapat mengajukan komplain dalam waktu 7 hari setelah penerimaan barang dengan menyertakan bukti foto dan video unboxing.",
                'category' => 'general',
                'keywords' => 'garansi,jaminan,komplain,rusak,tidak tumbuh',
                'order' => 13,
            ],
            [
                'question' => 'Bagaimana cara menyimpan benih yang sudah dibeli?',
                'answer' => "Tips penyimpanan benih:\n1. Simpan di tempat sejuk dan kering (suhu 15-20°C)\n2. Hindari paparan sinar matahari langsung\n3. Gunakan wadah kedap udara\n4. Jauhkan dari bahan kimia dan pupuk\n5. Gunakan dalam waktu 6-12 bulan untuk hasil optimal\n\nBenih yang tersimpan dengan baik dapat bertahan hingga 2 tahun dengan penurunan daya tumbuh minimal.",
                'category' => 'general',
                'keywords' => 'simpan,penyimpanan,cara simpan,tahan berapa lama',
                'order' => 14,
            ],
            [
                'question' => 'Apakah bisa konsultasi tentang budidaya?',
                'answer' => "Ya, kami menyediakan layanan konsultasi gratis untuk:\n1. Pemilihan varietas yang tepat\n2. Teknik budidaya dan perawatan\n3. Penanganan hama dan penyakit\n4. Pemupukan dan irigasi\n\nAnda bisa konsultasi melalui chatbot, WhatsApp, atau datang langsung ke Balai untuk konsultasi dengan ahli kami.",
                'category' => 'general',
                'keywords' => 'konsultasi,tanya,bantuan,cara tanam,budidaya',
                'order' => 15,
            ],
        ];

        foreach ($faqs as $faq) {
            DB::table('faqs')->insert([
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'category' => $faq['category'],
                'keywords' => $faq['keywords'],
                'order' => $faq['order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
