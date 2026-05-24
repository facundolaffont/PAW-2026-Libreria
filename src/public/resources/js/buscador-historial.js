(function () {
    'use strict';

    var STORAGE_KEY   = 'libreria_busquedas_recientes';
    var MAX_BUSQUEDAS = 5;

    var dom = {};

    function init() {
        dom.form     = document.querySelector('form.buscador');
        dom.input    = document.querySelector('.campo-buscar');
        dom.dropdown = document.getElementById('buscador-dropdown');

        if (!dom.input || !dom.dropdown) return;

        dom.input.addEventListener('focus', function () {
            renderDropdown(dom.input.value);
        });

        dom.input.addEventListener('input', function () {
            renderDropdown(dom.input.value);
        });

        dom.input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideDropdown();
                return;
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                var first = dom.dropdown.querySelector('li');
                if (first) first.focus();
            }
        });

        if (dom.form) {
            dom.form.addEventListener('submit', function () {
                var q = dom.input.value.trim();
                if (q) guardarBusqueda(q);
            });
        }

        document.addEventListener('click', function (e) {
            if (dom.form && !dom.form.contains(e.target)) {
                hideDropdown();
            }
        });
    }

    // -------------------------------------------------------------------------
    // localStorage
    // -------------------------------------------------------------------------

    function leerBusquedas() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    }

    function guardarBusqueda(q) {
        if (!q || !q.trim()) return;
        q = q.trim();
        var list = leerBusquedas();
        list = list.filter(function (b) { return b !== q; });
        list.unshift(q);
        if (list.length > MAX_BUSQUEDAS) list = list.slice(0, MAX_BUSQUEDAS);
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(list)); } catch (e) {}
    }

    // -------------------------------------------------------------------------
    // Dropdown
    // -------------------------------------------------------------------------

    function renderDropdown(query) {
        var all = leerBusquedas();
        var q   = (query || '').trim().toLowerCase();
        var filtered = q
            ? all.filter(function (b) { return b.toLowerCase().indexOf(q) !== -1; })
            : all;

        if (filtered.length === 0) {
            hideDropdown();
            return;
        }

        positionDropdown();

        dom.dropdown.innerHTML = '';
        filtered.forEach(function (text) {
            var li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.setAttribute('tabindex', '-1');
            li.textContent = text;

            li.addEventListener('mousedown', function (e) {
                e.preventDefault();
                selectSuggestion(text);
            });

            li.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    selectSuggestion(text);
                } else if (e.key === 'Escape') {
                    hideDropdown();
                    dom.input.focus();
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (li.nextElementSibling) li.nextElementSibling.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (li.previousElementSibling) li.previousElementSibling.focus();
                    else dom.input.focus();
                }
            });

            dom.dropdown.appendChild(li);
        });

        dom.dropdown.hidden = false;
    }

    function selectSuggestion(text) {
        guardarBusqueda(text);
        dom.input.value = text;
        hideDropdown();
        if (dom.form) dom.form.submit();
    }

    function positionDropdown() {
        var rect = dom.input.getBoundingClientRect();
        dom.dropdown.style.top   = (rect.bottom + window.scrollY) + 'px';
        dom.dropdown.style.left  = (rect.left  + window.scrollX) + 'px';
        dom.dropdown.style.width = rect.width + 'px';
    }

    function hideDropdown() {
        dom.dropdown.hidden = true;
    }

    document.addEventListener('DOMContentLoaded', init);
}());
