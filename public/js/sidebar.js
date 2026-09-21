document.addEventListener("DOMContentLoaded", () => {
    // Clean, instant native CSS hover & active state architecture
    const nav = document.querySelector(".sidebar-nav");
    if (!nav) return;

    // ---------------------------------------------------------
    // LIQUID MOVING POINTER PILL (MACOS / APPLE ELASTIC HOVER)
    // ---------------------------------------------------------
    function initSidebarPill() {
        const nav = document.querySelector(".sidebar-nav");
        if (!nav) return;

        let pill = document.getElementById("nav_pointer_pill");
        if (!pill) {
            pill = document.createElement("div");
            pill.id = "nav_pointer_pill";
            pill.className = "nav-pointer-pill";
            nav.prepend(pill);
        }

        const items = [...nav.querySelectorAll(".sidebar-nav-item")];
        if (!items.length) return;

        let activeItem = nav.querySelector(".sidebar-nav-item.active") || items[0];
        let currentItem = activeItem;
        let currentY = null;
        let currentX = null;
        let stretchTimer = null;

        function movePill(item, instant = false) {
            if (!item) return;

            const navRect = nav.getBoundingClientRect();
            const itemRect = item.getBoundingClientRect();

            if (!itemRect.width || !itemRect.height) return;

            const x = itemRect.left - navRect.left;
            const y = itemRect.top - navRect.top + nav.scrollTop;

            const isMoving = currentY !== null && (Math.abs(y - currentY) > 3 || Math.abs(x - currentX) > 3);

            if (instant) {
                pill.style.transition = "none";
            } else {
                pill.style.transition = "";
            }

            pill.style.width = `${itemRect.width}px`;
            pill.style.height = `${itemRect.height}px`;

            if (stretchTimer) clearTimeout(stretchTimer);

            if (!instant && isMoving) {
                // macOS / iOS Liquid stretch blob effect while in motion
                pill.style.transform = `translate3d(${x}px, ${y}px, 0) scale3d(0.97, 1.12, 1)`;

                // Elastic recoil back to normal size as it lands on target item
                stretchTimer = setTimeout(() => {
                    pill.style.transform = `translate3d(${x}px, ${y}px, 0) scale3d(1, 1, 1)`;
                }, 180);
            } else {
                pill.style.transform = `translate3d(${x}px, ${y}px, 0) scale3d(1, 1, 1)`;
            }

            pill.style.opacity = "1";

            currentY = y;
            currentX = x;
            currentItem = item;

            items.forEach(i => {
                const isTarget = (i === item);
                i.classList.toggle("pill-highlighted", isTarget);
                i.classList.toggle("highlighted", isTarget);
            });

            if (instant) {
                requestAnimationFrame(() => {
                    pill.style.transition = "";
                });
            }
        }

        function setHighlight(item) {
            movePill(item);
        }

        function setActive(item) {
            items.forEach(i => i.classList.remove("active"));
            item.classList.add("active");
            activeItem = item;
            currentItem = item;
            setHighlight(item);
        }

        // Hover & Touch Events on nav items
        items.forEach(item => {
            item.addEventListener("mouseenter", () => {
                if (window.matchMedia("(hover: hover)").matches) {
                    setHighlight(item);
                }
            });

            item.addEventListener("pointerdown", (event) => {
                if (event.pointerType === "touch" || event.pointerType === "pen") {
                    setHighlight(item);
                }
            }, { passive: true });

            item.addEventListener("click", () => {
                setActive(item);
            });
        });

        // Hovering inside submenus keeps pill resting gracefully on the parent nav item
        const submenuItems = [...nav.querySelectorAll(".sidebar-submenu-item")];
        submenuItems.forEach(subItem => {
            subItem.addEventListener("mouseenter", () => {
                if (window.matchMedia("(hover: hover)").matches) {
                    const group = subItem.closest(".sidebar-group");
                    if (group) {
                        const parentNavItem = group.querySelector(".sidebar-nav-item");
                        if (parentNavItem) {
                            setHighlight(parentNavItem);
                        }
                    }
                }
            });
        });

        // Container mouseleave: cleanly glide back to activeItem without gap stutter
        nav.addEventListener("mouseleave", () => {
            if (window.matchMedia("(hover: hover)").matches) {
                setHighlight(activeItem);
            }
        });

        function refreshPosition(instant = true) {
            if (!currentItem) return;
            requestAnimationFrame(() => {
                movePill(currentItem, instant);
            });
        }

        window.addEventListener("resize", () => refreshPosition(true));
        window.addEventListener("orientationchange", () => setTimeout(() => refreshPosition(true), 250));

        if (window.ResizeObserver) {
            const ro = new ResizeObserver(() => {
                refreshPosition(true);
            });
            ro.observe(nav);
            const sidebarEl = document.querySelector(".bakery-sidebar");
            if (sidebarEl) ro.observe(sidebarEl);
        }

        // Sidebar toggle button listeners (Ctrl+B, mobile hamburger, in-sidebar toggle)
        const toggleBtns = document.querySelectorAll("#sidebar_toggle_btn, .mobile-sidebar-toggle-btn, .sidebar-header-toggle-btn, #sidebar_close_btn");
        toggleBtns.forEach(btn => {
            btn.addEventListener("click", () => {
                let ticks = 0;
                const interval = setInterval(() => {
                    refreshPosition(true);
                    ticks++;
                    if (ticks > 8) clearInterval(interval);
                }, 40);
            });
        });

        // Accordion chevrons
        document.querySelectorAll(".sidebar-dropdown-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                setTimeout(() => refreshPosition(false), 80);
                setTimeout(() => refreshPosition(true), 280);
            });
        });

        // Expose globally
        window.sidebarPillRefresh = () => refreshPosition(true);

        // Initial alignment on page load
        setTimeout(() => {
            movePill(activeItem, true);
        }, 40);
    }

    // ---------------------------------------------------------
    // iOS SEGMENTED CONTROL (MACOS APPLE LIQUID SMOOTH PILL)
    // ---------------------------------------------------------
    function initSegmentedControls() {
        const controls = document.querySelectorAll('.ios-segmented-control');

        controls.forEach(control => {
            let pill = control.querySelector('.segmented-pointer-pill');
            if (!pill) {
                pill = document.createElement('div');
                pill.className = 'segmented-pointer-pill';
                control.prepend(pill);
            }

            const buttons = [...control.querySelectorAll('.segmented-option')];
            if (!buttons.length) return;

            let activeBtn = control.querySelector('.segmented-option.active') || buttons[0];
            let stretchTimer = null;
            let currentX = null;

            function moveSegmentPill(btn, instant = false) {
                if (!btn) return;
                const controlRect = control.getBoundingClientRect();
                const btnRect = btn.getBoundingClientRect();

                if (!btnRect.width || !btnRect.height) return;

                const x = btnRect.left - controlRect.left;
                const y = btnRect.top - controlRect.top;
                const width = btnRect.width;
                const height = btnRect.height;

                const isMoving = currentX !== null && Math.abs(x - currentX) > 3;

                if (instant) {
                    pill.style.transition = 'none';
                } else {
                    pill.style.transition = '';
                }

                pill.style.width = `${width}px`;
                pill.style.height = `${height}px`;

                if (stretchTimer) clearTimeout(stretchTimer);

                if (!instant && isMoving) {
                    // Liquid stretch blob effect while sliding
                    pill.style.transform = `translate3d(${x}px, ${y}px, 0) scale3d(1.06, 0.94, 1)`;

                    stretchTimer = setTimeout(() => {
                        pill.style.transform = `translate3d(${x}px, ${y}px, 0) scale3d(1, 1, 1)`;
                    }, 180);
                } else {
                    pill.style.transform = `translate3d(${x}px, ${y}px, 0) scale3d(1, 1, 1)`;
                }

                pill.style.opacity = '1';
                currentX = x;

                buttons.forEach(b => {
                    b.classList.toggle('pill-highlighted', b === btn);
                });

                if (instant) {
                    requestAnimationFrame(() => {
                        pill.style.transition = '';
                    });
                }
            }

            // Move pill on load & resize
            setTimeout(() => moveSegmentPill(activeBtn, true), 40);
            window.addEventListener('resize', () => moveSegmentPill(activeBtn, true));

            buttons.forEach(btn => {
                btn.addEventListener('click', function() {
                    buttons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    activeBtn = this;
                    moveSegmentPill(this);

                    const themeAttr = this.getAttribute('data-theme');
                    if (themeAttr) {
                        applyTheme(themeAttr);
                    }
                });

                btn.addEventListener('mouseenter', function() {
                    moveSegmentPill(this);
                });
            });

            control.addEventListener('mouseleave', function() {
                moveSegmentPill(activeBtn);
            });
        });
    }

    // ---------------------------------------------------------
    // THEME SWITCHER ENGINE (LIGHT, DARK, GLASS)
    // ---------------------------------------------------------
    function applyTheme(themeName) {
        const validThemes = ['light', 'dark', 'glass'];
        const theme = validThemes.includes(themeName) ? themeName : 'light';

        document.documentElement.setAttribute('data-theme', theme);
        if (document.body) {
            document.body.classList.toggle('dark-mode', theme === 'dark');
            document.body.classList.toggle('theme-dark', theme === 'dark');
        }
        localStorage.setItem('bakery_theme', theme);

        document.querySelectorAll('.ios-segmented-control').forEach(control => {
            const buttons = control.querySelectorAll('.segmented-option');
            buttons.forEach(btn => {
                const isMatch = btn.getAttribute('data-theme') === theme;
                btn.classList.toggle('active', isMatch);
            });
        });

        if (typeof window.sidebarPillRefresh === 'function') {
            window.sidebarPillRefresh();
        }
        if (typeof window.menuBarsPillRefresh === 'function') {
            window.menuBarsPillRefresh();
        }
    }

    // ---------------------------------------------------------
    // HORIZONTAL MENU BARS LIQUID POINTER PILL (SIDEBAR PARITY)
    // ---------------------------------------------------------
    function initMenuBarsPill() {
        const barConfigs = [
            { containerSelector: '.period-pill-group', itemSelector: '.period-pill' },
            { containerSelector: '.orders-filter-bar', itemSelector: '.filter-btn' },
            { containerSelector: '.report-nav-tabs', itemSelector: '.report-tab-btn' },
            { containerSelector: '.hr-nav-tabs', itemSelector: '.hr-nav-tab' },
            { containerSelector: '.pos-payment-selector', itemSelector: '.pos-pay-btn' }
        ];

        barConfigs.forEach(({ containerSelector, itemSelector }) => {
            const containers = document.querySelectorAll(containerSelector);

            containers.forEach(container => {
                container.classList.add('has-liquid-pill');

                let pill = container.querySelector('.menu-pointer-pill');
                if (!pill) {
                    pill = document.createElement('div');
                    pill.className = 'menu-pointer-pill';
                    container.prepend(pill);
                }

                const items = [...container.querySelectorAll(itemSelector)];
                if (!items.length) return;

                let activeItem = container.querySelector(`${itemSelector}.active`) || items[0];
                let currentItem = activeItem;
                let currentX = null;
                let currentY = null;
                let stretchTimer = null;

                function moveMenuPill(item, instant = false) {
                    if (!item) return;

                    const containerRect = container.getBoundingClientRect();
                    const itemRect = item.getBoundingClientRect();

                    if (!itemRect.width || !itemRect.height) return;

                    const borderLeft = container.clientLeft || 0;
                    const borderTop = container.clientTop || 0;
                    const x = itemRect.left - containerRect.left - borderLeft + container.scrollLeft;
                    const y = itemRect.top - containerRect.top - borderTop + container.scrollTop;

                    const isMovingX = currentX !== null && Math.abs(x - currentX) > 3;
                    const isMovingY = currentY !== null && Math.abs(y - currentY) > 3;

                    if (instant) {
                        pill.style.transition = 'none';
                    } else {
                        pill.style.transition = '';
                    }

                    pill.style.width = `${itemRect.width}px`;
                    pill.style.height = `${itemRect.height}px`;

                    if (stretchTimer) clearTimeout(stretchTimer);

                    if (!instant && (isMovingX || isMovingY)) {
                        // macOS / Apple liquid stretch blob effect while sliding (Sidebar parity)
                        if (isMovingX) {
                            pill.style.transform = `translate3d(${x}px, ${y}px, 0) scale3d(1.10, 0.94, 1)`;
                        } else {
                            pill.style.transform = `translate3d(${x}px, ${y}px, 0) scale3d(0.94, 1.10, 1)`;
                        }

                        stretchTimer = setTimeout(() => {
                            pill.style.transform = `translate3d(${x}px, ${y}px, 0) scale3d(1, 1, 1)`;
                        }, 180);
                    } else {
                        pill.style.transform = `translate3d(${x}px, ${y}px, 0) scale3d(1, 1, 1)`;
                    }

                    pill.style.opacity = '1';
                    currentX = x;
                    currentY = y;
                    currentItem = item;

                    items.forEach(i => {
                        const isTarget = (i === item);
                        i.classList.toggle('pill-highlighted', isTarget);
                        i.classList.toggle('highlighted', isTarget);
                    });

                    if (instant) {
                        requestAnimationFrame(() => {
                            pill.style.transition = '';
                        });
                    }
                }

                function setHighlight(item) {
                    moveMenuPill(item);
                }

                function setActive(item) {
                    items.forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                    activeItem = item;
                    currentItem = item;
                    setHighlight(item);
                }

                items.forEach(item => {
                    item.addEventListener('mouseenter', () => {
                        if (window.matchMedia('(hover: hover)').matches) {
                            setHighlight(item);
                        }
                    });

                    item.addEventListener('pointerdown', (event) => {
                        if (event.pointerType === 'touch' || event.pointerType === 'pen') {
                            setHighlight(item);
                        }
                    }, { passive: true });

                    item.addEventListener('click', () => {
                        setActive(item);
                    });
                });

                container.addEventListener('mouseleave', () => {
                    if (window.matchMedia('(hover: hover)').matches) {
                        setHighlight(activeItem);
                    }
                });

                function refreshPosition(instant = true) {
                    if (currentItem) {
                        moveMenuPill(currentItem, instant);
                    }
                }

                window.addEventListener('resize', () => refreshPosition(true));

                if (window.ResizeObserver) {
                    const ro = new ResizeObserver(() => refreshPosition(true));
                    ro.observe(container);
                }

                // Initial alignment on page mount
                setTimeout(() => moveMenuPill(activeItem, true), 30);
                setTimeout(() => moveMenuPill(activeItem, true), 120);
                setTimeout(() => moveMenuPill(activeItem, true), 300);
            });
        });
    }

    window.menuBarsPillRefresh = function() {
        initMenuBarsPill();
    };

    // Auto-apply saved theme on DOM load
    const currentSavedTheme = localStorage.getItem('bakery_theme') || 'light';
    applyTheme(currentSavedTheme);

    initSidebarPill();
    initSegmentedControls();
    initMenuBarsPill();
    initGlobalConfirmModal();
});

