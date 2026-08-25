@extends('layouts.admin')

@section('title', 'Messages - Warehouse Chat')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Messages</li>
@endsection

@section('content')
<div class="row g-0" style="height: calc(100vh - var(--topbar-height) - 50px);">
    {{-- Conversations Sidebar --}}
    <div class="col-md-4 bg-light border-end d-flex flex-column" style="overflow: hidden;">
        <div class="p-3 border-bottom">
            <h5 class="mb-3">
                <i class="bi bi-chat-dots me-2"></i>Conversations
            </h5>
            <input 
                type="text" 
                class="form-control form-control-sm" 
                id="conversationSearch"
                placeholder="Search conversations..."
            >
        </div>

        <div class="flex-grow-1" id="conversationsList" style="overflow-y: auto;">
            <div class="p-3 text-center text-muted">
                <div class="spinner-border spinner-border-sm mb-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Loading conversations...</p>
            </div>
        </div>
    </div>

    {{-- Messages Panel --}}
    <div class="col-md-8 d-flex flex-column" style="overflow: hidden; background: #fff;">
        <div id="messagePanel" class="flex-grow-1 d-flex flex-column" style="display: none;">
            {{-- Conversation Header --}}
            <div class="p-3 border-bottom bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1" id="conversationTitle">—</h5>
                        <small class="text-muted" id="conversationInfo">—</small>
                    </div>
                    <div class="btn-group" role="group">
                        <button 
                            type="button" 
                            class="btn btn-sm btn-outline-secondary" 
                            id="muteBtn"
                            title="Mute this conversation"
                        >
                            <i class="bi bi-volume-mute"></i>
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-sm btn-outline-secondary" 
                            id="archiveBtn"
                            title="Archive this conversation"
                        >
                            <i class="bi bi-archive"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Messages List --}}
            <div class="flex-grow-1 p-3" id="messagesList" style="overflow-y: auto; background: #f8f9fa;">
                <div class="text-center text-muted">
                    <p>Loading messages...</p>
                </div>
            </div>

            {{-- Message Input Form --}}
            <div class="p-3 border-top bg-white">
                <form id="messageForm">
                    @csrf
                    <div class="input-group">
                        <textarea 
                            name="message" 
                            class="form-control"
                            id="messageInput"
                            placeholder="Type your message..."
                            rows="3"
                            style="resize: none; min-height: 45px; max-height: 120px;"
                        ></textarea>
                        <button 
                            type="submit" 
                            class="btn btn-primary"
                            id="sendBtn"
                        >
                            <i class="bi bi-send"></i> Send
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        Press <kbd>Shift</kbd> + <kbd>Enter</kbd> to send
                    </small>
                </form>
            </div>
        </div>

        {{-- Empty State --}}
        <div id="emptyState" class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
            <i class="bi bi-chat-dots" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
            <p>Select a conversation to start messaging</p>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .conversation-item {
        padding: 12px;
        border-bottom: 1px solid #e3e6f0;
        cursor: pointer;
        transition: all 0.2s;
    }

    .conversation-item:hover {
        background: #f8f9fa;
    }

    .conversation-item.active {
        background: #e7f3ff;
        border-left: 3px solid #3498db;
    }

    .conversation-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .conversation-item-title {
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .conversation-item-time {
        font-size: 0.85rem;
        color: #95a5a6;
    }

    .conversation-item-preview {
        font-size: 0.9rem;
        color: #7f8c8d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 4px;
    }

    .conversation-item-unread {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .unread-badge {
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
    }

    {{-- Message Styles --}}
    .message-group {
        margin-bottom: 20px;
    }

    .message-date-separator {
        text-align: center;
        margin: 20px 0;
        font-size: 0.85rem;
        color: #95a5a6;
    }

    .message-date-separator::before,
    .message-date-separator::after {
        content: '';
        display: inline-block;
        width: 40px;
        height: 1px;
        background: #e3e6f0;
        vertical-align: middle;
        margin: 0 10px;
    }

    .message {
        display: flex;
        margin-bottom: 12px;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message.sent {
        justify-content: flex-end;
    }

    .message.received {
        justify-content: flex-start;
    }

    .message-bubble {
        max-width: 70%;
        padding: 10px 14px;
        border-radius: 12px;
        word-wrap: break-word;
        word-break: break-word;
    }

    .message.sent .message-bubble {
        background: #3498db;
        color: white;
        border-bottom-right-radius: 4px;
    }

    .message.received .message-bubble {
        background: #ecf0f1;
        color: #2c3e50;
        border-bottom-left-radius: 4px;
    }

    .message-info {
        font-size: 0.75rem;
        color: #95a5a6;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .message.sent .message-info {
        justify-content: flex-end;
    }

    .message-timestamp {
        white-space: nowrap;
    }

    .read-receipt {
        font-size: 0.8rem;
    }

    {{-- Responsive --}}
    @media (max-width: 768px) {
        .col-md-4, .col-md-8 {
            flex: 1;
        }

        .message-bubble {
            max-width: 85%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
class WarehouseChat {
    constructor() {
        this.currentConversationId = null;
        this.currentPage = 1;
        this.messagesPerPage = 50;
        this.loadingMessages = false;
        this.searchQuery = '';
        this.conversations = [];
        this.messagesByConversation = {};
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.loadConversations();
        this.subscribeToWebSocket();
    }

    attachEventListeners() {
        {{-- Conversation search --}}
        document.getElementById('conversationSearch').addEventListener('input', (e) => {
            this.searchQuery = e.target.value.toLowerCase();
            this.filterConversations();
        });

        {{-- Message form --}}
        document.getElementById('messageForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.sendMessage();
        });

        {{-- Message input: Shift+Enter to send --}}
        document.getElementById('messageInput').addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        {{-- Auto-resize textarea --}}
        const textarea = document.getElementById('messageInput');
        textarea.addEventListener('input', () => {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        });

        {{-- Mute/Archive buttons --}}
        document.getElementById('muteBtn').addEventListener('click', () => this.toggleMute());
        document.getElementById('archiveBtn').addEventListener('click', () => this.archiveConversation());

        {{-- Messages scroll for pagination --}}
        document.getElementById('messagesList').addEventListener('scroll', (e) => {
            if (e.target.scrollTop === 0 && !this.loadingMessages) {
                this.loadMoreMessages();
            }
        });
    }

    async loadConversations() {
        try {
            const response = await fetch('{{ route("admin.chat.list-conversations") }}', {
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                this.conversations = data.conversations || [];
                this.renderConversations();
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
            alert('Failed to load conversations');
        }
    }

    filterConversations() {
        const filtered = this.conversations.filter(conv => {
            const title = (conv.warehouse_name || 'Super Admin').toLowerCase();
            return title.includes(this.searchQuery);
        });
        this.renderConversations(filtered);
    }

    renderConversations(conversationsToRender = this.conversations) {
        const container = document.getElementById('conversationsList');

        if (conversationsToRender.length === 0) {
            container.innerHTML = '<div class="p-3 text-center text-muted">No conversations found</div>';
            return;
        }

        container.innerHTML = conversationsToRender.map(conv => `
            <div 
                class="conversation-item ${this.currentConversationId === conv.id ? 'active' : ''}"
                data-conversation-id="${conv.id}"
                onclick="chat.selectConversation(${conv.id}, '${conv.warehouse_name || 'Super Admin'}', ${conv.unread_count || 0})"
            >
                <div class="conversation-item-header">
                    <p class="conversation-item-title">${this.escapeHtml(conv.warehouse_name || 'Super Admin')}</p>
                    <span class="conversation-item-time">${conv.latest_message_time || 'no messages'}</span>
                </div>
                <div class="conversation-item-preview">${this.escapeHtml(conv.latest_message || 'No messages yet')}</div>
                <div class="conversation-item-unread">
                    <small class="text-muted">${conv.warehouse_admin_name ? conv.warehouse_admin_name : (conv.latest_message_sender ? conv.latest_message_sender : 'No admin assigned')}</small>
                    ${conv.unread_count > 0 ? `<span class="unread-badge">${conv.unread_count}</span>` : ''}
                </div>
            </div>
        `).join('');
    }

    async selectConversation(conversationId, warehouseName, unreadCount) {
        this.currentConversationId = conversationId;
        this.currentPage = 1;
        this.messagesByConversation[conversationId] = [];

        {{-- Update UI --}}
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[data-conversation-id="${conversationId}"]`).classList.add('active');

        {{-- Show message panel --}}
        document.getElementById('messagePanel').style.display = 'flex';
        document.getElementById('emptyState').style.display = 'none';

        {{-- Update header --}}
        document.getElementById('conversationTitle').textContent = warehouseName || 'Super Admin';
        document.getElementById('conversationInfo').textContent = `${unreadCount} unread message${unreadCount !== 1 ? 's' : ''}`;

        {{-- Load messages --}}
        await this.loadMessages();

        {{-- Mark as read --}}
        await this.markAllAsRead();

        {{-- Update conversation in list --}}
        const conv = this.conversations.find(c => c.id === conversationId);
        if (conv) {
            conv.unread_count = 0;
            this.renderConversations();
        }
    }

    async loadMessages() {
        if (!this.currentConversationId) return;

        try {
            const url = new URL(`{{ route('admin.chat.get-messages', ':id') }}`.replace(':id', this.currentConversationId));
            url.searchParams.append('page', this.currentPage);
            url.searchParams.append('per_page', this.messagesPerPage);

            const response = await fetch(url, {
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                if (this.currentPage === 1) {
                    this.messagesByConversation[this.currentConversationId] = data.data || [];
                    this.renderMessages();
                    this.scrollToBottom();
                }
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    async loadMoreMessages() {
        if (!this.currentConversationId || this.loadingMessages) return;

        this.loadingMessages = true;
        this.currentPage++;

        try {
            const url = new URL(`{{ route('admin.chat.get-messages', ':id') }}`.replace(':id', this.currentConversationId));
            url.searchParams.append('page', this.currentPage);
            url.searchParams.append('per_page', this.messagesPerPage);

            const response = await fetch(url, {
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success && data.data.length > 0) {
                this.messagesByConversation[this.currentConversationId] = [
                    ...data.data,
                    ...this.messagesByConversation[this.currentConversationId]
                ];
                this.renderMessages();
            } else {
                this.currentPage--; {{-- Reset if no more messages --}}
            }
        } catch (error) {
            console.error('Error loading more messages:', error);
            this.currentPage--;
        } finally {
            this.loadingMessages = false;
        }
    }

    renderMessages() {
        const container = document.getElementById('messagesList');
        const messages = this.messagesByConversation[this.currentConversationId] || [];

        if (messages.length === 0) {
            container.innerHTML = '<div class="text-center text-muted">No messages yet. Start the conversation!</div>';
            return;
        }

        let html = '';
        let lastDate = null;

        messages.forEach(message => {
            const messageDate = new Date(message.created_at).toLocaleDateString();

            {{-- Add date separator --}}
            if (lastDate !== messageDate) {
                html += `<div class="message-date-separator">${messageDate}</div>`;
                lastDate = messageDate;
            }

            const isSent = message.sender_id === {{ auth()->user()->id }};
            const senderName = message.sender?.name || 'Unknown';
            const timestamp = this.formatTime(message.created_at, true);
            const isRead = message.read_at !== null;

            html += `
                <div class="message ${isSent ? 'sent' : 'received'}" data-message-id="${message.id}">
                    <div>
                        <div class="message-bubble">
                            ${this.escapeHtml(message.message)}
                        </div>
                        <div class="message-info">
                            <span class="message-timestamp">${timestamp}</span>
                            ${isSent && isRead ? '<span class="read-receipt">✓✓</span>' : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    async sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();

        if (!message || !this.currentConversationId) return;

        {{-- Disable send button --}}
        const sendBtn = document.getElementById('sendBtn');
        sendBtn.disabled = true;

        try {
            const url = `{{ route('admin.chat.send-message', ':id') }}`.replace(':id', this.currentConversationId);

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();

            if (data.success) {
                {{-- Add message to list optimistically --}}
                input.value = '';
                input.style.height = 'auto';

                {{-- Message will be added via WebSocket event --}}
                {{-- or we can add it here for instant feedback --}}
            } else {
                alert(data.message || 'Failed to send message');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            alert('Failed to send message');
        } finally {
            sendBtn.disabled = false;
        }
    }

    async markAllAsRead() {
        if (!this.currentConversationId) return;

        try {
            await fetch(`{{ route('admin.chat.mark-all-as-read', ':id') }}`.replace(':id', this.currentConversationId), {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
        } catch (error) {
            console.error('Error marking as read:', error);
        }
    }

    async toggleMute() {
        if (!this.currentConversationId) return;

        try {
            await fetch(`{{ route('admin.chat.mute', ':id') }}`.replace(':id', this.currentConversationId), {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const btn = document.getElementById('muteBtn');
            btn.classList.toggle('btn-danger');
            btn.classList.toggle('btn-outline-secondary');
        } catch (error) {
            console.error('Error toggling mute:', error);
        }
    }

    async archiveConversation() {
        if (!this.currentConversationId) return;

        if (!confirm('Archive this conversation?')) return;

        try {
            const response = await fetch(`{{ route('admin.chat.archive', ':id') }}`.replace(':id', this.currentConversationId), {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                {{-- Remove from list and reload --}}
                this.currentConversationId = null;
                this.loadConversations();
                document.getElementById('messagePanel').style.display = 'none';
                document.getElementById('emptyState').style.display = 'flex';
            }
        } catch (error) {
            console.error('Error archiving conversation:', error);
        }
    }

    scrollToBottom() {
        const container = document.getElementById('messagesList');
        setTimeout(() => {
            container.scrollTop = container.scrollHeight;
        }, 0);
    }

    subscribeToWebSocket() {
        {{-- Only subscribe if Echo is available --}}
        if (typeof Echo === 'undefined') {
            console.warn('Echo not initialized - real-time updates unavailable');
            return;
        }

        {{-- Subscribe to user channel for general notifications --}}
        if (window.Echo) {
            window.Echo.private(`App.Models.User.{{ auth()->user()->id }}`)
                .notification((notification) => {
                    {{-- Handle notifications if needed --}}
                    console.log('Notification:', notification);
                });
        }
    }

    addMessageToUI(message) {
        {{-- Only add if we're in this conversation --}}
        if (message.conversation_id !== this.currentConversationId) return;

        {{-- Add to messages array --}}
        if (!this.messagesByConversation[this.currentConversationId]) {
            this.messagesByConversation[this.currentConversationId] = [];
        }

        this.messagesByConversation[this.currentConversationId].push(message);

        {{-- Re-render --}}
        this.renderMessages();
        this.scrollToBottom();

        {{-- Mark as read --}}
        this.markAllAsRead();
    }

    formatTime(dateString, includeTime = false) {
        if (!dateString) return '';

        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays < 7) return `${diffDays}d ago`;

        {{-- Return formatted date --}}
        const options = includeTime 
            ? { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }
            : { month: 'short', day: 'numeric' };

        return date.toLocaleDateString('en-US', options);
    }

    escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

{{-- Initialize on page load --}}
let chat;
document.addEventListener('DOMContentLoaded', () => {
    chat = new WarehouseChat();
});
</script>

{{-- Real-time WebSocket subscription (if available) --}}
@if(env('BROADCAST_DRIVER') !== 'log' && env('BROADCAST_DRIVER') !== 'null')
<script>
    {{-- Subscribe to chat events when Echo is ready --}}
    if (typeof Echo !== 'undefined') {
        {{-- Wait a bit for Echo to initialize --}}
        setTimeout(() => {
            {{-- Listen for new messages (we'll get these from the server on page load) --}}
            {{-- This is for real-time updates when messages arrive --}}
        }, 1000);
    }
</script>
@endif
@endpush
