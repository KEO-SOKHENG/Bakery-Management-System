/* Bakery Management System - Settings Page JavaScript Interactivity */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Toast Notification Helper
    let toast = document.getElementById('settings_toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.id = 'settings_toast';
        toast.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span id="toast_message">Settings saved successfully!</span>
        `;
        document.body.appendChild(toast);
    }

    function showToast(message) {
        const toastMsg = document.getElementById('toast_message');
        if (toastMsg) toastMsg.textContent = message;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    // 2. Intercept All Settings Forms for Clean AJAX Submission & Feedback
    const settingsForms = document.querySelectorAll('.settings-form');
    settingsForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const actionUrl = this.getAttribute('action') || '/admin/settings/update';
            const formData = new FormData(this);
            const cardTitle = this.closest('.settings-glass-card')?.querySelector('.card-title-text h3')?.textContent || 'Settings';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                showToast(`${cardTitle} updated successfully!`);
                if (formData.has('language') && window.applyLanguage) {
                    window.applyLanguage(formData.get('language'));
                }
            })
            .catch(err => {
                showToast(`${cardTitle} saved!`);
                if (formData.has('language') && window.applyLanguage) {
                    window.applyLanguage(formData.get('language'));
                }
            });
        });
    });

    // 3. Logo Upload Live Image Preview
    const logoFileInput = document.getElementById('shop_logo_input');
    const logoPreviewBox = document.getElementById('logo_preview_box');

    if (logoFileInput && logoPreviewBox) {
        logoFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    logoPreviewBox.innerHTML = `<img src="${event.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;" alt="Shop Logo">`;
                    showToast('New logo selected! Click Save to apply.');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 4. iOS Segmented Control (Light/Dark Theme Picker)
    const currentTheme = localStorage.getItem('bakery_theme') || 'light';
    const segmentedButtons = document.querySelectorAll('.segmented-option');
    segmentedButtons.forEach(btn => {
        if (btn.getAttribute('data-theme') === currentTheme) {
            btn.classList.add('active');
        }
        btn.addEventListener('click', function() {
            segmentedButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const theme = this.getAttribute('data-theme');
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('bakery_theme', theme);
            showToast(`Theme switched to ${theme.toUpperCase()} mode!`);

            // Sync with backend
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('/admin/settings/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ theme: theme })
            }).catch(e => {});
        });
    });

    // 5. Notifications Toggle Switch Feedback
    const notifSwitch = document.getElementById('notifications_toggle');
    if (notifSwitch) {
        notifSwitch.addEventListener('change', function() {
            const status = this.checked ? 'ON' : 'OFF';
            showToast(`System notifications turned ${status}`);
        });
    }
});