// =========================================================
// Global Action & Deletion Confirmation Modal Engine
// =========================================================
function extractConfirmMessage(code) {
    if (!code) return null;
    const confirmIdx = code.indexOf('confirm(');
    if (confirmIdx === -1) return null;
    
    // Find opening quote
    let i = confirmIdx + 8;
    while (i < code.length && (code[i] === ' ' || code[i] === '\t' || code[i] === '\n')) i++;
    const quoteChar = code[i];
    if (quoteChar !== "'" && quoteChar !== '"' && quoteChar !== '`') return null;
    
    i++;
    let result = '';
    while (i < code.length) {
        if (code[i] === '\\' && i + 1 < code.length) {
            const next = code[i + 1];
            if (next === quoteChar) {
                result += quoteChar;
                i += 2;
                continue;
            } else if (next === 'n') {
                result += '\n';
                i += 2;
                continue;
            } else if (next === 'r') {
                result += '\r';
                i += 2;
                continue;
            } else if (next === '\\') {
                result += '\\';
                i += 2;
                continue;
            }
        }
        if (code[i] === quoteChar) {
            break;
        }
        result += code[i];
        i++;
    }
    return result;
}

window.showConfirmDialog = function(options) {
    options = options || {};
    const modal = document.getElementById('global_confirm_modal');
    if (!modal) {
        // Fallback to native confirm if modal markup is not present
        const confirmed = window.confirm(options.message || 'Are you sure you want to proceed?');
        if (confirmed && typeof options.onConfirm === 'function') {
            options.onConfirm();
        } else if (!confirmed && typeof options.onCancel === 'function') {
            options.onCancel();
        }
        return;
    }

    const iconChamber = document.getElementById('global_confirm_icon_chamber');
    const iconTrash = document.getElementById('global_confirm_icon_trash');
    const iconWarn = document.getElementById('global_confirm_icon_warn');
    const iconInfo = document.getElementById('global_confirm_icon_info');
    const titleEl = document.getElementById('global_confirm_title');
    const messageEl = document.getElementById('global_confirm_message');
    const cancelBtn = document.getElementById('btn_cancel_global_confirm');
    const cancelTextEl = document.getElementById('global_confirm_cancel_text');
    const proceedBtn = document.getElementById('btn_proceed_global_confirm');
    const proceedTextEl = document.getElementById('global_confirm_proceed_text');

    if (titleEl) titleEl.textContent = options.title || 'Confirm Deletion';
    if (messageEl) messageEl.textContent = options.message || 'Are you sure you want to proceed with this action? This cannot be undone.';
    if (cancelTextEl) cancelTextEl.textContent = options.cancelText || 'Cancel';
    if (proceedTextEl) proceedTextEl.textContent = options.confirmText || 'Yes, Delete';

    // Configure Icon and Button Color Scheme
    const isWarning = options.type === 'warning' || (options.isDanger === false && options.type !== 'info');
    const isInfo = options.type === 'info';

    if (iconChamber) {
        iconChamber.className = 'confirm-modal-icon-chamber ' + (isWarning ? 'warning' : (isInfo ? 'info' : 'danger'));
    }
    if (iconTrash) iconTrash.style.display = (!isWarning && !isInfo) ? 'block' : 'none';
    if (iconWarn) iconWarn.style.display = isWarning ? 'block' : 'none';
    if (iconInfo) iconInfo.style.display = isInfo ? 'block' : 'none';

    if (proceedBtn) {
        proceedBtn.className = 'confirm-modal-btn confirm-btn ' + (isWarning ? 'warning' : (isInfo ? 'primary' : 'danger'));
    }

    // Open Modal
    modal.classList.add('is-active');
    modal.setAttribute('aria-hidden', 'false');
    if (cancelBtn) cancelBtn.focus();

    function closeModal() {
        modal.classList.remove('is-active');
        modal.setAttribute('aria-hidden', 'true');
        cleanup();
    }

    function onProceedClick() {
        closeModal();
        if (typeof options.onConfirm === 'function') {
            options.onConfirm();
        }
    }

    function onCancelClick() {
        closeModal();
        if (typeof options.onCancel === 'function') {
            options.onCancel();
        }
    }

    function onBackdropClick(e) {
        if (e.target === modal) {
            onCancelClick();
        }
    }

    function onKeyDown(e) {
        if (e.key === 'Escape') {
            e.preventDefault();
            onCancelClick();
        }
    }

    function cleanup() {
        if (proceedBtn) proceedBtn.removeEventListener('click', onProceedClick);
        if (cancelBtn) cancelBtn.removeEventListener('click', onCancelClick);
        modal.removeEventListener('click', onBackdropClick);
        document.removeEventListener('keydown', onKeyDown);
    }

    if (proceedBtn) proceedBtn.addEventListener('click', onProceedClick);
    if (cancelBtn) cancelBtn.addEventListener('click', onCancelClick);
    modal.addEventListener('click', onBackdropClick);
    document.addEventListener('keydown', onKeyDown);
};

