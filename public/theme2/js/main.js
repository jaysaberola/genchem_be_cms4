/**
 * Pato - Main JavaScript
 * Version: 2.0.0 | January 2026
 * Vanilla JS (no jQuery dependency)
 */

(function() {
    'use strict';

    let glightboxInstance = null;
    let glightboxRefreshTimer = null;
    let isUpdatingLightbox = false;
    let isLightboxOpen = false;

    // In Next.js we load scripts with strategy=afterInteractive, which can run
    // after DOMContentLoaded. Run init immediately if the DOM is already ready.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        const path = (window.location && window.location.pathname) ? window.location.pathname : '';
        const isProductsPage = /^\/public\/(products|product)(\/|$)/.test(path);

        initWebPDetection();
        initPageTransitions();
        initBackToTop();
        initFixedHeader();
        initSidebar();
        initCopyrightYear();

        // Products pages don't use gallery/lightbox/parallax/video/datepicker features.
        // Skipping them reduces CPU work and avoids extra observers/timeouts.
        if (isProductsPage) return;

        initFlatpickr();
        initVideoModal();
        initGalleryFilter();
        initGalleryPagination();
        initGLightbox();
        initDynamicContentObserver();
        initParallax();
    }

    function applyGalleryItemVisibility(item) {
        if (!item) return;
        const filterHidden = item.dataset && item.dataset.filterHidden === '1';
        const pageHidden = item.dataset && item.dataset.pageHidden === '1';

        if (filterHidden || pageHidden) {
            item.style.display = 'none';
            item.classList.add('hidden');
        } else {
            item.style.display = '';
            item.classList.remove('hidden');
        }
    }

    /**
     * Dynamic Copyright Year
     */
    function initCopyrightYear() {
        const yearEl = document.getElementById('copyright-year');
        if (yearEl) {
            yearEl.textContent = new Date().getFullYear();
        }
    }

    /**
     * WebP Detection and Image Conversion
     * Detects WebP support and converts all JPG images to WebP
     */
    function initWebPDetection() {
        const webpTest = new Image();
        webpTest.onload = webpTest.onerror = function() {
            const supportsWebP = webpTest.height === 2;

            if (supportsWebP) {
                document.documentElement.classList.add('webp');
                // NOTE:
                // This template used to rewrite all .jpg URLs to .webp.
                // In this Next.js project, the /public/images folder ships mostly .jpg files,
                // and the corresponding .webp files do not exist, causing lots of 404s.
                // If you add real .webp assets later, you can re-enable this.
                // convertImagesToWebP();
            } else {
                document.documentElement.classList.add('no-webp');
            }
        };
        webpTest.src = 'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
    }

    /**
     * Convert all JPG images to WebP format
     */
    function convertImagesToWebP() {
        // Convert img src attributes
        document.querySelectorAll('img[src$=".jpg"]').forEach(img => {
            const webpSrc = img.src.replace(/\.jpg$/i, '.webp');
            img.src = webpSrc;
        });

        // Convert background-image inline styles
        document.querySelectorAll('[style*="background-image"]').forEach(el => {
            const style = el.getAttribute('style');
            if (style && style.includes('.jpg')) {
                el.setAttribute('style', style.replace(/\.jpg/gi, '.webp'));
            }
        });

        // Convert href attributes for lightbox links
        document.querySelectorAll('a[href$=".jpg"]').forEach(link => {
            link.href = link.href.replace(/\.jpg$/i, '.webp');
        });

        // Convert data-logofixed attributes
        document.querySelectorAll('[data-logofixed]').forEach(el => {
            const logoFixed = el.dataset.logofixed;
            if (logoFixed && logoFixed.includes('.png')) {
                // Keep PNG for logos (usually have transparency)
            }
        });
    }

    /**
     * Page Transitions (CSS-based replacement for Animsition)
     */
    function initPageTransitions() {
        // Add fade-in animation on page load
        document.body.classList.add('page-loaded');

        // Add fade-out on internal link clicks
        document
            .querySelectorAll(
                'a:not([target="_blank"]):not([href^="#"]):not([href^="mailto"]):not([href^="tel"]):not(.glightbox):not([data-lightbox])'
            )
            .forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.classList && this.classList.contains('glightbox')) return;
                const href = this.getAttribute('href');
                if (href && !href.startsWith('http') && !href.startsWith('javascript')) {
                    e.preventDefault();
                    document.body.classList.add('page-leaving');
                    setTimeout(() => {
                        window.location.href = href;
                    }, 300);
                }
            });
        });
    }

    /**
     * Back to Top Button
     */
    function initBackToTop() {
        const btn = document.getElementById('myBtn');
        if (!btn) return;

        const windowH = window.innerHeight / 2;

        window.addEventListener('scroll', () => {
            if (window.scrollY > windowH) {
                btn.style.display = 'flex';
            } else {
                btn.style.display = 'none';
            }
        });

        btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /**
     * Flatpickr Date Picker
     */
    function initFlatpickr() {
        // Support both old class (.my-calendar) and new class (.js-datepicker)
        const calendars = document.querySelectorAll('.my-calendar, .js-datepicker');
        if (typeof flatpickr === 'undefined' || calendars.length === 0) return;

        calendars.forEach(calendar => {
            flatpickr(calendar, {
                dateFormat: 'd/m/Y',
                minDate: 'today',
                disableMobile: true,
            });
        });

        // Calendar icon click handler
        document.querySelectorAll('.btn-calendar').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const input = btn.closest('.wrap-inputdate').querySelector('.my-calendar, .js-datepicker');
                if (input && input._flatpickr) {
                    input._flatpickr.open();
                }
            });
        });
    }

    /**
     * Video Modal Handling
     */
    function initVideoModal() {
        const videoContainer = document.querySelector('.video-mo-01');
        if (!videoContainer) return;

        const iframe = videoContainer.querySelector('iframe');
        if (!iframe) return;

        const srcOld = iframe.getAttribute('src');

        // Handle modal show - using Bootstrap 5 events
        const modal = document.getElementById('modal-video-01');
        if (modal) {
            modal.addEventListener('show.bs.modal', () => {
                iframe.src = srcOld + '&autoplay=1';
                setTimeout(() => {
                    videoContainer.style.opacity = '1';
                }, 300);
            });

            modal.addEventListener('hide.bs.modal', () => {
                iframe.src = srcOld;
                videoContainer.style.opacity = '0';
            });
        }
    }

    /**
     * Fixed Header on Scroll
     */
    function initFixedHeader() {
        const header = document.querySelector('header');
        if (!header) return;

        const logo = header.querySelector('.logo img');
        if (!logo) return;

        const linkLogo1 = logo.getAttribute('src');
        const linkLogo2 = logo.dataset.logofixed;

        window.addEventListener('scroll', () => {
            if (window.scrollY > 5 && window.innerWidth > 992) {
                logo.setAttribute('src', linkLogo2);
                header.classList.add('header-fixed');
            } else {
                header.classList.remove('header-fixed');
                logo.setAttribute('src', linkLogo1);
            }
        });
    }

    /**
     * Sidebar Toggle
     */
    function initSidebar() {
        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'overlay-sidebar trans-0-4';
        document.body.appendChild(overlay);

        const sidebar = document.querySelector('.sidebar');
        const btnShow = document.querySelector('.btn-show-sidebar');
        const btnHide = document.querySelector('.btn-hide-sidebar');

        if (!sidebar) return;

        if (btnShow) {
            btnShow.addEventListener('click', () => {
                sidebar.classList.add('show-sidebar');
                overlay.classList.add('show-overlay-sidebar');
            });
        }

        if (btnHide) {
            btnHide.addEventListener('click', () => {
                sidebar.classList.remove('show-sidebar');
                overlay.classList.remove('show-overlay-sidebar');
            });
        }

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('show-sidebar');
            overlay.classList.remove('show-overlay-sidebar');
        });
    }

    /**
     * Gallery Filter (CSS Grid replacement for Isotope)
     */
    function initGalleryFilter() {
        const filterGroups = document.querySelectorAll('.filter-tope-group');
        if (!filterGroups || filterGroups.length === 0) return;

        filterGroups.forEach(filterGroup => {
            if (filterGroup.dataset && filterGroup.dataset.patoFilterInit === '1') return;
            if (filterGroup.dataset) filterGroup.dataset.patoFilterInit = '1';

            const scope = filterGroup.closest('.section-gallery') || document;
            const grid = scope.querySelector('.isotope-grid') || document.querySelector('.isotope-grid');
            if (!grid) return;

            const items = grid.querySelectorAll('.isotope-item');
            const labels = filterGroup.querySelectorAll('.label-gallery');
            if (!labels || labels.length === 0) return;

            labels.forEach(label => {
                label.addEventListener('click', (e) => {
                    if (e && typeof e.preventDefault === 'function') e.preventDefault();

                    // Update active state
                    labels.forEach(l => l.classList.remove('is-actived'));
                    label.classList.add('is-actived');

                    // Filter items
                    const filter = label.dataset.filter;
                    const className = (filter || '').replace('.', '');

                    items.forEach(item => {
                        const shouldShow = !filter || filter === '*' || item.classList.contains(className);
                        if (item.dataset) item.dataset.filterHidden = shouldShow ? '0' : '1';
                        applyGalleryItemVisibility(item);
                    });

                    // Reset to page 1 after filter changes
                    initGalleryPagination();
                });
            });
        });
    }

    /**
     * Gallery Pagination (works with Gallery Filter)
     * Expects markup like:
     *  - .section-gallery
     *  - .isotope-grid containing .isotope-item
     *  - .pagination containing .item-pagination anchors
     */
    function initGalleryPagination() {
        const sections = document.querySelectorAll('.section-gallery');
        if (!sections || sections.length === 0) return;

        sections.forEach(section => {
            const grid = section.querySelector('.isotope-grid');
            const pagination = section.querySelector('.pagination');
            if (!grid || !pagination) return;

            const items = Array.from(grid.querySelectorAll('.isotope-item'));
            if (items.length === 0) return;

            // Prevent wiring twice on the same pagination container
            if (pagination.dataset && pagination.dataset.patoPaginationInit === '1') {
                // Still re-render because filter changes the number of visible items
            } else {
                if (pagination.dataset) pagination.dataset.patoPaginationInit = '1';
            }

            const perPageAttr = grid.getAttribute('data-per-page') || pagination.getAttribute('data-per-page');
            const perPage = Math.max(1, parseInt(perPageAttr || '9', 10) || 9);

            const filteredItems = items.filter(it => !(it.dataset && it.dataset.filterHidden === '1'));
            const totalPages = Math.max(1, Math.ceil(filteredItems.length / perPage));

            let currentPage = parseInt(pagination.getAttribute('data-current-page') || '1', 10) || 1;
            if (currentPage > totalPages) currentPage = 1;
            pagination.setAttribute('data-current-page', String(currentPage));

            // Rebuild pagination UI (keeps it consistent with totalPages)
            pagination.innerHTML = '';
            for (let page = 1; page <= totalPages; page++) {
                const a = document.createElement('a');
                a.className = 'item-pagination flex-c-m trans-0-4';
                if (page === currentPage) a.classList.add('active-pagination');
                a.href = '#';
                a.textContent = String(page);
                a.addEventListener('click', (e) => {
                    if (e && typeof e.preventDefault === 'function') e.preventDefault();
                    pagination.setAttribute('data-current-page', String(page));
                    initGalleryPagination();
                });
                pagination.appendChild(a);
            }

            // Apply page visibility
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            filteredItems.forEach((item, idx) => {
                if (item.dataset) item.dataset.pageHidden = (idx >= start && idx < end) ? '0' : '1';
                applyGalleryItemVisibility(item);
            });

            // Ensure filtered-out items remain hidden
            items.forEach(item => {
                if (item.dataset && item.dataset.filterHidden === '1') {
                    if (item.dataset) item.dataset.pageHidden = '0';
                    applyGalleryItemVisibility(item);
                }
            });
        });
    }

    /**
     * GLightbox for Image Galleries
     */
    function initGLightbox() {
        if (typeof GLightbox === 'undefined') return;
        // Never destroy/recreate while open; it will instantly close.
        if (isLightboxOpen) return;

        // Pato CMS gallery markup sometimes uses a <div class="overlay-item-gallery">
        // instead of an <a> link. Convert overlays to anchors so clicking zooms.
        ensureGalleryLightboxLinks(document);

        // Convert data-lightbox to glightbox format
        document.querySelectorAll('[data-lightbox]').forEach(el => {
            const gallery = el.dataset.lightbox;
            el.classList.add('glightbox');
            el.dataset.gallery = gallery;
            delete el.dataset.lightbox;
        });

        // Recreate instance so newly injected links (client-side navigation) work.
        if (glightboxInstance && typeof glightboxInstance.destroy === 'function') {
            glightboxInstance.destroy();
        }
        isLightboxOpen = false;
        glightboxInstance = GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
            zoomable: true,
        });

        // Track open/close state so the MutationObserver won't re-init and close it.
        try {
            if (glightboxInstance && typeof glightboxInstance.on === 'function') {
                glightboxInstance.on('open', () => {
                    isLightboxOpen = true;
                });
                glightboxInstance.on('close', () => {
                    isLightboxOpen = false;
                });
            }
        } catch (e) {
            // ignore; older GLightbox builds may not support event API
        }
    }

    function ensureGalleryLightboxLinks(root) {
        if (!root || !root.querySelectorAll) return;

        const items = root.querySelectorAll(
            '.public-page-content .item-gallery, .section-gallery .item-gallery'
        );

        isUpdatingLightbox = true;
        try {
            items.forEach(item => {
                const img = item.querySelector('img');
                if (!img) return;

                const src = img.getAttribute('src') || img.currentSrc || img.src;
                if (!src) return;

                const overlay = item.querySelector('.overlay-item-gallery');
                if (!overlay) return;

                // If already a link, just ensure glightbox attrs exist.
                if (overlay.tagName && overlay.tagName.toLowerCase() === 'a') {
                    overlay.classList.add('glightbox');
                    if (!overlay.getAttribute('href')) overlay.setAttribute('href', src);
                    if (!overlay.dataset.gallery) overlay.dataset.gallery = 'pato-gallery';
                    if (!overlay.getAttribute('aria-label')) overlay.setAttribute('aria-label', 'Zoom image');
                    return;
                }

                // Replace div overlay with anchor overlay to enable click-to-zoom.
                const link = document.createElement('a');
                link.className = overlay.className;
                link.classList.add('glightbox');
                link.setAttribute('href', src);
                link.setAttribute('aria-label', 'Zoom image');
                link.dataset.gallery = 'pato-gallery';
                link.innerHTML = overlay.innerHTML;

                overlay.parentNode.replaceChild(link, overlay);
            });
        } finally {
            isUpdatingLightbox = false;
        }
    }

    function initDynamicContentObserver() {
        // Re-run lightbox wiring when Next.js client-side navigation swaps page content.
        if (typeof MutationObserver === 'undefined') return;
        if (document.body && document.body.dataset.patoObserverInit === '1') return;
        if (document.body) document.body.dataset.patoObserverInit = '1';

        const obs = new MutationObserver(() => {
            if (isUpdatingLightbox) return;
            // GLightbox injects DOM nodes into <body> when opened.
            // Our observer would otherwise re-init (destroy) and instantly close it.
            if (isLightboxOpen) return;
            if (
                (document.documentElement && document.documentElement.classList.contains('glightbox-open')) ||
                (document.body && document.body.classList.contains('glightbox-open')) ||
                document.querySelector('.glightbox-container')
            ) {
                return;
            }
            clearTimeout(glightboxRefreshTimer);
            glightboxRefreshTimer = setTimeout(() => {
                initGalleryFilter();
                initGalleryPagination();
                initGLightbox();
            }, 100);
        });

        obs.observe(document.body, { childList: true, subtree: true });
    }

    /**
     * Simple Parallax Effect (CSS-based)
     */
    function initParallax() {
        const parallaxElements = document.querySelectorAll('.parallax100');
        if (parallaxElements.length === 0) return;

        // Use CSS background-attachment: fixed for simple parallax
        parallaxElements.forEach(el => {
            el.style.backgroundAttachment = 'fixed';
            el.style.backgroundPosition = 'center';
            el.style.backgroundSize = 'cover';
        });
    }

})();
