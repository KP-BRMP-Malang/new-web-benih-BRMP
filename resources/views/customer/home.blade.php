@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <style>
        .bg-image {
            background-image: url('images/image 15.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: top center;
            border-radius: 0 0 20px 20px;
        }


        .container-hero {
            margin-top: 0px;
            margin-bottom: 60px;
            margin-inline: auto;
            padding-top: 65px;
            padding-bottom: 40px;
            padding-inline: 16px;
            max-width: 1200px;
            z-index: 1;
        }

        .hero-banner {
            border-radius: 16px;
            background: rgba(146, 146, 146, 0.2);
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(5px);
            display: flex;
            padding: 40px;
            flex-direction: column;
            align-items: flex-start;
            position: relative;
            margin-bottom: 60px;
        }

        .hero-title {
            font-size: 48px;
            font-weight: 900;
            margin-bottom: 10px;
        }

        .hero-subtitle {
            font-size: 14px;
            margin-bottom: 43px;
            color: #fff;
            font-weight: 500;
        }

        .hero-btn {
            border-radius: 50px;
            border: 2px solid #D7D7D7;
            background: #FFF;
            box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.25);
            color: #929292;
            font-weight: 700;
            font-size: 16px;
            padding: 4px 16px;
            transition: all 0.3s ease;
        }

        .hero-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }

        .mitra-section {
            color: #fff;
        }

        .mitra-text {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px 20px;
        }

        .mitra-logos-carousel {
            overflow: hidden;
            width: 100%;
            padding: 10px 0;
            position: relative;
            white-space: nowrap;
        }

        .mitra-logos-carousel:hover .mitra-logos-track {
            animation-play-state: paused;
        }

        .mitra-logos-track {
            display: inline-flex;
            animation: 20s slide infinite linear;
        }

        @keyframes slide {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        .mitra-logo-box {
            width: 120px;
            height: 60px;
            background: #e0e0e0;
            border-radius: 10px;
            margin: 0 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: box-shadow 0.25s, transform 0.25s;
        }

        .mitra-logo-box:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18), 0 3px 12px rgba(0, 0, 0, 0.12);
            transform: scale(1.07);
        }

        .mitra-logo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 1;
            padding: 0;
            margin: 0;
            display: block;
            transition: transform 0.25s;
        }


        .container {
            max-width: 1200px;
            margin: 32px auto;
            padding: 0 16px;
            position: relative;
            z-index: 1;
        }

        .section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            padding: 24px 18px;
            margin-bottom: 32px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
        }

        .hero-banner-margin {
            margin: 60px 0 32px 0;
        }

        .btn-LebihBanyak {
            border-radius: 50px;
            border: 2px solid #054D33;
            background: #085C3D;
            box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.25);
            padding: 4px 16px;
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-LebihBanyak:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }

        @media (max-width: 1023px) {
            .mitra-section {
                display: none;
            }

            .container-hero {
                margin-top: 0px;
                padding-top: 35px;
                padding-bottom: 35px;
                margin-bottom: 35px;
            }

            .hero-banner {
                margin-bottom: 0px;
            }

            .hero-title {
                font-size: 32px;
            }

            .hero-subtitle {
                margin-bottom: 30px;
            }

            .section {
                padding: 0 0;
                background: none;
                box-shadow: none;
            }

            .cs-bubble {
                bottom: 12px;
                right: 12px;
            }
        }
    </style>

    <div class="bg-image">
        <div class="container-hero">
            <div class="hero-banner">
                <h1 class="hero-title">
                    <div>PRODUK BARU
                    </div>
                    <div>
                        KINI
                        <span style="color:#A1FF00">
                            TELAH HADIR.
                        </span>
                    </div>
                </h1>
                <p class="hero-subtitle">Temukan koleksi produk terbaru dengan penawaran menarik hanya di Benih BRMP.</p>
                <button class="hero-btn" onclick="window.location.href='{{ route('produk.baru') }}'">Lihat Produk
                    Baru</button>
            </div>

            <div class="mitra-section">
                <div class="mitra-text">
                    <h2 style="color: #fff">Mitra Kami</h2>
                    <div>Dalam melaksanakan tugas dan fungsinya, BRMP berkolaborasi dengan mitra dari dalam negeri dan mitra
                        internasional.</div>
                </div>
                <div class="mitra-logos-carousel">
                    <div class="mitra-logos-track">
                        <a href="https://www.irri.org/" target="_blank">
                            <div class="mitra-logo-box"><img src="{{ asset('images/IRRI.webp') }}" alt="IRRI"></div>
                        </a>
                        <a href="https://www.fao.org/fao-who-codexalimentarius/en" target="_blank">
                            <div class="mitra-logo-box"><img src="{{ asset('images/Codex.webp') }}" alt="Codex"></div>
                        </a>
                        <a href="https://www.fao.org/home/en" target="_blank">
                            <div class="mitra-logo-box"><img src="{{ asset('images/FAO.webp') }}" alt="FAO"></div>
                        </a>
                        <a href="https://www.afaci.org/" target="_blank">
                            <div class="mitra-logo-box"><img src="{{ asset('images/AFACI.webp') }}" alt="AFACI"></div>
                        </a>
                        <a href="https://www.jircas.go.jp/en" target="_blank">
                            <div class="mitra-logo-box"><img src="{{ asset('images/JIRCAS.webp') }}" alt="JIRCAS"></div>
                        </a>
                        <a href="https://kan.or.id/" target="_blank">
                            <div class="mitra-logo-box"><img src="{{ asset('images/KAN.webp') }}" alt="KAN"></div>
                        </a>
                        <a href="https://www.bsn.go.id/" target="_blank">
                            <div class="mitra-logo-box"><img src="{{ asset('images/BSN.webp') }}" alt="BSN"></div>
                        </a>
                        <a href="https://www.ekon.go.id/" target="_blank">
                            <div class="mitra-logo-box"><img src="{{ asset('images/Pancasila.webp') }}" alt="Pancasila">
                            </div>
                        </a>
                        <a href="https://www.pertanian.go.id/" target="_blank">
                            <div class="mitra-logo-box"><img src="{{ asset('images/BRMP.webp') }}" alt="BRMP"></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="section" style="margin-bottom:24px;">
            <h2 style="font-weight:bold; margin-bottom:18px;">
                @if (isset($q) && $q)
                    Hasil pencarian untuk: <span style="color:#388E3C">"{{ $q }}"</span>
                @else
                    Produk Pilihan
                @endif
            </h2>
            @if ($products->count() > 0)
                <div class="product-grid" id="productsGrid">
                    @foreach ($products as $product)
                        @include('customer.partials.product-card', ['produk' => $product])
                    @endforeach
                </div>

                <div id="loadMoreContainer" style="text-align:center;margin-top:30px;">
                    <button id="loadMoreBtn" class="btn-LebihBanyak">
                        <i class="fas fa-plus" style="margin-right:8px;"></i>
                        Lebih Banyak
                    </button>
                </div>
            @else
                <div style="padding:32px 0;text-align:center;color:#888;font-size:18px;">Produk tidak ditemukan.</div>
            @endif
        </div>
    </div>
