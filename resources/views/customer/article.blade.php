@extends('layouts.app')

@section('content')
    <style>
        .article-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 32px;
        }

        .article-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: transform 0.18s cubic-bezier(.4, 0, .2, 1), box-shadow 0.18s cubic-bezier(.4, 0, .2, 1);
            cursor: pointer;
            border: 1px solid #f3f3f3;
        }

        .article-card:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.10);
            border-color: #d1e7dd;
        }

        .article-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 24px 24px 0 0;
            background: #eee;
        }

        .article-content {
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .article-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #222;
        }

        .article-date {
            color: #888;
            font-size: 1rem;
            margin-bottom: 0;
        }

        .article-header {
            margin-top: 32px;
            margin-bottom: 24px;
        }
    </style>
    <div class="container" style="margin-bottom: 40px;">
        <div class="article-header">
            <h2>Artikel Terbaru</h2>
        </div>
        <div class="article-grid">
            @forelse($articles as $article)
                <a href="{{ route('articles.show', $article->id) }}" style="text-decoration:none;color:inherit;">
                    <div class="article-card">
                        @if ($article->image)
                            <img src="{{ asset('storage/articles/' . $article->image) }}" alt="{{ $article->headline }}">
                        @else
                            <div class="article-image"
                                style="display:flex;align-items:center;justify-content:center;color:#aaa;font-size:2rem;">
                                Tidak ada gambar
                            </div>
                        @endif
                        <div class="article-content">
                            <h2 class="article-title" style="margin-bottom:20px;">{{ $article->headline }}</h2>
                            <p class="article-date" style="margin-top:0;">
                                {{ \Carbon\Carbon::parse($article->created_at)->format('d F Y') }}
                            </p>
                        </div>
                    </div>
                </a>
            @empty
                <div style="grid-column:1/-1;text-align:center;color:#888;">Belum ada artikel.</div>
            @endforelse
        </div>
    </div>
@endsection

@section('after_content')
    @include('customer.partials.mitra_footer')
@endsection
