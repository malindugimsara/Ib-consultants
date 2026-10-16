/**
 * IB Consultants Admin Panel - Core Interaction Engine (admin.js)
 * Features:
 * - Floating Animated Toast Notifications
 * - Animated Delete Confirmation Modal & Row Exit Animation
 * - Mobile Off-canvas Navigation Drawer & Backdrop
 * - URL Query Message Auto-Detection & Sanitization
 */

(function () {
    'use strict';

    // -------------------------------------------------------------------------
    // 1. Toast Notification System
    // -------------------------------------------------------------------------
    function getToastContainer() {
        let container = document.getElementById('adminToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'adminToastContainer';
            container.className = 'admin-toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    window.showAdminToast = function (type, title, message, duration = 4000) {
        const container = getToastContainer();
        const toast = document.createElement('div');
        toast.className = `admin-toast toast-${type}`;

        let iconClass = 'fa-solid fa-circle-check';
        if (type === 'error') iconClass = 'fa-solid fa-triangle-exclamation';
        else if (type === 'warning') iconClass = 'fa-solid fa-circle-exclamation';
        else if (type === 'info') iconClass = 'fa-solid fa-circle-info';

        toast.innerHTML = `
            <div class="toast-icon-wrap">
                <i class="${iconClass}"></i>
            </div>
            <div class="toast-content">
                <h5 class="toast-title">${title}</h5>
                <p class="toast-message">${message}</p>
            </div>
            <button type="button" class="toast-close-btn" aria-label="Close notification">&times;</button>
            <div class="toast-progress-bar" style="animation-duration: ${duration}ms;"></div>
        `;

        container.appendChild(toast);

        let dismissTimeout = null;

        function dismissToast() {
            if (toast.classList.contains('hiding')) return;
            toast.classList.add('hiding');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 320);
        }

        // Close on X click
        const closeBtn = toast.querySelector('.toast-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                if (dismissTimeout) clearTimeout(dismissTimeout);
                dismissToast();
            });
        }

        // Pause timer on hover
        toast.addEventListener('mouseenter', () => {
            if (dismissTimeout) clearTimeout(dismissTimeout);
            const pBar = toast.querySelector('.toast-progress-bar');
            if (pBar) pBar.style.animationPlayState = 'paused';
        });

        toast.addEventListener('mouseleave', () => {
            const pBar = toast.querySelector('.toast-progress-bar');
            if (pBar) pBar.style.animationPlayState = 'running';
            dismissTimeout = setTimeout(dismissToast, 2000);
        });

        dismissTimeout = setTimeout(dismissToast, duration);
    };

    // Auto-detect message alerts and URL parameters
    function handleFlashMessages() {
        // 1. Check URL parameters like ?msg=deleted / updated / added
        const urlParams = new URLSearchParams(window.location.search);
        const msgParam = urlParams.get('msg');
        let triggered = false;

        if (msgParam) {
            const msgLower = msgParam.toLowerCase();
            if (msgLower === 'deleted') {
                window.showAdminToast('success', 'Deleted Successfully', 'The selected item was removed.');
                triggered = true;
            } else if (msgLower === 'updated') {
                window.showAdminToast('success', 'Changes Saved', 'Information updated successfully.');
                triggered = true;
            } else if (msgLower === 'added') {
                window.showAdminToast('success', 'Created Successfully', 'New record added to the database.');
                triggered = true;
            } else if (msgLower === 'published') {
                window.showAdminToast('success', 'Published', 'Item is now publicly visible.');
                triggered = true;
            } else if (msgLower === 'unpublished') {
                window.showAdminToast('info', 'Hidden', 'Item is hidden from the public website.');
                triggered = true;
            }

            if (triggered) {
                // Clean the URL without reloading
                urlParams.delete('msg');
                const cleanQuery = urlParams.toString();
                const cleanUrl = window.location.pathname + (cleanQuery ? '?' + cleanQuery : '');
                window.history.replaceState({}, document.title, cleanUrl);
            }
        }

        // 2. Also look for existing server-rendered .alert boxes and convert them to toasts
        const serverAlerts = document.querySelectorAll('.alert');
        serverAlerts.forEach(alertEl => {
            const text = alertEl.textContent.trim();
            if (text) {
                const isError = alertEl.classList.contains('alert-danger');
                const isWarning = alertEl.classList.contains('alert-warning');
                const isInfo = alertEl.classList.contains('alert-info');

                let type = 'success';
                let title = 'Action Completed';
                if (isError) {
                    type = 'error';
                    title = 'Notice';
                } else if (isWarning) {
                    type = 'warning';
                    title = 'Warning';
                } else if (isInfo) {
                    type = 'info';
                    title = 'Information';
                }

                // Hide ugly inline alert
                alertEl.style.display = 'none';
                window.showAdminToast(type, title, text);
            }
        });
    }

    // -------------------------------------------------------------------------
    // 2. Animated Delete Confirmation Modal & Row Exit Animation
    // -------------------------------------------------------------------------
    let modalOverlay = null;
    let pendingDeleteUrl = null;
    let pendingTargetRow = null;
    let isDeleting = false;

    function buildDeleteModal() {
        modalOverlay = document.getElementById('adminDeleteModalOverlay');
        if (modalOverlay) return modalOverlay;

        modalOverlay = document.createElement('div');
        modalOverlay.className = 'admin-modal-overlay';
        modalOverlay.id = 'adminDeleteModalOverlay';
        modalOverlay.innerHTML = `
            <div class="admin-modal-card">
                <div class="modal-warning-icon">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <h4 class="modal-title-text">Confirm Deletion</h4>
                <p class="modal-desc-text" id="adminDeleteModalDesc">
                    Are you sure you want to delete this record? This action cannot be undone.
                </p>
                <div class="modal-btn-row">
                    <button type="button" class="btn-modal-cancel" id="adminDeleteCancelBtn">Cancel</button>
                    <button type="button" class="btn-modal-delete" id="adminDeleteConfirmBtn">
                        <i class="fa-solid fa-trash-can"></i> Yes, Delete
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modalOverlay);

        const cancelBtn = modalOverlay.querySelector('#adminDeleteCancelBtn');
        const confirmBtn = modalOverlay.querySelector('#adminDeleteConfirmBtn');

        function closeModal() {
            if (isDeleting) return;
            modalOverlay.classList.remove('active');
            pendingDeleteUrl = null;
            pendingTargetRow = null;
        }

        cancelBtn.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) closeModal();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modalOverlay.classList.contains('active')) {
                closeModal();
            }
        });

        confirmBtn.addEventListener('click', () => {
            if (!pendingDeleteUrl || isDeleting) {
                closeModal();
                return;
            }

            isDeleting = true;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';

            const targetUrl = pendingDeleteUrl;
            const targetRow = pendingTargetRow;

            // Trigger smooth exit animation if table row exists
            if (targetRow && targetRow.tagName === 'TR') {
                targetRow.classList.add('row-deleting');
            }

            setTimeout(() => {
                window.location.href = targetUrl;
            }, 300);
        });

        return modalOverlay;
    }

    function openDeleteModal(url, itemName, rowElement) {
        buildDeleteModal();
        isDeleting = false;
        pendingDeleteUrl = url;
        pendingTargetRow = rowElement;

        const confirmBtn = modalOverlay.querySelector('#adminDeleteConfirmBtn');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fa-solid fa-trash-can"></i> Yes, Delete';
        }

        const descEl = document.getElementById('adminDeleteModalDesc');
        if (descEl) {
            if (itemName) {
                descEl.innerHTML = `Are you sure you want to delete <strong>${itemName}</strong>?<br>This action cannot be undone.`;
            } else {
                descEl.innerHTML = `Are you sure you want to delete this record?<br>This action cannot be undone.`;
            }
        }

        modalOverlay.classList.add('active');
    }

    function initDeleteInterceptors() {
        // Intercept clicks on delete action buttons or delete links
        document.addEventListener('click', function (e) {
            const deleteBtn = e.target.closest('.btn-action-delete, .btn-delete-confirm, a[href*="delete="]');
            if (!deleteBtn) return;

            // Ignore if it's logout or another form
            const hrefAttr = deleteBtn.getAttribute('href') || '';
            if (hrefAttr.includes('logout.php')) return;

            const url = deleteBtn.href || hrefAttr || deleteBtn.dataset.deleteUrl;
            if (!url) return;

            e.preventDefault();
            e.stopPropagation();

            // Detect row and item name
            const row = deleteBtn.closest('tr');
            let itemName = deleteBtn.dataset.itemName || '';

            if (!itemName && row) {
                const strongText = row.querySelector('strong');
                if (strongText) {
                    itemName = strongText.textContent.trim();
                }
            }

            openDeleteModal(url, itemName, row);
        }, true);
    }

    // -------------------------------------------------------------------------
    // 3. Animated Log Out Confirmation Modal
    // -------------------------------------------------------------------------
    let logoutOverlay = null;
    let pendingLogoutUrl = null;
    let isLoggingOut = false;

    function buildLogoutModal() {
        logoutOverlay = document.getElementById('adminLogoutModalOverlay');
        if (logoutOverlay) return logoutOverlay;

        logoutOverlay = document.createElement('div');
        logoutOverlay.className = 'admin-modal-overlay';
        logoutOverlay.id = 'adminLogoutModalOverlay';
        logoutOverlay.innerHTML = `
            <div class="admin-modal-card">
                <div class="modal-logout-icon">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </div>
                <h4 class="modal-title-text">Confirm Sign Out</h4>
                <p class="modal-desc-text">
                    Are you sure you want to log out from the <strong>IB Consultants Admin Portal</strong>?<br>
                    Your current session will end safely.
                </p>
                <div class="modal-btn-row">
                    <button type="button" class="btn-modal-cancel" id="adminLogoutCancelBtn">Stay Logged In</button>
                    <button type="button" class="btn-modal-logout" id="adminLogoutConfirmBtn">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Yes, Log Out
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(logoutOverlay);

        const cancelBtn = logoutOverlay.querySelector('#adminLogoutCancelBtn');
        const confirmBtn = logoutOverlay.querySelector('#adminLogoutConfirmBtn');

        function closeLogoutModal() {
            if (isLoggingOut) return;
            logoutOverlay.classList.remove('active');
            pendingLogoutUrl = null;
        }

        cancelBtn.addEventListener('click', closeLogoutModal);
        logoutOverlay.addEventListener('click', (e) => {
            if (e.target === logoutOverlay) closeLogoutModal();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && logoutOverlay.classList.contains('active')) {
                closeLogoutModal();
            }
        });

        confirmBtn.addEventListener('click', () => {
            if (!pendingLogoutUrl || isLoggingOut) {
                closeLogoutModal();
                return;
            }

            isLoggingOut = true;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Logging Out...';

            const targetUrl = pendingLogoutUrl;
            setTimeout(() => {
                window.location.href = targetUrl;
            }, 250);
        });

        return logoutOverlay;
    }

    function openLogoutModal(url) {
        buildLogoutModal();
        isLoggingOut = false;
        pendingLogoutUrl = url || 'logout.php';

        const confirmBtn = logoutOverlay.querySelector('#adminLogoutConfirmBtn');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fa-solid fa-arrow-right-from-bracket"></i> Yes, Log Out';
        }

        logoutOverlay.classList.add('active');
    }

    function initLogoutInterceptors() {
        // Intercept clicks on any logout button or link
        document.addEventListener('click', function (e) {
            const logoutBtn = e.target.closest('a[href*="logout.php"], .topbar-logout-btn, .btn-logout');
            if (!logoutBtn) return;

            e.preventDefault();
            e.stopPropagation();

            const url = logoutBtn.href || logoutBtn.getAttribute('href') || 'logout.php';
            openLogoutModal(url);
        }, true);
    }

    // -------------------------------------------------------------------------
    // 4. Mobile Off-Canvas Sidebar Drawer & Backdrop
    // -------------------------------------------------------------------------
    function initMobileNavigation() {
        let backdrop = document.getElementById('sidebarBackdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'sidebarBackdrop';
            backdrop.className = 'sidebar-backdrop';
            document.body.appendChild(backdrop);
        }

        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.getElementById('mobileSidebarToggle');
        const closeBtn = document.getElementById('sidebarCloseBtn');

        function openSidebar() {
            if (sidebar) sidebar.classList.add('show');
            if (backdrop) backdrop.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('show');
            if (backdrop) backdrop.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function (e) {
                e.preventDefault();
                openSidebar();
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                closeSidebar();
            });
        }

        backdrop.addEventListener('click', closeSidebar);

        // Close on window resize to desktop
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) {
                closeSidebar();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Initialize on DOM Ready or Immediately if Ready
    // -------------------------------------------------------------------------
    function init() {
        handleFlashMessages();
        initDeleteInterceptors();
        initLogoutInterceptors();
        initMobileNavigation();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
