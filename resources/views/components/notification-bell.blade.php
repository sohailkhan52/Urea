@php
    $unreadCount = auth()->check() ? app(\App\Services\NotificationService::class)->getUnreadCount(auth()->user()) : 0;
@endphp

<div class="notification-bell-container">
    <!-- Bell Icon with Badge -->
    <button class="btn btn-link position-relative" data-bs-toggle="dropdown" id="notificationBell" title="Notifications">
        <i class="bi bi-bell"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge">
                {{ $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Menu -->
    <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationBell" style="min-width: 350px; max-width: 400px;">
        <!-- Header -->
        <div class="dropdown-header d-flex justify-content-between align-items-center">
            <span class="fw-bold">Notifications</span>
            <button type="button" class="btn btn-sm btn-link text-muted" id="markAllRead" title="Mark all as read">
                <small>Mark all read</small>
            </button>
        </div>

        <div class="dropdown-divider"></div>

        <!-- Notifications List -->
        <div id="notificationsList" class="notification-list" style="max-height: 400px; overflow-y: auto;">
            <div class="text-center text-muted p-3">
                <small>Loading notifications...</small>
            </div>
        </div>

        <div class="dropdown-divider"></div>

        <!-- Footer -->
        <div class="dropdown-item text-center">
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-primary w-100">
                View All Notifications
            </a>
        </div>
    </div>
</div>

<style>
    .notification-bell-container {
        position: relative;
    }

    .notification-bell-container .btn {
        color: #2c3e50;
        font-size: 1.2rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .notification-bell-container .btn:hover {
        color: #3498db;
        transform: scale(1.1);
    }

    .notification-dropdown {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
    }

    .notification-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .notification-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }

    .notification-item:hover {
        background-color: #f8f9fa;
    }

    .notification-item.unread {
        background-color: #f0f7ff;
        border-left: 3px solid #3498db;
    }

    .notification-item-icon {
        font-size: 1.3rem;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #7f8c8d;
        flex-shrink: 0;
    }

    .notification-item-content {
        flex: 1;
        min-width: 0;
    }

    .notification-item-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
        font-size: 0.9rem;
    }

    .notification-item-body {
        color: #7f8c8d;
        font-size: 0.85rem;
        margin-bottom: 4px;
        word-wrap: break-word;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .notification-item-time {
        font-size: 0.75rem;
        color: #95a5a6;
    }

    .notification-item-actions {
        display: flex;
        gap: 8px;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .notification-item:hover .notification-item-actions {
        opacity: 1;
    }

    .notification-item-action-btn {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: #7f8c8d;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .notification-item-action-btn:hover {
        color: #e74c3c;
    }

    .empty-state {
        text-align: center;
        padding: 30px 16px;
        color: #95a5a6;
    }

    .empty-state-icon {
        font-size: 2.5rem;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .empty-state-text {
        font-size: 0.9rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationBell = document.getElementById('notificationBell');
    const notificationsList = document.getElementById('notificationsList');
    const notificationBadge = document.getElementById('notificationBadge');
    const markAllRead = document.getElementById('markAllRead');

    // Load notifications when dropdown opens
    notificationBell.addEventListener('click', function() {
        loadNotifications();
    });

    // Mark all as read
    markAllRead.addEventListener('click', function(e) {
        e.preventDefault();
        markAllAsRead();
    });

    function loadNotifications() {
        fetch('{{ route("admin.notifications.api.recent") }}', {
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderNotifications(data.notifications);
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
    }

    function renderNotifications(notifications) {
        if (notifications.length === 0) {
            notificationsList.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <div class="empty-state-text">
                        <p>No new notifications</p>
                    </div>
                </div>
            `;
            return;
        }

        notificationsList.innerHTML = notifications.map(notification => `
            <div class="notification-item unread" data-id="${notification.id}">
                <div class="notification-item-icon">
                    <i class="bi ${notification.icon}"></i>
                </div>
                <div class="notification-item-content">
                    <div class="notification-item-title">${escapeHtml(notification.title)}</div>
                    <div class="notification-item-body">${escapeHtml(notification.body)}</div>
                    <div class="notification-item-time">${notification.created_at_formatted}</div>
                </div>
                <div class="notification-item-actions">
                    <button class="notification-item-action-btn mark-read-btn" title="Mark as read">
                        <i class="bi bi-check"></i>
                    </button>
                    <button class="notification-item-action-btn delete-btn" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');

        // Attach event listeners
        document.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const notificationItem = btn.closest('.notification-item');
                const id = notificationItem.dataset.id;
                markAsRead(id);
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const notificationItem = btn.closest('.notification-item');
                const id = notificationItem.dataset.id;
                deleteNotification(id);
            });
        });

        // Click on notification to go to related item
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notification = notifications.find(n => n.id == this.dataset.id);
                if (notification && notification.route) {
                    window.location.href = notification.route;
                }
            });
        });
    }

    function markAsRead(notificationId) {
        fetch(`{{ route('admin.notifications.api.mark-read', ':id') }}`.replace(':id', notificationId), {
            method: 'POST',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateBadge(data.unread_count);
                loadNotifications();
            }
        })
        .catch(error => console.error('Error marking as read:', error));
    }

    function markAllAsRead() {
        fetch('{{ route("admin.notifications.api.mark-all-read") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateBadge(0);
                loadNotifications();
            }
        })
        .catch(error => console.error('Error marking all as read:', error));
    }

    function deleteNotification(notificationId) {
        fetch(`{{ route('admin.notifications.api.delete', ':id') }}`.replace(':id', notificationId), {
            method: 'POST',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateBadge(data.unread_count);
                loadNotifications();
            }
        })
        .catch(error => console.error('Error deleting notification:', error));
    }

    function updateBadge(count) {
        if (count > 0) {
            if (!notificationBadge) {
                const badge = document.createElement('span');
                badge.id = 'notificationBadge';
                badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                notificationBell.appendChild(badge);
            }
            document.getElementById('notificationBadge').textContent = count;
        } else {
            if (notificationBadge) {
                notificationBadge.remove();
            }
        }
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Subscribe to real-time notifications if Echo is available
    @if(env('BROADCAST_DRIVER') !== 'log' && env('BROADCAST_DRIVER') !== 'null')
        if (typeof Echo !== 'undefined' && {{ auth()->user()->id ?? 'null' }} !== null) {
            Echo.private('notifications.user.{{ auth()->user()->id }}')
                .listen('notification.created', (data) => {
                    updateBadge({{ $unreadCount }} + 1);
                    loadNotifications();
                });
        }
    @endif
});
</script>
