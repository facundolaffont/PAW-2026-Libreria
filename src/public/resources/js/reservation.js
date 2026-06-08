(function () {
    'use strict';

    if (!window.Cart) return;

    var IMAGE_BASE = 'resources/images/';

    var lista        = document.getElementById('reservation-lista');
    var vacia        = document.getElementById('reservation-vacia');
    var form         = document.getElementById('reservation-form');
    var hiddenWrap   = document.getElementById('reservation-libros-hidden');
    var btnConfirmar = document.getElementById('reservation-confirmar');

    if (!lista || !form || !hiddenWrap || !btnConfirmar) return;

    function resolveImageSrc(imageStr) {
        var parts = (imageStr || '').split(';');
        var fallback = '';
        for (var i = 0; i < parts.length; i++) {
            if (!/^(\d+):/.test(parts[i]) && parts[i]) {
                fallback = parts[i];
            }
        }
        // Si no hay fallback explícito, usa la última parte tras el ":"
        if (!fallback && parts.length) {
            var last = parts[parts.length - 1];
            var m = last.match(/^\d+:(.+)$/);
            fallback = m ? m[1] : last;
        }
        return fallback ? IMAGE_BASE + fallback : '';
    }

    function buildItem(item) {
        var li = document.createElement('li');
        li.dataset.itemId = item.id;

        var article = document.createElement('article');

        var img = document.createElement('img');
        img.src = resolveImageSrc(item.image);
        img.alt = 'Portada de ' + item.title;
        article.appendChild(img);

        var info = document.createElement('div');
        var h3 = document.createElement('h3');
        h3.textContent = item.title;
        var pAutor = document.createElement('p');
        pAutor.textContent = item.author;
        var pPrecio = document.createElement('p');
        pPrecio.className = 'reservation-item-precio';
        var precioUnit = parseFloat(item.price || 0);
        var subtotal   = precioUnit * (parseInt(item.quantity, 10) || 0);
        pPrecio.textContent = '$ ' + formatPrecio(subtotal);
        info.appendChild(h3);
        info.appendChild(pAutor);
        info.appendChild(pPrecio);
        article.appendChild(info);

        var qtyWrap = document.createElement('div');
        qtyWrap.className = 'reservation-cantidad';

        var btnMenos = document.createElement('button');
        btnMenos.type = 'button';
        btnMenos.className = 'reservation-btn-cantidad';
        btnMenos.dataset.action = 'decrement';
        btnMenos.setAttribute('aria-label', 'Disminuir cantidad de ' + item.title);
        btnMenos.textContent = '−';

        var qtySpan = document.createElement('span');
        qtySpan.className = 'reservation-cantidad-valor';
        qtySpan.setAttribute('aria-live', 'polite');
        qtySpan.textContent = String(item.quantity);

        var btnMas = document.createElement('button');
        btnMas.type = 'button';
        btnMas.className = 'reservation-btn-cantidad';
        btnMas.dataset.action = 'increment';
        btnMas.setAttribute('aria-label', 'Aumentar cantidad de ' + item.title);
        btnMas.textContent = '+';

        qtyWrap.appendChild(btnMenos);
        qtyWrap.appendChild(qtySpan);
        qtyWrap.appendChild(btnMas);
        article.appendChild(qtyWrap);

        var btnEliminar = document.createElement('button');
        btnEliminar.type = 'button';
        btnEliminar.dataset.action = 'remove';
        btnEliminar.setAttribute('aria-label', 'Eliminar ' + item.title + ' de la reserva');
        var imgEliminar = document.createElement('img');
        imgEliminar.className = 'boton-eliminar';
        imgEliminar.src = 'resources/images/ícono-eliminar.png';
        imgEliminar.alt = '';
        btnEliminar.appendChild(imgEliminar);
        article.appendChild(btnEliminar);

        li.appendChild(article);
        return li;
    }

    function syncHiddenInputs(items) {
        hiddenWrap.innerHTML = '';
        items.forEach(function (item, i) {
            var fields = {
                book_id:         String(item.id),
                titulo:          item.title,
                autor:           item.author,
                cantidad:        String(item.quantity),
                precio_unitario: String(item.price || 0)
            };
            Object.keys(fields).forEach(function (field) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'libros[' + i + '][' + field + ']';
                input.value = fields[field];
                hiddenWrap.appendChild(input);
            });
        });
    }

    function formatPrecio(value) {
        return parseFloat(value || 0).toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function render(items) {
        lista.innerHTML = '';
        if (!items.length) {
            if (vacia) vacia.hidden = false;
            btnConfirmar.disabled = true;
        } else {
            if (vacia) vacia.hidden = true;
            btnConfirmar.disabled = false;
            items.forEach(function (item) {
                lista.appendChild(buildItem(item));
            });
        }
        syncHiddenInputs(items);
        renderTotal(items);
    }

    function renderTotal(items) {
        var totalEl = document.getElementById('reservation-total');
        if (!totalEl) return;
        if (!items.length) {
            totalEl.hidden = true;
            return;
        }
        var total = items.reduce(function (acc, it) {
            return acc + (parseFloat(it.price || 0) * (parseInt(it.quantity, 10) || 0));
        }, 0);
        totalEl.hidden = false;
        totalEl.querySelector('.reservation-total-valor').textContent = '$ ' + formatPrecio(total);
    }

    // Delegación: +, −, eliminar
    lista.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-action]');
        if (!btn) return;
        var li = btn.closest('li[data-item-id]');
        if (!li) return;
        var id = li.dataset.itemId;
        var action = btn.dataset.action;
        if (action === 'increment') window.Cart.increment(id);
        else if (action === 'decrement') window.Cart.decrement(id);
        else if (action === 'remove') window.Cart.remove(id);
    });

    window.Cart.subscribe(function (items) {
        render(items);
    });

    render(window.Cart.getItems());

    // Si veníamos de un envío exitoso, vaciar el carrito una vez.
    if (/\?(.*&)?enviada=1(&|$)/.test(location.search)) {
        window.Cart.clear();
    }
}());
