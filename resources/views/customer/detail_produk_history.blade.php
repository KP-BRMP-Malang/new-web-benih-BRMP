@extends('layouts.app')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

@section('content')
    <div class="product-detail-modern-bg">
        <div class="product-detail-modern-container">
            <div class="product-detail-modern-left">
                @php
                    $image1Path = Str::startsWith($productHistory->image1, 'products/')
                        ? $productHistory->image1
                        : 'products/' . $productHistory->image1;

                    $image2Path = Str::startsWith($productHistory->image2, 'products/')
                        ? $productHistory->image2
                        : 'products/' . $productHistory->image2;

                    $imageCertificatePath = Str::startsWith($productHistory->image_certificate, 'certificates/')
                        ? $productHistory->image_certificate
                        : 'certificates/' . $productHistory->image_certificate;
                @endphp
                <div class="product-detail-main-image-wrapper">
                    @if ($productHistory->image1)
                        <img src="{{ asset('storage/' . $image1Path) }}" alt="{{ $productHistory->product_name }}"
                            class="product-detail-main-image" id="mainImage">
                    @else
                        <div class="product-detail-default-image" id="mainImage">
                            <i class="fas fa-seedling"></i>
                        </div>
                    @endif
                </div>
                <div class="product-detail-thumbs">
                    @if ($productHistory->image1)
                        <img src="{{ asset('storage/' . $image1Path) }}" alt="thumb1" class="selected"
                            onclick="changeImage(this, '{{ asset('storage/' . $image1Path) }}')">
                    @else
                        <div class="product-detail-thumb-default selected" onclick="changeImage(this, 'default')">
                            <i class="fas fa-seedling"></i>
                        </div>
                    @endif
                    @if ($productHistory->image2)
                        <img src="{{ asset('storage/' . $image2Path) }}" alt="thumb2"
                            onclick="changeImage(this, '{{ asset('storage/' . $image2Path) }}')">
                    @endif
                    @if ($productHistory->image_certificate)
                        <img src="{{ asset('storage/' . $imageCertificatePath) }}" alt="certificate"
                            onclick="changeImage(this, '{{ asset('storage/' . $imageCertificatePath) }}')">
                    @endif
                </div>
            </div>
            <div class="product-detail-modern-center">
                <div class="product-detail-title">{{ $productHistory->product_name }}</div>
                <div class="product-detail-price">Rp{{ number_format($productHistory->price_per_unit, 0, ',', '.') }} /
                    {{ $productHistory->unit }}</div>
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
                <div class="product-detail-desc">{!! renderProductDescription($productHistory->description) !!}</div>

                <!-- Informasi Sertifikat -->
                @if ($productHistory->certificate_number)
                    <div class="certificate-info">
                        <h4 style="color: #16a34a; margin-bottom: 8px;">
                            <i class="fas fa-certificate" style="margin-right: 6px;"></i>
                            Informasi Sertifikat
                        </h4>
                        <div
                            style="background: #f8f9fa; padding: 16px; border-radius: 8px; border-left: 4px solid #16a34a;">
                            <div style="margin-bottom: 8px;">
                                <strong>Nomor Sertifikat:</strong> {{ $productHistory->certificate_number }}
                            </div>
                            <div style="margin-bottom: 8px;">
                                <strong>Kelas:</strong> {{ $productHistory->certificate_class }}
                            </div>
                            <div style="margin-bottom: 8px;">
                                <strong>Berlaku dari:</strong>
                                {{ $productHistory->valid_from ? $productHistory->valid_from->format('d M Y') : '-' }}
                            </div>
                            <div>
                                <strong>Berlaku sampai:</strong>
                                {{ $productHistory->valid_until ? $productHistory->valid_until->format('d M Y') : '-' }}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Informasi Record -->
                <div class="record-info" style="margin-top: 20px;">
                    <div
                        style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 12px; color: #856404;">
                        <i class="fas fa-history" style="margin-right: 6px;"></i>
                        <strong>Foto produk dari transaksi sebelumnya</strong><br>
                        <small>Direkam pada:
                            {{ $productHistory->recorded_at ? $productHistory->recorded_at->format('d M Y H:i') : '-' }}</small>
                    </div>
                </div>
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
                <div class="product-detail-card">
                    <div class="product-detail-card-title">Status Produk Saat Ini</div>

                    @if ($isProductAvailable)
                        <div style="text-align: center; padding: 20px; color: #16a34a; font-weight: 500;">
                            <i class="fas fa-check-circle" style="font-size: 24px; margin-bottom: 8px; color: #16a34a;"></i>
                            <div>Produk masih tersedia</div>
                            <div style="font-size: 0.9rem; margin-top: 4px; color: #666;">
                                Stok: {{ $currentProduct->stock - $currentProduct->minimum_stock }}
                                {{ $currentProduct->unit }}
                            </div>
                            <button onclick="location.href='{{ route('produk.detail', $currentProduct->product_id) }}'"
                                class="btn-green w-100 mt-3">
                                Lihat Detail Produk Terkini
                            </button>
                        </div>
                    @else
                        <div style="text-align: center; padding: 20px; color: #d32f2f; font-weight: 500;">
                            <i class="fas fa-exclamation-triangle"
                                style="font-size: 24px; margin-bottom: 8px; color: #d32f2f;"></i>
                            <div>Produk tidak tersedia</div>
                            <div style="font-size: 0.9rem; margin-top: 4px; color: #666;">
                                @if ($currentProduct)
                                    Stok: {{ $currentProduct->stock - $currentProduct->minimum_stock }}
                                    {{ $currentProduct->unit }}
                                @else
                                    Produk telah dihapus
                                @endif
                            </div>
                            @if ($currentProduct)
                                <button onclick="location.href='{{ route('produk.detail', $currentProduct->product_id) }}'"
                                    class="btn-outline w-100 mt-3">
                                    Lihat Detail Produk Terkini
                                </button>
                            @endif
                        </div>
                    @endif

                    <!-- Tombol Kembali -->
                    <div>
                        <button onclick="location.href='{{ url()->previous() }}'" class="btn-outline w-100">
                            <i class="fas fa-arrow-left" style="margin-right: 6px;"></i>
                            Kembali
                        </button>
                    </div>
                </div>
            </div>
        </div>
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

    <!-- Gallery Modal -->
    <div class="modal fade gallery-modal" id="imageGalleryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"
                    aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div id="imageGalleryCarousel" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-inner">
                            @if ($productHistory->image1)
                                <div class="carousel-item active">
                                    <img src="{{ asset('storage/' . $image1Path) }}" class="d-block w-100"
                                        alt="{{ $productHistory->product_name }}">
                                </div>
                            @endif
                            @if ($productHistory->image2)
                                <div class="carousel-item">
                                    <img src="{{ asset('storage/' . $image2Path) }}" class="d-block w-100"
                                        alt="{{ $productHistory->product_name }}">
                                </div>
                            @endif
                            @if ($productHistory->image_certificate)
                                <div class="carousel-item">
                                    <img src="{{ asset('storage/' . $imageCertificatePath) }}" class="d-block w-100"
                                        alt="{{ $productHistory->product_name }}">
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
@endsection

