import './bootstrap';

document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const ctaEndpoint = document.body?.dataset?.ctaEndpoint;

    // 공개 페이지: 모바일 메뉴 토글
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function () {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // 관리자 페이지: 사이드바 토글
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay?.classList.toggle('hidden');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });
    }

    function trackCtaClick(payload) {
        if (!ctaEndpoint || !csrfToken) {
            return;
        }

        fetch(ctaEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            keepalive: true,
            body: JSON.stringify({
                _token: csrfToken,
                ...payload,
            }),
        }).catch(() => {
            // Silent failure: CTA navigation should never be blocked by tracking.
        });
    }

    document.addEventListener('click', function (event) {
        const target = event.target instanceof Element
            ? event.target.closest('[data-cta-track]')
            : null;

        if (!target || !('dataset' in target)) {
            return;
        }

        trackCtaClick({
            target: target.dataset.ctaTarget || 'google_play',
            source_type: target.dataset.ctaSourceType || 'unknown',
            placement: target.dataset.ctaPlacement || 'unknown',
            blog_post_id: target.dataset.ctaBlogPostId || null,
            slang_id: target.dataset.ctaSlangId || null,
            page_url: window.location.href,
        });
    });
});

window.openModal = function (id) {
    document.getElementById(id)?.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
};

window.closeModal = function (id) {
    document.getElementById(id)?.classList.add('hidden');
    document.body.style.overflow = '';
};
