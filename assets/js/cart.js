/*
 * Heartfolio cart (front-end only, no database).
 * The cart and orders are saved in the browser's localStorage,
 * so they survive page changes and refreshes.
 */
window.HFCart = window.HFCart || (function () {
    var CART_KEY = 'heartfolio_cart';
    var ORDERS_KEY = 'heartfolio_orders';
    var SHIPPING = 100;

    function read(key, fallback) {
        try {
            var raw = localStorage.getItem(key);
            return raw ? JSON.parse(raw) : fallback;
        } catch (e) {
            return fallback;
        }
    }

    function write(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) { /* storage full or blocked */ }
    }

    function getItems() {
        var items = read(CART_KEY, []);
        return Array.isArray(items) ? items : [];
    }

    function saveItems(items) {
        write(CART_KEY, items);
        updateBadge();
    }

    function findItem(items, id) {
        return items.find(function (i) { return i.id === id; });
    }

    function add(product, qty) {
        qty = Math.max(1, parseInt(qty, 10) || 1);
        var items = getItems();
        var existing = findItem(items, product.id);
        if (existing) {
            existing.qty += qty;
        } else {
            items.push({
                id: product.id,
                name: product.name,
                price: product.price,
                image: product.image,
                category: product.category,
                qty: qty
            });
        }
        saveItems(items);
    }

    function setQty(id, qty) {
        var items = getItems();
        var item = findItem(items, id);
        if (!item) return;
        item.qty = Math.max(1, parseInt(qty, 10) || 1);
        saveItems(items);
    }

    function remove(id) {
        saveItems(getItems().filter(function (i) { return i.id !== id; }));
    }

    function clear() {
        saveItems([]);
    }

    function count() {
        return getItems().reduce(function (sum, i) { return sum + i.qty; }, 0);
    }

    function totals(items) {
        items = items || getItems();
        var subtotal = items.reduce(function (sum, i) { return sum + i.price * i.qty; }, 0);
        var shipping = items.length ? SHIPPING : 0;
        return { subtotal: subtotal, shipping: shipping, total: subtotal + shipping };
    }

    function formatPrice(n) {
        return 'Rs. ' + Number(n).toLocaleString('en-IN');
    }

    function formatDate(iso) {
        return new Date(iso).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function getOrders() {
        var orders = read(ORDERS_KEY, []);
        return Array.isArray(orders) ? orders : [];
    }

    function getOrder(id) {
        return getOrders().find(function (o) { return o.id === id; }) || null;
    }

    // Turns the current cart into an order, saves it, and empties the cart.
    function placeOrder(customer, accountEmail) {
        var items = getItems();
        if (!items.length) return null;
        var now = new Date();
        var order = {
            id: 'HF-' + now.getFullYear() + '-' + String(now.getTime()).slice(-6),
            date: now.toISOString(),
            status: 'Pending',
            account: accountEmail || '',
            customer: customer,
            items: items,
            totals: totals(items)
        };
        var orders = getOrders();
        orders.unshift(order);
        write(ORDERS_KEY, orders);
        clear();
        return order;
    }

    // Any element with data-cart-count (e.g. in the navbar) shows the item count.
    function updateBadge() {
        var n = count();
        document.querySelectorAll('[data-cart-count]').forEach(function (el) {
            el.textContent = n;
        });
    }

    function toast(message) {
        var el = document.getElementById('hf-toast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'hf-toast';
            el.setAttribute('role', 'status');
            el.style.cssText = 'position:fixed;left:50%;bottom:30px;transform:translate(-50%,20px);' +
                'background:#3F352D;color:#fff;padding:12px 22px;border-radius:8px;font-size:14px;' +
                'box-shadow:0 6px 20px rgba(0,0,0,.2);opacity:0;transition:opacity .25s,transform .25s;' +
                'z-index:9999;pointer-events:none;max-width:90vw;text-align:center;';
            document.body.appendChild(el);
        }
        el.textContent = message;
        el.style.opacity = '1';
        el.style.transform = 'translate(-50%,0)';
        clearTimeout(el._timer);
        el._timer = setTimeout(function () {
            el.style.opacity = '0';
            el.style.transform = 'translate(-50%,20px)';
        }, 2200);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateBadge);
    } else {
        updateBadge();
    }
    // Keeps the count in sync if the site is open in two tabs
    window.addEventListener('storage', updateBadge);

    return {
        getItems: getItems, add: add, setQty: setQty, remove: remove, clear: clear,
        count: count, totals: totals, placeOrder: placeOrder,
        getOrders: getOrders, getOrder: getOrder,
        formatPrice: formatPrice, formatDate: formatDate, escapeHtml: escapeHtml,
        updateBadge: updateBadge, toast: toast
    };
})();
