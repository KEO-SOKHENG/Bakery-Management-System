@extends('layouts.app')

@section('title', 'Notification Center - Bakery Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ @filemtime(public_path('css/notifications.css')) ?: '1.0' }}">
@endpush

@section('content')
<div class="notifications-page-container">
    <!-- Header Area -->
    <div class="notif-header-area">
        <div class="notif-header-left">
            <h1>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.2">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                </svg>
                <span data-lang-key="notification_center">Notification Center</span>
            </h1>
            <p data-lang-key="notification_center_subtitle">Real-time alerts, operational updates, and event notifications.</p>
        </div>

        <div class="notif-action-group">
            <x-button variant="secondary" id="page_refresh_alerts" onclick="window.location.reload();">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                <span data-lang-key="refresh">{{ __('messages.refresh') ?? 'Refresh' }}</span>
            </x-button>

            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('notifications.promote'))
                <x-button variant="warning" id="btn_open_promotion_modal" onclick="openPromotionModal();">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span data-lang-key="broadcast_announcement">{{ __('messages.broadcast_announcement') ?? 'Broadcast Announcement' }}</span>
                </x-button>
            @endif

            @if($stats['unread'] > 0)
                <x-button variant="primary" id="page_mark_all_read">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <span data-lang-key="mark_all_read">{{ __('messages.mark_all_read') ?? 'Mark All as Read' }}</span>
                </x-button>
            @endif
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="notif-stats-grid">
        <div class="notif-stat-card">
            <div class="notif-stat-icon info">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            </div>
            <div class="notif-stat-info">
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label" data-lang-key="total_notifications">Total Notifications</div>
            </div>
        </div>

        <div class="notif-stat-card">
            <div class="notif-stat-icon warning">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
            </div>
            <div class="notif-stat-info">
                <div class="stat-value" id="stat_unread_count">{{ $stats['unread'] }}</div>
                <div class="stat-label" data-lang-key="unread_alerts">Unread Alerts</div>
            </div>
        </div>

        <div class="notif-stat-card">
            <div class="notif-stat-icon danger">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 1 2 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="notif-stat-info">
                <div class="stat-value">{{ $stats['critical'] }}</div>
                <div class="stat-label" data-lang-key="critical_action_required">Critical / Out of Stock</div>
            </div>
        </div>

        <div class="notif-stat-card">
            <div class="notif-stat-icon success">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            </div>
            <div class="notif-stat-info">
                <div class="stat-value">{{ $stats['inventory'] }}</div>
                <div class="stat-label" data-lang-key="inventory_alerts">Inventory Alerts</div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs & Search Form -->
    @php
        $selectedStatus = request('status', 'all');
        $selectedType = request('type', 'all');
        $readCount = max(0, $stats['total'] - $stats['unread']);
    @endphp

    <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem;">
        <!-- Status Filter Tabs -->
        <div class="orders-filter-bar" id="notif_status_tabs">
            <a href="{{ route('admin.notifications', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
               class="filter-btn {{ (!request()->filled('status') || request('status') === 'all') ? 'active' : '' }}">
                <span data-lang-key="all">{{ __('messages.all') ?? 'All' }}</span> ({{ $stats['total'] }})
            </a>
            <a href="{{ route('admin.notifications', array_merge(request()->except('status', 'page'), ['status' => 'unread'])) }}"
               class="filter-btn {{ request('status') === 'unread' ? 'active' : '' }}">
                <span data-lang-key="unread">{{ __('messages.unread') ?? 'Unread' }}</span> ({{ $stats['unread'] }})
            </a>
            <a href="{{ route('admin.notifications', array_merge(request()->except('status', 'page'), ['status' => 'read'])) }}"
               class="filter-btn {{ request('status') === 'read' ? 'active' : '' }}">
                <span data-lang-key="read">{{ __('messages.read') ?? 'Read' }}</span> ({{ $readCount }})
            </a>
        </div>
    </div>

    <!-- Live Search & Detailed Filters Form -->
    <form action="{{ route('admin.notifications') }}" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; align-items: center;">
        @if(request('status') && request('status') !== 'all')
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif

        <div style="flex: 1; min-width: 260px; position: relative;">
            <input type="text" name="search" id="notif_search_input" value="{{ request('search') }}"
                   placeholder="Search alerts by title or message..."
                   class="form-control-input" style="padding-left: 2.6rem;">
            <svg style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>

        <div style="min-width: 170px;">
            <select name="type" class="form-control-select" onchange="this.form.submit()">
                <option value="all" {{ (!request()->filled('type') || request('type') === 'all') ? 'selected' : '' }} data-lang-key="all_categories">{{ __('messages.all_categories') ?? 'All Categories' }}</option>
                <option value="inventory" {{ request('type') === 'inventory' ? 'selected' : '' }} data-lang-key="inventory">{{ __('messages.inventory') ?? 'Inventory' }}</option>
                <option value="order" {{ request('type') === 'order' ? 'selected' : '' }} data-lang-key="orders">{{ __('messages.orders') ?? 'Orders' }}</option>
                <option value="production" {{ request('type') === 'production' ? 'selected' : '' }} data-lang-key="production">{{ __('messages.production') ?? 'Production' }}</option>
                <option value="purchase_order" {{ request('type') === 'purchase_order' ? 'selected' : '' }} data-lang-key="purchase_orders">{{ __('messages.purchase_orders') ?? 'Purchase Orders' }}</option>
                <option value="promotion" {{ request('type') === 'promotion' ? 'selected' : '' }} data-lang-key="promotions">{{ __('messages.promotions') ?? 'Promotions' }}</option>
            </select>
        </div>

        @if(request()->hasAny(['search', 'type']) && (request('search') || (request('type') && request('type') !== 'all')))
            <a href="{{ route('admin.notifications', request('status') ? ['status' => request('status')] : []) }}" style="font-size: 0.85rem; font-weight: 700; color: #ef4444; text-decoration: none; padding: 0.5rem;" data-lang-key="clear_filters">
                {{ __('messages.clear_filters') ?? 'Clear Filters' }}
            </a>
        @endif
    </form>

    <!-- Notification Feed -->
    <div class="notif-feed-card">
        <div class="notif-feed-header">
            <span class="notif-feed-title" data-lang-key="notification_activity">Notification Activity</span>
            <span style="font-size: 0.8rem; color: var(--notif-text-muted);">
                Showing {{ $notifications->firstItem() ?? 0 }} - {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }}
            </span>
        </div>

        <div class="notif-feed-list" id="feed_container">
            @forelse($notifications as $notif)
                @php
                    $isUnread = !$notif->isRead();
                    $sev = $notif->severity ?? 'info';
                    $typeClass = $notif->type ?? 'system';
                @endphp
                <div class="notif-feed-row {{ $isUnread ? 'unread' : '' }}" id="notif_row_{{ $notif->id }}" data-id="{{ $notif->id }}">
                    <div class="notif-row-icon {{ $sev }}">
                        @if($sev === 'danger' || $sev === 'critical')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 1 2 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        @elseif($sev === 'warning')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        @elseif($sev === 'success')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        @else
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        @endif
                    </div>

                    <div class="notif-row-body">
                        <div class="notif-row-top">
                            <div class="notif-row-title-area">
                                <span class="notif-row-title">{{ $notif->title }}</span>
                                <span class="notif-type-tag {{ $typeClass }}">{{ str_replace('_', ' ', $notif->type) }}</span>
                            </div>
                            <span class="notif-row-time">{{ $notif->time_ago }}</span>
                        </div>

                        <div class="notif-row-desc">
                            {{ $notif->message }}
                        </div>

                        <div class="notif-row-actions">
                            @if($notif->action_url)
                                <a href="{{ $notif->action_url }}" class="btn-notif-link" onclick="markRowRead({{ $notif->id }});">
                                    <span data-lang-key="view_details">View Details</span> &rarr;
                                </a>
                            @endif

                            @if($isUnread)
                                <button type="button" class="btn-row-action" onclick="markRowRead({{ $notif->id }});" id="btn_read_{{ $notif->id }}">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span data-lang-key="mark_as_read">Mark as read</span>
                                </button>
                            @else
                                <button type="button" class="btn-row-action" onclick="markRowUnread({{ $notif->id }});" id="btn_unread_{{ $notif->id }}">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <span data-lang-key="mark_as_unread">Mark as unread</span>
                                </button>
                            @endif

                            <button type="button" class="btn-row-action btn-row-delete" onclick="deleteNotification({{ $notif->id }});" title="Delete notification" id="btn_delete_{{ $notif->id }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                <span data-lang-key="delete">Delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="notif-empty-state">
                    <div class="notif-empty-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    </div>
                    <h3 class="notif-empty-title" data-lang-key="no_notifications_found">No Notifications Found</h3>
                    <p class="notif-empty-desc" data-lang-key="all_caught_up">You're all caught up! There are currently no notifications matching this criteria.</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="notif-pagination-bar">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Promotion / Announcement Modal (Admin) -->
