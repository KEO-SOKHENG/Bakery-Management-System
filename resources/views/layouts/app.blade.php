<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bakery Management System')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Instant Theme Loader -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('bakery_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            const syncBody = () => {
                if (document.body) {
                    document.body.classList.toggle('dark-mode', savedTheme === 'dark');
                    document.body.classList.toggle('theme-dark', savedTheme === 'dark');
                }
            };
            if (document.body) {
                syncBody();
            } else {
                document.addEventListener('DOMContentLoaded', syncBody);
            }
        })();
    </script>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ asset('css/common.css') }}?v={{ @filemtime(public_path('css/common.css')) ?: '1.0' }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}?v={{ @filemtime(public_path('css/sidebar.css')) ?: '1.0' }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}?v={{ @filemtime(public_path('css/animations.css')) ?: '1.0' }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ @filemtime(public_path('css/responsive.css')) ?: '1.0' }}">
</head>
<body>
    <div class="bakery-app-layout">
        <!-- Sidebar Navigation -->
        <aside class="bakery-sidebar" id="sidebar_nav">
            <!-- Brand / Logo Area -->
            <div class="bakery-logo-area">
                <div class="bakery-brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12C2 7.5 5.5 4 12 4s10 3.5 10 8c0 4-3 7-10 7S2 16 2 12Z"/>
                        <path d="M7 11c1 1.5 2 2.5 3 2.5"/>
                        <path d="M12 10c1 1.5 2 2.5 2.5 2.5"/>
                        <path d="M16 11c.5 1 1 2 1.5 2.5"/>
                    </svg>
                </div>
                <div class="bakery-logo-text">
                    <div class="bakery-logo-title">Bakery</div>
                    <div class="bakery-logo-subtitle">Management System</div>
                </div>
                <button type="button" class="sidebar-header-toggle-btn" id="sidebar_close_btn" title="Toggle Sidebar (Ctrl+B)" aria-label="Toggle Sidebar">
                    <svg class="icon-chevron-toggle" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <svg class="icon-close-mobile" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="sidebar-nav">
                <!-- Liquid Moving Pointer Pill -->
                <div class="nav-pointer-pill" id="nav_pointer_pill"></div>

                @php
                    $role = auth()->check() ? strtolower(auth()->user()->role) : 'admin';
                @endphp

                <!-- ==============================================
                     1. MAIN SECTION
                     ============================================== -->
                <div class="sidebar-section-header" data-lang-key="main">Main</div>

                <!-- Dashboard (All Roles) -->
                @if($role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                        <span data-lang-key="dashboard">Dashboard</span>
                    </a>
                @elseif($role === 'manager')
                    <a href="{{ route('manager.dashboard') }}" class="sidebar-nav-item {{ request()->is('manager/dashboard') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                        <span data-lang-key="dashboard">Dashboard</span>
                    </a>
                @elseif($role === 'baker')
                    <a href="{{ route('admin.production') }}" class="sidebar-nav-item {{ request()->is('admin/production*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                        <span data-lang-key="production">Production</span>
                    </a>
                @else
                    <a href="{{ route('cashier.dashboard') }}" class="sidebar-nav-item {{ (request()->is('cashier/dashboard') || request()->is('pos*')) ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        <span data-lang-key="pos">POS Cashier</span>
                    </a>
                @endif

                <!-- POS Terminal (Admin & Manager) -->
                @if(in_array($role, ['admin', 'manager']))
                    <a href="{{ route('pos') }}" class="sidebar-nav-item {{ request()->is('pos*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        <span data-lang-key="pos_terminal">POS Terminal</span>
                    </a>
                @endif

                <!-- Orders (Admin, Manager, Cashier) -->
                @if(in_array($role, ['admin', 'manager', 'cashier']))
                    <a href="{{ route('admin.orders') }}" class="sidebar-nav-item {{ request()->is('admin/orders*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <span data-lang-key="orders">Orders</span>
                    </a>
                @endif

                <!-- ==============================================
                     2. MANAGEMENT SECTION
                     ============================================== -->
                <div class="sidebar-section-header" data-lang-key="management">Management</div>

                <!-- Products Group -->
                @php
                    $productsOpen = request()->is('admin/products*') || request()->is('admin/categories*');
                @endphp
                <div class="sidebar-group {{ $productsOpen ? 'is-open' : '' }}" id="group_products">
                    <a href="{{ route('admin.products') }}" class="sidebar-nav-item {{ $productsOpen ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        <span data-lang-key="products">Products</span>
                        @if(in_array($role, ['admin', 'manager']))
                            <span class="sidebar-dropdown-btn" id="group_products_btn" role="button" tabindex="0" title="Toggle Products" aria-label="Toggle Products Submenu">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </span>
                        @endif
                    </a>
                    @if(in_array($role, ['admin', 'manager']))
                        <div class="sidebar-submenu">
                            <a href="{{ route('admin.products') }}" class="sidebar-submenu-item {{ request()->is('admin/products*') ? 'active' : '' }}">
                                <span class="submenu-dot"></span>
                                <span data-lang-key="products">Products</span>
                            </a>
                            <a href="{{ route('admin.categories') }}" class="sidebar-submenu-item {{ request()->is('admin/categories*') ? 'active' : '' }}">
                                <span class="submenu-dot"></span>
                                <span data-lang-key="categories">Categories</span>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Inventory Group (Admin & Manager) -->
                @if(in_array($role, ['admin', 'manager']))
                    @php
                        $inventoryOpen = request()->is('admin/ingredients*') || request()->is('admin/recipes*') || request()->is('admin/production*') || request()->is('admin/suppliers*') || request()->is('admin/purchase-orders*');
                    @endphp
                    <div class="sidebar-group {{ $inventoryOpen ? 'is-open' : '' }}" id="group_inventory">
                        <a href="{{ route('admin.ingredients') }}" class="sidebar-nav-item {{ $inventoryOpen ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                            <span data-lang-key="inventory">Inventory</span>
                            <span class="sidebar-dropdown-btn" id="group_inventory_btn" role="button" tabindex="0" title="Toggle Inventory" aria-label="Toggle Inventory Submenu">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </span>
                        </a>
                        <div class="sidebar-submenu">
                            <a href="{{ route('admin.ingredients') }}" class="sidebar-submenu-item {{ request()->is('admin/ingredients*') ? 'active' : '' }}">
                                <span class="submenu-dot"></span>
                                <span data-lang-key="ingredients">Ingredients</span>
                            </a>
                            <a href="{{ route('admin.recipes') }}" class="sidebar-submenu-item {{ request()->is('admin/recipes*') ? 'active' : '' }}">
                                <span class="submenu-dot"></span>
                                <span data-lang-key="recipes">Recipes</span>
                            </a>
                            <a href="{{ route('admin.production') }}" class="sidebar-submenu-item {{ request()->is('admin/production*') ? 'active' : '' }}">
                                <span class="submenu-dot"></span>
                                <span data-lang-key="production">Production</span>
                            </a>
                            <a href="{{ route('admin.suppliers') }}" class="sidebar-submenu-item {{ request()->is('admin/suppliers*') ? 'active' : '' }}">
                                <span class="submenu-dot"></span>
                                <span data-lang-key="suppliers">Suppliers</span>
                            </a>
                            <a href="{{ route('admin.purchase-orders.index') }}" class="sidebar-submenu-item {{ request()->is('admin/purchase-orders*') ? 'active' : '' }}">
                                <span class="submenu-dot"></span>
                                <span data-lang-key="purchase_orders">Purchase Orders</span>
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Customers (Admin & Manager) -->
                @if(in_array($role, ['admin', 'manager']))
                    <a href="{{ route('admin.customers.index') }}" class="sidebar-nav-item {{ request()->is('admin/customers*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span data-lang-key="customers">Customers</span>
                    </a>
                @endif

                <!-- Staff & Accounts (Admin Only) -->
                @if($role === 'admin')
                    <a href="{{ route('admin.users.index') }}" class="sidebar-nav-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span data-lang-key="users_staff">Staff & Accounts</span>
                    </a>
                @endif

                <!-- ==============================================
                     3. ANALYTICS SECTION (Admin & Manager)
                     ============================================== -->
                @if(in_array($role, ['admin', 'manager']))
                    <div class="sidebar-section-header" data-lang-key="analytics">Analytics</div>

                    <a href="{{ route('admin.reports') }}" class="sidebar-nav-item {{ request()->is('admin/reports*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        <span data-lang-key="reports">Sales & Reports</span>
                    </a>
                @endif

                <!-- ==============================================
                     4. SYSTEM SECTION (Admin & Manager)
                     ============================================== -->
                @if(in_array($role, ['admin', 'manager']))
                    <div class="sidebar-section-header" data-lang-key="system">System</div>

                    <!-- Settings Group (Expandable Submenu like Inventory) -->
                    @php
                        $settingsOpen = request()->is('admin/settings*') || request()->is('admin/notifications*');
                    @endphp
                    <div class="sidebar-group {{ $settingsOpen ? 'is-open' : '' }}" id="group_settings">
                        <a href="{{ route('admin.settings') }}" class="sidebar-nav-item {{ $settingsOpen ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                            <span data-lang-key="settings">Settings</span>
                            @if(($realUnreadCount ?? 0) > 0)
                                <span class="sidebar-badge" style="margin-left: auto; margin-right: 4px; background: #ef4444; color: #fff; font-size: 0.7rem; font-weight: 700; padding: 1px 6px; border-radius: 9999px;">{{ $realUnreadCount }}</span>
                            @endif
                            <span class="sidebar-dropdown-btn" id="group_settings_btn" role="button" tabindex="0" title="Toggle Settings" aria-label="Toggle Settings Submenu" style="{{ ($realUnreadCount ?? 0) > 0 ? 'margin-left: 0;' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </span>
                        </a>
                        <div class="sidebar-submenu">
                            <a href="{{ route('admin.settings') }}" class="sidebar-submenu-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                                <span class="submenu-dot"></span>
                                <span data-lang-key="general">General</span>
                            </a>
                            <a href="{{ route('admin.notifications') }}" class="sidebar-submenu-item {{ request()->is('admin/notifications*') ? 'active' : '' }}">
                                <span class="submenu-dot"></span>
                                <span data-lang-key="notifications">Notifications</span>
                                @if(($realUnreadCount ?? 0) > 0)
                                    <span class="sidebar-badge" style="margin-left: auto; background: #ef4444; color: #fff; font-size: 0.68rem; font-weight: 700; padding: 1px 6px; border-radius: 9999px;">{{ $realUnreadCount }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                @endif
            </nav>

            <!-- Bottom Logout Button -->
            <div class="sidebar-logout">
                <a href="{{ route('logout') }}" class="logout-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span data-lang-key="logout">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Mobile & Tablet Drawer Backdrop -->
        <div id="sidebar_mobile_backdrop" class="sidebar-mobile-backdrop"></div>

        <!-- Main Content Area -->
        <div class="bakery-main-content">
            <!-- Header Bar -->
            <header class="top-header-bar">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button type="button" class="mobile-sidebar-toggle-btn" id="sidebar_toggle_btn" title="Toggle Navigation Drawer" aria-label="Toggle Navigation Drawer">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <div class="header-actions">
                    <!-- Language Switcher -->
                    <div class="header-lang-switcher">
                        <a href="{{ route('lang.switch', 'en') }}" id="lang_switch_en" class="lang-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                        <a href="{{ route('lang.switch', 'km') }}" id="lang_switch_km" class="lang-btn {{ app()->getLocale() === 'km' ? 'active' : '' }}">KM</a>
                    </div>

                    <!-- Live Search Input -->
                    <div class="header-search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" class="header-search-input" id="global_header_search" placeholder="Search page items...">
                    </div>

                    <!-- Notification Bell & Popover -->
                    <div class="header-notification-wrapper">
                        <button class="icon-bell-btn" id="notification_bell_btn" type="button" aria-label="Notifications">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                            @if(($realUnreadCount ?? 0) > 0)
                                <span class="bell-badge" id="notification_badge">{{ $realUnreadCount }}</span>
                            @endif
                        </button>

                        <div class="notification-dropdown" id="notification_dropdown">
                            <div class="notification-header">
                                <div class="notification-title-group">
                                    <span class="notification-heading">Notifications</span>
                                    <span class="notification-count-badge" id="notification_count_tag">{{ $realUnreadCount ?? 0 }} New</span>
                                </div>
                                <button type="button" class="btn-mark-all-read" id="btn_mark_all_read">Mark all as read</button>
                            </div>

                            <div class="notification-list" id="notification_list">
                                @forelse(($realNotifications ?? []) as $notif)
                                    @php
                                        $isUnread = !$notif->isRead();
                                        $sev = $notif->severity ?? 'info';
                                    @endphp
                                    <a href="{{ $notif->action_url ?: 'javascript:void(0)' }}" class="notification-item {{ $isUnread ? 'unread' : '' }}" data-id="{{ $notif->id }}" data-read="{{ $isUnread ? '0' : '1' }}">
                                        <div class="notification-icon-box {{ $sev }}">
                                            @if($sev === 'danger')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 1 2 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                            @elseif($sev === 'warning')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                            @elseif($sev === 'success')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                            @else
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                            @endif
                                        </div>
                                        <div class="notification-content">
                                            <div class="notification-item-title">{{ $notif->title }}</div>
                                            <div class="notification-item-desc">{{ $notif->message }}</div>
                                            <span class="notification-time">{{ $notif->time_ago }}</span>
                                        </div>
                                        @if($isUnread)
                                            <span class="unread-dot"></span>
                                        @endif
                                    </a>
                                @empty
                                    <div style="text-align: center; color: #a1a1aa; padding: 2rem; font-size: 0.825rem;" data-lang-key="no_notifications">
                                        No new notifications at this time!
                                    </div>
                                @endforelse
                            </div>

                            <div class="notification-footer">
                                <a href="{{ route('admin.notifications') }}" class="btn-notification-all" data-lang-key="view_all_notifications">View All Notifications &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Pill & Dropdown -->
                    <div class="user-profile-wrapper">
                        {{-- <div class="user-profile-pill" id="user_profile_pill">
                            <div class="user-avatar-img">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                            <span>{{ auth()->check() ? auth()->user()->name : 'User' }} ({{ auth()->check() ? ucfirst(auth()->user()->role) : 'Role' }})</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                        </div> --}}

                        <div class="user-dropdown-menu" id="user_dropdown_menu">
                            <div class="user-dropdown-header">
                                <div class="user-dropdown-name">{{ auth()->check() ? auth()->user()->name : 'User Account' }}</div>
                                <div class="user-dropdown-email">{{ auth()->check() ? auth()->user()->email : 'user@bakery.com' }}</div>
                            </div>
                            
                            @if(in_array(auth()->check() ? strtolower(auth()->user()->role) : 'admin', ['admin', 'manager']))
                                <a href="{{ route('admin.settings') }}" class="user-dropdown-item">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span>Settings & Theme</span>
                                </a>
                            @endif

                            <div class="user-dropdown-divider"></div>

                            <a href="{{ route('logout') }}" class="user-dropdown-item" style="color: #ef4444;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                <span>Sign Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Specific Content -->
            @yield('content')
        </div>
    </div>

    <!-- Logout Confirmation Modal Card -->
    <div id="logout_confirm_modal" class="logout-modal-backdrop" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="logout_modal_title">
        <div class="logout-modal-card">
            <div class="logout-modal-icon-chamber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </div>
            
            <h3 id="logout_modal_title" class="logout-modal-title">Ready to Sign Out?</h3>
            <p class="logout-modal-message">
                Are you sure you want to log out of your session? You will need to log in again to access the bakery management system.
            </p>

            <div class="logout-modal-actions">
                <button type="button" id="btn_cancel_logout" class="logout-modal-btn cancel-btn">
                    <span>Stay Logged In</span>
                </button>
                <a href="{{ route('logout') }}" id="btn_confirm_logout" class="logout-modal-btn confirm-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Yes, Log Out</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Global Action / Deletion Confirmation Modal Card -->
    <div id="global_confirm_modal" class="confirm-modal-backdrop" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="global_confirm_title">
        <div class="confirm-modal-card">
            <div class="confirm-modal-icon-chamber danger" id="global_confirm_icon_chamber">
                <svg id="global_confirm_icon_trash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
                <svg id="global_confirm_icon_warn" style="display: none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <svg id="global_confirm_icon_info" style="display: none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            
            <h3 id="global_confirm_title" class="confirm-modal-title">Confirm Deletion</h3>
            <p id="global_confirm_message" class="confirm-modal-message">
                Are you sure you want to delete this item? This action cannot be undone.
            </p>

            <div class="confirm-modal-actions">
                <button type="button" id="btn_cancel_global_confirm" class="confirm-modal-btn cancel-btn">
                    <span id="global_confirm_cancel_text">Cancel</span>
                </button>
                <button type="button" id="btn_proceed_global_confirm" class="confirm-modal-btn confirm-btn danger">
                    <svg id="global_confirm_btn_icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <span id="global_confirm_proceed_text">Yes, Delete</span>
                </button>
            </div>
        </div>
    </div>

    @stack('scripts')
    <script src="{{ asset('js/sidebar.js') }}?v={{ @filemtime(public_path('js/sidebar.js')) ?: '1.0' }}"></script>
    <script src="{{ asset('js/lang.js') }}?v={{ @filemtime(public_path('js/lang.js')) ?: '1.0' }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar synchronization helper
            function smoothPillSync() {
                // Lightweight trigger without continuous frame resize loops
                requestAnimationFrame(() => {
                    window.dispatchEvent(new Event('resize'));
                });
            }
            window.smoothPillSync = smoothPillSync;

            // =========================================================
            // Sidebar Open, Close & Toggle Controllers (Desktop, Tablet, Mobile)
            // =========================================================
            const headerToggleBtn = document.getElementById('sidebar_toggle_btn') || document.getElementById('header_sidebar_toggle_btn');
            const sidebar = document.querySelector('.bakery-sidebar');
            const mobileBackdrop = document.getElementById('sidebar_mobile_backdrop');

            function isDesktop() {
                return window.innerWidth >= 1200;
            }

            function isTablet() {
                return window.innerWidth >= 768 && window.innerWidth < 1200;
            }

            function isMobile() {
                return window.innerWidth < 768;
            }

            function openMobileDrawer() {
                if (sidebar) sidebar.classList.add('sidebar-expanded');
                if (mobileBackdrop) mobileBackdrop.classList.add('is-active');
                if (headerToggleBtn) headerToggleBtn.classList.add('is-closed-state');
            }

            function closeMobileDrawer() {
                if (sidebar) sidebar.classList.remove('sidebar-expanded');
                if (mobileBackdrop) mobileBackdrop.classList.remove('is-active');
                if (headerToggleBtn) headerToggleBtn.classList.remove('is-closed-state');
            }

            const sidebarHeaderToggleBtn = document.querySelector('.sidebar-header-toggle-btn') || document.getElementById('sidebar_close_btn');

            function updateSidebarHeaderToggleBtn() {
                const btn = document.querySelector('.sidebar-header-toggle-btn') || document.getElementById('sidebar_close_btn');
                if (!btn) return;
                const isCollapsed = isDesktop() 
                    ? (sidebar && (sidebar.classList.contains('sidebar-collapsed') || document.body.classList.contains('sidebar-is-closed')))
                    : (sidebar && !sidebar.classList.contains('sidebar-expanded'));

                if (isCollapsed) {
                    btn.setAttribute('title', 'Expand Sidebar (Ctrl+B)');
                    btn.setAttribute('aria-label', 'Expand Sidebar');
                    btn.classList.add('is-collapsed-state');
                } else {
                    btn.setAttribute('title', 'Collapse Sidebar (Ctrl+B)');
                    btn.setAttribute('aria-label', 'Collapse Sidebar');
                    btn.classList.remove('is-collapsed-state');
                }
            }

            function openSidebar() {
                if (isDesktop()) {
                    if (sidebar) sidebar.classList.remove('sidebar-collapsed');
                    document.body.classList.remove('sidebar-is-closed');
                    localStorage.setItem('bakery_sidebar_state', 'expanded');
                    if (headerToggleBtn) {
                        headerToggleBtn.classList.remove('is-closed-state');
                        headerToggleBtn.setAttribute('title', 'Collapse Sidebar (Ctrl+B)');
                    }
                } else {
                    openMobileDrawer();
                }
                updateSidebarHeaderToggleBtn();
                smoothPillSync();
            }

            function closeSidebar() {
                if (isDesktop()) {
                    if (sidebar) sidebar.classList.add('sidebar-collapsed');
                    document.body.classList.add('sidebar-is-closed');
                    localStorage.setItem('bakery_sidebar_state', 'collapsed');
                    if (headerToggleBtn) {
                        headerToggleBtn.classList.add('is-closed-state');
                        headerToggleBtn.setAttribute('title', 'Expand Sidebar (Ctrl+B)');
                    }
                } else {
                    closeMobileDrawer();
                }
                updateSidebarHeaderToggleBtn();
                smoothPillSync();
            }

            function toggleSidebar() {
                if (!sidebar) return;
                if (isDesktop()) {
                    const isCurrentlyCollapsed = sidebar.classList.contains('sidebar-collapsed') || document.body.classList.contains('sidebar-is-closed');
                    if (isCurrentlyCollapsed) {
                        openSidebar();
                    } else {
                        closeSidebar();
                    }
                } else {
                    const isCurrentlyOpen = sidebar.classList.contains('sidebar-expanded');
                    if (isCurrentlyOpen) {
                        closeMobileDrawer();
                    } else {
                        openMobileDrawer();
                    }
                }
                updateSidebarHeaderToggleBtn();
                smoothPillSync();
            }

            // Expose globally
            window.openSidebar = openSidebar;
            window.closeSidebar = closeSidebar;
            window.toggleSidebar = toggleSidebar;
            window.updateSidebarHeaderToggleBtn = updateSidebarHeaderToggleBtn;

            if (headerToggleBtn) {
                headerToggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSidebar();
                });
            }

            if (sidebarHeaderToggleBtn) {
                sidebarHeaderToggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSidebar();
                });
            }

            if (mobileBackdrop) {
                mobileBackdrop.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeSidebar();
                });
                mobileBackdrop.addEventListener('touchend', function(e) {
                    e.preventDefault();
                    closeSidebar();
                });
            }

            document.addEventListener('click', function(e) {
                if (!isDesktop() && sidebar && sidebar.classList.contains('sidebar-expanded')) {
                    if (!sidebar.contains(e.target) && (!headerToggleBtn || !headerToggleBtn.contains(e.target))) {
                        closeMobileDrawer();
                    }
                }
            });

            // Keyboard shortcut support: Ctrl+B or Cmd+B to toggle sidebar, Escape to close drawer
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
                    e.preventDefault();
                    toggleSidebar();
                } else if (e.key === 'Escape') {
                    if (!isDesktop() && sidebar && sidebar.classList.contains('sidebar-expanded')) {
                        closeMobileDrawer();
                    }
                }
            });

            // Initialize sidebar state on page load
            if (isDesktop()) {
                const savedState = localStorage.getItem('bakery_sidebar_state');
                if (savedState === 'collapsed') {
                    if (sidebar) sidebar.classList.add('sidebar-collapsed');
                    document.body.classList.add('sidebar-is-closed');
                    if (headerToggleBtn) {
                        headerToggleBtn.classList.add('is-closed-state');
                        headerToggleBtn.setAttribute('title', 'Expand Sidebar (Ctrl+B)');
                    }
                } else {
                    if (sidebar) sidebar.classList.remove('sidebar-collapsed');
                    document.body.classList.remove('sidebar-is-closed');
                    if (headerToggleBtn) {
                        headerToggleBtn.classList.remove('is-closed-state');
                        headerToggleBtn.setAttribute('title', 'Collapse Sidebar (Ctrl+B)');
                    }
                }
                if (mobileBackdrop) mobileBackdrop.classList.remove('is-active');
            } else {
                closeMobileDrawer();
            }
            updateSidebarHeaderToggleBtn();

            // Adjust on resize between desktop, tablet, and mobile
            window.addEventListener('resize', function() {
                if (isDesktop()) {
                    closeMobileDrawer();
                    const savedState = localStorage.getItem('bakery_sidebar_state');
                    if (savedState === 'collapsed') {
                        if (sidebar) sidebar.classList.add('sidebar-collapsed');
                        document.body.classList.add('sidebar-is-closed');
                    } else {
                        if (sidebar) sidebar.classList.remove('sidebar-collapsed');
                        document.body.classList.remove('sidebar-is-closed');
                    }
                } else {
                    if (sidebar) {
                        sidebar.classList.remove('sidebar-collapsed');
                        document.body.classList.remove('sidebar-is-closed');
                    }
                }
            });

            // User Profile Pill & Notification Elements
            const userPill = document.getElementById('user_profile_pill');
            const userMenu = document.getElementById('user_dropdown_menu');
            const bellBtn = document.getElementById('notification_bell_btn');
            const notificationDropdown = document.getElementById('notification_dropdown');
            const markAllReadBtn = document.getElementById('btn_mark_all_read');
            const notificationBadge = document.getElementById('notification_badge');
            const notificationCountTag = document.getElementById('notification_count_tag');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (bellBtn && notificationDropdown) {
                bellBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (userMenu) userMenu.classList.remove('active');
                    notificationDropdown.classList.toggle('active');
                });
            }

            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetch('{{ route('notifications.readAll') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'Accept': 'application/json'
                        }
                    }).then(res => res.json()).then(data => {
                        document.querySelectorAll('.notification-item').forEach(item => {
                            item.classList.remove('unread');
                            item.dataset.read = '1';
                            const dot = item.querySelector('.unread-dot');
                            if (dot) dot.remove();
                        });
                        if (notificationBadge) {
                            notificationBadge.style.display = 'none';
                            notificationBadge.textContent = '0';
                        }
                        if (notificationCountTag) {
                            notificationCountTag.textContent = '0 New';
                            notificationCountTag.style.backgroundColor = '#e4e4e7';
                            notificationCountTag.style.color = '#71717a';
                        }
                    }).catch(err => console.error('Failed to mark all as read:', err));
                });
            }

            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function() {
                    const notifId = this.dataset.id;
                    const isUnread = this.dataset.read === '0' || this.classList.contains('unread');
                    if (isUnread && notifId) {
                        this.classList.remove('unread');
                        this.dataset.read = '1';
                        const dot = this.querySelector('.unread-dot');
                        if (dot) dot.remove();

                        fetch(`/notifications/${notifId}/read`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || '',
                                'Accept': 'application/json'
                            }
                        }).then(res => res.json()).then(data => {
                            if (notificationBadge) {
                                const remaining = data.unread_count ?? document.querySelectorAll('.notification-item.unread').length;
                                if (remaining > 0) {
                                    notificationBadge.textContent = remaining;
                                    notificationBadge.style.display = 'inline-flex';
                                } else {
                                    notificationBadge.style.display = 'none';
                                }
                                if (notificationCountTag) {
                                    notificationCountTag.textContent = `${remaining} New`;
                                }
                            }
                        }).catch(err => console.error('Failed to mark notification read:', err));
                    }
                });
            });

            // User Profile Pill Toggle
            if (userPill && userMenu) {
                userPill.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (notificationDropdown) notificationDropdown.classList.remove('active');
                    userMenu.classList.toggle('active');
                });
            }

            // Close dropdowns on outside click
            document.addEventListener('click', function(e) {
                if (notificationDropdown && !notificationDropdown.contains(e.target) && (!bellBtn || !bellBtn.contains(e.target))) {
                    notificationDropdown.classList.remove('active');
                }
                if (userMenu && !userMenu.contains(e.target) && (!userPill || !userPill.contains(e.target))) {
                    userMenu.classList.remove('active');
                }
            });

            // Header Global Search Filter for Page Tables & Lists
            const searchInput = document.getElementById('global_header_search');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase().trim();
                    const tableRows = document.querySelectorAll('table tbody tr');
                    const stockItems = document.querySelectorAll('.stock-alert-item');
                    const productCards = document.querySelectorAll('.pos-product-card, .product-card');

                    tableRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(query) ? '' : 'none';
                    });

                    stockItems.forEach(item => {
                        const text = item.textContent.toLowerCase();
                        item.style.display = text.includes(query) ? '' : 'none';
                    });

                    productCards.forEach(card => {
                        const text = card.textContent.toLowerCase();
                        card.style.display = text.includes(query) ? '' : 'none';
                    });
                });
            }

            // Dark Mode Firefly Particle Generator (Dense Swarm)
            const fireflyContainer = document.getElementById('fireflies_layer');
            if (fireflyContainer) {
                const count = 120;
                for (let i = 0; i < count; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'firefly-particle';
                    
                    const size = Math.floor(Math.random() * 3) + 2;
                    const left = Math.random() * 100;
                    const top = Math.random() * 100;
                    const duration = (Math.random() * 10 + 10).toFixed(1);
                    const floatDelay = (Math.random() * 8).toFixed(1);
                    const pulseDelay = (Math.random() * 4).toFixed(1);
                    
                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`;
                    particle.style.left = `${left}%`;
                    particle.style.top = `${top}%`;
                    particle.style.animationDuration = `${duration}s, 3s`;
                    particle.style.animationDelay = `${floatDelay}s, ${pulseDelay}s`;
                    
                    fireflyContainer.appendChild(particle);
                }
            }

            // Sidebar Submenu Dropdown / Dropup Toggle with Auto-Close
            function closeAllSidebarDropdowns(exceptGroup = null) {
                let changed = false;
                document.querySelectorAll('.sidebar-group.is-open').forEach(grp => {
                    if (grp !== exceptGroup) {
                        grp.classList.remove('is-open');
                        changed = true;
                    }
                });
                if (changed) {
                    smoothPillSync();
                }
            }

            // Click & touch listener on dropdown toggle buttons
            document.querySelectorAll('.sidebar-dropdown-btn').forEach(btn => {
                // Instant touch response for iPad & Mobile
                btn.addEventListener('pointerdown', function(e) {
                    e.stopPropagation();
                });

                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const group = this.closest('.sidebar-group');
                    if (group) {
                        const isCurrentlyOpen = group.classList.contains('is-open');
                        // Auto-close any other open group
                        closeAllSidebarDropdowns(group);
                        // Toggle current group
                        if (isCurrentlyOpen) {
                            group.classList.remove('is-open');
                        } else {
                            group.classList.add('is-open');
                        }
                        smoothPillSync();
                    }
                });

                btn.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        e.stopPropagation();
                        this.click();
                    }
                });
            });

            // Auto-close dropdowns when clicking on any other function, nav item, button, or anywhere outside
            document.addEventListener('click', function(e) {
                const clickedGroup = e.target.closest('.sidebar-group');
                closeAllSidebarDropdowns(clickedGroup);
            });

            // =========================================================
            // Logout Confirmation Card Interceptor
            // =========================================================
            const logoutModal = document.getElementById('logout_confirm_modal');
            const cancelLogoutBtn = document.getElementById('btn_cancel_logout');

            function openLogoutModal(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                if (logoutModal) {
                    logoutModal.classList.add('is-active');
                    logoutModal.setAttribute('aria-hidden', 'false');
                }
            }

            function closeLogoutModal() {
                if (logoutModal) {
                    logoutModal.classList.remove('is-active');
                    logoutModal.setAttribute('aria-hidden', 'true');
                }
            }

            // Intercept both sidebar logout button and user dropdown sign out link
            document.querySelectorAll('.logout-btn, a[href*="/logout"]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (this.id === 'btn_confirm_logout') return; // Allow confirmed action to proceed
                    openLogoutModal(e);
                });
            });

            if (cancelLogoutBtn) {
                cancelLogoutBtn.addEventListener('click', closeLogoutModal);
            }

            if (logoutModal) {
                logoutModal.addEventListener('click', function(e) {
                    if (e.target === logoutModal) {
                        closeLogoutModal();
                    }
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && logoutModal && logoutModal.classList.contains('is-active')) {
                    closeLogoutModal();
                }
            });
        });
    </script>
</body>
</html>
