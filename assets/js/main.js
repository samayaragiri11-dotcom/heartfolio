/*
 * Heartfolio storefront script.
 * Everything works without JavaScript; this just makes it smoother.
 */
(function () {
    'use strict';

    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    /* ---------- Toast ---------- */
    var toastTimer;
    function toast(message, opts) {
        opts = opts || {};
        var el = document.getElementById('toast');
        if (!el) return;
        el.className = 'toast' + (opts.error ? ' error' : '');
        el.textContent = message;
        if (opts.link) {
            var a = document.createElement('a');
            a.href = opts.link.href;
            a.textContent = opts.link.text;
            el.appendChild(a);
        }
        requestAnimationFrame(function () { el.classList.add('show'); });
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { el.classList.remove('show'); }, opts.error ? 4500 : 3200);
    }
    window.hfToast = toast;

    function setCartCount(n) {
        document.querySelectorAll('[data-cart-count]').forEach(function (el) {
            el.textContent = n;
            el.setAttribute('data-count', n);
            el.classList.remove('bump');
            void el.offsetWidth;
            el.classList.add('bump');
        });
    }
    window.hfSetCartCount = setCartCount;

    /* ---------- Mobile menu & search ---------- */
    var navBtn = document.querySelector('[data-nav-toggle]');
    var nav = document.getElementById('main-nav');
    if (navBtn && nav) {
        navBtn.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            navBtn.setAttribute('aria-expanded', open);
            navBtn.innerHTML = open ? '<i class="fa-solid fa-xmark" aria-hidden="true"></i>' : '<i class="fa-solid fa-bars" aria-hidden="true"></i>';
        });
    }

    var searchBtn = document.querySelector('[data-search-toggle]');
    var searchBox = document.getElementById('header-search');
    if (searchBtn && searchBox) {
        searchBtn.addEventListener('click', function () {
            var open = searchBox.classList.toggle('open');
            searchBtn.setAttribute('aria-expanded', open);
            if (open) searchBox.querySelector('input').focus();
        });
    }

    /* ---------- Quantity steppers ---------- */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-step]');
        if (!btn) return;
        var input = btn.parentElement.querySelector('input');
        var min = parseInt(input.min, 10) || 1;
        var max = parseInt(input.max, 10) || 99;
        var next = Math.min(max, Math.max(min, (parseInt(input.value, 10) || min) + parseInt(btn.getAttribute('data-step'), 10)));
        if (next === parseInt(input.value, 10)) return;
        input.value = next;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    // Cart page: changing a quantity submits its form automatically
    document.querySelectorAll('[data-autosubmit]').forEach(function (form) {
        var timer;
        form.addEventListener('change', function () {
            clearTimeout(timer);
            timer = setTimeout(function () { form.submit(); }, 450);
        });
    });

    /* ---------- Show / hide password ---------- */
    document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.parentElement.querySelector('input');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.innerHTML = '<i class="fa-regular ' + (show ? 'fa-eye-slash' : 'fa-eye') + '" aria-hidden="true"></i>';
        });
    });

    /* ---------- Add to cart without leaving the page ---------- */
    document.querySelectorAll('form[data-ajax-cart]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var submitter = e.submitter;
            // "Buy now" goes straight to checkout, so let it submit normally
            if (submitter && submitter.name === 'buy_now') return;
            e.preventDefault();
            var button = form.querySelector('[type="submit"]');
            button.disabled = true;
            fetch(form.getAttribute('action'), {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'fetch', 'X-CSRF-Token': csrf }
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.ok) {
                        setCartCount(data.count);
                        toast(data.message, { link: { href: 'cart.php', text: 'View cart' } });
                    } else {
                        toast(data.error || 'Something went wrong.', { error: true });
                    }
                })
                .catch(function () { form.submit(); })
                .finally(function () { button.disabled = false; });
        });
    });

    /* ---------- Product gallery ---------- */
    var main = document.getElementById('pd-main-img');
    document.querySelectorAll('[data-thumb]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            main.src = btn.getAttribute('data-thumb');
            document.querySelectorAll('[data-thumb]').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
        });
    });

    /* ---------- Confirm before destructive actions ---------- */
    document.addEventListener('submit', function (e) {
        var msg = e.target.getAttribute('data-confirm');
        if (msg && !confirm(msg)) e.preventDefault();
    });
})();
