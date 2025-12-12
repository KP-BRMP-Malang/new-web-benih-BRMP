@extends('layouts.app')
@section('content')
    <style>
        .product-detail-modern-bg {
            min-height: 100vh;
            background: #f8f9fa;
            padding-top: 100px;
            /* dinaikkan agar tidak tenggelam appbar */
            padding-bottom: 40px;
        }

        .product-detail-modern-container {
            display: flex;
            gap: 32px;
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            padding: 40px 32px;
        }

        .product-detail-modern-left {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .product-detail-main-image {
            width: 340px;
            height: 340px;
            object-fit: contain;
            border-radius: 16px;
            background: #f3f3f3;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .product-detail-main-image-wrapper {
            width: 340px;
            height: 340px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f3f3f3;
            border-radius: 16px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .product-detail-default-image {
            width: 340px;
            height: 340px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f0f0f0;
            border-radius: 16px;
            color: #999;
        }

        .product-detail-default-image i {
            font-size: 80px;
        }

        .product-detail-main-image,
        .product-detail-default-image {
            cursor: zoom-in;
        }

        .product-detail-thumbs {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
        }

        .product-detail-thumbs img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            cursor: pointer;
        }

        .product-detail-thumbs img.selected {
            border: 2px solid #4CAF50;
        }

        .product-detail-thumb-default {
            width: 60px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f0f0f0;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            cursor: pointer;
            color: #999;
        }

        .product-detail-thumb-default.selected {
            border: 2px solid #4CAF50;
        }

        .product-detail-thumb-default i {
            font-size: 20px;
        }

        .product-detail-modern-center {
            flex: 1.5;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding-top: 10px;
        }

        .product-detail-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .product-detail-price {
            color: #388E3C;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .product-detail-stock {
            color: #888;
            font-size: 1rem;
            margin-bottom: 18px;
        }

        .product-detail-desc {
            margin-top: 18px;
            font-size: 1.1rem;
        }

        .certificate-info {
            margin-top: 20px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .product-detail-card {
            width: 320px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 24px 20px 18px 20px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            margin-top: 0;
        }

        .product-detail-card-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 16px;
        }

        .product-detail-card-qty {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .product-detail-card-subtotal {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 18px;
            margin-top: 8px;
        }

        .bar-pesan {
            display: none !important;
        }

        @media (max-width: 1023px) {

            .mobile-search-container,
            .mobile-menu-toggle {
                display: none !important;
            }

            .mobile-back-btn {
                display: flex !important;
            }

            .modal-backdrop.show {
                pointer-events: none !important;
                z-index: 1040 !important;
            }

            .product-detail-modern-bg {
                padding-top: 0px;
                padding-bottom: 0px
            }

            .product-detail-modern-container {
                flex-direction: column;
                padding-top: 0px;
                padding-left: 0px;
                padding-right: 0px;
                padding-bottom: 0px;
                gap: 0px;
            }

            .product-detail-main-image-wrapper {
                width: 100%;
                height: auto;
                border-radius: 0;
                margin-bottom: 0px;
            }

            .product-detail-main-image {
                width: 100%;
                height: 100%;
                border-radius: 0;
                margin-bottom: 0px
            }

            .product-detail-thumbs {
                flex-wrap: nowrap;
                justify-content: center;
                gap: 8px;
                padding: 8px 0;
                margin-bottom: 0px
            }

            .product-detail-title {
                font-size: 24px;
                margin-bottom: 0px;
            }

            .product-detail-price {
                font-size: 20px;
                margin-bottom: 0px;
            }

            .product-detail-desc {
                margin-top: 0px;
                font-size: 16px;
            }

            .product-detail-modern-center {
                flex: 1;
                padding-top: 8px;
                padding-inline: 20px;
                padding-bottom: 80px;
            }

            .product-detail-modern-right {
                position: fixed;
                bottom: 0;
                width: 100%;
                z-index: 1050;
            }

            .product-detail-card {
                display: none !important;
                left: 0;
                right: 0;
                bottom: 0;
                width: 100vw;
                max-width: 100vw;
                border-radius: 16px 16px 0 0;
                box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.10);
                padding-bottom: env(safe-area-inset-bottom, 16px);
                background: #fff;
                margin: 0;
            }

            .bar-pesan {
                display: flex !important;
                justify-content: space-between;
                align-items: center;
                background: #fff;
                bottom: 0;
                height: 80px;
                left: 0;
                right: 0;
                padding: 16px;
            }

            .modal-bottom-sheet {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                margin: 0;
                max-width: 100vw;
                width: 100vw;
                z-index: 1060;
            }

            .product-detail-card-modal-content {
                border-radius: 16px 16px 0 0;
                box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.10);
                background: #fff;
                margin: 0;
                animation: slideUpSheet 0.3s cubic-bezier(.4, 0, .2, 1);
            }

            @keyframes slideUpSheet {
                from {
                    transform: translateY(100%);
                }

                to {
                    transform: translateY(0);
                }
            }

            @keyframes slideDownSheet {
                from {
                    transform: translateY(0);
                }

                to {
                    transform: translateY(100%);
                }
            }

        }

        .slide-down-animation {
            animation: slideDownSheet 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        /* Gallery modal visuals */
        .gallery-modal .modal-body img {
            max-height: 80vh;
            object-fit: contain;
            background: #000;
        }

        .gallery-modal .modal-content {
            background: rgba(0, 0, 0, 0.06);
        }

        /* Zoom lens styles */
        .img-zoom-lens {
            position: absolute;
            border: 2px solid rgba(255, 255, 255, 0.8);
            width: 120px;
            height: 120px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            background-repeat: no-repeat;
            background-size: cover;
            pointer-events: none;
            display: none;
            z-index: 2000;
        }

        /* For small screens disable hover zoom (use modal instead) */
        @media (max-width: 768px) {
            .img-zoom-lens {
                display: none !important;
            }

            .product-detail-main-image {
                cursor: zoom-in;
            }
        }
    </style>

    <div class="product-detail-modern-bg">
        <div class="product-detail-modern-container">
            @php
                $image1Path = Str::startsWith($product->image1, 'products/')
                    ? $product->image1
                    : 'products/' . $product->image1;

                $image2Path = Str::startsWith($product->image2, 'products/')
                    ? $product->image2
                    : 'products/' . $product->image2;

                $imageCertificatePath = Str::startsWith($product->image_certificate, 'certificates/')
                    ? $product->image_certificate
                    : 'certificates/' . $product->image_certificate;
            @endphp
            <div class="product-detail-modern-left">
                <div class="product-detail-main-image-wrapper">
                    @if ($product->image1)
                        <img src="{{ asset('storage/' . $image1Path) }}" alt="{{ $product->product_name }}"
                            class="product-detail-main-image" id="mainImage">
                    @elseif ($product->image2)
                        <img src="{{ asset('storage/' . $image2Path) }}" alt="{{ $product->product_name }}"
                            class="product-detail-main-image" id="mainImage">
                    @elseif ($product->image_certificate)
                        <img src="{{ asset('storage/' . $imageCertificatePath) }}" alt="{{ $product->product_name }}"
                            class="product-detail-main-image" id="mainImage">
                    @else
                        <div class="product-detail-default-image" id="mainImage">
                            <i class="fas fa-seedling"></i>
                        </div>
                    @endif
                </div>

                <!-- Image Zoom Modal (click to open larger view) -->
                <div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content bg-transparent border-0">
                            <div class="modal-body text-center p-0">
                                <img src="" id="zoomModalImage" alt="Zoomed Image"
                                    style="max-width:100%;max-height:90vh;object-fit:contain;" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="product-detail-thumbs">
                    @if ($product->image1)
                        <img src="{{ asset('storage/' . $image1Path) }}" alt="thumb1" class="selected"
                            onclick="changeImage(this, '{{ asset('storage/' . $image1Path) }}')">
                    @else
                        <div class="product-detail-thumb-default selected" onclick="changeImage(this, 'default')">
                            <i class="fas fa-seedling"></i>
                        </div>
                    @endif
                    @if ($product->image2)
                        <img src="{{ asset('storage/' . $image2Path) }}" alt="thumb2"
                            onclick="changeImage(this, '{{ asset('storage/' . $image2Path) }}')">
                    @endif
                    @if ($product->image_certificate)
                        <img src="{{ asset('storage/' . $imageCertificatePath) }}" alt="certificate"
                            onclick="changeImage(this, '{{ asset('storage/' . $imageCertificatePath) }}')">
                    @endif
                </div>
            </div>
            <div class="product-detail-modern-center">
                <div class="product-detail-title">{{ $product->product_name }}</div>
                <div class="product-detail-price">Rp{{ number_format($product->price_per_unit, 0, ',', '.') }} /
                    {{ $product->unit }}</div>
                @php
                    $availableStock = $product->stock - $product->minimum_stock;
                    $isOutOfStock = $availableStock <= 0;
                @endphp
                <div class="product-detail-stock {{ $isOutOfStock ? 'text-danger' : '' }}">
                    Stok: {{ $availableStock }} {{ $product->unit }}
                    @if ($isOutOfStock)
                        <span style="color: #d32f2f; font-weight: 500;">(Stok Habis)</span>
                    @endif
                </div>
                @php
                    /**
                     * Render a simple markdown-like description stored with literal "\\n" sequences,
                     * double-asterisk bold `**bold**`, and list items that start with `- `.
                     * This helper performs escaping, bold, lists and paragraphs.
                     */
                    function renderProductDescription($text)
                    {
                        if (is_null($text) || $text === '') {
                            return '';
                        }

                        // Convert literal backslash-n sequences to actual newlines
                        $text = str_replace('\\\\n', "\\n", $text);

                        // Normalize CRLF to LF
                        $text = str_replace("\\r\\n", "\\n", $text);

                        // Escape HTML to prevent XSS
                        $text = e($text);

                        // Convert bold syntax **text** to <strong>
                        $text = preg_replace('/\\*\\*(.+?)\\*\\*/s', '<strong>$1</strong>', $text);

                        $lines = explode("\\n", $text);
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
                                // blank line: close paragraph or list
                                if ($inList) {
                                    $html .= '</ul>';
                                    $inList = false;
                                }
                                $flushParagraph();
                                continue;
                            }

                            // list item
                            if (preg_match('/^[\-\\*]\s+(.*)$/', $line, $m)) {
                                // close paragraph buffer first
                                $flushParagraph();
                                if (!$inList) {
                                    $html .= '<ul>';
                                    $inList = true;
                                }
                                $item = $m[1];
                                $html .= '<li>' . $item . '</li>';
                                continue;
                            }

                            // Normal text goes into paragraph buffer
                            if ($inList) {
                                $html .= '</ul>';
                                $inList = false;
                            }
                            $paraBuffer[] = $line;
                        }

                        // flush any remaining open structures
                        if ($inList) {
                            $html .= '</ul>';
                        }
                        $flushParagraph();

                        return $html;
                    }
                @endphp

                <div class="product-detail-desc">{!! renderProductDescription($product->description) !!}</div>

                <!-- Informasi Sertifikat -->
                @if ($product->certificate_number)
                    <div class="certificate-info" style="margin-top: 20px;">
                        <h4 style="color: #16a34a; margin-bottom: 8px;">
                            <i class="fas fa-certificate" style="margin-right: 6px;"></i>
                            Informasi Sertifikat
                        </h4>
                        <div
                            style="background: #f8f9fa; padding: 16px; border-radius: 8px; border-left: 4px solid #16a34a;">
                            <div style="margin-bottom: 8px;">
                                <strong>Nomor Sertifikat:</strong> {{ $product->certificate_number }}
                            </div>
                            <div style="margin-bottom: 8px;">
                                <strong>Kelas:</strong> {{ $product->certificate_class }}
                            </div>
                            <div style="margin-bottom: 8px;">
                                <strong>Berlaku dari:</strong>
                                {{ $product->valid_from ? \Carbon\Carbon::parse($product->valid_from)->format('d M Y') : '-' }}
                            </div>
                            <div>
                                <strong>Berlaku sampai:</strong>
                                {{ $product->valid_until ? \Carbon\Carbon::parse($product->valid_until)->format('d M Y') : '-' }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="product-detail-modern-right">
                @if (session('error'))
                    <div
                        style="background:#ffebee;border:1px solid #f44336;border-radius:8px;padding:12px;margin-bottom:16px;color:#d32f2f;font-size:0.9rem;">
                        <i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>
                        {{ session('error') }}
                    </div>
                @endif
                @if (session('success'))
                    <div
                        style="background:#e8f5e9;border:1px solid #4caf50;border-radius:8px;padding:12px;margin-bottom:16px;color:#2e7d32;font-size:0.9rem;">
                        <i class="fas fa-check-circle" style="margin-right:6px;"></i>
                        {{ session('success') }}
                    </div>
                @endif
                <!-- Mobile -->
                <div class="bar-pesan">
                    <div>
                        <div>Stok: {{ $availableStock }} {{ $product->unit }}
                            @if ($isOutOfStock)
                                <span style="color: #d32f2f; font-weight: 500;">(Stok Habis)</span>
                            @endif
                        </div>
                        <div style="color:#d32f2f;font-size:0.9rem;">*Minimal Pembelian
                            {{ number_format($product->minimum_purchase, 0, ',', '') }}{{ $product->unit }}</div>
                    </div>
                    <button id="openDetailCardBtn" class="btn-green w-50">
                        + Keranjang
                    </button>
                </div>
                <!-- Modal Bottom Sheet -->
                <div class="modal fade" id="productDetailCardModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-bottom-sheet">
                        <div class="modal-content product-detail-card-modal-content">
                            <div class="modal-header" style="border-bottom:1px solid #eee;">
                                <h5 class="modal-title">Atur jumlah dan catatan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                @if ($isOutOfStock)
                                    <div style="text-align: center; padding: 20px; color: #d32f2f; font-weight: 500;">
                                        <i class="fas fa-exclamation-triangle"
                                            style="font-size: 24px; margin-bottom: 8px;"></i>
                                        <div>Produk tidak tersedia</div>
                                        <div style="font-size: 0.9rem; margin-top: 4px; color: #666;">Stok telah habis</div>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('cart.add', $product->product_id) }}"
                                        class="addToCartForm">
                                        @csrf
                                        <div class="product-detail-card-qty">
                                            <input type="text" class="qtyInput" name="quantity" value=""
                                                placeholder="0" min="{{ $product->minimum_purchase }}"
                                                max="{{ $availableStock }}"
                                                style="width:60px;text-align:center;background:#fff;">
                                            <span style="margin-left:8px;">{{ $product->unit }}</span>
                                            <span style="margin-left:8px;color:#d32f2f;font-size:0.9rem;">*Minimal
                                                Pembelian
                                                {{ number_format($product->minimum_purchase, 0, ',', '') }}{{ $product->unit }}</span>
                                        </div>
                                        <div class="stockWarning"
                                            style="color:#d32f2f;font-size:0.98rem;display:none;margin-bottom:8px;">
                                            <i class="fas fa-exclamation-triangle" style="margin-right:4px;"></i>
                                            Stok tidak mencukupi
                                        </div>
                                        <div class="minPurchaseWarning"
                                            style="color:#d32f2f;font-size:0.98rem;display:none;margin-bottom:8px;">
                                            <i class="fas fa-exclamation-triangle" style="margin-right:4px;"></i>
                                            Minimal pembelian tidak terpenuhi
                                        </div>
                                        <div class="product-detail-card-subtotal">
                                            Subtotal
                                            <span class="subtotal">Rp0</span>
                                        </div>
                                        <button class="btn-green w-100 addToCartBtn" type="submit" disabled>+
                                            Keranjang</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="product-detail-card">
                    <div class="product-detail-card-title">Atur jumlah dan catatan</div>
                    @if ($isOutOfStock)
                        <div style="text-align: center; padding: 20px; color: #d32f2f; font-weight: 500;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 8px;"></i>
                            <div>Produk tidak tersedia</div>
                            <div style="font-size: 0.9rem; margin-top: 4px; color: #666;">Stok telah habis</div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('cart.add', $product->product_id) }}"
                            class="addToCartForm">
                            @csrf
                            <div class="product-detail-card-qty">
                                <input type="text" class="qtyInput" name="quantity" value="" placeholder="0"
                                    min="{{ $product->minimum_purchase }}" max="{{ $availableStock }}"
                                    style="width:60px;text-align:center;background:#fff;">
                                <span style="margin-left:8px;">{{ $product->unit }}</span>
                                <span style="margin-left:8px;color:#d32f2f;font-size:0.9rem;">*Minimal Pembelian
                                    {{ number_format($product->minimum_purchase, 0, ',', '') }}{{ $product->unit }}</span>
                            </div>
                            <div class="stockWarning"
                                style="color:#d32f2f;font-size:0.98rem;display:none;margin-bottom:8px;">
                                <i class="fas fa-exclamation-triangle" style="margin-right:4px;"></i>
                                Stok tidak mencukupi
                            </div>
                            <div class="minPurchaseWarning"
                                style="color:#d32f2f;font-size:0.98rem;display:none;margin-bottom:8px;">
                                <i class="fas fa-exclamation-triangle" style="margin-right:4px;"></i>
                                Minimal pembelian tidak terpenuhi
                            </div>
                            <div class="product-detail-card-subtotal">
                                Subtotal
                                <span class="subtotal">Rp0</span>
                            </div>
                            <button class="btn-green w-100 addToCartBtn" type="submit" disabled>+ Keranjang</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Gallery Modal -->
    <div class="modal fade gallery-modal" id="imageGalleryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"
                    aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div id="imageGalleryCarousel" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-inner">
                            @if ($product->image1)
                                <div class="carousel-item active">
                                    <img src="{{ asset('storage/' . $image1Path) }}" class="d-block w-100"
                                        alt="{{ $product->product_name }}">
                                </div>
                            @endif
                            @if ($product->image2)
                                <div class="carousel-item">
                                    <img src="{{ asset('storage/' . $image2Path) }}" class="d-block w-100"
                                        alt="{{ $product->product_name }}">
                                </div>
                            @endif
                            @if ($product->image_certificate)
                                <div class="carousel-item">
                                    <img src="{{ asset('storage/' . $imageCertificatePath) }}" class="d-block w-100"
                                        alt="{{ $product->product_name }}">
                                </div>
                            @endif
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#imageGalleryCarousel"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#imageGalleryCarousel"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function changeImage(thumbElement, imageSrc) {
            const mainImageContainer = document.getElementById('mainImage');

            if (imageSrc === 'default') {
                mainImageContainer.src =
                    '{{ asset('path/to/default/image.jpg') }}'; // Ganti dengan path gambar default jika ada
                mainImageContainer.className = 'product-detail-default-image';
            } else {
                mainImageContainer.src = imageSrc;
                mainImageContainer.className = 'product-detail-main-image';
            }

            document.querySelectorAll('.product-detail-thumbs img, .product-detail-thumbs .product-detail-thumb-default')
                .forEach(thumb => {
                    thumb.classList.remove('selected');
                });
            thumbElement.classList.add('selected');
            // Reinitialize zoom because the main image src changed
            setTimeout(reinitZoomAfterImageChange, 50);
        }

        // --- Logika untuk Form Tambah ke Keranjang ---
        @if (!$isOutOfStock)
            document.addEventListener('DOMContentLoaded', function() {
                const forms = document.querySelectorAll('.addToCartForm');
                forms.forEach(button => {
                    button.addEventListener('click', function(e) {
                        const isUserLoggedIn = @json(Auth::check());
                        if (!isUserLoggedIn) {
                            e.preventDefault();
                            window.location.href = '{{ route('login') }}';
                        }
                    });
                });
                const unitProduk = @json($product->unit);
                const minimumPurchase = {{ $product->minimum_purchase }};
                const availableStock = {{ $availableStock }};

                forms.forEach(form => {
                    const qtyInput = form.querySelector('.qtyInput');
                    const subtotalSpan = form.querySelector('.subtotal');
                    const addToCartBtn = form.querySelector('.addToCartBtn');
                    const stockWarning = form.querySelector('.stockWarning');
                    const minPurchaseWarning = form.querySelector('.minPurchaseWarning');

                    const updateSubtotal = () => {
                        let qty = parseFloat(qtyInput.value.replace(',', '.')) || 0;
                        let harga = {{ $product->price_per_unit }};

                        // Reset warnings
                        if (stockWarning) stockWarning.style.display = 'none';
                        if (minPurchaseWarning) minPurchaseWarning.style.display = 'none';

                        if (qty === 0 || qtyInput.value.trim() === '') {
                            subtotalSpan.innerText = 'Rp0';
                            if (addToCartBtn) {
                                addToCartBtn.disabled = true;
                                addToCartBtn.style.opacity = '0.6';
                                addToCartBtn.style.cursor = 'not-allowed';
                                addToCartBtn.style.background = '#ccc';
                                addToCartBtn.style.color = '#666';
                            }
                            return;
                        }

                        let isValid = true;

                        if (qty < minimumPurchase) {
                            if (minPurchaseWarning) minPurchaseWarning.style.display = 'block';
                            isValid = false;
                        }

                        if (qty > availableStock) {
                            if (stockWarning) stockWarning.style.display = 'block';
                            isValid = false;
                        }

                        subtotalSpan.innerText = 'Rp' + (harga * qty).toLocaleString('id-ID');

                        if (addToCartBtn) {
                            addToCartBtn.disabled = !isValid;
                            addToCartBtn.style.opacity = isValid ? '1' : '0.6';
                            addToCartBtn.style.cursor = isValid ? 'pointer' : 'not-allowed';
                            addToCartBtn.style.background = isValid ? '#388e3c' : '#ccc';
                            addToCartBtn.style.color = isValid ? '#fff' : '#666';
                        }
                    };

                    if (qtyInput) {
                        qtyInput.addEventListener('input', updateSubtotal);
                        qtyInput.addEventListener('blur', updateSubtotal);
                    }

                    if (form) {
                        form.addEventListener('submit', function(e) {
                            let qty = parseFloat(qtyInput.value.replace(',', '.')) || 0;

                            if (qty === 0 || qtyInput.value.trim() === '') {
                                e.preventDefault();
                                alert('Silakan masukkan jumlah yang ingin dibeli');
                                return false;
                            }

                            if (qty < minimumPurchase) {
                                e.preventDefault();
                                alert('Minimal pembelian: ' + minimumPurchase + ' ' + unitProduk);
                                return false;
                            }

                            if (qty > availableStock) {
                                e.preventDefault();
                                alert('Stok tidak mencukupi. Maksimal: ' + availableStock + ' ' +
                                    unitProduk);
                                return false;
                            }
                        });
                    }

                    // Panggil saat halaman dimuat
                    updateSubtotal();
                });
            });
        @endif

        // --- Logika untuk Modal ---
        const openDetailCardBtn = document.getElementById('openDetailCardBtn');
        const productDetailCardModal = document.getElementById('productDetailCardModal');

        if (openDetailCardBtn && productDetailCardModal) {
            openDetailCardBtn.addEventListener('click', function() {
                const isUserLoggedIn = @json(Auth::check());
                if (isUserLoggedIn) {
                    const modal = new bootstrap.Modal(productDetailCardModal);
                    modal.show();
                } else {
                    window.location.href = '{{ route('login') }}';
                }
            });

            productDetailCardModal.addEventListener('hide.bs.modal', function(event) {
                const modalContent = productDetailCardModal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.classList.add('slide-down-animation');
                }
            });

            productDetailCardModal.addEventListener('hidden.bs.modal', function(event) {
                const modalContent = productDetailCardModal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.classList.remove('slide-down-animation');
                }
            });
        }

        // --- Image zoom logic ---
        // Initialize zoom lens element
        const zoomLens = document.createElement('div');
        zoomLens.className = 'img-zoom-lens';
        document.body.appendChild(zoomLens);

        function isTouchDevice() {
            return (('ontouchstart' in window) || navigator.maxTouchPoints > 0);
        }

        function initImageZoom() {
            const mainImage = document.getElementById('mainImage');
            if (!mainImage) return;

            // Only enable hover magnifier on non-touch and wide screens
            if (isTouchDevice() || window.innerWidth <= 768) {
                // Enable click to open modal only
                if (mainImage instanceof HTMLImageElement) {
                    mainImage.style.cursor = 'zoom-in';
                    mainImage.addEventListener('click', openZoomModal);
                }
                return;
            }

            if (mainImage instanceof HTMLImageElement) {
                mainImage.addEventListener('mousemove', moveLens);
                mainImage.addEventListener('mouseenter', showLens);
                mainImage.addEventListener('mouseleave', hideLens);
                mainImage.addEventListener('click', openZoomModal);
            }
        }

        function showLens(e) {
            const img = e.currentTarget;
            if (!(img instanceof HTMLImageElement)) return;
            const rect = img.getBoundingClientRect();
            zoomLens.style.display = 'block';
            zoomLens.style.backgroundImage = `url('${img.src}')`;
            // Increase background size to create zoom effect
            zoomLens.style.backgroundSize = `${rect.width * 2.2}px ${rect.height * 2.2}px`;
        }

        function hideLens() {
            zoomLens.style.display = 'none';
        }

        function moveLens(e) {
            const img = e.currentTarget;
            if (!(img instanceof HTMLImageElement)) return;
            const rect = img.getBoundingClientRect();
            const lensWidth = zoomLens.offsetWidth || 120;
            const lensHeight = zoomLens.offsetHeight || 120;

            // Calculate cursor position relative to image
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Position lens centered on cursor
            let left = e.clientX - lensWidth / 2;
            let top = e.clientY - lensHeight / 2;

            // Keep lens inside viewport boundaries
            const maxLeft = rect.right - lensWidth / 2;
            const minLeft = rect.left + lensWidth / 2;
            const maxTop = rect.bottom - lensHeight / 2;
            const minTop = rect.top + lensHeight / 2;

            if (e.clientX > maxLeft) left = maxLeft - lensWidth / 2;
            if (e.clientX < minLeft) left = minLeft - lensWidth / 2;
            if (e.clientY > maxTop) top = maxTop - lensHeight / 2;
            if (e.clientY < minTop) top = minTop - lensHeight / 2;

            zoomLens.style.left = `${left}px`;
            zoomLens.style.top = `${top}px`;

            // Calculate background position
            const bgX = -((x * 2.2) - lensWidth / 2);
            const bgY = -((y * 2.2) - lensHeight / 2);
            zoomLens.style.backgroundPosition = `${bgX}px ${bgY}px`;
        }

        function openZoomModal(e) {
            const mainImage = document.getElementById('mainImage');
            const modalImg = document.getElementById('zoomModalImage');
            if (!mainImage || !(mainImage instanceof HTMLImageElement)) return;
            modalImg.src = mainImage.src;
            const modal = new bootstrap.Modal(document.getElementById('imageZoomModal'));
            modal.show();
        }

        // Re-init zoom on image change
        function reinitZoomAfterImageChange() {
            const mainImage = document.getElementById('mainImage');
            if (!mainImage) return;
            // Remove previous listeners to avoid duplicates
            try {
                mainImage.removeEventListener('mousemove', moveLens);
                mainImage.removeEventListener('mouseenter', showLens);
                mainImage.removeEventListener('mouseleave', hideLens);
                mainImage.removeEventListener('click', openZoomModal);
            } catch (e) {
                // ignore
            }
            initImageZoom();
        }

        // Initialize on DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            initImageZoom();
            // Also re-init on window resize to toggle behavior between mobile/desktop
            window.addEventListener('resize', function() {
                reinitZoomAfterImageChange();
            });
        });
    </script>
@endsection

@section('after_content')
    @include('customer.partials.mitra_footer')
@endsection
