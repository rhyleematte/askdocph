/**
 * Messenger JS
 */
document.addEventListener('DOMContentLoaded', function() {
    const messengerToggle = document.querySelector('.messenger-toggle');
    const messengerDrawer = document.querySelector('#messenger-drawer');
    const closeDrawer = document.querySelector('#close-messenger-drawer');
    const conversationList = document.querySelector('#conversation-list');
    const userSearchInput = document.querySelector('#messenger-user-search');
    const searchResults = document.querySelector('#messenger-search-results');
    const chatTray = document.querySelector('#chat-tray');
    const chatBoxTemplate = document.querySelector('#chat-box-template');

    // If messenger isn't on this page (like for Admins), exit safely.
    if (!messengerDrawer || !conversationList || !chatBoxTemplate) {
        return;
    }

    let openChats = new Map(); // conversationId -> { element, lastMsgId, pollInterval, typingInterval, isTyping }
    let convPollInterval = null;

    let isArchivedMode = false;

    // Toggle Drawer
    if (messengerToggle) {
        messengerToggle.addEventListener('click', () => {
            messengerDrawer.classList.toggle('open');
            if (messengerDrawer.classList.contains('open')) {
                // When opening, reset to normal mode
                exitArchivedMode();
                loadConversations();
                if (!convPollInterval) {
                    convPollInterval = setInterval(() => loadConversations(isArchivedMode), 5000);
                }
            } else {
                clearInterval(convPollInterval);
                convPollInterval = null;
            }
        });
    }

    if (closeDrawer) {
        closeDrawer.addEventListener('click', () => {
            messengerDrawer.classList.remove('open');
            clearInterval(convPollInterval);
            convPollInterval = null;
        });
    }

    // Load Conversations
    async function loadConversations(archived = false) {
        if (document.activeElement && (document.activeElement.id === 'messenger-user-search')) return;
        if (document.querySelector('.conv-menu-popover.open')) return;
        if (document.querySelector('.messenger-settings-dropdown.open')) return;

        try {
            const response = await fetch(`/api/messenger/conversations?archived=${archived ? 1 : 0}`);
            if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) {
                if (response.status === 401) return; // Silent on unauthorized
                const text = await response.text();
                return;
            }
            const conversations = await response.json();
            
            conversationList.innerHTML = '';
            if (!conversations || conversations.length === 0) {
                conversationList.innerHTML = '<div class="search-empty">No conversations found</div>';
                return;
            }
            conversations.forEach(conv => {
                if (!conv.other_user) return; // Skip conversations without other_user (e.g. system bot or deleted)
                
                const isMutual = conv.other_user ? conv.other_user.is_mutual : false;
                const isConversation = conv.is_conversation;
                
                const item = document.createElement('div');
                item.className = 'conversation-item';
                if (!isConversation) item.classList.add('contact-item');
                if (conv.unread_count > 0) item.classList.add('unread');
                
                let lastMsg = 'No messages yet';
                if (conv.latest_message) {
                    lastMsg = conv.latest_message.body;
                } else if (isMutual) {
                    lastMsg = '<span class="mutual-badge">Mutual Follower</span>';
                } else if (!isConversation) {
                    lastMsg = '<span class="following-badge">Following</span>';
                }

                item.innerHTML = `
                    <div class="chat-avatar-wrapper">
                        <img src="${conv.other_user.avatar}" class="chat-avatar">
                        ${isMutual ? '<div class="mutual-dot" title="Mutual Follower"></div>' : ''}
                        ${conv.other_user.is_online ? '<div class="presence-dot" title="Online"></div>' : ''}
                    </div>
                    <div class="conv-info">
                        <div class="conv-name">
                            ${conv.other_user.name}
                            ${conv.other_user.is_doctor ? '<i data-lucide="badge-check" class="doctor-badge"></i>' : ''}
                        </div>
                        <div class="conv-last-msg">${lastMsg}</div>
                    </div>
                    ${conv.unread_count > 0 ? '<div class="unread-dot"></div>' : ''}
                    ${isConversation ? `
                    <div class="conv-actions">
                        <button class="conv-menu-btn" type="button"><i data-lucide="more-vertical"></i></button>
                        <div class="conv-menu-popover">
                            <div class="conv-menu-item ${archived ? 'unarchive-conv' : 'archive-conv'}" data-id="${conv.id}">
                                ${archived ? 'Unarchive' : 'Archive'}
                            </div>
                        </div>
                    </div>
                    ` : ''}
                `;

                item.addEventListener('click', (e) => {
                    if (e.target.closest('.conv-actions')) return;
                    openChatBox(conv);
                });

                if (isConversation) {
                    const menuBtn = item.querySelector('.conv-menu-btn');
                    const popover = item.querySelector('.conv-menu-popover');
                    const archiveBtn = item.querySelector('.archive-conv');
                    const unarchiveBtn = item.querySelector('.unarchive-conv');

                    if (menuBtn) {
                        menuBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            // Close any other open menus
                            document.querySelectorAll('.conv-menu-popover.open').forEach(p => {
                                if (p !== popover) p.classList.remove('open');
                            });
                            if (popover) popover.classList.toggle('open');
                        });
                    }

                    if (archiveBtn) {
                        archiveBtn.addEventListener('click', async (e) => {
                            e.stopPropagation();
                            if (popover) popover.classList.remove('open');
                            
                            try {
                                const response = await fetch(`/api/messenger/conversations/${conv.id}/archive`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        'Accept': 'application/json'
                                    }
                                });
                                if (response.ok) {
                                    loadConversations(isArchivedMode);
                                }
                            } catch (err) { console.error(err); }
                        });
                    }

                    if (unarchiveBtn) {
                        unarchiveBtn.addEventListener('click', async (e) => {
                            e.stopPropagation();
                            if (popover) popover.classList.remove('open');
                            
                            try {
                                const response = await fetch(`/api/messenger/conversations/${conv.id}/unarchive`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        'Accept': 'application/json'
                                    }
                                });
                                if (response.ok) {
                                    loadConversations(isArchivedMode);
                                }
                            } catch (err) { console.error(err); }
                        });
                    }
                }

                conversationList.appendChild(item);
            });
            lucide.createIcons();

            // Handle open_chat URL param
            const urlParams = new URLSearchParams(window.location.search);
            const openChatId = urlParams.get('open_chat');
            if (openChatId) {
                const convToOpen = conversations.find(c => c.id == openChatId);
                if (convToOpen) {
                    openChatBox(convToOpen);
                    // Optionally clear the param to avoid re-opening on refresh
                    const newUrl = window.location.pathname + window.location.hash;
                    window.history.replaceState({}, document.title, newUrl);
                }
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
        }
    }

    document.addEventListener('click', () => {
        document.querySelectorAll('.conv-menu-popover.open').forEach(p => p.classList.remove('open'));
    });

    // Search Users
    userSearchInput.addEventListener('input', async (e) => {
        const query = e.target.value;
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/api/messenger/search?q=${query}`);
            if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) return;
            const users = await response.json();

            searchResults.innerHTML = '';
            if (users.length > 0) {
                users.forEach(user => {
                    const item = document.createElement('div');
                    item.className = 'search-result-item';
                    
                    let status = '';
                    if (user.priority === 1) status = 'Mutual';
                    else if (user.priority === 2) status = 'Following';

                    item.innerHTML = `
                        <img src="${user.avatar}" class="chat-avatar">
                        <div class="conv-info">
                            <div class="conv-name">
                                ${user.name}
                                ${user.is_doctor ? '<i data-lucide="badge-check" class="doctor-badge"></i>' : ''}
                            </div>
                            <div class="conv-last-msg">${status}</div>
                        </div>
                    `;
                    item.addEventListener('click', () => {
                        openChatBox({ other_user: user, id: null });
                        searchResults.style.display = 'none';
                        userSearchInput.value = '';
                    });
                    searchResults.appendChild(item);
                });
                searchResults.style.display = 'block';
            } else {
                searchResults.style.display = 'none';
            }
        } catch (error) {
            console.error('Error searching users:', error);
        }
    });

    // Open Chat Box
    function openChatBox(conv, pendingRequest = null) {
        const convId = conv.id || `temp-${conv.other_user.id}`;
        
        if (openChats.has(convId)) {
            const chatObj = openChats.get(convId);
            chatObj.element.classList.remove('minimized');
            
            if (pendingRequest) {
                // If it was already open but now we got a new request, show overlay
                setupPendingUI(chatObj.element, conv, pendingRequest);
            }
            return;
        }

        const clone = chatBoxTemplate.content.cloneNode(true);
        const chatBox = clone.querySelector('.chat-box');
        chatBox.dataset.conversationId = convId;
        chatBox.dataset.receiverId = conv.other_user.id;

        chatBox.querySelector('.chat-user-name').textContent = conv.other_user.name;

        // Avatar with presence dot
        const chatAvatarWrapper = chatBox.querySelector('.chat-avatar-wrapper') || chatBox.querySelector('.chat-box-header');
        const chatAvatar = chatBox.querySelector('.chat-avatar');
        chatAvatar.src = conv.other_user.avatar;

        // Add presence dot to chat header avatar
        const existingPresenceDot = chatBox.querySelector('.chat-header-presence');
        if (existingPresenceDot) existingPresenceDot.remove();
        if (conv.other_user.is_online) {
            const dot = document.createElement('div');
            dot.className = 'chat-header-presence';
            dot.title = 'Online';
            chatAvatar.insertAdjacentElement('afterend', dot);
        }

        // Update status label
        const statusLabel = chatBox.querySelector('.chat-user-status');
        if (statusLabel) {
            statusLabel.textContent = conv.other_user.is_online ? 'Online' : '';
            statusLabel.style.color = conv.other_user.is_online ? '#22c55e' : '';
        }

        if (conv.other_user.is_doctor) {
            const badge = document.createElement('i');
            badge.dataset.lucide = 'badge-check';
            badge.className = 'doctor-badge';
            chatBox.querySelector('.chat-user-info').appendChild(badge);
        }

        // Actions
        chatBox.querySelector('.close-chat').addEventListener('click', (e) => {
            e.stopPropagation();
            const chatObj = openChats.get(convId);
            if (chatObj) {
                clearInterval(chatObj.pollInterval);
                clearInterval(chatObj.typingInterval);
            }
            chatBox.remove();
            openChats.delete(convId);
        });

        chatBox.querySelector('.minimize-chat').addEventListener('click', (e) => {
            e.stopPropagation();
            chatBox.classList.toggle('minimized');
        });

        chatBox.querySelector('.chat-box-header').addEventListener('click', () => {
            chatBox.classList.toggle('minimized');
        });

        // Send Message
        const input = chatBox.querySelector('textarea');
        const sendBtn = chatBox.querySelector('.send-message-btn');

        const sendMessage = async () => {
            const body = input.value.trim();
            if (!body) return;

            try {
                const response = await fetch('/api/messenger/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        conversation_id: conv.id,
                        receiver_id: conv.other_user.id,
                        body: body
                    })
                });

                if (!response.ok) {
                    const text = await response.text();
                    console.error("Messenger Send Error:", response.status, text.substring(0, 100));
                    throw new Error("HTTP " + response.status);
                }

                const msg = await response.json();
                input.value = '';
                addMessageToBox(chatBox, msg, 'sent');
                
                if (!conv.id) {
                    chatBox.dataset.conversationId = msg.conversation_id;
                    openChats.delete(convId);
                    
                    const newChatObj = {
                        element: chatBox,
                        lastMsgId: msg.id,
                        pollInterval: setInterval(() => pollForMessages(chatBox, msg.conversation_id), 3000),
                        typingInterval: setInterval(() => pollForTyping(chatBox, msg.conversation_id), 3000),
                        isTyping: false
                    };
                    openChats.set(msg.conversation_id, newChatObj);
                    conv.id = msg.conversation_id;
                } else {
                    const chatObj = openChats.get(conv.id);
                    if (chatObj) chatObj.lastMsgId = msg.id;
                }
                
                setTypingStatus(conv.id, false);
                loadConversations();
            } catch (error) {
                console.error('Error sending message:', error);
            }
        };

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        let typingTimeout;
        input.addEventListener('input', () => {
            if (!conv.id) return;
            setTypingStatus(conv.id, true);
            
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                setTypingStatus(conv.id, false);
            }, 3000);
        });

        chatTray.appendChild(chatBox);
        
        if (pendingRequest) {
            setupPendingUI(chatBox, conv, pendingRequest);
        }
        
        const chatObj = {
            element: chatBox,
            lastMsgId: 0,
            pollInterval: conv.id ? setInterval(() => pollForMessages(chatBox, conv.id), 3000) : null,
            typingInterval: conv.id ? setInterval(() => pollForTyping(chatBox, conv.id), 3000) : null,
            isTyping: false
        };
        openChats.set(convId, chatObj);
        
        lucide.createIcons();

        if (conv.id) {
            loadMessages(chatBox, conv.id);

            // Clear unread status locally for better UX
            const convItem = document.querySelector(`.conversation-item[onclick*="'${convId}'"]`);
            if (convItem) {
                convItem.classList.remove('unread');
                const dot = convItem.querySelector('.unread-dot');
                if (dot) dot.remove();
            }
        }
    }

    async function setTypingStatus(convId, isTyping) {
        if (!convId) return;
        try {
            fetch('/api/messenger/typing', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ conversation_id: convId, is_typing: isTyping })
            });
        } catch (e) {}
    }

    async function pollForTyping(chatBox, convId) {
        if (!convId || chatBox.classList.contains('minimized')) return;
        try {
            const response = await fetch(`/api/messenger/typing/${convId}`);
            if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) return;
            const data = await response.json();
            const statusLabel = chatBox.querySelector('.chat-user-status');
            const typingIndicator = chatBox.querySelector('.typing-indicator');
            const typingText = typingIndicator.querySelector('.typing-text');
            const otherUserName = chatBox.querySelector('.chat-user-name').textContent;

            if (data.is_typing) {
                statusLabel.textContent = '';
                typingText.textContent = `${otherUserName} is typing...`;
                typingIndicator.style.display = 'flex';
                
                const container = chatBox.querySelector('.chat-box-messages');
                container.scrollTop = container.scrollHeight;
            } else {
                statusLabel.textContent = '';
                typingIndicator.style.display = 'none';
            }
        } catch (e) {}
    }

    async function pollForMessages(chatBox, convId) {
        const chatObj = openChats.get(convId);
        if (!chatObj || chatBox.classList.contains('minimized')) return;

        try {
            const response = await fetch(`/api/messenger/messages/${convId}?after_id=${chatObj.lastMsgId}`);
            if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) return;
            const messages = await response.json();
            if (messages.length > 0) {
                messages.forEach(msg => {
                    if (String(msg.sender_user_id) !== String(window.MY_ID)) {
                        addMessageToBox(chatBox, msg, 'received');
                    }
                    chatObj.lastMsgId = Math.max(chatObj.lastMsgId, msg.id);
                });
                loadConversations();
            }
        } catch (e) {}
    }

    async function loadMessages(chatBox, convId) {
        try {
            const response = await fetch(`/api/messenger/messages/${convId}`);
            if (!response.ok) return;
            const messages = await response.json();
            const container = chatBox.querySelector('.chat-box-messages');
            container.innerHTML = '';
            
            const chatObj = openChats.get(convId);
            
            messages.forEach(msg => {
                const type = (String(msg.sender_user_id) === String(window.MY_ID)) ? 'sent' : 'received';
                addMessageToBox(chatBox, msg, type);
                if (chatObj) chatObj.lastMsgId = Math.max(chatObj.lastMsgId, msg.id);
            });
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    function addMessageToBox(chatBox, msg, type) {
        const container = chatBox.querySelector('.chat-box-messages');
        
        // Prevent duplication by checking if message ID already exists in this box
        if (msg.id && container.querySelector(`[data-message-id="${msg.id}"]`)) {
            return;
        }

        // Ensure type is correct if not already provided
        if (!type) {
            type = (String(msg.sender_user_id) === String(window.MY_ID)) ? 'sent' : 'received';
        }

        const bubble = document.createElement('div');
        bubble.className = `message-bubble ${type}`;
        bubble.textContent = msg.body;
        if (msg.id) bubble.setAttribute('data-message-id', msg.id);
        container.appendChild(bubble);

        if (type === 'sent') {
            const status = document.createElement('div');
            status.className = 'message-status';
            status.textContent = msg.read_at ? 'Seen' : 'Sent';
            container.appendChild(status);
        }

        container.scrollTop = container.scrollHeight;
    }

    // Export to global scope
    window.openConversationById = async function(convId) {
        try {
            const response = await fetch('/api/messenger/conversations');
            if (!response.ok) return;
            const conversations = await response.json();
            const conv = conversations.find(c => c.id == convId);
            if (conv) {
                openChatBox(conv);
            }
        } catch (e) {
            console.error('Error opening conversation:', e);
        }
    };

    window.openMessengerWithRequest = async function(requester, requestId, suggestedTitle) {
        try {
            // Check if we already have an active conversation with this person to use its ID
            const response = await fetch('/api/messenger/conversations');
            if (!response.ok) return;
            const conversations = await response.json();
            const existingConv = conversations.find(c => c.other_user && String(c.other_user.id) === String(requester.id) && c.id);
            
            // We ALWAYS show the pending overlay for a new request notification,
            // but we pass the existing ID so that Acceptance connects correctly.
            const conv = {
                id: existingConv ? existingConv.id : null,
                other_user: {
                    id: requester.id,
                    name: requester.name,
                    avatar: requester.avatar_url || requester.avatar || '/assets/img/default.png',
                    is_doctor: false
                }
            };
            const pending = {
                id: requestId,
                suggestedTitle: suggestedTitle
            };
            openChatBox(conv, pending);
        } catch (e) {
            console.error('Error in openMessengerWithRequest:', e);
        }
    };

    function setupPendingUI(chatBox, conv, pendingRequest) {
        const convId = chatBox.dataset.conversationId;
        chatBox.classList.add('pending');
        const overlay = chatBox.querySelector('.chat-pending-overlay');
        overlay.style.display = 'flex';
        overlay.querySelector('.pending-user-name').textContent = conv.other_user.name;
        overlay.querySelector('.pending-request-text').textContent = `is requesting a ${pendingRequest.suggestedTitle} chat.`;
        
        overlay.querySelector('.decline-request-btn').onclick = async () => {
            if (!confirm('Are you sure you want to decline this request?')) return;
            try {
                const res = await fetch(`/api/help/decline/${pendingRequest.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                if (res.ok) {
                    chatBox.querySelector('.close-chat').click();
                }
            } catch (e) {
                console.error('Decline error:', e);
            }
        };
        
        overlay.querySelector('.accept-request-btn').onclick = async () => {
            const btn = overlay.querySelector('.accept-request-btn');
            btn.disabled = true;
            btn.innerText = 'Accepting...';
            
            try {
                const res = await fetch(`/api/help/accept/${pendingRequest.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                if (!res.ok) {
                    const text = await res.text();
                    console.error("Accept API Error:", res.status, text.substring(0, 100));
                    throw new Error("HTTP " + res.status);
                }
                const data = await res.json();
                if (data.success) {
                    chatBox.classList.remove('pending');
                    overlay.style.display = 'none';
                    chatBox.dataset.conversationId = data.conversation_id;
                    
                    // Update state in openChats
                    const chatObj = openChats.get(convId);
                    if (chatObj) {
                        clearInterval(chatObj.pollInterval);
                        clearInterval(chatObj.typingInterval);
                    }
                    
                    const newChatObj = {
                        element: chatBox,
                        lastMsgId: 0,
                        pollInterval: setInterval(() => pollForMessages(chatBox, data.conversation_id), 3000),
                        typingInterval: setInterval(() => pollForTyping(chatBox, data.conversation_id), 3000),
                        isTyping: false
                    };
                    openChats.set(data.conversation_id, newChatObj);
                    openChats.delete(convId);
                    
                    loadMessages(chatBox, data.conversation_id);
                    loadConversations();
                }
            } catch (e) {
                console.error('Accept error:', e);
                btn.disabled = false;
                btn.innerText = 'Accept';
            }
        };
    }

    // Initial Load
    loadConversations();
    
    // Auto-update unread badge on messenger icon
    const messengerBadge = document.createElement('span');
    messengerBadge.className = 'notif-badge messenger-badge';
    messengerBadge.style.display = 'none';
    if (messengerToggle) messengerToggle.appendChild(messengerBadge);

    async function updateMessengerBadge() {
        try {
            const response = await fetch('/api/unread-counts');
            if (!response.ok) return;
            const data = await response.json();
            
            const count = data.messages || 0;
            if (count > 0) {
                messengerBadge.textContent = count > 99 ? '99+' : count;
                messengerBadge.style.display = 'inline-flex';
                
                // Optional: Play sound or pulse if count increased
                if (parseInt(messengerBadge.dataset.oldCount || 0) < count) {
                   messengerBadge.classList.remove('pulse');
                   void messengerBadge.offsetWidth; // trigger reflow
                   messengerBadge.classList.add('pulse');
                }
                messengerBadge.dataset.oldCount = count;
            } else {
                messengerBadge.style.display = 'none';
                messengerBadge.dataset.oldCount = 0;
            }
        } catch (e) {}
    }

    updateMessengerBadge();
    setInterval(updateMessengerBadge, 10000);

    // ── Messenger Settings Logic ──────────────────────────────────
    const settingsBtn = document.querySelector('#messenger-settings-btn');
    const settingsDropdown = document.querySelector('#messenger-settings-dropdown');
    const activeStatusBtn = document.querySelector('#active-status-toggle-btn');
    const viewArchivedBtn = document.querySelector('#view-archived-chats');

    if (settingsBtn && settingsDropdown) {
        settingsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            settingsDropdown.style.display = settingsDropdown.style.display === 'block' ? 'none' : 'block';
            settingsBtn.classList.toggle('active');
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!settingsDropdown.contains(e.target) && e.target !== settingsBtn) {
                settingsDropdown.style.display = 'none';
                settingsBtn.classList.remove('active');
            }
        });
    }

    if (activeStatusBtn) {
        activeStatusBtn.addEventListener('click', async (e) => {
            e.stopPropagation();
            const isOn = activeStatusBtn.classList.contains('on');
            const newStatus = !isOn;
            
            // UI Feedback
            activeStatusBtn.classList.toggle('on', newStatus);
            activeStatusBtn.classList.toggle('off', !newStatus);
            activeStatusBtn.textContent = newStatus ? 'ON' : 'OFF';

            try {
                const response = await fetch('/api/messenger/settings/active-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                if (response.ok) {
                    console.log('Stealth Mode toggled:', newStatus);
                }
            } catch (err) { console.error('Error toggling active status:', err); }
        });
    }

    if (viewArchivedBtn) {
        viewArchivedBtn.addEventListener('click', () => {
            settingsDropdown.style.display = 'none';
            settingsBtn.classList.remove('active');
            enterArchivedMode();
        });
    }

    function enterArchivedMode() {
        isArchivedMode = true;
        messengerDrawer.classList.add('archived-mode');
        
        // Inject Archived Header
        const header = document.createElement('div');
        header.className = 'archived-header';
        header.id = 'archived-view-header';
        header.innerHTML = `
            <button class="back-to-chats-btn">
                <i data-lucide="chevron-left"></i> Back
            </button>
            <span style="font-weight: 700; font-size: 14px; opacity: 0.8;">Archived Chats</span>
        `;
        
        const existingHeader = document.querySelector('#archived-view-header');
        if (!existingHeader) {
            messengerDrawer.insertBefore(header, conversationList);
            lucide.createIcons();
            
            header.querySelector('.back-to-chats-btn').addEventListener('click', () => {
                exitArchivedMode();
            });
        }
        
        loadConversations(true);
    }

    function exitArchivedMode() {
        isArchivedMode = false;
        messengerDrawer.classList.remove('archived-mode');
        const header = document.querySelector('#archived-view-header');
        if (header) header.remove();
        loadConversations(false);
    }
});