function initGlobalConfirmModal() {
    // Capture-Phase Interceptor for Forms
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || !(form instanceof HTMLFormElement)) return;

        if (form._isConfirmedByModal) {
            form._isConfirmedByModal = false;
            return; // Allow submission to proceed
        }

        let message = form.getAttribute('data-confirm');
        if (!message) {
            message = extractConfirmMessage(form.getAttribute('onsubmit'));
        }

        const submitter = e.submitter;
        if (!message && submitter) {
            message = submitter.getAttribute('data-confirm') || extractConfirmMessage(submitter.getAttribute('onclick'));
        }

        if (message) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            const lower = message.toLowerCase();
            let title = 'Confirm Action';
            let confirmText = 'Confirm';
            let type = 'danger';

            if (lower.includes('delete') || lower.includes('remove')) {
                title = 'Confirm Deletion';
                confirmText = 'Yes, Delete';
                type = 'danger';
            } else if (lower.includes('cancel')) {
                title = 'Confirm Cancellation';
                confirmText = 'Yes, Cancel';
                type = 'danger';
            } else if (lower.includes('receive') || lower.includes('receipt')) {
                title = 'Confirm PO Receipt';
                confirmText = 'Receive Stock';
                type = 'info';
            } else if (lower.includes('paid') || lower.includes('disbursed')) {
                title = 'Mark as Paid';
                confirmText = 'Confirm Payment';
                type = 'warning';
            }

            window.showConfirmDialog({
                title: title,
                message: message,
                confirmText: confirmText,
                type: type,
                onConfirm: function() {
                    form._isConfirmedByModal = true;
                    const prevOnsubmit = form.onsubmit;
                    const prevSubmitAttr = form.getAttribute('onsubmit');
                    form.onsubmit = null;
                    if (prevSubmitAttr) form.removeAttribute('onsubmit');

                    try {
                        if (submitter && typeof form.requestSubmit === 'function') {
                            form.requestSubmit(submitter);
                        } else if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                        } else {
                            form.submit();
                        }
                    } finally {
                        setTimeout(() => {
                            if (prevOnsubmit) form.onsubmit = prevOnsubmit;
                            if (prevSubmitAttr) form.setAttribute('onsubmit', prevSubmitAttr);
                        }, 500);
                    }
                }
            });
        }
    }, true); // Use capture phase

    // Capture-Phase Interceptor for Buttons & Links with inline confirms
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('button, a');
        if (!btn) return;

        if (btn._isConfirmedByModal) {
            btn._isConfirmedByModal = false;
            return;
        }

        let message = btn.getAttribute('data-confirm');
        if (!message) {
            message = extractConfirmMessage(btn.getAttribute('onclick'));
        }

        if (message) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            const lower = message.toLowerCase();
            let title = 'Confirm Action';
            let confirmText = 'Confirm';
            let type = 'danger';

            if (lower.includes('delete') || lower.includes('remove')) {
                title = 'Confirm Deletion';
                confirmText = 'Yes, Delete';
                type = 'danger';
            } else if (lower.includes('cancel')) {
                title = 'Confirm Cancellation';
                confirmText = 'Yes, Cancel';
                type = 'danger';
            } else if (lower.includes('paid') || lower.includes('disbursed')) {
                title = 'Mark as Paid';
                confirmText = 'Confirm Payment';
                type = 'warning';
            } else if (lower.includes('receive') || lower.includes('receipt')) {
                title = 'Confirm PO Receipt';
                confirmText = 'Receive Stock';
                type = 'info';
            }

            window.showConfirmDialog({
                title: title,
                message: message,
                confirmText: confirmText,
                type: type,
                onConfirm: function() {
                    btn._isConfirmedByModal = true;
                    const form = btn.closest('form');
                    if (form && btn.type === 'submit') {
                        form._isConfirmedByModal = true;
                        const prevOnsubmit = form.onsubmit;
                        const prevSubmitAttr = form.getAttribute('onsubmit');
                        form.onsubmit = null;
                        if (prevSubmitAttr) form.removeAttribute('onsubmit');

                        try {
                            if (typeof form.requestSubmit === 'function') {
                                form.requestSubmit(btn);
                            } else {
                                form.submit();
                            }
                        } finally {
                            setTimeout(() => {
                                if (prevOnsubmit) form.onsubmit = prevOnsubmit;
                                if (prevSubmitAttr) form.setAttribute('onsubmit', prevSubmitAttr);
                            }, 500);
                        }
                    } else if (btn.tagName === 'A' && btn.href && !btn.href.startsWith('javascript:')) {
                        window.location.href = btn.href;
                    } else {
                        const prevOnclick = btn.onclick;
                        const prevClickAttr = btn.getAttribute('onclick');
                        btn.onclick = null;
                        if (prevClickAttr) btn.removeAttribute('onclick');

                        try {
                            btn.click();
                        } finally {
                            setTimeout(() => {
                                if (prevOnclick) btn.onclick = prevOnclick;
                                if (prevClickAttr) btn.setAttribute('onclick', prevClickAttr);
                            }, 500);
                        }
                    }
                }
            });
        }
    }, true); // Use capture phase
}