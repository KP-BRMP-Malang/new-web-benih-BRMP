@extends('layouts.admin')
@section('content')
    <style>
        .kuitansi-btn {
            border: 2px solid #16a34a;
            color: #16a34a;
            background: #fff;
            transition: all 0.3s;
        }

        .kuitansi-btn:hover {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: #fff !important;
            border-color: #16a34a;
            box-shadow: 0 2px 8px rgba(22, 163, 74, 0.15);
        }
    </style>

    <div style="padding-top: 80px;">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Detail Transaksi #{{ $transaction->transaction_id }}</h2>
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Informasi Transaksi -->
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Informasi Transaksi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>ID Transaksi:</strong></td>
                                                    <td>#{{ $transaction->transaction_id }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal Order:</strong></td>
                                                    <td>{{ $transaction->order_date->format('d M Y H:i') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status:</strong></td>
                                                    <td>
                                                        @if ($transaction->order_status == 'menunggu_kode_billing')
                                                            <span class="badge"
                                                                style="background-color: #AC8DF4; color: #44327A;">Menunggu
                                                                Kode Billing</span>
                                                        @elseif($transaction->order_status == 'menunggu_pembayaran')
                                                            <span class="badge"
                                                                style="background-color: #FFD469; color: #D07F39;">Menunggu
                                                                Pembayaran</span>
                                                        @elseif($transaction->order_status == 'menunggu_konfirmasi_pembayaran')
                                                            <span class="badge"
                                                                style="background-color: #FF97DA; color: #88135F;">Menunggu
                                                                Konfirmasi Pembayaran</span>
                                                        @elseif($transaction->order_status == 'diproses')
                                                            <span class="badge"
                                                                style="background-color: #81EBF1; color: #025B70;">Diproses</span>
                                                        @elseif($transaction->order_status == 'selesai')
                                                            <span class="badge"
                                                                style="background-color: #86F1B8; color: #178967;">Selesai</span>
                                                        @elseif($transaction->order_status == 'dibatalkan')
                                                            <span class="badge"
                                                                style="background-color: #FF7F98; color: #8E0116;">Dibatalkan</span>
                                                        @else
                                                            <span
                                                                class="badge bg-secondary">{{ $transaction->order_status }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Metode Pengiriman:</strong></td>
                                                    <td>{{ $transaction->delivery_method ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Harga:</strong></td>
                                                    <td><strong class="text-primary">Rp
                                                            {{ number_format($transaction->total_price, 0, ',', '.') }}</strong>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Customer:</strong></td>
                                                    <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email:</strong></td>
                                                    <td>{{ $transaction->user->email ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Telepon:</strong></td>
                                                    @php
                                                        $phoneNumber = $transaction->user->phone;

                                                        // Cek apakah nomor telepon dimulai dengan '08'
                                                        if (strpos($phoneNumber, '08') === 0) {
                                                            $phoneNumber = '628' . substr($phoneNumber, 2);
                                                        }
                                                    @endphp
                                                    @if ($transaction->user->phone)
                                                        <td>
                                                            <a href="https://wa.me/{{ $phoneNumber }}">
                                                                {{ $transaction->user->phone }}
                                                            </a>
                                                        </td>
                                                    @else
                                                        <td>N/A</td>
                                                    @endif
                                                </tr>
                                                <tr>
                                                    <td><strong>Penerima:</strong></td>
                                                    <td>{{ $transaction->recipient_name ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Telepon Penerima:</strong></td>
                                                    @php
                                                        $phoneNumber = $transaction->recipient_phone;

                                                        // Cek apakah nomor telepon dimulai dengan '08'
                                                        if (strpos($phoneNumber, '08') === 0) {
                                                            $phoneNumber = '628' . substr($phoneNumber, 2);
                                                        }
                                                    @endphp
                                                    @if ($transaction->recipient_phone)
                                                        <td>
                                                            <a href="https://wa.me/{{ $phoneNumber }}">
                                                                {{ $transaction->recipient_phone }}
                                                            </a>
                                                        </td>
                                                    @else
                                                        <td>N/A</td>
                                                    @endif
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Alamat Pengiriman -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Alamat Pengiriman</h5>
                                </div>
                                <div class="card-body">
                                    @if ($transaction->shippingAddress)
                                        <p><strong>{{ $transaction->shippingAddress->recipient_name }}</strong></p>
                                        <p>{{ $transaction->shippingAddress->address }}</p>
                                        <p>Telp: {{ $transaction->shippingAddress->recipient_phone }}</p>
                                        @if ($transaction->shippingAddress->note)
                                            <p><small class="text-muted">Catatan:
                                                    {{ $transaction->shippingAddress->note }}</small></p>
                                        @endif
                                    @else
                                        <p>{{ $transaction->shipping_address ?? 'N/A' }}</p>
                                        @if ($transaction->shipping_note)
                                            <p><small class="text-muted">Catatan: {{ $transaction->shipping_note }}</small>
                                            </p>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Item Transaksi -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Item Pesanan ({{ $transaction->transactionItems->count() }} item)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Produk</th>
                                                    <th>Harga</th>
                                                    <th>Jumlah</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($transaction->transactionItems as $item)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                @if ($item->product && $item->product->image1)
                                                                    <img src="{{ asset('storage/products/' . $item->product->image1) }}"
                                                                        alt="{{ $item->product->product_name }}"
                                                                        class="me-3"
                                                                        style="width: 50px; height: 50px; object-fit: cover;">
                                                                @endif
                                                                <div>
                                                                    <strong>{{ $item->product->product_name ?? 'Produk tidak ditemukan' }}</strong>
                                                                    <br>
                                                                    <small
                                                                        class="text-muted">{{ $item->product->plantType->plant_type_name ?? 'N/A' }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                        <td>{{ $item->quantity }} {{ $item->product->unit ?? '' }}</td>
                                                        <td><strong>Rp
                                                                {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Flow Status -->
                            <div class="card mb-4">
                                @php
                                    $payment = $transaction->payments->first();
                                @endphp
                                <div class="card-header">
                                    <h5 class="mb-0">Status Alur Pembayaran</h5>
                                </div>
                                <div class="card-body">
                                    <div class="timeline">
                                        <!-- Step 1: Pesanan Dibuat -->
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-shopping-cart text-primary d-flex justify-content-center"
                                                    style="font-size: 1.5rem;width:30px;"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0">Pesanan Dibuat</h6>
                                                <small
                                                    class="text-muted">{{ $transaction->order_date->format('d M Y H:i') }}</small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-check-circle text-success"></i>
                                            </div>
                                        </div>

                                        <!-- Step 2: Input Kode Billing & Ongkir -->
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-file-invoice text-info d-flex justify-content-center"
                                                    style="font-size: 1.5rem;width:30px;"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0">Input Kode Billing & Ongkir</h6>
                                                @if ($transaction->order_status == 'menunggu_kode_billing')
                                                    <small class="text-warning">Menunggu input kode billing &
                                                        ongkir</small>
                                                @else
                                                    <small class="text-success">Sudah diinput</small>
                                                @endif
                                            </div>
                                            <div class="flex-shrink-0">
                                                @if ($transaction->order_status == 'menunggu_kode_billing')
                                                    <i class="fas fa-clock text-warning"></i>
                                                @else
                                                    <i class="fas fa-check-circle text-success"></i>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Step 3: Pembayaran Diupload -->
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-credit-card text-{{ $payment && $payment->payment_status == 'approved' ? 'success' : ($payment && $payment->payment_status == 'rejected' ? 'danger' : 'warning') }} d-flex justify-content-center"
                                                    style="font-size: 1.5rem;width:30px;"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0">Bukti Pembayaran Diupload</h6>
                                                @if ($payment)
                                                    <small
                                                        class="text-muted">{{ $payment->payment_date ? $payment->payment_date->format('d M Y H:i') : '-' }}</small>
                                                    <br>
                                                    <small
                                                        class="text-{{ $payment->payment_status == 'approved' ? 'success' : ($payment->payment_status == 'rejected' ? 'danger' : 'warning') }}">
                                                        Status:
                                                        @if ($payment->payment_status == 'pending')
                                                            Menunggu Konfirmasi
                                                        @elseif($payment->payment_status == 'approved')
                                                            Disetujui
                                                        @elseif($payment->payment_status == 'rejected')
                                                            Ditolak
                                                        @else
                                                            Belum Dibayar
                                                        @endif
                                                    </small>
                                                @else
                                                    <small class="text-warning">Belum ada pembayaran</small>
                                                @endif
                                            </div>
                                            <div class="flex-shrink-0">
                                                @if ($payment)
                                                    @if ($payment->payment_status == 'pending')
                                                        <i class="fas fa-clock text-warning"></i>
                                                    @elseif($payment->payment_status == 'approved')
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @elseif($payment->payment_status == 'rejected')
                                                        <i class="fas fa-times-circle text-danger"></i>
                                                    @else
                                                        <i class="fas fa-credit-card text-muted"></i>
                                                    @endif
                                                @else
                                                    <i class="fas fa-credit-card text-muted"></i>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Step 4: Pesanan Diproses (jika pembayaran disetujui) -->
                                        @if ($payment && $payment->payment_status == 'approved')
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-cog text-primary d-flex justify-content-center"
                                                        style="font-size: 1.5rem;width:30px;"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0">Pesanan Diproses</h6>
                                                    <small class="text-muted">Status transaksi:
                                                        {{ ucfirst(str_replace('_', ' ', $transaction->order_status)) }}</small>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-check-circle text-success"></i>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Step 5: Pesanan Selesai -->
                                        @if ($transaction->order_status == 'selesai')
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-flag-checkered text-success d-flex justify-content-center"
                                                        style="font-size: 1.5rem;width:30px;"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0">Pesanan Selesai</h6>
                                                    <small
                                                        class="text-muted">{{ $transaction->done_date ? $transaction->done_date->format('d M Y H:i') : '' }}</small>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-check-circle text-success"></i>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Step 6: Pesanan Dibatalkan -->
                                        @if ($transaction->order_status == 'dibatalkan')
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-times-circle text-danger d-flex justify-content-center"
                                                        style="font-size: 1.5rem;width:30px;"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0 text-danger">Pesanan Dibatalkan</h6>
                                                    <small
                                                        class="text-muted">{{ $transaction->updated_at->format('d M Y H:i') }}</small>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-times-circle text-danger"></i>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Tambahan -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Informasi Tambahan</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Tujuan Pembelian:</strong></td>
                                            <td>{{ $transaction->purchase_purpose ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tempat Tanam - Provinsi:</strong></td>
                                            <td>{{ $transaction->province->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tempat Tanam - Kota/Kabupaten:</strong></td>
                                            <td>{{ $transaction->regency->name ?? 'N/A' }}</td>
                                        </tr>
                                        @if ($transaction->estimated_delivery_date)
                                            <tr>
                                                <td><strong>Estimasi Kirim:</strong></td>
                                                <td>{{ $transaction->estimated_delivery_date->format('d M Y') }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Dibuat:</strong></td>
                                            <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Diupdate:</strong></td>
                                            <td>{{ $transaction->updated_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-md-4">
                            <!-- Status Update -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Update Status</h5>
                                </div>
                                <div class="card-body">
                                    @if ($transaction->order_status == 'menunggu_kode_billing')
                                        <form method="POST"
                                            action="{{ route('admin.transactions.billing.store', $transaction->transaction_id) }}"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="billing_code_file" class="form-label">File Kode
                                                    Billing</label>
                                                <input type="file" class="form-control" name="billing_code_file"
                                                    id="billing_code_file" accept=".jpg,.jpeg,.png" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="no_rek_ongkir" class="form-label">No Rekening Ongkir</label>
                                                <input type="file" class="form-control" name="no_rek_ongkir"
                                                    id="no_rek_ongkir" accept=".jpg,.jpeg,.png" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="total_ongkir" class="form-label">Total Ongkir</label>
                                                <input type="number" class="form-control" name="total_ongkir"
                                                    id="total_ongkir" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">Simpan</button>
                                        </form>
                                    @elseif($transaction->order_status == 'menunggu_pembayaran')
                                        <div>
                                            Menunggu pelanggan melakukan pembayaran.
                                        </div>
                                    @elseif($transaction->order_status == 'menunggu_konfirmasi_pembayaran' && $payment->payment_status == 'pending')
                                        <div class="mt-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <form method="POST"
                                                        action="{{ route('admin.transactions.payment.approve', $payment->payment_id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm w-100"
                                                            onclick="return confirm('Konfirmasi pembayaran ini?')">
                                                            <i class="fas fa-check"></i> Konfirmasi
                                                        </button>
                                                    </form>
                                                </div>
                                                <div class="col-md-6">
                                                    <button type="button" class="btn btn-danger btn-sm w-100"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#rejectModal{{ $payment->payment_id }}">
                                                        <i class="fas fa-times"></i> Tolak
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($transaction->order_status == 'diproses')
                                        <form method="POST"
                                            action="{{ route('admin.transactions.resi.update', $transaction->transaction_id) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-3">
                                                <label for="no_resi" class="form-label">Nomor Resi</label>
                                                <input type="text" class="form-control" name="no_resi" id="no_resi"
                                                    required value="{{ $transaction->no_resi }}">
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">Simpan</button>
                                        </form>
                                    @elseif($transaction->order_status == 'selesai')
                                        <div class="alert alert-success" role="alert">
                                            Transaksi telah selesai. Nomor resi: {{ $transaction->no_resi }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @php
                                $payment = $transaction->payments->first();
                            @endphp
                            @if ($payment && $payment->payment_status == 'approved')
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Kuitansi Pembayaran</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div>
                                                <a href="{{ route('admin.transactions.invoice.view', $transaction->transaction_id) }}"
                                                    class="btn kuitansi-btn me-2"
                                                    style="border-radius:8px;font-weight:600;">
                                                    <i class="fas fa-file-invoice"></i> Lihat Kuitansi
                                                </a>
                                                <a href="{{ route('admin.transactions.invoice.download', $transaction->transaction_id) }}"
                                                    class="btn kuitansi-btn" style="border-radius:8px;font-weight:600;">
                                                    <i class="fas fa-download"></i> Unduh Kuitansi
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Informasi Pembayaran -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Informasi Pembayaran</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $payment = $transaction->payments->first();
                                    @endphp

                                    @if ($payment)
                                        <div class="mb-3">
                                            <strong>Status Pembayaran:</strong>
                                            @if ($payment->payment_status == 'no_payment')
                                                <span class="badge"
                                                    style="background-color: #AC8DF4; color: #44327A;">Belum Dibayar</span>
                                            @elseif($payment->payment_status == 'pending')
                                                <span class="badge"
                                                    style="background-color: #FFD469; color: #D07F39;">Menunggu
                                                    Konfirmasi</span>
                                            @elseif($payment->payment_status == 'approved')
                                                <span class="badge"
                                                    style="background-color: #86F1B8; color: #178967;">Disetujui</span>
                                            @elseif($payment->payment_status == 'rejected')
                                                <span class="badge"
                                                    style="background-color: #FF7F98; color: #8E0116;">Ditolak</span>
                                            @endif
                                        </div>
                                        <div class="mb-2">
                                            <strong>Tanggal Pembayaran:</strong>
                                            {{ $payment->payment_date ? $payment->payment_date->format('d M Y H:i') : '-' }}
                                        </div>
                                        <div class="mb-2">
                                            <strong>Kode Billing:</strong>
                                            @if ($payment->billing_code_file)
                                                <div class="mt-2">
                                                    <a href="{{ asset('storage/' . $payment->billing_code_file) }}"
                                                        target="_blank">
                                                        <img src="{{ asset('storage/' . $payment->billing_code_file) }}"
                                                            alt="Kode Billing" class="img-fluid rounded"
                                                            style="width: 100%; max-height: 300px; object-fit: contain;">
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-muted">Belum ada file</span>
                                            @endif
                                        </div>
                                        <div class="mb-2">
                                            <strong>No Rekening Ongkir:</strong>
                                            @if ($payment->no_rek_ongkir)
                                                <div class="mt-2">
                                                    <a href="{{ asset('storage/' . $payment->no_rek_ongkir) }}"
                                                        target="_blank">
                                                        <img src="{{ asset('storage/' . $payment->no_rek_ongkir) }}"
                                                            alt="No Rekening Ongkir" class="img-fluid rounded"
                                                            style="width: 100%; max-height: 300px; object-fit: contain;">
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-muted">Belum ada file</span>
                                            @endif
                                        </div>
                                        <div class="mb-2">
                                            <strong>Total Ongkir:</strong>
                                            Rp {{ number_format($transaction->total_ongkir, 0, ',', '.') }}
                                        </div>

                                        @if ($payment->rejection_reason)
                                            <div class="alert alert-danger mt-2">
                                                <strong>Alasan Penolakan:</strong><br>
                                                {!! nl2br($payment->rejection_reason) !!}
                                            </div>
                                        @endif

                                        <div class="mb-2">
                                            <strong>Bukti Pembayaran Billing:</strong>
                                            @if ($payment->photo_proof_payment_billing)
                                                <div class="mt-2">
                                                    <a href="{{ asset('storage/bukti_pembayaran/' . $payment->photo_proof_payment_billing) }}"
                                                        target="_blank">
                                                        <img src="{{ asset('storage/bukti_pembayaran/' . $payment->photo_proof_payment_billing) }}"
                                                            alt="Bukti Billing" class="img-fluid rounded"
                                                            style="max-width: 100%; max-height: 300px; object-fit: contain;">
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-muted">Belum ada bukti pembayaran billing</span>
                                            @endif
                                        </div>
                                        <div class="mb-2">
                                            <strong>Bukti Pembayaran Ongkir:</strong>
                                            @if ($payment->photo_proof_payment_ongkir)
                                                <div class="mt-2">
                                                    <a href="{{ asset('storage/bukti_pembayaran/' . $payment->photo_proof_payment_ongkir) }}"
                                                        target="_blank">
                                                        <img src="{{ asset('storage/bukti_pembayaran/' . $payment->photo_proof_payment_ongkir) }}"
                                                            alt="Bukti Ongkir" class="img-fluid rounded"
                                                            style="max-width: 100%; max-height: 300px; object-fit: contain;">
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-muted">Belum ada bukti pembayaran ongkir</span>
                                            @endif
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal{{ $payment->payment_id }}"
                                            tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Tolak Pembayaran</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST"
                                                        action="{{ route('admin.transactions.payment.reject', $payment->payment_id) }}">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Alasan Penolakan</label>
                                                                <textarea name="rejection_reason" class="form-control" rows="3" required
                                                                    placeholder="Berikan alasan mengapa pembayaran ditolak..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger">Tolak
                                                                Pembayaran</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        @if ($transaction->order_status == 'menunggu_kode_billing')
                                            <div> Masukkan Kode Billing dan Nomor Rekening Ongkir </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
