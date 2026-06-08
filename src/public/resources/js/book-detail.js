(function () {
    'use strict';

    var btn = document.querySelector('button.boton-reservar[data-book-id]');
    if (!btn || !window.Cart) return;

    btn.addEventListener('click', function () {
        var id = btn.dataset.bookId;
        if (!id || id === '0') return;

        window.Cart.add({
            id: id,
            title: btn.dataset.title || '',
            author: btn.dataset.author || '',
            image: btn.dataset.image || '',
            price: btn.dataset.price || ''
        });

        if (btn.dataset.flashing === '1') return;
        var original = btn.textContent;
        btn.dataset.flashing = '1';
        btn.textContent = 'Agregado a tu reserva';
        btn.disabled = true;
        setTimeout(function () {
            btn.textContent = original;
            btn.disabled = false;
            delete btn.dataset.flashing;
        }, 1500);
    });
}());