@endsection

@section('after_content')
    @include('customer.partials.mitra_footer')
    <script>
        let copy = document.querySelector('.mitra-logos-track').cloneNode(true);
        document.querySelector('.mitra-logos-carousel').appendChild(copy);
    </script>
    <script>
        let currentOffset = {{ $products->count() }};
        let totalProducts = {{ \App\Models\Product::count() }};
        let isLoading = false;

        // Reset session ketika halaman di-refresh
        window.addEventListener('beforeunload', function() {
            fetch('/reset-products-session', {
                method: 'GET'
            });
        });

        document.getElementById('loadMoreBtn').addEventListener('click', function() {
            if (isLoading) return;

            isLoading = true;
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i>Memuat...';
            btn.disabled = true;

            fetch(`/load-more-products?offset=${currentOffset}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    if (data.html && data.html.trim() !== '') {
                        document.getElementById('productsGrid').insertAdjacentHTML('beforeend', data.html);
                    }

                    currentOffset = data.totalLoaded;

                    if (!data.hasMore) {
                        document.getElementById('loadMoreContainer').style.display = 'none';
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan saat memuat produk: ' + error.message);
                })
                .finally(() => {
                    isLoading = false;
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });
    </script>

    <div class="cs-bubble" id="chatbotBtn" title="Chatbot Assistant" style="right: 90px;">
        <i class="fas fa-robot"></i>
    </div>

    <div id="chatbotWindow" class="card shadow-lg"
        style="display: none; position: fixed; bottom: 80px; right: 20px; width: 320px; z-index: 1050; border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span><i class="fas fa-robot me-2"></i> Asisten BRMP</span>
            <button type="button" class="btn-close btn-close-white" id="closeChatbot"></button>
        </div>
        <div class="card-body" id="chatContent" style="height: 350px; overflow-y: auto; background-color: #f8f9fa;">
            </div>
        <div class="card-footer bg-white">
            <form id="chatForm">
                <div class="input-group">
                    <input type="text" id="chatInput" class="form-control" placeholder="Cari benih..." required>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i></button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .chatbot-message {
            margin-bottom: 12px;
            display: flex;
            flex-direction: column;
        }

        .chatbot-message.user {
            align-items: flex-end;
        }

        .chatbot-message.bot {
            align-items: flex-start;
        }

        .chatbot-message .message {
            max-width: 80%;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 0.9rem;
            line-height: 1.4;
            position: relative;
        }

        .chatbot-message.user .message {
            background-color: #388E3C;
            color: white;
            border-bottom-right-radius: 2px;
        }

        .chatbot-message.bot .message {
            background-color: #ffffff;
            color: #333;
            border: 1px solid #e0e0e0;
            border-bottom-left-radius: 2px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .product-card {
            display: flex;
            align-items: flex-start;
            margin-top: 8px;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 8px;
            background: #fff;
            gap: 10px;
            max-width: 90%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .product-card img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            flex-shrink: 0;
        }

        .product-card-info {
            flex: 1;
            font-size: 0.85rem;
        }

        .product-card-title {
            font-weight: bold;
            margin-bottom: 2px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-card-price {
            color: #388E3C;
            font-weight: 600;
        }

        /* Updated styles for chatbot topbar and button */
        .card-header.bg-primary {
            background-color: #388E3C !important;
        }

        .btn-primary {
            background-color: #388E3C !important;
            border-color: #388E3C !important;
        }

        .btn-primary:hover {
            background-color: #2e6b2c !important;
            border-color: #2e6b2c !important;
        }
    </style>

    <script>
        const chatbotBtn = document.getElementById('chatbotBtn');
        const chatbotWindow = document.getElementById('chatbotWindow');
        const closeChatbot = document.getElementById('closeChatbot');
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const chatContent = document.getElementById('chatContent');

        // Toggle chatbot window
        chatbotBtn.addEventListener('click', () => {
            chatbotWindow.style.display = chatbotWindow.style.display === 'none' ? 'block' : 'none';
            if(chatbotWindow.style.display === 'block') {
                chatInput.focus();
            }
        });

        closeChatbot.addEventListener('click', () => {
            chatbotWindow.style.display = 'none';
        });

        // Handle form submission
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const userMessage = chatInput.value.trim();
            if (!userMessage) return;

            // Add user message to chat
            addMessage('user', userMessage);
            chatInput.value = '';

            // Show typing indicator
            const typingIndicator = document.createElement('div');
            typingIndicator.className = 'chatbot-message bot';
            typingIndicator.innerHTML = '<div class="message">Sedang mengetik...</div>';
            chatContent.appendChild(typingIndicator);
            chatContent.scrollTop = chatContent.scrollHeight;

            // Send message to backend
            try {
                const response = await fetch('{{ route("chatbot.ask") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ message: userMessage }),
                });
                const data = await response.json();

                // Remove typing indicator
                if(typingIndicator.parentNode) {
                    chatContent.removeChild(typingIndicator);
                }

                // Handle bot response
                if (data.status === 'found') {
                    addMessage('bot', data.message);
                    
                    // Render Products
                    data.data.forEach(product => {
                        const productCard = document.createElement('div');
                        productCard.className = 'product-card';
                        productCard.innerHTML = `
                        <img src="${product.image_url}" alt="${product.name}">
                        <div class="product-card-info">
                            <div class="product-card-title">${product.name}</div>
                            <div class="product-card-price">${product.price}</div>
                            <small class="text-muted">Stok: ${product.stock}</small> <br>
                            <a href="${product.link}" class="btn btn-sm btn-outline-success mt-1" style="padding: 1px 6px; font-size: 0.7rem;">Lihat Detail</a>
                        </div>
                        `;
                        // Append card as a separate element below the message
                        chatContent.appendChild(productCard);
                    });
                    
                } else {
                    addMessage('bot', data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                if(typingIndicator.parentNode) chatContent.removeChild(typingIndicator);
                addMessage('bot', 'Maaf, terjadi kesalahan koneksi. Silakan coba lagi.');
            }
            
            // Auto scroll to bottom
            chatContent.scrollTop = chatContent.scrollHeight;
        });

        // Add message to chat
        function addMessage(sender, text) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chatbot-message ${sender}`;
            messageDiv.innerHTML = `<div class="message">${text}</div>`;
            chatContent.appendChild(messageDiv);
            chatContent.scrollTop = chatContent.scrollHeight;
        }
    </script>
@endsection

<style>
    .cs-bubble {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        background: #388E3C;
        color: white;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
        cursor: pointer;
        transition: box-shadow 0.2s, transform 0.2s;
        font-size: 32px;
    }

    .cs-bubble:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.22);
        transform: scale(1.08);
        background: #2e7d32;
    }

    .cs-dropdown {
        position: fixed;
        bottom: 90px;
        right: 24px;
        z-index: 10000;
        display: none;
        flex-direction: column;
        gap: 12px;
        animation: csDropdownUp 0.2s;
    }

    @keyframes csDropdownUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .cs-dropdown-btn {
        background: #fff;
        color: #388E3C;
        border: none;
        border-radius: 12px;
        padding: 12px 20px;
        font-size: 1.1rem;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.10);
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
        min-width: 170px;
    }

    .cs-dropdown-btn:hover {
        background: #388E3C;
        color: #fff;
    }
