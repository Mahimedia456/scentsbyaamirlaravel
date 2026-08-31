import Alpine from 'alpinejs';
import Lenis from 'lenis';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

window.Alpine = Alpine;

const commerceStorageVersion = 2;
const moneyValue = (value) => Number(String(value ?? 0).replace(/,/g, '')) || 0;

const loadStoredArray = (key) => {
  try {
    const value = JSON.parse(localStorage.getItem(key) || '[]');
    return Array.isArray(value) ? value : [];
  } catch (_) {
    return [];
  }
};


document.addEventListener('alpine:init', () => {
  Alpine.data('catalogAjax', (options = {}) => ({
    filters: false,
    sort: false,
    mobileFilters: false,
    loading: false,
    activeAudience: options.initialAudience || '',
    activeEdit: options.initialEdit || '',
    endpoint: options.endpoint || '/shop',
    controller: null,

    init() {
      this._popstateHandler = () => {
        const url = new URL(window.location.href);
        this.activeAudience = url.searchParams.get('audience') || '';
        this.activeEdit = url.searchParams.get('edit') || '';
        this.fetchResults(url.toString(), false);
      };

      window.addEventListener('popstate', this._popstateHandler);
    },

    isActive(audience = '', edit = '') {
      return this.activeAudience === audience && this.activeEdit === edit;
    },

    loadEdit(audience = '', edit = '', url = null) {
      const target = new URL(url || this.endpoint, window.location.origin);

      if (audience) target.searchParams.set('audience', audience);
      else target.searchParams.delete('audience');

      if (edit) target.searchParams.set('edit', edit);
      else target.searchParams.delete('edit');

      target.searchParams.delete('q');
      target.searchParams.delete('category');
      target.searchParams.delete('collection');
      target.searchParams.delete('availability');
      target.searchParams.delete('min_price');
      target.searchParams.delete('max_price');

      this.activeAudience = audience;
      this.activeEdit = edit;
      this.fetchResults(target.toString(), true);
    },

    async fetchResults(url, updateHistory = true) {
      if (this.controller) this.controller.abort();
      this.controller = new AbortController();
      this.loading = true;

      try {
        const response = await fetch(url, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          signal: this.controller.signal,
        });

        if (!response.ok) throw new Error(`Catalog request failed: ${response.status}`);

        const payload = await response.json();

        if (this.$refs.results && typeof payload.html === 'string') {
          this.$refs.results.innerHTML = payload.html;

          if (window.Alpine?.initTree) {
            window.Alpine.initTree(this.$refs.results);
          }
        }

        const resolved = new URL(payload.url || url, window.location.origin);
        this.activeAudience = resolved.searchParams.get('audience') || '';
        this.activeEdit = resolved.searchParams.get('edit') || '';

        if (updateHistory) {
          window.history.pushState({}, '', `${resolved.pathname}${resolved.search}`);
        }
      } catch (error) {
        if (error?.name !== 'AbortError') {
          window.location.href = url;
        }
      } finally {
        this.loading = false;
      }
    },
  }));
});

