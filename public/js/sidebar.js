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

        // Sidebar toggle button listeners (Ctrl+B, mobile hamburger, header toggle, in-sidebar toggle)
        const toggleBtns = document.querySelectorAll("#sidebar_toggle_btn, .header-sidebar-toggle-btn, .sidebar-header-toggle-btn, #sidebar_close_btn");
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
    }

    // Auto-apply saved theme on DOM load
    const currentSavedTheme = localStorage.getItem('bakery_theme') || 'light';
    applyTheme(currentSavedTheme);

    initSidebarPill();
    initSegmentedControls();
});