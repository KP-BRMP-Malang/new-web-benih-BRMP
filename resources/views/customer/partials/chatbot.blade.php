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
        margin-bottom: 8px;
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

    /* Article Card Styles */
    .article-card-chatbot {
        display: flex;
        flex-direction: column;
        margin-bottom: 8px;
        border: 1px solid #e8f5e9;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        max-width: 90%;
        box-shadow: 0 2px 8px rgba(56, 142, 60, 0.1);
        transition: all 0.3s ease;
    }

    .article-card-chatbot:hover {
        box-shadow: 0 4px 12px rgba(56, 142, 60, 0.2);
        transform: translateY(-2px);
    }

    .article-card-chatbot-info {
        padding: 12px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .article-card-chatbot-title {
        font-weight: 700;
        font-size: 0.95rem;
        margin-bottom: 6px;
        color: #222;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .article-card-chatbot-excerpt {
        font-size: 0.8rem;
        color: #666;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }

    .article-card-chatbot-date {
        font-size: 0.75rem;
        color: #999;
        margin-bottom: 10px;
    }

    .article-card-chatbot-link {
        background: linear-gradient(135deg, #388E3C 0%, #4CAF50 100%);
        color: #fff !important;
        border: none !important;
        padding: 6px 12px !important;
        font-size: 0.8rem !important;
        border-radius: 6px;
        text-decoration: none !important;
        text-align: center;
        transition: all 0.2s ease;
        display: inline-block;
    }

    .article-card-chatbot-link:hover {
        background: linear-gradient(135deg, #2e6b2c 0%, #388E3C 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(56, 142, 60, 0.3);
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

    // Session ID for chat continuity (stored in localStorage)
    let sessionId = localStorage.getItem('chatbot_session_id');
    if (!sessionId) {
        sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('chatbot_session_id', sessionId);
    }

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

        // Send message to new chatbot API
        try {
            const response = await fetch('/api/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    session_id: sessionId,
                    message: userMessage
                }),
            });
            const data = await response.json();

            // Remove typing indicator
            if (typingIndicator.parentNode) {
                chatContent.removeChild(typingIndicator);
            }

            // Handle bot response based on new API format
            // Status: found, not_found, need_clarification, out_of_scope, success, error
            if (data.status === 'found' || data.status === 'success' || data.status ===
                'need_clarification') {
                addMessage('bot', data.message);

                // Render data items based on type
                if (data.data && data.data.length > 0) {
                    data.data.forEach(item => {
                        // Check item type from API response - prioritize explicit type field
                        if (item.type === 'faq' || item.question !== undefined) {
                            // FAQ item: type, id, question, answer, link (null)
                            const faqCard = document.createElement('div');
                            faqCard.className = 'chatbot-message bot';
                            faqCard.innerHTML = `
                                <div class="message" style="background: #e8f5e9; border-left: 3px solid #388E3C;">
                                    <strong>❓ ${item.question || item.title}</strong><br>
                                    <span style="color: #555;">${item.answer || item.summary}</span>
                                </div>
                            `;
                            chatContent.appendChild(faqCard);
                        } else if (item.type === 'product') {
                            // Product item: type, id, title, price, stock, unit, summary, link
                            const productCard = document.createElement('div');
                            productCard.className = 'product-card-chatbot';

                            const formattedPrice = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(item.price);

                            productCard.innerHTML = `
                                <div class="product-card-chatbot-info" style="width: 100%;">
                                    <div class="product-card-chatbot-title">${item.title}</div>
                                    <div class="product-card-chatbot-price">${formattedPrice}/${item.unit}</div>
                                    <small class="text-muted">Stok: ${item.stock} ${item.unit}</small>
                                    ${item.summary ? `<div style="font-size: 0.8rem; color: #666; margin-top: 4px;">${item.summary.substring(0, 100)}...</div>` : ''}
                                    ${item.link ? `<a href="${item.link}" class="btn btn-sm btn-outline-success mt-1" style="padding: 1px 6px; font-size: 0.7rem;">Lihat Detail</a>` : ''}
                                </div>
                            `;
                            chatContent.appendChild(productCard);
                        } else if (item.type === 'article' || (item.title !== undefined && item
                                .link)) {
                            // Article item: type, id, title, summary, link (must have link)
                            const articleCard = document.createElement('div');
                            articleCard.className = 'article-card-chatbot';

                            articleCard.innerHTML = `
                                <div class="article-card-chatbot-info">
                                    <div class="article-card-chatbot-title">📰 ${item.title}</div>
                                    <div class="article-card-chatbot-excerpt">${item.summary || ''}</div>
                                    <a href="${item.link}" class="article-card-chatbot-link">Baca Lengkap</a>
                                </div>
                            `;
                            chatContent.appendChild(articleCard);
                        }
                    });
                }
            } else if (data.status === 'not_found' || data.status === 'out_of_scope') {
                addMessage('bot', data.message);
            } else if (data.status === 'error') {
                addMessage('bot', data.message || 'Maaf, terjadi kesalahan. Silakan coba lagi.');
            } else {
                addMessage('bot', data.message || 'Maaf, saya tidak mengerti. Silakan coba lagi.');
            }
        } catch (error) {
            console.error('Chatbot Error:', error);
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

        // Format text: convert newlines to <br> and **text** to <strong>
        let formattedText = text
            .replace(/\n/g, '<br>') // Convert newlines to <br>
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>') // Convert **bold** to <strong>
            .replace(/\*(.+?)\*/g, '<em>$1</em>'); // Convert *italic* to <em>

        messageDiv.innerHTML = `<div class="message">${formattedText}</div>`;
        chatContent.appendChild(messageDiv);
        chatContent.scrollTop = chatContent.scrollHeight;
    }

    // Clear session (optional - for debugging)
    function clearChatSession() {
        localStorage.removeItem('chatbot_session_id');
        sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('chatbot_session_id', sessionId);
        chatContent.innerHTML = '';
    }
</script>
