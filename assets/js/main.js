/* ======================================================
   UNIQUE CERAMICS — MAIN JAVASCRIPT
   ====================================================== */

document.addEventListener('DOMContentLoaded', () => {

  // === Mobile Navigation ===
  const hamburger  = document.querySelector('.hamburger');
  const mobileNav  = document.querySelector('.mobile-nav');
  const mobileClose= document.querySelector('.mobile-nav-close');

  hamburger?.addEventListener('click', () => {
    mobileNav?.classList.add('open');
    document.body.style.overflow = 'hidden';
  });
  mobileClose?.addEventListener('click', closeMobileNav);
  mobileNav?.addEventListener('click', e => {
    if (e.target === mobileNav) closeMobileNav();
  });
  function closeMobileNav() {
    mobileNav?.classList.remove('open');
    document.body.style.overflow = '';
  }

  // === Product Gallery Thumbnails ===
  const thumbs   = document.querySelectorAll('.product-thumb');
  const mainImg  = document.querySelector('.product-main-img img');
  thumbs.forEach(thumb => {
    thumb.addEventListener('click', () => {
      thumbs.forEach(t => t.classList.remove('active'));
      thumb.classList.add('active');
      if (mainImg) mainImg.src = thumb.querySelector('img').src;
    });
  });

  // === Quantity Selector ===
  document.querySelectorAll('.qty-controls').forEach(ctrl => {
    const input = ctrl.querySelector('input[type="number"]');
    const btnMinus = ctrl.querySelector('[data-action="minus"]');
    const btnPlus  = ctrl.querySelector('[data-action="plus"]');
    if (!input) return;
    const max = parseInt(input.max) || 999;
    const min = parseInt(input.min) || 1;

    btnMinus?.addEventListener('click', () => {
      const val = parseInt(input.value) || 1;
      if (val > min) { input.value = val - 1; input.dispatchEvent(new Event('change')); }
    });
    btnPlus?.addEventListener('click', () => {
      const val = parseInt(input.value) || 1;
      if (val < max) { input.value = val + 1; input.dispatchEvent(new Event('change')); }
    });
  });

  // === Add to Cart (AJAX) ===
  document.querySelectorAll('.btn-cart-ajax').forEach(btn => {
    btn.addEventListener('click', async () => {
      const productId = btn.dataset.id;
      const qtyEl     = document.querySelector('#qty-input');
      const qty        = qtyEl ? parseInt(qtyEl.value) : 1;
      const origText   = btn.textContent;

      btn.disabled   = true;
      btn.textContent = '⏳';

      try {
        const res = await fetch(BASE_PATH + '/cart-action.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=add&id=${productId}&qty=${qty}&csrf_token=${CSRF_TOKEN}`
        });
        const data = await res.json();
        if (data.success) {
          updateCartCount(data.count);
          btn.textContent = '✓ Dodano!';
          btn.style.background = '#5A8A5A';
          showToast(data.message || 'Produkt dodany do koszyka');
          setTimeout(() => {
            btn.textContent = origText;
            btn.style.background = '';
            btn.disabled = false;
          }, 2000);
        } else {
          btn.textContent = origText;
          btn.disabled = false;
          showToast(data.message || 'Błąd', 'error');
        }
      } catch {
        btn.textContent = origText;
        btn.disabled = false;
        showToast('Wystąpił błąd', 'error');
      }
    });
  });

  // === Cart Count Update ===
  function updateCartCount(count) {
    document.querySelectorAll('.cart-count').forEach(el => {
      el.textContent = count;
      el.style.display = count > 0 ? '' : 'none';
    });
  }

  // === Cart Quantity Update ===
  document.querySelectorAll('.qty-cart-input').forEach(input => {
    let timer;
    input.addEventListener('change', () => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        const id  = input.dataset.id;
        const qty = parseInt(input.value);
        updateCartItem(id, qty);
      }, 400);
    });
  });

  async function updateCartItem(id, qty) {
    try {
      const res = await fetch(BASE_PATH + '/cart-action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&id=${id}&qty=${qty}&csrf_token=${CSRF_TOKEN}`
      });
      const data = await res.json();
      if (data.success) {
        updateCartCount(data.count);
        updateCartTotals(data);
      }
    } catch {}
  }

  // === Cart Remove ===
  document.querySelectorAll('.remove-item').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      if (!confirm('Usunąć ten produkt z koszyka?')) return;
      try {
        const res = await fetch(BASE_PATH + '/cart-action.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=remove&id=${id}&csrf_token=${CSRF_TOKEN}`
        });
        const data = await res.json();
        if (data.success) {
          const row = btn.closest('tr');
          row?.remove();
          updateCartCount(data.count);
          updateCartTotals(data);
          if (data.count === 0) location.reload();
        }
      } catch {}
    });
  });

  function updateCartTotals(data) {
    const subs = document.querySelector('.cart-subtotal-val');
    const ship = document.querySelector('.cart-shipping-val');
    const tot  = document.querySelector('.cart-total-val');
    if (subs) subs.textContent = data.subtotal_formatted;
    if (ship) ship.textContent = data.shipping_formatted;
    if (tot)  tot.textContent  = data.total_formatted;

    const bar  = document.querySelector('.free-ship-fill');
    if (bar && data.free_percent !== undefined) {
      bar.style.width = Math.min(data.free_percent, 100) + '%';
    }
  }

  // === Product Tabs ===
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.tab;
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
      btn.classList.add('active');
      document.querySelector('.tab-content[data-tab="' + target + '"]')?.classList.add('active');
    });
  });

  // === Payment Option Selection ===
  document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', () => {
      document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
      opt.classList.add('selected');
      opt.querySelector('input[type="radio"]').checked = true;
    });
  });

  // === Toast Notifications ===
  function showToast(msg, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.textContent = msg;
    document.body.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 400);
    }, 3000);
  }
  window.showToast = showToast;

  // === Cookie Notice ===
  const cookie = document.querySelector('.cookie-notice');
  const cookieBtn = document.querySelector('.cookie-accept');
  if (cookie && localStorage.getItem('cookies_accepted')) {
    cookie.style.display = 'none';
  }
  cookieBtn?.addEventListener('click', () => {
    localStorage.setItem('cookies_accepted', '1');
    cookie.style.display = 'none';
  });

  // === Smooth scroll for anchor links ===
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // === Confirm before remove ===
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm || 'Czy na pewno?')) e.preventDefault();
    });
  });

  // === Image lazy loading fallback ===
  document.querySelectorAll('img[data-src]').forEach(img => {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          img.src = img.dataset.src;
          observer.disconnect();
        }
      });
    });
    observer.observe(img);
  });

  // === Newsletter form (demo) ===
  document.querySelector('.newsletter-form')?.addEventListener('submit', e => {
    e.preventDefault();
    showToast('Dziękujemy za zapis! 🎉');
    e.target.reset();
  });

});

// Inject toast styles if not already in CSS
const toastStyle = document.createElement('style');
toastStyle.textContent = `
  .toast {
    position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
    background: #2C2C2C; color: #fff;
    padding: .8rem 1.4rem; border-radius: 8px;
    font-size: .88rem; font-family: 'Inter', sans-serif;
    box-shadow: 0 4px 20px rgba(0,0,0,.2);
    opacity: 0; transform: translateY(12px);
    transition: all .3s ease; pointer-events: none; max-width: 320px;
  }
  .toast.show { opacity: 1; transform: translateY(0); }
  .toast.toast-error { background: #C4714B; }
  .toast.toast-success { background: #5A8A5A; }
`;
document.head.appendChild(toastStyle);
