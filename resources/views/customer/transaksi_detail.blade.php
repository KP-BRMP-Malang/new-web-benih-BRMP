@extends('layouts.app')

@section('content')
    <style>
        .transaksi-detail-container {
            max-width: 1000px;
            margin: 40px auto 40px auto;
            background: #f8fafc;
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            padding: 40px 32px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .transaksi-detail-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 24px;
            color: #1a1a1a;
            text-align: center;
            position: relative;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .transaksi-detail-info {
            margin-bottom: 24px;
            font-size: 1.1rem;
            background: rgba(255, 255, 255, 0.7);
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .transaksi-detail-info .date-info {
            font-weight: 600;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .transaksi-detail-status {
            display: inline-block;
            padding: 10px;
            border-radius: 12px;
            font-size: 0.9em;
            font-weight: 700;
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #15803d;
            border: 2px solid #16a34a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(22, 163, 74, 0.2);
        }

        .delivery-info {
            background: rgba(255, 255, 255, 0.7);
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            backdrop-filter: blur(10px);
            margin-bottom: 24px;
        }

        .delivery-info .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            font-weight: 600;
            color: #374151;
        }

        .delivery-info .info-item:last-child {
            margin-bottom: 0;
        }

        .delivery-info .info-label {
            min-width: 120px;
            color: #6b7280;
        }

        .delivery-info .info-value {
            color: #16a34a;
            font-weight: 700;
        }

        /* Product Cards Styles */
        .product-items-container {
            margin-bottom: 24px;
        }

        .product-item {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(34, 197, 94, 0.2);
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: all 0.3s ease;
        }

        .product-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border-color: rgba(34, 197, 94, 0.4);
        }

        .click-indicator {
            text-align: center;
            margin-top: 8px;
            font-size: 0.85rem;
            color: #666;
            transition: color 0.3s ease;
        }

        .product-item:hover+.click-indicator {
            color: #16a34a;
        }

        .product-image-placeholder {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            flex-shrink: 0;
        }

        .product-left-column {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }

        .product-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            min-height: 128px;
            min-width: 0;
        }

        .product-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .product-name {
            font-size: 1.3em;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
        }

        .product-price {
            font-weight: 600;
            color: #16a34a;
            margin: 0;
        }

        .product-actions {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
        }

        .subtotal-info {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: flex-end;
        }

        .subtotal-label {
            font-size: 0.9rem;
            color: #6b7280;
            font-weight: 500;
        }

        .subtotal-amount {
            font-size: 1rem;
            font-weight: 700;
            color: #16a34a;
        }

        .transaksi-detail-total {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            padding: 20px;
            border-radius: 16px;
            font-weight: 800;
            color: white;
            font-size: 1.3em;
            text-align: right;
            margin-top: 16px;
            box-shadow: 0 4px 16px rgba(22, 163, 74, 0.2);
            border: 2px solid #16a34a;
        }

        .transaksi-detail-payment {
            background: linear-gradient(135deg, #fff, #f8fafc);
            border-radius: 16px;
            padding: 24px;
            margin-top: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }

        .payment-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .payment-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        .payment-item {
            background: rgba(22, 163, 74, 0.05);
            padding: 12px 16px;
            border-radius: 10px;
            border-left: 4px solid #16a34a;
        }

        .payment-item .label {
            font-size: 0.9rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .payment-item .value {
            font-weight: 700;
            color: #1f2937;
        }

        .payment-status {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #15803d;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .payment-proof {
            margin-top: 16px;
            text-align: center;
        }

        .payment-proof .proof-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            display: block;
        }

        .transaksi-detail-payment img {
            max-width: 280px;
            width: 100%;
            border-radius: 12px;
            margin-top: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            border: 2px solid #e5e7eb;
            transition: transform 0.3s ease;
        }

        .transaksi-detail-payment img:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .no-payment {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 20px;
            background: rgba(107, 114, 128, 0.1);
            border-radius: 10px;
            border: 2px dashed #d1d5db;
        }


        .upload-form-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .upload-form {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            border: 2px dashed #dee2e6;
            transition: all 0.3s ease;
        }

        .upload-form:hover {
            border-color: #16a34a;
            background: #f1f8f4;
        }

        .upload-form .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .upload-form .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .upload-form .form-control:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
        }

        .upload-form .form-text {
            font-size: 0.9rem;
            color: #6b7280;
            margin-top: 4px;
        }

        .payment-instructions {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border: 1px solid #90caf9;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            color: #1976d2;
        }

        .payment-instructions .instructions-title {
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .payment-instructions .instructions-title::before {
            content: '💡';
            font-size: 1.1rem;
        }

        .payment-instructions ul {
            margin: 8px 0 0 0;
            padding-left: 20px;
        }

        .payment-instructions li {
            margin-bottom: 4px;
            font-size: 0.95rem;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-weight: 500;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #15803d;
            border-left: 4px solid #16a34a;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fecaca, #fca5a5);
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .alert .btn-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: inherit;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .alert .btn-close:hover {
            opacity: 1;
        }

        @media (max-width: 1023px) {
            .btn-green {
                font-size: 12px;
            }

            .transaksi-detail-container {
                padding: 10px;
                font-size: 12px;
                margin: 0 0;
                background: none;
                box-shadow: none;

            }

            .mobile-back-btn,
            .mobile-detail-transaksi-title {
                display: flex !important;
            }

            .mobile-cart-btn,
            .mobile-menu-toggle,
            .mobile-search-container {
                display: none !important;
            }

            .btn-kembali-transaksi {
                display: none;
            }

            .transaksi-detail-title {
                font-size: 2em;
                margin-bottom: 20px;
            }

            .transaksi-detail-info {
                font-size: 1em;
                padding: 12px;
                margin-bottom: 12px;
            }

            .delivery-info {
                padding: 12px;
                font-size: 0.9em;
                margin-bottom: 12px;

            }

            .transaksi-detail-payment {
                padding: 0 0;
                background: none;
                box-shadow: none;

            }

            .product-item {
                padding: 12px;
                margin-bottom: 10px;
            }

            .product-image-placeholder {
                width: 80px;
                height: 80px;
            }
        }
    </style>

    <div class="transaksi-detail-container">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <button onclick="location.href='{{ route('transaksi') }}'" class="btn-green btn-kembali-transaksi mb-3 px-3"
            style="background:none;color:#4CAF50">
            Kembali ke Daftar Transaksi
        </button>

        <div class="transaksi-detail-title">Detail Transaksi #{{ $transaction->transaction_id }}</div>

        <div class="transaksi-detail-info">
            <div class="date-info">
                @php
                    $orderDate = $transaction->order_date ?? $transaction->created_at;
                @endphp
                <span><b>Tanggal:</b> {{ ($orderDate && method_exists($orderDate, 'format')) ? $orderDate->format('d M Y H:i') : '-' }}</span>
            </div>
            <span class="transaksi-detail-status">{{ $transaction->display_status }}</span>
            @if ($transaction->order_status == 'selesai' && $transaction->no_resi)
                <div class="date-info">
                    <span><b>Nomor Resi:</b> {{ $transaction->no_resi }}</span>
                </div>
            @endif
        </div>

        @php
            function formatDeliveryMethod($method)
            {
                switch (strtolower($method)) {
                    case 'reguler':
                        return 'Reguler';
                    case 'kargo':
                        return 'Kargo';
                    case 'pickup':
                        return 'Pickup di Tempat';
                    default:
                        return ucfirst($method);
                }
            }
        @endphp

        <div class="delivery-info">
            <div class="info-item">
                <span class="info-label"><b>Metode Pengiriman:</b></span>
                <span class="info-value">{{ formatDeliveryMethod($transaction->delivery_method) }}</span>
            </div>
            @if ($transaction->estimated_delivery_date)
                <div class="info-item">
                    <span class="info-label"><b>Estimasi Tiba:</b></span>
                    <span class="info-value">{{ $transaction->estimated_delivery_date->translatedFormat('d M Y') }}</span>
                </div>
            @endif
        </div>

        @if ($transaction->recipient_name || $transaction->shippingAddress)
            <div class="delivery-info">
                <div class="info-item">
                    <span class="info-label"><b>Alamat Pengiriman:</b></span>
                    <span class="info-value">
                        @if ($transaction->shippingAddress)
                            {{ $transaction->shippingAddress->recipient_name }}<br>
                            {{ $transaction->shippingAddress->address }}<br>
                            Telp: +{{ $transaction->shippingAddress->recipient_phone }}
                            @if ($transaction->shippingAddress->note)
                                <br><small class="text-muted">Catatan: {{ $transaction->shippingAddress->note }}</small>
                            @endif
                        @else
                            {{ $transaction->recipient_name }}<br>
                            {{ $transaction->shipping_address }}<br>
                            Telp: +{{ $transaction->recipient_phone }}
                            @if ($transaction->shipping_note)
                                <br><small class="text-muted">Catatan: {{ $transaction->shipping_note }}</small>
                            @endif
                        @endif
                    </span>
                </div>
            </div>
        @endif

        <!-- Product Cards Section -->
        <div class="product-items-container">
            @foreach ($transaction->transactionItems as $item)
                @php
                    $productHistory = \App\Models\ProductHistory::where('product_id', $item->product->product_id)
                        ->where('recorded_at', '<=', $transaction->order_date)
                        ->orderBy('recorded_at', 'desc')
                        ->first();
                @endphp
                <div class="product-item" style="cursor: pointer;"
                    onclick="window.location.href='{{ $productHistory ? route('produk.history.detail', $productHistory->history_id) : route('produk.detail', $item->product->product_id) }}'">
                    <div class="product-left-column">
                        <div class="product-image-placeholder">

                            @if ($item->product->image1)
                                @php
                                    $imagePath = Str::startsWith($item->product->image1, 'products/')
                                        ? $item->product->image1
                                        : 'products/' . $item->product->image1;
                                @endphp
                                <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ $item->product->product_name }}"
                                    style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
                            @else
                                <i class="fas fa-seedling" style="font-size:24px;"></i>
                            @endif
                        </div>
                        <div class="product-actions">
                            <button href="{{ route('produk.detail', $item->product->product_id) }}"
                                class="btn-green w-100 py-1" onclick="event.stopPropagation();">
                                Beli Lagi
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-details">
                            <div class="product-name">{{ $item->product->product_name ?? '-' }}</div>
                            <div class="product-price"><span style="color: black;">
                                    {{ $item->quantity }} {{ $item->product->unit ?? 'kg' }} x
                                </span>
                                Rp{{ number_format($item->unit_price, 0, ',', '.') }} /
                                {{ $item->product->unit ?? 'kg' }}</div>
                        </div>
                        <div class="subtotal-info">
                            <span class="subtotal-label">Subtotal:</span>
                            <span class="subtotal-amount">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="transaksi-detail-total">
            Total Belanja: Rp{{ number_format($transaction->total_price, 0, ',', '.') }}
        </div>
        @php
            $payment = $transaction->payments->first();
        @endphp
        @if ($payment && $payment->payment_status == 'approved')
            <div class="d-flex justify-content-end gap-2 my-3">
                <button
                    onclick="window.location.href='{{ route('transaksi.invoice.view', $transaction->transaction_id) }}'"
                    class="btn-green px-3" style="background: none;color:#4CAF50; border:2px solid #4CAF50;">
                    <i class="fas fa-file-invoice"></i> Lihat Kuitansi
                </button>
                <button
                    onclick="window.location.href='{{ route('transaksi.invoice.download', $transaction->transaction_id) }}'"
                    class="btn-green px-3">
                    <i class="fas fa-download"></i> Unduh Kuitansi
                </button>
            </div>
        @endif
        <div class="transaksi-detail-payment">
            <div class="payment-title"><b>Pembayaran</b></div>
            @php
                $payments = $transaction->payments;
                $hasPayments = $payments && $payments->count() > 0;
                $latestPayment = $hasPayments ? $payments->last() : null;
                $rejectedPayments = $latestPayment && $latestPayment->payment_status == 'rejected';
                $hasKodeBilling = $latestPayment && $latestPayment->billing_code_file != null;
                $hasBillingProof = $latestPayment && $latestPayment->photo_proof_payment_billing != null;
                $hasOngkirProof = $latestPayment && $latestPayment->photo_proof_payment_ongkir != null;
                $showBillingForm = !$hasBillingProof && $transaction->order_status === 'menunggu_pembayaran';
                $showOngkirForm = !$hasOngkirProof && $transaction->order_status === 'menunggu_pembayaran';
            @endphp

            @if ($hasBillingProof || $hasOngkirProof)
                <div class="payment-info">
                    <div class="payment-item">
                        <div class="label">📅 Tanggal Pembayaran</div>
                        <div class="value">
                            {{ ($latestPayment && $latestPayment->payment_date) ? \Carbon\Carbon::parse($latestPayment->payment_date)->format('d M Y H:i') : '-' }}
                        </div>
                    </div>
                    <div class="payment-item">
                        <div class="label">💰 Total Pembelian</div>
                        <div class="value">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</div>
                    </div>
                    <div class="payment-item">
                        <div class="label">🚚 Total Ongkir</div>
                        <div class="value">Rp {{ number_format($transaction->total_ongkir, 0, ',', '.') }}</div>
                    </div>
                    <div class="payment-item">
                        <div class="label">✅ Status Pembayaran</div>
                        <div class="value">
                            <span class="payment-status">{{ $latestPayment->payment_status }}</span>
                        </div>
                    </div>
                </div>
                @if ($hasBillingProof)
                    <div class="payment-proof">
                        <span class="proof-label">📸 Bukti Pembayaran Billing:</span>
                        <br>
                        <a href="{{ asset('storage/bukti_pembayaran/' . $latestPayment->photo_proof_payment_billing) }}"
                            target="_blank">
                            <img src="{{ asset('storage/bukti_pembayaran/' . $latestPayment->photo_proof_payment_billing) }}"
                                alt="Bukti Pembayaran Billing">
                        </a>
                    </div>
                @endif
                @if ($hasOngkirProof)
                    <div class="payment-proof">
                        <span class="proof-label">📸 Bukti Pembayaran Ongkir:</span>
                        <br>
                        <a href="{{ asset('storage/bukti_pembayaran/' . $latestPayment->photo_proof_payment_ongkir) }}"
                            target="_blank">
                            <img src="{{ asset('storage/bukti_pembayaran/' . $latestPayment->photo_proof_payment_ongkir) }}"
                                alt="Bukti Pembayaran Ongkir">
                        </a>
                    </div>
                @endif
                 <div class="no-payment">
                    <strong>Alasan Penolakan:</strong><br>
                    {!! nl2br($latestPayment->rejection_reason) !!}
                </div>
            @elseif(!$hasPayments && !$hasKodeBilling)
                <div class="no-payment">
                    ⚠️ Belum ada pembayaran untuk transaksi ini. Silakan tunggu kode billing dari admin.
                </div>
            @elseif($rejectedPayments)
               
            @endif
            @if ($showBillingForm || $showOngkirForm)
                <div class="payment-instructions">
                    <div class="instructions-title">Petunjuk Pembayaran:</div>
                    <ul>
                        <li>Transfer sejumlah <strong>Rp
                                {{ number_format($transaction->total_price, 0, ',', '.') }}</strong></li>
                        <li>Simpan bukti transfer (screenshot atau foto)</li>
                        <li>Upload bukti transfer di bawah ini</li>
                        <li>Tim kami akan memverifikasi pembayaran Anda</li>
                    </ul>
                </div>
                <div class="upload-form-title">
                    📤 Upload Bukti Pembayaran
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="upload-form-container">

                    {{-- Formulir Unggah Bukti Pembayaran Billing --}}
                    @if ($showBillingForm)
                        <div class="upload-form">
                            <h4>Bukti Pembayaran Billing</h4>
                            @if ($latestPayment && $latestPayment->billing_code_file)
                                <div class="mb-3 text-center">
                                    <span class="proof-label">Kode Billing:</span><br>
                                    <a href="{{ asset('storage/' . $latestPayment->billing_code_file) }}"
                                        target="_blank">
                                        <img src="{{ asset('storage/' . $latestPayment->billing_code_file) }}"
                                            alt="Kode Billing"
                                            style="max-width:400px;width:100%;border-radius:16px;margin-bottom:12px;box-shadow:0 4px 16px rgba(0,0,0,0.12);">
                                    </a>
                                </div>
                            @endif
                            <form enctype="multipart/form-data" method="POST" action="{{ route('payment.upload') }}">
                                @csrf
                                <input type="hidden" name="transaction_id" value="{{ $transaction->transaction_id }}">
                                <div class="mb-3">
                                    <label for="buktiPembayaranBilling" class="form-label">Pilih file gambar bukti
                                        transfer</label>
                                    <input class="form-control" type="file" id="buktiPembayaranBilling"
                                        name="bukti_pembayaran_billing" accept=".jpg,.jpeg,.png" required>
                                    <div class="form-text">Format yang didukung: JPG, JPEG, PNG (Max. 10MB)</div>
                                </div>
                                <button type="submit" class="btn-green px-3">
                                    📤 Upload Bukti Pembayaran Billing
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Formulir Unggah Bukti Pembayaran Ongkir --}}
                    @if ($showOngkirForm)
                        <div class="upload-form mt-4">
                            <h4>Bukti Pembayaran Ongkir</h4>
                            @if ($latestPayment && $latestPayment->no_rek_ongkir)
                                <div class="mb-3 text-center">
                                    <span class="proof-label">Nomor Rekening Ongkir:</span><br>
                                    <a href="{{ asset('storage/' . $latestPayment->no_rek_ongkir) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $latestPayment->no_rek_ongkir) }}"
                                            alt="Nomor Rekening Ongkir"
                                            style="max-width:400px;width:100%;border-radius:16px;margin-bottom:12px;box-shadow:0 4px 16px rgba(0,0,0,0.12);">
                                    </a>
                                </div>
                                <div class="mb-2 text-center">
                                    <span class="proof-label">Total Ongkir:</span>
                                    <span
                                        class="subtotal-amount">Rp{{ number_format($transaction->total_ongkir, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <form enctype="multipart/form-data" method="POST" action="{{ route('payment.upload') }}">
                                @csrf
                                <input type="hidden" name="transaction_id" value="{{ $transaction->transaction_id }}">
                                <div class="mb-3">
                                    <label for="buktiPembayaranOngkir" class="form-label">Pilih file gambar bukti
                                        transfer</label>
                                    <input class="form-control" type="file" id="buktiPembayaranOngkir"
                                        name="bukti_pembayaran_ongkir" accept=".jpg,.jpeg,.png" required>
                                    <div class="form-text">Format yang didukung: JPG, JPEG, PNG (Max. 10MB)</div>
                                </div>
                                <button type="submit" class="btn-green px-3">
                                    📤 Upload Bukti Pembayaran Ongkir
                                </button>
                            </form>
                        </div>
                    @endif


                </div>
            @endif
        </div>
    </div>
@endsection

