import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

/* ──────────────────────────────────────────
   Progress bar helpers
────────────────────────────────────────── */
const Progress = (() => {
    let bar, timer, width = 0;
    function getBar() { return bar || (bar = document.getElementById('nprogress-bar')); }

    function start() {
        const b = getBar();
        if (!b) return;
        width = 0;
        b.style.width = '0%';
        b.style.opacity = '1';
        b.classList.remove('done');
        clearInterval(timer);
        timer = setInterval(() => {
            if (width < 85) { width += Math.random() * 12; if (width > 85) width = 85; b.style.width = width + '%'; }
        }, 180);
    }

    function done() {
        const b = getBar();
        clearInterval(timer);
        if (!b) return;
        b.style.width = '100%';
        setTimeout(() => { b.classList.add('done'); }, 280);
    }

    return { start, done };
})();

/* ──────────────────────────────────────────
   SPA router — swaps only #spa-content,
   sidebar/topbar stay mounted (no blink)
────────────────────────────────────────── */
(function () {
    const SPA_ATTR = 'data-spa-ignore';

    function isSpaLink(link) {
        if (!link) return false;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript') || href.startsWith('mailto:')) return false;
        if (link.target === '_blank') return false;
        if (link.hasAttribute(SPA_ATTR) || link.closest('[' + SPA_ATTR + ']')) return false;
        try {
            const url = new URL(href, location.origin);
            return url.origin === location.origin;
        } catch { return false; }
    }

    async function navigate(href, push = true) {
        Progress.start();

        try {
            const res = await fetch(href, {
                headers: { 'X-SPA-Request': '1', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            // If server returns redirect (login page, etc.) follow it normally
            if (res.redirected && !res.url.includes('/admin')) {
                location.href = res.url;
                return;
            }

            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');

            // Swap content zone
            const newContent  = doc.getElementById('spa-content');
            const newFlash    = doc.getElementById('spa-flash');
            const newTitle    = doc.getElementById('spa-title');
            const newScripts  = doc.getElementById('spa-scripts');
            const content     = document.getElementById('spa-content');
            const flash       = document.getElementById('spa-flash');
            const title       = document.getElementById('spa-title');
            const scriptsZone = document.getElementById('spa-scripts');

            if (!newContent || !content) {
                // Not an admin layout page — do full navigation
                location.href = href;
                return;
            }

            // Animate out
            content.style.opacity = '0';
            content.style.transform = 'translateY(6px)';
            content.style.transition = 'opacity 0.12s ease, transform 0.12s ease';

            await new Promise(r => setTimeout(r, 120));

            // ── Collect scripts from spa-scripts zone ──
            const pendingScripts = newScripts
                ? Array.from(newScripts.querySelectorAll('script'))
                : [];

            // ── Swap innerHTML ──
            content.innerHTML = newContent.innerHTML;
            if (flash && newFlash) flash.innerHTML = newFlash.innerHTML;
            if (title && newTitle) title.textContent = newTitle.textContent;
            // Clear old scripts zone (old handlers gone after swap)
            if (scriptsZone) scriptsZone.innerHTML = '';

            // Update <title>
            const docTitle = doc.querySelector('title');
            if (docTitle) document.title = docTitle.textContent;

            // Update sidebar active state
            updateSidebarActive(href);

            // Push history
            if (push) history.pushState({ spa: true, href }, '', href);

            // Scroll content area to top
            const main = document.getElementById('spa-main');
            if (main) main.scrollTop = 0;

            // ── Run page scripts FIRST (defines Alpine component functions) ──
            for (const oldScript of pendingScripts) {
                await new Promise(resolve => {
                    const s = document.createElement('script');
                    if (oldScript.src) {
                        s.src = oldScript.src;
                        s.onload = resolve;
                        s.onerror = resolve;
                        document.body.appendChild(s);
                    } else {
                        s.textContent = oldScript.textContent;
                        document.body.appendChild(s);
                        resolve();
                    }
                });
            }

            // ── Re-init Alpine AFTER scripts are loaded ──
            content.querySelectorAll('[x-cloak]').forEach(el => el.removeAttribute('x-cloak'));
            if (flash) flash.querySelectorAll('[x-cloak]').forEach(el => el.removeAttribute('x-cloak'));

            if (window.Alpine) {
                Alpine.initTree(content);
                if (flash) Alpine.initTree(flash);
            }

            // ── Animate in ──
            content.style.transition = 'none';
            content.style.opacity = '0';
            content.style.transform = 'translateY(8px)';
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    content.style.transition = 'opacity 0.22s ease, transform 0.22s ease';
                    content.style.opacity = '1';
                    content.style.transform = 'translateY(0)';
                });
            });

        } catch (err) {
            // Network error — fall back to normal navigation
            location.href = href;
            return;
        }

        Progress.done();
    }

    function updateSidebarActive(href) {
        const url = new URL(href, location.origin);

        // Update <a> links
        document.querySelectorAll('aside nav a[href]').forEach(link => {
            const linkUrl = new URL(link.getAttribute('href'), location.origin);
            const isActive = url.pathname.startsWith(linkUrl.pathname) && linkUrl.pathname !== '/admin/';

            // Exact match for dashboard to avoid prefix collision
            const isDash = linkUrl.pathname === '/admin' || linkUrl.pathname === '/admin/';
            const finalActive = isDash
                ? (url.pathname === '/admin' || url.pathname === '/admin/')
                : url.pathname.startsWith(linkUrl.pathname);

            link.classList.toggle('nav-item-active', finalActive);
            link.classList.toggle('text-white/50', !finalActive);
            link.classList.toggle('text-white/45', false);
            link.classList.toggle('hover:text-white', !finalActive);
            link.classList.toggle('hover:bg-white/6', !finalActive);

            const existingBar = link.querySelector('span.absolute');
            if (finalActive && !existingBar) {
                const span = document.createElement('span');
                span.className = 'absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 bg-primary-400 rounded-r';
                link.insertBefore(span, link.firstChild);
            } else if (!finalActive && existingBar) {
                existingBar.remove();
            }

            const svg = link.querySelector('svg');
            if (svg) {
                svg.classList.toggle('text-primary-400', finalActive);
                svg.classList.toggle('text-white/40', !finalActive);
                svg.classList.toggle('text-white/35', false);
                svg.classList.toggle('group-hover:text-white/70', !finalActive);
            }
        });

        // Open Master Data group if navigating to students or subjects
        const isMaster = url.pathname.includes('/admin/students') || url.pathname.includes('/admin/subjects');
        document.querySelectorAll('aside nav [x-data]').forEach(el => {
            if (el._x_dataStack && isMaster) {
                // set open = true on the Alpine component
                try { el._x_dataStack[0].open = true; } catch {}
            }
        });
    }

    // Intercept clicks
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a[href]');
        if (!isSpaLink(link)) return;
        e.preventDefault();
        navigate(link.href);
    });

    // Browser back/forward
    window.addEventListener('popstate', function (e) {
        if (e.state && e.state.spa) navigate(e.state.href, false);
        else if (e.state === null) navigate(location.href, false);
    });

    // Mark initial state
    history.replaceState({ spa: true, href: location.href }, '');

    // Show progress on form submit (non-SPA)
    document.addEventListener('submit', () => Progress.start());

    // Finish on initial page load
    window.addEventListener('pageshow', Progress.done);
    if (document.readyState === 'complete') Progress.done();
    else window.addEventListener('load', Progress.done);
})();

/* ──────────────────────────────────────────
   Dark mode toggle helper (used by layout)
────────────────────────────────────────── */
window.darkMode = function () {
    return {
        dark: localStorage.getItem('theme') === 'dark',
        init() {
            this.applyTheme();
            this.$watch('dark', () => {
                localStorage.setItem('theme', this.dark ? 'dark' : 'light');
                this.applyTheme();
            });
        },
        toggle() { this.dark = !this.dark; },
        applyTheme() {
            document.documentElement.classList.toggle('dark', this.dark);
        },
    };
};

/* Apply saved theme ASAP to avoid flash */
(function () {
    const t = localStorage.getItem('theme');
    if (t === 'dark') document.documentElement.classList.add('dark');
    else if (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)
        document.documentElement.classList.add('dark');
})();
