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
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .product-card-chatbot {
        display: flex;
        align-items: flex-start;
        margin-top: 8px;
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 8px;
        background: #fff;
        gap: 10px;
        max-width: 90%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .product-card-chatbot img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        flex-shrink: 0;
    }

    .product-card-chatbot-info {
        flex: 1;
        font-size: 0.85rem;
    }

    .product-card-chatbot-title {
        font-weight: bold;
        margin-bottom: 2px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-card-chatbot-price {
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
        if (chatbotWindow.style.display === 'block') {
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
            const response = await fetch('{{ route('chatbot.ask') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    message: userMessage
                }),
            });
            const data = await response.json();

            // Remove typing indicator
            if (typingIndicator.parentNode) {
                chatContent.removeChild(typingIndicator);
            }

            // Handle bot response
            if (data.status === 'found') {
                addMessage('bot', data.message);

                // Render Products
                data.data.forEach(product => {
                    const productCard = document.createElement('div');
                    productCard.className = 'product-card-chatbot';

                    productCard.innerHTML = `
                        <img src="${product.image_url}" alt="${product.name}">
                        <div class="product-card-chatbot-info">
                            <div class="product-card-chatbot-title">${product.name}</div>
                            <div class="product-card-chatbot-price">${product.price}</div>
                            <small class="text-muted">Stok: ${product.stock}</small> <br>
                            <a href="${product.link}" class="btn btn-sm btn-outline-success mt-1" style="padding: 1px 6px; font-size: 0.7rem;">Lihat Detail</a>
                        </div>
                    `;
                    chatContent.appendChild(productCard);
                });

            } else {
                addMessage('bot', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            if (typingIndicator.parentNode) chatContent.removeChild(typingIndicator);
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
