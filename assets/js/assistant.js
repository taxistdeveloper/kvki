/**
 * ИИ-ассистент КВКИ — виджет чата
 */
(function () {
    'use strict';

    const BASE_URL = (document.querySelector('meta[name="base-url"]')?.content || window.BASE_URL || '').replace(/\/$/, '');
    const API_URL = BASE_URL + '/api/assistant.php';

    function getMascotSvg() {
        const id = 'kvki-m' + Math.random().toString(36).slice(2, 9);
        return `<svg class="kvki-mascot" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
                <linearGradient id="${id}-bg" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#5a7d5a"/><stop offset="100%" style="stop-color:#3d5c3d"/></linearGradient>
                <linearGradient id="${id}-face" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#fefdfb"/><stop offset="100%" style="stop-color:#f5f1eb"/></linearGradient>
            </defs>
            <circle cx="32" cy="32" r="30" fill="url(#${id}-bg)"/>
            <circle cx="32" cy="32" r="24" fill="url(#${id}-face)"/>
            <circle cx="26" cy="28" r="3" fill="#4a6d4a"/>
            <circle cx="38" cy="28" r="3" fill="#4a6d4a"/>
            <path d="M24 38 Q32 44 40 38" stroke="#4a6d4a" stroke-width="2" fill="none" stroke-linecap="round"/>
            <path d="M18 16 L22 12 L26 16 L32 10 L38 16 L42 12 L46 16" stroke="#4a6d4a" stroke-width="1.5" fill="none" stroke-linecap="round"/>
        </svg>`;
    }

    const html = `
        <div id="kvki-assistant" class="kvki-assistant" aria-hidden="true">
            <button type="button" id="kvki-assistant-toggle" class="kvki-assistant-toggle" aria-label="Открыть чат с ИИ-ассистентом КВКИ">
                <span class="kvki-assistant-toggle-avatar">${getMascotSvg()}</span>
                <span class="kvki-assistant-toggle-label">КВКИ</span>
            </button>
            <div id="kvki-assistant-panel" class="kvki-assistant-panel" role="dialog" aria-label="Чат с ИИ-ассистентом" aria-modal="true" hidden>
                <div class="kvki-assistant-header">
                    <div class="kvki-assistant-header-avatar">${getMascotSvg()}</div>
                    <div class="kvki-assistant-header-info">
                        <span class="kvki-assistant-header-title">КВКИ</span>
                        <span class="kvki-assistant-header-subtitle">ИИ-ассистент колледжа</span>
                        <span class="kvki-assistant-header-status">Онлайн</span>
                    </div>
                    <button type="button" id="kvki-assistant-close" class="kvki-assistant-close" aria-label="Закрыть чат">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div id="kvki-assistant-messages" class="kvki-assistant-messages">
                    <div class="kvki-assistant-msg kvki-assistant-msg--bot">
                        <div class="kvki-assistant-msg-avatar">${getMascotSvg()}</div>
                        <div class="kvki-assistant-msg-bubble">Здравствуйте! Я ИИ-ассистент Карагандинского высшего колледжа инжиниринга. Спрашивайте о приёме, специальностях, контактах — постараюсь помочь.</div>
                    </div>
                </div>
                <div class="kvki-assistant-input-wrap">
                    <textarea id="kvki-assistant-input" class="kvki-assistant-input" rows="1" placeholder="Напишите сообщение..." maxlength="2000"></textarea>
                    <button type="button" id="kvki-assistant-send" class="kvki-assistant-send" aria-label="Отправить">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </div>
            </div>
        </div>
    `;

    function init() {
        const root = document.createElement('div');
        root.innerHTML = html;
        document.body.appendChild(root.firstElementChild);

        const container = document.getElementById('kvki-assistant');
        const toggle = document.getElementById('kvki-assistant-toggle');
        const panel = document.getElementById('kvki-assistant-panel');
        const closeBtn = document.getElementById('kvki-assistant-close');
        const messagesEl = document.getElementById('kvki-assistant-messages');
        const inputEl = document.getElementById('kvki-assistant-input');
        const sendBtn = document.getElementById('kvki-assistant-send');

        let messages = [];
        let isLoading = false;

        function openPanel() {
            panel.hidden = false;
            panel.classList.add('is-open');
            container.setAttribute('aria-hidden', 'false');
            inputEl.focus();
        }

        function closePanel() {
            panel.classList.remove('is-open');
            panel.hidden = true;
            container.setAttribute('aria-hidden', 'true');
        }

        function scrollToBottom() {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }

        function addMessage(role, content) {
            const div = document.createElement('div');
            div.className = 'kvki-assistant-msg kvki-assistant-msg--' + (role === 'user' ? 'user' : 'bot');
            if (role === 'bot') {
                const avatar = document.createElement('div');
                avatar.className = 'kvki-assistant-msg-avatar';
                avatar.innerHTML = getMascotSvg();
                div.appendChild(avatar);
            }
            const bubble = document.createElement('div');
            bubble.className = 'kvki-assistant-msg-bubble';
            bubble.textContent = content;
            div.appendChild(bubble);
            messagesEl.appendChild(div);
            scrollToBottom();
        }

        function addLoading() {
            const div = document.createElement('div');
            div.className = 'kvki-assistant-msg kvki-assistant-msg--bot kvki-assistant-msg--loading';
            div.innerHTML = '<div class="kvki-assistant-msg-avatar">' + getMascotSvg() + '</div><div class="kvki-assistant-msg-bubble"><span class="kvki-assistant-typing"></span></div>';
            div.id = 'kvki-assistant-loading';
            messagesEl.appendChild(div);
            scrollToBottom();
        }

        function removeLoading() {
            const el = document.getElementById('kvki-assistant-loading');
            if (el) el.remove();
        }

        async function sendMessage() {
            const text = inputEl.value.trim();
            if (!text || isLoading) return;

            inputEl.value = '';
            inputEl.style.height = 'auto';
            messages.push({ role: 'user', content: text });
            addMessage('user', text);
            isLoading = true;
            sendBtn.disabled = true;
            addLoading();

            try {
                const res = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ messages: messages }),
                });
                const data = await res.json();

                removeLoading();
                if (data.error) {
                    addMessage('bot', 'Извините, произошла ошибка. Попробуйте позже или свяжитесь с нами по телефону.');
                } else {
                    const reply = data.reply || 'Нет ответа.';
                    messages.push({ role: 'assistant', content: reply });
                    addMessage('bot', reply);
                }
            } catch (err) {
                removeLoading();
                addMessage('bot', 'Не удалось отправить сообщение. Проверьте интернет и попробуйте снова.');
            }
            isLoading = false;
            sendBtn.disabled = false;
        }

        toggle.addEventListener('click', function () {
            if (panel.classList.contains('is-open')) closePanel();
            else openPanel();
        });
        closeBtn.addEventListener('click', closePanel);
        sendBtn.addEventListener('click', sendMessage);

        inputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        inputEl.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Закрытие по клику вне панели
        document.addEventListener('click', function (e) {
            if (panel.classList.contains('is-open') && !container.contains(e.target)) {
                closePanel();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
