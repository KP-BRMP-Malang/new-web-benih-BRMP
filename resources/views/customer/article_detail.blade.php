@extends('layouts.app')

@section('content')
    <style>
        .product-card {
        transition: transform 0.2s;
        cursor: pointer;
        position: relative;
        }
        .product-card:hover {
            transform: scale(1.05);
            z-index: 2;
            box-shadow: 0 4px 16px #0002;
        }
        .product-card {
        border-radius: 24px;
        background: #fff;
        box-shadow: 0 2px 8px #0001;
        width: 260px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 0;
        }
        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 24px #0002;
            z-index: 2;
        }
        .product-card img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-top-left-radius: 24px;
            border-top-right-radius: 24px;
        }
        .product-card .product-info {
            padding: 18px 16px 12px 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .product-card .product-price {
            font-weight: 700;
            color: #38b449;
            font-size: 1.2rem;
            margin-bottom: 4px;
        }
        .product-card .product-name {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 6px;
            color: #222;
        }
        .product-card .product-desc {
            font-size: 14px;
            color: #888;
            margin-bottom: 8px;
        }
        .product-card .product-stock {
            font-size: 14px;
            color: #38b449;
            margin-top: 8px;
            font-weight: 500;
        }
        .product-card .product-new {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #6fdc6f;
            color: #fff;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 8px;
            font-size: 13px;
        }
    </style>

    <div class="container" style="margin-top:32px; margin-bottom:40px;">
        <h2>{{ $article->headline }}</h2>
        <p style="color:#888;">{{ \Carbon\Carbon::parse($article->created_at)->format('d F Y') }}</p>
        @if ($article->image2)
            <img src="{{ asset('storage/articles/' . $article->image2) }}" alt="{{ $article->headline }} - gambar 2"
                style="width:100%;height:420px;object-fit:cover;border-radius:16px;display:block;margin-bottom:24px;">
        @endif
        <div style="margin-top:24px;padding-inline:8px;">{!! $article->body !!}</div>

        {{-- Section Benih Berkualitas --}}
        <div class="mt-5" style="width:100%;">
            <div style="background:linear-gradient(90deg,#38b449 60%,#6fdc6f 100%);border-radius:24px 24px 240px 24px;display:flex;align-items:center;padding:32px 24px;gap:32px;">
                <div style="display:flex;gap:16px;">
                    <div style="display:flex;gap:24px;">
                        @foreach ($randomProducts as $product)
                            <a href="{{ route('product.detail', ['id' => $product->product_id]) }}" style="text-decoration:none;">
                                <div class="product-card">
                                    <img src="{{ asset('storage/products/' . $product->image1) }}" alt="{{ $product->product_name }}">
                                    <div class="product-info">
                                        <div class="product-price">Rp{{ number_format($product->price_per_unit,0,',','.') }}</div>
                                        <div class="product-name">{{ $product->product_name }}</div>
                                        <div class="product-desc">
                                            {{ \Illuminate\Support\Str::limit($product->description ?? '', 60, '...') }}
                                        </div>
                                        <div class="product-stock">Stok: {{ $product->stock }} {{ $product->unit }}</div>
                                    </div>
                                    @if($product->is_new)
                                        <span class="product-new">BARU</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                <div style="flex:1;">
                    <div style="color:#fff;font-size:3.2rem;font-weight:900;line-height:1.1;margin-top:-30px;">
                        Dapatkan <span style="color:#fff;">benih berkualitas</span><br>di sini.
                    </div>
                    <div style="margin-top:32px;">
                        <a href="/" style="display:flex;align-items:center;gap:12px;background:#ffe44d;border:none;border-radius:16px;padding:0 24px;height:48px;box-shadow:0 2px 8px #0001;cursor:pointer;text-decoration:none;width: 250px;;">
                            <span style="display:flex;align-items:center;justify-content:center;background:#176d3b;border-radius:50%;width:36px;height:36px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="12" fill="none"/>
                                    <path d="M15 7L9 12L15 17" stroke="#ffe44d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span style="color:#176d3b;font-size:2rem;font-weight:700;line-height:1;">Beli di sini</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection