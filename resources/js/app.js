/**
 * VYORA runtime. Livewire v3 bundles Alpine, so we register stores/helpers on `alpine:init`
 * instead of importing Alpine ourselves (which would create a second instance).
 */
const boot = window.vyoraBoot || { cartCount: 0, wishlist: [], csrf: '' };

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    Alpine.store('cart', { count: boot.cartCount });
    Alpine.store('wl', { ids: boot.wishlist });
    Alpine.store('toasts', {
        items: [],
        push(message, type = 'success') {
            const id = Date.now() + Math.random();
            this.items.push({ id, message, type });
            setTimeout(() => this.remove(id), 4200);
        },
        remove(id) { this.items = this.items.filter((t) => t.id !== id); },
    });

    // Theme: 'light' | 'dark' | 'system'. Applied pre-paint by the inline script in the layout.
    Alpine.store('theme', {
        mode: (() => { try { return localStorage.getItem('vyora-theme') || 'system'; } catch { return 'system'; } })(),
        set(mode) {
            this.mode = mode;
            try { localStorage.setItem('vyora-theme', mode); } catch {}
            apply(mode);
        },
        toggle() {
            const dark = document.documentElement.classList.contains('dark');
            this.set(dark ? 'light' : 'dark');
        },
    });

    // Recent searches live in this browser only.
    Alpine.data('recentSearches', () => ({
        items: [],
        init() { this.items = read(); },
        save(term) {
            term = (term || '').trim();
            if (term.length < 2) return;
            const next = [term, ...read().filter((t) => t.toLowerCase() !== term.toLowerCase())].slice(0, 6);
            write(next); this.items = next;
        },
        clear() { write([]); this.items = []; },
    }));
});

function read() { try { return JSON.parse(localStorage.getItem('vyora-recent-searches') || '[]'); } catch { return []; } }
function write(v) { try { localStorage.setItem('vyora-recent-searches', JSON.stringify(v)); } catch {} }

function apply(mode) {
    const dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', dark);
}
window.matchMedia('(prefers-color-scheme: dark)').addEventListener?.('change', () => {
    let mode = 'system';
    try { mode = localStorage.getItem('vyora-theme') || 'system'; } catch {}
    if (mode === 'system') apply('system');
});

// ---- Shopper actions used by product cards, quick view and MATCH ---------------------------------
window.vyora = {
    addToCart(variantId, qty = 1) {
        window.Livewire.dispatch('cart-add', { variantId, qty });
    },
    quickView(productId) {
        window.Livewire.dispatch('quick-view', { productId });
    },
    async toggleWishlist(slug, id, button) {
        const store = window.Alpine.store('wl');
        const was = store.ids.includes(id);
        // optimistic update with rollback
        store.ids = was ? store.ids.filter((x) => x !== id) : [...store.ids, id];
        button?.classList.add('animate-pop');
        setTimeout(() => button?.classList.remove('animate-pop'), 400);
        try {
            const res = await fetch(`/wishlist/${encodeURIComponent(slug)}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': boot.csrf, Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            store.ids = data.ids;
            window.Alpine.store('toasts').push(data.message);
        } catch {
            store.ids = was ? [...store.ids, id] : store.ids.filter((x) => x !== id);
            window.Alpine.store('toasts').push('Could not update your wishlist. Please try again.', 'error');
        }
    },
};

// Livewire -> UI bridges
window.addEventListener('toast', (e) => {
    const d = e.detail || {};
    window.Alpine?.store('toasts').push(d.message || '', d.type || 'success');
});
window.addEventListener('cart-count', (e) => {
    window.Alpine?.store('cart') && (window.Alpine.store('cart').count = (e.detail || {}).count ?? 0);
});

// Countdown timers use the genuine end time passed from the server.
window.vyoraCountdown = (endIso) => ({
    end: new Date(endIso).getTime(), d: 0, h: 0, m: 0, s: 0, over: false, t: null,
    start() { this.tick(); this.t = setInterval(() => this.tick(), 1000); },
    tick() {
        const left = Math.max(0, this.end - Date.now());
        this.over = left === 0;
        this.d = Math.floor(left / 864e5); this.h = Math.floor(left / 36e5) % 24;
        this.m = Math.floor(left / 6e4) % 60; this.s = Math.floor(left / 1e3) % 60;
        if (this.over && this.t) clearInterval(this.t);
    },
    pad(n) { return String(n).padStart(2, '0'); },
});