@if(auth()->user()->isAdmin() || auth()->user()->hasPermission('notifications.promote'))
    <div class="notif-modal-backdrop" id="promotion_modal">
        <div class="notif-modal-card">
            <div class="notif-modal-header">
                <h3 class="notif-modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span data-lang-key="broadcast_announcement">Broadcast Announcement</span>
                </h3>
                <button type="button" class="btn-notif-modal-close" id="btn_close_promotion_modal" onclick="closePromotionModal();">&times;</button>
            </div>

            <form id="promotion_form" action="{{ route('admin.notifications.promotions') }}" method="POST">
                @csrf
                <div class="notif-modal-body">
                    <div class="notif-form-group">
                        <label class="notif-form-label" for="promotion_title_input" data-lang-key="promotion_title">Announcement Title</label>
                        <input type="text" id="promotion_title_input" name="title" class="notif-form-input" placeholder="e.g., Weekend Special: 20% Off All Cakes" required maxlength="150">
                    </div>

                    <div class="notif-form-group">
                        <label class="notif-form-label" for="promotion_message_input" data-lang-key="promotion_message">Announcement Message</label>
                        <textarea id="promotion_message_input" name="message" class="notif-form-textarea" placeholder="Enter announcement message for bakery staff..." required maxlength="1000"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="notif-form-group">
                            <label class="notif-form-label" for="promotion_role_select" data-lang-key="target_role">Target Role</label>
                            <select id="promotion_role_select" name="target_role" class="notif-form-select">
                                <option value="all" data-lang-key="all_roles">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="manager">Manager</option>
                                <option value="cashier">Cashier</option>
                                <option value="baker">Baker</option>
                            </select>
                        </div>

                        <div class="notif-form-group">
                            <label class="notif-form-label" for="promotion_severity_select" data-lang-key="severity">Severity</label>
                            <select id="promotion_severity_select" name="severity" class="notif-form-select">
                                <option value="info">Info</option>
                                <option value="warning">Warning</option>
                                <option value="success">Success</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="notif-modal-footer">
                    <x-button variant="secondary" onclick="closePromotionModal();" data-lang-key="cancel">{{ __('messages.cancel') ?? 'Cancel' }}</x-button>
                    <x-button variant="primary" type="submit" id="btn_submit_promotion" data-lang-key="send_promotion">{{ __('messages.send_promotion') ?? 'Send Promotion' }}</x-button>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function openPromotionModal() {
        const modal = document.getElementById('promotion_modal');
        if (modal) modal.classList.add('is-active');
    }

    function closePromotionModal() {
        const modal = document.getElementById('promotion_modal');
        if (modal) modal.classList.remove('is-active');
    }

    function markRowRead(id) {
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            }
        }).then(res => res.json()).then(data => {
            const row = document.getElementById(`notif_row_${id}`);
            if (row) {
                row.classList.remove('unread');
                const readBtn = document.getElementById(`btn_read_${id}`);
                if (readBtn) {
                    readBtn.outerHTML = `<button type="button" class="btn-row-action" onclick="markRowUnread(${id});" id="btn_unread_${id}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span data-lang-key="mark_as_unread">Mark as unread</span>
                    </button>`;
                }
            }
            // Update unread badges
            const unreadBadge = document.getElementById('stat_unread_count');
            if (unreadBadge && data.unread_count !== undefined) {
                unreadBadge.textContent = data.unread_count;
            }
            const headerBadge = document.getElementById('notification_badge');
            if (headerBadge) {
                if (data.unread_count > 0) {
                    headerBadge.textContent = data.unread_count;
                    headerBadge.style.display = 'inline-flex';
                } else {
                    headerBadge.style.display = 'none';
                }
            }
        }).catch(err => console.error(err));
    }

    function markRowUnread(id) {
        fetch(`/notifications/${id}/unread`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            }
        }).then(res => res.json()).then(data => {
            const row = document.getElementById(`notif_row_${id}`);
            if (row) {
                row.classList.add('unread');
                const unreadBtn = document.getElementById(`btn_unread_${id}`);
                if (unreadBtn) {
                    unreadBtn.outerHTML = `<button type="button" class="btn-row-action" onclick="markRowRead(${id});" id="btn_read_${id}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span data-lang-key="mark_as_read">Mark as read</span>
                    </button>`;
                }
            }
            // Update unread badges
            const unreadBadge = document.getElementById('stat_unread_count');
            if (unreadBadge && data.unread_count !== undefined) {
                unreadBadge.textContent = data.unread_count;
            }
            const headerBadge = document.getElementById('notification_badge');
            if (headerBadge) {
                if (data.unread_count > 0) {
                    headerBadge.textContent = data.unread_count;
                    headerBadge.style.display = 'inline-flex';
                } else {
                    headerBadge.style.display = 'none';
                }
            }
        }).catch(err => console.error(err));
    }

    function deleteNotification(id) {
        window.showConfirmDialog({
            title: 'Dismiss Notification',
            message: 'Are you sure you want to dismiss this notification?',
            confirmText: 'Dismiss',
            isDanger: true,
            onConfirm: function() {
                fetch(`/notifications/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json'
                    }
                }).then(res => res.json()).then(data => {
                    const row = document.getElementById(`notif_row_${id}`);
                    if (row) {
                        row.style.opacity = '0';
                        row.style.transform = 'translateY(-8px)';
                        setTimeout(() => row.remove(), 250);
                    }
                    if (data.unread_count !== undefined) {
                        const unreadBadge = document.getElementById('stat_unread_count');
                        if (unreadBadge) unreadBadge.textContent = data.unread_count;
                        const headerBadge = document.getElementById('notification_badge');
                        if (headerBadge) {
                            if (data.unread_count > 0) {
                                headerBadge.textContent = data.unread_count;
                                headerBadge.style.display = 'inline-flex';
                            } else {
                                headerBadge.style.display = 'none';
                            }
                        }
                    }
                }).catch(err => console.error(err));
            }
        });
    }

    const markAllBtn = document.getElementById('page_mark_all_read');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            fetch('{{ route('notifications.readAll') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                }
            }).then(res => res.json()).then(data => {
                document.querySelectorAll('.notif-feed-row.unread').forEach(row => {
                    row.classList.remove('unread');
                    const id = row.dataset.id;
                    const readBtn = document.getElementById(`btn_read_${id}`);
                    if (readBtn) {
                        readBtn.outerHTML = `<button type="button" class="btn-row-action" onclick="markRowUnread(${id});" id="btn_unread_${id}">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span data-lang-key="mark_as_unread">Mark as unread</span>
                        </button>`;
                    }
                });
                const statBadge = document.getElementById('stat_unread_count');
                if (statBadge) statBadge.textContent = '0';
                const headerBadge = document.getElementById('notification_badge');
                if (headerBadge) headerBadge.style.display = 'none';
                markAllBtn.style.display = 'none';
            }).catch(err => console.error(err));
        });
    }
</script>
@endsection