</style>
<div class="cs-bubble" id="csBubbleBtn" title="Customer Service">
    <i class="fas fa-headset"></i>
</div>
<div class="cs-dropdown" id="csDropdownMenu">
    <a href="https://wa.me/6281331162878?text=Halo%2C%20saya%20punya%20keluhan%20atau%20pertanyaan%20yang%20ingin%20saya%20sampaikan."
        target="_blank" class="cs-dropdown-btn" style="text-decoration:none;">
        <i class="fab fa-whatsapp"></i> WhatsApp
    </a>
    <a href="/komplain" class="cs-dropdown-btn" style="text-decoration:none;">
        <i class="fas fa-exclamation-circle"></i> Komplain
    </a>
</div>
<script>
    const csBubbleBtn = document.getElementById('csBubbleBtn');
    const csDropdownMenu = document.getElementById('csDropdownMenu');
    let csDropdownOpen = false;
    csBubbleBtn.addEventListener('click', function(e) {
        csDropdownOpen = !csDropdownOpen;
        csDropdownMenu.style.display = csDropdownOpen ? 'flex' : 'none';
    });
    // Close dropdown if click outside
    document.addEventListener('click', function(e) {
        if (!csBubbleBtn.contains(e.target) && !csDropdownMenu.contains(e.target)) {
            csDropdownMenu.style.display = 'none';
            csDropdownOpen = false;
        }
    });
</script>