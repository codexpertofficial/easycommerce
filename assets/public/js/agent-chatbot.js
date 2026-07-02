(function () {
    'use strict';

    var SESSION_KEY  = 'ec_agent_session_id';
    var HISTORY_KEY  = 'ec_agent_history';
    var panel, messages, input, sendBtn, bubble, typingEl;
    var sending = false;

    var cfg = (typeof EC_AGENT !== 'undefined') ? EC_AGENT : {};
    var agentName    = cfg.name     || 'AI Shopping Assistant';
    var agentAvatar  = cfg.avatar   || '';
    var position     = cfg.position || 'right';
    var primaryColor = cfg.color    || '#7351FD';

    // ── Storage ──────────────────────────────────────────────────────────────

    function getSessionId() {
        return localStorage.getItem(SESSION_KEY) || null;
    }

    function setSessionId(id) {
        localStorage.setItem(SESSION_KEY, id);
    }

    function loadHistory() {
        try {
            return JSON.parse(localStorage.getItem(HISTORY_KEY)) || [];
        } catch (e) {
            return [];
        }
    }

    function saveHistory(history) {
        localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
    }

    function clearSession() {
        localStorage.removeItem(SESSION_KEY);
        localStorage.removeItem(HISTORY_KEY);
    }

    // ── CSS variables ─────────────────────────────────────────────────────────

    function injectColorVars() {
        var style = document.createElement('style');
        style.id  = 'ec-agent-color-vars';

        // Derive a slightly darker shade for hover states
        style.textContent =
            ':root {' +
            '--ec-agent-primary: ' + primaryColor + ';' +
            '--ec-agent-primary-shadow: ' + primaryColor + '73;' +
            '}';

        document.head.appendChild(style);

        // Position overrides
        if (position === 'left') {
            var posStyle = document.createElement('style');
            posStyle.id  = 'ec-agent-position-vars';
            posStyle.textContent =
                '#ec-agent-bubble { right: auto; left: 24px; }' +
                '#ec-agent-panel  { right: auto; left: 24px; transform-origin: bottom left; }';
            document.head.appendChild(posStyle);
        }
    }

    // ── DOM helpers ───────────────────────────────────────────────────────────

    function scrollToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function escapeHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function markdownToHtml(str) {
        var s = str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        s = s.replace(/\*(.+?)\*/g, '<em>$1</em>');

        var lines = s.split('\n');
        var out = [];
        var inUl = false, inOl = false;

        lines.forEach(function (line) {
            var ulMatch = line.match(/^[-*] (.+)/);
            var olMatch = line.match(/^\d+\. (.+)/);

            if (ulMatch) {
                if (inOl) { out.push('</ol>'); inOl = false; }
                if (!inUl) { out.push('<ul>'); inUl = true; }
                out.push('<li>' + ulMatch[1] + '</li>');
            } else if (olMatch) {
                if (inUl) { out.push('</ul>'); inUl = false; }
                if (!inOl) { out.push('<ol>'); inOl = true; }
                out.push('<li>' + olMatch[1] + '</li>');
            } else {
                if (inUl) { out.push('</ul>'); inUl = false; }
                if (inOl) { out.push('</ol>'); inOl = false; }
                out.push(line);
            }
        });

        if (inUl) out.push('</ul>');
        if (inOl) out.push('</ol>');

        var html = '';
        var structural = /^<\/?[uo]l>|^<li>/;
        for (var i = 0; i < out.length; i++) {
            if (i > 0 && !structural.test(out[i]) && !structural.test(out[i - 1])) {
                html += '<br>';
            }
            html += out[i];
        }

        html = html.replace(/(https?:\/\/[^\s<>"]+)/g, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');

        return html;
    }

    function renderMessage(role, text, orderUrl) {
        var div = document.createElement('div');
        div.className = 'ec-agent-msg ec-agent-msg-' + role;

        if (role === 'assistant') {
            var avatarEl = document.createElement('div');
            avatarEl.className = 'ec-agent-msg-avatar';
            if (agentAvatar) {
                var img = document.createElement('img');
                img.src = agentAvatar;
                img.alt = agentName;
                avatarEl.appendChild(img);
            } else {
                avatarEl.innerHTML =
                    '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">' +
                    '<path d="M12 2C6.477 2 2 6.253 2 11.5c0 2.304.87 4.41 2.304 6.032L3 21l3.758-1.22A10.12 10.12 0 0012 21c5.523 0 10-4.253 10-9.5S17.523 2 12 2z"/>' +
                    '</svg>';
            }
            div.appendChild(avatarEl);
        }

        var bub = document.createElement('div');
        bub.className = 'ec-agent-msg-bubble';

        if (role === 'assistant') {
            var html = markdownToHtml(text);
            if (orderUrl) {
                html += '<br><br><a href="' + escapeHtml(orderUrl) + '" target="_blank" rel="noopener noreferrer" class="ec-agent-order-btn">Complete your order →</a>';
            }
            bub.innerHTML = html;
        } else {
            bub.textContent = text;
        }

        div.appendChild(bub);
        messages.appendChild(div);
        return div;
    }

    function appendMessage(role, text, orderUrl) {
        renderMessage(role, text, orderUrl || null);
        scrollToBottom();

        var history = loadHistory();
        history.push({ role: role, text: text, orderUrl: orderUrl || null });
        saveHistory(history);
    }

    function showTyping() {
        typingEl = document.createElement('div');
        typingEl.className = 'ec-agent-typing';

        var avatarEl = document.createElement('div');
        avatarEl.className = 'ec-agent-msg-avatar';
        if (agentAvatar) {
            var img = document.createElement('img');
            img.src = agentAvatar;
            img.alt = agentName;
            avatarEl.appendChild(img);
        } else {
            avatarEl.innerHTML =
                '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">' +
                '<path d="M12 2C6.477 2 2 6.253 2 11.5c0 2.304.87 4.41 2.304 6.032L3 21l3.758-1.22A10.12 10.12 0 0012 21c5.523 0 10-4.253 10-9.5S17.523 2 12 2z"/>' +
                '</svg>';
        }

        var dots = document.createElement('div');
        dots.className = 'ec-agent-typing-dots';
        dots.innerHTML = '<span></span><span></span><span></span>';

        typingEl.appendChild(avatarEl);
        typingEl.appendChild(dots);
        messages.appendChild(typingEl);
        scrollToBottom();
    }

    function hideTyping() {
        if (typingEl && typingEl.parentNode) {
            typingEl.parentNode.removeChild(typingEl);
            typingEl = null;
        }
    }

    function setSending(state) {
        sending = state;
        sendBtn.disabled = state;
        input.disabled = state;
    }

    function autoResizeInput() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 96) + 'px';
    }

    // ── API ───────────────────────────────────────────────────────────────────

    function sendMessage() {
        var text = input.value.trim();
        if (!text || sending) return;

        appendMessage('user', text);
        input.value = '';
        autoResizeInput();
        setSending(true);
        showTyping();

        var body = { message: text };
        var session = getSessionId();
        if (session) body.session_id = session;

        fetch(EASYCOMMERCE.rest_base + '/ai/agent/assistant', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': EASYCOMMERCE.nonce,
            },
            body: JSON.stringify(body),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                hideTyping();
                setSending(false);

                if (!data.session_id && !data.reply) {
                    appendMessage('assistant', 'Sorry, something went wrong. Please try again.');
                    return;
                }

                setSessionId(data.session_id);
                appendMessage('assistant', data.reply || '', data.order_url || null);
            })
            .catch(function () {
                hideTyping();
                setSending(false);
                appendMessage('assistant', 'Connection error. Please try again.');
            });
    }

    // ── Panel open/close ──────────────────────────────────────────────────────

    function openPanel() {
        panel.classList.add('ec-agent-panel-open');
        bubble.classList.add('ec-agent-open');
        input.focus();
    }

    function closePanel() {
        panel.classList.remove('ec-agent-panel-open');
        bubble.classList.remove('ec-agent-open');
    }

    function togglePanel() {
        if (panel.classList.contains('ec-agent-panel-open')) {
            closePanel();
        } else {
            openPanel();
        }
    }

    // ── Build UI ──────────────────────────────────────────────────────────────

    function buildAvatarHtml(size) {
        if (agentAvatar) {
            return '<img src="' + escapeHtml(agentAvatar) + '" alt="' + escapeHtml(agentName) + '" style="width:' + size + 'px;height:' + size + 'px;border-radius:50%;object-fit:cover;">';
        }
        return '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">' +
               '<path d="M12 2C6.477 2 2 6.253 2 11.5c0 2.304.87 4.41 2.304 6.032L3 21l3.758-1.22A10.12 10.12 0 0012 21c5.523 0 10-4.253 10-9.5S17.523 2 12 2z"/>' +
               '</svg>';
    }

    function buildUI() {
        injectColorVars();

        // Floating bubble
        bubble = document.createElement('button');
        bubble.id = 'ec-agent-bubble';
        bubble.setAttribute('aria-label', 'Open ' + agentName);
        bubble.innerHTML =
            '<svg class="ec-agent-chat-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">' +
            '<path d="M12 2C6.477 2 2 6.253 2 11.5c0 2.304.87 4.41 2.304 6.032L3 21l3.758-1.22A10.12 10.12 0 0012 21c5.523 0 10-4.253 10-9.5S17.523 2 12 2z"/>' +
            '</svg>' +
            '<svg class="ec-agent-close-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">' +
            '<path d="M18 6L6 18M6 6l12 12" stroke="#fff" stroke-width="2.5" stroke-linecap="round" fill="none"/>' +
            '</svg>';

        // Chat panel
        panel = document.createElement('div');
        panel.id = 'ec-agent-panel';
        panel.setAttribute('role', 'dialog');
        panel.setAttribute('aria-label', agentName);

        var header = document.createElement('div');
        header.id = 'ec-agent-panel-header';
        header.innerHTML =
            '<div class="ec-agent-avatar">' + buildAvatarHtml(38) + '</div>' +
            '<div class="ec-agent-header-text">' +
            '<h3>' + escapeHtml(agentName) + '</h3>' +
            '<p class="ec-agent-status"><span class="ec-agent-status-dot"></span>Online</p>' +
            '</div>' +
            '<div class="ec-agent-header-actions">' +
            '<button id="ec-agent-clear" title="New conversation">' +
            '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M4 12a8 8 0 018-8V2.5M20 12a8 8 0 01-8 8v1.5M14 2.5l-2-2-2 2M10 21.5l2 2 2-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg>' +
            '</button>' +
            '<button id="ec-agent-minimize" title="Minimize">' +
            '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" fill="none"/></svg>' +
            '</button>' +
            '</div>';

        messages = document.createElement('div');
        messages.id = 'ec-agent-messages';

        var inputArea = document.createElement('div');
        inputArea.id = 'ec-agent-input-area';

        input = document.createElement('textarea');
        input.id = 'ec-agent-input';
        input.placeholder = 'Ask about products, orders…';
        input.rows = 1;
        input.setAttribute('aria-label', 'Message');

        sendBtn = document.createElement('button');
        sendBtn.id = 'ec-agent-send';
        sendBtn.setAttribute('aria-label', 'Send');
        sendBtn.innerHTML =
            '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">' +
            '<path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/>' +
            '</svg>';

        inputArea.appendChild(input);
        inputArea.appendChild(sendBtn);
        panel.appendChild(header);
        panel.appendChild(messages);
        panel.appendChild(inputArea);
        document.body.appendChild(bubble);
        document.body.appendChild(panel);

        var history = loadHistory();
        if (history.length > 0) {
            history.forEach(function (msg) {
                renderMessage(msg.role, msg.text, msg.orderUrl);
            });
            scrollToBottom();
        } else {
            appendMessage('assistant', 'Hi! 👋 I\'m ' + agentName + '. How can I help you today?');
        }

        bubble.addEventListener('click', togglePanel);

        document.getElementById('ec-agent-minimize').addEventListener('click', closePanel);

        document.getElementById('ec-agent-clear').addEventListener('click', function () {
            clearSession();
            messages.innerHTML = '';
            appendMessage('assistant', 'Hi! 👋 I\'m ' + agentName + '. How can I help you today?');
        });

        sendBtn.addEventListener('click', sendMessage);

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        input.addEventListener('input', autoResizeInput);

        document.addEventListener('click', function (e) {
            if (
                panel.classList.contains('ec-agent-panel-open') &&
                !panel.contains(e.target) &&
                e.target !== bubble &&
                !bubble.contains(e.target)
            ) {
                closePanel();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', buildUI);
    } else {
        buildUI();
    }
})();
