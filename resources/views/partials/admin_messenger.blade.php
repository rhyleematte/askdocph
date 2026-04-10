<div id="admin-messenger-root" class="messenger-root">
    {{-- Chat Tray (the list of active chat boxes at the bottom) --}}
    <div id="admin-chat-tray" class="chat-tray"></div>

    {{-- Conversations Sidebar/Drawer (toggled by the header icon) --}}
    <div id="admin-messenger-drawer" class="messenger-drawer">
        <div class="drawer-header">
            <h3>Admin Messages</h3>
            <button id="close-admin-messenger-drawer" class="icon-btn"><i data-lucide="x"></i></button>
        </div>
        <div class="drawer-search">
            <i data-lucide="search"></i>
            <input type="text" id="admin-messenger-user-search" placeholder="Search admins...">
            <div id="admin-messenger-search-results" class="search-results-popover"></div>
        </div>
        <div id="admin-conversation-list" class="conversation-list">
            {{-- Conversations will be loaded here via JS --}}
        </div>
    </div>
</div>

<template id="admin-chat-box-template">
    <div class="chat-box" data-conversation-id="">
        <div class="chat-box-header">
            <div class="chat-box-user">
                <img src="" alt="" class="chat-avatar">
                <div class="chat-user-info">
                    <span class="chat-user-name"></span>
                    <span class="chat-user-status"></span>
                </div>
            </div>
            <div class="chat-box-actions">
                <button class="minimize-chat"><i data-lucide="minus"></i></button>
                <button class="close-chat"><i data-lucide="x"></i></button>
            </div>
        </div>
        <div class="chat-box-messages">
            {{-- Messages load here --}}
        </div>
        <div class="typing-indicator" style="display:none;">
            <div class="dot"></div>
            <span class="typing-text">someone is typing...</span>
        </div>
        <div class="chat-box-input">
            <textarea placeholder="Type a message..."></textarea>
            <button class="send-message-btn"><i data-lucide="send"></i></button>
        </div>
    </div>
</template>
