(function () {
    'use strict';

    var API_URL    = 'https://openlibrary.org/api/books';
    var DEBOUNCE   = 800;
    var debounceTimer = null;

    function init() {
        var isbnEl = document.getElementById('isbn');
        if (!isbnEl) return;

        isbnEl.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            var isbn = isbnEl.value.trim();
            if (!/^\d{13}$/.test(isbn)) {
                clearPreview();
                return;
            }
            debounceTimer = setTimeout(function () { fetchBook(isbn); }, DEBOUNCE);
        });

        // Limpiar preview de tapa de OL cuando el usuario sube su propio archivo
        var zonaEl = document.getElementById('zona-drop');
        if (zonaEl) {
            new MutationObserver(function () {
                if (zonaEl.classList.contains('has-image')) clearPreview();
            }).observe(zonaEl, { attributes: true, attributeFilter: ['class'] });
        }
    }

    // -------------------------------------------------------------------------
    // Llamada a la Books API de Open Library
    // -------------------------------------------------------------------------
    function clearFields() {
        ['titulo', 'autor', 'descripcion'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.value = '';
                el.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
        clearPreview();
    }

    function fetchBook(isbn) {
        clearFields();
        setStatus('Buscando en Open Library…', 'loading');

        var url = API_URL + '?bibkeys=ISBN:' + isbn + '&format=json&jscmd=data';
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function () {
            if (xhr.status !== 200) {
                setStatus('Open Library no respondió (HTTP ' + xhr.status + ').', 'error');
                return;
            }
            var data;
            try { data = JSON.parse(xhr.responseText); } catch (e) {
                setStatus('Respuesta inválida de Open Library.', 'error');
                return;
            }
            var book = data['ISBN:' + isbn];
            if (!book) {
                setStatus('Libro no encontrado en Open Library.', 'warning');
                return;
            }
            fillForm(book);
            setStatus('Datos cargados desde Open Library.', 'success');
        };

        xhr.onerror = function () {
            setStatus('No se pudo conectar con Open Library.', 'error');
        };

        xhr.send();
    }

    // -------------------------------------------------------------------------
    // Rellena los campos vacíos del formulario con los datos de la API
    // -------------------------------------------------------------------------
    function fillForm(book) {
        setField('titulo', book.title || '');

        var autor = '';
        if (book.authors && book.authors.length > 0) {
            autor = book.authors[0].name || '';
        }
        setField('autor', autor);

        var descripcion = '';
        if (typeof book.description === 'string') {
            descripcion = book.description;
        } else if (book.description && typeof book.description.value === 'string') {
            descripcion = book.description.value;
        }
        setField('descripcion', descripcion);

        if (book.cover && book.cover.medium) {
            showCoverPreview(book.cover.medium);
        }
    }

    // Rellena el campo (ahora siempre vacío por clearFields) y dispara
    // el evento input para que la validación inline reaccione.
    function setField(id, value) {
        if (!value) return;
        var el = document.getElementById(id);
        if (!el) return;
        el.value = value;
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }

    // -------------------------------------------------------------------------
    // Preview de tapa desde Open Library
    // -------------------------------------------------------------------------
    function showCoverPreview(url) {
        // No mostrar preview si el usuario ya seleccionó su propio archivo
        var zonaEl = document.getElementById('zona-drop');
        if (zonaEl && zonaEl.classList.contains('has-image')) return;

        clearPreview();

        var wrapper = document.createElement('div');
        wrapper.id        = 'ol-cover-preview';
        wrapper.className = 'ol-cover-preview';

        var img = document.createElement('img');
        img.src = url;
        img.alt = 'Tapa desde Open Library';

        var caption = document.createElement('p');
        caption.textContent = 'Tapa desde Open Library (se usará si no subís una imagen propia)';

        wrapper.appendChild(img);
        wrapper.appendChild(caption);

        var imagenError = document.getElementById('imagen-error');
        if (imagenError) {
            imagenError.parentNode.insertBefore(wrapper, imagenError);
        }
    }

    function clearPreview() {
        var el = document.getElementById('ol-cover-preview');
        if (el) el.parentNode.removeChild(el);
    }

    // -------------------------------------------------------------------------
    // Indicador de estado
    // -------------------------------------------------------------------------
    function setStatus(message, type) {
        var el = document.getElementById('ol-status');
        if (!el) return;
        el.textContent = message;
        el.className   = 'ol-status ol-status--' + type;
        el.hidden      = false;
    }

    document.addEventListener('DOMContentLoaded', init);

}());
