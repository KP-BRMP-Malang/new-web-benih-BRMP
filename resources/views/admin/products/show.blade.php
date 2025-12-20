@extends('layouts.admin')

@section('content')
    <div style="padding-top: 80px;">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Detail Produk</h2>
                        <div>
                            <a href="{{ route('admin.products.edit', $product->product_id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('admin.products.history', $product->product_id) }}" class="btn btn-secondary">
                                <i class="fas fa-history"></i> History
                            </a>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Informasi Produk</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Nama Produk:</strong></td>
                                                    <td>{{ $product->product_name }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Kategori:</strong></td>
                                                    <td>{{ $product->plantType->plant_type_name ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Deskripsi:</strong></td>
                                                    <td>
                                                        @php
                                                            if (!function_exists('renderProductDescriptionAdmin')) {
                                                                function renderProductDescriptionAdmin($text)
                                                                {
                                                                    if (is_null($text) || $text === '') {
                                                                        return '';
                                                                    }
                                                                    $text = str_replace('\\n', "\n", $text);
                                                                    $text = str_replace("\r\n", "\n", $text);
                                                                    $text = e($text);
                                                                    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
                                            
                                                                    $lines = explode("\n", $text);
                                                                    $html = '';
                                                                    $inList = false;
                                                                    $paraBuffer = [];
                                            
                                                                    $flushParagraph = function () use (&$paraBuffer, &$html) {
                                                                        if (count($paraBuffer) > 0) {
                                                                            $p = implode(' ', $paraBuffer);
                                                                            $html .= '<p>' . $p . '</p>';
                                                                            $paraBuffer = [];
                                                                        }
                                                                    };
                                            
                                                                    foreach ($lines as $rawLine) {
                                                                        $line = trim($rawLine);
                                                                        if ($line === '') {
                                                                            if ($inList) {
                                                                                $html .= '</ul>';
                                                                                $inList = false;
                                                                            }
                                                                            $flushParagraph();
                                                                            continue;
                                                                        }
                                                                        if (preg_match('/^[\-\*]\s+(.*)$/', $line, $m)) {
                                                                            $flushParagraph();
                                                                            if (!$inList) {
                                                                                $html .= '<ul>';
                                                                                $inList = true;
                                                                            }
                                                                            $html .= '<li>' . $m[1] . '</li>';
                                                                            continue;
                                                                        }
                                                                        if ($inList) {
                                                                            $html .= '</ul>';
                                                                            $inList = false;
                                                                        }
                                                                        $paraBuffer[] = $line;
                                                                    }
                                                                    if ($inList) {
                                                                        $html .= '</ul>';
                                                                    }
                                                                    $flushParagraph();
                                                                    return $html;
                                                                }
                                                            }
                                                        @endphp
                                                        {!! renderProductDescriptionAdmin($product->description) !!}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Stok:</strong></td>
                                                    <td>{{ $product->stock }} {{ $product->unit }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Stok Minimum:</strong></td>
                                                    <td>{{ $product->minimum_stock }} {{ $product->unit }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Satuan:</strong></td>
                                                    <td>{{ $product->unit }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Harga per Unit:</strong></td>
                                                    <td>Rp {{ number_format($product->price_per_unit, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Min. Pembelian:</strong></td>
                                                    <td>{{ $product->minimum_purchase }} {{ $product->unit }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status:</strong></td>
                                                    <td>
                                                        @if ($product->stock > $product->minimum_stock)
                                                            <span class="badge bg-success">Tersedia</span>
                                                        @elseif($product->stock > 0)
                                                            <span class="badge bg-warning">Stok Menipis</span>
                                                        @else
                                                            <span class="badge bg-danger">Habis</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Dibuat:</strong></td>
                                                    <td>{{ $product->created_at->format('d/m/Y H:i') }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($product->certificate_number || $product->certificate_class || $product->valid_from || $product->valid_until)
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Informasi Sertifikat</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    @if ($product->certificate_number)
                                                        <tr>
                                                            <td><strong>Nomor Sertifikat:</strong></td>
                                                            <td>{{ $product->certificate_number }}</td>
                                                        </tr>
                                                    @endif
                                                    @if ($product->certificate_class)
                                                        <tr>
                                                            <td><strong>Kelas Sertifikat:</strong></td>
                                                            <td>{{ $product->certificate_class }}</td>
                                                        </tr>
                                                    @endif
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    @if ($product->valid_from)
                                                        <tr>
                                                            <td><strong>Berlaku Dari:</strong></td>
                                                            <td>{{ \Carbon\Carbon::parse($product->valid_from)->format('d/m/Y') }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    @if ($product->valid_until)
                                                        <tr>
                                                            <td><strong>Berlaku Sampai:</strong></td>
                                                            <td>{{ \Carbon\Carbon::parse($product->valid_until)->format('d/m/Y') }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Gambar Produk</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $image1Path = Str::startsWith($product->image1, 'products/')
                                            ? $product->image1
                                            : 'products/' . $product->image1;

                                        $image2Path = Str::startsWith($product->image2, 'products/')
                                            ? $product->image2
                                            : 'products/' . $product->image2;

                                        $imageCertificatePath = Str::startsWith(
                                            $product->image_certificate,
                                            'certificates/',
                                        )
                                            ? $product->image_certificate
                                            : 'certificates/' . $product->image_certificate;
                                    @endphp
                                    @if ($product->image1)
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Gambar Utama</strong></label>
                                            <img src="{{ asset('storage/' . $image1Path) }}" alt="Gambar Utama"
                                                class="img-fluid rounded">
                                        </div>
                                    @endif

                                    @if ($product->image2)
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Gambar Tambahan</strong></label>
                                            <img src="{{ asset('storage/' . $image2Path) }}" alt="Gambar Tambahan"
                                                class="img-fluid rounded">
                                        </div>
                                    @endif

                                    @if ($product->image_certificate)
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Gambar Sertifikat</strong></label>
                                            <img src="{{ asset('storage/' . $imageCertificatePath) }}"
                                                alt="Gambar Sertifikat" class="img-fluid rounded">
                                        </div>
                                    @endif

                                    @if (!$product->image1 && !$product->image2 && !$product->image_certificate)
                                        <p class="text-muted text-center">Tidak ada gambar</p>
                                    @endif
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Riwayat Perubahan</h5>
                                </div>
                                <div class="card-body">
                                    @if ($product->histories->count() > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach ($product->histories->take(5) as $history)
                                                <div
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <small
                                                            class="text-muted">{{ $history->recorded_at->format('d/m/Y H:i') }}</small>
                                                    </div>
                                                    <span class="badge bg-info">Snapshot</span>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if ($product->histories->count() > 5)
                                            <div class="text-center mt-2">
                                                <a href="{{ route('admin.products.history', $product->product_id) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    Lihat Semua History
                                                </a>
                                            </div>
                                        @endif
                                    @else
                                        <p class="text-muted text-center">Belum ada riwayat perubahan</p>
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
