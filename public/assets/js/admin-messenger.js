/**
 * Admin Messenger JS - A clone of messenger.js targeting admin endpoints
 */
document.addEventListener('DOMContentLoaded', function() {
    const messengerToggle = document.querySelector('.admin-messenger-toggle');
    const messengerDrawer = document.querySelector('#admin-messenger-drawer');
    const closeDrawer = document.querySelector('#close-admin-messenger-drawer');
    const conversationList = document.querySelector('#admin-conversation-list');
    const userSearchInput = document.querySelector('#admin-messenger-user-search');
    const searchResults = document.querySelector('#admin-messenger-search-results');
    const chatTray = document.querySelector('#admin-chat-tray');
    const chatBoxTemplate = document.querySelector('#admin-chat-box-template');

    // Make sure we are actually on a page that HAS the admin messenger
    if (!messengerDrawer || !conversationList || !chatBoxTemplate) {
        return;
    }

    let openChats = new Map(); // conversationId -> { element, lastMsgId, pollInterval, typingInterval, isTyping }
    let convPollInterval = null;

    // Toggle Drawer
    if (messengerToggle) {
        messengerToggle.addEventListener('click', (e) => {
            e.preventDefault();
            messengerDrawer.classList.toggle('open');
            if (messengerDrawer.classList.contains('open')) {
                loadConversations();
                if (!convPollInterval) {
                    convPollInterval = setInterval(loadConversations, 5000);
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
    async function loadConversations() {
        if (document.activeElement && (document.activeElement.id === 'admin-messenger-user-search')) return;

        try {
            const response = await fetch('/admin/api/messenger/conversations');
            const conversations = await response.json();
            
            conversationList.innerHTML = '';
            conversations.forEach(conv => {
                const item = document.createElement('div');
                item.className = 'conversation-item';
                
                let lastMsg = 'No messages yet';
                if (conv.latest_message) {
                    lastMsg = conv.latest_message.body;
                }

                item.innerHTML = `
                    <div class="chat-avatar-wrapper">
                        <img src="${conv.other_user.avatar}" class="chat-avatar">
                        <div class="mutual-dot" title="Admin"></div>
                    </div>
                    <div class="conv-info">
                        <div class="conv-name" style="font-weight: 800;">
                            ${conv.other_user.name} <span style="font-size: 10px; color: var(--teal, #0c8f98); font-weight: 900; background: rgba(12, 143, 152, 0.1); padding: 2px 6px; border-radius: 4px; margin-left: 4px;">ADMIN</span>
                        </div>
                        <div class="conv-last-msg ${conv.latest_message && conv.latest_message.is_unread ? 'unread' : ''}">${lastMsg}</div>
                    </div>
                `;

                // Add unread styling if needed
                if (conv.latest_message && conv.latest_message.is_unread) {
                    item.style.background = 'var(--hover)';
                }

                item.addEventListener('click', () => {
                    openChatBox(conv);
                });

                conversationList.appendChild(item);
            });
            if(typeof lucide !== 'undefined') lucide.createIcons();
        } catch (error) {
            console.error('Error loading conversations:', error);
        }
    }

    // Search Users
    userSearchInput.addEventListener('input', async (e) => {
        const query = e.target.value;
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/admin/api/messenger/search?q=${query}`);
            const users = await response.json();

            searchResults.innerHTML = '';
            if (users.length > 0) {
                users.forEach(user => {
                    const item = document.createElement('div');
                    item.className = 'search-result-item';
                    
                    item.innerHTML = `
                        <img src="${user.avatar}" class="chat-avatar">
                        <div class="conv-info">
                            <div class="conv-name">
                                ${user.name}
                            </div>
                            <div class="conv-last-msg">Admin</div>
                        </div>
                    `;
                    item.addEventListener('click', () => {
                        openChatBox({ other_user: user, id: user.id }); // Use user ID directly as conversation ID since admins map 1:1
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
            console.error('Error searching admins:', error);
        }
    });

    // Open Chat Box
    function openChatBox(conv) {
        const convId = conv.id; // Treat the other admin's ID as the conversation ID essentially
        if (openChats.has(convId)) {
            const chatObj = openChats.get(convId);
            chatObj.element.classList.remove('minimized');
            return;
        }

        const clone = chatBoxTemplate.content.cloneNode(true);
        const chatBox = clone.querySelector('.chat-box');
        chatBox.dataset.conversationId = convId;
        chatBox.dataset.receiverId = conv.other_user.id;

        chatBox.querySelector('.chat-user-name').textContent = conv.other_user.name;
        chatBox.querySelector('.chat-avatar').src = conv.other_user.avatar;

        // Actions
        chatBox.querySelector('.close-chat').addEventListener('click', (e) => {
            e.stopPropagation();
            const chatObj = openChats.get(convId);
            if (chatObj) {
                clearInterval(chatObj.pollInterval);
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
                const response = await fetch('/admin/api/messenger/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        receiver_id: conv.other_user.id,
                        body: body
                    })
                });

                const msg = await response.json();
                input.value = '';
                addMessageToBox(chatBox, msg, 'sent');
                
                const chatObj = openChats.get(convId);
                if (chatObj) chatObj.lastMsgId = msg.id;
                
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

        chatTray.appendChild(chatBox);
        
        const chatObj = {
            element: chatBox,
            lastMsgId: 0,
            pollInterval: setInterval(() => pollForMessages(chatBox, convId), 3000),
            isTyping: false
        };
        openChats.set(convId, chatObj);
        
        if(typeof lucide !== 'undefined') lucide.createIcons();

        loadMessages(chatBox, convId);
    }

    async function pollForMessages(chatBox, convId) {
        const chatObj = openChats.get(convId);
        if (!chatObj || chatBox.classList.contains('minimized')) return;

        try {
            const response = await fetch(`/admin/api/messenger/messages/${convId}?after_id=${chatObj.lastMsgId}`);
            const messages = await response.json();
            if (messages.length > 0) {
                messages.forEach(msg => {
                    if (msg.sender_user_id != window.MY_ID) {
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
            const response = await fetch(`/admin/api/messenger/messages/${convId}`);
            const messages = await response.json();
            const container = chatBox.querySelector('.chat-box-messages');
            container.innerHTML = '';
            
            const chatObj = openChats.get(convId);
            
            messages.forEach(msg => {
                const type = msg.sender_user_id == window.MY_ID ? 'sent' : 'received';
                addMessageToBox(chatBox, msg, type);
                if (chatObj) chatObj.lastMsgId = Math.max(chatObj.lastMsgId, msg.id);
            });
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    function addMessageToBox(chatBox, msg, type) {
        const container = chatBox.querySelector('.chat-box-messages');
        const bubble = document.createElement('div');
        bubble.className = `message-bubble ${type}`;
        bubble.textContent = msg.body;
        container.appendChild(bubble);
        container.scrollTop = container.scrollHeight;
    }
});
