(function () {
    'use strict';

    var STORAGE_KEY = 'libreria_reserva_carrito';
    var MAX_QTY = 99;

    var listeners = [];

    function readRaw() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return [];
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function writeRaw(items) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
        } catch (e) {}
        notify();
    }

    function notify() {
        var items = getItems();
        listeners.forEach(function (cb) {
            try { cb(items); } catch (e) {}
        });
    }

    function normalizeBook(book) {
        if (!book || book.id === undefined || book.id === null) return null;
        return {
            id: String(book.id),
            title: String(book.title || ''),
            author: String(book.author || ''),
            image: String(book.image || ''),
            price: book.price !== undefined ? String(book.price) : ''
        };
    }

    function getItems() {
        return readRaw().map(function (it) {
            return {
                id: it.id,
                title: it.title,
                author: it.author,
                image: it.image,
                price: it.price,
                quantity: it.quantity
            };
        });
    }

    function getCount() {
        return readRaw().reduce(function (acc, it) {
            return acc + (parseInt(it.quantity, 10) || 0);
        }, 0);
    }

    function add(book) {
        var b = normalizeBook(book);
        if (!b) return;
        var items = readRaw();
        var existing = null;
        for (var i = 0; i < items.length; i++) {
            if (items[i].id === b.id) { existing = items[i]; break; }
        }
        if (existing) {
            existing.quantity = Math.min(MAX_QTY, (parseInt(existing.quantity, 10) || 0) + 1);
        } else {
            b.quantity = 1;
            items.push(b);
        }
        writeRaw(items);
    }

    function remove(id) {
        id = String(id);
        var items = readRaw().filter(function (it) { return it.id !== id; });
        writeRaw(items);
    }

    function setQuantity(id, qty) {
        id = String(id);
        qty = parseInt(qty, 10);
        if (isNaN(qty) || qty <= 0) {
            remove(id);
            return;
        }
        if (qty > MAX_QTY) qty = MAX_QTY;
        var items = readRaw();
        for (var i = 0; i < items.length; i++) {
            if (items[i].id === id) {
                items[i].quantity = qty;
                writeRaw(items);
                return;
            }
        }
    }

    function increment(id) {
        id = String(id);
        var items = readRaw();
        for (var i = 0; i < items.length; i++) {
            if (items[i].id === id) {
                items[i].quantity = Math.min(MAX_QTY, (parseInt(items[i].quantity, 10) || 0) + 1);
                writeRaw(items);
                return;
            }
        }
    }

    function decrement(id) {
        id = String(id);
        var items = readRaw();
        for (var i = 0; i < items.length; i++) {
            if (items[i].id === id) {
                var next = (parseInt(items[i].quantity, 10) || 0) - 1;
                if (next <= 0) {
                    items.splice(i, 1);
                } else {
                    items[i].quantity = next;
                }
                writeRaw(items);
                return;
            }
        }
    }

    function clear() {
        writeRaw([]);
    }

    function subscribe(cb) {
        if (typeof cb !== 'function') return function () {};
        listeners.push(cb);
        return function unsubscribe() {
            listeners = listeners.filter(function (l) { return l !== cb; });
        };
    }

    // Sincronizar entre pestañas
    window.addEventListener('storage', function (e) {
        if (e.key === STORAGE_KEY) notify();
    });

    window.Cart = {
        add: add,
        remove: remove,
        setQuantity: setQuantity,
        increment: increment,
        decrement: decrement,
        getItems: getItems,
        getCount: getCount,
        clear: clear,
        subscribe: subscribe
    };

    // Badge "Mis Reservas" en el header (si está presente)
    function updateBadge() {
        var badge = document.getElementById('mis-reservas-badge');
        if (!badge) return;
        var count = getCount();
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.hidden = false;
        } else {
            badge.hidden = true;
        }
    }

    function initBadge() {
        updateBadge();
        subscribe(updateBadge);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBadge);
    } else {
        initBadge();
    }
}());
