let ws = null;

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function initWebSocket() {
    if (ws && (ws.readyState === WebSocket.OPEN || ws.readyState === WebSocket.CONNECTING)) {
        return;
    }

    const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    
    const wsUrl = isLocal ? 'ws://127.0.0.1:8080' : `wss://${window.location.host}/ws`;

    try {
        ws = new WebSocket(wsUrl);
    } catch (e) {
        console.warn('WebSocket connection error:', e);
    }

    if (!ws) return;

    ws.onopen = function () {
        ws.send(JSON.stringify({
            type: 'auth',
            userId: parseInt(window.currentUserId, 10) || 0,
            username: window.currentUserName || 'User'
        }));
    };

    ws.onmessage = function (e) {
        try {
            const data = JSON.parse(e.data);

            if (data.type === 'private_message') {
                const myId = parseInt(window.currentUserId, 10) || 0;
                const fromId = parseInt(data.fromUserId, 10);
                const toId = parseInt(data.toUserId, 10);
                const activeTargetId = getActiveTargetUserId();
                const myRole = window.currentUserRole || 'user';

                if (fromId === myId || fromId === activeTargetId || toId === activeTargetId) {
                    appendChatMessage(data);
                }

                if (fromId !== myId && myRole !== 'admin') {
                    const isEn = (document.documentElement.lang === 'en') || (document.cookie.indexOf('site_lang=en') !== -1);
                    const notifTitle = isEn ? 'New message from Administrator' : 'Нове повідомлення від Адміністратора';
                    showToast(notifTitle, data.message, data.timestamp);
                }
            }
        } catch (err) {
            console.error('[WS Parse Error]:', err);
        }
    };

    ws.onclose = function () {
        setTimeout(initWebSocket, 4000);
    };

    ws.onerror = function (err) {
        if (ws) ws.close();
    };
}

    ws.onclose = function () {
        setTimeout(initWebSocket, 3000);
    };

    ws.onerror = function (err) {
        if (ws) ws.close();
    };
}

function getActiveTargetUserId() {
    const select = document.getElementById('chat-target-user');
    if (select) {
        return parseInt(select.value, 10) || 0;
    }
    return parseInt(window.activeTargetId || window.adminId || 1, 10);
}

function sendSupportMessage() {
    if (!ws || ws.readyState !== WebSocket.OPEN) {
        alert(document.documentElement.lang === 'en' ? 'Chat server is offline.' : 'Сервер чату вимкнений. Запустіть сокет-сервер.');
        return;
    }

    const input = document.getElementById('chat-input');
    if (!input) return;

    const text = input.value.trim();
    const toUserId = getActiveTargetUserId();

    if (!text || !toUserId) return;

    ws.send(JSON.stringify({
        type: 'private_message',
        toUserId: toUserId,
        message: text
    }));

    input.value = '';
}

function appendChatMessage(data) {
    const box = document.getElementById('chat-messages');
    if (!box) return;

    const myId = parseInt(window.currentUserId, 10) || 0;
    const isMine = parseInt(data.fromUserId, 10) === myId;

    const bubble = document.createElement('div');
    bubble.className = `chat-bubble ${isMine ? 'chat-bubble-mine' : 'chat-bubble-partner'}`;

    const isEn = (document.documentElement.lang === 'en');
    const senderTitle = isMine ? (isEn ? 'You' : 'Ви') : data.fromUsername;

    bubble.innerHTML = `
        <div class="chat-meta">
            <strong>${escapeHtml(senderTitle)}</strong>
            <small>${escapeHtml(data.timestamp || '')}</small>
        </div>
        <div class="chat-text">${escapeHtml(data.message)}</div>
    `;

    box.appendChild(bubble);
    box.scrollTop = box.scrollHeight;
}

function showToast(title, message, time) {
    let container = document.getElementById('toast-chat-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-chat-container';
        container.style.cssText = 'position:fixed;top:25px;right:25px;z-index:999999;display:flex;flex-direction:column;gap:12px;pointer-events:none;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.style.cssText = 'pointer-events:auto;background:#ffffff;color:#1e293b;padding:14px 18px;border-radius:10px;box-shadow:0 12px 30px rgba(0,0,0,0.25);border-left:6px solid #d81b60;min-width:280px;max-width:360px;font-family:inherit;opacity:1;display:block;';

    toast.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <strong style="color:#d81b60;font-size:14px;font-weight:700;">${escapeHtml(title)}</strong>
            <small style="color:#64748b;font-size:11px;">${escapeHtml(time || '')}</small>
        </div>
        <div style="font-size:13px;line-height:1.4;color:#334155;word-break:break-word;">${escapeHtml(message)}</div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 6000);
}

document.addEventListener('DOMContentLoaded', () => {
    initWebSocket();

    const box = document.getElementById('chat-messages');
    if (box) {
        box.scrollTop = box.scrollHeight;
    }

    const input = document.getElementById('chat-input');
    if (input) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendSupportMessage();
            }
        });
    }
});
