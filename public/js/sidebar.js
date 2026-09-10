document.addEventListener("DOMContentLoaded", () => {
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

    let activeItem =
        nav.querySelector(".sidebar-nav-item.active") || items[0];

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

        const isMoving = currentY !== null && (Math.abs(y - currentY) > 4 || Math.abs(x - currentX) > 4);

        if (instant) {
            pill.style.transition = "none";
        } else {
            pill.style.transition = "";
        }

        pill.style.width = `${itemRect.width}px`;
        pill.style.height = `${itemRect.height}px`;

        if (stretchTimer) clearTimeout(stretchTimer);

        if (!instant && isMoving) {
            // macOS/iOS Liquid stretch blob effect while in motion
            pill.style.transform = `translate3d(${x}px, ${y}px, 0) scale3d(0.96, 1.15, 1)`;

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
            const isTarget = i === item;
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

    // -------------------------
    // DESKTOP & MOBILE HOVER / TOUCH
    // -------------------------

    items.forEach(item => {

        item.addEventListener("mouseenter", () => {
            if (window.matchMedia("(hover: hover)").matches) {
                setHighlight(item);
            }
        });

        item.addEventListener("mouseleave", () => {
            if (window.matchMedia("(hover: hover)").matches) {
                setHighlight(activeItem);
            }
        });

        // -------------------------
        // IPAD / PHONE TOUCH
        // -------------------------

        item.addEventListener(
            "pointerdown",
            (event) => {

                if (
                    event.pointerType === "touch" ||
                    event.pointerType === "pen"
                ) {
                    setHighlight(item);
                }

            },
            { passive: true }
        );

        // Normal click
        item.addEventListener("click", () => {
            setActive(item);
        });
    });

    // -------------------------
    // DESKTOP LEAVE CONTAINER
    // -------------------------

    nav.addEventListener("mouseleave", () => {

        if (window.matchMedia("(hover: hover)").matches) {
            setHighlight(activeItem);
        }

    });

    // -------------------------
    // RESIZE & ORIENTATION
    // -------------------------

    function refreshPosition() {

        if (!currentItem) return;

        requestAnimationFrame(() => {
            movePill(currentItem, true);
        });

    }

    window.addEventListener("resize", refreshPosition);

    window.addEventListener("orientationchange", () => {
        setTimeout(refreshPosition, 250);
    });

    // -------------------------
    // RESIZE OBSERVER
    // -------------------------

    if (window.ResizeObserver) {

        const observer = new ResizeObserver(() => {
            refreshPosition();
        });

        observer.observe(nav);
    }

    // Hamburger toggle button listener for iPad/Mobile sidebar expansion
    const sidebarToggleBtn = document.getElementById('sidebar_toggle_btn');
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function() {
            let count = 0;
            const interval = setInterval(() => {
                refreshPosition();
                count++;
                if (count > 6) clearInterval(interval);
            }, 50);
        });
    }

    // -------------------------
    // INITIAL POSITION
    // -------------------------

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            movePill(activeItem, true);
        });
    });

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
    }

    // Auto-apply saved theme on DOM load
    const currentSavedTheme = localStorage.getItem('bakery_theme') || 'light';
    applyTheme(currentSavedTheme);

    initSegmentedControls();
});