(function () {
    'use strict';

    var MOBILE_BP      = 1024;
    var DEBOUNCE_MS    = 500;
    var IMAGE_BASE     = 'resources/images/';
    var STORAGE_KEY    = 'libreria_busquedas_recientes';
    var MAX_BUSQUEDAS  = 5;

    // -------------------------------------------------------------------------
    // Estado
    // -------------------------------------------------------------------------
    var state = {
        allBooks: [],
        filtered: [],
        filters: { generos: [], autor: [], precioMin: '', precioMax: '', q: '' },
        orden: 'titulo-asc',
        currentPage: 1,
        perPage: 12,
        infiniteScroll: false,
        infiniteScrollPage: 1,
        drawerOpen: false,
    };

    // -------------------------------------------------------------------------
    // Referencias al DOM (se populan en init)
    // -------------------------------------------------------------------------
    var dom = {};

    var scrollObserver = null;
    var priceDebounceTimer = null;
    var isSyncingSelects = false;

    // -------------------------------------------------------------------------
    // Init
    // -------------------------------------------------------------------------
    function init() {
        loadData();
        cacheDom();
        readUrlState();
        bindEvents();

        // Activar modo JS: oculta el fallback server-side
        document.querySelector('main').classList.add('catalog-js-active');
        dom.listaJs.hidden = false;
        dom.paginacionJs.hidden = false;
        dom.csvJs.hidden = false;

        guardarBusqueda(state.filters.q);

        syncSelectsToState();
        render();
    }

    // -------------------------------------------------------------------------
    // Carga de datos desde JSON island
    // -------------------------------------------------------------------------
    function loadData() {
        var el = document.getElementById('catalog-data');
        if (!el) return;
        var payload = JSON.parse(el.textContent);
        state.allBooks = payload.books || [];
        state.priceRange = payload.priceRange || { min: 0, max: 99999 };
    }

    // -------------------------------------------------------------------------
    // Referencias DOM
    // -------------------------------------------------------------------------
    function cacheDom() {
        dom.listaJs        = document.getElementById('catalog-lista-js');
        dom.paginacionJs   = document.getElementById('catalog-paginacion-js');
        dom.sentinel       = document.getElementById('catalog-sentinel');
        dom.backdrop       = document.getElementById('catalog-backdrop');
        dom.drawer         = document.getElementById('filtrar-mobile');
        dom.btnAbrir       = document.getElementById('btn-abrir-filtros');
        dom.btnCerrar      = document.getElementById('btn-cerrar-filtros');
        dom.btnAplicar     = document.getElementById('aplicar-filtros-mobile');
        dom.btnLimpiar     = document.getElementById('limpiar-filtros');
        dom.selectDesktop  = document.getElementById('ordenar');
        dom.selectMobile   = document.getElementById('ordenar-mobile-select');
        dom.porPagina      = document.getElementById('por-pagina');
        dom.scrollToggle   = document.getElementById('scroll-infinito');
        dom.badge          = document.getElementById('filtros-badge');
        dom.resultCount    = document.getElementById('resultado-count');
        dom.csvJs                = document.querySelector('.catalog-csv-js');
        dom.csvLinkJs            = document.getElementById('descargar-csv-js');
        dom.form                 = document.getElementById('catalog-form');
        dom.section              = document.querySelector('section[aria-label="Listado de libros"]');
    }

    // -------------------------------------------------------------------------
    // Leer estado inicial desde URL
    // -------------------------------------------------------------------------
    function readUrlState() {
        var params = new URLSearchParams(location.search);

        state.filters.generos  = params.getAll('genero[]');
        state.filters.autor    = params.getAll('autor[]');
        state.filters.precioMin = params.get('precio_min') || '';
        state.filters.precioMax = params.get('precio_max') || '';
        state.filters.q        = params.get('q') || '';
        state.orden            = params.get('orden') || 'titulo-asc';
        state.currentPage      = Math.max(1, parseInt(params.get('pagina') || '1', 10));
        state.perPage          = parseInt(params.get('por_pagina') || '12', 10);
        if ([12, 24, 48].indexOf(state.perPage) === -1) state.perPage = 12;
        state.infiniteScroll   = params.get('scroll') === '1';

        // Sincronizar checkboxes según el estado leído de la URL
        dom.form.querySelectorAll('input[name="genero[]"]').forEach(function (cb) {
            cb.checked = state.filters.generos.indexOf(cb.value) !== -1;
        });
        dom.form.querySelectorAll('input[name="autor[]"]').forEach(function (cb) {
            cb.checked = state.filters.autor.indexOf(cb.value) !== -1;
        });
        var precioMinEl = document.getElementById('precio-min');
        var precioMaxEl = document.getElementById('precio-max');
        if (precioMinEl) precioMinEl.value = state.filters.precioMin;
        if (precioMaxEl) precioMaxEl.value = state.filters.precioMax;

        if (dom.scrollToggle) dom.scrollToggle.checked = state.infiniteScroll;
        if (dom.porPagina) dom.porPagina.value = String(state.perPage);
    }

    // -------------------------------------------------------------------------
    // Binding de eventos
    // -------------------------------------------------------------------------
    function bindEvents() {
        // Drawer mobile
        if (dom.btnAbrir)  dom.btnAbrir.addEventListener('click', openDrawer);
        if (dom.btnCerrar) dom.btnCerrar.addEventListener('click', closeDrawer);
        if (dom.backdrop)  dom.backdrop.addEventListener('click', closeDrawer);
        if (dom.btnAplicar) {
            dom.btnAplicar.addEventListener('click', function () {
                collectFilterState();
                render();
                closeDrawer();
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && state.drawerOpen) closeDrawer();
        });

        // Limpiar filtros
        if (dom.btnLimpiar) dom.btnLimpiar.addEventListener('click', clearFilters);

        // Checkboxes (reactivos al instante)
        dom.form.querySelectorAll('input[name="genero[]"], input[name="autor[]"]').forEach(function (cb) {
            cb.addEventListener('change', function () {
                collectFilterState();
                render();
            });
        });

        // Precio (debounce)
        var precioMinEl = document.getElementById('precio-min');
        var precioMaxEl = document.getElementById('precio-max');
        function onPrecioInput() {
            clearTimeout(priceDebounceTimer);
            priceDebounceTimer = setTimeout(function () {
                collectFilterState();
                render();
            }, DEBOUNCE_MS);
        }
        if (precioMinEl) precioMinEl.addEventListener('input', onPrecioInput);
        if (precioMaxEl) precioMaxEl.addEventListener('input', onPrecioInput);

        // Ordenar (desktop)
        if (dom.selectDesktop) {
            dom.selectDesktop.addEventListener('change', function () {
                if (isSyncingSelects) return;
                state.orden = dom.selectDesktop.value;
                isSyncingSelects = true;
                dom.selectMobile.value = state.orden;
                isSyncingSelects = false;
                render();
            });
        }

        // Ordenar (mobile)
        if (dom.selectMobile) {
            dom.selectMobile.addEventListener('change', function () {
                if (isSyncingSelects) return;
                state.orden = dom.selectMobile.value;
                isSyncingSelects = true;
                dom.selectDesktop.value = state.orden;
                isSyncingSelects = false;
                render();
            });
        }

        // Por página
        if (dom.porPagina) {
            dom.porPagina.addEventListener('change', function () {
                state.perPage = parseInt(dom.porPagina.value, 10);
                render();
            });
        }

        // Scroll infinito toggle
        if (dom.scrollToggle) {
            dom.scrollToggle.addEventListener('change', function () {
                state.infiniteScroll = dom.scrollToggle.checked;
                render();
            });
        }

        // Paginación JS (delegado)
        if (dom.paginacionJs) {
            dom.paginacionJs.addEventListener('click', function (e) {
                var a = e.target.closest('a[data-page]');
                if (!a) return;
                e.preventDefault();
                var page = parseInt(a.dataset.page, 10);
                if (!isNaN(page)) {
                    state.currentPage = page;
                    renderPage();
                    renderPagination();
                    pushUrl();
                    dom.section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }

        // Back/forward del browser
        window.addEventListener('popstate', function () {
            readUrlState();
            syncSelectsToState();
            render();
        });
    }

    // -------------------------------------------------------------------------
    // Recolectar estado de los controles del formulario
    // -------------------------------------------------------------------------
    function collectFilterState() {
        var generos = [];
        dom.form.querySelectorAll('input[name="genero[]"]:checked').forEach(function (cb) {
            generos.push(cb.value);
        });
        var autores = [];
        dom.form.querySelectorAll('input[name="autor[]"]:checked').forEach(function (cb) {
            autores.push(cb.value);
        });
        var precioMinEl = document.getElementById('precio-min');
        var precioMaxEl = document.getElementById('precio-max');

        state.filters.generos  = generos;
        state.filters.autor    = autores;
        state.filters.precioMin = precioMinEl ? precioMinEl.value : '';
        state.filters.precioMax = precioMaxEl ? precioMaxEl.value : '';
    }

    // -------------------------------------------------------------------------
    // Sincronizar selects al estado (sin disparar eventos)
    // -------------------------------------------------------------------------
    function syncSelectsToState() {
        if (dom.selectDesktop) dom.selectDesktop.value = state.orden;
        if (dom.selectMobile)  dom.selectMobile.value  = state.orden;
    }

    // -------------------------------------------------------------------------
    // Limpiar filtros
    // -------------------------------------------------------------------------
    function clearFilters() {
        state.filters = { generos: [], autor: [], precioMin: '', precioMax: '', q: '' };
        state.orden = 'titulo-asc';
        state.currentPage = 1;

        dom.form.querySelectorAll('input[type="checkbox"]').forEach(function (cb) { cb.checked = false; });
        var precioMinEl = document.getElementById('precio-min');
        var precioMaxEl = document.getElementById('precio-max');
        if (precioMinEl) precioMinEl.value = '';
        if (precioMaxEl) precioMaxEl.value = '';

        syncSelectsToState();
        render();
    }

    // -------------------------------------------------------------------------
    // Motor de filtrado (función pura)
    // -------------------------------------------------------------------------
    function applyFilters(books, filters) {
        return books.filter(function (book) {
            if (filters.generos.length > 0 && filters.generos.indexOf(book.genre) === -1) return false;
            if (filters.autor.length > 0 && filters.autor.indexOf(book.author) === -1) return false;
            if (filters.precioMin !== '' && parseFloat(book.price) < parseFloat(filters.precioMin)) return false;
            if (filters.precioMax !== '' && parseFloat(book.price) > parseFloat(filters.precioMax)) return false;
            if (filters.q) {
                var q = filters.q.toLowerCase();
                var haystack = (book.title + ' ' + book.author + ' ' + book.genre).toLowerCase();
                if (haystack.indexOf(q) === -1) return false;
            }
            return true;
        });
    }

    // -------------------------------------------------------------------------
    // Motor de ordenamiento (función pura)
    // -------------------------------------------------------------------------
    function applySort(books, orden) {
        var sorted = books.slice();
        sorted.sort(function (a, b) {
            switch (orden) {
                case 'titulo-desc':
                    return b.title.localeCompare(a.title, 'es');
                case 'precio-asc':
                    return parseFloat(a.price) - parseFloat(b.price);
                case 'precio-desc':
                    return parseFloat(b.price) - parseFloat(a.price);
                case 'autor-asc':
                    var cmpAutor = a.author.localeCompare(b.author, 'es');
                    return cmpAutor !== 0 ? cmpAutor : a.title.localeCompare(b.title, 'es');
                case 'recientes':
                    return b.id - a.id;
                default: // titulo-asc
                    return a.title.localeCompare(b.title, 'es');
            }
        });
        return sorted;
    }

    // -------------------------------------------------------------------------
    // Render orquestador
    // -------------------------------------------------------------------------
    function render() {
        teardownSentinel();

        state.filtered = applySort(applyFilters(state.allBooks, state.filters), state.orden);

        // Siempre volver a página 1 al cambiar filtros/orden/perPage
        state.currentPage = 1;

        if (state.infiniteScroll) {
            state.infiniteScrollPage = 1;
            renderInfiniteFirstPage();
            setupSentinel();
        } else {
            renderPage();
            renderPagination();
        }

        updateResultCount();
        updateBadge();
        syncCsvHref();
        pushUrl();
    }

    // -------------------------------------------------------------------------
    // Renderizar página actual (paginación tradicional)
    // -------------------------------------------------------------------------
    function renderPage() {
        var start = (state.currentPage - 1) * state.perPage;
        var slice = state.filtered.slice(start, start + state.perPage);
        dom.listaJs.innerHTML = '';
        slice.forEach(function (book) {
            dom.listaJs.appendChild(buildCard(book));
        });
    }

    // -------------------------------------------------------------------------
    // Renderizar controles de paginación
    // -------------------------------------------------------------------------
    function renderPagination() {
        var total = state.filtered.length;
        var totalPages = Math.max(1, Math.ceil(total / state.perPage));
        var cur = state.currentPage;

        if (state.infiniteScroll) {
            dom.paginacionJs.innerHTML = '';
            return;
        }

        var ol = document.createElement('ol');

        if (cur > 1) {
            ol.appendChild(makePagLink('Anterior', cur - 1, 'Página anterior'));
        }

        // Ventana deslizante: máx 7 números visibles
        var windowSize = 7;
        var half = Math.floor(windowSize / 2);
        var start = Math.max(1, cur - half);
        var end   = Math.min(totalPages, start + windowSize - 1);
        if (end - start + 1 < windowSize) start = Math.max(1, end - windowSize + 1);

        if (start > 1) {
            ol.appendChild(makePagLink('1', 1, 'Página 1'));
            if (start > 2) ol.appendChild(makePagEllipsis());
        }

        for (var i = start; i <= end; i++) {
            var li = makePagLink(String(i), i, 'Página ' + i);
            if (i === cur) {
                li.querySelector('a').setAttribute('aria-current', 'page');
            }
            ol.appendChild(li);
        }

        if (end < totalPages) {
            if (end < totalPages - 1) ol.appendChild(makePagEllipsis());
            ol.appendChild(makePagLink(String(totalPages), totalPages, 'Página ' + totalPages));
        }

        if (cur < totalPages) {
            ol.appendChild(makePagLink('Siguiente', cur + 1, 'Página siguiente'));
        }

        dom.paginacionJs.innerHTML = '';
        dom.paginacionJs.appendChild(ol);
    }

    function makePagLink(text, page, label) {
        var li = document.createElement('li');
        var a  = document.createElement('a');
        a.textContent = text;
        a.href = '#';
        a.setAttribute('aria-label', label);
        a.dataset.page = String(page);
        li.appendChild(a);
        return li;
    }

    function makePagEllipsis() {
        var li = document.createElement('li');
        var span = document.createElement('span');
        span.textContent = '…';
        span.className = 'pag-ellipsis';
        li.appendChild(span);
        return li;
    }

    // -------------------------------------------------------------------------
    // Scroll infinito
    // -------------------------------------------------------------------------
    function renderInfiniteFirstPage() {
        var slice = state.filtered.slice(0, state.perPage);
        dom.listaJs.innerHTML = '';
        slice.forEach(function (book) {
            dom.listaJs.appendChild(buildCard(book));
        });
        dom.paginacionJs.innerHTML = '';
    }

    function appendInfinitePage() {
        var page = state.infiniteScrollPage;
        var start = page * state.perPage;
        var slice = state.filtered.slice(start, start + state.perPage);
        slice.forEach(function (book) {
            dom.listaJs.appendChild(buildCard(book));
        });
        state.infiniteScrollPage++;

        var totalLoaded = state.infiniteScrollPage * state.perPage;
        if (totalLoaded >= state.filtered.length) {
            teardownSentinel();
        }
    }

    function setupSentinel() {
        if (!dom.sentinel || !window.IntersectionObserver) return;
        scrollObserver = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) {
                appendInfinitePage();
            }
        }, { rootMargin: '200px' });
        scrollObserver.observe(dom.sentinel);
    }

    function teardownSentinel() {
        if (scrollObserver) {
            scrollObserver.disconnect();
            scrollObserver = null;
        }
    }

    // -------------------------------------------------------------------------
    // Construir tarjeta de libro
    // -------------------------------------------------------------------------
    function buildCard(book) {
        var li = document.createElement('li');
        var article = document.createElement('article');

        // Enlace + imagen + título
        var a = document.createElement('a');
        a.href = 'book-detail?id=' + encodeURIComponent(book.id);

        var picture = buildPicture(book.image, book.title);
        a.appendChild(picture);

        var h3 = document.createElement('h3');
        h3.textContent = book.title;
        a.appendChild(h3);
        article.appendChild(a);

        // Autor
        var pAutor = document.createElement('p');
        pAutor.textContent = book.author;
        article.appendChild(pAutor);

        // Precio
        var pPrecio = document.createElement('p');
        var strong = document.createElement('strong');
        strong.textContent = '$ ' + formatPrecio(book.price);
        pPrecio.appendChild(strong);
        article.appendChild(pPrecio);

        // Botón reservar
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = 'Reservar';
        article.appendChild(btn);

        li.appendChild(article);
        return li;
    }

    function buildPicture(imageStr, alt) {
        var picture = document.createElement('picture');
        var parts = (imageStr || '').split(';');
        var fallbackSrc = '';

        parts.forEach(function (part) {
            var m = part.match(/^(\d+):(.+)$/);
            if (m) {
                var source = document.createElement('source');
                source.srcset = IMAGE_BASE + m[2];
                source.media  = '(max-width: ' + m[1] + 'px)';
                picture.appendChild(source);
            } else {
                fallbackSrc = part;
            }
        });

        var img = document.createElement('img');
        img.src = IMAGE_BASE + fallbackSrc;
        img.alt = alt || '';
        picture.appendChild(img);
        return picture;
    }

    function formatPrecio(price) {
        return parseFloat(price).toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    // -------------------------------------------------------------------------
    // Conteo de resultados y badge
    // -------------------------------------------------------------------------
    function updateResultCount() {
        if (!dom.resultCount) return;
        var total = state.filtered.length;
        dom.resultCount.textContent = total === 1 ? '1 libro encontrado' : total + ' libros encontrados';
    }

    function updateBadge() {
        if (!dom.badge) return;
        var count = state.filters.generos.length + state.filters.autor.length +
            (state.filters.precioMin !== '' ? 1 : 0) +
            (state.filters.precioMax !== '' ? 1 : 0);
        if (count > 0) {
            dom.badge.textContent = count;
            dom.badge.hidden = false;
        } else {
            dom.badge.hidden = true;
        }
    }

    // -------------------------------------------------------------------------
    // Sincronizar href del link CSV con los filtros JS actuales
    // -------------------------------------------------------------------------
    function syncCsvHref() {
        if (!dom.csvLinkJs) return;
        var params = new URLSearchParams();
        state.filters.generos.forEach(function (g) { params.append('genero[]', g); });
        state.filters.autor.forEach(function (a)   { params.append('autor[]', a); });
        if (state.filters.precioMin) params.set('precio_min', state.filters.precioMin);
        if (state.filters.precioMax) params.set('precio_max', state.filters.precioMax);
        if (state.filters.q)        params.set('q', state.filters.q);
        if (state.orden)            params.set('orden', state.orden);
        params.set('format', 'csv');
        dom.csvLinkJs.href = '/catalog?' + params.toString();
    }

    // -------------------------------------------------------------------------
    // URL / History API
    // -------------------------------------------------------------------------
    function pushUrl() {
        var params = new URLSearchParams();
        state.filters.generos.forEach(function (g) { params.append('genero[]', g); });
        state.filters.autor.forEach(function (a)   { params.append('autor[]', a); });
        if (state.filters.precioMin) params.set('precio_min', state.filters.precioMin);
        if (state.filters.precioMax) params.set('precio_max', state.filters.precioMax);
        if (state.filters.q)        params.set('q', state.filters.q);
        if (state.orden && state.orden !== 'titulo-asc') params.set('orden', state.orden);
        if (!state.infiniteScroll && state.currentPage > 1) params.set('pagina', String(state.currentPage));
        if (state.perPage !== 12) params.set('por_pagina', String(state.perPage));
        if (state.infiniteScroll) params.set('scroll', '1');

        var qs = params.toString();
        history.pushState(null, '', qs ? '?' + qs : location.pathname);
    }

    // -------------------------------------------------------------------------
    // Drawer mobile
    // -------------------------------------------------------------------------
    function openDrawer() {
        state.drawerOpen = true;
        dom.drawer.classList.add('drawer-open');
        dom.backdrop.classList.add('visible');
        dom.backdrop.setAttribute('aria-hidden', 'false');
        if (dom.btnAbrir) dom.btnAbrir.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        dom.drawer.addEventListener('keydown', trapFocus);

        // Foco al primer elemento interactivo del drawer
        var first = dom.drawer.querySelector('button, input, select, [tabindex]:not([tabindex="-1"])');
        if (first) first.focus();
    }

    function closeDrawer() {
        state.drawerOpen = false;
        dom.drawer.classList.remove('drawer-open');
        dom.backdrop.classList.remove('visible');
        dom.backdrop.setAttribute('aria-hidden', 'true');
        if (dom.btnAbrir) {
            dom.btnAbrir.setAttribute('aria-expanded', 'false');
            dom.btnAbrir.focus();
        }
        document.body.style.overflow = '';
        dom.drawer.removeEventListener('keydown', trapFocus);
    }

    function trapFocus(e) {
        if (e.key !== 'Tab') return;
        var focusable = dom.drawer.querySelectorAll(
            'button:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        if (focusable.length === 0) return;
        var first = focusable[0];
        var last  = focusable[focusable.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    }

    // -------------------------------------------------------------------------
    // Búsquedas recientes (localStorage)
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
        var busquedas = leerBusquedas();
        busquedas = busquedas.filter(function (b) { return b !== q; });
        busquedas.unshift(q);
        if (busquedas.length > MAX_BUSQUEDAS) busquedas = busquedas.slice(0, MAX_BUSQUEDAS);
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(busquedas)); } catch (e) {}
    }

    // -------------------------------------------------------------------------
    // Arranque
    // -------------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', init);

    // Toggle "Ver todos" en filtro de autor (preservado del inline script original)
    document.addEventListener('DOMContentLoaded', function () {
        var verTodosBtn = document.getElementById('autor-ver-todos');
        var listaOculta = document.getElementById('autor-lista-oculta');
        if (verTodosBtn && listaOculta) {
            verTodosBtn.addEventListener('click', function () {
                var estaVisible = listaOculta.style.display !== 'none';
                listaOculta.style.display = estaVisible ? 'none' : 'block';
                verTodosBtn.textContent = estaVisible ? 'Ver todos' : 'Ver menos';
            });
        }
    });

}());
