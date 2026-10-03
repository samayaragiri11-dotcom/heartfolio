/*
 * Heartfolio cart (front-end only, no database).
 * The cart and orders are saved in the browser's localStorage.
 *
 * Every cart line has a "key":
 *   - plain product   -> key is the product id, e.g. "3"
 *   - customized copy -> key is unique, e.g. "c-1696280000000"
 * So two customized copies of the same magazine stay separate lines,
 * each with its own photos and text.
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
        if (!Array.isArray(items)) return [];
        // Older carts had no key: plain items use their product id
        items.forEach(function (i) {
            if (!i.key) i.key = String(i.id);
        });
        return items;
    }

    function saveItems(items) {
        write(CART_KEY, items);
        updateBadge();
    }

    function uniqueId(prefix) {
        return prefix + '-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);
    }

    function findItem(items, key) {
        return items.find(function (i) { return i.key === String(key); });
    }

    function getItem(key) {
        return findItem(getItems(), key) || null;
    }

    // Plain product: adds to the existing line if it is already in the cart
    function add(product, qty) {
        qty = Math.max(1, parseInt(qty, 10) || 1);
        var items = getItems();
        var key = String(product.id);
        var existing = findItem(items, key);
        if (existing) {
            existing.qty += qty;
        } else {
            items.push({
                key: key,
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

    // Customized product: always its own line. Pass an existing key to update it.
    function saveCustom(product, qty, custom, key) {
        qty = Math.max(1, parseInt(qty, 10) || 1);
        var items = getItems();
        var line = key ? findItem(items, key) : null;
        if (!line) {
            line = { key: key || uniqueId('c') };
            items.push(line);
        }
        line.id = product.id;
        line.name = product.name;
        line.price = product.price;
        line.image = product.image;
        line.category = product.category;
        line.qty = qty;
        line.custom = custom;
        saveItems(items);
        return line.key;
    }

    function setQty(key, qty) {
        var items = getItems();
        var item = findItem(items, key);
        if (!item) return;
        item.qty = Math.max(1, parseInt(qty, 10) || 1);
        saveItems(items);
    }

    // Removes a line. A customized line's photos are deleted too.
    function remove(key) {
        var items = getItems();
        var item = findItem(items, key);
        if (item && item.custom && item.custom.photoKey && window.HFPhotos) {
            HFPhotos.remove(item.custom.photoKey).catch(function () {});
        }
        saveItems(items.filter(function (i) { return i.key !== String(key); }));
    }

    // Empties the cart WITHOUT deleting photos (used after an order keeps them)
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

    // One-line description of a customization, for cart and order pages
    function customSummary(custom) {
        if (!custom) return '';
        var parts = [];
        if (custom.title) parts.push('"' + custom.title + '"');
        var photos = (custom.hasCover ? 1 : 0) + (custom.pageCount || 0);
        parts.push(photos + (photos === 1 ? ' photo' : ' photos'));
        return parts.join(', ');
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
        }, 2400);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateBadge);
    } else {
        updateBadge();
    }
    window.addEventListener('storage', updateBadge);

    return {
        getItems: getItems, getItem: getItem, add: add, saveCustom: saveCustom,
        setQty: setQty, remove: remove, clear: clear,
        count: count, totals: totals, placeOrder: placeOrder,
        getOrders: getOrders, getOrder: getOrder,
        formatPrice: formatPrice, formatDate: formatDate, escapeHtml: escapeHtml,
        customSummary: customSummary, uniqueId: uniqueId, updateBadge: updateBadge, toast: toast
    };
})();