document.addEventListener('alpine:init', () => {
  Alpine.store('commerce', {
    cart: loadStoredArray('sba_cart').map((item) => ({
      ...item,
      qty: Math.max(1, Number(item.qty || 1)),
      price_value: moneyValue(item.price_value ?? item.price),
      line_key: item.line_key || (item.variant_id
        ? `product:${item.product_id}:variant:${item.variant_id}`
        : `fallback:${item.slug || 'product'}:${String(item.size || 'default').toLowerCase()}`),
      available: item.available !== false,
    })),
    wishlist: loadStoredArray('sba_wishlist'),
    cartOpen: false,
    syncing: false,
    notice: '',
    storageVersion: commerceStorageVersion,

    init() {
      const oldVersion = Number(localStorage.getItem('sba_commerce_version') || 1);
      if (oldVersion < commerceStorageVersion) this.persist();
      this.validateCart();
      this.syncWishlist();
    },

    persist() {
      localStorage.setItem('sba_cart', JSON.stringify(this.cart));
      localStorage.setItem('sba_wishlist', JSON.stringify(this.wishlist));
      localStorage.setItem('sba_commerce_version', String(commerceStorageVersion));
    },

    lineKey(product) {
      if (product.line_key) return product.line_key;
      if (product.product_id && product.variant_id) return `product:${product.product_id}:variant:${product.variant_id}`;
      if (product.product_id) return `product:${product.product_id}:size:${String(product.size || 'default').toLowerCase()}`;
      return `fallback:${product.slug || 'product'}:${String(product.size || 'default').toLowerCase()}`;
    },

    async addToCart(product) {
      const normalized = {
        ...product,
        product_id: product.product_id ?? product.id ?? null,
        variant_id: product.variant_id ?? null,
        qty: Math.max(1, Number(product.qty || 1)),
        price_value: moneyValue(product.price_value ?? product.price),
      };
      normalized.line_key = this.lineKey(normalized);

      const found = this.cart.find((item) => this.lineKey(item) === normalized.line_key);
      if (found) {
        found.qty += normalized.qty;
      } else {
        this.cart.push({ ...normalized, available: normalized.available !== false });
      }

      this.persist();
      this.cartOpen = true;
      await this.validateCart();
    },

    removeFromCart(index) {
      this.cart.splice(index, 1);
      this.persist();
    },

    async updateQty(index, qty) {
      const item = this.cart[index];
      if (!item) return;
      const next = Math.max(1, Number(qty || 1));
      const max = Number(item.stock || 0);
      item.qty = max > 0 ? Math.min(next, max) : next;
      this.persist();
      await this.validateCart();
    },

    async validateCart() {
      if (!this.cart.length || this.syncing) return;
      this.syncing = true;
      this.notice = '';
      try {
        const response = await fetch('/api/v1/store/cart/validate', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ items: this.cart }),
        });
        if (!response.ok) throw new Error('Cart validation failed');
        const payload = await response.json();
        const data = payload.data || {};
        if (Array.isArray(data.items)) {
          const changed = data.items.some((item) => item.status && !['ok', 'fallback'].includes(item.status));
          this.cart = data.items;
          if (changed) this.notice = 'Your bag was refreshed using current price and stock.';
          this.persist();
        }
      } catch (_) {
        // Keep the local bag available if the API is temporarily unreachable.
      } finally {
        this.syncing = false;
      }
    },

    toggleWishlist(product) {
      const productId = product.product_id ?? product.id ?? null;
      const idx = this.wishlist.findIndex((item) =>
        productId ? Number(item.product_id ?? item.id) === Number(productId) : item.slug === product.slug
      );
      if (idx >= 0) this.wishlist.splice(idx, 1);
      else this.wishlist.push({
        ...product,
        product_id: productId,
        price_value: moneyValue(product.price_value ?? product.price),
      });
      this.persist();
      this.syncWishlist();
    },

    inWishlist(identifier) {
      return this.wishlist.some((item) =>
        Number.isFinite(Number(identifier)) && identifier !== null
          ? Number(item.product_id ?? item.id) === Number(identifier)
          : item.slug === identifier
      );
    },

    async syncWishlist() {
      if (!this.wishlist.length) return;
      try {
        const response = await fetch('/api/v1/store/wishlist/resolve', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ items: this.wishlist }),
        });
        if (!response.ok) throw new Error('Wishlist sync failed');
        const payload = await response.json();
        if (Array.isArray(payload.data?.items) && payload.data.items.length) {
          this.wishlist = payload.data.items;
          this.persist();
        }
      } catch (_) {
        // Wishlist remains locally available while offline/API unavailable.
      }
    },

    get count() {
      return this.cart.filter((item) => item.available !== false)
        .reduce((sum, item) => sum + Number(item.qty || 1), 0);
    },

    get subtotal() {
      return this.cart.filter((item) => item.available !== false)
        .reduce((sum, item) => sum + moneyValue(item.price_value ?? item.price) * Number(item.qty || 1), 0);
    },

    get checkoutItems() {
      return this.cart.filter((item) => item.available !== false).map((item) => ({
        product_id: item.product_id ?? null,
        variant_id: item.variant_id ?? null,
        sku: item.sku ?? null,
        qty: Number(item.qty || 1),
      }));
    },
  });
});

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
  window.Alpine?.store('commerce')?.init();
});

gsap.registerPlugin(ScrollTrigger);

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!reduceMotion) {
  const lenis = new Lenis({
    duration: 0.82,
    smoothWheel: true,
    wheelMultiplier: 0.86,
  });

  window.houseLenis = lenis;

  function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
  }
  requestAnimationFrame(raf);

  gsap.utils.toArray('[data-reveal]').forEach((el) => {
    gsap.fromTo(el, { y: 22, opacity: 0 }, {
      y: 0,
      opacity: 1,
      duration: .8,
      ease: 'power3.out',
      scrollTrigger: { trigger: el, start: 'top 90%', once: true }
    });
  });

  gsap.utils.toArray('[data-image-shift]').forEach((el) => {
    gsap.fromTo(el, { scale: 1.04 }, {
      scale: 1,
      ease: 'none',
      scrollTrigger: { trigger: el, start: 'top bottom', end: 'bottom top', scrub: true }
    });
  });
}

