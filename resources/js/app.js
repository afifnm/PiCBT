import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);
window.Alpine = Alpine;

/* ──────────────────────────────────────────
   Dark mode toggle helper (used by layout)
────────────────────────────────────────── */
window.darkMode = function () {
    return {
        dark: localStorage.getItem('theme') === 'dark' ||
              (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
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

/* ──────────────────────────────────────────
   Searchable <select> (Select2-style, Alpine)
   Usage: see resources/views/components/searchable-select.blade.php
────────────────────────────────────────── */
window.searchableSelect = function (config = {}) {
    return {
        // Public (x-modelable target)
        selected: config.value ?? '',

        // Config
        options: config.options ?? [],        // [{ value, label }]
        placeholder: config.placeholder ?? 'Pilih...',
        searchPlaceholder: config.searchPlaceholder ?? 'Cari...',
        emptyText: config.emptyText ?? 'Tidak ada hasil',
        allowClear: config.allowClear ?? true,

        // State
        open: false,
        search: '',
        highlighted: 0,

        init() {
            // Keep highlight valid whenever the filtered list changes
            this.$watch('search', () => { this.highlighted = 0; });
        },

        get filtered() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.options;
            return this.options.filter(o =>
                String(o.label).toLowerCase().includes(q)
            );
        },

        get selectedLabel() {
            const match = this.options.find(o => String(o.value) === String(this.selected));
            return match ? match.label : '';
        },

        get hasValue() {
            return this.selected !== '' && this.selected !== null && this.selected !== undefined;
        },

        toggle() {
            this.open ? this.close() : this.openDropdown();
        },

        openDropdown() {
            this.open = true;
            this.search = '';
            // Highlight the currently selected option, if any
            const idx = this.filtered.findIndex(o => String(o.value) === String(this.selected));
            this.highlighted = idx >= 0 ? idx : 0;
            this.$nextTick(() => {
                const input = this.$refs.searchInput;
                if (input) input.focus();
                this.scrollToHighlighted();
            });
        },

        close() {
            this.open = false;
            this.search = '';
        },

        choose(option) {
            this.selected = option.value;
            this.close();
        },

        clear() {
            this.selected = '';
            this.close();
        },

        // Keyboard navigation
        onArrowDown() {
            if (!this.open) { this.openDropdown(); return; }
            if (this.highlighted < this.filtered.length - 1) this.highlighted++;
            this.scrollToHighlighted();
        },
        onArrowUp() {
            if (this.highlighted > 0) this.highlighted--;
            this.scrollToHighlighted();
        },
        onEnter() {
            if (!this.open) { this.openDropdown(); return; }
            const opt = this.filtered[this.highlighted];
            if (opt) this.choose(opt);
        },

        scrollToHighlighted() {
            this.$nextTick(() => {
                const list = this.$refs.optionList;
                if (!list) return;
                const el = list.children[this.highlighted];
                if (el) el.scrollIntoView({ block: 'nearest' });
            });
        },

        isHighlighted(i) { return this.highlighted === i; },
        isSelected(option) { return String(option.value) === String(this.selected); },
    };
};

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

        const INACTIVE_TEXT = ['text-slate-600', 'dark:text-violet-300/50', 'dark:text-violet-300/45'];
        const INACTIVE_ICON = ['text-slate-400', 'dark:text-violet-500/60', 'dark:text-violet-500/55'];
        const ACTIVE_TEXT   = 'nav-item-active';
        const ACTIVE_ICON   = ['text-violet-600', 'dark:text-violet-400'];

        document.querySelectorAll('aside nav a[href]').forEach(link => {
            const linkUrl   = new URL(link.getAttribute('href'), location.origin);
            const isDash    = linkUrl.pathname === '/admin' || linkUrl.pathname === '/admin/';
            const finalActive = isDash
                ? (url.pathname === '/admin' || url.pathname === '/admin/')
                : url.pathname.startsWith(linkUrl.pathname);

            link.classList.toggle(ACTIVE_TEXT, finalActive);
            INACTIVE_TEXT.forEach(c => link.classList.toggle(c, !finalActive));

            const svg = link.querySelector('svg');
            if (svg) {
                ACTIVE_ICON.forEach(c => svg.classList.toggle(c, finalActive));
                INACTIVE_ICON.forEach(c => svg.classList.toggle(c, !finalActive));
            }
        });

        // Open Master Data group if navigating to a child route
        const isMaster = ['/admin/students', '/admin/subjects', '/admin/users']
            .some(p => url.pathname.startsWith(p));
        document.querySelectorAll('aside nav [x-data]').forEach(el => {
            if (el._x_dataStack && isMaster) {
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

/* Apply saved theme ASAP to avoid flash */
(function () {
    const t = localStorage.getItem('theme');
    if (t === 'dark') document.documentElement.classList.add('dark');
    else if (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)
        document.documentElement.classList.add('dark');
})();
