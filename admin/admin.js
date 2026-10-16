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

    let lastToastSignature = '';
    let lastToastTimestamp = 0;

    window.showAdminToast = function (type, title, message, duration = 4000) {
        // Prevent duplicate toasts with the exact same content within 1.5 seconds
        const signature = `${type}|${title}|${message}`;
        const now = Date.now();
        if (signature === lastToastSignature && (now - lastToastTimestamp < 1500)) {
            return;
        }
        lastToastSignature = signature;
        lastToastTimestamp = now;

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

    // Auto-detect message alerts and URL parameters without double-triggering
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

                // CRITICAL: Hide and remove server-rendered .alert boxes to avoid duplicate second toast
                document.querySelectorAll('.alert').forEach(alertEl => {
                    alertEl.style.display = 'none';
                    alertEl.remove();
                });
                return;
            }
        }

        // 2. Fallback: only inspect server-rendered .alert boxes if URL parameter didn't already trigger a toast
        const serverAlerts = document.querySelectorAll('.alert:not([data-toasted="true"])');
        let alertTriggered = false;
        serverAlerts.forEach(alertEl => {
            alertEl.dataset.toasted = 'true';
            alertEl.style.display = 'none';
            const text = alertEl.textContent.trim();
            if (text && !alertTriggered) {
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

                window.showAdminToast(type, title, text);
                alertTriggered = true; // Only show one toast per page load from server alerts
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
    // 5. Admin Icon Picker & Interactive Gallery Explorer
    // -------------------------------------------------------------------------
    const ADMIN_ICON_CATALOG = [
        {
            category: 'Manufacturing & Operations',
            icons: [
                { class: 'fa-solid fa-industry', name: 'Factory / Plant', tags: 'factory plant manufacturing industry building' },
                { class: 'fa-solid fa-gears', name: 'Machinery & Operations', tags: 'gears machinery mechanics production engineering' },
                { class: 'fa-solid fa-boxes-stacked', name: 'Production & Inventory', tags: 'boxes production batches inventory warehouse stock' },
                { class: 'fa-solid fa-warehouse', name: 'Warehouse & Storage', tags: 'warehouse storage logistics facility depot' },
                { class: 'fa-solid fa-hard-hat', name: 'Factory Safety & HSE', tags: 'safety helmet hardhat protection hse worker' },
                { class: 'fa-solid fa-screwdriver-wrench', name: 'Engineering & Maintenance', tags: 'tools maintenance engineering repair mechanics' },
                { class: 'fa-solid fa-truck-ramp-box', name: 'Material Handling & Logistics', tags: 'forklift material handling logistics cargo' },
                { class: 'fa-solid fa-microchip', name: 'Automation & Electronics', tags: 'chip microchip hardware automation technology' },
                { class: 'fa-solid fa-robot', name: 'Industry 4.0 & Robotics', tags: 'robot automation smart manufacturing industry 4.0' }
            ]
        },
        {
            category: 'Strategy, Growth & Consulting',
            icons: [
                { class: 'fa-solid fa-layer-group', name: 'Strategy & Frameworks', tags: 'strategy layers frameworks structure planning' },
                { class: 'fa-solid fa-chart-line', name: 'Growth & Trends', tags: 'chart growth line performance sales analytics' },
                { class: 'fa-solid fa-arrow-trend-up', name: 'Scaling & High Performance', tags: 'trend up scaling performance progress acceleration' },
                { class: 'fa-solid fa-magnifying-glass-chart', name: 'Diagnostics & Health Check', tags: 'audit diagnostics health check assessment research' },
                { class: 'fa-solid fa-diagram-project', name: 'Workflow SOPs & Process', tags: 'workflow sops process architecture flow diagram' },
                { class: 'fa-solid fa-bullseye', name: 'Target & KPIs / OKRs', tags: 'target bullseye goals kpi okr objectives' },
                { class: 'fa-solid fa-arrows-spin', name: 'Kaizen & Continuous Flow', tags: 'kaizen continuous improvement cycle workflow loop' },
                { class: 'fa-solid fa-chess-knight', name: 'Tactics & Strategic Moves', tags: 'chess tactics knight strategy competition advantage' },
                { class: 'fa-solid fa-compass', name: 'Corporate Vision & Direction', tags: 'compass vision direction strategy purpose advisory' },
                { class: 'fa-solid fa-road', name: 'Implementation Roadmap', tags: 'roadmap milestones execution timeline path' }
            ]
        },
        {
            category: 'Finance, Costing & Margins',
            icons: [
                { class: 'fa-solid fa-coins', name: 'Cost Reduction & Margins', tags: 'coins cost margin savings pricing money finance' },
                { class: 'fa-solid fa-sack-dollar', name: 'Profit Optimization & Revenue', tags: 'profit revenue income earnings dollar cash' },
                { class: 'fa-solid fa-calculator', name: 'Cost Accounting & Unit Cost', tags: 'calculator costing calculation accounting finance' },
                { class: 'fa-solid fa-scale-balanced', name: 'Governance & Balance Sheet', tags: 'scale balance governance law financial stability' },
                { class: 'fa-solid fa-file-invoice-dollar', name: 'Commercial Invoicing & Terms', tags: 'invoice billing commercial pricing accounting terms' },
                { class: 'fa-solid fa-wallet', name: 'Cash Flow & Working Capital', tags: 'wallet cash money funds working capital' },
                { class: 'fa-solid fa-piggy-bank', name: 'Cost Savings & Reserves', tags: 'piggy bank savings reserves retention finance' },
                { class: 'fa-solid fa-chart-pie', name: 'Budgeting & Cost Allocation', tags: 'chart pie budget allocation expenditure shares' }
            ]
        },
        {
            category: 'Global Trade, Export & Supply Chain',
            icons: [
                { class: 'fa-solid fa-globe', name: 'Global Export & B2B Markets', tags: 'globe world international trade export global market' },
                { class: 'fa-solid fa-ship', name: 'Ocean Freight & Sea Cargo', tags: 'ship sea ocean freight export vessel container maritime' },
                { class: 'fa-solid fa-plane-departure', name: 'Air Freight & Rapid Export', tags: 'airplane flight cargo air freight fast shipment' },
                { class: 'fa-solid fa-truck-fast', name: 'Express Logistics & Distribution', tags: 'truck delivery transport freight distribution logistics' },
                { class: 'fa-solid fa-handshake', name: 'Buyer Matchmaking & B2B Deals', tags: 'handshake partnership deals agreements trade contracts' },
                { class: 'fa-solid fa-map-location-dot', name: 'Market Territory & Distribution', tags: 'map location territory market expansion distribution' },
                { class: 'fa-solid fa-box-open', name: 'Packaging & Export Dispatch', tags: 'box package unboxing delivery dispatch goods' },
                { class: 'fa-solid fa-passport', name: 'Trade Compliance & Customs', tags: 'passport customs compliance borders tariffs trade' }
            ]
        },
        {
            category: 'Leadership, HR & Organization',
            icons: [
                { class: 'fa-solid fa-user-tie', name: 'Executive Leadership & C-Suite', tags: 'executive leadership manager ceo director tie professional' },
                { class: 'fa-solid fa-people-group', name: 'High-Performance Teams & HR', tags: 'team people human resources staff workforce group' },
                { class: 'fa-solid fa-chalkboard-user', name: 'Workshops & Training', tags: 'training teaching chalkboard workshop mentoring coaching' },
                { class: 'fa-solid fa-users-gear', name: 'Organizational Structuring', tags: 'organization structure manpower management operations' },
                { class: 'fa-solid fa-graduation-cap', name: 'Academy & Professional Certs', tags: 'graduation diploma academy education training diploma cert' },
                { class: 'fa-solid fa-briefcase', name: 'Corporate Management & Advisory', tags: 'briefcase corporate business advisory management enterprise' }
            ]
        },
        {
            category: 'Quality, ISO & Compliance',
            icons: [
                { class: 'fa-solid fa-award', name: 'Quality Excellence & Awards', tags: 'award ribbon quality excellence trophy honors' },
                { class: 'fa-solid fa-certificate', name: 'ISO & Industry Certification', tags: 'certificate iso standard accreditation license compliance' },
                { class: 'fa-solid fa-shield-halved', name: 'Risk Governance & Compliance', tags: 'shield security compliance protection risk legal' },
                { class: 'fa-solid fa-clipboard-check', name: 'Quality Audits & Checklists', tags: 'audit clipboard check verification inspection review' },
                { class: 'fa-solid fa-stamp', name: 'Official Endorsement & Seal', tags: 'stamp seal approval verified official certified' },
                { class: 'fa-solid fa-star', name: 'Benchmark Rating & Prestige', tags: 'star rating benchmark review prestige top' }
            ]
        },
        {
            category: 'Innovation & Digital Tech',
            icons: [
                { class: 'fa-solid fa-rocket', name: 'Enterprise Acceleration & Scale', tags: 'rocket startup scaleup launch acceleration speed' },
                { class: 'fa-solid fa-lightbulb', name: 'Innovation & Creative Ideation', tags: 'lightbulb idea innovation creativity solution thinking' },
                { class: 'fa-solid fa-laptop-code', name: 'Digital Systems & Software/ERP', tags: 'laptop digital code software systems erp it' },
                { class: 'fa-solid fa-cloud-arrow-up', name: 'Cloud Platforms & Connectivity', tags: 'cloud upload systems digital infrastructure connectivity' },
                { class: 'fa-solid fa-brain', name: 'AI, Intelligence & Analytics', tags: 'brain ai artificial intelligence smart thinking analytics' }
            ]
        },
        {
            category: 'Bexon Theme Icons',
            icons: [
                { class: 'tji-service-1', name: 'Bexon Chart & Analytics', tags: 'bexon service chart analytics growth report' },
                { class: 'tji-service-2', name: 'Bexon Global Network', tags: 'bexon network global world globe connection' },
                { class: 'tji-service-3', name: 'Bexon Industrial Gear', tags: 'bexon gear machinery settings process service' },
                { class: 'tji-service-4', name: 'Bexon Quality Ribbon', tags: 'bexon ribbon award star quality excellence' },
                { class: 'tji-strategy', name: 'Bexon Strategy Matrix', tags: 'bexon strategy matrix plan business' },
                { class: 'tji-operations', name: 'Bexon Operational Flow', tags: 'bexon operations workflow logistics management' },
                { class: 'tji-performance', name: 'Bexon Performance Meter', tags: 'bexon performance meter speed kpi gauge' },
                { class: 'tji-process-1', name: 'Bexon Process Architecture', tags: 'bexon process flow system design structure' },
                { class: 'tji-dart-board', name: 'Bexon Precision Target', tags: 'bexon target dartboard precision aim goals' }
            ]
        }
    ];

    function findIconMeta(iconClass) {
        const clean = (iconClass || '').trim();
        for (const cat of ADMIN_ICON_CATALOG) {
            for (const item of cat.icons) {
                if (item.class === clean) {
                    return item;
                }
            }
        }
        return null;
    }

    function updateIconPickerUI(inputId, iconClass, triggerPop = true) {
        const clean = (iconClass || '').trim();
        const rawInput = document.getElementById(inputId);
        const select = document.getElementById('select_' + inputId);
        const previewIcon = document.getElementById('preview_icon_' + inputId);
        const previewName = document.getElementById('preview_name_' + inputId);
        const previewClass = document.getElementById('preview_class_' + inputId);

        if (rawInput && rawInput.value !== clean) {
            rawInput.value = clean;
        }

        const meta = findIconMeta(clean);
        const displayName = meta ? meta.name : (clean ? 'Custom Icon Class' : 'No Icon Selected');

        if (previewIcon) {
            previewIcon.className = clean || 'fa-solid fa-icons';
            if (triggerPop) {
                const badgeFrame = previewIcon.closest('.admin-icon-badge-frame');
                if (badgeFrame) {
                    badgeFrame.classList.remove('admin-icon-pop');
                    void badgeFrame.offsetWidth; // trigger reflow
                    badgeFrame.classList.add('admin-icon-pop');
                }
            }
        }

        if (previewName) {
            previewName.textContent = displayName;
        }

        if (previewClass) {
            previewClass.textContent = clean || 'none';
        }

        if (select) {
            if (meta) {
                select.value = clean;
            } else if (clean) {
                select.value = '__custom__';
            } else {
                select.value = '';
            }
        }
    }

    window.handleAdminIconDropdownChange = function (inputId, selectedValue) {
        if (selectedValue === '__custom__') {
            const rawInput = document.getElementById(inputId);
            if (rawInput) {
                rawInput.focus();
                rawInput.select();
            }
            return;
        }
        updateIconPickerUI(inputId, selectedValue, true);
    };

    window.handleAdminIconInputChange = function (inputId, value) {
        updateIconPickerUI(inputId, value, false);
    };

    window.handleAdminIconPreviewRefresh = function (inputId) {
        const rawInput = document.getElementById(inputId);
        const val = rawInput ? rawInput.value : '';
        updateIconPickerUI(inputId, val, true);
        if (window.showAdminToast) {
            window.showAdminToast('info', 'Preview Refreshed', `Rendering '${val}'`);
        }
    };

    // -------------------------------------------------------------------------
    // Icon Gallery Modal Implementation
    // -------------------------------------------------------------------------
    let currentGalleryTargetId = null;
    let galleryModalEl = null;

    function buildGalleryModal() {
        if (galleryModalEl) return galleryModalEl;

        galleryModalEl = document.createElement('div');
        galleryModalEl.id = 'adminIconGalleryModal';
        galleryModalEl.className = 'admin-icon-modal-overlay';
        galleryModalEl.setAttribute('role', 'dialog');
        galleryModalEl.setAttribute('aria-modal', 'true');

        let totalIcons = 0;
        ADMIN_ICON_CATALOG.forEach(c => totalIcons += c.icons.length);

        galleryModalEl.innerHTML = `
            <div class="admin-icon-modal-container">
                <div class="admin-icon-modal-header">
                    <h4 class="admin-icon-modal-title">
                        <i class="fa-solid fa-shapes text-gold"></i>
                        Icon Library & Visual Explorer
                        <span class="badge bg-gold text-dark ms-2" style="font-size: 11px;">${totalIcons} Curated</span>
                    </h4>
                    <button type="button" class="admin-icon-modal-close-btn" aria-label="Close dialog">&times;</button>
                </div>
                
                <div class="admin-icon-modal-toolbar">
                    <div class="admin-icon-search-input-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="adminIconSearchInput" class="admin-icon-search-input" 
                               placeholder="Search icons by keyword (e.g. factory, chart, money, globe, team, quality)...">
                    </div>
                    <div class="admin-icon-category-pills" id="adminIconCategoryPills">
                        <button type="button" class="admin-icon-category-pill active" data-cat="all">All Icons (${totalIcons})</button>
                        ${ADMIN_ICON_CATALOG.map(c => `
                            <button type="button" class="admin-icon-category-pill" data-cat="${c.category}">
                                ${c.category.split('&')[0].trim()} (${c.icons.length})
                            </button>
                        `).join('')}
                    </div>
                </div>

                <div class="admin-icon-grid-container">
                    <div class="admin-icon-grid" id="adminIconGrid"></div>
                </div>

                <div class="admin-icon-modal-footer">
                    <div class="admin-icon-modal-footer-tip">
                        <i class="fa-solid fa-lightbulb text-warning"></i>
                        <span>Need more icons? Browse 2,000+ free icons on FontAwesome</span>
                        <a href="https://fontawesome.com/search?o=r&m=free" target="_blank" rel="noopener noreferrer" 
                           class="btn btn-sm btn-outline-primary ms-2 py-1 px-2" style="font-size: 11px;">
                            <i class="fa-brands fa-font-awesome me-1"></i> FontAwesome Directory <i class="fa-solid fa-arrow-up-right-from-square ms-1" style="font-size: 9px;"></i>
                        </a>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm px-3 admin-icon-modal-cancel">Close</button>
                </div>
            </div>
        `;

        document.body.appendChild(galleryModalEl);

        // Event listeners
        const closeBtn = galleryModalEl.querySelector('.admin-icon-modal-close-btn');
        const cancelBtn = galleryModalEl.querySelector('.admin-icon-modal-cancel');
        const searchInput = galleryModalEl.querySelector('#adminIconSearchInput');
        const pillsContainer = galleryModalEl.querySelector('#adminIconCategoryPills');

        function closeModal() {
            galleryModalEl.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        galleryModalEl.addEventListener('click', function (e) {
            if (e.target === galleryModalEl) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && galleryModalEl.classList.contains('active')) {
                closeModal();
            }
        });

        // Filter and Search logic
        let activeCategory = 'all';
        let searchQuery = '';

        function renderCards() {
            const grid = galleryModalEl.querySelector('#adminIconGrid');
            if (!grid) return;

            const targetInput = currentGalleryTargetId ? document.getElementById(currentGalleryTargetId) : null;
            const currentSelectedClass = targetInput ? targetInput.value.trim() : '';

            let matchedIcons = [];
            ADMIN_ICON_CATALOG.forEach(cat => {
                if (activeCategory === 'all' || activeCategory === cat.category) {
                    cat.icons.forEach(ico => {
                        const term = searchQuery.toLowerCase().trim();
                        if (!term || 
                            ico.name.toLowerCase().includes(term) || 
                            ico.class.toLowerCase().includes(term) || 
                            ico.tags.toLowerCase().includes(term) ||
                            cat.category.toLowerCase().includes(term)) {
                            matchedIcons.push({ ...ico, catName: cat.category });
                        }
                    });
                }
            });

            if (matchedIcons.length === 0) {
                grid.innerHTML = `
                    <div class="col-12 text-center py-5" style="grid-column: 1 / -1;">
                        <i class="fa-solid fa-face-frown text-muted mb-2" style="font-size: 36px;"></i>
                        <h6 class="text-muted fw-bold">No icons found matching "${searchQuery}"</h6>
                        <p class="text-muted small mb-3">Try searching for generic terms like "factory", "growth", "money", or browse FontAwesome.</p>
                        <a href="https://fontawesome.com/search?o=r&m=free&q=${encodeURIComponent(searchQuery)}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fa-brands fa-font-awesome me-1"></i> Search "${searchQuery}" on FontAwesome.com
                        </a>
                    </div>
                `;
                return;
            }

            grid.innerHTML = matchedIcons.map(ico => {
                const isSelected = (ico.class === currentSelectedClass);
                return `
                    <div class="admin-icon-grid-card ${isSelected ? 'is-selected' : ''}" 
                         data-class="${ico.class}" data-name="${ico.name}" title="${ico.name} (${ico.class})">
                        <div class="admin-icon-grid-card-symbol">
                            <i class="${ico.class}"></i>
                        </div>
                        <div class="admin-icon-grid-card-name">${ico.name}</div>
                        <div class="admin-icon-grid-card-code">${ico.class}</div>
                    </div>
                `;
            }).join('');

            // Card click handlers
            grid.querySelectorAll('.admin-icon-grid-card').forEach(card => {
                card.addEventListener('click', function () {
                    const chosenClass = this.getAttribute('data-class');
                    const chosenName = this.getAttribute('data-name');
                    if (currentGalleryTargetId) {
                        updateIconPickerUI(currentGalleryTargetId, chosenClass, true);
                        if (window.showAdminToast) {
                            window.showAdminToast('success', 'Icon Selected', `${chosenName} (${chosenClass})`);
                        }
                    }
                    closeModal();
                });
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                searchQuery = this.value;
                renderCards();
            });
        }

        if (pillsContainer) {
            pillsContainer.addEventListener('click', function (e) {
                const pill = e.target.closest('.admin-icon-category-pill');
                if (!pill) return;
                pillsContainer.querySelectorAll('.admin-icon-category-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                activeCategory = pill.getAttribute('data-cat');
                renderCards();
            });
        }

        galleryModalEl.renderCards = renderCards;
        return galleryModalEl;
    }

    window.openAdminIconGallery = function (targetInputId) {
        currentGalleryTargetId = targetInputId;
        const modal = buildGalleryModal();
        const searchInput = modal.querySelector('#adminIconSearchInput');
        if (searchInput) searchInput.value = '';

        const pillsContainer = modal.querySelector('#adminIconCategoryPills');
        if (pillsContainer) {
            pillsContainer.querySelectorAll('.admin-icon-category-pill').forEach((p, idx) => {
                p.classList.toggle('active', idx === 0);
            });
        }

        if (modal.renderCards) modal.renderCards();

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            if (searchInput) searchInput.focus();
        }, 150);
    };

    // -------------------------------------------------------------------------
    // -------------------------------------------------------------------------
    // 6. Universal AJAX Publish / Hide Status Toggle Interceptor
    // Prevents page reload and jumping to the top of the page on hide/show
    // -------------------------------------------------------------------------
    function initStatusToggleInterceptors() {
        document.addEventListener('click', function (e) {
            const toggleBtn = e.target.closest('a[href*="toggle_publish="], .btn-action-hide, .btn-action-publish');
            if (!toggleBtn) return;

            const href = toggleBtn.getAttribute('href');
            if (!href || href === '#' || href.startsWith('javascript:')) {
                return;
            }

            // Prevent browser from reloading the page and jumping to the top
            e.preventDefault();
            e.stopPropagation();

            const iconEl = toggleBtn.querySelector('i');
            const originalIconClass = iconEl ? iconEl.className : '';
            const row = toggleBtn.closest('tr');

            // Indicate processing with spinner
            if (iconEl) {
                iconEl.className = 'fa-solid fa-spinner fa-spin';
            }
            toggleBtn.style.pointerEvents = 'none';

            // Construct AJAX request URL
            const urlObj = new URL(href, window.location.href);
            urlObj.searchParams.set('ajax', '1');

            fetch(urlObj.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                toggleBtn.style.pointerEvents = '';
                if (data.status === 'success') {
                    const newStatus = parseInt(data.new_status, 10);

                    // 1. Update the status badge in the same table row
                    if (row) {
                        const badge = row.querySelector('.badge-published, .badge-hidden');
                        if (badge) {
                            if (newStatus === 1) {
                                badge.className = 'badge-published';
                                badge.textContent = 'Published';
                            } else {
                                badge.className = 'badge-hidden';
                                badge.textContent = 'Hidden';
                            }
                            badge.classList.remove('status-updated-flash');
                            void badge.offsetWidth; // Trigger reflow for animation
                            badge.classList.add('status-updated-flash');
                        }
                    }

                    // 2. Update toggle button icon and styling
                    if (newStatus === 1) {
                        toggleBtn.className = 'btn-action btn-action-hide';
                        toggleBtn.title = 'Hide / Unpublish';
                        if (iconEl) iconEl.className = 'fa-solid fa-eye-slash';
                    } else {
                        toggleBtn.className = 'btn-action btn-action-publish';
                        toggleBtn.title = 'Show / Publish';
                        if (iconEl) iconEl.className = 'fa-solid fa-eye';
                    }

                    // 3. Update href so subsequent toggles will work seamlessly
                    urlObj.searchParams.set('toggle_publish', newStatus);
                    urlObj.searchParams.delete('ajax');
                    toggleBtn.setAttribute('href', urlObj.pathname + urlObj.search);

                    // 4. Show non-intrusive floating toast notification
                    if (window.showAdminToast) {
                        const titleText = (newStatus === 1) ? 'Status: Published' : 'Status: Hidden';
                        const msgText = (newStatus === 1) 
                            ? 'Item is now published and live on website.' 
                            : 'Item has been hidden from public view.';
                        window.showAdminToast('success', titleText, msgText);
                    }
                } else {
                    if (iconEl) iconEl.className = originalIconClass;
                    if (window.showAdminToast) {
                        window.showAdminToast('error', 'Status Update Failed', data.message || 'Database error occurred.');
                    }
                }
            })
            .catch(err => {
                toggleBtn.style.pointerEvents = '';
                if (iconEl) iconEl.className = originalIconClass;
                console.error('AJAX Status Toggle failed:', err);
                if (window.showAdminToast) {
                    window.showAdminToast('error', 'Network Error', 'Could not complete status toggle. Please try again.');
                }
            });
        }, true);
    }

    // -------------------------------------------------------------------------
    // 7. Universal Scroll Position Preservation System
    // Prevents jumping to the top of the page when performing actions or submitting forms
    // -------------------------------------------------------------------------
    function initScrollPreservation() {
        const pageKey = 'admin_scroll_' + window.location.pathname;

        // Restore scroll position after page reloads
        const savedScrollY = sessionStorage.getItem(pageKey);
        if (savedScrollY !== null) {
            const targetY = parseInt(savedScrollY, 10);
            if (!isNaN(targetY) && targetY > 0) {
                // Instantly restore scroll without jarring smooth animation
                window.scrollTo({ top: targetY, behavior: 'instant' });
                // Double-check after rendering finishes in case layout expands
                setTimeout(() => {
                    window.scrollTo({ top: targetY, behavior: 'instant' });
                }, 60);
            }
            sessionStorage.removeItem(pageKey);
        }

        // Save scroll position before leaving or reloading
        window.addEventListener('beforeunload', () => {
            sessionStorage.setItem(pageKey, window.scrollY);
        });

        // Save scroll position when submitting any form
        document.addEventListener('submit', () => {
            sessionStorage.setItem(pageKey, window.scrollY);
        }, true);

        // Clear saved scroll position when navigating explicitly via sidebar menu
        document.addEventListener('click', (e) => {
            const navLink = e.target.closest('.sidebar-nav a, .sidebar-brand');
            if (navLink) {
                const href = navLink.getAttribute('href');
                if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
                    try {
                        const targetUrl = new URL(href, window.location.href);
                        sessionStorage.removeItem('admin_scroll_' + targetUrl.pathname);
                    } catch (err) {}
                }
            }
        });
    }

    // Direct helper for explicit calls
    window.toggleAjaxStatus = function (url, id, currentStatus, actionName, btnElement, badgeId) {
        const icon = btnElement ? btnElement.querySelector('i') : null;
        const originalClass = icon ? icon.className : '';
        if (icon) icon.className = 'fa-solid fa-spinner fa-spin';

        const formData = new FormData();
        formData.append(actionName, currentStatus);
        formData.append('id', id);

        fetch(url + (url.includes('?') ? '&' : '?') + 'ajax=1', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const newStatus = parseInt(data.new_status, 10);
                const badgeTd = document.getElementById(badgeId) || (btnElement ? btnElement.closest('tr').querySelector('.badge-published, .badge-hidden') : null);

                if (btnElement) {
                    btnElement.setAttribute('onclick', `toggleAjaxStatus('${url}', ${id}, ${newStatus}, '${actionName}', this, '${badgeId}')`);
                    if (newStatus === 1) {
                        btnElement.className = 'btn-action btn-action-hide';
                        btnElement.title = 'Hide / Unpublish';
                        if (icon) icon.className = 'fa-solid fa-eye-slash';
                    } else {
                        btnElement.className = 'btn-action btn-action-publish';
                        btnElement.title = 'Show / Publish';
                        if (icon) icon.className = 'fa-solid fa-eye';
                    }
                }

                if (badgeTd) {
                    badgeTd.className = (newStatus === 1) ? 'badge-published' : 'badge-hidden';
                    badgeTd.textContent = (newStatus === 1) ? 'Published' : 'Hidden';
                }

                if (window.showAdminToast) {
                    window.showAdminToast('success', 'Status Updated', 'Status changed successfully!');
                }
            } else {
                if (icon) icon.className = originalClass;
                if (window.showAdminToast) {
                    window.showAdminToast('error', 'Error', 'Failed to update status.');
                }
            }
        })
        .catch(err => {
            if (icon) icon.className = originalClass;
            console.error('toggleAjaxStatus error:', err);
        });
    };

    // -------------------------------------------------------------------------
    // Initialize on DOM Ready or Immediately if Ready
    // -------------------------------------------------------------------------
    let isInitialized = false;
    function init() {
        if (isInitialized) return;
        isInitialized = true;

        handleFlashMessages();
        initDeleteInterceptors();
        initLogoutInterceptors();
        initMobileNavigation();
        initStatusToggleInterceptors();
        initScrollPreservation();

        // Auto-sync any existing icon fields on the page
        document.querySelectorAll('.admin-icon-picker-box').forEach(box => {
            const fieldName = box.getAttribute('data-icon-field');
            if (fieldName) {
                const rawInput = document.getElementById(fieldName);
                if (rawInput) {
                    updateIconPickerUI(fieldName, rawInput.value, false);
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();