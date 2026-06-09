/**
 * Libreria de carrusel responsive con:
 * - carga progresiva de imagenes,
 * - navegacion por botones, thumbs, swipe y teclado,
 * - efectos de desplazamiento configurables por constructor.
 */
class Carousel {

    static #activeInstance = null;

    #container;
    #root;
    #effect = 'slide';
    #options = {
        effect: 'slide',
        autoPlayMs: 3500,
    };
    #currentIndex = 0;
    #isDragging = false;
    #touchStartX = 0;
    #touchCurrentX = 0;
    #autoPlayTimer = null;
    #preloadedImages = 0;
    #progressStartAt = 0;
    #progressHideTimer = null;
    #scrollAnimationFrame = null;

    /** Aloja los artículos del carrusel @type {HTMLElement[]} */
    #items = [];

    #totalItems = 0;
    #prevButton;
    #nextButton;
    #progress;
    #progressBar;
    #progressLabel;
    #thumbsContainer;
    #thumbButtons;

    /**
     * @param {HTMLElement} container - Contenedor donde se va a crear el carrusel.
     * @param {{effect?: 'slide'|'block'|'disappear', autoPlayMs?: number}} options - Parámetros para configurar el carrusel.
     */
    constructor(container, options = {}) {

        // Arroja error si el contenedor no es un elemento HTML.
        if (!(container instanceof HTMLElement)) {
            throw new Error('Carousel requiere un contenedor valido.');
        }

        this.#container = container;
        this.#root = container.parentElement;
        this.#effect = this.#normalizeEffect(options.effect || 'slide');
        this.#options = {
            effect: this.#effect,
            autoPlayMs: options.autoPlayMs || 3500,
        };

        this.#currentIndex = 0;
        this.#isDragging = false;
        this.#touchStartX = 0;
        this.#touchCurrentX = 0;
        this.#autoPlayTimer = null;
        this.#preloadedImages = 0;
        this.#progressStartAt = 0;
        this.#progressHideTimer = null;
        this.#scrollAnimationFrame = null;

        this.#items = Array.from(this.#container.querySelectorAll('article'));
        this.#totalItems = this.#items.length;

        if (!this.#root || this.#totalItems === 0) {
            return;
        }

        if (!Carousel.#activeInstance) {
            Carousel.#activeInstance = this;
        }

        this.#buildUi();
        this.#setupAccessibility();
        this.#bindEvents();

        // Deja que el navegador pinte la UI inicial antes de arrancar la precarga,
        // para que la barra de progreso sea perceptible en conexiones lentas.
        requestAnimationFrame(() => {
            this.#progressStartAt = performance.now();
            this.#activateMainImageSources();
            this.#preloadImagesAndStart();
        });
    }

    /**
     * Avanza al siguiente slide respetando el loop circular.
     * @returns {void}
     */
    next() {
        if (this.#effect === 'block') {
            const anchors = this.#getBlockAnchors();
            const currentAnchor = this.#getCurrentAnchor(this.#currentIndex, anchors);
            const currentAnchorPos = anchors.indexOf(currentAnchor);
            const nextAnchor = anchors[(currentAnchorPos + 1) % anchors.length];
            this.goTo(nextAnchor);
            return;
        }

        this.goTo(this.#currentIndex + 1);
    }

    /**
     * Retrocede al slide anterior respetando el loop circular.
     * @returns {void}
     */
    prev() {
        if (this.#effect === 'block') {
            const anchors = this.#getBlockAnchors();
            const currentAnchor = this.#getCurrentAnchor(this.#currentIndex, anchors);
            const currentAnchorPos = anchors.indexOf(currentAnchor);
            const prevPos = (currentAnchorPos - 1 + anchors.length) % anchors.length;
            this.goTo(anchors[prevPos]);
            return;
        }

        this.goTo(this.#currentIndex - 1);
    }

    /**
     * Navega a un índice del carrusel y aplica el efecto configurado.
     *
     * @param {number} index - Índice destino (se normaliza en rango válido).
     * @returns {void}
     */
    goTo(index) {
        // Si no hay items, no hay qué mover.
        if (!this.#totalItems) return;

        // Normaliza el índice al que hay que dirigirse y lo guarda ya
        // como índice actual.j
        this.#currentIndex =
            ((index % this.#totalItems) + this.#totalItems) // Transforma el potencial index negativo en valor positivo.
            % this.#totalItems;

        const shouldApplyScrollEffect = this.#hasRealScrollMovement(this.#currentIndex);

        if (shouldApplyScrollEffect) {
            if (this.#effect === 'disappear') {
                this.#applyDisappearEffect();
            } else {
                this.#applySlideEffect();
            }
        }

        this.#updateThumbs();
        this.#restartAutoplay();
    }

    /**
     * Construye la UI interna del carrusel: flechas, progreso y thumbnails.
     *
     * @returns {void}
     */
    #buildUi() {
        this.#root.classList.add('carousel-root');
        this.#container.classList.add('carousel-track');
        this.#container.classList.add('carousel-effect-' + this.#effect);

        this.#prevButton = this.#newElement('button', "", {
            type: 'button',
            'class': 'flecha flecha-izq',
            'aria-label': 'Imagen anterior'
        });

        this.#nextButton = this.#newElement('button', "", {
            type: 'button',
            'class': 'flecha flecha-der',
            'aria-label': 'Imagen siguiente'
        });

        this.#root.appendChild(this.#prevButton);
        this.#root.appendChild(this.#nextButton);

        this.#progress = this.#newElement('div',
            '<div class="carousel-progress-bar"></div><span class="carousel-progress-label">0%</span>',
            {
                'class': 'carousel-progress'
            },
            true
        );
        this.#progressBar = this.#progress.querySelector('.carousel-progress-bar');
        this.#progressLabel = this.#progress.querySelector('.carousel-progress-label');
        this.#root.appendChild(this.#progress);

        this.#thumbsContainer = this.#newElement('div', '', {
            'class': 'carousel-thumbs'
        });

        this.#thumbButtons = this.#items.map((item, index) => {
            // Contenedor para un thumb y su marca de selección.
            const thumbWrapper = this.#newElement('div', '', {
                'class': 'carousel-thumb-wrapper'
            });

            // Crea el botón del thumb.
            const thumb = this.#newElement('button', '', {
                type: 'button',
                'class': 'carousel-thumb is-loading',
                'aria-label': 'Ir a imagen ' + (index + 1),
                'data-index': String(index)
            });

            // Crea la marca de selección.
            const indicator = this.#newElement('span', '', {
                'class': 'carousel-thumb-indicator'
            });

            thumbWrapper.appendChild(thumb);
            thumbWrapper.appendChild(indicator);
            this.#thumbsContainer.appendChild(thumbWrapper);
            return thumb;
        });

        this.#root.appendChild(this.#thumbsContainer);
    }

    /**
     * Crea un elemento HTML con atributos y contenido opcional.
     *
     * @param {string} tagName - Etiqueta del elemento a crear.
     * @param {string} content - Texto o HTML según allowHtml.
     * @param {Record<string, string>} attributes - Atributos a setear.
     * @param {boolean} allowHtml - Si true, interpreta content como HTML.
     * @returns {HTMLElement}
     */
    #newElement(tagName, content = '', attributes = {}, allowHtml = false) {
        const element = document.createElement(tagName);

        Object.entries(attributes).forEach(([name, value]) => {
            element.setAttribute(name, value);
        });

        if (content) {
            if (allowHtml) element.innerHTML = content;
            else element.textContent = content;
        }

        return element;
    }

    /**
     * Configura atributos de accesibilidad para navegacion y anuncios de cambios.
     *
     * @returns {void}
     */
    #setupAccessibility() {
        // Habilita el tab en la secuencia en que aparecen los
        // elementos en el documento.
        this.#root.setAttribute('tabindex', '0');
        this.#container.setAttribute('tabindex', '0');

        // Informa al usuario de tecnologías asistivas cuando haya
        // un cambio, sin interrumpir la acción actual.
        this.#container.setAttribute('aria-live', 'polite');
    }

    /**
     * Registra los listeners de interacción: click, touch, teclado y wheel.
     *
     * @returns {void}
     */
    #bindEvents() {
        const setAsActive = () => {
            Carousel.#activeInstance = this;
        };

        this.#root.addEventListener('mouseenter', setAsActive);
        this.#root.addEventListener('pointerdown', setAsActive);
        this.#root.addEventListener('touchstart', setAsActive, { passive: true });
        this.#root.addEventListener('focusin', setAsActive);
        this.#prevButton.addEventListener('click', () => this.prev());
        this.#nextButton.addEventListener('click', () => this.next());

        this.#thumbButtons.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                this.goTo(Number(thumb.dataset.index || 0));
            });
        });

        this.#container.addEventListener(
            'touchstart',
            (event) => {

                // Si hay más de un punto de toque, no hace nada.
                if (event.touches.length !== 1) return;

                this.#isDragging = true;
                this.#touchStartX = event.touches[0].clientX;
                this.#touchCurrentX = this.#touchStartX;
            },

            // Permite que el navegador optimize el scroll nativo
            // y solo prevenga el comportamiento en touchmove si se
            // detecta un swipe horizontal significativo.
            { passive: true }

        );

        this.#container.addEventListener(
            'touchmove',
            (event) => {

                // Si no se está arrastrando o si el toque es con más de un
                // punto, no hace nada.
                if (!this.#isDragging || event.touches.length !== 1) return;

                this.#touchCurrentX = event.touches[0].clientX;
            },
            
            // Le indica al navegador que este manejador podría cancelar el
            // evento para evitar el scroll nativo, lo cual es necesario para
            // una experiencia de swipe fluida.
            { passive: false }
        );

        this.#container.addEventListener('touchend', () => {
            // Si no estaba arrastrando, no hace nada.
            if (!this.#isDragging) return;

            const distanceX = this.#touchCurrentX - this.#touchStartX;
            this.#isDragging = false;

            // Especifica un mínimo de arrastre para ejecutar la acción,
            // y determina, luego de ese mínimo, en si el movimiento es
            // hacia la izquierda o hacia la derecha.
            if (Math.abs(distanceX) < 35) return;
            if (distanceX < 0) this.next();
            if (distanceX > 0) this.prev();
        });

        document.addEventListener('keydown', (event) => {
            if (Carousel.#activeInstance !== this) return;

            // Permite que el usuario pueda moverse
            // en los campos mencionados cuando el foco
            // está en ellos.
            const target = event.target;
            if (target instanceof HTMLElement) {
                const tag = target.tagName;
                if (
                    tag === 'INPUT' ||
                    tag === 'TEXTAREA' ||
                    tag === 'SELECT' ||
                    target.isContentEditable
                ) {
                    return;
                }
            }

            // Asigna las respectivas acciones para las
            // teclas de flecha.
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                this.prev();
            }
            if (event.key === 'ArrowRight') {
                event.preventDefault();
                this.next();
            }
        });
    }

    /**
     * Precarga imagenes del carrusel y actualiza la barra de progreso.
     * Al completar el 100%, habilita el inicio de la animacion.
     * @returns {void}
     */
    #preloadImagesAndStart() {
        // Solo cuenta las imagenes principales de cada slide (una por article).
        const images = this.#items
            .map((item) => item.querySelector('img'))
            .filter((img) => img instanceof HTMLImageElement);
        const totalImages = images.length;

        if (totalImages === 0) {
            this.#setProgress(100);
            this.#finishProgressAndStart();
            return;
        }

        const onImageReady = (img, index) => {
            this.#preloadedImages += 1;
            this.#copyMainImgToThumbImg(img, index);
            const pct = Math.min(100, Math.round((this.#preloadedImages / totalImages) * 100));
            this.#setProgress(pct);

            if (this.#preloadedImages >= totalImages) {
                this.#finishProgressAndStart();
            }
        };

        images.forEach((img, index) => {
            if (img.complete && img.naturalWidth > 0) {
                onImageReady(img, index);
                return;
            }

            img.addEventListener('load', () => onImageReady(img, index), { once: true });
            img.addEventListener('error', () => onImageReady(img, index), { once: true });
        });
    }

    /**
     * Activa la carga real de imagenes principales seteando src y srcset desde data attributes.
     * @returns {void}
     */
    #activateMainImageSources() {
        this.#items.forEach((item) => {
            const picture = item.querySelector('picture');
            const img = item.querySelector('img');

            if (!img) return;

            if (picture) {
                const sources = Array.from(picture.querySelectorAll('source[data-carousel-srcset]'));
                sources.forEach((source) => {
                    const srcset = source.dataset.carouselSrcset;
                    if (srcset) {
                        source.setAttribute('srcset', srcset);
                    }
                });
            }

            const mainSrc = img.dataset.carouselSrc;
            if (mainSrc) {
                img.setAttribute('src', mainSrc);
            }
        });
    }

    /**
     * Copia la fuente de la imagen principal al thumbnail correspondiente.
     * 
     * @param {HTMLImageElement} img - Imagen principal.
     * @param {number} index - Indice asociado.
     * @returns {void}
     */
    #copyMainImgToThumbImg(img, index) {
        const thumb = this.#thumbButtons[index];
        if (!thumb || !img) return;

        const imageUrl = this.#getThumbImageUrl(img);
        if (!imageUrl) {
            return;
        }

        thumb.style.backgroundImage = 'url("' + imageUrl + '")';
        thumb.classList.remove('is-loading');
    }

    /**
     * Resuelve la URL que se debe usar en el thumbnail.
     * 
     * @param {HTMLImageElement} img - Imagen a resolver.
     * @returns {string}
     */
    #getThumbImageUrl(img) {
        // Devuelve la imagen que terminó seleccionando el navegador (que no
        // necesariamente es igual al valor del atributo img.src, debido a los
        // <source> de <picture>), siempre que no sea el placeholder.
        const currentSrc = img.currentSrc;
        if (currentSrc && !currentSrc.startsWith('data:image/svg+xml')) {
            return currentSrc;
        }

        // Devuelve el valor de img.data-carousel-src, que a esta altura será
        // la imagen fallback.
        const mainSrc = img.dataset.carouselSrc;
        if (mainSrc) {
            return mainSrc;
        }

        return '';
    }

    /**
     * Actualiza visualmente el porcentaje de progreso de carga.
     * @param {number} percent - Porcentaje en rango 0-100.
     * @returns {void}
     */
    #setProgress(percent) {
        this.#progressBar.style.width = percent + '%';
        this.#progressLabel.textContent = percent + '%';
    }

    /**
     * Marca el carrusel como listo, oculta progreso y posiciona el primer slide.
     * @returns {void}
     */
    #finishProgressAndStart() {
        const elapsed = performance.now() - this.#progressStartAt;
        const minimumVisibleMs = 700;

        if (this.#progressHideTimer) {
            clearTimeout(this.#progressHideTimer);
            this.#progressHideTimer = null;
        }

        const finalize = () => {
            this.#root.classList.add('carousel-ready');
            this.goTo(0);
        };

        if (elapsed < minimumVisibleMs) {
            this.#progressHideTimer = setTimeout(finalize, minimumVisibleMs - elapsed);
            return;
        }

        finalize();
    }

    /**
     * Sincroniza el estado visual de thumbnails segun el slide activo.
     * @returns {void}
     */
    #updateThumbs() {
        this.#thumbButtons.forEach((thumb, index) => {
            // Buscar el indicador en el wrapper padre
            const wrapper = thumb.parentElement;
            const indicator = wrapper.querySelector('.carousel-thumb-indicator');
            if (index === this.#currentIndex) {
                thumb.classList.add('is-active');
                if (indicator) indicator.classList.add('is-active');
            } else {
                thumb.classList.remove('is-active');
                if (indicator) indicator.classList.remove('is-active');
            }
        });
    }

    /**
     * Aplica transicion por desplazamiento horizontal suave.
     * @returns {void}
     */
    #applySlideEffect() {
        const target = this.#items[this.#currentIndex];
        if (!target) return;

        if (this.#scrollAnimationFrame) {
            cancelAnimationFrame(this.#scrollAnimationFrame);
            this.#scrollAnimationFrame = null;
        }

        const left = this.#getTargetScrollLeft(this.#currentIndex);
        this.#container.scrollTo({ left: left, behavior: 'smooth' });
    }

    /**
     * Aplica efecto de desaparicion: fade out, scroll, fade in.
     * Solo aplica si hay movimiento real de scroll.
     * @returns {void}
     */
    #applyDisappearEffect() {
        const target = this.#items[this.#currentIndex];
        if (!target) return;

        const targetLeft = this.#getTargetScrollLeft(this.#currentIndex);
        const currentLeft = this.#container.scrollLeft;

        if (Math.abs(targetLeft - currentLeft) < 1) {
            return;
        }

        const previousSnapType = this.#container.style.scrollSnapType;
        this.#container.style.scrollSnapType = 'none';
        this.#container.style.transition = 'opacity 0.35s ease';

        this.#container.style.opacity = '0';
        setTimeout(() => {
            this.#container.scrollLeft = targetLeft;
            setTimeout(() => {
                this.#container.style.opacity = '1';
                setTimeout(() => {
                    this.#container.style.transition = '';
                    this.#container.style.scrollSnapType = previousSnapType;
                }, 350);
            }, 50);
        }, 350);
    }

    /**
     * Determina si un índice objetivo implica movimiento real de scroll.
     *
     * @param {number} index - Índice del ítem objetivo.
     * @returns {boolean}
     */
    #hasRealScrollMovement(index) {
        const targetLeft = this.#getTargetScrollLeft(index);
        const currentLeft = this.#container.scrollLeft;
        return Math.abs(targetLeft - currentLeft) >= 1;
    }

    /**
     * Calcula el scrollLeft efectivo para un índice, limitado al rango válido.
     *
     * Utiliza:
     * - this.#items.
     * - this.#container.
     *
     * @param {number} index - Índice del ítem objetivo.
     * @returns {number}
     */
    #getTargetScrollLeft(index) {
        const target = this.#items[index];
        if (!target) return this.#container.scrollLeft;

        const rawLeft = target.offsetLeft;
        
        // Guarda la cantidad de píxeles que quedan por mostrar en pantalla.
        const maxLeft = this.#container.scrollWidth - this.#container.clientWidth;
        //const maxLeft = Math.max(0, this.#container.scrollWidth - this.#container.clientWidth);

        return Math.min(Math.max(0, rawLeft), maxLeft);
    }

    /**
     * Obtiene los índices que funcionarán como anclajes de bloque para
     * la navegacion por bloques. Cada índice será un inicio de bloque.
     *
     * @returns {number[]} - Índices de comienzo de bloque.
     */
    #getBlockAnchors() {
        const anchors = []; // Guardará los índices de los comienzos de bloque.
        let start = 0;

        // Guarda los índices de comienzo de bloque.
        while (start < this.#totalItems) {
            anchors.push(start);
            start += this.#countVisibleFromIndex(start);
        }

        return anchors.length ? anchors : [0];
    }

    /**
     * Obtiene el anclaje de bloque que contiene al índice pasado por
     * parámetro.
     *
     * @param {number} index - Índice de referencia actual.
     * @param {number[]} anchors - Lista de inicios de bloque.
     * @returns {number}
     */
    #getCurrentAnchor(index, anchors) {
        let closest = anchors[0];

        for (const anchor of anchors) {
            if (anchor <= index) {
                closest = anchor;
                continue;
            }
            break;
        }

        return closest;
    }

    /**
     * Cuenta cuántos ítems entran en viewport desde un índice de inicio.
     * @param {number} startIndex - Índice desde donde comienza el bloque
     * (se asume que el elemento señalado por este índice no desborda el
     * viewport).
     * @returns {number} - La cantidad de elementos que no desbordan el view
     * port (asume que el índice no desborda).
     */
    #countVisibleFromIndex(startIndex) {
        const startItem = this.#items[startIndex];
        const viewportWidth = this.#container.clientWidth;
        const startLeft = startItem.offsetLeft;

        // Para almacenar la cantidad de elementos adicionales que no
        // desbordan el viewport.
        let count = 0;

        for (let i = startIndex; i < this.#totalItems; i += 1) {
            const item = this.#items[i];

            // Guarda el tamaño en píxeles desde el comienzo del primer item
            // hasta el final del ítem actual.
            const relativeEnd = item.offsetLeft + item.offsetWidth - startLeft;

            // Si el elemento excede los límites del viewport del contenedor,
            // termina el bucle.
            if (count > 0 && relativeEnd > viewportWidth + 1) {
                break;
            }

            count += 1;
        }

        return Math.max(1, count);
    }

    /**
     * Realiza una animacion manual de scroll horizontal con easing custom.
     * @param {number} targetLeft - Posicion final de scrollLeft.
     * @param {number} durationMs - Duracion de animacion en milisegundos.
     * @param {(t:number) => number} easingFn - Funcion de easing, con t en [0,1].
     * @returns {void}
     */
    #animateScrollTo(targetLeft, durationMs, easingFn) {
        if (this.#scrollAnimationFrame) {
            cancelAnimationFrame(this.#scrollAnimationFrame);
            this.#scrollAnimationFrame = null;
        }

        const previousSnapType = this.#container.style.scrollSnapType;
        this.#container.style.scrollSnapType = 'none';

        const startLeft = this.#container.scrollLeft;
        const delta = targetLeft - startLeft;

        if (Math.abs(delta) < 1) {
            this.#container.scrollLeft = targetLeft;
            this.#container.style.scrollSnapType = previousSnapType;
            return;
        }

        const startAt = performance.now();
        const tick = (now) => {
            const elapsed = now - startAt;
            const progress = Math.min(1, elapsed / durationMs);
            const eased = easingFn(progress);
            this.#container.scrollLeft = startLeft + (delta * eased);

            if (progress < 1) {
                this.#scrollAnimationFrame = requestAnimationFrame(tick);
                return;
            }

            this.#container.scrollLeft = targetLeft;
            this.#container.style.scrollSnapType = previousSnapType;
            this.#scrollAnimationFrame = null;
        };

        this.#scrollAnimationFrame = requestAnimationFrame(tick);
    }

    /**
     * Normaliza nombres de efectos.
     *
     * @param {string} effect - Nombre solicitado para el efecto.
     * @returns {'slide'|'block'|'disappear'}
     */
    #normalizeEffect(effect) {
        if (effect === 'block' || effect === 'disappear') return effect;
        return 'slide';
    }

    /**
     * Reinicia el autoplay con el intervalo configurado.
     *
     * @returns {void}
     */
    #restartAutoplay() {
        if (!this.#options.autoPlayMs) {
            return;
        }

        if (this.#autoPlayTimer) {
            clearTimeout(this.#autoPlayTimer);
        }

        this.#autoPlayTimer = setTimeout(() => this.next(), this.#options.autoPlayMs);
    }

}