window.addEventListener('house:scroll-lock', (event) => {
  const locked = Boolean(event.detail?.locked);
  document.documentElement.classList.toggle('house-scroll-locked', locked);
  document.body.classList.toggle('house-scroll-locked', locked);

  if (window.houseLenis) {
    if (locked) window.houseLenis.stop();
    else window.houseLenis.start();
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const header = document.querySelector('[data-house-header]');
  if (!header) return;

  const update = () => header.dataset.scrolled = window.scrollY > 36 ? 'true' : 'false';
  update();
  window.addEventListener('scroll', update, { passive: true });
});


const initLuxuryMotion = () => {
  if (reduceMotion) return;

  gsap.utils.toArray('[data-reveal-line]').forEach((el) => {
    gsap.fromTo(el, { yPercent: 110 }, {
      yPercent: 0,
      duration: 1.05,
      ease: 'power4.out',
      scrollTrigger: { trigger: el, start: 'top 92%', once: true }
    });
  });

  gsap.utils.toArray('[data-fade-stagger]').forEach((group) => {
    const children = group.children;
    gsap.fromTo(children, { opacity: 0, y: 18 }, {
      opacity: 1,
      y: 0,
      duration: .72,
      stagger: .08,
      ease: 'power3.out',
      scrollTrigger: { trigger: group, start: 'top 90%', once: true }
    });
  });

  if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

  gsap.utils.toArray('[data-hover-tilt]').forEach((el) => {
    const strength = 3.2;
    const reset = () => gsap.to(el, { rotateX: 0, rotateY: 0, duration: .5, ease: 'power3.out' });

    el.addEventListener('mousemove', (e) => {
      const r = el.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width - .5;
      const y = (e.clientY - r.top) / r.height - .5;
      gsap.to(el, {
        rotateY: x * strength,
        rotateX: -y * strength,
        transformPerspective: 1000,
        transformOrigin: 'center',
        duration: .45,
        ease: 'power2.out'
      });
    });

    el.addEventListener('mouseleave', reset);
  });
};

const initThreeAtmosphere = async () => {
  const canvas = document.querySelector('[data-three-atmosphere]');
  if (!canvas || reduceMotion) return;

  const THREE = await import('three');

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 100);
  camera.position.z = 5;

  const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.6));

  const geometry = new THREE.IcosahedronGeometry(1.45, 4);
  const material = new THREE.MeshPhysicalMaterial({
    color: 0xc7b79c,
    roughness: 0.35,
    metalness: 0.05,
    transmission: 0.28,
    thickness: 1.2,
    clearcoat: 0.45,
    transparent: true,
    opacity: 0.94,
  });

  const mesh = new THREE.Mesh(geometry, material);
  scene.add(mesh);

  const key = new THREE.DirectionalLight(0xffffff, 2.3);
  key.position.set(3, 2, 4);
  scene.add(key);

  const rim = new THREE.PointLight(0x8a7a66, 4, 10);
  rim.position.set(-3, -1, 2);
  scene.add(rim);

  const resize = () => {
    const w = canvas.clientWidth || 1;
    const h = canvas.clientHeight || 1;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
  };

  resize();
  window.addEventListener('resize', resize);

  let pointerX = 0;
  let pointerY = 0;
  canvas.parentElement?.addEventListener('pointermove', (e) => {
    const r = canvas.getBoundingClientRect();
    pointerX = ((e.clientX - r.left) / r.width - .5) * .45;
    pointerY = ((e.clientY - r.top) / r.height - .5) * .28;
  });

  let frameId = null;
  const tick = () => {
    if (!document.hidden) {
      mesh.rotation.y += 0.0026;
      mesh.rotation.x += (pointerY - mesh.rotation.x) * .025;
      mesh.rotation.z += (pointerX - mesh.rotation.z) * .025;
      renderer.render(scene, camera);
    }
    frameId = requestAnimationFrame(tick);
  };
  tick();

  document.addEventListener('visibilitychange', () => {
    if (!document.hidden && frameId === null) tick();
  });
};

document.addEventListener('DOMContentLoaded', () => {
  initLuxuryMotion();
  initThreeAtmosphere();
});

// Global page loader: initial page load + real navigations/forms.
(() => {
  const loader = document.getElementById('house-page-loader');
  if (!loader) return;

  let revealed = false;
  const reveal = () => {
    if (revealed) return;
    revealed = true;
    window.setTimeout(() => loader.classList.add('is-hidden'), 180);
  };
  const show = () => {
    revealed = false;
    loader.classList.remove('is-hidden');
  };

  if (document.readyState === 'complete') reveal();
  else window.addEventListener('load', reveal, { once: true });

  window.setTimeout(reveal, 2200); // fail-safe if an external asset stalls

  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link) return;
    const href = link.getAttribute('href') || '';
    if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.target === '_blank' || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    try {
      const url = new URL(link.href, window.location.href);
      if (url.origin !== window.location.origin) return;
      if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;
      show();
    } catch (_) {}
  });

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.dataset.noLoader === 'true') return;
    if (!form.checkValidity()) return;
    show();
  });

  window.addEventListener('pageshow', reveal);
})();
