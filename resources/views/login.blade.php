<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bakery Management System</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Dedicated Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <!-- Background Blur Overlay -->
    <div class="login-bg-overlay"></div>

    <!-- Glassmorphic Login Card -->
    <div class="login-card-container">
        <!-- Logo Badge -->
        <div class="login-logo-badge">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 13.8a4.5 4.5 0 1 1 2.6-8.3 5 5 0 0 1 6.8 0 4.5 4.5 0 1 1 2.6 8.3v4.2H6v-4.2z" />
                <path d="M6 18h12v2H6z" />
            </svg>
        </div>

        <!-- System Branding -->
        <h1 class="login-title">Moon Cake</h1>
        <!-- <p class="login-subtitle">Bakery Management System</p> -->

        <!-- Role Selector Tabs -->
        <div class="role-selector-tabs">
            <div class="role-tab-pointer-pill"></div>
            <button type="button" class="role-tab-btn active" data-role="admin">Admin</button>
            <button type="button" class="role-tab-btn" data-role="manager">Manager</button>
            <button type="button" class="role-tab-btn" data-role="baker">Baker</button>
            <button type="button" class="role-tab-btn" data-role="cashier">Cashier</button>
        </div>

        <!-- Login Form -->
        <form action="{{ route('login.post') }}" method="POST" class="login-form">
            @csrf
            <input type="hidden" name="role" id="selected_role" value="admin">

            <!-- Username / Email Field -->
            <div class="form-group-item">
                <label class="form-field-label">Username / Email</label>
                <div class="input-pill-wrapper">
                    <span class="input-icon-left">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </span>
                    <input type="text" name="username" id="username_input" class="pill-input" required
                        placeholder="Admin / Manager / Cashier">
                </div>
            </div>

            <!-- Password Field -->
            <div class="form-group-item">
                <label class="form-field-label">Password</label>
                <div class="input-pill-wrapper">
                    <span class="input-icon-left">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </span>
                    <input type="password" name="password" id="password_input" class="pill-input has-right-icon"
                        required placeholder="••••••••••••">
                    <button type="button" class="input-icon-right" id="toggle_password_btn"
                        title="Toggle password visibility">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="form-options-row">
                <label class="remember-me-label">
                    <input type="checkbox" name="remember" class="remember-me-checkbox" checked>
                    <span>Remember Me</span>
                </label>
                <a href="#" class="forgot-password-link">Forgot Password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-pill-btn" id="submit_btn">
                Sign In as Admin
            </button>
        </form>
    </div>

    <!-- Role Switcher & Interactivity Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleButtons = document.querySelectorAll('.role-tab-btn');
            const hiddenRoleInput = document.getElementById('selected_role');
            const usernameInput = document.getElementById('username_input');
            const passwordInput = document.getElementById('password_input');
            const submitBtn = document.getElementById('submit_btn');
            const togglePasswordBtn = document.getElementById('toggle_password_btn');

            // ---------------------------------------------------------
            // LIQUID SLIDING PILL ANIMATION FOR ROLE SELECTOR
            // ---------------------------------------------------------
            const tabsContainer = document.querySelector('.role-selector-tabs');
            if (tabsContainer) {
                const pill = tabsContainer.querySelector('.role-tab-pointer-pill');
                const buttons = [...tabsContainer.querySelectorAll('.role-tab-btn')];
                let activeBtn = tabsContainer.querySelector('.role-tab-btn.active') || buttons[0];

                function movePill(btn, instant = false) {
                    if (!btn || !pill) return;
                    const containerRect = tabsContainer.getBoundingClientRect();
                    const btnRect = btn.getBoundingClientRect();

                    if (!btnRect.width) return;

                    const x = btnRect.left - containerRect.left;
                    const width = btnRect.width;

                    pill.style.transition = instant ? 'none' : '';
                    pill.style.width = `${width}px`;
                    pill.style.transform = `translate3d(${x}px, 0, 0)`;
                    pill.style.opacity = '1';

                    buttons.forEach(b => {
                        b.classList.toggle('pill-highlighted', b === btn);
                    });
                }

                // Initial alignment & resize listener
                movePill(activeBtn, true);
                window.addEventListener('resize', () => movePill(activeBtn, true));

                buttons.forEach(btn => {
                    btn.addEventListener('click', function () {
                        activeBtn = this;
                        movePill(this);
                    });

                    btn.addEventListener('mouseenter', function () {
                        if (activeBtn !== this) {
                            movePill(this);
                        }
                    });
                });

                tabsContainer.addEventListener('mouseleave', function () {
                    movePill(activeBtn);
                });
            }

            // Role tab switching form logic
            roleButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    roleButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const role = this.getAttribute('data-role');
                    hiddenRoleInput.value = role;

                    if (role === 'admin') {
                        submitBtn.textContent = 'Sign In as Admin';
                    } else if (role === 'manager') {
                        submitBtn.textContent = 'Sign In as Manager';
                    } else if (role === 'baker') {
                        submitBtn.textContent = 'Sign In as Baker';
                    } else if (role === 'cashier') {
                        submitBtn.textContent = 'Sign In as Cashier';
                    }
                });
            });

            // Toggle password visibility
            if (togglePasswordBtn) {
                togglePasswordBtn.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                });
            }

            // Ensure synchronous submission so session cookies are committed before immediate navigations in WebKit
            const loginForm = document.querySelector('.login-form');
            if (loginForm) {
                loginForm.addEventListener('submit', function (e) {
                    try {
                        e.preventDefault();
                        const formData = new FormData(loginForm);
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', loginForm.action, false);
                        xhr.send(formData);

                        if (xhr.responseURL && xhr.responseURL !== window.location.href) {
                            window.location.href = xhr.responseURL;
                        } else {
                            window.location.reload();
                        }
                    } catch (err) {
                        loginForm.submit();
                    }
                });
            }
        });
    </script>
</body>

</html>