@push('styles')
    <style>
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
            flex: 1.1;
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
            cursor: zoom-in;
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
            cursor: zoom-in;
        }

        .product-detail-default-image i {
            font-size: 80px;
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

        .product-detail-info-list {
            margin-bottom: 18px;
        }

        .product-detail-info-list .label {
            color: #888;
            font-weight: 500;
            margin-right: 8px;
        }

        .product-detail-info-list .value {
            color: #222;
            font-weight: 500;
        }

        .product-detail-desc {
            margin-top: 18px;
            color: #444;
            font-size: 1.1rem;
        }

        .product-detail-modern-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
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

        .btn-outline {
            border-radius: 8px;
            border: 2px solid #3C9A40;
            background: #fff;
            color: #3C9A40;
            font-size: 16px;
            font-weight: 700;
            padding: 10px 0;
            white-space: nowrap;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            transform: translateY(-2px);
        }

        .certificate-info {
            margin-top: 20px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .record-info {
            margin-top: 20px;
        }

        @media (max-width: 1100px) {
            .product-detail-modern-container {
                flex-direction: column;
                padding: 18px 6px;
                gap: 18px;
            }

            .product-detail-modern-right {
                align-items: stretch;
            }

            .product-detail-card {
                width: 100%;
                margin-top: 18px;
            }
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
    </style>
@endpush

@push('scripts')
    <script>
        function changeImage(thumbElement, imageSrc) {
            const mainImageWrapper = document.querySelector('.product-detail-main-image-wrapper');
            let mainImageContainer = document.getElementById('mainImage');

            if (imageSrc === 'default') {
                // If current mainImage is an <img>, replace it with a default <div>
                if (mainImageContainer.tagName === 'IMG') {
                    const defaultDiv = document.createElement('div');
                    defaultDiv.className = 'product-detail-default-image';
                    defaultDiv.id = 'mainImage';
                    defaultDiv.innerHTML = '<i class="fas fa-seedling"></i>';
                    mainImageContainer.replaceWith(defaultDiv);
                }
            } else {
                // If current mainImage is a <div>, replace it with an <img>
                if (mainImageContainer.tagName === 'DIV' && mainImageContainer.classList.contains('product-detail-default-image')) {
                     const newImg = document.createElement('img');
                     newImg.src = imageSrc;
                     newImg.className = 'product-detail-main-image';
                     newImg.id = 'mainImage';
                     newImg.alt = '{{ $productHistory->product_name }}';
                     mainImageContainer.replaceWith(newImg);
                } else {
                     // If it's already an <img>, just update its src
                     mainImageContainer.src = imageSrc;
                }
            }

            // Update selected thumbnail
            document.querySelectorAll('.product-detail-thumbs img, .product-detail-thumbs .product-detail-thumb-default')
                .forEach(thumb => {
                    thumb.classList.remove('selected');
                });
            thumbElement.classList.add('selected');
            
             // Reinitialize zoom because the main image src changed
            setTimeout(reinitZoomAfterImageChange, 50);
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

            // Only enable hover magnifier on non-touch and wide screen
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

            // Use pageX/pageY to account for scrolling
            // e.pageX is relative to the document
            
            // However, we want the lens to follow the cursor.
            // If zoomLens is absolute to body, we set top/left relative to document.
            // But we need to calculate where the 'zoom' should be looking at (relative to image).
            
            // Curser position relative to image viewport
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Prevent lens from going out of image bounds
            // But for the lens POSITION (the box), we center on cursor.
            let left = e.pageX - lensWidth / 2;
            let top = e.pageY - lensHeight / 2;
            
            // Constrain lens position within the image boundaries (in document coords)
            // Image boundaries in doc coords:
            const imgLeft = rect.left + window.scrollX;
            const imgRight = rect.right + window.scrollX;
            const imgTop = rect.top + window.scrollY;
            const imgBottom = rect.bottom + window.scrollY;
            
            const maxLeft = imgRight - lensWidth / 2;
            const minLeft = imgLeft + lensWidth / 2;
            const maxTop = imgBottom - lensHeight / 2;
            const minTop = imgTop + lensHeight / 2;

            if (e.pageX > maxLeft) left = maxLeft - lensWidth / 2;
            if (e.pageX < minLeft) left = minLeft - lensWidth / 2;
            if (e.pageY > maxTop) top = maxTop - lensHeight / 2;
            if (e.pageY < minTop) top = minTop - lensHeight / 2;

            zoomLens.style.left = `${left}px`;
            zoomLens.style.top = `${top}px`;

            // Calculate background position
            // The background position is negative to shift the image
            // We want to show the part of the image under the cursor (x,y relative to image)
            // x, y calculated from clientX/rect are correct for offset within image
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

       // Image gallery modal logic (for carousel, usage if needed)
        (function() {
            const galleryImages = [
                @if ($productHistory->image1)
                    '{{ asset('storage/' . $image1Path) }}',
                @endif
                @if ($productHistory->image2)
                    '{{ asset('storage/' . $image2Path) }}',
                @endif
                @if ($productHistory->image_certificate)
                    '{{ asset('storage/' . $imageCertificatePath) }}',
                @endif
            ];

            if (galleryImages.length === 0) return;

            const modalEl = document.getElementById('imageGalleryModal');
            const carouselEl = document.getElementById('imageGalleryCarousel');
            let carouselInstance;

            function ensureCarousel() {
                if (!carouselInstance) {
                    carouselInstance = new bootstrap.Carousel(carouselEl, {
                        interval: false,
                        ride: false,
                        keyboard: true
                    });
                }
            }
            
            // We expose this function but don't bind it to mainImage click to avoid conflict
            // The user wanted the Zoom Modal (single image) on click, not carousel.
            // If we want carousel functionality from thumbnails, we can add it there, 
            // but currently thumbnails change the main image.
            
             // Initialize on DOMContentLoaded
            document.addEventListener('DOMContentLoaded', function() {
                initImageZoom();
                // Also re-init on window resize to toggle behavior between mobile/desktop
                window.addEventListener('resize', function() {
                    reinitZoomAfterImageChange();
                });
            });
        })();
    </script>
@endpush

@section('after_content')
    @include('customer.partials.mitra_footer')
@endsection
